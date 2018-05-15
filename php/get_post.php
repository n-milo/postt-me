<?php
require("php/db.php");
require("php/login_session.php");
require("php/Parsedown.php");
date_default_timezone_set('America/Toronto');

$title = $body = $agotext = $author = $type = $filename = "";
$id = $views = 0;
$post_exists = false;

/*
Gets a post with the specified base32 ID and stores the data into the global variables
of $title, $body, $agotext, $author, $type, $filename, $id, and $views
*/
function get_post($post_id, $increment_views = true) {
	global $title, $body, $agotext, $author, $type, $filename, $tags, $id, $views, $conn, $login, $user_id, $comment_count,  $post_exists, $loop, $likes, $dislikes;
	$id = base_convert(strtolower($post_id), 32, 10);
	
	$stmt = $conn->prepare("SELECT type, title, body, author, tags, time_created FROM posts WHERE id = ?;");
	if (!$stmt) {
		echo "Error getting post: " . $conn->error;
		die();
	}
	$stmt->bind_param("i", $id);
	$stmt->execute();
	$stmt->bind_result($type, $title, $body, $author, $tags, $time_created_str);
	$stmt->fetch();
	$stmt->close();

	$likesql = "SELECT COUNT(rating) FROM likes WHERE rating = 1 AND post_id = $id";
	$result = $conn->query($likesql);
	$row = $result->fetch_assoc();
	$likes = $row["COUNT(rating)"];

	$dislikesql = "SELECT COUNT(rating) FROM likes WHERE rating = -1 AND post_id = $id";
	$result = $conn->query($dislikesql);
	$row = $result->fetch_assoc();
	$dislikes = $row["COUNT(rating)"];

	$tags = substr($tags, 1, -1);
	$tags = explode(",", $tags);

	if ($type == "" && $title == "" && $body == "" && $author == "" && $time_created_str == "") {
		$post_exists = false;
		return;
	} else {
		$post_exists = true;
	}
	
	if ($type == "image" || $type == "video") {
		if ($type == "video") {
			$filestmt = $conn->prepare("SELECT file, video_loop FROM posts WHERE id = ?;");
		} else {
			$filestmt = $conn->prepare("SELECT file FROM posts WHERE id = ?;");
		}
		$filestmt->bind_param("i", $id);
		$filestmt->execute();
		if ($type == "video") {
			$filestmt->bind_result($filename, $loop);
		} else {
			$filestmt->bind_result($filename);
		}
		$filestmt->fetch();
		$filestmt->close();
	}
	
	$created = new DateTime($time_created_str);//start time
	$agotext = get_ago($created);
	
	$view_stmt = $conn->prepare("SELECT COUNT(id) FROM views WHERE post_id = ?;");
	$view_stmt->bind_param("i", $id);
	$view_stmt->execute();
	$view_stmt->bind_result($views);
	$view_stmt->fetch();
	$view_stmt->close();
	
	if ($increment_views) {
		$views += 1;
		if ($login) {
			$view_stmt = $conn->prepare("INSERT INTO views (user_id, user_ip, post_id) VALUES (?, ?, ?);");
			$view_stmt->bind_param("ssi", $user_id, $_SERVER['REMOTE_ADDR'], $id);
		} else {
			$view_stmt = $conn->prepare("INSERT INTO views (user_ip, post_id) VALUES (?, ?);");
			$view_stmt->bind_param("si", $_SERVER['REMOTE_ADDR'], $id);
		}
		$view_stmt->execute();
		$view_stmt->close();
	}

	$comment_count = 0;
	$commstmt = $conn->prepare("SELECT COUNT(id) FROM comments WHERE post_id = ?");
	$commstmt->bind_param("i", $id);
	$commstmt->execute();
	$commstmt->bind_result($comment_count);
	$commstmt->fetch();
	$commstmt->close();
}

function get_comments($id) {
	global $conn;
	$stmt = $conn->prepare("SELECT id, username, content, time_created FROM comments WHERE post_id = ? ORDER BY time_created DESC;");
	$stmt->bind_param("i", $id);
	$stmt->execute();
	$result = $stmt->get_result();
	$stmt->close();
	return $result;
}

function get_ago($timestamp) {
	$now = new DateTime();//end time
	$interval = $timestamp->diff($now);
	
	$ago_amt = 0;
	$ago_txt = "";
	
	$years = $interval->y;
	$months = $interval->m;
	$days = $interval->d;
	$hours = $interval->h;
	$minutes = $interval->i;
	$seconds = $interval->s;
	if ($years > 0) { $ago_txt = "year"; $ago_amt = $years; }
	elseif ($months > 0) {
		$ago_txt = "month";
		$ago_amt = $months;
	}
	elseif ($days > 0) {
		$ago_txt = "day";
		$ago_amt = $days;
	}
	elseif ($hours > 0) {
		$ago_txt = "hour";
		$ago_amt = $hours;
	}
	elseif ($minutes > 0) {
		$ago_txt = "minute";
		$ago_amt = $minutes;
	}
	elseif ($seconds > 0) {
		$ago_txt = "second";
		$ago_amt = $seconds;
	} else {
		return "just now";
	}
	
	if ($ago_amt != 1) {
		$ago_txt = $ago_txt . "s";
	}
	
	return $ago_amt . " " . $ago_txt . " ago";
}

function displayPreview($post_id, $show_author = true) {
	global $title, $body, $agotext, $author, $type, $filename, $id, $views, $conn, $login, $user_id, $post_exists, $comment_count;
	get_post($post_id, false);
	?>
	<div class="post-preview">
			<?php if ($type == "image" || $type == "video") {
				if ($type == "image") {
					$path = $filename;
				} else if ($type == "video") {
					$path = "uploads/thumbnail_$post_id.jpg";
				}
				list($width, $height) = getimagesize($path);
				if ($width <= 178) {
					$min = min($width, $height) * 2;
				} else {
					$min = min($width, $height) / 2;
				}
			?>
			<div class="thumbnail <?php echo $type ?>" style="background-image: url('<?php echo $path ?>'); background-size: <?php echo $min ?>px;">
			</div>
			<?php } else if ($type == "text" ) { ?>
			<i class="text-icon material-icons md-48" aria-label="Text Post">insert_comment</i>
			<?php } ?>
			<div class="content-container">
				<h2 style="margin-bottom: 0px;" class="post-title"><a href="post?id=<?php echo $post_id; ?>"><?php echo htmlspecialchars($title)?></a></h2>
				<small style="padding-bottom: 20px;">Posted <?php echo $agotext; if ($show_author) { echo " by <a href='user?id=$author'>$author</a>"; } ?> </small><br>
				<small><?php echo $views; ?> views, <?php echo $comment_count ?>  comments</small>
		
				<div class="body-preview">
				<?php echo htmlspecialchars($body); ?></div>
			</div>
	</div>
	<hr>
	<?php
}

function displayPreviewsBulk($posts, $show_author = true) {
	if (count($posts) == 0) {
	?>
	<p>No posts found!</p>
	<?php
	} else {
		foreach ($posts as $id) {
			$post_id = base_convert($id, 10, 32);
			displayPreview($post_id, $show_author);
		}
	}
}

?>
