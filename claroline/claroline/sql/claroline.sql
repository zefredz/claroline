# phpMyAdmin MySQL-Dump
# version 2.2.6
# http://phpwizard.net/phpMyAdmin/
# http://www.phpmyadmin.net/ (download page)
#
# Host: localhost
# Generation Time: Sep 18, 2002 at 05:26 PM
# Server version: 3.23.47
# PHP Version: 4.1.2
# Database : `claroline`
# --------------------------------------------------------

#
# Table structure for table `PMA_bookmark`
#

CREATE TABLE PMA_bookmark (
  id int(11) NOT NULL auto_increment,
  dbase varchar(255) NOT NULL default '',
  user varchar(255) NOT NULL default '',
  label varchar(255) NOT NULL default '',
  query text NOT NULL,
  PRIMARY KEY  (id)
) TYPE=MyISAM COMMENT='Bookmarks';
# --------------------------------------------------------

#
# Table structure for table `PMA_relation`
#

CREATE TABLE PMA_relation (
  master_db varchar(64) NOT NULL default '',
  master_table varchar(64) NOT NULL default '',
  master_field varchar(64) NOT NULL default '',
  foreign_db varchar(64) NOT NULL default '',
  foreign_table varchar(64) NOT NULL default '',
  foreign_field varchar(64) NOT NULL default '',
  PRIMARY KEY  (master_db,master_table,master_field),
  KEY foreign_field (foreign_db,foreign_table)
) TYPE=MyISAM COMMENT='Relation table';
# --------------------------------------------------------

#
# Table structure for table `admin`
#

CREATE TABLE admin (
  idUser mediumint(8) unsigned NOT NULL default '0',
  UNIQUE KEY idUser (idUser)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `annonces`
#

CREATE TABLE annonces (
  id mediumint(11) NOT NULL auto_increment,
  contenu text,
  temps date default NULL,
  code_cours varchar(20) default NULL,
  ordre mediumint(11) NOT NULL default '0',
  PRIMARY KEY  (id)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `cours`
#

CREATE TABLE cours (
  cours_id int(11) NOT NULL auto_increment,
  code varchar(20) default NULL,
  languageCourse varchar(15) default NULL,
  intitule varchar(250) default NULL,
  description text,
  faculte varchar(12) default NULL,
  visible tinyint(4) default NULL,
  cahier_charges varchar(250) default NULL,
  scoreShow int(11) NOT NULL default '1',
  titulaires varchar(200) default NULL,
  fake_code varchar(20) default NULL,
  departmentUrlName varchar(30) default NULL,
  departmentUrl varchar(180) default NULL,
  versionDb varchar(10) NOT NULL default 'NEVER SET',
  versionClaro varchar(10) NOT NULL default 'NEVER SET',
  lastVisit date NOT NULL default '0000-00-00',
  lastEdit datetime NOT NULL default '0000-00-00 00:00:00',
  expirationDate datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (cours_id)
) TYPE=MyISAM COMMENT='data of courses';
# --------------------------------------------------------

#
# Table structure for table `cours_faculte`
#

#CREATE TABLE cours_faculte (
#  id int(11) NOT NULL auto_increment,
#  faculte varchar(12) NOT NULL default '',
#  code varchar(20) NOT NULL default '',
#  PRIMARY KEY  (id)
#) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `cours_user`
#

CREATE TABLE cours_user (
  code_cours varchar(30) NOT NULL default '0',
  user_id int(11) unsigned NOT NULL default '0',
  statut tinyint(4) NOT NULL default '0',
  role varchar(60) default NULL,
  team int(11) NOT NULL default '0',
  tutor int(11) NOT NULL default '0',
  PRIMARY KEY  (code_cours,user_id)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `faculte`
#

CREATE TABLE faculte (
  id int(11) NOT NULL auto_increment,
  code varchar(10) NOT NULL default '',
  name varchar(100) NOT NULL default '',
  number int(11) NOT NULL default '0',
  PRIMARY KEY  (id),
  UNIQUE KEY number (number)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `loginout`
#

CREATE TABLE loginout (
  idLog mediumint(9) unsigned NOT NULL auto_increment,
  id_user mediumint(9) unsigned NOT NULL default '0',
  ip char(16) NOT NULL default '0.0.0.0',
  when datetime NOT NULL default '0000-00-00 00:00:00',
  action enum('LOGIN','LOGOUT') NOT NULL default 'LOGIN',
  PRIMARY KEY  (idLog)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `todo`
#

CREATE TABLE todo (
  id mediumint(9) NOT NULL auto_increment,
  contenu text,
  temps datetime default '0000-00-00 00:00:00',
  auteur varchar(80) default NULL,
  email varchar(80) default NULL,
  priority tinyint(4) default '0',
  type varchar(8) default NULL,
  cible varchar(30) default NULL,
  statut varchar(8) default NULL,
  assignTo mediumint(9) default NULL,
  showToUsers enum('YES','NO') NOT NULL default 'YES',
  PRIMARY KEY  (id),
  KEY temps (temps)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `user`
#

CREATE TABLE user (
  user_id mediumint(8) unsigned NOT NULL auto_increment,
  nom varchar(60) default NULL,
  prenom varchar(60) default NULL,
  username varchar(20) default 'empty',
  password varchar(50) default 'empty',
  email varchar(100) default NULL,
  statut tinyint(4) default NULL,
  PRIMARY KEY  (user_id)
) TYPE=MyISAM;

