<?php

require("php/db.php");
require("php/login_session.php");
ini_set('display_errors', '1');

$errors = [];
function make_post($type) {
	global $conn, $username, $errors;

	$title = $content = $tags = "";
	$title = $_POST["title"];
	$content = $_POST["content"];
	$tags = $_POST["tags"];

	if ($tags != "") {
		$tagsArr = explode(",", $tags);
		$tagsValid = true;
		foreach ($tagsArr as $tag) {
			if ($tag == "") {
				$tagsValid = false;
				break;
			}
		}

		if (!$tagsValid) {
			array_push($errors, "Invalid tags.");
		}
	}
	if ($title == "") {
		array_push($errors, "Title cannot be empty.");
	}
	if (strlen($title) > 100) {
		array_push($errors, "Title too long (100 characters max).");
	}
	if (strlen($content) > 50000) {
		array_push($errors, "Body too long (50000 characters max).");
	}
	
	if (count($errors) > 0) {
		return "_ERROR";
	}

	$tags = "," . $tags . ",";
	
	$id = generate_id();
	$post_id = base_convert($id, 10, 32);
	
	if ($type == "image" or $type == "video") {
		$filename = upload_file($post_id, $type);
		if ($type == "video") {
			if (isset($_POST["loop"]) && $_POST["loop"]) { $loop = 1; } else { $loop = 0; }
			$stmt = $conn->prepare("INSERT INTO posts (id, type, title, body, author, file, video_loop, tags, time_created) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
			$stmt->bind_param("isssssis", $id, $type, $title, $content, $username, $filename, $loop, $tags);
		} else {
			$stmt = $conn->prepare("INSERT INTO posts (id, type, title, body, author, file, tags, time_created) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
			$stmt->bind_param("issssss", $id, $type, $title, $content, $username, $filename, $tags);
		}
	} else {
		$stmt = $conn->prepare("INSERT INTO posts (id, type, title, body, author, tags, time_created) VALUES (?, ?, ?, ?, ?, ?, NOW())");
		$stmt->bind_param("isssss", $id, $type, $title, $content, $username, $tags);
	}
	
	$stmt->execute();
	
	$stmt->close();
	$conn->close();
	return $post_id;
}

function generate_id() {
	global $conn;
	$id = random_int(0, 1073741823);
	$sql = "SELECT id FROM posts WHERE id = $id";
	$result = $conn->query($sql);
	if ($result->num_rows > 0) {
		// that id already exists, reroll
		return generate_id();
	}
	return $id;
}

function upload_file($post_id, $type) {
	$file = $_FILES["userfile"];
	$ext = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
	$uploadfile = "uploads/$post_id.$ext";

	echo "<pre>";
	print_r($file);
	echo "</pre>";
	
	if (file_exists($uploadfile)) {
		throw new Exception("File already exists.");
	}
	if ($file['size'] >= 500000000) {
		throw new Exception("File too large.");
	}
	
	if ($type == "image") {
		if ($ext != "jpg" && $ext != "jpeg" && $ext != "png" && $ext != "gif") {
			throw new Exception("Invalid file format. (Only JPG, PNG, and GIF files allowed)");
		}
	} elseif ($type == "video") {
		if ($ext != "mp4") {
			throw new Exception("Invalid file format. (Only MP4 files allowed)");
		}
	}

	if (move_uploaded_file($file['tmp_name'], $uploadfile)) {
		if ($type == "video") {
			require("php/process_video.php");
			processVideo($post_id, $_SERVER["DOCUMENT_ROOT"] . "/$uploadfile");
		}

		return $uploadfile;
	} else {
		throw new Exception("Unknown error occurred while moving the file " . $file['tmp_name'] . " to " . $uploadfile);
	}
}
