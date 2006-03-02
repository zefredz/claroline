<?php // $Id$
/**
 * CLAROLINE
 * @version 1.8 $Revision$
 *
 * @copyright (c) 2001-2006 Universite catholique de Louvain (UCL)
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
if ( ! $_uid ) claro_disp_auth_form();
if ( ! $is_platformAdmin ) claro_die(get_lang('Not allowed'));

// Include configuration
include $includePath . '/conf/user_profile.conf.php';

// Include libraries
require_once $includePath . '/lib/user.lib.php';


// Initialise variables
$nameTools = get_lang('User settings');
$error = false;
$messageList = array();

/*=====================================================================
  Main Section
 =====================================================================*/

// see which user we are working with ...

if ( empty($_REQUEST['uidToEdit']) ) header('Location: adminusers.php');
else                                 $user_id = $_REQUEST['uidToEdit'];

$user_data = user_initialise();
$user_data['is_admin'] = false;

if ( isset($_REQUEST['applyChange']) )  //for formular modification
{

    // get params form the form
    if ( isset($_REQUEST['lastname']) )      $user_data['lastname'] = trim($_REQUEST['lastname']);
    if ( isset($_REQUEST['firstname']) )     $user_data['firstname'] = trim($_REQUEST['firstname']);
    if ( isset($_REQUEST['officialCode']) )  $user_data['officialCode'] = trim($_REQUEST['officialCode']);
    if ( isset($_REQUEST['username']) )      $user_data['username'] = trim($_REQUEST['username' ]);
    if ( isset($_REQUEST['password']) )      $user_data['password'] = trim($_REQUEST['password']);
    if ( isset($_REQUEST['password_conf']) ) $user_data['password_conf'] = trim($_REQUEST['password_conf']);
    if ( isset($_REQUEST['email']) )         $user_data['email'] = trim($_REQUEST['email']);
    if ( isset($_REQUEST['phone']) )         $user_data['phone'] = trim($_REQUEST['phone']);
    if ( isset($_REQUEST['language']) )      $user_data['language'] = trim($_REQUEST['language']);
    if ( isset($_REQUEST['status']) )        $user_data['status'] = (int) $_REQUEST['status'];

    if ( isset($_REQUEST['is_admin']) )      $user_data['is_admin'] = (bool) $_REQUEST['is_admin'];

    // validate forum params

    $messageList = user_validate_form_profile($user_data, $user_id);

    if ( count($messageList) == 0 )
    {

        // if no error update use setting
        user_update ($user_id, $user_data);

        // re-init the system to take new settings in account
        if ( $user_id == $_uid )
        {
            $uidReset = true;
            include $includePath . '/claro_init_local.inc.php';
        }

        $classMsg = 'success';
        $dialogBox = get_lang('Changes have been applied to the user settings');

        // set user admin parameter
        if ( $user_data['is_admin'] ) user_add_admin($user_id);
        else                          user_delete_admin($user_id);

        $messageList[] = get_lang('Changes have been applied to the user settings');
    }
    // user validate form return error messages
    else $error = true;

} // if apply changes


/**
 * PREPARE DISPLAY
 */

$interbredcrump[]= array ('url' => $rootAdminWeb, 'name' => get_lang('Administration'));

if( isset($_REQUEST['cfrom']) && $_REQUEST['cfrom'] == 'ulist')
{
    $interbredcrump[]= array ('url' => $rootAdminWeb . 'adminusers.php', 'name' => get_lang('User list'));
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

$user_data = user_get_data($user_id);
$user_data['is_admin'] = user_is_admin($user_id);

$cmd_menu[] = '<a class="claroCmd" href="adminuserdeleted.php'
.             '?uidToEdit=' . $user_id
.             '&amp;cmd=delete" '
.             'onClick="return confirmation(\'' . clean_str_for_javascript(get_lang('Are you sure to delete') . ' ' . $user_data['username']) . '\');" >'
.             '<img src="' . $imgRepositoryWeb . 'deluser.gif" /> '
.             get_lang('Delete user')
.             '</a>'

;

$cmd_menu[] = '<a class="claroCmd" href="../auth/courses.php'
.             '?cmd=rqReg'
.             '&amp;uidToEdit=' . $user_id
.             '&amp;fromAdmin=settings'
.             '&amp;category=" >'
.             '<img src="' . $imgRepositoryWeb . 'enroll.gif">'
.             get_lang('Enrol to a new course')
.             '</a>'

;

$cmd_menu[] = '<a class="claroCmd" href="../auth/lostPassword.php'
.             '?Femail=' . urlencode($user_data['email'])
.             '&amp;searchPassword=1" >'
.             '<img src="' . $imgRepositoryWeb . 'email.gif" />'
.             get_lang('Send account information to user by email')
.             '</a>'
;

if ( isset($cfrom) && $cfrom == 'ulist' ) // if we come form user list, we must display go back to list
{
    $cmd_menu[] = '<a class="claroCmd" href="adminusers.php" >' . get_lang('Back to user list') . '</a>';
}



/**
 * DISPLAY
 */

// Disdplay header
include $includePath . '/claro_init_header.inc.php';

// Display tool title
echo claro_disp_tool_title($nameTools);

// Display Forms or dialog box(if needed)
if ( count($messageList) > 0 )
{
    echo claro_html::message_box(implode('<br />', $messageList));
}

// Display "form and info" about the user

user_display_form_admin_user_profile($user_data);

echo claro_html::menu_horizontal($cmd_menu);

include $includePath . '/claro_init_footer.inc.php';

?>
