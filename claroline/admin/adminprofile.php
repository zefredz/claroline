<?php // $Id$

/**
 * CLAROLINE
 *
 * Management tools for users' profiles.
 *
 * @version     $Revision$
 * @copyright   (c) 2001-2011, Universite catholique de Louvain (UCL)
 * @license     http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @package     ADMIN
 * @author      Guillaume Lederer <lederer@claroline.net>
 * @author      claro team <cvs@claroline.net>
 */

$cidReset = true;
$gidReset = true;
$tidReset = true;

define( 'CSRF_PROTECTED', true );

require '../inc/claro_init_global.inc.php';

// Security check
if ( ! claro_is_user_authenticated() ) claro_disp_auth_form();
if ( ! claro_is_platform_admin() ) claro_die(get_lang('Not allowed'));

// Include configuration
include claro_get_conf_repository() . 'user_profile.conf.php';

// Include libraries
require_once get_path('incRepositorySys') . '/lib/user.lib.php';
require_once get_path('incRepositorySys') . '/lib/file.lib.php';
require_once get_path('incRepositorySys') . '/lib/image.lib.php';
require_once get_path('incRepositorySys') . '/lib/fileUpload.lib.php';
require_once get_path('incRepositorySys') . '/lib/fileManage.lib.php';
require_once get_path('incRepositorySys') . '/lib/display/dialogBox.lib.php';

// Initialise variables
$nameTools = get_lang('User settings');
$dialogBox = new DialogBox;

/*=====================================================================
  Main Section
 =====================================================================*/

// see which user we are working with ...

if ( empty($_REQUEST['uidToEdit']) ) claro_redirect('adminusers.php');
else                                 $userId = $_REQUEST['uidToEdit'];

$user_data = user_get_properties($userId);

if ( empty($user_data) )
{
    claro_die(get_lang('Unable to load user information'));
}

$user_extra_data = user_get_extra_data($userId);

if (count($user_extra_data))
{
    $dgExtra = new claro_datagrid(user_get_extra_data($userId));
}
else
{
    $dgExtra = null;
}

if ( isset($_REQUEST['applyChange']) )  //for formular modification
{
    // get params form the form
    if ( isset($_POST['lastname']) )       $user_data['lastname'] = trim($_POST['lastname']);
    if ( isset($_POST['firstname']) )      $user_data['firstname'] = trim($_POST['firstname']);
    if ( isset($_POST['officialCode']) )   $user_data['officialCode'] = trim($_POST['officialCode']);
    if ( isset($_POST['username']) )       $user_data['username'] = trim($_POST['username' ]);
    if ( isset($_POST['password']) )       $user_data['password'] = trim($_POST['password']);
    if ( isset($_POST['password_conf']) )  $user_data['password_conf'] = trim($_POST['password_conf']);
    if ( isset($_POST['email']) )          $user_data['email'] = trim($_POST['email']);
    if ( isset($_POST['officialEmail']) )  $user_data['officialEmail'] = trim($_POST['officialEmail']);
    if ( isset($_POST['phone']) )          $user_data['phone'] = trim($_POST['phone']);
    if ( isset($_POST['language']) )       $user_data['language'] = trim($_POST['language']);
    if ( isset($_POST['isCourseCreator'])) $user_data['isCourseCreator'] = (int) $_POST['isCourseCreator'];
    if ( isset($_POST['is_admin']) )       $user_data['is_admin'] = (bool) $_POST['is_admin'];
    
    if ( isset($_POST['delPicture']) && $_POST['delPicture'] =='true' )
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
                $dialBox->error(get_lang("Invalid file format, use gif, jpg or png"));
            }
        }
        else
        {
            // Handle error
            $dialogBox->error(get_lang('Upload failed'));
        }
    }

    // validate forum params

    $messageList = user_validate_form_admin_user_profile($user_data, $userId);

    if ( count($messageList) == 0 )
    {
        if ( empty($user_data['password'])) unset($user_data['password']);

        user_set_properties($userId, $user_data);  // if no error update use setting

        if ( $userId == claro_get_current_user_id()  )// re-init system to take new settings in account
        {
            $uidReset = true;
            include get_path('incRepositorySys') . '/claro_init_local.inc.php';
        }

        //$classMsg = 'success';
        $dialogBox->success( get_lang('Changes have been applied to the user settings') );

        // set user admin parameter
        if ( $user_data['is_admin'] ) user_set_platform_admin(true, $userId);
        else                          user_set_platform_admin(false, $userId);

        //$messageList[] = get_lang('Changes have been applied to the user settings');
    }
    else // user validate form return error messages
    {
        // $error = true;
        $dialogBox->error( get_lang('Changes have not been applied to the user settings') );
        foreach ( $messageList as $message )
        {
            $dialogBox->error( $message );
        }
    }

} // if apply changes


/**
 * PREPARE DISPLAY
 */

// Prepend in reverse order !!!
if( isset($_REQUEST['cfrom']) && $_REQUEST['cfrom'] == 'ulist')
{
    ClaroBreadCrumbs::getInstance()->prepend( get_lang('User list'), get_path('rootAdminWeb') . 'adminusers.php' );
}

ClaroBreadCrumbs::getInstance()->prepend( get_lang('Administration'), get_path('rootAdminWeb') );

$htmlHeadXtra[] =
            "<script>
            function confirmation (name)
            {
                if (confirm(\"".clean_str_for_javascript(get_lang('Are you sure to delete'))." \"+ name + \"? \"))
                    {return true;}
                else
                    {return false;}
            }
            </script>";

$user_data['is_admin'] = user_is_admin($userId);


$cmd_menu[] = '<a class="claroCmd" href="../auth/courses.php'
.             '?cmd=rqReg'
.             '&amp;uidToEdit=' . $userId
.             '&amp;fromAdmin=settings'
.             '&amp;category=" >'
.             '<img src="' . get_icon_url('enroll') . '" />'
.             get_lang('Enrol to a new course')
.             '</a>'

;

$cmd_menu[] = '<a class="claroCmd" href="../auth/lostPassword.php'
.             '?Femail=' . urlencode($user_data['email'])
.             '&amp;searchPassword=1" >'
.             '<img src="' . get_icon_url('mail_close') . '" />'
.             get_lang('Send account information to user by email')
.             '</a>'
;

$cmd_menu[] = '<a class="claroCmd" href="adminuserdeleted.php'
.             '?uidToEdit=' . $userId
.             '&amp;cmd=rqDelete" '
//.             'onclick="return confirmation(\'' . $user_data['username'] . '\');"
.             ' id="delete" >'
.             '<img src="' . get_icon_url('deluser') . '" /> '
.             get_lang('Delete user')
.             '</a>'
;

$cmd_menu[] = '<a class="claroCmd" href="../messaging/sendmessage.php'
.             '?cmd=rqMessageToUser'
.             '&amp;userId='.$userId.'">'
.             get_lang('Send a message to the user')
.             '</a>'
;

if (isset($_REQUEST['cfrom']) && $_REQUEST['cfrom'] == 'ulist' ) // if we come form user list, we must display go back to list
{
    $cmd_menu[] = '<a class="claroCmd" href="adminusers.php" >' . get_lang('Back to user list') . '</a>';
}

/**
 * DISPLAY
 */

$out = '';

// Display tool title
$out .= claro_html_tool_title($nameTools)
.   $dialogBox->render()
// Display "form and info" about the user
.    '<p>'
.    claro_html_menu_horizontal($cmd_menu)
.    '</p>'
.    user_html_form_admin_user_profile($user_data)
;
if (!is_null($dgExtra)) $out .= $dgExtra->render();

$out .=
'<script type="text/javascript">
    $(document).ready(function(){
        $("#delete").click(function(){
            return confirmation("' . $user_data['firstname'] . " " . $user_data['lastname'] .'");
        }).attr("href","adminuserdeleted.php?uidToEdit=' . $userId . '&cmd=exDelete");
    });
</script>';

$claroline->display->body->appendContent($out);

echo $claroline->display->render();