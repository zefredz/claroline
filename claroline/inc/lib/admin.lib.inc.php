<?php // $Id$
/**
 * CLAROLINE
 *
 *     THIS LIBRARY script propose some basic function to administrate the campus :
 *
 *     register a user,
 *     delete a user of the plateform,
 *     unregister a user form a specific course,
 *     remove a user fro ma group,
 *     delete a course of the plateform,
 *     back up a hole course,
 *     change status of a user : admin, prof or student,
 *     Add users with CSV files
 *     ...see details of pre/post for each function's proper use.
 *
 * @version 1.7 $Revision$
 * 
 * @copyright (c) 2001-2005 Universite catholique de Louvain (UCL)
 * 
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE 
 *
 * @see http://www.claroline.net/wiki/CLCRS/
 *
 * @package COURSE
 *
 * @author Claro Team <cvs@claroline.net>
 * @author Christophe Gesché <moosh@claroline.net>
 *
 */

include_once( dirname(__FILE__) . '/fileManage.lib.php');
include_once( dirname(__FILE__) . '/auth.lib.inc.php');

/**
 * remove a specific user from a course groups
 *
 * @param  int     $userId     user ID from the course_user table
 * @param  int     $classId    class ID  from the rel_class_user table
 *
 * @return boolean TRUE        if subscribe suceeded
 *         boolean FALSE       otherwise.
 * @author Guillaume Lederer <lederer@cerdecam.be>
 */

function add_user_to_class($userId, $classId)
{
    $tbl_mdb_names                  = claro_sql_get_main_tbl();
    $tbl_user                       = $tbl_mdb_names['user'];
    $tbl_rel_class_user             = $tbl_mdb_names['rel_class_user'];
    $tbl_class                      = $tbl_mdb_names['class'];

    //1. See if there is a user with such ID in the main DB (not implemented)
    $user_data = user_get_data($userId);
    if (!$user_data)
    {
        return claro_failure::get_last_failure();
    }
    //2. See if there is a class with such ID in the main DB

    $sql = "SELECT * FROM `" . $tbl_class . "` WHERE `id` = '" . $classId . "' ";
    $handle = claro_sql_query($sql);

    if (mysql_num_rows($handle) == 0)
    {
        return claro_failure::set_failure('CLASS_NOT_FOUND'); // the class does not exist
    }

    //3. See if user is not already in class

    $sql = "SELECT * FROM `".$tbl_rel_class_user."` WHERE `user_id` = '".$userId."' ";
    $handle = claro_sql_query($sql);

    if (mysql_num_rows($handle) > 0)
    {
        return false; // the user is already subscrided to the class
    }

    //4. Add user to class in the rel_class_user table

    $sql = "INSERT INTO `".$tbl_rel_class_user."`
	       SET `user_id` = '".$userId."',
	           `class_id` = '".$classId."' "; 
    claro_sql_query($sql);
    return true;
}

/**
 * delete a course of the plateform
 *
 * @author
 *
 * @param
 *
 * @return boolean TRUE        if suceed
 *         boolean FALSE       otherwise.
 */

function delete_course($code)
{
    global $mainDbName;
    global $singleDbEnabled;
    global $courseTablePrefix;
    global $dbGlu;
    global $coursesRepositorySys;
    global $garbageRepositorySys;

    //declare needed tables
    $tbl_mdb_names = claro_sql_get_main_tbl();
    $tbl_course           = $tbl_mdb_names['course'           ];
    $tbl_rel_course_user  = $tbl_mdb_names['rel_course_user'  ];

    $this_course = claro_get_course_data($code);
    $currentCourseId = $this_course['sysCode'];

    // DELETE USER ENROLLMENT INTO THIS COURSE

    $sql = 'DELETE FROM `' . $tbl_rel_course_user . '`
            WHERE code_cours="' . $currentCourseId . '"';

    claro_sql_query($sql);

    // DELETE THE COURSE INSIDE THE PLATFORM COURSE REGISTERY

    $sql = 'DELETE FROM `' . $tbl_course . '`
            WHERE code= "' . $currentCourseId . '"';

    claro_sql_query($sql);


    if ($currentCourseId == $code)
    {
        $currentCourseDbName    = $this_course['dbName'];
        $currentCourseDbNameGlu = $this_course['dbNameGlu'];
        $currentCoursePath      = $this_course['path'];

        if($singleDbEnabled)
        // IF THE PLATFORM IS IN MONO DATABASE MODE
        {
            // SEARCH ALL TABLES RELATED TO THE CURRENT COURSE
            claro_sql_query("use " . $mainDbName);
            $tbl_to_delete = claro_sql_get_course_tbl(claro_get_course_db_name_glued($currentCourseId));
            foreach($tbl_to_delete as $tbl_name)
            {
                $sql = 'DROP TABLE IF EXISTS `' . $tbl_name . '`';
                claro_sql_query($sql);
            }
            // underscores must be replaced because they are used as wildcards in LIKE sql statement
            $cleanCourseDbNameGlu = str_replace("_","\_", $currentCourseDbNameGlu);
            $sql = 'SHOW TABLES LIKE "' . $cleanCourseDbNameGlu . '%"';

            $result = claro_sql_query($sql);
            // DELETE ALL TABLES OF THE CURRENT COURSE

            $tblSurvivor = array();
            while( $courseTable = mysql_fetch_array($result,MYSQL_NUM ) )
            {
                $tblSurvivor[]=$courseTable[0];
                //$tblSurvivor[$courseTable]='not deleted';
            }
            if (sizeof($tblSurvivor) > 0)
            {
                event_default( 'DELETE_COURSE'
                , array_merge(array ('DELETED_COURSE_CODE'=>$code
                ,'UNDELETED_TABLE_COUNTER'=>sizeof($tblSurvivor)
                )
                , $tblSurvivor )
                );
            }
        }
        else
        // IF THE PLATFORM IS IN MULTI DATABASE MODE
        {
            $sql = "DROP DATABASE `" . $currentCourseDbName . "`";
            claro_sql_query($sql);
        }

        // MOVE THE COURSE DIRECTORY INTO THE COURSE GARBAGE COLLECTOR

        claro_mkdir($garbageRepositorySys, 0777, true);

        rename($coursesRepositorySys . $currentCoursePath . '/',
        $garbageRepositorySys . '/' . $currentCoursePath . '_' . date('YmdHis')
        );
    }
    else
    {
        die('WRONG CID');
    }
}

/**
 * change the status of a user for a specific course
 *
 * @author Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @param  int     $user_id
 * @param  string  $course_id
 * @param  array   $properties - should contain 'role', 'status', 'tutor'
 * @return boolean true if succeed false otherwise
 */

function update_user_course_properties($user_id, $course_id, $properties)
{
    global $_uid;

    //declare needed tables

    $tbl_mdb_names = claro_sql_get_main_tbl();
    $tbl_rel_course_user  = $tbl_mdb_names['rel_course_user'  ];

    $sqlChangeStatus = "";
    if (($properties['status']=="1" or $properties['status']=="5"))
    {
        $sqlChangeStatus = "`statut` = \"".$properties['status']."\",";
    }

    $sql = "UPDATE `".$tbl_rel_course_user."`
            SET     `role`       = \"".$properties['role']."\",
           ".$sqlChangeStatus."
           `tutor`      = \"".$properties['tutor']."\"
           WHERE   `user_id`    = \"".$user_id."\"
           AND     `code_cours` = \"".$course_id."\"";

    claro_sql_query($sql) or die ("CANNOT UPDATE DB !");

    if (mysql_affected_rows() > 0)
    {
        return true;
    }
    else
    {
        return false;
    }
}

/**
 * to know if user is registered to a course or not
 *
 * @author Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @param  int     id of user in DB
 * @param  int     id of course in DB
 * @return boolean true if user is enrolled false otherwise
 */
function is_registered_to($user_id, $course_id)
{

    $tbl_mdb_names = claro_sql_get_main_tbl();
    $tbl_rel_course_user = $tbl_mdb_names['rel_course_user'];

    $sql = "SELECT count(*) `user_reg`
                 FROM `" . $tbl_rel_course_user . "`
                 WHERE `code_cours` = '" . $course_id . "' AND `user_id` = '".$user_id."'";
    $res = claro_sql_query_fetch_all($sql);
    return (bool) ($res[0]['user_reg']>0);
}

/**
 * Transfrom a key word into a usable key word ina SQL : "*" must be replaced by "%" and "%" by "\%"
 * @param  the string to transform
 * @return the string modified
 */

function pr_star_replace($string)
{
    $string = str_replace("%",'\%', $string);
    $string = str_replace("*",'%', $string);
    return $string;
}

?>
