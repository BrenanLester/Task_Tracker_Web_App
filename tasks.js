let currentQuadrant = null;

function openModal(quad) {
    currentQuadrant = quad;
    document.getElementById("modal").style.display = "flex";
}

function closeModal() {
    document.getElementById("modal").style.display = "none";
    document.getElementById("task-input").value = "";
}

function updateNoTaskMessage() {
    document.querySelectorAll('.quad').forEach(q => {
        const taskList = q.querySelector('.task-list');
        const msg = q.querySelector('.no-task-message');

        if (taskList.children.length === 0) {
            msg.classList.remove('hidden');  // show
        } else {
            msg.classList.add('hidden');     // hide
        }
    });
}

function addTask() {
    let taskName = document.getElementById("task-input").value.trim();
    if (taskName === "") return;

    let list = document.getElementById(currentQuadrant);

    let task = document.createElement("div");
    task.className = "task";
    task.innerText = taskName;

    list.appendChild(task);

    closeModal();

    updateNoTaskMessage();
}

document.addEventListener("DOMContentLoaded", updateNoTaskMessage);



const sidebar = document.getElementById('sidebar');
const menuToggle = document.getElementById('menu-toggle');
const content = document.getElementById('content');

if (menuToggle) {
    menuToggle.addEventListener('click', () => {
        sidebar.classList.toggle('sidebar-open'); 
        
        content.classList.toggle('content-pushed');
     
    });
}
