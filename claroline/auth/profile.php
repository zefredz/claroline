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

$langFile = 'registration';
$cidReset = true;

#default - don't edit default !!! change in config files
$userOfficialCodeCanBeEmpty = true;
$userMailCanBeEmpty			= true;

require '../inc/claro_init_global.inc.php';
include $includePath.'/conf/profile.conf.inc.php'; // find this file to modify values.
include $includePath.'/lib/text.lib.php';
include $includePath.'/lib/fileManage.lib.php';
include $includePath.'/lib/auth.lib.inc.php';

$nameTools = $langModifProfile;

$tbl_log    = $mainDbName."`.`loginout";
$tbl_user   = $mainDbName."`.`user";

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
	$form_password1    = trim($_REQUEST['form_password1'   ]);
	$form_password2    = trim($_REQUEST['form_password2'   ]);
	$form_userName     = claro_strip_tags ( trim($_REQUEST['form_userName'    ]) );

	$form_officialCode = claro_strip_tags ( trim($_REQUEST['form_officialCode']) );
	$form_lastName     = claro_strip_tags ( trim($_REQUEST['form_lastName'    ]) );
	$form_firstName    = claro_strip_tags ( trim($_REQUEST['form_firstName'   ]) );
	$form_email        = claro_strip_tags ( trim($_REQUEST['form_email'       ]) );

	$form_del_picture  = trim($_REQUEST['form_del_picture'] );


	/*
	 * CHECK IF USERNAME IS ARLEADY TAKEN BY ANOTHER USER
	 */

    $sql = "SELECT COUNT(*) loginNameCount
            FROM `".$tbl_user."`
            WHERE username = \"".$form_userName."\"
            AND `user_id` <> '".$_uid."'";

    list($result) = claro_sql_query_fetch_all($sql);

    if ($result['loginNameCount'] > 0)
    {
		$userNameOk = false;
		$messageList[] = $langUserTaken;
    }
    else
	{
		$userNameOk = true;
	}

    $sql_ActualUserInfo = "SELECT `username`  `userName`,
                                  `nom`       `lastName`,
                                  `prenom`     `firstName`,
                                  `pictureUri` `actual_ImageFile`
                          FROM `".$tbl_user."`
                          WHERE `user_id` = '".$_uid."'";

    list($data_ActualUserInfo) = claro_sql_query_fetch_all($sql_ActualUserInfo);

    if (is_null($data_ActualUserInfo))
    {
        $data_ActualUserInfo = array('lastName' => '', 'firstName' => '', 
                                     'userName' => '', 'actual_ImageFile' => '');
    }
    

	/*
	 * CHECK BOTH PASSWORD TOKEN ARE THE SAME
	 */

	if ($form_password2 !== $form_password1)
	{
		$passwordOK    = false;
		$messageList[] = $langPassTwo.'<br>';
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

    if ( $form_password1 && SECURE_PASSWORD_REQUIRED && $passwordOK)
    {
        if (is_password_secure_enough($form_password,
                                      array($form_userName, $form_officalCode, 
                                            $form_lastName, $form_firstName, $form_email)))
        {
        	$passwordOK = true;
        }
        else
        {
            $passwordOK = false;
            $messageList[] =  $langPassTooEasy." :\n"
                            ."<code>".substr( md5( date('Bis').$HTTP_REFFERER ), 0, 8 )."</code>\n";
        }       
    }
    



	/*
	 * CHECK THERE IS NO EMPTY FIELD
	 */

	if (     empty($form_lastName) 
        ||   empty($form_firstName)
	    ||   empty($form_userName)
	    || ( empty($form_officialCode) && ! $userOfficialCodeCanBeEmpty )
	    || ( empty($form_email)        && ! $userMailCanBeEmpty         ) )
	{
        $importantFieldFilled = false;
		$messageList[] =  $langFields;
	}
	else
	{
		$importantFieldFilled = true;
	}


	/*
	 * CHECK EMAIL SYNTAX
	 */

	$emailRegex = "^[0-9a-z_\.-]+@(([0-9]{1,3}\.){3}[0-9]{1,3}|([0-9a-z][0-9a-z-]*[0-9a-z]\.)+[a-z]{2,4})$";

	if( ! empty($form_email) && ! eregi( $emailRegex, $form_email ))
	{
		$emailOk = false;
		$messageList[] = $langEmailWrong;
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
		$messageList[] = '<b>'.$langAgain.'</b>';
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
		if ($form_del_picture== 'yes')
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
                    phoneNumber   = \"".$form_phone."\",
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
		include('../inc/claro_init_local.inc.php');
		$messageList[] = $langProfileReg."<br>\n"
		                ."<a href=\"../../index.php\">".$langHome."</a>";

	} // end if $userSettingChangeAllowed

}	// end iF applyChange


//////////////////////////////////////////////////////////////////////////////


$sql = "SELECT nom, prenom, username, email , pictureUri, officialCode, phoneNumber
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
    $form_phone        = $myrow['phoneNumber' ];

	$disp_picture      = $myrow['pictureUri'  ];
}

//////////////////////////////////////////////////////////////////////////////

/*==========================
         DISPLAY
  ==========================*/
include('../inc/claro_init_header.inc.php');
claro_disp_tool_title($nameTools);

if ( count($messageList) > 0) claro_disp_message_box( implode('<br />', $messageList) );

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



echo	"<table>
		   <tr>
              <td align=\"right\"><label for=\"form_lastName\" >",$langLastname,"</label> : </td>\n",

		     "<td valign=\"middle\">\n",
			   "<input type=\"text\" size=\"40\" id=\"form_lastName\" name=\"form_lastName\" value=\"".$form_lastName."\">\n",
		     "</td>\n",

    	  "</tr>\n",

          "<tr>\n",
            "<td  align=\"right\">\n<label for=\"form_firstName\">",
              $langFirstname,"</label> : \n",
            "</td>\n",
            "<td >\n",
              "<input type=\"text\" size=\"40\" name=\"form_firstName\" id=\"form_firstName\" value=\"",$form_firstName,"\">\n",
            "</td>\n",
          "</tr>\n";

if (CONFVAL_ASK_FOR_OFFICIAL_CODE)
{
	echo
		"<tr>\n",
		"<td   align=\"right\">\n<label for=\"form_officialCode\">",
		$langOfficialCode,"</label> : \n",
		"</td>\n",
		"<td>\n",
		"<input type=\"text\" size=\"40\" name=\"form_officialCode\" id=\"form_officialCode\" value=\"",$form_officialCode,"\">\n",
		"</td>\n",
		"</tr>\n";
}
?>
<tr>
    <td >
    </td>
    <td >
       <br>
    </td>
</tr>
<?
/*    //PICTURE
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
*/

echo
        "<tr>\n",
        "<td></td>",
        "<td>\n<small>",
        $langEnter2passToChange,
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
		"<td  align=\"right\">\n<label for=\"form_password1\">",
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
		"</tr>\n";

?>
<tr>
    <td >
    </td>
    <td >
       <br>
    </td>
</tr>
<?

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
        "<tr>\n",
        "<td align=\"right\">\n",
        "<label for=\"form_email\">",$langPhone,"</label> : \n",
        "</td>\n",
        "<td >\n",
        "<input type=\"text\" size=\"40\" name=\"form_phone\" id=\"form_phone\" value=\"",$form_phone,"\">\n",
        "</td>",
        "</tr>";


echo 	"<tr>\n"
		."<td></td>\n",

		"<td>\n",
		"<input type=\"submit\" name=\"applyChange\" value=\" ",$langSaveChange," \">\n",
		"</td>\n",
		"</tr>\n",

		"</table>\n",

		"</form>\n</P>";

echo "
<p>
	<hr noshade size=\"1\">
	<a href='../tracking/personnalLog.php'>".$langMyStats."</a>
</p>";

include($includePath."/claro_init_footer.inc.php");
?>
