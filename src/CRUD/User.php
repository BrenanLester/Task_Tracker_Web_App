<?php
session_start();
if (!isset($_SESSION['user_id']) && !isset($_SESSION['user'])) {
    header('Location: ../Authentication/login.php');
    exit;
}
$user_id = $_SESSION['user_id'] ?? ($_SESSION['user']['user_id'] ?? null);

require_once __DIR__ . '/../Database/db.php'; // expects $pdo

$action = $_GET['action'] ?? 'list';
$msg = $_GET['msg'] ?? '';
$error = '';

// STORE (create)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'store') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($name && filter_var($email, FILTER_VALIDATE_EMAIL) && $password) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('INSERT INTO users (name,email,password) VALUES (?, ?, ?)');
        $stmt->execute([$name, $email, $hash]);
        header('Location: crud.php?msg=User+added');
        exit;
    } else {
        $error = 'Please fill in all fields correctly.';
        $action = 'create';
    }
}

// UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'update') {
    $id = (int)($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($id > 0 && $name && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        if ($password) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('UPDATE users SET name = ?, email = ?, password = ? WHERE user_id = ?');
            $stmt->execute([$name, $email, $hash, $id]);
        } else {
            $stmt = $pdo->prepare('UPDATE users SET name = ?, email = ? WHERE user_id = ?');
            $stmt->execute([$name, $email, $id]);
        }
        header('Location: crud.php?msg=User+updated');
        exit;
    } else {
        $error = 'Please fill in all fields correctly.';
        $action = 'edit';
        $_GET['id'] = (string)$id;
    }
}

// DELETE (simple GET for teaching; change to POST for production)
if ($action === 'delete') {
    $id = (int)($_GET['id'] ?? 0);
    if ($id > 0) {
        $stmt = $pdo->prepare('DELETE FROM users WHERE user_id = ?');
        $stmt->execute([$id]);
        header('Location: crud.php?msg=User+deleted');
        exit;
    } else {
        $msg = 'Invalid user id.';
        $action = 'list';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Users - CRUD</title>
<link rel="stylesheet" href="../../style.css">
</head>
<body>
<div class="app">
    <h2>Users</h2>
    <div><a href="../Authentication/logout.php">Logout</a> | <a href="crud.php?action=create">Add User</a></div>
    <?php if ($msg): ?><div class="success"><?php echo htmlspecialchars($msg); ?></div><?php endif; ?>
    <?php if ($error): ?><div class="error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

    <?php if ($action === 'create'): ?>
        <h3>Add User</h3>
        <form method="post" action="?action=store">
            <input name="name" placeholder="Full name" required>
            <input name="email" type="email" placeholder="Email" required>
            <input name="password" type="password" placeholder="Password" required>
            <button type="submit">Save</button>
            <a href="crud.php">Cancel</a>
        </form>

    <?php elseif ($action === 'edit'): 
        $id = (int)($_GET['id'] ?? 0);
        $user = null;
        if ($id > 0) {
            $stmt = $pdo->prepare('SELECT user_id,name,email,created_at FROM users WHERE user_id = ?');
            $stmt->execute([$id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        if (!$user): ?>
            <p>User not found.</p>
            <a href="crud.php">Back</a>
        <?php else: ?>
            <h3>Edit User</h3>
            <form method="post" action="?action=update">
                <input type="hidden" name="id" value="<?php echo (int)$user['user_id']; ?>">
                <input name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                <input name="email" type="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                <input name="password" type="password" placeholder="Leave blank to keep current">
                <button type="submit">Update</button>
                <a href="crud.php">Cancel</a>
            </form>
        <?php endif; ?>

    <?php else: // list
        $stmt = $pdo->query('SELECT user_id,name,email,created_at FROM users ORDER BY user_id DESC');
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (empty($users)): ?>
            <p>No users found.</p>
        <?php else: ?>
            <table>
                <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Created</th><th>Actions</th></tr></thead>
                <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td><?php echo (int)$u['user_id']; ?></td>
                        <td><?php echo htmlspecialchars($u['name']); ?></td>
                        <td><?php echo htmlspecialchars($u['email']); ?></td>
                        <td><?php echo htmlspecialchars($u['created_at']); ?></td>
                        <td>
                            <a href="?action=edit&id=<?php echo (int)$u['user_id']; ?>">Edit</a>
                            <a href="?action=delete&id=<?php echo (int)$u['user_id']; ?>" onclick="return confirm('Delete user?');">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    <?php endif; ?>
</div>
</body>
</html>