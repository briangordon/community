<?php
define("indirect","watches");
require("../common.php");
pushHeader();

$mysql = dbConnect();
$query = sprintf("SELECT watches.tid AS tid, topics.subject AS subject, topics.starter AS starter FROM topics, watches WHERE watches.subscriber = '%s' AND watches.tid = topics.id AND watches.deleted = '0' ORDER BY watches.tid DESC", $_SESSION["username"]);
$result = doQuery($query, $mysql) or dieGracefully("could not get watches");
echo "You are watching " . mysql_num_rows($result) . " topics.<br />";
if(mysql_num_rows($result)==0) echo "Use this panel to track activity on the Boards and manage your access to private topics.\n";
while($row = mysql_fetch_assoc($result)) {
	echo "<a href=\"" . $options["path"] . "/boards/topics/show/?" . $row["tid"] . "\"><ul class=\"listrow\"><li class=\"rowhead\">" . $row["subject"] . "</li>";
	echo "<li class=\"rowtail\">(by " . username($row["starter"]) . ")</li>";
	echo "<li><a href=\"" . $options["path"] . "/watches/delete/?" . $row["tid"] . "\">(delete)</a></li></ul></a>";
}

footer();
?>