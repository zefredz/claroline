<?php // $Id$
/**
 * CLAROLINE
 *
 * The lib provide claroline kernel library extention for 'annoucement' tools
 *
 * DB Table structure:
 * ---
 *
 * id         : announcement id
 * contenu    : announcement content
 * temps      : date of the announcement introduction / modification
 * title      : optionnal title for an announcement
 * ordre      : order of the announcement display
 *              (the announcements are display in desc order)
 *
 * @version 1.8 $Revision$
 *
 * @copyright (c) 2001-2005 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @package CLANN
 *
 * @author Claro Team <cvs@claroline.net>
 * @author Christophe Gesché <moosh@claroline.net>
 */

class CLANN
{
    function aivailable_context_tool()
    {
        return array(CLARO_CONTEXT_COURSE);
    }

    /**
     * install work space for announcement tool in the given course
     * @param cours_code $course_id id of course where do the work
     * @return true
     */
    function install_tool($context,$course_id)
    {

        if (CLARO_CONTEXT_COURSE == $context)
        {
        $tbl_cdb_names = claro_sql_get_course_tbl(claro_get_course_db_name_glued($course_id));

        $sql =" #tbl_cdb_names['announcement']
                CREATE TABLE `" . $tbl_cdb_names['announcement'] . "` (
                  `id` mediumint(11) NOT NULL auto_increment,
                  `title` varchar(80) default NULL,
                  `contenu` text,
                  `temps` date default NULL,
                  `ordre` mediumint(11) NOT NULL default '0',
                  `visibility` enum('SHOW','HIDE') NOT NULL default 'SHOW',
                  PRIMARY KEY  (`id`)
                ) TYPE=MyISAM COMMENT='announcements table'";
        return claro_sql_query($sql);
    }
    }

    /**
     * @param cours_code $course_id id of course where do the work
     * @return true
     */
    function enable_tool($context,$course_id)
    {
            return true;
    }

    /**
     * @param cours_code $course_id id of course where do the work
     * @return true
     */
    function disable_tool($context,$course_id)
    {
            return true;
    }

    /**
     * @param cours_code $course_id id of course where do the work
     * @return true
     */
    function export_tool($context,$course_id)
    {
            return true;
    }
}
?>