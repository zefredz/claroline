# $Id$ #

# this  SQL update Main database of  claroline from 111 to 120RC2

############### NEW TABLE

# table `admin`

CREATE TABLE `admin` (
  `admin`.`idUser` mediumint UNSIGNED NOT NULL default '0',
  UNIQUE KEY `idUser` (`idUser`)
) TYPE=MyISAM COMMENT='id des Utilisateurs Administrateurs';

# table `loginout`
CREATE TABLE loginout (
  `loginout`.`idLog` mediumint unsigned NOT NULL auto_increment,
  `loginout`.`id_user` mediumint unsigned NOT NULL default '0',
  `loginout`.`ip` char(16) NOT NULL default '0.0.0.0',
  `loginout`.`when` datetime NOT NULL default '0000-00-00 00:00:00',
  `loginout`.`action` enum('LOGIN','LOGOUT') NOT NULL default 'LOGIN',
  PRIMARY KEY  (idLog)
) TYPE=MyISAM;

############## Alter Table
#  table `annonces`
ALTER TABLE `annonces` ADD `ordre` mediumint NOT NULL after `code_cours`;  

#  table `cours_user`
ALTER TABLE `cours_user` CHANGE `cours_user`.`user_id` `cours_user`.`user_id` MEDIUMINT UNSIGNED DEFAULT '0' NOT NULL ;

# table `todo`
ALTER TABLE `todo` ADD `auteur` varchar(80) default NULL after `temps`;  
ALTER TABLE `todo` ADD `idAuteur` mediumint UNSIGNED default NULL after `auteur`;  
ALTER TABLE `todo` ADD `email` varchar(80) default NULL after `idAuteur`;  
ALTER TABLE `todo` ADD `priority` tinyint(4) default '0' after `email`;  
ALTER TABLE `todo` ADD `type` varchar(8) default NULL after `priority`;  
ALTER TABLE `todo` ADD `cible` varchar(30) default NULL after `type`;  
ALTER TABLE `todo` ADD `statut` varchar(8) default NULL after `cible`;  
ALTER TABLE `todo` ADD `assignTo` mediumint UNSIGNED default NULL after `statut`;  
ALTER TABLE `todo` ADD `showToUsers` enum('YES','NO') NOT NULL default 'YES' after `assignTo`;  

# table `user`
ALTER TABLE `user` CHANGE `user_id` `user_id` MEDIUMINT UNSIGNED DEFAULT '0' NOT NULL  auto_increment;

############## Drop Table
#  table `document`
DROP TABLE `document` ;
DROP TABLE `work` ;

################## Unchanged tables
# table `cours`
# table `cours_faculte`
# table `faculte`

