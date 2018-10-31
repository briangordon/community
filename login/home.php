<?php
session_start();
if(!isset($_SESSION["username"])) {
	header("Location: index.php");
	die("Invalid session");
}
require 'functions.php';
?>
<html>
<head>
<title>Home</title>
<style>
body {
	color: #232D50;
	background-color:#eeeeff;
}
a {
	color: #232D50;
	text-decoration: none;
}
a:hover {
	color: blue;
}

img {
	border:0px;
}

input.button {
	color: #232D50;
	border: solid 1px #b0b0b0;
	font-size: 15px;
	font-weight: bold; 
	background-color:eeeeff;
}
input {
	color: #232D50;
	border: solid 1px #C8C8C8;
	background-color:eeeeff;
}

#userlist {
	border: 1px solid #c8c8c8;
	background-color: #eeeeff;
}

tr.userlist_row_a {
	background-color:#DDDDEE;
	height:1em;
}
tr.userlist_row_b {
	background-color:#EEEEFF;
	height:1em;
}
tr.userlist_row_a:hover {
	background-color:#EEEEFF;
}
select {
	color: #232D50;
	background-color: transparent;
	border: 1px solid #c8c8c8;
}

option {
	background-color: #ddddee;
	color: #232D50;
	text-align:right;
	left-padding: 17px;
	border: 0px;
}

option.superadmin {
	background-image: url(images/user_suit.png);
	background-repeat: no-repeat;
}

option.admin {
	background-image: url(images/user_green.png);
	background-repeat: no-repeat;
}

option.user {
	background-image: url(images/user.png);
	background-repeat: no-repeat;
}

option.banned {
	background-image: url(images/lock.png);
	background-repeat: no-repeat;
}

option.disabled {
	background-color: #eeeeff;
}

#help {
	padding-top:.5em;
}

#helpButton:hover {
	cursor:pointer;
}

#userlist_head {

}

#contentContainer {
	border: solid 1px #C8C8C8;
	background-color:ffffff; 
	padding:10px; 
}
</style>
<script>
function writeHelp() {
	whatToWrite = "<b>Privilege levels:</b><br />\n";
	whatToWrite += "<img src=\"images/user_suit.png\" /> (Level =0) - superadmin<br />\n";
	whatToWrite += "<img src=\"images/user_green.png\" /> (admin level) - admin<br />\n";
	whatToWrite += "<img src=\"images/user.png\" /> (Level <=10) - user<br />\n";
	whatToWrite += "<img src=\"images/lock.png\" /> (Level >10) - banned<br />\n";
	whatToWrite += "<br /><b>Current requirements:</b><br />\n";
	whatToWrite += "<?php writeReqs(); ?><br>\n";
	whatToWrite += "No user can ever affect another user of equal or better privileges (including theirself), or view their password hash. Admins can only promote as high as below their own level and can't demote past 10 without ban privileges.";
	document.getElementById("help").innerHTML = whatToWrite;
	if(document.getElementById("help").style.display=="none")
		document.getElementById("help").style.display = "block";
	else document.getElementById("help").style.display = "none";
}
</script>
</head>
<body>
<div id="contentContainer">
<?php

echo "Welcome " . $_SESSION["username"] . "!<br>\n";
$startTime = microtime(true);
$total_queries = 0;

?>
<ul>
<?php
if(checkPrivs($_SESSION["username"])<=$permissions["levelToList"]) echo "<li><a href=\"?act=ulist\">User list</a></li>";
?>
<br />
<li><a href="?act=pprompt">Change password</a></li>
<li><a href="?act=remove&id=<?php echo $_SESSION["username"]; ?>">Close account</a></li>
<li><a href="?act=logout">Log out</a></li>
</ul>

<?php
if(isset($_POST["act"])) {
	if($_POST["act"] == "password") {
		if(strlen($_POST["password"]) > 3 && strlen($_POST["password"]) <=30)
			changePassword($_POST["password"]);
		else echo "Password not long enough or too long.\n";
	}
}
if(isset($_GET["act"])) { //screen sql injections and weirdness, strict alphanum check
if($_GET["act"] == "pprompt") 
	echo "<form action=\"home.php\" method=\"post\"><input type=\"hidden\" name=\"act\" value=\"password\" /><input type=\"password\" name=\"password\" />&nbsp;<input type=\"submit\" value=\"Submit\" class=\"button\" /></form>";

if($_GET["act"] == "remove" && isset($_GET["id"])) {
	if(ctype_alnum($_GET["id"]))
		removeUser($_GET["id"]);
	if($_GET["id"]==$_SESSION["username"])
		doLogout();
}
if($_GET["act"] == "logout") {
	doLogout();
}
if($_GET["act"] == "setPerms" && isset($_GET["id"]) && isset($_GET["level"])) {
	if(ctype_alnum($_GET["id"]) && ctype_digit($_GET["level"]))
		setPrivs($_GET["id"], $_GET["level"]);
}
if($_GET["act"] == "ulist") {
	if(isset($_GET["sort"]) && isset($_GET["order"])) {
		//down with switch
		if($_GET["sort"]=="name") $sort = "username";
		//if($_GET["sort"]=="password") $sort = "password"; //danger will robinson danger
		if($_GET["sort"]=="level") $sort = "privileges";
		if($_GET["order"]=="asc") $order = "ASC";
		if($_GET["order"]=="desc") $order = "DESC";
		if(isset($order) && isset($sort)) drawList($sort, $order);
		else drawList();
	} else drawList();
}
}
echo "<small>Processing time: " . round((microtime(true)-$startTime)*1000) . "ms / Database queries: " . $total_queries . "</small>\n";
	echo "<div id=\"help\" style=\"display:none;\">&nbsp;</div>";
?>
</div>
</body>
</html>
