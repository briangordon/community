<?php
define("indirect","boards");
require("../../common.php");
pushHeader();

if(!ctype_digit($_SERVER["QUERY_STRING"])) dieGracefully("non-numerical forum id");

$query = sprintf("SELECT * FROM (SELECT id AS wid, tid, subscriber, inviter FROM watches WHERE watches.subscriber = '%s' AND watches.deleted = '0') AS watches RIGHT OUTER JOIN topics ON watches.tid = topics.id WHERE topics.fid = '%d' AND topics.deleted = '0' ORDER BY topics.id DESC", 
		$_SESSION["username"],
		$_SERVER["QUERY_STRING"]);
$result = doQuery($query, $mysql) or dieGracefully("could not get topics & watches list");

while($row = mysql_fetch_assoc($result)) {
	if($row["private"] == "0" || $row["subscriber"] == $_SESSION["username"]) {
		echo "<ul class=\"listrow\">";
		echo "<a href=\"" . $options["path"] . "/boards/topics/show/?" . $row["id"] . "\" style=\"text-decoration: none\">";
		echo "<li class=\"rowhead\">";
		echo $row["subject"];
		echo "</li><li class=\"rowbody\">";
		echo unsanitize($row["body"]);
		echo "</li>";

		if(PERMISSION >= 2) echo "<li><a href=\"" . $options["path"] . "/boards/topics/delete/?" . $row["id"] . "\">(delete)</a></li>";

		echo "<li>";
		if($row["subscriber"] == $_SESSION["username"]) echo " <a href=\"" . $options["path"] . "/watches/delete/?" . $row["id"] . "\">(unwatch)</a>";
		else echo " <a href=\"" . $options["path"] . "/watches/new/?" . $row["id"] . "\">(watch)</a>";
		echo "</li>";

		if($row["starter"] == $_SESSION["username"]) echo "<li>(Yours!)</li>";
		elseif($row["private"] == 1) echo "<li>(Invited by " . username($row["inviter"]) . ")</li>";

		echo "<li>by " . username($row["starter"]) . "</li>";


		echo "</a></ul>";
	}
}

echo "<a href=\"" . $options["path"] . "/boards/topics/new/?" . $_SERVER["QUERY_STRING"] . "\">New Topic</a>";

footer();
?>