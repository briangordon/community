<?php
define("indirect","boards");
require("../../common.php");

if(PERMISSION < 3) dieGracefully("Not a high enough level");

$failtext = "Enter a name: ";
$nfailed = "";
$dfailed = "";

if(isset($_POST["name"])) {
	$failtext = "";
	if(!ctype_alpha(str_replace(" ", "a", $_POST["name"]))) { $failtext .= "Board names can contain only letters and spaces. "; $nfailed = "failed"; }
	if(strlen($_POST["name"]) > 10) { $failtext .= "Board names cannot be more than 10 characters long. "; $nfailed = "failed"; }
	if(strlen($_POST["name"]) < 2) { $failtext .= "Board names must be at least 2 characters long. "; $nfailed = "failed"; }
	if(strlen($_POST["description"]) > 100) { $failtext .= "Board descriptions cannot be more than 100 characters long. "; $dfailed = "failed"; }
	if(strlen($_POST["description"]) < 2) { $failtext .= "Board descriptions must be at least 2 characters long. "; $dfailed = "failed"; }

}

if($failtext == "") {
	$query = sprintf("SELECT * FROM boards WHERE name = '%s'", 
			$_POST["name"]);
	$result = doQuery($query, $mysql) or dieGracefully("could not get boards list");
	if (mysql_num_rows($result) > 0) { $failtext .= "A board by this name already exists. "; $nfailed = "failed"; }
	else {
		$id = nextID("boards", $mysql);

		$query = sprintf("INSERT INTO boards VALUES ('%s','%s','%s','%s','0')",
				$id,
				$_POST["name"],
				$_POST["description"],
				$_SESSION["username"]);
		$result = doQuery($query, $mysql) or dieGracefully("could not add new forum");
		
		pushHeader("boards/");

		echo "Board added. Please wait while you are redirected, or just <a href=\"" . $options["path"] . "/boards/\">click here</a>.";
	}

} if($failtext != "") {
	pushHeader();
	echo $failtext;
	echo "<br /><form method=\"POST\" action=\"" . $options["path"] . "/boards/new/\">";
	echo "Name (max 10 chars): <input type=\"text\" name=\"name\" class=\"" .  $nfailed . "\"><br />";
	echo "Description (max 100 chars): <textarea name=\"description\" class=\"" .  $dfailed . "\">" . $_POST["description"] . "</textarea><br />";
	echo "<input type=\"submit\" value=\"Submit\" />";
}

footer();

?>