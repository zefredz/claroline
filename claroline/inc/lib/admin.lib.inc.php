<?php // $Id$
//----------------------------------------------------------------------
// CLAROLINE 1.6.*
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
 *     Add users with CSV files
 *     ...see details of pre/post for each function's proper use.
 */
/*
 * DB tables initialisation
 */

include_once( dirname(__FILE__) . '/fileManage.lib.php');
include_once( dirname(__FILE__) . '/auth.lib.inc.php');


$tbl_mdb_names = claro_sql_get_main_tbl();
$tbl_course           = $tbl_mdb_names['course'          ];
$tbl_rel_course_user  = $tbl_mdb_names['rel_course_user' ];
$tbl_user             = $tbl_mdb_names['user'            ];
$tbl_admin            = $tbl_mdb_names['admin'           ];
$tbl_category         = $tbl_mdb_names['category'        ];
$tbl_rel_class_user   = $tbl_mdb_names['rel_class_user'  ];
$tbl_track_default    = $tbl_mdb_names['track_e_default'];
$tbl_track_login      = $tbl_mdb_names['track_e_login'];

// List of alias  to track an set at original name
$tbl_courseUser         = $tbl_rel_course_user ;
$tbl_courses_nodes      = $tbl_category;
// End of List of alias  to track an set at original name

 /**
 * Create a new user in Claroline with the specified informations
 *
 * @author Guillaume Lederer <lederer@cerdecam.be>
 *
 * @param  string  $name name of the new user                 
 * @param  string  $surname surname of the new user              
 * @param  string  $email email of the new user                 
 * @param  string  $admincode official code of the new user           
 * @param  string  $name of the new user               
 * @param  string  $password password of the new user               
 * @param  boolean $teacher :                                     
 *                      TRUE  if new user must be a teacher,
 *                      FALSE otherwise,              
 * 
 * @return $_UID : id of the new user if creation succeeded, FALSE otherwise
 * 
 */

function add_user($name,$surname,$email,$phone,$admincode,$username,$password,$teacher)
{
    // see if password must be stored crypted or not
    $tbl_mdb_names = claro_sql_get_main_tbl();
    $tbl_user      = $tbl_mdb_names['user'];
    global $userPasswordCrypted;
    if (!isset($userPasswordCrypted))  $userPasswordCrypted	 = false;
    // set the status DB code with the value of $teacher

    if ($teacher==TRUE) 
    {
        $status = 1;
    }
    else
    {
        $status = 5;
    }

    $sql = "INSERT INTO `".$tbl_user."`
            SET `nom`          = \"".$name."\",
                `prenom`       = \"".$surname."\",
                `username`     = \"".$username."\",
                `password`     = \"".($userPasswordCrypted?md5($password):$password)."\",
                `email`        = \"".$email."\",
                `statut`       = \"".$status."\",
                `officialCode` = \"".$admincode."\",
                `phoneNumber`  = \"".$phone."\"";

    $_uid = claro_sql_query_insert_id($sql);
    return $_uid;
}

/**
 * Set a user as admin
 * @author Christophe Gesché <moosh@claroline.net>
 * @version 1.6
 * @param $idAdmin Id of user to set as admin
 *
 * @return void
 */
function set_user_admin($idAdmin)
{
    $tbl_mdb_names = claro_sql_get_main_tbl();
    $tbl_admin     = $tbl_mdb_names['admin'];
    $tbl_user      = $tbl_mdb_names['user'];
	
    $sql = "INSERT INTO `".$tbl_admin."` (idUser) VALUES (".$idAdmin.")";
    claro_sql_query($sql);
    //adduser in .htaccess
}


/**
 * subscribe a specific user to a specific course
 *
 * @author Hugues Peeters <peeters@ipm.ucl.ac.be>
 *
 * @param int     $userId     user ID from the course_user table
 * @param string  $courseCode course code from the cours table
 * @param boolean $force_it if true  : it means we must'nt check if subcription is the course is set to allowed or not
 *                          if false : (default value) it means we must take account of the subscription setting 
 *
 * @return boolean TRUE        if subscribtion succeed
 *         boolean FALSE       otherwise.
 */

function add_user_to_course($userId, $courseCode, $force_it=false)
{
	$tbl_mdb_names = claro_sql_get_main_tbl();
	$tbl_course           = $tbl_mdb_names['course'           ];
	$tbl_rel_course_user  = $tbl_mdb_names['rel_course_user'  ];
	$tbl_user             = $tbl_mdb_names['user'             ];

    if (empty($userId) || empty ($courseCode))
    {
        return false;
    }
    else
    {
        // previously check if the user are already registered on the platform
		$sql = 'SELECT `statut` `status` FROM `'.$tbl_user.'`
                               WHERE user_id = "'.$userId.'"';
        $handle = claro_sql_query($sql);

        if (mysql_num_rows($handle) == 0)
        {
            return false; // the user isn't registered to the platform
        }
        else
        {
            // previously check if the user isn't already subscribed to the course

            $handle = claro_sql_query("SELECT * FROM `".$tbl_rel_course_user."`
                                   WHERE `user_id` = \"".$userId."\"
                                   AND `code_cours` =\"".$courseCode."\"");

            if (mysql_num_rows($handle) > 0)
            {
                return false; // the user is already subscrided to the course
            }
            else
            {
                // previously check if subscribtion is allowed for this course

                $handle = claro_sql_query( "SELECT `code`, `visible` FROM `".$tbl_course."`
                                        WHERE  `code` = \"".$courseCode."\"
                                        AND    (`visible` = 0 OR `visible` = 3)");

                if ((mysql_num_rows($handle) > 0) && !($force_it))
                {
                    return false; // subscribtion not allowed for this course
                }
                elseif ( claro_sql_query("INSERT INTO `".$tbl_rel_course_user."`
                                     SET `code_cours` = \"".$courseCode."\",
                                         `user_id`    = \"".$userId."\",
                                         `statut`     = \"5\""))
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
	$tbl_mdb_names = claro_sql_get_main_tbl();
	$tbl_rel_course_user  = $tbl_mdb_names['rel_course_user'  ];

    // previously check if the user is not administrator of the course
    // a course administrator can not unsubscribe himself from a course
	$sql = 'SELECT * 
			FROM `'.$tbl_rel_course_user.'`
            WHERE 	user_id  = "'.$userId.'"
            	AND code_cours = "'.$courseCode.'"
            	AND statut = "1"';

    $handle = claro_sql_query($sql);

    $numrows = mysql_num_rows($handle);

    if ( $numrows > 0)
    {
        return false; // the user is administrator of the course
    }

	$sql = 'DELETE FROM `'.$tbl_rel_course_user.'`
                      WHERE user_id  = "'.$userId.'"
                      AND code_cours = "'.$courseCode.'"';
    if ( claro_sql_query($sql) )
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
    global $courseTablePrefix, $tbl_course;

    $sql = "SELECT CONCAT(dbName,\"".$dbGlu."\") dbNameGlued 
            FROM `".$tbl_course."` 
            WHERE code=\"".$courseCode."\"";

    $res = claro_sql_query_fetch_all($sql);

    $tbl_group = $courseTablePrefix.$res[0]['dbNameGlued']."group_rel_team_user";

    if ( mysql_query("DELETE FROM `".$tbl_group."`
                      WHERE user = \"".$userId."\""))
    {
        return true;
    }
}

/**
 * remove a specific user from a course groups
 *
 * @author Guillaume Lederer <lederer@cerdecam.be>
 *
 * @param  int     $userId     user ID from the course_user table
 * @param  int     $classId    class ID  from the rel_class_user table
 *
 * @return boolean TRUE        if subscribe suceeded
 *         boolean FALSE       otherwise.
 */
 
function add_user_to_class($userId, $classId)
{
    $tbl_mdb_names                  = claro_sql_get_main_tbl();
    $tbl_user                       = $tbl_mdb_names['user'];
    $tbl_rel_class_user             = $tbl_mdb_names['rel_class_user'];
    $tbl_class                      = $tbl_mdb_names['class'];
    
    //1. See if there is a user with such ID in the main DB (not implemented)     
    
    //2. See if there is a class with such ID in the main DB
    
    $sql = "SELECT * FROM `".$tbl_class."` WHERE `id` = '".$classId."' ";
    $handle = claro_sql_query($sql);

    if (mysql_num_rows($handle) == 0)
    {
        return false; // the class does not exist
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
 * delete a user of the plateform
 *
 * @author  Benoit
 *
 * @param the id of the user to delete
 *
 */


function delete_user($su_user_id)
{
   global $tbl_rel_course_user;
   global $tbl_course;
   global $tbl_admin;
   global $tbl_courseUser;
   global $tbl_user;
   global $tbl_courses_nodes;
   global $tbl_admin;
   global $tbl_track_default;
   global $tbl_track_login;
   global $courseTablePrefix;
   global $dbGlu;
   global $tbl_rel_class_user;

   $sql_searchCourseData =
        "SELECT
            `c`.`dbName`
        FROM `".$tbl_rel_course_user."` cu,`".$tbl_course."` c
        WHERE `cu`.`code_cours`=`c`.`code` AND `cu`.`user_id`='".$su_user_id."'";

        $res_searchCourseData = claro_sql_query_fetch_all($sql_searchCourseData) ;

	//delete the info in the class table

        $sql_DeleteUser="delete from `".$tbl_rel_class_user."` where user_id='".$su_user_id."'";       
        claro_sql_query($sql_DeleteUser);
        
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
            $tbl_track_upload    = $_course["dbNameGlu"]."track_e_uploads";// upload_user_id

            //delete user information in the table group_rel_team_user
            $sql_deleteUserFromGroup = "delete from `".$tbl_rel_usergroup."` where user='".$su_user_id."'";
            claro_sql_query($sql_deleteUserFromGroup) ;

            //delete user information in the table userinfo_content
            $sql_deleteUserFromGroup = "delete from `".$tbl_userInfo."` where user_id='".$su_user_id."'";
            claro_sql_query($sql_deleteUserFromGroup) ;

            //change tutor -> NULL for the course where the the tutor is the user deleting
            $sql_update="update `".$tbl_group."` set tutor=NULL where tutor='".$su_user_id."'";
            claro_sql_query($sql_update) ;

            $sql_DeleteUser="delete from `".$tbl_track_access."` where access_user_id='".$su_user_id."'";
            claro_sql_query($sql_DeleteUser);

            $sql_DeleteUser="delete from `".$tbl_track_downloads."` where down_user_id='".$su_user_id."'";
            claro_sql_query($sql_DeleteUser);

            $sql_DeleteUser="delete from `".$tbl_track_exercices."` where exe_user_id='".$su_user_id."'";
            claro_sql_query($sql_DeleteUser);

            $sql_DeleteUser="delete from `".$tbl_track_upload."` where upload_user_id='".$su_user_id."'";
            claro_sql_query($sql_DeleteUser);
        }
    }

    //delete the user in the table user
    $sql_DeleteUser="delete from `".$tbl_user."` where user_id='".$su_user_id."'";
    claro_sql_query($sql_DeleteUser);

    //delete user information in the table course_user
    $sql_DeleteUser="delete from `".$tbl_rel_course_user."` where user_id='".$su_user_id."'";
    claro_sql_query($sql_DeleteUser);

    //delete user information in the table admin
    $sql_DeleteUser="delete from `".$tbl_admin."` where idUser='".$su_user_id."'";
    claro_sql_query($sql_DeleteUser);

    
    
    //Change creatorId -> NULL
    $sql_update="update `".$tbl_user."` set creatorId=NULL where creatorId='".$su_user_id."'";
    claro_sql_query($sql_update);

    //delete user information in the tables clarolineStat
    $sql_DeleteUser="delete from `".$tbl_track_default."` where default_user_id='".$su_user_id."'";
    claro_sql_query($sql_DeleteUser);

    $sql_DeleteUser="delete from `".$tbl_track_login."` where login_user_id='".$su_user_id."'";
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
    global $courseTablePrefix;
    global $dbGlu;
    global $coursesRepositorySys;
    global $garbageRepositorySys;

	//declare needed tables
	$tbl_mdb_names = claro_sql_get_main_tbl();
	$tbl_course           = $tbl_mdb_names['course'           ];
	$tbl_rel_course_user  = $tbl_mdb_names['rel_course_user'  ];
	$tbl_course_nodes     = $tbl_mdb_names['category'         ];

    $sql = "SELECT `code`      `code`, 
                   `dbName`    `dbName`, 
                   `directory` `directory`, 
                   `fake_code` `officialCode`, 
                   `intitule`   `name`
            FROM `".$tbl_course."`
            WHERE `code` = '".$code."'";

    $this_course = claro_sql_query_fetch_all($sql);
    
    
    $currentCourseId        = $this_course[0]['code'];
    
    if ($currentCourseId == $code)
    {
        $currentCourseDbName    = $this_course[0]['dbName'];
        $currentCourseDbNameGlu = $courseTablePrefix.$this_course[0]['dbName'].$dbGlu;
        $currentCoursePath      = $this_course[0]['directory'];
        $currentCourseCode      = $this_course[0]['officialCode'];
        $currentCourseName      = $this_course[0]['name'];
    
        if($singleDbEnabled) 
        // IF THE PLATFORM IS IN MONO DATABASE MODE
        {
            // SEARCH ALL TABLES RELATED TO THE CURRENT COURSE
            claro_sql_query("use ".$mainDbName);
            $tbl_to_delete = claro_sql_get_course_tbl($currentCourseDbNameGlu);
            foreach($tbl_to_delete as $tbl_name)
            {
                $sql = 'DROP TABLE `'.$tbl_name.'`';
                claro_sql_query($sql);
            }
            // underscores must be replaced because they are used as wildcards in LIKE sql statement
            $cleanCourseDbNameGlu = str_replace("_","\_", $currentCourseDbNameGlu);
            $sql = 'SHOW TABLES LIKE "'.$cleanCourseDbNameGlu.'%"';
    
            $result = claro_sql_query($sql);
            // DELETE ALL TABLES OF THE CURRENT COURSE
    
            $tblSurvivor = array();
            while( $courseTable = mysql_fetch_array($result,MYSQL_NUM ) )
            {
                $tblSurvivor[]=$courseTable[0];
                //$tblSurvivor[$courseTable]='not deleted';
            }
            if (sizeof($tblSurvivor)>0)
            {
                 event_default( "DELETE_COURSE"
                             , array_merge(array ("DELETED_COURSE_CODE"=>$code
                                                 ,"UNDELETED_TABLE_COUNTER"=>sizeof($tblSurvivor)
                                                 )
                                          , $tblSurvivor )
                              );
            }
        }
        else 
        // IF THE PLATFORM IS IN MULTI DATABASE MODE
        {
            $sql = "DROP DATABASE `".$currentCourseDbName."`";
            claro_sql_query($sql);
        }
        
        // DELETE THE COURSE INSIDE THE PLATFORM COURSE REGISTERY
        
        $sql = 'DELETE FROM `'.$tbl_course.'`
                WHERE code= "'.$currentCourseId.'"';
        
        claro_sql_query($sql);
        
        // DELETE USER ENROLLMENT INTO THIS COURSE
        
        $sql = 'DELETE FROM `'.$tbl_rel_course_user.'`
                WHERE code_cours="'.$currentCourseId.'"';
        
        claro_sql_query($sql);
    
        // MOVE THE COURSE DIRECTORY INTO THE COURSE GARBAGE COLLECTOR
        
        claro_mkdir($garbageRepositorySys, 0777, true);
        
        rename($coursesRepositorySys.$currentCoursePath."/",
               $garbageRepositorySys."/".$currentCoursePath.'_'.date('YmdHis')
              );
     }
     else
     {
        die('WRONG CID');
     }
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
	           
    $result = claro_sql_query($sql) or die ("CANNOT UPDATE DB !");

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
function is_registered_to($user_id, $course_id)
{
 
    $tbl_mdb_names = claro_sql_get_main_tbl();
	$tbl_rel_course_user = $tbl_mdb_names['rel_course_user'];

    $sql = "SELECT count(*) `user_reg`
                 FROM `".$tbl_rel_course_user."`
                 WHERE `code_cours` = '".$course_id."' AND `user_id` = '".$user_id."'";
    $res = claro_sql_query_fetch_all($sql);
    return (bool) ($res[0]['user_reg']>0);
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
