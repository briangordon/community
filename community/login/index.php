<?php
define("indirect", "logout");
define("FROM_LOGIN", true);
require("../common.php");

$failtext = "Please log in below. ";
$unamefail = "";
$pwfail = "";

if(isset($_POST["username"]) && isset($_POST["password"])) {
	$failtext = "";
	if(!ctype_alnum($_POST["username"])) { $failtext .= "Usernames can contain only alphanumeric characters. "; $unamefail = "failed"; }
	if(strlen($_POST["username"]) > 15) { $failtext .= "Usernames cannot contain more than 15 characters. "; $unamefail = "failed"; }
	if(strlen($_POST["password"]) > 40) { $failtext .= "Passwords cannot contain more than 40 characters. "; $pwfail = "failed"; }
}

if($failtext == "") {
	$mysql = dbConnect();
	$query = sprintf("SELECT * FROM logins WHERE username='%s'", $_POST["username"]);
	$result = doQuery($query, $mysql) or dieGracefully("could not get userlist");
	if(mysql_num_rows($result) == 0) { $failtext = "No matching username found. "; $unamefail = "failed"; }
	else {
		$row = mysql_fetch_assoc($result);
		if($row["password"] == md5($row["salt"] . $_POST["password"])) doLogin($_POST["username"]);
		else { $failtext = "Incorrect password for this username. "; $pwfail = "failed"; }
	}
}

pushHeader();

echo '<h2><span style="color:#f00">Welcome to the demo. You can easily register a new 
account, or log in with 
the username/pass <i>demo/demo</i></span></h3>';
echo "<form method=\"POST\" action=\"" . $options["path"] . "/login/\">";
echo $failtext . "<br />";
echo "<table border=\"0\">";
echo "<tr><td>Username</td><td><input type=\"text\" name=\"username\" class=\"" . $unamefail . "\" /></td></tr>";
echo "<tr><td>Password</td><td><input type=\"password\" name=\"password\" class=\"" . $pwfail . "\" /></td></tr>";
echo "<tr><td>&nbsp;</td><td><input type=\"submit\" name=\"submit\" value=\"Log In\" />&nbsp;<a href=\"register/\">Register</a></td></tr>";
echo "</table>";
echo "</form>";

footer();
?>
