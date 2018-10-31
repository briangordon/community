<?php
define("indirect","boards");
require("../../../common.php");

$ifailed = "";
$failtext = "Enter the name of the person you would like to invite below: ";

if($_SERVER["QUERY_STRING"] == "") dieGracefully("No topic ID provided.");
if($_SERVER["QUERY_STRING"] == "0") dieGracefully("You can't invite another user to a personal message.");

if(isset($_POST["invite"])) {
	$failtext = "";
	if(!ctype_digit($_SERVER["QUERY_STRING"])) dieGracefully("Invalid topic ID"); //DONT SOFT FAIL, really look below
	if(!ctype_alnum($_POST["invite"])) dieGracefully("Invalid characters in invite field");
	if(strlen($_POST["invite"]) > 15) { $failtext .= "Usernames cannot be longer than 15 characters. "; $ifailed = "failed"; }
	if($_POST["invite"] == $_SESSION["username"]) { $failtext .= "You can't invite yourself! "; $ifailed = "failed"; }
}

if($failtext == "") {
	$query = sprintf("SELECT topics.* FROM topics,watches WHERE topics.id = '%d' AND ( topics.private = '0' OR ( watches.tid = '%d' AND watches.subscriber = '%s' AND watches.deleted = '0' ) ) AND topics.deleted = '0' ORDER BY topics.id DESC", 
			$_SERVER["QUERY_STRING"],
			$_SERVER["QUERY_STRING"],
			$_SESSION["username"]);
	$result = doQuery($query, $mysql) or dieGracefully("could not get topic");
	if(mysql_num_rows($result) == 0) dieGracefully("No such topic! (Or if it's private you weren't invited or you deleted your subscription)");
	$row = mysql_fetch_assoc($result);
	if($row["private"] == "0") dieGracefully("It's not even a private topic.");
	else {
		$query = sprintf("SELECT username FROM logins WHERE username = '%s'",
				$_POST["invite"]);
		$result = doQuery($query, $mysql) or dieGracefully("could not get users");
		if (mysql_num_rows($result) == 0) $failtext .= "That user doesn't exist! ";
		else {
			$query = sprintf("SELECT * FROM watches WHERE subscriber = '%s' AND tid = '%d' AND deleted = '0'",
					$_POST["invite"],
					$_SERVER["QUERY_STRING"]);
			$result = doQuery($query, $mysql) or dieGracefully("could not get watches");
			if (mysql_num_rows($result) > 0) $failtext .= "That user already has access to this topic. ";
			else {
				//notify the invited user
				$id = nextID("watches", $mysql);
				$query = sprintf("INSERT INTO watches VALUES ('%d','%d','%s','%s','0')",
						$id,
						$_SERVER["QUERY_STRING"],
						$_POST["invite"],
						$_SESSION["username"]);
				doQuery($query, $mysql) or dieGracefully("couldn't add watch");

				$id = nextID("messages", $mysql);
				$query = sprintf("INSERT INTO messages VALUES ('%d','%d','%d','%s','0')",
						$id,
						$_SERVER["QUERY_STRING"],
						-2,
						$_POST["invite"]);
				doQuery($query, $mysql) or dieGracefully("couldn't add message");

				pushHeader("boards/topics/show/?" . $_SERVER["QUERY_STRING"]);
				echo "User invited. Please wait while you are redirected, or just <a href=\"" . $options["path"] . "/boards/topics/show/?" . $_SERVER["QUERY_STRING"] . "\">click here</a>.";
			}
		}
	}
}

if($failtext != "") {
	pushHeader();
	echo $failtext;
	echo "<br /><form method=\"POST\" action=\"" . $options["path"] . "/boards/topics/invite/?" . $_SERVER["QUERY_STRING"] . "\">";
	echo "Invite: <input type=\"text\" name=\"invite\" maxlength=\"15\" class=\"" .  $ifailed . "\" /><br /><br />";
	echo "<input type=\"submit\" value=\"Submit\" />";
}

footer();
?>