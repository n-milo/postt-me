<?php
require("php/db.php");
require("php/navbar.php");
require("php/get_post.php");

$tag = $_GET["id"];
$tag = strtolower($tag);
$dbtag = $conn->escape_string($tag);

$query = "
SELECT COUNT(views.id) as view_count, posts.id 
FROM views, posts
WHERE views.post_id = posts.id
AND posts.time_created > DATE_SUB(NOW(), INTERVAL 7 DAY)
AND posts.tags LIKE '%,$dbtag,%'
GROUP BY posts.id
ORDER BY view_count DESC
";
$result = $conn->query($query);

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
		<meta name="description" content="#<?php echo $tag ?> on postt.me">
		<title>#<?php echo $tag ?> | postt.me</title>
		<link rel="stylesheet" href="css/default.css">
		<link rel="stylesheet" href="css/forms.css">
		<link rel="stylesheet" href="css/post.css">
		<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
	</head>
	<body>
		<?php navbar(""); ?>
		
		<main>
		<div class="section">
			<h1>Posts in #<?php echo $tag ?></h1>
			<?php
			displayPreviewsBulk($posts);
			?>
		</div>
		</main>
	</body>
</html>
