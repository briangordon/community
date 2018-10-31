<?php
session_start();
if(isset($_SESSION["username"])) {
	header("Location: home.php");
	die("Session exists");
}
?>

<html>
<head>
<title>Login</title>
<style>
body {
	color: #232D50;
	background-color:eeeeff;
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
#loginContainer {
	border: solid 1px #C8C8C8;
	background-color:ffffff; 
	padding:10px; 
	padding-bottom:0px; 
	text-align:right; 
	display: table-cell;
}
</style>
</head>
<body>
<div style="height:30%;">&nbsp;</div>
<center>
<div id="loginContainer">
<form method="POST" action="login.php">
Username: <input type="text" name="username" /><br /><br />
Password: <input type="password" name="password" /><br /><br />
<input type="submit" name="submit" value="login" class="button" />&nbsp;
<input type="submit" name="submit" value="register" class="button" />
</form>
</div>
</center>
</body>
</html>