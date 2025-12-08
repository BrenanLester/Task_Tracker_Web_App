let currentQuadrant = null;
let editingTaskId = null;
let taskIdCounter = 0;

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

    // Create unique task ID
    taskIdCounter++;
    const taskId = `task-${taskIdCounter}`;

    // Create task element
    const task = document.createElement("div");
    task.className = "task";
    task.id = taskId;
    task.dataset.title = taskTitle;
    task.dataset.description = taskDescription;
    task.dataset.subject = taskSubject;
    task.dataset.quadrant = currentQuadrant;
    task.dataset.completed = "false";

    task.innerHTML = createTaskHTML(taskId, taskTitle, taskDescription, taskSubject, false);
    list.appendChild(task);

    closeModal();
    showNotification("Task added successfully");
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