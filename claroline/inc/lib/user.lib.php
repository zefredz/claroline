<?php // $Id$
/**
 * CLAROLINE
 *
 * User lib contains function to manage users on the platform
 *
 * @version 1.7 $Revision$
 *
 * @copyright 2001-2005 Universite catholique de Louvain (UCL)
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
!defined('COURSE_ADMIN_STATUS') && define('COURSE_ADMIN_STATUS', 1);
!defined('STUDENT_STATUS') && define('STUDENT_STATUS', 5);

/**
 * Initialise user data
 *
 * @return  array with user data
 *
 * @author Mathieu Laurent <laurent@cerdecam.be>
 *
 */

function user_initialise()
{
    $data = array();

    $data['lastname'] = '';
    $data['firstname'] = '';
    $data['officialCode'] = '';
    $data['username'] = '';
    $data['password'] = '';
    $data['password_conf'] = '';
    $data['status'] = '';
    $data['email'] = '';
    $data['phone'] = '';
    $data['picture'] = '';

    return $data;
}

/**
 * Get user data on the platform
 *
 * @param $user_id integer
 *
 * @return  array( `user_id`, `lastname`, `firstname`, `username`, `email`, `picture`, `officialCode`, `phone`, `status` ) with user data
 *
 * @author Mathieu Laurent <laurent@cerdecam.be>
 *
 */

function user_get_data($user_id)
{
    // user table
    $tbl_mdb_names = claro_sql_get_main_tbl();
    $tbl_user      = $tbl_mdb_names['user'];

    $sql = 'SELECT  `user_id`,
                    `nom`         AS `lastname` ,
                    `prenom`      AS `firstname`,
                    `username`                  ,
                    `email`                     ,
                    `authSource`  AS authsource ,
                    `pictureUri`  AS `picture`  ,
                    `officialCode`              ,
                    `phoneNumber` AS `phone`    ,
                    `statut`      AS `status`
            FROM   `' . $tbl_user . '`
            WHERE  `user_id` = "' . (int) $user_id . '"';

    $result = claro_sql_query($sql);

    if ( mysql_num_rows($result) )
    {
        $data = mysql_fetch_array($result);
        return $data;
    }
    else
    {
        return claro_failure::set_failure('user_not_found');
    }
}

/**
 * Add a new user
 *
 * @param $data array to fill the form
 *
 * @author Mathieu Laurent <laurent@cerdecam.be>
 *
 */

function user_add ($data)
{
    global $userPasswordCrypted, $_uid;

    $password = $userPasswordCrypted?md5($data['password']):$data['password'];

    $tbl_mdb_names = claro_sql_get_main_tbl();
    $tbl_user      = $tbl_mdb_names['user'];

    if ( empty($data['status']) ) $data['status'] = STUDENT;

    $sql = "INSERT INTO `".$tbl_user."`
            SET `nom`          = '". addslashes($data['lastname']) ."' ,
                `prenom`       = '". addslashes($data['firstname']) ."',
                `username`     = '". addslashes($data['username']) ."',
                `password`     = '". addslashes($password) ."',
                `email`        = '". addslashes($data['email']) ."',
                `statut`       = '". (int) $data['status'] ."',
                `officialCode` = '". addslashes($data['officialCode']) ."',
                `phoneNumber`  = '". addslashes($data['phone']) ."'";

    if ( !empty($_uid) ) $sql .= ", `creatorId` = '". (int)$_uid ."'";

    return claro_sql_query_insert_id($sql);
}

/**
 * Update user data
 *
 * @param $user_id integer
 * @param $data array
 *
 * @author Mathieu Laurent <laurent@cerdecam.be>
 *
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
                `creatorId`    = '" . (int)$_uid. "',
                `email`        = '" . addslashes($data['email']) . "',
                `officialCode` = '" . addslashes($data['officialCode']) . "' ";

    if ( !empty($data['status']) )
    {
        $sql .= ", `statut` = '" . (int) $data['status'] . "' " ;
    }

    if ( !empty($data['password']) )
    {
        $password = $userPasswordCrypted ? md5($data['password']) : $data['password'];
        $sql .= ", `password`   = '" . addslashes($password) . "' " ;
    }

    if ( !empty($data['picture']) )
    {
        $sql .= ", `pictureUri` = '" . addslashes($data['picture']) . "' " ;
    }
    else
    {
        $sql .= ", `pictureUri` = NULL " ;
    }

    $sql .= " WHERE `user_id`  = '" . (int) $user_id . "'";

    return claro_sql_query($sql);

}

/**
 * Delete user form claroline platform
 *
 * @param $user_id integer
 *
 * @author Mathieu Laurent <laurent@cerdecam.be>
 *
 */

function user_delete ($user_id)
{
    global $_uid;

    $user_id = (int) $user_id;

    // user cannot remove himself of the platform
    if ( $_uid == $user_id )
    {
        return claro_failure::set_failure('user_cannot_remove_himself');
    }

    // main tables name
    $tbl_mdb_names = claro_sql_get_main_tbl();
    $tbl_user = $tbl_mdb_names['user'];
    $tbl_admin = $tbl_mdb_names['admin'];
    $tbl_course = $tbl_mdb_names['course'];
    $tbl_sso = $tbl_mdb_names['sso'];
    $tbl_rel_course_user = $tbl_mdb_names['rel_course_user'];
    $tbl_rel_class_user = $tbl_mdb_names['rel_class_user'];
    $tbl_track_default = $tbl_mdb_names['track_e_default'];
    $tbl_track_login = $tbl_mdb_names['track_e_login'];

    // get the list of course code where the user is subscribed
    $sql_user_courses = " SELECT `c`.`code`
                          FROM `" . $tbl_rel_course_user . "` cu,`" . $tbl_course . "` c
                          WHERE `cu`.`code_cours`=`c`.`code`
                            AND `cu`.`user_id`='" . $user_id . "'";

    $res_user_courses = claro_sql_query($sql_user_courses) ;

    if (  mysql_num_rows($res_user_courses) )
    {
        while ( ( $user_course = mysql_fetch_array($res_user_courses) ) )
        {
            // course tables name
            $dbNameGlued = claro_get_course_db_name_glued($user_course['code']);
            $tbl_cdb_names = claro_sql_get_course_tbl($dbNameGlued);

            $tbl_bb_rel_topic_userstonotify = $tbl_cdb_names['bb_rel_topic_userstonotify'];
            $tbl_group_rel_team_user = $tbl_cdb_names['group_rel_team_user'];
            $tbl_group_team = $tbl_cdb_names['group_team'];
            $tbl_track_e_access = $tbl_cdb_names['track_e_access'];
            $tbl_track_e_downloads = $tbl_cdb_names['track_e_downloads'];
            $tbl_track_e_exercices = $tbl_cdb_names['track_e_exercices'];
            $tbl_track_e_uploads = $tbl_cdb_names['track_e_uploads'];
            $tbl_userinfo_content = $tbl_cdb_names['userinfo_content'];

            // delete user information in the table group_rel_team_user
            $sql_deleteUserFromGroup = " delete from `" . $tbl_group_rel_team_user . "` where `user`='" . $user_id . "'";
            claro_sql_query($sql_deleteUserFromGroup);

            // change tutor -> NULL for the course where the the tutor is the user deleting
            $sql_update = " update `" . $tbl_group_team . "` set `tutor`=NULL where `tutor`='" . $user_id . "'";
            claro_sql_query($sql_update);

            // delete user notification in the table bb_rel_topic_userstonotify
            $sql_deleteUserNotification = " delete from `" . $tbl_bb_rel_topic_userstonotify . "` where `user_id` ='" . $user_id . "'";
            claro_sql_query($sql_deleteUserNotification) ;

            // delete user information in the table userinfo_content
            $sql_deleteUserFromGroup = " delete from `" . $tbl_userinfo_content . "` where `user_id`='" . $user_id . "'";
            claro_sql_query($sql_deleteUserFromGroup) ;

            // delete user data in tracking tables
            $sql_DeleteUser = " delete from `" . $tbl_track_e_access ."` where `access_user_id`='" . $user_id."'";
            claro_sql_query($sql_DeleteUser);

            $sql_DeleteUser = " delete from `" . $tbl_track_e_downloads . "` where `down_user_id`='" . $user_id . "'";
            claro_sql_query($sql_DeleteUser);

            $sql_DeleteUser = " delete from `" . $tbl_track_e_exercices . "` where `exe_user_id`='" . $user_id . "'";
            claro_sql_query($sql_DeleteUser);

            $sql_DeleteUser = " delete from `" . $tbl_track_e_uploads . "` where `upload_user_id`='" . $user_id . "'";
            claro_sql_query($sql_DeleteUser);

        }

    }

    // delete the user in the table user
    $sql_DeleteUser= " delete from `" . $tbl_user . "` where `user_id`='" . $user_id . "'";
    claro_sql_query($sql_DeleteUser);

    // delete user information in the table course_user
    $sql_DeleteUser = " delete from `" . $tbl_rel_course_user . "` where `user_id`='" . $user_id . "'";
    claro_sql_query($sql_DeleteUser);

    // delete user information in the table admin
    $sql_DeleteUser = "delete from `" . $tbl_admin . "` where `idUser`='" . $user_id . "'";
    claro_sql_query($sql_DeleteUser);

    // change creatorId -> NULL
    $sql_update = " update `" . $tbl_user . "` set `creatorId`=NULL where `creatorId`='" . $user_id . "'";
    claro_sql_query($sql_update);

    // delete user information in the tables clarolineStat
    $sql_DeleteUser = " delete from `" . $tbl_track_default . "` where `default_user_id`='" . $user_id . "'";
    claro_sql_query($sql_DeleteUser);

    $sql_DeleteUser = " delete from `" . $tbl_track_login . "` where `login_user_id`='" . $user_id . "'";
    claro_sql_query($sql_DeleteUser);

    // delete the info in the class table
    $sql_DeleteUser = " delete from `" . $tbl_rel_class_user . "` where `user_id`='" . $user_id . "'";
    claro_sql_query($sql_DeleteUser);

    // delete info from sso table
    $sql_DeleteUser = " delete from `" . $tbl_sso . "` where `user_id`='" . $user_id . "'";
    claro_sql_query($sql_DeleteUser);

    return true;

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

    $sql = " SELECT `idUser`
             FROM `" . $tbl_admin . "`
             WHERE `idUser` = " .  (int) $user_id . "";
    $result = claro_sql_query($sql);

    return (bool) mysql_num_rows($result);
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

    $sql = "SELECT `idUser` FROM `" . $tbl_admin . "`
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
 * update course manager status of the user in a course
 *
 * @author Hugues Peeters <peeters@ipm.ucl.ac.be>
 *
 * @param int $user_id user ID from the course_user table
 * @param string $course_code course code from the cours table
 * @param string status course code from the cours table
 *
 * @return boolean TRUE  if update succeed
 *         boolean FALSE otherwise.
 */

function user_update_course_manager_status($user_id, $course_code, $status)
{
    $tbl_mdb_names = claro_sql_get_main_tbl();
    $tbl_rel_course_user = $tbl_mdb_names['rel_course_user'];

    $sql = "UPDATE `" . $tbl_rel_course_user . "`
            SET statut = '" . (int)$status . "'
            WHERE `user_id` = '" . (int)$user_id . "'
            AND `code_cours` ='" . addslashes($course_code) . "'";

    if ( claro_sql_query($sql) ) return true;
    else                         return false;

}

/**
 * subscribe a specific user to a class
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
            WHERE `user_id` = '" . $user_id . "' ";
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
 * update course tutor status of the user in a course
 *
 * @author Hugues Peeters <peeters@ipm.ucl.ac.be>
 *
 * @param int $user_id user ID from the course_user table
 * @param string $course_code course code from the cours table
 * @param int tutor status
 *
 * @return boolean TRUE  if update succeed
 *         boolean FALSE otherwise.
 */

function user_update_course_tutor_status($user_id, $course_code, $status)
{
    $tbl_mdb_names = claro_sql_get_main_tbl();
    $tbl_rel_course_user = $tbl_mdb_names['rel_course_user'];

    $sql = "UPDATE `" . $tbl_rel_course_user . "`
            SET tutor = '" . (int) $status . "'
            WHERE `user_id` = " . (int) $user_id . "
            AND `code_cours` ='" . addslashes($course_code) . "'";

    if ( claro_sql_query($sql) ) return true;
    else                         return false;
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
 * unsubscribe a specific user from a specific course
 *
 * @author Hugues Peeters <peeters@ipm.ucl.ac.be>
 *
 * @param  int     $user_id     user ID from the course_user table
 * @param  string  $course_code course code from the cours table
 * @param boolean $force_it if true  : a course manager can unsubscribe it himself
 *                          if false : (default value)
 *
 * @return boolean TRUE        if unsubscribtion succeed
 *         boolean FALSE       otherwise.
 */

function user_remove_from_course($user_id, $course_code,$force_it=false)
{
    global $_uid;

    $tbl_mdb_names = claro_sql_get_main_tbl();
    $tbl_rel_course_user  = $tbl_mdb_names['rel_course_user'];

    // select courses manager of the course
    $sql = "SELECT user_id
            FROM `" . $tbl_rel_course_user . "`
            WHERE code_cours = '" . addslashes($course_code) . "'
              AND statut = '" . COURSEMANAGER . "' ";

    $course_manager = claro_sql_query_fetch_all_cols($sql);

    if ( $course_manager !== false )
    {
        // user to unsubscribe is course manager
        if ( in_array($user_id,$course_manager['user_id']) )
        {
            // cannot unsubscribe the last course manager
            // it's a priority case
            if ( count($course_manager['user_id']) == 1 )
            {
                return claro_failure::set_failure('cannot_unsubscribe_the_last_course_manager');
            }

            // a course manager cannot unsubscribe himself from a course
            if ( $_uid == $user_id && !$force_it )
            {
                return claro_failure::set_failure('course_manager_cannot_unsubscribe_himself');
            }

        }
    }

    $sql = "DELETE FROM `" . $tbl_rel_course_user . "`
            WHERE user_id = '" . (int)$user_id . "'
              AND code_cours = '" . addslashes($course_code) . "'";

    if ( claro_sql_query($sql) )
    {
        user_remove_from_group($user_id, $course_code);
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

function user_remove_from_group($user_id, $course_code)
{
    $tbl_cdb_names = claro_sql_get_course_tbl(claro_get_course_db_name_glued($course_code));
    $tbl_group_rel_team_user = $tbl_cdb_names['group_rel_team_user'];

    $sql = "DELETE FROM `" . $tbl_group_rel_team_user . "`
            WHERE user = '" . (int)$user_id . "'";

    claro_sql_query($sql);

    return true;
}

/**
 * Send registration succeded email to user
 *
 * @param $user_id integer
 * @param $data array
 *
 * @author Mathieu Laurent <laurent@cerdecam.be>
 *
 */

function user_send_registration_mail ($user_id, $data)
{
    global $siteName, $rootWeb, $administrator_name, $administrator_phone, $administrator_email;

    if ( ! empty($data['email']) )
    {
        // email subjet
        $emailSubject  = '[' . $siteName . '] ' . get_lang('YourReg') ;

        // email body
        $emailBody = get_lang('Dear') . ' ' . $data['firstname'] . ' ' . $data['lastname'] . ',' . "\n"
                    . get_lang('YouAreReg') . ' ' . $siteName . ' ' . get_lang('Settings') . ' ' . $data['username'] . "\n"
                    . get_lang('Password') . ' : ' . $data['password'] . "\n"
                    . get_lang('Address') . ' ' . $siteName . ' ' . get_lang('Is') . ' : ' . $rootWeb . "\n"
                    . get_lang('Problem') . "\n"
                    . get_lang('Formula') . ',' . "\n"
                    . $administrator_name . "\n"
                    . get_lang('Manager') . ' ' . $siteName . "\n"
                    . 'T. ' . $administrator_phone . "\n"
                    . get_lang('Email') . ' : ' . $administrator_email . "\n";

        if ( claro_mail_user($user_id, $emailBody, $emailSubject) )
        {
            return true;
        }
        else
        {
            return false;
        }
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

function user_send_enroll_to_course_mail ($user_id, $data)
{
    global $siteName, $rootWeb, $administrator_name, $administrator_phone, $administrator_email, $_course ;

    if ( ! empty($data['email']) )
    {
        // email subjet
        $emailSubject  = '[' . $siteName . '] ' . get_lang('YourReg') ;

        // Send message
	    $emailBody = get_lang('Dear') . " %s %s ,\n"
                    . get_lang('OneResp'). $_course['officialCode'] . get_lang('RegYou') . " " . $siteName . " " . get_lang('Settings') . " %s\n"
                    . get_lang('Address') . " " . $siteName . " " . get_lang('Is'). ": $rootWeb\n"
                    . get_lang('Problem') . "\n"
                    . "\n"
                    . get_lang('Formula') . ",\n"
                    . get_lang('Administrator') . " $administrator_name \n"
                    . get_lang('Manager') . " $siteName\n";

        $emailBody = sprintf($emailBody,$data['firstname'],$data['lastname'], $data['email']);

        if ( ! empty($administrator_phone) ) $emailBody .= "T. $administrator_phone \n";

        $emailBody .= get_lang('Email') . ": $administrator_email \n";

        if ( claro_mail_user($user_id, $emailBody, $emailSubject) )
        {
            return true;
        }
        else
        {
            return false;
        }
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
    global $userOfficialCodeCanBeEmpty, $userMailCanBeEmpty;

    $messageList = array();

    // required fields
    if ( empty($data['lastname'])
        || empty($data['firstname'])
        || empty($data['password_conf'])
        || empty($data['password'])
        || empty($data['username'])
        || ( empty($data['officialCode']) && ! $userOfficialCodeCanBeEmpty )
        || ( empty($data['email'] ) && !$userMailCanBeEmpty )
       )
    {
        $error = true;
        $messageList[] = get_lang('EmptyFields');
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
            $error = true;
            $messageList[] = claro_failure::get_last_failure();
        }
    }

    // check if the two password are identical
    if ( $data['password_conf']  != $data['password']  )
    {
        $error = true;
        $messageList[] = get_lang('PassTwice') ;
    }

    // check if password isn't too easy
    if ( !empty($data['password']) && SECURE_PASSWORD_REQUIRED )
    {
        if ( ! is_password_secure_enough( $data['password'],
                                          array( $data['username'] ,
                                                 $data['officialCode'] ,
                                                 $data['lastname'] ,
                                                 $data['firstname'] ,
                                                 $data['email'] ))
            )
        {
            $error = true;
            if (claro_failure::get_last_failure()=='ERROR_CODE_too_easy')
                $messageList[] = get_lang('PassTooEasy') . ' <code>' . substr(md5(date('Bis')),0,8) . '</code></p>';

        }
    }

    // check email validity
    if ( !empty($data['email']) )
    {
        if ( ! is_valid_email($data['email']) )
        {
            $error = true;
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
    global $userOfficialCodeCanBeEmpty, $userMailCanBeEmpty ;

    $messageList = array();

    // required fields
    if ( empty($data['lastname'])
        || empty($data['firstname'])
        || empty($data['username'])
        || ( empty($data['officialCode']) && ! $userOfficialCodeCanBeEmpty )
        || ( empty($data['email'] ) && !$userMailCanBeEmpty )
       )
    {
        $error = true;
        $messageList[] = get_lang('EmptyFields');
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
            $error = true;
            $messageList[] = claro_failure::get_last_failure();
        }
    }

    // check if the two password are identical
    if ( $data['password_conf'] != $data['password']  )
    {
        $error = true;
        $messageList[] = get_lang('PassTwice') ;
    }
    else
    {
        // check if password isn't too easy
        if ( !empty($data['password']) && SECURE_PASSWORD_REQUIRED )
        {
            if ( ! is_password_secure_enough( $data['password'],
                                              array( $data['username'] ,
                                                     $data['officialCode'] ,
                                                     $data['lastname'] ,
                                                     $data['firstname'] ,
                                                     $data['email'] ))
                )
            {
                $error = true;
                if (claro_failure::get_last_failure()=='ERROR_CODE_too_easy')
                    $messageList[] =  get_lang('PassTooEasy')  . ' <code>' . substr(md5(date('Bis')),0,8) . '</code></p>';
            }
        }
    }

    // check email validity
    if ( !empty($data['email']) )
    {
        if ( ! is_valid_email($data['email']) )
        {
            $error = true;
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
        return claro_failure::set_failure(get_lang('EmailWrong'));
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
        return claro_failure::set_failure(get_lang('UserTaken'));
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
        return claro_failure::set_failure(get_lang('CodeUsed'));
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
    global $allowSelfRegProf, $userOfficialCodeCanBeEmpty, $userMailCanBeEmpty, $imgRepositoryWeb;

    global $rootWeb;

    // display registration form
    echo '<form action="' . $_SERVER['PHP_SELF'] . '" method="post" enctype="multipart/form-data" >' . "\n";

    // hidden fields
    echo '<input type="hidden" name="cmd" value="registration" />' . "\n"
        . '<input type="hidden" name="claroFormId" value="' . uniqid('') . '" />' . "\n";

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
        . '  <td align="right"><label for="lastname">' . required_field(get_lang('Lastname')) . '&nbsp;:</label></td>' . "\n"
        . '  <td><input type="text" size="40" name="lastname" id="lastname" value="' . htmlspecialchars($data['lastname']) . '" /></td>' . "\n"
        . ' </tr>' . "\n";

    // firstname
    echo ' <tr>' . "\n"
        . '  <td align="right"><label for="firstname">' . required_field(get_lang('Firstname')) . '&nbsp;:</label></td>' . "\n"
        . '  <td><input type="text" size="40" id="firstname" name="firstname" value="' . htmlspecialchars($data['firstname']) . '" /></td>' . "\n"
        . ' </tr>' . "\n" ;

    // official code
    if ( isset($ask_for_official_code) && $ask_for_official_code == TRUE )
    {
        echo ' <tr>'  . "\n"
            . '  <td align="right"><label for="officialCode">' . ($userOfficialCodeCanBeEmpty?get_lang('OfficialCode'):required_field(get_lang('OfficialCode'))) . '&nbsp;:</label></td>'  . "\n"
            . '  <td><input type="text" size="40" id="offcialCode" name="officialCode" value="' . htmlspecialchars($data['officialCode']) . '" /></td>' . "\n"
            . ' </tr>' . "\n";
    }

    // user picture
    if ( defined('CONFVAL_ASK_FOR_PICTURE ') && CONFVAL_ASK_FOR_PICTURE == TRUE && $form_type == 'profile' )
    {
        echo '<tr>' . "\n"
            . '<td align="right">' . "\n"
            . ' <label for="picture">' . $data['picture']?get_lang('UpdateImage'):get_lang('AddImage') . ' :<br />' . "\n"
            . ' <small>(.jpg or .jpeg only)</small></label>'
            . ' </td>' . "\n"
            . ' <td>' . "\n"
            . '<input type="file" name="picture" id="picture" >';

        if ( empty($data['picture']) )
        {
            echo '<br />' . "\n" . '<label for="del_picture">' . get_lang('DelImage') . '</label>'
                . '<input type="checkbox" name="del_picture" id="del_picture" value="yes">';
        }
        else
        {
            echo '<input type="hidden" name="del_picture" id="del_picture" value="no">';
        }
        echo '</td>' . "\n"
            . '</tr>' . "\n";
    }

    if ( isset($data['authsource']) && 
         ( strtolower($data['authsource']) != 'claroline' && strtolower($data['authsource']) != 'clarocrypt' ) 
        && $form_type == 'profile' )
    {
        // disable modification of username and password with external autentication
    	echo '<tr><td align="right">'.get_lang('UserName').' :</td><td>'.htmlspecialchars($data['username']).'</td></tr>';
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
                . '<td><small>(' . get_lang('ChangePwdexp') . ')</small></td>' . "\n"
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
                .    get_lang('ChooseYourPassword') . '<br />'
                .    get_lang('MemorizeYourPassord') . '<br />'
                .    '<strong>' . get_lang('Warning') . ' '. get_lang('TheSystemIsCaseSensitive') . '</strong>'
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
            . '  <td align="right"><label for="username">' . required_field(get_lang('UserName')) . '&nbsp;:</label></td>' . "\n"
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
        . '  <td align="right"><label for="email">' . ($userMailCanBeEmpty?get_lang('Email'):required_field(get_lang('Email'))) . '&nbsp;:</label></td>' . "\n"
        . '  <td><input type="text" size="40" id="email" name="email" value="' . htmlspecialchars($data['email']) . '" /></td>' . "\n"
        . ' </tr>' . "\n"

        . ' <tr>' . "\n"
        . '  <td align="right"><label for="phone">' . get_lang('Phone') . '&nbsp;:</label></td>' . "\n"
        . '  <td><input type="text" size="40" id="phone" name="phone" value="' . htmlspecialchars($data['phone']) . '" /></td>' . "\n"
        . ' </tr>' . "\n";

    // Group Tutor
    if ( $form_type == 'add_new_user' )
    {
        echo '<tr valign="top">'
            . '<td align="right">' . get_lang('GroupTutor') .' : </td>'
            . '<td>'
            . '<input type="radio" name="is_tutor" value="1" id="tutor_form_yes" ' . ($data['is_tutor']?'checked':'') . ' >'
            . '<label for="tutor_form_yes">' . get_lang('Yes') . '</label>'
            . '<input type="radio" name="is_tutor" value="0"  id="tutor_form_no" ' . (!$data['is_tutor']?'checked':'') . ' >'
            . '<label for="tutor_form_no">' . get_lang('No') . '</label>'
            . '</td>'
            . '</tr>';
    }

    // Course manager of the course
    if ( $form_type == 'add_new_user' )
    {
        echo ' <tr>' . "\n"
            . '  <td align="right">' . get_lang('Manager') . '&nbsp;:</label></td>' . "\n"
            . '<td>'
            . '<input type="radio" name="is_coursemanager" value="' . COURSEMANAGER . '" id="coursemanager_form_yes" ' . ($data['is_coursemanager']==COURSEMANAGER?'checked':'') . ' >'
            . '<label for="coursemanager_form_yes">' . get_lang('Yes') . '</label>'
            . '<input type="radio" name="is_coursemanager" value="' . STUDENT . '" id="coursemanager_form_no" ' . ($data['is_coursemanager']==STUDENT?'checked':'') . ' >'
            . '<label for="coursemanager_form_no">' . get_lang('No') . '</label>'
            . '</td>'
            . ' </tr>' . "\n";
    }

    // Status: Allow registration as course manager
    if ( ($allowSelfRegProf && $form_type == 'registration') || $form_type == 'admin_add_new_user' || $form_type == 'admin_user_profile' )
    {
        echo ' <tr>' . "\n"
            . '  <td align="right"><label for="status">' . get_lang('Action') . '&nbsp;:</label></td>' . "\n"
            . '  <td>' . "\n"
            . '<select id="status" name="status">'
            . '    <option value="' . STUDENT . '">' . get_lang('RegStudent') . '</option>'
            . '    <option value="' . COURSEMANAGER . '" ' . ($data['status'] == COURSEMANAGER ? 'selected="selected"' : '') . '>' . get_lang('RegAdmin') . '</option>'
            . '</select>'
            . '  </td>' . "\n"
            . ' </tr>' . "\n";
    }

    // Administrator
    if ( $form_type == 'admin_user_profile' )
    {
        echo '<tr valign="top">'
            . '<td align="right">' . get_lang('UserIsPlaformAdmin') .' : </td>'
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
            . claro_disp_button($rootWeb, get_lang('Cancel'))
            . ' </td>' . "\n"
            . '</tr>' . "\n";
    }
    elseif (  $form_type == 'admin_add_new_user' )
    {
        echo ' <tr>' . "\n"
            . '  <td align="right">' . ucfirst(get_lang('Create')) . ' : </td>' . "\n"
            . '  <td>' . "\n"
            . '  <input type="submit" value="' . get_lang('Ok') . '" />&nbsp;'
            . claro_disp_button($_SERVER['HTTP_REFERER'], get_lang('Cancel'))
            . ' </td>' . "\n"
            . '</tr>' . "\n";
    }
    elseif ($form_type == 'add_new_user')
    {
       echo '<tr>' . "\n"
            . ' <td align="right"><label for="applyChange">' . get_lang('SaveChanges') . ' : </label></td>' . "\n"
            . ' <td>'
            . ' <input type="submit" name="applyChange" id="applyChange" value="' . get_lang('Ok') . '" />&nbsp;'
            . ' <input type="submit" name="applySearch" id="applySearch" value="' . get_lang('Search') . '" />&nbsp;'
            . claro_disp_button($_SERVER['HTTP_REFERER'], get_lang('Cancel'))
            . ' </td>' . "\n"
            . '</tr>' . "\n";
    }
    else
    {
        echo '<tr>' . "\n"
            . ' <td align="right"><label for="applyChange">' . get_lang('SaveChanges') . ' : </label></td>' . "\n"
            . ' <td>'
            . ' <input type="submit" name="applyChange" id="applyChange" value="' . get_lang('Ok') . '" />&nbsp;'
            . claro_disp_button($_SERVER['HTTP_REFERER'], get_lang('Cancel'))
            . ' </td>' . "\n"
            . '</tr>' . "\n";
    }

    echo '<tr>' . "\n"
         . '<td>&nbsp;</td>' . "\n"
         . '<td><small>' . get_lang('LegendRequiredFields') . '</small></td>' . "\n"
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
 * Returns an array containing the users'informations who meets the search criteria given in parameters
 *
 * @param $name    the name as a search criteria, leave empty is not needed
 * @param $mail    the mail as a search criteria, leave empty is not needed
 * @param $code    the code as a search criteria, leave empty is not needed
 *
 * @author guillaume Lederer
 *
 */

function user_search($name, $mail, $code, $course_id="")
{
    global $allowSearchInAddUser;

    $tbl_mdb_names   = claro_sql_get_main_tbl();
    $tbl_user        = $tbl_mdb_names['user'];
    $tbl_course_user = $tbl_mdb_names['rel_course_user'];

    if (!empty($course_id))
    {
        $toAdd = ",CU.`user_id` AS registered";
    }
    else
    {
        $toAdd = "";
    }

    $sql =  "SELECT nom,prenom,email,officialCode,U.`user_id` AS user ".$toAdd." FROM `" . $tbl_user . "` AS U";

    if (!empty($course_id)) $sql.= " LEFT JOIN `" . $tbl_course_user . "` AS CU
                                            ON  CU.`user_id`=U.`user_id`
                                            AND CU.`code_cours` = '" . $course_id . "'
                                            ";
    $sql .= " WHERE (0=0) ";

    if ($allowSearchInAddUser)
    {
        $like_search = "%";
    }
    else
    {
        $like_search = "";
    }

    if (!empty($name)) $sql .= " AND (U.`nom` LIKE '". addslashes($name) .$like_search."')";
    if (!empty($mail)) $sql .= " AND (U.`email` LIKE '". addslashes($mail) .$like_search."')";
    if (!empty($code)) $sql .= " AND (U.`officialCode` = '". addslashes($code) ."')";

    $sql .= " ORDER BY nom,prenom ";

    $result = claro_sql_query_fetch_all($sql);
    return $result;
}

?>
