<?php

$page = 0;
if (isset($_GET["page"]) && is_numeric($_GET["page"])) {
    $page = $_GET["page"] - 1;
    if ($page < 0) {
        $page = 0;
    }
}

$lim = $page * 20;

require("php/navbar.php");
require("php/get_post.php");

// time_penalty =
$timeWeight = 200;
$sql = "SELECT COUNT(views.id) - DATEDIFF(NOW(), posts.time_created) * $timeWeight as score, posts.id 
        FROM views, posts
        WHERE views.post_id = posts.id
   /*   AND posts.time_created > DATE_SUB(NOW(), INTERVAL 7 DAY)  */
        GROUP BY posts.id
        ORDER BY score DESC
        LIMIT $lim, 20;";
//die($conn->error);
$result = $conn->query($sql);
//die($conn->error);
$posts = [];
while ($row = $result->fetch_assoc()) {
    array_push($posts, $row["id"]);
}
?>

<!DOCTYPE html>
<html lang="en-US">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=450">
        <meta name="description" content="Find and share your favourite stuff.">
        <meta name="google-site-verification" content="4g9JLUN3sI1C2hYyOu-Qqq36mrkXVOVMGlOUcauNNf4">
        <title>postt.me</title>
        <link rel="stylesheet" href="css/default.css">
        <link rel="stylesheet" href="css/post.css">
        <link rel="stylesheet" href="css/forms.css">
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
        <?php head(); ?>
    </head>
    <body>
        <?php navbar("/") ?>
        
        <main>
            
        <div id="top-posts" class="section">
            <h1>Hot posts of the week:</h1>
            <?php
            displayPreviewsBulk($posts);
            ?>
        </div>
        
        </main>

        <script>
        if (window.location.hostname == "31.220.62.53") {
            alert("The website has been moved to http://www.postt.me! Taking you there now...");
            window.location.replace("http://postt.me");
        }
        </script>
    </body>
</html>
