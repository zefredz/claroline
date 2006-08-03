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

function query_to_upgrade_main_database_to_18 ()
{
    $tbl_mdb_names = claro_sql_get_main_tbl();

// table used for claroline's modules

     $sqlForUpdate[]  = "CREATE TABLE IF NOT EXISTS `" . $tbl_mdb_names['module'] . "` (
       `id` int(11) NOT NULL auto_increment,
       `label` varchar(8) NOT NULL default '',
       `name` varchar(100) NOT NULL default '',
       `activation` enum('activated','desactivated') NOT NULL default 'desactivated',
       `type` enum('coursetool','applet') NOT NULL default 'applet',
       `module_info_id` int(11) NOT NULL default '0',
       PRIMARY KEY  (`id`)
     ) TYPE=MyISAM AUTO_INCREMENT=0";

     //table used to store claroline's modules complementary information

     $sqlForUpdate[]  = "CREATE TABLE IF NOT EXISTS `" . $tbl_mdb_names['module_info'] . "` (
       `id` int(11) NOT NULL auto_increment,
       `module_id` int(11) NOT NULL default '0',
       `version` varchar(10) NOT NULL default '',
       `author` varchar(50) default NULL,
       `author_email` varchar(100) default NULL,
       `website` varchar(255) default NULL,
       `description` varchar(255) default NULL,
       `license` varchar(50) default NULL,
       PRIMARY KEY  (`id`)
     ) TYPE=MyISAM AUTO_INCREMENT=0";

     //table used to store claroline's docks (where some content can be displayed by the modules)

     $sqlForUpdate[] = "CREATE TABLE IF NOT EXISTS `" . $tbl_mdb_names['dock'] . "` (
       `id` int(11) NOT NULL auto_increment,
       `module_id` int(11) NOT NULL default '0',
       `name` varchar(50) NOT NULL default '',
       `rank` int(11) NOT NULL default '0',
       PRIMARY KEY  (`id`)
     ) TYPE=MyISAM AUTO_INCREMENT=0";


    $sqlForUpdate[] = "ALTER IGNORE TABLE `" . $tbl_mdb_names['user'] . "` ADD `language` varchar(15) default NULL";
    $sqlForUpdate[] = "ALTER IGNORE TABLE `" . $tbl_mdb_names['user'] . "` ADD `officialEmail` varchar(255) default NULL AFTER `officialCode`";
    $sqlForUpdate[] = "ALTER IGNORE TABLE `" . $tbl_mdb_names['user'] . "` CHANGE `email` `email` varchar(255) default NULL";

    $sqlForUpdate[] = "ALTER IGNORE TABLE `" . $tbl_mdb_names['user'] . "` ADD `isPlatformAdmin`  tinyint(4) default 0";

    return $sqlForUpdate;
}
?>
