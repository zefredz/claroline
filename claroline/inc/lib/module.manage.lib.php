<?php // $Id$
if ( count( get_included_files() ) == 1 ) die( '---' );
/**
 * CLAROLINE
 *
 * manage module of the system
 *
 * @version 1.8 $Revision$
 *
 * @copyright 2001-2006 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @see http://www.claroline.net/wiki/index.php/Install
 *
 * @author Claro Team <cvs@claroline.net>
 *
 * @package MODULES
 *
 */

 // code of module.inc.php will come here.

include_once(realpath(dirname(__FILE__) . '/../../admin/module') . '/module.inc.php');

/**
 * move the place of the module in the module list
 * (it changes the value of def_rank in the course_tool table)
 * @param $moduleId id of the module tool to move
 * @param $sense should either 'up' or 'down' to know in which direction the module has to move in the list
 */


function move_module_tool($toolId, $sense)
{
   $tbl_mdb_names        = claro_sql_get_main_tbl();
   $tbl_tool_list        = $tbl_mdb_names['tool'];

   $current_rank = get_course_tool_rank($toolId);

   switch($sense)
   {
        case 'up':
        {
            $min_rank = get_course_tool_min_rank();
            if ($current_rank == $min_rank) //do not allow to move up if this is the first in the list
            {
                return false;
            }
            else
            {
                $before_rank = get_before_course_tool($current_rank);

                //SWAP the two ranks

                $sql = "UPDATE `".$tbl_tool_list."`
                        SET def_rank = '".$current_rank."' WHERE def_rank = '".$before_rank."'";
                claro_sql_query($sql);

                $sql = "UPDATE `".$tbl_tool_list."`
                        SET def_rank = '".$before_rank."' WHERE id = '".$toolId."'";
                claro_sql_query($sql);

            }
        }
        break;

        case 'down':
        {
            $max_rank = get_course_tool_max_rank();
            if ($current_rank == $max_rank) //do not allow to move down if this is the last in the list
            {
                return false;
            }
            else
            {
                $next_rank = get_next_course_tool($current_rank);

                //SWAP the two ranks

                $sql = "UPDATE `".$tbl_tool_list."`
                        SET def_rank = ".$current_rank." WHERE def_rank = ".$next_rank;
                claro_sql_query($sql);

                $sql = "UPDATE `".$tbl_tool_list."`
                        SET def_rank = ".$next_rank." WHERE id = ".$toolId;
                claro_sql_query($sql);
            }
        }
        break;
   }
}

/**
 * 
 * @param id of the course_tool module to get rank
 * @return the value of the rank (def_rank table)
 */

function get_course_tool_rank($toolId)
{
    $tbl_mdb_names        = claro_sql_get_main_tbl();
    $tbl_tool_list        = $tbl_mdb_names['tool'];

    $sql = "SELECT def_rank 
            FROM `" . $tbl_tool_list . "`
            WHERE id=".(int)$toolId;
    return claro_sql_query_get_single_value($sql);
}

/**
 *
 * @return integer the value of the rank of the course_tool just after the course tool of rank $rank in the list
 */

function get_next_course_tool($rank)
{
    $tbl_mdb_names        = claro_sql_get_main_tbl();
    $tbl_tool_list        = $tbl_mdb_names['tool'];

    $sql = "SELECT def_rank
            FROM `" . $tbl_tool_list . "`
            WHERE (def_rank>".(int)$rank.") ORDER BY def_rank";

    $result = claro_sql_query_get_single_value($sql);

    return $result;
}

/**
 *
 * @return integer the value of the rank of the course_tool just before the course toolof rank $rank in the list
 */

function get_before_course_tool($rank)
{
    $tbl_mdb_names        = claro_sql_get_main_tbl();
    $tbl_tool_list        = $tbl_mdb_names['tool'];

    $sql = "SELECT def_rank 
            FROM `" . $tbl_tool_list . "`
            WHERE (def_rank<".(int)$rank.") ORDER BY def_rank DESC";
    return claro_sql_query_get_single_value($sql);
}


/**
 * get maximum position already used in the course_tool of the def_rank value
 * 
 * @return : the maximum value
 */

function get_course_tool_max_rank()
{
    $tbl_mdb_names        = claro_sql_get_main_tbl();
    $tbl_tool_list        = $tbl_mdb_names['tool'];

    $sql = "SELECT MAX(def_rank) as maxrank FROM `" . $tbl_tool_list . "`";
    return claro_sql_query_get_single_value($sql);
}

/**
 * get minimum position already used in the course_tool of the def_rank value
 * 
 * @return : the minimum value
 */

function get_course_tool_min_rank()
{
    $tbl_mdb_names        = claro_sql_get_main_tbl();
    $tbl_tool_list        = $tbl_mdb_names['tool'];

    $sql = "SELECT MIN(def_rank) as minrank FROM `" . $tbl_tool_list . "`";
    return claro_sql_query_get_single_value($sql);
}


?>