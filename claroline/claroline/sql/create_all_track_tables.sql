# phpMyAdmin MySQL-Dump
# version 2.4.0
# http://www.phpmyadmin.net/ (download page)
#
# Serveur: localhost
# Généré le : Mercredi 07 Mai 2003 à 10:30
# Version du serveur: 3.23.49
# Version de PHP: 4.2.0
# Base de données: `tracking`
# --------------------------------------------------------

#
# Structure de la table `track_c_browsers`
#

CREATE TABLE `track_c_browsers` (
  `id` int(11) NOT NULL auto_increment,
  `browser` varchar(255) NOT NULL default '',
  `counter` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM COMMENT='record browsers occurences';
# --------------------------------------------------------

#
# Structure de la table `track_c_countries`
#

CREATE TABLE `track_c_countries` (
  `id` int(11) NOT NULL auto_increment,
  `code` varchar(10) NOT NULL default '',
  `country` varchar(50) NOT NULL default '',
  `counter` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Structure de la table `track_c_os`
#

CREATE TABLE `track_c_os` (
  `id` int(11) NOT NULL auto_increment,
  `os` varchar(255) NOT NULL default '',
  `counter` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM COMMENT='record OS occurences';
# --------------------------------------------------------

#
# Structure de la table `track_c_providers`
#

CREATE TABLE `track_c_providers` (
  `id` int(11) NOT NULL auto_increment,
  `provider` varchar(255) NOT NULL default '',
  `counter` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM COMMENT='list of providers used by users and number of occurences';
# --------------------------------------------------------

#
# Structure de la table `track_c_referers`
#

CREATE TABLE `track_c_referers` (
  `id` int(11) NOT NULL auto_increment,
  `referer` varchar(255) NOT NULL default '',
  `counter` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM COMMENT='record refering url occurences';
# --------------------------------------------------------

#
# Structure de la table `track_e_access`
#

CREATE TABLE `track_e_access` (
  `access_id` int(11) NOT NULL auto_increment,
  `access_user_id` int(10) default NULL,
  `access_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `access_cours_code` varchar(20) NOT NULL default '0',
  `access_tool` varchar(30) default NULL,
  PRIMARY KEY  (`access_id`)
) TYPE=MyISAM COMMENT='Record informations about access to course or tools';
# --------------------------------------------------------

#
# Structure de la table `track_e_default`
#

CREATE TABLE `track_e_default` (
  `default_id` int(11) NOT NULL auto_increment,
  `default_user_id` int(10) NOT NULL default '0',
  `default_cours_code` varchar(20) NOT NULL default '',
  `default_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `default_event_type` varchar(20) NOT NULL default '',
  `default_value_type` varchar(20) NOT NULL default '',
  `default_value` tinytext NOT NULL,
  PRIMARY KEY  (`default_id`)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Structure de la table `track_e_downloads`
#

CREATE TABLE `track_e_downloads` (
  `down_id` int(11) NOT NULL auto_increment,
  `down_user_id` int(10) default NULL,
  `down_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `down_cours_id` varchar(20) NOT NULL default '0',
  `down_doc_path` varchar(255) NOT NULL default '0',
  PRIMARY KEY  (`down_id`)
) TYPE=MyISAM COMMENT='Record informations about downloads';
# --------------------------------------------------------

#
# Structure de la table `track_e_exercices`
#

CREATE TABLE `track_e_exercices` (
  `exe_id` int(11) NOT NULL auto_increment,
  `exe_user_id` int(10) default NULL,
  `exe_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `exe_cours_id` varchar(20) NOT NULL default '',
  `exe_exo_id` tinyint(4) NOT NULL default '0',
  `exe_result` tinyint(4) NOT NULL default '0',
  `exe_weighting` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`exe_id`)
) TYPE=MyISAM COMMENT='Record informations about exercices';
# --------------------------------------------------------

#
# Structure de la table `track_e_links`
#

CREATE TABLE `track_e_links` (
  `links_id` int(11) NOT NULL auto_increment,
  `links_user_id` int(10) default NULL,
  `links_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `links_cours_id` varchar(20) NOT NULL default '0',
  `links_link_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`links_id`)
) TYPE=MyISAM COMMENT='Record informations about clicks on links';
# --------------------------------------------------------

#
# Structure de la table `track_e_login`
#

CREATE TABLE `track_e_login` (
  `login_id` int(11) NOT NULL auto_increment,
  `login_user_id` int(10) NOT NULL default '0',
  `login_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `login_ip` varchar(39) NOT NULL default '',
  PRIMARY KEY  (`login_id`)
) TYPE=MyISAM COMMENT='Record informations about logins';
# --------------------------------------------------------

#
# Structure de la table `track_e_open`
#

CREATE TABLE `track_e_open` (
  `open_id` int(11) NOT NULL auto_increment,
  `open_remote_host` tinytext NOT NULL,
  `open_agent` tinytext NOT NULL,
  `open_referer` tinytext NOT NULL,
  `open_date` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`open_id`)
) TYPE=MyISAM COMMENT='Record informations about software used by users';
# --------------------------------------------------------

#
# Structure de la table `track_e_uploads`
#

CREATE TABLE `track_e_uploads` (
  `upload_id` int(11) NOT NULL auto_increment,
  `upload_user_id` int(10) default NULL,
  `upload_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `upload_cours_id` varchar(20) NOT NULL default '0',
  `upload_work_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`upload_id`)
) TYPE=MyISAM COMMENT='Record some more informations about uploaded works';
