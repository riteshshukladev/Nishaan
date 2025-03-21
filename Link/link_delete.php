<?php
session_start();
header('Content-Type: application/json');
include '../db/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $userId = $_POST['userID'];
    $foldername = $_POST['folderName'] ?? '';
    $linkno = $_POST['linknum'] ?? '';
    $session_user_id = $_SESSION['user_id'];

    try {
        $pdo->beginTransaction();

        // Check if user owns the folder
        $stmt = $pdo->prepare("SELECT 1 FROM folders WHERE username = ? AND foldername = ?");
        $stmt->execute([$userId, $foldername]);
        $isOwner = $stmt->fetchColumn();

        if (!$isOwner) {
            // Check if user has delete permission
            $stmt = $pdo->prepare("
                SELECT fp.can_delete 
                FROM folder_permissions fp 
                INNER JOIN users u ON fp.collaborator_email = u.email 
                WHERE fp.foldername = ? 
                AND fp.owner_username = ? 
                AND u.username = ?
                AND fp.can_delete = true
            ");
            $stmt->execute([$foldername, $userId, $session_user_id]);
            $hasDeletePermission = $stmt->fetchColumn();

            if (!$hasDeletePermission) {
                $pdo->rollBack();
                echo json_encode(['success' => false, 'message' => 'You do not have permission to delete links from this folder']);
                exit;
            }
        }

        // Delete the link
        $stmt = $pdo->prepare("DELETE FROM links WHERE username = ? AND foldername = ? AND linkno = ?");
        $stmt->execute([$userId, $foldername, $linkno]);

        if ($stmt->rowCount() > 0) {
            $pdo->commit();
            echo json_encode(['success' => true]);
        } else {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'message' => 'Link not found']);
        }
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
