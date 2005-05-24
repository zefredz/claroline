<?php // $Id$
//----------------------------------------------------------------------
// CLAROLINE 1.6
//----------------------------------------------------------------------
// Copyright (c) 2001-2005 Universite catholique de Louvain (UCL)
//----------------------------------------------------------------------
// This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
// as published by the FREE SOFTWARE FOUNDATION. The GPL is available 
// through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
//----------------------------------------------------------------------
// Authors: see 'credits' file
//----------------------------------------------------------------------

// Include 
require '../inc/claro_init_global.inc.php';

claro_unquote_gpc();

// include profile configuration file
include $includePath.'/conf/user_profile.conf.php';

// include auth library
include($includePath."/lib/auth.lib.inc.php");

// include mail library
include($includePath.'/lib/claro_mail.lib.inc.php');

// DB tables definition
$tbl_mdb_names = claro_sql_get_main_tbl();
$tbl_user  = $tbl_mdb_names['user'];

// Configuration Variables Default Values
if ( !isset($userMailCanBeEmpty) )   $userMailCanBeEmpty   = TRUE;
if ( !isset($userPasswordCrypted) )  $userPasswordCrypted	 = FALSE;

// Initialise variables
$regDataOk = FALSE; // default value...
$msg = "";

// Display
$interbredcrump[]= array ("url"=>"inscription.php", "name"=> $langRegistration);
include($includePath."/claro_init_header.inc.php");

claro_disp_tool_title($langRegistration);

if( isset($_REQUEST['submitRegistration']) )
{
	$regexp = "^[0-9a-z_\.-]+@(([0-9]{1,3}\.){3}[0-9]{1,3}|([0-9a-z][0-9a-z-]*[0-9a-z]\.)+[a-z]{2,4})$";

    // get form param

    $username      = strip_tags ( trim ($_POST['username']) );
    $email         = strip_tags ( trim ($_POST['email']) );
    $lastname      = strip_tags ( trim ($_POST['lastname']) );
    $firstname     = strip_tags ( trim ($_POST['firstname']) );
    $password      = trim ($_POST['password']);
    $password_conf = trim ($_POST['password_conf']);
    $phone         = trim ($_POST['phone']);
    $officialCode  = strip_tags ( trim ($_POST['officialCode']) );
    $status        = ($allowSelfRegProf && $_REQUEST['status'] == COURSEMANAGER) ? COURSEMANAGER : STUDENT;

	/*==========================
	   DATA SUBIMITED CHECKIN
	  ==========================*/

	// CHECK IF THERE IS NO EMPTY FIELD

	if (   empty($lastname)       || empty($firstname) 
        || empty($password_conf) || empty($password)
		|| empty($username)     || (empty($email) && !$userMailCanBeEmpty) )
	{
		$regDataOk = FALSE;
		unset($password_conf, $password);
		$msg .= '<p>'.$langEmptyFields.'</p>'."\n";
	}

	// CHECK IF THE TWO PASSWORD TOKEN ARE IDENTICAL

	elseif ( $password_conf != $password )
	{
		$regDataOk = FALSE;
		unset($password_conf, $password);

		$msg .= '<p>'.$langPassTwice.'</p>'."\n";
	}

    // CHECK PASSWORD AREN'T TOO EASY

    elseif (   $password_conf 
            && SECURE_PASSWORD_REQUIRED
            && ! is_password_secure_enough( $password_conf,
                                          array($username, $officialCode, 
                                                $lastname, $firstname, $email) ) )
    {
        $regDataOk = FALSE;
        $msg .= '<p>' . $langPassTooEasy . ' <code>'.substr( md5( date('Bis').$_SERVER['HTTP_REFERER'] ), 0, 8 ).'</code></p>'."\n";
    }

	// CHECK EMAIL ADDRESS VALIDITY

    elseif ( !empty($email) && ! eregi($regexp,$email) )
	{
		$regDataOk = FALSE;
		unset($password_conf, $password, $email);

		$msg .= '<p>' . $langEmailWrong . '.</p>'."\n";
	}

	// CHECK IF THE LOGIN NAME IS ALREADY OWNED BY ANOTHER USER

	else
	{
        $sql = 'SELECT COUNT(*) `loginCount`
                FROM `'.$tbl_user.'` 
                WHERE username="' . addslashes($username) . '"';

        list($result) = claro_sql_query_fetch_all($sql);

        if ( $result['loginCount'] > 0 )
        {
            $regDataOk = FALSE;

            unset($password_conf, $password, $username);

            $msg .= '<p>' . $langUserTaken . '</p>' . "\n";
        }
        else
        {
			$regDataOk = TRUE;
        }
    }
} // if ! isset($_REQUEST['submitRegistration']) 

if ( ! empty($msg) ) claro_disp_message_box($msg);

if ( ! $regDataOk)
{
	echo '<p>'
       . '<a href="inscription.php'
       . '?lastname='. urlencode($lastname)
       . '&amp;firstname='. urlencode($firstname)
       . '&amp;email='. urlencode($email)
       . '&amp;officialCode='. urlencode($officialCode)
       . '&amp;phone='. urlencode($phone)
       . '&amp;status='. urlencode($status)
       . '">'
	   . $langAgain
       . '</a>'
       . '</p>'."\n"
       ;
}

/*> > > > > > > > > > > > REGISTRATION ACCEPTED < < < < < < < < < < < <*/

if ( $regDataOk )
{
	/*-----------------------------------------------------
	  STORE THE NEW USER DATA INSIDE THE CLAROLINE DATABASE
	  -----------------------------------------------------*/

    $password = $userPasswordCrypted?md5($password):$password;

    $sql = "INSERT INTO `".$tbl_user."`
            SET `nom`          = '". addslashes($lastname) ."' ,
                `prenom`       = '". addslashes($firstname) ."',
                `username`     = '". addslashes($username) ."',
                `password`     = '". addslashes($password) ."',
                `email`        = '". addslashes($email) ."',
                `statut`       = '". (int) $status ."',
                `officialCode` = '". addslashes($officialCode) ."',
                `phoneNumber`  = '". addslashes($phone) ."'";

    $_uid = claro_sql_query_insert_id($sql);

    if ( $_uid )
    {
    	/*--------------------------------------
    	          SESSION REGISTERING
    	  --------------------------------------*/
    
    	$_user['firstName']     = $firstname;
    	$_user['lastName' ]     = $lastname;
    	$_user['mail'     ]     = $email;
        $_user['lastLogin']     = time() - (24 * 60 * 60); // DATE_SUB(CURDATE(), INTERVAL 1 DAY)
    	$is_allowedCreateCourse = ($status == 1) ? TRUE : FALSE ;

        $_SESSION['_uid'] = $_uid;
        $_SESSION['_user'] = $_user;
        $_SESSION['is_allowedCreateCourse'] = $is_allowedCreateCourse;
            
        //stats
        include("../inc/lib/events.lib.inc.php");
        event_login();
    
        // last user login date is now
        $user_last_login_datetime = 0; // used as a unix timestamp it will correspond to : 1 1 1970
        $_SESSION['user_last_login_datetime'] = $user_last_login_datetime;
    
    	/*--------------------------------------
    	             EMAIL NOTIFICATION
    	  --------------------------------------*/
    	// do not event try to send the mail if there is no specified email address
    	// mail address has already be checked via regex if set
    	if( !empty($email) )
    	{
	    	$emailSubject  = '['.$siteName.'] '.$langYourReg;

	    	// The body can be as long as you wish, and any combination of text and variables

	    	$emailBody    = $langDear.' '.$firstname.' '.$lastname.",\n"
							.$langYouAreReg.' '.$siteName.' '.$langSettings.' '.$username."\n"
	                        .$langPassword.' : '.$password."\n"
	                        .$langAddress.' '.$siteName.' '.$langIs.' : '.$rootWeb."\n"
	                        .$langProblem."\n"
	                        .$langFormula.",\n"
	                        .$administrator_name."\n"
	                        .$langManager.' '.$siteName."\n"
							.'T. '. $administrator_phone."\n"
	        				.$langEmail.' : '.$administrator_email."\n";

			claro_mail_user($_uid, $emailBody, $emailSubject);
		}
        
    } // if _uid
 
    printf($langMessageSubscribeDone_p_firstname_lastname, $firstname, $lastname);

	if ( $is_allowedCreateCourse )
	{
		echo '<p>'.$langNowGoCreateYourCourse.'</p>'."\n";
	}
	else
	{
		echo '<p>'.$langNowGoChooseYourCourses.'</p>'."\n";
	}

	echo '<form action="../../index.php?cidReset=1" >'
       . '<input type="submit" name="next" value="'.$langNext.'" validationmsg=" '.$langNext.' ">'."\n"
       . '</form>'."\n"
       ;

}	// else Registration accepted

include($includePath."/claro_init_footer.inc.php");
?>
