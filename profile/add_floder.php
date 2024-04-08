<?php


session_start();
header('Content-Type: application/json');

include '../db/db.php';

if(isset($_POST['newFolderName'] , $_POST['OriginalUsernameFolder'])){
    $folderName = $_POST['newFolderName'];
    $OriginalUsernameFolder = $_POST['OriginalUsernameFolder'];

    $folderName = strtolower($folderName);
   if(!isset($_SESSION['user_id'])){
        echo json_encode(['success' => false, 'message' => 'Session expired or user not logged in.']);
        exit;
   }

   $stmt = $pdo->prepare("INSERT INTO folders (foldername, username) VALUES (?,?)");

    $success = $stmt->execute([$folderName, $OriginalUsernameFolder]);

    if ($success){
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database update failed.']);
    }

}
else{
    echo json_encode(['success' => false, 'message' => 'Invalid request. Missing data.']);
}

?>