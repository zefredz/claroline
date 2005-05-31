<?php // $Id$
/**
 * CLAROLINE 
 *
 * The script works with the 'annoucement' tables in the main claroline table
 *
 * DB Table structure:
 * ---
 *
 * id         : announcement id
 * contenu    : announcement content
 * temps      : date of the announcement introduction / modification
 * title      : optionnal title for an announcement
 * ordre      : order of the announcement display
 *              (the announcements are display in desc order)
 *
 * @version 1.7 $Revision$
 *
 * @copyright (c) 2001-2005 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE 
 *
 * @package CLANN
 *
 * @author Claro Team <cvs@claroline.net>
 * @author Christophe Gesché <moosh@claroline.net>
 */


/**
 * get list of all announcements in the given or current course
 *
 * @param $order  'ASC' || 'DESC' : ordering of the list.
 * @param $course_id string=current :sysCode of the course (leaveblank for current course) 
 * @author Christophe Gesché <moosh@claroline.net>
 * @return array of array(id, title, content, time, visibility, rank)
 * @since  1.7
 */

function CLANN_get_item_list($order='DESC', $course_id=NULL)
{
    $tbl_c_names = claro_sql_get_course_tbl(claro_get_course_db_name_glued($course_id));
    $tbl_announcement = $tbl_c_names['announcement'];
    
    $sql = "SELECT id, title, contenu content, temps `time`, visibility, ordre rank
            FROM `" . $tbl_announcement . "`
            ORDER BY ordre ".($order=='DESC'?'DESC':'ASC');
    
     return claro_sql_query_fetch_all($sql);
}

/**
 * Delete an announcement in the given or current course
 *
 * @param $announcement_id integer:id the requested announcement
 * @param $course_id string=current :sysCode of the course (leaveblank for current course) 
 * @author Christophe Gesché <moosh@claroline.net>
 * @return result of deletion query
 * @since  1.7
 */
function CLANN_delete_item($id, $course_id=NULL) 
{
    $tbl_c_names = claro_sql_get_course_tbl(claro_get_course_db_name_glued($course_id));
    $tbl_announcement = $tbl_c_names['announcement'];

    $sql = "DELETE FROM  `" . $tbl_announcement . "`
            WHERE id='" . (int) $id . "'";
    return claro_sql_query($sql);
}


/**
 * Delete an announcement in the given or current course
 *
 * @param $announcement_id integer:id the requested announcement
 * @param $course_id string=current :sysCode of the course (leaveblank for current course) 
 * @author Christophe Gesché <moosh@claroline.net>
 * @return result of deletion query
 * @since  1.7
 */
function CLANN_delete_all_items($course_id=NULL) 
{
    $tbl_c_names = claro_sql_get_course_tbl(claro_get_course_db_name_glued($course_id));
    $tbl_announcement = $tbl_c_names['announcement'];

    $sql = "DELETE FROM  `" . $tbl_announcement . "`";
    return claro_sql_query($sql);
}

/**
 * add an new announcement in the given or current course
 *
 * @param $title     string=''      :title of the new item        
 * @param $content   string=''      :content of the new item
 * @param $time      date='now'     :publication dat of the item def:now
 * @param $course_id string=current :sysCode of the course (leaveblank for current course) 
 * @author Christophe Gesché <moosh@claroline.net>
 * @return id of the new item
 * @since  1.7
 */

function CLANN_add_item($title='',$content='', $visibility='SHOW', $time=NULL, $course_id=NULL) 
{
    $tbl_c_names = claro_sql_get_course_tbl(claro_get_course_db_name_glued($course_id));
    $tbl_announcement = $tbl_c_names['announcement'];
    
    if(is_null($time))
    {
        $sqlTime = " temps = NOW(), ";
    }
    else
    {
        $sqlTime = " temps = from_unixtime('".$time."'), ";
    }
    
    // DETERMINE THE ORDER OF THE NEW ANNOUNCEMENT
    $sql = "SELECT (MAX(ordre) + 1) nextRank
            FROM  `" . $tbl_announcement . "`";
    
    $nextRank = claro_sql_query_fetch_all($sql);
    // INSERT ANNOUNCEMENT
    
    $sql = "INSERT INTO `" . $tbl_announcement . "`
            SET title ='" . addslashes(trim($title)) . "',
                contenu = '" . addslashes(trim($content)) . "',
                visibility = '" . ($visibility=='HIDE'?'HIDE':'SHOW') . "',
             ". $sqlTime ."
            ordre ='" . (int) $nextRank[0]['nextRank'] . "'";
    return claro_sql_query_insert_id($sql);
}

/**
 * Update an announcement in the given or current course
 *
 * @param $title     string=''      :title of the new item        
 * @param $content   string=''      :content of the new item
 * @param $time      date='now'     :publication dat of the item def:now
 * @param $course_id string=current :sysCode of the course (leaveblank for current course) 
 * @author Christophe Gesché <moosh@claroline.net>
 * @return id of the new item
 * @since  1.7
 */

function CLANN_update_item($announcement_id, $title=NULL,$content=NULL, $visibility=NULL, $time=NULL, $course_id=NULL) 
{
    $tbl_c_names = claro_sql_get_course_tbl(claro_get_course_db_name_glued($course_id));
    $tbl_announcement = $tbl_c_names['announcement'];
    $sqlSet = array();
    if(!is_null($title))      $sqlSet[] = " title = '" . addslashes(trim($title)) . "' ";
    if(!is_null($content))    $sqlSet[] = " contenu = '".addslashes(trim($content))."' ";
    if(!is_null($visibility)) $sqlSet[] = " visibility = '" . ($visibility=='HIDE'?'HIDE':'SHOW') . "' ";
    if(!is_null($time))       $sqlSet[] = " temps = from_unixtime('".$time."') ";
    
    if (count($sqlSet)>0)
    {
        $sql = "UPDATE  `".$tbl_announcement."`
                SET " . implode(', ',$sqlSet)
            ."  WHERE id='" . (int) $announcement_id . "'";
    
        echo $sql;
        return claro_sql_query_insert_id($sql);
    }
    else return NULL;
}

/**
 * return data for the announcement  of the given id of the given or current course
 *
 * @param $announcement_id integer:id the requested announcement
 * @param $course_id       string :sysCode of the course (leaveblank for current course) 
 * @author Christophe Gesché <moosh@claroline.net>
 * @return array(id, title, content, visibility, rank) of the announcement
 * @since  1.7
 */

function CLANN_get_item($announcement_id, $course_id=NULL) 
{
    $tbl_c_names = claro_sql_get_course_tbl(claro_get_course_db_name_glued($course_id));
    $tbl_announcement = $tbl_c_names['announcement'];


    $sql = "SELECT id, title, contenu content, visibility, ordre rank
            FROM  `" . $tbl_announcement . "`
            WHERE id='" . (int) $announcement_id . "'";
    
    $announcement =  claro_sql_query_fetch_all($sql);
    return  $announcement[0];
}

function CLANN_set_item_visibility($announcement_id, $visibility, $course_id=NULL) 
{
    $tbl_c_names = claro_sql_get_course_tbl(claro_get_course_db_name_glued($course_id));
    $tbl_announcement = $tbl_c_names['announcement'];

    $sql = "UPDATE `" . $tbl_announcement . "`
            SET   visibility = '" . ($visibility=='HIDE'?'HIDE':'SHOW') . "'
                  WHERE id =  '" . (int) $announcement_id . "'";

    return  claro_sql_query($sql);
}

/**
 * function moveEntry($entryId,$cmd)
 *
 * @author Christophe Gesché <moosh@claroline.net>
 * @param $entryId     integer     an valid id of announcement.
 * @param $cmd         string         'UP' or 'DOWN'
 * @return true;
 *
 */
function moveEntry($item_id, $cmd, $course_id=NULL)
{
    $tbl_c_names = claro_sql_get_course_tbl(claro_get_course_db_name_glued($course_id));
    $tbl_announcement = $tbl_c_names['announcement'];

    if ( $cmd == 'DOWN' )
    {
        $thisAnnouncementId = $item_id;
        $sortDirection      = 'DESC';
    }
    elseif ( $cmd == 'UP' )
    {
        $thisAnnouncementId = $item_id;
        $sortDirection      = 'ASC';
    }
    else
        return FALSE;

    if ( $sortDirection )
    {
        $sql = "SELECT id, ordre rank
            FROM `" . $tbl_announcement . "`
            ORDER BY `ordre` " . $sortDirection;

        $result = claro_sql_query($sql);
        $thisAnnouncementRankFound = FALSE;
        $thisAnnouncementRank = '';
        while (list ($announcementId, $announcementRank) = mysql_fetch_row($result))
        {
            // STEP 2 : FOUND THE NEXT ANNOUNCEMENT ID AND ORDER.
            //          COMMIT ORDER SWAP ON THE DB

            if ($thisAnnouncementRankFound == TRUE)
            {
                $nextAnnouncementId    = $announcementId;
                $nextAnnouncementRank  = $announcementRank;

                $sql = "UPDATE `" . $tbl_announcement . "`
                    SET ordre = '" . (int) $nextAnnouncementRank . "'
                    WHERE id =  '" . (int) $thisAnnouncementId . "'";

                claro_sql_query($sql);

                $sql = "UPDATE `" . $tbl_announcement . "`
                    SET ordre = '" . $thisAnnouncementRank . "'
                    WHERE id =  '" . $nextAnnouncementId . "'";
                claro_sql_query($sql);

                break;
            }

            // STEP 1 : FIND THE ORDER OF THE ANNOUNCEMENT

            if ( $announcementId == $thisAnnouncementId )
            {
                $thisAnnouncementRank      = $announcementRank;
                $thisAnnouncementRankFound = TRUE;
            }
        }
    }
    return TRUE;
}

?>