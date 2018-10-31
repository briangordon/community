<?php
define("indirect","boards");
require("../../../../common.php");

if(!ctype_digit($_SERVER["QUERY_STRING"])) dieGracefully("non-numerical reply ID");
if(PERMISSION < 2) dieGracefully("Not a high enough level");

$query = sprintf("UPDATE replies SET deleted = '1' WHERE id = '%s'", 
		$_SERVER["QUERY_STRING"]);
$result = doQuery($query, $mysql) or dieGracefully("Couldn't delete replies");

$query = sprintf("DELETE FROM messages WHERE rid = '%s'", 
		$_SERVER["QUERY_STRING"]);
$result = doQuery($query, $mysql) or dieGracefully("Couldn't delete messages");

pushHeader("boards/");
echo "Reply deleted. Please wait while you are redirected, or just <a href=\"" . $options["path"] . "/boards/\">click here</a>.";

footer();
?>