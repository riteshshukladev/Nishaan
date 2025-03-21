<?php
// filepath: c:\xampp\htdocs\Nishaan\profile\profile.php
session_start();
error_log("Starting profile.php");
error_log("Session user_id: " . ($_SESSION['user_id'] ?? 'Not set'));
error_log("tab_id: " . ($_GET['tab_id'] ?? 'Not set'));
error_log("tab_context: " . print_r($_SESSION['tab_context'] ?? [], true));
header('Content-Type: application/json');
include '../db/db.php';

$base_url = 'http://localhost/Nishaan/'; // Define base URL


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $userId = $_POST['userID'];
    $folderName = $_POST['folderName'] ?? '';
    $tab_id = $_POST['tab_id'] ?? '';
    $session_user_id = $_SESSION['user_id'];

    error_log("delete_folder.php - tab_id: " . $tab_id);
    error_log("delete_folder.php - session_user_id: " . $session_user_id);
    error_log("delete_folder.php - tab_context[tab_id]: " . ($_SESSION['tab_context'][$tab_id] ?? 'Not set'));

    // Validate tab-specific context
    if (!isset($_SESSION['tab_context'][$tab_id])) {
        echo json_encode([
            'success' => false,
            'message' => 'Unauthorized actionnnn',
            'redirectTo' => $base_url . 'profile/profile.php?user=' . urlencode($_SESSION['tab_context'][$tab_id] ?? $session_user_id) . '&tab_id=' . urlencode($tab_id)
        ]);
        exit;
    }

    try {
        $pdo->beginTransaction();

        // Verify ownership or delete permission
        $stmt = $pdo->prepare("SELECT 1 FROM folders WHERE username = ? AND foldername = ?");
        $stmt->execute([$userId, $folderName]);
        $isOwner = $stmt->fetchColumn();

        if (!$isOwner && $session_user_id !== $userId) {
            // Check collaborator permissions
            $stmt = $pdo->prepare("
                SELECT fp.can_delete 
                FROM folder_permissions fp 
                INNER JOIN users u ON fp.collaborator_email = u.email 
                WHERE fp.foldername = ? 
                AND fp.owner_username = ? 
                AND u.username = ?
                AND fp.can_delete = true
            ");
            $stmt->execute([$folderName, $userId, $session_user_id]);
            if (!$stmt->fetchColumn()) {
                $pdo->rollBack();
                echo json_encode([
                    'success' => false,
                    'message' => 'Unauthorized action',
                    'redirectTo' => $base_url . 'profile/profile.php?user=' . urlencode($_SESSION['tab_context'][$tab_id] ?? $session_user_id) . '&tab_id=' . urlencode($tab_id)
                ]);
                exit;
            }
        }

        // Delete operations in correct order
        $stmt = $pdo->prepare("DELETE FROM folder_permissions WHERE foldername = ? AND owner_username = ?");
        $stmt->execute([$folderName, $userId]);

        $stmt = $pdo->prepare("DELETE FROM links WHERE foldername = ? AND username = ?");
        $stmt->execute([$folderName, $userId]);

        $stmt = $pdo->prepare("DELETE FROM folders WHERE username = ? AND foldername = ?");
        $stmt->execute([$userId, $folderName]);

        if ($stmt->rowCount() > 0) {
            $pdo->commit();
            echo json_encode([
                'success' => true,
                'redirectTo' => $base_url . 'profile/profile.php?user=' . urlencode($_SESSION['tab_context'][$tab_id]) . '&tab_id=' . urlencode($tab_id)
            ]);
        } else {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'message' => 'Folder not found']);
        }
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
