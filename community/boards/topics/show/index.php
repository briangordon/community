<?php
define("indirect","boards");
require("../../../common.php");
pushHeader();

if(!ctype_digit($_SERVER["QUERY_STRING"])) dieGracefully("non-numerical topic ID");

$query = sprintf("SELECT * FROM (SELECT tid, subscriber FROM watches WHERE subscriber = '%s' AND watches.deleted = '0') AS watches RIGHT OUTER JOIN topics ON watches.tid = topics.id WHERE topics.id = '%d' AND topics.deleted = '0' ORDER BY topics.id DESC", 
		$_SESSION["username"],
		$_SERVER["QUERY_STRING"]);
$result = doQuery($query, $mysql) or dieGracefully("could not get topic");
if(mysql_num_rows($result) == 0) dieGracefully("No such topic!");
$row = mysql_fetch_assoc($result);

if($row["private"] == "1" && $row["subscriber"] != $_SESSION["username"])
	dieGracefully("You weren't invited to this topic.");

$tstarter = $row["starter"];

echo username($tstarter) . "<br>";
echo $row["subject"] . "<br>";
echo unsanitize($row["body"]) . "<br>";

if($row["subscriber"] == $_SESSION["username"])	
	echo "<a href=\"" . $options["path"] . "/watches/delete/?" . $_SERVER["QUERY_STRING"] . "\">Unwatch</a><br />";
else
	echo "<a href=\"" . $options["path"] . "/watches/new/?" . $_SERVER["QUERY_STRING"] . "\">Watch</a><br />";

if(PERMISSION >= 2 || $_SESSION["username"] == $tstarter) echo "<a href=\"" . $options["path"] . "/boards/topics/delete/?" . $row["id"] . "\">Delete</a>";
echo "<hr>";

$private = FALSE;
$pm = FALSE;

if($row["private"] == "1") {
	if($row["fid"] == "0") { 
		echo "This is a personal message. Only the original author and original recipient may have access. <br />";
		$pm = TRUE;
	} else {
		$private = TRUE;
		$query = sprintf("SELECT watches.* FROM topics,watches WHERE topics.id = '%d' AND watches.tid = '%d' AND watches.deleted = '0' ORDER BY topics.id DESC", 
				$_SERVER["QUERY_STRING"],
				$_SERVER["QUERY_STRING"]);
		$result = doQuery($query, $mysql) or dieGracefully("could not get topic");
		if(mysql_num_rows($result) == 0) dieGracefully("nobody is subscribing.. how are you seeing this?");

		echo "This is a private topic. The following users currently have access: ";
		while($row = mysql_fetch_assoc($result)) {
			$inviter[$row["subscriber"]] = $row["inviter"];
			echo $row["subscriber"] . " ";	
		}
		echo "<br />";
	}
}

$query = sprintf("SELECT * FROM replies WHERE tid = '%s' ORDER BY id ASC", $_SERVER["QUERY_STRING"]);
$result = doQuery($query, $mysql) or dieGracefully("could not get replies");
while($row = mysql_fetch_assoc($result)) {
	if($row["deleted"] == "0") {
		echo "<a name=\"" . $row["id"] . "\"></a>";
		echo username($row["starter"]);
		if($private && !($pm)) {
			if($row["starter"] == $tstarter) echo " (topic starter!)";
			else echo " (invited by " . $inviter[$row["starter"]] . ")";
		}
		echo "<br />" . unsanitize($row["body"]) . "<br />";
		if(PERMISSION >= 2 || $_SESSION["username"] == $row["starter"])
			echo "<a href=\"" . $options["path"] . "/boards/topics/reply/delete/?" . $row["id"] . "\">Delete</a>";
		echo "<hr>";
	}
}

echo "<a href=\"" . $options["path"] . "/boards/topics/reply/?" . $_SERVER["QUERY_STRING"] . "\">Reply</a>"; 
if($private && !($pm)) echo "<br /><a href=\"" . $options["path"] . "/boards/topics/invite/?" . $_SERVER["QUERY_STRING"] . "\">Invite</a>"; 

footer();
?>