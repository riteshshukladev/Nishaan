<?php

include '../../db/db.php';


if (isset($_POST['login'])) { // Ensure your login form has a button named 'login'
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        session_start();
        $_SESSION['user_id'] = $user['username'];
        $usernameurlencoded = urlencode($user['username']);

        // header("Location: ../../profile/profile.php/@".$usernameurlencoded);
        header("Location: ../../profile/profile.php?user=".$usernameurlencoded);
        exit;
    } else {
        echo "Login failed: Incorrect username or password";
    }
}
?>