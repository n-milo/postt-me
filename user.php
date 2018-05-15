<?php
require("php/db.php");
require("php/navbar.php");
require("php/get_post.php");

$user = $_GET["id"];
$user = strtolower($user);

$post_stmt = $conn->prepare("SELECT id FROM posts WHERE author = ? ORDER BY time_created DESC;");
$post_stmt->bind_param("s", $user);
$post_stmt->execute();
$post_stmt->bind_result($id);
$posts = [];
while ($post_stmt->fetch()) {
	array_push($posts, $id);
}
$post_stmt->close();

?>
<!DOCTYPE html>
<html lang="en-US">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=450">
		<meta name="description" content="<?php echo $user ?>'s profile on postt.me">
		<title><?php echo $user ?>'s Profile | postt.me</title>
		<link rel="stylesheet" href="css/default.css">
		<link rel="stylesheet" href="css/forms.css">
		<link rel="stylesheet" href="css/post.css">
		<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
        <?php head(); ?>
	</head>
	<body>
		<?php navbar(""); ?>
		
		<main>
		<div class="section">
			<h1><?php echo $user ?>'s Profile</h1>
		</div>
		<div class="section">
			<h1>Posts</h1>
			<?php
			displayPreviewsBulk($posts, false);
			?>
		</div>
		</main>
	</body>
</html>
