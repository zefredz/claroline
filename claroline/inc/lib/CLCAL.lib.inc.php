<?php // $Id$
/**
 * CLAROLINE 
 *
 * - For a Student -> View angeda Content
 * - For a Prof    -> - View agenda Content
 *         - Update/delete existing entries
 *         - Add entries
 *         - generate an "announce" entries about an entries
 *
 * @version 1.7 $Revision$
 *
 * @copyright (c) 2001-2005 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE 
 *
 * @package CLCAL
 *
 * @author Claro Team <cvs@claroline.net>
 * @author Christophe Gesché <moosh@claroline.net>
 */

/**
 * get list of all agenda item in the given or current course
 *
 * @param $order  'ASC' || 'DESC' : ordering of the list.
 * @param $course_id string=current :sysCode of the course (leaveblank for current course) 
 * @author Christophe Gesché <moosh@claroline.net>
 * @return array of array(`id`, `titre`, `contenu`, `day`, `hour`, `lasting`, `visibility`)
 * @since  1.7
 */

function CLCAL_get_item_list($order='DESC', $course_id=NULL)
{
    $tbl_c_names = claro_sql_get_course_tbl(claro_get_course_db_name_glued($course_id));
    $tbl_calendar_event = $tbl_c_names['calendar_event']; 
    
    $sql = "SELECT `id`, `titre`, `contenu`, `day`, `hour`, `lasting`, `visibility`
        FROM `".$tbl_calendar_event."`
        ORDER BY `day` ".($order=='DESC'?'DESC':'ASC')." , `hour` ".($order=='DESC'?'DESC':'ASC');
    
     return claro_sql_query_fetch_all($sql);
}

/**
 * Delete an event in the given or current course
 *
 * @param $event_id integer:id the requested event
 * @param $course_id string=current :sysCode of the course (leaveblank for current course) 
 * @author Christophe Gesché <moosh@claroline.net>
 * @return result of deletion query
 * @since  1.7
 */
function CLCAL_delete_item($event_id, $course_id=NULL) 
{
    $tbl_c_names = claro_sql_get_course_tbl(claro_get_course_db_name_glued($course_id));
    $tbl_calendar_event = $tbl_c_names['calendar_event']; 

    $sql = "DELETE FROM  `" . $tbl_calendar_event . "`
            WHERE id='" . (int) $event_id . "'";
    return claro_sql_query($sql);
}


/**
 * Delete an event in the given or current course
 *
 * @param $event_id integer:id the requested event
 * @param $course_id string=current :sysCode of the course (leaveblank for current course) 
 * @author Christophe Gesché <moosh@claroline.net>
 * @return result of deletion query
 * @since  1.7
 */
function CLCAL_delete_all_items($course_id=NULL) 
{
    $tbl_c_names = claro_sql_get_course_tbl(claro_get_course_db_name_glued($course_id));
    $tbl_calendar_event = $tbl_c_names['calendar_event']; 

    $sql = "DELETE FROM  `" . $tbl_calendar_event . "`";
    return claro_sql_query($sql);
}

/**
 * add an new event in the given or current course
 *
 * @param $title     string=''      :title of the new item        
 * @param $content   string=''      :content of the new item
 * @param $time      date='now'     :publication dat of the item def:now
 * @param $course_id string=current :sysCode of the course (leaveblank for current course) 
 * @author Christophe Gesché <moosh@claroline.net>
 * @return id of the new item
 * @since  1.7
 */

function CLCAL_add_item($title='',$content='', $day=NULL, $hour=NULL, $lasting='', $visibility='SHOW', $course_id=NULL) 
{
    $tbl_c_names = claro_sql_get_course_tbl(claro_get_course_db_name_glued($course_id));
    $tbl_calendar_event = $tbl_c_names['calendar_event']; 
    
    if (is_null($day)) $day = date('Y-m-d');
    if (is_null($hour)) $hour =  date('H:i:s');
    $sql = "INSERT INTO `" . $tbl_calendar_event . "`
        SET   titre   = '" . addslashes(trim($title)) . "',
              contenu = '" . addslashes(trim($content)) . "',
              day     = '" . $day . "',
              hour    = '" . $hour . "',
              visibility = '" . ($visibility=='HIDE'?'HIDE':'SHOW') . "',
              lasting = '" . addslashes(trim($lasting)) . "'";

    return claro_sql_query_insert_id($sql);
}

/**
 * return data for the event  of the given id of the given or current course
 *
 * @param $event_id integer:id the requested event
 * @param $course_id       string :sysCode of the course (leaveblank for current course) 
 * @author Christophe Gesché <moosh@claroline.net>
 * @return array(`id`, `title`, `content`, `dayAncient`, `hourAncient`, `lastingAncient`) of the event
 * @since  1.7
 */

function CLCAL_get_item($event_id, $course_id=NULL) 
{
    $tbl_c_names = claro_sql_get_course_tbl(claro_get_course_db_name_glued($course_id));
    $tbl_calendar_event = $tbl_c_names['calendar_event']; 
    $sql = "SELECT `id`, 
                   `titre` `title`, 
                   `contenu` `content`,
                   `day` as `dayAncient`,
                   `hour` as `hourAncient`,
                   `lasting` as `lastingAncient`
            FROM `" . $tbl_calendar_event . "` 

            WHERE `id` = '". (int) $event_id . "'";

    $event =  claro_sql_query_fetch_all($sql);
    return  $event[0];
}

function CLCAL_set_item_visibility($event_id, $visibility, $course_id=NULL) 
{
    $tbl_c_names = claro_sql_get_course_tbl(claro_get_course_db_name_glued($course_id));
    $tbl_calendar_event = $tbl_c_names['calendar_event']; 
    
    $sql = "UPDATE `" . $tbl_calendar_event . "`
            SET   visibility = '" . ($visibility=='HIDE'?'HIDE':'SHOW') . "'
                  WHERE id =  '" . (int) $event_id . "'";
    return  claro_sql_query($sql);
}
?>