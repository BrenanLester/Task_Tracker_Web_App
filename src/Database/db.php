<?php
/**
 * Database Connection Handler
 * Establishes PDO connection to SQLite database with proper error handling
 * Initializes schema from schema.sql on first run
 */

try {
    // Construct path to SQLite database file
    $dbFile = __DIR__ . "/database.sqlite";
    
    // Create PDO connection
    $pdo = new PDO("sqlite:" . str_replace("\\", "/", $dbFile));
    
    // Set error mode to exceptions for better error handling
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Enable foreign key support in SQLite
    $pdo->exec('PRAGMA foreign_keys = ON');
    
    // Initialize schema from schema.sql if database is empty
    $schemaFile = __DIR__ . "/schema.sql";
    if (file_exists($schemaFile)) {
        // Check if any tables exist
        $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'");
        $tables = $stmt->fetchAll();
        
        if (empty($tables)) {
            // No tables exist, initialize schema from file
            $pdo->exec(file_get_contents($schemaFile));
        }
    }
    
} catch (PDOException $e) {
    // Log error securely
    error_log("Database Connection Error: " . $e->getMessage());
    die("Database connection failed. Please try again later.");
}
?>