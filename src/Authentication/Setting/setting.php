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
  <link rel="stylesheet" href="setting.css">
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
    <a class="menu-item active" href="setting.php"><i class="bi bi-gear-fill"></i> Settings</a>
    <a class="menu-item" href="../About/about.php"><i class="bi bi-info-circle"></i> About Us</a>

    <a class="menu-item" href="../logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
</div>

    <!-- Main Content -->
    <div class="content">
        <h1 class="page-title">Settings</h1>
        <p class="subtitle">Customize your experience</p>

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
                    <label class="form-check-label" for="taskReminders">
                        Task Reminders
                    </label>
                </div>
            </div>
            <div class="setting-item">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="pomodoroAlerts" checked>
                    <label class="form-check-label" for="pomodoroAlerts">
                        Pomodoro Alerts
                    </label>
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
    </div>

</div>
<script src="../Shared/audioPlayer.js"></script>

</body>
</html>
