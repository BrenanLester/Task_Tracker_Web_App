<?php

require __DIR__ . '/../Database/db.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    if (!$name || !$email || !$password) {
        header("Location: ../../index.html?error=" . urlencode("All fields are required"));
        exit;
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: ../../index.html?error=" . urlencode("Please enter a valid email address"));
        exit;
    } else {
        $check = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $check->execute([$email]);
        if ($check->fetch()) {
            header("Location: ../../index.html?error=" . urlencode("Email already registered"));
            exit;
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
            $stmt->execute([$name, $email, $hash]);
            header("Location: ../../index.html?success=" . urlencode("Registration successful! Please login"));
            exit;
        }
    }
}
?>
