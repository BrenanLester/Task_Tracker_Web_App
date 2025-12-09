<?php
session_start();

// Check if user confirmed logout
if (isset($_GET['confirm']) && $_GET['confirm'] == 'yes') {
    // Clear all session data
    session_unset();
    session_destroy();
    
    // Redirect to index page
    header("Location: ../../index.html");
    exit;
}
?>

