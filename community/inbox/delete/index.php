<?php
define("indirect","inbox");
require("../../common.php");

if(!ctype_digit($_SERVER["QUERY_STRING"])) dieGracefully("non-numerical message ID");

$query = sprintf("DELETE FROM messages WHERE tid = '%s' AND recipient = '%s'", $_SERVER["QUERY_STRING"], $_SESSION["username"]);
$result = doQuery($query, $mysql) or dieGracefully("Couldn't delete from database");

pushHeader("inbox/");
echo "Message deleted. Please wait while you are redirected, or just <a href=\"" . $options["path"] . "/inbox/\">click here</a>.";

footer();
?>