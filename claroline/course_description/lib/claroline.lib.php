<?php // $Id$
/**
 * CLAROLINE
 *
 * Course description
 *
 * @version 1.7 $Revision$
 *
 * @copyright 2001-2005 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @see http://www.claroline.net/wiki/index.php/CLDSC
 *
 * @package CLDSC
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
function CLDSC_aivailable_context_tool()
{
    return array(CLARO_CONTEXT_COURSE);
}

/**
 * install work space for course description tool in the given course
 * @param cours_code $course_id id of course where do the work
 * @return true
 */
function CLDSC_install_tool($context,$course_id)
{
    $tbl_cdb_names = claro_sql_get_course_tbl(claro_get_course_db_name_glued($course_id));
    $sql = "
    CREATE TABLE `" . $tbl_cdb_names['course_description'] . "` (
        `id` TINYINT UNSIGNED DEFAULT '0' NOT NULL,
        `title` VARCHAR(255),
        `content` TEXT,
        `upDate` DATETIME NOT NULL,
        `visibility` enum('SHOW','HIDE') NOT NULL default 'SHOW',
        UNIQUE (`id`)
    )
    COMMENT = 'for course description tool';";

    claro_sql_query($sql);

}

/**
 * @param cours_code $course_id id of course where do the work
 * @return true
 */
function CLDSC_enable_tool($context,$course_id)
{
    return true;
}

/**
 * @param cours_code $course_id id of course where do the work
 * @return true
 */
function CLDSC_disable_tool($context,$course_id)
{
    return true;
}

/**
 * @param cours_code $course_id id of course where do the work
 * @return true
 */
function CLDSC_export_tool($context,$course_id)
{
    return true;
}
?>