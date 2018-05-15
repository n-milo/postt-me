<?php
require("php/navbar.php");
require("php/get_post.php");
$post_id = $_GET["id"];
get_post($post_id, true, true);
//$likebarWidth = $likes / ($likes + $dislikes) * 100;

if ($_POST) {
	if (isset($_POST["comment"]) && $_POST["comment"] != "" && $login) {
		$comment = $_POST["comment"];
		if (strlen($comment) > 0) {
			$stmt = $conn->prepare("INSERT INTO comments (username, content, post_id, time_created) VALUES (?,?,?, NOW());");
			$stmt->bind_param("ssi", $username, $comment, $id);
			$stmt->execute();
			$stmt->close();
		} else {
			die("Illegal comment: Too short.");
		}
	}

	$request = $_SERVER['REQUEST_URI'];
	
	header("Location: $request");
	die();
}

$count = $conn->prepare("SELECT rating FROM likes WHERE username = ? AND post_id = ?");
$count->bind_param("si", $username, $id);
$count->execute();
$count->bind_result($currentRating);
$count->fetch();
$count->close();

$comments = get_comments($id);

$conn->close();
?>
<!DOCTYPE html>
<html lang="en-US">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=450">
		<meta name="description" content="<?php echo substr(htmlspecialchars($body), 0, 300); ?>">
		<meta name="author" content="<?php echo htmlspecialchars($author) ?>">
		<title><?php echo htmlspecialchars($title) ?> | postt.me</title>
		<link rel="stylesheet" href="css/default.css">
		<link rel="stylesheet" href="css/forms.css">
		<link rel="stylesheet" href="css/post.css">
		<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
		<?php if ($type == "video") {?>
		<link href="http://vjs.zencdn.net/6.6.3/video-js.css" rel="stylesheet">
		<link href="css/video.css" rel="stylesheet">
		<?php } ?>
        <?php head(); ?>
	</head>
	<body>
		<?php navbar(""); ?>
		
		<main>
		<?php if ($post_exists) { ?>
		<div class="section post">
			<h1 style="margin-bottom: 0px;" class="post-title"><?php echo htmlspecialchars($title) ?></h1>
			<small style="padding-bottom: 20px;">Posted <?php echo $agotext ?> by
				<a href=<?php echo "'user?id=" . htmlspecialchars($author) . "'>" .  htmlspecialchars($author)?></a></small>
				<p>
			<?php if ($type == "image") {?>
			<img src='<?php echo $filename ?>' style='width:75%' alt="<?php echo htmlspecialchars($body) ?>" />
			<?php } else if ($type == "video") { ?>

			<video id="my-video" class="video-js" controls autoplay <?php if ($loop) echo "loop "; ?>preload="auto" width="960" height="540" style="max-width:100%;" data-setup="{}">
				<source src="<?php echo $filename ?>" type='video/mp4'>
			</video>

			<?php }
			$parsedown = new Parsedown();
			echo $parsedown->text(htmlspecialchars($body));
			?>
			</p>

			<form class="like-section" id="like-section" method="post">
				<div class="like-button">
					<a href="#" id="like-post-button"><i class="material-icons">thumb_up</i></a><br>
					<small id="like-count"><?php echo $likes ?></small>
				</div>
				<div class="like-bar">
					<div class="likes" id="likes"></div>
				</div>
				<div class="like-button">
					<a href="#" id="dislike-post-button"><i class="material-icons">thumb_down</i></a><br>
					<small id="dislike-count"><?php echo $dislikes ?></small>
				</div>
				<input type="hidden" name="rating_type" id="rating">
			</form>
			<p>
			<small>
				<?php echo $views ?> views<br>
				Tags: <span class="tags">
				<?php
				for ($i = 0; $i < count($tags); $i++) {
					$tag = $tags[$i];
					echo "<a href='tag?id=$tag'>$tag</a>";
					if ($i != count($tags) - 1) {
						echo ", ";
					}
				}
				?>
				</span>
			</small>
			</p>
		</div>
		<div class="section comments">
			<h3><?php echo $comments->num_rows; ?> comments</h3>
			<?php if ($login) { ?>
			<form method="post">
				<textarea name="comment" class="form-input full-width" style="height:80px;" oninput="textAreaChange()" id="comment-box"></textarea>
				<br><input type="submit" value="Post Comment" id="submit-button"disabled>
			</form>
			<?php } ?>
			
			<?php while ($comment = $comments->fetch_assoc()) {
				$ago = get_ago(new DateTime($comment['time_created']));
				$username = $comment['username'];
				$content = htmlspecialchars($comment['content']);
			?>
				<div class="comment">
					<a href='user?id=<?php echo $username ?>'><?php echo $username ?></a>
					<small><?php echo $ago ?></small>
					<p><?php echo htmlspecialchars($content) ?></p>
				</div>
			<?php } ?>

		</div>
		<?php } else { ?>
		<div class="section">
			<h1>Sorry! That post doesn't exist.</h1>
			<p>It might have been deleted, made private, or moved somewhere else.</p>
			<a href="/">Back to home page</a>
		</div>
		<?php } ?>
		</main>

		<script>
			var login = <?php echo $login ? "true" : "false" ?>;
			var post_id = <?php echo $id ?>;
			var currentRating = <?php if (!is_null($currentRating)) { echo $currentRating; } else { echo "0"; } ?>;
		</script>
		<script>
		var commentBox = document.getElementById('comment-box');
		var submitButton = document.getElementById('submit-button');
		function textAreaChange() {
			if (commentBox.value.length > 0) {
				submitButton.removeAttribute('disabled');
			} else {
				submitButton.setAttribute('disabled', 'disabled');
			}
		}
		</script>
		<?php if ($type == "video") { ?>
		<script src="http://vjs.zencdn.net/6.6.3/video.js"></script>
		<?php } ?>
		<script src="js/post.js"></script>
	</body>
</html>
