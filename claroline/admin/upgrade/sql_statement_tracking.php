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

/**
 *
 * TRY TO CREATE track_e_default
 *
*/

$sqlForUpdate[] = "CREATE TABLE IF NOT EXISTS `track_e_default` (
  `default_id` int(11) NOT NULL auto_increment,
  `default_user_id` int(10) NOT NULL default '0',
  `default_cours_code` varchar(40) NOT NULL default '',
  `default_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `default_event_type` varchar(20) NOT NULL default '',
  `default_value_type` varchar(20) NOT NULL default '',
  `default_value` tinytext NOT NULL,
  PRIMARY KEY  (`default_id`)
) TYPE=MyISAM COMMENT='Use for other develloppers users';";

$sqlForUpdate[] = "# `track_e_default`;";

$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_default` ADD `default_id` int(11) NOT NULL auto_increment ;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_default` ADD `default_user_id` int(10) NOT NULL default '0' ;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_default` ADD `default_cours_code` varchar(40) NOT NULL default '' ;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_default` ADD `default_date` datetime NOT NULL default '0000-00-00 00:00:00' ;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_default` ADD  `default_event_type` varchar(20) NOT NULL default '' ;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_default` ADD  `default_value_type` varchar(20) NOT NULL default '' ;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_default` ADD  `default_value` tinytext NOT NULL ;";

$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_default` CHANGE `default_id` `default_id` int(11) NOT NULL auto_increment ;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_default` CHANGE `default_user_id` `default_user_id` int(10) NOT NULL default '0' ;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_default` CHANGE `default_cours_code` `default_cours_code` varchar(40) NOT NULL default '' ;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_default` CHANGE `default_date` `default_date` datetime NOT NULL default '0000-00-00 00:00:00' ;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_default` CHANGE `default_event_type` `default_event_type` varchar(20) NOT NULL default '' ;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_default` CHANGE `default_value_type` `default_value_type` varchar(20) NOT NULL default '' ;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_default` CHANGE `default_value` `default_value` tinytext NOT NULL ;";

/**
 *
 * TRY TO CREATE track_e_login
 *
*/

$sqlForUpdate[] = "CREATE TABLE  IF NOT EXISTS `track_e_login` (
  `login_id` int(11) NOT NULL auto_increment,
  `login_user_id` int(10) NOT NULL default '0',
  `login_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `login_ip` varchar(15) NOT NULL default '',
  PRIMARY KEY  (`login_id`)
) TYPE=MyISAM COMMENT='Record informations about logins';";

$sqlForUpdate[] = "# `track_e_login`;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_login` ADD `login_id` int(11) NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_login` ADD `login_user_id` int(10) NOT NULL default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_login` ADD `login_date` datetime NOT NULL default '0000-00-00 00:00:00';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_login` ADD `login_ip` varchar(15) NOT NULL default '';";

$sqlForUpdate[] = "# `track_e_login`;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_login` CHANGE `login_id` `login_id` int(11) NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_login` CHANGE `login_user_id` `login_user_id` int(10) NOT NULL default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_login` CHANGE `login_date` `login_date` datetime NOT NULL default '0000-00-00 00:00:00';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_login` CHANGE `login_ip` `login_ip` varchar(15) NOT NULL default '';";

/**
 *
 * TRY TO CREATE track_e_open
 *
*/

$sqlForUpdate[] = "CREATE TABLE  IF NOT EXISTS `track_e_open` (
  `open_id` int(11) NOT NULL auto_increment,
  `open_date` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`open_id`)
) TYPE=MyISAM COMMENT='Record informations about software used by users';";

$sqlForUpdate[] = "# `track_e_open`;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_open` ADD `open_id` int(11) NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_open` ADD `open_date` datetime NOT NULL default '0000-00-00 00:00:00';";

$sqlForUpdate[] = "# `track_e_open`;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_open` CHANGE `open_id` `open_id` int(11) NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `track_e_open` CHANGE `open_date` `open_date` datetime NOT NULL default '0000-00-00 00:00:00';";

#####################################################
################### COMMENT TABLES ##################
#####################################################

$sqlForUpdate[] = "# COMMENT TABLES ";
$sqlForUpdate[] = " ALTER TABLE `track_e_default` COMMENT='Use for other develloppers users';";
$sqlForUpdate[] = " ALTER TABLE `track_e_login`		COMMENT='Record informations about logins';";
$sqlForUpdate[] = " ALTER TABLE `track_e_open` 		COMMENT='Record informations about software used by users';";
$sqlForUpdate[] = "# End for tracking TABLES Queries";

?>
