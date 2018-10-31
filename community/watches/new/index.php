<?php
define("indirect","watches");
require("../../common.php");

if(!ctype_digit($_SERVER["QUERY_STRING"])) dieGracefully("non-numerical topic ID");

$query = sprintf("SELECT * FROM topics WHERE id = '%s' AND deleted = '0'", 
		$_SERVER["QUERY_STRING"]);
$result = doQuery($query, $mysql) or dieGracefully("could not get topic");
if(mysql_num_rows($result) == 0) dieGracefully("no such topic");

$row = mysql_fetch_assoc($result);
if($row["private"] == 1) dieGracefully("You can't invite yourself to a private topic!");

$query = sprintf("SELECT * FROM watches WHERE tid = '%s' AND subscriber = '%s' AND deleted = '0'", 
		$_SERVER["QUERY_STRING"], 
		$_SESSION["username"]);
$result = doQuery($query, $mysql) or dieGracefully("couldn't get watches");

pushHeader("boards/topics/show?" . $_SERVER["QUERY_STRING"]); //push the redirect whether or not they're already watching

if(mysql_num_rows($result) == 0) {
	$id = nextID("watches", $mysql);
	$query = sprintf("INSERT INTO watches VALUES ('%d','%d','%s','%s','0')",
			$id,
			$_SERVER["QUERY_STRING"],
			$_SESSION["username"],
			$_SESSION["username"]);
	doQuery($query, $mysql) or dieGracefully("couldn't add watch");
	echo "Watch added. Please wait while you are redirected, or just <a href=\"" . $options["path"] . "/boards/topics/show?" . $_SERVER["QUERY_STRING"] . "\">click here</a>.";
} else {
	echo "You're already watching that topic! Please wait while you are redirected, or just <a href=\"" . $options["path"] . "/boards/topics/show?" . $_SERVER["QUERY_STRING"] . "\">click here</a>.";
}
footer();
?>