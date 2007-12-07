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

$langFile = "registration";
$cidReset=TRUE;
require '../../inc/claro_init_global.inc.php';

$nameTools 			= $lang_SearchUser_SearchUser;
$interbredcrump[]	= array ("url"=>$rootAdminWeb, "name"=> $langAdministrationTools);
$noQUERY_STRING 	= TRUE;

//****************************
//Change $includePath
//****************************
include($includePath."/lib/text.lib.php");
include($includePath."/lib/debug.lib.inc.php");
include($includePath.'/conf/profile.conf.inc.php'); // find this file to modify values.
include($includePath.'/lib/fileManage.lib.php');

$dateNow 			= claro_format_locale_date($dateTimeFormatLong);
$is_allowedToAdmin 	= $is_platformAdmin || $PHP_AUTH_USER;

/* Pour le lien vers ces stat et ces cours
$tbl_log    = $mainDbName."`.`loginout";*/
$tbl_user   = $mainDbName."`.`user";


//****************************
//Change uid
//****************************
$_uid=$_REQUEST["user_id"];

//If no user selected, error
if(empty($_uid))
{
	$controlMsg["error"][]=$langNoUserSelected;
}
else
{

	if (isset($userImageRepositorySys))
		$userImageRepositorySys = $clarolineRepositorySys.'img/users/';
	if (isset($userImageRepositoryWeb))
		$userImageRepositoryWeb = $clarolineRepositoryWeb.'img/users/';
	///// COMMAND ///

	if ($_REQUEST['applyChange'])
	{
		/*
		*==========================
		* DATA CHECKING
		*==========================
		*/
		$form_password1    = trim($_REQUEST['form_password1']   );
		$form_password2    = trim($_REQUEST['form_password2']   );
		$form_userName     = trim($_REQUEST['form_userName']    );

		$form_officialCode = trim($_REQUEST['form_officialCode']);
		$form_lastName     = trim($_REQUEST['form_lastName']    );
		$form_firstName    = trim($_REQUEST['form_firstName']   );
		$form_email        = trim($_REQUEST['form_email']       );

		$form_del_picture  = trim($_REQUEST['form_del_picture'] );


		/*
		* CHECK IF USERNAME IS ARLEADY TAKEN BY ANOTHER USER
		*/

		$sql = "SELECT user_id
				FROM `".$tbl_user."`
				WHERE username = \"".$form_userName."\" AND `user_id` != '".$_uid."'";

		$username_check = mysql_query($sql);

		while ( $myusername = mysql_fetch_array($username_check, MYSQL_ASSOC) )
		{
			$userNameAlreadyExist = true;
			$userNameOwner        = $myusername['user_id'];
		}

		if( $userNameAlreadyExist && ($userNameOwner != $_uid) )
		{
			$userNameOk = false;
			$msgClass ="error"; // success | warning | error
			$msgArrBody[$msgClass][] ="$langUserTaken";
		}
		else
		{
			$userNameOk = true;
		}

		$sql_ActualUserInfo = "
	SELECT
	`username` `userName`,
	`nom` `lastName`,
	`prenom` `firstName`,
	`pictureUri` `actual_ImageFile`
				FROM `".$tbl_user."`
				WHERE `user_id` = '".$_uid."'";
		$res_ActualUserInfo = @mysql_query($sql_ActualUserInfo) or die(mysql_error());
		$data_ActualUserInfo = mysql_fetch_array($res_ActualUserInfo,MYSQL_ASSOC);

		/*
		* CHECK BOTH PASSWORD TOKEN ARE THE SAME
		*/

		if ($form_password2 !== $form_password1)
		{
			$passwordOK    = false;
			$msgClass ="error"; // success | warning | error
			$msgArrBody[$msgClass][] = $langPassTwo.'<br>';
			$form_password = '';
		}
		else
		{
			$passwordOK = true;
			$form_password = $form_password2 ;
		}


		/*
		* CHECK PASSWORD AREN'T TOO EASY
		*/

		if ($form_password1 && SECURE_PASSWORD_REQUIRED)
		{
			if ( ( strtoupper($form_password) == strtoupper($form_userName) )
				|| ( strtoupper($form_password) == strtoupper($form_officalCode) )
				|| ( strtoupper($form_password) == strtoupper($form_lastName) )
				|| ( strtoupper($form_password) == strtoupper($form_firstName))
				|| ( strtoupper($form_password) == strtoupper($data_ActualUserInfo['userName']) )
				|| ( strtoupper($form_password) == strtoupper($data_ActualUserInfo['lastName']) )
				|| ( strtoupper($form_password) == strtoupper($data_ActualUserInfo['firstName']))
				|| ( strtoupper($form_password) == strtoupper($form_email)    ) )
			{
				$passwordOK = false;
				$msgClass ="error"; // success | warning | error
				$msgArrBody[$msgClass][] =  $langPassTooEasy." :\n"
							."<code>".substr( md5( date('Bis').$HTTP_REFFERER ), 0, 8 )."</code><br>\n";
			}
			else
			{
				$passwordOK = true;
			}
		}


		/*
		* CHECK THERE IS NO EMPTY FIELD
		*/

		if (   empty($form_lastName)
			|| empty($form_firstName)
			|| empty($form_userName)
			|| (empty($form_officialCode) && !$userOfficialCodeCanBeEmpty)
			|| (empty($form_email) && !$userMailCanBeEmpty)
		)
		{
			$importantFieldFilled = false;
			$msgClass ="error"; // success | warning | error
			$msgArrBody[$msgClass][] =  $langFields;
		}
		else
		{
			$importantFieldFilled = true;
		}


		/*
		* CHECK EMAIL SYNTAX
		*/

		$emailRegex = "^[0-9a-z_\.-]+@(([0-9]{1,3}\.){3}[0-9]{1,3}|([0-9a-z][0-9a-z-]*[0-9a-z]\.)+[a-z]{2,3})$";

		if( ! empty($form_email) && ! eregi( $emailRegex, $form_email ))
		{
			$emailOk = false;
			$msgClass ="error"; // success | warning | error
			$msgArrBody[$msgClass][] = $langEmailWrong.'<br>';
		}
		else
		{
			$emailOk = true;
		}


		/*
		* RESUME ALL THE CHECKIN
		*/

		if ($importantFieldFilled && $emailOk && $userNameOk && $passwordOK)
		{
			$userSettingChangeAllowed = true;
		}
		else
		{
			$userSettingChangeAllowed = false;
			$msgClass ="error"; // success | warning | error
			$msgArrBody[$msgClass][] = '<b>'.$langAgain.'</b>';
		}

		/*
		*--------------------------------------
		* PROCEED TO THE USER SETTINGS CHANGE
		*--------------------------------------
		*/

		if ($userSettingChangeAllowed)
		{
			/*
			* UPLOAD A USER IMAGE
			*
			* Originally added by Miguel (miguel@cesga.es) - 04/11/2003
			* Image resizing  added by Patrick Cool Ugent - 24/11/2003
			* Code Refactoring Hugues Peeters (hugues.peeters@claroline.net) 24/11/2003
			*/
			$actualImage = $data_ActualUserInfo['actual_ImageFile'];
			if ($form_del_picture=="yes")
			{
				$form_picture = NULL;
			}
			elseif ( is_uploaded_file( $form_picture ) )
			{
				$fileExtension = strtolower( array_pop( explode(".",$HTTP_POST_FILES['form_picture']['name']) ) );

				if ( in_array($fileExtension, array('php', 'php4', 'php3', 'phtml') ) )
				{
					die('<center>No PHP Filles allowed</center>');
				}
				mkpath($userImageRepositorySys);
				$user_have_pic = (bool) (trim($actualImage)!="");
				if ($user_have_pic)
				{
					if (KEEP_THE_NAME_WHEN_CHANGE_IMAGE)
					{
						$picture_FileName     = $actualImage;
						$old_picture_FileName  = "save_".date("Y_m_d_H_i_s")."_".uniqid('')."_".$actualImage;
					}
					else
					{
						$old_picture_FileName     = $actualImage;
						$picture_FileName     = (PREFIX_IMAGE_FILENAME_WITH_UID?"u".$_uid."_":"").uniqid('').".".$fileExtension;
					}
					if (KEEP_THE_OLD_IMAGE_AFTER_CHANGE)
					{
						rename($userImageRepositorySys.$actualImage,$userImageRepositorySys.$old_picture_FileName);
					}
					else
					{
						unlink($userImageRepositorySys.$actualImage);
					}

				}
				else
				{
					$picture_FileName     = (PREFIX_IMAGE_FILENAME_WITH_UID?$_uid."_":"").uniqid('').".".$fileExtension;
				}


				if (move_uploaded_file( $form_picture,
										$userImageRepositorySys.$picture_FileName))
				{
					/*
					*--------------------------------------
					*            Image resizing
					*--------------------------------------
					*/

					if ( extension_loaded('gd') ) // Check the GD library is available
					{
						// Get Width, Height and type from the original image

						list($actualWidth,
							$actualHeight,
							$type, )       = getimagesize($userImageRepositorySys.$picture_FileName);


						if ($type == 2) // Check to see if it is a reall JPEF file
						{               // 1 stands for GIF, 2 for JPG, 3 for PNG

							// Set and compute the final image size

							$finalHeight         = RESIZE_IMAGE_TO_THIS_HEIGTH;
							$factor              = $actualHeight / $finalHeight;
							$finalWidth          = round( $actualWidth / $factor );

							// Create an image from the original image file

							$actualImage = ImageCreateFromJPEG($userImageRepositorySys.$picture_FileName)
										or die('<center>can not open image</center>');

							// Create a new image set with new size

							$finalImage   = ImageCreate($finalWidth, $finalHeight)
											or die('<center>can not create new image</center>');

							// Copy and resize the original image into the new one

							ImageCopyResized( $finalImage,
											$actualImage,
											0,
											0,
											0,
											0,
											$finalWidth,
											$finalHeight,
											ImageSX($actualImage),
											ImageSY($actualImage) )

								or die("<center>can not resize image</center>");

							// Store the final image

							ImageJPEG($finalImage, $userImageRepositorySys.$picture_FileName)
								or die('<center>can not save image</center>');

							$picture = $picture_FileName;

						}			// end if type == JPEG
					}				// end if GD extension loaded
				}					 // end if move_uploaded file
			}						// end if is_uploaded_file $form_picture

			if ($userPasswordCrypted) $form_password1 = md5(trim($form_password1));

			$sql = "UPDATE  `".$tbl_user."`

					SET nom        = \"".$form_lastName."\",
						prenom     = \"".$form_firstName."\",
						username   = \"".$form_userName."\",
						email      = \"".$form_email."\" ";

			if ($form_officialCode) $sql .= ", officialCode   = \"".$form_officialCode."\" ";
			if ($form_password) $sql .= ", password   = \"".$form_password."\" ";
			if ($form_picture||$form_del_picture)
			{
				if ($form_del_picture=="yes")
				{
					$sql .= ", pictureUri = null ";
					if (KEEP_THE_OLD_IMAGE_AFTER_CHANGE)
					{
						rename($userImageRepositorySys.$actualImage, $userImageRepositorySys."deleted_".date("Y_m_d_H_i_s")."_".$actualImage);
					}
					else
					{
						unlink($userImageRepositorySys.$actualImage);
					}
				}
				else
				{
					$sql .= ", pictureUri = \"".$picture."\" ";
				}
			}

			$sql .= " WHERE user_id  = \"".$_uid."\"";

			mysql_query($sql) or die ('<center>can not UPDATE user data</center>');

			/*
			* re-init the system to take new settings in account
			*/

			$uidReset = true;
			$controlMsg["info"][]=$lang_SearchUser_ModifOk;
		} // end if $userSettingChangeAllowed

	}	// end iF applyChange


	$sql = "SELECT nom, prenom, username, email , pictureUri, officialCode
			FROM  `".$tbl_user."`
			WHERE user_id = \"".$_uid."\"";

	$result = mysql_query($sql) or die("Erreur SELECT FROM user");

	if ($result)
	{
		$myrow = mysql_fetch_array($result);

		$form_lastName     = $myrow['nom'         ];
		$form_firstName    = $myrow['prenom'      ];
		$form_userName     = $myrow['username'    ];
		$form_officialCode = $myrow['officialCode'];
		$form_email        = $myrow['email'       ];

		$disp_picture      = $myrow['pictureUri'  ];
	}

	//////////////////////////////////////////////////////////////////////////////
}

/*==========================
         DISPLAY
  ==========================*/
include($includePath.'/claro_init_header.inc.php');
claro_disp_tool_title($nameTools);
claro_disp_msg_arr($msgArrBody);

claro_disp_tool_title(
    array(
    'mainTitle'=>$nameTools,
    'subTitle'=> $PHP_AUTH_USER." - ".$siteName." - ".$clarolineVersion." - ".$dateNow
    )
    );
claro_disp_msg_arr($controlMsg);


if(!empty($_uid))
{
	/*
	* Data Form
	*/

	echo	"<P><form method=\"post\" action=\"".$PHP_SELF."\" \n",
			" enctype=\"multipart/form-data\">\n";


	if( $disp_picture != '')
	{
		echo
			"<img align=\"right\" ",
				"alt=\"".$form_lastName." ".$form_firstName."\" ",
				"src=\"".$clarolineRepositoryWeb."img/users/",$disp_picture,"\"	",
				"border=\"0\" ",
				"hspace=\"5\" ",
				"vspace=\"5\">";
	}
	else
	{
		echo    "&nbsp;";
	}



	echo	"<table>\n",

			"<tr>\n",

			"<td align=\"right\"><label for=\"form_lastName\" >",$langName,"</label> : </td>\n",

			"<td valign=\"middle\">\n",
				"<input type=\"text\" size=\"40\" id=\"form_lastName\" name=\"form_lastName\" value=\"".$form_lastName."\">\n",
			"</td>\n";

	echo	"</tr>\n",

			"<tr>\n",
			"<td  align=\"right\">\n<label for=\"form_firstName\">",
			$langSurname,"</label> : \n",
			"</td>\n",
			"<td >\n",
			"<input type=\"text\" size=\"40\" name=\"form_firstName\" id=\"form_firstName\" value=\"",$form_firstName,"\">\n",
			"</td>\n",
			"</tr>\n";

	if (CONFVAL_ASK_FOR_OFFICIAL_CODE)
	{
		echo
			"<tr>\n",
			"<td  align=\"right\">\n<label for=\"form_officialCode\">",
			$langOfficialCode,"</label> : \n",
			"</td>\n",
			"<td>\n",
			"<input type=\"text\" size=\"40\" name=\"form_officialCode\" id=\"form_officialCode\" value=\"",$form_officialCode,"\">\n",
			"</td>\n",
			"</tr>\n";
	}
	echo
			"<tr>\n",
			"<td align=\"right\">\n<label for=\"form_email\">",
			$langEmail,"</label> : \n",
			"</td>\n",
			"<td >\n",
			"<input type=\"text\" size=\"40\" name=\"form_email\" id=\"form_email\" value=\"",$form_email,"\">\n",
			"</td>",
			"</tr>";

	echo

			"<tr>\n"
			."<td align=\"right\">\n<label for=\"form_picture\">"

			.($disp_picture?$langUpdateImage:$langAddImage)." : "
			."<br><small>(.jpg or .jpeg only)</br></label>\n"
			."</td>\n"
			."<td>\n"
			."<input type=\"file\" name=\"form_picture\" id=\"form_picture\" >\n"
			.( $disp_picture
			?
			"<br><label for=\"form_del_picture\">"
			.$langDelImage
			."</label><input type=\"checkbox\" name=\"form_del_picture\" id=\"form_del_picture\" value=\"yes\">\n"." : "
			:
			""
			)
			."</td>\n"
			."<tr>\n";

	echo
			"<tr>\n",
			"<td colspan=\"2\" >\n<small>",
			$langAuthInfo,
			"</small></td>\n",
			"</tr>\n",
			"<tr>\n",
			"<td  align=\"right\">\n<label for=\"form_userName\">",
			$langUsername,"</label> : \n",
			"</td>\n",
			"<td>\n",
			"<input type=\"text\" size=\"40\" name=\"form_userName\" id=\"form_userName\" value=\"",$form_userName,"\">\n",
			"</td>\n",
			"</tr>\n",
			"<tr>\n",
			"<td colspan=\"2\" >\n<small>",
			$langEnter2passToChange,
			"</small></td>\n",
			"</tr>\n",
			"<tr>\n",
			"<td align=\"right\">\n<label for=\"form_password1\">",
			$langPass,"</label> : \n",
			"</td>\n",
			"<td>\n",
			"<input type=\"password\" size=\"40\" name=\"form_password1\" id=\"form_password1\" value=\"\">\n",
			"</td>\n",
			"</tr>\n",

			"<tr>\n",
			"<td align=\"right\">\n<label for=\"form_password2\">",
			"",$langConfirmation,"</label> : \n",
			"</td>\n",
			"<td>\n",
			"<input type=\"password\" size=\"40\" name=\"form_password2\" id=\"form_password2\" value=\"\">\n",
			"</td>\n",
			"</tr>\n",

			//***************************
			//Ajout pour id
			//***************************
			"<tr>\n",
			"<td>\n",
			"<input type=\"hidden\" name=\"user_id\" id=\"user_id\" value=\"$_uid\">\n",
			"</td>\n",
			"</tr>\n";

	echo 	"<tr>\n"
			."<td></td>\n",

			"<td>\n",
			"<input type=\"submit\" name=\"applyChange\" value=\"",$langOk,"\">\n",
			"</td>\n",
			"</tr>\n",

			"</table>\n",

			"</form>\n</P>";


	echo   "<a href=\"search_user.php?display=1&user_id=".$_uid."\">".$langReturnSearchUser."</a>";


			//Suppression des stat et lien vers ces cours
	/*
	echo "
	<p>
		<hr noshade size=\"1\">
		<a href='../tracking/personnalLog.php'>".$langMyStats."</a>
		<a href='../auth/courses.php'>".$langMyCourses."</a>
	</p>";
	*/
}


include($includePath."/claro_init_footer.inc.php");
?>
