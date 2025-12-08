/**
 * Tasks Frontend Logic
 * Communicates with CRUD API (src/CRUD/Task.php)
 */

const API_URL = '/Task_Tracker_Web_App/src/CRUD/Task.php';
let currentQuadrant = null;

// Load tasks on page load
document.addEventListener('DOMContentLoaded', function () {
    loadTasks();

    // Handle Enter key in modal
    const taskInput = document.getElementById('task-input');
    if (taskInput) {
        taskInput.addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                addTask();
            }
        });
    }
});

// Load all tasks from API
function loadTasks() {
    fetch(`${API_URL}?action=grouped`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Clear all quadrants
                ['urgent-important', 'important', 'urgent', 'others'].forEach(quadrant => {
                    document.getElementById(quadrant).innerHTML = '';
                });

                // Populate each quadrant
                const tasks = data.tasks;
                for (const quadrant in tasks) {
                    tasks[quadrant].forEach(task => {
                        renderTask(quadrant, task);
                    });
                }
            } else {
                console.error('Failed to load tasks:', data.message);
            }
        })
        .catch(error => {
            console.error('Error loading tasks:', error);
        });
}

// Render a task in the DOM
function renderTask(quadrant, task) {
    const list = document.getElementById(quadrant);
    const taskElement = document.createElement('div');
    taskElement.className = 'task';
    if (task.status === 'Completed') {
        taskElement.classList.add('completed');
    }
    taskElement.setAttribute('data-id', task.task_id);
    taskElement.innerHTML = `
        <input type="checkbox" class="task-checkbox" 
               ${task.status === 'Completed' ? 'checked' : ''} 
               onchange="toggleComplete(${task.task_id}, this.checked)">
        <span class="task-title">${escapeHtml(task.title)}</span>
        <button class="delete-btn" onclick="deleteTask(${task.task_id})">×</button>
    `;
    list.appendChild(taskElement);
}

// Toggle task completion
function toggleComplete(taskId, isCompleted) {
    const formData = new URLSearchParams();
    formData.append('action', 'update');
    formData.append('id', taskId);
    formData.append('status', isCompleted ? 'Completed' : 'Pending');

    // Get current task data to preserve other fields
    fetch(`${API_URL}?action=read&id=${taskId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const task = data.task;
                formData.append('title', task.title);
                formData.append('description', task.description || '');
                formData.append('priority', task.priority || 'Medium');
                formData.append('due_date', task.due_date || '');
                formData.append('quadrant', task.quadrant || 'others');

                return fetch(API_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: formData
                });
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update UI
                const taskElement = document.querySelector(`[data-id="${taskId}"]`);
                if (taskElement) {
                    if (isCompleted) {
                        taskElement.classList.add('completed');
                    } else {
                        taskElement.classList.remove('completed');
                    }
                }
            } else {
                alert('Error updating task: ' + (data.message || 'Unknown error'));
                // Revert checkbox
                const checkbox = document.querySelector(`[data-id="${taskId}"] .task-checkbox`);
                if (checkbox) checkbox.checked = !isCompleted;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to update task');
            // Revert checkbox
            const checkbox = document.querySelector(`[data-id="${taskId}"] .task-checkbox`);
            if (checkbox) checkbox.checked = !isCompleted;
        });
}

// Open add task modal
function openModal(quadrant) {
    currentQuadrant = quadrant;
    document.getElementById('modal').style.display = 'flex';
    document.getElementById('task-input').focus();
}

// Close modal
function closeModal() {
    document.getElementById('modal').style.display = 'none';
    document.getElementById('task-input').value = '';
    currentQuadrant = null;
}

// Add new task
function addTask() {
    const taskName = document.getElementById('task-input').value.trim();
    if (taskName === '') return;

    const formData = new URLSearchParams();
    formData.append('action', 'create');
    formData.append('title', taskName);
    formData.append('quadrant', currentQuadrant);

    console.log('Sending to:', API_URL);
    console.log('Data:', formData.toString());

    fetch(API_URL, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: formData
    })
        .then(response => {
            console.log('Status:', response.status);
            return response.text();
        })
        .then(text => {
            console.log('Response:', text);
            const data = JSON.parse(text);
            if (data.success) {
                const task = {
                    task_id: data.id,
                    title: taskName,
                    quadrant: currentQuadrant
                };
                renderTask(currentQuadrant, task);
                closeModal();
            } else {
                alert('Error: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to add task: ' + error.message);
        });
}

// Delete task
function deleteTask(taskId) {
    if (!confirm('Delete this task?')) return;

    const formData = new URLSearchParams();
    formData.append('action', 'delete');
    formData.append('id', taskId);

    fetch(API_URL, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const taskElement = document.querySelector(`[data-id="${taskId}"]`);
                if (taskElement) {
                    taskElement.remove();
                }
            } else {
                alert('Error deleting: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to delete task');
        });
}

// Helper function to escape HTML
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
