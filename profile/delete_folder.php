<?php
session_start();
header('Content-Type: application/json');

include '../db/db.php'; // Include your database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $userId = $_POST['userID'] == null ? $_SESSION['user_id'] : $_POST['userID'];
    $folderName = $_POST['folderName'] ?? '';
    

    // Validate userId and folderName
    // Ensure the user has permission to delete the folder

    try {
        $stmt = $pdo->prepare("DELETE FROM folders WHERE username = ? AND foldername = ?");
        $stmt->execute([$userId, $folderName]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }
       
    } catch (PDOException $e) {
        // Handle error
        echo json_encode(['success' => false]);
    }
} else {
    echo json_encode(['success' => false]);
}
