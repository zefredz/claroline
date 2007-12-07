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


// 4 Commands, 3 displays
// default Display : Form to edit own profile

$cidReset = TRUE;
$gidReset = TRUE;

#default - don't edit default !!! change in config files
$userOfficialCodeCanBeEmpty    = TRUE;
$userMailCanBeEmpty            = TRUE;

define('DISP_COURSE_CREATOR_STATUS_REQ',__LINE__);
define('DISP_REVOQUATION',__LINE__);

require '../inc/claro_init_global.inc.php';
include $includePath.'/conf/user_profile.conf.php'; // find this file to modify values.
include $includePath.'/lib/fileManage.lib.php';
include $includePath.'/lib/auth.lib.inc.php';
include($includePath.'/lib/claro_mail.lib.inc.php');

$nameTools = $langModifyProfile;

/*
 * DB tables definition
 */
$tbl_mdb_names = claro_sql_get_main_tbl();
$tbl_user      = $tbl_mdb_names['user'];

if (isset($userImageRepositorySys))
    $userImageRepositorySys = $clarolineRepositorySys.'img/users/';
if (isset($userImageRepositoryWeb))
    $userImageRepositoryWeb = $imgRepositoryWeb.'users/';
///// COMMAND ///

if (isset($can_request_course_creator_status) && $can_request_course_creator_status && $_REQUEST['exCCstatus'])
{
	$mailToUidList = claro_get_uid_of_platform_admin();
	
	$requestMessage_Title = '['.$siteName.'][Rq]'
	                   .sprintf($langCourseManagerStatusToUser,$_user['lastName'],$_user['firstName']);
	$requestMessage_Content = '
     '.claro_disp_localised_date( $dateFormatLong).'
	 '.sprintf($langCourseManagerStatusToUser,$_user['lastName'],$_user['firstName']).'
User:'.$_uid.'
     '.$_user['firstName'].'
     '.$_user['lastName'].'
     '.$_user['mail'].'
     '.$_user['lastLogin'].'
*Comment : '.$_REQUEST['explanation'].'
*User profile : '.$rootAdminWeb.'adminprofile.php?uidToEdit='.$_uid.' ';
	foreach ($mailToUidList as $mailToUid)
	{
		claro_mail_user($mailToUid['idUser'], $requestMessage_Content, $requestMessage_Title, $administrator_email, 'profile');
	}

	$messageList[] = $langYourRequestToBeCourseManagerIsSent;
}
elseif (isset($can_request_revoquation) && $can_request_revoquation && $_REQUEST['exRevoquation'])
{
	$mailToUidList = claro_get_uid_of_platform_admin();
	$requestMessage_Title = '['.$siteName.'][Rq]'
	                   .sprintf($langRevoquationOfUser,$_user['lastName'],$_user['firstName']);
	$requestMessage_Content = '
     '.claro_disp_localised_date( $dateFormatLong).'
	 '.sprintf($langRevoquationOfUser,$_user['lastName'],$_user['firstName']).'
User:'.$_uid.'
     '.$_user['firstName'].'
     '.$_user['lastName'].'
     '.$_user['mail'].'
     '.$_user['lastLogin'].'

     login de confirmation '.$_REQUEST['loginToDelete'].'
     paswd de confirmation '.$_REQUEST['passwordToDelete'].'

*comment : '.$_REQUEST['explanation'].'
*user profile : '.$rootAdminWeb.'adminprofile.php?uidToEdit='.$_uid.' ';
	foreach ($mailToUidList as $mailToUid)
	{
		claro_mail_user($mailToUid['idUser'], $requestMessage_Content, $requestMessage_Title, $administrator_email, 'profile');
	}
	$messageList[] = $langYourRequestToRemoveYourAccountIsSent;

}
elseif (isset($can_request_course_creator_status) && $can_request_course_creator_status  && $_REQUEST['reqCCstatus'])
{
	$noQueryString=TRUE;
	$display = DISP_COURSE_CREATOR_STATUS_REQ;
	$nameTools = $langRequestOfCourseCreatorStatus;
}
elseif (isset($can_request_revoquation) && $can_request_revoquation && $_REQUEST['reqRevoquation'])
{
	$noQueryString=TRUE;
	$display = DISP_REVOQUATION;
}
elseif ($_REQUEST['applyChange'])
{
    /*
     *==========================
     * DATA CHECKING
     *==========================
     */
    $form_password1    = trim($_REQUEST['form_password1'   ]);
    $form_password2    = trim($_REQUEST['form_password2'   ]);
    $form_userName     = stripslashes ( trim($_REQUEST['form_userName'    ]) );

    $form_officialCode = stripslashes ( trim($_REQUEST['form_officialCode']) );
    $form_lastName     = stripslashes ( trim($_REQUEST['form_lastName'    ]) );
    $form_firstName    = stripslashes ( trim($_REQUEST['form_firstName'   ]) );
    $form_email        = stripslashes ( trim($_REQUEST['form_email'       ]) );

    $form_del_picture  = trim($_REQUEST['form_del_picture'] );

    /*
     * CHECK IF USERNAME IS ALREADY TAKEN BY ANOTHER USER
     */

    $sql = 'SELECT COUNT(*) `loginNameCount`
            FROM `'.$tbl_user.'`
            WHERE `username` =  "'.$form_userName.'"
              AND `user_id`  <> "'.$_uid.'"';

    list($result) = claro_sql_query_fetch_all($sql);

    if ($result['loginNameCount'] > 0)
    {
        $userNameOk    = false;
        $messageList[] = $langUserTaken;
    }
    else
    {
        $userNameOk = true;
    }

    $sql_ActualUserInfo = 'SELECT `username`   `userName`,
                                  `nom`        `lastName`,
                                  `prenom`     `firstName`,
                                  `pictureUri` `actual_ImageFile`
                          FROM `'.$tbl_user.'`
                          WHERE `user_id` = "'.$_uid.'"';

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
        unset($new_password);
    }
    else
    {
        $passwordOK    = true;
        $new_password  = $form_password2 ;
    }
    unset($form_password1);
    unset($form_password2);

    /*
     * CHECK PASSWORD AREN'T TOO EASY
     */

    if ( $new_password && SECURE_PASSWORD_REQUIRED && $passwordOK)
    {
        if (is_password_secure_enough($new_password,
                                      array($form_userName, $form_officalCode, 
                                            $form_lastName, $form_firstName, $form_email)))
        {
            $passwordOK = true;
        }
        else
        {
            $passwordOK    = false;
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
            $fileExtension = strtolower( array_pop( explode(".",$_FILES['form_picture']['name']) ) );

            if ( in_array($fileExtension, array('php', 'php4', 'php3', 'phtml') ) )
            {
                trigger_error('<div align="center">No PHP Files allowed</div>',E_USER_ERROR);
            }

            claro_mkdir($userImageRepositorySys, 0777, true);

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
                                       or trigger_error('<div align="center">can not open image</div>',E_USER_ERROR);

                        // Create a new image set with new size

                        $finalImage   = ImageCreate($finalWidth, $finalHeight)
                                        or trigger_error('<div align="center">can not create image</div>',E_USER_ERROR);


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
                            or trigger_error('<div align="center">can not resize image</div>',E_USER_ERROR);

                        // Store the final image

                        ImageJPEG($finalImage, $userImageRepositorySys.$picture_FileName)
                            or trigger_error('<div align="center">can not save image</div>',E_USER_ERROR);

                        $picture = $picture_FileName;

                    }            // end if type == JPEG
                }                // end if GD extension loaded
            }                     // end if move_uploaded file
        }                        // end if is_uploaded_file $form_picture


        $sql = 'UPDATE  `'.$tbl_user.'`

                SET `nom`         = "'.$form_lastName.'",
                    `prenom`      = "'.$form_firstName.'",
                    `username`    = "'.$form_userName.'",
                    `phoneNumber` = "'.$form_phone.'",
                    `creatorId`   = "'.$_uid.'",
                    `email`       = "'.$form_email.'" ';

        if ($form_officialCode) $sql .= ', officialCode   = "'.$form_officialCode.'" ';

        if ($new_password) 
        {
            $new_password = ($userPasswordCrypted?md5(trim($new_password)):trim($new_password)) ;
            $sql .= ', `password`   = "'.$new_password.'" ';
        }
        
        if ($form_picture||$form_del_picture)
        {
            if ($form_del_picture=="yes")
            {
                $sql .= ', `pictureUri` = NULL ';
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
                $sql .= ', `pictureUri` = "'.$picture.'"';
            }
        }

        $sql .= ' WHERE `user_id`  = "'.$_uid.'"';

        claro_sql_query($sql);

        /*
         * re-init the system to take new settings in account
         */

        $uidReset = true;
        include('../inc/claro_init_local.inc.php');
        $messageList[] = $langProfileReg."<br>\n"
                        ."<a href=\"../../index.php\">".$langHome."</a>";

    } // end if $userSettingChangeAllowed

}    // end iF applyChange


//////////////////////////////////////////////////////////////////////////////


$sql = 'SELECT 
			`nom`          `lastname` , 
			`prenom`       `firstname`, 
			`username`                , 
			`email`                   , 
			`pictureUri`              , 
			`officialCode`            , 
			`phoneNumber`  
        FROM  `'.$tbl_user.'`
        WHERE 
			`user_id` = "'.$_uid.'"';

$result = claro_sql_query($sql);

if ($result)
{
    $myrow = mysql_fetch_array($result);

    $form_lastName     = $myrow['lastname'    ];
    $form_firstName    = $myrow['firstname'   ];
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

switch($display)
{
	case DISP_REVOQUATION : 
	if (isset($can_request_revoquation) && $can_request_revoquation)
	{
?>
	<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
		<label for="explanation"><?php echo $langComment ?></label><br>
		<textarea cols="60" rows="6" name="explanation" id="explanation"></textarea><br>
		<fieldset>
		<legend ><?php echo $langConfirmation ?></legend>
		<?php echo $langUserName ?><br>
		<input type="text" name="loginToDelete" ><br>
        <?php echo $langPassword ?><br>
		<input type="password" name="passwordToDelete" ><br>
		</fieldset><br>
		<input type="hidden" name="exRevoquation" value="1">
		<input type="submit" value="<?php  echo $langDeleteMyAccount ?>">
	</form>
<?php 
	}
	break;
	case DISP_COURSE_CREATOR_STATUS_REQ : 
		if (isset($can_request_course_creator_status) && $can_request_course_creator_status )
		{
		?>
<p>
<?php echo $langFillTheAreaToExplainTheMotivations ?>
</p>
	<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
    <input type="hidden" name="exCCstatus" value="1">
    <table>
    <tr valign="top">
    <td><label for="explanation"><?php echo $langComment ?> : </label></td>
    <td><textarea cols="60" rows="6" name="explanation" id="explanation"></textarea></td>
    </tr>
    <tr valign="top">
    <td><?php echo $langSubmit ?> : </td>
	<td>
    <input type="submit" value="<?php echo $langOk ?>">
    <?php claro_disp_button($PHP_SELF, $langCancel); ?>
    </td>
    </table>
	</form>
<?php 
	}

	break;
	default  : 


?>
<p>
<form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>" enctype="multipart/form-data">
<?php
if( $disp_picture != '')
{
?>
<img align="right" alt="<?php echo $form_lastName." ".$form_firstName ?>"
     src="<?php echo $imgRepositoryWeb."users/".$disp_picture ?>"
     border="0" hspace="5" vspace="5">
<?php
}
?>
<table>
    <tr>
        <td align="right">
            <label for="form_lastName" >
				<?php echo $langLastname ?>
				
			</label> : </td>
            <td valign="middle">
				<input type="text" size="40" id="form_lastName" name="form_lastName" value="<?php echo $form_lastName ?>" >
            </td>
    </tr>
    <tr>
        <td  align="right">
            <label for="form_firstName">
                <?php echo $langFirstname ?>
            </label> : 
        </td>
        <td >
            <input type="text" size="40" name="form_firstName" id="form_firstName" value="<?php echo $form_firstName ?>" >
        </td>
    </tr>
<?php
if (CONFVAL_ASK_FOR_OFFICIAL_CODE)
{
?>
    <tr>
        <td align="right">
            <label for="form_officialCode">
                <?php echo $langOfficialCode ?>

            </label> : 
        </td>
        <td>
            <input type="text" size="40" name="form_officialCode" id="form_officialCode" value="<?php echo $form_officialCode ?>">
        </td>
    </tr>
<?php
}
?>
    <tr>
        <td >
        </td>
        <td >
           <br>
        </td>
    </tr>
<!-- 
    <tr>
        <td align="right">
            <label for="form_picture">
                <?php echo ($disp_picture?$langUpdateImage:$langAddImage)?>  : "
            <br>
            <small>
                (.jpg or .jpeg only)
                </br>
            </small>
            </label>
        </td>
        <td>
            <input type="file" name="form_picture" id="form_picture" >
            <?php 
            if ( $disp_picture)
            { ?>
            <br>
            <label for="form_del_picture">
                <?php echo $langDelImage ?>
            </label>
            <input type="checkbox" name="form_del_picture" id="form_del_picture" value="yes"> : 
            <?php 
            }
            ?>
        </td>
    <tr>
-->
    <tr>
        <td></td>
        <td>
            <small>
                <?php echo $langEnter2passToChange ?>
            </small>
        </td>
    </tr>
    <tr>
        <td  align="right">
            <label for="form_userName">
                <?php echo $langUserName ?>
            </label> : 
        </td>
        <td>
            <input type="text" size="40" name="form_userName" id="form_userName" value="<?php echo $form_userName?>">
        </td>
    </tr>
    <tr>
        <td  align="right">
            <label for="form_password1">
                <?php echo $langPassword ?>
            </label> : 
        </td>
        <td>
            <input type="password" size="40" name="form_password1" id="form_password1" value="">
        </td>
    </tr>
    <tr>
        <td align="right">
            <label for="form_password2">
                <?php echo $langConfirmation ?>
            </label> : 
        </td>
        <td>
            <input type="password" size="40" name="form_password2" id="form_password2" value="">
        </td>
    </tr>
    <tr>
        <td >
        </td>
        <td >
            <br>
        </td>
    </tr>
    <tr>
        <td align="right">
            <label for="form_email">
                <?php echo $langEmail ?>
            </label> :
        </td>
        <td >
            <input type="text" size="40" name="form_email" id="form_email" value="<?php echo $form_email ?>">
        </td>
    </tr>
    <tr>
        <td align="right">
            <label for="form_phone">
                <?php echo $langPhone ?>
            </label> : 
        </td>
        <td >
            <input type="text" size="40" name="form_phone" id="form_phone" value="<?php echo $form_phone ?>">
        </td>
    </tr>
    <tr>
         <td align="right">
            <label for="applyChange">
                <?php echo $langSaveChanges?> : 
            </label>
         </td>
         <td>
            <input type="submit" name="applyChange" id="applyChange" value="<?php echo $langOk?>" > 
            <?php claro_disp_button($_SERVER['HTTP_REFERER'], $langCancel); ?>
        </td>
    </tr>
</table>
</form>
</p>

<p><a class="claroCmd" href="../tracking/personnalLog.php"><img src="<?php echo $clarolineRepositoryWeb ?>/img/statistics.gif"> <?php echo $langMyStats ?></a>
<?php 
	if (isset($can_request_course_creator_status) && $can_request_course_creator_status )
	{
?>
    | <SPAN> <a class="claroCmd" href="<?php echo $_SERVER['PHP_SELF'] ?>?reqCCstatus=1"><?php echo $langRequestOfCourseCreatorStatus ?></a> </SPAN>
<?php 
	}
?>
<?php 
	if (isset($can_request_revoquation) && $can_request_revoquation)
	{
?>
    | <SPAN> <a href="<?php echo $_SERVER['PHP_SELF'] ?>?reqRevoquation=1"><?php echo $langDeleteMyAccount ?></a> </SPAN>
<?php 
	}
	
	break;
}
/*
 * Data Form
 */
?>
</p>
<?php
include($includePath."/claro_init_footer.inc.php");

/**
 * claro_get_uid_of_platform_admin()
 * 
 * @return list of users
 **/
function claro_get_uid_of_platform_admin()
{
	$tbl_mdb_names = claro_sql_get_main_tbl();
	$sql = 'SELECT * from `'.$tbl_mdb_names['admin'].'`';
	$adminUidList =	claro_sql_query_fetch_all($sql);
	return $adminUidList;
}

?>
