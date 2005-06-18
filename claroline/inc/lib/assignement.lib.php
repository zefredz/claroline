<?php // $Id$
/**
 * CLAROLINE 
 *
 * The script works with the 'assignment' tables in the main claroline table
 *
 * @version 1.7 $Revision$
 *
 * @copyright (c) 2001-2005 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE 
 *
 * @package CLWRK
 *
 * @author Claro Team <cvs@claroline.net>
 * @author Christophe Gesché <moosh@claroline.net>
 */

/**
 * Delete an assignment in the given or current course
 *
 * @param integer  $assignment_id id the requested assignment
 * @param path $wrkDir path to  workRepository 
 * @return result of deletion query
 * @since  1.7
 */
function assignment_delete_assignment($assignment_id, $wrkDir)
{
    $tbl_cdb_names = claro_sql_get_course_tbl();
    $tbl_wrk_submission = $tbl_cdb_names['wrk_submission'];
    $tbl_wrk_assignment = $tbl_cdb_names['wrk_assignment'];

    // delete all works in this assignment if the delete of the files worked
    if( claro_delete_file( $wrkDir . 'assig_' . $assignment_id ))
    {
        $sql = "DELETE FROM `" . $tbl_wrk_submission . "`
                WHERE `assignment_id` = " . (int) $assignment_id;
        claro_sql_query($sql);
    }
    
    $sql = "DELETE FROM `".$tbl_wrk_assignment."`
                WHERE `id` = " . (int) $assignment_id;
        
    claro_sql_query($sql);
    return null;
    
};

?>