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
 * @package CLDOC
 *
 * @author Claro Team <cvs@claroline.net>
 * @author Sebastien Piraux <pir@cerdecam.be>
 */

/**
 * Delete
 */
class CLDOC_TrackingManager extends TrackingManager
{
    private $tbl_course_tracking_event;
    
    public function __construct($courseId)
    {
        $tbl_cdb_names = claro_sql_get_course_tbl(claro_get_course_db_name_glued($courseId));
        $this->tbl_course_tracking_event = $tbl_cdb_names['tracking_event'];
    }
    
    public function deleteAll()
    {
        $sql = "DELETE `T`
                FROM `".$this->tbl_course_tracking_event."` AS `T`
                WHERE `type` = 'download'";
        
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
        $sql = "DELETE `T`
                FROM `".$this->tbl_course_tracking_event."` AS `T`
                WHERE `type` = 'download'
                  AND `T`.`date` < FROM_UNIXTIME('" . (int) $date ."')";
        
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
            $dateCondition = " AND `T`.`date` < FROM_UNIXTIME('" . (int) $date . "')";            
        }
        
        $sql = "DELETE `T`
                FROM `".$this->tbl_course_tracking_event."` AS `T`
                WHERE `type` = 'download'
                  AND `T`.`user_id` = ".(int) $userId
                  . $dateCondition;
        
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

TrackingManagerRegistry::register('CLDOC_TrackingManager');
?>