

# Claroline120RC2 SQL file for creating main claroline DB

DROP DATABASE IF EXISTS claroline;

CREATE DATABASE claroline;


DROP TABLE IF EXISTS admin;

CREATE TABLE admin (
  admin.idUser mediumint unsigned  NOT NULL default '0',
  UNIQUE KEY idUser (idUser));



DROP TABLE IF EXISTS annonces;

CREATE TABLE annonces (
  annonces.id mediumint(11) NOT NULL auto_increment,
  annonces.contenu text,
  annonces.temps date default NULL,
  annonces.code_cours varchar(20) default NULL,
  annonces.ordre mediumint(11) NOT NULL,
  PRIMARY KEY  (id));



DROP TABLE IF EXISTS cours;

CREATE TABLE cours (
  cours.cours_id int(11) NOT NULL auto_increment,
  cours.code varchar(20) default NULL,
  cours.languageCourse varchar(15) default NULL,
  cours.intitule varchar(250) default NULL,
  cours.description text,
  cours.faculte varchar(12) default NULL,
  cours.visible tinyint(4) default NULL,
  cours.cahier_charges varchar(250) default NULL,
  cours.scoreShow int(11) NOT NULL default 1,
  cours.titulaires varchar(200) default NULL,
  cours.fake_code varchar(20) default NULL,
  PRIMARY KEY  (cours_id));



DROP TABLE IF EXISTS cours_faculte;

CREATE TABLE cours_faculte (
  cours_faculte.id int(11) NOT NULL auto_increment,
  cours_faculte.faculte varchar(12) NOT NULL,
  cours_user.code varchar(20) NOT NULL,
  PRIMARY KEY  (id));



DROP TABLE IF EXISTS cours_user;

CREATE TABLE cours_user (
  cours_user.code_cours varchar(30) NOT NULL default '0',
  cours_user.user_id int(11) unsigned NOT NULL default '0',
  cours_user.statut tinyint(4) NOT NULL default '0',
  cours_user.role varchar(60) default NULL,
  cours_user.team int(11) NOT NULL default '0',
  PRIMARY KEY  (code_cours,user_id));




DROP TABLE IF EXISTS faculte;

CREATE TABLE faculte (
  faculte.id int(11) NOT NULL auto_increment,
  faculte.code varchar(10) NOT NULL,
  faculte.name varchar(100) NOT NULL,
  faculte.number int(11) NOT NULL default 0,
  PRIMARY KEY  (id),
  UNIQUE KEY number (number));


INSERT INTO faculte VALUES ( '1', 'ARTS', 'Department of Arts', '1');
INSERT INTO faculte VALUES ( '2', 'ECO', 'Department of Economics', '2');
INSERT INTO faculte VALUES ( '3', 'PSYCHO', 'Department of Psychology', '3');
INSERT INTO faculte VALUES ( '4', 'MD', 'Medicine', '4');
INSERT INTO faculte VALUES ( '5', 'SC', 'Sciences', '5');
INSERT INTO faculte VALUES ( '6', 'APSC', 'Applied sciences', '6');
INSERT INTO faculte VALUES ( '7', 'AGRO', 'Agronomy', '7');
INSERT INTO faculte VALUES ( '8', 'LING', 'Department of Linguistics', '8');
INSERT INTO faculte VALUES ( '9', 'LAW', 'Department of Law', '9');
INSERT INTO faculte VALUES ( '10', 'MBA', 'Masters in Business Administration', '10');




DROP TABLE IF EXISTS todo;

CREATE TABLE todo (
  todo.id mediumint(9) NOT NULL auto_increment,
  todo.contenu text,
  todo. temps datetime default '0000-00-00 00:00:00',
  todo.auteur varchar(80) default NULL,
  todo.email varchar(80) default NULL,
  todo.priority tinyint(4) default '0',
  todo.type varchar(8) default NULL,
  todo.cible varchar(30) default NULL,
  todo.statut varchar(8) default NULL,
  todo.assignTo mediumint(9) default NULL,
  todo.showToUsers enum('YES','NO') NOT NULL default 'YES',
  PRIMARY KEY  (id),
  KEY temps (temps));



DROP TABLE IF EXISTS user;

CREATE TABLE user (
  user.user_id mediumint unsigned NOT NULL auto_increment,
  user.nom varchar(60) default NULL,
  user.prenom varchar(60) default NULL,
  user.username varchar(20) default 'empty',
  password varchar(50) default 'empty',
  user.email varchar(100) default NULL,
  user.statut tinyint(4) default NULL,
  PRIMARY KEY  (user_id));



DROP TABLE IF EXISTS loginout;

CREATE TABLE loginout (
  loginout.idLog mediumint(9) unsigned NOT NULL auto_increment,
  loginout.id_user mediumint(9) unsigned NOT NULL default '0',
  loginout.ip char(16) NOT NULL default '0.0.0.0',
  loginout.when datetime NOT NULL default '0000-00-00 00:00:00',
  loginout.action enum('LOGIN','LOGOUT') NOT NULL default 'LOGIN',
  PRIMARY KEY  (idLog));

# END







