<?php # $Id$

//----------------------------------------------------------------------
// CLAROLINE
//----------------------------------------------------------------------
// Copyright (c) 2001-2003 Universite catholique de Louvain (UCL)
//----------------------------------------------------------------------
// This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
// as published by the FREE SOFTWARE FOUNDATION. The GPL is available
// through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
//----------------------------------------------------------------------
// Authors: see 'credits' file
//----------------------------------------------------------------------

//	$chmod0444=chmod( "../inc/conf/config.inc.php", 0444 );

// CREATE TABLE `admin`
// CREATE TABLE `cours`
// CREATE TABLE `cours_user`
// CREATE TABLE faculte
// CREATE TABLE `user`

############# claroline DB CREATE #############################

	$sql ="
CREATE TABLE `admin` (
  `idUser` mediumint(8) unsigned NOT NULL default '0',
  UNIQUE KEY `idUser` (`idUser`)
) TYPE=MyISAM";
	mysql_query($sql);
	$sql ="
CREATE TABLE `cours` (
  `cours_id` int(11) NOT NULL auto_increment,
  `code` varchar(40) default NULL,
  `directory` varchar(20) default NULL,
  `dbName` varchar(40) default NULL,
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
) TYPE=MyISAM COMMENT='data of courses'";


	mysql_query($sql);
	$sql ="
CREATE TABLE `cours_user` (
  `code_cours` varchar(40) NOT NULL default '0',
  `user_id` int(11) unsigned NOT NULL default '0',
  `statut` tinyint(4) NOT NULL default '5',
  `role` varchar(60) default NULL,
  `team` int(11) NOT NULL default '0',
  `tutor` int(11) NOT NULL default '0',
   PRIMARY KEY  (`code_cours`,`user_id`),
  KEY `statut` (`statut`)
) TYPE=MyISAM";
mysql_query($sql);
$sql ="CREATE TABLE faculte (
  id 					int(11) NOT NULL auto_increment,
  name 					varchar(100) NOT NULL default '',
  code 					varchar(12) NOT NULL default '',
  code_P 				varchar(40) default NULL,
  bc 					varchar(255) default NULL,
  treePos 				int(10) unsigned default NULL,
  nb_childs 			smallint(6) default NULL,
  canHaveCoursesChild 	enum('TRUE','FALSE') default 'TRUE',
  canHaveCatChild 		enum('TRUE','FALSE') default 'TRUE',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `code_P` (`code_P`),
  KEY `treePos` (`treePos`)

) TYPE=MyISAM;";
mysql_query($sql);
	$sql ="
CREATE TABLE `user` (
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
) TYPE=MyISAM";
	mysql_query($sql);
        
$sql ="
CREATE TABLE `course_tool` (
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
) TYPE=MyISAM COMMENT='based definiton of the claroline tool used in each course'" ;
        mysql_query($sql);
$sql ="
CREATE TABLE `class` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  `class_parent_id` int(11) default NULL,
  `class_level` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM COMMENT='classe_id, name, classe_parent_id, classe_level'";
mysql_query($sql);

$sql ="
CREATE TABLE `rel_class_user` (
  `id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL default '0',
  `class_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM";
mysql_query($sql);
	
?>
