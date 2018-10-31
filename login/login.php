<?php
function doLogin() {
session_start();
$_SESSION["username"] = $username;
header("Location: home.php");
die("Login successful");
}

function loopBack($message) {
header("Location: index.php");
die($message);
}

if(!isset($_POST["username"]) || !isset($_POST["password"]) || !isset($_POST["submit"]))
	loopBack("Improper form field names.");

if($_POST["submit"] != "login" && $_POST["submit"] != "register")
	loopBack("Register/login not specified.");

$username = strip_tags($_POST["username"]);
if(!ctype_alnum($username))
	loopBack("Usernames can contain only alphanumeric characters.");
$password = $_POST["password"];

require 'sqlExport.php'; //$sql_pw

$mysql = mysql_connect('frothsql.db', 'froth', $sql_pw) OR die("couldn't connect..");
mysql_select_db('logins', $mysql);

if($_POST["submit"] == "register") {
	if(strlen($username) < 4) loopBack("Username too short.");
	if(strlen($password) < 4) loopBack("Password too short.");
	if(strlen($username) > 15) loopBack("Username too long.");
	if(strlen($password) > 30) loopBack("Password too long.");
	
	$query = sprintf("INSERT INTO login VALUES ('%s','%s',10)", //mysql defaults don't work?
		$username,
		md5($password));
	if(mysql_query($query, $mysql)) {
		doLogin();
	} else 
		loopBack("Could not add user. Duplicate name?");
}

$query = sprintf("SELECT * FROM login WHERE username='%s'", $username);
$result = mysql_query($query, $mysql) or die("could not get userlist");
if(mysql_num_rows($result) == 0) loopBack("No matching username");
$loginFound = mysql_fetch_assoc($result);
if($loginFound["password"] != md5($password)) loopBack("Incorrect password for this username.");

//username/password correct at this point

if($loginFound["priviliges"]<10)
doLogin();
else loopBack("banned");

?>