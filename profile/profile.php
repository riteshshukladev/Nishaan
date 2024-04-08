<?php
session_start();

include '../db/db.php';

$FoldersNames = [];
$userID = '';
$userDetails = [];

if (isset($_GET['user'])) {
    $userID = urldecode($_GET['user']);

    if ($userID === $_SESSION['user_id']) {

        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$userID]);
        $user = $stmt->fetch();

        if ($user) {
            $user_id =  $user['username'];
            $user_email = $user['email'];
            $user_name = $user['name'];
            $user_desc = $user['user_desc'];
        } else {
            die("User not found");
        }
    } else {
        die("You are not authorized to view this page");
    }
}

if ($userID) {
    $stmt2 = $pdo->prepare("SELECT * FROM folders WHERE username = ?");
    $stmt2->execute([$userID]);
    // $folders = $stmt2->fetchAll();
    while ($row = $stmt2->fetch()) {
        $FoldersNames[] = $row['foldername'];
    }
} else {
    die("User not found");
}

// fetching the data from the user table to display the user details

$stmt2 = $pdo->prepare("SELECT * FROM users WHERE username = ?");
$stmt2->execute([$userID]);

$userDetails = $stmt2->fetch();


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
            <?php foreach ($FoldersNames as $foldername) : ?>
                <div class="folder-block">
                    <div class="folder-block-1">
                        <img src="../images/icons/folder.png" alt="folder icon" class="folder-img">

                        <p><?php echo htmlspecialchars($foldername) ?></p>
                    </div>
                    <div class="folder-icn-wp">
                        <button class="folder-button" name="deletebtn" data-userid='<?php echo htmlspecialchars($user_id) ?>' data-foldername='<?php echo htmlspecialchars($foldername) ?>'><img src="../images/icons/image.png" alt="delete button" class="folder-img"></button>

                        
                        <button onclick="navigateToLink('<?php echo htmlspecialchars($foldername) ?>', '<?php echo htmlspecialchars($user_id) ?>')">
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
            <input type="hidden" name="OriginalUsernameFolder" value="<?php echo htmlspecialchars($user_id) ?>">
            <button type="submit">New</button>
        </form>
    </div>


    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <form id="editForm">
                <label for="user_id">User id</label>
                <input type="text" id="user_id" name="user_id" value="<?php echo htmlspecialchars($user_id); ?>">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user_name); ?>">

                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user_email); ?>">

                <label for="description">Description:</label>
                <textarea id="description" name="description"><?php echo htmlspecialchars($user_desc); ?></textarea>

                <!-- hidden input field -->
                <input type="hidden" name="originalUsername" value="<?php echo htmlspecialchars($user_id); ?>">
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

    <!-- script -->
    <script src="./dashboard/dashboard.js"></script>
    <script src="./folder/folder.js"></script>
    <script src="./deleteFolder/deleteFolder.js"></script>
    <script src="../Link/link.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            document.querySelector('.logout').addEventListener('click', function() {
                window.location.href = '../authentication/login/login.html';
            })
        })
    </script>
</body>

</html>