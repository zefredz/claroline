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

$tlabelReq = "CLUSR___";

require '../inc/claro_init_global.inc.php';
if (! ($is_courseAdmin || $is_platformAdmin)) claro_disp_auth_form();

@include($includePath."/lib/debug.lib.inc.php");
include($includePath."/conf/user_profile.conf.php");
include($includePath.'/lib/claro_mail.lib.inc.php');


$nameTools        = $langAddAU;
$interbredcrump[] = array ('url'=>'user.php', 'name'=> $langUsers);
include('../inc/claro_init_header.inc.php');

claro_disp_tool_title(array('mainTitle' =>$nameTools, 'supraTitle' => $langUsers),
				'help_user.php');

$currentCourseID   = $_course['sysCode'];
$currentCourseName = $_course['officialCode'];

$tbl_cdb_names = claro_sql_get_course_tbl();
$tbl_mdb_names = claro_sql_get_main_tbl();
$tbl_user            = $tbl_mdb_names['user'             ];
$tbl_rel_course_user = $tbl_mdb_names['rel_course_user'  ];

// variables

$platformRegSucceed = false;

//get variables from previous attempt to create user to prefill form fields

if (isset($_REQUEST['username_form']))      $username_form       = trim($_REQUEST['username_form']);      else $username_form = "";
if (isset($_REQUEST['nom_form']))           $nom_form            = trim($_REQUEST['nom_form']);           else $nom_form = "";
if (isset($_REQUEST['prenom_form']))        $prenom_form         = trim($_REQUEST['prenom_form']);        else $prenom_form = "";
if (isset($_REQUEST['email_form']))         $email_form          = trim($_REQUEST['email_form']);         else $email_form = "";
if (isset($_REQUEST['official_code_form'])) $official_code_form  = trim($_REQUEST['official_code_form']); else $official_code_form = "";
if (isset($_REQUEST['phone_form ']))        $phone_form          = trim($_REQUEST['phone_form']);         else $phone_form = "";
if (isset($_REQUEST['admin_form']))         $admin_form          = trim($_REQUEST['admin_form']);         else $admin_form = STUDENT;
if (isset($_REQUEST['tutor_form']))         $tutor_form          = trim($_REQUEST['tutor_form']);         else $tutor_form = "";
if (isset($_REQUEST['password_form']))      $password_form       = trim($_REQUEST['password_form']);      else $password_form = "";
if (isset($_REQUEST['confirm_form']))       $confirm_form        = trim($_REQUEST['confirm_form']);       else $confirm_form = "";

/*==========================
         DATA CHECKING
  ==========================*/

if(isset($_REQUEST['register']) && $_REQUEST['register'])
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
		$message = $langFields;
	}

	// valid mail address checking

	elseif( !empty($email_form) && !eregi( $emailRegex, $email_form))
	{
		$dataChecked = false;
		$message     = $langEmailWrong;
	}
    
    // CHECK BOTH PASSWORD TOKEN ARE THE SAME

    if ($password_form != $confirm_form)
    {
        $dataChecked = false;
        $message     = $langPassTwo;
        $password_form = '';
        $confirm_form = '';
    }

	// prevent conflict with existing user account

	if($dataChecked)
	{
		$sql = "SELECT user_id,
		                       (username='".$username_form."') AS loginExists,
		                       (nom='".$nom_form."' AND prenom='".$prenom_form."' AND email='".$email_form."') AS userExists
		                     FROM `".$tbl_user."`
		                     WHERE username='".$username_form."' OR (nom='".$nom_form."' AND prenom='".$prenom_form."' AND email='".$email_form."')
		                     ORDER BY userExists DESC, loginExists DESC";
		
		$result=claro_sql_query($sql);
                		
		if(mysql_num_rows($result))
		{
			while($user=mysql_fetch_array($result))
			{
				// check if the user is already registered to the platform

				if($user['userExists'])
				{
				    $userExists  = true;
				    $userId      = $user['user_id'];
				    $message     = $langUserNameTaken;
				    break;
				}
				else
				{
				    $userExists = false;
				}

				// check if the login name choosen is already taken by another user

				if($user['loginExists'])
				{
				    $loginExists = true;
				    $userId      = 0;

				    $message     = $langUserNo." (".$username_form.") ".$langTaken;

				    break;
				}
				else
				{
				    $loginExists = false;
				}
			}				// end while $result
		}					// end if num rows
	}						// end if datachecked

/*=============================
  NEW USER REGISTRATION PROCESS
  =============================*/

	if($dataChecked && (!(isset($userExists) && $userExists)) && (!(isset($loginExists) && $loginExists)))
	{
	/*---------------------------
	    PLATFORM REGISTRATION
	----------------------------*/

            $platformStatus = STUDENT;
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
                           SET officialCode = \"".$official_code_form."\"
                         WHERE user_id  = \"".$userId."\"";
                claro_sql_query($sql);
            }
            if (isset($userId)) $platformRegSucceed = true;


	/*---------------------------
	      COURSE REGISTRATION
	  ----------------------------*/
	  
	    if (claro_sql_query("INSERT IGNORE INTO `".$tbl_rel_course_user."`
					SET user_id     = '".$userId."',
					code_cours  = '".$currentCourseID."',
					statut      = '".$admin_form."',
					tutor       = '".$tutor_form."'"))
	    {
		    $courseRegSucceed = true;
	    }
	    
	    $display_success = true;
	    
	} // if $platformRegSucceed && $_cid
        else
	{
	    
	}
	
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

		unset($nom_form, $prenom_form, $username_form, $password_form, $email_form, $admin_form, $tutor_form, $phone_form, $official_code_form);

	} 	// end if ($platformRegSucceed)

} // end if register request

/*==========================
         MESSAGE BOX
  ==========================*/

if(isset($message))
{
    claro_disp_message_box($message);
    if ($platformRegSucceed) echo "<p><a href=\"user.php\"><< $langBackToUsersList</a></p>\n";
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

<?php
if (CONFVAL_ASK_FOR_OFFICIAL_CODE)
{
?>
<tr>
    <td align="right"><label for="official_code_form"><?php echo $langOfficialCode; ?></label> :
    </td>
    <td>
    <input type="text" size="40" id="official_code_form" name="official_code_form" value="<?php echo htmlentities(stripslashes($official_code_form)); ?>">
    </td>
</tr>
<?php
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

<td align="right"><?php echo  $langGroupTutor; ?> :</td>
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
  <input type="radio" name="admin_form" value="<?php echo STUDENT?>"       <?php if($admin_form == STUDENT) echo 'checked="checked"'; ?> id="no" > <label for="no"><?php echo $langNo ?></label>
  <input type="radio" name="admin_form" value="<?php echo COURSEMANAGER?>" <?php if($admin_form == COURSEMANAGER) echo 'checked="checked"';                        ?> id="yes"> <label for="yes"><?php echo  $langYes; ?></label></td>
</tr>
<tr>
<td>&nbsp;</td>
<td>
<input type="submit" name="submit" value="<?php echo  $langOk ?>">
<?php claro_disp_button("user.php", $langCancel); ?>
</td>
</tr>
</table>
</form>

<?php

}

include("../inc/claro_init_footer.inc.php");
?>
