<?php
$starttime = microtime(true);

if(!defined("indirect")) die("Don't call this directly..");

///////////////////////////////////////
$options = array(
"path" => "/demo/community" //path off wwwroot
	);
///////////////////////////////////////

session_start(); //for the auto-login
if(!isset($_SESSION["username"])) { 
	if(!defined("FROM_LOGIN") || defined("FROM_LOGOUT")) {
		header("Location: " . $options["path"] . "/login/"); 
		die("You must log in."); 
	}
} else {
		$mysql = dbConnect();
		$query = sprintf("SELECT perm FROM permissions WHERE username = '%s'",
					$_SESSION["username"]);
		$result = doQuery($query, $mysql);
		if(mysql_num_rows($result) == 0) dieGracefully("No permission level set for this username- contact the administrator");
		$row = mysql_fetch_assoc($result);
		define("PERMISSION", $row["perm"]);
}

function dbConnect() {
	global $options;
	require 'sqlExport.php'; //$sql_pw
	$mysql = mysql_connect('frothsql.db', 'froth', $sql_pw) OR die("couldn't connect to mysql");
	mysql_select_db('community', $mysql);
	return $mysql;
}

$total_queries = 0;

function doQuery($theQuery, $mysql) {
	global $total_queries;
	$total_queries++;
	return mysql_query($theQuery, $mysql);
}

function doLogin($username) {
	global $options;
	session_start();
	$_SESSION["username"] = $username;
	pushHeader("boards/"); //redirect
	dieGracefully("Login successful! Please wait while you are redirected, or just <a href=\"" . $options["path"] . "/inbox/\">click here</a>.");
}

function doLogout() {
	global $options;
	session_unset();
	session_destroy();
	pushHeader("login/"); //redirect
	dieGracefully("Logout successful! Please wait while you are redirected, or just <a href=\"" . $options["path"] . "/login/\">click here</a>.");
}

function username($user) {
	global $options;
	return "<a href=\"" . $options["path"] . "/user/?" . $user . "\">" . $user . "</a>";
}

function topic($id, $subject) {
	global $options;
	return "<a href=\"" . $options["path"] . "/boards/topics/show/?" . $id . "\">" . $subject . "</a>";
}
function nextID($table, $mysql) {
	$query = "SELECT id FROM " . $table . " ORDER BY id DESC";
	$result = doQuery($query, $mysql) or die("could not get " . $table . " for nextID");
	if (mysql_num_rows($result) == 0) return 0;
	else { 
		$row = mysql_fetch_assoc($result);
		return $row["id"] + 1;
	}
}

function sanitize($input, $conn) {
	return mysql_real_escape_string($input, $conn);
}

function unsanitize($input) {
	if(empty($input)) return "";
	else return stripslashes($input);
}

function pushHeader($redirect = "none") {
	global $options;
	define("headed", true);
	require "header.php";
}

function footer() {
	if(!defined("headed")) 
		pushHeader();

	global $starttime, $options;
	$totaltime = (microtime(true) - $starttime);

	global $total_queries;
	echo "<br /><small>SQL queries: " . $total_queries . "; execution " . round($totaltime,3) . " seconds.</small>";
	echo "</div>"; //end of maincontainer
	echo "<div id=\"notice\"><small>Community Demo by Brian Gordon<br 
/>bpgordon at umd dot edu</small></div>";
	echo "</body></html>";
}

function dieGracefully($message) {
	if(!defined("headed")) 
		pushHeader();
	echo $message;
	footer();
	die();
}

?>
