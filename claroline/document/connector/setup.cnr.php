<?php // $Id$
if ( count( get_included_files() ) == 1 ) die( '---' );
/**
 * CLAROLINE
 *
 * @version 1.8 $Revision$
 *
 * @copyright (c) 2001-2005 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @package CLDOC
 *
 * @author Claro Team <cvs@claroline.net>
 */

/**
 * This function retrun to kernel context that this plugin support.
 * This is probably redudant with a future value of the manifest.
 *
 * @return unknown
 */
function CLDOC_aivailable_context_tool()
{
    return array(CLARO_CONTEXT_COURSE);
}

/**
 * install work space for tool in the given course
 * @param cours_code $course_id id of course where do the work
 * @return true
 */
function CLDOC_install_tool($context,$contextData)
{
    if (CLARO_CONTEXT_COURSE == $context)
    {
        $tbl_cdb_names = claro_sql_get_course_tbl(claro_get_course_db_name_glued($contextData));

        $sql = "
        CREATE TABLE `" . $tbl_cdb_names['document'] . "` (
            id int(4) NOT NULL auto_increment,
            path varchar(255) NOT NULL,
            visibility char(1) DEFAULT 'v' NOT NULL,
            comment varchar(255),
        PRIMARY KEY (id))";
        claro_sql_query($sql);

        $courseRepository = claro_get_course_path($contextData);
        claro_mkdir(get_conf('coursesRepositorySys') . $courseRepository . '/document', CLARO_FILE_PERMISSIONS);
        return true;
    }
    elseif (CLARO_CONTEXT_GROUP == $context)
    {

        // Groups don't need table.
        $courseRepository = claro_get_course_path($contextData[CLARO_CONTEXT_COURSE]);
        $group = claro_get_group_data($contextData[CLARO_CONTEXT_GROUP],$contextData[CLARO_CONTEXT_COURSE]);
        claro_mkdir(get_conf('coursesRepositorySys') . $courseRepository .'/group/' . $group['directory'] . '/document', CLARO_FILE_PERMISSIONS);
        return true;
    }
    else return claro_failure::set_failure($context.'_not_implemented');


}

/**
 * @param cours_code $course_id id of course where do the work
 * @return true
 * @todo Example_document.pdf would com from another place
 */
function CLDOC_enable_tool($context,$contextData)
{
    global $coursesRepositorySys,$clarolineRepositorySys;
    $courseRepository = claro_get_course_path($contextData);
    copy($clarolineRepositorySys . 'document/Example_document.pdf', $coursesRepositorySys . $courseRepository . '/document/Example_document.pdf');

    return true;
}

/**
 * @param cours_code $course_id id of course where do the work
 * @return true
 */
function CLDOC_disable_tool($context,$contextData)
{
    return true;
}

/**
 * @param cours_code $course_id id of course where do the work
 * @return true
 */
function CLDOC_export_tool($context,$contextData)
{
    return true;
}
?>