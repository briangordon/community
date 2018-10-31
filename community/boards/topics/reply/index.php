<?php
define("indirect","boards");
require("../../../common.php");

$bfailed = "";

$tid = 0;
if(isset($_POST["body"]) && isset($_POST["tid"]) && ctype_digit($_POST["tid"])) {
	$failtext = "";
	if(strlen($_POST["body"]) > 150) { $failtext .= "Replies cannot be longer than 150 characters. "; $bfailed = "failed"; }
	if(strlen($_POST["body"]) < 2) { $failtext .= "Replies cannot be shorter than 2 characters. "; $bfailed = "failed"; }
	$tid = $_POST["tid"];
} elseif(ctype_digit($_SERVER["QUERY_STRING"])) {
	$failtext = "Enter your reply below. ";
	$tid = $_SERVER["QUERY_STRING"]; //weird
} else dieGracefully("Not a valid topic ID. "); //make sure $tid doesn't get set without its value being a ctype_digit

$mysql = dbConnect();

$query = sprintf("SELECT topics.* FROM topics,watches WHERE topics.id = '%d' AND ( topics.private = '0' OR ( watches.tid = '%d' AND watches.subscriber = '%s' AND watches.deleted = '0' ) ) AND topics.deleted = '0'ORDER BY topics.id DESC", 
		$tid,
		$tid,
		$_SESSION["username"]);
$result = doQuery($query, $mysql) or dieGracefully("could not get topics");
if (mysql_num_rows($result) == 0) $failtext .= "Can't find that topic (or if it's private you're not invited!) ";
else {
	$row = mysql_fetch_assoc($result);
	$replyingto = $row["subject"];
	$replyingtoStarter = $row["starter"];

	if ($failtext == "") {
		$id = nextID("replies", $mysql);
		$query = sprintf("INSERT INTO replies VALUES ('%d','%d','%s','%s','0')",
			$id,
			$tid,
			sanitize($_POST["body"], $mysql),
			$_SESSION["username"]);
		$result = doQuery($query, $mysql) or dieGracefully("could not add new reply");

		$id2 = nextID("messages", $mysql);
		$query = sprintf("SELECT subscriber FROM watches WHERE tid = '%d'", $tid);
		$result = doQuery($query, $mysql) or dieGracefully("could not get subscribers");
		while($row = mysql_fetch_assoc($result)) {
			if($row["subscriber"] != $_SESSION["username"]) {
				$query = sprintf("INSERT INTO messages VALUES ('%d','%d','%d','%s','0')",
						$id2,
						$tid,
						$id,
						$row["subscriber"],
						0);
				doQuery($query, $mysql) or dieGracefully("could not add new reply");
				$id2++;
			}
		}
		pushHeader("boards/topics/show/?" . $tid);
		echo "Reply added. Please wait while you are redirected, or just " . topic($tid, "click here") . ".";
	}
}


if($failtext != "") {
	pushHeader();
	echo "Replying to " . topic($tid, $replyingto) . " by " . username($replyingtoStarter) . ".<br />";
	echo $failtext;
	echo "<br><form method=\"POST\" action=\"" . $options["path"] . "/boards/topics/reply/?" . $tid ."\">";
	echo "<input type=\"hidden\" name=\"tid\" value=\"" . $tid . "\" />";
	echo "Body (max 150 chars):<br /><textarea cols=\"20\" rows=\"5\" name=\"body\" class=\"" . $bfailed . "\"></textarea><br />";
	echo "<input type=\"submit\" value=\"Submit\" />";

}
footer();
?>