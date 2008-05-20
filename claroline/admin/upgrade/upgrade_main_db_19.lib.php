<?php // $Id$
if ( count( get_included_files() ) == 1 ) die( '---' );
/**
 * CLAROLINE
 *
 * Sql query to update main database
 *
 * @version 1.9 $Revision$
 *
 * @copyright (c) 2001-2007 Universite catholique de Louvain (UCL)
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
 Upgrade to claroline 1.9
 ===========================================================================*/

/**
 * Upgrade table course (from main database) to 1.9
 * @return step value, 0 if succeed
 */

function upgrade_main_database_course_to_19 ()
{
    $tbl_mdb_names = claro_sql_get_main_tbl();
    $tool = 'COURSE_19' ;

    switch( $step = get_upgrade_status($tool) )
    {
        case 1 :

            // Rename `enrolment_key` column

            $sqlForUpdate[] = "ALTER IGNORE TABLE `" . $tbl_mdb_names['course'] . "`,
                               CHANGE `enrollment_key` `registrationKey` VARCHAR (255)  NULL  COLLATE latin1_swedish_ci ";
            $sqlForUpdate[] = "ALTER IGNORE TABLE `" . $tbl_mdb_names['course'] . "`,
                               CHANGE `enrolment_key` `registrationKey` VARCHAR (255)  NULL  COLLATE latin1_swedish_ci ";
            if ( upgrade_apply_sql($sqlForUpdate) ) $step = set_upgrade_status($tool, $step+1);
            else return $step ;

            unset($sqlForUpdate);
        case 2 :

            // Add new column

            $sqlForUpdate[] = "ALTER IGNORE TABLE `" . $tbl_mdb_names['course'] . "`,
                                 ADD COLUMN `visibility` ENUM ('VISIBLE','INVISIBLE') DEFAULT 'INVISIBLE' NOT NULL  AFTER `visible`,
                                 ADD COLUMN `access`     ENUM ('PUBLIC','PRIVATE') DEFAULT 'PUBLIC' NOT NULL  after `visibility`,
                                 ADD COLUMN `registration` ENUM ('OPEN','CLOSE') DEFAULT 'OPEN' NOT NULL  AFTER `access`";
            if ( upgrade_apply_sql($sqlForUpdate) ) $step = set_upgrade_status($tool, $step+1);
            else return $step ;

            unset($sqlForUpdate);
        case 3 :

            // Add new column

            // Old value was treated like this
            // $courseDataList['visibility'         ] = (bool) (2 == $courseDataList['visible'] || 3 == $courseDataList['visible'] );
            // $courseDataList['registrationAllowed'] = (bool) (1 == $courseDataList['visible'] || 2 == $courseDataList['visible'] );

            $sqlForUpdate[] = "UPDATE TABLE `" . $tbl_mdb_names['course'] . "`
                                SET `visibility`   = 'VISIBLE',
                                    `access`       = IF(visible=2 OR visible=3,'PUBLIC','PRIVATE') ,
                                    `registration` = IF(visible=1 OR visible=2,'OPEN','CLOSE')";
            if ( upgrade_apply_sql($sqlForUpdate) ) $step = set_upgrade_status($tool, $step+1);
            else return $step ;
            unset($sqlForUpdate);
        case 4 :
            // Remove the old column
            $sqlForUpdate[] = "ALTER IGNORE TABLE `" . $tbl_mdb_names['course'] . "`,
                                 REM COLUMN `visible`";

            if ( upgrade_apply_sql($sqlForUpdate) ) $step = set_upgrade_status($tool, $step+1);
            else return $step ;

            unset($sqlForUpdate);

        case 5 :

            // Rename `fake_code` column

            $sqlForUpdate[] = "ALTER IGNORE TABLE `" . $tbl_mdb_names['course'] . "`,
                               CHANGE `fake_code` `administrativeNumber` VARCHAR (255)  NULL  COLLATE latin1_swedish_ci ";

            if ( upgrade_apply_sql($sqlForUpdate) ) $step = set_upgrade_status($tool, $step+1);
            else return $step ;

            unset($sqlForUpdate);
        case 6 :

            // Rename `department` columns

            $sqlForUpdate[] = "ALTER IGNORE TABLE `" . $tbl_mdb_names['course'] . "`,
                              CHANGE `departmentUrlName` `extLinkName` VARCHAR (180)  NULL  COLLATE latin1_swedish_ci ";
            $sqlForUpdate[] = "ALTER IGNORE TABLE `" . $tbl_mdb_names['course'] . "`,
                              CHANGE `departmentUrl` `extLinkUrl` VARCHAR (30)  NULL  COLLATE latin1_swedish_ci ";

            if ( upgrade_apply_sql($sqlForUpdate) ) $step = set_upgrade_status($tool, $step+1);
            else return $step ;

            unset($sqlForUpdate);
        case 7 :

            // Rename `language` column

            $sqlForUpdate[] = "ALTER IGNORE TABLE `" . $tbl_mdb_names['course'] . "`,
                               CHANGE `languageCourse` `language` VARCHAR (15)  'english'  COLLATE latin1_swedish_ci ";

            if ( upgrade_apply_sql($sqlForUpdate) ) $step = set_upgrade_status($tool, $step+1);
            else return $step ;

            unset($sqlForUpdate);
        default :

            $step = set_upgrade_status($tool, 0);
            return $step;

    }
}

/**
 * Upgrade table rel_course_user (from main database) to 1.9
 * @return step value, 0 if succeed
 */
/*
function upgrade_main_database_rel_course_user_to_19 ()
{
    $tbl_mdb_names = claro_sql_get_main_tbl();
    $tool = 'COURSEUSER_19';

    switch( $step = get_upgrade_status($tool) )
    {
        case 1 :

            $sqlForUpdate[] = "ALTER IGNORE TABLE `" . $tbl_mdb_names['rel_course_user'] . "` ADD `profile_id` int(11) NOT NULL ";
            $sqlForUpdate[] = "ALTER IGNORE TABLE `" . $tbl_mdb_names['rel_course_user'] . "` ADD `count_user_enrol` int(11) NOT NULL default 0 ";
            $sqlForUpdate[] = "ALTER IGNORE TABLE `" . $tbl_mdb_names['rel_course_user'] . "` ADD `count_class_enrol` int(11) NOT NULL default 0 ";
            $sqlForUpdate[] = "ALTER IGNORE TABLE `" . $tbl_mdb_names['rel_course_user'] . "` ADD `isCourseManager` tinyint(4) NOT NULL default 0 ";

            // `statut` tinyint(4) NOT NULL default '5' --> `isCourseManager` tinyint(4) NOT NULL default 0

            $sqlForUpdate[] = "UPDATE `" . $tbl_mdb_names['rel_course_user'] . "`
                               SET `isCourseManager` = 1
                               WHERE `statut` = 1 ";

            $sqlForUpdate[] = "ALTER IGNORE TABLE `" . $tbl_mdb_names['rel_course_user'] . "` DROP COLUMN `statut` ";

            // count_user_enrol egals 1

            $sqlForUpdate[] = "UPDATE `" . $tbl_mdb_names['rel_course_user'] . "` SET `count_user_enrol` = 1 ";

            if ( upgrade_apply_sql($sqlForUpdate) ) $step = set_upgrade_status($tool, $step+1);
            else return $step ;

        default :

            $step = set_upgrade_status($tool, 0);
            return $step;

    }

    return false;

}
*/
/**
 * Upgrade table course_category (from main database) to 1.9
 * @return step value, 0 if succeed
 */
/*
function upgrade_main_database_course_category_to_19 ()
{
    $tbl_mdb_names = claro_sql_get_main_tbl();
    $tool = 'COURSECAT_19';

    switch( $step = get_upgrade_status($tool) )
    {

        case 1 :

            $sqlForUpdate[] = "ALTER IGNORE TABLE `" . $tbl_mdb_names['category'] . "` DROP COLUMN `bc` ";
            $sqlForUpdate[] = "ALTER IGNORE TABLE `" . $tbl_mdb_names['category'] . "` CHANGE `nb_childs` `nb_childs` smallint(6) default 0";

            if ( upgrade_apply_sql($sqlForUpdate) ) $step = set_upgrade_status($tool, $step+1);
            else return $step ;

        default :

            $step = set_upgrade_status($tool, 0);
            return $step;

    }

    return false;
}
*/
/**
 * Upgrade table user (from main database) to 1.9
 * @return step value, 0 if succeed
 */
/*
function upgrade_main_database_user_to_19 ()
{
    $tbl_mdb_names = claro_sql_get_main_tbl();
    $tbl_mdb_names['admin'] = get_conf('mainDbName') . '`.`' . get_conf('mainTblPrefix') . 'admin' ;

    $tool = 'USER_19';

    switch( $step = get_upgrade_status($tool) )
    {
        case 1 :

            $sqlForUpdate[] = "ALTER IGNORE TABLE `" . $tbl_mdb_names['user'] . "` ADD `language` varchar(15) default NULL";
            $sqlForUpdate[] = "ALTER IGNORE TABLE `" . $tbl_mdb_names['user'] . "` ADD `officialEmail` varchar(255) default NULL AFTER `officialCode`";

            $sqlForUpdate[] = "ALTER IGNORE TABLE `" . $tbl_mdb_names['user'] . "` CHANGE `email` `email` varchar(255) default NULL";
            $sqlForUpdate[] = "ALTER IGNORE TABLE `" . $tbl_mdb_names['user'] . "` CHANGE `officialCode` `officialCode`  varchar(255) default NULL";

            // `statut` tinyint(4) default NULL, -->    `isCourseCreator` tinyint(4) default 0

            $sqlForUpdate[] = "ALTER IGNORE TABLE `" . $tbl_mdb_names['user'] . "` ADD `isCourseCreator` tinyint(4) default 0 ";

            $sqlForUpdate[] = "UPDATE `" . $tbl_mdb_names['user'] . "`
                               SET `isCourseCreator` = 1
                               WHERE `statut` = 1";

            $sqlForUpdate[] = "ALTER IGNORE TABLE `" . $tbl_mdb_names['user'] . "` DROP COLUMN `statut` ";

            $sqlForUpdate[] = "ALTER IGNORE TABLE `" . $tbl_mdb_names['user'] . "` ADD `isPlatformAdmin`  tinyint(4) default 0";

            if ( upgrade_apply_sql($sqlForUpdate) ) $step = set_upgrade_status($tool, $step+1);
            else return $step ;

            unset($sqlForUpdate);

        case 2 :

            // `isPlatformAdmin` --> from admin table

            $sql = " SELECT `idUser` FROM `" . $tbl_mdb_names['admin'] . "`";

            $result = claro_sql_query_fetch_all_cols($sql);

            $admin_uid_list = $result['idUser'];

            $sql = " UPDATE `" . $tbl_mdb_names['user'] . "`
                     SET `isPlatformAdmin` = 1
                     WHERE user_id IN (" . implode(',',$admin_uid_list) . ")";

            if ( upgrade_sql_query($sql) ) $step = set_upgrade_status($tool, $step+1);
            else return $step ;

        case 3 :

            // drop table admin

            $sqlForUpdate[] = "DROP TABLE IF EXISTS `" . $tbl_mdb_names['admin'] . "`";

            if ( upgrade_apply_sql($sqlForUpdate) ) $step = set_upgrade_status($tool, $step+1);
            else return $step ;

            unset($sqlForUpdate);

        default :

            $step = set_upgrade_status($tool, 0);
            return $step;

    }

    return false;

}
*/
/**
 * Upgrade table rel_course_class (from main database) to 1.9
 * @return step value, 0 if succeed
 */
/*
function upgrade_main_database_course_class_to_19 ()
{
    $tbl_mdb_names = claro_sql_get_main_tbl();
    $tool = 'COURSE_CLASS_19';

    switch( $step = get_upgrade_status($tool) )
    {
        case 1 :

            // course class

            $sqlForUpdate[] = "CREATE TABLE IF NOT EXISTS `" .  $tbl_mdb_names['rel_course_class'] . "` (
                `courseId` varchar(40) NOT NULL,
                `classId` int(11) NOT NULL default '0',
                PRIMARY KEY  (`courseId`,`classId`) )
                TYPE=MyISAM ";

            if ( upgrade_apply_sql($sqlForUpdate) ) $step = set_upgrade_status($tool, $step+1);
            else return $step ;

        default :

            $step = set_upgrade_status($tool, 0);
            return $step;

    }

    return false;
}
*/
/**
 * Upgrade module (from main database) to 1.9
 * @return step value, 0 if succeed
 */
/*
function upgrade_main_database_module_to_19 ()
{
    $tbl_mdb_names = claro_sql_get_main_tbl();
    $tool = 'MODULE_19';

    switch( $step = get_upgrade_status($tool) )
    {
        case 1 :

            // module

            $sqlForUpdate[] = "CREATE TABLE IF NOT EXISTS `" . $tbl_mdb_names['module'] . "` (
              `id`         smallint    unsigned             NOT NULL auto_increment,
              `label`      char(8)                          NOT NULL default '',
              `name`       char(100)                        NOT NULL default '',
              `activation` enum('activated','desactivated') NOT NULL default 'desactivated',
              `type`       enum('tool','applet')            NOT NULL default 'applet',
              `script_url` char(255)                        NOT NULL default 'entry.php',
              PRIMARY KEY  (`id`)
            ) TYPE=MyISAM";

            $sqlForUpdate[] = "CREATE TABLE IF NOT EXISTS `".$tbl_mdb_names['module_info'] . "` (
              id             smallint     NOT NULL auto_increment,
              module_id      smallint     NOT NULL default '0',
              version        varchar(10)  NOT NULL default '',
              author         varchar(50)  default NULL,
              author_email   varchar(100) default NULL,
              author_website varchar(255) default NULL,
              description    varchar(255) default NULL,
              website        varchar(255) default NULL,
              license        varchar(50)  default NULL,
              PRIMARY KEY (id)
            ) TYPE=MyISAM AUTO_INCREMENT=0";

            $sqlForUpdate[]= "CREATE TABLE IF NOT EXISTS `" . $tbl_mdb_names['dock'] . "` (
              id        smallint unsigned NOT NULL auto_increment,
              module_id smallint unsigned NOT NULL default '0',
              name      varchar(50)          NOT NULL default '',
              rank      tinyint  unsigned NOT NULL default '0',
              PRIMARY KEY  (id)
            ) TYPE=MyISAM AUTO_INCREMENT=0";

            if ( upgrade_apply_sql($sqlForUpdate) ) $step = set_upgrade_status($tool, $step+1);
            else return $step ;

            unset($sqlForUpdate);

        case 3 :

            $sqlForUpdate[] = "UPDATE `" . $tbl_mdb_names['tool'] . "`
                             SET claro_label = TRIM(TRAILING '_' FROM claro_label )";

            $sqlForUpdate[] = "UPDATE `" . $tbl_mdb_names['tool'] . "`
                             SET `script_url` = SUBSTRING_INDEX( `script_url` , '/', -1 ) ";

            $sqlForUpdate[] = "UPDATE `" . $tbl_mdb_names['tool'] . "`
                             SET `script_url` = 'exercise.php' WHERE `script_url` = 'exercice.php' ";

            if ( upgrade_apply_sql($sqlForUpdate) ) $step = set_upgrade_status($tool, $step+1);
            else return $step ;

            unset($sqlForUpdate);

        case 4 :

            // include libray to manage module
            require_once $GLOBALS['includePath'] . '/lib/module/manage.lib.php';

            $error = false ;

            $sql = " SELECT id, claro_label, script_url, icon, def_access, def_rank, add_in_course, access_manager
                     FROM `" . $tbl_mdb_names['tool'] . "`";

            $toolList = claro_sql_query_fetch_all($sql);

            foreach ( $toolList as $tool )
            {
                $toolLabel = $tool['claro_label'];

                // get module path, for read module manifest
                $toolPath = get_module_path($toolLabel);

                if ( ( $toolInfo = readModuleManifest($toolPath) ) !== false )
                {
                    // get script url
                    if (isset($toolInfo['ENTRY']))
                    {
                        $script_url = $toolInfo['ENTRY'];
                    }
                    else
                    {
                        $script_url = 'entry.php';
                    }
                }
                else
                {
                    // init toolInfo
                    $toolInfo['LABEL'] = $tool['claro_label'];
                    $toolInfo['NAME'] = $tool['claro_label'];
                    $toolInfo['TYPE'] = 'tool';
                    $toolInfo['VERSION'] = '1.9';
                    $toolInfo['AUTHOR']['NAME'] = '' ;
                    $toolInfo['AUTHOR']['EMAIL'] = '' ;
                    $toolInfo['AUTHOR']['WEB'] = '' ;
                    $toolInfo['DESCRIPTION'] = '';
                    $toolInfo['LICENSE'] = 'unknown' ;
                    $script_url = $tool['script_url'];
                }

                // fill table module and module_info
                // code from register_module_core from inc/lib/module.manage.lib.php

                $sql = "INSERT INTO `" . $tbl_mdb_names['module'] . "`
                        SET label      = '" . addslashes($toolInfo['LABEL']) . "',
                            name       = '" . addslashes($toolInfo['NAME']) . "',
                            type       = '" . addslashes($toolInfo['TYPE']) . "',
                            activation = 'activated' ,
                            script_url = '" . addslashes($script_url). "'";

                $moduleId = claro_sql_query_insert_id($sql);

                $sql = "INSERT INTO `" . $tbl_mdb_names['module_info'] . "`
                        SET module_id    = " . (int) $moduleId . ",
                            version      = '" . addslashes($toolInfo['VERSION']) . "',
                            author       = '" . addslashes($toolInfo['AUTHOR']['NAME'  ]) . "',
                            author_email = '" . addslashes($toolInfo['AUTHOR']['EMAIL' ]) . "',
                            website      = '" . addslashes($toolInfo['AUTHOR']['WEB'   ]) . "',
                            description  = '" . addslashes($toolInfo['DESCRIPTION'     ]) . "',
                            license      = '" . addslashes($toolInfo['LICENSE'         ]) . "'";

                if ( upgrade_sql_query($sql) === false )
                {
                    $error = true ;
                    break;
                }
            }

            if ( ! $error ) $step = set_upgrade_status($tool, $step+1);
            else return $step ;

        default :

            $step = set_upgrade_status($tool, 0);
            return $step;

    }

    return false;
}
*/
/**
 * Upgrade right (from main database) to 1.9
 * @return step value, 0 if succeed
 */
/*
function upgrade_main_database_right_to_19 ()
{
    include_once $GLOBALS['includePath'] . '/lib/right/right_profile.lib.php' ;
    include_once $GLOBALS['includePath'] . '/../install/init_profile_right.lib.php' ;

    $tbl_mdb_names = claro_sql_get_main_tbl();
    $tool = 'RIGHT_19';

    switch( $step = get_upgrade_status($tool) )
    {
        case 1 :

            // add right tables

            $sqlForUpdate[] = " CREATE TABLE IF NOT EXISTS `". $tbl_mdb_names['right_profile'] . "` (
               `profile_id` int(11) NOT NULL auto_increment,
               `type` enum('COURSE','PLATFORM') NOT NULL default 'COURSE',
               `name` varchar(255) NOT NULL default '',
               `label` varchar(50) NOT NULL default '',
               `description` varchar(255) default '',
               `courseManager` tinyint(4) default '0',
               `mailingList` tinyint(4) default '0',
               `userlistPublic` tinyint(4) default '0',
               `groupTutor` tinyint(4) default '0',
               `locked` tinyint(4) default '0',
               `required` tinyint(4) default '0',
               PRIMARY KEY  (`profile_id`),
               KEY `type` (`type`)
            )TYPE=MyISAM " ;

            $sqlForUpdate[] = "CREATE TABLE IF NOT EXISTS `".$tbl_mdb_names['right_action'] . "` (
               `id` int(11) NOT NULL auto_increment,
               `name` varchar(255) NOT NULL default '',
               `description` varchar(255) default '',
               `tool_id` int(11) default NULL,
               `rank` int(11) default '0',
               `type` enum('COURSE','PLATFORM') NOT NULL default 'COURSE',
               PRIMARY KEY  (`id`),
               KEY `tool_id` (`tool_id`),
               KEY `type` (`type`)
             )TYPE=MyISAM ";

             $sqlForUpdate[] = "CREATE TABLE IF NOT EXISTS `".$tbl_mdb_names['right_rel_profile_action'] . "` (
               `profile_id` int(11) NOT NULL,
               `action_id` int(11) NOT NULL,
               `courseId`  varchar(40) NOT NULL default '',
               `value` tinyint(4) default '0',
               PRIMARY KEY  (`profile_id`,`action_id`,`courseId`)
             ) TYPE=MyISAM ";

            if ( upgrade_apply_sql($sqlForUpdate) ) $step = set_upgrade_status($tool, $step+1);
            else return $step ;

            unset($sqlForUpdate);

        case 2 :

            create_required_profile();
            $step = set_upgrade_status($tool, $step+1);

        case 3 :

            // Init action/right

            $sql = " SELECT id
                     FROM `" . $tbl_mdb_names['tool'] . "`";

            $result = claro_sql_query_fetch_all_cols($sql);

            $toolIdList = $result['id'];

            foreach ( $toolIdList as $toolId)
            {
                // Manage right - Add read action
                $action = new RightToolAction();
                $action->setName('read');
                $action->setToolId($toolId);
                $action->save();

                // Manage right - Add edit action
                $action = new RightToolAction();
                $action->setName('edit');
                $action->setToolId($toolId);
                $action->save();
            }

            $step = set_upgrade_status($tool, $step+1);

        case 4 :

            init_default_right_profile();
            $step = set_upgrade_status($tool, $step+1);

        case 5 :

            // set profile_id in rel course_user
            $sqlForUpdate[] = "UPDATE `" . $tbl_mdb_names['rel_course_user'] . "` SET `profile_id` = " . claro_get_profile_id(USER_PROFILE) . "
                               WHERE `isCourseManager` = 0";

            $sqlForUpdate[] = "UPDATE `" . $tbl_mdb_names['rel_course_user'] . "` SET `profile_id` = " . claro_get_profile_id(MANAGER_PROFILE) . "
                               WHERE `isCourseManager` = 1";

            // set default profile_id in course

            if ( upgrade_apply_sql($sqlForUpdate) ) $step = set_upgrade_status($tool, $step+1);
            else return $step ;

            unset($sqlForUpdate);

        default :

            $step = set_upgrade_status($tool, 0);
            return $step;

    }

    return false;
}
*/

/**
 * Upgrade user_property to 1.9
 * @return step value, 0 if succeed
 */

function upgrade_main_database_user_property_to_19 ()
{
    $tbl_mdb_names = claro_sql_get_main_tbl();
    $tool = 'USERPROP_19';

    switch( $step = get_upgrade_status($tool) )
    {
        case 1 :

            // create tables

            $sqlForUpdate[]= "ALTER TABLE `" . $tbl_mdb_names['user_property'] . "` 
              DROP PRIMARY KEY,
              ADD PRIMARY KEY  (`scope`,`propertyId`,`userId`)
             ";

            if ( upgrade_apply_sql($sqlForUpdate) ) $step = set_upgrade_status($tool, $step+1);
            else return $step ;

            unset($sqlForUpdate);

        case 2 :

            // create tables

            $sqlForUpdate[]= "ALTER TABLE `" . $tbl_mdb_names['property_definition'] . "` 
              DROP PRIMARY KEY,
              PRIMARY KEY  (`contextScope`,`propertyId`)
              ";

            if ( upgrade_apply_sql($sqlForUpdate) ) $step = set_upgrade_status($tool, $step+1);
            else return $step ;

            unset($sqlForUpdate);

            default :

            $step = set_upgrade_status($tool, 0);
            return $step;
    }

 
}

/**
 * Upgrade tracking to 1.9
 * @return step value, 0 if succeed
 */

function upgrade_main_database_tracking_to_19 ()
{
    $tbl_mdb_names = claro_sql_get_main_tbl();
    $tool = 'TRACKING_19';

    switch( $step = get_upgrade_status($tool) )
    {
        case 1 :

            // create a new table
            $sqlForUpdate[] = "
                CREATE 
                 TABLE `" . $tbl_mdb_names['tracking_event'] . "`  (
                         `id` int(11) NOT NULL auto_increment,
                         `course_code` varchar(40) NULL DEFAULT NULL,
                         `tool_id` int(11) NULL DEFAULT NULL,
                         `user_id` int(11) NULL DEFAULT NULL,
                         `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                         `type` varchar(60) NOT NULL DEFAULT '',
                         `data` text NOT NULL DEFAULT '',
                         PRIMARY KEY  (`id`),
                         KEY `course_id` (`course_code`)
                       ) TYPE=MyISAM";
            if ( upgrade_apply_sql($sqlForUpdate) ) $step = set_upgrade_status($tool, $step+1);
            else return $step ;

            unset($sqlForUpdate);

        default :

            $step = set_upgrade_status($tool, 0);
            return $step;
    }

    return false;
}

?>