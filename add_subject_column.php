<?php
/**
 * Quick script to add the subject column to the tasks table if it doesn't exist
 * Access this file in your browser: http://localhost/Task_Tracker_Web_App/add_subject_column.php
 */

try {
    $dbFile = __DIR__ . '/src/Database/database.sqlite';
    $pdo = new PDO('sqlite:' . $dbFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "<h2>Database Migration</h2>";
    echo "<p>Checking tasks table structure...</p>";

    // Check if subject column exists
    $cols = $pdo->query("PRAGMA table_info('tasks')")->fetchAll(PDO::FETCH_ASSOC);
    $hasSubject = false;
    
    echo "<p><strong>Current columns in tasks table:</strong></p>";
    echo "<ul>";
    foreach ($cols as $col) {
        echo "<li>" . $col['name'] . " (" . $col['type'] . ")</li>";
        if ($col['name'] === 'subject') {
            $hasSubject = true;
        }
    }
    echo "</ul>";

    if ($hasSubject) {
        echo "<p style='color: green;'><strong>✓ Subject column already exists!</strong></p>";
    } else {
        echo "<p style='color: orange;'><strong>Adding subject column...</strong></p>";
        $pdo->exec("ALTER TABLE tasks ADD COLUMN subject TEXT");
        echo "<p style='color: green;'><strong>✓ Subject column added successfully!</strong></p>";
    }

    // Show sample data
    echo "<p><strong>Tasks in database:</strong></p>";
    $stmt = $pdo->query("SELECT task_id, title, description FROM tasks LIMIT 5");
    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<pre>";
    print_r($tasks);
    echo "</pre>";

} catch (Exception $e) {
    echo "<p style='color: red;'><strong>Error:</strong> " . $e->getMessage() . "</p>";
}
?>
