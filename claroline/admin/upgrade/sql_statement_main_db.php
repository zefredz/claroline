<?php // $Id$

/*
    Sql query to update main database
*/

$lenForDbNameOfACourse = 20 + 30; // (max for prefix + max  for code course);

// Update table admin 
$sqlForUpdate[] = "ALTER IGNORE TABLE `admin` CHANGE `idUser` `idUser` int(11) unsigned NOT NULL default '0'";

// Create new table class
$sqlForUpdate[] = "CREATE TABLE IF NOT EXISTS `class` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  `class_parent_id` int(11) default NULL,
  `class_level` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM";

// Create new table rel_class_user
$sqlForUpdate[] = "CREATE TABLE IF NOT EXISTS `rel_class_user` (
  `id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL default '0',
  `class_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM";

// Update table track_e_default
$sqlForUpdate[] = "ALTER IGNORE TABLE `track_e_default` CHANGE `default_user_id` `default_user_id` int(11) unsigned NOT NULL default '0'" ;

// Update table login_user_id
$sqlForUpdate[] = "ALTER IGNORE TABLE `track_e_login` CHANGE `login_user_id` `login_user_id` int(11) NOT NULL default '0'" ;

// Update table user_id
$sqlForUpdate[] = "ALTER IGNORE TABLE `user` CHANGE `user_id` `user_id` int(11) unsigned NOT NULL auto_increment" ;
$sqlForUpdate[] = "ALTER IGNORE TABLE `user` CHANGE `creatorId` `creatorId` int(11) unsigned default NULL" ;

// Update table cours
$sqlForUpdate[] = " ALTER IGNORE TABLE `cours` CHANGE `dbName` `dbName` varchar(".$lenForDbNameOfACourse.") default NULL";

?>
