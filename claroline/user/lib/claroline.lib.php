<?php // $Id$
/**
 * CLAROLINE
 *
 * The lib provide claroline kernel library extention for 'user' tools
 *
 * @version 1.7 $Revision$
 *
 * @copyright (c) 2001-2005 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @package CLUSR
 *
 * @author Claro Team <cvs@claroline.net>
 * @author Christophe Gesché <moosh@claroline.net>
 */


/**
 * This function retrun to kernel context that this plugin support.
 * This is probably redudant with a future value of the manifest.
 *
 * @return unknown
 */
function CLUSR_aivailable_context_tool()
{
    return array('course');
}

/**
 * install work space for tool in the given course
 * @param cours_code $course_id id of course where do the work
 * @return true
 */
function CLUSR_install_tool($context,$course_id)
{
    $tbl_cdb_names = claro_sql_get_course_tbl(claro_get_course_db_name_glued($course_id));
    $TABLETOOLUSERINFOCONTENT    = $tbl_cdb_names['userinfo_content'];// $courseDbName."userinfo_content";
    $TABLETOOLUSERINFODEF        = $tbl_cdb_names['userinfo_def'];// $courseDbName."userinfo_def";

    $sql ="
CREATE TABLE `".$TABLETOOLUSERINFOCONTENT."` (
   `id` int(10) unsigned NOT NULL auto_increment,
   `user_id` mediumint(8) unsigned NOT NULL default '0',
   `def_id` int(10) unsigned NOT NULL default '0',
   `ed_ip` varchar(39) default NULL,
   `ed_date` datetime default NULL,
   `content` text,
   PRIMARY KEY  (`id`),
   KEY `user_id` (`user_id`)
) TYPE=MyISAM COMMENT='content of users information - organisation based on
userinf'";

claro_sql_query($sql);

    $sql ="
CREATE TABLE `".$TABLETOOLUSERINFODEF."` (
   `id` int(10) unsigned NOT NULL auto_increment,
   `title` varchar(80) NOT NULL default '',
   `comment` varchar(160) default NULL,
   `nbLine` int(10) unsigned NOT NULL default '5',
   `rank` tinyint(3) unsigned NOT NULL default '0',
   PRIMARY KEY  (`id`)
) TYPE=MyISAM COMMENT='categories definition for user information of a course'";
claro_sql_query($sql);



}

/**
 * @param cours_code $course_id id of course where do the work
 * @return true
 */
function CLUSR_enable_tool($context,$course_id)
{

        return true;
}

/**
 * @param cours_code $course_id id of course where do the work
 * @return true
 */
function CLUSR_disable_tool($context,$course_id)
{
        return true;
}

/**
 * @param cours_code $course_id id of course where do the work
 * @return true
 */
function CLUSR_export_tool($context,$course_id)
{
        return true;
}
?>