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
    <title>Settings - DebugMyDay</title>

    <!-- Page CSS -->
    <link rel="stylesheet" href="setting.css">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
          rel="stylesheet">
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar" id="app-nav">
    <div class="nav-header d-flex justify-content-between align-items-center">
        <h5 class="app-title">DebugMyDay</h5>

        <!-- Mobile Hamburger -->
        <button id="menu-toggle" class="btn btn-sm text-white d-block d-md-none" type="button">
            <i class="bi bi-list fs-4"></i>
        </button>
    </div>

    <nav>
        <a class="menu-item" href="../Dashboard/dashboard.php">
            <i class="bi bi-speedometer2"></i> <span class="menu-text">Dashboard</span>
        </a>
        <a class="menu-item" href="../Tasks/tasks.php">
            <i class="bi bi-list-check"></i> <span class="menu-text">Tasks</span>
        </a>
        <a class="menu-item" href="../profile.php">
            <i class="bi bi-person-circle"></i> <span class="menu-text">Profile</span>
        </a>
        <a class="menu-item" href="../Pomodoro/pomodoro.php">
            <i class="bi bi-stopwatch"></i> <span class="menu-text">Pomodoro Timer</span>
        </a>
        <a class="menu-item active" href="setting.php">
            <i class="bi bi-gear-fill"></i> <span class="menu-text">Settings</span>
        </a>
        <a class="menu-item" href="../About/about.php">
            <i class="bi bi-info-circle"></i> <span class="menu-text">About Us</span>
        </a>
    </nav>

    <a class="menu-item" href="../logout.php">
        <i class="bi bi-box-arrow-right"></i> <span class="menu-text">Logout</span>
    </a>
</div>

<!-- MAIN CONTENT -->
<div class="app-content p-4 p-md-5">
    <header class="content-header d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="page-title fs-4 fw-bold">Settings</h1>
            <p class="page-sub text-muted fs-6">Customize your experience</p>
        </div>
    </header>

    <div class="settings-card">
        <h2><i class="bi bi-palette-fill"></i> Appearance</h2>
        <div class="setting-item">
            <label for="theme">Theme</label>
            <select id="theme" class="form-select">
                <option value="light">Light Mode</option>
                <option value="dark">Dark Mode</option>
                <option value="auto">Auto (System)</option>
            </select>
        </div>

        <h2><i class="bi bi-bell-fill"></i> Notifications</h2>
        <div class="setting-item">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="taskReminders" checked>
                <label class="form-check-label" for="taskReminders">Task Reminders</label>
            </div>
        </div>

        <div class="setting-item">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="pomodoroAlerts" checked>
                <label class="form-check-label" for="pomodoroAlerts">Pomodoro Alerts</label>
            </div>
        </div>

        <h2><i class="bi bi-clock-fill"></i> Pomodoro Settings</h2>
        <div class="setting-item">
            <label for="workDuration">Work Duration (minutes)</label>
            <input type="number" id="workDuration" class="form-control" value="25" min="1" max="60">
        </div>

        <div class="setting-item">
            <label for="breakDuration">Short Break (minutes)</label>
            <input type="number" id="breakDuration" class="form-control" value="5" min="1" max="30">
        </div>

        <div class="setting-item">
            <label for="longBreakDuration">Long Break (minutes)</label>
            <input type="number" id="longBreakDuration" class="form-control" value="15" min="1" max="60">
        </div>

        <div class="save-section">
            <button class="btn-save"><i class="bi bi-check-circle-fill"></i> Save Settings</button>
        </div>
    </div>

    <footer class="footer p-4 p-md-3">
        <div class="footer-wrapper d-flex flex-column flex-md-row justify-content-between align-items-center">
            <p>&copy; 2025 DebugMyDay. All rights reserved.</p>
            <p class="footer-nav">
                <a href="#" class="text-decoration-none mx-2">Privacy Policy</a> |
                <a href="#" class="text-decoration-none mx-2">Terms of Service</a>
            </p>
        </div>
    </footer>
</div>

<!-- SIDEBAR JS -->
<script src="../Dashboard/about.js"></script>

<!-- SETTINGS PAGE JS -->
<script src="setting.js"></script>

<!-- Shared Audio -->
<script src="../Shared/audioPlayer.js"></script>

</body>
</html>
