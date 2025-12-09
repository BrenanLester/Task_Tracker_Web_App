<?php

require __DIR__ . '/../Database/db.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST["name"]);
    $email = trim(strtolower($_POST["email"] ?? ''));
    $password = trim($_POST["password"] ?? '');

    if (!$name || !$email || !$password) {
        header("Location: ../../signin.html?error=" . urlencode("All fields are required"));
        exit;
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: ../../signin.html?error=" . urlencode("Please enter a valid email address"));
        exit;
    } elseif (strlen($password) < 8) {
        header("Location: ../../signin.html?error=" . urlencode("Password must be at least 8 characters"));
        exit;
    } else {
        // Optional: validate that email domain has DNS records (MX or A) if function available
        $domain = substr(strrchr($email, '@'), 1);
        if ($domain && function_exists('checkdnsrr')) {
            $hasDns = checkdnsrr($domain, 'MX') || checkdnsrr($domain, 'A');
            if (!$hasDns) {
                header("Location: ../../signin.html?error=" . urlencode("Email domain does not appear to be valid"));
                exit;
            }
        }

        $check = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $check->execute([$email]);
        if ($check->fetch()) {
            header("Location: ../../signin.html?error=" . urlencode("Email already registered"));
            exit;
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
            $stmt->execute([$name, $email, $hash]);
            
            // Set session and redirect based on redirect parameter
            $_SESSION["user_id"] = $pdo->lastInsertId();
            $_SESSION["name"] = $name;
            
            $redirect = $_POST['redirect'] ?? '';
            if ($redirect === 'about') {
                header("Location: About/about.php");
            } else {
                header("Location: Dashboard/dashboard.php");
            }
            exit;
        }
    }
}
?>
