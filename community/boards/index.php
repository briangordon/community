<?php
define("indirect","boards");
require("../common.php");
pushHeader();

$query = "SELECT * FROM boards WHERE deleted = '0' ORDER BY id ASC";
$result = doQuery($query, $mysql) or dieGracefully("could not get boards list");

if(mysql_num_rows($result) == 0) echo "There are no boards.";

while($row = mysql_fetch_assoc($result)) {
	if($row[deleted] == "0") {
		echo "<a href=\"" . $options["path"] . "/boards/topics/?" . $row["id"] . "\" style=\"text-decoration:none;\">";
		echo "<ul class=\"listrow\">\n";
		echo "<li class=\"rowhead\">\n";
		echo $row["name"];
		echo "</li><li class=\"rowbody\">\n";
		echo $row["description"];
		echo "</li>\n";
		if(PERMISSION >= 3) {
			echo "<li>\n";
			echo "<a href=\"" . $options["path"] . "/boards/delete/?" . $row["id"] . "\">Delete</a>\n";
			echo "</li>\n";
		}
		echo "</ul>\n";
		echo "</a>\n\n";
	}
}

if(PERMISSION >= 3) echo "<a href=\"" . $options["path"] . "/boards/new/\">New Board</a>";

footer();
?>