<?php
ini_set('display_errors', '1');
require("php/navbar.php");
require("php/submit.php");
if (!$login) {
	header('Location: login');
	die();
}
if ($_POST) {
	$post_id = make_post("image");
	if ($post_id == "_ERROR") {
		return;
	}
	
	header('Location: post?id=' . $post_id);
	die();
}

?>
<!DOCTYPE html>
<html lang="en-US">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=450">
	<title>New Image Post | postt.me</title>
	<link rel="stylesheet" href="css/default.css">
	<link rel="stylesheet" href="css/forms.css">
	<link rel="stylesheet" href="css/new_post.css">
	<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>
	<?php navbar("new_post"); ?>
	
	<main>
	<div class="section">
		<h1>Create a post!</h1>
		<form method="post" enctype="multipart/form-data" id="post-form">
			<span style="float:right;"><span id="title-count">0</span>/100</span>
			Title<br>
			<input name="title" type="text" id="title-input" class="form-input full-width" maxlength="100">
			<p></p>

			<div id="file-zone">
				<button class="form-button" type="button" onclick="selectFiles()" id="file-select-button">Select an Image</button>
				<span id="selected-file">No file selected</span>
				<input type="file" id="file-upload" name="userfile"></input>
			</div><p></p>
			<div id="preview">
				<img id="preview-image"/>
			</div>

			<span style="float:right;"><span id="body-count">0</span>/50000</span>
			<span id="body-descriptor">Description (optional)</span><br>
			<textarea name="content" id="body-input" class="form-input full-width" maxlength="50000"></textarea>



			Tags<br>
			<div class="tags-input form-input" data-name="tags"></div>
		
			<input type="submit" value="Post!" class="full-width">
		
		</form>
		<?php if (count($errors) > 0) { ?>
			<ul class='error'>
				<?php
				foreach ($errors as $error) {
					echo "<li>$error</li>";
				}
				?>
			</ul>
		<?php	} ?>
	</div>
	</main>
	
	<script src="js/submit_text.js"></script>
	<script src="js/upload.js"></script>
</body>
</html>
