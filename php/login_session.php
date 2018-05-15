<?php
if (session_status() == PHP_SESSION_NONE) {
	session_start();
}

$login = false;

if ($_SESSION) {
	if ($_SESSION["login"] === true){
		$login = true;
		$username = $_SESSION["username"];
		$user_id = $_SESSION["user_id"];
	}
}