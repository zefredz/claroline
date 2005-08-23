<?php // $Id$
/**
 * CLAROLINE
 *
 * Function to update course tool 1.6 to 1.7
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
 Upgrade to claroline 1.7
 ===========================================================================*/

/**
 * Upgrade announcement tool to 1.7
 */

function announcement_upgrade_to_17()
{
    global $db_error_counter, $fs_error_counter;
    global $currentCourseDbVersion, $currentCourseClarolineVersion;
    global $currentCourseDbNameGlu;
    
    if ( preg_match('/^1.6/',$currentCourseDbVersion) )
    {
        // add visibility fields in announcement, calendar and course_description
        $sqlForUpdate[] = "ALTER IGNORE TABLE `" . $currentCourseDbNameGlu."announcement` ADD `visibility` enum('SHOW','HIDE') NOT NULL default 'SHOW'";

        if ( ! upgrade_apply_sql($sqlForUpdate) )
        {
            // upgrade db failed
            $db_error_counter++;
        }
    }
}

/**
 * Upgrade agenda tool to 1.7
 */

function agenda_upgrade_to_17()
{
    global $db_error_counter, $fs_error_counter;
    global $currentCourseDbVersion, $currentCourseClarolineVersion;
    global $currentCourseDbNameGlu;

    if ( preg_match('/^1.6/',$currentCourseDbVersion) )
    {
        // add visibility fields in announcement, calendar and course_description
        $sqlForUpdate[] = "ALTER IGNORE TABLE `" . $currentCourseDbNameGlu."calendar_event` ADD `visibility` enum('SHOW','HIDE') NOT NULL default 'SHOW'";
        if ( ! upgrade_apply_sql($sqlForUpdate) )
        {
            // upgrade db failed
            $db_error_counter++;
        }
    }

}

/**
 * Upgrade course description tool to 1.7
 */

function course_despcription_upgrade_to_17()
{
    global $db_error_counter, $fs_error_counter;
    global $currentCourseDbVersion, $currentCourseClarolineVersion;
    global $currentCourseDbNameGlu;

    if ( preg_match('/^1.6/',$currentCourseDbVersion) )
    {

        // add visibility fields in announcement, calendar and course_description
        $sqlForUpdate[] = "ALTER IGNORE TABLE `" . $currentCourseDbNameGlu."course_description` ADD `visibility` enum('SHOW','HIDE') NOT NULL default 'SHOW'";

        if ( ! upgrade_apply_sql($sqlForUpdate) )
        {
            // upgrade db failed
            $db_error_counter++;
        }
    }
}

/**
 * Upgrade tracking tool to 1.7
 */

function tracking_upgrade_to_17()
{
    global $db_error_counter, $fs_error_counter;
    global $currentCourseDbVersion, $currentCourseClarolineVersion;
    global $currentCourseDbNameGlu;

    if ( preg_match('/^1.6/',$currentCourseDbVersion) )
    {
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

        if ( ! upgrade_apply_sql($sqlForUpdate) )
        {
            // upgrade db failed
            $db_error_counter++;
        }
    }
}

/**
 * Upgrade linker tool to 1.7
 */

function linker_upgrade_to_17()
{
    global $db_error_counter, $fs_error_counter;
    global $currentCourseDbVersion, $currentCourseClarolineVersion;
    global $currentCourseDbNameGlu;

    if ( preg_match('/^1.6/',$currentCourseDbVersion) )
    {
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

        if ( ! upgrade_apply_sql($sqlForUpdate) )
        {
            // upgrade db failed
            $db_error_counter++;
        }
    }
}

/**
 * Upgrade wiki tool to 1.6
 */

function wiki_upgrade_to_17()
{
    global $db_error_counter, $fs_error_counter;
    global $currentCourseDbVersion, $currentCourseClarolineVersion;
    global $currentCourseDbNameGlu;
    
    if ( preg_match('/^1.6/',$currentCourseDbVersion) )
    {
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

        if ( ! upgrade_apply_sql($sqlForUpdate) )
        {
            // upgrade db failed
            $db_error_counter++;
        }
    }
}

/**
 * Upgrade forum tool to 1.7
 */

function forum_upgrade_to_17()
{
    global $db_error_counter, $fs_error_counter;
    global $currentCourseDbVersion, $currentCourseClarolineVersion;
    global $currentCourseDbNameGlu;
   
    if ( preg_match('/^1.6/',$currentCourseDbVersion) )
    {
        // groups of forums
    
        $sqlForUpdate[] = "ALTER IGNORE TABLE `" . $currentCourseDbNameGlu."bb_forums` ADD group_id int(11) default NULL";
        $sqlForUpdate[] = "ALTER IGNORE TABLE `" . $currentCourseDbNameGlu."bb_forums` DROP COLUMN md5";
        if ( ! upgrade_apply_sql($sqlForUpdate) )
        {
            // upgrade db failed
            $db_error_counter++;
        }

        $sql = "SELECT `id`,`forumId`
                FROM `" . $currentCourseDbNameGlu."group_team`";
        $result = claro_sql_query();

        while ( $row = mysql_fetch_array() )
        {
            $sql = " UPDATE `" . $currentCourseDbNameGlu."bb_forums`
                     SET group_id = " . $row['id'] . "
                     WHERE `forum_id` = " . $row['forumId'] . "";
            claro_sql_query($sql);
        }

        $sqlForUpdate[] = "ALTER IGNORE TABLE `" . $currentCourseDbNameGlu."group_team` DROP COLUMN forumId";

        if ( ! upgrade_apply_sql($sqlForUpdate) )
        {
            // upgrade db failed
            $db_error_counter++;
        }
    }
}

/**
 * Upgrade introduction text table to 1.7
 */

function introtext_upgrade_to_17()
{
    global $db_error_counter, $fs_error_counter;
    global $currentCourseDbVersion, $currentCourseClarolineVersion;
    global $currentCourseDbNameGlu;

    if ( preg_match('/^1.6/',$currentCourseDbVersion) )
    {
        $sqlForUpdate[] = "RENAME TABLE `".$currentCourseDbNameGlu."tool_intro` TO `".$currentCourseDbNameGlu."old_tool_intro`";       
        $sqlForUpdate[] = "DROP IF EXISTS TABLE `".$currentCourseDbNameGlu."tool_intro`";
        $sqlForUpdate[] = "CREATE TABLE `".$currentCourseDbNameGlu."tool_intro` (
                              `id` int(11) NOT NULL auto_increment,
                              `tool_id` int(11) NOT NULL default '0',
                              `title` varchar(255) default NULL,
                              `display_date` datetime default NULL,
                              `content` text,
                              `rank` int(11) default '1',
                              `visibility` enum('SHOW','HIDE') NOT NULL default 'SHOW',
                           PRIMARY KEY  (`id`) ) ";

        if ( ! upgrade_apply_sql($sqlForUpdate) )
        {
            // upgrade db failed
            $db_error_counter++;
        }

        $sql = " SELECT `id`, `texte_intro` 
                 FROM `".$currentCourseDbNameGlu."old_tool_intro` ";

        $result = claro_sql_query($sql);

        while ( $row = mysql_fetch_array() )
        {
            $sql = "INSERT INTO `".$currentCourseDbNameGlu."tool_intro`
                    (`tool_id`,`content`)
                    VALUES
                    (`" . $row['id'] . "`,`" . $row['texte_intro'] . "`)";
            claro_sql_query($sql);
        }

    }

}

?>
