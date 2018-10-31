<?php if(!defined("indirect")) die("Don't call this directly.."); ?>

<html>

<head>
<title>Community</title>

<?php 
if($redirect != "none")
 echo "<meta http-equiv=\"refresh\" content=\"2;url=" . $options["path"] . "/" . $redirect ."\" />";
?>

<link rel="stylesheet" type="text/css" href="<?php echo $options["path"];?>/main.css" />

<style>
<?php echo "#" . constant("indirect"); ?>tab {
	background-color: #f0f0f0;
	color: #000;
}
</style>
</head>

<body>
<div id="navcontainer">
<?php
echo "<a href=\"" . $options["path"] . "/boards/\"><div class=\"nav\" id=\"boardstab\">Boards</div></a>";
echo "<a href=\"" . $options["path"] . "/inbox/\"><div class=\"nav\" id=\"inboxtab\">Inbox</div></a>";
echo "<a href=\"" . $options["path"] . "/watches/\"><div class=\"nav\" id=\"watchestab\">Watches</div></a>";
echo "<a href=\"" . $options["path"] . "/prefs/\"><div class=\"nav\" id=\"prefstab\">Settings</div></a>";
echo "<a href=\"" . $options["path"] . "/logout/\"><div class=\"nav\" id=\"logouttab\">Log Out</div></a>";
?>
<a name="top">&nbsp;</a>
</div>
<div id="maincontainer">
<?php
if(defined("FROM_LOGIN") || defined("FROM_LOGOUT")) echo "<b>Welcome to the community!</b><br />\n";
else echo "<b>Welcome, " . username($_SESSION["username"]) . "!</b><br />\n";
?>