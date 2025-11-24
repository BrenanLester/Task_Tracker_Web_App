<?php
session_start();
if (!isset($_SESSION['user_id']) && !isset($_SESSION['user'])) {
    header('Location: ../Authentication/login.php');
    exit;
}
$current_user_id = $_SESSION['user_id'] ?? ($_SESSION['user']['user_id'] ?? null);

require_once __DIR__ . '/../Database/db.php'; // expects $pdo

$action = $_GET['action'] ?? 'list';
$msg = $_GET['msg'] ?? '';
$error = '';

// STORE (create task)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'store') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $priority = $_POST['priority'] ?? 'Medium';
    $due_date = $_POST['due_date'] ?: null;

    if ($title) {
        $stmt = $pdo->prepare('INSERT INTO tasks (user_id,title,description,priority,due_date) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$current_user_id, $title, $description, $priority, $due_date]);
        header('Location: Task.php?msg=Task+added');
        exit;
    } else {
        $error = 'Title is required.';
        $action = 'create';
    }
}

// UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'update') {
    $id = (int)($_POST['id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $priority = $_POST['priority'] ?? 'Medium';
    $due_date = $_POST['due_date'] ?: null;

    if ($id > 0 && $title) {
        // ownership check
        $stmt = $pdo->prepare('SELECT user_id FROM tasks WHERE task_id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row || $row['user_id'] != $current_user_id) { http_response_code(403); die('Forbidden'); }

        $stmt = $pdo->prepare('UPDATE tasks SET title = ?, description = ?, priority = ?, due_date = ? WHERE task_id = ?');
        $stmt->execute([$title, $description, $priority, $due_date, $id]);
        header('Location: Task.php?msg=Task+updated');
        exit;
    } else {
        $error = 'Please fill in all fields correctly.';
        $action = 'edit';
        $_GET['id'] = (string)$id;
    }
}

// DELETE (simple GET for teaching)
if ($action === 'delete') {
    $id = (int)($_GET['id'] ?? 0);
    if ($id > 0) {
        // ownership check
        $stmt = $pdo->prepare('SELECT user_id FROM tasks WHERE task_id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row || $row['user_id'] != $current_user_id) { http_response_code(403); die('Forbidden'); }

        $stmt = $pdo->prepare('DELETE FROM tasks WHERE task_id = ?');
        $stmt->execute([$id]);
        header('Location: Task.php?msg=Task+deleted');
        exit;
    } else {
        $msg = 'Invalid task id.';
        $action = 'list';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Tasks - CRUD</title>
<link rel="stylesheet" href="../../style.css">
</head>
<body>
<div class="app">
    <h2>Your Tasks</h2>
    <div><a href="../Authentication/logout.php">Logout</a> | <a href="Task.php?action=create">New Task</a></div>
    <?php if ($msg): ?><div class="success"><?php echo htmlspecialchars($msg); ?></div><?php endif; ?>
    <?php if ($error): ?><div class="error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

    <?php if ($action === 'create'): ?>
        <h3>Create Task</h3>
        <form method="post" action="?action=store">
            <input name="title" placeholder="Title" required>
            <textarea name="description" placeholder="Description"></textarea>
            <select name="priority">
                <option>Low</option><option selected>Medium</option><option>High</option>
            </select>
            <input name="due_date" type="date">
            <button type="submit">Save</button>
            <a href="Task.php">Cancel</a>
        </form>

    <?php elseif ($action === 'edit'):
        $id = (int)($_GET['id'] ?? 0);
        $task = null;
        if ($id > 0) {
            $stmt = $pdo->prepare('SELECT * FROM tasks WHERE task_id = ?');
            $stmt->execute([$id]);
            $task = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($task && $task['user_id'] != $current_user_id) { http_response_code(403); die('Forbidden'); }
        }
        if (!$task): ?>
            <p>Task not found.</p>
            <a href="Task.php">Back</a>
        <?php else: ?>
            <h3>Edit Task</h3>
            <form method="post" action="?action=update">
                <input type="hidden" name="id" value="<?php echo (int)$task['task_id']; ?>">
                <input name="title" value="<?php echo htmlspecialchars($task['title']); ?>" required>
                <textarea name="description"><?php echo htmlspecialchars($task['description']); ?></textarea>
                <select name="priority">
                    <option <?php if ($task['priority']=='Low') echo 'selected'; ?>>Low</option>
                    <option <?php if ($task['priority']=='Medium') echo 'selected'; ?>>Medium</option>
                    <option <?php if ($task['priority']=='High') echo 'selected'; ?>>High</option>
                </select>
                <input name="due_date" type="date" value="<?php echo $task['due_date'] ? htmlspecialchars(substr($task['due_date'],0,10)) : ''; ?>">
                <button type="submit">Update</button>
                <a href="Task.php">Cancel</a>
            </form>
        <?php endif; ?>

    <?php else: // list
        $stmt = $pdo->prepare('SELECT * FROM tasks WHERE user_id = ? ORDER BY created_at DESC');
        $stmt->execute([$current_user_id]);
        $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (empty($tasks)): ?>
            <p>No tasks yet.</p>
        <?php else: ?>
            <table>
                <thead><tr><th>ID</th><th>Title</th><th>Priority</th><th>Status</th><th>Due</th><th>Actions</th></tr></thead>
                <tbody>
                <?php foreach ($tasks as $t): ?>
                    <tr>
                        <td><?php echo (int)$t['task_id']; ?></td>
                        <td><?php echo htmlspecialchars($t['title']); ?></td>
                        <td><?php echo htmlspecialchars($t['priority']); ?></td>
                        <td><?php echo htmlspecialchars($t['status']); ?></td>
                        <td><?php echo $t['due_date'] ? htmlspecialchars($t['due_date']) : '-'; ?></td>
                        <td>
                            <a href="?action=edit&id=<?php echo (int)$t['task_id']; ?>">Edit</a>
                            <a href="?action=delete&id=<?php echo (int)$t['task_id']; ?>" onclick="return confirm('Delete this task?');">Delete</a>
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