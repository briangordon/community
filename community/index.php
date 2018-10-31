<?php
session_start();
if(isset($_SESSION["username"])) {
	header("Location: inbox/");
	die("Session already exists");
}
header("Location: login/");
die("Please log in.");

?>