<?php
session_start();
if (!isset($_SESSION['user_id']) && !isset($_SESSION['user'])) {
    header('Location: ../login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>About Us - DebugMyDay</title>
  <link rel="stylesheet" href="about.css">

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />

  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

  <!-- Google Font -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    
  <!-- SIDEBAR -->
<div class="sidebar">
    <h5 class="fw-bold mb-4">DebugMyDay</h5>

    <a class="menu-item" href="../Dashboard/dashboard.php">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>

    <a class="menu-item" href="../Tasks/tasks.php">
        <i class="bi bi-list-check"></i> Tasks
    </a>

    <a class="menu-item" href="../profile.php"><i class="bi bi-person-circle"></i> Profile</a>
    <a class="menu-item" href="../Pomodoro/pomodoro.php"><i class="bi bi-stopwatch"></i> Pomodoro Timer</a>
    <a class="menu-item" href="../Setting/setting.php"><i class="bi bi-gear-fill"></i> Settings</a>
    <a class="menu-item active" href="about.php"><i class="bi bi-info-circle"></i> About Us</a>

    <a class="menu-item" href="../logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
</div>

    <!-- Main Content -->
    <div class="content">
        <h1 class="page-title">About DebugMyDay</h1>
        <p class="subtitle">Your productivity companion</p>

        <div class="about-card">
            <h2>Welcome to DebugMyDay</h2>
            <p>DebugMyDay is a comprehensive task management and productivity application designed to help you organize
                your work, stay focused, and achieve your goals.</p>

            <h3><i class="bi bi-star-fill"></i> Features</h3>
            <ul>
                <li><strong>Task Management:</strong> Organize tasks using the Eisenhower Matrix</li>
                <li><strong>Pomodoro Timer:</strong> Stay focused with timed work sessions</li>
                <li><strong>Dashboard:</strong> Track your productivity at a glance</li>
                <li><strong>Calendar Integration:</strong> Never miss a deadline</li>
            </ul>

            <h3><i class="bi bi-bullseye"></i> Our Mission</h3>
            <p>We believe that productivity should be simple, intuitive, and accessible to everyone. DebugMyDay helps
                you debug your day by organizing tasks, managing time, and maintaining focus.</p>

            <h3><i class="bi bi-info-circle-fill"></i> Version</h3>
            <p>Version 1.0.0 - Built with ❤️ for productivity enthusiasts</p>
        </div>
    </div>
</div>
<script src="../Shared/audioPlayer.js"></script>
</body>
</html>
