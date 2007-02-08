<?php // $Id$
if ( count( get_included_files() ) == 1 ) die( '---' );
/**
 * CLAROLINE
 *
 * @version 1.8 $Revision$
 *
 * @copyright 2001-2007 Universite catholique de Louvain (UCL)
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

function course_description_get_item_list($courseId=null)
{
    /*
    $c = claro_get_course_data($courseId);
    $tbl = claro_sql_get_tbl('course_description',array(CLARO_CONTEXT_TOOLLABEL => 'CLDSC'
                                                       ,CLARO_CONTEXT_COURSE    => $c['sysCode']));
                                                       */
    $tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($courseId));

    $sql = "SELECT `id`, `title`, `content` , `visibility`
            FROM `" . $tbl['course_description'] . "`
            ORDER BY `id`";
    return  claro_sql_query_fetch_all($sql);
}

/**
 * Get the item of the given id.
 *
 * @param $descId   integer id of the item to get
 * @param $courseId string  glued dbName of the course to affect default: current course
 *
 * @return array with data of the item
 *
 * @author Christophe Gesché <moosh@claroline.net>
 *
*/

function course_description_get_item($descId, $courseId=null)
{
    $tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($courseId));

    $sql = 'SELECT `id`, `title`, `content`, `visibility`
            FROM `' . $tbl['course_description'] . '`
            WHERE id = ' . (int) $descId ;

    $descItem = claro_sql_query_get_single_row($sql);
    return $descItem;
}

/**
 * remove the item of the given id.
 *
 * @param $descId   integer id of the item to delete
 * @param $courseId string  glued dbName of the course to affect default: current course
 *
 * @return result of query
 *
 * @author Christophe Gesché <moosh@claroline.net>
 *
 */

function course_description_delete_item($descId, $courseId=null)
{
    $tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($courseId));

    $sql = 'DELETE FROM `' . $tbl['course_description'] . '`
            WHERE id = ' . (int) $descId;

    return  claro_sql_query($sql);
}


/**
 * update values of the item of the given id.
 *
 * @param $descId       integer id of the item to update
 * @param $descTitle    string Title of the item
 * @param $descContent  string Content of the item
 * @param $courseId    string  glued dbName of the course to affect default: current course
 *
 * @return result of query
 *
 * @author Christophe Gesché <moosh@claroline.net>
 *
 */

function course_description_set_item($descId , $descTitle , $descContent, $courseId=null)
{
    $tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($courseId));

    $sql = "UPDATE `" . $tbl['course_description']."`
               SET   `title`   = '" . addslashes($descTitle) . "',
                     `content` = '" . addslashes($descContent) . "',
                     `upDate`  = NOW()
               WHERE `id` = " . (int) $descId ;

    return claro_sql_query($sql);
}


/**
 * insert values in a new item
 *
 * @param integer $id id of the item (-1 for a new)
 * @param string $descTitle    Title of the item
 * @param string $descContent  Content of the item
 * @param int    $maxBloc      size of predefined set of blocs
 * @param string $courseId    glued dbName of the course to affect default: current course
 *
 * @return integer id of the new item
 *
 * @author Christophe Gesché <moosh@claroline.net>
 *
 */
function course_description_add_item($descId,$descTitle,$descContent,$maxBloc,$courseId=null)
{
    $tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($courseId));
    if ( $descId < 0 )
    {
        $sql = "SELECT MAX(id)
                FROM `" . $tbl['course_description'] . "` ";
        $maxId = claro_sql_query_get_single_value($sql);
        $descId = max( (int) $maxBloc, $maxId + 1);
    }

    $sql ="INSERT INTO `" . $tbl['course_description'] . "`
               SET   `title`   = '" . addslashes($descTitle  ) . "',
                     `content` = '" . addslashes($descContent) . "',
                     `upDate`  = NOW(),
                     `id` = " . (int) ($descId);

    if (claro_sql_query($sql))return (int) $descId;
    else                      return false;

}

/**
 * insert values in a new item
 *
 * @param $descTitle    string Title of the item
 * @param $cmd          string with command to hide or show item
 * @param $dbnameGlu    string  glued dbName of the course to affect default: current course
 *
 * @return integer id of the new item
 *
 * @author Christophe Gesché <moosh@claroline.net>
 *
 */
function course_description_visibility_item($descId, $cmd, $courseId=null)
{
    $tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($courseId));

    if ($cmd == 'mkShow')  $visibility = 'SHOW'; else $visibility = 'HIDE';
    if ($cmd == 'mkHide')  $visibility = 'HIDE'; else $visibility = 'SHOW';

    $sql = "UPDATE `" . $tbl['course_description'] . "`
               SET   `visibility`   = '" . $visibility . "'
               WHERE `id` = ". (int) $descId;

    return claro_sql_query($sql);
}

/**
 * return tips id of a new item
 *
 * @param $id integer id of the item
 *
 * @return integer tips id of the new item
 *
 * @author Christophe Gesché <moosh@claroline.net>
 *
 */

function course_description_get_tips_id($id)
{
    global $titreBloc;

    if ( $id >=0 && $id < sizeof($titreBloc) ) return $id;
    else                                       return -1;
}

?>