<?php // $Id$
if ( count( get_included_files() ) == 1 ) die( '---' );
/**
 * CLAROLINE
 *
 * Sql query to update main database
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

function upgrade_main_database_to_18 ()
{
    $tbl_mdb_names = claro_sql_get_main_tbl();

    switch( $step = get_upgrade_status('MAINDB18') )
    {
        case 1 :    

            // course

            $sqlForUpdate[] = "ALTER IGNORE TABLE `" . $tbl_mdb_names['course'] . "` ADD `defaultProfileId` int(11) NOT NULL";
            
            if ( upgrade_apply_sql($sqlForUpdate) ) $step = set_upgrade_status($tool, $step+1);
            else return $step ;
           
        case 2 :
 
            // rel_course_user

            $sqlForUpdate[] = "ALTER IGNORE TABLE `" . $tbl_mdb_names['rel_course_user'] . "` ADD `profile_id` int(11) NOT NULL ";
            $sqlForUpdate[] = "ALTER IGNORE TABLE `" . $tbl_mdb_names['rel_course_user'] . "` ADD `count_user_enrol` int(11) NOT NULL default 0 ";
            $sqlForUpdate[] = "ALTER IGNORE TABLE `" . $tbl_mdb_names['rel_course_user'] . "` ADD `count_class_enrol` int(11) NOT NULL default 0 ";
            $sqlForUpdate[] = "ALTER IGNORE TABLE `" . $tbl_mdb_names['rel_course_user'] . "` ADD `isCourseManager` tinyint(4) NOT NULL default 0 "; 
                
            // `statut` tinyint(4) NOT NULL default '5' --> `isCourseManager` tinyint(4) NOT NULL default 0

            $sqlForUpdate[] = "UPDATE `" . $tbl_mdb_names['rel_course_user'] . "`
                               SET `isCourseManager` = 1
                               WHERE `statut` = 1 ";
            
            $sqlForUpdate[] = "ALTER IGNORE TABLE `" . $tbl_mdb_names['rel_course_user'] . "` DROP COLUMN `statut` "; 
            
            if ( upgrade_apply_sql($sqlForUpdate) ) $step = set_upgrade_status($tool, $step+1);
            else return $step ;

        case 3 :

            // course category

            $sqlForUpdate[] = "ALTER IGNORE TABLE `" . $tbl_mdb_names['category'] . "` DROP COLUMN `bc` ";
            $sqlForUpdate[] = "ALTER IGNORE TABLE `" . $tbl_mdb_names['category'] . "` CHANGE `nb_childs` `nb_childs` smallint(6) default 0";
            
            if ( upgrade_apply_sql($sqlForUpdate) ) $step = set_upgrade_status($tool, $step+1);
            else return $step ;
            
        case 4 :

            // user

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

            // TODO `isPlatformAdmin` --> from admin table
            $sqlForUpdate[] = "ALTER IGNORE TABLE `" . $tbl_mdb_names['user'] . "` ADD `isPlatformAdmin`  tinyint(4) default 0";
            
            if ( upgrade_apply_sql($sqlForUpdate) ) $step = set_upgrade_status($tool, 5);
            else return $step ;

        case 5 :

            // course class

            $sqlForUpdate[] = "CREATE TABLE IF NOT EXISTS `" .  $tbl_mdb_names['rel_course_class'] . "` (
                `courseId` varchar(40) NOT NULL,
                `classId` int(11) NOT NULL default '0',
                PRIMARY KEY  (`courseId`,`classId`) ";
            
            if ( upgrade_apply_sql($sqlForUpdate) ) $step = set_upgrade_status($tool, $step+1);
            else return $step ;

        case 6 :

            // module
             
            $sqlForUpdate[] = "CREATE TABLE `" . $tbl_mdb_names['module'] . "` (
              `id`         smallint    unsigned             NOT NULL auto_increment,
              `label`      char(8)                          NOT NULL default '',
              `name`       char(100)                        NOT NULL default '',
              `activation` enum('activated','desactivated') NOT NULL default 'desactivated',
              `type`       enum('tool','applet')            NOT NULL default 'applet',
              `script_url` char(255)                        NOT NULL default 'entry.php',
              PRIMARY KEY  (`id`)
            ) TYPE=MyISAM";
            
            $sqlForUpdate[] = "CREATE TABLE `".$tbl_mdb_names['module_info'] . "` (
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
            
            $sqlForUpdate[]= "CREATE TABLE `" . $tbl_mdb_names['dock'] . "` (
              id        smallint unsigned NOT NULL auto_increment,
              module_id smallint unsigned NOT NULL default '0',
              name      varchar(50)          NOT NULL default '',
              rank      tinyint  unsigned NOT NULL default '0',
              PRIMARY KEY  (id)
            ) TYPE=MyISAM AUTO_INCREMENT=0";
            
            $sqlForUpdate[]= "CREATE TABLE `" . $tbl_mdb_names['module_tool'] . "` (
              id        smallint  unsigned NOT NULL auto_increment,
              module_id smallint  unsigned NOT NULL,
              entry     varchar(255) NOT NULL default 'entry.php',
              icon      varchar(255) NOT NULL default 'icon.png',
              PRIMARY KEY  (id)
            ) TYPE=MyISAM COMMENT='based definiton of the claroline tool'" ;
            
            if ( upgrade_apply_sql($sqlForUpdate) ) $step = set_upgrade_status($tool, $step+1);
            else return $step ;

        case 7 :

            // add right table

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

        default :
            $step = set_upgrade_status($tool, 0);
            return $step;
    
    }
  	
    return false;
}

?>
