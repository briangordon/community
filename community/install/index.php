<?php
define("indirect","logout");
require("../common.php");
pushHeader();

$query1 = "CREATE TABLE IF NOT EXISTS `boards` (
  `id` int(11) NOT NULL,
  `name` varchar(10) NOT NULL,
  `description` varchar(100) NOT NULL,
  `starter` varchar(15) NOT NULL,
  `deleted` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;";
$query2 = "CREATE TABLE IF NOT EXISTS `logins` (
  `username` varchar(15) NOT NULL,
  `password` varchar(32) NOT NULL,
  `salt` tinyint(4) unsigned NOT NULL,
  PRIMARY KEY  (`username`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;";
$query3 = "CREATE TABLE IF NOT EXISTS `messages` (
  `id` int(11) NOT NULL,
  `tid` int(11) NOT NULL,
  `rid` int(11) NOT NULL,
  `recipient` varchar(15) NOT NULL,
  `read` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;";
$query4 = "CREATE TABLE IF NOT EXISTS `permissions` (
  `username` varchar(15) NOT NULL,
  `perm` tinyint(4) NOT NULL,
  PRIMARY KEY  (`username`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;";
$query5 = "CREATE TABLE IF NOT EXISTS `prefs` (
  `username` varchar(15) NOT NULL,
  `bio` varchar(150) default NULL,
  `realname` varchar(20) default NULL,
  PRIMARY KEY  (`username`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;";
$query6 = "CREATE TABLE IF NOT EXISTS `replies` (
  `id` int(11) NOT NULL,
  `tid` int(11) NOT NULL,
  `body` varchar(150) NOT NULL,
  `starter` varchar(15) NOT NULL,
  `deleted` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;";
$query7 = "
CREATE TABLE IF NOT EXISTS `topics` (
  `id` int(11) NOT NULL,
  `fid` int(11) NOT NULL,
  `subject` varchar(20) NOT NULL,
  `body` varchar(150) NOT NULL,
  `starter` varchar(15) NOT NULL,
  `private` tinyint(1) NOT NULL,
  `deleted` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;";
$query8 = "CREATE TABLE IF NOT EXISTS `watches` (
  `id` int(11) NOT NULL,
  `tid` int(11) NOT NULL,
  `subscriber` varchar(15) NOT NULL,
  `inviter` varchar(15) NOT NULL,
  `deleted` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;";
$query9 = "INSERT INTO `replies` (`id`, `tid`, `body`, `starter`, `deleted`) VALUES(-2, 0, 'Placeholder for invited', 'brian', 1);";
$query10 = "INSERT INTO `replies` (`id`, `tid`, `body`, `starter`, `deleted`) VALUES(-1, 0, 'Placeholder for PM', 'brian', 1);";

?>
