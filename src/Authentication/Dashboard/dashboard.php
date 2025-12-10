<?php
session_start();
if (!isset($_SESSION['user_id']) && !isset($_SESSION['user'])) {
    header('Location: ../login.php');
    exit;
}

$user_name = $_SESSION['name'] ?? 'User';
$user_id = $_SESSION['user_id'] ?? ($_SESSION['user']['user_id'] ?? null);

require_once __DIR__ . '/../../Database/db.php';

// Get task statistics
$stmt = $pdo->prepare('SELECT 
    COUNT(*) as total_tasks,
    SUM(CASE WHEN status = "Pending" THEN 1 ELSE 0 END) as pending_tasks,
    SUM(CASE WHEN status = "In progress" THEN 1 ELSE 0 END) as in_progress_tasks,
    SUM(CASE WHEN status = "Completed" THEN 1 ELSE 0 END) as completed_tasks,
    SUM(CASE WHEN priority = "High" THEN 1 ELSE 0 END) as high_priority_tasks
    FROM tasks WHERE user_id = ?');
$stmt->execute([$user_id]);
$stats = $stmt->fetch(PDO::FETCH_ASSOC);

// Get recent tasks
$stmt = $pdo->prepare('SELECT task_id, title, priority, status, due_date, created_at 
    FROM tasks WHERE user_id = ? ORDER BY created_at DESC LIMIT 5');
$stmt->execute([$user_id]);
$recent_tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get all tasks for calendar
$stmt = $pdo->prepare('SELECT task_id, title, due_date, status, priority 
    FROM tasks WHERE user_id = ? AND due_date IS NOT NULL');
$stmt->execute([$user_id]);
$calendar_tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard</title>
  
  <link rel="stylesheet" href="../../reusable.css">
  <link rel="stylesheet" href="dashboard.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>

  <div class="sidebar" id="app-nav">
    <div class="nav-header d-flex justify-content-between align-items-center">
        <h5 class="app-title">DebugMyDay</h5>
        <button id="menu-toggle" class="btn btn-sm text-white d-block d-md-none" type="button">
            <i class="bi bi-list fs-4"></i>
        </button>
    </div>

    <nav>
      <a class="menu-item active" href="dashboard.php">
          <i class="bi bi-speedometer2"></i> <span class="menu-text">Dashboard</span>
      </a>

      <a class="menu-item" href="../Tasks/tasks.php">
          <i class="bi bi-list-check"></i> <span class="menu-text">Tasks</span>
      </a>

      <a class="menu-item" href="../profile.php"><i class="bi bi-person-circle"></i> <span class="menu-text">Profile</span></a>
      <a class="menu-item" href="../Pomodoro/pomodoro.php"><i class="bi bi-stopwatch"></i> <span class="menu-text">Pomodoro Timer</span></a>
      <a class="menu-item" href="../Setting/setting.php"><i class="bi bi-gear-fill"></i> <span class="menu-text">Settings</span></a>
      <a class="menu-item" href="../About/about.php"><i class="bi bi-info-circle"></i> <span class="menu-text">About Us</span></a>
    </nav>
    
    <a class="menu-item" href="#"><i class="bi bi-box-arrow-right"></i> <span class="menu-text">Logout</span></a>
</div>

  <div class="app-content p-4 p-md-5">
    <h2 class="fw-semibold">Hello, [Username Here]! 👋</h2>
    <p class="text-muted">Here's your productivity overview</p>

    <div class="row mt-3 g-3">
      <div class="col-md-4">
        <div class="card-metric">
          <h6>Total Tasks</h6>
          <h2 class="fw-bold">—</h2>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card-metric">
          <h6>Pending Tasks</h6>
          <h2 class="fw-bold text-warning">—</h2>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card-metric">
          <h6>Completed Tasks</h6>
          <h2 class="fw-bold text-success">—</h2>
        </div>
      </div>
    </div>

    <div class="row mt-4 g-4">
      
      <div class="col-lg-6">
        <div class="activity-box card-box">
          <h5 class="mb-3">Recently added tasks</h5>
          <ul class="list-group">
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <span class="text-success"><i class="bi bi-check-circle-fill me-2"></i> Task "Design Mockup" accomplished.</span>
                <span class="badge bg-success rounded-pill">5m ago</span>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <span class="text-primary"><i class="bi bi-plus-circle-fill me-2"></i> New task "Test Payment Gateway" created.</span>
                <span class="badge bg-primary rounded-pill">2h ago</span>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <span class="text-warning"><i class="bi bi-arrow-clockwise me-2"></i> Task "Database Setup" moved to Pending.</span>
                <span class="badge bg-warning rounded-pill">Today</span>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <span class="text-secondary"><i class="bi bi-stopwatch-fill me-2"></i> Pomodoro session finished (25m).</span>
                <span class="badge bg-secondary rounded-pill">3h ago</span>
            </li>
          </ul>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="card-box minimalist-calendar-container">
          
          <div class="calendar-header d-flex justify-content-between align-items-center mb-4 p-2 rounded-2">
            <div class="date-display fw-bold" id="current-month">Month</div>
            <div class="navigation d-flex align-items-center">
              <i class="bi bi-chevron-left me-2"></i>
              <span class="fw-bold me-2" id="current-year">Year</span>
              <i class="bi bi-chevron-right"></i>
            </div>
          </div>
          
          <div class="calendar-grid" id="calendar-grid">
            <div class="day-label">Sun</div>
            <div class="day-label">Mon</div>
            <div class="day-label">Tue</div>
            <div class="day-label">Wed</div>
            <div class="day-label">Thu</div>
            <div class="day-label">Fri</div>
            <div class="day-label">Sat</div>
            
            </div>
          
          <hr class="calendar-divider my-4">
          
          <div class="time-footer d-flex align-items-center">
            <div class="time-label fw-bold me-3">TODAY</div>
            <div class="time-separator me-3"></div>
            <div class="time-details">
              <div class="current-time fw-bold" id="current-time">Time</div>
              <small class="text-muted" id="current-full-date">Full Date</small>
            </div>
          </div>
          
        </div>
      </div>
      </div>
  </div>
  
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  
  <script src="../About/about.js"></script> 
  <script src="dashboard.js"></script> 

</body>
</html>