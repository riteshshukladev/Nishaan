<?php
session_start(); // Add session start
include '../../db/db.php';

if (isset($_POST['signup'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $email = $_POST['email'];
    $name = $_POST['name'];

    try {
        // Check if username already exists
        $stmt = $pdo->prepare("SELECT 1 FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetchColumn()) {
            header("Location: signup.html?error=Username already exists");
            exit;
        }

        // Check if email already exists
        $stmt = $pdo->prepare("SELECT 1 FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetchColumn()) {
            header("Location: signup.html?error=Email already exists");
            exit;
        }

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO users (username, name, email, password) VALUES (?, ?, ?, ?)");
        $result = $stmt->execute([$username, $name, $email, $hashed_password]);

        if ($result) {
            // Set both session variables
            $_SESSION['user_id'] = $username;
            $_SESSION['tab_user_id'] = $username;

            // Redirect to profile page
            header("Location: ../../profile/profile.php?user=" . urlencode($username));
            exit;
        } else {
            header("Location: signup.html?error=Signup failed");
            exit;
        }
    } catch (PDOException $e) {
        header("Location: signup.html?error=" . urlencode("Database error: " . $e->getMessage()));
        exit;
    }
}
