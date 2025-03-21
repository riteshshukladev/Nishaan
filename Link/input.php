<?php
session_start();
header('Content-Type: application/json');
include '../db/db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Session expired or not logged in']);
    exit;
}

if (isset($_POST['foldername'], $_POST['inputname'], $_POST['inputurl'], $_POST['folder_owner'])) {
    $foldername = $_POST['foldername'];
    $inputname = $_POST['inputname'];
    $inputurl = $_POST['inputurl'];
    $folder_owner = $_POST['folder_owner'];
    $session_user_id = $_SESSION['user_id'];

    try {
        $pdo->beginTransaction();

        // Check if user is owner
        if ($session_user_id === $folder_owner) {
            $canWrite = true;
        } else {
            // Check collaborator permissions
            $stmt = $pdo->prepare("
                SELECT fp.can_write 
                FROM folder_permissions fp 
                INNER JOIN users u ON fp.collaborator_email = u.email 
                WHERE fp.foldername = ? 
                AND fp.owner_username = ? 
                AND u.username = ?
                AND fp.can_write = true
            ");
            $stmt->execute([$foldername, $folder_owner, $session_user_id]);
            $permission = $stmt->fetch();
            $canWrite = $permission ? true : false;
        }

        if (!$canWrite) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'message' => 'You do not have permission to add links to this folder']);
            exit;
        }

        // Insert the link
        $stmt = $pdo->prepare("INSERT INTO links (foldername, username, linknames, url) VALUES (?, ?, ?, ?)");
        $success = $stmt->execute([$foldername, $folder_owner, $inputname, $inputurl]);

        if ($success) {
            $pdo->commit();
            echo json_encode(['success' => true, 'message' => 'Link added successfully']);
        } else {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'message' => 'Failed to add link to database']);
        }
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Missing required data']);
}
