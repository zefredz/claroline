<?php // $Id$

/**
 * @version CLAROLINE 1.6
 * 
 * @copyright (c) 2001-2005 Universite catholique de Louvain (UCL)
 * 
 * @license GENERAL PUBLIC LICENSE (GPL)
 * 
 * @author Mathieu Laurent   <mla@claroline.net>
 * @author Christophe Gesché <moosh@claroline.net>
 * 
 *  Sql query to update main database
 */

$lenForDbNameOfACourse = 20 + 30; // (max for prefix + max  for code course);

// Update table admin 
$sqlForUpdate[] = "ALTER IGNORE TABLE `" . $mainTblPrefix . "admin` CHANGE `idUser` `idUser` int(11) unsigned NOT NULL default '0'";

// Create new table class
$sqlForUpdate[] = "CREATE TABLE IF NOT EXISTS `" . $mainTblPrefix . "class` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  `class_parent_id` int(11) default NULL,
  `class_level` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM";

// Create new table rel_class_user
$sqlForUpdate[] = "CREATE TABLE IF NOT EXISTS `" . $mainTblPrefix . "rel_class_user` (
  `id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL default '0',
  `class_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM";

// Update table user_id
$sqlForUpdate[] = "ALTER IGNORE TABLE `" . $mainTblPrefix . "user` CHANGE `user_id` `user_id` int(11) unsigned NOT NULL auto_increment" ;
$sqlForUpdate[] = "ALTER IGNORE TABLE `" . $mainTblPrefix . "user` CHANGE `creatorId` `creatorId` int(11) unsigned default NULL" ;

// Update table cours
$sqlForUpdate[] = " ALTER IGNORE TABLE `" . $mainTblPrefix . "cours` CHANGE `dbName` `dbName` varchar(".$lenForDbNameOfACourse.") default NULL";

// Create new table config_file
$sqlForUpdate[] = "CREATE TABLE IF NOT EXISTS `" . $mainTblPrefix . "config_file` (
  `config_code` varchar(30) NOT NULL default '',
  `config_hash` varchar(40) NOT NULL default '',
  PRIMARY KEY  (`config_code` )
) TYPE=MyISAM  AVG_ROW_LENGTH=48";


// Create new table sso
$sqlForUpdate[] = "CREATE TABLE IF NOT EXISTS`" . $mainTblPrefix . "sso` (
  `id` int(11) NOT NULL auto_increment,
  `cookie` varchar(255) NOT NULL default '',
  `rec_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `user_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM";

// Update course tool icon 
$sqlForUpdate[] = "UPDATE `" . $mainTblPrefix . "course_tool` SET `icon` = 'announcement.gif' WHERE `claro_label` = 'CLANN___'";
$sqlForUpdate[] = "UPDATE `" . $mainTblPrefix . "course_tool` SET `icon` = 'assignment.gif' WHERE `claro_label` = 'CLWRK___'";
$sqlForUpdate[] = "UPDATE `" . $mainTblPrefix . "course_tool` SET `icon` = 'chat.gif' WHERE `claro_label` = 'CLCHT___'";
$sqlForUpdate[] = "UPDATE `" . $mainTblPrefix . "course_tool` SET `icon` = 'document.gif' WHERE `claro_label` = 'CLDOC___'";
$sqlForUpdate[] = "UPDATE `" . $mainTblPrefix . "course_tool` SET `icon` = 'learnpath.gif' WHERE `claro_label` = 'CLLNP___'";
$sqlForUpdate[] = "UPDATE `" . $mainTblPrefix . "course_tool` SET `icon` = 'user.gif' WHERE `claro_label` = 'CLUSR___'";

?>
