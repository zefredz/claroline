<?php // $Id$
//----------------------------------------------------------------------
// CLAROLINE
//----------------------------------------------------------------------
// Copyright (c) 2001-2004 Universite catholique de Louvain (UCL)
//----------------------------------------------------------------------
// This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
// as published by the FREE SOFTWARE FOUNDATION. The GPL is available
// through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
//----------------------------------------------------------------------
// Authors: see 'credits' file
//----------------------------------------------------------------------

/**
 *     THIS LIBRARY script propose some basic function to administrate the campus :
 *
 *     register a user,
 *     delete a user of the plateform,
 *     unregister a user form a specific course,
 *     remove a user fro ma group,
 *     delete a course of the plateform,
 *     back up a hole course,
 *     change status of a user : admin, prof or student,
 *
 *     ...see details of pre/post for each function's proper use.
 */



/*
 * DB tables initialisation
 */

$tbl_category           = $mainDbName.'`.`faculte';

$tbl_course             = $mainDbName.'`.`cours';
$tbl_courses            = $mainDbName.'`.`cours';

$tbl_courseUser         = $mainDbName.'`.`cours_user';
$tbl_user               = $mainDbName.'`.`user';
$tbl_courses_nodes      = $mainDbName.'`.`faculte';
$tbl_admin              = $mainDbName.'`.`admin';
$tbl_track_default    = $statsDbName."`.`track_e_default";
$tbl_track_login    = $statsDbName."`.`track_e_login";


include_once($includePath."/lib/fileManage.lib.php");

 /**
 * subscribe a specific user to a specific course
 *
 * @author Hugues Peeters <peeters@ipm.ucl.ac.be>
 *
 * @param  int     $userId     user ID from the course_user table
 * @param  string  $courseCode course code from the cours table
 * @param boolean $force_it if true : it means we must'nt check if subcription is the course is set to allowed or not
 *                          if false : (default value) it means we must take account of the subscription setting 
 *
 * @return boolean TRUE        if subscribtion suceed
 *         boolean FALSE       otherwise.
 */

function add_user_to_course($userId, $courseCode, $force_it=false)
{
    global $tbl_user, $tbl_course, $tbl_courseUser;

    if (empty($userId) || empty ($courseCode))
    {
        return false;
    }
    else
    {
        // previously check if the user are already registered on the platform

        $handle = mysql_query("SELECT statut FROM `".$tbl_user."`
                               WHERE user_id = \"".$userId."\" ");

        if (mysql_num_rows($handle) == 0)
        {
            return false; // the user isn't registered to the platform
        }
        else
        {
            // previously check if the user isn't already subscribed to the course

            $handle = mysql_query("SELECT * FROM `".$tbl_courseUser."`
                                   WHERE user_id = \"".$userId."\"
                                   AND code_cours =\"".$courseCode."\"");

            if (mysql_num_rows($handle) > 0)
            {
                return false; // the user is already subscrided to the course
            }
            else
            {
                // previously check if subscribtion is allowed for this course

                $handle = mysql_query( "SELECT code, visible FROM `".$tbl_course."`
                                        WHERE  code = \"".$courseCode."\"
                                        AND    (visible = 0 OR visible = 3)");

                if ((mysql_num_rows($handle) > 0) && !($force_it))
                {
                    return false; // subscribtion not allowed for this course
                }
                elseif ( mysql_query("INSERT INTO `".$tbl_courseUser."`
                                     SET code_cours = \"".$courseCode."\",
                                         user_id    = \"".$userId."\",
                                         statut     = \"5\""))
                {
                    return true;
                }
                else
                {
                    return false;
                }
            }
        }
    }
}



/**
 * unsubscribe a specific user from a specific course
 *
 * @author Hugues Peeters <peeters@ipm.ucl.ac.be>
 *
 * @param  int     $userId     user ID from the course_user table
 * @param  string  $courseCode course code from the cours table
 *
 * @return boolean TRUE        if unsubscribtion succeed
 *         boolean FALSE       otherwise.
 */

function remove_user_from_course($userId, $courseCode)
{
    global  $tbl_courseUser;

    // previously check if the user is not administrator of the course
    // a course administrator can not unsubscribe himself from a course

    $handle = mysql_query (    "SELECT * FROM `".$tbl_courseUser."`
                             WHERE user_id  = \"".$userId."\"
                             AND code_cours = \"".$courseCode."\"
                             AND statut = 1") or die ("problem");

    $numrows = mysql_num_rows($handle);

    if ( $numrows > 0)
    {
        return false; // the user is administrator of the course
    }


    if ( mysql_query("DELETE FROM `".$tbl_courseUser."`
                      WHERE user_id  = \"".$userId."\"
                      AND code_cours = \"".$courseCode."\"") )
    {
        remove_user_from_group($userId, $courseCode);
    }
    return true;
}


/**
 * remove a specific user from a course groups
 *
 * @author Hugues Peeters <peeters@ipm.ucl.ac.be>
 *
 * @param  int     $userId     user ID from the course_user table
 * @param  string  $courseCode course code from the cours table
 *
 * @return boolean TRUE        if removing suceed
 *         boolean FALSE       otherwise.
 */

function remove_user_from_group($userId, $courseCode)
{
	global $dbGlu;
	global $mainDbName;
	global $courseTablePrefix;
	$tbl_courses            = $mainDbName.'`.`cours';
    
	$sql = "SELECT concat(dbName,\"".$dbGlu."\") dbNameGlued FROM `".$tbl_courses."` WHERE code=\"".$courseCode."\"";
	$res = claro_sql_query_fetch_all($sql);
	$tbl_group = $courseTablePrefix.$res[0]['dbNameGlued']."group_rel_team_user";
	
    if ( mysql_query("DELETE FROM `".$tbl_group."`
                      WHERE user = \"".$userId."\""))
    {
        return true;
    }
}



/**
 * delete a user of the plateform
 *
 * @author  Benoit
 *
 * @param the id of the user to delete
 *
 */


function delete_user($su_user_id)
{
   global $tbl_courseUser;
   global $tbl_courses;
   global $tbl_admin;
   global $tbl_courseUser;
   global $tbl_user;
   global $tbl_courses_nodes;
   global $tbl_admin;
   global $tbl_track_default;
   global $tbl_track_login;
   global $courseTablePrefix;
   global $dbGlu;

   $sql_searchCourseData =
        "SELECT
            `c`.`dbName`
        FROM `".$tbl_courseUser."` cu,`".$tbl_courses."` c
        WHERE `cu`.`code_cours`=`c`.`code` AND `cu`.`user_id`='".$su_user_id."'";

        $res_searchCourseData = claro_sql_query_fetch_all($sql_searchCourseData) ;

        //For each course of the user

        if($res_searchCourseData)
        {
            foreach($res_searchCourseData as $one_course)
            {
                $_course["dbNameGlu"]    = $courseTablePrefix . $one_course["dbName"] . $dbGlu; // use in all queries
                $tbl_rel_usergroup       = $_course["dbNameGlu"]."group_rel_team_user";
                $tbl_group               = $_course["dbNameGlu"]."group_team";
                $tbl_userInfo            = $_course["dbNameGlu"]."userinfo_content";


                $tbl_track_access    = $_course["dbNameGlu"]."track_e_access";    // access_user_id
                $tbl_track_downloads = $_course["dbNameGlu"]."track_e_downloads";
                $tbl_track_exercices = $_course["dbNameGlu"]."track_e_exercices";
                //$tbl_track_link      = $_course["dbNameGlu"]."track_e_links";    //links_user_id
                $tbl_track_upload    = $_course["dbNameGlu"]."track_e_uploads";// upload_user_id

                //delete user information in the table group_rel_team_user
                $sql_deleteUserFromGroup = "delete from `$tbl_rel_usergroup` where user='".$su_user_id."'";
                claro_sql_query($sql_deleteUserFromGroup) ;

                //delete user information in the table userinfo_content
                $sql_deleteUserFromGroup = "delete from `$tbl_userInfo` where user_id='".$su_user_id."'";
                claro_sql_query($sql_deleteUserFromGroup) ;

                //change tutor -> NULL for the course where the the tutor is the user deleting
                $sql_update="update `$tbl_group` set tutor=NULL where tutor='".$su_user_id."'";
                claro_sql_query($sql_update) ;

                 $sql_DeleteUser="delete from `$tbl_track_access` where access_user_id='".$su_user_id."'";
                 claro_sql_query($sql_DeleteUser);

                 $sql_DeleteUser="delete from `$tbl_track_downloads` where down_user_id='".$su_user_id."'";
                 claro_sql_query($sql_DeleteUser);

                 $sql_DeleteUser="delete from `$tbl_track_exercices` where exe_user_id='".$su_user_id."'";
                 claro_sql_query($sql_DeleteUser);

                 //$sql_DeleteUser="delete from `$tbl_track_link` where links_user_id='".$su_user_id."'";
                 //claro_sql_query($sql_DeleteUser);

                 $sql_DeleteUser="delete from `$tbl_track_upload` where upload_user_id='".$su_user_id."'";
                 claro_sql_query($sql_DeleteUser);
            }
        }

    //delete the user in the table user
    $sql_DeleteUser="delete from `$tbl_user` where user_id='".$su_user_id."'";
    claro_sql_query($sql_DeleteUser);

    //delete user information in the table course_user
    $sql_DeleteUser="delete from `$tbl_courseUser` where user_id='".$su_user_id."'";
    claro_sql_query($sql_DeleteUser);

    //delete user information in the table admin
    $sql_DeleteUser="delete from `$tbl_admin` where idUser='".$su_user_id."'";
    claro_sql_query($sql_DeleteUser);

    //Change creatorId -> NULL
    $sql_update="update `$tbl_user` set creatorId=NULL where creatorId='".$su_user_id."'";
    claro_sql_query($sql_update);

    //delete user information in the tables clarolineStat
    $sql_DeleteUser="delete from `$tbl_track_default` where default_user_id='".$su_user_id."'";
    claro_sql_query($sql_DeleteUser);

    $sql_DeleteUser="delete from `$tbl_track_login` where login_user_id='".$su_user_id."'";
    claro_sql_query($sql_DeleteUser);

    unset($su_user_id);
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
    global $tbl_courseUser;
    global $tbl_course;
    global $courseTablePrefix;
    global $dbGlu;
    global $coursesRepositorySys;
    global $garbageRepositorySys;

    $sql = "SELECT 
                code         `sysCode`,
			    dbName       ,
	            directory
            FROM `".$tbl_course."` `cours`
            WHERE `code` = '".$code."'";

    $result = claro_sql_query($sql);
    $the_course = mysql_fetch_array($result);

    $currentCourseId           = $the_course['sysCode'];
    $currentCourseDbName       = $the_course['dbName'];
    $currentCourseDbNameGlu    = $courseTablePrefix.$the_course['dbName'].$dbGlu;
    $currentCoursePath         = $the_course['directory'];

    if( !$singleDbEnabled) // IF THE PLATFORM IS IN MULTI DATABASE MODE
    {
        $sql = "DROP DATABASE `".$currentCourseDbName."`";
        claro_sql_query($sql);
    }
    else // IF THE PLATFORM IS IN MONO DATABASE MODE
    {
        // SEARCH ALL TABLES RELATED TO THE CURRENT COURSE
        claro_sql_query("use ".$mainDbName);
        // underscores must be replaced because they are used as wildcards in LIKE sql statement
        $cleanCourseDbNameGlu = str_replace("_","\_", $currentCourseDbNameGlu);
        $sql = "SHOW TABLES LIKE \"".$cleanCourseDbNameGlu."%\"";

        $result= claro_sql_query($sql);
        // DELETE ALL TABLES OF THE CURRENT COURSE
        while( $courseTable = mysql_fetch_array($result,MYSQL_NUM ) )
        {
            $sql = "DROP TABLE ".$courseTable[0]."";
            mysql_query($sql)
                or die('Error in file '.__FILE__.' at line '.__LINE__.' :'.$sql);
        }
    }

    // DELETE THE COURSE INSIDE THE PLATFORM COURSE REGISTERY

        $sql = 'DELETE FROM `'.$tbl_course.'`
                WHERE code= "'.$currentCourseId.'"';

        claro_sql_query($sql);

        // DELETE USER ENROLLMENT INTO THIS COURSE

        $sql = 'DELETE FROM `'.$tbl_courseUser.'`
                WHERE code_cours="'.$currentCourseId.'"';

        claro_sql_query($sql);

        // MOVE THE COURSE DIRECTORY INTO THE COURSE GARBAGE COLLECTOR

        mkPath($garbageRepositorySys);

        @rename($coursesRepositorySys.$currentCoursePath."/",
            $garbageRepositorySys."/".$currentCoursePath.'_'.time());
}

/**
 *  backup a course of the plateform
 *
 * @author
 *
 * @param
 *
 * @return boolean TRUE        if suceed
 *         boolean FALSE       otherwise.
 */

function backup_course($cid)
{

}


/**
 * change the status of a user for a specific course
 *
 * @author Hugues Peeters - peeters@ipm.ucl.ac.be
 * @param  int     $user_id
 * @param  string  $course_id
 * @param  array   $properties - should contain 'role', 'status', 'tutor'
 * @return boolean true if succeed false otherwise
 */

function update_user_course_properties($user_id, $course_id, $properties)
{
    global $tbl_courseUser,$_uid,$is_platformAdmin;
    
    $sqlChangeStatus = "";
    
    if ( ($user_id != $_uid  //do we allow user to change his own settings? what about course without teacher?
	      || $is_platformAdmin 
		 )
		and ($properties['status']=="1" or $properties['status']=="5") 
		)
        $sqlChangeStatus = '`statut` = "'.$properties['status'].'", ';
    
    $result = claro_sql_query('UPDATE `'.$tbl_courseUser.'`
                            SET     `role`       = "'.$properties['role'].'",
                                    '.$sqlChangeStatus.'
                                    `tutor`      = "'.$properties['tutor'].'"
                            WHERE   `user_id`    = "'.$user_id.'"
                            AND     `code_cours` = "'.$course_id.'"');

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
 * @author Hugues Peeters - peeters@ipm.ucl.ac.be
 * @param  int     id of user in DB
 * @param  int     id of course in DB
 * @return boolean true if user is enrolled false otherwise
 */

function isRegisteredTo($user_id, $course_id)
{
    global $tbl_courseUser;

    $sql = "SELECT *
                 FROM `".$tbl_courseUser."`
                 WHERE `code_cours` = '".$course_id."' AND `user_id` = '".$user_id."'";

    $result = claro_sql_query($sql);
    $list = mysql_fetch_array($result);
    if (mysql_num_rows($result)>0) {return true;} else {return false;}
}


function treatNotAuthorized()
{
 claro_disp_auth_form();
}

/**
 * function to transfrom a key word into a usable key word ina SQL : "*" must be replaced by "%" and "%" by "\%"
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
