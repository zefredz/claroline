#CREATE DATABASE IF NOT EXISTS $mysqlStatDb


# phpMyAdmin MySQL-Dump
# version 2.2.6
# http://phpwizard.net/phpMyAdmin/
# http://www.phpmyadmin.net/ (download page)
#
# Host: localhost
# Generation Time: Feb 28, 2003 at 04:13 PM
# Server version: 3.23.49
# PHP Version: 4.2.0
# Database : `stats`
# --------------------------------------------------------

#
# Table structure for table `track_access`
#

CREATE TABLE `track_access` (
  `access_id` int(11) NOT NULL auto_increment,
  `access_user_id` int(10) default NULL,
  `access_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `access_cours_code` varchar(20) NOT NULL default '0',
  `access_tool` varchar(15) default NULL,
  PRIMARY KEY  (`access_id`)
) TYPE=MyISAM COMMENT='Record informations about access to course or tools';
# --------------------------------------------------------

#
# Table structure for table `track_downloads`
#

CREATE TABLE `track_downloads` (
  `down_id` int(11) NOT NULL auto_increment,
  `down_user_id` int(10) default NULL,
  `down_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `down_cours_id` int(11) NOT NULL default '0',
  `down_doc_id` int(4) NOT NULL default '0',
  PRIMARY KEY  (`down_id`)
) TYPE=MyISAM COMMENT='Record informations about downloads';
# --------------------------------------------------------

#
# Table structure for table `track_exercices`
#

CREATE TABLE `track_exercices` (
  `exe_id` int(11) NOT NULL auto_increment,
  `exe_user_id` int(10) default NULL,
  `exe_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `exe_cours_id` int(11) NOT NULL default '0',
  `exe_exo_id` tinyint(4) NOT NULL default '0',
  `exe_result` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`exe_id`)
) TYPE=MyISAM COMMENT='Record informations about exercices';
# --------------------------------------------------------

#
# Table structure for table `track_links`
#

CREATE TABLE `track_links` (
  `links_id` int(11) NOT NULL auto_increment,
  `links_user_id` int(10) default NULL,
  `links_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `links_cours_id` int(11) NOT NULL default '0',
  `links_link_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`links_id`)
) TYPE=MyISAM COMMENT='Record informations about clicks on links';
# --------------------------------------------------------

#
# Table structure for table `track_login`
#

CREATE TABLE `track_login` (
  `login_id` int(11) NOT NULL auto_increment,
  `login_user_id` int(10) NOT NULL default '0',
  `login_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `login_ip` varchar(39) NOT NULL default '',
  PRIMARY KEY  (`login_id`)
) TYPE=MyISAM COMMENT='Record informations about logins';
# --------------------------------------------------------

#
# Table structure for table `track_open`
#

CREATE TABLE `track_open` (
  `open_id` int(11) NOT NULL auto_increment,
  `open_remote_host` varchar(50) NOT NULL default '',
  `open_agent` varchar(35) NOT NULL default '',
  `open_referer` varchar(100) NOT NULL default '',
  `open_date` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`open_id`)
) TYPE=MyISAM COMMENT='Record informations about software used by users';
# --------------------------------------------------------

#
# Table structure for table `track_subscriptions`
#

CREATE TABLE `track_subscriptions` (
  `sub_id` int(11) NOT NULL auto_increment,
  `sub_user_id` int(10) NOT NULL default '0',
  `sub_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `sub_cours_id` int(11) NOT NULL default '0',
  `sub_action` enum('sub','unsub') NOT NULL default 'sub',
  PRIMARY KEY  (`sub_id`)
) TYPE=MyISAM COMMENT='Record informations about subscriptions to courses';

    