<?php
session_start();
header('Content-Type: application/json');

include '../db/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Session expired',
        'redirect' => '../authentication/login/login.html'
    ]);
    exit;
}

if (isset($_POST['newFolderName'], $_POST['OriginalUsernameFolder'], $_POST['tab_id'])) {
    $folderName = $_POST['newFolderName'];
    $OriginalUsernameFolder = $_POST['OriginalUsernameFolder'];
    $tab_id = $_POST['tab_id'];

    // Validate tab-specific context
    if (!isset($_SESSION['tab_context'][$tab_id]) || $_SESSION['tab_context'][$tab_id] !== $OriginalUsernameFolder) {
        echo json_encode([
            'success' => false,
            'message' => 'Unauthorized action',
            'currentUser' => $_SESSION['tab_context'][$tab_id] ?? null
        ]);
        exit;
    }

    try {
        // Check if folder already exists for THIS user only
        $stmt = $pdo->prepare("SELECT 1 FROM folders WHERE username = ? AND LOWER(foldername) = LOWER(?)");
        $stmt->execute([$OriginalUsernameFolder, $folderName]);

        if ($stmt->fetchColumn()) {
            echo json_encode([
                'success' => false,
                'message' => 'A folder with this name already exists in your account'
            ]);
            exit;
        }

        // Insert new folder
        $stmt = $pdo->prepare("INSERT INTO folders (foldername, username) VALUES (?,?)");
        $success = $stmt->execute([$folderName, $OriginalUsernameFolder]);

        if ($success) {
            echo json_encode([
                'success' => true,
                'message' => 'Folder created successfully',
                'currentUser' => $_SESSION['tab_context'][$tab_id]
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to create folder']);
        }
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Missing required data']);
}
