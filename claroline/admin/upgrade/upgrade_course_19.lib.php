<?php // $Id$
if ( count( get_included_files() ) == 1 ) die( '---' );
/**
 * CLAROLINE
 *
 * Function to update course tool 1.8 to 1.9
 *
 * - READ THE SAMPLE AND COPY PASTE IT
 *
 * - ADD TWICE MORE COMMENT THAT YOU THINK NEEDED
 *
 * This code would be splited by task for the 1.8 Stable but code inside
 * function won't change, so let's go to write it.
 *
 * @version 1.9 $Revision$
 *
 * @copyright (c) 2001-2008 Universite catholique de Louvain (UCL)
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

/*===========================================================================
 Upgrade to claroline 1.8
 ===========================================================================*/

/**
 * Upgrade course repository files and script to 1.8
 */

/*function course_repository_upgrade_to_19 ($course_code)
{
    global $currentCourseVersion, $currentcoursePathSys;

    $versionRequiredToProceed = '/^1.7/';
    $tool = 'CLINDEX';

    if ( preg_match($versionRequiredToProceed,$currentCourseVersion) )
    {
        switch( $step = get_upgrade_status($tool,$course_code) )
        {
            case 1 :
                
                if ( is_writable($currentcoursePathSys) )
                {
                    if ( !is_dir($currentcoursePathSys) ) 
                        claro_mkdir($currentcoursePathSys);
                    if ( !is_dir($currentcoursePathSys.'/chat') )
                        claro_mkdir($currentcoursePathSys.'/chat');
                    if ( !is_dir($currentcoursePathSys.'/modules') )
                        claro_mkdir($currentcoursePathSys.'/modules');
                    if ( !is_dir($currentcoursePathSys.'/scormPackages') )
                        claro_mkdir($currentcoursePathSys . '/scormPackages');
            
                    $step = set_upgrade_status($tool, 2, $course_code);
                }
                else
                {
                    log_message(sprintf('Repository %s not writable', $currentcoursePathSys));
                    return $step;
                }

            case 2 :

                // build index.php of course
                $fd = fopen($currentcoursePathSys . '/index.php', 'w');
        
                if (!$fd) return $step ;

                // build index.php
                $string = '<?php ' . "\n"
                    . 'header (\'Location: '. $GLOBALS['urlAppend'] . '/claroline/course/index.php?cid=' . rawurlencode($course_code) . '\') ;' . "\n"
                    . '?' . '>' . "\n" ;

                if ( ! fwrite($fd, $string) ) return $step;
                if ( ! fclose($fd) )          return $step;
                    
                $step = set_upgrade_status($tool, 0, $course_code);

            default :
                return $step;
        }
    }
    return false ;
}*/

/**
 * Upgrade foo tool to 1.8
 *
 * explanation of task
 *
 * @param $course_code string
 * @return boolean whether true if succeed
 */

/*function group_upgrade_to_19($course_code)
{
    global $currentCourseVersion;

    $versionRequiredToProceed = '/^1.8/';
    $tool = 'CLGRP';
    $currentCourseDbNameGlu = claro_get_course_db_name_glued($course_code);

    if ( preg_match($versionRequiredToProceed,$currentCourseVersion) )
    {
        // On init , $step = 1
        switch( $step = get_upgrade_status($tool,$course_code) )
        {
            case 1 :

                $sql_step1 = " CREATE TABLE
                        `".$currentCourseDbNameGlu."course_properties`
                        (
                            `id` int(11) NOT NULL auto_increment,
                            `name` varchar(255) NOT NULL default '',
                            `value` varchar(255) default NULL,
                            `category` varchar(255) default NULL,
                            PRIMARY KEY  (`id`)
                        ) TYPE=MyISAM ";

                if ( upgrade_sql_query($sql_step1) )
                {
                    $step = set_upgrade_status($tool, 2, $course_code);
                }
                else
                {
                    return $step;
                }

            case 2 :

                $sql = "SELECT self_registration,
                               private,
                               nbGroupPerUser,
                               forum,
                               document,
                               wiki,
                               chat
                    FROM `".$currentCourseDbNameGlu."group_property`";

                $groupSettings = claro_sql_query_get_single_row($sql);

                if ( is_array($groupSettings) )
                {
                    $sql = "INSERT
                            INTO `".$currentCourseDbNameGlu."course_properties`
                                   (`name`, `value`, `category`)
                            VALUES
                            ('self_registration', '".$groupSettings['self_registration']."', 'GROUP'),
                            ('nbGroupPerUser',    '".$groupSettings['nbGroupPerUser'   ]."', 'GROUP'),
                            ('private',           '".$groupSettings['private'          ]."', 'GROUP'),
                            ('CLFRM',             '".$groupSettings['forum'            ]."', 'GROUP'),
                            ('CLDOC',             '".$groupSettings['document'         ]."', 'GROUP'),
                            ('CLWIKI',            '".$groupSettings['wiki'             ]."', 'GROUP'),
                            ('CLCHT',             '".$groupSettings['chat'             ]."', 'GROUP')";
                }

                if ( upgrade_sql_query($sql) )
                {
                    $step = set_upgrade_status($tool, 3, $course_code);
                }
                else
                {
                    return $step;
                }

            case 3 :

                $sql = "DROP TABLE IF EXISTS`".$currentCourseDbNameGlu."group_property`";

                if ( upgrade_sql_query($sql) )
                {
                    $step = set_upgrade_status($tool, 4, $course_code);
                }
                else
                {
                    return $step;
                }

            case 4 :

                $sql = "UPDATE `".$currentCourseDbNameGlu."group_team`
                        SET `maxStudent` = NULL
                        WHERE `maxStudent` = 0 ";

                if ( upgrade_sql_query($sql) )
                {
                    $step = set_upgrade_status($tool, 0, $course_code);
                }
                else
                {
                    return $step;
                }


            default :
                return $step;
        }
    }

    return false;
}*/

/**
 * Upgrade foo tool to 1.8
 *
 * explanation of task
 *
 * @param $course_code string
 * @return boolean whether true if succeed
 */

function tool_list_upgrade_to_19 ($course_code)
{
    global $currentCourseVersion;

    $versionRequiredToProceed = '/^1.8/';
    $tool = 'TOOLLIST';
    $currentCourseDbNameGlu = claro_get_course_db_name_glued($course_code);

    if ( preg_match($versionRequiredToProceed,$currentCourseVersion) )
    {
        // On init , $step = 1
        switch( $step = get_upgrade_status($tool,$course_code) )
        {
            case 1 :
                $sql_step1 = "ALTER IGNORE TABLE `" . $currentCourseDbNameGlu . "tool_list` ADD `activated` ENUM('true','false') NOT NULL DEFAULT 'true'";
                
                if ( upgrade_sql_query($sql_step1) )
                {
                    $step = set_upgrade_status($tool, 2, $course_code);
                }
                else
                {
                    return $step;
                }
            default :
                $step = set_upgrade_status($tool, 0, $course_code);
                return $step;
        }
    }
    return false;
}

/**
 * Upgrade foo tool to 1.8
 *
 * explanation of task
 *
 * @param $course_code string
 * @return boolean whether true if succeed
 */

/* function quiz_upgrade_to_19 ($course_code)
{
    // PRIMARY KEY (`exerciseId`,`questionId`)
    global $currentCourseVersion, $currentcoursePathSys;

    $versionRequiredToProceed = '/^1.8/';
    $tool = 'CLQWZ';
    $currentCourseDbNameGlu = claro_get_course_db_name_glued($course_code);

    if ( preg_match($versionRequiredToProceed,$currentCourseVersion) )
    {
        // On init , $step = 1
        switch( $step = get_upgrade_status($tool,$course_code) )
        {
            case 1 :
                
                $sql_step1[] = "ALTER TABLE `". $currentCourseDbNameGlu . "qwz_rel_exercise_question`
                  DROP PRIMARY KEY,
                   ADD PRIMARY KEY(`exerciseId`, `questionId`)";

                $sql_step1[] = "CREATE TABLE IF NOT EXISTS `". $currentCourseDbNameGlu . "qwz_tracking` (
                    `id` int(11) NOT NULL auto_increment,
                    `user_id` int(10) default NULL,
                    `date` datetime NOT NULL default '0000-00-00 00:00:00',
                    `exo_id` int(11) NOT NULL default '0',
                    `result` float NOT NULL default '0',
                    `time`    mediumint(8) NOT NULL default '0',
                    `weighting` float NOT NULL default '0',
                    PRIMARY KEY  (`id`)
                ) TYPE=MyISAM  COMMENT='Record informations about exercices';";
                
                $sql_step1[] = "CREATE TABLE IF NOT EXISTS `". $currentCourseDbNameGlu . "qwz_tracking_questions` (
                    `id` int(11) NOT NULL auto_increment,
                    `exercise_track_id` int(11) NOT NULL default '0',
                    `question_id` int(11) NOT NULL default '0',
                    `result` float NOT NULL default '0',
                    PRIMARY KEY  (`id`)
                ) TYPE=MyISAM  COMMENT='Record answers of students in exercices';";
                
                $sql_step1[] = "CREATE TABLE IF NOT EXISTS `". $currentCourseDbNameGlu . "qwz_tracking_answers` (
                    `id` int(11) NOT NULL auto_increment,
                    `details_id` int(11) NOT NULL default '0',
                    `answer` text NOT NULL,
                    PRIMARY KEY  (`id`)
                ) TYPE=MyISAM  COMMENT='';";

                if ( upgrade_apply_sql($sql_step1) )
                {
                    $step = set_upgrade_status($tool, 0, $course_code);
                }
                else
                {
                    return $step;
                }
                
        default :
                return $step;
        }
    }

    return false;
} */

/**
 * Function to upgrade tool intro 
 */

/* function tool_intro_upgrade_to_18 ($course_code)
{    
    global $currentCourseVersion, $currentcoursePathSys;

    $versionRequiredToProceed = '/^1.7/';
    $tool = 'CLINTRO';
    $currentCourseDbNameGlu = claro_get_course_db_name_glued($course_code);

    if ( preg_match($versionRequiredToProceed,$currentCourseVersion) )
    {
        switch( $step = get_upgrade_status($tool,$course_code) )
        {
            case 1 :

                // remove tool intro
                $sql = "DELETE FROM `".$currentCourseDbNameGlu."tool_intro`
                        WHERE tool_id > 0" ;

                if ( upgrade_sql_query($sql) ) $step = set_upgrade_status($tool, 0, $course_code);
                else return $step;

            default :
                return $step;
        }
    }

    return false;
}*/

/**
 * Upgrade forum tool to 1.8
 */

/* function forum_upgrade_to_18($course_code)
{
    $versionRequiredToProceed = '/^1.7/';
    $tool = 'CLFRM';
    
    global $currentCourseVersion;
    $currentCourseDbNameGlu = claro_get_course_db_name_glued($course_code);

    if ( preg_match($versionRequiredToProceed,$currentCourseVersion) )
    {
        switch( $step = get_upgrade_status($tool,$course_code) )
        {
            case 1 :

                // update type of cat_order (fix bug 740)
                $sql = "ALTER IGNORE TABLE `".$currentCourseDbNameGlu."bb_categories`
                        CHANGE `cat_order` `cat_order` int(10)" ;

                if ( upgrade_sql_query($sql) ) $step = set_upgrade_status($tool, 0, $course_code);
                else return $step;

            default :
                return $step;
        }
    }

    return false;
}*/

/**
 * Upgrade tracking tool to 1.8
 */

function tracking_upgrade_to_19($course_code)
{
    /*$versionRequiredToProceed = '/^1.8/';
    $tool = 'CLSTATS';
    
    global $currentCourseVersion;
    $currentCourseDbNameGlu = claro_get_course_db_name_glued($course_code);

    if ( preg_match($versionRequiredToProceed,$currentCourseVersion) )
    {
        switch( $step = get_upgrade_status($tool,$course_code) )
        {
            case 1 :

                $sql = "UPDATE `".$currentCourseDbNameGlu."track_e_access` 
                        SET access_tlabel = TRIM(TRAILING '_' FROM access_tlabel)";
                
                if ( upgrade_sql_query($sql) ) $step = set_upgrade_status($tool, 2, $course_code);
                else return $step;

            case 2 :

                $sql = "ALTER IGNORE TABLE `".$currentCourseDbNameGlu."track_e_exercices` 
                        CHANGE `exe_exo_id` `exe_exo_id` int(11)";
                
                if ( upgrade_sql_query($sql) ) $step = set_upgrade_status($tool, 0, $course_code);
                else return $step;

            default :
                return $step;
        }
    }

    return false;*/
}

function calendar_upgrade_to_19($course_code)
{
    $versionRequiredToProceed = '/^1.8/';
    $tool = 'CLCAL';
    
    global $currentCourseVersion;
    $currentCourseDbNameGlu = claro_get_course_db_name_glued($course_code);

    if ( preg_match($versionRequiredToProceed,$currentCourseVersion) )
    {
        switch( $step = get_upgrade_status($tool,$course_code) )
        {
            case 1 :
                $sql = "ALTER IGNORE TABLE `".$currentCourseDbNameGlu."calendar_event` 
                        ADD `location` varchar(50)";
                
                if ( upgrade_sql_query($sql) ) $step = set_upgrade_status($tool, 0, $course_code);
                else return $step;

            default :
                return $step;
        }
    }

    return false;
}

function convert_crl_from_18_to_19( $crl )
{
    if (preg_match(
        '!(crl://'.get_conf('platform_id').'/[^/]+/groups/\d+/)([^/])(.*)!',
        $crl, $matches ) )
    {
        return $matches[1] . rtrim( $matches[2], '_' ) . $matches[3];
    }
    elseif (preg_match(
        '!(crl://'.get_conf('platform_id').'/[^/]+/)([^/])(.*)!',
        $crl, $matches ) )
    {
        return $matches[1] . rtrim( $matches[2], '_' ) . $matches[3];
    }
    else
    {
        return $crl;
    }
}


function linker_upgrade_to_19($course_code)
{
    $versionRequiredToProceed = '/^1.8/';
    $tool = 'CLCAL';
    
    global $currentCourseVersion;
    $currentCourseDbNameGlu = claro_get_course_db_name_glued($course_code);

    if ( preg_match($versionRequiredToProceed,$currentCourseVersion) )
    {
        switch( $step = get_upgrade_status($tool,$course_code) )
        {
            case 1 :
                $sql = "SELECT `crl` FROM `".$currentCourseDbNameGlu."lnk_resource`";
                
                $res = claro_sql_query_fetch_all_rows( $sql );
                $success = ($res !== false);
                
                foreach( $res as $resource )
                {
                    $sql = "UPDATE `".$currentCourseDbNameGlu."lnk_resource`
                    SET `crl` = '" . claro_sql_escape( convert_crl_from_18_to_19($resource['crl']) ) ."'
                    WHERE `crl` = '" .claro_sql_escape( $resource['crl'] ) ."'";
                    
                    $success = upgrade_sql_query( $sql );
                    
                    if ( ! $success )
                    {
                        break;
                    }
                }
                
                if ( $success ) $step = set_upgrade_status($tool, 0, $course_code);
                else return $step;

            default :
                return $step;
        }
    }

    return false;
}