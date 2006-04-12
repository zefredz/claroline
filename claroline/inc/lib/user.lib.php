<?php // $Id$
/**
 * CLAROLINE
 *
 * User lib contains function to manage users on the platform
 *
 * @version 1.8 $Revision$
 *
 * @copyright 2001-2006 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @package CLUSR
 *
 * @author Claro Team <cvs@claroline.net>
 * @author Christophe Gesché <moosh@claroline.net>
 * @author Mathieu Laurent <laurent@cerdecam.be>
 *
 */


include_once(dirname(__FILE__).'/auth.lib.inc.php');
include_once(dirname(__FILE__).'/form.lib.php');

defined('COURSE_ADMIN_STATUS') || define('COURSE_ADMIN_STATUS', 1);
defined('STUDENT_STATUS'     ) || define('STUDENT_STATUS'     , 5);

/**
 * Initialise user data
 * @return  array with user data
 * @author Mathieu Laurent <laurent@cerdecam.be>
 */

function user_initialise()
{
    return array('lastname'       => '',
                  'firstname'     => '',
                  'officialCode'  => '',
                  'username'      => '',
                  'password'      => '',
                  'password_conf' => '',
                  'status'        => '',
                  'email'         => '',
                  'phone'         => '',
                  'picture'       => '',
                 );
}


/**
 * Get user data on the platform
 * @param $user_id integer
 * @return  array( `user_id`, `lastname`, `firstname`, `username`, `email`, `picture`, `officialCode`, `phone`, `status` ) with user data
 * @author Mathieu Laurent <laurent@cerdecam.be>
 */

function user_get_data($user_id)
{
    $tbl_mdb_names = claro_sql_get_main_tbl();
    $tbl_user      = $tbl_mdb_names['user'];

    $sql = "SELECT                   user_id,
                    `nom`         AS lastname,
                    `prenom`      AS firstname,
                                     username,
                                     email,
                                     language,
                    `authSource`  AS authsource,
                    `pictureUri`  AS picture,
                                     officialCode,
                    `phoneNumber` AS phone,
                    `statut`      AS status
            FROM   `" . $tbl_user . "`
            WHERE  `user_id` = " . (int) $user_id;

    $result = claro_sql_query_get_single_row($sql);

    if ( $result ) return $result;
    else           return claro_failure::set_failure('user_not_found');
}

/**
 * Add a new user
 * @param $data array to fill the form
 * @author Mathieu Laurent <laurent@cerdecam.be>
 */

function user_add($data)
{
    global $userPasswordCrypted, $_uid;

    $password = $userPasswordCrypted ? md5($data['password']):$data['password'];

    $tbl_mdb_names = claro_sql_get_main_tbl();
    $tbl_user      = $tbl_mdb_names['user'];

    if ( empty($data['status'  ]) ) $data['status']   = STUDENT_STATUS;
    if ( empty($data['language']) ) $data['language'] = null;

    $sql = "INSERT INTO `" . $tbl_user . "`
            SET `nom`          = '". addslashes($data['lastname']) ."' ,
                `prenom`       = '". addslashes($data['firstname']) ."',
                `username`     = '". addslashes($data['username']) ."',
                `password`     = '". addslashes($password) . "',
                `language`     = '" .addslashes($data['language']) . "',
                `email`        = '". addslashes($data['email']) ."',
                `statut`       = ". (int) $data['status'] .",
                `officialCode` = '". addslashes($data['officialCode']) ."',
                `phoneNumber`  = '". addslashes($data['phone']) ."'";

    if ( !empty($_uid) ) $sql .= ", `creatorId` = '". (int)$_uid ."'";

    return claro_sql_query_insert_id($sql);
}

/**
 * Update user data
 * @param $user_id integer
 * @param $data array
 * @author Mathieu Laurent <laurent@cerdecam.be>
 */

function user_update ($user_id, $data)
{
    global $userPasswordCrypted, $_uid;

    $tbl_mdb_names = claro_sql_get_main_tbl();
    $tbl_user      = $tbl_mdb_names['user'];

    $sql = "UPDATE  `" . $tbl_user . "`
            SET `nom`          = '" . addslashes($data['lastname']) . "',
                `prenom`       = '" . addslashes($data['firstname']) . "',
                `username`     = '" . addslashes($data['username']) . "',
                `phoneNumber`  = '" . addslashes($data['phone']) . "',
                `creatorId`    = " . (int)$_uid. ",
                `email`        = '" . addslashes($data['email']) . "',
                `officialCode` = '" . addslashes($data['officialCode']) . "' ";

    if ( ! empty($data['status']) )
    {
        $sql .= ", `statut` = '" . (int) $data['status'] . "' " . "\n" ;
    }

    if ( ! empty($data['password']) )
    {
        $password = $userPasswordCrypted ? md5($data['password']) : $data['password'];
        $sql .= ", `password`   = '" . addslashes($password) . "' "  . "\n";
    }

    if ( ! empty($data['language']) )
    {
        $sql .= ", `language` = '" . addslashes($data['language']) . "' "  . "\n";
    }

    if ( ! empty($data['picture']) )
    {
        $sql .= ", `pictureUri` = '" . addslashes($data['picture']) . "' "  . "\n";
    }
    else
    {
        $sql .= ", `pictureUri` = NULL "  . "\n";
    }

    $sql .= " WHERE `user_id`  = " . (int) $user_id;
    return claro_sql_query($sql);
}

/**
 * Delete user form claroline platform
 * @author Mathieu Laurent <laurent@cerdecam.be>
 * @param int $user_id
 * @return boolean 'true' if it succeeds, 'false' otherwise
 */

function user_delete($userId)
{
    if ( $GLOBALS['_uid'] == $userId ) // user cannot remove himself of the platform
    {
        return claro_failure::set_failure('user_cannot_remove_himself');
    }

    // main tables name

    $tbl_mdb_names       = claro_sql_get_main_tbl();
    $tbl_user            = $tbl_mdb_names['user'           ];
    $tbl_admin           = $tbl_mdb_names['admin'          ];
    $tbl_course          = $tbl_mdb_names['course'         ];
    $tbl_sso             = $tbl_mdb_names['sso'            ];
    $tbl_rel_course_user = $tbl_mdb_names['rel_course_user'];
    $tbl_rel_class_user  = $tbl_mdb_names['rel_class_user' ];
    $tbl_track_default   = $tbl_mdb_names['track_e_default'];
    $tbl_track_login     = $tbl_mdb_names['track_e_login'  ];

    // get the list of course code where the user is subscribed
    $sql_user_courses = " SELECT `c`.`code`
                          FROM `" . $tbl_rel_course_user . "` AS cu,
                               `" . $tbl_course . "`          AS c
                          WHERE `cu`.`code_cours`=`c`.`code`
                            AND `cu`.`user_id`=" . $userId;

    $courseSysCodeList = claro_sql_query_fetch_all($sql_user_courses);

    if ( user_remove_from_course($userId, $courseSysCodeList, true, true) == false ) return false;

    $sqlList = array(

      "DELETE FROM `" . $tbl_user            . "` WHERE user_id         = " . (int) $userId ,
      "DELETE FROM `" . $tbl_admin           . "` WHERE idUser          = " . (int) $userId ,
      "DELETE FROM `" . $tbl_track_default   . "` WHERE default_user_id = " . (int) $userId ,
      "DELETE FROM `" . $tbl_track_login     . "` WHERE login_user_id   = " . (int) $userId ,
      "DELETE FROM `" . $tbl_rel_class_user  . "` WHERE user_id         = " . (int) $userId ,
      "DELETE FROM `" . $tbl_sso             . "` WHERE user_id         = " . (int) $userId ,
      // Change creatorId to NULL
      "UPDATE `" . $tbl_user . "` SET `creatorId`=NULL WHERE `creatorId`='" . (int) $userId . "'"

                    );

    foreach($sqlList as $thisSql)
    {
        if ( claro_sql_query($sqlList) == false ) return false;
        else                                      continue;
    }

    return true;
}

/**
 * unsubscribe a specific user from a specific course
 *
 * @author Hugues Peeters <peeters@ipm.ucl.ac.be>
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

function user_remove_from_course( $userId, $courseCodeList = array(), $force = false, $delTrackData = false)
{
    $tbl_mdb_names         = claro_sql_get_main_tbl();
    $tbl_rel_course_user   = $tbl_mdb_names['rel_course_user'];

    if ( ! is_array($courseCodeList) ) $courseCodeList = array($courseCodeList);

    if ( ! $force && $userId == $GLOBALS['_uid'] )
    {
        // PREVIOUSLY CHECK THE USER IS NOT COURSE ADMIN OF THIS COURSE

        $sql = "SELECT COUNT(user_id)
                FROM `" . $tbl_rel_course_user . "`
                WHERE user_id = ". (int) $userId ."
                  AND statut = '" . COURSE_ADMIN_STATUS . "' 
                  AND course_code IN ('" . implode("', '", array_map('addslashes', $courseCodeList) ) . "') ";

        if ( claro_sql_query_get_single_value($sql)  > 0 )
        {
            return claro_failure::set_failure('course_manager_cannot_unsubscribe_himself');
        }
    }

    foreach($courseCodeList as $thisCourseCode)
    {

        if ( user_remove_from_group($userId, $thisCourseCode) == false ) return false;

        $dbNameGlued   = claro_get_course_db_name_glued($thisCourseCode);
        $tbl_cdb_names = claro_sql_get_course_tbl($dbNameGlued);

        $tbl_bb_notify         = $tbl_cdb_names['bb_rel_topic_userstonotify'];
        $tbl_group_team        = $tbl_cdb_names['group_team'         ];
        $tbl_userinfo_content  = $tbl_cdb_names['userinfo_content'   ];


        $sqlList = array(
          "DELETE FROM `" . $tbl_bb_notify        . "` WHERE user_id        = " . (int) $userId ,
          "DELETE FROM `" . $tbl_userinfo_content . "` WHERE user_id        = " . (int) $userId ,
           // change tutor to NULL for the course WHERE the tutor is the user to delete
          "UPDATE `" . $tbl_group_team . "` SET `tutor` = NULL WHERE `tutor`='" . $userId . "'"
                        );

        foreach($sqlList as $thisSql)
        {
            if ( claro_sql_query($sqlList) == false ) return false;
            else                                      continue;
        }

        if ($delTrackData) 
        {
            if ( user_delete_course_tracking_data($userId, $thisCourseCode) == false) return false;
        }
    }

    $sql = "DELETE FROM `" . $tbl_rel_course_user . "`
            WHERE user_id = " . (int) $userId . "
            AND code_cours IN ('" . implode("', '", array_map('addslashes', $courseCodeList) ) . "')";

    if ( claro_sql_query($sql) == false ) return false;

    return true;
}


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
        if ( claro_sql_query($sqlList) == false ) return false;
        else                                      continue;
    }

    return true;
}


/**
 * remove a specific user from a course groups
 *
 * @author Hugues Peeters <peeters@ipm.ucl.ac.be>
 *
 * @param  int     $user_id     user ID from the course_user table
 * @param  string  $course_code course code from the cours table
 *
 * @return boolean TRUE        if removing suceed
 *         boolean FALSE       otherwise.
 */

function user_remove_from_group($user_id, $courseCode)
{
    $tbl_cdb_names           = claro_sql_get_course_tbl(claro_get_course_db_name_glued($courseCode));
    $tbl_group_rel_team_user = $tbl_cdb_names['group_rel_team_user'];

    $sql = "DELETE FROM `" . $tbl_group_rel_team_user . "`
            WHERE user = " . (int) $userId;

    if ( claro_sql_query($sql) ) return true;
    else                         return false;

}

/**
 * Return true, if user is admin on the platform
 *
 * @param $user_id integer
 *
 * @return boolean
 *
 * @author Mathieu Laurent <laurent@cerdecam.be>
 *
 */

function user_is_admin($user_id)
{
    $tbl_mdb_names = claro_sql_get_main_tbl();
    $tbl_admin = $tbl_mdb_names['admin'];

    $sql = " SELECT COUNT(`idUser`)
             FROM `" . $tbl_admin . "`
             WHERE `idUser` = " .  (int) $user_id . "";

    return claro_sql_query_get_single_value($sql) > 0;
}

/**
 * Add user in admin table
 *
 * @param $user_id integer
 *
 * @return boolean
 *
 * @author Mathieu Laurent <laurent@cerdecam.be>
 *
 */

function user_add_admin($user_id)
{
    $tbl_mdb_names = claro_sql_get_main_tbl();
    $tbl_admin = $tbl_mdb_names['admin'];

    $sql = "SELECT `idUser`
            FROM `" . $tbl_admin . "`
            WHERE `idUser`= " . (int) $user_id;

    $result =  claro_sql_query($sql);

    if ( mysql_num_rows($result) > 0 )
    {
        // user is already administrator
        return true;
    }
    else
    {
        // add user in administrator table
        $sql = "INSERT INTO `" . $tbl_admin . "` (`idUser`) VALUES (" . (int)$user_id . ")";
        return (bool) claro_sql_query($sql);
    }

}

/**
 * delete user from admin table
 *
 * @param $user_id integer
 *
 * @return boolean
 *
 * @author Mathieu Laurent <laurent@cerdecam.be>
 *
 */

function user_delete_admin($user_id)
{
    $tbl_mdb_names = claro_sql_get_main_tbl();
    $tbl_admin = $tbl_mdb_names['admin'];

    $sql = "DELETE FROM `" . $tbl_admin . "`
            WHERE `idUser`= " . (int) $user_id ;

    return (bool) claro_sql_query($sql);
}

/**
 * subscribe a specific user to a specific course
 *
 * @author Hugues Peeters <peeters@ipm.ucl.ac.be>
 *
 * @param int $user_id user ID from the course_user table
 * @param string $course_code course code from the cours table
 * @param boolean $force_it if true  : it means we must'nt check if subcription is the course is set to allowed or not
 *                          if false : (default value) it means we must take account of the subscription setting
 *
 * @return boolean TRUE  if subscribtion succeed
 *         boolean FALSE otherwise.
 */

function user_add_to_course($user_id, $course_code)
{
    $tbl_mdb_names = claro_sql_get_main_tbl();
    $tbl_user            = $tbl_mdb_names['user'];
    $tbl_rel_course_user = $tbl_mdb_names['rel_course_user'];

    // previously check if the user are already registered on the platform
    $sql = "SELECT `statut` `status`
            FROM `" . $tbl_user . "`
            WHERE user_id = " . (int) $user_id ;
    $handle = claro_sql_query($sql);

    if ( mysql_num_rows($handle) == 0 )
    {
        return claro_failure::set_failure('user_not_found'); // the user isn't registered to the platform
    }
    else
    {
        // previously check if the user isn't already subscribed to the course
        $sql = "SELECT `user_id`
                FROM `" . $tbl_rel_course_user . "`
                WHERE `user_id` = " . (int) $user_id . "
                AND `code_cours` ='" . addslashes($course_code) . "'";

        $userResultList = claro_sql_query_fetch_all($sql);

        if ( count($userResultList) > 0 )
        {
            return claro_failure::set_failure('already_enrolled_in_course');
        }
        else
        {
                $sql = "INSERT INTO `" . $tbl_rel_course_user . "`
                        SET `code_cours` = '" . addslashes($course_code) . "',
                            `user_id`    = " . (int) $user_id . ",
                            `statut`     = '" . STUDENT_STATUS . "' ";

                if ( claro_sql_query($sql) ) return true;
                else                         return false;
        }
    } // end else user register in the platform
}

/**
 * @author Hugues Peeters <peeters@ipm.ucl.ac.be>
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
 * @author Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @param string $courseId - sys code of the course
 * @return string enrollment key
 */

function get_course_enrollment_key($courseId)
{
    $tbl_mdb_names = claro_sql_get_main_tbl();
    $tbl_course = $tbl_mdb_names['course'];

    $sql = " SELECT enrollment_key
             FROM `" . $tbl_course . "`
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
 * set or unset course manager status for a the user in a course
 *
 * @author Hugues Peeters <peeters@ipm.ucl.ac.be>
 *
 * @param boolean $status 'true' for course manager, 'false' for not
 * @param int $user_id user ID from the course_user table
 * @param string $course_code course code from the cours table
 *
 * @return boolean TRUE  if update succeed
 *         boolean FALSE otherwise.
 */

function user_set_course_manager($status, $userId, $courseCode)
{
    $status = ($status == true) ? COURSE_ADMIN_STATUS : STUDENT_STATUS;

    $tbl_mdb_names = claro_sql_get_main_tbl();
    $tbl_rel_course_user = $tbl_mdb_names['rel_course_user'];

    $sql = "UPDATE `" . $tbl_rel_course_user . "`
            SET statut = '" . (int) $status . "'
            WHERE `user_id` = '" . (int)$userId . "'
            AND `code_cours` ='" . addslashes($courseCode) . "'";

    if ( claro_sql_query_affected_rows($sql) > 0 ) return true;
    else                                           return false;
}

/**
 * set or unset course tutor status for a user in a course
 *
 * @author Hugues Peeters <peeters@ipm.ucl.ac.be>
 *
 * @param boolean $status, 'true' for tutor status, 'false' for not ...
 * @param int $user_id user ID from the course_user table
 * @param string $course_code course code from the cours table
 *
 * @return boolean TRUE  if update succeed
 *         boolean FALSE otherwise.
 */

function user_set_course_tutor($status , $userId, $courseCode)
{
    $status = ($status == true) ? 1 : 0;

    $tbl_mdb_names = claro_sql_get_main_tbl();
    $tbl_rel_course_user = $tbl_mdb_names['rel_course_user'];

    $sql = "UPDATE `" . $tbl_rel_course_user . "`
            SET tutor = '" . (int) $status . "'
            WHERE `user_id` = " . (int) $userId . "
            AND `code_cours` ='" . addslashes($courseCode) . "'";

    if ( claro_sql_query_affected_rows($sql) > 0 ) return true;
    else                                           return false;
}

/**
 * subscribe a specific user to a class
 *
 * @author Guillaume Lederer < guillaume@claroline.net >
 *
 * @param int $user_id user ID from the course_user table
 * @param string $course_code course code from the cours table
 * @param boolean $force_it if true  : it means we must'nt check if subcription is the course is set to allowed or not
 *                          if false : (default value) it means we must take account of the subscription setting
 *
 * @return boolean TRUE  if subscribtion succeed
 *         boolean FALSE otherwise.
 */

function user_add_to_class($user_id,$class_id)
{
    $user_id  = (int)$user_id;
    $class_id = (int)$class_id;

    // get database information

    $tbl_mdb_names       = claro_sql_get_main_tbl();
    $tbl_rel_class_user  = $tbl_mdb_names['rel_class_user'];
    $tbl_class           = $tbl_mdb_names['class'];

    // 1. See if there is a user with such ID in the main database

    $user_data = user_get_data($user_id);

    if ( !$user_data )
    {
        return claro_failure::get_last_failure('USER_NOT_FOUND');
    }

    // 2. See if there is a class with such ID in the main DB

    $sql = "SELECT `id`
            FROM `" . $tbl_class . "`
            WHERE `id` = '" . $class_id . "' ";
    $handle = claro_sql_query($sql);

    if ( mysql_num_rows($handle) == 0 )
    {
        return claro_failure::set_failure('CLASS_NOT_FOUND'); // the class doesn't exist
    }

    // 3. See if user is not already in class

    $sql = "SELECT `user_id`
            FROM `" . $tbl_rel_class_user . "`
            WHERE `user_id` = '" . $user_id . "'
            AND `class_id` = '" . $class_id . "'";
    $handle = claro_sql_query($sql);

    if ( mysql_num_rows($handle) > 0 )
    {
        return claro_failure::set_failure('USER_ALREADY_IN_CLASS'); // the user is already subscrided to the class
    }

    // 4. Add user to class in the rel_class_user table

    $sql = "INSERT INTO `" . $tbl_rel_class_user . "`
            SET `user_id` = '" . $user_id . "',
               `class_id` = '" . $class_id . "' ";

    return claro_sql_query($sql);
}

/**
 * change the status of the user in a course
 *
 * @author Hugues Peeters <peeters@ipm.ucl.ac.be>
 *
 * @param int $user_id user ID from the course_user table
 * @param string $course_code course code from the cours table
 * @param array $properties - should contain 'role', 'status', 'tutor'
 *
 * @return boolean TRUE if update succeed, FALSE otherwise.
 */

function user_update_course_properties($user_id, $course_code, $properties)
{
    $tbl_mdb_names = claro_sql_get_main_tbl();
    $tbl_rel_course_user  = $tbl_mdb_names['rel_course_user'  ];

    $sqlChangeStatus = '';

    if ( ( $properties['status'] == 1 or $properties['status'] ==  5 ) )
    {
        $sqlChangeStatus = "`statut` = '" . $properties['status'] . "', ";
    }

    $sql = "UPDATE `" . $tbl_rel_course_user . "`
            SET `role` = '" . addslashes($properties['role']) . "',
           " . $sqlChangeStatus . "
           `tutor`      = " . (int) $properties['tutor'] . "
           WHERE   `user_id`    = " . (int) $user_id . "
           AND     `code_cours` = '" . addslashes($course_code) . "'";

    if ( claro_sql_query($sql) ) return true;
    else                         return false;
}



/**
 * Send registration succeded email to user
 *
 * @param integer $user_id
 * @param mixed $data array of user data or null to keep data following $user_id param.
 *
 * @author Mathieu Laurent <laurent@cerdecam.be>
 *
 */

function user_send_registration_mail ($user_id, $data)
{
    if ( ! empty($data['email']) )
    {
        // email subjet

        $emailSubject  = '[' . get_conf('siteName') . '] ' . get_lang('Your registration') ;

        // email body

        $emailBody = get_block('blockAccountCreationNotification',
                                array(
                                '%firstname'=> $data['firstname'],
                                '%lastname' => $data['lastname'],
                                '%username' => $data['username'],
                                '%password' => $data['password'],
                                '%siteName'=> get_conf('siteName'),
                                '%rootWeb' => get_conf('rootWeb'),
                                '%administratorName' => get_conf('administrator_name'),
                                '%administratorPhone'=> get_conf('administrator_phone'),
                                '%administratorEmail'=> get_conf('administrator_email')
                                 )
                              );

        if ( claro_mail_user($user_id, $emailBody, $emailSubject) ) return true;
        else                                                        return false;
    }
    else
    {
        return false;
    }

}
/**
 * Send enroll to course succeded email to user
 *
 * @param $user_id integer
 * @param $data array
 *
 * @author Mathieu Laurent <laurent@cerdecam.be>
 *
 */

function user_send_enroll_to_course_mail ($user_id, $data, $course=null)
{
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
        '%coursePath' => $rootWeb . '/' . $courseData['path'] .'/',
        '%siteName'=> get_conf('siteName'),
        '%rootWeb' => get_conf('rootWeb'),
        '%administratorName' => get_conf('administrator_name'),
        '%administratorPhone'=> get_conf('administrator_phone'),
        '%administratorEmail'=> get_conf('administrator_email')
        ))
        ;

        if ( claro_mail_user($user_id, $emailBody, $emailSubject) ) return true;
        else                                                        return false;

    }
    else
    {
        return false;
    }
}

/**
 * validate form registration
 *
 * @param $data array from the form
 *
 * @return array with error messages
 *
 * @author Mathieu Laurent <laurent@cerdecam.be>
 *
 */

function user_validate_form_registration($data)
{
    $messageList = array();

    // required fields
    if ( empty($data['lastname'])
        || empty($data['firstname'])
        || empty($data['password_conf'])
        || empty($data['password'])
        || empty($data['username'])
        || ( empty($data['officialCode']) && ! get_conf('userOfficialCodeCanBeEmpty') )
        || ( empty($data['email'] ) && ! get_conf('userMailCanBeEmpty') )
       )
    {
        $error = true;
        $messageList[] = get_lang('You left some required fields empty');
    }

    // check if official code is available
    if ( !empty($data['officialCode']) )
    {
        if ( ! is_official_code_available($data['officialCode']) )
        {
            $error = true;
            $messageList[] = claro_failure::get_last_failure();
        }
    }

    // check if username is available
    if ( !empty($data['username']) )
    {
        if ( ! is_username_available($data['username']) )
        {
            $messageList[] = claro_failure::get_last_failure();
        }
    }

    // check if the two password are identical
    if ( $data['password_conf']  != $data['password']  )
    {
        $messageList[] = get_lang('You typed two different passwords') ;
    }

    // check if password isn't too easy
    if ( !empty($data['password']) && get_conf('SECURE_PASSWORD_REQUIRED') )
    {
        if ( ! is_password_secure_enough( $data['password'],
                                          array( $data['username'] ,
                                                 $data['officialCode'] ,
                                                 $data['lastname'] ,
                                                 $data['firstname'] ,
                                                 $data['email'] ))
            )
        {
            if (claro_failure::get_last_failure() == 'ERROR_CODE_too_easy')
                $messageList[] = get_lang(
                'this password is too simple. Use a password like this <code>%passpruposed</code>',
                array('%passpruposed', substr(md5(date('Bis')),0,8) ));

        }
    }

    // check email validity
    if ( !empty($data['email']) )
    {
        if ( ! is_valid_email($data['email']) )
        {
            $messageList[] = claro_failure::get_last_failure();
        }
    }

    return $messageList;
}

/**
 * validate form profile
 *
 * @param $data array to fill the form
 *
 * @author Mathieu Laurent <laurent@cerdecam.be>
 *
 */

function user_validate_form_profile($data,$user_id)
{
    $messageList = array();

    // required fields
    if ( empty($data['lastname'])
        || empty($data['firstname'])
        || empty($data['username'])
        || ( empty($data['officialCode']) && ! get_conf('userOfficialCodeCanBeEmpty') )
        || ( empty($data['email'] ) && ! get_conf('userMailCanBeEmpty') )
       )
    {
        $error = true;
        $messageList[] = get_lang('You left some required fields empty');
    }

    // check if official code is available
    if ( !empty($data['officialCode']) )
    {
        if ( ! is_official_code_available($data['officialCode'],$user_id) )
        {
            $error = true;
            $messageList[] = claro_failure::get_last_failure();
        }
    }

    // check if username is available
    if ( !empty($data['username']) )
    {
        if ( ! is_username_available($data['username'],$user_id) )
        {
            $messageList[] = claro_failure::get_last_failure();
        }
    }

    // check if the two password are identical
    if ( $data['password_conf'] != $data['password']  )
    {
        $messageList[] = get_lang('You typed two different passwords') ;
    }
    else
    {
        // check if password isn't too easy
        if ( !empty($data['password']) && get_conf('SECURE_PASSWORD_REQUIRED') )
        {
            if ( ! is_password_secure_enough( $data['password'],
                                              array( $data['username'] ,
                                                     $data['officialCode'] ,
                                                     $data['lastname'] ,
                                                     $data['firstname'] ,
                                                     $data['email'] ))
                )
            {
                if (claro_failure::get_last_failure()=='ERROR_CODE_too_easy')
                    $messageList[] = get_lang(
                'this password is too simple. Use a password like this <code>%passpruposed</code>',
                array('%passpruposed', substr(md5(date('Bis')),0,8) ));

            }
        }
    }

    // check email validity
    if ( !empty($data['email']) )
    {
        if ( ! is_valid_email($data['email']) )
        {
            $messageList[] = claro_failure::get_last_failure();
        }
    }

    return $messageList;

}

/**
 * Check if the password chosen by the user is not too much easy to find
 *
 * @author Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @param string requested password
 * @param array list of other values of the form we wnt to check the password
 *
 * @return boolean true if not too much easy to find
 *
 */

function is_password_secure_enough($requestedPassword, $forbiddenValueList)
{
    foreach ( $forbiddenValueList as $thisValue )
    {
        if ( strtoupper($requestedPassword) == strtoupper($thisValue) )
        {
           return claro_failure::set_failure('ERROR_CODE_too_easy');
        }
    }

    return true;
}

/**
 * Check if the email is valid
 *
 * @param string email
 *
 * @return boolean
 */

function is_valid_email($email)
{
    if (is_well_formed_email_address($email) )
    {
        return true;
    }
    else
    {
        return claro_failure::set_failure(get_lang('The email address is not valid'));
    }
}

/**
 * Check if the username is available
 *
 * @param string username
 * @param integer user_id
 *
 * @return boolean
 */

function is_username_available($username,$user_id=null)
{
    $tbl_mdb_names = claro_sql_get_main_tbl();
    $tbl_user = $tbl_mdb_names['user'];

    $sql = "SELECT COUNT(*) `loginCount`
            FROM `" . $tbl_user . "`
            WHERE username='" . addslashes($username) . "' ";

    if ( !empty($user_id) )
    {
        $sql .= " AND user_id <> "  . (int) $user_id ;
    }

    list($result) = claro_sql_query_fetch_all($sql);

    if ( $result['loginCount'] == 0 )
    {
        return true;
    }
    else
    {
        return claro_failure::set_failure(get_lang('This user name is already taken'));
    }
}

/**
 * Check if the official code is available
 *
 * @param string official code
 * @param integer user_id
 *
 * @return boolean
 */

function is_official_code_available($official_code,$user_id=null)
{
    $tbl_mdb_names = claro_sql_get_main_tbl();
    $tbl_user = $tbl_mdb_names['user'];

    $sql = "SELECT COUNT(*) `officialCodeCount`
            FROM `" . $tbl_user . "`
            WHERE officialCode='" . addslashes($official_code) . "' ";

    if ( !empty($user_id) )
    {
        $sql .= " AND user_id <> "  . (int) $user_id ;
    }

    list($result) = claro_sql_query_fetch_all($sql);

    if ( $result['officialCodeCount'] == 0 )
    {
        return true;
    }
    else
    {
        return claro_failure::set_failure(get_lang('This official code is already used by another user.'));
    }
}

/**
 * Display user form registration
 *
 * @param $data array to fill the form
 *
 * @author Mathieu Laurent <laurent@cerdecam.be>
 *
 */

function user_display_form_registration($data)
{
    user_display_form($data,'registration');
}

/**
 * Display user form profile
 *
 * @param $data array to fill the form
 *
 * @author Mathieu Laurent <laurent@cerdecam.be>
 *
 */

function user_display_form_profile($data)
{
    user_display_form($data,'profile');
}

/**
 * Display user form registration
 *
 * @param $data array to fill the form
 *
 * @author Mathieu Laurent <laurent@cerdecam.be>
 *
 */

function user_display_form_add_new_user($data)
{
    user_display_form($data,'add_new_user');
}

/**
 * Display user admin form registration
 *
 * @param $data array to fill the form
 *
 * @author Mathieu Laurent <laurent@cerdecam.be>
 *
 */

function user_display_form_admin_add_new_user($data)
{
    user_display_form($data,'admin_add_new_user');
}

/**
 * Display user admin form registration
 *
 * @param $data array to fill the form
 *
 * @author Mathieu Laurent <laurent@cerdecam.be>
 *
 */

function user_display_form_admin_user_profile($data)
{
    user_display_form($data,'admin_user_profile');
}

/**
 * Display form to edit or add user to the platform
 *
 * @param $data array to fill the form
 * @author Mathieu Laurent <laurent@cerdecam.be>
 */

function user_display_form($data, $form_type='registration')
{
    global $imgRepositoryWeb;

    global $urlAppend;

    // display registration form
    echo '<form action="' . $_SERVER['PHP_SELF'] . '" method="post" enctype="multipart/form-data" >' . "\n";

    // hidden fields
    echo '<input type="hidden" name="cmd" value="registration" />' . "\n"
    .    '<input type="hidden" name="claroFormId" value="' . uniqid('') . '" />' . "\n";

    if ( array_key_exists('confirmUserCreate', $data) )
    {
        echo '<tr><td></td><td><input type="hidden" id="confirmUserCreate" name="confirmUserCreate" value="'.( $data['confirmUserCreate'] ? '1' : '0').'"></td></tr>';
        $onChange = 'onchange="getElementById(\'confirmUserCreate\').value=0;"';
    }
    else
    {
        $onChange = '';
    }

    // table begin
    echo '<table cellpadding="3" cellspacing="0" border="0">' . "\n";

    // user id
    if ( $form_type == 'admin_user_profile' )
    {
        echo '<input type="hidden" name="uidToEdit" value="' . $data['user_id'] . '">';
        echo '<tr>'
            . '<td align="right">' . get_lang('Userid') . ' :</td>'
            . '<td >' . $data['user_id'] . '</td>'
            . '</tr>';

    }

    // lastname
    echo ' <tr>' . "\n"
        . '  <td align="right"><label for="lastname">' . required_field(get_lang('Last name')) . '&nbsp;:</label></td>' . "\n"
        . '  <td><input type="text" size="40" name="lastname" id="lastname" value="' . htmlspecialchars($data['lastname']) . '" /></td>' . "\n"
        . ' </tr>' . "\n";

    // firstname
    echo ' <tr>' . "\n"
        . '  <td align="right"><label for="firstname">' . required_field(get_lang('First name')) . '&nbsp;:</label></td>' . "\n"
        . '  <td><input type="text" size="40" id="firstname" name="firstname" value="' . htmlspecialchars($data['firstname']) . '" /></td>' . "\n"
        . ' </tr>' . "\n" ;

    // official code
    if ( get_conf('ask_for_official_code') )
    {
        echo ' <tr>'  . "\n"
            . '  <td align="right"><label for="officialCode">' . ( get_conf('userOfficialCodeCanBeEmpty')?get_lang('Administrative code'):required_field(get_lang('Administrative code'))) . '&nbsp;:</label></td>'  . "\n"
            . '  <td><input type="text" size="40" id="offcialCode" name="officialCode" value="' . htmlspecialchars($data['officialCode']) . '" /></td>' . "\n"
            . ' </tr>' . "\n";
    }

    // user picture
    if ( get_conf('CONFVAL_ASK_FOR_PICTURE',false) && $form_type == 'profile' )
    {
        echo '<tr>' . "\n"
            . '<td align="right">' . "\n"
            . ' <label for="picture">' . $data['picture']?get_lang('Change picture'):get_lang('Include picture') . ' :<br />' . "\n"
            . ' <small>(.jpg or .jpeg only)</small></label>'
            . ' </td>' . "\n"
            . ' <td>' . "\n"
            . '<input type="file" name="picture" id="picture" >';

        if ( empty($data['picture']) )
        {
            echo '<br />' . "\n" . '<label for="del_picture">' . get_lang('Remove picture') . '</label>'
                . '<input type="checkbox" name="del_picture" id="del_picture" value="yes">';
        }
        else
        {
            echo '<input type="hidden" name="del_picture" id="del_picture" value="no">';
        }
        echo '</td>' . "\n"
            . '</tr>' . "\n";
    }

    if ( get_conf('l10n_platform',true))
    {
        $language_select_box = user_display_preferred_language_select_box();
        if ( !empty($language_select_box) )
        {
            echo ' <tr>' . "\n"
                . '  <td align="right"><label for="language_selector">' . get_lang('Language') . '&nbsp;:</label></td>' . "\n"
        . '  <td>'. $language_select_box .'</td>' . "\n"
        . ' </tr>' . "\n" ;


        }
    }

    if ( isset($data['authsource']) &&
         ( strtolower($data['authsource']) != 'claroline' && strtolower($data['authsource']) != 'clarocrypt' )
        && $form_type == 'profile' )
    {
        // disable modification of username and password with external autentication
        echo '<tr><td align="right">' . get_lang('Username') . ' :</td>'
        .    '<td>' . htmlspecialchars($data['username']) . '</td>'
        .    '</tr>'
        ;
    }
    else
    {
        echo ' <tr>' . "\n"
            . '  <td>&nbsp;</td>' . "\n"
            . '  <td>&nbsp;</td>' . "\n"
            . ' </tr>' . "\n";

        if ( $form_type == 'profile' || $form_type == 'admin_user_profile' )
        {
            echo '<tr>' . "\n"
                . '<td>&nbsp;</td>' . "\n"
                . '<td><small>(' . get_lang('Enter new password twice to change, leave empty to keep it') . ')</small></td>' . "\n"
                . '</tr>' . "\n" ;

            $required_password = false;
        }
        else
        {
            if ( $form_type == 'registration' )
            {
                echo  '<tr>'
                .     '<td>&nbsp;</td>'
                .     '<td>'
                .    '<small>'
                .    get_lang('Choose now a username and a password for the user account') . '<br />'
                .    get_lang('Memorize them, you will use them the next time you will enter to this site.') . '<br />'
                .    '<strong>' . get_lang('Warning The system is case sensitive') . '</strong>'
                .    '</small>'
                .    '</td>'
                .    '</tr>';

            }

            $required_password = true;
        }

        if ( $required_password )
        {
            $password_label = required_field(get_lang('Password'));
        }
        else
        {
            $password_label = get_lang('Password');
        }

        // username
        echo ' <tr>' . "\n"
            . '  <td align="right"><label for="username">' . required_field(get_lang('Username')) . '&nbsp;:</label></td>' . "\n"
            . '  <td><input type="text" size="40" id="username" name="username" value="' . htmlspecialchars($data['username']) . '" /></td>' . "\n"
            . ' </tr>' . "\n";

        // password
        echo ' <tr>'  . "\n"
            . '     <td align="right"><label for="password">' . $password_label . '&nbsp;:</label></td>' . "\n"
            . '  <td><input type="password" size="40" id="password" name="password" /></td>' . "\n"
            . '    </tr>' . "\n";

        // password confirmation
        echo ' <tr>' . "\n"
            . '     <td align="right"><label for="password_conf">' . $password_label . '&nbsp;:<br>' . "\n" . "\n"
            . ' <small>(' . get_lang('Confirmation') . ')</small></label></td>' . "\n"
            . '  <td><input type="password" size="40" id="password_conf" name="password_conf" /></td>' . "\n"
            . ' </tr>' . "\n";

        echo ' <tr>' . "\n"
            . '  <td>&nbsp;</td>' . "\n"
            . '  <td>&nbsp;</td>' . "\n"
            . ' </tr>' . "\n";
    }

    // email
    echo ' <tr>' . "\n"
        . '<td align="right"><label for="email">' . ( get_conf('userMailCanBeEmpty')?get_lang('Email'):required_field(get_lang('Email'))) . '&nbsp;:</label></td>' . "\n"
        . '<td><input type="text" size="40" id="email" name="email" value="' . htmlspecialchars($data['email']) . '" /></td>' . "\n"
        . '</tr>' . "\n"

        . '<tr>' . "\n"
        . '<td align="right"><label for="phone">' . get_lang('Phone') . '&nbsp;:</label></td>' . "\n"
        . '<td><input type="text" size="40" id="phone" name="phone" value="' . htmlspecialchars($data['phone']) . '" /></td>' . "\n"
        . '</tr>' . "\n";

    // Group Tutor
    if ( $form_type == 'add_new_user' )
    {
        echo '<tr valign="top">'
            . '<td align="right">' . get_lang('Group Tutor') .' : </td>'
            . '<td>'
            . '<input type="radio" name="tutor" value="1" id="tutorYes" ' . ($data['tutor']?'checked':'') . ' >'
            . '<label for="tutorYes">' . get_lang('Yes') . '</label>'
            . '<input type="radio" name="tutor" value="0"  id="tutorNo" ' . (!$data['tutor']?'checked':'') . ' >'
            . '<label for="tutorNo">' . get_lang('No') . '</label>'
            . '</td>'
            . '</tr>';
    }

    // Course manager of the course
    if ( $form_type == 'add_new_user' )
    {
        echo ' <tr>' . "\n"
            . '  <td align="right">' . get_lang('Manager') . '&nbsp;:</td>' . "\n"
            . '<td>'
            . '<input type="radio" name="courseAdmin" value="1" id="courseAdminYes" ' . ($data['courseAdmin'] ? 'checked' : '') . ' >'
            . '<label for="courseAdminYes">' . get_lang('Yes') . '</label>'
            . '<input type="radio" name="courseAdmin" value="0" id="courseAdminNo" '  . ($data['courseAdmin'] ? '' : 'checked') . ' >'
            . '<label for="courseAdminNo">' . get_lang('No') . '</label>'
            . '</td>'
            . ' </tr>' . "\n";
    }

    // Status: Allow registration as course manager
    if ( ( get_conf('allowSelfRegProf') && $form_type == 'registration') || $form_type == 'admin_add_new_user' || $form_type == 'admin_user_profile' )
    {
        echo ' <tr>' . "\n"
            . '<td align="right"><label for="status">' . get_lang('Action') . '&nbsp;:</label></td>' . "\n"
            . '<td>' . "\n"
            . '<select id="status" name="status">'
            . '<option value="' . STUDENT_STATUS . '">' . get_lang('Follow courses') . '</option>'
            . '<option value="' . COURSE_ADMIN_STATUS . '" ' . ($data['status'] == COURSE_ADMIN_STATUS ? 'selected="selected"' : '') . '>' . get_lang('Create course') . '</option>'
            . '</select>'
            . '  </td>' . "\n"
            . ' </tr>' . "\n";
    }

    // Administrator
    if ( $form_type == 'admin_user_profile' )
    {
        echo '<tr valign="top">'
            . '<td align="right">' . get_lang('Is platform admin') .' : </td>'
            . '<td>'
            . '<input type="radio" name="is_admin" value="1" id="admin_form_yes" ' . ($data['is_admin']?'checked':'') . ' >'
            . '<label for="admin_form_yes">' . get_lang('Yes') . '</label>'
            . '<input type="radio" name="is_admin" value="0"  id="admin_form_no" ' . (!$data['is_admin']?'checked':'') . ' >'
            . '<label for="admin_form_no">' . get_lang('No') . '</label>'
            . '</td>'
            . '</tr>';
    }

    // Submit
    if ( $form_type == 'registration' )
    {
        echo ' <tr>' . "\n"
            . '  <td align="right">' . ucfirst(get_lang('Create')) . ' : </td>' . "\n"
            . '  <td>' . "\n"
            . '  <input type="submit" value="' . get_lang('Ok') . '" />&nbsp;'
            . claro_html_button($urlAppend.'/index.php', get_lang('Cancel'))
            . ' </td>' . "\n"
            . '</tr>' . "\n";
    }
    elseif (  $form_type == 'admin_add_new_user' )
    {
        echo ' <tr>' . "\n"
            . '  <td align="right">' . ucfirst(get_lang('Create')) . ' : </td>' . "\n"
            . '  <td>' . "\n"
            . '  <input type="submit" value="' . get_lang('Ok') . '" />&nbsp;'
            . claro_html_button($_SERVER['HTTP_REFERER'], get_lang('Cancel'))
            . ' </td>' . "\n"
            . '</tr>' . "\n";
    }
    elseif ($form_type == 'add_new_user')
    {
       echo '<tr>' . "\n"
            . ' <td align="right"><label for="applyChange">' . get_lang('Save changes') . ' : </label></td>' . "\n"
            . ' <td>'
            . ' <input type="submit" name="applyChange" id="applyChange" value="' . get_lang('Ok') . '" />&nbsp;'
            . ' <input type="submit" name="applySearch" id="applySearch" value="' . get_lang('Search') . '" />&nbsp;'
            . claro_html_button($_SERVER['HTTP_REFERER'], get_lang('Cancel'))
            . ' </td>' . "\n"
            . '</tr>' . "\n";
    }
    else
    {
        echo '<tr>' . "\n"
            . ' <td align="right"><label for="applyChange">' . get_lang('Save changes') . ' : </label></td>' . "\n"
            . ' <td>'
            . ' <input type="submit" name="applyChange" id="applyChange" value="' . get_lang('Ok') . '" />&nbsp;'
            . claro_html_button($_SERVER['HTTP_REFERER'], get_lang('Cancel'))
            . ' </td>' . "\n"
            . '</tr>' . "\n";
    }

    echo '<tr>' . "\n"
         . '<td>&nbsp;</td>' . "\n"
         . '<td><small>' . get_lang('<span class="required">*</span> denotes required field') . '</small></td>' . "\n"
         . '</tr>' . "\n" ;

    // Personnal course list
    if ( $form_type == 'admin_user_profile' )
    {
        echo '<tr>'
            . '<td align="right">&nbsp;</td>'
            . '<td>'
            .'<a href="adminusercourses.php?uidToEdit=' . $data['user_id'] . '">'
            . '<img src="'.$imgRepositoryWeb.'course.gif">' . get_lang('PersonalCourseList')
            . '</a>'
            .'</td>'
            . '</tr>';
    }


    echo '</table>' . "\n"
        . '</form>' . "\n";

}

function required_field($field)
{
    return '<span class="required">*</span>&nbsp;' . $field;
}

/**
 * @param array $criterionList -
 *        Allowed keys are 'name', 'firstname', 'email', 'officialCode'
 * @param string $courseId (optional) 
 *        permit check if user are already enrolled in the concerned cours
 * @param boolean $allCriterion (optional) 
 *        define if all submited criterion has to be set.
 * @param boolean $strictCompare (optional) 
 *        define if criterion comparison use wildcard or not
 * @return array - existing users who met the criterions
 */

function user_search( $criterionList = array() , $courseId = null, 
                      $allCriterion = true, $strictCompare = false )
{
    $validatedCritList = array('lastname' => '', 'firstname'    => '', 
                               'email' => ''   , 'officialCode' => '');

    foreach($criterionList as $thisCritKey => $thisCritValue)
    {
        if ( array_key_exists($thisCritKey, $validatedCritList ) )
        {
            $validatedCritList[$thisCritKey] = str_replace('%', '\%', $thisCritValue);
        }
        else claro_die('user_search(): WRONG CRITERION KEY !');
    }

    $operator = $allCriterion  ? 'AND' : 'OR';
    $wildcard = $strictCompare ? '' : '%';

    $tbl_mdb_names   = claro_sql_get_main_tbl();
    $tbl_user        = $tbl_mdb_names['user'           ];
    $tbl_course_user = $tbl_mdb_names['rel_course_user'];

    $sql =  "SELECT U.nom           lastname, 
                    U.prenom        firstname, 
                    U.email         email, 
                    U.officialCode  officialCode, 
                    U.`user_id` AS  uid 
                   ". ($courseId ? ', CU.`user_id` AS registered' : '') . " 
             FROM `" . $tbl_user . "` AS U";

    if ($courseId) $sql .= " LEFT JOIN `" . $tbl_course_user . "` AS CU
                                    ON CU.`user_id`=U.`user_id`
                                   AND CU.`code_cours` = '" . $courseId . "' ";

    $sqlCritList = array();

    if ($validatedCritList['lastname']) 
        $sqlCritList[] = " U.nom    LIKE '". addslashes($validatedCritList['lastname'    ])   . $wildcard . "'";
    if ($validatedCritList['firstname'   ]) 
        $sqlCritList[] = " U.prenom LIKE '". addslashes($validatedCritList['firstname'   ])   . $wildcard . "'";
    if ($validatedCritList['email']) 
        $sqlCritList[] = " U.email  LIKE '". addslashes($validatedCritList['email'       ])   . $wildcard . "'";
    if ($validatedCritList['officialCode']) 
        $sqlCritList[] = " U.officialCode = '". addslashes($validatedCritList['officialCode']) .$wildcard . "'";

    if ( count($sqlCritList) > 0) $sql .= 'WHERE ' . implode(" $operator ", $sqlCritList);

    $sql .= " ORDER BY nom, prenom ";

    return claro_sql_query_fetch_all($sql);
}

function user_display_preferred_language_select_box()
{
    $language_list = get_language_to_display_list();

    $form = '';

    if ( is_array($language_list) && count($language_list) > 1 )
    {
        // get the the current language
        $user_language = language::current_language();
        // build language selector form
        $form .= claro_html_form_select('language',$language_list,$user_language,array('id'=>'language_selector')) ;
    }
    return $form;
}

?>