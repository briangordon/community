<?php
define("indirect","prefs");
require("../common.php");
pushHeader();

if(!ctype_alnum($_SERVER["QUERY_STRING"]))
	dieGracefully("Usernames can contain only alphanumeric characters.");

$mysql = dbConnect();
$query = sprintf("SELECT * FROM prefs WHERE username = '%s'", $_SERVER["QUERY_STRING"]);
$result = doQuery($query, $mysql) or dieGracefully("could not get user");

if(mysql_num_rows($result) == 0) dieGracefully("no such user");

$row = mysql_fetch_assoc($result);
echo "<b>" . $_SERVER["QUERY_STRING"] . "'s profile</b><br />";
echo "Real name: <br />" . unsanitize($row["realname"]) . "<br /><br />";
echo "Bio: <br />" . unsanitize($row["bio"]) . "<br /><br />";

$query = sprintf("SELECT * FROM permissions WHERE username = '%s'", $_SERVER["QUERY_STRING"]);
$result = doQuery($query, $mysql) or dieGracefully("could not get user");
$row = mysql_fetch_assoc($result);
echo "Level: " . $row["perm"] . "<br />";

footer();
?>