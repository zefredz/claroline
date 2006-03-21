<?php // $Id$
/**
 * CLAROLINE
 *
 * - For a Student -> View agenda Content
 * - For a Prof    -> - View agenda Content
 *         - Update/delete existing entries
 *         - Add entries
 *         - generate an "announce" entries about an entries
 *
 * @version 1.7 $Revision$
 *
 * @copyright (c) 2001-2005 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @package CLCAL
 *
 * @author Claro Team <cvs@claroline.net>
 */

/**
 * This function retrun to kernel context that this plugin support.
 * This is probably redudant with a future value of the manifest.
 *
 * @return unknown
 */
function CLCAL_aivailable_context_tool()
{
    return array('course');
}

/**
 * install work space for tool in the given course
 * @param cours_code $course_id id of course where do the work
 * @return true
 */
function CLCAL_install_tool($context,$course_id)
{
    $tbl_cdb_names    = claro_sql_get_course_tbl(claro_get_course_db_name_glued($course_id));

    $sql = "
    CREATE TABLE `" . $tbl_cdb_names['calendar_event'] . "` (
        `id` int(11) NOT NULL auto_increment,
        `titre` varchar(200),
        `contenu` text,
        `day` date NOT NULL default '0000-00-00',
        `hour` time NOT NULL default '00:00:00',
        `lasting` varchar(20),
        `visibility` enum('SHOW','HIDE') NOT NULL default 'SHOW',
    PRIMARY KEY (id))";
        // Agenda
    claro_sql_query($sql);

}

/**
 * @param cours_code $course_id id of course where do the work
 * @return true
 */
function CLCAL_enable_tool($context,$course_id)
{
    return true;
}

/**
 * @param cours_code $course_id id of course where do the work
 * @return true
 */
function CLCAL_disable_tool($context,$course_id)
{
    return true;
}

/**
 * @param cours_code $course_id id of course where do the work
 * @return true
 */
function CLCAL_export_tool($context,$course_id)
{
    return true;
}
?>