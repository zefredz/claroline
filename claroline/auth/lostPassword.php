<?php # $Id$

//----------------------------------------------------------------------
// CLAROLINE 1.6
//----------------------------------------------------------------------
// Copyright (c) 2001-2004 Universite catholique de Louvain (UCL)
//----------------------------------------------------------------------
// This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
// as published by the FREE SOFTWARE FOUNDATION. The GPL is available 
// through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
//----------------------------------------------------------------------
// Authors: see 'credits' file
//----------------------------------------------------------------------

/*
 * SCRIPT PURPOSE :
 *
 * This script allows users to retrieve the password of their profile(s) 
 * on the basis of their e-mail address. The password is send via email 
 * to the user.
 *
 * Special case : If the password are encrypted in the database, we have 
 * to generate a new one.
 */

require '../inc/claro_init_global.inc.php';

$nameTools = $langLostPassword;

// DB tables definition
$tbl_mdb_names = claro_sql_get_main_tbl();
$tbl_user      = $tbl_mdb_names['user'];

// library for authentification and mail
include($includePath.'/lib/auth.lib.inc.php');
include($includePath.'/lib/claro_mail.lib.inc.php');

// Initialise variables

$passwordFound = FALSE;
$msg = "";

// Get the forgotten email from the form

if ( isset ($_REQUEST['Femail']) ) $Femail = strtolower(trim($_REQUEST['Femail']));
else $Femail = "";

// Main section

if (isset($_REQUEST['searchPassword']) && !empty($Femail) )
{  

    // search user with this email

	$sql = 'SELECT  `user_id` AS `uid`, 
					`nom` AS `lastName`, 
					`prenom` AS `firstName`, 
	                `username` AS `loginName`, 
					`password`, 
					`email`, 
					`creatorId`
	         FROM `'.$tbl_user.'`
	         WHERE LOWER(email) LIKE "'. claro_addslashes($Femail) .'"
	               AND   `email` != "" ';
	$result = claro_sql_query($sql);

	if ($result)
	{
		if (mysql_num_rows($result) > 0)
		{
			while ($data = mysql_fetch_array($result))
			{
				$user [] = $data;
			}

			/*
			 * If password are crypted, we can not send them as they are.
			 * There are unusable for the end user. So, we have to generate new ones.
			 */
			if ($userPasswordCrypted) // $userPasswordCrypted comes claro_main.conf.php
			{
				for ($i = 0, $j = count($user); $i < $j; $i++)
				{
					$user[$i]['password'] = generate_passwd();

					// UPDATE THE DB WITH THE NEW GENERATED PASSWORD

					$result = claro_sql_query('UPDATE `'.$tbl_user.'`
					                     SET `password` = "'.md5($user[$i]['password']).'"
					                     WHERE `user_id` = "'.$user[$i]['uid'].'"');
				}
			}

			$passwordFound = true;

			/*
			 * Prepare the email message wich has to be send to the user
			 */

            // mail subject
			$emailSubject = $langLoginRequest." ".$siteName;


			// mail body
			foreach($user as $thisUser)
			{
				$userAccountList [] = $thisUser['firstName']." ".$thisUser['lastName']."\r\n\r\n"
									 ."\t".$langUserName." : ".$thisUser['loginName']."\r\n"
									 ."\t".$langPassword." : ".$thisUser['password']." \r\n";
			}

			if ($userAccountList)
			{
				$userAccountList = implode ("-----------\r\n", $userAccountList);
			}
			
			$emailBody = $emailSubject."\r\n"
			            .$rootWeb."\r\n"
			            .$langYourAccountParam."\r\n\r\n"
			            .$userAccountList;

			// send message
            $emailTo = $user[0]['uid'];
      
            if( claro_mail_user($emailTo, $emailBody, $emailSubject) )
			{
				$msg = $langPasswordHasBeenEmailed.$Femail;
			}
			else
			{
				$msg = $langEmailNotSent
                .	'<a href="mailto:'.$administrator_email.'?BODY='.$Femail.'">'
                .	$langPlatformAdministrator
                .	"</a>";
			}	

		} 	// end if mysql_num_rows($result) > 0
		else
		{
			$msg = $langEmailAddressNotFound;
		}
	}
}
else
{
	$msg = "<p>".$langEnterMail."</p>";
}

// display section

include($includePath.'/claro_init_header.inc.php');

// display title

claro_disp_tool_title($nameTools);

// display message box

if ( ! empty($msg)) claro_disp_message_box($msg);

// display form

if ( ! $passwordFound)
{ 
?>
<br />
<form action="<?php echo $_SERVER['PHP_SELF']?>" method="post">
<input type="hidden" name="searchPassword" value="1">
<fieldset>
<table>
	<tr>
		<td>
			<label for="Femail"><?php echo $langEmail ?> : </label>
		</td>
		<td>
			<input type="text" name="Femail" id="Femail" size="50" maxlength="100" value="<?php echo $Femail ?>">
		</td>
	</tr>
	<tr>
		<td>
		</td>
		<td>
			<input type="submit" name="retrieve" value="Submit">
		</td>
	</tr>
</table>
</fieldset>
</form>
<?php
}

include($includePath."/claro_init_footer.inc.php");
?>
