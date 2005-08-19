<?php // $Id$
/**
 * CLAROLINE
 *
 * Sql query to update courses databases
 *
 * @version  1.6 $Revision$
 * 
 * @copyright (c) 2001-2005 Universite catholique de Louvain (UCL)
 * 
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @see http://www.claroline.net/wiki/index.php/Upgrade_claroline_1.6
 *
 * @package UPGRADE
 * 
 * @author Claro Team <cvs@claroline.net>
 * @author Mathieu Laurent   <mla@claroline.net>
 * @author Christophe Gesché <moosh@claroline.net>
 *
 */

$sqlkeys['user'] = "int(11) default NULL";

/*===========================================================================
 Upgrade to claroline 1.6
 ===========================================================================*/

function query_to_upgrade_course_database_to_16 ()
{

    global $currentCourseDbNameGlu;

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
    $sqlForUpdate[] = "ALTER IGNORE TABLE `".$currentCourseDbNameGlu."quiz_question` CHANGE `ponderation` `ponderation` float unsigned default NULL";
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
      `user_id` ".$sqlkeys['user'].",
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
        `description`= '" . mysql_escape_string($work_intro) . "', 
        `visibility` = 'VISIBLE', 
        `def_submission_visibility` = 'VISIBLE',
        `assignment_type` = 'INDIVIDUAL',
        `authorized_content` = 'FILE',
        `allow_late_upload` = 'NO',
        `start_date` = '" . $currentCourseCreationDate . "',
        `end_date` = DATE_ADD(NOW(),INTERVAL 1 YEAR),
        `prefill_text` = '',
        `prefill_doc_path` = '',
        `prefill_submit` = 'ENDDATE' ";
    
    /**
     * Upgrade assignments
     */
    
    $sqlForUpdate[] = "INSERT IGNORE INTO `".$currentCourseDbNameGlu."wrk_submission`
     (assignment_id,user_id,title,visibility,authors,submitted_text,submitted_doc_path)
     SELECT 1, '". $teacher_uid ."', titre, IF(accepted,'VISIBLE','INVISIBLE'), auteurs, description, url 
        FROM `".$currentCourseDbNameGlu."assignment_doc`";  
    
    $sqlForUpdate[] = "UPDATE `".$currentCourseDbNameGlu."wrk_submission` 
                       SET submitted_doc_path = REPLACE (`submitted_doc_path` ,'work/','')";
    
    /**
     * Upgrade groups
     */
    
    $sqlForUpdate[] = "RENAME TABLE `".$currentCourseDbNameGlu."properties` TO `".$currentCourseDbNameGlu."property`";
    
    
    /**
     * Drop deprecated assignment_doc
     */
    
    // $sqlForUpdate[] = "DROP TABLE IF EXISTS `".$currentCourseDbNameGlu."assignment_doc`"; 
    
    /**
     * Upgrade tracking
     */
    
    $sqlForUpdate[] = "RENAME TABLE `".$currentCourseDbNameGlu."track_e_access` TO `".$currentCourseDbNameGlu."track_e_access_15`";
    
    $sqlForUpdate[] = "CREATE TABLE IF NOT EXISTS `".$currentCourseDbNameGlu."track_e_access` (
      `access_id` int(11) NOT NULL auto_increment,
      `access_user_id` ".$sqlkeys['user'].",
      `access_date` datetime NOT NULL default '0000-00-00 00:00:00',
      `access_tid` int(10) default NULL,
      `access_tlabel` varchar(8) default NULL,
      PRIMARY KEY  (`access_id`)
    ) TYPE=MyISAM COMMENT='Record informations about access to course or tools'";
    
    $sqlForUpdate[] = "ALTER IGNORE TABLE `".$currentCourseDbNameGlu."quiz_answer` CHANGE `ponderation` `ponderation` float default NULL";
    
    $sqlForUpdate[] = "ALTER IGNORE TABLE `".$currentCourseDbNameGlu."track_e_exercices` ADD `exe_time`  mediumint(8) NOT NULL default '0'";
    $sqlForUpdate[] = "ALTER IGNORE TABLE `".$currentCourseDbNameGlu."track_e_exercices` CHANGE `exe_result` `exe_result` float NOT NULL default '0'";
    $sqlForUpdate[] = "ALTER IGNORE TABLE `".$currentCourseDbNameGlu."track_e_exercices` CHANGE `exe_weighting` `exe_weighting` float NOT NULL default '0'";

    return $sqlForUpdate;

}

// TODO: code from 1.6 adapt it to work in 1.7

function upgrade_assignments_to_16()
{
    global $currentCourseDbNameGlu, $currentCourseIDsys, $currentCourseCode;
    global $_uid;

    $tbl_mdb_names = claro_sql_get_main_tbl();
    $tbl_course = $tbl_mdb_names['course'];
    $tbl_rel_course_user = $tbl_mdb_names['rel_course_user'];
    $tbl_course_tool = $tbl_mdb_names['tool'];
    
    // get work intro
    $sql_work_intro = "SELECT ti.texte_intro
                        FROM `" . $currentCourseDbNameGlu . "tool_list` tl,
                             `" . $currentCourseDbNameGlu . "tool_intro` ti,
                             `" . $tbl_course_tool . "` ct
                        WHERE ti.id = tl.id
                            AND tl.tool_id =  ct.id
                            AND ct.claro_label = 'CLWRK___'";

    $work_intro = claro_sql_query_get_single_value($sql_work_intro);

    if ( $work_intro === FALSE ) $work_intro = '';

    // get course manager of the course

    $sql_get_id_of_one_teacher = "SELECT `user_id` `uid` " .
                                 " FROM `". $tbl_rel_course_user . "` " .
                                 " WHERE `code_cours` = '".$currentCourseIDsys."' LIMIT 1";
    
    $res_id_of_one_teacher = claro_sql_query($sql_get_id_of_one_teacher);
    
    $teacher = claro_sql_fetch_all($res_id_of_one_teacher);

    $teacher_uid = $teacher[0]['uid'];

    // if no course manager, you are enrolled in as

    if ( !is_numeric($teacher_uid) )
    {
        $teacher_uid = $_uid;

        $sql_set_teacher = "INSERT INTO `". $tbl_rel_course_user . "`  
                            SET `user_id` = '" . $teacher_uid . "'
                                 , `code_cours` = '" . $currentCourseIDsys . "'
                                 , `role` = 'Course missing manager';";
        claro_sql_query($sql_set_teacher);

        // TODO
        // $errorMsgs .= '<p class="error">Course '.$currentCourseCode.' has no teacher, you are enrolled in as course manager. </p>' . "\n";
    }

    return true;

}

/*===========================================================================
 Upgrade to claroline 1.7
 ===========================================================================*/

function query_to_upgrade_course_database_to_17()
{

    global $currentCourseDbNameGlu;

    // add visibility fields in announcement, calendar and course_description
    
    $sqlForUpdate[] = "ALTER IGNORE TABLE `" . $currentCourseDbNameGlu."announcement` ADD `visibility` enum('SHOW','HIDE') NOT NULL default 'SHOW'";
    $sqlForUpdate[] = "ALTER IGNORE TABLE `" . $currentCourseDbNameGlu."calendar_event` ADD `visibility` enum('SHOW','HIDE') NOT NULL default 'SHOW'";
    $sqlForUpdate[] = "ALTER IGNORE TABLE `" . $currentCourseDbNameGlu."course_description` ADD `visibility` enum('SHOW','HIDE') NOT NULL default 'SHOW'";
    
    // tracking improvement
    
    $sqlForUpdate[] = "CREATE TABLE IF NOT EXISTS `" . $currentCourseDbNameGlu . "track_e_exe_details` (
                        `id` int(11) NOT NULL auto_increment,
                        `exercise_track_id` int(11) NOT NULL default '0',
                        `question_id` int(11) NOT NULL default '0',
                        `result` float NOT NULL default '0',
                        PRIMARY KEY  (`id`)
                        ) TYPE=MyISAM COMMENT='Record answers of students in exercices'";
    
    $sqlForUpdate[] = "CREATE TABLE IF NOT EXISTS `" . $currentCourseDbNameGlu . "track_e_exe_answers` (
                        `id` int(11) NOT NULL auto_increment,
                        `details_id` int(11) NOT NULL default '0',
                        `answer` text NOT NULL,
                        PRIMARY KEY  (`id`)
                        ) TYPE=MyISAM COMMENT=''";
    
    // linker
    
    $sqlForUpdate[] = "CREATE TABLE IF NOT EXISTS `". $currentCourseDbNameGlu ."lnk_links` (
                        `id` int(11) NOT NULL auto_increment,
                        `src_id` int(11) NOT NULL default '0',
                        `dest_id` int(11) NOT NULL default '0',
                        `creation_time` timestamp(14) NOT NULL,
                        PRIMARY KEY  (`id`)
                        ) TYPE=MyISAM";
                   
    $sqlForUpdate[] = "CREATE TABLE IF NOT EXISTS `". $currentCourseDbNameGlu ."lnk_resources` (
                        `id` int(11) NOT NULL auto_increment,
                        `crl` text NOT NULL,
                        `title` text NOT NULL,
                        PRIMARY KEY  (`id`)
                        ) TYPE=MyISAM";
    
    // wiki
    
    $sqlForUpdate[] = "CREATE TABLE IF NOT EXISTS `". $currentCourseDbNameGlu ."wiki_properties`(
                        `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                        `title` VARCHAR(255) NOT NULL DEFAULT '',
                        `description` TEXT NULL,
                        `group_id` INT(11) NOT NULL DEFAULT 0,
                        PRIMARY KEY(`id`)
                        )";
    
    $sqlForUpdate[] = "CREATE TABLE IF NOT EXISTS `". $currentCourseDbNameGlu ."wiki_acls` (
                        `wiki_id` INT(11) UNSIGNED NOT NULL,
                        `flag` VARCHAR(255) NOT NULL,
                        `value` ENUM('false','true') NOT NULL DEFAULT 'false'
                        )";
    
    $sqlForUpdate[] = "CREATE TABLE IF NOT EXISTS `". $currentCourseDbNameGlu ."wiki_pages` (
                        `id` int(11) unsigned NOT NULL auto_increment,
                        `wiki_id` int(11) unsigned NOT NULL default '0',
                        `owner_id` int(11) unsigned NOT NULL default '0',
                        `title` varchar(255) NOT NULL default '',
                        `ctime` timestamp NOT NULL default '0000-00-00 00:00:00',
                        `last_version` int(11) unsigned NOT NULL default '0',
                        `last_mtime` timestamp NOT NULL default '0000-00-00 00:00:00',
                        PRIMARY KEY  (`id`) )" ;
    
    $sqlForUpdate[] = "CREATE TABLE IF NOT EXISTS `". $currentCourseDbNameGlu ."wiki_pages_content` (
                        `id` int(11) unsigned NOT NULL auto_increment,
                        `pid` int(11) unsigned NOT NULL default '0',
                        `editor_id` int(11) NOT NULL default '0',
                        `mtime` timestamp NOT NULL default '0000-00-00 00:00:00',
                        `content` text NOT NULL,
                        PRIMARY KEY  (`id`) )";
    
    // groups of forums
    
    $sqlForUpdate[] = "ALTER IGNORE TABLE `" . $currentCourseDbNameGlu."bb_forums` ADD group_id int(11) default NULL";
    $sqlForUpdate[] = "ALTER IGNORE TABLE `" . $currentCourseDbNameGlu."bb_forums` DROP COLUMN md5";
    $sqlForUpdate[] = "ALTER IGNORE TABLE `" . $currentCourseDbNameGlu."group_team` DROP COLUMN forumId";

    return $sqlForUpdate;

}

?>
