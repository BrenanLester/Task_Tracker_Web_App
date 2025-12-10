<?php
/**
 * Simple idempotent migration helper for development.
 * Run: php migrate.php
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    $dbFile = __DIR__ . '/database.sqlite';
    $pdo = new PDO('sqlite:' . $dbFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('PRAGMA foreign_keys = ON');

    echo "Connected to SQLite DB: $dbFile\n";

    // Ensure users.activity_count exists
    $cols = $pdo->query("PRAGMA table_info('users')")->fetchAll(PDO::FETCH_ASSOC);
    $hasActivity = false;
    foreach ($cols as $c) {
        if (isset($c['name']) && $c['name'] === 'activity_count') { $hasActivity = true; break; }
    }
    if (!$hasActivity) {
        echo "Adding users.activity_count column...\n";
        $pdo->exec("ALTER TABLE users ADD COLUMN activity_count INTEGER DEFAULT 0");
    } else {
        echo "users.activity_count already present\n";
    }

    // Ensure tasks table exists (CREATE IF NOT EXISTS is safe)
    echo "Ensuring tasks table exists...\n";
    $schema = file_get_contents(__DIR__ . '/schema.sql');
    // Extract the tasks CREATE TABLE block (simple approach: execute whole file safe because CREATE IF NOT EXISTS present)
    $pdo->exec($schema);

    // Ensure subject column exists in tasks table
    $cols = $pdo->query("PRAGMA table_info('tasks')")->fetchAll(PDO::FETCH_ASSOC);
    $hasSubject = false;
    foreach ($cols as $c) {
        if (isset($c['name']) && $c['name'] === 'subject') { $hasSubject = true; break; }
    }
    if (!$hasSubject) {
        echo "Adding tasks.subject column...\n";
        $pdo->exec("ALTER TABLE tasks ADD COLUMN subject TEXT");
    } else {
        echo "tasks.subject already present\n";
    }

    // Ensure index exists
    echo "Ensuring idx_tasks_user_id exists...\n";
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_tasks_user_id ON tasks(user_id)");

    // Show recent tasks count
    echo "Recent tasks (last 5):\n";
    $stmt = $pdo->query("SELECT task_id, user_id, title, created_at FROM tasks ORDER BY created_at DESC LIMIT 5");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (empty($rows)) {
        echo "  (no tasks found)\n";
    } else {
        foreach ($rows as $r) {
            echo sprintf("  [%d] user=%s title=%s created=%s\n", $r['task_id'], $r['user_id'], $r['title'], $r['created_at']);
        }
    }

    echo "Migration finished.\n";

} catch (Exception $e) {
    echo "Migration error: " . $e->getMessage() . "\n";
    exit(1);
}

