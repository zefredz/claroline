<?php // $Id$
if ( count( get_included_files() ) == 1 ) die( '---' );
/**
 * CLAROLINE
 *
 * Sql query to update main database
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
                $sql = "INSERT IGNORE INTO `" . $tbl_mdb_names['module_contexts'] . "`
                    SET `module_id` = ".(int) $tool['id'].", `context` = 'course'";
                    
                $success = upgrade_sql_query( $sql );
                
                if ( in_array( rtrim($tool['label'], '_'), $groupTools ) )
                {
                    $sql = "INSERT IGNORE INTO `" . $tbl_mdb_names['module_contexts'] . "`
                    SET `module_id` = ".(int) $tool['id'].", `context` = 'group'";
                    
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
        case 3:
            
            $sqlForUpdate[] = "ALTER IGNORE TABLE `" . $tbl_mdb_names['module'] . "` 
                CHANGE `type` `type` VARCHAR( 10 ) NOT NULL DEFAULT 'applet'";
            $sqlForUpdate[] = "UPDATE `" . $tbl_mdb_names['module'] . "`
                SET `name` = 'Announcements'
                WHERE `name`= 'Announcement'";
                        
            if ( upgrade_apply_sql($sqlForUpdate) ) $step = set_upgrade_status($tool, $step+1);
            else return $step ;

            unset($sqlForUpdate);
        
        case 4 :
            $sqlForUpdate[] = "UPDATE `" . $tbl_mdb_names['tool'] . "`
                SET `icon` = 'icon.png'
                WHERE 1";
                        
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

            // Add new column

            $sqlForUpdate[] = "ALTER IGNORE TABLE `" . $tbl_mdb_names['course'] . "`,
                                 ADD COLUMN `visibility` ENUM ('visible','invisible') DEFAULT 'invisible' NOT NULL  AFTER `visible`,
                                 ADD COLUMN `access`     ENUM ('public','private', 'platform') DEFAULT 'public' NOT NULL  after `visibility`,
                                 ADD COLUMN `registration` ENUM ('open','close') DEFAULT 'open' NOT NULL  AFTER `access`";
            if ( upgrade_apply_sql($sqlForUpdate) ) $step = set_upgrade_status($tool, $step+1);
            else return $step ;

            unset($sqlForUpdate);
        case 2 :

            // Add new column

            // Old value was treated like this
            // $courseDataList['visibility'         ] = (bool) (2 == $courseDataList['visible'] || 3 == $courseDataList['visible'] );
            // $courseDataList['registrationAllowed'] = (bool) (1 == $courseDataList['visible'] || 2 == $courseDataList['visible'] );

            $sqlForUpdate[] = "UPDATE `" . $tbl_mdb_names['course'] . "`
                                SET `visibility`   = 'visible',
                                    `access`       = IF(visible=2 OR visible=3,'public','private') ,
                                    `registration` = IF(visible=1 OR visible=2,'open','close')";
                                    
            if ( upgrade_apply_sql($sqlForUpdate) ) $step = set_upgrade_status($tool, $step+1);
            else return $step ;
            
            unset($sqlForUpdate);
            
        case 3 :
            // Remove the old column
            $sqlForUpdate[] = "ALTER IGNORE TABLE `" . $tbl_mdb_names['course'] . "`,
                                DROP COLUMN `visible`";

            if ( upgrade_apply_sql($sqlForUpdate) ) $step = set_upgrade_status($tool, $step+1);
            else return $step ;

            unset($sqlForUpdate);

        case 4 :

            // Rename `fake_code` column

            $sqlForUpdate[] = "ALTER IGNORE TABLE `" . $tbl_mdb_names['course'] . "`,
                               CHANGE `fake_code` `administrativeNumber` VARCHAR (255)  NULL";

            if ( upgrade_apply_sql($sqlForUpdate) ) $step = set_upgrade_status($tool, $step+1);
            else return $step ;

            unset($sqlForUpdate);
            
        case 5 :

            // Rename `department` columns

            $sqlForUpdate[] = "ALTER IGNORE TABLE `" . $tbl_mdb_names['course'] . "`,
                              CHANGE `departmentUrlName` `extLinkName` VARCHAR (180)  NULL";
            $sqlForUpdate[] = "ALTER IGNORE TABLE `" . $tbl_mdb_names['course'] . "`,
                              CHANGE `departmentUrl` `extLinkUrl` VARCHAR (30)  NULL";

            if ( upgrade_apply_sql($sqlForUpdate) ) $step = set_upgrade_status($tool, $step+1);
            else return $step ;

            unset($sqlForUpdate);
            
        case 6 :

            // Rename `language` column

            $sqlForUpdate[] = "ALTER IGNORE TABLE `" . $tbl_mdb_names['course'] . "`,
                               CHANGE `languageCourse` `language` VARCHAR (15) NOT NULL DEFAULT 'english'";

            if ( upgrade_apply_sql($sqlForUpdate) ) $step = set_upgrade_status($tool, $step+1);
            else return $step ;

            unset($sqlForUpdate);
        
        case 7 : 
    
            // rename enrollment_key column registrationKey
            
            $sqlForUpdate[] = "ALTER IGNORE TABLE `" . $tbl_mdb_names['course'] . "`,
                               CHANGE `enrollment_key` `registrationKey` VARCHAR (255) DEFAULT NULL";
            
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

            $sqlForUpdate[]= "ALTER IGNORE TABLE `" . $tbl_mdb_names['user_property'] . "` 
              DROP PRIMARY KEY,
              ADD PRIMARY KEY  (`scope`,`propertyId`,`userId`)
             ";

            if ( upgrade_apply_sql($sqlForUpdate) ) $step = set_upgrade_status($tool, $step+1);
            else return $step ;

            unset($sqlForUpdate);

        case 2 :

            // create tables

            $sqlForUpdate[]= "ALTER IGNORE TABLE `" . $tbl_mdb_names['property_definition'] . "` 
              DROP PRIMARY KEY,
              ADD PRIMARY KEY  (`contextScope`,`propertyId`)
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
                 TABLE IF NOT EXISTS `" . $tbl_mdb_names['tracking_event'] . "`  (
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
                 TABLE IF NOT EXISTS `" . $tbl_mdb_names['log'] . "`  (
                        `id` INT(11) NOT NULL AUTO_INCREMENT,
                        `course_code` VARCHAR(40) NULL DEFAULT NULL,
                        `tool_id` INT(11) NULL DEFAULT NULL,
                        `user_id` INT(11) NULL DEFAULT NULL,
                        `ip` VARCHAR(15) NULL DEFAULT NULL,
                        `date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
                        `type` VARCHAR(60) NOT NULL DEFAULT '',
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
                 TABLE IF NOT EXISTS `" . $tbl_mdb_names['im_message'] . "`  (
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
                 TABLE IF NOT EXISTS `" . $tbl_mdb_names['im_message_status'] . "`  (
                        `user_id` int(11) NOT NULL,
                        `message_id` int(11) NOT NULL,
                        `is_read` tinyint(4) NOT NULL default '0',
                        `is_deleted` tinyint(4) NOT NULL default '0',
                        PRIMARY KEY  (`user_id`,`message_id`)
                       ) ENGINE=MyISAM";
                       
            $sqlForUpdate[] = "
                CREATE 
                 TABLE IF NOT EXISTS `" . $tbl_mdb_names['im_recipient'] . "`  (
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
                 TABLE IF NOT EXISTS `" . $tbl_mdb_names['desktop_portlet'] . "`  (
                        `label` varchar(255) NOT NULL,
                        `name` varchar(255) NOT NULL,
                        `rank` int(11) NOT NULL,
                        `visibility` ENUM ('visible','invisible') DEFAULT 'visible' NOT NULL,
                        `activated` int(11) NOT NULL,
                        PRIMARY KEY  (`label`)
                       ) TYPE=MyISAM";
                       
            $sqlForUpdate[] = "
                CREATE 
                 TABLE IF NOT EXISTS `" . $tbl_mdb_names['desktop_portlet_data'] . "`  (
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

function upgrade_chat_to_19 ()
{
    global $includePath;
    // activate new module to replace the old one
    $tool = 'CLCHAT_19';

    switch( $step = get_upgrade_status($tool) )
    {
        case 1 :
            // install new chat
            list( $backLog, $moduleId ) = install_module($includePath . '/../../module/CLCHAT', true);
            
            log_message($backLog->output());
            
            if( $moduleId )
            {
                list( $backLog, $success ) = activate_module_in_platform($moduleId);
                
                log_message($backLog->output());
            }
            else
            {
                return $step;
            }
            
            if ( $success ) $step = set_upgrade_status($tool, $step+1);
            else return $step ;
            
        case 2 :
            // remove old chat
            $moduleId = get_module_data('CLCHT', 'id');
            
            list( $backLog, $success ) = uninstall_module( $moduleId );
            log_message($backLog->output());
            
            if ( $success ) $step = set_upgrade_status($tool, $step+1);
            else return $step ;
        default :

            $step = set_upgrade_status($tool, 0);
            return $step;
    }

    return false;
}
