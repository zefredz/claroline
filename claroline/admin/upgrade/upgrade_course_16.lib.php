<?php // $Id$
/**
 * CLAROLINE
 *
 * Function to upgrade course tool 1.5 to 1.6
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

function upgrade_to_16_remove_deprecated_tool()
{     
    global $db_error_counter, $fs_error_counter;
    global $currentCourseDbVersion, $currentCourseClarolineVersion;
    global $currentCourseDbNameGlu;

    if ( preg_match('/^1.5/',$currentCourseDbVersion) )
    {
        /**
         * Drop deprecated pages tables
         */
    
        $sqlForUpdate[] = "DROP TABLE IF EXISTS `".$currentCourseDbNameGlu."pages`"; 
        
        if ( ! upgrade_apply_sql($sqlForUpdate) )
        {
            // upgrade db failed
            $db_error_counter++;
        }
    }
}

/**
 * Upgrade forum tool to 1.6
 */

function forum_upgrade_to_16($course_code)
{
    global $db_error_counter, $fs_error_counter;
    global $currentCourseDbVersion, $currentCourseClarolineVersion;
    global $currentCourseDbNameGlu;

    // upgrade table
    if ( preg_match('/^1.5/',$currentCourseDbVersion) )
    {
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
    
        if ( ! upgrade_apply_sql($sqlForUpdate) )
        {
            // upgrade db failed
            $db_error_counter++;
        }

    } 

}
 
/**
 * Upgrade quizz tool to 1.6
 */

function quizz_upgrade_to_16()
{   
    global $db_error_counter, $fs_error_counter;
    global $currentCourseDbVersion, $currentCourseClarolineVersion;
    global $currentCourseDbNameGlu;

    if ( preg_match('/^1.5/',$currentCourseDbVersion) )
    {
        $sqlForUpdate[] = "ALTER IGNORE TABLE `".$currentCourseDbNameGlu."quiz_question` CHANGE `picture_name` `attached_file` varchar(50) default ''";
        $sqlForUpdate[] = "ALTER IGNORE TABLE `".$currentCourseDbNameGlu."quiz_question` CHANGE `ponderation` `ponderation` float unsigned default NULL";
        $sqlForUpdate[] = "ALTER IGNORE TABLE `".$currentCourseDbNameGlu."quiz_test` ADD `max_time` smallint(5) unsigned NOT NULL default '0'";
        $sqlForUpdate[] = "ALTER IGNORE TABLE `".$currentCourseDbNameGlu."quiz_test` ADD `max_attempt` tinyint(3) unsigned NOT NULL default '0'";
        $sqlForUpdate[] = "ALTER IGNORE TABLE `".$currentCourseDbNameGlu."quiz_test` ADD `show_answer` enum('ALWAYS','NEVER','LASTTRY') NOT NULL default 'ALWAYS'";
        $sqlForUpdate[] = "ALTER IGNORE TABLE `".$currentCourseDbNameGlu."quiz_test` ADD `anonymous_attempts` enum('YES','NO') NOT NULL default 'YES'";
        $sqlForUpdate[] = "ALTER IGNORE TABLE `".$currentCourseDbNameGlu."quiz_test` ADD `start_date` datetime NOT NULL default '0000-00-00 00:00:00'";
        $sqlForUpdate[] = "ALTER IGNORE TABLE `".$currentCourseDbNameGlu."quiz_test` ADD `end_date` datetime NOT NULL default '0000-00-00 00:00:00'";
        $sqlForUpdate[] = "ALTER IGNORE TABLE `".$currentCourseDbNameGlu."quiz_answer` CHANGE `ponderation` `ponderation` float default NULL";
        
        if ( ! upgrade_apply_sql($sqlForUpdate) )
        {
            // upgrade db failed
            $db_error_counter++;
        }

    }

    // upgrade file
    if ( preg_match('/^1.5/',$currentCourseClarolineVersion)  ) 
    {
        if ( quizz_upgrade_to_16_rename_folder() === false )
        {
            // upgrade file failed
            $fs_error_counter++;
        }
    }
    
}

function quizz_upgrade_to_16_rename_folder()
{   
    global $currentcoursePathSys, $lang_p_CannotCreate_s, $lang_p_CannotRename_s_s;

    $nb_error = 0;

    // rename folder image in course folder to exercise 
    if ( is_dir($currentcoursePathSys.'image') ) 
    {   
        if ( ! @rename($currentcoursePathSys.'image',$currentcoursePathSys.'exercise') )
        {
            $nb_error++;
            $errorMsgs .= '<p class="error">'
               . '<strong>' . sprintf($lang_p_CannotRename_s_s,$currentcoursePathSys.'/image',$currentcoursePathSys.'/exercise') . '</strong> '
               . '</p>';
        } 
    }
    elseif ( !is_dir($currentcoursePathSys.'exercise') ) 
    {
        if ( !@mkdir($currentcoursePathSys.'exercise', 0777) )
        {
            $nb_error++;
            $errorMsgs .= '<p class="error">'
               . '<strong>' . sprintf($lang_p_CannotCreate_s,$currentcoursePathSys.'exercise') . '</strong> '
               . '</p>';
        }
    }
    return $nb_error;
}

/**
 * Upgrade assignment tool to 1.6
 */

function assignment_upgrade_to_16()
{
    global $db_error_counter, $fs_error_counter;
    global $currentCourseDbVersion, $currentCourseClarolineVersion;
    global $currentCourseDbNameGlu;
    
    if ( preg_match('/^1.5/',$currentCourseDbVersion) )
    {
    
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
         * Drop deprecated assignment_doc
         */
    
        // $sqlForUpdate[] = "DROP TABLE IF EXISTS `".$currentCourseDbNameGlu."assignment_doc`"; 
        if ( ! upgrade_apply_sql($sqlForUpdate) )
        {
            // upgrade db failed
            $db_error_counter++;
        }
    
    }

    global $currentCourseDbNameGlu, $currentCourseIDsys, $currentCourseCode;
    global $_uid;
    
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
    
    global $currentcoursePathSys, $lang_p_CannotCreate_s;
    
    $nb_error = 0;

    // create work assig_1 folder    
    $work_dirname = $currentcoursePathSys.'work/';
    $assignment_dirname = $work_dirname . 'assig_1/';
    
    if ( !is_dir($assignment_dirname) )
    {
        if ( !@mkdir($assignment_dirname, 0777) )
        {
            $nb_error++;
            $errorMsgs .= '<p class="error">'
               . '<strong>' . sprintf($lang_p_CannotCreate_s,$assignment_dirname) . '</strong> '
               . '</p>';
        }
    }
    
    // move assignment from work to work/assig_1    
    if ( is_dir($work_dirname) )
    {
        if ( $handle=opendir($work_dirname) )
        {   
            while ( FALSE !== ($file = readdir($handle)) )
            {
                if ( is_dir($work_dirname.$file) ) continue;
    
                if ( @rename($work_dirname.$file,$assignment_dirname.$file) === FALSE )
                {
                    $nb_error++;
                    $errorMsgs .= '<p class="error">'
                   . '<strong>' . sprintf($lang_p_CannotRename_s_s,$work_dirname.$file,$assignment_dirname.$file) . '</strong> '
                   . '</p>';
    
                }
    
            }
            closedir($handle);
        }                    
    }
    return $nb_error;

}

/**
 * Upgrade group tool to 1.6
 */

function group_upgrade_to_16()
{   
    global $db_error_counter, $fs_error_counter;
    global $currentCourseDbVersion, $currentCourseClarolineVersion;
    global $currentCourseDbNameGlu;
    
    if ( preg_match('/^1.5/',$currentCourseDbVersion) )
    {
        $sqlForUpdate[] = "RENAME TABLE `".$currentCourseDbNameGlu."properties` TO `".$currentCourseDbNameGlu."property`";

        if ( ! upgrade_apply_sql($sqlForUpdate) )
        {
            // upgrade db failed
            $db_error_counter++;
        }
    }    
}
    
/**
 * Upgrade tracking tool to 1.6
 */

function tracking_upgrade_to_16()
{
    global $db_error_counter, $fs_error_counter;
    global $currentCourseDbVersion, $currentCourseClarolineVersion;
    global $currentCourseDbNameGlu;

    if ( preg_match('/^1.5/',$currentCourseDbVersion) )
    { 
        $sqlForUpdate[] = "RENAME TABLE `".$currentCourseDbNameGlu."track_e_access` TO `".$currentCourseDbNameGlu."track_e_access_15`";
        
        $sqlForUpdate[] = "CREATE TABLE IF NOT EXISTS `".$currentCourseDbNameGlu."track_e_access` (
          `access_id` int(11) NOT NULL auto_increment,
          `access_user_id` ".$sqlkeys['user'].",
          `access_date` datetime NOT NULL default '0000-00-00 00:00:00',
          `access_tid` int(10) default NULL,
          `access_tlabel` varchar(8) default NULL,
          PRIMARY KEY  (`access_id`)
        ) TYPE=MyISAM COMMENT='Record informations about access to course or tools'";
        
        $sqlForUpdate[] = "ALTER IGNORE TABLE `".$currentCourseDbNameGlu."track_e_exercices` ADD `exe_time`  mediumint(8) NOT NULL default '0'";
        $sqlForUpdate[] = "ALTER IGNORE TABLE `".$currentCourseDbNameGlu."track_e_exercices` CHANGE `exe_result` `exe_result` float NOT NULL default '0'";
        $sqlForUpdate[] = "ALTER IGNORE TABLE `".$currentCourseDbNameGlu."track_e_exercices` CHANGE `exe_weighting` `exe_weighting` float NOT NULL default '0'";

        if ( ! upgrade_apply_sql($sqlForUpdate) )
        {
            // upgrade db failed
            $db_error_counter++;
        }
    }

}

?>
