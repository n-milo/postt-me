<?php
require("php/navbar.php");
require("php/db.php");

$username = $password = $confirm = $email = "";
$errors = [];
if ($_SERVER["REQUEST_METHOD"] == "POST") {
	verify_data();
}

function verify_data() {
	global $username, $password, $confirm, $email, $errors, $conn;
	$username = $_POST["username"];
	$password = $_POST["password"];
	$confirm = $_POST["confirm"];
	$email = $_POST["email"];
	if (!isset($_POST["terms"]) || $_POST["terms"] != true) {
		array_push($errors, "You must agree to the terms and conditions.");
	}
	
	if (!preg_match("/^[A-Za-z0-9_]*$/", $username)) {
		array_push($errors, "Username must only contain letters, numbers, and underscores.");
	} elseif (strlen($username) < 4 or strlen($username) > 16) {
		array_push($errors, "Username must be 4 to 16 characters long.");
	}
	
	if (strlen($password) < 6) {
		array_push($errors, "Password must be at least 6 characters long.");
	}
	
	if ($password != $confirm) {
		array_push($errors, "Passwords must match.");
	}
	
	if ($email != "") {
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			array_push($errors, "Email must be valid.");
		} elseif (strlen($email) > 255) {
			array_push($errors, "Email must be less than 255 characters long.");
		}
	}
	
	// check if username exists in database
	if ($stmt = $conn->prepare("SELECT username FROM users WHERE username = ?;")) {
		$stmt->bind_param("s", $username);
		$stmt->execute();
		$result = $stmt->get_result();
		$stmt->close();
		if ($result->num_rows > 0) { // if there is at least 1 result
			array_push($errors, "Username is taken.");
		}
	}
	
	if (count($errors) > 0) {
		return;
	}
	
	$hash = password_hash($password, PASSWORD_DEFAULT);
	
	if ($email == "") {
		$stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?);");
		$stmt->bind_param("ss", $username, $hash);
	} else {
		$stmt = $conn->prepare("INSERT INTO users (username, password, email) VALUES (?, ?, ?);");
		$stmt->bind_param("sss", $username, $hash, $email);
	}
	
	$stmt->execute();
	$stmt->close();
	
	$conn->close();
	header('Location: signup_success');
	die();
}

$conn->close();

?>

<!DOCTYPE html>
<html lang="en-US">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=450">
		<meta name="description" content="Create an account on postt.me">
		<title>Sign Up | postt.me</title>
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
		<?php navbar("signup") ?>
		
		<main>
		<div class="section">
			<h1>Sign up for an account!</h1>
			<form method="post">
				<p><input type="text" name="username" value="<?php echo $username ?>" class="form-input" placeholder="Username"></p>
				<p><input type="password" name="password" value="<?php echo $password ?>" class="form-input" placeholder="Password"></p>
				<p><input type="password" name="confirm" value="<?php echo $confirm ?>" class="form-input" placeholder="Confirm Password"></p>
				<p><input type="text" name="email" value="<?php echo $email ?>" class="form-input" placeholder="Email (optional)"></p>
				<p><input type="checkbox" name="terms" value="true" style="font-size: 1em;" />I agree to the <a href="terms" target="_blank">terms and conditions</a>.</p>
				<input type="submit">
			</form>
			<?php
			if (count($errors) > 0) {
				echo "<ul class='error'>";
				foreach ($errors as $error) {
					echo "<li>$error</li>";
				}
				echo "</ul>";
			}
			?>
			<p><small>Already have an account? <a href="login">Sign in!</a></small></p>
		</div>
		</main>
	</body>
</html>
