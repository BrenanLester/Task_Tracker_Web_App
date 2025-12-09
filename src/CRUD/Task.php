<?php
/**
 * Task CRUD API
 * Pure backend - handles all task database operations
 * Returns JSON responses for frontend consumption
 */

// Suppress all errors from being displayed
error_reporting(0);
ini_set('display_errors', 0);

// Start output buffering to catch any accidental output
ob_start();

session_start();

// Set JSON header immediately
header('Content-Type: application/json');

// Authentication check
if (!isset($_SESSION['user_id']) && !isset($_SESSION['user'])) {
    ob_end_clean(); // Clear buffer and close
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$current_user_id = $_SESSION['user_id'] ?? ($_SESSION['user']['user_id'] ?? null);

try {
    require_once __DIR__ . '/../Database/db.php';
} catch (Exception $e) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Clear any output from db.php and close buffer
ob_end_clean();

$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

try {
    switch ($action) {
        
        // CREATE - Add new task
        case 'create':
        case 'store':
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $priority = $_POST['priority'] ?? 'Medium';
            $due_date = $_POST['due_date'] ?: null;
            $quadrant = $_POST['quadrant'] ?? 'others';
            $status = $_POST['status'] ?? 'Pending';

            if (!$title) {
                throw new Exception('Title is required');
            }

            $stmt = $pdo->prepare(
                'INSERT INTO tasks (user_id, title, description, priority, due_date, quadrant, status) 
                 VALUES (?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([$current_user_id, $title, $description, $priority, $due_date, $quadrant, $status]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Task created successfully',
                'id' => $pdo->lastInsertId()
            ]);
            break;

        // READ - Get all tasks or single task
        case 'list':
        case 'read':
            $id = $_GET['id'] ?? null;

            if ($id) {
                // Get single task
                $stmt = $pdo->prepare('SELECT * FROM tasks WHERE task_id = ? AND user_id = ?');
                $stmt->execute([(int)$id, $current_user_id]);
                $task = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$task) {
                    throw new Exception('Task not found');
                }

                echo json_encode(['success' => true, 'task' => $task]);
            } else {
                // Get all tasks for user
                $quadrant = $_GET['quadrant'] ?? null;
                
                if ($quadrant) {
                    $stmt = $pdo->prepare('SELECT * FROM tasks WHERE user_id = ? AND quadrant = ? ORDER BY created_at DESC');
                    $stmt->execute([$current_user_id, $quadrant]);
                } else {
                    $stmt = $pdo->prepare('SELECT * FROM tasks WHERE user_id = ? ORDER BY created_at DESC');
                    $stmt->execute([$current_user_id]);
                }

                $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode(['success' => true, 'tasks' => $tasks]);
            }
            break;

        // UPDATE - Edit existing task
        case 'update':
            $id = (int)($_POST['id'] ?? 0);
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $priority = $_POST['priority'] ?? 'Medium';
            $due_date = $_POST['due_date'] ?: null;
            $quadrant = $_POST['quadrant'] ?? 'others';
            $status = $_POST['status'] ?? 'Pending';

            if ($id <= 0 || !$title) {
                throw new Exception('Invalid task data');
            }

            // Verify ownership
            $stmt = $pdo->prepare('SELECT user_id FROM tasks WHERE task_id = ?');
            $stmt->execute([$id]);
            $task = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$task || $task['user_id'] != $current_user_id) {
                http_response_code(403);
                throw new Exception('Forbidden');
            }

            $stmt = $pdo->prepare(
                'UPDATE tasks SET title = ?, description = ?, priority = ?, due_date = ?, quadrant = ?, status = ?, updated_at = CURRENT_TIMESTAMP 
                 WHERE task_id = ?'
            );
            $stmt->execute([$title, $description, $priority, $due_date, $quadrant, $status, $id]);

            echo json_encode(['success' => true, 'message' => 'Task updated successfully']);
            break;

        // DELETE - Remove task
        case 'delete':
            $id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);

            if ($id <= 0) {
                throw new Exception('Invalid task ID');
            }

            // Verify ownership
            $stmt = $pdo->prepare('SELECT user_id FROM tasks WHERE task_id = ?');
            $stmt->execute([$id]);
            $task = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$task || $task['user_id'] != $current_user_id) {
                http_response_code(403);
                throw new Exception('Forbidden');
            }

            $stmt = $pdo->prepare('DELETE FROM tasks WHERE task_id = ?');
            $stmt->execute([$id]);

            echo json_encode(['success' => true, 'message' => 'Task deleted successfully']);
            break;

        // GET GROUPED BY QUADRANT
        case 'grouped':
            $stmt = $pdo->prepare('SELECT * FROM tasks WHERE user_id = ? ORDER BY created_at DESC');
            $stmt->execute([$current_user_id]);
            $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Group by quadrant
            $grouped = [
                'urgent-important' => [],
                'important' => [],
                'urgent' => [],
                'others' => []
            ];

            foreach ($tasks as $task) {
                $quadrant = $task['quadrant'] ?? 'others';
                if (isset($grouped[$quadrant])) {
                    $grouped[$quadrant][] = $task;
                } else {
                    $grouped['others'][] = $task;
                }
            }

            echo json_encode(['success' => true, 'tasks' => $grouped]);
            break;

        default:
            http_response_code(400);
            throw new Exception('Invalid action');
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>