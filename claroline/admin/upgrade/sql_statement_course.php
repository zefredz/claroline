<?php // $Id$

$sqlForUpdate[] = "### Try to upgrade course tables (rename, create, alter, update)";

/**
 * Drop deprecated php_bb tables
 */

$sqlForUpdate[] = "DROP TABLE IF EXISTS `".$currentCourseDbNameGlu."bb_access`"; 
$sqlForUpdate[] = "DROP TABLE IF EXISTS `".$currentCourseDbNameGlu."bb_banlist`"; 
$sqlForUpdate[] = "DROP TABLE IF EXISTS `".$currentCourseDbNameGlu."bb_config`"; 
$sqlForUpdate[] = "DROP TABLE IF EXISTS `".$currentCourseDbNameGlu."bb_disallow`"; 
$sqlForUpdate[] = "DROP TABLE IF EXISTS `".$currentCourseDbNameGlu."bb_forum_access`"; 
$sqlForUpdate[] = "DROP TABLE IF EXISTS `".$currentCourseDbNameGlu."bb_forum_mods`"; 
$sqlForUpdate[] = "DROP TABLE IF EXISTS `".$currentCourseDbNameGlu."bb_headermetafooter`"; 
$sqlForUpdate[] = "DROP TABLE IF EXISTS `".$currentCourseDbNameGlu."bb_ranks`"; 
$sqlForUpdate[] = "DROP TABLE IF EXISTS `".$currentCourseDbNameGlu."bb_sessions`"; 
$sqlForUpdate[] = "DROP TABLE IF EXISTS `".$currentCourseDbNameGlu."bb_themes`"; 
$sqlForUpdate[] = "DROP TABLE IF EXISTS `".$currentCourseDbNameGlu."bb_words`"; 
 
/**
 * Drop deprecated pages tables
 */

$sqlForUpdate[] = "DROP TABLE IF EXISTS `".$currentCourseDbNameGlu."pages`"; 

/**
 * Upgrade quizz tables
 */

$sqlForUpdate[] = "ALTER IGNORE TABLE `".$currentCourseDbNameGlu."quiz_question` CHANGE `picture_name` `attached_file` varchar(50) default ''";
$sqlForUpdate[] = "ALTER IGNORE TABLE `".$currentCourseDbNameGlu."quiz_test` ADD `max_time` smallint(5) unsigned NOT NULL default '0'";
$sqlForUpdate[] = "ALTER IGNORE TABLE `".$currentCourseDbNameGlu."quiz_test` ADD `max_attempt` tinyint(3) unsigned NOT NULL default '0'";
$sqlForUpdate[] = "ALTER IGNORE TABLE `".$currentCourseDbNameGlu."quiz_test` ADD `show_answer` enum('ALWAYS','NEVER','LASTTRY') NOT NULL default 'ALWAYS'";
$sqlForUpdate[] = "ALTER IGNORE TABLE `".$currentCourseDbNameGlu."quiz_test` ADD `anonymous_attempts` enum('YES','NO') NOT NULL default 'YES'";
$sqlForUpdate[] = "ALTER IGNORE TABLE `".$currentCourseDbNameGlu."quiz_test` ADD `start_date` datetime NOT NULL default '0000-00-00 00:00:00'";
$sqlForUpdate[] = "ALTER IGNORE TABLE `".$currentCourseDbNameGlu."quiz_test` ADD `end_date` datetime NOT NULL default '0000-00-00 00:00:00'";

/**
 * Upgrade assigments tables
 */

$sqlForUpdate[] = "CREATE TABLE IF NOT EXISTS `".$currentCourseDbNameGlu."wrk_assignment` (
  `id` int(11) NOT NULL auto_increment,
  `title` varchar(200) NOT NULL default '',
  `description` text NOT NULL,
  `visibility` enum('VISIBLE','INVISIBLE') NOT NULL default 'VISIBLE',
  `def_submission_visibility` enum('VISIBLE','INVISIBLE') NOT NULL default 'VISIBLE',
  `assignment_type` enum('INDIVIDUAL','GROUP') NOT NULL default 'INDIVIDUAL',
  `authorized_content` enum('TEXT','FILE','TEXTFILE') NOT NULL default 'FILE',
  `allow_late_upload` enum('YES','NO') NOT NULL default 'YES',
  `start_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `end_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `prefill_text` text NOT NULL,
  `prefill_doc_path` varchar(200) NOT NULL default '',
  `prefill_submit` enum('ENDDATE','AFTERPOST') NOT NULL default 'ENDDATE',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM";

$sqlForUpdate[] = "CREATE TABLE IF NOT EXISTS `".$currentCourseDbNameGlu."wrk_submission` (
  `id` int(11) NOT NULL auto_increment,
  `assignment_id` int(11) default NULL,
  `parent_id` int(11) default NULL,
  `user_id` int(11) default NULL,
  `group_id` int(11) default NULL,
  `title` varchar(200) NOT NULL default '',
  `visibility` enum('VISIBLE','INVISIBLE') default 'VISIBLE',
  `creation_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `last_edit_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `authors` varchar(200) NOT NULL default '',
  `submitted_text` text NOT NULL,
  `submitted_doc_path` varchar(200) NOT NULL default '',
  `private_feedback` text,
  `original_id` int(11) default NULL,
  `score` smallint(3) default NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM";

/**
 * Create FAKE assignments
 */

$sqlForUpdate[] = "INSERT INTO `".$currentCourseDbNameGlu."wrk_assignment` 
SET `id` = 1,
    `title` = 'Assignments', 
    `description`= '', 
    `visibility` = 'VISIBLE', 
    `def_submission_visibility` = 'VISIBLE',
    `assignment_type` = 'INDIVIDUAL',
    `authorized_content` = 'FILE',
    `allow_late_upload` = 'NO',
    `start_date` = '0000-00-00 00:00:00',
    `end_date` = '0000-00-00 00:00:00',
    `prefill_text` = '',
    `prefill_doc_path` = '',
    `prefill_submit` = 'ENDDATE' ";

/**
 * Upgrade tracking
 */

$sqlForUpdate[] = "RENAME TABLE `".$currentCourseDbNameGlu."track_e_access` TO `".$currentCourseDbNameGlu."track_e_access_15`";

$sqlForUpdate[] = "CREATE TABLE IF NOT EXISTS `track_e_access` (
  `access_id` int(11) NOT NULL auto_increment,
  `access_user_id` int(10) default NULL,
  `access_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `access_tid` int(10) default NULL,
  `access_tlabel` varchar(8) default NULL,
  PRIMARY KEY  (`access_id`)
) TYPE=MyISAM COMMENT='Record informations about access to course or tools'";
        

/**
 * Upgrade course
 */

$sqlForUpdate[] = "INSERT IGNORE INTO `".$currentCourseDbNameGlu."wrk_submissions`
 (assignment_id,title,visibility,authors,submitted_text,submitted_doc_path)
 SELECT 1, titre, IF(accepted,'VISIBLE','INVISIBLE'), auteurs, description, url 
    FROM `".$currentCourseDbNameGlu."`.`assignment_doc`";  

/**
 * Drop deprecated pages assignment_doc
 */

$sqlForUpdate[] = "DROP TABLE IF EXISTS `".$currentCourseDbNameGlu."assignment_doc`"; 

?>
