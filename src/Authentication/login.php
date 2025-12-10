<?php

require __DIR__ . '/../Database/db.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    if (!$email || !$password) {
        header("Location: ../../signin.html?error=" . urlencode("Email and password are required"));
        exit;
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: ../../signin.html?error=" . urlencode("Please enter a valid email address"));
        exit;
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user["password"])) {
            $_SESSION["user_id"] = $user["user_id"];
            $_SESSION["name"] = $user["name"];
            header("Location: Dashboard/dashboard.php");
            exit;
        } else {
            header("Location: ../../signin.html?error=" . urlencode("Invalid email or password"));
            exit;
        }
    }
}
?>
