<?php
define("indirect","boards");
require("../../common.php");

if(!ctype_digit($_SERVER["QUERY_STRING"])) dieGracefully("non-numerical board ID");
if(PERMISSION < 3) dieGracefully("Not a high enough level");

$query = sprintf("UPDATE replies SET deleted = '1' WHERE tid IN (SELECT id AS tid FROM topics WHERE fid = '%s')", 
		$_SERVER["QUERY_STRING"]);
$result = doQuery($query, $mysql) or dieGracefully("Couldn't delete replies");

$query = sprintf("DELETE FROM messages WHERE tid IN (SELECT id AS tid FROM topics WHERE fid = '%s')", 
		$_SERVER["QUERY_STRING"]);
$result = doQuery($query, $mysql) or dieGracefully("Couldn't delete messages");

$query = sprintf("UPDATE watches SET deleted = '1' WHERE tid IN (SELECT id AS tid FROM topics WHERE fid = '%s')", 
		$_SERVER["QUERY_STRING"]);
$result = doQuery($query, $mysql) or dieGracefully("Couldn't delete watches");

$query = sprintf("UPDATE topics SET deleted = '1' WHERE fid = '%s'", 
		$_SERVER["QUERY_STRING"]);
$result = doQuery($query, $mysql) or dieGracefully("Couldn't delete topics");

$query = sprintf("UPDATE boards SET deleted = '1' WHERE id = '%s'", 
		$_SERVER["QUERY_STRING"]);
$result = doQuery($query, $mysql) or dieGracefully("Couldn't delete board");

pushHeader("boards/"); //redirect to boards

echo "Board deleted. Please wait while you are redirected, or just <a href=\"" . $options["path"] . "/boards/\">click here</a>.";

footer();
?>