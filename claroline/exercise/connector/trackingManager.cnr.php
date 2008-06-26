<?php
if ( count( get_included_files() ) == 1 ) die( '---' );
/**
 * CLAROLINE
 *
 * @version 1.8 $Revision$
 *
 * @copyright (c) 2001-2006 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @package CLQWZ
 *
 * @author Claro Team <cvs@claroline.net>
 * @author Sebastien Piraux <pir@cerdecam.be>
 */

/**
 * Delete
 */
class CLQWZ_TrackingManager extends TrackingManager
{
    private $tbl_qwz_tracking;
    private $tbl_qwz_tracking_questions;
    private $tbl_qwz_tracking_answers;
    
    public function __construct($courseId)
    {
        $tbl_cdb_names = get_module_course_tbl( array( 'qwz_exercise', 'qwz_tracking', 'qwz_tracking_questions', 'qwz_tracking_answers' ), $courseId );
        $this->tbl_qwz_tracking = $tbl_cdb_names['qwz_tracking'];
        $this->tbl_qwz_tracking_questions = $tbl_cdb_names['qwz_tracking_questions'];
        $this->tbl_qwz_tracking_answers = $tbl_cdb_names['qwz_tracking_answers'];
    }
    
    public function deleteAll()
    {
        $sql = "DELETE `T`, `Q`, `A` 
                FROM `".$this->tbl_qwz_tracking."` AS `T`,
                     `".$this->tbl_qwz_tracking_questions."` AS `Q`, 
                     `".$this->tbl_qwz_tracking_answers."` AS `A`";
        
        if( claro_sql_query($sql) )
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    
    public function deleteBefore( $date )
    {
        $sql = "DELETE `T`, `Q`, `A`
                FROM `".$this->tbl_qwz_tracking."` AS `T`,
                     `".$this->tbl_qwz_tracking_questions."` AS `Q`, 
                     `".$this->tbl_qwz_tracking_answers."` AS `A`
                WHERE `T`.`date` < FROM_UNIXTIME('" . (int) $date ."')
                  AND `T`.`id` = `Q`.`exercise_track_id`
                  AND `Q`.`id` = `A`.`details_id`";
        
        if( claro_sql_query($sql) )
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    
    public function deleteForUser( $userId, $date = null )
    {
        if( !is_null($date) && !empty($date) )
        {
            $dateCondition = " AND `T`.`date` < FROM_UNIXTIME('" . (int) $date . "') ";            
        }
        
        $sql = "DELETE `T`, `Q`, `A`
                FROM `".$this->tbl_qwz_tracking."` AS `T`,
                     `".$this->tbl_qwz_tracking_questions."` AS `Q`, 
                     `".$this->tbl_qwz_tracking_answers."` AS `A`
                WHERE `T`.`user_id` = ".(int) $userId
                  . $dateCondition . "   
                  AND `T`.`id` = `Q`.`exercise_track_id`
                  AND `Q`.`id` = `A`.`details_id`";
        
        if( claro_sql_query($sql) )
        {
            return true;
        }
        else
        {
            return false;
        }            
    }
}

TrackingManagerRegistry::register('CLQWZ_TrackingManager');
?>