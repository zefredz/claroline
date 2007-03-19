<?php // $Id$
if ( count( get_included_files() ) == 1 ) die( '---' );
/**
 * CLAROLINE
 *
 * @version 1.8 $Revision$
 *
 * @copyright 2001-2006 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @see http://www.claroline.net/wiki/CLDSC/
 *
 * @author Claro Team <cvs@claroline.net>
 *
 * @package CLDSC
 *
 */


/**
 * get all the items
 *
 * @param $courseId string  glued dbName of the course to affect default: current course
 *
 * @return array of arrays with data of the item
 *
 * @author Christophe Gesché <moosh@claroline.net>
 *
 */

function course_description_get_item_list($courseId = null)
{
    $tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($courseId));
    $tblCourseDescription = $tbl['course_description'];

    $sql = "SELECT `id`,
                `category`,
                `title`,
                `content`,
                UNIX_TIMESTAMP(`lastEditDate`) AS `unix_lastEditDate`,
                `visibility`
        FROM `".$tblCourseDescription."`
        ORDER BY `category` ASC";

    return  claro_sql_query_fetch_all($sql);
}

/**
 * return the tips list
 */

function get_tiplistinit()
{
    include_once './tiplistinit.inc.php';
    return $tipList;
}

?>
