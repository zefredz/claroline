<?php # $Id$

//----------------------------------------------------------------------
// CLAROLINE
//----------------------------------------------------------------------
// Copyright (c) 2001-2003 Universite catholique de Louvain (UCL)
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

$langFile = "registration";
require '../inc/claro_init_global.inc.php';
$nameTools = $langLostPassword;

/*
 * DB tables definition
 */
$tbl_mdb_names = claro_sql_get_main_tbl();
$tbl_user            = $tbl_mdb_names['user'];

include($includePath.'/claro_init_header.inc.php');

include($includePath.'/lib/claro_mail.lib.inc.php');
claro_disp_tool_title($nameTools);

if ($searchPassword)
{
	$Femail = strtolower(trim($Femail));

	$result = claro_sql_query('SELECT `user_id` AS `uid`, `nom` AS `lastName`, `prenom` AS `firstName`, 
	                    `username` AS `loginName`, `password`, `email`, `statut` AS `status`, 
	                    `officialCode`, `phoneNumber`, `pictureUri`, `creatorId`
	                    FROM `'.$tbl_user.'`
	                    WHERE LOWER(email) LIKE "'.$_REQUEST['Femail'].'"
	                    AND   `email` != "" ');

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

      // SUBJECT

			$emailSubject = $langLoginRequest." ".$siteName;


			// BODY

			foreach($user as $thisUser)
			{
				$userAccountList [] = $thisUser['firstName']." ".$thisUser['lastName']."\r\n\r\n"
									 ."\t".$langUsername." : ".$thisUser['loginName']."\r\n"
									 ."\t".$langPass." : ".$thisUser['password']." \r\n";
			}

			if ($userAccountList)
			{
				$userAccountList = implode ("-----------\r\n", $userAccountList);
			}
			
			$emailBody = $emailSubject."\r\n"
			            .$rootWeb."\r\n"
			            .$langYourAccountParam."\r\n\r\n"
			            .$userAccountList;

			// SEND MESSAGE

      $emailTo = $user[0]['uid'];
      
      if( claro_mail_user($emailTo, $emailBody, $emailSubject) )
			{
				$msg = $langPasswordHasBeenEmailed.$Femail;
			}
			else
			{
				$msg = $langEmailNotSent
                .	"<a href=\"mailto:".$administrator["email"]."\">"
                .	$langPlatformAdmin
                .	"</a>";
			}
			

		}				// end if mysql_num_rows($result) > 0
		else
		{
			$msg = $langEmailAddressNotFound;
		}
	}
  if ($msg) claro_disp_message_box($msg);
}
else
{
	echo "<p>".$langEnterMail."</p>";
}



if ( ! $passwordFound)
{ ?>
<br />
<form action="<?php echo $PHP_SELF?>" method="post">
<input type="hidden" name="searchPassword" value="1">
<table>
<tr>
<td><label for="Femail"><?php echo $langEmail ?> : </label></td>
<td><input type="text" name="Femail" id="Femail" size="50" maxlength="100" value="<?php echo $Femail ?>"></td>
</tr>
<td></td>
<td><input type="submit" name="retrieve" value="Submit"></td>
</tr>
</table>
</form>

<?php
}

include($includePath."/claro_init_footer.inc.php");

//////////////////////////////////////////////////////////////////////////////

/**
 * generates randomly a new password
 *
 * @author Damien Seguy
 * @param - void
 * @return string the new password
 */
 
function generate_passwd()
{
	if (func_num_args() == 1) $nb = func_get_arg(0);
	else                      $nb = 8;

	// on utilise certains chiffres : 1 = i, 5 = S, 6=b, 3=E, 9=G, 0=O

	$lettre = array();

	$lettre[0] = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 
	                   'j', 'k', 'l', 'm', 'o', 'n', 'p', 'q', 'r', 
	                   's', 't', 'u', 'v', 'w', 'x', 'y', 'z', 'A', 
	                   'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 
	                   'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'D', 
	                   'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', '9', 
	                   '0', '6', '5', '1', '3');

	$lettre[1] =  array('a', 'e', 'i', 'o', 'u', 'y', 'A', 'E', 
	                    'I', 'O', 'U', 'Y' , '1', '3', '0' );

	$lettre[-1] = array('b', 'c', 'd', 'f', 'g', 'h', 'j', 'k', 
	                    'l', 'm', 'n', 'p', 'q', 'r', 's', 't', 
	                    'v', 'w', 'x', 'z', 'B', 'C', 'D', 'F', 
	                    'G', 'H', 'J', 'K', 'L', 'M', 'N', 'P', 
	                    'Q', 'R', 'S', 'T', 'V', 'W', 'X', 'Z', 
	                    '5', '6', '9');

	$retour   = "";
	$prec     = 1;
	$precprec = -1;

	srand((double)microtime()*20001107);

	while(strlen($retour) < $nb)
	{
		// To generate the password string we follow these rules : (1) If two 
		// letters are consonnance (vowel), the following one have to be a vowel 
		// (consonnace) - (2) If letters are from different type, we choose a 
		// letter from the alphabet.

		$type     = ($precprec + $prec)/2;
		$r        = $lettre[$type][array_rand($lettre[$type], 1)];
		$retour  .= $r;
		$precprec = $prec;
		$prec     = in_array($r, $lettre[-1]) - in_array($r, $lettre[1]);

	}
	return $retour;
}

?>
