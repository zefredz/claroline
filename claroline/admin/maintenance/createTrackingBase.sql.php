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

$sqlForUpdate[] = "# Start for tracking TABLES Queries";
#####################################################
################### CREATE TABLES ###################
#####################################################

$sqlForUpdate[] = "# CREATE TABLES ";

$sqlForUpdate[] = "CREATE TABLE  IF NOT EXISTS `track_c_browsers` (
  `id` int(11) NOT NULL auto_increment,
  `browser` varchar(255) NOT NULL default '',
  `counter` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM COMMENT='record browsers occurences';";

$sqlForUpdate[] = "CREATE TABLE  IF NOT EXISTS `track_c_countries` (
  `id` int(11) NOT NULL auto_increment,
  `code` varchar(10) NOT NULL default '',
  `country` varchar(50) NOT NULL default '',
  `counter` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;";

$sqlForUpdate[] = "CREATE TABLE  IF NOT EXISTS `track_c_os` (
  `id` int(11) NOT NULL auto_increment,
  `os` varchar(255) NOT NULL default '',
  `counter` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM COMMENT='record OS occurences';";

$sqlForUpdate[] = "CREATE TABLE  IF NOT EXISTS `track_c_providers` (
  `id` int(11) NOT NULL auto_increment,
  `provider` varchar(255) NOT NULL default '',
  `counter` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM COMMENT='list of providers used by users and number of occurences';";

$sqlForUpdate[] = "CREATE TABLE  IF NOT EXISTS `track_c_referers` (
  `id` int(11) NOT NULL auto_increment,
  `referer` varchar(255) NOT NULL default '',
  `counter` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM COMMENT='record refering url occurences';";

$sqlForUpdate[] = "CREATE TABLE  IF NOT EXISTS `track_e_access` (
  `access_id` int(11) NOT NULL auto_increment,
  `access_user_id` int(10) default NULL,
  `access_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `access_cours_code` varchar(20) NOT NULL default '0',
  `access_tool` varchar(30) default NULL,
  PRIMARY KEY  (`access_id`)
) TYPE=MyISAM COMMENT='Record informations about access to course or tools';";

$sqlForUpdate[] = "CREATE TABLE  IF NOT EXISTS `track_e_downloads` (
  `down_id` int(11) NOT NULL auto_increment,
  `down_user_id` int(10) default NULL,
  `down_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `down_cours_id` varchar(20) NOT NULL default '0',
  `down_doc_path` varchar(255) NOT NULL default '0',
  PRIMARY KEY  (`down_id`)
) TYPE=MyISAM COMMENT='Record informations about downloads';";

$sqlForUpdate[] = "CREATE TABLE  IF NOT EXISTS `track_e_exercices` (
  `exe_id` int(11) NOT NULL auto_increment,
  `exe_user_id` int(10) default NULL,
  `exe_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `exe_cours_id` varchar(20) NOT NULL default '',
  `exe_exo_id` tinyint(4) NOT NULL default '0',
  `exe_result` tinyint(4) NOT NULL default '0',
  `exe_weighting` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`exe_id`)
) TYPE=MyISAM COMMENT='Record informations about exercices';";


$sqlForUpdate[] = "CREATE TABLE  IF NOT EXISTS `track_e_links` (
  `links_id` int(11) NOT NULL auto_increment,
  `links_user_id` int(10) default NULL,
  `links_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `links_cours_id` varchar(20) NOT NULL default '0',
  `links_link_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`links_id`)
) TYPE=MyISAM COMMENT='Record informations about clicks on links';";

$sqlForUpdate[] = "CREATE TABLE  IF NOT EXISTS `track_e_login` (
  `login_id` int(11) NOT NULL auto_increment,
  `login_user_id` int(10) NOT NULL default '0',
  `login_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `login_ip` varchar(39) NOT NULL default '',
  PRIMARY KEY  (`login_id`)
) TYPE=MyISAM COMMENT='Record informations about logins';";

$sqlForUpdate[] = "CREATE TABLE  IF NOT EXISTS `track_e_open` (
  `open_id` int(11) NOT NULL auto_increment,
  `open_remote_host` tinytext NOT NULL,
  `open_agent` tinytext NOT NULL,
  `open_referer` tinytext NOT NULL,
  `open_date` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`open_id`)
) TYPE=MyISAM COMMENT='Record informations about software used by users';";

$sqlForUpdate[] = "CREATE TABLE  IF NOT EXISTS `track_e_subscriptions` (
  `sub_id` int(11) NOT NULL auto_increment,
  `sub_user_id` int(10) NOT NULL default '0',
  `sub_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `sub_cours_id` int(11) NOT NULL default '0',
  `sub_action` enum('sub','unsub') NOT NULL default 'sub',
  PRIMARY KEY  (`sub_id`)
) TYPE=MyISAM COMMENT='Record informations about subscriptions to courses';";

$sqlForUpdate[] = "CREATE TABLE  IF NOT EXISTS `track_e_uploads` (
  `upload_id` int(11) NOT NULL auto_increment,
  `upload_user_id` int(10) default NULL,
  `upload_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `upload_cours_id` varchar(20) NOT NULL default '0',
  `upload_work_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`upload_id`)
) TYPE=MyISAM COMMENT='Record some more informations about uploaded works';";





##################################################
################### ADD FIELDS ###################
##################################################

$sqlForUpdate[] = "# `track_c_browsers`";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_c_browsers` ADD `id` int(11) NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_c_browsers` ADD `browser` varchar(255) NOT NULL default '';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_c_browsers` ADD `counter` int(11) NOT NULL default '0';";

$sqlForUpdate[] = "# `track_c_countries` ;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_c_countries` ADD `id` int(11) NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_c_countries` ADD `code` varchar(10) NOT NULL default '';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_c_countries` ADD `country` varchar(50) NOT NULL default '';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_c_countries` ADD `counter` int(11) NOT NULL default '0';";

$sqlForUpdate[] = "# `track_c_os`;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_c_os` ADD `id` int(11) NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_c_os` ADD `os` varchar(255) NOT NULL default '';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_c_os` ADD `counter` int(11) NOT NULL default '0';";

$sqlForUpdate[] = "# `track_c_providers`;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_c_providers` ADD `id` int(11) NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_c_providers` ADD `provider` varchar(255) NOT NULL default '';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_c_providers` ADD `counter` int(11) NOT NULL default '0';";

$sqlForUpdate[] = "# `track_c_referers`;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_c_referers` ADD `id` int(11) NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_c_referers` ADD `referer` varchar(255) NOT NULL default '';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_c_referers` ADD `counter` int(11) NOT NULL default '0';";

$sqlForUpdate[] = "# `track_e_access`;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_access` ADD `access_id` int(11) NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_access` ADD `access_user_id` int(10) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_access` ADD `access_date` datetime NOT NULL default '0000-00-00 00:00:00';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_access` ADD `access_cours_code` varchar(20) NOT NULL default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_access` ADD `access_tool` varchar(30) default NULL;";

$sqlForUpdate[] = "# `track_e_downloads`;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_downloads` ADD `down_id` int(11) NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_downloads` ADD `down_user_id` int(10) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_downloads` ADD `down_date` datetime NOT NULL default '0000-00-00 00:00:00';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_downloads` ADD `down_cours_id` varchar(20) NOT NULL default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_downloads` ADD `down_doc_path` varchar(255) NOT NULL default '0';";

$sqlForUpdate[] = "# `track_e_exercices`;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_exercices` ADD `exe_id` int(11) NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_exercices` ADD `exe_user_id` int(10) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_exercices` ADD `exe_date` datetime NOT NULL default '0000-00-00 00:00:00';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_exercices` ADD `exe_cours_id` varchar(20) NOT NULL default '';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_exercices` ADD `exe_exo_id` tinyint(4) NOT NULL default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_exercices` ADD `exe_result` tinyint(4) NOT NULL default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_exercices` ADD `exe_weighting` tinyint(4) NOT NULL default '0';";


$sqlForUpdate[] = "# `track_e_links`;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_links` ADD `links_id` int(11) NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_links` ADD `links_user_id` int(10) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_links` ADD `links_date` datetime NOT NULL default '0000-00-00 00:00:00';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_links` ADD `links_cours_id` varchar(20) NOT NULL default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_links` ADD `links_link_id` int(11) NOT NULL default '0';";

$sqlForUpdate[] = "# `track_e_login`;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_login` ADD `login_id` int(11) NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_login` ADD `login_user_id` int(10) NOT NULL default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_login` ADD `login_date` datetime NOT NULL default '0000-00-00 00:00:00';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_login` ADD `login_ip` varchar(39) NOT NULL default '';";

$sqlForUpdate[] = "# `track_e_open`;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_open` ADD `open_id` int(11) NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_open` ADD `open_remote_host` tinytext NOT NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_open` ADD `open_agent` tinytext NOT NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_open` ADD `open_referer` tinytext NOT NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_open` ADD `open_date` datetime NOT NULL default '0000-00-00 00:00:00';";

$sqlForUpdate[] = "# `track_e_subscriptions`;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_subscriptions` ADD `sub_id` int(11) NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_subscriptions` ADD `sub_user_id` int(10) NOT NULL default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_subscriptions` ADD `sub_date` datetime NOT NULL default '0000-00-00 00:00:00';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_subscriptions` ADD `sub_cours_id` int(11) NOT NULL default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_subscriptions` ADD `sub_action` enum('sub','unsub') NOT NULL default 'sub';";

$sqlForUpdate[] = "# `track_e_uploads`;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_uploads` ADD `upload_id` int(11) NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_uploads` ADD `upload_user_id` int(10) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_uploads` ADD `upload_date` datetime NOT NULL default '0000-00-00 00:00:00';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_uploads` ADD `upload_cours_id` varchar(20) NOT NULL default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_uploads` ADD `upload_work_id` int(11) NOT NULL default '0';";







##################################################
################# CHANGE FIELDS ##################
##################################################

$sqlForUpdate[] = "# `track_c_browsers`";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_c_browsers` CHANGE `id` `id` int(11) NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_c_browsers` CHANGE `browser` `browser` varchar(255) NOT NULL default '';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_c_browsers` CHANGE `counter` `counter` int(11) NOT NULL default '0';";

$sqlForUpdate[] = "# `track_c_countries` ;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_c_countries` CHANGE `id` `id` int(11) NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_c_countries` CHANGE `code` `code` varchar(10) NOT NULL default '';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_c_countries` CHANGE `country` `country` varchar(50) NOT NULL default '';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_c_countries` CHANGE `counter` `counter` int(11) NOT NULL default '0';";

$sqlForUpdate[] = "# `track_c_os`;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_c_os` CHANGE `id` `id` int(11) NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_c_os` CHANGE `os` `os` varchar(255) NOT NULL default '';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_c_os` CHANGE `counter` `counter` int(11) NOT NULL default '0';";

$sqlForUpdate[] = "# `track_c_providers`;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_c_providers` CHANGE `id` `id` int(11) NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_c_providers` CHANGE `provider` `provider` varchar(255) NOT NULL default '';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_c_providers` CHANGE `counter` `counter` int(11) NOT NULL default '0';";

$sqlForUpdate[] = "# `track_c_referers`;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_c_referers` CHANGE `id` `id` int(11) NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_c_referers` CHANGE `referer` `referer` varchar(255) NOT NULL default '';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_c_referers` CHANGE `counter` `counter` int(11) NOT NULL default '0';";

$sqlForUpdate[] = "# `track_e_access`;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_access` CHANGE `access_id` `access_id` int(11) NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_access` CHANGE `access_user_id` `access_user_id` int(10) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_access` CHANGE `access_date` `access_date` datetime NOT NULL default '0000-00-00 00:00:00';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_access` CHANGE `access_cours_code` `access_cours_code` varchar(20) NOT NULL default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_access` CHANGE `access_tool` `access_tool` varchar(30) default NULL;";

$sqlForUpdate[] = "# `track_e_downloads`;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_downloads` CHANGE `down_id` `down_id` int(11) NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_downloads` CHANGE `down_user_id` `down_user_id` int(10) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_downloads` CHANGE `down_date` `down_date` datetime NOT NULL default '0000-00-00 00:00:00';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_downloads` CHANGE `down_cours_id` `down_cours_id` varchar(20) NOT NULL default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_downloads` CHANGE `down_doc_path` `down_doc_path` varchar(255) NOT NULL default '0';";

$sqlForUpdate[] = "# `track_e_exercices`;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_exercices` CHANGE `exe_id` `exe_id` int(11) NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_exercices` CHANGE `exe_user_id` `exe_user_id` int(10) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_exercices` CHANGE `exe_date` `exe_date` datetime NOT NULL default '0000-00-00 00:00:00';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_exercices` CHANGE `exe_cours_id` `exe_cours_id` varchar(20) NOT NULL default '';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_exercices` CHANGE `exe_exo_id` `exe_exo_id` tinyint(4) NOT NULL default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_exercices` CHANGE `exe_result` `exe_result` tinyint(4) NOT NULL default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_exercices` CHANGE `exe_weighting` `exe_weighting` tinyint(4) NOT NULL default '0';";

$sqlForUpdate[] = "# `track_e_links`;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_links` CHANGE `links_id` `links_id` int(11) NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_links` CHANGE `links_user_id` `links_user_id` int(10) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_links` CHANGE `links_date` `links_date` datetime NOT NULL default '0000-00-00 00:00:00';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_links` CHANGE `links_cours_id` `links_cours_id` varchar(20) NOT NULL default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_links` CHANGE `links_link_id` `links_link_id` int(11) NOT NULL default '0';";

$sqlForUpdate[] = "# `track_e_login`;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_login` CHANGE `login_id` `login_id` int(11) NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_login` CHANGE `login_user_id` `login_user_id` int(10) NOT NULL default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_login` CHANGE `login_date` `login_date` datetime NOT NULL default '0000-00-00 00:00:00';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_login` CHANGE `login_ip` `login_ip` varchar(39) NOT NULL default '';";

$sqlForUpdate[] = "# `track_e_open`;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_open` CHANGE `open_id` `open_id` int(11) NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_open` CHANGE `open_remote_host` `open_remote_host` tinytext NOT NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_open` CHANGE `open_agent` `open_agent` tinytext NOT NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_open` CHANGE `open_referer` `open_referer` tinytext NOT NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_open` CHANGE `open_date` `open_date` datetime NOT NULL default '0000-00-00 00:00:00';";

$sqlForUpdate[] = "# `track_e_subscriptions`;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_subscriptions` CHANGE `sub_id` `sub_id` int(11) NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_subscriptions` CHANGE `sub_user_id` `sub_user_id` int(10) NOT NULL default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_subscriptions` CHANGE `sub_date` `sub_date` datetime NOT NULL default '0000-00-00 00:00:00';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_subscriptions` CHANGE `sub_cours_id` `sub_cours_id` int(11) NOT NULL default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_subscriptions` CHANGE `sub_action` `sub_action` enum('sub','unsub') NOT NULL default 'sub';";

$sqlForUpdate[] = "# `track_e_uploads`;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_uploads` CHANGE `upload_id` `upload_id` int(11) NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_uploads` CHANGE `upload_user_id` `upload_user_id` int(10) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_uploads` CHANGE `upload_date` `upload_date` datetime NOT NULL default '0000-00-00 00:00:00';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_uploads` CHANGE `upload_cours_id` `upload_cours_id` varchar(20) NOT NULL default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_uploads` CHANGE `upload_work_id` `upload_work_id` int(11) NOT NULL default '0';";

#####################################################
################### COMMENT TABLES ##################
#####################################################

$sqlForUpdate[] = "# COMMENT TABLES ";

$sqlForUpdate[] = " ALTER TABLE `track_c_browsers`  	COMMENT='record browsers occurences';";
$sqlForUpdate[] = " ALTER TABLE `track_c_os`  			COMMENT='record OS occurences';";
$sqlForUpdate[] = " ALTER TABLE `track_c_providers`  	COMMENT='list of providers used by users and number of occurences';";
$sqlForUpdate[] = " ALTER TABLE `track_c_referers`  	COMMENT='record refering url occurences';";
$sqlForUpdate[] = " ALTER TABLE `track_e_access`  		COMMENT='Record informations about access to course or tools';";
$sqlForUpdate[] = " ALTER TABLE `track_e_exercices`  	COMMENT='Record informations about exercices';";$sqlForUpdate[] = " ALTER TABLE `track_e_links`  		COMMENT='Record informations about clicks on links';";
$sqlForUpdate[] = " ALTER TABLE `track_e_login`			COMMENT='Record informations about logins';";
$sqlForUpdate[] = " ALTER TABLE `track_e_open` 			COMMENT='Record informations about software used by users';";
$sqlForUpdate[] = " ALTER TABLE `track_e_subscriptions` COMMENT='Record informations about subscriptions to courses';";
$sqlForUpdate[] = " ALTER TABLE `track_e_uploads`		COMMENT='Record some more informations about uploaded works';";
$sqlForUpdate[] = "# End for tracking TABLES Queries";
?>
