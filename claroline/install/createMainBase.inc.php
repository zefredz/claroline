<?php // $Id$
if ( count( get_included_files() ) == 1 ) die( '---' );
/**
 * CLAROLINE
 *
 * SQL Statement to create table of central database
 *
 * @version 1.9 $Revision$
 *
 * @copyright 2001-2007 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @see http://www.claroline.net/wiki/index.php/Install
 *
 * @author Claro Team <cvs@claroline.net>
 *
 * @package INSTALL
 *
 */

############# claroline DB CREATE #############################

$creationStatementList[] ="
CREATE TABLE `".$mainTblPrefixForm."cours` (
  `cours_id` int(11) NOT NULL auto_increment,
  `code` varchar(40) default NULL,
  `administrativeNumber` varchar(40) default NULL,
  `directory` varchar(20) default NULL,
  `dbName` varchar(40) default NULL,
  `language` varchar(15) default NULL,
  `intitule` varchar(250) default NULL,
  `faculte` varchar(12) default NULL,
  `titulaires` varchar(255) default NULL,
  `email` varchar(255) default NULL,
  `extLinkName` varchar(30) default NULL,
  `extLinkUrl` varchar(180) default NULL,
# `visibility` ENUM ('show','hidden') DEFAULT 'show' NOT NULL,
  `visibility` ENUM ('visible','invisible') DEFAULT 'visible' NOT NULL,
  `access`     ENUM ('public','private') DEFAULT 'public' NOT NULL,
  `registration` ENUM ('open','close') DEFAULT 'open' NOT NULL,
  `registrationKey` varchar(255) default NULL,
  `diskQuota` int(10) unsigned default NULL,
  `versionDb` varchar(250) NOT NULL default 'NEVER SET',
  `versionClaro` varchar(250) NOT NULL default 'NEVER SET',
  `lastVisit` datetime default NULL,
  `lastEdit` datetime default NULL,
  `creationDate` datetime default NULL,
  `expirationDate` datetime default NULL,
  `defaultProfileId` int(11) NOT NULL,
  PRIMARY KEY  (`cours_id`),
  KEY `administrativeNumber` (`administrativeNumber`),
  KEY `faculte` (`faculte`)
) TYPE=MyISAM COMMENT='data of courses'";

    $creationStatementList[] ="
CREATE TABLE `".$mainTblPrefixForm."cours_user` (
  `code_cours` varchar(40) NOT NULL default '0',
  `user_id` int(11) unsigned NOT NULL default '0',
  `profile_id` int(11) NOT NULL,
  `isCourseManager` tinyint(4) NOT NULL default 0,
  `role` varchar(60) default NULL,
  `team` int(11) NOT NULL default '0',
  `tutor` int(11) NOT NULL default '0',
  `count_user_enrol` int(11) NOT NULL default '0',
  `count_class_enrol` int(11) NOT NULL default '0',
   PRIMARY KEY  (`code_cours`,`user_id`),
  KEY `isCourseManager` (`isCourseManager`)
) TYPE=MyISAM";

$creationStatementList[] ="CREATE TABLE `".$mainTblPrefixForm."faculte` (
  id                    int(11) NOT NULL auto_increment,
  name                  varchar(100) NOT NULL default '',
  code                  varchar(12) NOT NULL default '',
  code_P                varchar(40) default NULL,
  treePos               int(10) unsigned default NULL,
  nb_childs             smallint(6) default 0,
  canHaveCoursesChild   enum('TRUE','FALSE') default 'TRUE',
  canHaveCatChild       enum('TRUE','FALSE') default 'TRUE',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `code_P` (`code_P`),
  KEY `treePos` (`treePos`)

) TYPE=MyISAM;";

    $creationStatementList[] ="
CREATE TABLE `".$mainTblPrefixForm . "user` (
  `user_id` int(11)  unsigned NOT NULL auto_increment,
  `nom` varchar(60) default NULL,
  `prenom` varchar(60) default NULL,
  `username` varchar(20) default 'empty',
  `password` varchar(50) default 'empty',
  `language` varchar(15) default NULL,
  `authSource` varchar(50) default 'claroline',
  `email` varchar(255) default NULL,
  `isCourseCreator` tinyint(4) default 0,
  `officialCode`  varchar(255) default NULL,
  `officialEmail` varchar(255) default NULL,
  `phoneNumber` varchar(30) default NULL,
  `pictureUri` varchar(250) default NULL,
  `creatorId` int(11)  unsigned default NULL,
  `isPlatformAdmin` tinyint(4) default 0,
   PRIMARY KEY  (`user_id`),
  KEY `loginpass` (`username`,`password`)
) TYPE=MyISAM";


$creationStatementList[] ="
CREATE TABLE `".$mainTblPrefixForm . "course_tool` (
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

$creationStatementList[] ="
CREATE TABLE `".$mainTblPrefixForm."class` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  `class_parent_id` int(11) default NULL,
  `class_level` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM COMMENT='classe_id, name, classe_parent_id, classe_level'";


$creationStatementList[] ="
CREATE TABLE `".$mainTblPrefixForm."rel_class_user` (
  `id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL default '0',
  `class_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `user_id` (`user_id`),
  KEY `class_id` (`class_id`)
) TYPE=MyISAM";


$creationStatementList[] = "
CREATE TABLE `".$mainTblPrefixForm."rel_course_class` (
    `courseId` varchar(40) NOT NULL,
    `classId` int(11) NOT NULL default '0',
    PRIMARY KEY  (`courseId`,`classId`)
    ) TYPE=MyISAM";

$creationStatementList[] ="
CREATE TABLE `".$mainTblPrefixForm."config_file` (
  `config_code` varchar(30) NOT NULL default '',
  `config_hash` varchar(40) NOT NULL default '',
  PRIMARY KEY  (`config_code` )
) TYPE=MyISAM  AVG_ROW_LENGTH=48";



$creationStatementList[] =
"CREATE TABLE `".$mainTblPrefixForm."sso` (
  `id` int(11) NOT NULL auto_increment,
  `cookie` varchar(255) NOT NULL default '',
  `rec_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `user_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM";



$creationStatementList[] = "CREATE TABLE `".$mainTblPrefixForm."notify` (
  `id` int(11) NOT NULL auto_increment,
  `course_code` varchar(40) NOT NULL default '0',
  `tool_id` int(11) NOT NULL default '0',
  `ressource_id` varchar(255) NOT NULL default '0',
  `group_id` int(11) NOT NULL default '0',
  `user_id` int(11) NOT NULL default '0',
  `date` datetime default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`),
  KEY `course_id` (`course_code`)
) TYPE=MyISAM";



// table used for upgrading tools

$creationStatementList[] = "CREATE TABLE `".$mainTblPrefixForm."upgrade_status` (
    `id` INT NOT NULL auto_increment,
    `cid` VARCHAR( 40 ) NOT NULL ,
    `claro_label` VARCHAR( 8 ) ,
    `status` TINYINT NOT NULL ,
    PRIMARY KEY ( `id` )
) TYPE=MyISAM";



// table used for claroline's modules

$creationStatementList[] = "CREATE TABLE `" . $mainTblPrefixForm . "module` (
  `id`         smallint    unsigned             NOT NULL auto_increment,
  `label`      char(8)                          NOT NULL default '',
  `name`       char(100)                        NOT NULL default '',
  `activation` enum('activated','desactivated') NOT NULL default 'desactivated',
 # `type`       enum('tool','applet')            NOT NULL default 'applet',
  `type`       varchar(10)                      NOT NULL default 'applet',
  `script_url` char(255)                        NOT NULL default 'entry.php',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM";

$creationStatementList[] =
"CREATE TABLE `".$mainTblPrefixForm."module_info` (
  id             smallint     NOT NULL auto_increment,
  module_id      smallint     NOT NULL default '0',
  version        varchar(10)  NOT NULL default '',
  author         varchar(50)  default NULL,
  author_email   varchar(100) default NULL,
  author_website varchar(255) default NULL,
  description    varchar(255) default NULL,
  website        varchar(255) default NULL,
  license        varchar(50)  default NULL,
  PRIMARY KEY (id)
) TYPE=MyISAM AUTO_INCREMENT=0";

//table used to store claroline's modules complementary information
/*
$sql[] = "ALTER IGNORE TABLE `".$mainTblPrefixForm."module_info`
  CHANGE id id smallint NOT NULL auto_increment,
  ADD `website` varchar(255) default NULL";

*/
//table used to store claroline's docks (where some content can be displayed by the modules)

$creationStatementList[]=
"CREATE TABLE `" . $mainTblPrefixForm . "dock` (
  id        smallint unsigned NOT NULL auto_increment,
  module_id smallint unsigned NOT NULL default '0',
  name      varchar(50)          NOT NULL default '',
  rank      tinyint  unsigned NOT NULL default '0',
  PRIMARY KEY  (id)
) TYPE=MyISAM AUTO_INCREMENT=0";

/*
$creationStatementList[]=
"CREATE TABLE `" . $mainTblPrefixForm . "module_tool` (
  id        smallint  unsigned NOT NULL auto_increment,
  module_id smallint  unsigned NOT NULL,
  entry     varchar(255) NOT NULL default 'entry.php',
  icon      varchar(255) NOT NULL default 'icon.png',
  PRIMARY KEY  (id)
) TYPE=MyISAM COMMENT='based definiton of the claroline tool'" ;

$creationStatementList[]=
"CREATE TABLE `" . $mainTblPrefixForm . "module_rel_tool_context` (
  id         smallint unsigned NOT NULL auto_increment,
  tool_id    smallint unsigned NOT NULL,
  context    enum('PLATFORM','COURSE','USER','GROUP','CLASSE','SESSION') NOT NULL default 'COURSE',
  enabling   enum('MANUAL','AUTOMATIC') NOT NULL default 'AUTOMATIC',
  def_access enum('ALL','COURSE_MEMBER','GROUP_MEMBER','GROUP_TUTOR','COURSE_ADMIN','PLATFORM_ADMIN') NOT NULL default 'ALL',
  def_rank   int(10) unsigned default NULL,
  access_manager enum('PLATFORM_ADMIN','COURSE_ADMIN','GROUP_ADMIN','USER_ADMIN') NOT NULL default 'COURSE_ADMIN',
  PRIMARY KEY  (id)
) TYPE=MyISAM COMMENT='based definiton of the claroline tool used in each context'" ;
*/

$creationStatementList[]= "CREATE TABLE `".$mainTblPrefixForm."right_profile` (
  `profile_id` int(11) NOT NULL auto_increment,
  `type` enum('COURSE','PLATFORM') NOT NULL default 'COURSE',
  `name` varchar(255) NOT NULL default '',
  `label` varchar(50) NOT NULL default '',
  `description` varchar(255) default '',
  `courseManager` tinyint(4) default '0',
  `mailingList` tinyint(4) default '0',
  `userlistPublic` tinyint(4) default '0',
  `groupTutor` tinyint(4) default '0',
  `locked` tinyint(4) default '0',
  `required` tinyint(4) default '0',
  PRIMARY KEY  (`profile_id`),
  KEY `type` (`type`)
)TYPE=MyISAM" ;

$creationStatementList[]= "CREATE TABLE `".$mainTblPrefixForm."right_action` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `description` varchar(255) default '',
  `tool_id` int(11) default NULL,
  `rank` int(11) default '0',
  `type` enum('COURSE','PLATFORM') NOT NULL default 'COURSE',
  PRIMARY KEY  (`id`),
  KEY `tool_id` (`tool_id`),
  KEY `type` (`type`)
)TYPE=MyISAM";

$creationStatementList[]= "CREATE TABLE `".$mainTblPrefixForm."right_rel_profile_action` (
  `profile_id` int(11) NOT NULL,
  `action_id` int(11) NOT NULL,
  `courseId`  varchar(40) NOT NULL default '',
  `value` tinyint(4) default '0',
  PRIMARY KEY  (`profile_id`,`action_id`,`courseId`)
) TYPE=MyISAM";

$creationStatementList[]= "CREATE TABLE
  `" . $mainTblPrefixForm . "property_definition` (
  `propertyId` varchar(50) NOT NULL default '',
  `contextScope` varchar(10) NOT NULL default '',
  `label` varchar(50) NOT NULL default '',
  `type` varchar(10) NOT NULL default '',
  `defaultValue` varchar(255) NOT NULL default '',
  `description` text NOT NULL,
  `required` tinyint(1) NOT NULL default '0',
  `rank` int(10) unsigned NOT NULL default '0',
  `acceptedValue` text NOT NULL,
  PRIMARY KEY  (`contextScope`(2),`propertyId`),
  KEY `rank` (`rank`)
) TYPE=MyISAM ";

$creationStatementList[]= "
CREATE TABLE  `" . $mainTblPrefixForm . "user_property` (
  `userId`        int(10) unsigned NOT NULL default '0',
  `propertyId`    varchar(255) NOT NULL default '',
  `propertyValue` varchar(255) NOT NULL default '',
  `scope`         varchar(45) NOT NULL default '',
  PRIMARY KEY  (`scope`(2),`propertyId`,`userId`)
) TYPE=MyISAM" ;

?>