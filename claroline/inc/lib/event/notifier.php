<?php // $Id$
if ( count( get_included_files() ) == 1 ) die( '---' );
/**
 * CLAROLINE
 *
 * Class which update the main database with the date
 * of the latest new item in each tool of each course.
 *
 * @version 1.8 $Revision$
 * @author Guillaume Lederer guim@claroline.net
 *
 */
class Notifier extends EventDriven
{
    /**
     * constructor
     */
    function Notifier ( & $registry )
    {
        parent::EventDriven( $registry );
    }

    /**
    * implements the parent update function
    * @param $event object
    * @return void
    */

    function update ($event)
    {
        // get needed info from event

        $event_args = $event->getArgs();

        $course     = $event_args['cid'];
        $tool       = $event_args['tid'];
        $ressource  = $event_args['rid'];
        $gid        = $event_args['gid'];
        $uid        = $event_args['uid'];
        $eventType  = $event->getEventType();

        // call function to update db info

        if ($eventType != 'delete')
        {
            $this->update_last_event($course, $tool, $ressource,$gid, $uid);
        }

    }
    
    /**
    * implements the parent update_rid function
    * @param $event object
    * @return void
    */

    function update_rid ($event)
    {
        $tbl_mdb_names = claro_sql_get_main_tbl();
        $tbl_notify     = $tbl_mdb_names['notify'];

        // get needed info from event

        $event_args = $event->getArgs();

        $course     = $event_args['cid'];
        $tool       = $event_args['tid'];
        $ressource  = $event_args['rid'];
        $gid        = $event_args['gid'];
        $uid        = $event_args['uid'];
        $eventType  = $event->getEventType();

        $oldRessourceId = $ressource['old_uri'];
        $newRessourceId = $ressource['new_uri'];

        // update ressource_id
            
        $sql = "UPDATE `" . $tbl_notify . "`
                SET `ressource_id`= '" . addslashes($newRessourceId) . "'
                WHERE `course_code`='". addslashes($course) ."'
                  AND `tool_id`= ". (int) $tool."
                  AND `ressource_id`= '". addslashes($oldRessourceId) ."'
                  AND `group_id` = ". (int) $gid;

        claro_sql_query($sql);
    }

    /**
     *  delete the notification information about a ressource that do not exist any longer
     */

    function delete_notif($event)
    {
        $tbl_mdb_names = claro_sql_get_main_tbl();
        $tbl_notify     = $tbl_mdb_names['notify'];

        // get needed info from event

        $event_args = $event->getArgs();

        $course     = $event_args['cid'];
        $tool       = $event_args['tid'];
        $ressource  = $event_args['rid'];
        $gid        = $event_args['gid'];
        $uid        = $event_args['uid'];
        $eventType  = $event->getEventType();

        // in case of a complete deletion of a COURSE, all event regarding this course must be deleted
        if ($eventType == 'course_deleted')
        {
            $sql = "DELETE FROM `" . $tbl_notify . "`
                    WHERE `course_code`='". addslashes($course)."'";
        }

        // in case of a complete deletion of a GROUP, all event regarding this group must be deleted
        elseif ($eventType == 'group_deleted')
        {
            $sql = "DELETE FROM `" . $tbl_notify . "`
                    WHERE `course_code`='" . addslashes($course) . "'
                      AND `group_id` = ". (int) $gid;
        }
        // otherwise, just delete event concerning the tool or the ressource in the course
        else
        {
            $sql = "DELETE FROM `" . $tbl_notify . "`
                      WHERE `course_code`='". addslashes($course) ."'
                        AND `tool_id`= ". (int) $tool."
                        AND `ressource_id`= '". addslashes($ressource) ."'
                        AND `group_id` = ". (int) $gid;
        }

        claro_sql_query($sql);

    }

    /**
     * Function used to tell the notifier that some new event happened in a tool :
     * For a specific tool in a specific course,
     * the field 'date' of the latest new event, is set to the current date.
     * Event might concern a specific group (optionnal)
     * Event might concern a specific user (optionnal)
     *
     * @param $course_id
     * @param $tool_id
     * @param $ressource_id
     * @param $gid
     * @param $uid
     * @param $dbnameGlu default NULL
     *
     * @return void
     */

    function update_last_event($course_id,$tool_id, $ressource_id, $gid, $uid)
    {
        $tbl_mdb_names = claro_sql_get_main_tbl();
        $tbl_notify    = $tbl_mdb_names['notify'];

        // 1- check if row already exists

        $sql = "SELECT count(`id`) FROM `" . $tbl_notify . "`
                     WHERE `course_code`= '".$course_id."'
                       AND `tool_id`= ". (int)$tool_id . "
                       AND `ressource_id`= '". addslashes($ressource_id) . "'
                       AND `group_id` = ". (int)$gid . "
                       AND `user_id` = ". (int)$uid;
        $do_update = (bool) claro_sql_query_get_single_value($sql);

        // 2- update or create for concerned row

        $now_date = date("Y-m-d H:i:s");

        if ($do_update)
        {
            $sqlDoUpdate = "UPDATE `" . $tbl_notify . "`
                       SET `date` = '" . $now_date . "'
                 WHERE `course_code` = '" . addslashes($course_id) . "'
                   AND `tool_id`     =  " . (int) $tool_id . "
                   AND `ressource_id`= '" . addslashes($ressource_id) . "'
                   AND `group_id`    =  " . (int) $gid . "
                   AND `user_id`     =  " . (int) $uid;
        }
        else
        {

                $sqlDoUpdate = "INSERT INTO `" . $tbl_notify . "`
                        SET   `course_code`  = '" . addslashes($course_id) . "',
                              `tool_id`      =  " . (int) $tool_id . ",
                              `date`         = '" . $now_date . "',
                              `ressource_id` = '" . addslashes($ressource_id) . "',
                              `group_id`     =  " . (int) $gid . ",
                              `user_id`      =  " . (int) $uid ;

        }
        claro_sql_query($sqlDoUpdate);
        // 3- save in session of this user that this ressource is already seen.
        // --> as he did the modification himself, he shouldn't be notified

        $_SESSION['ConsultedRessourceList'][$course_id . ':' . $tool_id . ':' . $gid . ':' . $ressource_id . ':' . $now_date] = TRUE;

    }

    /**
     * Function to know which course contains new ressources that must be notified for a specific user and since a specific date
     *
     * @param $date unix_timestamp the date from wich we must take account new items
     * @param $user_id user for which we must know what is new
     *
     * @return an array with the courses with recent unknown event until the date '$date' in the course list of the user
     */

    function get_notified_courses($date, $user_id)
    {
        $tbl_mdb_names = claro_sql_get_main_tbl();
        $tbl_cours_user = $tbl_mdb_names['rel_course_user'];
        $tbl_notify     = $tbl_mdb_names['notify'];

        $courses = array();
        $courseList = array();

        //  1- find the list of the user's course and in this list, take only the course where recent events happened
        //    A- FOR A STUDENT : where the events concerned everybody (uid = 0) or the user himself (uid)
        //    B- FOR A TEACHER : every events of a course must be reported (this take much sense in the work tool, with submissions)
        if ( !isset($_SESSION['firstLogin']) || !$_SESSION['firstLogin'] )
        {
            $sql="SELECT `course_code`, `tool_id`, `group_id`, `ressource_id`,`date` FROM `".$tbl_cours_user."` AS CU, `".$tbl_notify."` AS N
                WHERE CU.`code_cours` = N.`course_code`
                    AND CU.`user_id` = '". (int)$user_id."'
                    AND N.`date` > '".$date."'
                    AND ((N.`user_id` = '0' OR N.`user_id` = '". (int)$user_id."') OR CU.`isCourseManager`='1')
                    ";

            $courseList = claro_sql_query_fetch_all($sql);

        }

        // from result in the notify table, we mustn't take account of the ressources already consulted,
        // (saved in session)

        $size = count($courseList);

        for($i=0;$i<$size;$i++)
        {

           if (($courseList[$i]['group_id'])==0) $courseList[$i]['group_id'] = "";

           if (isset($_SESSION['ConsultedRessourceList'])
            && isset($_SESSION['ConsultedRessourceList'][$courseList[$i]['course_code'].":".$courseList[$i]['tool_id'].":".$courseList[$i]['group_id'].":".$courseList[$i]['ressource_id'].":".$courseList[$i]['date']]))
           {
              unset($courseList[$i]); // the ressource is already seen, we retrieve it from the results

           }
           else
           {
               $courses[] = $courseList[$i]['course_code']; // there is a ressource not consulted yet, we add the course_id in results
           }
        }

        //2- return an array with the courses with recent unknow event until the date '$date' in the course list of the user

        return $courses;
    }

    /**
     *  Function to know which course of a user
     *
     *  @return an array with the tools id with recent unknow event until the date '$date'
     */

    function get_notified_tools($course_id, $date, $user_id,$group_id = '0')
    {
        global $is_courseAdmin;

        $tbl_mdb_names = claro_sql_get_main_tbl();
        $tbl_notify    = $tbl_mdb_names['notify'];

        //if user is course admin, he is notified of event concerning all user in the course

        if ($is_courseAdmin)
        {
           $toadd = "";
        } // otherwise we must only know about what concerns everybody or himself
        else
        {
           $toadd = "AND (N.`user_id` = '0' OR N.`user_id` = '".(int)$user_id."')";
        }


        $tools = array();

        // 1 - Find the tool list of the given course that contains some event newer than the date '$date'
        //    - FOR A STUDENT : where the events concerned everybody (uid = 0) or the user himself (uid)
        //    - FOR A TEACHER : every events of a course must be reported (this take much sense in the work tool, with submissions)


        if ( !isset($_SESSION['firstLogin']) || !$_SESSION['firstLogin'] ) {


            // A- retrieve new item since last login in the notify table

            $sql = "SELECT `tool_id`, `date`, `group_id`, `course_code`, `ressource_id`
                    FROM `".$tbl_notify."` AS N
                    WHERE N.`course_code` = '".addslashes($course_id)."'
                    AND N.`date` > '".$date."'
                    ".$toadd."
                    AND (N.`group_id` = '".$group_id."')
                    ";

            $toolList = claro_sql_query_fetch_all($sql);

            if (is_array($toolList))

            // B- from result in the notify table, we mustn't take the ressources already consulted, saved in session

            $size = count($toolList);

            for($i=0;$i<$size;$i++)
            {

               if (($toolList[$i]['group_id'])==0) $toolList[$i]['group_id'] = "";

               if (isset($_SESSION['ConsultedRessourceList'])
                && isset($_SESSION['ConsultedRessourceList'][$toolList[$i]['course_code'].":".$toolList[$i]['tool_id'].":".$toolList[$i]['group_id'].":".$toolList[$i]['ressource_id'].":".$toolList[$i]['date']]))
               {
                  unset($toolList[$i]); // the ressource is already seen, we retrieve it from the results

               }
               else
               {
                   $tools[] = $toolList[$i]['tool_id']; // there is a ressource not consulted yet, we add tht tool_id in results
               }
            }

        }


        // 2- return an array with the tools id with recent unknow event until the date '$date'

        return $tools;

    }

    /**
     *  Function to know which group contain new item in a given course
     *
     *  @return an array with the groups id with recent unknow event until the date '$date'
     */

    function get_notified_groups($course_id, $date)
    {
        //1 - Find infiormation in Session and DB

        $tbl_mdb_names = claro_sql_get_main_tbl();
        $tbl_notify    = $tbl_mdb_names['notify'];

        $groups = array();

            // A- retrieve new item since last login in the notify table

        $sql = "SELECT `group_id`, `date`, `ressource_id`, `tool_id`, `course_code`
                    FROM `" . $tbl_notify . "` AS N
                    WHERE N.`course_code` = '" . addslashes($course_id) . "'
                    AND N.`date` > '" . addslashes($date) . "'
                    AND (N.`group_id` != '0')
                    GROUP BY `group_id`
                    ";
        $groupList = claro_sql_query_fetch_all($sql);

            // B- from result in the notify table, we mustn't take the ressources already consulted, saved in session

       $size = count($groupList);

       for($i=0; $i < $size; $i++)
       {
           if (isset($_SESSION['ConsultedRessourceList'])
           && isset($_SESSION['ConsultedRessourceList'][$groupList[$i]['course_code'] . ':' . $groupList[$i]['tool_id'] . ':' . $groupList[$i]['group_id'] . ':' . $groupList[$i]['ressource_id'] . ':' . $groupList[$i]['date']]))
           {
               unset($groupList[$i]); // the ressource is already seen, we retrieve it from the results
           }
           else
           {
               $groups[] = $groupList[$i]['group_id']; // there is a ressource not consulted yet, we add the group_id in results
           }
       }

       // 2- return an array with the group id with recent unknow event until the date '$date'

         return $groups;
    }

    /**
     *  Function to know if a specific ressource must be considered
     *  as a 'new ressource' (not already seen by a user who did not login since the date $date)
     *
     *  @return boolean : true if the ressource is new, false otherwise
     */


    function is_a_notified_ressource($_cid, $date, $_uid, $_gid, $_tid, $ressourceId,$setAsViewed=TRUE)
    {
        global $_courseTool;
        // global $fileList, $fileKey; //needed for the document tool
        global $thisFile;
        $keysStrings = $_cid . ':' . $_tid . ':' . $_gid . ':';

        // see if the ressource is new AND not consulted yet

        if (!isset($this->toolNotifiedRessourceList))
        {
            $this->toolNotifiedRessourceList = $this->get_notified_ressources($_cid, $date, $_uid, $_gid, $_tid);
        }

        //deal with specific case of folders in document tool

        if (($_courseTool['label'] == 'CLDOC') && ($thisFile['type'] == A_DIRECTORY))
        {
            $ressourceList = $this->toolNotifiedRessourceList;

            foreach ($ressourceList as $ressource)
            {
                $ressource_identification = $keysStrings
                .                           $ressource['ressource_id'] . ':'
                .                           $ressource['date']
                ;
                $pattern = '/' . $keysStrings . preg_quote($ressourceId, '/') . '.*:' . $ressource['date'] . '/';

                if (!isset($_SESSION['ConsultedRessourceList'][$ressource_identification])
                && preg_match($pattern,$ressource_identification))
                {
                    if ($ressource_identification == $keysStrings . $ressourceId . ':' . $ressource['date'])
                    //in case the new item is the folder itself only
                    {
                        $_SESSION['ConsultedRessourceList'][$ressource_identification] = TRUE;
                    }
                    return true;
                }
            }
            return false;
        }


        // compare table result with SESSION information

        if (isset($this->toolNotifiedRessourceList[$ressourceId])
             && !isset($_SESSION['ConsultedRessourceList'][$keysStrings . $ressourceId . ':' . $this->toolNotifiedRessourceList[$ressourceId]['date']]))

        {
            //now, the ressource is seen

            if ($setAsViewed) $_SESSION['ConsultedRessourceList'][$keysStrings . $ressourceId . ':' . $this->toolNotifiedRessourceList[$ressourceId]['date']] = TRUE;

            return true;
        }
        else return false;
    } //function is_a_notified_ressource


    /**
     *  Function to know if a specific forum in a course must be considered
     *  as a 'containing new posts' (not already seen by a user who did not login since the date $date)
     *
     *  @return boolean : true if the forum contains new topics/posts, false otherwise
     */


    function is_a_notified_forum($_cid, $date, $_uid, $_gid, $_tid, $forumId)
    {

        $keysStrings = $_cid . ':' . $_tid . ':' . $_gid . ':';

        // see if the ressource is new AND not consulted yet

        $notified_ressources = $this->get_notified_ressources($_cid, $date, $_uid, $_gid, $_tid);

        // see if the forum is to be notified or not.

        foreach ($notified_ressources as $ressource)
        {
            $ressource_identification = $keysStrings . $ressource['ressource_id'] . ':' . $ressource['date'];

            $pattern = '/' . $keysStrings . $forumId . '-.*:' . $ressource['date'] . '/';

            //for each ressource, it must not be in session yet and it must concern this forum exactly

            if (!isset($_SESSION['ConsultedRessourceList'][$ressource_identification])
            && preg_match($pattern, $ressource_identification))
            {
                return true;
            }
        }
        return false;
    }

    /**
     *  Function to know which ressources in a course of a user is new since a given date
     *  @param course_id the course code of the course concerned
     *  @param date the given date
     *  @param user_id the user concerned
     *  @param gid the group ID from which the tool is concerned
     *
     *  @return an array with the ressources (paths in case of document tool) with recent unknow event until the date '$date' for the user_id and course_id concerned
     */

    function get_notified_ressources($course_id, $date, $user_id, $gid, $tid)
    {
        $tbl_mdb_names = claro_sql_get_main_tbl();
        $tbl_notify    = $tbl_mdb_names['notify'];

        $ressources = array();

        if ( !isset($_SESSION['firstLogin']) || !$_SESSION['firstLogin'] )
        {
            $sql = "SELECT `ressource_id`, `date`
                    FROM `" . $tbl_notify . "` AS N
                    WHERE  N.`course_code`     = '" . addslashes($course_id) . "'
                      AND  N.`date`            > '" . addslashes($date) . "'
                      AND (N.`user_id`  = '0' OR N.`user_id`  = " . (int) $user_id . ")
                      AND (N.`group_id` = '0' OR N.`group_id` = " . (int) $gid . ")
                      AND (N.`tool_id`  = '" . addslashes($tid) . "')";
            $ressourceList = claro_sql_query_fetch_all($sql);

            foreach($ressourceList as $ressourceItem)
            {
                $ressources[$ressourceItem['ressource_id']] = $ressourceItem;
            }

        }

        return $ressources;

    }


    /**
     *  Function to know when was the last login BEFORE TODAY
     *  of the user on the platform, wihtout taking account of the login
     *
     *  @param user_id the UID of the user.
     *
     *  @return the last login date with the last login before 00:00:00
     *  of today of the user with the UID  ==  $user_id
     */

    function get_last_login_before_today($user_id)
    {
        $tbl_mdb_names        = claro_sql_get_main_tbl();
        $tbl_track_e_login    = $tbl_mdb_names['track_e_login'];

        $today = date('Y-m-d 00:00:00');

        $sql = "SELECT MAX(`login_date`) AS THEDAY
                  FROM `" . $tbl_track_e_login . "` AS N
                 WHERE N.`login_user_id` = " . (int) $user_id . "
                   AND N.`login_date` < '" . $today . "'";

        $theday = claro_sql_query_get_single_value($sql);

        $login_date = ($theday ? $theday : $today);

        return $login_date;
    }
    /**
    * Function returning the date (depending the behaviour we want) for a specific user
    * (from which every event will be considered as new).
    * In case we want different behaviours available in config, this should be coded HERE
    *
    */

    function get_notification_date($user_id)
    {
        return $this->get_last_action_before_login_date($user_id);
    }

    function get_last_action_before_login_date($user_id)
    {
        global $_user;
        global $tbl_rel_course_user;

        //if we already knwo in session what is the last action date, just retrieve it from the session

        if (isset($_SESSION['last_action'])) return $_SESSION['last_action'];

        //otherwise we must find it in the access statistics information of the database

        $last_login_date = date("Y-m-d H:i:s", $_user['lastLogin']);

        // 1 - retriev course list in which the user is subscribed

        $sql  = "SELECT `code_cours`
                   FROM `".$tbl_rel_course_user."` AS CU
                  WHERE CU.`user_id`='".(int)$user_id."'";
        $courses = claro_sql_query_fetch_all($sql);

        // 2 - retrieve each max(date) of acces for each date

        $last_action_date = "0000-00-00 00:00:00"; //set default last action date

            //look for last action date in every tracking table of courses where the user is registered

        foreach ($courses as $course)
        {

            $tbl_c_names = claro_sql_get_course_tbl(claro_get_course_db_name_glued($course['code_cours']));

            $sqlMaxDate = "SELECT MAX(`access_date`) AS MAXDATE
                      FROM `" . $tbl_c_names['track_e_access'] . "` AS STAT,
                           `" . $tbl_rel_course_user . "` AS CU
                     WHERE STAT.`access_user_id` = " . (int) $user_id . "
                       AND STAT.`access_user_id` = CU.`user_id`
                       AND CU.`user_id`          = " . (int) $user_id;
            $maxDate = claro_sql_query_get_single_value($sqlMaxDate);

            // DEBUG : echo "<br>result for : ".$course['code_cours'];var_dump($result);

            if ($maxDate && (strtotime($maxDate) > strtotime($last_action_date)))
            {
                $last_action_date = $maxDate;
            }
        }

        // return (and save in session) the latest action :
        // last login or latest action in the tracking tables

        if (strtotime($last_action_date) > strtotime($last_login_date))
        {
            $_SESSION['last_action'] = $last_action_date;
            return $last_action_date;
        }
        else
        {
            $_SESSION['last_action'] = $last_login_date;
            return $last_login_date;
        }
    }


} // CLASS Notifier extends EventDriven

?>
