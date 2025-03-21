<?php
session_start();
include '../db/db.php';

// Check for tab_id
if (isset($_GET['tab_id'])) {
    $tab_id = $_GET['tab_id'];
} else {
    die("tab_id is not set");
}

// Validate tab-specific context
if (!isset($_SESSION['tab_context'][$tab_id]) || $_SESSION['tab_context'][$tab_id] !== $_SESSION['user_id']) {
    // Redirect to profile page with correct user and tab_id
    header('Location: ../profile/profile.php?user=' . urlencode($_SESSION['user_id']) . '&tab_id=' . urlencode($tab_id));
    exit;
}

// Initialize variables
$links = [];
$userDetails = [];
$username = '';
$userid = '';
$foldername = '';
$hasAccess = false;
$isOwner = false;
$canWrite = false;
$canDelete = false;

// Use the tab-specific user ID
$current_tab_user = $_SESSION['user_id'];

// First check if we have the required parameters
if (isset($_GET['user_id']) && isset($_GET['foldername'])) {
    $userid = $_GET['user_id'];
    $foldername = $_GET['foldername'];

    if (!isset($_SESSION['user_id'])) {
        die("You are not authorized to view this page");
    }

    // Store the actual logged-in user's ID
    $logged_in_user = $_SESSION['user_id'];

    // Check if user owns the folder
    $stmt = $pdo->prepare("SELECT 1 FROM folders WHERE username = ? AND foldername = ?");
    $stmt->execute([$userid, $foldername]);
    if ($stmt->fetchColumn()) {
        $hasAccess = true;
        $isOwner = ($userid === $logged_in_user);
        $canWrite = true;
        $canDelete = true;
    } else {
        // Check if user has permission through folder_permissions
        $stmt = $pdo->prepare("
            SELECT fp.can_write, fp.can_delete 
            FROM folder_permissions fp 
            INNER JOIN users u ON fp.collaborator_email = u.email 
            WHERE fp.foldername = ? 
            AND fp.owner_username = ? 
            AND u.username = ?
        ");
        $stmt->execute([$foldername, $userid, $logged_in_user]);
        $permissions = $stmt->fetch();
        if ($permissions) {
            $hasAccess = true;
            $canWrite = $permissions['can_write'];
            $canDelete = $permissions['can_delete'];
        }
    }

    if (!$hasAccess) {
        die("You don't have permission to view this folder");
    }

    // Fetch links
    $stmt = $pdo->prepare("SELECT * FROM links WHERE username = ? AND foldername = ?");
    $stmt->execute([$userid, $foldername]);
    $links = $stmt->fetchAll();

    // Fetch user details of the folder owner
    $stmt2 = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt2->execute([$userid]);
    $userDetails = $stmt2->fetch();
} else {
    die("Missing required parameters");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./style.css">
    <title>Links Page</title>
</head>

<body>
    <header>
        <h6><?php echo htmlspecialchars($userDetails['name']) ?></h6>
        <button class="homepage-button">Back</button>
    </header>

    <main>
        <div class="main-img">
            <img src="../images/img/profile.png" alt="Profile Picture">
        </div>
        <div class="user-details">
            <h6>@<?php echo htmlspecialchars($userid) ?></h6>
            <h4><?php echo htmlspecialchars($userDetails['name']) ?></h4>
            <h6><?php echo htmlspecialchars($userDetails['email']) ?></h6>
            <h6><?php echo htmlspecialchars($userDetails['user_desc']) ?></h6>
        </div>
    </main>

    <div id="Display">
        <h5><?php echo htmlspecialchars($foldername) ?></h5>
        <div class="links">
            <?php if (sizeof($links) == 0) : ?>
                <h3 id="empty-links">
                    <?php echo ($canWrite) ? "There isn't any link, Add one?" : "No links in this folder yet"; ?>
                </h3>
            <?php endif; ?>

            <?php foreach ($links as $link) : ?>
                <div class="folder-block">
                    <div class="folder-block-1">
                        <p><?php echo htmlspecialchars($link['linknames']) ?></p>
                    </div>
                    <div class="folder-icn-wp">
                        <?php if ($isOwner || $canDelete) : ?>
                            <button class="delete-link"
                                data-foldername="<?php echo htmlspecialchars($foldername) ?>"
                                data-linknum="<?php echo htmlspecialchars($link['linkno']) ?>"
                                data-userid="<?php echo htmlspecialchars($userid) ?>">
                                <img src="../images/icons/image.png" alt="delete button" class="folder-img">
                            </button>
                        <?php endif; ?>

                        <?php
                        $url = $link['url'];
                        if (strpos($url, 'http') === false) {
                            $url = 'http://' . $url;
                        }
                        ?>
                        <a href="<?php echo htmlspecialchars($url) ?>" target="_blank"
                            title="<?php echo $canWrite ? 'Click to visit link' : 'View only access'; ?>">
                            <img src="../images/icons/link.png" alt="goto link" class="folder-img">
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <?php if ($isOwner || $canWrite) : ?>
        <footer>
            <form id="linkInputForm">
                <input type="hidden" name="foldername" value="<?php echo htmlspecialchars($foldername) ?>">
                <input type="hidden" name="folder_owner" value="<?php echo htmlspecialchars($userid) ?>">
                <input type="text" placeholder="known as?" name="inputname" required>
                <input type="text" placeholder="www.addlink.com" name="inputurl" required>
                <button type="submit">Add Link</button>
            </form>
        </footer>
    <?php endif; ?>

    <script src="./LinkInput/input.js"></script>
    <script src="./LinkDelete/LinkDelete.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const backButton = document.querySelector('.homepage-button');
            backButton.addEventListener('click', () => {
                // Always redirect to the tab's original user
                const tabUser = '<?php echo $current_tab_user; ?>';
                window.location.href = '../profile/profile.php?user=' + encodeURIComponent(tabUser);
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const logoutButton = document.querySelector('.homepage-button');
            logoutButton.addEventListener('click', () => {
                // Always redirect to logged-in user's profile
                window.location.href = '../profile/profile.php?user=<?php echo urlencode($logged_in_user); ?>';
            });
        });
    </script>
</body>

</html>