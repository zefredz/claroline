<?php // $Id$
/**
 * CLAROLINE
 *
 * This script prupose to user to edit his own profile
 *
 * @version 1.9 $Revision$
 *
 * @copyright   (c) 2001-2011, Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @see http://www.claroline.net/wiki/Auth/
 *
 * @author Claro Team <cvs@claroline.net>
 * @copyright   (c) 2001-2011, Universite catholique de Louvain (UCL)
 *
 * @package Auth
 *
 */

/*=====================================================================
Init Section
=====================================================================*/

$cidReset = TRUE;
$gidReset = TRUE;
$uidRequired = TRUE;

require '../inc/claro_init_global.inc.php';

if( ! claro_is_user_authenticated() ) claro_disp_auth_form();

$dialogBox = new DialogBox();
$display = '';
$error = false;

// include configuration files
include claro_get_conf_repository() . 'user_profile.conf.php'; // find this file to modify values.

// include library files
include_once get_path('incRepositorySys') . '/lib/user.lib.php';
include_once get_path('incRepositorySys') . '/lib/sendmail.lib.php';
include_once get_path('incRepositorySys') . '/lib/fileManage.lib.php';
include_once get_path('incRepositorySys') . '/lib/fileUpload.lib.php';
include_once get_path('incRepositorySys') . '/lib/image.lib.php';
include_once get_path('incRepositorySys') . '/lib/display/dialogBox.lib.php';

$nameTools = get_lang('My user account');

// define display
define('DISP_PROFILE_FORM',__LINE__);
define('DISP_MOREINFO_FORM',__LINE__);
define('DISP_REQUEST_COURSE_CREATOR_STATUS',__LINE__);
define('DISP_REQUEST_REVOQUATION',__LINE__);

$display = DISP_PROFILE_FORM;

/*=====================================================================
CONTROLER Section
=====================================================================*/

$extraInfoDefList = get_userInfoExtraDefinitionList();


$user_data = user_initialise();
$user_data = user_get_properties(claro_get_current_user_id());

$acceptedCmdList = array( 'exCCstatus'
                        , 'exRevoquation'
                        , 'reqCCstatus'
                        , 'reqRevoquation'
                        , 'editExtraInfo'
                        , 'exMoreInfo'
                        );

if ( isset($_REQUEST['cmd']) && in_array($_REQUEST['cmd'],$acceptedCmdList) )
{
    $cmd = $_REQUEST['cmd'];
}
else
{
    $cmd = '';
}

if ( isset($_REQUEST['applyChange']) )
{
    $profile_editable = get_conf('profile_editable');

    // get params form the form
    if ( isset($_REQUEST['lastname']) && in_array('name',$profile_editable) )              $user_data['lastname'] = trim($_REQUEST['lastname']);
    if ( isset($_REQUEST['firstname']) && in_array('name',$profile_editable) )             $user_data['firstname'] = trim($_REQUEST['firstname']);
    if ( isset($_REQUEST['officialCode']) && in_array('official_code',$profile_editable) ) $user_data['officialCode'] = trim($_REQUEST['officialCode']);
    if ( isset($_REQUEST['username']) && in_array('login',$profile_editable) )             $user_data['username'] = trim($_REQUEST['username' ]);
    if ( isset($_REQUEST['old_password']) && in_array('password',$profile_editable) )      $user_data['old_password'] = trim($_REQUEST['old_password']);
    if ( isset($_REQUEST['password']) && in_array('password',$profile_editable) )          $user_data['password'] = trim($_REQUEST['password']);
    if ( isset($_REQUEST['password_conf']) && in_array('password',$profile_editable) )     $user_data['password_conf'] = trim($_REQUEST['password_conf']);
    if ( isset($_REQUEST['email']) && in_array('email',$profile_editable) )                $user_data['email'] = trim($_REQUEST['email']);
    if ( isset($_REQUEST['officialEmail']) && in_array('email',$profile_editable) )        $user_data['officialEmail'] = trim($_REQUEST['officialEmail']);
    if ( isset($_REQUEST['phone']) && in_array('phone',$profile_editable) )                $user_data['phone'] = trim($_REQUEST['phone']);
    if ( isset($_REQUEST['language']) && in_array('language',$profile_editable) )          $user_data['language'] = trim($_REQUEST['language']);
    
    
    if ( isset($_REQUEST['delPicture']) && $_REQUEST['delPicture'] =='true' )
    {
        $picturePath = user_get_picture_path( $user_data );
        
        if ( $picturePath )
        {
            claro_delete_file( $picturePath );
            $user_data['picture'] = '';
            $dialogBox->success(get_lang("User picture deleted"));
        }
        else
        {
            $dialogBox->error(get_lang("Cannot delete user picture"));
        }
    }
    
    // Handle user picture
    
    if ( isset($_FILES['picture']['name'])
        && $_FILES['picture']['size'] > 0 )
    {
        $fileName = $_FILES['picture']['name'];
        $fileTmpName = $_FILES['picture']['tmp_name'];
        
        if ( is_uploaded_file( $fileTmpName ) )
        {
            if ( is_image( $fileName ) )
            {
                list($width, $height, $type, $attr) = getimagesize($fileTmpName);
                
                if ( $width > 0 && $width <= get_conf( 'maxUserPictureWidth', 150 )
                    && $height > 0 && $height <= get_conf( 'maxUserPictureHeight', 200 )
                    && $_FILES['picture']['size'] <= get_conf( 'maxUserPictureSize', 100*1024 )
                )
                {
                    $uploadDir = user_get_private_folder_path($user_data['user_id']);
                    
                    if ( ! file_exists( $uploadDir ) )
                    {
                        claro_mkdir( $uploadDir, CLARO_FILE_PERMISSIONS, true );
                    }
                    
                    if ( false !== ( $pictureName = treat_uploaded_file(
                            $_FILES['picture'],
                            $uploadDir,
                            '',
                            1000000000000 ) ) )
                    {
                        // Update Database
                        $user_data['picture'] = $pictureName;
                        $dialogBox->success(get_lang("User picture added"));
                    }
                    else
                    {
                        // Handle Error
                        $dialogBox->error(get_lang("Cannot upload file"));
                    }
                }
                else
                {
                    // Handle error
                    $dialogBox->error(
                        get_lang("Image is too big : max size %width%x%height%, %size% bytes"
                            , array(
                                    '%width%' => get_conf( 'maxUserPictureWidth', 150 ),
                                    '%height%' => get_conf( 'maxUserPictureHeight', 200 ),
                                    '%size%' => get_conf( 'maxUserPictureHeight', 100*1024 )
                                ) ) );
                }
            }
            else
            {
                // Handle error
                $dialogBox->error(get_lang("Invalid file format, use gif, jpg or png"));
            }
        }
        else
        {
            // Handle error
            $dialogBox->error(get_lang('Upload failed'));
        }
    }

    // manage password.

    if (empty($user_data['password']) && empty($user_data['password_conf']))
    {
        unset ($user_data['password']);
        unset ($user_data['password_conf']);
    }

    // validate forum params

    $errorMsgList = user_validate_form_profile($user_data, claro_get_current_user_id());

    if ( count($errorMsgList) == 0 )
    {
        // if no error update use setting
        user_set_properties(claro_get_current_user_id(), $user_data);
        $claroline->log('PROFILE_UPDATE', array('user'=>claro_get_current_user_id()));

        // re-init the system to take new settings in account

        $uidReset = true;
        include dirname(__FILE__) . '/../inc/claro_init_local.inc.php';
        $dialogBox->success( get_lang('The information have been modified') );

        // Initialise
        $user_data = user_get_properties(claro_get_current_user_id());

    } // end if $userSettingChangeAllowed
    else
    {
        // user validate form return error messages
        foreach( $errorMsgList as $errorMsg )
        {
            $dialogBox->error($errorMsg);
        }
        $error = true;
    }

}
elseif ( ! claro_is_allowed_to_create_course()
    && get_conf('can_request_course_creator_status')
    && 'exCCstatus' == $cmd )
{
    // send a request for course creator status
    profile_send_request_course_creator_status($_REQUEST['explanation']);
    $dialogBox->success( get_lang('Your request to become a course creator has been sent to platform administrator(s).') );
}
elseif ( get_conf('can_request_revoquation')
    && 'exRevoquation' == $cmd )
{
    // send a request for revoquation
    if (profile_send_request_revoquation($_REQUEST['explanation'], $_REQUEST['loginToDelete'],$_REQUEST['passwordToDelete']))
    {
        $dialogBox->success( get_lang('Your request to remove your account has been sent') );
    }
    else
    {
        switch (claro_failure::get_last_failure())
        {
            case 'EXPLANATION_EMPTY' :
                $dialogBox->error( get_lang('You left some required fields empty') );
                $noQUERY_STRING = TRUE;
                ClaroBreadCrumbs::getInstance()->prepend( $nameTools, $_SERVER['PHP_SELF'] );
                $nameTools = get_lang('Request to remove this account');
                $display = DISP_REQUEST_REVOQUATION;
            break;
    
        }
    }
}
elseif (  !claro_is_allowed_to_create_course()
    && get_conf('can_request_course_creator_status')
    && 'reqCCstatus' == $cmd )
{
    // display course creator status form
    $noQUERY_STRING = TRUE;
    $display = DISP_REQUEST_COURSE_CREATOR_STATUS;
    ClaroBreadCrumbs::getInstance()->prepend( $nameTools, $_SERVER['PHP_SELF'] );
    $nameTools = get_lang('Request course creation status');
}
elseif ( get_conf('can_request_revoquation')
    && 'reqRevoquation' == $cmd )
{
    // display revoquation form
    $noQUERY_STRING = TRUE;
    ClaroBreadCrumbs::getInstance()->prepend( $nameTools, $_SERVER['PHP_SELF'] );
    $nameTools = get_lang('Request to remove this account');
    $display = DISP_REQUEST_REVOQUATION;
}
elseif ( 'editExtraInfo' == $cmd
    && 0 < count($extraInfoDefList) )
{
    // display revoquation form
    $noQUERY_STRING = TRUE;
    $display = DISP_MOREINFO_FORM;
    ClaroBreadCrumbs::getInstance()->prepend( $nameTools, $_SERVER['PHP_SELF'] );
    $nameTools = get_lang('Complementary fields');
    $userInfo = get_user_property_list(claro_get_current_user_id());

}
elseif ( 'exMoreInfo' == $cmd
    && 0 < count($extraInfoDefList)  )
{
    if (array_key_exists('extraInfoList',$_REQUEST))
    {
        foreach( $_REQUEST['extraInfoList'] as $extraInfoName=> $extraInfoValue)
        {
            set_user_property(claro_get_current_user_id(),$extraInfoName,$extraInfoValue,'userExtraInfo');
        }
    }
}

// Initialise
$user_data['userExtraInfoList'] =  get_user_property_list(claro_get_current_user_id());

$profileMenu =  array();

switch ( $display )
{
    case DISP_PROFILE_FORM :
        // display user tracking link
        $profileText = claro_text_zone::get_content('textzone_edit_profile_form');
        
        if( get_conf('is_trackingEnabled') )
        {
            // display user tracking link
            $profileMenu[] = '<a class="claroCmd" href="' . get_conf('urlAppend') . '/claroline/tracking/userReport.php?userId='.claro_get_current_user_id() . claro_url_relay_context('&amp;') . '">'
            .                 '<img src="' . get_icon_url('statistics') . '" alt="" />&nbsp;' . get_lang('View my statistics')
            .                 '</a>'
            ;
        }
        // display request course creator status
        if ( ! claro_is_allowed_to_create_course() && get_conf('can_request_course_creator_status') )
        {
            $profileMenu[] = claro_html_cmd_link($_SERVER['PHP_SELF'] . '?cmd=reqCCstatus' . claro_url_relay_context('&amp;')
                                                , get_lang('Request course creation status') );
        }
        // display user revoquation
        if ( get_conf('can_request_revoquation') )
        {
            $profileMenu[] =  claro_html_cmd_link( $_SERVER['PHP_SELF']
                                                 . '?cmd=reqRevoquation' . claro_url_relay_context('&amp;')
                                                 , get_lang('Delete my account')
                                                 ) ;
        }
        break;
}

/**********************************************************************
    View Section
**********************************************************************/
$jsloader = JavascriptLoader::getInstance();
$jsloader->load('jquery');

$htmlHeadXtra[] =
'<script type="text/javascript">
    $(document).ready(
        function() {
            $("#password").val("");
        }
    );
</script>';

$out = '';

$out .= claro_html_tool_title($nameTools);

$out .= $dialogBox->render();

switch ( $display )
{
    case DISP_PROFILE_FORM :

        // display form profile
        if ( trim ($profileText) != '')
        {
            $out .= '<div class="info profileEdit">'
            .    $profileText
            .    '</div>'
            ;
        }

        $out .= '<p>'
        .    claro_html_menu_horizontal($profileMenu)
        .    '</p>'
        .    user_html_form_profile($user_data)
        ;

        break;

    case DISP_MOREINFO_FORM :

        // display request course creator form
        $out .= '<form action="' . $_SERVER['PHP_SELF'] . '" method="post">' . "\n"
        .    '<input type="hidden" name="cmd" value="exMoreInfo" />' . "\n"
        .    '<table>' . "\n"
        ;

        foreach ($extraInfoDefList as $extraInfoDef)
        {
            $currentValue = array_key_exists($extraInfoDef['propertyId'],$userInfo)
            ? $userInfo[$extraInfoDef['propertyId']]
            : $extraInfoDef['defaultValue'];
            $requirement = (bool) (TRUE == $extraInfoDef['required']);

            $labelExtraInfoDef = $extraInfoDef['label'];
            $out .= form_input_text('extraInfoList['.htmlentities($extraInfoDef['propertyId']).']',$currentValue,get_lang($labelExtraInfoDef),$requirement);

        }

        $out .= '<tr valign="top">' . "\n"
        .    '<td>' . get_lang('Submit') . ': </td>' . "\n"
        .    '<td>'
        .    '<input type="submit" value="' . get_lang('Ok') . '" />&nbsp; ' . "\n"
        .    claro_html_button($_SERVER['PHP_SELF'], get_lang('Cancel')) . "\n"
        .    '</td>'
        .    '</tr>' . "\n"
        .     form_row('&nbsp;', '<small>' . get_lang('<span class="required">*</span> denotes required field') . '</small>')
        .    '</table>' . "\n"
        .    '</form>' . "\n"
        ;
        break;

    case DISP_REQUEST_COURSE_CREATOR_STATUS :


        $out .= '<p>' . get_lang('Fill in the text area to motivate your request and then submit the form to send it to platform administrators') . '</p>';

        // display request course creator form
        $out .= '<form action="' . $_SERVER['PHP_SELF'] . '" method="post">' . "\n"
        .    '<input type="hidden" name="cmd" value="exCCstatus" />' . "\n"
        .    '<table>' . "\n"
        .    form_input_textarea('explanation','',get_lang('Comment'),true,6)
        .    '<tr valign="top">' . "\n"
        .    '<td>' . get_lang('Submit') . ': </td>' . "\n"
        .    '<td><input type="submit" value="' . get_lang('Ok') . '" />&nbsp; ' . "\n"
        .    claro_html_button($_SERVER['PHP_SELF'], get_lang('Cancel')) . "\n"
        .    '</td></tr>' . "\n"
        .    '</table>' . "\n"
        .    '</form>' . "\n"
        ;

        break;

    case DISP_REQUEST_REVOQUATION :
        if ( get_conf('can_request_revoquation') )
        {

            $out .= '<form action="' . $_SERVER['PHP_SELF'] . '" method="post">' . "\n"
            .    '<input type="hidden" name="cmd" value="exRevoquation" />' . "\n"
            .    '<table>' . "\n"
            .    form_input_text('loginToDelete','',get_lang('Username'),true)
            .    form_input_password('passwordToDelete','',get_lang('Password'),true)
            .    form_input_textarea('explanation','',get_lang('Comment'),true,6)
            .    '<tr valign="top">' . "\n"
            .    '<td>' . get_lang('Delete my account') . ': </td>' . "\n"
            .    '<td>'
            .    '<input type="submit" value="' . get_lang('Ok') . '" />&nbsp; ' . "\n"
            .    claro_html_button($_SERVER['PHP_SELF'], get_lang('Cancel')) . "\n"
            .    '</td></tr>' . "\n"
            .    '</table>' . "\n"
            .    '</form>' . "\n"
            ;
        }
        break;

} // end switch display

$claroline->display->body->appendContent($out);

echo $claroline->display->render();

?>