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
$tbl_track_default      = $statsDbName."`.`track_e_default";
$tbl_track_login        = $statsDbName."`.`track_e_login";

include_once($includePath."/lib/fileManage.lib.php");

/**
 * @author Hugues Peeters <peeters@ipm.ucl.ac.be>
 * INVERT A MATRIX function :
 * 
 * this function allows to invert cols and rows of a 2D array
 */

function array_swap_cols_and_rows( $OrigMatrix, $presumedColKeyList)
{
    $RevertedMatrix = array();

    foreach($OrigMatrix as $thisRow)
    {
        $ActualColKeyList = array();

        foreach($thisRow as $thisColKey => $thisColValue)
        {
            $RevertedMatrix[$thisColKey][] = $thisColValue;

            $actualColKeyList[] = $thisColKey;
        }

        // IN case of missing columns, fill them with NULL

        $missingColKeyList = array_diff($presumedColKeyList, $actualColKeyList);

        if (count($missingColKeyList) > 0)
        {
            foreach($missingColKeyList as $thisColKey)
            {
                $RevertedMatrix[$thisColKey][] = NULL;
            }
           
        }
    }
    return $RevertedMatrix;
} 


/**
 * Check EMAIL SYNTHAX : if a new users in Claroline with the specified parameters contains synthax error
 * in mail given.
 *
 * @author Guillaume Lederer <lederer@cerdecam.be>
 *
 * @param  $userlist must be a 2D array with the list of potential new users :
 *         $userlist['email'][$i] for the email  
 *
 *
 * @return $errors : an array of boolean where $errors[$i] is TRUE if there is an error with entry $i in the given array.
 *
 * 
 */

function check_email_synthax_userlist($userlist)
{
    global $tbl_user;        
    $errors = array();
  
    //CHECK: check email validity
    
    $regexp = "^[0-9a-z_\.-]+@(([0-9]{1,3}\.){3}[0-9]{1,3}|([0-9a-z][0-9a-z-]*[0-9a-z]\.)+[a-z]{2,4})$";
    
    for ($i=0, $size=sizeof($userlist['email']); $i<$size; $i++)
    {
        
	if ((!empty($userlist['email'][$i])) && !eregi( $regexp, $userlist['email'][$i] )) 
        {
	    $errors[$i] = TRUE;
        }
    }
    
    return $errors;
}

 /**
 * Check USERNAME NOT TAKEN YET : check if usernames are not already token by someone else
 *
 * @author Guillaume Lederer <lederer@cerdecam.be>
 *
 * @param  $userlist must be a 2D array with the list of potential new users :
 *         $userlist['username'][$i]   for the username
 *
 * @return $errors : an array of boolean where $errors[$i] is TRUE if there is an error with entry $i in the given array.
 *
 * 
 */
 
function check_username_used_userlist($userlist)
{   
    global $tbl_user;        
    $errors = array();
    
    //CHECK : check if usernames are not already token by someone else
    
    $sql = "SELECT * FROM `".$tbl_user."` WHERE 1=0 ";
    
    for ($i=0, $size=sizeof($userlist['username']); $i<$size; $i++)
    {
        if (!empty($userlist['username'][$i]) && ($userlist['username'][$i]!=""))
	{
	    $sql .= " OR username=\"".addslashes($userlist['username'][$i])."\"";
	}  
    }  
    
    //echo $sql."<br>\n";
    
    //for each user found, report the potential problem in an error array returned
    
    $foundUser = claro_sql_query($sql);
    
    while ($list = mysql_fetch_array($foundUser))
    {
	
	$found = array_search($list['username'],$userlist['username']);
		
	if (!($found===FALSE)) 
	{
	    $errors[$found] = TRUE; 
	}
    }
    return $errors;
}
 
 /**
 * Check OFFICIAL CODE NOT TAKEN YET : check if admincode (officialCode) is not already taken by someone else
 *
 * @author Guillaume Lederer <lederer@cerdecam.be>
 *
 * @param  $userlist must be a 2D array with the list of potential new users :
 *         
 *         $userlist['officialCode'][$i]  for the officialCode
 *
 * @return $errors : an array of boolean where $errors[$i] is TRUE if there is an error with entry $i in the given array.
 *
 * 
 */
    
function check_officialcode_used_userlist($userlist)
{
    global $tbl_user;        
    $errors = array();
    
    //CHECK : check if admincode (officialCode) is not already taken by someone else
    
    $sql = "SELECT * FROM `".$tbl_user."` WHERE 1=0 ";
    
    for ($i=0, $size=sizeof($userlist['officialCode']); $i<$size; $i++) 
    {
        if (!empty($userlist['officialCode'][$i]) && ($userlist['officialCode'][$i]!=""))
	{
	    $sql .= " OR officialCode=\"".addslashes($userlist['officialCode'][$i])."\"";
	}  
    }
    
    //for each user found, report the potential problem
    
    $foundUser = claro_sql_query($sql);
    
    //echo $sql."<br>\n";
    
    while ($list = mysql_fetch_array($foundUser))
    {
	$found = array_search($list['officialCode'],$userlist['officialCode']);
		
	if (!($found===FALSE)) 
	{
	    $errors[$found] = TRUE; 
	}
    }   
    return $errors;
}    
     
/**
 * Check PASSWORD ACCEPTABLE  : check if password is sufficently complex for this user
 *
 * @author Guillaume Lederer <lederer@cerdecam.be>
 *
 * @param  $userlist must be a 2D array with the list of potential new users :
 *
 *         $userlist['username'][$i]   for the username
 *         $userlist['password'][$i]   for the password
 *
 * @return $errors : an array of boolean where $errors[$i] is TRUE if there is an error with entry $i in the given array.
 *
 * 
 */ 
 
 function check_password_userlist($userlist)
 {
     $errors = array();
     
     for ($i=0, $size=sizeof($userlist['password']); $i<$size; $i++) 
     {
        if ($userlist['password'][$i]==$userlist['username'][$i])
	{
	    $errors[$i] = TRUE; 
	}  
     }
          
     return $errors;
 }   
       
 /**
 * Check EMAIL NOT TAKEN YET : check if the e-mails are not already taken by someone in the plateform
 *
 * @author Guillaume Lederer <lederer@cerdecam.be>
 *
 * @param  $userlist must be a 2D array with the list of potential new users :
 *
 *         $userlist['email'][$i] for the email
 *
 * @return $errors : an array of boolean where $errors[$i] is TRUE if there is an error with entry $i in the given array.
 *
 * 
 */    
    
function check_mail_used_userlist($userlist)
{
    global $tbl_user;        
    $errors = array();
    
    $sql = "SELECT * FROM `".$tbl_user."` WHERE 1=0 ";
    
    for ($i=0, $size=sizeof($userlist['email']); $i<$size; $i++) 
    {
        if (!empty($userlist['email'][$i]) && ($userlist['email'][$i]!=""))
	{
	    $sql .= " OR email=\"".addslashes($userlist['email'][$i])."\"";
	}  
    }
    
        //for each user found, report the potential problem for email
    
    $foundUser = claro_sql_query($sql);
    
    while ($list = mysql_fetch_array($foundUser))
    {
        
	$found = array_search($list['email'],$userlist['email']);
		
	if (!($found===FALSE)) 
	{
	    $errors[$found] = TRUE; 
	}
    }
    
    //echo $sql."<br>\n";
     
    return $errors; 
}

/**
 * Check DUPLICATE EMAIL OF ADDABLE USERS : take the 2D array in param and check if  email 
 * are all different.
 *
 * @author Guillaume Lederer <lederer@cerdecam.be>
 *
 * @param  $userlist must be a 2D array with the list of potential new users :
 *         $userlist['email'][$i] for the email  
 *         $userlist['username'][$i] for the username
 *         $userlist['officialCode'][$i]  for the officialCod
 *
 * @return $errors : an array of boolean where $errors[$i] is TRUE if there is an error with entry $i in the given 2D array.
 *
 * 
 */

function check_duplicate_mail_userlist($userlist)
{
    global $tbl_user;        
    $errors = array();
        
    for ($i=0, $size=sizeof($userlist['name']); $i<$size; $i++)
    {       
        //check email duplicata in the array
    
	$found = array_search($userlist['email'][$i],$userlist['email']);
	
	if (!($found===FALSE) && ($i!=$found))
        {
	    $errors[$i] = TRUE;
        }
    }
       
    return $errors;
}

/**
 * Check DUPLICATE USERNAMES OF ADDABLE USERS : take the 2D array in param and check if username are all different.
 *
 * @author Guillaume Lederer <lederer@cerdecam.be>
 *
 * @param  $userlist must be a 2D array with the list of potential new users :
 *         $userlist['email'][$i] for the email  
 *         $userlist['username'][$i] for the username
 *         $userlist['officialCode'][$i]  for the officialCod
 *
 * @return $errors : an array of boolean where $errors[$i] is TRUE if there is an error with entry $i in the given 2D array.
 *
 * 
 */

function check_duplicate_username_userlist($userlist)
{
    global $tbl_user;        
    $errors = array();
        
    for ($i=0, $size=sizeof($userlist['username']); $i<$size; $i++)
    {       
        //check username duplicata in the array
    
	$found = array_search($userlist['username'][$i],$userlist['username']);
	
	if (!($found===FALSE) && ($i!=$found))
        {
	    $errors[$i] = TRUE;
        }
    }
       
    return $errors;
}

/**
 * Check DUPLICATE OFFICIAL CODE OF ADDABLE USERS : take the 2D array in param and check if official codes are all different.
 *
 * @author Guillaume Lederer <lederer@cerdecam.be>
 *
 * @param  $userlist must be a 2D array with the list of potential new users :
 *         $userlist['email'][$i] for the email  
 *         $userlist['username'][$i] for the username
 *         $userlist['officialCode'][$i]  for the officialCod
 *
 * @return $errors : an array of boolean where $errors[$i] is TRUE if there is an error with entry $i in the given 2D array.
 *
 * 
 */

function check_duplicate_officialcode_userlist($userlist)
{
    global $tbl_user;        
    $errors = array();
        
    for ($i=0, $size=sizeof($userlist['username']); $i<$size; $i++)
    {       
        //check username duplicata in the array
    
	$found = array_search($userlist['username'][$i],$userlist['username']);
	
	if (!($found===FALSE) && ($i!=$found))
        {
	    $errors[$i] = TRUE;
        }
    }
       
    return $errors;
}
/**
 * Class needed for parsing CSV files
 *
 *
 */
   class CSV
   {
       var $raw_data;
       var $new_data;
       var $mapping;
       var $results = array();
       var $errors = array();
      
       function CRLFclean()
       {
           $replace = array(
               "\n",
               "\r",
               "\r\n"
           );
           $this->raw_data = str_replace($replace,"\n",$this->raw_data);
       }
      
       function validKEY($v)
       {
           return ereg_replace("[^a-zA-Z0-9_\s]","",$v);
       }
      
       function stripENCLOSED(&$v,$eb)
       {
           if($eb!="" AND strpos($v,$eb)!==false)
             {
                   if($v[0]==$eb)
                       $v = substr($v,1,strlen($v));
                   if($v[strlen($v)-1]==$eb)
                       $v = substr($v,0,-1);
                   $v = stripslashes($v);
               }
               else
                   return;
       }

       function CSV($filename,$delim,$linedef,$enclosed_by="\"",$eol="\n")
           {
               //open the file
               $this->raw_data = implode("",file($filename));
              
               // make sure all CRLF's are consistent
               $this->CRLFclean();
              
               // use custom $eol (if exists)
               if($eol!="\n" AND trim($eol)=="")
                   $this->error("Couldn't split data via empty \$eol, please specify a valid end of line character.");
               else
               {
                   $this->new_data = @explode($eol,$this->raw_data);
                   if(count($this->new_data)==0)
                       $this->error("Couldn't split data via given \$eol.<li>\$eol='".$eol."'");
               }
               
               // create data keys with the line definition given in params, 
	       // if linedef is not define, take first line of file to define it
               
	       if (($linedef=="FIRSTLINE") || ($linedef==NULL)) 
	       {
	           $linedef = $this->new_data[0];
		   $skipFirstLine = TRUE; 
	       }

	       $temp = @explode($delim,$linedef);
	
	       foreach($temp AS $field_index=>$field_value)
	       {            
		   $this->mapping[] = $this->validKEY($field_value); 
	       }

	       
	       // fill the 2D array using the keys given
	       
	       foreach($this->new_data AS $index1=>$line)
               {
                   $temp = @explode($delim,$line);
                  
                   if(trim($line)=="")
                       // skip empty lines
                       continue;
                   elseif(count($temp)==0)
                       // line didn't split properly so record error
                       $this->errors[] = "Couldn't split data line ".$c." via given \$delim.";
                   elseif (!(($index1==0) && ($skipFirstLine)))
                   {
                       $data_set = array();
                       foreach($temp AS $field_index=>$field_value)
                       {
                           // Remove enclose characters
                           $this->stripENCLOSED($field_value,$enclosed_by);
                           $data_set[$this->mapping[$field_index]] = $field_value;
                   }
                   if(count($data_set)>0)
                           $this->results[] = $data_set;
                   }       
                   unset($data_set);
               }

               return $this->results;
              
       }
      
       function error($msg)
       {
           exit(
           "<hr size=1 noshade>".
           "<font color=red face=arial size=3>".
           "<h2>CSV Class Exception</h2>".
           $msg.
           "<p><b>Script Halted</b>".
           "</font>".
           "<hr size=1 noshade>"
           );
       }
      
       function help()
       {
           print(
           "<hr size=1 noshade>".
           "<font face=arial size=3>".
           "<h2>CSV Class Usage</h2>".
           "\$myVar = new CSV(\"path_to_my_file\",\"field delimeter\",\"fields enclosed by\",\"EOL character (defaults to \\n)\");<p>".
           "Output is a 2d result array (\$myVar->results)".
           "</font>".
           "<hr size=1 noshade>"
           );
       }
  }


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
 * @return TRUE if creation succeeded, FALSE otherwise
 * 
 */

function add_user($name,$surname,$email,$phone,$admincode,$username,$password,$teacher)
{
    // see if password must be stored crypted or not
    global $tbl_user;
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
    return true;
}

/**
 * Create users in Claroline from a list in a CSV file with the specified format
 *
 * @author Guillaume Lederer <lederer@cerdecam.be>
 *
 * @param  $userlist a 2D array with the list of new users :
 *         $userlist[$i]['name']       for the name
 *         $userlist[$i]['surname']    for the surname
 *         $userlist[$i]['username']   for the username
 *         $userlist[$i]['password']   for the password
 *         $userlist[$i]['officialcode']  for the official Code
 *         $userlist[$i]['phone']      for the phone
 *         $userlist[$i]['email']      for the email
 * 
 *
 * NOTE : COULD BE OPTIMIZED : NOT USING THE ADD_USER function so that only query creates all the users
 *
 */
 
function add_userlist($userlist)
{
    
    foreach ($userlist as $user)
    {
       add_user($user['name'],$user['surname'],$user['email'],$user['phone'],$user['officialCode'],$user['username'], $user['password'],FALSE); 
    }
}

/**
 * subscribe a specific user to a specific course
 *
 * @author Hugues Peeters <peeters@ipm.ucl.ac.be>
 *
 * @param  int     $userId     user ID from the course_user table
 * @param  string  $courseCode course code from the cours table
 *
 * @return boolean TRUE        if subscribtion suceed
 *         boolean FALSE       otherwise.
 */

function add_user_to_course($userId, $courseCode)
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

                if (mysql_num_rows($handle) > 0)
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
    $courseDb = $courseCode; // <- this  is  not very  true,  add here a  sql to find $courseDb of this $courseCode.

    if ( mysql_query("DELETE FROM `".$courseDb."`.`user_group`
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

    $sql = "SELECT *
                 FROM `".$mainDbName."`.`cours`
                 WHERE `code` = '".$code."'";

    $result = mysql_query($sql)
                or die('Error in file '.__FILE__.' at line '.__LINE__);
    $the_course = mysql_fetch_array($result);

    $sql = "SELECT *
                 FROM `".$mainDbName."`.`cours`
                 WHERE `code` = '".$code."'";

    $currentCourseId           = $the_course['code'];
    $currentCourseDbName       = $the_course['dbName'];
    $currentCourseDbNameGlu    = $courseTablePrefix.$the_course['dbName'].$dbGlu;
    $currentCoursePath         = $the_course['directory'];
    $currentCourseCode         = $the_course['officialCode'];
    $currentCourseName         = $the_course['name'];


    if( !$singleDbEnabled) // IF THE PLATFORM IS IN MULTI DATABASE MODE
        {

            $sql = "DROP DATABASE `".$currentCourseDbName."`";

            mysql_query($sql)
                or die('Error in file '.__FILE__.' at line '.__LINE__);
        }
        else // IF THE PLATFORM IS IN MONO DATABASE MODE
        {
            // SEARCH ALL TABLES RELATED TO THE CURRENT COURSE
            mysql_query("use ".$mainDbName);
      // underscores must be replaced because they are used as wildcards in LIKE sql statement
            $cleanCourseDbNameGlu = str_replace("_","\_", $currentCourseDbNameGlu);
            $sql = "SHOW TABLES LIKE \"".$cleanCourseDbNameGlu."%\"";

            $result=mysql_query($sql)
                or die('Error in file '.__FILE__.' at line '.__LINE__." : ".$sql);
            // DELETE ALL TABLES OF THE CURRENT COURSE
            while( $courseTable = mysql_fetch_array($result,MYSQL_NUM ) )
            {
                $sql = "DROP TABLE ".$courseTable[0]."";
                mysql_query($sql)
                    or die('Error in file '.__FILE__.' at line '.__LINE__.' :'.$sql);
            }
        }

        // DELETE THE COURSE INSIDE THE PLATFORM COURSE REGISTERY

        $sql = "DELETE FROM `".$tbl_course."`
                WHERE code= \"".$currentCourseId."\"";

        mysql_query($sql) or die('Error in file '.__FILE__.' at line '.__LINE__);

        // DELETE USER ENROLLMENT INTO THIS COURSE

        $sql = "DELETE FROM `".$tbl_courseUser."`
                WHERE code_cours=\"".$currentCourseId."\"";

        mysql_query($sql) or die('Error in file '.__FILE__.' at line '.__LINE__);

        // MOVE THE COURSE DIRECTORY INTO THE COURSE GARBAGE COLLECTOR

        mkPath($garbageRepositorySys);

        rename($coursesRepositorySys.$currentCoursePath."/",
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
    global $tbl_courseUser,$_uid;
    
    $sqlChangeStatus = "";
    
    if ($user_id != $_uid //do we allow user to change his own settings? what about course without teacher?
            and ($properties['status']=="1" or $properties['status']=="5")
            )

    $sqlChangeStatus = "`statut` = \"".$properties['status']."\",";
    
    $result = claro_sql_query("UPDATE `$tbl_courseUser`
                            SET     `role`       = \"".$properties['role']."\",
                                    ".$sqlChangeStatus."
                                    `tutor`      = \"".$properties['tutor']."\"
                            WHERE   `user_id`    = \"".$user_id."\"
                            AND     `code_cours` = \"".$course_id."\"") or die ("CANNOT UPDATE DB !");

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

/**
 * deprecated : use claro_disp_auth_form()
 * To know if user is registered to a course or not
 * @ver 1.5
 
 */
function treatNotAuthorized()
{
    return claro_disp_auth_form();
}

?>
