<?php // $Id$
/**
 * CLAROLINE
 *
 * Chat install
 *
 * @version 1.7 $Revision$
 *
 * @copyright 2001-2005 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @see http://www.claroline.net/wiki/index.php/CLCHT
 *
 * @package CLCHT
 *
 * @author Claro Team <cvs@claroline.net>
 * @author Christophe Gesché <moosh@claroline.net>
 * @author Hugues Peeters <peeters@ipm.ucl.ac.be>
 *
 */


/**
 * This function retrun to kernel context that this plugin support.
 * This is probably redudant with a future value of the manifest.
 *
 * @return unknown
 */
function CLCHT_aivailable_context_tool()
{
    return array('course');
}

/**
 * install work space for chat tool in the given course
 * @param cours_code $course_id id of course where do the work
 * @return true
 */
function CLCHT_install_tool($context,$course_id)
{
    global $coursesRepositorySys;
    $courseRepository = claro_get_course_path($course_id);
    claro_mkdir($coursesRepositorySys . $courseRepository . '/chat', CLARO_FILE_PERMISSIONS);

}

/**
 * @param cours_code $course_id id of course where do the work
 * @return true
 */
function CLCHT_enable_tool($context,$course_id)
{
    return true;
}

/**
 * @param cours_code $course_id id of course where do the work
 * @return true
 */
function CLCHT_disable_tool($context,$course_id)
{
    return true;
}

/**
 * @param cours_code $course_id id of course where do the work
 * @return true
 */
function CLCHT_export_tool($context,$course_id)
{
    return true;
}
?>