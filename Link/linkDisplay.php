<?php

session_start();

include '../db/db.php';


$links = [];
$userDetails = [];
$username = '';
$userid = '';
$foldername = '';
if (isset($_GET['user_id']) && isset($_GET['foldername'])) {

    $userid = $_GET['user_id'];
    $foldername = $_GET['foldername'];

    if (!isset($_SESSION['user_id'])) {
        die("You are not authorized to view this page");
    }

    $stmt = $pdo->prepare("SELECT * FROM links WHERE username = ? AND foldername = ?");
    $stmt->execute([$userid, $foldername]);

    $links = $stmt->fetchAll();

    $stmt2 = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt2->execute([$userid]);

    $userDetails = $stmt2->fetch();
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
                <?php if(sizeof($links)==0){
                    echo "<h3 id ='empty-links'>There's isn't any link, Add one?</h3>";
                } ?>
                <?php foreach ($links as $link) : ?>
                    <div class="folder-block">
                        <div class="folder-block-1">
                            <p><?php echo htmlspecialchars($link['linknames']) ?></p>
                        </div>
                        <div class="folder-icn-wp">
                            <button 
                                class="delete-link "
                                    data-foldername="<?php echo htmlspecialchars($foldername) ?>"
                                    data-linknum="<?php echo htmlspecialchars($link['linkno']) ?>"
                                    data-userid="<?php echo htmlspecialchars($userid) ?>">
                                    <img src="../images/icons/image.png" alt="delete button" class="folder-img">
                            </button>
                                <?php 
                                $url = $link['url'];
                                if (strpos($url, 'http') === false) {
                                    $url = 'http://' . $url;
                                }
                                 ?>
                                <a href="<?php echo htmlspecialchars($url) ?>" target="_blank">
                                    <img src="../images/icons/link.png" alt="goto link" class="folder-img">
                                </a>
                        </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>


        <footer>
        <form id="linkInputForm">    
            <input type="hidden" name="foldername" value="<?php echo htmlspecialchars($foldername) ?>">
            <input type="text" placeholder="known as?" name="inputname" required>
            <input type="text" placeholder="www.addlink.com" name="inputurl" required>
            <button type="submit">Add Link</button>
        </form>
        </footer>

        <script src="../Link/LinkInput/input.js"></script>
        <script src="../Link/LinkDelete/LinkDelete.js"></script>

        <script>
            const logoutButton = document.querySelector('.homepage-button');
            logoutButton.addEventListener('click', () => {
                window.history.back();
            });
        </script>
</body>
    

</html>
