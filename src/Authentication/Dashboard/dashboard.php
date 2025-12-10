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
  
  <link rel="stylesheet" href="../../../reusable.css">
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
    
    <a class="menu-item" href="../logout.php"><i class="bi bi-box-arrow-right"></i> <span class="menu-text">Logout</span></a>
</div>

  <!-- Main Content -->
  <div class="app-content">
    <h2 class="fw-semibold">Hello, <?php echo htmlspecialchars($user_name); ?>! 👋</h2>
    <p class="text-muted">Here's your productivity overview</p>

    <div class="row mt-3 g-3">
      <div class="col-md-4">
        <div class="card-metric">
          <h6>Total Tasks</h6>
          <h2 class="fw-bold"><?php echo $stats['total_tasks'] ?? 0; ?></h2>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card-metric">
          <h6>Pending Tasks</h6>
          <h2 class="fw-bold text-warning"><?php echo $stats['pending_tasks'] ?? 0; ?></h2>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card-metric">
          <h6>Completed Tasks</h6>
          <h2 class="fw-bold text-success"><?php echo $stats['completed_tasks'] ?? 0; ?></h2>
        </div>
      </div>
    </div>

    <div class="row mt-4 g-4">
      <div class="col-lg-6">
        <div class="card-box">
          <h5 class="mb-3">Recent Task To Do</h5>
          <?php if (empty($recent_tasks)): ?>
            <p class="text-muted text-center py-4">No recent activity. <a href="../Tasks/tasks.php">Create your first task!</a></p>
          <?php else: ?>
            <?php foreach ($recent_tasks as $task): ?>
              <div class="activity-item">
                <div class="task-title"><?php echo htmlspecialchars($task['title']); ?></div>
                <div class="task-meta">
                  <span class="priority-badge priority-<?php echo strtolower($task['priority']); ?>">
                    <?php echo htmlspecialchars($task['priority']); ?>
                  </span>
                  <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $task['status'])); ?>">
                    <?php echo htmlspecialchars($task['status']); ?>
                  </span>
                  <?php if ($task['due_date']): ?>
                    <span class="ms-2"><i class="bi bi-calendar"></i> <?php echo htmlspecialchars($task['due_date']); ?></span>
                  <?php endif; ?>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
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
  
  <script>
    // Auto-refresh recent tasks every 5 seconds
    setInterval(function() {
      fetch('/Task_Tracker_Web_App/src/CRUD/Task.php?action=list')
        .then(response => response.json())
        .then(data => {
          if (data.success && data.tasks) {
            const recentTasksContainer = document.querySelector('.card-box');
            if (recentTasksContainer && !recentTasksContainer.classList.contains('minimalist-calendar-container')) {
              // Get only the 5 most recent tasks
              const recentTasks = data.tasks.slice(0, 5);
              
              // Build HTML
              let html = '<h5 class="mb-3">Recent Task To Do</h5>';
              
              if (recentTasks.length === 0) {
                html += '<p class="text-muted text-center py-4">No recent activity. <a href="../Tasks/tasks.php">Create your first task!</a></p>';
              } else {
                recentTasks.forEach(task => {
                  const priorityClass = task.priority ? task.priority.toLowerCase() : 'medium';
                  const statusClass = task.status ? task.status.toLowerCase().replace(' ', '-') : 'pending';
                  
                  html += '<div class="activity-item">';
                  html += '<div class="task-title">' + (task.title || '') + '</div>';
                  html += '<div class="task-meta">';
                  html += '<span class="priority-badge priority-' + priorityClass + '">' + (task.priority || 'Medium') + '</span>';
                  html += '<span class="status-badge status-' + statusClass + '">' + (task.status || 'Pending') + '</span>';
                  if (task.due_date) {
                    html += '<span class="ms-2"><i class="bi bi-calendar"></i> ' + task.due_date + '</span>';
                  }
                  html += '</div>';
                  html += '</div>';
                });
              }
              
              recentTasksContainer.innerHTML = html;
            }
          }
        })
        .catch(error => console.error('Error refreshing tasks:', error));
    }, 5000); // Refresh every 5 seconds
  </script>

</body>
</html>