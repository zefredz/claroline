<?php // $Id$

/**
 * Claroline extension modules course related functions
 *
 * @version     Claroline 1.12 $Revision$
 * @copyright   (c) 2001-2014, Universite catholique de Louvain (UCL)
 * @license     http://www.gnu.org/copyleft/gpl.html
 *      GNU GENERAL PUBLIC LICENSE version 2 or later
 * @author      Claro Team <cvs@claroline.net>
 * @package     kernel.module
 * @since       1.12
 */

/**
 * Is the given module installed in the given course ?
 * @param string $toolLabel module label
 * @param string $courseIdReq course code
 * @return boolean
 */
function is_module_installed_in_course( $toolLabel, $courseIdReq, $forceCacheRefresh = false )
{
    static $courseInstalledToolList = false;
    
    $toolId = get_tool_id_from_module_label( $toolLabel );
    
    if ( ! $courseInstalledToolList )
    {
        $courseInstalledToolList = array();
    }
    
    if ( ! isset($courseInstalledToolList[$courseIdReq]) || $forceCacheRefresh )
    {
        
        $courseInstalledToolList[$courseIdReq] = array();
    
        $tbl_cdb_names = claro_sql_get_course_tbl(
            claro_get_course_db_name_glued( $courseIdReq ) 
        );
        
        $tbl_course_tool_list = $tbl_cdb_names['tool'];

        $sql = "SELECT tool_id \n"
            . "FROM `{$tbl_course_tool_list}`\n"
            . "WHERE `installed` = 'true'"
            ;
        
        $result = claro_sql_query_fetch_all_rows($sql);
    
        foreach ( $result as $tool )
        {
            $courseInstalledToolList[$courseIdReq][$tool['tool_id']] = true;
        }
    
    }
    
    if ( isset( $courseInstalledToolList[$courseIdReq][$toolId] ) )
    {
        return true;
    }
    else
    {
        return false;
    }
}

/**
 * Is the given module installed in the given course ? This version does not call
 * claro_get_course_data and should be used in conjunction with get_user_course_list
 * @param string $toolLabel module label
 * @param array $course course( 'sysCode' => sysCode, 'db' => dbName
 * @return boolean
 */
function is_module_installed_in_course_lightversion( $toolLabel, $course, $forceCacheRefresh = false )
{
    static $courseInstalledToolList = false;
    
    $toolId = get_tool_id_from_module_label( $toolLabel );
    
    if ( ! $courseInstalledToolList )
    {
        $courseInstalledToolList = array();
    }
    
    $courseIdReq = $course['sysCode'];
    
    if ( ! isset($courseInstalledToolList[$courseIdReq]) || $forceCacheRefresh )
    {
        
        $courseInstalledToolList[$courseIdReq] = array();
    
        $tbl_cdb_names = claro_sql_get_course_tbl(
            get_conf('courseTablePrefix') . $course['db'] . get_conf('dbGlu')
        );
        
        $tbl_course_tool_list = $tbl_cdb_names['tool'];

        $sql = "SELECT tool_id \n"
            . "FROM `{$tbl_course_tool_list}`\n"
            . "WHERE `installed` = 'true'"
            ;
        
        $result = claro_sql_query_fetch_all_rows($sql);
    
        foreach ( $result as $tool )
        {
            $courseInstalledToolList[$courseIdReq][$tool['tool_id']] = true;
        }
    
    }
    
    if ( isset( $courseInstalledToolList[$courseIdReq][$toolId] ) )
    {
        return true;
    }
    else
    {
        return false;
    }
}

/**
 * Move the place of the module in the module list
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
   return true;
}

/**
 * Get the rank of the tool in the course tool list
 * @param int $toolId id of the course_tool module to get rank
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
 * @return int the rank of the course_tool just after the course tool of rank
 *  $rank in the list
 */
function get_next_course_tool( $rank )
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
 * @return integer the value of the rank of the course_tool just before the
 * course toolof rank $rank in the list
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
 * @return : the maximum value
 */
function get_course_tool_max_rank()
{
    $tbl = claro_sql_get_main_tbl();

    $sql = "SELECT MAX(def_rank) as maxrank FROM `" . $tbl['tool'] . "`";
    return claro_sql_query_get_single_value($sql);
}

/**
 * get minimum position already used in the course_tool of the def_rank value
 * @return : the minimum value
 */
function get_course_tool_min_rank()
{
    $tbl = claro_sql_get_main_tbl();

    $sql = "SELECT MIN(def_rank) as minrank FROM `" . $tbl ['tool'] . "`";
    return claro_sql_query_get_single_value($sql);
}


/**
 * Is the tool already installed in the course
 * @param int $toolId main tool id
 * @param string $courseId course code
 * @return boolean
 */
function course_tool_already_installed( $toolId, $courseId )
{
    $tbl_cdb_names = claro_sql_get_course_tbl( claro_get_course_db_name_glued($courseId) );
    $tblCourseToolList = $tbl_cdb_names['tool'];
    
    $sql = "SELECT `installed`\n"
        . "FROM `{$tblCourseToolList}`\n"
        . "WHERE tool_id = " . (int) $toolId
        ;
    
    return claro_sql_query_fetch_single_value($sql) == 'true';
}

/**
 * Is the tool already registered in the course
 * @param int $toolId main tool id
 * @param string $courseId course code
 * @return boolean
 */
function is_tool_registered_in_course( $toolId, $courseId )
{
    $currentCourseDbNameGlu = claro_get_course_db_name_glued($courseId);
    $course_tbl = claro_sql_get_course_tbl($currentCourseDbNameGlu);
    $default_visibility = false;

    //find max rank in the tool_list

    $sql = "SELECT count(*) FROM `" . $course_tbl['tool'] . "`
    WHERE tool_id      = " . $toolId;

    return claro_sql_query_fetch_single_value($sql);
}

/**
 * Change the activation status for the given tool in the given course
 * @param int $toolId main tool id
 * @param string $courseId
 * @param boolean $activated
 * @return boolean
 */
function update_course_tool_activation_in_course( $toolId, $courseId, $activated )
{
    if ( $activated && !course_tool_already_installed($toolId,$courseId) )
    {
        $tLabel = get_module_label_from_tool_id( $toolId );

        if ( $tLabel )
        {
            if ( ! is_tool_registered_in_course( $toolId, $courseId ) )
            {
                register_module_in_single_course( $toolId, $courseId );
            }
            
            install_module_in_course( $tLabel, $courseId );
            update_tool_installation_in_course( $toolId, $courseId );
        }
    }
    
    $sql_activated = $activated ? "'true'" : "'false'";
    
    $tbl_cdb_names = claro_sql_get_course_tbl( claro_get_course_db_name_glued($courseId) );
    $tblCourseToolList = $tbl_cdb_names['tool'];
    
    $sql = "UPDATE `{$tblCourseToolList}`\n"
        . "SET `activated` = " . $sql_activated . "\n"
        . "WHERE tool_id = " . (int) $toolId
        ;
        
    if ( claro_sql_query( $sql ) )
    {
        return claro_sql_affected_rows();
    }
    else
    {
        false;
    }
}

/**
 * Change the tool installation status in the course
 * @param int $toolId main tool id
 * @param string $courseId
 * @return boolean
 */
function update_tool_installation_in_course( $toolId, $courseId )
{
    $tbl_cdb_names = claro_sql_get_course_tbl( claro_get_course_db_name_glued($courseId) );
    $tblCourseToolList = $tbl_cdb_names['tool'];
    
    $sql = "UPDATE `{$tblCourseToolList}`\n"
        . "SET `installed` = 'true'\n"
        . "WHERE tool_id = " . (int) $toolId
        ;
        
    if ( claro_sql_query( $sql ) )
    {
        return claro_sql_affected_rows() == 1;
    }
    else
    {
        false;
    }
}

/**
 * Is the module registered in the given course ?
 * @param int $toolId main tool id
 * @param string $courseId course code
 * @return boolean
 */
function is_module_registered_in_course( $toolId, $courseId )
{
    $tbl_cdb_names = claro_sql_get_course_tbl( claro_get_course_db_name_glued($courseId) );
    $tblCourseToolList = $tbl_cdb_names['tool'];
    
    $sql = "SELECT COUNT(*) FROM `{$tblCourseToolList}`\n"
        . "WHERE tool_id = " . (int) $toolId;
    
    $res = claro_sql_query_fetch_single_value( $sql );
    
    return $res;
}
