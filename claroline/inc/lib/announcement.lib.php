<?php // $Id$
/**
 * CLAROLINE
 *
 * The script works with the 'annoucement' tables in the main claroline table
 *
 * DB Table structure:
 * ---
 *
 * * id         : announcement id
 * * contenu    : announcement content
 * * temps      : date of the announcement introduction / modification
 * * title      : optionnal title for an announcement
 * * ordre      : order of the announcement display
 *              (the announcements are display in desc order)
 *
 * @version 1.8 $Revision$
 *
 * @copyright (c) 2001-2006 Universite catholique de Louvain (UCL)
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
 * @param string $order  'ASC' || 'DESC' : ordering of the list.
 * @param  string $course_id sysCode of the course (leaveblank for current course)
 * @return array of array(id, title, content, time, visibility, rank)
 * @since  1.7
 */

function announcement_get_item_list($order='DESC', $course_id=NULL)
{
    $tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($course_id));

    $sql = "SELECT            id,
                              title,
                   contenu AS content,
                   temps   AS `time`,
                              visibility,
                   ordre AS   rank
            FROM `" . $tbl['announcement'] . "`
            ORDER BY ordre " . ($order == 'DESC' ? 'DESC' : 'ASC');
    return claro_sql_query_fetch_all($sql);
}

/**
 * Delete an announcement in the given or current course
 *
 * @param integer $announcement_id id the requested announcement
 * @param string $course_id  sysCode of the course (leaveblank for current course)
 * @return result of deletion query
 * @since  1.7
 */
function announcement_delete_item($id, $course_id=NULL)
{
    $tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($course_id));

    $sql = "DELETE FROM  `" . $tbl['announcement'] . "`
            WHERE id='" . (int) $id . "'";
    return claro_sql_query($sql);
}


/**
 * Delete an announcement in the given or current course
 *
 * @param integer $announcement_id id the requested announcement
 * @param string $course_id        sysCode of the course (leaveblank for current course)
 * @return result of deletion query
 * @since  1.7
 */
function announcement_delete_all_items($course_id=NULL)
{
    $tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($course_id));

    $sql = "DELETE FROM  `" . $tbl['announcement'] . "`";
    return claro_sql_query($sql);
}

/**
 * add an new announcement in the given or current course
 *
 * @param string $title title of the new item
 * @param string $content   content of the new item
 * @param date   $time  publication dat of the item def:now
 * @param course_code $course_id sysCode of the course (leaveblank for current course)
 * @return id of the new item
 * @since  1.7
 * @todo convert to param date  timestamp
 */

function announcement_add_item($title='',$content='', $visibility='SHOW', $time=NULL, $course_id=NULL)
{
    $tbl= claro_sql_get_course_tbl(claro_get_course_db_name_glued($course_id));

    if(is_null($time))
    {
        $sqlTime = " temps = NOW(), ";
    }
    else
    {
        $sqlTime = " temps = from_unixtime('". (int)$time ."'), ";
    }

    // DETERMINE THE ORDER OF THE NEW ANNOUNCEMENT
    $sql = "SELECT (MAX(ordre) + 1) AS nextRank
            FROM  `" . $tbl['announcement'] . "`";

    $nextRank = claro_sql_query_fetch_all($sql);
    // INSERT ANNOUNCEMENT

    $sql = "INSERT INTO `" . $tbl['announcement'] . "`
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
 * @param string $title     title of the new item
 * @param string $content   content of the new item
 * @param date   $time      publication dat of the item def:now
 * @param string $course_id sysCode of the course (leaveblank for current course)
 * @return handler of query
 * @since  1.7
 * @todo convert to param date  timestamp
 */

function announcement_update_item($announcement_id, $title=NULL, $content=NULL, $visibility=NULL, $time=NULL, $course_id=NULL)
{
    $tbl= claro_sql_get_course_tbl(claro_get_course_db_name_glued($course_id));
    $sqlSet = array();
    if(!is_null($title))      $sqlSet[] = " title = '" . addslashes(trim($title)) . "' ";
    if(!is_null($content))    $sqlSet[] = " contenu = '" . addslashes(trim($content)) . "' ";
    if(!is_null($visibility)) $sqlSet[] = " visibility = '" . ($visibility=='HIDE'?'HIDE':'SHOW') . "' ";
    if(!is_null($time))       $sqlSet[] = " temps = from_unixtime('".(int)$time."') ";

    if (count($sqlSet)>0)
    {
        $sql = "UPDATE  `" . $tbl['announcement'] . "`
                SET " . implode(', ',$sqlSet) . "
                WHERE id='" . (int) $announcement_id . "'";
        return claro_sql_query($sql);
    }
    else return NULL;
}

/**
 * return data for the announcement  of the given id of the given or current course
 *
 * @param integer $announcement_id id the requested announcement
 * @param string  $course_id       sysCode of the course (leaveblank for current course)
 * @return array(id, title, content, visibility, rank) of the announcement
 * @since  1.7
 */

function announcement_get_item($announcement_id, $course_id=NULL)
{
    $tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($course_id));

    $sql = "SELECT                id,
                                  title,
                   contenu     AS content,
                                  visibility,
                   ordre       AS rank
            FROM  `" . $tbl['announcement'] . "`
            WHERE id=" . (int) $announcement_id ;

    $announcement = claro_sql_query_get_single_row($sql);

    if ($announcement) return $announcement;
    else               return claro_failure::set_failure('ANNOUNCEMENT_UNKNOW');
}

function announcement_set_item_visibility($announcement_id, $visibility, $course_id=NULL)
{
    $tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($course_id));

    if (!in_array($visibility, array ('HIDE','SHOW')))
     trigger_error('ANNOUNCEMENT_VISIBILITY_UNKNOW', E_USER_NOTICE);
    $sql = "UPDATE `" . $tbl['announcement'] . "`
            SET   visibility = '" . ($visibility=='HIDE'?'HIDE':'SHOW') . "'
                  WHERE id =  '" . (int) $announcement_id . "'";
    return  claro_sql_query($sql);
}

/**
 * function move_entry($entryId,$cmd)
 *
 * @param  integer $entryId  an valid id of announcement.
 * @param  string $cmd       'UP' or 'DOWN'
 * @return true;
 *
 * @author Christophe Gesché <moosh@claroline.net>
 */
function move_entry($item_id, $cmd, $course_id=NULL)
{
    $tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($course_id));

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
        return false;

    if ( $sortDirection )
    {
        $sql = "SELECT          id,
                       ordre AS rank
            FROM `" . $tbl['announcement'] . "`
            ORDER BY `ordre` " . $sortDirection;

        $result = claro_sql_query($sql);
        $thisAnnouncementRankFound = false;
        $thisAnnouncementRank = '';
        while ( (list ($announcementId, $announcementRank) = mysql_fetch_row($result)) )
        {
            // STEP 2 : FOUND THE NEXT ANNOUNCEMENT ID AND ORDER.
            //          COMMIT ORDER SWAP ON THE DB

            if ($thisAnnouncementRankFound == true)
            {
                $nextAnnouncementId    = $announcementId;
                $nextAnnouncementRank  = $announcementRank;

                $sql = "UPDATE `" . $tbl['announcement'] . "`
                    SET ordre = '" . (int) $nextAnnouncementRank . "'
                    WHERE id =  '" . (int) $thisAnnouncementId . "'";

                claro_sql_query($sql);

                $sql = "UPDATE `" . $tbl['announcement'] . "`
                    SET ordre = '" . $thisAnnouncementRank . "'
                    WHERE id =  '" . $nextAnnouncementId . "'";
                claro_sql_query($sql);

                break;
            }

            // STEP 1 : FIND THE ORDER OF THE ANNOUNCEMENT

            if ( $announcementId == $thisAnnouncementId )
            {
                $thisAnnouncementRank      = $announcementRank;
                $thisAnnouncementRankFound = true;
            }
        }
    }
    return true;
}

?>