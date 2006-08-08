<?php // $Id$
if ( count( get_included_files() ) == 1 ) die( '---' );
/**
 * CLAROLINE
 *
 * Function to update course tool 1.7 to 1.8
 *
 * - READ THE SAMPLE AND COPY PASTE IT
 *
 * - ADD TWICE MORE COMMENT THAT YOU THINK NEEDED
 *
 * This code would be splited by task for the 1.8 Stable but code inside
 * function won't change, so let's go to write it.
 *
 * @version 1.8 $Revision$
 *
 * @copyright (c) 2001-2006 Universite catholique de Louvain (UCL)
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
 * Upgrade foo tool to 1.8
 *
 * explanation of task
 *
 * @param $course_code string
 * @return boolean whether true if succeed
 */

function group_upgrade_to_18($course_code)
{
    global $currentCourseVersion;

    $versionRequiredToProceed = '/^1.7/';
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
                        )";

                if ( upgrade_apply_sql($sql_step1) )
                {
                    $step = set_upgrade_status($tool, 2, $course_code);
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

                if ( upgrade_apply_sql($sql) )
                {
                    $step = set_upgrade_status($tool, 3, $course_code);
                }

            case 3 :
                $sql = "DROP TABLE IF EXISTS`".$currentCourseDbNameGlu."group_property`";

                if ( upgrade_apply_sql($sql) )
                {
                    $step = set_upgrade_status($tool, 0, $course_code);
                }

            default :
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

function tool_list_upgrade_to_18 ($course_code)
{
    global $currentCourseVersion;

    $versionRequiredToProceed = '/^1.7/';
    $tool = 'TOOLLIST';
    $currentCourseDbNameGlu = claro_get_course_db_name_glued($course_code);

    if ( preg_match($versionRequiredToProceed,$currentCourseVersion) )
    {
        // On init , $step = 1
        switch( $step = get_upgrade_status($tool,$course_code) )
        {
            case 1 :

                $sql_step1 = "ALTER IGNORE TABLE `" . $currentCourseDbNameGlu . "tool_list` ADD `visibility` tinyint(4) default 0 ";

                if ( upgrade_apply_sql($sql_step1) )
                {
                    $step = set_upgrade_status($tool, 2, $course_code);
                }

            case 2 :

                $sql_step2 = "UPDATE `" . $currentCourseDbNameGlu . "tool_list` 
                      SET `visibility` = 1
                      WHERE `access` = 'ALL' ";

                if ( upgrade_apply_sql($sql_step2) )
                {
                    $step = set_upgrade_status($tool, 3, $course_code);
                }
    
            case 3 :

                $sql_step3 = "ALTER IGNORE TABLE `" . $currentCourseDbNameGlu . "tool_list` DROP column `access` ";

                if ( upgrade_apply_sql($sql_step3) )
                {
                    $step = set_upgrade_status($tool, 4, $course_code);
                }

            case 4 :
                
                $sql_step4 = "ALTER IGNORE TABLE `" . $currentCourseDbNameGlu . "tool_list` ADD `addedTool` ENUM('YES','NO') DEFAULT 'YES' ";

                if ( upgrade_apply_sql($sql_step4) )
                {
                    $step = set_upgrade_status($tool, 0, $course_code);
                }

            default :

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

function qwiz_upgrade_to_18 ($course_code)
{
    global $currentCourseVersion;

    $versionRequiredToProceed = '/^1.7/';
    $tool = 'CLQWZ';
    $currentCourseDbNameGlu = claro_get_course_db_name_glued($course_code);

    if ( preg_match($versionRequiredToProceed,$currentCourseVersion) )
    {
        // On init , $step = 1
        switch( $step = get_upgrade_status($tool,$course_code) )
        {
            case 1 :
                
                $sql_step1[] = "CREATE TABLE `". $currentCourseDbNameGlu . "qwz_exercise` (
                    `id` int(11) NOT NULL auto_increment,
                    `title` varchar(255) NOT NULL,
                    `description` text NOT NULL,
                    `visibility` enum('VISIBLE','INVISIBLE') NOT NULL default 'INVISIBLE',
                    `displayType` enum('SEQUENTIAL','ONEPAGE') NOT NULL default 'ONEPAGE',
                    `shuffle` smallint(6) NOT NULL default '0',
                    `showAnswers` enum('ALWAYS','NEVER','LASTTRY') NOT NULL default 'ALWAYS',
                    `startDate` datetime NOT NULL,
                    `endDate` datetime NOT NULL,
                    `timeLimit` smallint(6) NOT NULL default '0',
                    `attempts` tinyint(4) NOT NULL default '0',
                    `anonymousAttempts` enum('ALLOWED','NOTALLOWED') NOT NULL default 'NOTALLOWED',
                PRIMARY KEY  (`id`)
                )";

                $sql_step1[] = "CREATE TABLE `". $currentCourseDbNameGlu . "qwz_question` (
                    `id` int(11) NOT NULL auto_increment,
                    `title` varchar(255) NOT NULL default '',
                    `description` text NOT NULL,
                    `attachment` varchar(255) NOT NULL default '',
                    `type` enum('MCUA','MCMA','TF','FIB','MATCHING') NOT NULL default 'MCUA',
                    `grade` float NOT NULL default '0',
                PRIMARY KEY  (`id`)
                )";

                $sql_step1[] = "CREATE TABLE `" . $currentCourseDbNameGlu . "qwz_rel_exercise_question` (
                    `exerciseId` int(11) NOT NULL,
                    `questionId` int(11) NOT NULL,
                    `rank` int(11) NOT NULL default '0'
                )";

                $sql_step1[] = "CREATE TABLE `" . $currentCourseDbNameGlu . "qwz_answer_truefalse'` (
                    `id` int(11) NOT NULL auto_increment,
                    `questionId` int(11) NOT NULL,
                    `trueFeedback` text NOT NULL,
                    `trueGrade` float NOT NULL,
                    `falseFeedback` text NOT NULL,
                    `falseGrade` float NOT NULL,
                    `correctAnswer` enum('TRUE','FALSE') NOT NULL,
                PRIMARY KEY  (`id`)
                )";

                $sql_step1[] = "CREATE TABLE `" . $currentCourseDbNameGlu . "qwz_answer_multiple_choice` (
                    `id` int(11) NOT NULL auto_increment,
                    `questionId` int(11) NOT NULL,
                    `answer` text NOT NULL,
                    `correct` tinyint(4) NOT NULL,
                    `grade` float NOT NULL,
                    `comment` text NOT NULL,
                PRIMARY KEY  (`id`)
                )";

                $sql_step1[] = "CREATE TABLE `" . $currentCourseDbNameGlu . "qwz_answer_fib` (
                    `id` int(11) NOT NULL auto_increment,
                    `questionId` int(11) NOT NULL,
                    `answer` text NOT NULL,
                    `gradeList` text NOT NULL,
                    `wrongAnswerList` text NOT NULL,
                    `type` tinyint(4) NOT NULL,
                PRIMARY KEY  (`id`)
                )";

                $sql_step1[] = "CREATE TABLE `" . $currentCourseDbNameGlu . "qwz_answer_matching` (
                    `id` int(11) NOT NULL auto_increment,
                    `questionId` int(11) NOT NULL,
                    `answer` text NOT NULL,
                    `match` varchar(32) default NULL,
                    `grade` float NOT NULL default '0',
                    `code` varchar(32) default NULL,
                PRIMARY KEY  (`id`)
                )";

                if ( upgrade_apply_sql($sql_step1) )
                {
                    $step = set_upgrade_status($tool, 2, $course_code);
                }

        case 2 :
                    
                $step = set_upgrade_status($tool, 0, $course_code);

        default :
                return $step;
        }
    }

    return false;
}

?>
