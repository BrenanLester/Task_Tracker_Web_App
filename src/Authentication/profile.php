<?php
require __DIR__ . '/../Database/db.php';
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION["user_id"];
$message = "";
$error = "";

// Get current user data
$stmt = $pdo->prepare("SELECT name, email FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'update') {
          // Update profile (name/email) and optionally change password
          $name = trim($_POST["name"]);
          $email = trim($_POST["email"]);
          $current_password = trim($_POST['current_password'] ?? '');
          $new_password = trim($_POST['new_password'] ?? '');
          $verify_password = trim($_POST['verify_password'] ?? '');

          if (!$name || !$email) {
            $error = "Name and email are required.";
          } else {
            // Check if email is already taken by another user
            $check = $pdo->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ?");
            $check->execute([$email, $user_id]);
            if ($check->fetch()) {
              $error = "This email is already taken by another user.";
            } else {
              // If any password field filled, perform password change validation
              $changingPassword = ($current_password !== '' || $new_password !== '' || $verify_password !== '');

              if ($changingPassword) {
                // All three must be provided
                if ($current_password === '' || $new_password === '' || $verify_password === '') {
                  $error = "To change your password, fill current, new, and verify fields.";
                } else {
                  // Fetch current hashed password
                  $stmt = $pdo->prepare("SELECT password FROM users WHERE user_id = ?");
                  $stmt->execute([$user_id]);
                  $row = $stmt->fetch(PDO::FETCH_ASSOC);
                  if (!$row || !password_verify($current_password, $row['password'])) {
                    $error = "Current password is incorrect.";
                  } elseif ($new_password !== $verify_password) {
                    $error = "New password and verification do not match.";
                  } elseif (strlen($new_password) < 8) {
                    $error = "New password must be at least 8 characters.";
                  } else {
                    // All good — update name, email and password
                    $hash = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, password = ? WHERE user_id = ?");
                    $stmt->execute([$name, $email, $hash, $user_id]);

                    // Update session and user variable
                    $_SESSION["name"] = $name;
                    $message = "Profile and password updated successfully!";
                    $user = ["name" => $name, "email" => $email];
                  }
                }
              } else {
                // No password change requested — update name/email only
                $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ? WHERE user_id = ?");
                $stmt->execute([$name, $email, $user_id]);
                $_SESSION["name"] = $name;
                $message = "Profile updated successfully!";
                $user = ["name" => $name, "email" => $email];
              }
            }
          }
        } elseif ($_POST['action'] === 'delete') {
            // Delete account
            $password = trim($_POST["password"] ?? "");
            
            // Verify password before deletion
            $stmt = $pdo->prepare("SELECT password FROM users WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!password_verify($password, $user_data['password'])) {
                $error = "Incorrect password. Account not deleted.";
            } else {
                // Delete user (cascades to all related data)
                $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
                $stmt->execute([$user_id]);
                
                session_destroy();
                header("Location: ../../index.html");
                exit;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>DebugMyDay - My Profile</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            background-color: #f6f2fc;
            font-family: 'Poppins', sans-serif;
        }

        /* Sidebar - Matching Dashboard */
        /* === Base Styles === */
body {
  background-color: #f6f2fc;
  font-family: 'Poppins', sans-serif;
  margin: 0;
}

/* === Sidebar === */
.sidebar {
  width: 260px;
  background-color: #3b1366;
  min-height: 100vh;
  color: #fff;
  position: fixed;
  top: 0;
  left: 0;
  overflow: hidden;
  padding-top: 20px;
  display: flex;
  flex-direction: column;
  transition: width 0.3s ease, transform 0.3s ease;
}

.sidebar h5 {
  font-weight: 700;
  font-size: 20px;
  margin-bottom: 25px;
  padding-left: 20px;
}

/* Sidebar Links */
.sidebar a {
  color: #fff;
  text-decoration: none;
}

.menu-item {
  padding: 12px 20px;
  border-radius: 8px;
  margin-bottom: 4px;
  display: flex;
  align-items: center;
  gap: 12px;
  white-space: nowrap;
  transition: 0.3s;
  font-weight: 500;
  font-size: 15px;
}

.menu-item:hover {
  background-color: rgba(109, 40, 217, 0.5);
}

.menu-item i {
  font-size: 20px;
}

.menu-item.active {
  background-color: #6d28d9 !important;
  font-weight: 600;
}

/* Main Content */
.content {
  margin-left: 260px;
  padding: 30px;
  transition: margin-left 0.3s ease;
}

/* Profile Container */
.profile-container {
  max-width: 800px;
  margin: 0 auto;
  background: white;
  padding: 40px;
  border-radius: 16px;
  box-shadow: 0 0 20px rgba(0,0,0,0.08);
}

        /* Tabs removed: show both sections on a single page */
        .tabs {
            display: none; /* tabs UI hidden since both sections are merged */
        }

        .tab-content {
            display: block; /* show all tab sections on the single merged page */
        }
/* Profile Header */
.profile-header {
  margin-bottom: 30px;
  padding-bottom: 20px;
  border-bottom: 2px solid #e9ddff;
}

.profile-header h1 {
  color: #3b1366;
  font-weight: 600;
  margin-bottom: 8px;
  display: flex;
  align-items: center;
  gap: 10px;
}

/* Tabs */
.tabs {
  display: flex;
  gap: 10px;
  margin-bottom: 30px;
  border-bottom: 2px solid #e9ddff;
  flex-wrap: wrap;
}

.tab-btn {
  padding: 12px 24px;
  background: none;
  border: none;
  border-bottom: 3px solid transparent;
  cursor: pointer;
  font-size: 1em;
  font-weight: 600;
  color: #666;
  transition: all 0.3s;
}

.tab-btn.active {
  color: #6d28d9;
  border-bottom-color: #6d28d9;
}

.tab-btn:hover {
  color: #5a1fb8;
}

.tab-content {
  display: none;
}

.tab-content.active {
  display: block;
}

/* Forms */
.form-label {
  font-weight: 600;
  color: #3b1366;
  margin-bottom: 8px;
  font-size: 0.95rem;
}

        .button-group {
            display: flex;
            gap: 15px;
            margin: 25px 0px;
        }

.form-control {
  border: 2px solid #e9ddff;
  border-radius: 8px;
  padding: 12px 16px;
  font-size: 1rem;
  transition: all 0.3s;
  background-color: #faf8ff;
  width: 100%;
  box-sizing: border-box;
}

.form-control:focus {
  border-color: #6d28d9;
  box-shadow: 0 0 0 0.25rem rgba(109, 40, 217, 0.1);
  background-color: #fff;
  outline: none;
}

/* Buttons */
.button-group {
  display: flex;
  gap: 15px;
  margin-top: 25px;
  flex-wrap: wrap;
}

button {
  padding: 12px 25px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  font-size: 1rem;
  transition: all 0.3s;
}

.btn-primary {
  background: linear-gradient(135deg, #6d28d9 0%, #8b5cf6 100%);
  color: white;
}

.btn-primary:hover {
  background: linear-gradient(135deg, #5a1fb8 0%, #7c3aed 100%);
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(109, 40, 217, 0.3);
}

.btn-danger {
  background: #ef4444;
  color: white;
}

.btn-danger:hover {
  background: #dc2626;
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(239, 68, 68, 0.3);
}

.btn-secondary {
  background: #e2e8f0;
  color: #64748b;
}

.btn-secondary:hover {
  background: #cbd5e1;
  transform: translateY(-2px);
}

/* Messages */
.message {
  padding: 15px 20px;
  border-radius: 8px;
  margin-bottom: 20px;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 10px;
}

.message.success {
  background: #d1fae5;
  color: #065f46;
  border: 2px solid #10b981;
}

.message.error {
  background: #fee2e2;
  color: #991b1b;
  border: 2px solid #ef4444;
}

/* Warning & Delete */
.warning-box {
  background: #fef3c7;
  border: 2px solid #f59e0b;
  border-radius: 8px;
  padding: 20px;
  margin-bottom: 20px;
  color: #92400e;
}

.warning-box strong {
  display: block;
  margin-bottom: 10px;
  font-size: 1.1em;
}

.warning-box ul {
  margin: 10px 0 0 20px;
}

.delete-confirm {
  display: none;
  background: #fee2e2;
  border: 2px solid #ef4444;
  padding: 20px;
  border-radius: 8px;
  margin-top: 20px;
}

.delete-confirm.show {
  display: block;
}

.user-info {
  background: #faf8ff;
  padding: 20px;
  border-radius: 8px;
  margin-bottom: 20px;
  border: 2px solid #e9ddff;
}

.user-info p {
  margin: 10px 0;
  font-size: 1rem;
}

.user-info strong {
  color: #3b1366;
  display: inline-block;
  width: 100px;
}

/* === Responsive === */
@media (max-width: 1100px) {
  .content {
    padding: 25px;
  }
}

@media (max-width: 768px) {
  /* Sidebar compact */
  .sidebar {
    width: 70px;
    padding: 16px 0;
  }

  .sidebar h5 {
    display: none;
  }

  .menu-item {
    justify-content: center;
    padding: 12px 0;
  }

  .menu-item span.text {
    display: none;
  }

  /* Content margin adjusts */
  .content {
    margin-left: 70px;
    padding: 20px;
  }

  /* Profile container adapts */
  .profile-container {
    padding: 25px;
    margin: 0 10px;
  }

  /* Tabs wrap */
  .tabs {
    flex-wrap: wrap;
  }

  .button-group {
    flex-direction: column;
    gap: 10px;
  }
}

        
    </style>
</head>
<body>

  <!-- Sidebar -->
  <div class="sidebar p-3">
    <h5 class="fw-bold mb-4">DebugMyDay</h5>
    <a class="menu-item" href="Dashboard/dashboard.php"><i class="bi bi-speedometer2"></i> <span class="text">Dashboard</span></a>
    <a class="menu-item" href="Tasks/tasks.php"><i class="bi bi-list-check"></i> <span class="text">Tasks</span></a>
    <a class="menu-item active" href="profile.php"><i class="bi bi-person-circle"></i> <span class="text">Profile</span></a>
    <a class="menu-item" href="Pomodoro/pomodoro.php"><i class="bi bi-stopwatch"></i> <span class="text">Pomodoro Timer</span></a>
    <a class="menu-item" href="Setting/setting.php"><i class="bi bi-gear-fill"></i> <span class="text">Settings</span></a>
    <a class="menu-item" href="About/about.php"><i class="bi bi-info-circle"></i> <span class="text">About Us</span></a>

    <div class="mt-auto pt-3">
      <a class="menu-item" href="logout.php"><i class="bi bi-box-arrow-right"></i> <span class="text">Logout</span></a>
    </div>
  </div>

  <!-- Main Content -->
  <div class="content">
    <div class="profile-container">
        <div class="profile-header">
            <h1><i class="bi bi-person-circle"></i> My Profile</h1>
            <p class="text-muted">Manage your account settings and preferences</p>
        </div>

        <?php if ($message): ?>
            <div class="message success">
                <i class="bi bi-check-circle-fill"></i>
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="message error">
                <i class="bi bi-exclamation-circle-fill"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <!-- Single merged page: profile edit form followed by delete-account section -->

        <!-- Edit Profile Tab -->
        <div id="edit" class="tab-content active">
            <form method="POST">
                <input type="hidden" name="action" value="update">

                <div class="mb-4">
                    <label for="name" class="form-label">Full Name</label>
                    <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                </div>

                <div class="mb-4">
                  <label for="email" class="form-label">Email Address</label>
                  <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>

                <hr />
                <h5 style="color:#3b1366; margin-bottom:12px;">Update Password</h5>
                <p class="text-muted" style="margin-top:-8px; margin-bottom:12px; font-size:0.95rem;">Leave blank to keep your current password.</p>

                <div class="mb-3">
                  <label for="current_password" class="form-label">Current Password</label>
                  <input type="password" class="form-control" id="current_password" name="current_password" placeholder="Enter current password">
                </div>

                <div class="mb-3">
                  <label for="new_password" class="form-label">New Password</label>
                  <input type="password" class="form-control" id="new_password" name="new_password" placeholder="New password (min 8 characters)">
                </div>

                <div class="mb-3">
                  <label for="verify_password" class="form-label">Verify Password</label>
                  <input type="password" class="form-control" id="verify_password" name="verify_password" placeholder="Re-type new password">
                </div>

                <div class="button-group">
                  <button type="submit" class="btn-primary">
                    <i class="bi bi-save"></i> Save Changes
                  </button>
                </div>
            </form>
        </div>

        <!-- Delete Account Tab -->
        <div id="delete" class="tab-content">
            <div class="warning-box">
                <strong>⚠️ Warning: This action is permanent!</strong>
                Deleting your account will:
                <ul>
                    <li>Permanently delete your account</li>
                    <li>Delete all your tasks and reminders</li>
                    <li>Delete all your categories</li>
                    <li>This cannot be undone</li>
                </ul>
            </div>

            <form method="POST" id="deleteForm">
                <input type="hidden" name="action" value="delete">

                <div class="user-info">
                    <p><strong>Account:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                    <p><strong>User:</strong> <?php echo htmlspecialchars($user['name']); ?></p>
                </div>

                <p style="margin-bottom: 15px; color: #666; font-weight: 500;">
                    To confirm deletion, please enter your password:
                </p>

                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                </div>

                <div class="button-group">
                    <button type="button" class="btn-secondary" onclick="toggleDeleteConfirm()">
                        <i class="bi bi-trash"></i> Delete My Account
                    </button>
                </div>

                <div id="deleteConfirm" class="delete-confirm">
                    <p><strong><i class="bi bi-exclamation-triangle-fill"></i> Are you absolutely sure?</strong></p>
                    <p>This will permanently delete your account and all associated data.</p>
                    <div class="button-group" style="margin-top: 15px;">
                        <button type="submit" class="btn-danger">
                            <i class="bi bi-trash-fill"></i> Yes, Delete Permanently
                        </button>
                        <button type="button" class="btn-secondary" onclick="toggleDeleteConfirm()">Cancel</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
  </div>

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    function showTab(tabName) {
        // Hide all tabs
        document.querySelectorAll('.tab-content').forEach(tab => {
            tab.classList.remove('active');
        });
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('active');
        });

        // Show selected tab
        document.getElementById(tabName).classList.add('active');
        event.target.classList.add('active');
    }

    function toggleDeleteConfirm() {
        const confirmBox = document.getElementById('deleteConfirm');
        confirmBox.classList.toggle('show');
    }
  </script>
  <script src="Shared/audioPlayer.js"></script>
</body>
</html>
