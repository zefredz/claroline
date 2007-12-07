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

// include profile configuration file
include $includePath.'/conf/user_profile.conf.php';

// include auth library
include($includePath."/lib/auth.lib.inc.php");

// DB tables definition
$tbl_mdb_names = claro_sql_get_main_tbl();
$tbl_user  = $tbl_mdb_names['user'];

if (!isset($userMailCanBeEmpty))   $userMailCanBeEmpty   = TRUE;
if (!isset($userPasswordCrypted))  $userPasswordCrypted	 = FALSE;

// NAMING STATUS VALUES FOR THE PROFILES SCRIPTS

define ("STUDENT",      5);
define ("COURSEMANAGER",1);

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

    $username      = strip_tags ( trim ($_REQUEST['username']) );
    $email         = strip_tags ( trim ($_REQUEST['email']) );
    $lastname      = strip_tags ( trim ($_REQUEST['lastname']) );
    $firstname     = strip_tags ( trim ($_REQUEST['firstname']) );
    $password      = trim ($_REQUEST['password']);
    $password_conf = trim ($_REQUEST['password_conf']);
    $phone         = trim ($_REQUEST['phone']);
    $officialCode  = strip_tags ( trim ($_REQUEST['officialCode']) );
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

	elseif($password_conf != $password)
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
        $msg .= '<p>'.$langPassTooEasy.' <code>'.substr( md5( date('Bis').$_SERVER['HTTP_REFERER'] ), 0, 8 ).'</code></p>'."\n";
    }

	// CHECK EMAIL ADDRESS VALIDITY

    elseif( !empty($email) && ! eregi( $regexp, $email ))
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
                WHERE username="'.$username.'"';

        list($result) = claro_sql_query_fetch_all($sql);

        if ($result['loginCount'] > 0)
        {
            $regDataOk = FALSE;

            unset($password_conf, $password, $username);

            $msg .= '<p>'.$langUserTaken.'</p>'."\n";
        }
        else
        {
			$regDataOk = TRUE;
        }
    }
} // if ! isset($_REQUEST['submitRegistration']) 

if ( ! empty($msg)) claro_disp_message_box($msg);

if ( ! $regDataOk)
{
	echo '<p>'
       . '<a href="inscription.php'
       . '?lastname='.$lastname
       . '&amp;firstname='.$firstname
       . '&amp;username='.$username
       . '&amp;email='.$email
       . '&amp;officialCode='.$officialCode
       . '&amp;phone='.$phone
       . '&amp;status='.$status
       . '">'
	   . $langAgain
       . '</a>'
       . '</p>'."\n"
       ;
}

/*> > > > > > > > > > > > REGISTRATION ACCEPTED < < < < < < < < < < < <*/

if ($regDataOk)
{
	/*-----------------------------------------------------
	  STORE THE NEW USER DATA INSIDE THE CLAROLINE DATABASE
	  -----------------------------------------------------*/

    $sql = "INSERT INTO `".$tbl_user."`
            SET `nom`          = \"".$lastname."\",
                `prenom`       = \"".$firstname."\",
                `username`     = \"".$username."\",
                `password`     = \"".($userPasswordCrypted?md5($password):$password)."\",
                `email`        = \"".$email."\",
                `statut`       = \"".$status."\",
                `officialCode` = \"".$officialCode."\",
                `phoneNumber`  = \"".$phone."\"";

    $_uid = claro_sql_query_insert_id($sql);

    if ($_uid)
    {
    	/*--------------------------------------
    	          SESSION REGISTERING
    	  --------------------------------------*/
    
    	$_user['firstName']     = $firstname;
    	$_user['lastName' ]     = $lastname;
    	$_user['mail'     ]     = $email;
    	$is_allowedCreateCourse = ($status == 1) ? TRUE : FALSE ;
            
        session_register("_uid");
        session_register("_user");
        session_register("is_allowedCreateCourse");
    
        //stats
        include("../inc/lib/events.lib.inc.php");
        event_login();
    
        // last user login date is now
        $user_last_login_datetime = 0; // used as a unix timestamp it will correspond to : 1 1 1970
        session_register('user_last_login_datetime');
   
        if ( !empty($email) )
        { 
            /*--------------------------------------
                         EMAIL NOTIFICATION
              --------------------------------------*/
            
            // Lets predefine some variables. Be sure to change the from address!
        
            $emailto       = '"'.$firstname.' '.$lastname.'" <'.$email.'>';
            $emailfromaddr =  $administrator_email;
            $emailfromname = $siteName;
            $emailsubject  = '['.$siteName.'] '.$langYourReg;
        
            // The body can be as long as you wish, and any combination of text and variables
        
            $emailbody    = "$langDear $firstname $lastname,\n" .
                            "$langYouAreReg $siteName $langSettings $username\n" .
                            "$langPassword : $password\n" .
                            "$langAddress $siteName $langIs : $rootWeb\n".
                            "$langProblem\n" .
                            "$langFormula,\n" .
                            $administrator_name . "\n" .
                            "$langManager $siteName\n" .
                            "T. " . $administrator_phone . "\n" .
                            "$langEmail : " . $administrator_email . "\n";
        
            // Here we are forming one large header line
            // Every header must be followed by a \n except the last
            $emailheaders = "From: " . $administrator_name . " <".$administrator_email.">\n";
            $emailheaders .= "Reply-To: " . $administrator_email . ""; 
        
            // Because I predefined all of my variables, this mail() function looks nice and clean hmm?
            @mail( $emailto, $emailsubject, $emailbody, $emailheaders);
        }

    } // if _uid
 
    printf($langMessageSubscribeDone_p_firstname_lastname, $firstname,$lastname);

	if($is_allowedCreateCourse)
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
