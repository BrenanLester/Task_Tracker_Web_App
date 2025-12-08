<?php
session_start();

// Clear all session data
session_unset();
session_destroy();

// Redirect to index page
header("Location: ../../index.html");
exit;
?>
