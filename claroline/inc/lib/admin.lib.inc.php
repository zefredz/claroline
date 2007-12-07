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

include_once(dirname(__FILE__).'/../lib/fileManage.lib.php');

/**
 * @author Hugues Peeters <peeters@ipm.ucl.ac.be>
 * INVERT A MATRIX function :
 * 
 * this function allows to invert cols and rows of a 2D array 
 * needed to treat the potentialy new users to add form a CSV file
 * @param origMartix array source array to be reverted
 * @param $presumedColKeyList array contain the minimum list of colum in the builded array
 */

function array_swap_cols_and_rows( $origMatrix, $presumedColKeyList)
{
    $revertedMatrix = array();

    foreach($origMatrix as $thisRow)
    {
        $actualColKeyList = array();

        foreach($thisRow as $thisColKey => $thisColValue)
        {
            $revertedMatrix[$thisColKey][] = $thisColValue;

            $actualColKeyList[] = $thisColKey;
        }

        // IN case of missing columns, fill them with NULL

        $missingColKeyList = array_diff($presumedColKeyList, $actualColKeyList);

        if (count($missingColKeyList) > 0)
        {
            foreach($missingColKeyList as $thisColKey)
            {
                $revertedMatrix[$thisColKey][] = NULL;
            }
           
        }
    }
    return $revertedMatrix;
}
/**
 * test if the given format is correct to be used in claroline to add user,
 * if all the complusory fields are present.
 * @param format to test
 * @return boolean TRUE if format is acceptable
 *                 FALSE if format is not acceptable (missing a needed field) 
 *
 */

function claro_CSV_format_ok($format, $delim =";", $enclosedBy="")
{
    $fieldarray = explode(";",$format);
    if ($enclosedBy == "dbquote") $enclosedBy = "\"";
    
    $username_found = FALSE;
    $password_found = FALSE;
    $surname_found  = FALSE;
    $name_found     = FALSE;
    
    foreach ($fieldarray as $field)
    {
    if (!empty($enclosedBy))         
    {
        $fieldTempArray = explode($enclosedBy,$field);         
        if (isset($fieldTempArray[1])) $field = $fieldTempArray[1];
    } 
    if (trim($field)=="surname")
	{
	    $surname_found = TRUE;
	}
	if (trim($field)=="name")
	{
	    $name_found = TRUE;
	}
	if (trim($field)=="username")
	{
	    $username_found = TRUE;
	}
	if (trim($field)=="password")
	{
	    $password_found = TRUE;
	}
    } 
    
    return ($username_found && $password_found && $surname_found && $name_found);
}
 
/**
 * Check ERRORS in a CSV file uploaded of potential new user to add in Claroline 
 *
 * format used for line of CSV file must be stored in SESSION to use this function properly: ...
 *
 * @author Guillaume Lederer <lederer@cerdecam.be>
 *
 * @param  $uploadTempDir : place where the folder is stored
 * @param  $useFirstLine  : boolean true if parser should user the first line of file to know where the format is
 *                                  false otherwise 
 * @param $format : the used format, if empty, this means that we use first line format mode
 * 
 * @return a 2D array with the users found in the file is stored in session, 7 boolean errors arrays are created for each type of possible errors, they are stored in session too:
 *      
 *      $_SESSION['claro_csv_userlist'] for the users to add
 *      
 *      $_SESSION['claro_mail_synthax_error']               for mail synthax error   
 *      $_SESSION['claro_mail_used_error']                  for mail used in campus error      
 *      $_SESSION['claro_username_used_error']              for username used in campus error   
 *      $_SESSION['claro_officialcode_used_error']          for official code used error
 *      $_SESSION['claro_password_error']                   for password error
 *      $_SESSION['claro_mail_duplicate_error']             for mail duplicate error
 *      $_SESSION['claro_username_duplicate_error']         for username duplicate error
 *      $_SESSION['claro_officialcode_duplicate_error']     for officialcode duplicate error
 * 
 */
 
function claro_check_campus_CSV_File($uploadTempDir, $useFirstLine, $format="", $fieldSep=";", $fieldEnclose="")
{
        //check if temporary directory for uploaded file exists, if not we create it
	
	if (!file_exists($uploadTempDir))
	{
	    mkdir($uploadTempDir,0777);
	}

	//check if the uploaded fie path exists, otherwise 
	
	//store the uploaded file in a temporary dir

	move_uploaded_file($_FILES["CSVfile"]["tmp_name"], $uploadTempDir.$_FILES["CSVfile"]["name"]);

	$openfile = fopen($uploadTempDir.$_FILES['CSVfile']['name'],"r") or die ("Impossible to open file ".$_FILES['CSVfile']['name']);

	//Read each ligne : we put one user in an array, and build an array of arrays for the list of user.

	   //see where the line format must be found and which seperator and enclosion must be used

	if ($useFirstLine)
	{
	    $usedFormat      = "FIRSTLINE";
	    $fieldSeparator  = ";";
	    $enclosedBy      = "";
	}
	else
	{
	    $usedFormat = $format; 
	    $fieldSeparator  = $fieldSep;	    
	    $enclosedBy      = $fieldEnclose;
	    if ($fieldEnclose=="dbquote") 
	    {
	        $enclosedBy = "\"";
	    }   
	}

	$CSVParser = new CSV($uploadTempDir.$_FILES["CSVfile"]["name"],$fieldSeparator,$usedFormat,$enclosedBy);
	
	if ($CSVParser->validFormat==false)
	{
	    $_SESSION['claro_invalid_format_error']               =  true;
	    return;
	}
	else
	{
	    $_SESSION['claro_invalid_format_error']               =  false;
	}
	
	$userlist = $CSVParser->results;

	//save this 2D array userlist in session

	$_SESSION['claro_csv_userlist'] = $userlist;

	// test for each user if it is addable, get possible errors messages in tables

	   //first, we inverse the 2D array containing the lines of CSV file just parsed 
	   //because it is much easier and faster to have line numbers of the CSV file as second indice in the array
	
	$cols[] = "surname";
	$cols[] = "name";
	$cols[] = "email";
	$cols[] = "phone";
	$cols[] = "username";
	$cols[] = "password";
	$cols[] = "officialCode";   
	
	//var_dump($_SESSION['claro_csv_userlist']);
	   
	$working2Darray = array_swap_cols_and_rows($_SESSION['claro_csv_userlist'],$cols);
	
	//look for possible new errors
	      
	$mail_synthax_error           = check_email_synthax_userlist($working2Darray);
	$mail_used_error              = check_mail_used_userlist($working2Darray);
	$username_used_error          = check_username_used_userlist($working2Darray);
	$officialcode_used_error      = check_officialcode_used_userlist($working2Darray);
	$password_error               = check_password_userlist($working2Darray);
	$mail_duplicate_error         = check_duplicate_mail_userlist($working2Darray);
	$username_duplicate_error     = check_duplicate_username_userlist($working2Darray);
	$officialcode_duplicate_error = check_duplicate_officialcode_userlist($working2Darray);
	
	//save error arrays in session (needed in second step)
	
	$_SESSION['claro_mail_synthax_error']               =  $mail_synthax_error;    
	$_SESSION['claro_mail_used_error']                  =  $mail_used_error;      
	$_SESSION['claro_username_used_error']              =  $username_used_error;   
	$_SESSION['claro_officialcode_used_error']          =  $officialcode_used_error;
	$_SESSION['claro_password_error']                   =  $password_error;
	$_SESSION['claro_mail_duplicate_error']             =  $mail_duplicate_error;
	$_SESSION['claro_username_duplicate_error']         =  $username_duplicate_error;
	$_SESSION['claro_officialcode_duplicate_error']     =  $officialcode_duplicate_error;
	
	//delete the temp file
	
	@unlink($uploadTempDir.$_FILES["CSVfile"]["name"]);	    
}

/**
 * display the errors caused by a conflict with the platform after parsing the CSV file used to add new users found in the platform.
 * ERRORS and USERS must be saved in the session at these places :
 *
 *      $_SESSION['claro_csv_userlist'] for the users to add
 *      
 *      $_SESSION['claro_mail_synthax_error']               for mail synthax error   
 *      $_SESSION['claro_mail_used_error']                  for mail used in campus error      
 *      $_SESSION['claro_username_used_error']              for username used in campus error   
 *      $_SESSION['claro_officialcode_used_error']          for official code used error
 *      $_SESSION['claro_password_error']                   for password error
 *      $_SESSION['claro_mail_duplicate_error']             for mail duplicate error
 *      $_SESSION['claro_username_duplicate_error']         for username duplicate error
 *      $_SESSION['claro_officialcode_duplicate_error']     for officialcode duplicate error
 * 
 * @author Guillaume Lederer <lederer@cerdecam.be>
 * 
 * 
 */
 
function claro_disp_CSV_error_backlog()
{
    global $langMailSynthaxError;
    global $langMailUsed;
    global $langUsernameUsed;
    global $langCodeUsed;
    global $langPasswordSimple;
    global $langMailAppearAlready;
    global $langUsernameAppearAlready;
    global $langCodeAppearAlready;
    
    if (isset($_SESSION['claro_invalid_format_error']) && $_SESSION['claro_invalid_format_error'] == true)
    {
       echo "Error in format<br>";
       return;
    }   
       
    for ($i=0, $size=sizeof($_SESSION['claro_csv_userlist']); $i<=$size; $i++)
    {
        $line=$i+1;

	    if (isset($_SESSION['claro_mail_synthax_error'][$i]) && $_SESSION['claro_mail_synthax_error'][$i]) 
	    {
	        echo "<b>line $line :</b> \"".$_SESSION['claro_csv_userlist'][$i]['email']."\" <b>:</b> $langMailSynthaxError  <br>";
	    }      
	    if (isset($_SESSION['claro_mail_used_error'][$i]) && $_SESSION['claro_mail_used_error'][$i])
	    {
	        echo "<b>line $line :</b> \"".$_SESSION['claro_csv_userlist'][$i]['email']."\" <b>:</b> $langMailUsed  <br>\n";         
	    }
	    if (isset($_SESSION['claro_username_used_error'][$i]) && $_SESSION['claro_username_used_error'][$i])
	    {
	        echo "<b>line $line :</b> \"".$_SESSION['claro_csv_userlist'][$i]['username']."\" <b>:</b> $langUsernameUsed  <br>\n";     
	    }
	    if (isset($_SESSION['claro_officialcode_used_error'][$i]) && $_SESSION['claro_officialcode_used_error'][$i])
	    {
	        echo "<b>line $line :</b> \"".$_SESSION['claro_csv_userlist'][$i]['officialCode']."\" <b>:</b> $langCodeUsed  <br>\n"; 
	    }
	    if (isset($_SESSION['claro_password_error'][$i]) && $_SESSION['claro_password_error'][$i])
	    {
	        echo "<b>line $line :</b> \"".$_SESSION['claro_csv_userlist'][$i]['password']."\" <b>:</b> $langPasswordSimple  <br>\n";
	    }
	    if (isset($_SESSION['claro_mail_duplicate_error'][$i]) && $_SESSION['claro_mail_duplicate_error'][$i])
	    {
	        echo "<b>line $line :</b> \"".$_SESSION['claro_csv_userlist'][$i]['email']."\" <b>:</b>$langMailAppearAlready  <br>\n";
	    }
	    if (isset($_SESSION['claro_username_duplicate_error'][$i]) && $_SESSION['claro_username_duplicate_error'][$i])
	    {
	        echo "<b>line $line :</b> \"".$_SESSION['claro_csv_userlist'][$i]['username']."\" <b>:</b> $langUsernameAppearAlready  <br>\n";
	    }
	    if (isset($_SESSION['claro_officialcode_duplicate_error'][$i]) && $_SESSION['claro_officialcode_duplicate_error'][$i])
	    {
	        echo "<b>line $line :</b> \"".$_SESSION['claro_csv_userlist'][$i]['officialCode']."\" <b>:</b> $langCodeAppearAlready  <br>\n";
	    }
    }
}


/**
 * Check EMAIL SYNTHAX : if new users in Claroline with the specified parameters contains synthax error
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
	$tbl_mdb_names = claro_sql_get_main_tbl();
	$tbl_user      = $tbl_mdb_names['user'];
    $errors = array();
    
    //CHECK : check if usernames are not already token by someone else
    
    $sql = 'SELECT * FROM `'.$tbl_user.'` WHERE 1=0 ';
    
    for ($i=0, $size=sizeof($userlist['username']); $i<$size; $i++)
    {
        if (!empty($userlist['username'][$i]) && ($userlist['username'][$i]!=""))
		{
		    $sql .= ' OR username="'.addslashes($userlist['username'][$i]).'"';
		}  
    }  
    
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
	$tbl_mdb_names = claro_sql_get_main_tbl();
	$tbl_user      = $tbl_mdb_names['user'];

    //create an array with default values of errors
    
    $errors = array();
    
    //CHECK : check if admincode (officialCode) is not already taken by someone else
    $sql = 'SELECT * FROM `'.$tbl_user.'` WHERE 1=0 ';
    
    for ($i=0, $size=sizeof($userlist['officialCode']); $i<$size; $i++) 
    {
        if (!empty($userlist['officialCode'][$i]) && ($userlist['officialCode'][$i]!=""))
		{
		    $sql .= ' OR officialCode="'.addslashes($userlist['officialCode'][$i]).'"';
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
    	if ($userlist['password'][$i]==$userlist['username'][$i]) // do not allow to put username equals to password
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
	$tbl_mdb_names = claro_sql_get_main_tbl();
	$tbl_user             = $tbl_mdb_names['user'             ];
    
    //create an array with default values of errors
    
    $errors = array();

    $sql = 'SELECT * FROM `'.$tbl_user.'` WHERE 1=0 ';
    
    for ($i=0, $size=sizeof($userlist['email']); $i<$size; $i++) 
    {
        if (!empty($userlist['email'][$i]) && ($userlist['email'][$i]!=""))
		{
		    $sql .= ' OR email="'.addslashes($userlist['email'][$i]).'"';
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
	$tbl_mdb_names = claro_sql_get_main_tbl();
	$tbl_user             = $tbl_mdb_names['user'             ];
    $errors = array();
    for ($i=0, $size=sizeof($userlist['name']); $i<$size; $i++)
    {       
        //check email duplicata in the array

		if ($userlist['email'][$i] != "")
		{
		    $found = array_search($userlist['email'][$i],$userlist['email']);
        }
		else
		{
		    $found = FALSE; // do not check if email is empty
		}
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
	$tbl_mdb_names = claro_sql_get_main_tbl();
	$tbl_user             = $tbl_mdb_names['user'             ];
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
	
    $errors = array();
        
    for ($i=0, $size=sizeof($userlist['officialCode']); $i<$size; $i++)
    {       
        //check officialCode duplicata in the array
    
	$found = array_search($userlist['officialCode'][$i],$userlist['officialCode']);
	
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
    var $validFormat; //boolean variable set to true if the format useed in the file is usable in Claroline user database
    
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

/**
 * 
 *
 * @param
 * @param    
 * @param  $linedef FIRSTLINE means we take the first line of the file as the definition of the fields
 * @param    
 *
 * @return $errors : an array of boolean where $errors[$i] is TRUE if there is an error with entry $i in the given 2D array.
 *
 * 
 */
	
	
    function CSV($filename,$delim=";",$linedef,$enclosed_by="\"",$eol="\n")
    {
    	//open the file
        $this->raw_data = implode("",file($filename));
        // make sure all CRLF's are consistent
        $this->CRLFclean();
		// use custom $eol (if exists)
        if($eol!="\n" AND trim($eol)=="")
		{
        	$this->error("Couldn't split data via empty \$eol, please specify a valid end of line character.");
		}
		else
		{
			$this->new_data = @explode($eol,$this->raw_data);
			if(count($this->new_data)==0)
            {
                $this->error("Couldn't split data via given \$eol.<li>\$eol='".$eol."'");
			}
		}
		// create data keys with the line definition given in params, 
	    // if linedef is not define, take first line of file to define it
		if ($linedef=="FIRSTLINE") 
	    {
	    	$linedef = $this->new_data[0];	
		    $skipFirstLine = TRUE;
		}
		else
		{
		    $skipFirstLine = FALSE;     
		}
        
        //Create array with the fields format in the file :
        
        
        $temp = @explode($delim,$linedef);
   
        if (!empty($enclosed_by))         
        {
            $temporary = array();
            
            foreach ($temp as $tempfield)
            {
                $fieldTempArray = explode($enclosed_by,$tempfield);         
                if (isset($fieldTempArray[1])) $temporary[] = $fieldTempArray[1];
            }
            $temp = $temporary;
        }
        
        //check if the used format is ok for Claroline
        
                
        $this->validFormat = claro_CSV_format_ok($linedef, $delim, $enclosed_by);
        
	    if (!($this->validFormat)) return array();
        
        
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
                $dbgoutput[]=$courseTable;
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
function isRegisteredTo($user_id, $course_id)
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
 * deprecated : use claro_disp_auth_form()
 * To know if user is registered to a course or not
 * @ver 1.5
 
 */
function treatNotAuthorized()
{
    return claro_disp_auth_form();
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
