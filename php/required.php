<?php

require("php/login_session.php");

class NavbarItem
{
	public $href;
	public $label;
	public $floatRight;
	public $showWhenLoggedIn;
	public $showWhenLoggedOut;
	public $style = "";
	
	public function __construct($href, $label, $floatRight = false, $showWhenLoggedIn = true, $showWhenLoggedOut = true) {
		$this->href = $href;
		$this->label = $label;
		$this->floatRight = $floatRight;
		$this->showWhenLoggedIn = $showWhenLoggedIn;
		$this->showWhenLoggedOut = $showWhenLoggedOut;
		
		if ($floatRight) {
			$this->style = "style='float:right;'";
		}
	}
}

$navbarItems = array(
	new NavbarItem("/", "Home"),
	new NavbarItem("new_post.php", "Create New Post!", true, true, false),
	new NavbarItem("signup.php", "Sign Up", true, false),
	new NavbarItem("login.php", "Login", true, false),
);

$dropdownItems = array(
	new NavbarItem("profile.php", "Profile"),
	new NavbarItem("options.php", "Options"),
	new NavbarItem("logout.php", "Logout")
);

function navbar($active) {
	global $navbarItems, $dropdownItems, $login, $username;
	$navbar = "
	<nav class='navbar'><span class='navbar-item'><img src='img/logo.png' alt='Logo' id='logo'><span style='padding-left:48px'>MY COOL WEBSITE</span></span>"
	;
	if ($login) {
		$navbar .= "<div class='dropdown'><button class='dropbtn'>Hello, $username!</button><div class='dropdown-content'>";
		foreach ($dropdownItems as $item) {
			$navbar .= "<a href='$item->href'>$item->label</a>";
		}
		$navbar .= "</div></div>";
	}
	foreach ($navbarItems as $item) {
		if ($login and !$item->showWhenLoggedIn) { continue; }
		if (!$login and !$item->showWhenLoggedOut) { continue; }
		$class = "";
		if ($active == $item->href) { $class = "class='active'"; } 
		$navbar .= "<a href='$item->href' $class $item->style>$item->label</a>";
	}
	
	$navbar .= "</nav>";
	echo $navbar;
}

if (!$login) {
	//rememberMe();
}

function rememberMe() {
	$cookie = isset($_COOKIE['stayLoggedIn']) ? $_COOKIE['stayLoggedIn'] : '';
	if ($cookie) {
		require("php/login_helper.php");
		list ($user, $token, $mac) = explode(':', $cookie);
		if (!hash_equals(hash_hmac('sha256', $user . ':' . $token, SECRET_KEY), $mac)) {
			return false;
		}
		$usertoken = fetchTokenByUserID($user);
		if (hash_equals($usertoken, $token)) {
			// success
			// logInUser($user)
			return true;
		}
	}

	return false;
}