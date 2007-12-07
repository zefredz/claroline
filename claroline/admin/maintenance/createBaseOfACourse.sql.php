<?php // $Id$

//if ($singleDbEnabled) die('$singleDbEnabled is true,  we can upgrade this actually');

$sqlForUpdate[] = "### Try to upgrade course tables (rename, create, alter, update)";

/**
 * RENAME AND TRY TO CREATE ANNOUCEMENT AND ASSIGNMENT_DOC TABLE
 *
 * announcement (rename annonces 1.4)
 *
 */

{
 
$sqlForUpdate[] = "## Create and alter table announcement";

// Rename and create table

$sqlForUpdate[] = "RENAME TABLE `".$currentCourseDbNameGlu."annonces` TO `".$currentCourseDbNameGlu."announcement`";
$sqlForUpdate[] = "CREATE TABLE IF NOT EXISTS `".$currentCourseDbNameGlu."announcement` (
  `id` mediumint(11) NOT NULL auto_increment,
  `title` varchar(80) default NULL,
  `contenu` text,
  `temps` date default NULL,
  `ordre` mediumint(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM COMMENT='announcements table';";

// Add missing fields

$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."announcement` ADD `id` mediumint(11) NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."announcement` ADD `title` varchar(80) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."announcement` ADD `contenu` text;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."announcement` ADD `temps` date default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."announcement` ADD `code_cours` varchar(40) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."announcement` ADD `ordre` mediumint(11) NOT NULL default '0';";

// Alter fields

$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."announcement` CHANGE `id` `id` mediumint(11) NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."announcement` CHANGE `title` `title` varchar(80) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."announcement` CHANGE `contenu` `contenu` text;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."announcement` CHANGE `temps` `temps` date default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."announcement` CHANGE `code_cours` `code_cours` varchar(40) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."announcement` CHANGE `ordre` `ordre` mediumint(11) NOT NULL default '0';";

// Get data from main db

$sqlForUpdate[] = "# Query: Import announcement in new announcement table";
$sqlForUpdate[] = "INSERT IGNORE INTO `".$currentCourseDbNameGlu."announcement`
 (`id`, `contenu`, `temps`, `ordre`)
 SELECT
 	`id`, `contenu`, `temps`, `ordre` FROM `".$mainDbName."`.`annonces`
	WHERE `code_cours` = @currentCourseCode";
 
}

/**
 * RENAME AND TRY TO CREATE ASSIGNMENT_DOC TABLE
 *
 * assignment_doc (rename work 1.4)
 *
 */

{

$sqlForUpdate[] = "## Create and alter table `assignment_doc`";

// Rename and create

$sqlForUpdate[] = "RENAME TABLE `".$currentCourseDbNameGlu."work` TO `".$currentCourseDbNameGlu."assignment_doc`  "; 
$sqlForUpdate[] = "CREATE TABLE IF NOT EXISTS `".$currentCourseDbNameGlu."assignment_doc` (
  `id` int(11) NOT NULL auto_increment,
  `url` varchar(200) default NULL,
  `titre` varchar(200) default NULL,
  `description` varchar(250) default NULL,
  `auteurs` varchar(200) default NULL,
  `active` tinyint(1) default NULL,
  `accepted` tinyint(1) default NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;";

// Missing fields

$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."assignment_doc` ADD `id` int(11) NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."assignment_doc` ADD `url` varchar(200) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."assignment_doc` ADD `titre` varchar(200) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."assignment_doc` ADD `description` varchar(250) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."assignment_doc` ADD `auteurs` varchar(200) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."assignment_doc` ADD `active` tinyint(1) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."assignment_doc` ADD `accepted` tinyint(1) default NULL;";

// Alter fields

$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."assignment_doc` CHANGE `id` `id` int(11) NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."assignment_doc` CHANGE `url` `url` varchar(200) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."assignment_doc` CHANGE `titre` `titre` varchar(200) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."assignment_doc` CHANGE `description` `description` varchar(250) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."assignment_doc` CHANGE `auteurs` `auteurs` varchar(200) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."assignment_doc` CHANGE `active` `active` tinyint(1) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."assignment_doc` CHANGE `accepted` `accepted` tinyint(1) default NULL;";

}

/**
 * RENAME AND TRY TO CREATE FORUM TABLE
 *
 * all tables are prefixed by bb_ in 1.5
 *
 * bb_access
 * bb_banlist
 * bb_categories
 * bb_config
 * bb_disallow
 * bb_forum_access
 * bb_forum_mods
 * bb_forums
 * bb_headermetafooter
 * bb_posts
 * bb_posts_text
 * bb_priv_msgs
 * bb_ranks
 * bb_rel_topic_userstonotify (new table in 1.5 for mail notify)
 * bb_sessions
 * bb_themes
 * bb_topics
 * bb_users
 * bb_whosonline
 * bb_words
 *
 */

{

$sqlForUpdate[] = "## Create and alter forum tables";

// Create and rename table

$sqlForUpdate[] = "RENAME TABLE `".$currentCourseDbNameGlu."access` TO `".$currentCourseDbNameGlu."bb_access`";
$sqlForUpdate[] = "RENAME TABLE `".$currentCourseDbNameGlu."banlist` TO `".$currentCourseDbNameGlu."bb_banlist`";
$sqlForUpdate[] = "RENAME TABLE `".$currentCourseDbNameGlu."catagories` TO `".$currentCourseDbNameGlu."bb_categories`";
$sqlForUpdate[] = "RENAME TABLE `".$currentCourseDbNameGlu."config` TO `".$currentCourseDbNameGlu."bb_config`";
$sqlForUpdate[] = "RENAME TABLE `".$currentCourseDbNameGlu."disallow` TO `".$currentCourseDbNameGlu."bb_disallow`";
$sqlForUpdate[] = "RENAME TABLE `".$currentCourseDbNameGlu."forum_access` TO `".$currentCourseDbNameGlu."bb_forum_access`";
$sqlForUpdate[] = "RENAME TABLE `".$currentCourseDbNameGlu."forum_mods` TO `".$currentCourseDbNameGlu."bb_forum_mods`";
$sqlForUpdate[] = "RENAME TABLE `".$currentCourseDbNameGlu."forums` TO `".$currentCourseDbNameGlu."bb_forums`";
$sqlForUpdate[] = "RENAME TABLE `".$currentCourseDbNameGlu."headermetafooter` TO `".$currentCourseDbNameGlu."bb_headermetafooter`";
$sqlForUpdate[] = "RENAME TABLE `".$currentCourseDbNameGlu."posts` TO `".$currentCourseDbNameGlu."bb_posts`";
$sqlForUpdate[] = "RENAME TABLE `".$currentCourseDbNameGlu."posts_text` TO `".$currentCourseDbNameGlu."bb_posts_text`";
$sqlForUpdate[] = "RENAME TABLE `".$currentCourseDbNameGlu."priv_msgs`TO `".$currentCourseDbNameGlu."bb_priv_msgs`";
$sqlForUpdate[] = "RENAME TABLE `".$currentCourseDbNameGlu."ranks` TO `".$currentCourseDbNameGlu."bb_ranks`";
$sqlForUpdate[] = "RENAME TABLE `".$currentCourseDbNameGlu."rel_topic_userstonotify` TO `".$currentCourseDbNameGlu."bb_rel_topic_userstonotify`";
$sqlForUpdate[] = "RENAME TABLE `".$currentCourseDbNameGlu."sessions` TO `".$currentCourseDbNameGlu."bb_sessions`";
$sqlForUpdate[] = "RENAME TABLE `".$currentCourseDbNameGlu."themes` TO `".$currentCourseDbNameGlu."bb_themes`";
$sqlForUpdate[] = "RENAME TABLE `".$currentCourseDbNameGlu."topics` TO `".$currentCourseDbNameGlu."bb_topics`";
$sqlForUpdate[] = "RENAME TABLE `".$currentCourseDbNameGlu."users` TO `".$currentCourseDbNameGlu."bb_users`";
$sqlForUpdate[] = "RENAME TABLE `".$currentCourseDbNameGlu."whosonline` TO `".$currentCourseDbNameGlu."bb_whosonline`";
$sqlForUpdate[] = "RENAME TABLE `".$currentCourseDbNameGlu."words` TO `".$currentCourseDbNameGlu."bb_words`";

// bb_access

$sqlForUpdate[] = "# table bb_access";
$sqlForUpdate[] = "CREATE TABLE IF NOT EXISTS `".$currentCourseDbNameGlu."bb_access` (
  `access_id` int(10) NOT NULL auto_increment,
  `access_title` varchar(20) default NULL,
  PRIMARY KEY  (`access_id`)
) TYPE=MyISAM;";

// bb_access: Add Missing fields

$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_access` ADD `access_id` int(10) NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_access` ADD `access_title` varchar(20) default NULL ;";

// bb_access: Alter

$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_access` CHANGE `access_id` `access_id` int(10) NOT NULL auto_increment ;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_access` CHANGE `access_title` `access_title` varchar(20) default NULL ;";

// bb_banlist

$sqlForUpdate[] = "# table bb_banlist`";
$sqlForUpdate[] = "CREATE TABLE IF NOT EXISTS `".$currentCourseDbNameGlu."bb_banlist` (
  `ban_id` int(10) NOT NULL auto_increment,
  `ban_userid` int(10) default NULL,
  `ban_ip` varchar(16) default NULL,
  `ban_start` int(32) default NULL,
  `ban_end` int(50) default NULL,
  `ban_time_type` int(10) default NULL,
  PRIMARY KEY  (`ban_id`),
  KEY `ban_id` (`ban_id`)
) TYPE=MyISAM;";

// bb_banlist : Add missing fields

$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_banlist` ADD `ban_id` int(10) NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_banlist` ADD `ban_userid` int(10) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_banlist` ADD `ban_ip` varchar(16) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_banlist` ADD `ban_start` int(32) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_banlist` ADD `ban_end` int(50) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_banlist` ADD `ban_time_type` int(10) default NULL;";

// bb_banlist : alter 

$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_banlist` CHANGE `ban_id` `ban_id` int(10) NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_banlist` CHANGE `ban_userid` `ban_userid` int(10) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_banlist` CHANGE `ban_ip` `ban_ip` varchar(16) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_banlist` CHANGE `ban_start` `ban_start` int(32) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_banlist` CHANGE `ban_end` `ban_end` int(50) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_banlist` CHANGE `ban_time_type` `ban_time_type` int(10) default NULL;";

// bb_categories

$sqlForUpdate[] = "# table `bb_categories`";
$sqlForUpdate[] = "CREATE TABLE IF NOT EXISTS `".$currentCourseDbNameGlu."bb_categories` (
  `cat_id` int(10) NOT NULL auto_increment,
  `cat_title` varchar(100) default NULL,
  `cat_order` varchar(10) default NULL,
  PRIMARY KEY  (`cat_id`)
) TYPE=MyISAM;";

// bb_categories

$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_categories` ADD `cat_id` int(10) NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_categories` ADD `cat_title` varchar(100) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_categories` ADD `cat_order` varchar(10) default NULL;";

// bb_categories

$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_categories` CHANGE `cat_id` `cat_id` int(10) NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_categories` CHANGE `cat_title` `cat_title` varchar(100) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_categories` CHANGE `cat_order` `cat_order` varchar(10) default NULL;";

// bb_config

$sqlForUpdate[] = "# table `bb_config`";
$sqlForUpdate[] = "CREATE TABLE IF NOT EXISTS `".$currentCourseDbNameGlu."bb_config` (
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
) TYPE=MyISAM;";

// bb_config : add missing fields 

$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_config` ADD `config_id` int(10) NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_config` ADD `sitename` varchar(100) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_config` ADD `allow_html` int(2) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_config` ADD `allow_bbcode` int(2) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_config` ADD `allow_sig` int(2) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_config` ADD `allow_namechange` int(2) default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_config` ADD `admin_passwd` varchar(32) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_config` ADD `selected` int(2) NOT NULL default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_config` ADD `posts_per_page` int(10) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_config` ADD `hot_threshold` int(10) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_config` ADD `topics_per_page` int(10) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_config` ADD `allow_theme_create` int(10) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_config` ADD `override_themes` int(2) default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_config` ADD `email_sig` varchar(255) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_config` ADD `email_from` varchar(100) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_config` ADD `default_lang` varchar(255) default NULL;";

// bb_config : alter 
 
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_config` CHANGE `config_id` `config_id` int(10) NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_config` CHANGE `sitename`	`sitename` varchar(100) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_config` CHANGE `allow_html` `allow_html` int(2) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_config` CHANGE `allow_bbcode` `allow_bbcode` int(2) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_config` CHANGE `allow_sig` `allow_sig` int(2) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_config` CHANGE `allow_namechange`	`allow_namechange` int(2) default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_config` CHANGE `admin_passwd` `admin_passwd` varchar(32) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_config` CHANGE `selected` `selected` int(2) NOT NULL default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_config` CHANGE `posts_per_page` `posts_per_page` int(10) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_config` CHANGE `hot_threshold` `hot_threshold` int(10) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_config` CHANGE `topics_per_page`	`topics_per_page` int(10) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_config` CHANGE `allow_theme_create` `allow_theme_create` int(10) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_config` CHANGE `override_themes`	`override_themes` int(2) default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_config` CHANGE `email_sig` `email_sig` varchar(255) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_config` CHANGE `email_from` `email_from` varchar(100) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_config` CHANGE `default_lang` `default_lang` varchar(255) default NULL;";

// bb_disallow

$sqlForUpdate[] = "# table `bb_disallow`";
$sqlForUpdate[] = "CREATE TABLE IF NOT EXISTS `".$currentCourseDbNameGlu."bb_disallow` (
  `disallow_id` int(10) NOT NULL auto_increment,
  `disallow_username` varchar(50) default NULL,
  PRIMARY KEY  (`disallow_id`)
) TYPE=MyISAM;";

// bb_disallow : Add missing fields 

$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_disallow` ADD `disallow_id` int(10) NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_disallow` ADD `disallow_username` varchar(50) default NULL;";

// bb_disallow : alter 

$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_disallow` CHANGE `disallow_id` `disallow_id` int(10) NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_disallow` CHANGE `disallow_username` `disallow_username` varchar(50) default NULL;";

// bb_forum_access

$sqlForUpdate[] = "# table `bb_forum_access`";
$sqlForUpdate[] = "CREATE TABLE IF NOT EXISTS `".$currentCourseDbNameGlu."bb_forum_access` (
  `forum_id` int(10) NOT NULL default '0',
  `user_id` int(10) NOT NULL default '0',
  `can_post` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`forum_id`,`user_id`)
) TYPE=MyISAM;";

// bb_forum_access : add missing fields 

$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_forum_access` ADD `forum_id` int(10) NOT NULL default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_forum_access` ADD `user_id` int(10) NOT NULL default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_forum_access` ADD `can_post` tinyint(1) NOT NULL default '0';";

// bb_forum_access: alter 

$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_forum_access` CHANGE `forum_id` `forum_id` int(10) NOT NULL default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_forum_access` CHANGE `user_id` `user_id` int(10) NOT NULL default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_forum_access` CHANGE `can_post` `can_post` tinyint(1) NOT NULL default '0';";

// bb_forum_mods

$sqlForUpdate[] = "# table `bb_forum_mods`";
$sqlForUpdate[] = "CREATE TABLE IF NOT EXISTS `".$currentCourseDbNameGlu."bb_forum_mods` (
  `forum_id` int(10) NOT NULL default '0',
  `user_id` int(10) NOT NULL default '0'
) TYPE=MyISAM;";

// bb_forum_mods : add missing fields 

$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_forum_mods` ADD `forum_id` int(10) NOT NULL default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_forum_mods` ADD `user_id` int(10) NOT NULL default '0';";

// bb_forum_mods : alter 

$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_forum_mods` CHANGE `forum_id` `forum_id` int(10) NOT NULL default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_forum_mods` CHANGE `user_id` `user_id` int(10) NOT NULL default '0';";

// bb_forums

$sqlForUpdate[] = "# table `bb_forums`";
$sqlForUpdate[] = "CREATE TABLE IF NOT EXISTS `".$currentCourseDbNameGlu."bb_forums` (
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
) TYPE=MyISAM;";

// bb_forums : add missing fields 

$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_forums` ADD `forum_id` int(10) NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_forums` ADD `forum_name` varchar(150) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_forums` ADD `forum_desc` text;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_forums` ADD `forum_access` int(10) default '1';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_forums` ADD `forum_moderator` int(10) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_forums` ADD `forum_topics` int(10) NOT NULL default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_forums` ADD `forum_posts` int(10) NOT NULL default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_forums` ADD `forum_last_post_id` int(10) NOT NULL default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_forums` ADD `cat_id` int(10) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_forums` ADD `forum_type` int(10) default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_forums` ADD `md5` varchar(32) NOT NULL default '';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_forums` ADD `forum_order` int(10) default '0';";

// bb_forums : alter 

$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_forums` CHANGE `forum_id`	`forum_id` int(10) NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_forums` CHANGE `forum_name` `forum_name` varchar(150) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_forums` CHANGE `forum_desc` `forum_desc` text;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_forums` CHANGE `forum_access` `forum_access` int(10) default '1';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_forums` CHANGE `forum_moderator` `forum_moderator` int(10) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_forums` CHANGE `forum_topics` `forum_topics` int(10) NOT NULL default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_forums` CHANGE `forum_posts` `forum_posts` int(10) NOT NULL default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_forums` CHANGE `forum_last_post_id` `forum_last_post_id` int(10) NOT NULL default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_forums` CHANGE `cat_id` `cat_id` int(10) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_forums` CHANGE `forum_type` `forum_type` int(10) default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_forums` CHANGE `md5` `md5` varchar(32) NOT NULL default '';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_forums` CHANGE `forum_order` `forum_order` int(10) default '0';";

// bb_headermetafooter

$sqlForUpdate[] = "# table `bb_headermetafooter`";
$sqlForUpdate[] = "CREATE TABLE IF NOT EXISTS `".$currentCourseDbNameGlu."bb_headermetafooter` (
  `header` text,
  `meta` text,
  `footer` text
) TYPE=MyISAM;";

// bb_headermetafooter  : add missing fields 

$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_headermetafooter` ADD `header` text ;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_headermetafooter` ADD `meta` text;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_headermetafooter` ADD `footer` text ;";

// bb_headermetafooter :  alter 

$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_headermetafooter` CHANGE `header` `header` text ;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_headermetafooter` CHANGE `meta` `meta` text ;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_headermetafooter` CHANGE `footer` `footer` text ;";

// bb_posts

$sqlForUpdate[] = "# table `bb_posts`";
$sqlForUpdate[] = "CREATE TABLE IF NOT EXISTS `".$currentCourseDbNameGlu."bb_posts` (
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
) TYPE=MyISAM;";

// bb_posts : add missing fields 

$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_posts` ADD `post_id` int(10) NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_posts` ADD `topic_id` int(10) NOT NULL default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_posts` ADD `forum_id` int(10) NOT NULL default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_posts` ADD `poster_id` int(10) NOT NULL default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_posts` ADD `post_time` varchar(20) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_posts` ADD `poster_ip` varchar(16) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_posts` ADD `nom` varchar(30) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_posts` ADD `prenom` varchar(30) default NULL;";

// bb_posts :  alter 

$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_posts` CHANGE `post_id` `post_id` int(10) NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_posts` CHANGE `topic_id` `topic_id` int(10) NOT NULL default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_posts` CHANGE `forum_id` `forum_id` int(10) NOT NULL default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_posts` CHANGE `poster_id`	`poster_id` int(10) NOT NULL default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_posts` CHANGE `post_time`	`post_time` varchar(20) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_posts` CHANGE `poster_ip`	`poster_ip` varchar(16) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_posts` CHANGE `nom` `nom` varchar(30) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_posts` CHANGE `prenom` `prenom` varchar(30) default NULL;";

// bb_posts_text

$sqlForUpdate[] = "# table `bb_posts_text`";
$sqlForUpdate[] = "CREATE TABLE IF NOT EXISTS `".$currentCourseDbNameGlu."bb_posts_text` (
  `post_id` int(10) NOT NULL default '0',
  `post_text` text,
  PRIMARY KEY  (`post_id`)
) TYPE=MyISAM;";

// bb_posts_text : add missing fields 

$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_posts_text` ADD `post_id` int(10) NOT NULL default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_posts_text` ADD `post_text` text;";

// bb_posts_text : alter 

$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_posts_text` CHANGE `post_id` `post_id` int(10) NOT NULL default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_posts_text` CHANGE `post_text` `post_text` text;";

// bb_priv_msgs

$sqlForUpdate[] = "# table `bb_priv_msgs`";
$sqlForUpdate[] = "CREATE TABLE IF NOT EXISTS `".$currentCourseDbNameGlu."bb_priv_msgs` (
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
) TYPE=MyISAM;";

// bb_priv_msgs : add missing fields 

$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_priv_msgs` ADD `msg_id` int(10) NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_priv_msgs` ADD `from_userid` int(10) NOT NULL default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_priv_msgs` ADD `to_userid` int(10) NOT NULL default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_priv_msgs` ADD `msg_time` varchar(20) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_priv_msgs` ADD `poster_ip` varchar(16) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_priv_msgs` ADD `msg_status` int(10) default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_priv_msgs` ADD `msg_text` text;";

// bb_priv_msgs : alter 

$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_priv_msgs` CHANGE `msg_id` `msg_id` int(10) NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_priv_msgs` CHANGE `from_userid` `from_userid` int(10) NOT NULL default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_priv_msgs` CHANGE `to_userid` `to_userid` int(10) NOT NULL default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_priv_msgs` CHANGE `msg_time` `msg_time` varchar(20) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_priv_msgs` CHANGE `poster_ip` `poster_ip` varchar(16) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_priv_msgs` CHANGE `msg_status` `msg_status` int(10) default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_priv_msgs` CHANGE `msg_text` `msg_text` text;";

// bb_ranks 

$sqlForUpdate[] = "# Structure of table `bb_ranks`";
$sqlForUpdate[] = "CREATE TABLE IF NOT EXISTS `".$currentCourseDbNameGlu."bb_ranks` (
  `rank_id` int(10) NOT NULL auto_increment,
  `rank_title` varchar(50) NOT NULL default '',
  `rank_min` int(10) NOT NULL default '0',
  `rank_max` int(10) NOT NULL default '0',
  `rank_special` int(2) default '0',
  `rank_image` varchar(255) default NULL,
  PRIMARY KEY  (`rank_id`),
  KEY `rank_min` (`rank_min`),
  KEY `rank_max` (`rank_max`)
) TYPE=MyISAM;";

// bb_ranks: add missing fields 

$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_ranks` ADD `rank_id` int(10) NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_ranks` ADD `rank_title` varchar(50) NOT NULL default '';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_ranks` ADD `rank_min` int(10) NOT NULL default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_ranks` ADD `rank_max` int(10) NOT NULL default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_ranks` ADD `rank_special` int(2) default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_ranks` ADD `rank_image` varchar(255) default NULL;";

// bb_ranks: alter 

$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_ranks` CHANGE `rank_id` `rank_id` int(10) NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_ranks` CHANGE `rank_title` `rank_title` varchar(50) NOT NULL default '';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_ranks` CHANGE `rank_min` `rank_min` int(10) NOT NULL default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_ranks` CHANGE `rank_max` `rank_max` int(10) NOT NULL default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_ranks` CHANGE `rank_special` `rank_special` int(2) default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_ranks` CHANGE `rank_image` `rank_image` varchar(255) default NULL;";

// bb_rel_topic_userstonotify

$sqlForUpdate[] = "# table `bb_rel_topic_userstonotify`";
$sqlForUpdate[] = "CREATE TABLE IF NOT EXISTS `".$currentCourseDbNameGlu."bb_rel_topic_userstonotify` (
  `notify_id` int(10) NOT NULL auto_increment,
  `user_id` int(10) NOT NULL default '0',
  `topic_id` int(10) NOT NULL default '0',
  PRIMARY KEY  (`notify_id`),
  KEY `SECONDARY` (`user_id`,`topic_id`)
) TYPE=MyISAM;";

// bb_rel_topic_userstonotify : add missing fields 

$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_rel_topic_userstonotify` ADD `notify_id` int(10) NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_rel_topic_userstonotify` ADD `user_id` int(10) NOT NULL default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_rel_topic_userstonotify` ADD `topic_id` int(10) NOT NULL default '0';";

// bb_rel_topic_userstonotify : alter 

$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_rel_topic_userstonotify` CHANGE `notify_id` `notify_id` int(10) NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_rel_topic_userstonotify` CHANGE `user_id` `user_id` int(10) NOT NULL default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_rel_topic_userstonotify` CHANGE `topic_id` `topic_id` int(10) NOT NULL default '0'";

// bb_sessions

$sqlForUpdate[] = "# table `bb_sessions`";
$sqlForUpdate[] = "CREATE TABLE IF NOT EXISTS `".$currentCourseDbNameGlu."bb_sessions` (
  `sess_id` int(10) unsigned NOT NULL default '0',
  `user_id` int(10) NOT NULL default '0',
  `start_time` int(10) unsigned NOT NULL default '0',
  `remote_ip` varchar(15) NOT NULL default '',
  PRIMARY KEY  (`sess_id`),
  KEY `sess_id` (`sess_id`),
  KEY `start_time` (`start_time`),
  KEY `remote_ip` (`remote_ip`)
) TYPE=MyISAM;";

// bb_sessions : add missing fields 

$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_sessions` ADD `sess_id` int(10) unsigned NOT NULL default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_sessions` ADD `user_id` int(10) NOT NULL default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_sessions` ADD `start_time` int(10) unsigned NOT NULL default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_sessions` ADD `remote_ip` varchar(15) NOT NULL default '';";

// bb_sessions : alter 

$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_sessions` CHANGE `sess_id` `sess_id` int(10) unsigned NOT NULL default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_sessions` CHANGE `user_id` `user_id` int(10) NOT NULL default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_sessions` CHANGE `start_time` `start_time` int(10) unsigned NOT NULL default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_sessions` CHANGE `remote_ip` `remote_ip` varchar(15) NOT NULL default '';";

// bb_themes

$sqlForUpdate[] = "# table `bb_themes`";
$sqlForUpdate[] = "CREATE TABLE IF NOT EXISTS `".$currentCourseDbNameGlu."bb_themes` (
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
) TYPE=MyISAM;";

// bb_themes : add missing fields 

$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_themes` ADD `theme_id` int(10) NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_themes` ADD `theme_name` varchar(35) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_themes` ADD `bgcolor` varchar(10) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_themes` ADD `textcolor` varchar(10) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_themes` ADD `color1` varchar(10) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_themes` ADD `color2` varchar(10) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_themes` ADD `table_bgcolor` varchar(10) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_themes` ADD `header_image` varchar(50) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_themes` ADD `newtopic_image` varchar(50) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_themes` ADD `reply_image` varchar(50) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_themes` ADD `linkcolor` varchar(15) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_themes` ADD `vlinkcolor` varchar(15) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_themes` ADD `theme_default` int(2) default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_themes` ADD `fontface` varchar(100) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_themes` ADD `fontsize1` varchar(5) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_themes` ADD `fontsize2` varchar(5) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_themes` ADD `fontsize3` varchar(5) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_themes` ADD `fontsize4` varchar(5) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_themes` ADD `tablewidth` varchar(10) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_themes` ADD `replylocked_image` varchar(255) default NULL;";

// bb_themes : alter 

$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_themes` CHANGE `theme_id` `theme_id` int(10) NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_themes` CHANGE `theme_name` `theme_name` varchar(35) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_themes` CHANGE `bgcolor` 	`bgcolor` varchar(10) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_themes` CHANGE `textcolor` `textcolor` varchar(10) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_themes` CHANGE `color1` `color1` varchar(10) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_themes` CHANGE `color2` `color2` varchar(10) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_themes` CHANGE `table_bgcolor` `table_bgcolor` varchar(10) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_themes` CHANGE `header_image` `header_image` varchar(50) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_themes` CHANGE `newtopic_image` `newtopic_image` varchar(50) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_themes` CHANGE `reply_image` `reply_image` varchar(50) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_themes` CHANGE `linkcolor` `linkcolor` varchar(15) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_themes` CHANGE `vlinkcolor` `vlinkcolor` varchar(15) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_themes` CHANGE `theme_default` `theme_default` int(2) default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_themes` CHANGE `fontface` `fontface` varchar(100) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_themes` CHANGE `fontsize1` `fontsize1` varchar(5) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_themes` CHANGE `fontsize2` `fontsize2` varchar(5) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_themes` CHANGE `fontsize3` `fontsize3` varchar(5) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_themes` CHANGE `fontsize4` `fontsize4` varchar(5) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_themes` CHANGE `tablewidth` `tablewidth` varchar(10) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_themes` CHANGE `replylocked_image` `replylocked_image` varchar(255) default NULL;";

// bb_topics

$sqlForUpdate[] = "# table `bb_topics`";
$sqlForUpdate[] = "CREATE TABLE IF NOT EXISTS `".$currentCourseDbNameGlu."bb_topics` (
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
) TYPE=MyISAM;";

// bb_topics : add missing fields 

$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_topics` ADD `topic_id` int(10) NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_topics` ADD `topic_title` varchar(100) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_topics` ADD `topic_poster` int(10) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_topics` ADD `topic_time` varchar(20) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_topics` ADD `topic_views` int(10) NOT NULL default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_topics` ADD `topic_replies` int(10) NOT NULL default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_topics` ADD `topic_last_post_id` int(10) NOT NULL default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_topics` ADD `forum_id` int(10) NOT NULL default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_topics` ADD `topic_status` int(10) NOT NULL default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_topics` ADD `topic_notify` int(2) default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_topics` ADD `nom` varchar(30) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_topics` ADD `prenom` varchar(30) default NULL;";

// bb_topics : alter 

$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_topics` CHANGE `topic_id`	`topic_id` int(10) NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_topics` CHANGE `topic_title` `topic_title` varchar(100) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_topics` CHANGE `topic_poster` `topic_poster` int(10) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_topics` CHANGE `topic_time` `topic_time` varchar(20) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_topics` CHANGE `topic_views` `topic_views` int(10) NOT NULL default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_topics` CHANGE `topic_replies` `topic_replies` int(10) NOT NULL default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_topics` CHANGE `topic_last_post_id` `topic_last_post_id` int(10) NOT NULL default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_topics` CHANGE `forum_id`	`forum_id` int(10) NOT NULL default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_topics` CHANGE `topic_status` `topic_status` int(10) NOT NULL default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_topics` CHANGE `topic_notify` `topic_notify` int(2) default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_topics` CHANGE `nom` `nom` varchar(30) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_topics` CHANGE `prenom` `prenom` varchar(30) default NULL;";

// bb_users

$sqlForUpdate[] = "# table `bb_users`";
$sqlForUpdate[] = "CREATE TABLE IF NOT EXISTS `".$currentCourseDbNameGlu."bb_users` (
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
) TYPE=MyISAM;";

// bb_users : add missing fields 

$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_users` ADD `user_id` int(10) NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_users` ADD `username` varchar(40) NOT NULL default '';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_users` ADD `user_regdate` varchar(20) NOT NULL default '';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_users` ADD `user_password` varchar(32) NOT NULL default '';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_users` ADD `user_email` varchar(50) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_users` ADD `user_icq` varchar(15) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_users` ADD `user_website` varchar(100) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_users` ADD `user_occ` varchar(100) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_users` ADD `user_from` varchar(100) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_users` ADD `user_intrest` varchar(150) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_users` ADD `user_sig` varchar(255) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_users` ADD `user_viewemail` tinyint(2) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_users` ADD `user_theme` int(10) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_users` ADD `user_aim` varchar(18) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_users` ADD `user_yim` varchar(25) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_users` ADD `user_msnm` varchar(25) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_users` ADD `user_posts` int(10) default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_users` ADD `user_attachsig` int(2) default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_users` ADD `user_desmile` int(2) default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_users` ADD `user_html` int(2) default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_users` ADD `user_bbcode` int(2) default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_users` ADD `user_rank` int(10) default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_users` ADD `user_level` int(10) default '1';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_users` ADD `user_lang` varchar(255) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_users` ADD `user_actkey` varchar(32) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_users` ADD `user_newpasswd` varchar(32) default NULL;";

// bb_users : alter 

$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_users` CHANGE `user_id` `user_id` int(10) NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_users` CHANGE `username` `username` varchar(40) NOT NULL default '';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_users` CHANGE `user_regdate` `user_regdate` varchar(20) NOT NULL default '';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_users` CHANGE `user_password` `user_password` varchar(32) NOT NULL default '';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_users` CHANGE `user_email` `user_email` varchar(50) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_users` CHANGE `user_icq` `user_icq` varchar(15) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_users` CHANGE `user_website` `user_website` varchar(100) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_users` CHANGE `user_occ` `user_occ` varchar(100) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_users` CHANGE `user_from` `user_from` varchar(100) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_users` CHANGE `user_intrest` `user_intrest` varchar(150) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_users` CHANGE `user_sig` `user_sig` varchar(255) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_users` CHANGE `user_viewemail` `user_viewemail` tinyint(2) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_users` CHANGE `user_theme` `user_theme` int(10) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_users` CHANGE `user_aim` `user_aim` varchar(18) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_users` CHANGE `user_yim` `user_yim` varchar(25) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_users` CHANGE `user_msnm` `user_msnm` varchar(25) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_users` CHANGE `user_posts` `user_posts` int(10) default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_users` CHANGE `user_attachsig` `user_attachsig` int(2) default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_users` CHANGE `user_desmile` `user_desmile` int(2) default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_users` CHANGE `user_html` `user_html` int(2) default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_users` CHANGE `user_bbcode` `user_bbcode` int(2) default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_users` CHANGE `user_rank` `user_rank` int(10) default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_users` CHANGE `user_level` `user_level` int(10) default '1';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_users` CHANGE `user_lang`	`user_lang` varchar(255) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_users` CHANGE `user_actkey` `user_actkey` varchar(32) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_users` CHANGE `user_newpasswd` `user_newpasswd` varchar(32) default NULL;";

// bb_whosonline

$sqlForUpdate[] = "# table `bb_whosonline`";
$sqlForUpdate[] = "CREATE TABLE IF NOT EXISTS `".$currentCourseDbNameGlu."bb_whosonline` (
  `id` int(3) NOT NULL auto_increment,
  `ip` varchar(255) default NULL,
  `name` varchar(255) default NULL,
  `count` varchar(255) default NULL,
  `date` varchar(255) default NULL,
  `username` varchar(40) default NULL,
  `forum` int(10) default NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;";

// bb_whosonline : add missing fields 

$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_whosonline` ADD `id` int(3) NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_whosonline` ADD `ip` varchar(255) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_whosonline` ADD `name` varchar(255) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_whosonline` ADD `count` varchar(255) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_whosonline` ADD `date` varchar(255) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_whosonline` ADD `username` varchar(40) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_whosonline` ADD `forum` int(10) default NULL;";

// bb_whosonline : alter 

$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_whosonline` CHANGE `id` `id` int(3) NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_whosonline` CHANGE `ip` `ip` varchar(255) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_whosonline` CHANGE `name` `name` varchar(255) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_whosonline` CHANGE `count` `count` varchar(255) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_whosonline` CHANGE `date`	`date` varchar(255) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_whosonline` CHANGE `username` `username` varchar(40) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_whosonline` CHANGE `forum` `forum` int(10) default NULL;";

// bb_words

$sqlForUpdate[] = "# table `bb_words`";
$sqlForUpdate[] = "CREATE TABLE IF NOT EXISTS `".$currentCourseDbNameGlu."bb_words` (
  `word_id` int(10) NOT NULL auto_increment,
  `word` varchar(100) default NULL,
  `replacement` varchar(100) default NULL,
  PRIMARY KEY  (`word_id`)
) TYPE=MyISAM;";

// bb_words : add missing fields 

$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_words` ADD `word_id` int(10) NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_words` ADD `word` varchar(100) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_words` ADD `replacement` varchar(100) default NULL;";

// bb_words : alter 

$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_words` CHANGE `word_id` `word_id` int(10) NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_words` CHANGE `word` `word` varchar(100) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_words` CHANGE `replacement` `replacement` varchar(100) default NULL;";

}

/**
 * TRY TO CREATE CALENDAR TABLE
 *
 * calendar_event (rename agenda 1.4)
 *
 */

{

$sqlForUpdate[] = "## Create and alter table calendar_event";

// Rename and create table

$sqlForUpdate[] = "RENAME TABLE `".$currentCourseDbNameGlu."agenda` TO `".$currentCourseDbNameGlu."calendar_event`";
$sqlForUpdate[] = "CREATE TABLE IF NOT EXISTS `".$currentCourseDbNameGlu."calendar_event` (
  `id` int(11) NOT NULL auto_increment,
  `titre` varchar(200) default NULL,
  `contenu` text,
  `day` date NOT NULL default '0000-00-00',
  `hour` time NOT NULL default '00:00:00',
  `lasting` varchar(20) default NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;";

// Add Missing Fields

$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."calendar_event` ADD `id` int(11) NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."calendar_event` ADD `titre` varchar(200) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."calendar_event` ADD `contenu` text;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."calendar_event` ADD `day` date NOT NULL default '0000-00-00';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."calendar_event` ADD `hour` time NOT NULL default '00:00:00';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."calendar_event` ADD `lasting` varchar(20) default NULL;";

// Alter Fields

$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."calendar_event` CHANGE `id` `id` int(11) NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."calendar_event` CHANGE `titre` `titre` varchar(200) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."calendar_event` CHANGE `contenu` `contenu` text;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."calendar_event` CHANGE `day`	`day` date NOT NULL default '0000-00-00';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."calendar_event` CHANGE `hour` `hour` time NOT NULL default '00:00:00';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."calendar_event` CHANGE `lasting` `lasting` varchar(20) default NULL;";

}

/**
 * TRY TO CREATE COURSE DESCRIPTION TABLE
 *
 * course_description
 *
 */

{

$sqlForUpdate[] = "## Create and alter table course_description";

// Create table

$sqlForUpdate[] = "CREATE TABLE IF NOT EXISTS `".$currentCourseDbNameGlu."course_description` (
  `id` tinyint(3) unsigned NOT NULL default '0',
  `title` varchar(255) default NULL,
  `content` text,
  `upDate` datetime NOT NULL default '0000-00-00 00:00:00',
  UNIQUE KEY `id` (`id`)
) TYPE=MyISAM COMMENT='for course description tool';";

// Add missing fields

$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."course_description` ADD `id` tinyint(3) unsigned NOT NULL default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."course_description` ADD `title` varchar(255) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."course_description` ADD `content` text;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."course_description` ADD `upDate` datetime NOT NULL default '0000-00-00 00:00:00';";

// Alter fields

$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."course_description` CHANGE `id` `id` tinyint(3) unsigned NOT NULL default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."course_description` CHANGE `title` `title` varchar(255) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."course_description` CHANGE `content`	`content` text;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."course_description` CHANGE `upDate` `upDate` datetime NOT NULL default '0000-00-00 00:00:00';";

}

/**
 * TRY TO CREATE DOCUMENT TABLE
 *
 * document
 *
 * TODO: get links table content
 *
 */

{

$sqlForUpdate[] = "## Create and alter table document";

// Create table

$sqlForUpdate[] = "CREATE TABLE IF NOT EXISTS `".$currentCourseDbNameGlu."document` (
  `id` int(4) NOT NULL auto_increment,
  `path` varchar(255) NOT NULL default '',
  `visibility` char(1) NOT NULL default 'v',
  `comment` varchar(255) default NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;";

// Add missing fields

$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."document` ADD `id` int(4) NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."document` ADD `path` varchar(255) NOT NULL default '';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."document` ADD `visibility` char(1) NOT NULL default 'v';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."document` ADD `comment` varchar(255) default NULL;";

// Alter fields

$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."document` CHANGE `id` `id` int(4) NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."document` CHANGE `path` `path` varchar(255) NOT NULL default '';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."document` CHANGE `visibility` `visibility` char(1) NOT NULL default 'v';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."document` CHANGE `comment` `comment` varchar(255) default NULL;";

// Add and alter fields in link table

$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."liens` ADD `id` int(11) NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."liens` ADD `url` varchar(150) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."liens` ADD `titre` varchar(150) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."liens` ADD `description` text;";

$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."liens` CHANGE `id` `id` int(11) NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."liens` CHANGE `url` `url` varchar(150) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."liens` CHANGE `titre` `titre` varchar(150) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."liens` CHANGE `description`	`description` text;";

}

/**
 * RENAME AND TRY TO CREATE GROUP TABLE
 *
 * group_property (rename group_properties 1.4)
 * group_rel_team_user (rename user_group 1.4)
 * group_team (rename student_group 1.4)
 *
 */

{

$sqlForUpdate[] = "## Create and alter group tables";

// Rename and create table

$sqlForUpdate[] = "RENAME TABLE `".$currentCourseDbNameGlu."group_properties` 	TO `".$currentCourseDbNameGlu."group_property` "; 
$sqlForUpdate[] = "RENAME TABLE `".$currentCourseDbNameGlu."user_group`		TO `".$currentCourseDbNameGlu."group_rel_team_user` ";
$sqlForUpdate[] = "RENAME TABLE `".$currentCourseDbNameGlu."student_group`	TO `".$currentCourseDbNameGlu."group_team` ";
$sqlForUpdate[] = "# table group_property";
$sqlForUpdate[] = "CREATE TABLE IF NOT EXISTS `".$currentCourseDbNameGlu."group_property` (
  `id` tinyint(4) NOT NULL auto_increment,
  `self_registration` tinyint(4) default '1',
  `nbGroupPerUser` tinyint(3) unsigned default '1',
  `private` tinyint(4) default '0',
  `forum` tinyint(4) default '1',
  `document` tinyint(4) default '1',
  `wiki` tinyint(4) default '0',
  `chat` tinyint(4) default '1',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;";

$sqlForUpdate[] = "# table group_rel_team_user";
$sqlForUpdate[] = "CREATE TABLE IF NOT EXISTS `".$currentCourseDbNameGlu."group_rel_team_user` (
  `id` int(11) NOT NULL auto_increment,
  `user` int(11) NOT NULL default '0',
  `team` int(11) NOT NULL default '0',
  `status` int(11) NOT NULL default '0',
  `role` varchar(50) NOT NULL default '',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;";

$sqlForUpdate[] = "# table group_team";
$sqlForUpdate[] = "CREATE TABLE IF NOT EXISTS `".$currentCourseDbNameGlu."group_team` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(100) default NULL,
  `description` text,
  `tutor` int(11) default NULL,
  `forumId` int(11) default NULL,
  `maxStudent` int(11) NOT NULL default '0',
  `secretDirectory` varchar(30) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;";

// Add missing fields

$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."group_property` ADD `id` tinyint(4) NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."group_property` ADD `self_registration` tinyint(4) default '1';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."group_property` ADD `nbGroupPerUser` tinyint(3) unsigned default '1';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."group_property` ADD `private` tinyint(4) default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."group_property` ADD `forum` tinyint(4) default '1';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."group_property` ADD `document` tinyint(4) default '1';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."group_property` ADD `wiki` tinyint(4) default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."group_property` ADD `agenda` tinyint(4) default '0';";

$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."group_team` ADD `id` int(11) NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."group_team` ADD `name` varchar(100) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."group_team` ADD `description` text;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."group_team` ADD `tutor` int(11) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."group_team` ADD `forumId` int(11) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."group_team` ADD `maxStudent` int(11) NOT NULL default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."group_team` ADD `secretDirectory` varchar(30) NOT NULL default '0';";

$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."group_rel_team_user` ADD `id` int(11) NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."group_rel_team_user` ADD `user` int(11) NOT NULL default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."group_rel_team_user` ADD `team` int(11) NOT NULL default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."group_rel_team_user` ADD `status` int(11) NOT NULL default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."group_rel_team_user` ADD `role` varchar(50) NOT NULL default '';";

// alter fields

$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."group_property` CHANGE `forum` `forum` tinyint(4) default '1';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."group_property` CHANGE `document` `document` tinyint(4) default '1';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."group_property` CHANGE `wiki` `wiki` tinyint(4) default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."group_property` CHANGE `agenda` `agenda` tinyint(4) default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."group_property` CHANGE `nbGroupPerUser` `nbGroupPerUser` tinyint(3) unsigned default '1';";
  // rename field agenda in chat
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."group_property` CHANGE `agenda` `chat` tinyint(4) default '1';";

$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."group_team` CHANGE `id` `id` int(11) NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."group_team` CHANGE `name` `name` varchar(100) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."group_team` CHANGE `description` `description` text;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."group_team` CHANGE `tutor` `tutor` int(11) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."group_team` CHANGE `forumId` `forumId` int(11) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."group_team` CHANGE `maxStudent` `maxStudent` int(11) NOT NULL default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."group_team` CHANGE `secretDirectory`	`secretDirectory` varchar(30) NOT NULL default '0';";
 
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."group_rel_team_user` CHANGE `id` `id` int(11) NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."group_rel_team_user` CHANGE `user` `user` int(11) NOT NULL default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."group_rel_team_user` CHANGE `team` `team` int(11) NOT NULL default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."group_rel_team_user` CHANGE `status` `status` int(11) NOT NULL default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."group_rel_team_user` CHANGE `role` `role` varchar(50) NOT NULL default '';";

}

/**
 * TRY TO CREATE LEARNING PATH TABLE
 *
 * lp_asset
 * lp_learnPath
 * lp_module
 * lp_rel_learnPath_module
 * lp_user_module_progress
 *
 */

{

$sqlForUpdate[] = "## Create lerning path tables ";

# Create table

$sqlForUpdate[] = "# table lp_asset";
$sqlForUpdate[] = "CREATE TABLE IF NOT EXISTS `".$currentCourseDbNameGlu."lp_asset` (
  `asset_id` int(11) NOT NULL auto_increment,
  `module_id` int(11) NOT NULL default '0',
  `path` varchar(255) NOT NULL default '',
  `comment` varchar(255) default NULL,
  PRIMARY KEY  (`asset_id`)
) TYPE=MyISAM COMMENT='List of resources of module of learning paths';";

$sqlForUpdate[] = "# table lp_learnPath";
$sqlForUpdate[] = "CREATE TABLE IF NOT EXISTS `".$currentCourseDbNameGlu."lp_learnPath` (
  `learnPath_id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `comment` text NOT NULL,
  `lock` enum('OPEN','CLOSE') NOT NULL default 'OPEN',
  `visibility` enum('HIDE','SHOW') NOT NULL default 'SHOW',
  `rank` int(11) NOT NULL default '0',
  PRIMARY KEY  (`learnPath_id`),
  UNIQUE KEY `rank` (`rank`)
) TYPE=MyISAM COMMENT='List of learning Paths';";

$sqlForUpdate[] = "# table lp_module";
$sqlForUpdate[] = "CREATE TABLE IF NOT EXISTS `".$currentCourseDbNameGlu."lp_module` (
  `module_id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `comment` text NOT NULL,
  `accessibility` enum('PRIVATE','PUBLIC') NOT NULL default 'PRIVATE',
  `startAsset_id` int(11) NOT NULL default '0',
  `contentType` enum('CLARODOC','DOCUMENT','EXERCISE','HANDMADE','SCORM','LABEL') NOT NULL default 'CLARODOC',
  `launch_data` text NOT NULL,
  PRIMARY KEY  (`module_id`)
) TYPE=MyISAM COMMENT='List of available modules used in learning paths';";

$sqlForUpdate[] = "# table lp_rel_learnPath_module";
$sqlForUpdate[] = "CREATE TABLE IF NOT EXISTS `".$currentCourseDbNameGlu."lp_rel_learnPath_module` (
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
) TYPE=MyISAM COMMENT='This table links module to the learning path using them';";

$sqlForUpdate[] = "# table lp_user_module_progress";
$sqlForUpdate[] = "CREATE TABLE IF NOT EXISTS `".$currentCourseDbNameGlu."lp_user_module_progress` (
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
) TYPE=MyISAM COMMENT='Record the last known status of the user in the course';";

}

/**
 *
 * TRY TO CREATE PAGES TABLE
 *
 */

// pages

$sqlForUpdate[] = "# table `pages`";
$sqlForUpdate[] = "CREATE TABLE IF NOT EXISTS `".$currentCourseDbNameGlu."pages` (
  `id` int(11) NOT NULL auto_increment,
  `url` varchar(200) default NULL,
  `titre` varchar(200) default NULL,
  `description` text,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;";

// pages : add missing fields 

$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."pages` ADD `id` int(11) NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."pages` ADD `url` varchar(200) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."pages` ADD `titre` varchar(200) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."pages` ADD `description` text;";

// pages : alter 

$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."pages` CHANGE `id` `id` int(11) NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."pages` CHANGE `url` `url` varchar(200) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."pages` CHANGE `titre` `titre` varchar(200) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."pages` CHANGE `description` `description` text;";

/**
 * RENAME AND TRY TO CREATE QUIZZ TABLE
 *
 * quiz_answer
 * quiz_question
 * quiz_rel_test_question
 * quiz_test
 *
 */

{

$sqlForUpdate[] = "## Create and alter quizz tables";

// Rename and create table

$sqlForUpdate[] = "# Rename Quizz tables";
$sqlForUpdate[] = "RENAME TABLE `".$currentCourseDbNameGlu."exercices` 		TO `".$currentCourseDbNameGlu."quiz_test` ";
$sqlForUpdate[] = "RENAME TABLE `".$currentCourseDbNameGlu."exercice_question` 	TO `".$currentCourseDbNameGlu."quiz_rel_test_question` ";
$sqlForUpdate[] = "RENAME TABLE `".$currentCourseDbNameGlu."questions` 		TO `".$currentCourseDbNameGlu."quiz_question` ";
$sqlForUpdate[] = "RENAME TABLE `".$currentCourseDbNameGlu."reponses` 		TO `".$currentCourseDbNameGlu."quiz_answer` ";

$sqlForUpdate[] = "# table `quiz_test`";
$sqlForUpdate[] = "CREATE TABLE IF NOT EXISTS `".$currentCourseDbNameGlu."quiz_test` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `titre` varchar(100) NOT NULL default '',
  `description` text NOT NULL,
  `type` tinyint(4) unsigned NOT NULL default '1',
  `random` smallint(6) NOT NULL default '0',
  `active` tinyint(4) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;";

$sqlForUpdate[] = "# table `quiz_rel_test_question`";
$sqlForUpdate[] = "CREATE TABLE IF NOT EXISTS `".$currentCourseDbNameGlu."quiz_rel_test_question` (
  `question_id` mediumint(8) unsigned NOT NULL default '0',
  `exercice_id` mediumint(8) unsigned NOT NULL default '0',
  PRIMARY KEY  (`question_id`,`exercice_id`)
) TYPE=MyISAM;";

$sqlForUpdate[] = "# table `quiz_question`";
$sqlForUpdate[] = "CREATE TABLE IF NOT EXISTS `".$currentCourseDbNameGlu."quiz_question` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `question` varchar(200) NOT NULL default '',
  `description` text NOT NULL,
  `ponderation` smallint(5) unsigned default NULL,
  `q_position` mediumint(8) unsigned NOT NULL default '1',
  `type` tinyint(3) unsigned NOT NULL default '2',
  `picture_name` varchar(50) default '',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;";

$sqlForUpdate[] = "# table `quiz_answer`";
$sqlForUpdate[] = "CREATE TABLE IF NOT EXISTS `".$currentCourseDbNameGlu."quiz_answer` (
  `id` mediumint(8) unsigned NOT NULL default '0',
  `question_id` mediumint(8) unsigned NOT NULL default '0',
  `reponse` text NOT NULL,
  `correct` mediumint(8) unsigned default NULL,
  `comment` text,
  `ponderation` smallint(5) default NULL,
  `r_position` mediumint(8) unsigned NOT NULL default '1',
  PRIMARY KEY  (`id`,`question_id`)
) TYPE=MyISAM;";

// Add Missing fields

$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."quiz_test` ADD `id` mediumint(8) unsigned NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."quiz_test` ADD `titre` varchar(100) NOT NULL default '';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."quiz_test` ADD `description` text NOT NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."quiz_test` ADD `type` tinyint(4) unsigned NOT NULL default '1';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."quiz_test` ADD `random` smallint(6) NOT NULL default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."quiz_test` ADD `active` tinyint(4) unsigned NOT NULL default '0';";

$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."quiz_rel_test_question` ADD `question_id` mediumint(8) unsigned NOT NULL default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."quiz_rel_test_question` ADD `exercice_id` mediumint(8) unsigned NOT NULL default '0';";

$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."quiz_question` ADD `id` mediumint(8) unsigned NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."quiz_question` ADD `question` varchar(100) NOT NULL default '';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."quiz_question` ADD `description` text NOT NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."quiz_question` ADD `ponderation` smallint(5) unsigned default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."quiz_question` ADD `q_position` mediumint(8) unsigned NOT NULL default '1';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."quiz_question` ADD `type` tinyint(3) unsigned NOT NULL default '2';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."quiz_question` ADD `picture_name` varchar(50) default '';";

$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."quiz_answer` ADD `id` mediumint(8) unsigned NOT NULL default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."quiz_answer` ADD `question_id` mediumint(8) unsigned NOT NULL default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."quiz_answer` ADD `reponse` text NOT NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."quiz_answer` ADD `correct` mediumint(8) unsigned default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."quiz_answer` ADD `comment` text;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."quiz_answer` ADD `ponderation` smallint(5) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."quiz_answer` ADD `r_position` mediumint(8) unsigned NOT NULL default '1';";

// Alter fields

$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."quiz_test` CHANGE `id`			`id` mediumint(8) unsigned NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."quiz_test` CHANGE `titre`		`titre` varchar(100) NOT NULL default '';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."quiz_test` CHANGE `description`	`description` text NOT NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."quiz_test` CHANGE `type`		`type` tinyint(4) unsigned NOT NULL default '1';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."quiz_test` CHANGE `random`		`random` smallint(6) NOT NULL default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."quiz_test` CHANGE `active`		`active` tinyint(4) unsigned NOT NULL default '0';";
 
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."quiz_rel_test_question` CHANGE `question_id`	`question_id` mediumint(8) unsigned NOT NULL default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."quiz_rel_test_question` CHANGE `exercice_id`	`exercice_id` mediumint(8) unsigned NOT NULL default '0';";

$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."quiz_question` CHANGE `id`		`id` mediumint(8) unsigned NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."quiz_question` CHANGE `question`	`question` varchar(100) NOT NULL default '';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."quiz_question` CHANGE `description`	`description` text NOT NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."quiz_question` CHANGE `ponderation`	`ponderation` smallint(5) unsigned default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."quiz_question` CHANGE `q_position`	`q_position` mediumint(8) unsigned NOT NULL default '1';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."quiz_question` CHANGE `type`		`type` tinyint(3) unsigned NOT NULL default '2';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."quiz_question` CHANGE `picture_name` `picture_name` varchar(50) default '';";
 
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."quiz_answer` CHANGE `id`			`id` mediumint(8) unsigned NOT NULL default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."quiz_answer` CHANGE `question_id`	`question_id` mediumint(8) unsigned NOT NULL default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."quiz_answer` CHANGE `reponse`		`reponse` text NOT NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."quiz_answer` CHANGE `correct`		`correct` mediumint(8) unsigned default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."quiz_answer` CHANGE `comment`		`comment` text;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."quiz_answer` CHANGE `ponderation`	`ponderation` smallint(5) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."quiz_answer` CHANGE `r_position`	`r_position` mediumint(8) unsigned NOT NULL default '1';";

}

/**
 * RENAME AND TRY TO CREATE TOOL TABLE
 *
 * tool_intro (rename introduction 1.4)
 *
 */

{

$sqlForUpdate[] = "## Create and alter table tool_intro";

// Rename and create table

$sqlForUpdate[] = "# Structure of table `tool_intro`";
$sqlForUpdate[] = "RENAME TABLE `".$currentCourseDbNameGlu."introduction`	TO `".$currentCourseDbNameGlu."tool_intro`";
$sqlForUpdate[] = "CREATE TABLE IF NOT EXISTS `".$currentCourseDbNameGlu."tool_intro` (
  `id` varchar(8) NOT NULL,
  `texte_intro` text,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;";

// Add missing Fields

$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."tool_intro` ADD `id` varchar(8) NOT NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."tool_intro` ADD `texte_intro` text;";

// Alter Fields

$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."tool_intro` CHANGE `id`		`id` varchar(8) NOT NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."tool_intro` CHANGE `texte_intro`	`texte_intro` text;";

// update id_tool in tool_intro 
// - 1 intro for cours_home
// 7 intro for work (assignment)
$sqlForUpdate[] = "# update";
$sqlForUpdate[] = " update `".$currentCourseDbNameGlu."tool_intro` set id = '-1' where id ='1';";
$sqlForUpdate[] = " update `".$currentCourseDbNameGlu."tool_intro` set id = '7' where id ='2';";


}

/**
 * RENAME AND TRY TO CREATE TOOL TABLE
 *
 * tool_list (don't rename accueil to tool_list. tool_list would be created and after accueil datas are imported in)
 *
 */

{

$sqlForUpdate[] = "# Structure of table `tool_list`";
$sqlForUpdate[] = "CREATE TABLE IF NOT EXISTS `".$currentCourseDbNameGlu."tool_list` (
  `id` int(11) NOT NULL auto_increment,
  `tool_id` int(10) unsigned default NULL,
  `rank` int(10) unsigned NOT NULL default '0',
  `access` enum('ALL','PLATFORM_MEMBER','COURSE_MEMBER','COURSE_TUTOR','GROUP_MEMBER','GROUP_TUTOR','COURSE_ADMIN','PLATFORM_ADMIN') NOT NULL default 'ALL',
  `script_url` varchar(255) default NULL,
  `script_name` varchar(255) default NULL,
  `addedTool` enum('YES','NO') default 'YES',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;";


$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."accueil` CHANGE `id`		`id` int(11) NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."accueil` CHANGE `rubrique`	`rubrique` varchar(100) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."accueil` CHANGE `lien`	`lien` varchar(255) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."accueil` CHANGE `image`	`image` varchar(100) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."accueil` CHANGE `visible`	`visible` tinyint(4) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."accueil` CHANGE `admin`	`admin` varchar(200) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."accueil` CHANGE `address`	`address` varchar(120) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."accueil` CHANGE `addedTool`	`addedTool` enum('YES','NO') default 'YES';";

//
//////////////// NOW ALL TABLE  ARE  CREATED,
/////////////// 1° insert link to tools
//////////////  2° insert sample.
//ADD TOOLS IN  ACCUEIL
// visible 4 all = 1;
// visible 4 Admin Of Course = 0;
// visible 4 Admin Of Claroline = 2;

$sqlForUpdate[] = "Update `".$currentCourseDbNameGlu."accueil` set addedTool = 'NO' where lien ='../claroline/calendar/agenda.php';";
$sqlForUpdate[] = "Update `".$currentCourseDbNameGlu."accueil` set addedTool = 'NO' where lien ='../claroline/link/link.php';";
$sqlForUpdate[] = "Update `".$currentCourseDbNameGlu."accueil` set addedTool = 'NO' where lien ='../claroline/document/document.php';";
$sqlForUpdate[] = "Update `".$currentCourseDbNameGlu."accueil` set addedTool = 'YES' where lien ='../claroline/video/video.php';";
$sqlForUpdate[] = "Update `".$currentCourseDbNameGlu."accueil` set addedTool = 'NO' where lien ='../claroline/work/work.php';";
$sqlForUpdate[] = "Update `".$currentCourseDbNameGlu."accueil` set addedTool = 'NO' where lien ='../claroline/announcements/announcements.php';";
$sqlForUpdate[] = "Update `".$currentCourseDbNameGlu."accueil` set addedTool = 'NO' where lien ='../claroline/user/user.php';";
$sqlForUpdate[] = "Update `".$currentCourseDbNameGlu."accueil` set addedTool = 'NO' where lien ='../claroline/phpbb/index.php';";
$sqlForUpdate[] = "Update `".$currentCourseDbNameGlu."accueil` set addedTool = 'NO' where lien ='../claroline/exercice/exercice.php';";
$sqlForUpdate[] = "Update `".$currentCourseDbNameGlu."accueil` set addedTool = 'NO' where lien ='../claroline/group/group.php';";
$sqlForUpdate[] = "Update `".$currentCourseDbNameGlu."accueil` set addedTool = 'YES' where lien LIKE '../claroline/stat/index2.php3%';";
$sqlForUpdate[] = "Update `".$currentCourseDbNameGlu."accueil` set addedTool = 'NO' where lien ='../claroline/import/import.php?';";
$sqlForUpdate[] = "Update `".$currentCourseDbNameGlu."accueil` set addedTool = 'NO' where lien ='../claroline/external_module/external_module.php?';";
$sqlForUpdate[] = "Update `".$currentCourseDbNameGlu."accueil` set addedTool = 'NO' where lien ='../claroline/course_info/infocours.php?';";
$sqlForUpdate[] = "Update `".$currentCourseDbNameGlu."accueil` set addedTool = 'NO' where lien ='../claroline/chat/chat.php';";
$sqlForUpdate[] = "Update `".$currentCourseDbNameGlu."accueil` set addedTool = 'NO' where lien ='../claroline/course_description/';";


$sqlForUpdate[] = "Update `".$currentCourseDbNameGlu."accueil` set lien = '../claroline/course_description/index.php' where lien ='../claroline/course_description/';";

$sqlForUpdate[] = "Update `".$currentCourseDbNameGlu."accueil` set
`image` = REPLACE(`image`,'/images/','/img/'),
`address` = REPLACE(`address`,'/images/','/img/');";

$sqlForUpdate[] = "Update `".$currentCourseDbNameGlu."accueil` set
`image` = REPLACE(`image`,'/image/','/img/'),
`address` = REPLACE(`address`,'/image/','/img/');";

$sqlForUpdate[] = "Update `".$currentCourseDbNameGlu."accueil` set
`image` = REPLACE(`image`,'../../claroline/','../claroline/'),
`address` = REPLACE(`address`,'../../claroline/','../claroline/');";

$sqlForUpdate[] = "Update `".$currentCourseDbNameGlu."accueil` set
`address` = CONCAT('../claroline/',`address`)
WHERE NOT (LEFT(`address`,17) = '../claroline/img/');";

$sqlForUpdate[] = "Update `".$currentCourseDbNameGlu."accueil` set
`image` = CONCAT('../claroline/',`image`)
WHERE NOT (LEFT(`image`,17) = '../claroline/img/');";

$sqlForUpdate[] = "Update `".$currentCourseDbNameGlu."accueil` set
`image` = REPLACE(`image`,'../claroline/../claroline/','../claroline/'),
`address` = REPLACE(`address`,'../claroline/../claroline/','../claroline/');";
$sqlForUpdate[] = "Update `".$currentCourseDbNameGlu."accueil` set
`image` = REPLACE(`image`,'../claroline/../claroline/','../claroline/'),
`address` = REPLACE(`address`,'../claroline/../claroline/','../claroline/');";
$sqlForUpdate[] = "Update `".$currentCourseDbNameGlu."accueil` set
`image` = REPLACE(`image`,'../claroline/../claroline/','../claroline/'),
`address` = REPLACE(`address`,'../claroline/../claroline/','../claroline/');";
$sqlForUpdate[] = "Update `".$currentCourseDbNameGlu."accueil` set
`image` = REPLACE(`image`,'../claroline/../claroline/','../claroline/'),
`address` = REPLACE(`address`,'../claroline/../claroline/','../claroline/');";

$sqlForUpdate[] = "Update `".$currentCourseDbNameGlu."accueil` SET `image` = '../claroline/img/agenda.gif'			, address='../claroline/img/pastillegris.gif'	 WHERE lien LIKE '%agenda.ph%'";
$sqlForUpdate[] = "Update `".$currentCourseDbNameGlu."accueil` SET `image` = '../claroline/img/liens.gif'			, address='../claroline/img/pastillegris.gif'	 WHERE lien LIKE '%link.ph%'";
$sqlForUpdate[] = "Update `".$currentCourseDbNameGlu."accueil` SET `image` = '../claroline/img/documents.gif'		, address='../claroline/img/pastillegris.gif'	 WHERE lien LIKE '%document.ph%'";
$sqlForUpdate[] = "Update `".$currentCourseDbNameGlu."accueil` SET `image` = '../claroline/img/works.gif'			, address='../claroline/img/pastillegris.gif'	 WHERE lien LIKE '%work.ph%'";
$sqlForUpdate[] = "Update `".$currentCourseDbNameGlu."accueil` SET `image` = '../claroline/img/valves.gif'			, address='../claroline/img/pastillegris.gif'	 WHERE lien LIKE '%announcements.ph%'";
$sqlForUpdate[] = "Update `".$currentCourseDbNameGlu."accueil` SET `image` = '../claroline/img/membres.gif'			, address='../claroline/img/pastillegris.gif'	 WHERE lien LIKE '%user.ph%'";
$sqlForUpdate[] = "Update `".$currentCourseDbNameGlu."accueil` SET `image` = '../claroline/img/forum.gif'			, address='../claroline/img/pastillegris.gif'	 WHERE lien LIKE '%phpbb/index.ph%'";
$sqlForUpdate[] = "Update `".$currentCourseDbNameGlu."accueil` SET `image` = '../claroline/img/quiz.gif'			, address='../claroline/img/pastillegris.gif'	 WHERE lien LIKE '%exercice.ph%'";
$sqlForUpdate[] = "Update `".$currentCourseDbNameGlu."accueil` SET `image` = '../claroline/img/group.gif'			, address='../claroline/img/pastillegris.gif'	 WHERE lien LIKE '%group.ph%'";
$sqlForUpdate[] = "Update `".$currentCourseDbNameGlu."accueil` SET `image` = '../claroline/img/info.gif'			, address='../claroline/img/pastillegris.gif'	 WHERE lien LIKE '%course_description/%'";
$sqlForUpdate[] = "Update `".$currentCourseDbNameGlu."accueil` SET `image` = '../claroline/img/videos.gif'			, address='../claroline/img/pastillegris.gif'	 WHERE lien LIKE '%video/video.php%'";
$sqlForUpdate[] = "Update `".$currentCourseDbNameGlu."accueil` SET `image` = '../claroline/img/forum.gif'			, address='../claroline/img/pastillegris.gif'	 WHERE lien LIKE '%chat.ph%'";
$sqlForUpdate[] = "Update `".$currentCourseDbNameGlu."accueil` SET `image` = '../claroline/img/statistiques.gif'	, address='../claroline/img/pastillegris.gif'	 WHERE lien LIKE '%stat/index2.php%'";
$sqlForUpdate[] = "Update `".$currentCourseDbNameGlu."accueil` SET `image` = '../claroline/img/statistiques.gif'	, address='../claroline/img/pastillegris.gif'	 WHERE lien LIKE '%courseLog.ph%'";
$sqlForUpdate[] = "Update `".$currentCourseDbNameGlu."accueil` SET `image` = '../claroline/img/page.gif'			, address='../claroline/img/pastillegris.gif'	 WHERE lien LIKE '%import.ph%'";
$sqlForUpdate[] = "Update `".$currentCourseDbNameGlu."accueil` SET `image` = '../claroline/img/npage.gif'			, address='../claroline/img/pastillegris.gif'	 WHERE lien LIKE '%/external_module.ph%'";
$sqlForUpdate[] = "Update `".$currentCourseDbNameGlu."accueil` SET `image` = '../claroline/img/referencement.gif'	, address='../claroline/img/pastillegris.gif'	 WHERE lien LIKE '../claroline/course_info/infocours.ph%'";

$sqlForUpdate[] = "
Update `".$currentCourseDbNameGlu."accueil`
	SET
		`image` 	= '../claroline/img/external.gif'			,
		`address`	= '../claroline/img/external_inactive.gif'
	WHERE
		`lien` NOT LIKE '%work.ph%'
		and (
			`image` LIKE '%travaux.pn%'
			OR
			address LIKE '%travaux.pn%')";

}

/**
 * RENAME AND TRY TO CREATE TRACKING TABLE
 *
 * track_e_access
 * track_e_downloads
 * track_e_exercices
 * track_e_uploads *
*/

{
 
$sqlForUpdate[] = "### TRACKING ###`";

$sqlForUpdate[] = "# Structure of table `track_e_access`";
$sqlForUpdate[] = "CREATE TABLE IF NOT EXISTS `".$currentCourseDbNameGlu."track_e_access` (
  `access_id` int(11) NOT NULL auto_increment,
  `access_user_id` int(10) default NULL,
  `access_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `access_tool` varchar(30) default NULL,
  PRIMARY KEY  (`access_id`)
) TYPE=MyISAM COMMENT='Record informations about access to course or tools';";

$sqlForUpdate[] = "# Structure of table `track_e_downloads`";
$sqlForUpdate[] = "CREATE TABLE IF NOT EXISTS `".$currentCourseDbNameGlu."track_e_downloads` (
  `down_id` int(11) NOT NULL auto_increment,
  `down_user_id` int(10) default NULL,
  `down_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `down_doc_path` varchar(255) NOT NULL default '0',
  PRIMARY KEY  (`down_id`)
) TYPE=MyISAM COMMENT='Record informations about downloads';";

$sqlForUpdate[] = "# Structure of table `track_e_exercices`";
$sqlForUpdate[] = "CREATE TABLE IF NOT EXISTS `".$currentCourseDbNameGlu."track_e_exercices` (
  `exe_id` int(11) NOT NULL auto_increment,
  `exe_user_id` int(10) default NULL,
  `exe_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `exe_exo_id` tinyint(4) NOT NULL default '0',
  `exe_result` mediumint(8) NOT NULL default '0',
  `exe_weighting` mediumint(8) NOT NULL default '0',
  PRIMARY KEY  (`exe_id`)
) TYPE=MyISAM COMMENT='Record informations about exercices';";

$sqlForUpdate[] = "# Structure of table track_e_uploads ";
$sqlForUpdate[] = "CREATE TABLE IF NOT EXISTS `".$currentCourseDbNameGlu."track_e_uploads` (
  `upload_id` int(11) NOT NULL auto_increment,
  `upload_user_id` int(10) default NULL,
  `upload_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `upload_work_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`upload_id`)
) TYPE=MyISAM COMMENT='Record some more informations about uploaded works';";

//

$sqlForUpdate[] = "# Import Tracking in new tracking tables";
$sqlForUpdate[] = "INSERT IGNORE INTO `".$currentCourseDbNameGlu."track_e_access`
 (access_id, access_user_id, access_date, access_tool)
 SELECT
 	access_id, access_user_id, access_date, access_tool FROM `".$statsDbName."`.`track_e_access`
	WHERE access_cours_code = @currentCourseCode";

$sqlForUpdate[] = "INSERT IGNORE INTO `".$currentCourseDbNameGlu."track_e_exercices`
 (exe_id, exe_user_id, exe_date, exe_exo_id, exe_result, exe_weighting)
 SELECT
 	exe_id, exe_user_id, exe_date, exe_exo_id, exe_result, exe_weighting FROM `".$statsDbName."`.`track_e_exercices`
	WHERE exe_cours_id = @currentCourseCode";
	
$sqlForUpdate[] = "INSERT IGNORE INTO `".$currentCourseDbNameGlu."track_e_downloads`
 (down_id, down_user_id, down_date, down_doc_path)
 SELECT
 	down_id, down_user_id, down_date, down_doc_path FROM `".$statsDbName."`.`track_e_downloads`
	WHERE down_cours_id = @currentCourseCode";	

$sqlForUpdate[] = "INSERT IGNORE INTO `".$currentCourseDbNameGlu."track_e_uploads`
 (upload_id, upload_user_id, upload_date, upload_work_id)
 SELECT
 	upload_id, upload_user_id, upload_date, upload_work_id FROM `".$statsDbName."`.`track_e_uploads`
	WHERE upload_cours_id = @currentCourseCode";


}

/**
 * TRY TO CREATE USER INFO TABLE
 *
 * userinfo_content
 * userinfo_def
 *
 */

{

$sqlForUpdate[] = "# Structure of table `userinfo_content`";
$sqlForUpdate[] = "CREATE TABLE IF NOT EXISTS `".$currentCourseDbNameGlu."userinfo_content` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `user_id` mediumint(8) unsigned NOT NULL default '0',
  `def_id` int(10) unsigned NOT NULL default '0',
  `ed_ip` varchar(39) default NULL,
  `ed_date` datetime default NULL,
  `content` text,
  PRIMARY KEY  (`id`),
  KEY `user_id` (`user_id`)
) TYPE=MyISAM COMMENT='content of users information - organisation based on userinfo';";

$sqlForUpdate[] = "# Structure of table `userinfo_def`";
$sqlForUpdate[] = "CREATE TABLE IF NOT EXISTS `".$currentCourseDbNameGlu."userinfo_def` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `title` varchar(80) NOT NULL default '',
  `comment` varchar(160) default NULL,
  `nbLine` int(10) unsigned NOT NULL default '5',
  `rank` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM COMMENT='categories definition for user information of a course';";

$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."userinfo_content` ADD `id` int(10) unsigned NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."userinfo_content` ADD `user_id` mediumint(8) unsigned NOT NULL default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."userinfo_content` ADD `def_id` int(10) unsigned NOT NULL default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."userinfo_content` ADD `ed_ip` varchar(39) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."userinfo_content` ADD `ed_date` datetime default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."userinfo_content` ADD `content` text;";

$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."userinfo_def` ADD `id` int(10) unsigned NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."userinfo_def` ADD `title` varchar(80) NOT NULL default '';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."userinfo_def` ADD `comment` varchar(160) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."userinfo_def` ADD `nbLine` int(10) unsigned NOT NULL default '5';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."userinfo_def` ADD `rank` tinyint(3) unsigned NOT NULL default '0';";

// missing fields

$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."userinfo_content` CHANGE `id`		`id` int(10) unsigned NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."userinfo_content` CHANGE `user_id`	`user_id` mediumint(8) unsigned NOT NULL default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."userinfo_content` CHANGE `def_id`	`def_id` int(10) unsigned NOT NULL default '0';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."userinfo_content` CHANGE `ed_ip`	`ed_ip` varchar(39) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."userinfo_content` CHANGE `ed_date`	`ed_date` datetime default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."userinfo_content` CHANGE `content`	`content` text;";
 
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."userinfo_def` CHANGE `id`		`id` int(10) unsigned NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."userinfo_def` CHANGE `title`	`title` varchar(80) NOT NULL default '';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."userinfo_def` CHANGE `comment`	`comment` varchar(160) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."userinfo_def` CHANGE `nbLine`	`nbLine` int(10) unsigned NOT NULL default '5';";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."userinfo_def` CHANGE `rank`		`rank` tinyint(3) unsigned NOT NULL default '0';";

}

/**
 * TRY TO CREATE WORK TABLE
 *
 * work_student
 *
 */

{
 
$sqlForUpdate[] = "# Structure of table `work_student`";
$sqlForUpdate[] = "CREATE TABLE IF NOT EXISTS `".$currentCourseDbNameGlu."work_student` (
  `work_id` int(11) NOT NULL default '0',
  `uname` varchar(30) NOT NULL default '',
  PRIMARY KEY  (`work_id`,`uname`)
) TYPE=MyISAM;";

$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."work` CHANGE `id`			`id` int(11) NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."work` CHANGE `url`			`url` varchar(200) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."work` CHANGE `titre`		`titre` varchar(200) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."work` CHANGE `description`	`description` varchar(250) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."work` CHANGE `auteurs`		`auteurs` varchar(200) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."work` CHANGE `active`		`active` tinyint(1) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE TABLE `".$currentCourseDbNameGlu."work` CHANGE `accepted`		`accepted` tinyint(1) default NULL;";

}

/**** DEPRECATED by MLA

$sqlForUpdate[] = "### LINKS ###`";

$sqlForUpdate[] = "# Structure of table `liens`";
$sqlForUpdate[] = "CREATE TABLE IF NOT EXISTS `".$currentCourseDbNameGlu."liens` (
  `id` int(11) NOT NULL auto_increment,
  `url` varchar(150) default NULL,
  `titre` varchar(150) default NULL,
  `description` text,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;";

*/

//////////////////////// COMMENTS //////////////////////////////

$sqlForUpdate[] = "# Commentaires / Comments";
$sqlForUpdate[] = " ALTER TABLE `".$currentCourseDbNameGlu."accueil` COMMENT='list  of tools for the course';";
$sqlForUpdate[] = " ALTER TABLE `".$currentCourseDbNameGlu."agenda` COMMENT='data for calandar';";
$sqlForUpdate[] = " ALTER TABLE `".$currentCourseDbNameGlu."course_description` COMMENT='data fo course description';";

$sqlForUpdate[] = " ALTER TABLE `".$currentCourseDbNameGlu."exercice_question` COMMENT='data for exercice tool';";
$sqlForUpdate[] = " ALTER TABLE `".$currentCourseDbNameGlu."exercices` COMMENT='data for exercice tool';";
$sqlForUpdate[] = " ALTER TABLE `".$currentCourseDbNameGlu."questions` COMMENT='data for exercice tool';";
$sqlForUpdate[] = " ALTER TABLE `".$currentCourseDbNameGlu."ranks` COMMENT='data for exercice tool';";
$sqlForUpdate[] = " ALTER TABLE `".$currentCourseDbNameGlu."reponses` COMMENT='data for exercice tool';";

$sqlForUpdate[] = " ALTER TABLE `".$currentCourseDbNameGlu."group_properties` COMMENT='data for groups managing';";
$sqlForUpdate[] = " ALTER TABLE `".$currentCourseDbNameGlu."student_group` COMMENT='data for groups managing';";
$sqlForUpdate[] = " ALTER TABLE `".$currentCourseDbNameGlu."user_group` COMMENT='data for groups managing';";

$sqlForUpdate[] = " ALTER TABLE `".$currentCourseDbNameGlu."document` COMMENT='data for document tool';";

$sqlForUpdate[] = " ALTER TABLE `".$currentCourseDbNameGlu."introduction` COMMENT='data for introduction in some tool';";
$sqlForUpdate[] = " ALTER TABLE `".$currentCourseDbNameGlu."liens` COMMENT='data for links tool';";
$sqlForUpdate[] = " ALTER TABLE `".$currentCourseDbNameGlu."work` COMMENT='data for student papers tool';";

$sqlForUpdate[] = " ALTER TABLE `".$currentCourseDbNameGlu."bb_access` COMMENT='data for forum tool';";
$sqlForUpdate[] = " ALTER TABLE `".$currentCourseDbNameGlu."bb_banlist` COMMENT='data for forum tool';";
$sqlForUpdate[] = " ALTER TABLE `".$currentCourseDbNameGlu."bb_categories` COMMENT='data for forum tool';";
$sqlForUpdate[] = " ALTER TABLE `".$currentCourseDbNameGlu."bb_config` COMMENT='data for forum tool';";
$sqlForUpdate[] = " ALTER TABLE `".$currentCourseDbNameGlu."bb_disallow` COMMENT='data for forum tool';";
$sqlForUpdate[] = " ALTER TABLE `".$currentCourseDbNameGlu."bb_forum_access` COMMENT='data for forum tool';";
$sqlForUpdate[] = " ALTER TABLE `".$currentCourseDbNameGlu."bb_forum_mods` COMMENT='data for forum tool';";
$sqlForUpdate[] = " ALTER TABLE `".$currentCourseDbNameGlu."bb_forums` COMMENT='data for forum tool';";
$sqlForUpdate[] = " ALTER TABLE `".$currentCourseDbNameGlu."bb_headermetafooter` COMMENT='data for forum tool';";
$sqlForUpdate[] = " ALTER TABLE `".$currentCourseDbNameGlu."bb_pages` COMMENT='data for forum tool';";
$sqlForUpdate[] = " ALTER TABLE `".$currentCourseDbNameGlu."bb_posts` COMMENT='data for forum tool';";
$sqlForUpdate[] = " ALTER TABLE `".$currentCourseDbNameGlu."bb_posts_text` COMMENT='data for forum tool';";
$sqlForUpdate[] = " ALTER TABLE `".$currentCourseDbNameGlu."bb_priv_msgs` COMMENT='data for forum tool';";
$sqlForUpdate[] = " ALTER TABLE `".$currentCourseDbNameGlu."bb_sessions` COMMENT='data for forum tool';";
$sqlForUpdate[] = " ALTER TABLE `".$currentCourseDbNameGlu."bb_themes` COMMENT='data for forum tool';";
$sqlForUpdate[] = " ALTER TABLE `".$currentCourseDbNameGlu."bb_topics` COMMENT='data for forum tool';";
$sqlForUpdate[] = " ALTER TABLE `".$currentCourseDbNameGlu."bb_users` COMMENT='data for forum tool';";
$sqlForUpdate[] = " ALTER TABLE `".$currentCourseDbNameGlu."bb_whosonline` COMMENT='data for forum tool';";
$sqlForUpdate[] = " ALTER TABLE `".$currentCourseDbNameGlu."bb_words` COMMENT='data for forum tool';";

/**
 * $Log$
 * Revision 1.6  2004/07/01 10:15:20  mathieu
 * remove char and line return after ; of query
 *
 * Revision 1.5  2004/06/24 10:14:10  mathieu
 * replace sql with function to fill picture_name with id, previously implicit
 *
 * Revision 1.4  2004/06/04 17:01:41  mathieu
 * rename field agenda to chat
 *
 * Revision 1.3  2004/06/04 12:45:11  mathieu
 * default value of picture name is '' not null.
 *
 * Revision 1.2  2004/06/03 13:44:43  moosh
 * Implement the the tool <select> box in the claroline banner
 *
 * Revision 1.1.1.1  2004/06/02 07:49:03  moosh
 * startnew 
 *
 * Revision 1.19  2004/05/27 05:52:52  seb
 * - added missing enum value for field lesson_status of table lp_user_module_progress
 *
 * Revision 1.18  2004/05/26 13:47:31  mathieu
 * upgrade bb_forum:
 * - reorder query sql
 * - bug: rename catagories to bb_categories
 * - add create table bb_rel_notify_
 *
 * Revision 1.17  2004/05/26 08:56:29  mathieu
 * - add field title to table annoucement
 * - build picture name of table quiz_question
 *
 * Revision 1.16  2004/05/25 11:23:28  mathieu
 * use tool_id instead of claro_label
 *
 * Revision 1.15  2004/05/21 13:25:18  mathieu
 * Upgrade to 1.5: SQL to upgrade tables of a course
 *
 * Revision 1.14  2003/09/11 13:43:45  moosh
 * remove upgrade of a deleted tool
 *
 * Revision 1.13  2003/06/30 12:26:02  moosh
 * - add sql to  try change  .png in an available and  corresponding .gif
 *
 * Revision 1.12  2003/06/29 10:11:05  moosh
 * replace image png  by gif
 *
 * Revision 1.11  2003/06/27 07:30:00  moosh
 * - correct the  lines wich make the bug of ../claroline/../claroline
 *
 * Revision 1.10  2003/06/27 07:24:16  moosh
 * - correct path to image (if the  problem  is  to big, tha can be recorrected)
 * - remove old log
 *
 * Revision 1.9  2003/06/23 10:56:11  moosh
 * now  upgrade can  upgrade  system in singleDb
 *
 * Revision 1.8  2003/06/06 13:30:15  thomas
 * correct path to image
 *
 * Revision 1.6  2003/06/06 13:02:33  moosh
 * correct path  for  icon in  homepage
 *
 * Revision 1.5  2003/06/06 11:51:46  moosh
 * move  announcement sql
 *
 * Revision 1.4  2003/06/05 19:51:17  moosh
 * correct  many sql error
 *
 * Revision 1.3  2003/06/04 11:01:13  moosh
 * new upgrade system
 *
 * Revision 1.2  2003/06/02 11:17:57  moosh
 * updated  for  1.4.0 DATABASE
 *
 * Revision 1.1  2003/06/02 09:28:05  moosh
 * tools for  maintenance of the system
 */
?>
