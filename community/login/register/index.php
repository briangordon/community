<?php
define("indirect", "logout");
define("FROM_LOGIN", true);
require("../../common.php");

$failtext = "Please select a username and password and confirm the password. ";
$unamefail = "";
$pwfail = "";

if(isset($_POST["username"]) && isset($_POST["password"])) {
	$failtext = "";
	if(!ctype_alnum($_POST["username"])) { $failtext .= "Usernames can contain only alphanumeric characters. "; $unamefail = "failed"; }
	if(strlen($_POST["username"]) > 15) { $failtext .= "Usernames cannot contain more than 15 characters. "; $unamefail = "failed"; }
	if(strlen($_POST["password"]) > 40) { $failtext .= "Passwords cannot contain more than 40 characters. "; $pwfail = "failed"; }
	if(strlen($_POST["username"]) < 4) { $failtext .= "Usernames must contain at least 4 characters. "; $unamefail = "failed"; }
	if(strlen($_POST["password"]) < 4) { $failtext .= "Passwords must contain at least 4 characters. "; $pwfail = "failed"; }
} 

if($failtext == "") {
	$failtext = "The lengths and character types of your username and password are OK. ";
	if($_POST["password"] != $_POST["password2"]) { $failtext .= "The passwords you entered don't match!"; $pwfail = "failed"; }
	else { 
		$mysql = dbConnect();
		$query = sprintf("SELECT username FROM logins WHERE username = '%s'",
				$_POST["username"]);
		$result = doQuery($query, $mysql);
		if(mysql_num_rows($result) > 0) { $failtext .= "The username <b>" . $_POST["username"] . "</b> is already taken. "; $unamefail = "failed"; }
		else {
			$salt = rand(0,255);
			$query = sprintf("INSERT INTO logins VALUES ('%s','%s','%d')",
					$_POST["username"], 
					md5($salt . $_POST["password"]), 
					$salt);
			$query2 = sprintf("INSERT INTO prefs VALUES ('%s','','')",
					$_POST["username"]);
			$query3 = sprintf("INSERT INTO permissions VALUES ('%s','0')",
					$_POST["username"]);
			if(doQuery($query, $mysql) && doQuery($query2, $mysql) && doQuery($query3, $mysql)) doLogin($_POST["username"]);
			else dieGracefully("Couldn't add user.");
		}
	}
}

pushHeader();

echo "<form method=\"POST\" action=\"" . $options["path"] . "/login/register/\">";
echo $failtext . "<br />";
echo "<table border=\"0\">";
echo "<tr><td>Username</td><td><input type=\"text\" name=\"username\" class=\"" . $unamefail . "\" /></td></tr>";
echo "<tr><td>Password</td><td><input type=\"password\" name=\"password\" class=\"" . $pwfail . "\" /></td></tr>";
echo "<tr><td>&nbsp;</td><td><input type=\"password\" name=\"password2\" class=\"" . $pwfail . "\" /></td></tr>";
echo "<tr><td>&nbsp;</td><td><input type=\"submit\" name=\"submit\" value=\"Register\" /></td></tr>";
echo "</table>";
echo "</form>";

footer();
?>