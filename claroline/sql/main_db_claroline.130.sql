

# phpMyAdmin MySQL-Dump
# version 2.2.6
# http://phpwizard.net/phpMyAdmin/
# http://www.phpmyadmin.net/ (download page)
#
# Serveur: localhost
# Généré le : Vendredi 05 Juillet 2002 à 14:37
# Version du serveur: 3.23.49
# Version de PHP: 4.2.0
# Base de données: main db for claroline
# --------------------------------------------------------

#
# Structure de la table `admin`


CREATE TABLE `admin` (
 `idUser` mediumint(8) unsigned NOT NULL default '0',
  UNIQUE KEY `idUser` (`idUser`)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Structure de la table `annonces`

CREATE TABLE `annonces` (
  `id` mediumint(11) NOT NULL auto_increment,
  `contenu` text,
  `temps` date default NULL,
  `code_cours` varchar(20) default NULL,
  `ordre` mediumint(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Structure de la table `cours`
#

CREATE TABLE `cours` (
  `cours_id` int(11) NOT NULL auto_increment,
  `code` varchar(20) default NULL,
  `languageCourse` varchar(15) default NULL,
  `intitule` varchar(250) default NULL,
  `description` text,
  `faculte` varchar(12) default NULL,
  `visible` tinyint(4) default NULL,
  `cahier_charges` varchar(250) default NULL,
  `scoreShow` int(11) NOT NULL default '1',
  `titulaires` varchar(200) default NULL,
  `fake_code` varchar(20) default NULL,
  `departmentUrlName` varchar(30) default NULL,
  `departmentUrl` varchar(180) default NULL,
  PRIMARY KEY  (`cours_id`)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Structure de la table `cours_faculte`
#
#
#CREATE TABLE `cours_faculte` (
#  `id` int(11) NOT NULL auto_increment,
#  `faculte` varchar(12) NOT NULL default '',
#  `code` varchar(20) NOT NULL default '',
#  PRIMARY KEY  (`id`)
#) TYPE=MyISAM;
# --------------------------------------------------------

#
# Structure de la table `cours_user`
#

CREATE TABLE `cours_user` (
  `code_cours` varchar(30) NOT NULL default '0',
  `user_id` int(11) unsigned NOT NULL default '0',
  `statut` tinyint(4) NOT NULL default '0',
  `role` varchar(60) default NULL,
  `team` int(11) NOT NULL default '0',
  `tutor` int(11) NOT NULL default '0',
  PRIMARY KEY  (`code_cours`,`user_id`)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Structure de la table `faculte`
#

CREATE TABLE `faculte` (
  `id` int(11) NOT NULL auto_increment,
  `code` varchar(10) NOT NULL default '',
  `name` varchar(100) NOT NULL default '',
  `number` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `number` (`number`)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Structure de la table `loginout`
#

CREATE TABLE `loginout` (
  `idLog` mediumint(9) unsigned NOT NULL auto_increment,
  `id_user` mediumint(9) unsigned NOT NULL default '0',
  `ip` char(16) NOT NULL default '0.0.0.0',
  `when` datetime NOT NULL default '0000-00-00 00:00:00',
  `action` enum('LOGIN','LOGOUT') NOT NULL default 'LOGIN',
  PRIMARY KEY  (`idLog`)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Structure de la table `todo`
#

CREATE TABLE `todo` (
  `id` mediumint(9) NOT NULL auto_increment,
  `contenu` text,
  `temps` datetime default '0000-00-00 00:00:00',
  `auteur` varchar(80) default NULL,
  `email` varchar(80) default NULL,
  `priority` tinyint(4) default '0',
  `type` varchar(8) default NULL,
  `cible` varchar(30) default NULL,
  `statut` varchar(8) default NULL,
  `assignTo` mediumint(9) default NULL,
  `showToUsers` enum('YES','NO') NOT NULL default 'YES',
  PRIMARY KEY  (`id`),
  KEY `temps` (`temps`)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Structure de la table `user`
#

CREATE TABLE `user` (
  `user_id` mediumint(8) unsigned NOT NULL auto_increment,
  `nom` varchar(60) default NULL,
  `prenom` varchar(60) default NULL,
  `username` varchar(20) default 'empty',
  `password` varchar(50) default 'empty',
  `email` varchar(100) default NULL,
  `statut` tinyint(4) default NULL,
  PRIMARY KEY  (`user_id`)
) TYPE=MyISAM;

    

