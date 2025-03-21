<?php
session_start();

include '../../db/db.php';

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['username'];
        $_SESSION['tab_user_id'] = $user['username']; // Set initial tab user
        $usernameurlencoded = urlencode($user['username']);

        header("Location: ../../profile/profile.php?user=" . $usernameurlencoded);
        exit;
    } else {
        header("Location: ./login.html?error=Incorrect username or password");
        exit;
    }
}
