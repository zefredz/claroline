<?php // $Id$
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.4.* $Revision$                             |
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2003 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
      |    This program is free software; you can redistribute it and/or     |
      |    modify it under the terms of the GNU General Public License       |
      |    as published by the Free Software Foundation; either version 2    |
      |   of the License, or (at your option) any later version.             |
      |                                                                      |
      |   This program is distributed in the hope that it will be useful,    |
      |   but WITHOUT ANY WARRANTY; without even the implied warranty of     |
      |   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the      |
      |   GNU General Public License for more details.                       |
      |                                                                      |
      |   You should have received a copy of the GNU General Public License  |
      |   along with this program; if not, write to the Free Software        |
      |   Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA          |
      |   02111-1307, USA. The GPL license is also available through the     |
      |   world-wide-web at http://www.gnu.org/copyleft/gpl.html             |
      +----------------------------------------------------------------------+
      | Authors: Thomas Depraetere <depraetere@ipm.ucl.ac.be>                |
      |          Hugues Peeters    <peeters@ipm.ucl.ac.be>                   |
      |          Christophe Gesché <gesche@ipm.ucl.ac.be>                    |
      +----------------------------------------------------------------------+
*/

// 4 STEPS
// CREATE
// ADD
// CHANGE
// COMMENT
#####################################################
################### CREATE TABLES ###################
#####################################################
$sqlForUpdate[] = "# Start For Php My Admin TABLES Queries";
$sqlForUpdate[] = "# CREATE TABLES";

$sqlForUpdate[] = "CREATE TABLE IF NOT EXISTS `pma_bookmark` (
  `id` int(11) NOT NULL auto_increment,
  `dbase` varchar(255) NOT NULL default '',
  `user` varchar(255) NOT NULL default '',
  `label` varchar(255) NOT NULL default '',
  `query` text NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM COMMENT='Bookmarks';";

$sqlForUpdate[] = "CREATE TABLE IF NOT EXISTS `pma_column_comments` (
  `id` int(5) unsigned NOT NULL auto_increment,
  `db_name` varchar(64) NOT NULL default '',
  `table_name` varchar(64) NOT NULL default '',
  `column_name` varchar(64) NOT NULL default '',
  `comment` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `db_name` (`db_name`,`table_name`,`column_name`)
) TYPE=MyISAM COMMENT='Comments for Columns';";

$sqlForUpdate[] = "CREATE TABLE IF NOT EXISTS `pma_pdf_pages` (
  `db_name` varchar(64) NOT NULL default '',
  `page_nr` int(10) unsigned NOT NULL auto_increment,
  `page_descr` varchar(50) NOT NULL default '',
  PRIMARY KEY  (`page_nr`),
  KEY `db_name` (`db_name`)
) TYPE=MyISAM COMMENT='PDF Relationpages for PMA';";

$sqlForUpdate[] = "CREATE TABLE IF NOT EXISTS `pma_relation` (
  `master_db` varchar(64) NOT NULL default '',
  `master_table` varchar(64) NOT NULL default '',
  `master_field` varchar(64) NOT NULL default '',
  `foreign_db` varchar(64) NOT NULL default '',
  `foreign_table` varchar(64) NOT NULL default '',
  `foreign_field` varchar(64) NOT NULL default '',
  PRIMARY KEY  (`master_db`,`master_table`,`master_field`),
  KEY `foreign_field` (`foreign_db`,`foreign_table`)
) TYPE=MyISAM COMMENT='Relation table';";

$sqlForUpdate[] = "CREATE TABLE IF NOT EXISTS `pma_table_coords` (
  `db_name` varchar(64) NOT NULL default '',
  `table_name` varchar(64) NOT NULL default '',
  `pdf_page_number` int(11) NOT NULL default '0',
  `x` float unsigned NOT NULL default '0',
  `y` float unsigned NOT NULL default '0',
  PRIMARY KEY  (`db_name`,`table_name`,`pdf_page_number`)
) TYPE=MyISAM COMMENT='Table coordinates for phpMyAdmin PDF output';";

$sqlForUpdate[] = "CREATE TABLE IF NOT EXISTS `pma_table_info` (
  `db_name` varchar(64) NOT NULL default '',
  `table_name` varchar(64) NOT NULL default '',
  `display_field` varchar(64) NOT NULL default '',
  PRIMARY KEY  (`db_name`,`table_name`)
) TYPE=MyISAM COMMENT='Table information for phpMyAdmin';";


#####################################################
################ ADD MISSING FIELDS #################
#####################################################

$sqlForUpdate[] = "# ADD MISSING FIELDS";

$sqlForUpdate[] = "# table `pma_bookmark`";
$sqlForUpdate[] = " ALTER IGNORE  TABLE `pma_bookmark` ADD `id` int(11) NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER IGNORE  TABLE `pma_bookmark` ADD `dbase` varchar(255) NOT NULL default '';";
$sqlForUpdate[] = " ALTER IGNORE  TABLE `pma_bookmark` ADD `user` varchar(255) NOT NULL default '';";
$sqlForUpdate[] = " ALTER IGNORE  TABLE `pma_bookmark` ADD `label` varchar(255) NOT NULL default '';";
$sqlForUpdate[] = " ALTER IGNORE  TABLE `pma_bookmark` ADD `query` text NOT NULL;";

$sqlForUpdate[] = "# table `pma_column_comments`";
$sqlForUpdate[] = " ALTER IGNORE  TABLE `pma_column_comments` ADD   `id` int(5) unsigned NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER IGNORE  TABLE `pma_column_comments` ADD   `db_name` varchar(64) NOT NULL default '';";
$sqlForUpdate[] = " ALTER IGNORE  TABLE `pma_column_comments` ADD   `table_name` varchar(64) NOT NULL default '';";
$sqlForUpdate[] = " ALTER IGNORE  TABLE `pma_column_comments` ADD   `column_name` varchar(64) NOT NULL default '';";
$sqlForUpdate[] = " ALTER IGNORE  TABLE `pma_column_comments` ADD   `comment` varchar(255) NOT NULL default '';";

$sqlForUpdate[] = "# table `pma_pdf_pages`";
$sqlForUpdate[] = " ALTER IGNORE  TABLE `pma_pdf_pages` ADD `db_name` varchar(64) NOT NULL default '';";
$sqlForUpdate[] = " ALTER IGNORE  TABLE `pma_pdf_pages` ADD `page_nr` int(10) unsigned NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER IGNORE  TABLE `pma_pdf_pages` ADD `page_descr` varchar(50) NOT NULL default '';";


$sqlForUpdate[] = "# table `pma_relation`";
$sqlForUpdate[] = " ALTER IGNORE  TABLE `pma_relation` ADD `master_db` varchar(64) NOT NULL default '';";
$sqlForUpdate[] = " ALTER IGNORE  TABLE `pma_relation` ADD `master_table` varchar(64) NOT NULL default '';";
$sqlForUpdate[] = " ALTER IGNORE  TABLE `pma_relation` ADD `master_field` varchar(64) NOT NULL default '';";
$sqlForUpdate[] = " ALTER IGNORE  TABLE `pma_relation` ADD `foreign_db` varchar(64) NOT NULL default '';";
$sqlForUpdate[] = " ALTER IGNORE  TABLE `pma_relation` ADD `foreign_table` varchar(64) NOT NULL default '';";
$sqlForUpdate[] = " ALTER IGNORE  TABLE `pma_relation` ADD `foreign_field` varchar(64) NOT NULL default '';";

$sqlForUpdate[] = "# table `pma_table_coords`";
$sqlForUpdate[] = " ALTER IGNORE  TABLE `pma_table_coords` ADD `db_name` varchar(64) NOT NULL default '';";
$sqlForUpdate[] = " ALTER IGNORE  TABLE `pma_table_coords` ADD `table_name` varchar(64) NOT NULL default '';";
$sqlForUpdate[] = " ALTER IGNORE  TABLE `pma_table_coords` ADD `pdf_page_number` int(11) NOT NULL default '0';";
$sqlForUpdate[] = " ALTER IGNORE  TABLE `pma_table_coords` ADD `x` float unsigned NOT NULL default '0';";
$sqlForUpdate[] = " ALTER IGNORE  TABLE `pma_table_coords` ADD `y` float unsigned NOT NULL default '0';";

$sqlForUpdate[] = "# table `pma_table_info`";
$sqlForUpdate[] = " ALTER IGNORE  TABLE `pma_table_info` ADD `db_name` varchar(64) NOT NULL default '';";
$sqlForUpdate[] = " ALTER IGNORE  TABLE `pma_table_info` ADD `table_name` varchar(64) NOT NULL default '';";
$sqlForUpdate[] = " ALTER IGNORE  TABLE `pma_table_info` ADD `display_field` varchar(64) NOT NULL default '';";

#####################################################
################### CHANGE FIELDS ###################
#####################################################

$sqlForUpdate[] = "# CHANGE FIELDS";
$sqlForUpdate[] = "# table `pma_bookmark`";
$sqlForUpdate[] = " ALTER TABLE `pma_bookmark` CHANGE `id` `id` int(11) NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER TABLE `pma_bookmark` CHANGE `dbase` `dbase` varchar(255) NOT NULL default '';";
$sqlForUpdate[] = " ALTER TABLE `pma_bookmark` CHANGE `user` `user` varchar(255) NOT NULL default '';";
$sqlForUpdate[] = " ALTER TABLE `pma_bookmark` CHANGE `label` `label` varchar(255) NOT NULL default '';";
$sqlForUpdate[] = " ALTER TABLE `pma_bookmark` CHANGE `query` `query` text NOT NULL;";

$sqlForUpdate[] = "# table `pma_column_comments`";
$sqlForUpdate[] = " ALTER TABLE `pma_column_comments` CHANGE   `id` `id` int(5) unsigned NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER TABLE `pma_column_comments` CHANGE   `db_name` `db_name` varchar(64) NOT NULL default '';";
$sqlForUpdate[] = " ALTER TABLE `pma_column_comments` CHANGE   `table_name` `table_name` varchar(64) NOT NULL default '';";
$sqlForUpdate[] = " ALTER TABLE `pma_column_comments` CHANGE   `column_name` `column_name` varchar(64) NOT NULL default '';";
$sqlForUpdate[] = " ALTER TABLE `pma_column_comments` CHANGE   `comment` `comment` varchar(255) NOT NULL default '';";

$sqlForUpdate[] = "# table `pma_pdf_pages`";
$sqlForUpdate[] = " ALTER TABLE `pma_pdf_pages` CHANGE `db_name` `db_name` varchar(64) NOT NULL default '';";
$sqlForUpdate[] = " ALTER TABLE `pma_pdf_pages` CHANGE `page_nr` `page_nr` int(10) unsigned NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER TABLE `pma_pdf_pages` CHANGE `page_nr` `page_nr` varchar(50) NOT NULL default '';";

$sqlForUpdate[] = "# table `pma_relation`";
$sqlForUpdate[] = " ALTER TABLE `pma_relation` CHANGE `master_db` `master_db` varchar(64) NOT NULL default '';";
$sqlForUpdate[] = " ALTER TABLE `pma_relation` CHANGE `master_table` `master_table` varchar(64) NOT NULL default '';";
$sqlForUpdate[] = " ALTER TABLE `pma_relation` CHANGE `master_field` `master_field` varchar(64) NOT NULL default '';";
$sqlForUpdate[] = " ALTER TABLE `pma_relation` CHANGE `foreign_db` `foreign_db` varchar(64) NOT NULL default '';";
$sqlForUpdate[] = " ALTER TABLE `pma_relation` CHANGE `foreign_table` `foreign_table` varchar(64) NOT NULL default '';";
$sqlForUpdate[] = " ALTER TABLE `pma_relation` CHANGE `foreign_field` `foreign_field` varchar(64) NOT NULL default '';";

$sqlForUpdate[] = "# table `pma_table_coords`";
$sqlForUpdate[] = " ALTER TABLE `pma_table_coords` CHANGE `db_name` `db_name` varchar(64) NOT NULL default '';";
$sqlForUpdate[] = " ALTER TABLE `pma_table_coords` CHANGE `table_name` `table_name` varchar(64) NOT NULL default '';";
$sqlForUpdate[] = " ALTER TABLE `pma_table_coords` CHANGE `pdf_page_number` `pdf_page_number` int(11) NOT NULL default '0';";
$sqlForUpdate[] = " ALTER TABLE `pma_table_coords` CHANGE `x` `x` float unsigned NOT NULL default '0';";
$sqlForUpdate[] = " ALTER TABLE `pma_table_coords` CHANGE `y` `y` float unsigned NOT NULL default '0';";

$sqlForUpdate[] = "# table `pma_table_info`";
$sqlForUpdate[] = " ALTER TABLE `pma_table_info` CHANGE `db_name` `db_name` varchar(64) NOT NULL default '';";
$sqlForUpdate[] = " ALTER TABLE `pma_table_info` CHANGE `table_name` `table_name` varchar(64) NOT NULL default '';";
$sqlForUpdate[] = " ALTER TABLE `pma_table_info` CHANGE `display_field` `display_field` varchar(64) NOT NULL default '';";

####################################################
################### ADD COMMENTS ###################
####################################################

$sqlForUpdate[] = "# ADD COMMENTS";
$sqlForUpdate[] = " ALTER TABLE `pma_bookmark` 			COMMENT='Bookmarks';";
$sqlForUpdate[] = " ALTER TABLE `pma_pdf_pages`			COMMENT='PDF Relationpages for PMA';";
$sqlForUpdate[] = " ALTER TABLE `pma_relation` 			COMMENT='Relation table';";
$sqlForUpdate[] = " ALTER TABLE `pma_table_info`		COMMENT='Table information for phpMyAdmin';";
$sqlForUpdate[] = " ALTER TABLE `pma_column_comments`	COMMENT='Comments for Columns';";
$sqlForUpdate[] = " ALTER TABLE `pma_table_coords`		COMMENT='Table coordinates for phpMyAdmin PDF output';";
$sqlForUpdate[] = "INSERT INTO `pma_relation` (`master_db`, `master_table`, `master_field`, `foreign_db`, `foreign_table`, `foreign_field`) VALUES ('".$mainDbName."', 'admin', 'idUser', '".$mainDbName."', 'user', 'user_id');";
$sqlForUpdate[] = "INSERT INTO `pma_relation` (`master_db`, `master_table`, `master_field`, `foreign_db`, `foreign_table`, `foreign_field`) VALUES ('".$mainDbName."', 'cours', 'code', '".$mainDbName."', 'cours_user', 'code_cours');";
$sqlForUpdate[] = "INSERT INTO `pma_relation` (`master_db`, `master_table`, `master_field`, `foreign_db`, `foreign_table`, `foreign_field`) VALUES ('".$mainDbName."', 'cours', 'directory', '".$mainDbName."', 'cours', 'directory');";
$sqlForUpdate[] = "INSERT INTO `pma_relation` (`master_db`, `master_table`, `master_field`, `foreign_db`, `foreign_table`, `foreign_field`) VALUES ('".$mainDbName."', 'cours', 'dbName', '".$mainDbName."', 'cours', 'dbName');";
$sqlForUpdate[] = "INSERT INTO `pma_relation` (`master_db`, `master_table`, `master_field`, `foreign_db`, `foreign_table`, `foreign_field`) VALUES ('".$mainDbName."', 'cours', 'languageCourse', '".$mainDbName."', 'cours', 'languageCourse');";
$sqlForUpdate[] = "INSERT INTO `pma_relation` (`master_db`, `master_table`, `master_field`, `foreign_db`, `foreign_table`, `foreign_field`) VALUES ('".$mainDbName."', 'cours', 'faculte', '".$mainDbName."', 'faculte', 'code');";
$sqlForUpdate[] = "INSERT INTO `pma_relation` (`master_db`, `master_table`, `master_field`, `foreign_db`, `foreign_table`, `foreign_field`) VALUES ('".$mainDbName."', 'cours', 'fake_code', '".$mainDbName."', 'cours', 'fake_code');";
$sqlForUpdate[] = "INSERT INTO `pma_relation` (`master_db`, `master_table`, `master_field`, `foreign_db`, `foreign_table`, `foreign_field`) VALUES ('".$mainDbName."', 'user', 'user_id', '".$mainDbName."', 'cours_user', 'user_id');";
$sqlForUpdate[] = "INSERT INTO `pma_relation` (`master_db`, `master_table`, `master_field`, `foreign_db`, `foreign_table`, `foreign_field`) VALUES ('".$mainDbName."', 'user', 'nom', '".$mainDbName."', 'user', 'nom');";
$sqlForUpdate[] = "INSERT INTO `pma_relation` (`master_db`, `master_table`, `master_field`, `foreign_db`, `foreign_table`, `foreign_field`) VALUES ('".$mainDbName."', 'user', 'prenom', '".$mainDbName."', 'user', 'prenom');";
$sqlForUpdate[] = "INSERT INTO `pma_relation` (`master_db`, `master_table`, `master_field`, `foreign_db`, `foreign_table`, `foreign_field`) VALUES ('".$mainDbName."', 'user', 'username', '".$mainDbName."', 'user', 'username');";
$sqlForUpdate[] = "INSERT INTO `pma_relation` (`master_db`, `master_table`, `master_field`, `foreign_db`, `foreign_table`, `foreign_field`) VALUES ('".$mainDbName."', 'user', 'password', '".$mainDbName."', 'user', 'password');";
$sqlForUpdate[] = "INSERT INTO `pma_relation` (`master_db`, `master_table`, `master_field`, `foreign_db`, `foreign_table`, `foreign_field`) VALUES ('".$mainDbName."', 'user', 'email', '".$mainDbName."', 'user', 'email');";
$sqlForUpdate[] = "INSERT INTO `pma_relation` (`master_db`, `master_table`, `master_field`, `foreign_db`, `foreign_table`, `foreign_field`) VALUES ('".$mainDbName."', 'user', 'officialCode', '".$mainDbName."', 'user', 'officialCode');";
$sqlForUpdate[] = "INSERT INTO `pma_relation` (`master_db`, `master_table`, `master_field`, `foreign_db`, `foreign_table`, `foreign_field`) VALUES ('".$mainDbName."', 'user', 'creatorId', '".$mainDbName."', 'user', 'user_id');";
$sqlForUpdate[] = "INSERT INTO `pma_relation` (`master_db`, `master_table`, `master_field`, `foreign_db`, `foreign_table`, `foreign_field`) VALUES ('".$mainDbName."', 'cours_user', 'code_cours', '".$mainDbName."', 'cours', 'code');";
$sqlForUpdate[] = "INSERT INTO `pma_relation` (`master_db`, `master_table`, `master_field`, `foreign_db`, `foreign_table`, `foreign_field`) VALUES ('".$mainDbName."', 'cours_user', 'user_id', '".$mainDbName."', 'user', 'user_id');";
$sqlForUpdate[] = "INSERT INTO `pma_relation` (`master_db`, `master_table`, `master_field`, `foreign_db`, `foreign_table`, `foreign_field`) VALUES ('".$mainDbName."', 'todo', 'priority', '".$mainDbName."', 'todo', 'priority');";
$sqlForUpdate[] = "INSERT INTO `pma_relation` (`master_db`, `master_table`, `master_field`, `foreign_db`, `foreign_table`, `foreign_field`) VALUES ('".$mainDbName."', 'todo', 'type', '".$mainDbName."', 'todo', 'type');";
$sqlForUpdate[] = "INSERT INTO `pma_relation` (`master_db`, `master_table`, `master_field`, `foreign_db`, `foreign_table`, `foreign_field`) VALUES ('".$mainDbName."', 'todo', 'cible', '".$mainDbName."', 'todo', 'cible');";
$sqlForUpdate[] = "INSERT INTO `pma_relation` (`master_db`, `master_table`, `master_field`, `foreign_db`, `foreign_table`, `foreign_field`) VALUES ('".$mainDbName."', 'todo', 'statut', '".$mainDbName."', 'todo', 'statut');";
$sqlForUpdate[] = "INSERT INTO `pma_relation` (`master_db`, `master_table`, `master_field`, `foreign_db`, `foreign_table`, `foreign_field`) VALUES ('".$mainDbName."', 'todo', 'assignTo', '".$mainDbName."', 'user', 'user_id');";
$sqlForUpdate[] = "INSERT INTO `pma_relation` (`master_db`, `master_table`, `master_field`, `foreign_db`, `foreign_table`, `foreign_field`) VALUES ('".$mainDbName."', 'faculte', 'code', '".$mainDbName."', 'cours', 'faculte');";

$sqlForUpdate[] = "INSERT INTO `pma_column_comments` (`db_name`, `table_name`, `column_name`, `comment`) VALUES ('".$mainDbName."', 'admin', 'idUser', 'Id  of  user');";
$sqlForUpdate[] = "INSERT INTO `pma_column_comments` (`db_name`, `table_name`, `column_name`, `comment`) VALUES ('".$mainDbName."', 'cours', 'code', 'sysCode');";
$sqlForUpdate[] = "INSERT INTO `pma_column_comments` (`db_name`, `table_name`, `column_name`, `comment`) VALUES ('".$mainDbName."', 'cours', 'directory', 'path  form  course repository to course');";
$sqlForUpdate[] = "INSERT INTO `pma_column_comments` (`db_name`, `table_name`, `column_name`, `comment`) VALUES ('".$mainDbName."', 'cours', 'dbName', 'name of database of this course');";
$sqlForUpdate[] = "INSERT INTO `pma_column_comments` (`db_name`, `table_name`, `column_name`, `comment`) VALUES ('".$mainDbName."', 'cours', 'languageCourse', 'directory of languages files');";
$sqlForUpdate[] = "INSERT INTO `pma_column_comments` (`db_name`, `table_name`, `column_name`, `comment`) VALUES ('".$mainDbName."', 'cours', 'faculte', 'code of node in faculty table');";
$sqlForUpdate[] = "INSERT INTO `pma_column_comments` (`db_name`, `table_name`, `column_name`, `comment`) VALUES ('".$mainDbName."', 'cours', 'cahier_charges', 'deprecated');";
$sqlForUpdate[] = "INSERT INTO `pma_column_comments` (`db_name`, `table_name`, `column_name`, `comment`) VALUES ('".$mainDbName."', 'cours', 'titulaires', 'litteral name of course team managers');";
$sqlForUpdate[] = "INSERT INTO `pma_column_comments` (`db_name`, `table_name`, `column_name`, `comment`) VALUES ('".$mainDbName."', 'cours', 'fake_code', 'officialCode');";
$sqlForUpdate[] = "INSERT INTO `pma_column_comments` (`db_name`, `table_name`, `column_name`, `comment`) VALUES ('".$mainDbName."', 'user', 'user_id', 'uid');";
$sqlForUpdate[] = "INSERT INTO `pma_column_comments` (`db_name`, `table_name`, `column_name`, `comment`) VALUES ('".$mainDbName."', 'user', 'statut', '1= course creator, 5 for  others');";
$sqlForUpdate[] = "INSERT INTO `pma_column_comments` (`db_name`, `table_name`, `column_name`, `comment`) VALUES ('".$mainDbName."', 'user', 'officialCode', 'unique or null');";
$sqlForUpdate[] = "INSERT INTO `pma_column_comments` (`db_name`, `table_name`, `column_name`, `comment`) VALUES ('".$mainDbName."', 'user', 'creatorId', 'owner  of account only user have this ID can edit the account.');";
$sqlForUpdate[] = "INSERT INTO `pma_column_comments` (`db_name`, `table_name`, `column_name`, `comment`) VALUES ('".$mainDbName."', 'faculte', 'number', 'rank to order in lists');";
$sqlForUpdate[] = "INSERT INTO `pma_column_comments` (`db_name`, `table_name`, `column_name`, `comment`) VALUES ('".$mainDbName."', 'cours_user', 'statut', '1=adminofCourse');";
$sqlForUpdate[] = "INSERT INTO `pma_column_comments` (`db_name`, `table_name`, `column_name`, `comment`) VALUES ('".$mainDbName."', 'cours_user', 'role', 'litteral');";
$sqlForUpdate[] = "INSERT INTO `pma_column_comments` (`db_name`, `table_name`, `column_name`, `comment`) VALUES ('".$mainDbName."', 'cours_user', 'team', 'deprecated');";
$sqlForUpdate[] = "INSERT INTO `pma_column_comments` (`db_name`, `table_name`, `column_name`, `comment`) VALUES ('".$mainDbName."', 'cours_user', 'tutor', 'is  tutor in course ?');";
$sqlForUpdate[] = "# END  OF Php My Admin TABLES Queries";
?>
