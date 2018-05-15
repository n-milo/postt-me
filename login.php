<?php
require("php/navbar.php");
require("php/db.php");

$username = $password = "";
$error = false;
if ($_SERVER["REQUEST_METHOD"] == "POST") {
	login();
}

function login() {
	global $username, $password, $error, $conn;
	$username = $_POST["username"];
	$password = $_POST["password"];
	
	if ($username == "" or $password == "") {
		$error = true;
		return;
	}
	
	if ($stmt = $conn->prepare("SELECT password, id FROM users WHERE username = ?;")) {
		$stmt->bind_param("s", $username);
		$stmt->execute();
		$stmt->bind_result($hash, $user_id);
		
		if ($stmt->fetch()) {
			$stmt->close();
			
			if (password_verify($password, $hash)) {
				// correct password
				
				session_start();
				$_SESSION["login"] = true;
				$_SESSION["username"] = $username;
				$_SESSION["user_id"] = $user_id;
				
				header('Location: /');
				die();
			} else {
				// incorrect password!
				$error = true;
				return;
			}
			
		} else {
			// no accounts with that username
			$error = true;
			return;
		}
	}
}

$conn->close();

function stayLoggedIn($user_id) {
	$token = generateRandomToken();
	storeTokenForUser($user_id, $token);
	$cookie = $user_id . ':' . $token;
	$mac = hash_hmac('sha256', $cookie, SECRET_KEY);
	$cookie .= ':' . $mac;

	// TODO: set correct expiry time, change params, etc.
	setcookie('stayLoggedIn', $cookie);
}

function generateRandomToken() {

}

function storeTokenForUser($user_id, $token) {

}

?>

<!DOCTYPE html>
<html lang="en-US">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=450">
		<meta name="description" content="Log in to postt.me">
		<title>Log In | postt.me</title>
		<link rel="stylesheet" href="css/default.css">
		<link rel="stylesheet" href="css/forms.css">
		<style>
			td {
				padding-bottom: 5px;
				padding-right: 10px;
			}
		</style>
	</head>
	<body>
		<?php navbar("login") ?>
		
		<main>
		<div class="section">
			<h1>Login!</h1>
		
			<form method="post">
			<p><input type="text" name="username" class="form-input" placeholder="Username"></p>
			<p><input type="password" name="password" class="form-input" placeholder="Password"></p>
			<input type="submit">
			</form>
			
			<?php if ($error) { ?>
			<p class='error'>Incorrect username or password.</p>
			<?php } ?>
			
			<p><small>Don't have an account? <a href="signup">Sign up!</a></small></p>
		</div>
		</main>
	</body>
</html>
