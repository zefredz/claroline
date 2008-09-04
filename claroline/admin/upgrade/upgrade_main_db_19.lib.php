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
 * @author Christophe Gesch� <moosh@claroline.net>
 *
 */


/*===========================================================================
 Upgrade to claroline 1.9
 ===========================================================================*/

function upgrade_main_database_module_to_19 ()
{
    $tbl_mdb_names = claro_sql_get_main_tbl();
    $tool = 'MODULE_19';

    switch( $step = get_upgrade_status($tool) )
    {           
        case 1 :

            // module
            $sqlForUpdate[] = "CREATE TABLE IF NOT EXISTS `" . $tbl_mdb_names['module_contexts'] . "` (
                module_id INTEGER UNSIGNED NOT NULL,
                context VARCHAR(60) NOT NULL DEFAULT 'course',
                PRIMARY KEY(`module_id`,`context`)
               ) TYPE=MyISAM";
                        
            if ( upgrade_apply_sql($sqlForUpdate) ) $step = set_upgrade_status($tool, $step+1);
            else return $step ;

            unset($sqlForUpdate);

        case 2 :
            
            $sql = "SELECT `id`, `label` FROM `" . $tbl_mdb_names['module'] . "`";
            
            $toolList = claro_sql_query_fetch_all_rows( $sql );
            
            $groupTools = array('CLDOC','CLWIKI','CLCHT', 'CLFRM');
            
            foreach ( $toolList as $tool )
            {
                $sql = "INSERT INTO `" . $tbl_mdb_names['module_contexts'] . "`
                    SET module_id = ".(int) $tool['id'].", CONTEXT = 'course'";
                    
                $success = upgrade_sql_query( $sql );
                
                if ( $success && in_array( $tool['id'], $groupTools ) )
                {
                    $sql = "INSERT INTO `" . $tbl_mdb_names['module_contexts'] . "`
                    SET module_id = ".(int) $tool['id'].", CONTEXT = 'group'";
                    
                    $success = upgrade_sql_query( $sql );
                }
                
                if ( ! $success )
                {
                    break;
                }
            }
            
            if ( $success ) $step = set_upgrade_status($tool, $step+1);
            else return $step ;
            
            unset($sqlForUpdate);

        default :

            $step = set_upgrade_status($tool, 0);
            return $step;
    
    }
      
    return false;    
}

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
                               CHANGE `fake_code` `administrativeNumber` VARCHAR (255)  NULL";

            if ( upgrade_apply_sql($sqlForUpdate) ) $step = set_upgrade_status($tool, $step+1);
            else return $step ;

            unset($sqlForUpdate);
        case 6 :

            // Rename `department` columns

            $sqlForUpdate[] = "ALTER IGNORE TABLE `" . $tbl_mdb_names['course'] . "`,
                              CHANGE `departmentUrlName` `extLinkName` VARCHAR (180)  NULL";
            $sqlForUpdate[] = "ALTER IGNORE TABLE `" . $tbl_mdb_names['course'] . "`,
                              CHANGE `departmentUrl` `extLinkUrl` VARCHAR (30)  NULL";

            if ( upgrade_apply_sql($sqlForUpdate) ) $step = set_upgrade_status($tool, $step+1);
            else return $step ;

            unset($sqlForUpdate);
        case 7 :

            // Rename `language` column

            $sqlForUpdate[] = "ALTER IGNORE TABLE `" . $tbl_mdb_names['course'] . "`,
                               CHANGE `languageCourse` `language` VARCHAR (15)  'english'";

            if ( upgrade_apply_sql($sqlForUpdate) ) $step = set_upgrade_status($tool, $step+1);
            else return $step ;

            unset($sqlForUpdate);
        case 8:
            $sqlForUpdate[] = "ALTER IGNORE TABLE `" . $tbl_mdb_names['course'] . "`
                               ADD `visibility` ENUM ('visible','invisible') DEFAULT 'visible' NOT NULL";
            $sqlForUpdate[] = "ALTER IGNORE TABLE `" . $tbl_mdb_names['course'] . "`
                               CHANGE `access` `access` ENUM( 'public', 'private', 'platform' ) NOT NULL DEFAULT 'public'";

            if ( upgrade_apply_sql($sqlForUpdate) ) $step = set_upgrade_status($tool, $step+1);
            else return $step ;

            unset($sqlForUpdate);
        default :

            $step = set_upgrade_status($tool, 0);
            return $step;

    }
}

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
        
        case 2 :

            // create a new table
            $sqlForUpdate[] = "
                CREATE 
                 TABLE `" . $tbl_mdb_names['log'] . "`  (
                        `id` INT(11) NOT NULL AUTO_INCREMENT,
                        `course_code` VARCHAR(40) NULL DEFAULT NULL,
                        `tool_id` INT(11) NULL DEFAULT NULL,
                        `user_id` INT(11) NULL DEFAULT NULL,
                        `ip` VARCHAR(15) NULL DEFAULT NULL,
                        `date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
                        `type` VARCHAR(60) NOT NULL DEFAULT ,
                        `data` text NOT NULL DEFAULT ,
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

/**
 * Upgrade messaging to 1.9
 * @return step value, 0 if succeed
 */

function upgrade_main_database_messaging_to_19 ()
{
    $tbl_mdb_names = claro_sql_get_main_tbl();
    $tool = 'MESSAGING_19';

    switch( $step = get_upgrade_status($tool) )
    {
        case 1 :

            // create a new table
            $sqlForUpdate[] = "
                CREATE 
                 TABLE `" . $tbl_mdb_names['im_message'] . "`  (
                        `message_id` int(10) unsigned NOT NULL auto_increment,
                        `sender` int(11) NOT NULL,
                        `subject` varchar(100) NOT NULL,
                        `message` text NOT NULL,
                        `send_time` datetime NOT NULL default '0000-00-00 00:00:00',
                        `course` varchar(40) default NULL,
                        `group` int(11) default NULL,
                        `tools` char(8) default NULL,
                        PRIMARY KEY  (`message_id`)
                       ) ENGINE=MyISAM";
            $sqlForUpdate[] = "
                CREATE 
                 TABLE `" . $tbl_mdb_names['im_message_status'] . "`  (
                        `user_id` int(11) NOT NULL,
                        `message_id` int(11) NOT NULL,
                        `is_read` tinyint(4) NOT NULL default '0',
                        `is_deleted` tinyint(4) NOT NULL default '0',
                        PRIMARY KEY  (`user_id`,`message_id`)
                       ) ENGINE=MyISAM";
            $sqlForUpdate[] = "
                CREATE 
                 TABLE `" . $tbl_mdb_names['im_recipient'] . "`  (
                        `message_id` int(11) NOT NULL,
                        `user_id` int(11) NOT NULL,
                        `sent_to` enum('toUser','toGroup','toCourse','toAll') NOT NULL,
                        PRIMARY KEY  (`message_id`,`user_id`)
                       ) ENGINE=MyISAM";
            
            if ( upgrade_apply_sql($sqlForUpdate) ) $step = set_upgrade_status($tool, $step+1);
            else return $step ;

            unset($sqlForUpdate);
            
        default :

            $step = set_upgrade_status($tool, 0);
            return $step;
    }

    return false;
}

/**
 * Upgrade desktop to 1.9
 * @return step value, 0 if succeed
 */

function upgrade_main_database_desktop_to_19 ()
{
    $tbl_mdb_names = claro_sql_get_main_tbl();
    $tool = 'DESKTOP_19';

    switch( $step = get_upgrade_status($tool) )
    {
        case 1 :

            // create a new table
            $sqlForUpdate[] = "
                CREATE 
                 TABLE `" . $tbl_mdb_names['desktop_portlet'] . "`  (
                        `label` varchar(255) NOT NULL,
                        `name` varchar(255) NOT NULL,
                        `rank` int(11) NOT NULL,
                        `visibility` ENUM ('visible','invisible') DEFAULT 'visible' NOT NULL,
                        `activated` int(11) NOT NULL,
                        PRIMARY KEY  (`label`)
                       ) TYPE=MyISAM";
            $sqlForUpdate[] = "
                CREATE 
                 TABLE `" . $tbl_mdb_names['desktop_portlet_data'] . "`  (
                        `id` INT(11) NOT NULL AUTO_INCREMENT,
                        `label` varchar(255) NOT NULL,
                        `idUser` int(11) NOT NULL,
                        `data` text NOT NULL,
                        PRIMARY KEY  (`id`)
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