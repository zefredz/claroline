# phpMyAdmin SQL Dump
# version 2.5.3
# http://www.phpmyadmin.net
#
# Serveur: localhost
# Généré le : Mercredi 07 Juillet 2004 à 17:22
# Version du serveur: 4.0.15
# Version de PHP: 4.3.3
# 
# Base de données: `coursecoursecode`
# 

# --------------------------------------------------------

#
# Structure de la table `announcement`
#
# Création: Mercredi 07 Juillet 2004 à 17:20
# Dernière modification: Mercredi 07 Juillet 2004 à 17:20
#

CREATE TABLE `announcement` (
  `id` mediumint(11) NOT NULL auto_increment,
  `title` varchar(80) default NULL,
  `contenu` text,
  `temps` date default NULL,
  `ordre` mediumint(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM COMMENT='announcements table' AUTO_INCREMENT=1 ;

#
# Contenu de la table `announcement`
#


# --------------------------------------------------------

#
# Structure de la table `assignment_doc`
#
# Création: Mercredi 07 Juillet 2004 à 17:20
# Dernière modification: Mercredi 07 Juillet 2004 à 17:20
#

CREATE TABLE `assignment_doc` (
  `id` int(11) NOT NULL auto_increment,
  `url` varchar(200) default NULL,
  `titre` varchar(200) default NULL,
  `description` varchar(250) default NULL,
  `auteurs` varchar(200) default NULL,
  `active` tinyint(1) default NULL,
  `accepted` tinyint(1) default NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

#
# Contenu de la table `assignment_doc`
#


# --------------------------------------------------------

#
# Structure de la table `bb_access`
#
# Création: Mercredi 07 Juillet 2004 à 17:20
# Dernière modification: Mercredi 07 Juillet 2004 à 17:20
#

CREATE TABLE `bb_access` (
  `access_id` int(10) NOT NULL auto_increment,
  `access_title` varchar(20) default NULL,
  PRIMARY KEY  (`access_id`)
) TYPE=MyISAM AUTO_INCREMENT=2147483647 ;

#
# Contenu de la table `bb_access`
#

INSERT INTO `bb_access` (`access_id`, `access_title`) VALUES (-1, 'Deleted'),
(1, 'User'),
(2, 'Moderator'),
(3, 'Super Moderator'),
(4, 'Administrator');

# --------------------------------------------------------

#
# Structure de la table `bb_banlist`
#
# Création: Mercredi 07 Juillet 2004 à 17:20
# Dernière modification: Mercredi 07 Juillet 2004 à 17:20
#

CREATE TABLE `bb_banlist` (
  `ban_id` int(10) NOT NULL auto_increment,
  `ban_userid` int(10) default NULL,
  `ban_ip` varchar(16) default NULL,
  `ban_start` int(32) default NULL,
  `ban_end` int(50) default NULL,
  `ban_time_type` int(10) default NULL,
  PRIMARY KEY  (`ban_id`),
  KEY `ban_id` (`ban_id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

#
# Contenu de la table `bb_banlist`
#


# --------------------------------------------------------

#
# Structure de la table `bb_categories`
#
# Création: Mercredi 07 Juillet 2004 à 17:20
# Dernière modification: Mercredi 07 Juillet 2004 à 17:20
#

CREATE TABLE `bb_categories` (
  `cat_id` int(10) NOT NULL auto_increment,
  `cat_title` varchar(100) default NULL,
  `cat_order` varchar(10) default NULL,
  PRIMARY KEY  (`cat_id`)
) TYPE=MyISAM AUTO_INCREMENT=3 ;

#
# Contenu de la table `bb_categories`
#

INSERT INTO `bb_categories` (`cat_id`, `cat_title`, `cat_order`) VALUES (1, 'Groups forums', '1'),
(2, 'Main', '2');

# --------------------------------------------------------

#
# Structure de la table `bb_config`
#
# Création: Mercredi 07 Juillet 2004 à 17:20
# Dernière modification: Mercredi 07 Juillet 2004 à 17:20
#

CREATE TABLE `bb_config` (
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
) TYPE=MyISAM AUTO_INCREMENT=2 ;

#
# Contenu de la table `bb_config`
#

INSERT INTO `bb_config` (`config_id`, `sitename`, `allow_html`, `allow_bbcode`, `allow_sig`, `allow_namechange`, `admin_passwd`, `selected`, `posts_per_page`, `hot_threshold`, `topics_per_page`, `allow_theme_create`, `override_themes`, `email_sig`, `email_from`, `default_lang`) VALUES (1, '', 1, 1, 1, 0, NULL, 1, 15, 15, 50, NULL, 0, 'Yours sincerely, your professor', '', 'english');

# --------------------------------------------------------

#
# Structure de la table `bb_disallow`
#
# Création: Mercredi 07 Juillet 2004 à 17:20
# Dernière modification: Mercredi 07 Juillet 2004 à 17:20
#

CREATE TABLE `bb_disallow` (
  `disallow_id` int(10) NOT NULL auto_increment,
  `disallow_username` varchar(50) default NULL,
  PRIMARY KEY  (`disallow_id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

#
# Contenu de la table `bb_disallow`
#


# --------------------------------------------------------

#
# Structure de la table `bb_forum_access`
#
# Création: Mercredi 07 Juillet 2004 à 17:20
# Dernière modification: Mercredi 07 Juillet 2004 à 17:20
#

CREATE TABLE `bb_forum_access` (
  `forum_id` int(10) NOT NULL default '0',
  `user_id` int(10) NOT NULL default '0',
  `can_post` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`forum_id`,`user_id`)
) TYPE=MyISAM;

#
# Contenu de la table `bb_forum_access`
#


# --------------------------------------------------------

#
# Structure de la table `bb_forum_mods`
#
# Création: Mercredi 07 Juillet 2004 à 17:20
# Dernière modification: Mercredi 07 Juillet 2004 à 17:20
#

CREATE TABLE `bb_forum_mods` (
  `forum_id` int(10) NOT NULL default '0',
  `user_id` int(10) NOT NULL default '0'
) TYPE=MyISAM;

#
# Contenu de la table `bb_forum_mods`
#

INSERT INTO `bb_forum_mods` (`forum_id`, `user_id`) VALUES (1, 1);

# --------------------------------------------------------

#
# Structure de la table `bb_forums`
#
# Création: Mercredi 07 Juillet 2004 à 17:20
# Dernière modification: Mercredi 07 Juillet 2004 à 17:20
#

CREATE TABLE `bb_forums` (
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
  `forum_order` int(10) default '0',
  PRIMARY KEY  (`forum_id`),
  KEY `forum_last_post_id` (`forum_last_post_id`)
) TYPE=MyISAM AUTO_INCREMENT=2 ;

#
# Contenu de la table `bb_forums`
#

INSERT INTO `bb_forums` (`forum_id`, `forum_name`, `forum_desc`, `forum_access`, `forum_moderator`, `forum_topics`, `forum_posts`, `forum_last_post_id`, `cat_id`, `forum_type`, `md5`, `forum_order`) VALUES (1, 'Test forum', 'Remove this through the forum admin tool', 2, 1, 1, 1, 1, 2, 0, 'c4ca4238a0b923820dcc509a6f75849b', 1);

# --------------------------------------------------------

#
# Structure de la table `bb_headermetafooter`
#
# Création: Mercredi 07 Juillet 2004 à 17:20
# Dernière modification: Mercredi 07 Juillet 2004 à 17:20
#

CREATE TABLE `bb_headermetafooter` (
  `header` text,
  `meta` text,
  `footer` text
) TYPE=MyISAM;

#
# Contenu de la table `bb_headermetafooter`
#

INSERT INTO `bb_headermetafooter` (`header`, `meta`, `footer`) VALUES ('<center><a href="../COURSECODE"><img border=0 src=../claroline/img/logo.gif></a></center>', '', '');

# --------------------------------------------------------

#
# Structure de la table `bb_posts`
#
# Création: Mercredi 07 Juillet 2004 à 17:20
# Dernière modification: Mercredi 07 Juillet 2004 à 17:20
#

CREATE TABLE `bb_posts` (
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
) TYPE=MyISAM AUTO_INCREMENT=2 ;

#
# Contenu de la table `bb_posts`
#

INSERT INTO `bb_posts` (`post_id`, `topic_id`, `forum_id`, `poster_id`, `post_time`, `poster_ip`, `nom`, `prenom`) VALUES (1, 1, 1, 1, '2004-07-07 17:20:58', '127.0.0.1', 'Doe', 'John');

# --------------------------------------------------------

#
# Structure de la table `bb_posts_text`
#
# Création: Mercredi 07 Juillet 2004 à 17:20
# Dernière modification: Mercredi 07 Juillet 2004 à 17:20
#

CREATE TABLE `bb_posts_text` (
  `post_id` int(10) NOT NULL default '0',
  `post_text` text,
  PRIMARY KEY  (`post_id`)
) TYPE=MyISAM;

#
# Contenu de la table `bb_posts_text`
#

INSERT INTO `bb_posts_text` (`post_id`, `post_text`) VALUES (1, 'When you remove the test forum, it will remove all messages in that forum too.');

# --------------------------------------------------------

#
# Structure de la table `bb_priv_msgs`
#
# Création: Mercredi 07 Juillet 2004 à 17:20
# Dernière modification: Mercredi 07 Juillet 2004 à 17:20
#

CREATE TABLE `bb_priv_msgs` (
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
) TYPE=MyISAM AUTO_INCREMENT=1 ;

#
# Contenu de la table `bb_priv_msgs`
#


# --------------------------------------------------------

#
# Structure de la table `bb_ranks`
#
# Création: Mercredi 07 Juillet 2004 à 17:20
# Dernière modification: Mercredi 07 Juillet 2004 à 17:20
#

CREATE TABLE `bb_ranks` (
  `rank_id` int(10) NOT NULL auto_increment,
  `rank_title` varchar(50) NOT NULL default '',
  `rank_min` int(10) NOT NULL default '0',
  `rank_max` int(10) NOT NULL default '0',
  `rank_special` int(2) default '0',
  `rank_image` varchar(255) default NULL,
  PRIMARY KEY  (`rank_id`),
  KEY `rank_min` (`rank_min`),
  KEY `rank_max` (`rank_max`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

#
# Contenu de la table `bb_ranks`
#


# --------------------------------------------------------

#
# Structure de la table `bb_rel_topic_userstonotify`
#
# Création: Mercredi 07 Juillet 2004 à 17:20
# Dernière modification: Mercredi 07 Juillet 2004 à 17:20
#

CREATE TABLE `bb_rel_topic_userstonotify` (
  `notify_id` int(10) NOT NULL auto_increment,
  `user_id` int(10) NOT NULL default '0',
  `topic_id` int(10) NOT NULL default '0',
  PRIMARY KEY  (`notify_id`),
  KEY `SECONDARY` (`user_id`,`topic_id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

#
# Contenu de la table `bb_rel_topic_userstonotify`
#


# --------------------------------------------------------

#
# Structure de la table `bb_sessions`
#
# Création: Mercredi 07 Juillet 2004 à 17:20
# Dernière modification: Mercredi 07 Juillet 2004 à 17:20
#

CREATE TABLE `bb_sessions` (
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
# Contenu de la table `bb_sessions`
#


# --------------------------------------------------------

#
# Structure de la table `bb_themes`
#
# Création: Mercredi 07 Juillet 2004 à 17:20
# Dernière modification: Mercredi 07 Juillet 2004 à 17:20
#

CREATE TABLE `bb_themes` (
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
) TYPE=MyISAM AUTO_INCREMENT=4 ;

#
# Contenu de la table `bb_themes`
#

INSERT INTO `bb_themes` (`theme_id`, `theme_name`, `bgcolor`, `textcolor`, `color1`, `color2`, `table_bgcolor`, `header_image`, `newtopic_image`, `reply_image`, `linkcolor`, `vlinkcolor`, `theme_default`, `fontface`, `fontsize1`, `fontsize2`, `fontsize3`, `fontsize4`, `tablewidth`, `replylocked_image`) VALUES (1, 'Default', '#000000', '#FFFFFF', '#6C706D', '#2E4460', '#001100', 'images/header-dark.jpg', 'images/new_topic-dark.jpg', 'images/reply-dark.jpg', '#0000FF', '#800080', 0, 'sans-serif', '1', '2', '-2', '+1', '95%', 'images/reply_locked-dark.jpg'),
(2, 'Ocean', '#FFFFFF', '#000000', '#CCCCCC', '#9BB6DA', '#000000', 'images/header.jpg', 'images/new_topic.jpg', 'images/reply.jpg', '#0000FF', '#800080', 0, 'sans-serif', '1', '2', '-2', '+1', '95%', 'images/reply_locked-dark.jpg'),
(3, 'OCPrices.com', '#FFFFFF', '#000000', '#F5F5F5', '#E6E6E6', '#FFFFFF', 'images/forum.jpg', 'images/nouveausujet.jpg', 'images/repondre.jpg', '#0000FF', '#800080', 1, 'Arial,Helvetica, Sans-serif', '1', '2', '-2', '+1', '600', 'images/reply_locked-dark.jpg');

# --------------------------------------------------------

#
# Structure de la table `bb_topics`
#
# Création: Mercredi 07 Juillet 2004 à 17:20
# Dernière modification: Mercredi 07 Juillet 2004 à 17:21
#

CREATE TABLE `bb_topics` (
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
) TYPE=MyISAM AUTO_INCREMENT=2 ;

#
# Contenu de la table `bb_topics`
#

INSERT INTO `bb_topics` (`topic_id`, `topic_title`, `topic_poster`, `topic_time`, `topic_views`, `topic_replies`, `topic_last_post_id`, `forum_id`, `topic_status`, `topic_notify`, `nom`, `prenom`) VALUES (1, 'Example message', -1, '2001-09-18 20:25', 1, 0, 1, 1, 0, 1, 'Doe', 'John');

# --------------------------------------------------------

#
# Structure de la table `bb_users`
#
# Création: Mercredi 07 Juillet 2004 à 17:20
# Dernière modification: Mercredi 07 Juillet 2004 à 17:20
#

CREATE TABLE `bb_users` (
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
) TYPE=MyISAM AUTO_INCREMENT=2147483647 ;

#
# Contenu de la table `bb_users`
#

INSERT INTO `bb_users` (`user_id`, `username`, `user_regdate`, `user_password`, `user_email`, `user_icq`, `user_website`, `user_occ`, `user_from`, `user_intrest`, `user_sig`, `user_viewemail`, `user_theme`, `user_aim`, `user_yim`, `user_msnm`, `user_posts`, `user_attachsig`, `user_desmile`, `user_html`, `user_bbcode`, `user_rank`, `user_level`, `user_lang`, `user_actkey`, `user_newpasswd`) VALUES (1, 'Doe John', '2004-07-07 17:20:58', 'password', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 1, NULL, NULL, NULL),
(-1, 'Anonymous', '2004-07-07 17:20:58', 'password', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 1, NULL, NULL, NULL);

# --------------------------------------------------------

#
# Structure de la table `bb_whosonline`
#
# Création: Mercredi 07 Juillet 2004 à 17:20
# Dernière modification: Mercredi 07 Juillet 2004 à 17:20
#

CREATE TABLE `bb_whosonline` (
  `id` int(3) NOT NULL auto_increment,
  `ip` varchar(255) default NULL,
  `name` varchar(255) default NULL,
  `count` varchar(255) default NULL,
  `date` varchar(255) default NULL,
  `username` varchar(40) default NULL,
  `forum` int(10) default NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

#
# Contenu de la table `bb_whosonline`
#


# --------------------------------------------------------

#
# Structure de la table `bb_words`
#
# Création: Mercredi 07 Juillet 2004 à 17:20
# Dernière modification: Mercredi 07 Juillet 2004 à 17:20
#

CREATE TABLE `bb_words` (
  `word_id` int(10) NOT NULL auto_increment,
  `word` varchar(100) default NULL,
  `replacement` varchar(100) default NULL,
  PRIMARY KEY  (`word_id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

#
# Contenu de la table `bb_words`
#


# --------------------------------------------------------

#
# Structure de la table `calendar_event`
#
# Création: Mercredi 07 Juillet 2004 à 17:20
# Dernière modification: Mercredi 07 Juillet 2004 à 17:20
#

CREATE TABLE `calendar_event` (
  `id` int(11) NOT NULL auto_increment,
  `titre` varchar(200) default NULL,
  `contenu` text,
  `day` date NOT NULL default '0000-00-00',
  `hour` time NOT NULL default '00:00:00',
  `lasting` varchar(20) default NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

#
# Contenu de la table `calendar_event`
#


# --------------------------------------------------------

#
# Structure de la table `course_description`
#
# Création: Mercredi 07 Juillet 2004 à 17:20
# Dernière modification: Mercredi 07 Juillet 2004 à 17:20
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
# Structure de la table `document`
#
# Création: Mercredi 07 Juillet 2004 à 17:20
# Dernière modification: Mercredi 07 Juillet 2004 à 17:20
#

CREATE TABLE `document` (
  `id` int(4) NOT NULL auto_increment,
  `path` varchar(255) NOT NULL default '',
  `visibility` char(1) NOT NULL default 'v',
  `comment` varchar(255) default NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

#
# Contenu de la table `document`
#


# --------------------------------------------------------

#
# Structure de la table `group_property`
#
# Création: Mercredi 07 Juillet 2004 à 17:20
# Dernière modification: Mercredi 07 Juillet 2004 à 17:20
#

CREATE TABLE `group_property` (
  `id` tinyint(4) NOT NULL auto_increment,
  `self_registration` tinyint(4) default '1',
  `nbGroupPerUser` tinyint(3) unsigned default '1',
  `private` tinyint(4) default '0',
  `forum` tinyint(4) default '1',
  `document` tinyint(4) default '1',
  `wiki` tinyint(4) default '0',
  `chat` tinyint(4) default '1',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=2 ;

#
# Contenu de la table `group_property`
#

INSERT INTO `group_property` (`id`, `self_registration`, `nbGroupPerUser`, `private`, `forum`, `document`, `wiki`, `chat`) VALUES (1, 1, 1, 0, 1, 1, 0, 1);

# --------------------------------------------------------

#
# Structure de la table `group_rel_team_user`
#
# Création: Mercredi 07 Juillet 2004 à 17:20
# Dernière modification: Mercredi 07 Juillet 2004 à 17:20
#

CREATE TABLE `group_rel_team_user` (
  `id` int(11) NOT NULL auto_increment,
  `user` int(11) NOT NULL default '0',
  `team` int(11) NOT NULL default '0',
  `status` int(11) NOT NULL default '0',
  `role` varchar(50) NOT NULL default '',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

#
# Contenu de la table `group_rel_team_user`
#


# --------------------------------------------------------

#
# Structure de la table `group_team`
#
# Création: Mercredi 07 Juillet 2004 à 17:20
# Dernière modification: Mercredi 07 Juillet 2004 à 17:20
#

CREATE TABLE `group_team` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(100) default NULL,
  `description` text,
  `tutor` int(11) default NULL,
  `forumId` int(11) default NULL,
  `maxStudent` int(11) NOT NULL default '0',
  `secretDirectory` varchar(30) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

#
# Contenu de la table `group_team`
#


# --------------------------------------------------------

#
# Structure de la table `lp_asset`
#
# Création: Mercredi 07 Juillet 2004 à 17:20
# Dernière modification: Mercredi 07 Juillet 2004 à 17:21
#

CREATE TABLE `lp_asset` (
  `asset_id` int(11) NOT NULL auto_increment,
  `module_id` int(11) NOT NULL default '0',
  `path` varchar(255) NOT NULL default '',
  `comment` varchar(255) default NULL,
  PRIMARY KEY  (`asset_id`)
) TYPE=MyISAM COMMENT='List of resources of module of learning paths' AUTO_INCREMENT=3 ;

#
# Contenu de la table `lp_asset`
#

INSERT INTO `lp_asset` (`asset_id`, `module_id`, `path`, `comment`) VALUES (1, 1, '/Example_document.pdf', ''),
(2, 2, '1', '');

# --------------------------------------------------------

#
# Structure de la table `lp_learnpath`
#
# Création: Mercredi 07 Juillet 2004 à 17:20
# Dernière modification: Mercredi 07 Juillet 2004 à 17:20
#

CREATE TABLE `lp_learnpath` (
  `learnPath_id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `comment` text NOT NULL,
  `lock` enum('OPEN','CLOSE') NOT NULL default 'OPEN',
  `visibility` enum('HIDE','SHOW') NOT NULL default 'SHOW',
  `rank` int(11) NOT NULL default '0',
  PRIMARY KEY  (`learnPath_id`),
  UNIQUE KEY `rank` (`rank`)
) TYPE=MyISAM COMMENT='List of learning Paths' AUTO_INCREMENT=2 ;

#
# Contenu de la table `lp_learnpath`
#

INSERT INTO `lp_learnpath` (`learnPath_id`, `name`, `comment`, `lock`, `visibility`, `rank`) VALUES (1, 'Sample learning path', 'This is a sample learning path, it uses the sample exercise and the sample document of the exercise tool and the document tool. Click on\r\n                            <b>Modify</b> to change this text.', 'OPEN', 'SHOW', 1);

# --------------------------------------------------------

#
# Structure de la table `lp_module`
#
# Création: Mercredi 07 Juillet 2004 à 17:20
# Dernière modification: Mercredi 07 Juillet 2004 à 17:20
#

CREATE TABLE `lp_module` (
  `module_id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `comment` text NOT NULL,
  `accessibility` enum('PRIVATE','PUBLIC') NOT NULL default 'PRIVATE',
  `startAsset_id` int(11) NOT NULL default '0',
  `contentType` enum('CLARODOC','DOCUMENT','EXERCISE','HANDMADE','SCORM','LABEL') NOT NULL default 'CLARODOC',
  `launch_data` text NOT NULL,
  PRIMARY KEY  (`module_id`)
) TYPE=MyISAM COMMENT='List of available modules used in learning paths' AUTO_INCREMENT=3 ;

#
# Contenu de la table `lp_module`
#

INSERT INTO `lp_module` (`module_id`, `name`, `comment`, `accessibility`, `startAsset_id`, `contentType`, `launch_data`) VALUES (1, 'example_document', 'You can use any document existing in the documents tool of this course.', 'PRIVATE', 1, 'DOCUMENT', ''),
(2, 'Sample exercise', 'You can use any exercise of the exercises tool of your course.', 'PRIVATE', 2, 'EXERCISE', '');

# --------------------------------------------------------

#
# Structure de la table `lp_rel_learnpath_module`
#
# Création: Mercredi 07 Juillet 2004 à 17:20
# Dernière modification: Mercredi 07 Juillet 2004 à 17:20
#

CREATE TABLE `lp_rel_learnpath_module` (
  `learnPath_module_id` int(11) NOT NULL auto_increment,
  `learnPath_id` int(11) NOT NULL default '0',
  `module_id` int(11) NOT NULL default '0',
  `lock` enum('OPEN','CLOSE') NOT NULL default 'OPEN',
  `visibility` enum('HIDE','SHOW') NOT NULL default 'SHOW',
  `specificComment` text NOT NULL,
  `rank` int(11) NOT NULL default '0',
  `parent` int(11) NOT NULL default '0',
  `raw_to_pass` tinyint(4) NOT NULL default '50',
  PRIMARY KEY  (`learnPath_module_id`)
) TYPE=MyISAM COMMENT='This table links module to the learning path using them' AUTO_INCREMENT=3 ;

#
# Contenu de la table `lp_rel_learnpath_module`
#

INSERT INTO `lp_rel_learnpath_module` (`learnPath_module_id`, `learnPath_id`, `module_id`, `lock`, `visibility`, `specificComment`, `rank`, `parent`, `raw_to_pass`) VALUES (1, 1, 1, 'OPEN', 'SHOW', '', 1, 0, 50),
(2, 1, 2, 'OPEN', 'SHOW', '', 2, 0, 50);

# --------------------------------------------------------

#
# Structure de la table `lp_user_module_progress`
#
# Création: Mercredi 07 Juillet 2004 à 17:20
# Dernière modification: Mercredi 07 Juillet 2004 à 17:20
#

CREATE TABLE `lp_user_module_progress` (
  `user_module_progress_id` int(22) NOT NULL auto_increment,
  `user_id` mediumint(9) NOT NULL default '0',
  `learnPath_module_id` int(11) NOT NULL default '0',
  `learnPath_id` int(11) NOT NULL default '0',
  `lesson_location` varchar(255) NOT NULL default '',
  `lesson_status` enum('NOT ATTEMPTED','PASSED','FAILED','COMPLETED','BROWSED','INCOMPLETE','UNKNOWN') NOT NULL default 'NOT ATTEMPTED',
  `entry` enum('AB-INITIO','RESUME','') NOT NULL default 'AB-INITIO',
  `raw` tinyint(4) NOT NULL default '-1',
  `scoreMin` tinyint(4) NOT NULL default '-1',
  `scoreMax` tinyint(4) NOT NULL default '-1',
  `total_time` varchar(13) NOT NULL default '0000:00:00.00',
  `session_time` varchar(13) NOT NULL default '0000:00:00.00',
  `suspend_data` text NOT NULL,
  `credit` enum('CREDIT','NO-CREDIT') NOT NULL default 'NO-CREDIT',
  PRIMARY KEY  (`user_module_progress_id`)
) TYPE=MyISAM COMMENT='Record the last known status of the user in the course' AUTO_INCREMENT=1 ;

#
# Contenu de la table `lp_user_module_progress`
#


# --------------------------------------------------------

#
# Structure de la table `pages`
#
# Création: Mercredi 07 Juillet 2004 à 17:20
# Dernière modification: Mercredi 07 Juillet 2004 à 17:20
#

CREATE TABLE `pages` (
  `id` int(11) NOT NULL auto_increment,
  `url` varchar(200) default NULL,
  `titre` varchar(200) default NULL,
  `description` text,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

#
# Contenu de la table `pages`
#


# --------------------------------------------------------

#
# Structure de la table `quiz_answer`
#
# Création: Mercredi 07 Juillet 2004 à 17:20
# Dernière modification: Mercredi 07 Juillet 2004 à 17:20
#

CREATE TABLE `quiz_answer` (
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
# Contenu de la table `quiz_answer`
#

INSERT INTO `quiz_answer` (`id`, `question_id`, `reponse`, `correct`, `comment`, `ponderation`, `r_position`) VALUES (1, 1, 'Ridiculise one\'s interlocutor in order to have him concede he is wrong.', 0, 'No. Socratic irony is not a matter of psychology, it concerns argumentation.', -5, 1),
(2, 1, 'Admit one\'s own errors to invite one\'s interlocutor to do the same.', 0, 'No. Socratic irony is not a seduction strategy or a method based on the example.', -5, 2),
(3, 1, 'Compell one\'s interlocutor, by a series of questions and sub-questions, to admit he doesn\'t know what he claims to know.', 1, 'Indeed. Socratic irony is an interrogative method. The Greek "eirotao" means "ask questions"', 5, 3),
(4, 1, 'Use the Principle of Non Contradiction to force one\'s interlocutor into a dead end.', 1, 'This answer is not false. It is true that the revelation of the interlocutor\'s ignorance means showing the contradictory conclusions where lead his premisses.', 5, 4);

# --------------------------------------------------------

#
# Structure de la table `quiz_question`
#
# Création: Mercredi 07 Juillet 2004 à 17:20
# Dernière modification: Mercredi 07 Juillet 2004 à 17:20
#

CREATE TABLE `quiz_question` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `question` varchar(200) NOT NULL default '',
  `description` text NOT NULL,
  `ponderation` smallint(5) unsigned default NULL,
  `q_position` mediumint(8) unsigned NOT NULL default '1',
  `type` tinyint(3) unsigned NOT NULL default '2',
  `picture_name` varchar(50) default '',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=2 ;

#
# Contenu de la table `quiz_question`
#

INSERT INTO `quiz_question` (`id`, `question`, `description`, `ponderation`, `q_position`, `type`, `picture_name`) VALUES (1, 'Socratic irony is...', '(more than one answer can be true)', 10, 1, 2, '');

# --------------------------------------------------------

#
# Structure de la table `quiz_rel_test_question`
#
# Création: Mercredi 07 Juillet 2004 à 17:20
# Dernière modification: Mercredi 07 Juillet 2004 à 17:20
#

CREATE TABLE `quiz_rel_test_question` (
  `question_id` mediumint(8) unsigned NOT NULL default '0',
  `exercice_id` mediumint(8) unsigned NOT NULL default '0',
  PRIMARY KEY  (`question_id`,`exercice_id`)
) TYPE=MyISAM;

#
# Contenu de la table `quiz_rel_test_question`
#

INSERT INTO `quiz_rel_test_question` (`question_id`, `exercice_id`) VALUES (1, 1);

# --------------------------------------------------------

#
# Structure de la table `quiz_test`
#
# Création: Mercredi 07 Juillet 2004 à 17:20
# Dernière modification: Mercredi 07 Juillet 2004 à 17:20
#

CREATE TABLE `quiz_test` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `titre` varchar(200) NOT NULL default '',
  `description` text NOT NULL,
  `type` tinyint(4) unsigned NOT NULL default '1',
  `random` smallint(6) NOT NULL default '0',
  `active` tinyint(4) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=2 ;

#
# Contenu de la table `quiz_test`
#

INSERT INTO `quiz_test` (`id`, `titre`, `description`, `type`, `random`, `active`) VALUES (1, 'Sample exercise', 'History of Ancient Philosophy', 1, 0, 0);

# --------------------------------------------------------

#
# Structure de la table `tool_intro`
#
# Création: Mercredi 07 Juillet 2004 à 17:20
# Dernière modification: Mercredi 07 Juillet 2004 à 17:20
#

CREATE TABLE `tool_intro` (
  `id` int(11) NOT NULL default '1',
  `texte_intro` text,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

#
# Contenu de la table `tool_intro`
#


# --------------------------------------------------------

#
# Structure de la table `tool_list`
#
# Création: Mercredi 07 Juillet 2004 à 17:20
# Dernière modification: Mercredi 07 Juillet 2004 à 17:20
#

CREATE TABLE `tool_list` (
  `id` int(11) NOT NULL auto_increment,
  `tool_id` int(10) unsigned default NULL,
  `rank` int(10) unsigned NOT NULL default '0',
  `access` enum('ALL','PLATFORM_MEMBER','COURSE_MEMBER','COURSE_TUTOR','GROUP_MEMBER','GROUP_TUTOR','COURSE_ADMIN','PLATFORM_ADMIN') NOT NULL default 'ALL',
  `script_url` varchar(255) default NULL,
  `script_name` varchar(255) default NULL,
  `addedTool` enum('YES','NO') default 'YES',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=12 ;

#
# Contenu de la table `tool_list`
#

INSERT INTO `tool_list` (`id`, `tool_id`, `rank`, `access`, `script_url`, `script_name`, `addedTool`) VALUES (1, 1, 1, 'ALL', NULL, NULL, 'YES'),
(2, 2, 2, 'ALL', NULL, NULL, 'YES'),
(3, 3, 3, 'ALL', NULL, NULL, 'YES'),
(4, 4, 4, 'ALL', NULL, NULL, 'YES'),
(5, 5, 5, 'ALL', NULL, NULL, 'YES'),
(6, 6, 6, 'ALL', NULL, NULL, 'YES'),
(7, 7, 7, 'ALL', NULL, NULL, 'YES'),
(8, 8, 8, 'ALL', NULL, NULL, 'YES'),
(9, 9, 9, 'ALL', NULL, NULL, 'YES'),
(10, 10, 10, 'ALL', NULL, NULL, 'YES'),
(11, 11, 11, 'ALL', NULL, NULL, 'YES');

# --------------------------------------------------------

#
# Structure de la table `track_e_access`
#
# Création: Mercredi 07 Juillet 2004 à 17:20
# Dernière modification: Mercredi 07 Juillet 2004 à 17:20
#

CREATE TABLE `track_e_access` (
  `access_id` int(11) NOT NULL auto_increment,
  `access_user_id` int(10) default NULL,
  `access_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `access_tool` varchar(30) default NULL,
  PRIMARY KEY  (`access_id`)
) TYPE=MyISAM COMMENT='Record informations about access to course or tools' AUTO_INCREMENT=1 ;

#
# Contenu de la table `track_e_access`
#


# --------------------------------------------------------

#
# Structure de la table `track_e_downloads`
#
# Création: Mercredi 07 Juillet 2004 à 17:20
# Dernière modification: Mercredi 07 Juillet 2004 à 17:20
#

CREATE TABLE `track_e_downloads` (
  `down_id` int(11) NOT NULL auto_increment,
  `down_user_id` int(10) default NULL,
  `down_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `down_doc_path` varchar(255) NOT NULL default '0',
  PRIMARY KEY  (`down_id`)
) TYPE=MyISAM COMMENT='Record informations about downloads' AUTO_INCREMENT=1 ;

#
# Contenu de la table `track_e_downloads`
#


# --------------------------------------------------------

#
# Structure de la table `track_e_exercices`
#
# Création: Mercredi 07 Juillet 2004 à 17:20
# Dernière modification: Mercredi 07 Juillet 2004 à 17:20
#

CREATE TABLE `track_e_exercices` (
  `exe_id` int(11) NOT NULL auto_increment,
  `exe_user_id` int(10) default NULL,
  `exe_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `exe_exo_id` tinyint(4) NOT NULL default '0',
  `exe_result` mediumint(8) NOT NULL default '0',
  `exe_weighting` mediumint(8) NOT NULL default '0',
  PRIMARY KEY  (`exe_id`)
) TYPE=MyISAM COMMENT='Record informations about exercices' AUTO_INCREMENT=1 ;

#
# Contenu de la table `track_e_exercices`
#


# --------------------------------------------------------

#
# Structure de la table `track_e_uploads`
#
# Création: Mercredi 07 Juillet 2004 à 17:20
# Dernière modification: Mercredi 07 Juillet 2004 à 17:20
#

CREATE TABLE `track_e_uploads` (
  `upload_id` int(11) NOT NULL auto_increment,
  `upload_user_id` int(10) default NULL,
  `upload_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `upload_work_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`upload_id`)
) TYPE=MyISAM COMMENT='Record some more informations about uploaded works' AUTO_INCREMENT=1 ;

#
# Contenu de la table `track_e_uploads`
#


# --------------------------------------------------------

#
# Structure de la table `userinfo_content`
#
# Création: Mercredi 07 Juillet 2004 à 17:20
# Dernière modification: Mercredi 07 Juillet 2004 à 17:20
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
) TYPE=MyISAM COMMENT='content of users information - organisation based on\r\nuserin' AUTO_INCREMENT=1 ;

#
# Contenu de la table `userinfo_content`
#


# --------------------------------------------------------

#
# Structure de la table `userinfo_def`
#
# Création: Mercredi 07 Juillet 2004 à 17:20
# Dernière modification: Mercredi 07 Juillet 2004 à 17:20
#

CREATE TABLE `userinfo_def` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `title` varchar(80) NOT NULL default '',
  `comment` varchar(160) default NULL,
  `nbLine` int(10) unsigned NOT NULL default '5',
  `rank` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM COMMENT='categories definition for user information of a course' AUTO_INCREMENT=1 ;

#
# Contenu de la table `userinfo_def`
#


# --------------------------------------------------------

#
# Structure de la table `work_student`
#
# Création: Mercredi 07 Juillet 2004 à 17:20
# Dernière modification: Mercredi 07 Juillet 2004 à 17:20
#

CREATE TABLE `work_student` (
  `work_id` int(11) NOT NULL default '0',
  `uname` varchar(30) NOT NULL default '',
  PRIMARY KEY  (`work_id`,`uname`)
) TYPE=MyISAM;

#
# Contenu de la table `work_student`
#

