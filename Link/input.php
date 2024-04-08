<?php 


session_start();
header('Content-Type: application/json');

include '../db/db.php';


if(isset($_POST['foldername'] , $_POST['inputname'] , $_POST['inputurl'])){
    $foldername = $_POST['foldername'];
    $inputname = $_POST['inputname'];
    $inputurl = $_POST['inputurl'];

    $user_id = $_SESSION['user_id'];

    $stmt = $pdo->prepare("INSERT INTO links (foldername, username , linknames, url ) VALUES (?,?,?,?)");

    $success = $stmt->execute([$foldername, $user_id, $inputname, $inputurl]);

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