<?php

include '../../db/db.php';

if (isset($_POST['signup'])) { // Ensure your signup form has a button named 'signup'
    $username = $_POST['username'];
    $password = $_POST['password'];
    $email = $_POST['email'];
    $name = $_POST['name'];


    // Check if username already exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->rowCount() > 0) {
        die("Username already exists.");
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (username,name,email, password) VALUES (?,?,?,?)");
    $result = $stmt->execute([$username,$name,$email, $hashed_password]);
    $user = $stmt->fetch();
    if ($result){
        session_start();
        $_SESSION['user_id'] = $username;
        $usernameurlencoded = urlencode($username);

        header("Location:../../profile/profile.php?user=".$usernameurlencoded);
        
    } else {
        echo "Signup failed!";
    }
}
?>

