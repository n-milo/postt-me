<?php

require("php/navbar.php");
require("php/get_post.php");

if ($_GET) {
	$query = isset($_GET["query"]) ? $_GET["query"] : "";

	// the exact term matches is found in the title
	$scoreFullTitle = 6; 
	// match the title in part
	$scoreTitleKeyword = 5;
	// the exact term matches is found in the content
	$scoreFullDocument = 4;
	// match the document in part
	$scoreDocumentKeyword = 3;
	// matches a tag
	$scoreTagKeyword = 3;

	
	$query = limitChars($query);
	$keywords = filterSearchKeys($query);
	$escQuery = $conn->escape_string($query);
	$titleSQL = array();
	$docSQL = array();
	$tagSQL = array();

	if (count($keywords) > 1) {
		$titleSQL[] = "if (title LIKE '%$escQuery%', {$scoreFullTitle}, 0)";
		$docSQL[] = "if (body LIKE '%$escQuery%', {$scoreFullDocument}, 0)";
	}

	foreach ($keywords as $key) {
		$titleSQL[] = "if (title LIKE '%" . $conn->escape_string($key) . "%', {$scoreTitleKeyword}, 0)";
		$docSQL[] = "if (body LIKE '%" . $conn->escape_string($key) . "%', {$scoreDocumentKeyword}, 0)";
		$tagSQL[] = "if (tags LIKE '%," . $conn->escape_string($key) . ",%', {$scoreTagKeyword}, 0)";
	}
	if (empty($titleSQL)) {
		$titleSQL[] = 0;
	}
	if (empty($docSQL)) {
		$docSQL[] = 0;
	}
	if (empty($urlSQL)) {
		$urlSQL[] = 0;
	}

	$titleImploded = implode(" + ", $titleSQL);
	$docImploded = implode(" + ", $docSQL);
	$tagImploded = implode(" + ", $tagSQL);

	$sql = "
	SELECT posts.id, (
		($titleImploded)+
		($docImploded)+
		($tagImploded)
	) as relevance, (
		SELECT COUNT(views.id)
		FROM views
		WHERE views.post_id = posts.id
	) as view_count
	FROM posts
	HAVING relevance > 0
	ORDER BY relevance DESC, view_count DESC
	LIMIT 25
	";

	$result = $conn->query($sql);
	if (!$result) {
		die("<b>MySQL Error: </b>" . $conn->error);
	}
	$posts = [];
	while ($row = $result->fetch_assoc()) {
		array_push($posts, $row["id"]);
	}
}

function filterSearchKeys($query) {
	$query = trim(preg_replace("/(\s+)+/", " ", $query));
	$words = array();
	$common_words = array("in","it","a","the","of","or","I","you","he","me","us","they","she","to","but","that","this","those","then");
	$c = 0;
	foreach(explode(" ", $query) as $key) {
		if (in_array($key, $common_words)) {
			continue;
		}
		$words[] = $key;
		if ($c >= 15) {
			break;
		}
		$c++;
	}
	return $words;
}

function limitChars($query, $limit = 200) {
	return substr($query, 0, $limit);
}

?>

<!DOCTYPE html>
<html lang="en-US">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=450">
		<title>Results for <?php echo(htmlspecialchars($query)) ?> | postt.me</title>
		<link rel="stylesheet" href="css/default.css">
		<link rel="stylesheet" href="css/index.css">
		<link rel="stylesheet" href="css/post.css">
		<link rel="stylesheet" href="css/forms.css">
		<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
	</head>
	<body>
		<?php navbar("/") ?>
		
		<main>
			
		<div id="top-posts" class="section">
			<h1>Results for "<?php echo htmlspecialchars($query); ?>" </h1>
			<?php
			displayPreviewsBulk($posts);
			?>
		</div>
		
		</main>
	</body>
</html>
