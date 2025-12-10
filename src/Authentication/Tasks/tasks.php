<?php
session_start();
if (!isset($_SESSION['user_id']) && !isset($_SESSION['user'])) {
    header('Location: ../login.php');
    exit;
}

$current_user = $_SESSION['user']['username'] ?? $_SESSION['name'] ?? 'User';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tasks</title>
  <link rel="stylesheet" href="tasks.css">

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />

  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

  <!-- Google Font -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar" id="app-nav">
    <div class="nav-header d-flex justify-content-between align-items-center">
        <h5 class="app-title">DebugMyDay</h5>
        <button id="menu-toggle" class="btn btn-sm text-white d-block d-md-none" type="button">
            <i class="bi bi-list fs-4"></i>
        </button>
    </div>

    <nav>
        <a class="menu-item" href="../Dashboard/dashboard.php"><i class="bi bi-speedometer2"></i> <span class="menu-text">Dashboard</span></a>
        <a class="menu-item active" href="tasks.php"><i class="bi bi-list-check"></i> <span class="menu-text">Tasks</span></a>
        <a class="menu-item" href="../profile.php"><i class="bi bi-person-circle"></i> <span class="menu-text">Profile</span></a>
        <a class="menu-item" href="../Pomodoro/pomodoro.php"><i class="bi bi-stopwatch"></i> <span class="menu-text">Pomodoro Timer</span></a>
        <a class="menu-item" href="../Setting/setting.php"><i class="bi bi-gear-fill"></i> <span class="menu-text">Settings</span></a>
        <a class="menu-item" href="../About/about.php"><i class="bi bi-info-circle"></i> <span class="menu-text">About Us</span></a>
    </nav>

    <a class="menu-item" href="../logout.php"><i class="bi bi-box-arrow-right"></i> <span class="menu-text">Logout</span></a>
</div>

<!-- MAIN CONTENT -->
<div class="app-content p-4 p-md-5">
    <header class="content-header d-flex align-items-center justify-content-between mb-4">
        <div>
            <h2 class="fw-semibold">Eisenhower Matrix</h2>
            <p class="subtitle text-muted">Organize tasks by urgency and importance</p>
        </div>
    </header>

    <div class="matrix">
        <!-- IMPORTANT & URGENT -->
        <div class="quad quad-red">
            <div class="quad-header">
                <h4>Important & Urgent</h4>
                <button class="add-btn" onclick="openModal('urgent-important')">+</button>
            </div>
            <div class="task-list" id="urgent-important"></div>
        </div>

        <!-- IMPORTANT BUT NOT URGENT -->
        <div class="quad quad-yellow">
            <div class="quad-header">
                <h4>Important but Not Urgent</h4>
                <button class="add-btn" onclick="openModal('important')">+</button>
            </div>
            <div class="task-list" id="important"></div>
        </div>

        <!-- NOT IMPORTANT BUT URGENT -->
        <div class="quad quad-blue">
            <div class="quad-header">
                <h4>Not Important but Urgent</h4>
                <button class="add-btn" onclick="openModal('urgent')">+</button>
            </div>
            <div class="task-list" id="urgent"></div>
        </div>

        <!-- NOT IMPORTANT & NOT URGENT -->
        <div class="quad quad-green">
            <div class="quad-header">
                <h4>Not Important & Not Urgent</h4>
                <button class="add-btn" onclick="openModal('others')">+</button>
            </div>
            <div class="task-list" id="others"></div>
        </div>
    </div>

    <footer class="footer p-4 p-md-3">
        <div class="footer-wrapper d-flex flex-column flex-md-row justify-content-between align-items-center">
            <p>&copy; 2025 DebugMyDay. All rights reserved.</p>
            <p class="footer-nav"><a href="#" class="text-decoration-none mx-2">Privacy Policy</a> | <a href="#" class="text-decoration-none mx-2">Terms of Service</a></p>
        </div>
    </footer>
</div>

<!-- ADD TASK MODAL -->
<div id="modal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modal-title">Add New Task</h2>
            <button class="close-btn" onclick="closeModal()">×</button>
        </div>
        <p class="modal-subtitle" id="modal-subtitle">Create a new task in the Eisenhower Matrix</p>

        <form onsubmit="saveTask(event)">
            <div class="form-group">
                <label for="task-title">Task Title <span class="required">*</span></label>
                <input type="text" id="task-title" placeholder="Enter task title" required>
            </div>
            <div class="form-group">
                <label for="task-description">Description</label>
                <textarea id="task-description" placeholder="Enter task description" rows="3"></textarea>
            </div>
            <div class="form-group">
                <label for="task-subject">Subject</label>
                <input type="text" id="task-subject" placeholder="e.g., Work, Personal, Health">
            </div>
            <div class="form-group">
                <label for="task-quadrant">Quadrant <span class="required">*</span></label>
                <div class="dropdown-wrapper">
                    <button type="button" class="dropdown-btn" id="quadrant-btn" onclick="toggleDropdown()">
                        <span id="selected-quadrant">Important & Urgent</span>
                        <i class="bi bi-chevron-down"></i>
                    </button>
                    <div class="dropdown-menu" id="dropdown-menu">
                        <div class="dropdown-item active" onclick="selectQuadrant('urgent-important', 'Important & Urgent', this)">
                            <i class="bi bi-check-lg"></i>
                            <span>Important & Urgent</span>
                        </div>
                        <div class="dropdown-item" onclick="selectQuadrant('important', 'Important but Not Urgent', this)">
                            <i class="bi bi-check-lg"></i>
                            <span>Important but Not Urgent</span>
                        </div>
                        <div class="dropdown-item" onclick="selectQuadrant('urgent', 'Not Important but Urgent', this)">
                            <i class="bi bi-check-lg"></i>
                            <span>Not Important but Urgent</span>
                        </div>
                        <div class="dropdown-item" onclick="selectQuadrant('others', 'Not Important & Not Urgent', this)">
                            <i class="bi bi-check-lg"></i>
                            <span>Not Important & Not Urgent</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-actions">
                <button type="button" onclick="closeModal()" class="cancel-btn">Cancel</button>
                <button type="submit" class="save-btn" id="submit-btn">Add Task</button>
            </div>
        </form>
    </div>
</div>

<!-- SUCCESS NOTIFICATION -->
<div id="notification" class="notification">
    <i class="bi bi-check-circle-fill"></i>
    <span id="notification-text">Task added successfully</span>
</div>

<script src="tasks.js"></script>
<script src="../Shared/audioPlayer.js"></script>
</body>
</html>
