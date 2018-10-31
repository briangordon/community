<?php
define("indirect","watches");
require("../../common.php");

if(!ctype_digit($_SERVER["QUERY_STRING"])) dieGracefully("non-numerical watch ID");

$mysql = dbConnect();
$query = sprintf("UPDATE watches SET deleted = '1' WHERE tid = '%s' AND subscriber = '%s'", 
		$_SERVER["QUERY_STRING"], 
		$_SESSION["username"]);
$result = doQuery($query, $mysql) or dieGracefully("Couldn't delete from database");

pushHeader("watches/");
echo "Watch deleted. Please wait while you are redirected, or just <a href=\"" . $options["path"] . "/watches/\">click here</a>.";

footer();
?>