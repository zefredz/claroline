# Structure de la table `admin`
CREATE TABLE `admin` (
  `idUser` mediumint(8) unsigned NOT NULL default '0',
  UNIQUE KEY `idUser` (`idUser`)
) TYPE=MyISAM;

# Structure de la table `cours`
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
  `titulaires` varchar(200) default NULL,
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
  PRIMARY KEY  (`cours_id`)
) TYPE=MyISAM COMMENT='data of courses';

# Structure de la table `cours_user`
CREATE TABLE `cours_user` (
  `code_cours` varchar(40) NOT NULL default '0',
  `user_id` int(11) unsigned NOT NULL default '0',
  `statut` tinyint(4) NOT NULL default '0',
  `role` varchar(60) default NULL,
  `team` int(11) NOT NULL default '0',
  `tutor` int(11) NOT NULL default '0',
  PRIMARY KEY  (`code_cours`,`user_id`)
) TYPE=MyISAM;

# Structure de la table `faculte`
CREATE TABLE `faculte` (
  `id` int(11) NOT NULL auto_increment,
  `code` varchar(40) NOT NULL default '',
  `name` varchar(100) NOT NULL default '',
  `number` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `number` (`number`)
) TYPE=MyISAM;

# Structure de la table `todo`
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

# Structure de la table `user`
CREATE TABLE `user` (
  `user_id` mediumint(8) unsigned NOT NULL auto_increment,
  `nom` varchar(60) default NULL,
  `prenom` varchar(60) default NULL,
  `username` varchar(20) default 'empty',
  `password` varchar(50) default 'empty',
  `email` varchar(100) default NULL,
  `statut` tinyint(4) default NULL,
  `officialCode` varchar(40) default NULL,
  `phoneNumber` varchar(30) default NULL,
  `pictureUri` varchar(250) default NULL,
  `creatorId` mediumint(8) unsigned default NULL,
  PRIMARY KEY  (`user_id`)
) TYPE=MyISAM;

# Structure de la table `pma_bookmark`
CREATE TABLE `pma_bookmark` (
  `id` int(11) NOT NULL auto_increment,
  `dbase` varchar(255) NOT NULL default '',
  `user` varchar(255) NOT NULL default '',
  `label` varchar(255) NOT NULL default '',
  `query` text NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM COMMENT='Bookmarks';

# Structure de la table `pma_column_comments`
CREATE TABLE `pma_column_comments` (
  `id` int(5) unsigned NOT NULL auto_increment,
  `db_name` varchar(64) NOT NULL default '',
  `table_name` varchar(64) NOT NULL default '',
  `column_name` varchar(64) NOT NULL default '',
  `comment` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `db_name` (`db_name`,`table_name`,`column_name`)
) TYPE=MyISAM COMMENT='Comments for Columns';

# Structure de la table `pma_pdf_pages`
CREATE TABLE `pma_pdf_pages` (
  `db_name` varchar(64) NOT NULL default '',
  `page_nr` int(10) unsigned NOT NULL auto_increment,
  `page_descr` varchar(50) NOT NULL default '',
  PRIMARY KEY  (`page_nr`),
  KEY `db_name` (`db_name`)
) TYPE=MyISAM COMMENT='PDF Relationpages for PMA';

# Structure de la table `pma_relation`

CREATE TABLE `pma_relation` (
  `master_db` varchar(64) NOT NULL default '',
  `master_table` varchar(64) NOT NULL default '',
  `master_field` varchar(64) NOT NULL default '',
  `foreign_db` varchar(64) NOT NULL default '',
  `foreign_table` varchar(64) NOT NULL default '',
  `foreign_field` varchar(64) NOT NULL default '',
  PRIMARY KEY  (`master_db`,`master_table`,`master_field`),
  KEY `foreign_field` (`foreign_db`,`foreign_table`)
) TYPE=MyISAM COMMENT='Relation table';

# Structure de la table `pma_table_coords`
CREATE TABLE `pma_table_coords` (
  `db_name` varchar(64) NOT NULL default '',
  `table_name` varchar(64) NOT NULL default '',
  `pdf_page_number` int(11) NOT NULL default '0',
  `x` float unsigned NOT NULL default '0',
  `y` float unsigned NOT NULL default '0',
  PRIMARY KEY  (`db_name`,`table_name`,`pdf_page_number`)
) TYPE=MyISAM COMMENT='Table coordinates for phpMyAdmin PDF output';

# Structure de la table `pma_table_info`
CREATE TABLE `pma_table_info` (
  `db_name` varchar(64) NOT NULL default '',
  `table_name` varchar(64) NOT NULL default '',
  `display_field` varchar(64) NOT NULL default '',
  PRIMARY KEY  (`db_name`,`table_name`)
) TYPE=MyISAM COMMENT='Table information for phpMyAdmin';


# Contenu de la table `faculte`
INSERT INTO `faculte` (`id`, `code`, `name`, `number`) VALUES (1, 'ARTS', 'Department of Arts', 1);
INSERT INTO `faculte` (`id`, `code`, `name`, `number`) VALUES (2, 'ECO', 'Department of Economics', 2);
INSERT INTO `faculte` (`id`, `code`, `name`, `number`) VALUES (3, 'PSYCHO', 'Department of Psychology', 3);
INSERT INTO `faculte` (`id`, `code`, `name`, `number`) VALUES (4, 'MD', 'Medicine', 4);
INSERT INTO `faculte` (`id`, `code`, `name`, `number`) VALUES (5, 'SC', 'Sciences', 5);
INSERT INTO `faculte` (`id`, `code`, `name`, `number`) VALUES (6, 'APSC', 'Applied sciences', 6);
INSERT INTO `faculte` (`id`, `code`, `name`, `number`) VALUES (7, 'AGRO', 'Agronomy', 7);
INSERT INTO `faculte` (`id`, `code`, `name`, `number`) VALUES (8, 'LING', 'Department of Linguistics', 8);
INSERT INTO `faculte` (`id`, `code`, `name`, `number`) VALUES (9, 'LAW', 'Department of Law', 9);
INSERT INTO `faculte` (`id`, `code`, `name`, `number`) VALUES (10, 'MBA', 'Masters in Business Administration', 10);
# --------------------------------------------------------
INSERT INTO `pma_relation` (`master_db`, `master_table`, `master_field`, `foreign_db`, `foreign_table`, `foreign_field`) VALUES ('here type nameof CentralDB', 'admin', 'idUser', 'here type nameof CentralDB', 'user', 'user_id');
INSERT INTO `pma_relation` (`master_db`, `master_table`, `master_field`, `foreign_db`, `foreign_table`, `foreign_field`) VALUES ('here type nameof CentralDB', 'cours', 'code', 'here type nameof CentralDB', 'cours_user', 'code_cours');
INSERT INTO `pma_relation` (`master_db`, `master_table`, `master_field`, `foreign_db`, `foreign_table`, `foreign_field`) VALUES ('here type nameof CentralDB', 'cours', 'directory', 'here type nameof CentralDB', 'cours', 'directory');
INSERT INTO `pma_relation` (`master_db`, `master_table`, `master_field`, `foreign_db`, `foreign_table`, `foreign_field`) VALUES ('here type nameof CentralDB', 'cours', 'dbName', 'here type nameof CentralDB', 'cours', 'dbName');
INSERT INTO `pma_relation` (`master_db`, `master_table`, `master_field`, `foreign_db`, `foreign_table`, `foreign_field`) VALUES ('here type nameof CentralDB', 'cours', 'languageCourse', 'here type nameof CentralDB', 'cours', 'languageCourse');
INSERT INTO `pma_relation` (`master_db`, `master_table`, `master_field`, `foreign_db`, `foreign_table`, `foreign_field`) VALUES ('here type nameof CentralDB', 'cours', 'faculte', 'here type nameof CentralDB', 'faculte', 'code');
INSERT INTO `pma_relation` (`master_db`, `master_table`, `master_field`, `foreign_db`, `foreign_table`, `foreign_field`) VALUES ('here type nameof CentralDB', 'cours', 'fake_code', 'here type nameof CentralDB', 'cours', 'fake_code');
INSERT INTO `pma_relation` (`master_db`, `master_table`, `master_field`, `foreign_db`, `foreign_table`, `foreign_field`) VALUES ('here type nameof CentralDB', 'user', 'user_id', 'here type nameof CentralDB', 'cours_user', 'user_id');
INSERT INTO `pma_relation` (`master_db`, `master_table`, `master_field`, `foreign_db`, `foreign_table`, `foreign_field`) VALUES ('here type nameof CentralDB', 'user', 'nom', 'here type nameof CentralDB', 'user', 'nom');
INSERT INTO `pma_relation` (`master_db`, `master_table`, `master_field`, `foreign_db`, `foreign_table`, `foreign_field`) VALUES ('here type nameof CentralDB', 'user', 'prenom', 'here type nameof CentralDB', 'user', 'prenom');
INSERT INTO `pma_relation` (`master_db`, `master_table`, `master_field`, `foreign_db`, `foreign_table`, `foreign_field`) VALUES ('here type nameof CentralDB', 'user', 'username', 'here type nameof CentralDB', 'user', 'username');
INSERT INTO `pma_relation` (`master_db`, `master_table`, `master_field`, `foreign_db`, `foreign_table`, `foreign_field`) VALUES ('here type nameof CentralDB', 'user', 'password', 'here type nameof CentralDB', 'user', 'password');
INSERT INTO `pma_relation` (`master_db`, `master_table`, `master_field`, `foreign_db`, `foreign_table`, `foreign_field`) VALUES ('here type nameof CentralDB', 'user', 'email', 'here type nameof CentralDB', 'user', 'email');
INSERT INTO `pma_relation` (`master_db`, `master_table`, `master_field`, `foreign_db`, `foreign_table`, `foreign_field`) VALUES ('here type nameof CentralDB', 'user', 'officialCode', 'here type nameof CentralDB', 'user', 'officialCode');
INSERT INTO `pma_relation` (`master_db`, `master_table`, `master_field`, `foreign_db`, `foreign_table`, `foreign_field`) VALUES ('here type nameof CentralDB', 'user', 'creatorId', 'here type nameof CentralDB', 'user', 'user_id');
INSERT INTO `pma_relation` (`master_db`, `master_table`, `master_field`, `foreign_db`, `foreign_table`, `foreign_field`) VALUES ('here type nameof CentralDB', 'cours_user', 'code_cours', 'here type nameof CentralDB', 'cours', 'code');
INSERT INTO `pma_relation` (`master_db`, `master_table`, `master_field`, `foreign_db`, `foreign_table`, `foreign_field`) VALUES ('here type nameof CentralDB', 'cours_user', 'user_id', 'here type nameof CentralDB', 'user', 'user_id');
INSERT INTO `pma_relation` (`master_db`, `master_table`, `master_field`, `foreign_db`, `foreign_table`, `foreign_field`) VALUES ('here type nameof CentralDB', 'todo', 'priority', 'here type nameof CentralDB', 'todo', 'priority');
INSERT INTO `pma_relation` (`master_db`, `master_table`, `master_field`, `foreign_db`, `foreign_table`, `foreign_field`) VALUES ('here type nameof CentralDB', 'todo', 'type', 'here type nameof CentralDB', 'todo', 'type');
INSERT INTO `pma_relation` (`master_db`, `master_table`, `master_field`, `foreign_db`, `foreign_table`, `foreign_field`) VALUES ('here type nameof CentralDB', 'todo', 'cible', 'here type nameof CentralDB', 'todo', 'cible');
INSERT INTO `pma_relation` (`master_db`, `master_table`, `master_field`, `foreign_db`, `foreign_table`, `foreign_field`) VALUES ('here type nameof CentralDB', 'todo', 'statut', 'here type nameof CentralDB', 'todo', 'statut');
INSERT INTO `pma_relation` (`master_db`, `master_table`, `master_field`, `foreign_db`, `foreign_table`, `foreign_field`) VALUES ('here type nameof CentralDB', 'todo', 'assignTo', 'here type nameof CentralDB', 'user', 'user_id');
INSERT INTO `pma_relation` (`master_db`, `master_table`, `master_field`, `foreign_db`, `foreign_table`, `foreign_field`) VALUES ('here type nameof CentralDB', 'faculte', 'code', 'here type nameof CentralDB', 'cours', 'faculte');


é&("§     REMOVE THIS LINE and  change the NEXT
UPDATE `pma_relation` set `master_db` = 'here type nameof CentralDB',`foreign_db` = 'here type nameof CentralDB'
