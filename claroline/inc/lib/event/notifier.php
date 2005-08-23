<?php
/**
 * CLAROLINE 
 *
 * Class which update the main database with the date of the latest new item in each tool of each course.
 *
 * @version 1.7 $Revision$
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

        if ($eventType != "delete")
        {
             $this->update_last_event($course, $tool, $ressource,$gid, $uid);
        }
        
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
        
        // in case of a complete deletion of the course, all event regarding this course must be deleted
        if ($eventType == 'course_deleted') 
        {
            $sql = "DELETE FROM `".$tbl_notify."`
                      WHERE `course_code`='". addslashes($course)."'";
        }
        else // otherwise, just delete vent concerning the tool or the ressource in the course
        {        
            $sql = "DELETE FROM `".$tbl_notify."`
                      WHERE `course_code`='". addslashes($course) ."'
                        AND `tool_id`='". (int)$tool."'
                        AND `ressource_id`='". addslashes($ressource) ."'
                        AND `group_id` = '". (int)$gid."'
                        AND `user_id` = '". (int)$uid."'
                        ";
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
        $tbl_notify     = $tbl_mdb_names['notify'];
    
        // 1- check if row already exists
        
        $sql = "SELECT * 
                  FROM `".$tbl_notify."` 
             WHERE `course_code`='".$course_id."'
               AND `tool_id`='". (int)$tool_id."'
               AND `ressource_id`='". addslashes($ressource_id) ."'
               AND `group_id` = '". (int)$gid."'
               AND `user_id` = '". (int)$uid."'
               ";
        
        $notificationTable = claro_sql_query_fetch_all($sql);
        if (isset($notificationTable[0])) $notification = $notificationTable[0];
        
        if (isset($notification))
        {
          $do_update = true;
        }
        else
        {
          $do_update = false;
        }
        
        claro_sql_query($sql);

        // 2- update or create for concerned row

        if ($do_update) 
        {
            $sql = "UPDATE `".$tbl_notify."` 
                       SET `date`= NOW() 
                 WHERE `course_code`='". addslashes($course_id) ."' 
                   AND `tool_id`='". (int)$tool_id."'
                   AND `ressource_id`='". addslashes($ressource_id)."'
                   AND `group_id` = '". (int)$gid."'
                   AND `user_id` = '". (int)$uid."'
                       ";
            claro_sql_query($sql);
        }
        else
        {
            
                $sql = "INSERT INTO `".$tbl_notify."` 
                        SET   `course_code`   = '". addslashes($course_id) ."',
                              `tool_id` = '". (int)$tool_id."',
                  `date` = NOW(),
                  `ressource_id` = '". addslashes($ressource_id) ."',
                  `group_id` = '". (int)$gid."',
                  `user_id` = '". (int)$uid."'
                  ";
            claro_sql_query($sql);
        }

        //echo "updating in db for last event : ".$course_id." ".$tool_id." : <br>";

        // echo $sql."<br>"; debug    

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

        //  1- find the list of the user's course and in this list, take only the course where recent events happened 
        //    A- FOR A STUDENT : where the events concerned everybody (uid = 0) or the user himself (uid)
        //    B- FOR A TEACHER : every events of a course must be reported (this take much sense in the work tool, with submissions)
        if ( !isset($_SESSION['firstLogin']) || !$_SESSION['firstLogin'] ) {
            $sql="SELECT `code_cours` FROM `".$tbl_cours_user."` AS CU, `".$tbl_notify."` AS N 
                WHERE CU.`code_cours` = N.`course_code`
                    AND CU.`user_id` = '". (int)$user_id."'
                    AND N.`date` > '".$date."'
                    AND ((N.`user_id` = '0' OR N.`user_id` = '". (int)$user_id."') OR CU.`statut`='1')
                    ";

            $courseList = claro_sql_query_fetch_all($sql);
                    
            if (is_array($courseList))
            foreach ($courseList as $course)
            {
                $courses[] = $course['code_cours'];
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
        $tbl_cours_user = $tbl_mdb_names['rel_course_user'];
        $tbl_notify    = $tbl_mdb_names['notify'];
        
        //if user is course admin, he is notified of event concerning all user in the course
        
        if ($is_courseAdmin)
        {
           $toadd = "";
        } // otherwise he must only know about what concerns evrybody or himself
        else
        {
           $toadd = "AND (N.`user_id` = '0' OR N.`user_id` = '".(int)$user_id."')";
        }
        
        
        $tools = array();

        // 1- find the tool list of the given course that contains some event newer than the date '$date'
        //    A- FOR A STUDENT : where the events concerned everybody (uid = 0) or the user himself (uid)
        //    B- FOR A TEACHER : every events of a course must be reported (this take much sense in the work tool, with submissions)

        
        if ( !isset($_SESSION['firstLogin']) || !$_SESSION['firstLogin'] ) {
            
            $sql = "SELECT `tool_id`, MAX(`date`)
                    FROM `".$tbl_notify."` AS N, `".$tbl_cours_user."` AS CU
                    WHERE N.`course_code` = '".addslashes($course_id)."'
                    AND CU.`user_id` = '".(int)$user_id."'
                    AND CU.`code_cours` = N.`course_code`
                    AND N.`date` > '".$date."' 
                    ".$toadd." 
                    AND (N.`group_id` = '".$group_id."')
                    GROUP BY `tool_id`
                    ";
                          
            $toolList = claro_sql_query_fetch_all($sql);
            if (is_array($toolList))
            foreach ($toolList as $tool)
            {
                $tools[] = $tool['tool_id'];
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
        $tbl_mdb_names = claro_sql_get_main_tbl();
        $tbl_notify    = $tbl_mdb_names['notify'];   
       
        $groups = array();
        
        if ( !isset($_SESSION['firstLogin']) || !$_SESSION['firstLogin'] ) {
            $sql = "SELECT `group_id`, MAX(`date`)
                    FROM `".$tbl_notify."` AS N
                    WHERE N.`course_code` = '".$course_id."'
                    AND N.`date` > '".$date."'
                    AND (N.`group_id` != '0')
                    GROUP BY `group_id`
                    ";
            $groupList = claro_sql_query_fetch_all($sql);
            if (is_array($groupList))
            foreach ($groupList as $group)
            {
                $groups[] = $group['group_id'];
            }
        }
        
        // 2- return an array with the group id with recent unknow event until the date '$date'

        return $groups;

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
     
    function get_notified_ressources($course_id, $date, $user_id, $gid = "0", $tid)
    {
        $tbl_mdb_names = claro_sql_get_main_tbl();
        $tbl_notify    = $tbl_mdb_names['notify'];
        
        $ressources = array();
        
        if ( !isset($_SESSION['firstLogin']) || !$_SESSION['firstLogin'] ) 
        {
            $sql = "SELECT `ressource_id`
                    FROM `".$tbl_notify."` AS N
                    WHERE N.`course_code` = '". addslashes($course_id)."'
                      AND N.`date` > '".$date."'
                      AND (N.`user_id` = '0' OR N.`user_id` = '". (int)$user_id."') 
                      AND (N.`group_id` = '0' OR N.`group_id` = '". (int)$gid."')
                      AND (N.`tool_id` = '".$tid."')      
                    ";
            $ressourceList = claro_sql_query_fetch_all($sql);
            if (is_array($ressourceList))
            {
                foreach ($ressourceList as $ressource)
                {
                    $ressources[] = $ressource['ressource_id'];
                }            
            }
        }
             
        // 2- return an array with the ressources ID with recent unknow event until the date '$date' in the course and for 

        return $ressources;     
             
    }
    
    
    /**
     *  Function to know when was the last login BEFORE TODAY of the user on the platform, wihtout taking account of the login
     *
     *  @param user_id the UID of the user. 
     *
     *  @return the last login date with the last login before 00:00:00 of today of the user with the UID  ==  $user_id
     */
    
    function get_last_login_before_today($user_id)
    {
        $tbl_mdb_names        = claro_sql_get_main_tbl();
        $tbl_track_e_login    = $tbl_mdb_names['track_e_login'];
        
        $today = date("Y-m-d 00:00:00");
        
        $sql = "SELECT MAX(`login_date`) AS THEDAY
                  FROM `".$tbl_track_e_login."` AS N
                 WHERE N.`login_user_id` = '". (int)$user_id."'
                   AND N.`login_date` < '".$today."'
               ";
        
        $result = claro_sql_query_fetch_all($sql); 
        
        if (isset($result[0]['THEDAY'])) $login_date = $result[0]['THEDAY']; else $login_date = $today;
        
        //echo $login_date;//debug
        
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
        return $this->get_last_login_before_today($user_id);
    }
    
        
} // CLASS Notifier extends EventDriven 

?>
