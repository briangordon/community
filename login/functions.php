<?php
require 'permissions.php';

function doQuery($theQuery) {
	global $total_queries;
	$total_queries++;
	return mysql_query($theQuery);
}

function loginsConnect() {
	require 'sqlExport.php'; //$sql_pw
	$mysql = mysql_connect('frothsql.db', 'froth', $sql_pw) OR die("couldn't connect..");
	mysql_select_db('logins', $mysql);
	return $mysql;
}

function doLogout() {
	session_unset();
	session_destroy();
	die("Logged out. <a href=\"/demo/login/\">Click here</a> to return to the login 
page.");
}

function removeUser($theUser) {
	global $permissions; //permissions used in this function
	$yourPrivs = checkPrivs($_SESSION["username"]);
	if($theUser != $_SESSION["username"] && ($yourPrivs > $permissions["levelToRemove"] || $yourPrivs >= checkPrivs($theUser)))
		privFail();
	$mysql = loginsConnect();
	$query = sprintf("DELETE FROM login WHERE username = '%s'",$theUser);
	$result = doQuery($query) or die("Couldn't delete from database");
	echo "Account dropped.<br>";
}

function changePassword($password) {
	$mysql = loginsConnect();
	$query = sprintf("UPDATE login SET password = '%s' WHERE username = '%s'", md5($password), $_SESSION["username"]);
	$result = doQuery($query) or die("couldn't update database");
	echo "Password changed.<br>";
}	

function drawList($sort = "username", $order = "ASC") {
	global $permissions; //permissions used in this function
	$yourPrivs = checkPrivs($_SESSION["username"]);
	if($yourPrivs >$permissions["levelToList"])
		privFail();
	$mysql = loginsConnect();
	$query = sprintf("SELECT * FROM login ORDER BY %s %s, username ASC", $sort, $order);
	$result = doQuery($query, $mysql) or die("could not get userlist");

	if (mysql_num_rows($result) == 0) {
   	 echo "No users found";
	} else {
		echo "<table border=\"0\" cellpadding=\"0\" cellspacing=\"2\" id=\"userlist\">\n";
		echo "<tr id=\"userlist_head\"><td><a href=\"home.php?act=ulist&sort=name&order=asc\"><img src=\"images/arrow_up.png\" title=\"sort by username ascending\" /></a><a href=\"home.php?act=ulist&sort=name&order=desc\"><img src=\"images/arrow_down.png\" title=\"sort by username descending\" /></a></td>\n";
		if($yourPrivs<=$permissions["levelToPassword"])
			echo "<td>&nbsp;<!--Password field placeholder--></td>\n";
		if($yourPrivs<=$permissions["levelToRemove"])
			echo "<td>&nbsp;<!--Remove field placeholder--></td>\n";
		if($yourPrivs<=$permissions["levelToBan"])
			echo "<td>&nbsp;<!--Ban field placeholder--></td>\n";
		echo "<td><a href=\"home.php?act=ulist&sort=level&order=asc\"><img src=\"images/arrow_up.png\" title=\"sort by privilege level ascending\" /></a><a href=\"home.php?act=ulist&sort=level&order=desc\"><img src=\"images/arrow_down.png\" title=\"sort by privilege level descending\" /></a></td><td>&nbsp;</td></tr>\n";
		while($row = mysql_fetch_assoc($result)) {
			drawRow($row, $yourPrivs);
		}
		echo "</table>\n";
	}
}

function drawRow($row, $yourPrivs) {
	global $permissions; //permissions used in this function
	$rowsPrivs = checkPrivs($row["username"]);
	if($yourPrivs < $rowsPrivs)
		echo "<tr class=\"userlist_row_a\">\n";
	else
		echo "<tr class=\"userlist_row_b\">\n";

	if($rowsPrivs==0)
		echo "<td><img src=\"images/user_suit.png\" title=\"superadmin\" />" . $row["username"] . "</td>\n";
	else if($rowsPrivs>0 && $rowsPrivs<=$permissions["levelToList"])
		echo "<td><img src=\"images/user_green.png\" title=\"admin\" />" . $row["username"] . "</td>\n";
	else if($rowsPrivs>10)
		echo "<td><img src=\"images/lock.png\" title=\"banned\" />" . $row["username"] . "</td>\n";
	else	echo "<td><img src=\"images/user.png\" title=\"user\" />" . $row["username"] . "</td>\n";

	if($yourPrivs<=$permissions["levelToPassword"]) {
		if($yourPrivs < $rowsPrivs)
			echo "<td><img src=\"images/key.png\" title=\"md5 password hash\" />&nbsp;" . $row["password"] . " <a href=\"http://md5.rednoize.com/?q=" . $row["password"] . "&s=md5\">(lookup)</a></td>\n";
		else echo "<td><img src=\"images/key.png\" title=\"md5 password hash\" />&nbsp;Insufficient privileges</td>\n";
	}
	if($yourPrivs<=$permissions["levelToRemove"])
		if($yourPrivs < $rowsPrivs)
			echo "<td><img src=\"images/user_delete.png\" title=\"close account\" />&nbsp;<a href=\"?act=remove&id=" . $row["username"] . "\">Remove user</a></td>\n";
		else echo "<td><img src=\"images/user_delete.png\" title=\"close account\" />&nbsp;Remove user</td>\n";
	if($yourPrivs<=$permissions["levelToBan"]) {
		if($yourPrivs < $rowsPrivs) {
			if($rowsPrivs<=10)
				echo "<td><img src=\"images/lock.png\" title=\"ban user\" />&nbsp;<a href=\"?act=setPerms&id=" . $row["username"] . "&level=11\">Ban user</a></td>\n";
			else
				echo "<td><img src=\"images/lock_open.png\" title=\"unban user\" />&nbsp;<a href=\"?act=setPerms&id=" . $row["username"] . "&level=10\">Unban user</a></td>\n";
		} else {
			if($rowsPrivs<=10)
				echo "<td><img src=\"images/lock.png\" title=\"ban user\" />&nbsp;Ban user</td>\n";
			else
				echo "<td><img src=\"images/lock_open.png\" title=\"unban user\" />&nbsp;Unban user</td>\n";
		}
	}

	echo "<td><form style=\"margin:0px;\" name=\"form_" . $row["username"] . "\" action=\"home.php\" method=\"get\"><input type=\"hidden\" name=\"act\" value=\"setPerms\"><input type=\"hidden\" name=\"id\" value=\"" . $row["username"] . "\">";
	
	echo "<select name=\"level\" size=\"1\" onChange=\"javascript:document.form_" . $row["username"] . ".submit()\"";
	if($yourPrivs >= $rowsPrivs)
		echo "disabled=\"disabled\"";
	echo ">\n";
	for($i = 0; $i<=11; $i++)
		makeOption($i, $rowsPrivs, $yourPrivs);
	echo "</select>";

	echo "</form></td>";
	echo "<td><img src=\"images/information.png\" title=\"help\" id=\"helpButton\" onClick=\"javascript:writeHelp();\" /></td>";

	echo "</tr>\n";
}

function makeOption($level, $usersLevel, $yourPrivs) {
	global $permissions; //permissions used in this function

	echo "<option value=\"" . $level . "\" class=\"";
	if($level == 0) echo "superadmin";
	else if($level > 0 && $level <= $permissions["levelToList"]) echo "admin";
	else if($level > 10) echo "banned";
	else echo "user";
	if($yourPrivs >= $level)
		echo " disabled";	
	echo "\"";

	if($level == $usersLevel) echo " selected=\"selected\"";
	if($yourPrivs >= $level) echo " disabled=\"disabled\"";
	echo ">" . $level . "</option>\n";
}

function checkPrivs($theUser) {
	$mysql = loginsConnect();
	$query = sprintf("SELECT privileges FROM login WHERE username = '%s' ", $theUser);
	$result = doQuery($query) or die("couldn't read from database");
	$thePriv = mysql_fetch_assoc($result);
	return $thePriv["privileges"];
}

function setPrivs($theUser, $privilege) {
	global $permissions; //permissions used in this function
	$yourPrivs = checkPrivs($_SESSION["username"]);
	$curPrivs = checkPrivs($theUser);
	if($yourPrivs>$permissions["levelToList"] || $yourPrivs>=$privilege || $yourPrivs>=$curPrivs || ($yourPrivs>$permissions["levelToBan"] && $privilege>10) || ($yourPrivs>$permissions["levelToBan"] && $curPrivs>10))
		privFail(); 
	$mysql = loginsConnect();
	$query = sprintf("UPDATE login SET privileges = '%s' WHERE username = '%s'", $privilege, $theUser);
	$result = doQuery($query) or die("couldn't update database");
	echo "Privilege level set.<br>";
	if($_GET["id"]==$_SESSION["username"] && $privilege > 10)
		doLogout();
}

function privFail() {
	die("Insufficient priviliges.");
}

function writeReqs() {
	global $permissions; //permissions used in this function
	echo "Level <=" . $permissions["levelToPassword"] . " - Can see people's password hashes<br />\\n"; //double backslash O_O
	echo "Level <=" . $permissions["levelToRemove"] . " - Can remove users<br />\\n";
	echo "Level <=" . $permissions["levelToBan"] . " - Can ban users<br />\\n";
	echo "Level <=" . $permissions["levelToList"] . " (admin threshold) - Can see the user list and set permissions<br />\\n";
	echo "Level &nbsp;=10 - The default level for new users<br />\\n";
	echo "Level &nbsp;>10 - Banned<br />\\n";
}
?>
