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

/*==========================
            INIT
  ==========================*/
// Status definition

define ("STUDENT"      , 5);
define ("COURSEMANAGER", 1);

$tlabelReq = "CLUSR___";

require '../inc/claro_init_global.inc.php';
if (! ($is_courseAdmin || $is_platformAdmin)) claro_disp_auth_form();

@include($includePath."/lib/debug.lib.inc.php");
include($includePath."/conf/user_profile.conf.php");
include($includePath.'/lib/claro_mail.lib.inc.php');


$nameTools        = $langAddAU;
$interbredcrump[] = array ("url"=>"user.php", "name"=> $langUsers);
include("../inc/claro_init_header.inc.php");

claro_disp_tool_title(array('mainTitle' =>$nameTools, 'subTitle' => $langUsers),
				'help_user.php');

$currentCourseID   = $_course['sysCode'];
$currentCourseName = $_course['officialCode'];

$tbl_cdb_names = claro_sql_get_course_tbl();
$tbl_mdb_names = claro_sql_get_main_tbl();
$tbl_user            = $tbl_mdb_names['user'             ];
$tbl_rel_course_user = $tbl_mdb_names['rel_course_user'  ];

// variables

$platformRegSucceed = false;

/*==========================
         DATA CHECKING
  ==========================*/

if($register)
{
	/*
	 * Fields Checking
	 */

    $emailRegex = "^[0-9a-z_\.-]+@(([0-9]{1,3}\.){3}[0-9]{1,3}|([0-9a-z][0-9a-z-]*[0-9a-z]\.)+[a-z]{2,4})$";

	$username_form  = trim($_REQUEST['username_form']);
	$email_form     = trim($_REQUEST['email_form']);
	$nom_form       = trim($_REQUEST['nom_form']);
	$prenom_form    = trim($_REQUEST['prenom_form']);
	$password_form  = trim($_REQUEST['password_form']);
    $confirm_form   = trim($_REQUEST['confirm_form']);
    $platformStatus = trim($_REQUEST['platformStatus']);

	$dataChecked = true; // initially set to true, will change to false if there is a problem

	// empty field checking
	if (
        empty($nom_form) 
        || empty($prenom_form) 
        || empty($password_form)
        || empty($confirm_form)
        || empty($username_form)
        || (empty($email_form) && !$userMailCanBeEmpty)
        )
	{
		$dataChecked = false;
		$message     = $langFilled;
	}

	// valid mail address checking

	elseif( !empty($email_form) && !eregi( $emailRegex, $email_form))
	{
		$dataChecked = false;
		$message     = $langEmailWrong;
	}
    
    // CHECK BOTH PASSWORD TOKEN ARE THE SAME

    if ($password_form !== $confirm_form)
    {
        $dataChecked = false;
        $message     = $langPassTwo;
        $password_form = '';
        $confirm_form = '';
    }
    else
    {
        $form_password = $form_password2 ;
    }

	// prevent conflict with existing user account

	if($dataChecked)
	{
		$result=claro_sql_query("SELECT user_id,
		                       (username='".$username_form."') AS loginExists,
		                       (nom='".$nom_form."' AND prenom='".$prenom_form."' AND email='".$email_form."') AS userExists
		                     FROM `".$tbl_user."`
		                     WHERE username='".$username_form."' OR (nom='".$nom_form."' AND prenom='".$prenom_form."' AND email='".$email_form."')
		                     ORDER BY userExists DESC, loginExists DESC");

		if(mysql_num_rows($result))
		{
			while($user=mysql_fetch_array($result))
			{
				// check if the user is already registered to the platform

				if($user['userExists'])
				{
					$userExists = true;
					$userId     = $user['user_id'];
					break;
				}

				// check if the login name choosen is already taken by another user

				if($user['loginExists'])
				{
					$loginExists = true;
					$userId      = 0;

					$message     = $langUserNo." (".$username_form.") ".$langTaken;

					break;
				}
			}				// end while $result
		}					// end if num rows
	}						// end if datachecked





/*=============================
  NEW USER REGISTRATION PROCESS
  =============================*/

	if($dataChecked && !$userExists && !$loginExists)
	{
			/*---------------------------
			      PLATFORM REGISTRATION
			  ----------------------------*/

		if ($_cid) $platformStatus = STUDENT;          // course registrartion context...
		else       $platformStatus = $platformStatus; // admin section of the platform context...

		if ($userPasswordCrypted) $pw = md5($password_form);
		else                      $pw = $password_form;

		$result = claro_sql_query("INSERT INTO `".$tbl_user."`
		                       SET nom         = \"$nom_form\",
		                           prenom      = \"$prenom_form\",
		                           username    = \"$username_form\",
		                           password    = \"$pw\",
		                           email       = \"$email_form\",
                                   phoneNumber = \"$phone_form\",
		                           statut      = \"$platformStatus\",
		                           creatorId   = \"$_uid\"");

		$userId = mysql_insert_id();

        if (CONFVAL_ASK_FOR_OFFICIAL_CODE)
        {
            $sql = "UPDATE  `".$tbl_user."`
                    SET officialCode = \"".$official_code."\"
                    WHERE user_id  = \"".$userId."\"";
            claro_sql_query($sql);
        }
        if ($userId) $platformRegSucceed = true;
	}

	if($userId && $_cid)
	{
		/*
		  Note : As we temporarly use this script in the platform administration 
		  section to also add user to the platform, We have to prevent course 
		  registration. That's why we check if $_cid is initialized, it gives us 
		  an hint about the use context of the script
		*/

			/*---------------------------
			      COURSE REGISTRATION
			  ----------------------------*/

		/*
		 * check the return value of the query
		 * if 0, the user is already registered to the course
		 */
		 
		if (claro_sql_query("INSERT IGNORE INTO `".$tbl_rel_course_user."`
						SET user_id     = '".$userId."',
							code_cours  = '".$currentCourseID."',
							statut      = '".$admin_form."',
							tutor       = '".$tutor_form."'"))
		{
			$courseRegSucceed = true;
		}
	} // if $platformRegSucceed && $_cid


	/*---------------------------
	   MAIL NOTIFICATION TO NEW USER
	  ----------------------------*/

	if ($platformRegSucceed)
	{
	
		$emailto       = "$nom_form $prenom_form <$email_form>";
		$emailSubject  = "$langYourReg $siteName";
		$serverAddress = $rootWeb."index.php";

		if ($courseRegSucceed)
		{
		    $emailBody = "$langDear $prenom_form $nom_form,\n
            $langOneResp $currentCourseName $langRegYou $siteName $langSettings $username_form\n
            $langPassword: $password_form\n
            $langAddress $siteName $langIs: $serverAddress\n
            $langProblem\n\n
            $langFormula,\n
            $langAdministrator ".$administrator_name."\n
            $langManager $siteName\n";
            if(! empty($administrator_phone) ) $emailBody .= "T. ".$administrator_phone."\n";
            $emailBody .= $langEmail.": ".$administrator_email."\n";
			$message = "$langTheU $prenom_form $nom_form $langAddedToCourse. ";
        }
		else
		{
            $emailBody = "$langDear  $prenom_form $nom_form,\n
            $langYouAreReg $siteName $langSettings $username_form\n
            $langPassword: $password_form\n
            $langAddress $siteName $langIs: $serverAddress\n
            $langProblem\n\n
            $langFormula,\n
            $administratorSurname ".$administrator_name."\n\n
            $langManager $siteName\n";
            if(! empty($administrator_phone) ) $emailBody .= "T. ".$administrator_phone."\n";
            $emailBody .= $langEmail.": ".$administrator_email."\n";
			$message = "$prenom_form $nom_form Added to platform.";
		}

        if (! empty($email_form)) claro_mail_user($userId, $emailBody, $emailSubject);
		
		/*
		 * remove <form> variables to prevent any pre-filled fields
		 */

		unset($nom_form, $prenom_form, $username_form, $password_form, $email_form, $admin_form, $tutor_form, $phone_form, $official_code);

	} 	// end if ($platformRegSucceed)
	//else
	//{
	//	$message = $langUserAlreadyRegistered;
	//}

} // end if register request

/*==========================
         MESSAGE BOX
  ==========================*/

if($message)
{
    claro_disp_message_box($message);
    if ($platformRegSucceed) echo "<p><a href=\"user.php\"><< $langBackUser</a></p>\n";
}

if ($platformRegSucceed == false)
{

/*==========================
     ADD ONE USER FORM
  ==========================*/
?>

<?php echo $langOneByOne; ?>. <?php echo "<p>" . $langUserOneByOneExplanation . "</p>"; ?>

<form method="post" action="<?php echo  $_SERVER['PHP_SELF'] ?>?register=yes">
<table cellpadding="3" cellspacing="0" border="0">

<tr>
<td align="right"><label for="nom_form"><?php echo $langLastName; ?></label> :</td>
<td><input type="text" size="40" name="nom_form" id="nom_form" value="<?php echo htmlentities(stripslashes($nom_form)); ?>"></td>
</tr>

<tr>
<td align="right"><label for="prenom_form"><?php echo $langFirstName; ?></label> :</td>
<td><input type="text" size="40" name="prenom_form" id="prenom_form" value="<?php echo htmlentities(stripslashes($prenom_form)); ?>"></td>
</tr>

<?
if (CONFVAL_ASK_FOR_OFFICIAL_CODE)
{
?>
<tr>
    <td align="right"><label for="official_code"><?php echo $langOfficialCode; ?></label> :
    </td>
    <td>
    <input type="text" size="40" id="official_code" name="official_code" value="<?php echo htmlentities(stripslashes($official_code)); ?>">
    </td>
</tr>
<?
}
?>
<tr>
<td><br></td>
</tr>
<tr>
<td></td>
</tr>

<tr>
<td align="right">
	<label for="username_form"><?php echo  $langUserName ?></label> 
	:
</td>
<td>
	<input type="text" id="username_form" size="40" name="username_form" value="<?php echo htmlentities(stripslashes($username_form)); ?>"></td>
</tr>

<tr>
<td align="right">
	<label for="password_form"><?php echo  $langPassword ?></label> 
	:
</td>
<td>
	<input type="password" size="40" name="password_form" value="<?php echo  htmlentities(stripslashes($password_form)) ?>"></td>
</tr>

<tr>
    <td align="right">
		<label for="confirm_form"><?php echo $langConfirm ?></label> 
		:
    </td>
    <td>
    <input type="password" size="40" name="confirm_form" value="" id="confirm_form">
    </td>
</tr>

<tr>
<td><br></td>
</tr>
<tr>
<td></td>
</tr>

<tr>
<td align="right"><label for="email_form"><?php echo  $langEmail; ?></label> :</td>
<td><input type="text" size="40" name="email_form" id="email_form" value="<?php echo $email_form; ?>"></td>
</tr>

<tr>
<td align="right"><label for="phone_form"><?php echo  $langPhone; ?></label> :</td>
<td><input type="text" size="40" name="phone_form" id="phone_form" value="<?php echo $phone_form; ?>"></td>
</tr>

<tr>
<?

if ($_cid) // if we're inside a course, then it's a course registration
{

?>
<td align="right"><?php echo  $langTutor; ?> :</td>
<td>
 <input type="radio" name="tutor_form" value="0" <?php 
 	if(!isset($tutor_form) || !$tutor_form) echo 'checked="checked"'; 
  	?> id="tutor_form_value_0"> <label for="tutor_form_value_0"><?php echo $langNo; ?></label>
 <input type="radio" name="tutor_form" value="1" <?php 
 	if($tutor_form == 1) echo 'checked="checked"';                    
	?> id="tutor_form_value_1"> <label for="tutor_form_value_1"><?php echo  $langYes ?></label>
</td>
</tr>
<tr>
<td align="right"><?php echo  $langManager ?> :</td>
<td>
  <input type="radio" name="admin_form" value="5" <?php if(!isset($admin_form) || $admin_form == 5) echo 'checked="checked"'; ?> id="no" > <label for="no"><?php echo $langNo ?></label>
  <input type="radio" name="admin_form" value="1" <?php if($admin_form == 1) echo 'checked="checked"';                        ?> id="yes"> <label for="yes"><?php echo  $langYes; ?></label></td>
</tr>
<?

}			// end if $_cid - for the case we're not in a course registration
			// but a platform registration
else
{ 

?>
<tr>
<td align="right"><label for="platformStatus"><?php echo $langStatus ?></label> : </td>
<td>
<select name="platformStatus" id="platformStatus">
<option value="<?php echo STUDENT       ?>"><?php echo  $langRegStudent ?></option>
<option value="<?php echo COURSEMANAGER ?>"><?php echo  $langRegAdmin   ?></option>
</select>
</td>
</tr>

<?
} // end else if $_cid
?>
<tr>
<td>&nbsp;</td>
<td><input type="submit" name="submit" value="<?php echo  $langOk ?>"></td>
</tr>
</table>
</form>

<?php

}

include("../inc/claro_init_footer.inc.php");
?>
