<?php
define("indirect","prefs");
require("../common.php");

$failtext = "Change your settings below.";

if(isset($_POST["submitting"])) {
	$failtext = "";
	if(!ctype_alpha(str_replace(" ", "a", $_POST["realname"]))) $failtext .= "non alphabetic/spaces characters in real name";
}

if($failtext == "") {
	$mysql = dbConnect();
	$query = sprintf("UPDATE prefs SET bio = '%s', realname = '%s' WHERE username = '%s'", 
			sanitize($_POST["body"], $mysql), 
			sanitize($_POST["realname"], $mysql), //sanitize anyway
			$_SESSION["username"]);
	$result = doQuery($query, $mysql) or dieGracefully("could not update prefs");
	pushHeader("prefs/");
	echo "Settings updated! Please wait while you are redirected, or just <a href=\"" . $options["path"] . "/prefs/\">click here</a>.";
} else {
	pushHeader();
	$mysql = dbConnect();
	$query = sprintf("SELECT * FROM prefs WHERE username = '%s'", $_SESSION["username"]);
	$result = doQuery($query, $mysql) or dieGracefully("could not get prefs");
	$row = mysql_fetch_assoc($result);
	echo $failtext;
	echo "<form method=\"POST\" action=\"" . $options["path"] . "/prefs/\"><input type=\"hidden\" name=\"submitting\" />";
	echo "Bio:<br /><textarea cols=\"20\" rows=\"5\" name=\"body\">" . unsanitize($row["bio"]) . "</textarea><br />";
	echo "Real name:<br /><input type=\"text\" name=\"realname\" value=\"" . unsanitize($row["realname"]) . "\" /><br />";
	echo "<br /><input type=\"submit\" value=\"Submit\" /></form>";
}

footer();
?>