<?php // $Id$
if ( count( get_included_files() ) == 1 ) die( '---' );
/**
 * CLAROLINE
 *
 * Course user library contains function to manage users enrolment and properties in course
 * @version 1.8 $Revision$
 * @copyright 2001-2006 Universite catholique de Louvain (UCL)
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @package CLUSR
 * @author Claro Team <cvs@claroline.net>
 * @author Christophe Gesché <moosh@claroline.net>
 * @author Mathieu Laurent <laurent@cerdecam.be>
 * @author Hugues Peeters <hugues.peeters@advalvas.be>
 */

/**
 * subscribe a specific user to a specific course
 *
 * @author Hugues Peeters <hugues.peeters@advalvas.be>
 * @param int $user_id user ID from the course_user table
 * @param string $course_code course code from the cours table
 * @return boolean TRUE  if it succeeds, FALSE otherwise
 */

function user_add_to_course($userId, $courseCode, $admin = false, $tutor = false, $register_by_class = false)
{
    $tbl_mdb_names = claro_sql_get_main_tbl();
    $tbl_user            = $tbl_mdb_names['user'];
    $tbl_rel_course_user = $tbl_mdb_names['rel_course_user'];

    // previously check if the user are already registered on the platform
    $sql = "SELECT COUNT(user_id)
            FROM `" . $tbl_user . "`
            WHERE user_id = " . (int) $userId ;

    if (  claro_sql_query_get_single_value($sql) == 0 )
    {
        return claro_failure::set_failure('user_not_found'); // the user isn't registered to the platform
    }
    else
    {
        // previously check if the user isn't already subscribed to the course
        $sql = "SELECT count_user_enrol, count_class_enrol
                FROM `" . $tbl_rel_course_user . "`
                WHERE user_id = " . (int) $userId . "
                  AND code_cours ='" . addslashes($courseCode) . "'";

        $course_user_list = claro_sql_query_get_single_row($sql);

        if ( $course_user_list !== false && count($course_user_list) > 0 )
        {
            $count_user_enrol = (int) $course_user_list['count_user_enrol'];
            $count_class_enrol = (int) $course_user_list['count_class_enrol'];

            // increment the count of enrolment by the user or class
            if ( ! $register_by_class )  $count_user_enrol = 1;
            else                         $count_class_enrol++;

            $sql = "UPDATE `". $tbl_rel_course_user ."`
                    SET `count_user_enrol` = " . $count_user_enrol . ",
                        `count_class_enrol` = " . $count_class_enrol . "
                    WHERE `user_id` = ". (int)$userId . "
                    AND  `code_cours` = '" . addslashes($courseCode) . "'";
            if ( claro_sql_query($sql) ) return true;
            else                         return false;
        }
        else
        {
            // first enrolment to the course
            $count_user_enrol = 0;
            $count_class_enrol = 0;

            if ( ! $register_by_class )  $count_user_enrol = 1;
            else                         $count_class_enrol = 1;

            // TODO 
            if ( $admin ) $profileId = claro_get_profile_id('manager');
            else          $profileId = claro_get_profile_id('user');            

            $sql = "INSERT INTO `" . $tbl_rel_course_user . "`
                    SET code_cours = '" . addslashes($courseCode) . "',
                        user_id    = " . (int) $userId . ",
                        profile_id = " . (int) $profileId . ",
                        isCourseManager = " . (int) ($admin ? 1 : 0 ) . ",
                        tutor  = " . (int) ($tutor ? 1 : 0) . ",
                        count_user_enrol = " . $count_user_enrol . ",
                        count_class_enrol = " . $count_class_enrol ;

            if ( claro_sql_query($sql) ) return true;
            else                         return false;
        }
    } // end else user register in the platform
}

/**
 * @author Hugues Peeters <hugues.peeters@advalvas.be>
 * @param string $courseId - sys code of the course
 * @return boolean
 */

function is_course_enrollment_allowed($courseId)
{
    $tbl_mdb_names = claro_sql_get_main_tbl();
    $tbl_course = $tbl_mdb_names['course'];

    $sql = " SELECT `code`, `visible`
             FROM `" . $tbl_course . "`
             WHERE  `code` = '" . addslashes($courseId) . "'
             AND    (`visible` = 0 OR `visible` = 3)" ;

    $resultCourseEnrollmentList = claro_sql_query_fetch_all($sql);

    if (count ($resultCourseEnrollmentList) > 0 ) return false;
    else                                          return true;
}

/**
 * @author Hugues Peeters <hugues.peeters@advalvas.be>
 * @param string $courseId - sys code of the course
 * @return string enrollment key
 */

function get_course_enrollment_key($courseId)
{
    $tbl = claro_sql_get_main_tbl();


    $sql = " SELECT enrollment_key
             FROM `" . $tbl['course'] . "`
             WHERE  code = '" . addslashes($courseId) . "'";

    $enrollmentKey = claro_sql_query_get_single_value($sql);

    if ( ! is_null($enrollmentKey) || ! empty($enrollmentKey) )
    {
        return $enrollmentKey;
    }
    else
    {
        return null;
    }
}

/**
 * unsubscribe a specific user from a specific course
 *
 * @author Hugues Peeters <hugues.peeters@advalvas.be>
 *
 * @param  int     $user_id        user ID from the course_user table
 * @param  mixed (string or array) $courseCodeList course sys code
 * @param  boolean $force  true  possible to remove a course admin from course
 *                        (default false)
 * @param  boolean $deleteTrackingData (default false)
 *
 * @return boolean TRUE        if unsubscribtion succeed
 *         boolean FALSE       otherwise.
 */

function user_remove_from_course( $userId, $courseCodeList = array(), $force = false, $delTrackData = false, $unregister_by_class = false)
{
    $tbl = claro_sql_get_main_tbl();

    if ( ! is_array($courseCodeList) ) $courseCodeList = array($courseCodeList);

    if ( ! $force && $userId == $GLOBALS['_uid'] )
    {
        // PREVIOUSLY CHECK THE USER IS NOT COURSE ADMIN OF THESE COURSES

        $sql = "SELECT COUNT(user_id)
                FROM `" . $tbl['rel_course_user'] . "`
                WHERE user_id = ". (int) $userId ."
                  AND isCourseManager = 1
                  AND code_cours IN ('" . implode("', '", array_map('addslashes', $courseCodeList) ) . "') ";

        if ( claro_sql_query_get_single_value($sql)  > 0 )
        {
            return claro_failure::set_failure('course_manager_cannot_unsubscribe_himself');
        }
    }

    $sql = "SELECT code_cours , count_user_enrol, count_class_enrol
            FROM `" . $tbl['rel_course_user'] . "`
            WHERE `code_cours` IN ('" . implode("', '", array_map('addslashes', $courseCodeList) ) . "')
            AND   `user_id` = " . $userId ;

    $userEnrolCourseList = claro_sql_query_fetch_all($sql);

    foreach ( $userEnrolCourseList as $thisUserEnrolCourse )
    {

        $thisCourseCode    = $thisUserEnrolCourse['code_cours'];
        $count_user_enrol  = $thisUserEnrolCourse['count_user_enrol'];
        $count_class_enrol = $thisUserEnrolCourse['count_class_enrol'];

        if ( ( $count_user_enrol + $count_class_enrol ) == 1 )
        {
            // remove user from course
            if ( user_remove_from_group($userId, $thisCourseCode) == false ) return false;

            $dbNameGlued   = claro_get_course_db_name_glued($thisCourseCode);
            $tbl_cdb_names = claro_sql_get_course_tbl($dbNameGlued);

            $tbl_bb_notify         = $tbl_cdb_names['bb_rel_topic_userstonotify'];
            $tbl_group_team        = $tbl_cdb_names['group_team'         ];
            $tbl_userinfo_content  = $tbl_cdb_names['userinfo_content'   ];

            $sqlList = array(
            "DELETE FROM `" . $tbl_bb_notify        . "` WHERE user_id = " . (int) $userId ,
            "DELETE FROM `" . $tbl_userinfo_content . "` WHERE user_id = " . (int) $userId ,
            // change tutor to NULL for the course WHERE the tutor is the user to delete
            "UPDATE `" . $tbl_group_team . "` SET `tutor` = NULL WHERE `tutor`='" . (int) $userId . "'"
            );

            foreach( $sqlList as $thisSql )
            {
                if ( claro_sql_query($thisSql) == false ) return false;
                else                                      continue;
            }

            if ($delTrackData)
            {
                if ( user_delete_course_tracking_data($userId, $thisCourseCode) == false) return false;
            }

            $sql = "DELETE FROM `" . $tbl['rel_course_user'] . "`
                WHERE user_id = " . (int) $userId . "
                  AND code_cours = '" . addslashes($thisCourseCode) . "'";

            if ( claro_sql_query($sql) == false ) return false;
        }
        else
        {
            // decrement the count of enrolment by the user or class
            if ( ! $unregister_by_class )  $count_user_enrol--;
            else                           $count_class_enrol--;

            // update enrol count in table rel_course_user

            $sql = "UPDATE `".$tbl['rel_course_user']."`
  	                SET `count_user_enrol` = '" . $count_user_enrol . "',
                        `count_class_enrol` = '" . $count_class_enrol . "'
  	                WHERE `user_id`   =  " . (int) $userId . "
  	                AND  `code_cours` = '" . addslashes($thisCourseCode) . "'";

            if ( claro_sql_query($sql) ) return true;
            else                         return false;

        }
    }

    return true;
}

/**
 * remove a specific user from a course groups
 *
 * TODO : move in group.lib.php
 *
 * @author Hugues Peeters <hugues.peeters@advalvas.be>
 *
 * @param  int     $userId     user ID from the course_user table
 * @param  string  $courseCode course code from the cours table
 *
 * @return boolean TRUE        if removing suceed
 *         boolean FALSE       otherwise.
 */

function user_remove_from_group($userId, $courseId)
{
    $tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($courseId));

    $sql = "DELETE FROM `" . $tbl['group_rel_team_user'] . "`
            WHERE user = " . (int) $userId;

    if ( claro_sql_query($sql) ) return true;
    else                         return false;

}

/**
 * remove tracking user data from a course
 *
 * @author Hugues Peeters <hugues.peeters@advalvas.be>
 *
 * @param  int     $userId     user ID from the course_user table
 * @param  string  $courseCode course code from the cours table
 *
 * @return boolean TRUE        if removing suceed
 *         boolean FALSE       otherwise.
 */

function user_delete_course_tracking_data($userId, $courseId)
{
    $dbNameGlued   = claro_get_course_db_name_glued($courseId);
    $tbl_cdb_names = claro_sql_get_course_tbl($dbNameGlued);

    $tbl_track_access     = $tbl_cdb_names['track_e_access'   ];
    $tbl_track_downloads  = $tbl_cdb_names['track_e_downloads'];
    $tbl_track_exercices  = $tbl_cdb_names['track_e_exercices'];
    $tbl_track_uploads    = $tbl_cdb_names['track_e_uploads'  ];

    $sqlList = array(
    "DELETE FROM `" . $tbl_track_access     . "` WHERE access_user_id = " . (int) $userId ,
    "DELETE FROM `" . $tbl_track_downloads  . "` WHERE down_user_id   = " . (int) $userId ,
    "DELETE FROM `" . $tbl_track_exercices  . "` WHERE exe_user_id    = " . (int) $userId ,
    "DELETE FROM `" . $tbl_track_uploads    . "` WHERE upload_user_id = " . (int) $userId
    );

    foreach($sqlList as $thisSql)
    {
        if ( claro_sql_query($thisSql) == false ) return false;
        else                                      continue;
    }

    return true;
}

/**
 * change the status of the user in a course
 * @author Hugues Peeters <hugues.peeters@advalvas.be>
 *
 * @param $userId       integer user ID from the course_user table
 * @param $courseId     string course code from the cours table
 * @param $propertyList array should contain 'role', 'profileId', 'isCOurseManager', 'tutor'
 *
 * @return boolean TRUE if update succeed, FALSE otherwise.
 */

function user_set_course_properties($userId, $courseId, $propertyList)
{
    $tbl = claro_sql_get_main_tbl();

    $setList = array();

    if ( array_key_exists('isCourseManager', $propertyList) )
    {
        if ( $propertyList['isCourseManager'] ) $propertyList['profileId'] = claro_get_profile_id('manager') ;
    }
    
    if ( array_key_exists('profileId', $propertyList) )
    {
        $setList[] = "profile_id = '" . (int) $propertyList['profileId'] . "'";

        if ( $propertyList['profileId'] == claro_get_profile_id('manager') ) $propertyList['isCourseManager'] = 1 ;
        else                                                                 $propertyList['isCourseManager'] = 0 ;
    }

    if ( array_key_exists('isCourseManager', $propertyList) )
    {
        if ( $propertyList['isCourseManager'] ) $setList[] = 'isCourseManager = 1';
        else                                    $setList[] = 'isCourseManager = 0';
    }

    if ( array_key_exists('tutor', $propertyList) )
    {
        if ( $propertyList['tutor'] ) $setList[] = 'tutor = 1';
        else                          $setList[] = 'tutor = 0';
    }

    if ( array_key_exists('role', $propertyList) )
    {
        $setList[] = "role = '" . addslashes($propertyList['role']) . "'";
    }
    
    if ( count($setList) > 0 )
    {
        $sql = "UPDATE `" . $tbl['rel_course_user'] . "`
                SET " . implode(', ', $setList) ."
                WHERE   `user_id`    = " . (int) $userId . "
                AND     `code_cours` = '" . addslashes($courseId) . "'";

        if ( claro_sql_query_affected_rows($sql) > 0 ) return true;
        else                                           return false;
    }

    return false;
}

/**
 * set or unset course manager status for a the user in a course
 *
 * @author Hugues Peeters <hugues.peeters@advalvas.be>
 *
 * @param boolean $status 'true' for course manager, 'false' for not
 * @param integer $user_id user ID from the course_user table
 * @param string  $course_code course code from the cours table
 *
 * @return boolean TRUE  if update succeed
 *         boolean FALSE otherwise.
 */

function user_set_course_manager($status, $userId, $courseId)
{
    return user_set_course_properties($userId, $courseId,
    array('isCourseManager' => $status));
}

/**
 * set or unset course tutor status for a user in a course
 *
 * @author Hugues Peeters <hugues.peeters@advalvas.be>
 *
 * @param boolean $status, 'true' for tutor status, 'false' for not ...
 * @param int $userId user ID from the course_user table
 * @param string $courseId course code from the cours table
 *
 * @return boolean TRUE  if update succeed
 *         boolean FALSE otherwise.
 */

function user_set_course_tutor($status , $userId, $courseId)
{
    $status = ($status == true) ? 1 : 0;

    return user_set_course_properties($userId, $courseId,
    array('tutor' => $status));
}

/**
 * Send enroll to course succeded email to user
 * @author Mathieu Laurent <laurent@cerdecam.be>
 *
 * @param $userId integer
 * @param $data array
 * @return boolean
 */

function user_send_enroll_to_course_mail($userId, $data, $course=null)
{
    require_once $GLOBALS['includePath'] . '/lib/sendmail.lib.php';

    $courseData = claro_get_course_data($course);

    if ( ! empty($data['email']) )
    {

        $emailSubject  = '[' .  get_conf('siteName') . '-' . $courseData['officialCode']. '] ' . get_lang('Your registration') ;

        $emailBody = get_block('blockCourseSubscriptionNotification',
        array(
        '%firstname'=> $data['firstname'],
        '%lastname' => $data['lastname'],
        '%courseCode' => $courseData['officialCode'],
        '%courseName' => $courseData['name'],
        '%coursePath' => get_conf('rootWeb') . '/' . $courseData['path'] .'/',
        '%siteName'=> get_conf('siteName'),
        '%rootWeb' => get_conf('rootWeb'),
        '%administratorName' => get_conf('administrator_name'),
        '%administratorPhone'=> get_conf('administrator_phone'),
        '%administratorEmail'=> get_conf('administrator_email')
        ))
        ;

        if ( claro_mail_user($userId, $emailBody, $emailSubject) ) return true;
        else                                                        return false;

    }
    else
    {
        return false;
    }
}

/**
 * get the main user information
 * @param  integer $user_id user id as stored in the claroline main db
 * @return array   containing user info as 'lastName', 'firstName'
 *           'email', 'role'
 */

function course_user_get_properties($userId, $courseId)
{
    if (0 == (int) $userId)
    {
        return false;
    }

    $tbl_mdb_names       = claro_sql_get_main_tbl();
    $tbl_user            = $tbl_mdb_names['user'];
    $tbl_rel_course_user = $tbl_mdb_names['rel_course_user'];
    $tbl_course          = $tbl_mdb_names['course'];

    $sql = "SELECT  u.nom        AS lastName,
                    u.prenom     AS firstName,
                    u.email      AS email,
                    u.officialEmail  AS officialEmail,
                    u.pictureUri AS picture,
                    cu.profile_id AS profileId,
                    cu.role      AS role,
                    cu.isCourseManager ,
                    cu.tutor     AS isTutor,
                    c.intitule   AS courseName
            FROM    `" . $tbl_user            . "` AS u,
                    `" . $tbl_rel_course_user . "` AS cu,
                    `" . $tbl_course . "` AS c
            WHERE   u.user_id = cu.user_id
            AND     u.user_id = " . (int) $userId . "
            AND     cu.code_cours = '" . addslashes($courseId) . "'
            AND     c.code = cu.code_cours ";

    $result = claro_sql_query($sql);

    if (mysql_num_rows($result) > 0)
    {
        $userInfo = mysql_fetch_array($result, MYSQL_ASSOC);
        return $userInfo;
    }

    return false;
}

/**
 * Display form to edit course user properties
 * @author Mathieu Laurent <laurent@cerdecam.be>
 * @param $data array to fill the form
 */

function course_user_html_form ( $data, $courseId, $userId, $hiddenParam = null )
{
    global $_course, $_cid;
    global $_uid, $is_platformAdmin;

    $courseManagerChecked = $data['isCourseManager'] == 1 ? 'checked="checked"':'';
    $tutorChecked = $data['isTutor'] == 1 ? 'checked="checked"':'';
    $selectedProfileId = isset($data['profileId'])?(int)$data['profileId']:0;

    $form = '';

    $form .= '<form action="' . $_SERVER['PHP_SELF'] . '" method="post">' . "\n"
          .  '<input type="hidden" name="cmd" value="exUpdateCourseUserProperties" />' . "\n"; 

    if ( ! is_null($hiddenParam) && is_array($hiddenParam) )
    {
        foreach ( $hiddenParam as $name => $value )
        {
            $form .= '<input type="hidden" name="'. htmlspecialchars($name) .'" value="'. htmlspecialchars($value).'" />' . "\n";
        }
    }

    $form.=  '<table class="claroTable" cellpadding="3" cellspacing="0" border="0">' . "\n";

    // User firstname and lastname
    $form .= '<tr >' . "\n"
          .  '<td align="right">' . get_lang('Name') . ' :</td>' . "\n"
          .  '<td ><b>' . htmlspecialchars($data['firstName']) . ' ' . htmlspecialchars($data['lastName'])  . '</b></td>' . "\n"
          .  '</tr>' . "\n" ; 
    
    // Profile select box

    $profileList = claro_get_all_profile_name_list ();

    $form .= '<tr >' . "\n"
        . '<td align="right"><label for="profileId">' . get_lang('Profile') . ' :</label></td>' . "\n" 
        . '<td>' ;

    if ( $userId == $GLOBALS['_uid'] )
    {
        $form .= htmlspecialchars($profileList[$selectedProfileId]['name']) ;
    }
    else
    {
        $form .= '<select name="profileId" id="profileId">' ;

        foreach ( $profileList as $id => $info )
        {
            if ( $info['label'] != 'anonymous' )
            {
                $form .= '<option value="' . $id . '" ' . ($selectedProfileId==$id?'selected="selected"':'') . '>' . $info['name'] . '</option>' . "\n" ;
            }
        }    

        $form .= '</select>' ;
    }

    $form .= '</td>' . "\n"
              .  '</tr>' . "\n" ;

    // User role label
    $form .= '<tr >' . "\n"
          .  '<td align="right"><label for="role">' . get_lang('Role') . ' (' . get_lang('Optional') .')</label> :</td>' . "\n"
          .  '<td ><input type="text" name="role" id="role" value="'. htmlspecialchars($data['role']) . '" maxlength="40" /></td>' . "\n"
          .  '</tr>' . "\n" ;
    
    // User is tutor
    $form .= '<tr >' . "\n"
          .  '<td align="right"><label for="isTutor">' . get_lang('Group Tutor') . '</label> :</td>' . "\n"
          .  '<td><input type="checkbox" name="isTutor" id="isTutor" value="1" ' . $tutorChecked . ' /></td>' . "\n"
          .  '</tr>' . "\n" ;

    $form .= '<tr >' . "\n"
          .  '<td align="right"><label for="applyChange">' . get_lang('Save changes') . '</label> :</td>' . "\n"
          .  '<td><input type="submit" name="applyChange" id="applyChange" value="'.get_lang('Ok').'" />&nbsp;'
                      . claro_html_button($_SERVER['HTTP_REFERER'], get_lang('Cancel')) . '</td>' . "\n"
          .  '</tr>' . "\n";

    $form .= '</table>' . "\n"
          .  '</form>' . "\n" ;

    return $form;
}

?>