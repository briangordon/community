<?php
define("indirect","inbox");
require("../common.php");
pushHeader();

$query = sprintf("SELECT id FROM messages WHERE `read` = '0' AND `recipient` = '%s'", $_SESSION["username"]); //read is apparently reserved
$result = doQuery($query, $mysql) or dieGracefully("could not get read messages");
$msgs = mysql_num_rows($result);
echo "You have <b>" . $msgs . " new messages</b> and ";

$query = sprintf("SELECT * FROM (SELECT topics.body AS tbody, replies.body AS rbody, replies.starter AS rstarter, topics.subject, topics.starter AS tstarter, messages.tid, messages.rid, messages.read FROM replies, topics, messages WHERE recipient = '%s' AND topics.id = messages.tid AND messages.rid = replies.id ORDER BY messages.tid DESC, messages.rid ASC) AS bigq LEFT OUTER JOIN watches ON bigq.tid = watches.tid WHERE watches.subscriber = '%s' AND watches.deleted = '0'", 
		$_SESSION["username"],
		$_SESSION["username"]);
$result = doQuery($query, $mysql) or dieGracefully("could not get messages");
$msgs2 = mysql_num_rows($result);
echo $msgs2 - $msgs . " old messages. <br />";

if(mysql_num_rows($result) == 0) echo "Use this panel to communicate with other users as well as recieve notifications and invites.\n";

$lasttid = -1;
while($row = mysql_fetch_assoc($result)) {
	if($row["tid"] != $lasttid) echo "<br /><a href=\"" . $options["path"] . "/boards/topics/show/?" . $row["tid"] . "\">" . $row["subject"] . "</a> by " . username($row["tstarter"]) . " <a href=\"" . $options["path"] . "/boards/topics/reply/?" . $row["tid"] . "\">(reply)</a> <a href=\"" . $options["path"] . "/inbox/delete/?" . $row["tid"] . "\">(delete)</a><br>";
	$lasttid = $row["tid"];

	if($row["rid"] == "-2") {
		echo "Invited: ";
		echo username($row["inviter"]) . " ";
		if($row["read"] == 0) echo "<span class=\"bold\">";
		echo "invited you!";
		if($row["read"] == 0) echo "</span>";

		echo "<br />";
	} elseif($row["rid"] == "-1") {
		echo "Message: ";

		if($row["read"] == 0) echo "<span class=\"bold\">";
		echo "<a href=\"" . $options["path"] . "/boards/topics/show?" . $row["tid"] . "#top\">";
		echo unsanitize($row["tbody"]);
		echo "</a>";
		if($row["read"] == 0) echo "</span>";
		echo "<br />";

	} else {
		echo "Reply: ";

		if($row["read"] == 0) echo "<b>";
		echo "<a href=\"" . $options["path"] . "/boards/topics/show?" . $row["tid"] . "#" . $row["rid"] . "\">" . unsanitize($row["rbody"]) . "</a>";
		if($row["read"] == 0) echo "</b>";

		echo " by " . username($row["rstarter"]) . "<br />";
	}
}

$query = sprintf("UPDATE messages SET `read` = '1' WHERE recipient = '%s'", $_SESSION["username"]);
$result = doQuery($query, $mysql) or dieGracefully("could not set messages as read");

footer();
?>