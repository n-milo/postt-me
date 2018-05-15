<?php require("php/navbar.php") ?>
<!DOCTYPE html>
<html lang="en-US">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=450">
	<meta name="description" content="Create a post on postt.me">
	<title>Create A Post | postt.me</title>
	<link rel="stylesheet" href="css/default.css">
	<link rel="stylesheet" href="css/new_post_type.css">
	<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>
	<?php navbar("new_post"); ?>
	<main>
	<div class="section">
		<h1>Choose post type</h1>
		<div id="type-buttons">
			<a href="submit_text">
				<i class="material-icons md-48">insert_comment</i>
				<p>Text</p>
			</a>
			<a href="submit_image">
				<i class="material-icons md-48">image</i>
				<p>Image</p>
			</a>
			<a href="submit_video">
				<i class="material-icons md-48">movie</i>
				<p>Video</p>
			</a>
		</div>
	</div>
	</main>
</body>
</html>
