<?php
session_start();
header('Content-Type: application/json');
include '../db/db.php'; // Ensure this path points to your actual database connection script

// Check if the required data is present
if(isset($_POST['originalUsername'], $_POST['user_id'],$_POST['name'], $_POST['email'], $_POST['description'])) {
    $originalUsername = $_POST['originalUsername'];
    $newUsername = $_POST['user_id'];
    $newEmail = $_POST['email'];
    $newDescription = $_POST['description'];
    $newName = $_POST['name'];
    
    // Assuming user_id is stored in the session when the user logs in
    if (!isset($_SESSION['user_id'])) {
        // Handle the case where the session doesn't contain user_id
        echo json_encode(['success' => false, 'message' => 'Session expired or user not logged in.']);
        exit;
    }

    // Validate and sanitize inputs here (omitted for brevity)
    
    // Update query
    $stmt = $pdo->prepare("UPDATE users SET username = ?,name = ?, email = ?, user_desc = ? WHERE username = ?");
    $success = $stmt->execute([$newUsername, $newName, $newEmail, $newDescription, $originalUsername]);

    if ($success) {
        $usernameChanged = ($originalUsername !== $newUsername);
        if ($usernameChanged) {
            $_SESSION['user_id'] = $newUsername;
            $newUrl = "./profile.php?user=" . urlencode($newUsername);
            echo json_encode(['success' => true, 'usernameChanged' => $usernameChanged, 'redirectUrl' => $newUrl]);
        } else {
            echo json_encode(['success' => true, 'usernameChanged' => $usernameChanged]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Database update failed.']);
    }


} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request. Missing data.']);
}
?>
