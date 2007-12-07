# phpMyAdmin SQL Dump
# version 2.5.3
# http://www.phpmyadmin.net
#
# Serveur: localhost
# Généré le : Mercredi 07 Juillet 2004 à 17:18
# Version du serveur: 4.0.15
# Version de PHP: 4.3.3
# 
# Base de données: `claroline`
# 

# --------------------------------------------------------

#
# Structure de la table `admin`
#
# Création: Mercredi 07 Juillet 2004 à 17:16
# Dernière modification: Mercredi 07 Juillet 2004 à 17:16
#

CREATE TABLE `admin` (
  `idUser` mediumint(8) unsigned NOT NULL default '0',
  UNIQUE KEY `idUser` (`idUser`)
) TYPE=MyISAM;

#
# Contenu de la table `admin`
#

INSERT INTO `admin` (`idUser`) VALUES (1);

# --------------------------------------------------------

#
# Structure de la table `cours`
#
# Création: Mercredi 07 Juillet 2004 à 17:16
# Dernière modification: Mercredi 07 Juillet 2004 à 17:16
#

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
  `titulaires` varchar(255) default NULL,
  `email` varchar(255) default NULL,
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
  PRIMARY KEY  (`cours_id`),
  KEY `fake_code` (`fake_code`),
  KEY `faculte` (`faculte`)
) TYPE=MyISAM COMMENT='data of courses' AUTO_INCREMENT=1 ;

#
# Contenu de la table `cours`
#


# --------------------------------------------------------

#
# Structure de la table `cours_user`
#
# Création: Mercredi 07 Juillet 2004 à 17:16
# Dernière modification: Mercredi 07 Juillet 2004 à 17:16
#

CREATE TABLE `cours_user` (
  `code_cours` varchar(40) NOT NULL default '0',
  `user_id` int(11) unsigned NOT NULL default '0',
  `statut` tinyint(4) NOT NULL default '5',
  `role` varchar(60) default NULL,
  `team` int(11) NOT NULL default '0',
  `tutor` int(11) NOT NULL default '0',
  PRIMARY KEY  (`code_cours`,`user_id`),
  KEY `statut` (`statut`)
) TYPE=MyISAM;

#
# Contenu de la table `cours_user`
#


# --------------------------------------------------------

#
# Structure de la table `course_tool`
#
# Création: Mercredi 07 Juillet 2004 à 17:16
# Dernière modification: Mercredi 07 Juillet 2004 à 17:16
#

CREATE TABLE `course_tool` (
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
) TYPE=MyISAM COMMENT='based definiton of the claroline tool used in each course' AUTO_INCREMENT=12 ;

#
# Contenu de la table `course_tool`
#

INSERT INTO `course_tool` (`id`, `claro_label`, `script_url`, `icon`, `def_access`, `def_rank`, `add_in_course`, `access_manager`) VALUES (1, 'CLDSC___', 'course_description/index.php', 'info.gif', 'ALL', 1, 'AUTOMATIC', 'COURSE_ADMIN'),
(2, 'CLCAL___', 'calendar/agenda.php', 'agenda.gif', 'ALL', 2, 'AUTOMATIC', 'COURSE_ADMIN'),
(3, 'CLANN___', 'announcements/announcements.php', 'valves.gif', 'ALL', 3, 'AUTOMATIC', 'COURSE_ADMIN'),
(4, 'CLDOC___', 'document/document.php', 'documents.gif', 'ALL', 4, 'AUTOMATIC', 'COURSE_ADMIN'),
(5, 'CLQWZ___', 'exercice/exercice.php', 'quiz.gif', 'ALL', 5, 'AUTOMATIC', 'COURSE_ADMIN'),
(6, 'CLLNP___', 'learnPath/learningPathList.php', 'step.gif', 'ALL', 6, 'AUTOMATIC', 'COURSE_ADMIN'),
(7, 'CLWRK___', 'work/work.php', 'works.gif', 'ALL', 7, 'AUTOMATIC', 'COURSE_ADMIN'),
(8, 'CLFRM___', 'phpbb/index.php', 'forum.gif', 'ALL', 8, 'AUTOMATIC', 'COURSE_ADMIN'),
(9, 'CLGRP___', 'group/group.php', 'group.gif', 'ALL', 9, 'AUTOMATIC', 'COURSE_ADMIN'),
(10, 'CLUSR___', 'user/user.php', 'membres.gif', 'ALL', 10, 'AUTOMATIC', 'COURSE_ADMIN'),
(11, 'CLCHT___', 'chat/chat.php', 'forum.gif', 'ALL', 11, 'AUTOMATIC', 'COURSE_ADMIN');

# --------------------------------------------------------

#
# Structure de la table `faculte`
#
# Création: Mercredi 07 Juillet 2004 à 17:16
# Dernière modification: Mercredi 07 Juillet 2004 à 17:16
#

CREATE TABLE `faculte` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  `code` varchar(12) NOT NULL default '',
  `code_P` varchar(40) default NULL,
  `bc` varchar(255) default NULL,
  `treePos` int(10) unsigned default NULL,
  `nb_childs` smallint(6) default NULL,
  `canHaveCoursesChild` enum('TRUE','FALSE') default 'TRUE',
  `canHaveCatChild` enum('TRUE','FALSE') default 'TRUE',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `code_P` (`code_P`),
  KEY `treePos` (`treePos`)
) TYPE=MyISAM AUTO_INCREMENT=6 ;

#
# Contenu de la table `faculte`
#

INSERT INTO `faculte` (`id`, `name`, `code`, `code_P`, `bc`, `treePos`, `nb_childs`, `canHaveCoursesChild`, `canHaveCatChild`) VALUES (1, 'Sciences', 'SC', NULL, NULL, 1, 0, 'TRUE', 'TRUE'),
(2, 'Economics', 'ECO', NULL, NULL, 2, 0, 'TRUE', 'TRUE'),
(3, 'Humanities', 'HUMA', NULL, NULL, 3, 0, 'TRUE', 'TRUE'),
(4, 'Psychology', 'PSYCHO', NULL, NULL, 4, 0, 'TRUE', 'TRUE'),
(5, 'Medicine', 'MD', NULL, NULL, 5, 0, 'TRUE', 'TRUE');

# --------------------------------------------------------

#
# Structure de la table `track_c_browsers`
#
# Création: Mercredi 07 Juillet 2004 à 17:16
# Dernière modification: Mercredi 07 Juillet 2004 à 17:16
#

CREATE TABLE `track_c_browsers` (
  `id` int(11) NOT NULL auto_increment,
  `browser` varchar(255) NOT NULL default '',
  `counter` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM COMMENT='record browsers occurences' AUTO_INCREMENT=1 ;

#
# Contenu de la table `track_c_browsers`
#


# --------------------------------------------------------

#
# Structure de la table `track_c_countries`
#
# Création: Mercredi 07 Juillet 2004 à 17:16
# Dernière modification: Mercredi 07 Juillet 2004 à 17:16
#

CREATE TABLE `track_c_countries` (
  `id` int(11) NOT NULL auto_increment,
  `code` varchar(40) NOT NULL default '',
  `country` varchar(50) NOT NULL default '',
  `counter` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=265 ;

#
# Contenu de la table `track_c_countries`
#

INSERT INTO `track_c_countries` (`id`, `code`, `country`, `counter`) VALUES (1, 'ac', 'Ascension (ile)', 0),
(2, 'ad', 'Andorre', 0),
(3, 'ae', 'Emirats  Arabes Unis', 0),
(4, 'af', 'Afghanistan', 0),
(5, 'ag', 'Antigua et Barbuda', 0),
(6, 'ai', 'Anguilla', 0),
(7, 'al', 'Albanie', 0),
(8, 'am', 'Arménie', 0),
(9, 'an', 'Antilles Neerlandaises', 0),
(10, 'ao', 'Angola', 0),
(11, 'aq', 'Antarctique', 0),
(12, 'ar', 'Argentine', 0),
(13, 'as', 'American Samoa', 0),
(14, 'au', 'Australie', 0),
(15, 'aw', 'Aruba', 0),
(16, 'az', 'Azerbaijan', 0),
(17, 'ba', 'Bosnie Herzegovine', 0),
(18, 'bb', 'Barbade', 0),
(19, 'bd', 'Bangladesh', 0),
(20, 'be', 'Belgique', 0),
(21, 'bf', 'Burkina Faso', 0),
(22, 'bg', 'Bulgarie', 0),
(23, 'bh', 'Bahrain', 0),
(24, 'bi', 'Burundi', 0),
(25, 'bj', 'Benin', 0),
(26, 'bm', 'Bermudes', 0),
(27, 'bn', 'Brunei Darussalam', 0),
(28, 'bo', 'Bolivie', 0),
(29, 'br', 'Brésil', 0),
(30, 'bs', 'Bahamas', 0),
(31, 'bt', 'Bhoutan', 0),
(32, 'bv', 'Bouvet (ile)', 0),
(33, 'bw', 'Botswana', 0),
(34, 'by', 'Biélorussie', 0),
(35, 'bz', 'Bélize', 0),
(36, 'ca', 'Canada', 0),
(37, 'cc', 'Cocos (Keeling) iles', 0),
(38, 'cd', 'Congo, (République démocratique du)', 0),
(39, 'cf', 'Centrafricaine (République )', 0),
(40, 'cg', 'Congo', 0),
(41, 'ch', 'Suisse', 0),
(42, 'ci', 'Cote d\'Ivoire', 0),
(43, 'ck', 'Cook (iles)', 0),
(44, 'cl', 'Chili', 0),
(45, 'cm', 'Cameroun', 0),
(46, 'cn', 'Chine', 0),
(47, 'co', 'Colombie', 0),
(48, 'cr', 'Costa Rica', 0),
(49, 'cu', 'Cuba', 0),
(50, 'cv', 'Cap Vert', 0),
(51, 'cx', 'Christmas (ile)', 0),
(52, 'cy', 'Chypre', 0),
(53, 'cz', 'Tchéque (République)', 0),
(54, 'de', 'Allemagne', 0),
(55, 'dj', 'Djibouti', 0),
(56, 'dk', 'Danemark', 0),
(57, 'dm', 'Dominique', 0),
(58, 'do', 'Dominicaine (république)', 0),
(59, 'dz', 'Algérie', 0),
(60, 'ec', 'Equateur', 0),
(61, 'ee', 'Estonie', 0),
(62, 'eg', 'Egypte', 0),
(63, 'eh', 'Sahara Occidental', 0),
(64, 'er', 'Erythrée', 0),
(65, 'es', 'Espagne', 0),
(66, 'et', 'Ethiopie', 0),
(67, 'fi', 'Finlande', 0),
(68, 'fj', 'Fiji', 0),
(69, 'fk', 'Falkland (Malouines) iles', 0),
(70, 'fm', 'Micronésie', 0),
(71, 'fo', 'Faroe (iles)', 0),
(72, 'fr', 'France', 0),
(73, 'ga', 'Gabon', 0),
(74, 'gd', 'Grenade', 0),
(75, 'ge', 'Géorgie', 0),
(76, 'gf', 'Guyane Française', 0),
(77, 'gg', 'Guernsey', 0),
(78, 'gh', 'Ghana', 0),
(79, 'gi', 'Gibraltar', 0),
(80, 'gl', 'Groenland', 0),
(81, 'gm', 'Gambie', 0),
(82, 'gn', 'Guinée', 0),
(83, 'gp', 'Guadeloupe', 0),
(84, 'gq', 'Guinée Equatoriale', 0),
(85, 'gr', 'Grèce', 0),
(86, 'gs', 'Georgie du sud et iles Sandwich du sud', 0),
(87, 'gt', 'Guatemala', 0),
(88, 'gu', 'Guam', 0),
(89, 'gw', 'Guinée-Bissau', 0),
(90, 'gy', 'Guyana', 0),
(91, 'hk', 'Hong Kong', 0),
(92, 'hm', 'Heard et McDonald (iles)', 0),
(93, 'hn', 'Honduras', 0),
(94, 'hr', 'Croatie', 0),
(95, 'ht', 'Haiti', 0),
(96, 'hu', 'Hongrie', 0),
(97, 'id', 'Indonésie', 0),
(98, 'ie', 'Irlande', 0),
(99, 'il', 'Israël', 0),
(100, 'im', 'Ile de Man', 0),
(101, 'in', 'Inde', 0),
(102, 'io', 'Territoire Britannique de l\'Océan Indien', 0),
(103, 'iq', 'Iraq', 0),
(104, 'ir', 'Iran', 0),
(105, 'is', 'Islande', 0),
(106, 'it', 'Italie', 0),
(107, 'je', 'Jersey', 0),
(108, 'jm', 'Jamaïque', 0),
(109, 'jo', 'Jordanie', 0),
(110, 'jp', 'Japon', 0),
(111, 'ke', 'Kenya', 0),
(112, 'kg', 'Kirgizstan', 0),
(113, 'kh', 'Cambodge', 0),
(114, 'ki', 'Kiribati', 0),
(115, 'km', 'Comores', 0),
(116, 'kn', 'Saint Kitts et Nevis', 0),
(117, 'kp', 'Corée du nord', 0),
(118, 'kr', 'Corée du sud', 0),
(119, 'kw', 'Koweït', 0),
(120, 'ky', 'Caïmanes (iles)', 0),
(121, 'kz', 'Kazakhstan', 0),
(122, 'la', 'Laos', 0),
(123, 'lb', 'Liban', 0),
(124, 'lc', 'Sainte Lucie', 0),
(125, 'li', 'Liechtenstein', 0),
(126, 'lk', 'Sri Lanka', 0),
(127, 'lr', 'Liberia', 0),
(128, 'ls', 'Lesotho', 0),
(129, 'lt', 'Lituanie', 0),
(130, 'lu', 'Luxembourg', 0),
(131, 'lv', 'Latvia', 0),
(132, 'ly', 'Libyan Arab Jamahiriya', 0),
(133, 'ma', 'Maroc', 0),
(134, 'mc', 'Monaco', 0),
(135, 'md', 'Moldavie', 0),
(136, 'mg', 'Madagascar', 0),
(137, 'mh', 'Marshall (iles)', 0),
(138, 'mk', 'Macédoine', 0),
(139, 'ml', 'Mali', 0),
(140, 'mm', 'Myanmar', 0),
(141, 'mn', 'Mongolie', 0),
(142, 'mo', 'Macao', 0),
(143, 'mp', 'Mariannes du nord (iles)', 0),
(144, 'mq', 'Martinique', 0),
(145, 'mr', 'Mauritanie', 0),
(146, 'ms', 'Montserrat', 0),
(147, 'mt', 'Malte', 0),
(148, 'mu', 'Maurice (ile)', 0),
(149, 'mv', 'Maldives', 0),
(150, 'mw', 'Malawi', 0),
(151, 'mx', 'Mexique', 0),
(152, 'my', 'Malaisie', 0),
(153, 'mz', 'Mozambique', 0),
(154, 'na', 'Namibie', 0),
(155, 'nc', 'Nouvelle Calédonie', 0),
(156, 'ne', 'Niger', 0),
(157, 'nf', 'Norfolk (ile)', 0),
(158, 'ng', 'Nigéria', 0),
(159, 'ni', 'Nicaragua', 0),
(160, 'nl', 'Pays Bas', 0),
(161, 'no', 'Norvège', 0),
(162, 'np', 'Népal', 0),
(163, 'nr', 'Nauru', 0),
(164, 'nu', 'Niue', 0),
(165, 'nz', 'Nouvelle Zélande', 0),
(166, 'om', 'Oman', 0),
(167, 'pa', 'Panama', 0),
(168, 'pe', 'Pérou', 0),
(169, 'pf', 'Polynésie Française', 0),
(170, 'pg', 'Papouasie Nouvelle Guinée', 0),
(171, 'ph', 'Philippines', 0),
(172, 'pk', 'Pakistan', 0),
(173, 'pl', 'Pologne', 0),
(174, 'pm', 'St. Pierre et Miquelon', 0),
(175, 'pn', 'Pitcairn (ile)', 0),
(176, 'pr', 'Porto Rico', 0),
(177, 'pt', 'Portugal', 0),
(178, 'pw', 'Palau', 0),
(179, 'py', 'Paraguay', 0),
(180, 'qa', 'Qatar', 0),
(181, 're', 'Réunion (ile de la)', 0),
(182, 'ro', 'Roumanie', 0),
(183, 'ru', 'Russie', 0),
(184, 'rw', 'Rwanda', 0),
(185, 'sa', 'Arabie Saoudite', 0),
(186, 'sb', 'Salomon (iles)', 0),
(187, 'sc', 'Seychelles', 0),
(188, 'sd', 'Soudan', 0),
(189, 'se', 'Suède', 0),
(190, 'sg', 'Singapour', 0),
(191, 'sh', 'St. Hélène', 0),
(192, 'si', 'Slovénie', 0),
(193, 'sj', 'Svalbard et Jan Mayen (iles)', 0),
(194, 'sk', 'Slovaquie', 0),
(195, 'sl', 'Sierra Leone', 0),
(196, 'sm', 'Saint Marin', 0),
(197, 'sn', 'Sénégal', 0),
(198, 'so', 'Somalie', 0),
(199, 'sr', 'Suriname', 0),
(200, 'st', 'Sao Tome et Principe', 0),
(201, 'sv', 'Salvador', 0),
(202, 'sy', 'Syrie', 0),
(203, 'sz', 'Swaziland', 0),
(204, 'tc', 'Turks et Caïques (iles)', 0),
(205, 'td', 'Tchad', 0),
(206, 'tf', 'Territoires Français du sud', 0),
(207, 'tg', 'Togo', 0),
(208, 'th', 'Thailande', 0),
(209, 'tj', 'Tajikistan', 0),
(210, 'tk', 'Tokelau', 0),
(211, 'tm', 'Turkménistan', 0),
(212, 'tn', 'Tunisie', 0),
(213, 'to', 'Tonga', 0),
(214, 'tp', 'Timor Oriental', 0),
(215, 'tr', 'Turquie', 0),
(216, 'tt', 'Trinidad et Tobago', 0),
(217, 'tv', 'Tuvalu', 0),
(218, 'tw', 'Taiwan', 0),
(219, 'tz', 'Tanzanie', 0),
(220, 'ua', 'Ukraine', 0),
(221, 'ug', 'Ouganda', 0),
(222, 'uk', 'Royaume Uni', 0),
(223, 'gb', 'Royaume Uni', 0),
(224, 'um', 'US Minor Outlying (iles)', 0),
(225, 'us', 'Etats Unis', 0),
(226, 'uy', 'Uruguay', 0),
(227, 'uz', 'Ouzbékistan', 0),
(228, 'va', 'Vatican', 0),
(229, 'vc', 'Saint Vincent et les Grenadines', 0),
(230, 've', 'Venezuela', 0),
(231, 'vg', 'Vierges Britaniques (iles)', 0),
(232, 'vi', 'Vierges USA (iles)', 0),
(233, 'vn', 'Viêt Nam', 0),
(234, 'vu', 'Vanuatu', 0),
(235, 'wf', 'Wallis et Futuna (iles)', 0),
(236, 'ws', 'Western Samoa', 0),
(237, 'ye', 'Yemen', 0),
(238, 'yt', 'Mayotte', 0),
(239, 'yu', 'Yugoslavie', 0),
(240, 'za', 'Afrique du Sud', 0),
(241, 'zm', 'Zambie', 0),
(242, 'zr', 'Zaïre', 0),
(243, 'zw', 'Zimbabwe', 0),
(244, 'com', '.COM', 0),
(245, 'net', '.NET', 0),
(246, 'org', '.ORG', 0),
(247, 'edu', 'Education', 0),
(248, 'int', '.INT', 0),
(249, 'arpa', '.ARPA', 0),
(250, 'at', 'Autriche', 0),
(251, 'gov', 'Gouvernement', 0),
(252, 'mil', 'Militaire', 0),
(253, 'su', 'Ex U.R.S.S.', 0),
(254, 'reverse', 'Reverse', 0),
(255, 'biz', 'Businesses', 0),
(256, 'info', '.INFO', 0),
(257, 'name', '.NAME', 0),
(258, 'pro', '.PRO', 0),
(259, 'coop', '.COOP', 0),
(260, 'aero', '.AERO', 0),
(261, 'museum', '.MUSEUM', 0),
(262, 'tv', '.TV', 0),
(263, 'ws', 'Web site', 0),
(264, '--', 'Unknown', 0);

# --------------------------------------------------------

#
# Structure de la table `track_c_os`
#
# Création: Mercredi 07 Juillet 2004 à 17:16
# Dernière modification: Mercredi 07 Juillet 2004 à 17:16
#

CREATE TABLE `track_c_os` (
  `id` int(11) NOT NULL auto_increment,
  `os` varchar(255) NOT NULL default '',
  `counter` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM COMMENT='record OS occurences' AUTO_INCREMENT=1 ;

#
# Contenu de la table `track_c_os`
#


# --------------------------------------------------------

#
# Structure de la table `track_c_providers`
#
# Création: Mercredi 07 Juillet 2004 à 17:16
# Dernière modification: Mercredi 07 Juillet 2004 à 17:16
#

CREATE TABLE `track_c_providers` (
  `id` int(11) NOT NULL auto_increment,
  `provider` varchar(255) NOT NULL default '',
  `counter` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM COMMENT='list of providers used by users and number of occurences' AUTO_INCREMENT=1 ;

#
# Contenu de la table `track_c_providers`
#


# --------------------------------------------------------

#
# Structure de la table `track_c_referers`
#
# Création: Mercredi 07 Juillet 2004 à 17:16
# Dernière modification: Mercredi 07 Juillet 2004 à 17:16
#

CREATE TABLE `track_c_referers` (
  `id` int(11) NOT NULL auto_increment,
  `referer` varchar(255) NOT NULL default '',
  `counter` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM COMMENT='record refering url occurences' AUTO_INCREMENT=1 ;

#
# Contenu de la table `track_c_referers`
#


# --------------------------------------------------------

#
# Structure de la table `track_e_default`
#
# Création: Mercredi 07 Juillet 2004 à 17:16
# Dernière modification: Mercredi 07 Juillet 2004 à 17:16
#

CREATE TABLE `track_e_default` (
  `default_id` int(11) NOT NULL auto_increment,
  `default_user_id` int(10) NOT NULL default '0',
  `default_cours_code` varchar(40) NOT NULL default '',
  `default_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `default_event_type` varchar(20) NOT NULL default '',
  `default_value_type` varchar(20) NOT NULL default '',
  `default_value` tinytext NOT NULL,
  PRIMARY KEY  (`default_id`)
) TYPE=MyISAM COMMENT='Use for other develloppers users' AUTO_INCREMENT=1 ;

#
# Contenu de la table `track_e_default`
#


# --------------------------------------------------------

#
# Structure de la table `track_e_login`
#
# Création: Mercredi 07 Juillet 2004 à 17:16
# Dernière modification: Mercredi 07 Juillet 2004 à 17:16
#

CREATE TABLE `track_e_login` (
  `login_id` int(11) NOT NULL auto_increment,
  `login_user_id` int(10) NOT NULL default '0',
  `login_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `login_ip` char(15) NOT NULL default '',
  PRIMARY KEY  (`login_id`)
) TYPE=MyISAM COMMENT='Record informations about logins' AUTO_INCREMENT=1 ;

#
# Contenu de la table `track_e_login`
#


# --------------------------------------------------------

#
# Structure de la table `track_e_open`
#
# Création: Mercredi 07 Juillet 2004 à 17:16
# Dernière modification: Mercredi 07 Juillet 2004 à 17:16
#

CREATE TABLE `track_e_open` (
  `open_id` int(11) NOT NULL auto_increment,
  `open_date` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`open_id`)
) TYPE=MyISAM COMMENT='Record informations about software used by users' AUTO_INCREMENT=1 ;

#
# Contenu de la table `track_e_open`
#


# --------------------------------------------------------

#
# Structure de la table `user`
#
# Création: Mercredi 07 Juillet 2004 à 17:16
# Dernière modification: Mercredi 07 Juillet 2004 à 17:17
#

CREATE TABLE `user` (
  `user_id` mediumint(8) unsigned NOT NULL auto_increment,
  `nom` varchar(60) default NULL,
  `prenom` varchar(60) default NULL,
  `username` varchar(20) default 'empty',
  `password` varchar(50) default 'empty',
  `authSource` varchar(50) default 'claroline',
  `email` varchar(100) default NULL,
  `statut` tinyint(4) default NULL,
  `officialCode` varchar(40) default NULL,
  `phoneNumber` varchar(30) default NULL,
  `pictureUri` varchar(250) default NULL,
  `creatorId` mediumint(8) unsigned default NULL,
  PRIMARY KEY  (`user_id`),
  KEY `loginpass` (`username`,`password`)
) TYPE=MyISAM AUTO_INCREMENT=2 ;

#
# Contenu de la table `user`
#

INSERT INTO `user` (`user_id`, `nom`, `prenom`, `username`, `password`, `authSource`, `email`, `statut`, `officialCode`, `phoneNumber`, `pictureUri`, `creatorId`) VALUES (1, 'Doe', 'John', 'login', 'password', 'claroline', 'mee@foo.com', 1, NULL, NULL, NULL, 0);
