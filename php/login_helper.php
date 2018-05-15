<?php

const SECRET_KEY = "Z0lmDjB8v1BJubeXrB0PUh6mCKO2GRvZ";

function fetchTokenByUserID($user_id) {
	$result = $conn->$query("SELECT token FROM user_tokens WHERE user_id = $user_id");
	$token = $result->fetch_assoc()["token"];
	return $token;
}