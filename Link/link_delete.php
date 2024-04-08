<?php

session_start();
header('Content-Type: application/json');

include '../db/db.php';

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])){
    $userId = $_POST['userID'] == null ? $_SESSION['user_id'] : $_POST['userID'];

    $foldername = $_POST['folderName'] ?? '';
    $linkno = $_POST['linknum'] ?? '';

    try{
        $stmt = $pdo->prepare("DELETE FROM links WHERE username = ? AND foldername = ? AND linkno = ?");
        $stmt->execute([$userId, $foldername, $linkno]);

        if($stmt->rowCount() > 0){
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }
    } catch (PDOException $e){
        echo json_encode(['success' => false]);
    }
}


?>