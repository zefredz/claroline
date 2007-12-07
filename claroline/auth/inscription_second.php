<?php // $Id$
//----------------------------------------------------------------------
// CLAROLINE 1.5.1
//----------------------------------------------------------------------
// Copyright (c) 2001-2004 Universite catholique de Louvain (UCL)
//----------------------------------------------------------------------
// This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
// as published by the FREE SOFTWARE FOUNDATION. The GPL is available 
// through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
//----------------------------------------------------------------------
// Authors: see 'credits' file
//----------------------------------------------------------------------

/*==========================
             INIT
  ==========================*/

$langFile = 'registration';
require '../inc/claro_init_global.inc.php';
include $includePath.'/conf/profile.conf.inc.php'; // find this file to modify values.

// NAMING STATUS VALUES FOR THE PROFILES SCRIPTS
define ("STUDENT",      5);
define ("COURSEMANAGER",1);

$interbredcrump[]= array ("url"=>"inscription.php", "name"=> $langRegistration);
include($includePath."/claro_init_header.inc.php");
include($includePath."/lib/auth.lib.inc.php");
$nameTools = "2";

$TABLEUSER      = $mainDbName."`.`user";


if (!isset($userMailCanBeEmpty))   $userMailCanBeEmpty   = true;
if (!isset($checkEmailByHashSent)) $checkEmailByHashSent = false;
if (!isset($userPasswordCrypted))  $userPasswordCrypted	 = false;

$regDataOk = false; // default value...

claro_disp_tool_title($langRegistration);

if($submitRegistration)
{
	$regexp = "^[0-9a-z_\.-]+@(([0-9]{1,3}\.){3}[0-9]{1,3}|([0-9a-z][0-9a-z-]*[0-9a-z]\.)+[a-z]{2,4})$";

	$uname        = claro_strip_tags ( trim ($_REQUEST['uname'       ]) );
	$email        = claro_strip_tags ( trim ($_REQUEST['email'       ]) );
	$nom          = claro_strip_tags ( trim ($_REQUEST['nom'         ]) );
	$prenom       = claro_strip_tags ( trim ($_REQUEST['prenom'      ]) );
	$password     = trim ($_REQUEST['password'    ]);
	$password1    = trim ($_REQUEST['password1'   ]);
    $officialCode = claro_strip_tags ( trim ($_REQUEST['officialCode']) );
    $statut       = ($allowSelfRegProf && $_REQUEST['statut'] == COURSEMANAGER) ? COURSEMANAGER : STUDENT;


	/*==========================
	   DATA SUBIMITED CHECKIN
	  ==========================*/

	// CHECK IF THERE IS NO EMPTY FIELD

	if (   empty($nom)       || empty($prenom) 
        || empty($password1) || empty($password)
		|| empty($uname)     || (empty($email) && !$userMailCanBeEmpty) )
	{
		$regDataOk = false;

		unset($password1, $password);

		echo	"<p>",$langEmptyFields,"</p>\n";
	}

	// CHECK IF THE TWO PASSWORD TOKEN ARE IDENTICAL

	elseif($password1 != $password)
	{
		$regDataOk = false;
		unset($password1, $password);

		echo	"<p>",$langPassTwice,"</p>\n";
	}


    // CHECK PASSWORD AREN'T TOO EASY

    elseif (   $password1 
            && SECURE_PASSWORD_REQUIRED
            && ! is_password_secure_enough( $password1,
                                          array($uname, $officialCode, 
                                                $nom, $prenom, $email) ) )
    {
        $regDataOk = false;
        echo "<p>".$langPassTooEasy." : <code>".substr( md5( date('Bis').$HTTP_REFFERER ), 0, 8 )."</code></p>\n";
    }
    

	// CHECK EMAIL ADDRESS VALIDITY

    elseif( !empty($email) && ! eregi( $regexp, $email ))
	{
		$regDataOk = false;
		unset($password1, $password, $email);

		echo	"<p>",$langEmailWrong,".</p>\n";
	}

	// CHECK IF THE LOGIN NAME IS ALREADY OWNED BY ANOTHER USER

	else
	{
        $sql = "SELECT COUNT(*) loginCount
                FROM `".$TABLEUSER."` 
                WHERE username=\"".$uname."\"";

        list($result) = claro_sql_query_fetch_all($sql);

        if ($result['loginCount'] > 0)
        {
            $regDataOk = false;

            unset($password1, $password, $uname);

            echo "<p>",$langUserFree,"</p>\n";
        }
        else
        {
			$regDataOk = true;
        }
    }
}

if ( ! $regDataOk)
{
	echo	"<p>",
			"<a href=\"inscription.php?nom=",$nom,"&prenom=",$prenom,"&uname=",$uname,"&email=",$email,"&officialCode=",$officialCode,"&phone=",$phone,"&statut=",$statut,"\">",
			$langAgain,
			"</a>",
			"</p>\n";
}


/*> > > > > > > > > > > > REGISTRATION ACCEPTED < < < < < < < < < < < <*/

if ($regDataOk)
{
	/*-----------------------------------------------------
	  STORE THE NEW USER DATA INSIDE THE CLAROLINE DATABASE
	  -----------------------------------------------------*/

    $sql = "INSERT INTO `".$TABLEUSER."`
            SET `nom`          = \"".$nom."\",
                `prenom`       = \"".$prenom."\",
                `username`     = \"".$uname."\",
                `password`     = \"".($userPasswordCrypted?md5($password):$password)."\",
                `email`        = \"".$email."\",
                `statut`       = \"".$statut."\",
                `officialCode` = \"".$officialCode."\",
                `phoneNumber`  = \"".$phone."\"";

    $_uid = claro_sql_query_insert_id($sql);

    /*
            @claro_sql_query("INSERT INTO `$mainDbName`.`user_hash`
                          (user_id, hash, state) 
                          VALUES ('$last_id', '$hash', 'WAITCHECK')");
    */

if ($_uid)
{
	/*--------------------------------------
	          SESSION REGISTERING
	  --------------------------------------*/

	$_user['firstName']     = $prenom;
	$_user['lastName' ]     = $nom;
	$_user['mail'     ]     = $email;
	$is_allowedCreateCourse = ($statut == 1) ? true : false ;
        
    session_register("_uid");
    session_register("_user");
    session_register("is_allowedCreateCourse");

        //stats
        @include("../inc/lib/events.lib.inc.php");
        event_login();
        // last user login date is now
        $user_last_login_datetime = 0; // used as a unix timestamp it will correspond to : 1 1 1970
        session_register('user_last_login_datetime');

	/*--------------------------------------
	             EMAIL NOTIFICATION
	  --------------------------------------*/
	

	// Lets predefine some variables. Be sure to change the from address!

	$emailto       = "\"$prenom $nom\" <$email>";
	$emailfromaddr =  $administrator["email"];
	$emailfromname = "$siteName";
	$emailsubject  = "[".$siteName."] $langYourReg";

	// The body can be as long as you wish, and any combination of text and variables

	$emailbody    = "$langDear $prenom $nom,\n
$langYouAreReg $siteName $langSettings $uname\n$langPass : $password\n$langAddress $siteName $langIs : $rootWeb\n$langProblem\n$langFormula,\n" .
$administrator['name'] . "\n $langManager $siteName\nT. " . $administrator['phone'] . "\n$langEmail : " . $administrator['email'] . "\n";

		/*
			if ($checkEmailByHAshSent)
			{
				$hash = md5($email).md5($REMOTE_ADDR);
				$emailbody .= $rootWeb."claroline/auth/checkEmail.php?hash=".$hash."&emailHash=".$email;
			}
			else
			{
				$hash = "ok";
			}
		*/

	// Here we are forming one large header line
	// Every header must be followed by a \n except the last
	$emailheaders = "From: " . $administrator['name'] . " <".$administrator['email'].">\n";
	$emailheaders .= "Reply-To: " . $administrator['email'] . ""; 

	// Because I predefined all of my variables, this mail() function looks nice and clean hmm?
	@mail( $emailto, $emailsubject, $emailbody, $emailheaders);
}

	echo "<p>$langDear $prenom $nom, $langPersonalSettings</p>\n";

	if($is_allowedCreateCourse)
	{
		echo "<p>",$langNowGoCreateYourCourse,"</p>\n";
		$actionUrl = "../create_course/add_course.php";
	}
	else
	{
		echo "<p>",$langNowGoChooseYourCourses,"</p>\n";
		$actionUrl = "../../index.php?cidReset=1";
	}

	echo	"<form action=\"",$actionUrl,"\"\n>",
			"<input type=\"submit\" name=\"next\" value=\"",$langNext,"\" validationmsg=\" ",$langNext," \">\n",
			"</form>\n";

}	// else Registration accepted

$already_second=1;

include($includePath."/claro_init_footer.inc.php");

 ?>
