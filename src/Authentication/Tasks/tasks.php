<?php
/**
 * Tasks Frontend - Eisenhower Matrix UI
 * Pure presentation layer - no database logic
 * Connects to CRUD API via AJAX
 */

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
  <title>Tasks – Eisenhower Matrix</title>
  <link rel="stylesheet" href="tasks.css">

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />

  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

  <!-- Google Font -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

<div class="layout">

  <!-- SIDEBAR -->
<div class="sidebar">
    <h5>DebugMyDay</h5>

    <a class="menu-item" href="../Dashboard/dashboard.php">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>

    <a class="menu-item active" href="tasks.php">
        <i class="bi bi-list-check"></i> Tasks
    </a>

    <a class="menu-item" href="../profile.php"><i class="bi bi-person-circle"></i> Profile</a>
    <a class="menu-item" href="#"><i class="bi bi-stopwatch"></i> Pomodoro Timer</a>
    <a class="menu-item" href="#"><i class="bi bi-gear-fill"></i> Settings</a>
    <a class="menu-item" href="#"><i class="bi bi-info-circle"></i> About Us</a>

    <a class="menu-item" href="../logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
</div>


  <!-- MAIN CONTENT -->
  <main class="main">
    <h1 class="fw-semibold">Eisenhower Matrix</h1>
    <p class="subtitle text-muted">Organize tasks by urgency and importance</p>

    <div class="matrix">

      <!-- IMPORTANT & URGENT -->
      <div class="quad quad-red">
        <div class="quad-header">
          <h3>Important & Urgent</h3>
          <button class="add-btn" onclick="openModal('urgent-important')">+</button>
        </div>
        <div class="task-list" id="urgent-important">
          <!-- Tasks loaded via AJAX -->
        </div>
      </div>

      <!-- IMPORTANT BUT NOT URGENT -->
      <div class="quad quad-yellow">
        <div class="quad-header">
          <h3>Important but Not Urgent</h3>
          <button class="add-btn" onclick="openModal('important')">+</button>
        </div>
        <div class="task-list" id="important">
          <!-- Tasks loaded via AJAX -->
        </div>
      </div>

      <!-- NOT IMPORTANT BUT URGENT -->
      <div class="quad quad-blue">
        <div class="quad-header">
          <h3>Not Important but Urgent</h3>
          <button class="add-btn" onclick="openModal('urgent')">+</button>
        </div>
        <div class="task-list" id="urgent">
          <!-- Tasks loaded via AJAX -->
        </div>
      </div>

      <!-- NOT IMPORTANT & NOT URGENT -->
      <div class="quad quad-green">
        <div class="quad-header">
          <h3>Not Important & Not Urgent</h3>
          <button class="add-btn" onclick="openModal('others')">+</button>
        </div>
        <div class="task-list" id="others">
          <!-- Tasks loaded via AJAX -->
        </div>
      </div>

    </div>
  </main>
</div>

<!-- ADD TASK MODAL -->
<div id="modal" class="modal">
  <div class="modal-content">
    <h2>Add Task</h2>
    <input type="text" id="task-input" placeholder="Task name...">
    <div class="modal-actions">
      <button onclick="closeModal()" class="cancel-btn">Cancel</button>
      <button onclick="addTask()" class="save-btn">Add</button>
    </div>
  </div>
</div>

<script src="tasks.js"></script>
</body>
</html>
