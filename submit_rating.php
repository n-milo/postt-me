<?php
require("php/db.php");
require("php/login_session.php");

ini_set('display_errors', '1');

echo "{";

$errorMsg = "";

if (!isset($_POST["rating"])) {
	$errorMsg = "invalid rating value";
}

if (!isset($_POST["post_id"])) {
	$errorMsg = "invalid post_id value";
}

if (!$login) {
	$errorMsg = "You must be logged in to rate this post!";
}

if ($errorMsg != "") {
	echo '"errors":true,"errorMsg":"' . $errorMsg . '","likeChange":0,"dislikeChange":0}';
	exit;
}

$rating = 0;
if ($_POST["rating"] == -1) {
	$rating = -1;
} else {
	$rating = 1;
}
$id = $_POST["post_id"];

$count = $conn->prepare("SELECT rating FROM likes WHERE username = ? AND post_id = ?");
$count->bind_param("si", $username, $id);
$count->execute();
$count->bind_result($currentRating);
$count->fetch();
$count->close();

if ($currentRating == $rating) {
	echo '"errors":false,"likeChange":0,"dislikeChange":0}';
	exit;
}

$likeChange = $dislikeChange = 0;

if (!is_null($currentRating)) {
	if ($currentRating == 1) {
		$likeChange = -1;
	} else {
		$dislikeChange = -1;
	}


	$stmt = $conn->prepare("UPDATE likes SET rating = ? WHERE username = ? AND post_id = ?");
} else {
	$stmt = $conn->prepare("INSERT INTO likes (rating, username, post_id) VALUES (?, ?, ?)");
}
if ($conn->error) {
	$errorMsg = $conn->error;
}
$stmt->bind_param("isi", $rating, $username, $id);
$stmt->execute();
$stmt->close();

if ($errorMsg == "") {
	echo '"errors":false,';
} else {
	echo '"errors":true,"errorMsg":"' . $errorMsg . '","likeChange":0,"dislikeChange":0}';
	exit;
}

echo '"likeChange":';
if ($rating == 1) {
	echo "1";
} else {
	echo $likeChange;
}
echo ',"dislikeChange":';
if ($rating == -1) {
	echo "1";
} else {
	echo $dislikeChange;
}

echo "}";

?>