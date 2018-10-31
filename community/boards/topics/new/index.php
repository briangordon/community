<?php
define("indirect","boards");
require("../../../common.php");

$sfailed = "";
$bfailed = "";
$ifailed = "";
$failtext = "Enter your discussion topic below: ";

if($_SERVER["QUERY_STRING"]=="") dieGracefully("No forum ID provided.");

if((isset($_POST["subject"]) || $_SERVER["QUERY_STRING"] == "0") && isset($_POST["body"])) {
	$failtext = "";
	if(!ctype_digit($_SERVER["QUERY_STRING"])) dieGracefully("Invalid board ID"); //DONT SOFT FAIL!

	if(strlen($_POST["body"]) > 150) { $failtext .= "Topic bodies cannot be longer than 150 characters. "; $bfailed = "failed"; }
	if(strlen($_POST["body"]) < 5) { $failtext .= "Topic bodies cannot be shorter than 5 characters. "; $bfailed = "failed"; }

	if($_SERVER["QUERY_STRING"] == "0") {
		if(!ctype_alnum($_POST["invite"])) dieGracefully("Invalid characters in invite field");
		if($_POST["invite"] == "") { $failtext .= "You must enter a valid username in the invite field. "; $ifailed = "failed"; }
		if(strlen($_POST["invite"]) > 15) { $failtext .= "Usernames cannot be longer than 15 characters. "; $ifailed = "failed"; }
		if($_POST["invite"] == $_SESSION["username"]) { $failtext .= "You can't invite yourself! "; $ifailed = "failed"; }
		$subject = "PM to " . $_POST["invite"];
		$private = 1;
	} else {
		if(!ctype_alnum(str_replace(" ", "0", $_POST["subject"]))) { $failtext .= "Topic subjects can contain only alphanumeric characters and spaces. "; $sfailed = "failed"; }
		if(strlen($_POST["subject"]) > 20) { $failtext .= "Topic subjects cannot be longer than 20 characters. "; $sfailed = "failed"; }
		if(strlen($_POST["subject"]) < 2) { $failtext .= "Topic subjects cannot be shorter than 2 characters. "; $sfailed = "failed"; }
		$subject = $_POST["subject"];
		$private = (isset($_POST["private"]) && $_POST["private"] == "on") ? 1 : 0;
	}
}

if($failtext == "") {
	$mysql = dbConnect();
	$query = sprintf("SELECT * FROM boards WHERE id = '%d'",
			$_SERVER["QUERY_STRING"]);
	$result = doQuery($query, $mysql) or dieGracefully("could not get boards list");
	if (mysql_num_rows($result) == 0) dieGracefully("no such board...");

	$query = sprintf("SELECT username FROM logins WHERE username = '%s'",
			$_POST["invite"]);
	$result = doQuery($query, $mysql) or dieGracefully("could not get users");
	if (mysql_num_rows($result) == 0 && $_SERVER["QUERY_STRING"] == "0") $failtext .= "That user doesn't exist! "; //painful
	else {
		$query = sprintf("SELECT * FROM topics WHERE subject = '%s'", 
				$subject);
		$result = doQuery($query, $mysql) or dieGracefully("could not get topics list");
		if (mysql_num_rows($result) > 0 && $_SERVER["QUERY_STRING"] != "0") { $failtext .= "A topic with this subject already exists. "; $sfailed = "failed"; } //painful
		else {
			$id = nextID("topics", $mysql);
	
			$query = sprintf("INSERT INTO topics VALUES ('%d','%d','%s','%s','%s','%d','0')",
					$id,
					$_SERVER["QUERY_STRING"],
					sanitize($subject, $mysql),
					sanitize($_POST["body"], $mysql),
					$_SESSION["username"],
					$private);
			$result = doQuery($query, $mysql) or dieGracefully("could not add new topic");

			if($private) {
				//give yourself access!!
				$id2 = nextID("watches", $mysql);
				$query = sprintf("INSERT INTO watches VALUES ('%d','%d','%s','%s','0')",
						$id2,
						$id,
						$_SESSION["username"],
						$_SESSION["username"]);
				doQuery($query, $mysql) or dieGracefully("couldn't add watch");
			}

			if($_SERVER["QUERY_STRING"] == "0") { 
					//notify the invited user
					$id3 = nextID("watches", $mysql);
					$query = sprintf("INSERT INTO watches VALUES ('%d','%d','%s','%s','0')",
							$id3,
							$id,
							$_POST["invite"],
							$_SESSION["username"]);
					doQuery($query, $mysql) or dieGracefully("couldn't add watch");

					$id3 = nextID("messages", $mysql);
					$query = sprintf("INSERT INTO messages VALUES ('%d','%d','%d','%s','0')",
							$id3,
							$id,
							-1,
							$_POST["invite"]);
					doQuery($query, $mysql) or dieGracefully("couldn't add message");
			}
	
			pushHeader("boards/topics/show/?" . $id);
			echo "Topic added. Please wait while you are redirected, or just <a href=\"" . $options["path"] . "/boards/topics/show/?" . $_SERVER["QUERY_STRING"] . "\">click here</a>.";
		}
	}

} 
if($failtext != "") {
	pushHeader();
	echo $failtext;
	echo "<br /><form method=\"POST\" action=\"" . $options["path"] . "/boards/topics/new/?" . $_SERVER["QUERY_STRING"] . "\">";

	if($_SERVER["QUERY_STRING"] == "0") { //PM board		
		echo "Body (max 150 chars):<br /><textarea cols=\"20\" rows=\"5\" name=\"body\" class=\"" . $bfailed . "\">" . $_POST["body"] . "</textarea><br />";
		echo "Private: <input type=\"checkbox\" name=\"private\" disabled=\"disabled\" checked=\"checked\" /><br />";
		echo "On the Private Messages board you must invite exactly one other user.<br />";
		echo "Invite: <input type=\"text\" name=\"invite\" maxlength=\"15\" class=\"" .  $ifailed . "\" /><br /><br />";
	} else {
		echo "Subject (max 20 chars): <input type=\"text\" name=\"subject\" maxlength=\"20\" class=\"" .  $sfailed . "\" value=\"" . unsanitize($subject) . "\" /><br />";
		echo "Body (max 150 chars):<br /><textarea cols=\"20\" rows=\"5\" name=\"body\" class=\"" . $bfailed . "\">" . unsanitize($_POST["body"]) . "</textarea><br />";
		echo "Private? <input type=\"checkbox\" name=\"private\" /><br /><br />";
	}

	echo "<input type=\"submit\" value=\"Submit\" />";
}

footer();

?>