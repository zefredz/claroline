<?php // $Id$
/**
 * CLAROLINE
 *
 * @version 1.9 $Revision$
 *
 * @copyright (c) 2001-2007 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @package ADMIN
 *
 * @author Guillaume Lederer <lederer@claroline.net>
 * @author claro team <cvs@claroline.net>
 */

$cidReset = TRUE;$gidReset = TRUE;$tidReset = TRUE;

require '../inc/claro_init_global.inc.php';

// Security check
if ( ! claro_is_user_authenticated() ) claro_disp_auth_form();
if ( ! claro_is_platform_admin() ) claro_die(get_lang('Not allowed'));

// Include configuration
include claro_get_conf_repository() . 'user_profile.conf.php';

// Include libraries
require_once get_path('incRepositorySys') . '/lib/user.lib.php';


// Initialise variables
$nameTools = get_lang('User settings');
$error = false;
$messageList = array();

/*=====================================================================
  Main Section
 =====================================================================*/

// see which user we are working with ...

if ( empty($_REQUEST['uidToEdit']) ) claro_redirect('adminusers.php');
else                                 $userId = $_REQUEST['uidToEdit'];

$user_data = user_get_properties($userId);

$user_extra_data = user_get_extra_data($userId);
$dgExtra =null;
if (count($user_extra_data))
{
    $dgExtra = new claro_datagrid(user_get_extra_data($userId));
}


if ( isset($_REQUEST['applyChange']) )  //for formular modification
{
    // get params form the form
    if ( isset($_REQUEST['lastname']) )       $user_data['lastname'] = trim($_REQUEST['lastname']);
    if ( isset($_REQUEST['firstname']) )      $user_data['firstname'] = trim($_REQUEST['firstname']);
    if ( isset($_REQUEST['officialCode']) )   $user_data['officialCode'] = trim($_REQUEST['officialCode']);
    if ( isset($_REQUEST['username']) )       $user_data['username'] = trim($_REQUEST['username' ]);
    if ( isset($_REQUEST['password']) )       $user_data['password'] = trim($_REQUEST['password']);
    if ( isset($_REQUEST['password_conf']) )  $user_data['password_conf'] = trim($_REQUEST['password_conf']);
    if ( isset($_REQUEST['email']) )          $user_data['email'] = trim($_REQUEST['email']);
    if ( isset($_REQUEST['officialEmail']) )  $user_data['officialEmail'] = trim($_REQUEST['officialEmail']);
    if ( isset($_REQUEST['phone']) )          $user_data['phone'] = trim($_REQUEST['phone']);
    if ( isset($_REQUEST['language']) )       $user_data['language'] = trim($_REQUEST['language']);
    if ( isset($_REQUEST['isCourseCreator'])) $user_data['isCourseCreator'] = (int) $_REQUEST['isCourseCreator'];
    if ( isset($_REQUEST['is_admin']) )       $user_data['is_admin'] = (bool) $_REQUEST['is_admin'];

    // validate forum params

    $messageList = user_validate_form_profile($user_data, $userId);

    if ( count($messageList) == 0 )
    {
        if ( empty($user_data['password'])) unset($user_data['password']);

        user_set_properties($userId, $user_data);  // if no error update use setting

        if ( $userId == claro_get_current_user_id()  )// re-init system to take new settings in account
        {
            $uidReset = true;
            include get_path('incRepositorySys') . '/claro_init_local.inc.php';
        }

        $classMsg = 'success';
        $dialogBox = get_lang('Changes have been applied to the user settings');

        // set user admin parameter
        if ( $user_data['is_admin'] ) user_set_platform_admin(true, $userId);
        else                          user_set_platform_admin(false, $userId);

        $messageList[] = get_lang('Changes have been applied to the user settings');
    }
    else // user validate form return error messages
    {
        $error = true;
    }

} // if apply changes


/**
 * PREPARE DISPLAY
 */

$interbredcrump[]= array ('url' => get_path('rootAdminWeb'), 'name' => get_lang('Administration'));

if( isset($_REQUEST['cfrom']) && $_REQUEST['cfrom'] == 'ulist')
{
    $interbredcrump[]= array ('url' => get_path('rootAdminWeb') . 'adminusers.php', 'name' => get_lang('User list'));
}

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
.             '<img src="' . get_path('imgRepositoryWeb') . 'enroll.gif" />'
.             get_lang('Enrol to a new course')
.             '</a>'

;

$cmd_menu[] = '<a class="claroCmd" href="../auth/lostPassword.php'
.             '?Femail=' . urlencode($user_data['email'])
.             '&amp;searchPassword=1" >'
.             '<img src="' . get_path('imgRepositoryWeb') . 'email.gif" />'
.             get_lang('Send account information to user by email')
.             '</a>'
;

$cmd_menu[] = '<a class="claroCmd" href="adminuserdeleted.php'
.             '?uidToEdit=' . $userId
.             '&amp;cmd=delete" '
.             'onclick="return confirmation(\'' . clean_str_for_javascript(get_lang('Are you sure to delete') . ' ' . $user_data['username']) . '\');" >'
.             '<img src="' . get_path('imgRepositoryWeb') . 'deluser.gif" /> '
.             get_lang('Delete user')
.             '</a>'

;

$cmd_menu[] = '<a class="claroCmd" href="../messaging/sendmessage.php?cmd=rqMessageToUser&amp;userId='.$userId.'">'.get_lang('Send a message to the user').'</a>';

if (isset($_REQUEST['cfrom']) && $_REQUEST['cfrom'] == 'ulist' ) // if we come form user list, we must display go back to list
{
    $cmd_menu[] = '<a class="claroCmd" href="adminusers.php" >' . get_lang('Back to user list') . '</a>';
}

/**
 * DISPLAY
 */

// Disdplay header
include get_path('incRepositorySys') . '/claro_init_header.inc.php';

// Display tool title
echo claro_html_tool_title($nameTools)
.    claro_html_msg_list($messageList)

// Display "form and info" about the user
.    '<p>'
.    claro_html_menu_horizontal($cmd_menu)
.    '</p>'
.    user_html_form_admin_user_profile($user_data)
;
if (!is_null($dgExtra)) echo $dgExtra->render();

include get_path('incRepositorySys') . '/claro_init_footer.inc.php';

function user_get_extra_data($userId)
{
    $extraInfo = array();
    $extraInfoDefList = get_userInfoExtraDefinitionList();
    $userInfo = get_user_property_list($userId);

/**
    $extraInfo['user_id']['label'] = get_lang('User id');
    $extraInfo['user_id']['value'] = $userId;
*/

    foreach ($extraInfoDefList as $extraInfoDef)
    {
        $currentValue = array_key_exists($extraInfoDef['propertyId'],$userInfo)
            ? $userInfo[$extraInfoDef['propertyId']]
            : $extraInfoDef['defaultValue'];

            // propertyId, label, type, defaultValue, required
            $extraInfo[$extraInfoDef['propertyId']]['label'] = $extraInfoDef['label'];
            $extraInfo[$extraInfoDef['propertyId']]['value'] = $currentValue;

    }
    return $extraInfo;
}
?>