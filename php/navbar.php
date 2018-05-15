<?php
ini_set('display_errors', '1');
require("php/login_session.php");

class NavbarItem {
	public $href;
	public $showWhenLoggedIn;
	public $showWhenLoggedOut;

	public function __construct() {
		$this->href = "";
		$this->showWhenLoggedIn = true;
		$this->showWhenLoggedOut = true;
	}

	public function get_html($class) {
		return "";
	}
}

class NavbarLink extends NavbarItem {
	public $label;
	public $floatRight;
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

	public function get_html($class) {
		return "<a href='$this->href' $class $this->style>$this->label</a>";
	}
}

class NavbarSearchBox extends NavbarItem {
	public function get_html($class) {
		return "
		<span class='navbar-item' id='search-container'>
		<form action='search' id='search-form' method='get'>
			<input type='text' name='query' id='search-box' placeholder='Search...' />
			<button type='submit' id='search-button'><i class='material-icons'>search</i></button>
		</form>
		</span>
		";
	}
}

$navbarItems = array(
	new NavbarLink("/", "Home"),
	new NavbarSearchBox(),
	new NavbarLink("new_post", "Create New Post!", true, true, false),
	new NavbarLink("signup", "Sign Up", true, false),
	new NavbarLink("login", "Login", true, false),
);

function navbar($active) {
	global $navbarItems, $dropdownItems, $login, $username;
	$navbar = "
	<nav class='navbar'>
		<span class='navbar-item' style='padding:14px 16px;'>
			<img src='img/logo.png' alt='Logo' id='logo'>
			<span style='padding-left:48px'>postt.me</span>
		</span>
	";
	if ($login) {

		$dropdownItems = array(
			new NavbarLink("user?id=" . $username, "Profile"),
			new NavbarLink("logout", "Logout")
		);

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
		$navbar .= $item->get_html($class);
	}
	
	$navbar .= "</nav>";
	echo $navbar;
}

if (!$login) {
	rememberMe();
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
		}
	}
}

function head() {
?>

<script async src="https://www.googletagmanager.com/gtag/js?id=UA-115367757-1"></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', 'UA-115367757-1');
</script>

<?php
}

?>
