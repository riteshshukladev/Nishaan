<?php
// filepath: c:\xampp\htdocs\Nishaan\profile\profile.php
session_start();

// Generate a unique tab ID if not already set
if (!isset($_GET['tab_id'])) {
    $tab_id = uniqid('tab_', true);
    header('Location: profile.php?user=' . urlencode($_SESSION['user_id']) . '&tab_id=' . $tab_id);
    exit;
} else {
    $tab_id = $_GET['tab_id'];
}

// Store tab-specific user ID in a session array
if (!isset($_SESSION['tab_context'])) {
    $_SESSION['tab_context'] = [];
}

if (!isset($_SESSION['tab_context'][$tab_id])) {
    $_SESSION['tab_context'][$tab_id] = $_SESSION['user_id'];
}

$current_tab_user = $_SESSION['tab_context'][$tab_id];

// Validate the requested user
if (isset($_GET['user'])) {
    $requested_user = urldecode($_GET['user']);
    if ($requested_user !== $current_tab_user) {
        header('Location: profile.php?user=' . urlencode($current_tab_user) . '&tab_id=' . $tab_id);
        exit;
    }
} else {
    header('Location: profile.php?user=' . urlencode($current_tab_user) . '&tab_id=' . $tab_id);
    exit;
}

include '../db/db.php';

// Initialize variables
$FoldersNames = [];
$userID = '';
$userDetails = [];

// Fetch user details
if ($current_tab_user) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$current_tab_user]);
    $userDetails = $stmt->fetch();

    if (!$userDetails) {
        header('Location: ../authentication/login/login.html?error=User not found');
        exit;
    }

    $userID = $userDetails['username'];
    $user_email = $userDetails['email'];
    $user_name = $userDetails['name'];
    $user_desc = $userDetails['user_desc'];
} else {
    header('Location: ../authentication/login/login.html?error=Unauthorized access');
    exit;
}

// Fetch owned folders
$stmt2 = $pdo->prepare("SELECT foldername FROM folders WHERE username = ?");
$stmt2->execute([$userID]);
$ownedFolders = $stmt2->fetchAll();

// Fetch shared folders
$stmt3 = $pdo->prepare("
    SELECT DISTINCT f.foldername, f.username as owner_username, fp.can_write, fp.can_delete 
    FROM folders f 
    INNER JOIN folder_permissions fp ON f.foldername = fp.foldername AND f.username = fp.owner_username 
    INNER JOIN users u ON fp.collaborator_email = u.email 
    WHERE u.username = ?
");
$stmt3->execute([$userID]);
$sharedFolders = $stmt3->fetchAll();

// Combine both sets of folders
$FoldersNames = [];
foreach ($ownedFolders as $folder) {
    $FoldersNames[] = [
        'foldername' => $folder['foldername'],
        'is_owner' => true,
        'can_write' => true,
        'can_delete' => true
    ];
}
foreach ($sharedFolders as $folder) {
    $FoldersNames[] = [
        'foldername' => $folder['foldername'],
        'is_owner' => false,
        'owner_username' => $folder['owner_username'],
        'can_write' => $folder['can_write'],
        'can_delete' => $folder['can_delete']
    ];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Page</title>
    <link rel="stylesheet" href="./styleprofile.css">
</head>

<body>

    <header>
        <h6><?php echo htmlspecialchars($userDetails['name']) ?></h6>
        <button class="logout">Logout</button>
    </header>

    <main>
        <div class="main-img">
            <img src="../images/img/profile.png" alt="Profile Picture">
        </div>
        <div class="user-details">
            <h6>@<?php echo htmlspecialchars($userID) ?></h6>
            <h4><?php echo htmlspecialchars($userDetails['name']) ?></h4>
            <h6><?php echo htmlspecialchars($userDetails['email']) ?></h6>
            <h6><?php echo htmlspecialchars($userDetails['user_desc']) ?></h6>
            <button class="edit_button">Edit Profile</button>
        </div>
    </main>

    <div id="Display">
        <div class="links">
            <?php if (sizeof($FoldersNames) == 0) {
                echo "<h3 id ='empty-folders'>Empty Folders! Create One?</h3>";
            } ?>
            <?php foreach ($FoldersNames as $folder) : ?>
                <div class="folder-block">
                    <div class="folder-block-1">
                        <img src="../images/icons/folder.png" alt="folder icon" class="folder-img">
                        <p><?php echo htmlspecialchars($folder['foldername']) ?></p>
                        <?php if (!$folder['is_owner']) : ?>
                            <span class="shared-badge">(Shared by <?php echo htmlspecialchars($folder['owner_username']) ?>)</span>
                        <?php endif; ?>
                    </div>
                    <div class="folder-icn-wp">
                        <?php if ($folder['is_owner']) : ?>
                            <button class="folder-button" name="deletebtn"
                                data-userid='<?php echo htmlspecialchars($userID) ?>'
                                data-foldername='<?php echo htmlspecialchars($folder['foldername']) ?>'>
                                <img src="../images/icons/image.png" alt="delete button" class="folder-img">
                            </button>
                        <?php elseif ($folder['can_delete']) : ?>
                            <button class="folder-button" name="deletebtn"
                                data-userid='<?php echo htmlspecialchars($folder['owner_username']) ?>'
                                data-foldername='<?php echo htmlspecialchars($folder['foldername']) ?>'>
                                <img src="../images/icons/image.png" alt="delete button" class="folder-img">
                            </button>
                        <?php endif; ?>

                        <button onclick="navigateToLink('<?php echo htmlspecialchars($folder['foldername']) ?>', '<?php echo $folder['is_owner'] ? htmlspecialchars($userID) : htmlspecialchars($folder['owner_username']) ?>')">
                            <img src="../images/icons/link.png" alt="" class="folder-img">
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="footer">
        <form id="addFolderForm">
            <input type="text" placeholder="FolderName" name="newFolderName">
            <input type="hidden" name="OriginalUsernameFolder" value="<?php echo htmlspecialchars($userID) ?>">
            <input type="hidden" name="tab_id" value="<?php echo htmlspecialchars($tab_id) ?>">
            <button type="submit">New</button>
        </form>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <form id="editForm">
                <label for="user_id">User id</label>
                <input type="text" id="user_id" name="user_id" value="<?php echo htmlspecialchars($userID); ?>">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user_name); ?>">

                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user_email); ?>">

                <label for="description">Description:</label>
                <textarea id="description" name="description"><?php echo htmlspecialchars($user_desc); ?></textarea>

                <input type="hidden" name="originalUsername" value="<?php echo htmlspecialchars($userID); ?>">
                <button type="submit" class="folder_generic_class">Submit</button>
            </form>
        </div>
    </div>

    <!-- Delete modal -->
    <div id="deleteFolderModal">
        <div class="delete-model-content">
            <span class="folder-close">&times;</span>
            <p>Do you really want to delete <span class="deletefoldername"></span> folder?</p>
            <button type="submit" class="confirmdelete folder_generic_class">Submit</button>
        </div>
    </div>

    <!-- Permissions Modal -->
    <div id="permissionsModal" class="modal">
        <div class="modal-content">
            <span class="close-permissions">&times;</span>
            <h3>Folder Permissions</h3>
            <form id="permissionsForm">
                <input type="hidden" id="currentFolder" name="folderName">
                <input type="hidden" name="ownerUsername" value="<?php echo htmlspecialchars($userID); ?>">
                <input type="hidden" name="tab_user_id" value="<?php echo htmlspecialchars($current_tab_user); ?>">
                <div class="collaborator-input">
                    <input type="email" name="collaboratorEmail" placeholder="Collaborator's Email" required>
                    <p class="permission-note">By default, collaborators will have read-only access</p>
                    <div class="permission-options">
                        <label>
                            <input type="checkbox" name="canWrite"> Can Edit
                        </label>
                        <label>
                            <input type="checkbox" name="canDelete"> Can Delete
                        </label>
                    </div>
                    <button type="submit">Add Collaborator</button>
                </div>
            </form>
            <div id="collaboratorsList">
                <!-- Collaborators will be listed here -->
            </div>
        </div>
    </div>

    <!-- script -->
    <script src="./dashboard/dashboard.js"></script>
    <script src="./folder/folder.js"></script>
    <script src="./deleteFolder/deleteFolder.js"></script>
    <script src="../Link/link.js"></script>
    <script src="./folder_permissions.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelector('.logout').addEventListener('click', function() {
                window.location.href = '../authentication/login/login.html';
            })
        })
    </script>
    <!-- <script>
        document.addEventListener("DOMContentLoaded", function() {
            document.querySelector("#addFolderForm").addEventListener("submit", function(event) {
                event.preventDefault();
                const formData = new FormData(this);
                const currentUserId = formData.get("OriginalUsernameFolder");
                const tabId = new URLSearchParams(window.location.search).get("tab_id");

                formData.append("tab_id", tabId);

                fetch("add_floder.php", {
                        method: "POST",
                        body: formData,
                    })
                    .then((response) => {
                        if (!response.ok) {
                            throw new Error("Network Issue");
                        }
                        return response.json();
                    })
                    .then((data) => {
                        if (data.success) {
                            alert(data.message || "Folder created successfully");
                            // Use the current user's ID from the response
                            window.location.href = `profile.php?user=${encodeURIComponent(data.currentUser || currentUserId)}&tab_id=${encodeURIComponent(tabId)}`;
                        } else {
                            if (data.redirect) {
                                alert(data.message || "Session expired. Please login again.");
                                window.location.href = data.redirect;
                            } else {
                                alert(data.message || "Failed to create folder");
                            }
                        }
                    })
                    .catch((err) => {
                        console.error(err);
                        alert("An error occurred, please try again later");
                    });
            });
        });
    </script> -->
</body>

</html>