<?php // $Id$
/*

*/

$lenForDbNameOfACourse = 20 + 30; //(max for prefix + max  for code course);

/** 
 *
 * TRY TO CREATE admin table
 *
*/

{

$sqlForUpdate[] = "# Try create admin table";
$sqlForUpdate[] = "
CREATE TABLE IF NOT EXISTS  `admin` (
  `idUser` mediumint(8) unsigned NOT NULL default '0',
  UNIQUE KEY `idUser` (`idUser`)
) TYPE=MyISAM;";

// try to add missing fields

$sqlForUpdate[] = " ALTER IGNORE  TABLE admin ADD `idUser` mediumint(8) unsigned NOT NULL default '0';";

// alter fields

$sqlForUpdate[] = " ALTER IGNORE TABLE  admin CHANGE idUser `idUser` mediumint(8) unsigned NOT NULL default '0';";

}

/** 
 *
 * TRY TO CREATE cours table
 *
 *
*/

{

$sqlForUpdate[] = "# Try create cours table";

$sqlForUpdate[] = "CREATE TABLE IF NOT EXISTS  `cours` (
  `cours_id` int(11) NOT NULL auto_increment,
  `code` varchar(40) default NULL,
  `directory` varchar(20) default NULL,
  `dbName` varchar(".$lenForDbNameOfACourse.") default NULL,
  `languageCourse` varchar(15) default NULL,
  `intitule` varchar(250) default NULL,
  `description` text,
  `faculte` varchar(12) default NULL,
  `visible` tinyint(4) default NULL,
  `cahier_charges` varchar(250) default NULL,
  `scoreShow` int(11) NOT NULL default '1',
  `titulaires` varchar(255) default NULL,
  `email` varchar(255) default NULL,
  `fake_code` varchar(40) default NULL,
  `departmentUrlName` varchar(30) default NULL,
  `departmentUrl` varchar(180) default NULL,
  `diskQuota` int(10) unsigned default NULL,
  `versionDb` varchar(10) NOT NULL default 'NEVER SET',
  `versionClaro` varchar(10) NOT NULL default 'NEVER SET',
  `lastVisit` datetime default NULL,
  `lastEdit` datetime default NULL,
  `creationDate` datetime default NULL,
  `expirationDate` datetime default NULL,
  PRIMARY KEY  (`cours_id`),
  KEY `fake_code` (`fake_code`),
  KEY `faculte` (`faculte`)
) TYPE=MyISAM;";

// Add missing fields

$sqlForUpdate[] = " ALTER IGNORE  TABLE `cours` ADD `cours_id` int(11) NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER IGNORE  TABLE `cours` ADD `code` varchar(40) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE  TABLE `cours` ADD `directory` varchar(20) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE  TABLE `cours` ADD `dbName` varchar(".$lenForDbNameOfACourse.") default NULL;";
$sqlForUpdate[] = " ALTER IGNORE  TABLE `cours` ADD `languageCourse` varchar(15) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE  TABLE `cours` ADD `intitule` varchar(250) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE  TABLE `cours` ADD `description` text;";
$sqlForUpdate[] = " ALTER IGNORE  TABLE `cours` ADD `faculte` varchar(12) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE  TABLE `cours` ADD `visible` tinyint(4) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE  TABLE `cours` ADD `cahier_charges` varchar(250) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE  TABLE `cours` ADD `scoreShow` int(11) NOT NULL default '1';";
$sqlForUpdate[] = " ALTER IGNORE  TABLE `cours` ADD `titulaires` varchar(255) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE  TABLE `cours` ADD `email` varchar(255) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE  TABLE `cours` ADD `fake_code` varchar(40) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE  TABLE `cours` ADD `departmentUrlName` varchar(30) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE  TABLE `cours` ADD `departmentUrl` varchar(180) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE  TABLE `cours` ADD `diskQuota` int(10) unsigned default NULL;";
$sqlForUpdate[] = " ALTER IGNORE  TABLE `cours` ADD `versionDb` varchar(10) NOT NULL default 'NEVER SET';";
$sqlForUpdate[] = " ALTER IGNORE  TABLE `cours` ADD `versionClaro` varchar(10) NOT NULL default 'NEVER SET';";
$sqlForUpdate[] = " ALTER IGNORE  TABLE `cours` ADD `lastVisit` datetime default NULL;";
$sqlForUpdate[] = " ALTER IGNORE  TABLE `cours` ADD `lastEdit` datetime default NULL;";
$sqlForUpdate[] = " ALTER IGNORE  TABLE `cours` ADD `creationDate` datetime default NULL;";
$sqlForUpdate[] = " ALTER IGNORE  TABLE `cours` ADD `expirationDate` datetime default NULL;";

// Alter fields

$sqlForUpdate[] = " ALTER IGNORE TABLE `cours` CHANGE `cours_id` `cours_id` int(11) NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `cours` CHANGE `code` `code` varchar(40) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `cours` CHANGE `directory` `directory` varchar(20) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `cours` CHANGE `dbName` `dbName` varchar(".$lenForDbNameOfACourse.") default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `cours` CHANGE `languageCourse` `languageCourse` varchar(15) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `cours` CHANGE `intitule` `intitule` varchar(250) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `cours` CHANGE `description` `description`   text;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `cours` CHANGE `faculte` `faculte` varchar(12) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `cours` CHANGE `visible` `visible` tinyint(4) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `cours` CHANGE `cahier_charges` `cahier_charges`   varchar(250) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `cours` CHANGE `scoreShow` `scoreShow` int(11) NOT NULL default '1';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `cours` CHANGE `titulaires` `titulaires` varchar(255) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `cours` CHANGE `email` `email` varchar(255) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `cours` CHANGE `fake_code` `fake_code` varchar(40) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `cours` CHANGE `departmentUrlName` `departmentUrlName` varchar(30) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `cours` CHANGE `departmentUrl` `departmentUrl` varchar(180) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `cours` CHANGE `diskQuota` `diskQuota` int(10) unsigned default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `cours` CHANGE `versionDb` `versionDb` varchar(10) NOT NULL default 'NEVER SET';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `cours` CHANGE `versionClaro` `versionClaro` varchar(10) NOT NULL default 'NEVER SET';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `cours` CHANGE `lastVisit` `lastVisit` date default '0000-00-00';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `cours` CHANGE `lastEdit` `lastEdit` datetime default '0000-00-00 00:00:00';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `cours` CHANGE `creationDate`  `creationDate` datetime default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `cours` CHANGE `expirationDate` `expirationDate` datetime default '0000-00-00 00:00:00';";

// update 

$sqlForUpdate[] = "UPDATE `cours` SET `dbName` 	= `code` WHERE `dbName` is NULL";
$sqlForUpdate[] = "UPDATE `cours` SET `directory` = `code` WHERE `directory` is NULL";
$sqlForUpdate[] = "UPDATE `cours` SET `directory` = `code` WHERE `directory` ='';";
$sqlForUpdate[] = "UPDATE `cours` SET `dbName` = `code` WHERE `dbName` ='';";
$sqlForUpdate[] = "UPDATE `cours` SET `fake_code` = `code` WHERE `fake_code` ='';";

// Comment

$sqlForUpdate[] = " ALTER IGNORE TABLE  `cours` COMMENT='data of courses';";

}

/** 
 *
 * TRY TO CREATE cours_user table
 *
*/

{

$sqlForUpdate[] = "# Try create cours_user table";
$sqlForUpdate[] = "CREATE TABLE IF NOT EXISTS  `cours_user` (
  `code_cours` varchar(40) NOT NULL default '0',
  `user_id` int(11) unsigned NOT NULL default '0',
  `statut` tinyint(4) NOT NULL default '0',
  `role` varchar(60) default NULL,
  `team` int(11) NOT NULL default '0',
  `tutor` int(11) NOT NULL default '0',
  PRIMARY KEY  (`code_cours`,`user_id`),
  KEY `statut` (`statut`)
) TYPE=MyISAM;";

// Add Missing fields

$sqlForUpdate[] = " ALTER IGNORE  TABLE `cours_user` ADD code_cours varchar(40) NOT NULL default '0';";
$sqlForUpdate[] = " ALTER IGNORE  TABLE `cours_user` ADD user_id INT(11) UNSIGNED NOT NULL DEFAULT '0' ;";
$sqlForUpdate[] = " ALTER IGNORE  TABLE `cours_user` ADD statut tinyint(4) NOT NULL default '5';";
$sqlForUpdate[] = " ALTER IGNORE  TABLE `cours_user` ADD role varchar(60) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE  TABLE `cours_user` ADD team int(11) NOT NULL default '0';";
$sqlForUpdate[] = " ALTER IGNORE  TABLE `cours_user` ADD tutor int(11) NOT NULL default '0';";

// Alter fields

$sqlForUpdate[] = " ALTER IGNORE TABLE  cours_user CHANGE code_cours code_cours varchar(40) NOT NULL default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE  cours_user CHANGE user_id user_id INT(11) UNSIGNED DEFAULT '0' NOT NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE  cours_user CHANGE statut statut tinyint(4) NOT NULL default '5';";
$sqlForUpdate[] = " ALTER IGNORE TABLE  cours_user CHANGE role role varchar(60) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE  cours_user CHANGE team team int(11) NOT NULL default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE  cours_user CHANGE tutor tutor int(11) NOT NULL default '0';";

// comment

$sqlForUpdate[] = " ALTER IGNORE TABLE  `cours_user` COMMENT='link between courses and users (subscribe state)';";

// update 

$sqlForUpdate[] = " UPDATE `cours_user` SET `statut` = '5' WHERE `statut` != 1;";

}

/** 
 *
 * TRY TO CREATE course_tool table
 *
 *
*/

{

$sqlForUpdate[] = "# Try create course_tool table";
$sqlForUpdate[] = "
CREATE TABLE IF NOT EXISTS  `course_tool` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `claro_label` varchar(8) NOT NULL default '',
  `script_url` varchar(255) NOT NULL default '',
  `icon` varchar(255) default NULL,
  `def_access` enum('ALL','COURSE_MEMBER','GROUP_MEMBER','GROUP_TUTOR','COURSE_ADMIN','PLATFORM_ADMIN') NOT NULL default 'ALL',
  `def_rank` int(10) unsigned default NULL,
  `add_in_course` enum('MANUAL','AUTOMATIC') NOT NULL default 'AUTOMATIC',
  `access_manager` enum('PLATFORM_ADMIN','COURSE_ADMIN') NOT NULL default 'COURSE_ADMIN',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `claro_label` (`claro_label`)
) TYPE=MyISAM;";

// comment

$sqlForUpdate[] = " ALTER IGNORE TABLE  `course_tool`  	COMMENT='based definiton of the claroline tool used in each course';";

// fill course table

$sqlForUpdate[] = " INSERT INTO `course_tool` 
(`id`,`claro_label`,`script_url`,`icon`,`def_access`,`def_rank`,`add_in_course`,`access_manager`)
VALUES 
(1, 'CLDSC___', '../claroline/course_description/index.php', 'info.gif', 'ALL', 1, 'AUTOMATIC', 'COURSE_ADMIN'),
(2, 'CLCAL___', '../claroline/calendar/agenda.php', 'agenda.gif', 'ALL', 2, 'AUTOMATIC', 'COURSE_ADMIN'),
(3, 'CLANN___', '../claroline/announcements/announcements.php', 'valves.gif', 'ALL', 3, 'AUTOMATIC', 'COURSE_ADMIN'),
(4, 'CLDOC___', '../claroline/document/document.php', 'documents.gif', 'ALL', 4, 'AUTOMATIC', 'COURSE_ADMIN'),
(5, 'CLQWZ___', '../claroline/exercice/exercice.php', 'quiz.gif', 'ALL', 5, 'AUTOMATIC', 'COURSE_ADMIN'),
(6, 'CLLNP___', '../claroline/learnPath/learningPathList.php', 'step.gif', 'ALL', 6, 'AUTOMATIC', 'COURSE_ADMIN'),
(7, 'CLWRK___', '../claroline/work/work.php', 'works.gif', 'ALL', 7, 'AUTOMATIC', 'COURSE_ADMIN'),
(8, 'CLFRM___', '../claroline/phpbb/index.php', 'forum.gif', 'ALL', 8, 'AUTOMATIC', 'COURSE_ADMIN'),
(9, 'CLGRP___', '../claroline/group/group.php', 'group.gif', 'ALL', 9, 'AUTOMATIC', 'COURSE_ADMIN'),
(10, 'CLUSR___', '../claroline/user/user.php', 'membres.gif', 'ALL', 10, 'AUTOMATIC', 'COURSE_ADMIN'),
(11, 'CLCHT___', '../claroline/chat/chat.php', 'forum.gif', 'ALL', 11, 'AUTOMATIC', 'COURSE_ADMIN')
";

}

/** 
 *
 * Try to create faculte
 *
*/

{

$sqlForUpdate[] = "# Try create faculte table";
$sqlForUpdate[] = "
CREATE TABLE IF NOT EXISTS  `faculte` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  `code` varchar(12) NOT NULL default '',
  `code_P` varchar(40) default NULL,
  `bc` varchar(255) default NULL,
  `treePos` int(10) unsigned default NULL,
  `nb_childs` smallint(6) default NULL,
  `canHaveCoursesChild` enum('TRUE','FALSE') default 'TRUE',
  `canHaveCatChild` enum('TRUE','FALSE') default 'TRUE',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `code_P` (`code_P`),
  KEY `treePos` (`treePos`)
) TYPE=MyISAM;";

// Try to add missing fields

$sqlForUpdate[] = " ALTER IGNORE  TABLE `faculte` ADD `id` int(11) NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER IGNORE  TABLE `faculte` ADD `name` varchar(100) NOT NULL default '';";
$sqlForUpdate[] = " ALTER IGNORE  TABLE `faculte` ADD `code` varchar(12) NOT NULL default '';";
$sqlForUpdate[] = " ALTER IGNORE  TABLE `faculte` ADD `code_P` varchar(40) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE  TABLE `faculte` ADD `bc` varchar(255) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE  TABLE `faculte` ADD `treePos` int(10) unsigned default NULL;";
$sqlForUpdate[] = " ALTER IGNORE  TABLE `faculte` ADD `nb_childs` smallint(6) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE  TABLE `faculte` ADD `canHaveCoursesChild` enum('TRUE','FALSE') default 'TRUE';";
$sqlForUpdate[] = " ALTER IGNORE  TABLE `faculte` ADD `canHaveCatChild` enum('TRUE','FALSE') default 'TRUE';";

// alter fields

$sqlForUpdate[] = " ALTER IGNORE TABLE  faculte CHANGE `id`	`id` 	int(11) NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER IGNORE TABLE  faculte CHANGE `name` `name` varchar(100) NOT NULL default '';";
$sqlForUpdate[] = " ALTER IGNORE TABLE  faculte CHANGE `code` `code` varchar(12) NOT NULL default '';";
$sqlForUpdate[] = " ALTER IGNORE TABLE  faculte CHANGE `code_P` `code_P` varchar(40) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE  faculte CHANGE `bc` `bc` varchar(255) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE  faculte CHANGE `treePos` `treePos` int(10) unsigned default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE  faculte CHANGE `nb_childs` `nb_childs` 	smallint(6) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE  faculte CHANGE `canHaveCoursesChild` `canHaveCoursesChild` enum('TRUE','FALSE') default 'TRUE';";
$sqlForUpdate[] = " ALTER IGNORE TABLE  faculte CHANGE `canHaveCatChild` `canHaveCatChild` enum('TRUE','FALSE') default 'TRUE';";

// comment

$sqlForUpdate[] = " ALTER IGNORE TABLE `faculte`  COMMENT='department of the institution';";

// update

$sqlForUpdate[] = " UPDATE faculte set `treePos`=`id` where `treePos` is NULL;";
$sqlForUpdate[] = " UPDATE faculte set `nb_childs`='0' where `nb_childs` is NULL;";

}

/** 
 *
 * TRY TO CREATE user table
 *
 *
*/

{

$sqlForUpdate[] = "# Try create user table";
$sqlForUpdate[] = "CREATE TABLE IF NOT EXISTS  `user` (
  `user_id` mediumint(8) unsigned NOT NULL auto_increment,
  `nom` varchar(60) default NULL,
  `prenom` varchar(60) default NULL,
  `username` varchar(20) default 'empty',
  `password` varchar(50) default 'empty',
  `authSource` varchar(50) default 'claroline',
  `email` varchar(100) default NULL,
  `statut` tinyint(4) default NULL,
  `officialCode` varchar(40) default NULL,
  `phoneNumber` varchar(30) default NULL,
  `pictureUri` varchar(250) default NULL,
  `creatorId` mediumint(8) unsigned default NULL,
  PRIMARY KEY  (`user_id`),
  KEY `loginpass` (`username`,`password`)
) TYPE=MyISAM;";

// try to add missing fields

$sqlForUpdate[] = " ALTER IGNORE  TABLE `user` ADD `user_id` mediumint(8) unsigned NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER IGNORE  TABLE `user` ADD `nom` varchar(60) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE  TABLE `user` ADD `prenom` varchar(60) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE  TABLE `user` ADD `username` varchar(20) default 'empty';";
$sqlForUpdate[] = " ALTER IGNORE  TABLE `user` ADD `password` varchar(50) default 'empty';";
$sqlForUpdate[] = " ALTER IGNORE  TABLE `user` ADD `authSource` varchar(50) default 'claroline';";
$sqlForUpdate[] = " ALTER IGNORE  TABLE `user` ADD `email` varchar(100) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE  TABLE `user` ADD `statut` tinyint(4) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE  TABLE `user` ADD `officialCode` varchar(40) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE  TABLE `user` ADD `phoneNumber` varchar(30) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE  TABLE `user` ADD `pictureUri` varchar(250) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE  TABLE `user` ADD `creatorId` mediumint(8) unsigned default NULL;";

// alter fields

$sqlForUpdate[] = " ALTER IGNORE  TABLE `user` CHANGE `user_id` `user_id` mediumint(8) unsigned NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER IGNORE  TABLE `user` CHANGE `nom` `nom` varchar(60) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE  TABLE `user` CHANGE `prenom` `prenom` varchar(60) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE  TABLE `user` CHANGE `username` `username` varchar(20) default 'empty';";
$sqlForUpdate[] = " ALTER IGNORE  TABLE `user` CHANGE `password` `password` varchar(50) default 'empty';";
$sqlForUpdate[] = " ALTER IGNORE  TABLE `user` CHANGE `authSource` `authSource` varchar(50) default 'claroline';";
$sqlForUpdate[] = " ALTER IGNORE  TABLE `user` CHANGE `email` `email` varchar(100) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE  TABLE `user` CHANGE `statut` `statut` tinyint(4) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE  TABLE `user` CHANGE `officialCode` `officialCode` varchar(40) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE  TABLE `user` CHANGE `phoneNumber` `phoneNumber` varchar(30) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE  TABLE `user` CHANGE `pictureUri` `pictureUri` varchar(250) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE  TABLE `user` CHANGE `creatorId` `creatorId` mediumint(8) unsigned default NULL;";


// comment

$sqlForUpdate[] = " ALTER IGNORE TABLE  `user` COMMENT='data of users';";

$sqlForUpdate[] = "UPDATE `user`  SET  `creatorId` = `user_id` WHERE `creatorId` ='';";
$sqlForUpdate[] = "UPDATE `user`  SET `statut` = '5' WHERE `statut` != 1;";

}



?>
