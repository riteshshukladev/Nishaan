<?php
session_start();
header('Content-Type: application/json');
include '../db/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $folderName = $_POST['folderName'] ?? '';
    $ownerUsername = $_POST['ownerUsername'] ?? '';
    $collaboratorEmail = $_POST['collaboratorEmail'] ?? '';

    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }

    switch ($action) {
        case 'add':
            $canWrite = isset($_POST['canWrite']) ? 'true' : 'false';
            $canDelete = isset($_POST['canDelete']) ? 'true' : 'false';

            try {
                $pdo->beginTransaction();

                $stmt = $pdo->prepare("SELECT 1 FROM users WHERE email = ?");
                $stmt->execute([$collaboratorEmail]);
                if (!$stmt->fetchColumn()) {
                    $pdo->rollBack();
                    echo json_encode(['success' => false, 'message' => 'User with this email does not exist']);
                    exit;
                }

                $stmt = $pdo->prepare("SELECT 1 FROM folder_permissions 
                    WHERE foldername = ? AND owner_username = ? AND collaborator_email = ?");
                $stmt->execute([$folderName, $ownerUsername, $collaboratorEmail]);
                if ($stmt->fetchColumn()) {
                    $pdo->rollBack();
                    echo json_encode(['success' => false, 'message' => 'This user already has access to this folder']);
                    exit;
                }

                $stmt = $pdo->prepare("INSERT INTO folder_permissions 
                    (foldername, owner_username, collaborator_email, can_write, can_delete) 
                    VALUES (?, ?, ?, ?::boolean, ?::boolean)");
                $success = $stmt->execute([$folderName, $ownerUsername, $collaboratorEmail, $canWrite, $canDelete]);

                if ($success) {
                    $pdo->commit();
                    echo json_encode([
                        'success' => true,
                        'message' => 'Collaborator added successfully with ' .
                            ($canWrite === 'true' ? 'write' : 'read-only') .
                            ($canDelete === 'true' ? ' and delete' : '') .
                            ' permissions'
                    ]);
                } else {
                    $pdo->rollBack();
                    echo json_encode(['success' => false, 'message' => 'Failed to add permission']);
                }
            } catch (PDOException $e) {
                $pdo->rollBack();
                error_log("Error in folder_permissions.php (add case): " . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
            }
            break;

        case 'remove':
            try {
                $pdo->beginTransaction();

                $stmt = $pdo->prepare("SELECT 1 FROM folders WHERE username = ? AND foldername = ?");
                $stmt->execute([$ownerUsername, $folderName]);
                if (!$stmt->fetchColumn()) {
                    $pdo->rollBack();
                    echo json_encode(['success' => false, 'message' => 'Folder not found or unauthorized']);
                    exit;
                }

                $stmt = $pdo->prepare("DELETE FROM folder_permissions 
                    WHERE foldername = ? AND owner_username = ? AND collaborator_email = ?");
                $success = $stmt->execute([$folderName, $ownerUsername, $collaboratorEmail]);

                if ($success) {
                    $pdo->commit();
                    echo json_encode(['success' => true, 'message' => 'Collaborator removed successfully']);
                } else {
                    $pdo->rollBack();
                    echo json_encode(['success' => false, 'message' => 'Failed to remove collaborator']);
                }
            } catch (PDOException $e) {
                $pdo->rollBack();
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
            break;

        case 'update':
            try {
                $pdo->beginTransaction();
                $canWrite = isset($_POST['canWrite']) && $_POST['canWrite'] === 'on' ? true : false;
                $canDelete = isset($_POST['canDelete']) && $_POST['canDelete'] === 'on' ? true : false;

                $stmt = $pdo->prepare("UPDATE folder_permissions 
                    SET can_write = ?, can_delete = ? 
                    WHERE foldername = ? AND owner_username = ? AND collaborator_email = ?");
                $success = $stmt->execute([$canWrite, $canDelete, $folderName, $ownerUsername, $collaboratorEmail]);

                if ($success) {
                    $pdo->commit();
                    echo json_encode(['success' => true, 'message' => 'Permissions updated successfully']);
                } else {
                    $pdo->rollBack();
                    echo json_encode(['success' => false, 'message' => 'Failed to update permissions']);
                }
            } catch (PDOException $e) {
                $pdo->rollBack();
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $folderName = $_GET['folderName'] ?? '';
    $ownerUsername = $_GET['ownerUsername'] ?? '';

    try {
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $stmt = $pdo->prepare("SELECT 
                fp.*,
                u.username as collaborator_username,
                u.name as collaborator_name
            FROM folder_permissions fp
            INNER JOIN users u ON fp.collaborator_email = u.email
            WHERE fp.foldername = ? AND fp.owner_username = ?");
        $stmt->execute([$folderName, $ownerUsername]);
        $permissions = $stmt->fetchAll();
        echo json_encode(['success' => true, 'permissions' => $permissions]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
