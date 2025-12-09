let currentQuadrant = null;
let editingTaskId = null;
let taskIdCounter = 0;

// API helper - uses relative path to CRUD Task.php and includes credentials
function api(path, opts = {}) {
  const url = path.startsWith('http') ? path : ('../../CRUD/Task.php' + (path ? (path.startsWith('?') ? path : ('?' + path)) : ''));
  const fetchOpts = Object.assign({
    credentials: 'same-origin',
    headers: {}
  }, opts);

  // If body is plain object, convert to URLSearchParams
  if (fetchOpts.body && typeof fetchOpts.body === 'object' && !(fetchOpts.body instanceof FormData)) {
    fetchOpts.body = new URLSearchParams(fetchOpts.body);
    fetchOpts.headers['Content-Type'] = 'application/x-www-form-urlencoded';
  }

  return fetch(url, fetchOpts).then(r => r.json());
}

// Load tasks from server and render
function loadTasks() {
  api('action=list', { method: 'GET' })
    .then(data => {
      if (!data || !data.success) return;
      const tasks = data.tasks || [];
      // Clear current lists
      Object.keys(quadrantNames).forEach(q => {
        const el = document.getElementById(q);
        if (el) el.innerHTML = '';
      });

      if (tasks.length === 0) {
        // show no-tasks in each quadrant
        Object.keys(quadrantNames).forEach(q => {
          const el = document.getElementById(q);
          if (el && el.children.length === 0) el.innerHTML = '<p class="no-tasks">No tasks in this quadrant</p>';
        });
        return;
      }

      tasks.forEach(t => {
        const list = document.getElementById(t.quadrant || 'others');
        if (!list) return;

        // Create task element with server id
        taskIdCounter++;
        const taskId = 'task-' + taskIdCounter;
        const task = document.createElement('div');
        task.className = 'task';
        task.id = taskId;
        task.dataset.title = t.title;
        task.dataset.description = t.description || '';
        task.dataset.subject = t.subject || '';
        task.dataset.quadrant = t.quadrant || 'others';
        task.dataset.completed = (t.status && t.status.toLowerCase() === 'completed') ? 'true' : 'false';
        task.dataset.serverId = t.task_id;

        task.innerHTML = createTaskHTML(taskId, t.title, t.description || '', t.subject || '', task.dataset.completed === 'true');
        list.appendChild(task);
      });
    })
    .catch(err => {
      console.error('Failed to load tasks', err);
    });
}

// Quadrant name mapping
const quadrantNames = {
  'urgent-important': 'Important & Urgent',
  'important': 'Important but Not Urgent',
  'urgent': 'Not Important but Urgent',
  'others': 'Not Important & Not Urgent'
};

function openModal(quad) {
  currentQuadrant = quad;
  editingTaskId = null;

  const modal = document.getElementById("modal");
  modal.classList.add('show');

  // Reset form for new task
  document.getElementById('modal-title').textContent = 'Add New Task';
  document.getElementById('modal-subtitle').textContent = 'Create a new task in the Eisenhower Matrix';
  document.getElementById('submit-btn').textContent = 'Add Task';

  // Set the quadrant dropdown to match the clicked quadrant
  const quadrantBtn = document.getElementById('selected-quadrant');
  quadrantBtn.textContent = quadrantNames[quad];

  // Update active state in dropdown
  const dropdownItems = document.querySelectorAll('.dropdown-item');
  dropdownItems.forEach(item => {
    item.classList.remove('active');
  });

  // Find and activate the correct dropdown item
  dropdownItems.forEach(item => {
    if (item.querySelector('span').textContent === quadrantNames[quad]) {
      item.classList.add('active');
    }
  });

  // Focus on the task title input
  setTimeout(() => {
    document.getElementById('task-title').focus();
  }, 100);
}

function closeModal() {
  const modal = document.getElementById("modal");
  modal.classList.remove('show');

  // Reset form
  document.getElementById("task-title").value = "";
  document.getElementById("task-description").value = "";
  document.getElementById("task-subject").value = "";
  editingTaskId = null;

  // Close dropdown if open
  const dropdownMenu = document.getElementById('dropdown-menu');
  const dropdownBtn = document.getElementById('quadrant-btn');
  dropdownMenu.classList.remove('show');
  dropdownBtn.classList.remove('open');
}

function toggleDropdown() {
  const dropdownMenu = document.getElementById('dropdown-menu');
  const dropdownBtn = document.getElementById('quadrant-btn');

  dropdownMenu.classList.toggle('show');
  dropdownBtn.classList.toggle('open');
}

function selectQuadrant(quadrantId, quadrantName, element) {
  // Update current quadrant
  currentQuadrant = quadrantId;

  // Update button text
  document.getElementById('selected-quadrant').textContent = quadrantName;

  // Update active state - remove from all
  const dropdownItems = document.querySelectorAll('.dropdown-item');
  dropdownItems.forEach(item => {
    item.classList.remove('active');
  });

  // Add active to clicked item
  element.classList.add('active');

  // Close dropdown
  toggleDropdown();
}

function saveTask(event) {
  event.preventDefault();

  if (editingTaskId) {
    updateTask();
  } else {
    addTask();
  }
}

function addTask() {
  const taskTitle = document.getElementById("task-title").value.trim();
  const taskDescription = document.getElementById("task-description").value.trim();
  const taskSubject = document.getElementById("task-subject").value.trim();

  if (taskTitle === "") {
    alert("Please enter a task title");
    return;
  }

  const list = document.getElementById(currentQuadrant);

  // Remove "no tasks" message if it exists
  const noTasksMsg = list.querySelector('.no-tasks');
  if (noTasksMsg) {
    noTasksMsg.remove();
  }

  // Optimistically create UI element and persist to server
  taskIdCounter++;
  const taskId = `task-${taskIdCounter}`;

  const task = document.createElement("div");
  task.className = "task pending-save";
  task.id = taskId;
  task.dataset.title = taskTitle;
  task.dataset.description = taskDescription;
  task.dataset.subject = taskSubject;
  task.dataset.quadrant = currentQuadrant;
  task.dataset.completed = "false";

  task.innerHTML = createTaskHTML(taskId, taskTitle, taskDescription, taskSubject, false);
  list.appendChild(task);

  // Close modal immediately for UX
  closeModal();

  // Persist to server
  api('action=create', {
    method: 'POST',
    body: {
      title: taskTitle,
      description: taskDescription,
      subject: taskSubject,
      quadrant: currentQuadrant,
      priority: 'Medium',
      due_date: '' ,
      status: 'Pending'
    }
  }).then(resp => {
    if (resp && resp.success) {
      // Save server id on element and remove pending marker
      task.dataset.serverId = resp.id || resp.inserted_id || resp.task_id || '';
      task.classList.remove('pending-save');
      showNotification('Task added successfully');
      // Optionally refresh dashboard counts via redirect or small polling
    } else {
      // Remove optimistic element
      task.remove();
      showNotification('Failed to save task');
      console.error('Create task failed', resp);
    }
  }).catch(err => {
    task.remove();
    showNotification('Failed to save task');
    console.error('Create task error', err);
  });
}

function createTaskHTML(taskId, title, description, subject, completed) {
  let html = '<div class="task-header">';
  html += '<div class="task-content">';
  html += `<div class="task-title ${completed ? 'completed' : ''}">${title}</div>`;

  if (description) {
    html += `<div class="task-description">${description}</div>`;
  }

  html += '</div>';
  html += '<div class="task-actions">';
  html += `<button class="task-action-btn check" onclick="toggleComplete('${taskId}')" title="Mark as complete"><i class="bi bi-check-lg"></i></button>`;
  html += `<button class="task-action-btn edit" onclick="editTask('${taskId}')" title="Edit task"><i class="bi bi-pencil"></i></button>`;
  html += `<button class="task-action-btn delete" onclick="deleteTask('${taskId}')" title="Delete task"><i class="bi bi-trash"></i></button>`;
  html += '</div>';
  html += '</div>';

  return html;
}

function toggleComplete(taskId) {
  const task = document.getElementById(taskId);
  const isCompleted = task.dataset.completed === "true";

  task.dataset.completed = !isCompleted;

  const titleElement = task.querySelector('.task-title');
  if (!isCompleted) {
    titleElement.classList.add('completed');
  } else {
    titleElement.classList.remove('completed');
  }
}

function editTask(taskId) {
  const task = document.getElementById(taskId);
  editingTaskId = taskId;
  currentQuadrant = task.dataset.quadrant;

  // Populate form with existing data
  document.getElementById('task-title').value = task.dataset.title;
  document.getElementById('task-description').value = task.dataset.description;
  document.getElementById('task-subject').value = task.dataset.subject;

  // Update modal UI for editing
  document.getElementById('modal-title').textContent = 'Edit Task';
  document.getElementById('modal-subtitle').textContent = 'Update task details';
  document.getElementById('submit-btn').textContent = 'Update Task';

  // Set quadrant dropdown
  const quadrantBtn = document.getElementById('selected-quadrant');
  quadrantBtn.textContent = quadrantNames[currentQuadrant];

  // Update active state in dropdown
  const dropdownItems = document.querySelectorAll('.dropdown-item');
  dropdownItems.forEach(item => {
    item.classList.remove('active');
    if (item.querySelector('span').textContent === quadrantNames[currentQuadrant]) {
      item.classList.add('active');
    }
  });

  // Open modal
  const modal = document.getElementById("modal");
  modal.classList.add('show');

  setTimeout(() => {
    document.getElementById('task-title').focus();
  }, 100);
}

function updateTask() {
  const taskTitle = document.getElementById("task-title").value.trim();
  const taskDescription = document.getElementById("task-description").value.trim();
  const taskSubject = document.getElementById("task-subject").value.trim();

  if (taskTitle === "") {
    alert("Please enter a task title");
    return;
  }

  const task = document.getElementById(editingTaskId);
  const oldQuadrant = task.dataset.quadrant;
  const completed = task.dataset.completed === "true";

  // Update task data
  task.dataset.title = taskTitle;
  task.dataset.description = taskDescription;
  task.dataset.subject = taskSubject;
  task.dataset.quadrant = currentQuadrant;

  // If quadrant changed, move task
  if (oldQuadrant !== currentQuadrant) {
    const newList = document.getElementById(currentQuadrant);

    // Remove "no tasks" message if exists
    const noTasksMsg = newList.querySelector('.no-tasks');
    if (noTasksMsg) {
      noTasksMsg.remove();
    }

    newList.appendChild(task);

    // Check if old quadrant is now empty
    const oldList = document.getElementById(oldQuadrant);
    if (oldList.children.length === 0) {
      oldList.innerHTML = '<p class="no-tasks">No tasks in this quadrant</p>';
    }
  }

  // Update task HTML
  task.innerHTML = createTaskHTML(editingTaskId, taskTitle, taskDescription, taskSubject, completed);

  closeModal();
  showNotification("Task updated successfully");
}

function deleteTask(taskId) {
  if (!confirm("Are you sure you want to delete this task?")) {
    return;
  }

  const task = document.getElementById(taskId);
  const quadrant = task.dataset.quadrant;

  task.remove();

  // Check if quadrant is now empty
  const list = document.getElementById(quadrant);
  if (list.children.length === 0) {
    list.innerHTML = '<p class="no-tasks">No tasks in this quadrant</p>';
  }

  showNotification("Task deleted");
}

function showNotification(message) {
  const notification = document.getElementById('notification');
  const notificationText = document.getElementById('notification-text');

  notificationText.textContent = message;
  notification.classList.add('show');

  setTimeout(() => {
    notification.classList.remove('show');
  }, 3000);
}

// Close dropdown when clicking outside
document.addEventListener('click', function (event) {
  const dropdownWrapper = document.querySelector('.dropdown-wrapper');
  const dropdownMenu = document.getElementById('dropdown-menu');
  const dropdownBtn = document.getElementById('quadrant-btn');

  if (dropdownWrapper && !dropdownWrapper.contains(event.target)) {
    dropdownMenu.classList.remove('show');
    dropdownBtn.classList.remove('open');
  }
});

// Close modal when clicking outside
document.getElementById('modal').addEventListener('click', function (event) {
  if (event.target === this) {
    closeModal();
  }
});

// Close modal with Escape key
document.addEventListener('keydown', function (event) {
  if (event.key === 'Escape') {
    const modal = document.getElementById('modal');
    if (modal.classList.contains('show')) {
      closeModal();
    }
  }
});

// Load tasks from server on page load
document.addEventListener('DOMContentLoaded', function () {
  loadTasks();
});