#
# Structure de la table `access`
#

CREATE TABLE `access` (
  `access_id` int(10) NOT NULL auto_increment,
  `access_title` varchar(20) default NULL,
  PRIMARY KEY  (`access_id`)
) TYPE=MyISAM;

#
# Contenu de la table `access`
INSERT INTO `access` (`access_id`, `access_title`) VALUES (-1, 'Deleted');
INSERT INTO `access` (`access_id`, `access_title`) VALUES (1, 'User');
INSERT INTO `access` (`access_id`, `access_title`) VALUES (2, 'Moderator');
INSERT INTO `access` (`access_id`, `access_title`) VALUES (3, 'Super Moderator');
INSERT INTO `access` (`access_id`, `access_title`) VALUES (4, 'Administrator');
# --------------------------------------------------------

# Structure de la table `accueil`
CREATE TABLE `accueil` (
  `id` int(11) NOT NULL auto_increment,
  `rubrique` varchar(100) default NULL,
  `lien` varchar(255) default NULL,
  `image` varchar(100) default NULL,
  `visible` tinyint(4) default NULL,
  `admin` varchar(200) default NULL,
  `address` varchar(120) default NULL,
  `addedTool` enum('YES','NO') default 'YES',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

#
# Contenu de la table `accueil`
#

INSERT INTO `accueil` (`id`, `rubrique`, `lien`, `image`, `visible`, `admin`, `address`, `addedTool`) VALUES (1, 'Agenda', '../claroline/calendar/agenda.php', '../claroline/img/agenda.png', 1, '0', '../claroline/img/pastillegris.png', 'NO');
INSERT INTO `accueil` (`id`, `rubrique`, `lien`, `image`, `visible`, `admin`, `address`, `addedTool`) VALUES (2, 'Liens', '../claroline/link/link.php', '../claroline/img/liens.png', 1, '0', '../claroline/img/pastillegris.png', 'NO');
INSERT INTO `accueil` (`id`, `rubrique`, `lien`, `image`, `visible`, `admin`, `address`, `addedTool`) VALUES (3, 'Documents', '../claroline/document/document.php', '../claroline/img/documents.png', 0, '0', '../claroline/img/pastillegris.png', 'NO');
INSERT INTO `accueil` (`id`, `rubrique`, `lien`, `image`, `visible`, `admin`, `address`, `addedTool`) VALUES (4, 'Travaux', '../claroline/work/work.php', '../claroline/img/works.gif', 1, '0', '../claroline/img/pastillegris.png', 'NO');
INSERT INTO `accueil` (`id`, `rubrique`, `lien`, `image`, `visible`, `admin`, `address`, `addedTool`) VALUES (5, 'Annonces', '../claroline/announcements/announcements.php', '../claroline/img/valves.png', 1, '0', '../claroline/img/pastillegris.png', 'NO');
INSERT INTO `accueil` (`id`, `rubrique`, `lien`, `image`, `visible`, `admin`, `address`, `addedTool`) VALUES (6, 'Utilisateurs', '../claroline/user/user.php', '../claroline/img/membres.png', 1, '0', '../claroline/img/pastillegris.png', 'NO');
INSERT INTO `accueil` (`id`, `rubrique`, `lien`, `image`, `visible`, `admin`, `address`, `addedTool`) VALUES (7, 'Forums', '../claroline/phpbb/index.php', '../claroline/img/forum.png', 1, '0', '../claroline/img/pastillegris.png', 'NO');
INSERT INTO `accueil` (`id`, `rubrique`, `lien`, `image`, `visible`, `admin`, `address`, `addedTool`) VALUES (8, 'Exercices', '../claroline/exercice/exercice.php', '../claroline/img/quiz.png', 1, '0', '../claroline/img/pastillegris.png', 'NO');
INSERT INTO `accueil` (`id`, `rubrique`, `lien`, `image`, `visible`, `admin`, `address`, `addedTool`) VALUES (9, 'Groupes', '../claroline/group/group.php', '../claroline/img/group.png', 1, '0', '../claroline/img/pastillegris.png', 'NO');
INSERT INTO `accueil` (`id`, `rubrique`, `lien`, `image`, `visible`, `admin`, `address`, `addedTool`) VALUES (10, 'Description du cours', '../claroline/course_description/', '../claroline/img/info.gif', 1, '0', '../claroline/img/pastillegris.png', 'NO');
INSERT INTO `accueil` (`id`, `rubrique`, `lien`, `image`, `visible`, `admin`, `address`, `addedTool`) VALUES (11, 'Discuter', '../claroline/chat/chat.php', '../claroline/img/forum.png', 0, '0', '../claroline/img/pastillegris.png', 'NO');
INSERT INTO `accueil` (`id`, `rubrique`, `lien`, `image`, `visible`, `admin`, `address`, `addedTool`) VALUES (12, 'Statistiques', '../claroline/tracking/courseLog.php', '../claroline/img/statistiques.png', 0, '1', '', 'NO');
INSERT INTO `accueil` (`id`, `rubrique`, `lien`, `image`, `visible`, `admin`, `address`, `addedTool`) VALUES (13, 'Ajouter un lien sur la page d\'accueil', '../claroline/external_module/external_module.php?', '../claroline/img/npage.png', 0, '1', '', 'NO');
INSERT INTO `accueil` (`id`, `rubrique`, `lien`, `image`, `visible`, `admin`, `address`, `addedTool`) VALUES (14, 'Propriétés du cours', '../claroline/course_info/infocours.php?', '../claroline/img/referencement.png', 0, '1', '', 'NO');
# --------------------------------------------------------

# Structure de la table `agenda`

CREATE TABLE `agenda` (
  `id` int(11) NOT NULL auto_increment,
  `titre` varchar(200) default NULL,
  `contenu` text,
  `day` date NOT NULL default '0000-00-00',
  `hour` time NOT NULL default '00:00:00',
  `lasting` varchar(20) default NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

# Contenu de la table `agenda`
INSERT INTO `agenda` (`id`, `titre`, `contenu`, `day`, `hour`, `lasting`) VALUES (1, 'creation', 'creation', now(), now(), '');
# --------------------------------------------------------

# Structure de la table `annonces`
CREATE TABLE `annonces` (
  `id` mediumint(11) NOT NULL auto_increment,
  `contenu` text,
  `temps` date default NULL,
  `code_cours` varchar(40) default NULL,
  `ordre` mediumint(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM COMMENT='announcements table';

# Contenu de la table `annonces`
INSERT INTO `annonces` (`id`, `contenu`, `temps`, `code_cours`, `ordre`) VALUES (1, 'Ceci est un exemple d\'annonce.', now(), 'TEST', 1);
# --------------------------------------------------------

# Structure de la table `banlist`
CREATE TABLE `banlist` (
  `ban_id` int(10) NOT NULL auto_increment,
  `ban_userid` int(10) default NULL,
  `ban_ip` varchar(16) default NULL,
  `ban_start` int(32) default NULL,
  `ban_end` int(50) default NULL,
  `ban_time_type` int(10) default NULL,
  PRIMARY KEY  (`ban_id`),
  KEY `ban_id` (`ban_id`)
) TYPE=MyISAM;

#
# Contenu de la table `banlist`
#

# --------------------------------------------------------

#
# Structure de la table `catagories`
#

CREATE TABLE `catagories` (
  `cat_id` int(10) NOT NULL auto_increment,
  `cat_title` varchar(100) default NULL,
  `cat_order` varchar(10) default NULL,
  PRIMARY KEY  (`cat_id`)
) TYPE=MyISAM;

#
# Contenu de la table `catagories`
#

INSERT INTO `catagories` (`cat_id`, `cat_title`, `cat_order`) VALUES (1, 'Forums des Groupes', NULL);
INSERT INTO `catagories` (`cat_id`, `cat_title`, `cat_order`) VALUES (2, 'Général', NULL);
# --------------------------------------------------------

#
# Structure de la table `config`
#

CREATE TABLE `config` (
  `config_id` int(10) NOT NULL auto_increment,
  `sitename` varchar(100) default NULL,
  `allow_html` int(2) default NULL,
  `allow_bbcode` int(2) default NULL,
  `allow_sig` int(2) default NULL,
  `allow_namechange` int(2) default '0',
  `admin_passwd` varchar(32) default NULL,
  `selected` int(2) NOT NULL default '0',
  `posts_per_page` int(10) default NULL,
  `hot_threshold` int(10) default NULL,
  `topics_per_page` int(10) default NULL,
  `allow_theme_create` int(10) default NULL,
  `override_themes` int(2) default '0',
  `email_sig` varchar(255) default NULL,
  `email_from` varchar(100) default NULL,
  `default_lang` varchar(255) default NULL,
  PRIMARY KEY  (`config_id`),
  UNIQUE KEY `selected` (`selected`)
) TYPE=MyISAM;

#
# Contenu de la table `config`
#

INSERT INTO `config` (`config_id`, `sitename`, `allow_html`, `allow_bbcode`, `allow_sig`, `allow_namechange`, `admin_passwd`, `selected`, `posts_per_page`, `hot_threshold`, `topics_per_page`, `allow_theme_create`, `override_themes`, `email_sig`, `email_from`, `default_lang`) VALUES (1, '', 1, 1, 1, 0, NULL, 1, 15, 15, 50, NULL, 0, 'Cordialement, votre professeur', '', 'french');
# --------------------------------------------------------

#
# Structure de la table `course_description`
#

CREATE TABLE `course_description` (
  `id` tinyint(3) unsigned NOT NULL default '0',
  `title` varchar(255) default NULL,
  `content` text,
  `upDate` datetime NOT NULL default '0000-00-00 00:00:00',
  UNIQUE KEY `id` (`id`)
) TYPE=MyISAM COMMENT='for course description tool';

#
# Contenu de la table `course_description`
#

# --------------------------------------------------------

#
# Structure de la table `disallow`
#

CREATE TABLE `disallow` (
  `disallow_id` int(10) NOT NULL auto_increment,
  `disallow_username` varchar(50) default NULL,
  PRIMARY KEY  (`disallow_id`)
) TYPE=MyISAM;

#
# Contenu de la table `disallow`
#

# --------------------------------------------------------

#
# Structure de la table `document`
#

CREATE TABLE `document` (
  `id` int(4) NOT NULL auto_increment,
  `path` varchar(255) NOT NULL default '',
  `visibility` char(1) NOT NULL default 'v',
  `comment` varchar(255) default NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

#
# Contenu de la table `document`
#

# --------------------------------------------------------

#
# Structure de la table `exercice_question`
#

CREATE TABLE `exercice_question` (
  `question_id` mediumint(8) unsigned NOT NULL default '0',
  `exercice_id` mediumint(8) unsigned NOT NULL default '0',
  PRIMARY KEY  (`question_id`,`exercice_id`)
) TYPE=MyISAM;

#
# Contenu de la table `exercice_question`
#

INSERT INTO `exercice_question` (`question_id`, `exercice_id`) VALUES (1, 1);
# --------------------------------------------------------

#
# Structure de la table `exercices`
#

CREATE TABLE `exercices` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `titre` varchar(200) NOT NULL default '',
  `description` text NOT NULL,
  `type` tinyint(4) unsigned NOT NULL default '1',
  `random` smallint(6) NOT NULL default '0',
  `active` tinyint(4) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

#
# Contenu de la table `exercices`
#

INSERT INTO `exercices` (`id`, `titre`, `description`, `type`, `random`, `active`) VALUES (1, 'Exemple d\'exercice', 'Histoire de la philosophie antique', 1, 0, 0);
# --------------------------------------------------------

#
# Structure de la table `forum_access`
#

CREATE TABLE `forum_access` (
  `forum_id` int(10) NOT NULL default '0',
  `user_id` int(10) NOT NULL default '0',
  `can_post` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`forum_id`,`user_id`)
) TYPE=MyISAM;

#
# Contenu de la table `forum_access`
#

# --------------------------------------------------------

#
# Structure de la table `forum_mods`
#

CREATE TABLE `forum_mods` (
  `forum_id` int(10) NOT NULL default '0',
  `user_id` int(10) NOT NULL default '0'
) TYPE=MyISAM;

#
# Contenu de la table `forum_mods`
#

INSERT INTO `forum_mods` (`forum_id`, `user_id`) VALUES (1, 1);
# --------------------------------------------------------

#
# Structure de la table `forums`
#

CREATE TABLE `forums` (
  `forum_id` int(10) NOT NULL auto_increment,
  `forum_name` varchar(150) default NULL,
  `forum_desc` text,
  `forum_access` int(10) default '1',
  `forum_moderator` int(10) default NULL,
  `forum_topics` int(10) NOT NULL default '0',
  `forum_posts` int(10) NOT NULL default '0',
  `forum_last_post_id` int(10) NOT NULL default '0',
  `cat_id` int(10) default NULL,
  `forum_type` int(10) default '0',
  `md5` varchar(32) NOT NULL default '',
  PRIMARY KEY  (`forum_id`),
  KEY `forum_last_post_id` (`forum_last_post_id`)
) TYPE=MyISAM;

#
# Contenu de la table `forums`
#

INSERT INTO `forums` (`forum_id`, `forum_name`, `forum_desc`, `forum_access`, `forum_moderator`, `forum_topics`, `forum_posts`, `forum_last_post_id`, `cat_id`, `forum_type`, `md5`) VALUES (1, 'Forum d\'essais', 'A supprimer via l\'administration des forums', 2, 1, 1, 1, 1, 2, 0, 'c4ca4238a0b923820dcc509a6f75849b');
# --------------------------------------------------------

#
# Structure de la table `group_properties`
#

CREATE TABLE `group_properties` (
  `id` tinyint(4) NOT NULL auto_increment,
  `self_registration` tinyint(4) default '1',
  `nbCoursPerUser` tinyint(3) unsigned default '1',
  `private` tinyint(4) default '0',
  `forum` tinyint(4) default '1',
  `document` tinyint(4) default '1',
  `wiki` tinyint(4) default '0',
  `agenda` tinyint(4) default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

#
# Contenu de la table `group_properties`
#

INSERT INTO `group_properties` (`id`, `self_registration`, `nbCoursPerUser`, `private`, `forum`, `document`, `wiki`, `agenda`) VALUES (1, 1, 1, 0, 1, 1, 0, 0);
# --------------------------------------------------------

#
# Structure de la table `headermetafooter`
#

CREATE TABLE `headermetafooter` (
  `header` text,
  `meta` text,
  `footer` text
) TYPE=MyISAM;

#
# Contenu de la table `headermetafooter`
#

INSERT INTO `headermetafooter` (`header`, `meta`, `footer`) VALUES ('<center><a href="../TEST"><img border=0 src=../claroline/img/logo.png></a></center>', '', '');
# --------------------------------------------------------

#
# Structure de la table `introduction`
#

CREATE TABLE `introduction` (
  `id` int(11) NOT NULL default '1',
  `texte_intro` text,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

#
# Contenu de la table `introduction`
#

INSERT INTO `introduction` (`id`, `texte_intro`) VALUES (1, 'Ceci est le texte d\'introduction de votre cours. Modifier ce texte régulièrement est une bonne façon d\'indiquer clairement que ce site est un lieu d\'interaction vivant et non un simple répertoire de documents.');
INSERT INTO `introduction` (`id`, `texte_intro`) VALUES (2, 'Cette page est un espace de publication. Elle permet à chaque étudiant ou groupe d\'étudiants d\'envoyer un document (Word, Excel, HTML... ) vers le site du cours afin de le rendre accessible aux autres étudiants ainsi qu\'au professeur.\r\nSi vous passez par votre espace de groupe pour publier le document (option publier), l\'outil de travaux fera un simple lien vers le document là où il se trouve dans votre répertoire de groupe sans le déplacer.');
# --------------------------------------------------------

#
# Structure de la table `liens`
#

CREATE TABLE `liens` (
  `id` int(11) NOT NULL auto_increment,
  `url` varchar(150) default NULL,
  `titre` varchar(150) default NULL,
  `description` text,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

#
# Contenu de la table `liens`
#

INSERT INTO `liens` (`id`, `url`, `titre`, `description`) VALUES (1, 'http://www.google.com', 'Google', 'Moteur de recherche généraliste performant');
# --------------------------------------------------------

#
# Structure de la table `pages`
#

CREATE TABLE `pages` (
  `id` int(11) NOT NULL auto_increment,
  `url` varchar(200) default NULL,
  `titre` varchar(200) default NULL,
  `description` text,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

#
# Contenu de la table `pages`
#

# --------------------------------------------------------

#
# Structure de la table `posts`
#

CREATE TABLE `posts` (
  `post_id` int(10) NOT NULL auto_increment,
  `topic_id` int(10) NOT NULL default '0',
  `forum_id` int(10) NOT NULL default '0',
  `poster_id` int(10) NOT NULL default '0',
  `post_time` varchar(20) default NULL,
  `poster_ip` varchar(16) default NULL,
  `nom` varchar(30) default NULL,
  `prenom` varchar(30) default NULL,
  PRIMARY KEY  (`post_id`),
  KEY `post_id` (`post_id`),
  KEY `forum_id` (`forum_id`),
  KEY `topic_id` (`topic_id`),
  KEY `poster_id` (`poster_id`)
) TYPE=MyISAM;

#
# Contenu de la table `posts`
#

INSERT INTO `posts` (`post_id`, `topic_id`, `forum_id`, `poster_id`, `post_time`, `poster_ip`, `nom`, `prenom`) VALUES (1, 1, 1, 1, now(), '', 'Doe', 'John');
# --------------------------------------------------------

#
# Structure de la table `posts_text`
#

CREATE TABLE `posts_text` (
  `post_id` int(10) NOT NULL default '0',
  `post_text` text,
  PRIMARY KEY  (`post_id`)
) TYPE=MyISAM;

#
# Contenu de la table `posts_text`
#

INSERT INTO `posts_text` (`post_id`, `post_text`) VALUES (1, 'Lorsque vous supprimerez le forum "Forum d\'essai", cela supprimera également le présent sujet qui ne contient que ce seul message');
# --------------------------------------------------------

#
# Structure de la table `priv_msgs`
#

CREATE TABLE `priv_msgs` (
  `msg_id` int(10) NOT NULL auto_increment,
  `from_userid` int(10) NOT NULL default '0',
  `to_userid` int(10) NOT NULL default '0',
  `msg_time` varchar(20) default NULL,
  `poster_ip` varchar(16) default NULL,
  `msg_status` int(10) default '0',
  `msg_text` text,
  PRIMARY KEY  (`msg_id`),
  KEY `msg_id` (`msg_id`),
  KEY `to_userid` (`to_userid`)
) TYPE=MyISAM;

#
# Contenu de la table `priv_msgs`
#

# --------------------------------------------------------

#
# Structure de la table `questions`
#

CREATE TABLE `questions` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `question` varchar(200) NOT NULL default '',
  `description` text NOT NULL,
  `ponderation` smallint(5) unsigned default NULL,
  `q_position` mediumint(8) unsigned NOT NULL default '1',
  `type` tinyint(3) unsigned NOT NULL default '2',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

#
# Contenu de la table `questions`
#

INSERT INTO `questions` (`id`, `question`, `description`, `ponderation`, `q_position`, `type`) VALUES (1, 'L\'ironie socratique consiste à...', '(plusieurs bonnes réponses possibles)', 10, 1, 2);
# --------------------------------------------------------

#
# Structure de la table `ranks`
#

CREATE TABLE `ranks` (
  `rank_id` int(10) NOT NULL auto_increment,
  `rank_title` varchar(50) NOT NULL default '',
  `rank_min` int(10) NOT NULL default '0',
  `rank_max` int(10) NOT NULL default '0',
  `rank_special` int(2) default '0',
  `rank_image` varchar(255) default NULL,
  PRIMARY KEY  (`rank_id`),
  KEY `rank_min` (`rank_min`),
  KEY `rank_max` (`rank_max`)
) TYPE=MyISAM;

#
# Contenu de la table `ranks`
#

# --------------------------------------------------------

#
# Structure de la table `reponses`
#

CREATE TABLE `reponses` (
  `id` mediumint(8) unsigned NOT NULL default '0',
  `question_id` mediumint(8) unsigned NOT NULL default '0',
  `reponse` text NOT NULL,
  `correct` mediumint(8) unsigned default NULL,
  `comment` text,
  `ponderation` smallint(5) default NULL,
  `r_position` mediumint(8) unsigned NOT NULL default '1',
  PRIMARY KEY  (`id`,`question_id`)
) TYPE=MyISAM;

#
# Contenu de la table `reponses`
#

INSERT INTO `reponses` (`id`, `question_id`, `reponse`, `correct`, `comment`, `ponderation`, `r_position`) VALUES (1, 1, 'Ridiculiser son interlocuteur pour lui faire admettre son erreur.', 0, 'Non. L\'ironie socratique ne se joue pas sur le terrain de la psychologie, mais sur celui de l\'argumentation.', -5, 1);
INSERT INTO `reponses` (`id`, `question_id`, `reponse`, `correct`, `comment`, `ponderation`, `r_position`) VALUES (2, 1, 'Reconnaître ses erreurs pour inviter son interlocuteur à faire de même.', 0, 'Non. Il ne s\'agit pas d\'une stratégie de séduction ou d\'une méthode par l\'exemple.', -5, 2);
INSERT INTO `reponses` (`id`, `question_id`, `reponse`, `correct`, `comment`, `ponderation`, `r_position`) VALUES (3, 1, 'Contraindre son interlocuteur, par une série de questions et de sous-questions, à reconnaître qu\'il ne connaît pas ce qu\'il prétend connaître.', 1, 'En effet. L\'ironie socratique est une méthode interrogative. Le grec "eirotao" signifie d\'ailleurs "interroger".', 5, 3);
INSERT INTO `reponses` (`id`, `question_id`, `reponse`, `correct`, `comment`, `ponderation`, `r_position`) VALUES (4, 1, 'Utiliser le principe de non-contradiction pour amener son interlocuteur dans l\'impasse.', 1, 'Cette réponse n\'est pas fausse. Il est exact que la mise en évidence de l\'ignorance de l\'interlocuteur se fait en mettant en évidence les contradictions auxquelles abouttisent ses thèses.', 5, 4);
# --------------------------------------------------------

#
# Structure de la table `sessions`
#

CREATE TABLE `sessions` (
  `sess_id` int(10) unsigned NOT NULL default '0',
  `user_id` int(10) NOT NULL default '0',
  `start_time` int(10) unsigned NOT NULL default '0',
  `remote_ip` varchar(15) NOT NULL default '',
  PRIMARY KEY  (`sess_id`),
  KEY `sess_id` (`sess_id`),
  KEY `start_time` (`start_time`),
  KEY `remote_ip` (`remote_ip`)
) TYPE=MyISAM;

#
# Contenu de la table `sessions`
#

# --------------------------------------------------------

#
# Structure de la table `student_group`
#

CREATE TABLE `student_group` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(100) default NULL,
  `description` text,
  `tutor` int(11) default NULL,
  `forumId` int(11) default NULL,
  `maxStudent` int(11) NOT NULL default '0',
  `secretDirectory` varchar(30) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

#
# Contenu de la table `student_group`
#

# --------------------------------------------------------

#
# Structure de la table `themes`
#

CREATE TABLE `themes` (
  `theme_id` int(10) NOT NULL auto_increment,
  `theme_name` varchar(35) default NULL,
  `bgcolor` varchar(10) default NULL,
  `textcolor` varchar(10) default NULL,
  `color1` varchar(10) default NULL,
  `color2` varchar(10) default NULL,
  `table_bgcolor` varchar(10) default NULL,
  `header_image` varchar(50) default NULL,
  `newtopic_image` varchar(50) default NULL,
  `reply_image` varchar(50) default NULL,
  `linkcolor` varchar(15) default NULL,
  `vlinkcolor` varchar(15) default NULL,
  `theme_default` int(2) default '0',
  `fontface` varchar(100) default NULL,
  `fontsize1` varchar(5) default NULL,
  `fontsize2` varchar(5) default NULL,
  `fontsize3` varchar(5) default NULL,
  `fontsize4` varchar(5) default NULL,
  `tablewidth` varchar(10) default NULL,
  `replylocked_image` varchar(255) default NULL,
  PRIMARY KEY  (`theme_id`)
) TYPE=MyISAM;

#
# Contenu de la table `themes`
#

INSERT INTO `themes` (`theme_id`, `theme_name`, `bgcolor`, `textcolor`, `color1`, `color2`, `table_bgcolor`, `header_image`, `newtopic_image`, `reply_image`, `linkcolor`, `vlinkcolor`, `theme_default`, `fontface`, `fontsize1`, `fontsize2`, `fontsize3`, `fontsize4`, `tablewidth`, `replylocked_image`) VALUES (1, 'Default', '#000000', '#FFFFFF', '#6C706D', '#2E4460', '#001100', 'images/header-dark.jpg', 'images/new_topic-dark.jpg', 'images/reply-dark.jpg', '#0000FF', '#800080', 0, 'sans-serif', '1', '2', '-2', '+1', '95%', 'images/reply_locked-dark.jpg');
# --------------------------------------------------------

#
# Structure de la table `topics`
#

CREATE TABLE `topics` (
  `topic_id` int(10) NOT NULL auto_increment,
  `topic_title` varchar(100) default NULL,
  `topic_poster` int(10) default NULL,
  `topic_time` varchar(20) default NULL,
  `topic_views` int(10) NOT NULL default '0',
  `topic_replies` int(10) NOT NULL default '0',
  `topic_last_post_id` int(10) NOT NULL default '0',
  `forum_id` int(10) NOT NULL default '0',
  `topic_status` int(10) NOT NULL default '0',
  `topic_notify` int(2) default '0',
  `nom` varchar(30) default NULL,
  `prenom` varchar(30) default NULL,
  PRIMARY KEY  (`topic_id`),
  KEY `topic_id` (`topic_id`),
  KEY `forum_id` (`forum_id`),
  KEY `topic_last_post_id` (`topic_last_post_id`)
) TYPE=MyISAM;

#
# Contenu de la table `topics`
#

INSERT INTO `topics` (`topic_id`, `topic_title`, `topic_poster`, `topic_time`, `topic_views`, `topic_replies`, `topic_last_post_id`, `forum_id`, `topic_status`, `topic_notify`, `nom`, `prenom`) VALUES (1, 'Message exemple', -1, '2001-09-18 20:25', 1, 0, 1, 1, 0, 1, 'Doe', 'John');
# --------------------------------------------------------

#
# Structure de la table `user_group`
#

CREATE TABLE `user_group` (
  `id` int(11) NOT NULL auto_increment,
  `user` int(11) NOT NULL default '0',
  `team` int(11) NOT NULL default '0',
  `status` int(11) NOT NULL default '0',
  `role` varchar(50) NOT NULL default '',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

# Structure de la table `userinfo_content`
#

CREATE TABLE `userinfo_content` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `user_id` mediumint(8) unsigned NOT NULL default '0',
  `def_id` int(10) unsigned NOT NULL default '0',
  `ed_ip` varchar(39) default NULL,
  `ed_date` datetime default NULL,
  `content` text,
  PRIMARY KEY  (`id`),
  KEY `user_id` (`user_id`)
) TYPE=MyISAM COMMENT='content of users information - organisation based on\r\nuserin';

#
# Contenu de la table `userinfo_content`
#

# --------------------------------------------------------

#
# Structure de la table `userinfo_def`
#

CREATE TABLE `userinfo_def` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `title` varchar(80) NOT NULL default '',
  `comment` varchar(160) default NULL,
  `nbLine` int(10) unsigned NOT NULL default '5',
  `rank` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM COMMENT='categories definition for user information of a course';

#
# Contenu de la table `userinfo_def`
#

# --------------------------------------------------------

#
# Structure de la table `users`
#

CREATE TABLE `users` (
  `user_id` int(10) NOT NULL auto_increment,
  `username` varchar(40) NOT NULL default '',
  `user_regdate` varchar(20) NOT NULL default '',
  `user_password` varchar(32) NOT NULL default '',
  `user_email` varchar(50) default NULL,
  `user_icq` varchar(15) default NULL,
  `user_website` varchar(100) default NULL,
  `user_occ` varchar(100) default NULL,
  `user_from` varchar(100) default NULL,
  `user_intrest` varchar(150) default NULL,
  `user_sig` varchar(255) default NULL,
  `user_viewemail` tinyint(2) default NULL,
  `user_theme` int(10) default NULL,
  `user_aim` varchar(18) default NULL,
  `user_yim` varchar(25) default NULL,
  `user_msnm` varchar(25) default NULL,
  `user_posts` int(10) default '0',
  `user_attachsig` int(2) default '0',
  `user_desmile` int(2) default '0',
  `user_html` int(2) default '0',
  `user_bbcode` int(2) default '0',
  `user_rank` int(10) default '0',
  `user_level` int(10) default '1',
  `user_lang` varchar(255) default NULL,
  `user_actkey` varchar(32) default NULL,
  `user_newpasswd` varchar(32) default NULL,
  PRIMARY KEY  (`user_id`)
) TYPE=MyISAM;

#
# Contenu de la table `users`
#

INSERT INTO `users` (`user_id`, `username`, `user_regdate`, `user_password`, `user_email`, `user_icq`, `user_website`, `user_occ`, `user_from`, `user_intrest`, `user_sig`, `user_viewemail`, `user_theme`, `user_aim`, `user_yim`, `user_msnm`, `user_posts`, `user_attachsig`, `user_desmile`, `user_html`, `user_bbcode`, `user_rank`, `user_level`, `user_lang`, `user_actkey`, `user_newpasswd`) VALUES (1, 'Doe John', '2003-06-18 15:27:42', 'password', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 1, NULL, NULL, NULL);
INSERT INTO `users` (`user_id`, `username`, `user_regdate`, `user_password`, `user_email`, `user_icq`, `user_website`, `user_occ`, `user_from`, `user_intrest`, `user_sig`, `user_viewemail`, `user_theme`, `user_aim`, `user_yim`, `user_msnm`, `user_posts`, `user_attachsig`, `user_desmile`, `user_html`, `user_bbcode`, `user_rank`, `user_level`, `user_lang`, `user_actkey`, `user_newpasswd`) VALUES (-1, 'Anonyme', '2003-06-18 15:27:42', 'password', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 1, NULL, NULL, NULL);
# --------------------------------------------------------

# Structure de la table `whosonline`
CREATE TABLE `whosonline` (
  `id` int(3) NOT NULL auto_increment,
  `ip` varchar(255) default NULL,
  `name` varchar(255) default NULL,
  `count` varchar(255) default NULL,
  `date` varchar(255) default NULL,
  `username` varchar(40) default NULL,
  `forum` int(10) default NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

# Structure de la table `words`
CREATE TABLE `words` (
  `word_id` int(10) NOT NULL auto_increment,
  `word` varchar(100) default NULL,
  `replacement` varchar(100) default NULL,
  PRIMARY KEY  (`word_id`)
) TYPE=MyISAM;

# Structure de la table `work`
CREATE TABLE `work` (
  `id` int(11) NOT NULL auto_increment,
  `url` varchar(200) default NULL,
  `titre` varchar(200) default NULL,
  `description` varchar(250) default NULL,
  `auteurs` varchar(200) default NULL,
  `active` tinyint(1) default NULL,
  `accepted` tinyint(1) default NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;
