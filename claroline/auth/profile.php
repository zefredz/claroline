<?php // $Id$
/**
 * CLAROLINE
 *
 * This script prupose to user to edit his own profile
 *
 * @version 1.8 $Revision$
 *
 * @copyright 2001-2006 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @see http://www.claroline.net/wiki/Auth/
 *
 * @author Claro Team <cvs@claroline.net>
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

$messageList = array();
$display = '';
$error = false;

// include configuration files
include $includePath . '/conf/user_profile.conf.php'; // find this file to modify values.

// include library files
include_once $includePath . '/lib/user.lib.php';
include_once $includePath . '/lib/sendmail.lib.php';
include_once $includePath . '/lib/fileManage.lib.php';

$nameTools = get_lang('My User Account');

// DB tables definition
$tbl_mdb_names = claro_sql_get_main_tbl();
$tbl_user      = $tbl_mdb_names['user'];

// define display
define('DISP_PROFILE_FORM',__LINE__);
define('DISP_REQUEST_COURSE_CREATOR_STATUS',__LINE__);
define('DISP_REQUEST_REVOQUATION',__LINE__);

$display = DISP_PROFILE_FORM;

/*=====================================================================
 CONTROLER Section
 =====================================================================*/

$user_data = user_initialise();

if ( isset($_REQUEST['cmd']) ) $cmd = $_REQUEST['cmd'];
else                           $cmd = '';

if ( isset($_REQUEST['applyChange']) )
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

    // validate forum params

    $messageList = user_validate_form_profile($user_data, $_uid);

    if ( count($messageList) == 0 )
    {

        // if no error update use setting
        user_set_properties($_uid, $user_data);
        event_default('PROFILE_UPDATE', array('user'=>$_uid));

        // re-init the system to take new settings in account

        $uidReset = true;
        include '../inc/claro_init_local.inc.php';
        $messageList[] = get_lang('Your new profile has been saved') . '<br />' . "\n";

    } // end if $userSettingChangeAllowed
    else
    {
        // user validate form return error messages
        $error = true;
    }

    // Initialise
    $user_data = user_get_properties($_uid);

}
elseif (    get_conf('can_request_course_creator_status')
         && $cmd == 'exCCstatus' )
{
    // send a request for course creator status
    profile_send_request_course_creator_status($_REQUEST['explanation']);
    $messageList[] = get_lang('Your request to become a course creator has been sent to platform administrator(s).');
}
elseif (    get_conf('can_request_revoquation')
         && $cmd == 'exRevoquation' )
{
    // send a request for revoquation
    profile_send_request_revoquation($_REQUEST['explanation'], $_REQUEST['loginToDelete'],$_REQUEST['passwordToDelete']);
    $messageList[] = get_lang('Your request to remove your account has been sent');
}
elseif (    get_conf('can_request_course_creator_status')
         && $cmd == 'reqCCstatus' )
{
    // display course creator status form
    $noQUERY_STRING = TRUE;
    $display = DISP_REQUEST_COURSE_CREATOR_STATUS;
    $nameTools = get_lang('Request course creation status');
}
elseif ( get_conf('can_request_revoquation')
         && $cmd == 'reqRevoquation' )
{
    // display revoquation form
    $noQUERY_STRING = TRUE;
    $display = DISP_REQUEST_REVOQUATION;
}

// Initialise
$user_data = user_get_properties($_uid);

/**********************************************************************
  View Section
 **********************************************************************/

// display header
include $includePath . '/claro_init_header.inc.php';

echo claro_html_tool_title($nameTools);

if ( count($messageList) > 0 )
{
    echo claro_html_message_box( implode('<br />', $messageList) );
}

switch ( $display )
{
    case DISP_PROFILE_FORM :

        // display form profile

        user_display_form_profile($user_data);

        // display user tracking link
        $profile_menu[] = '<a class="claroCmd" href="' . $urlAppend . '/claroline/tracking/personnalLog.php">'
        .                 '<img src="' . $clarolineRepositoryWeb . '/img/statistics.gif" />' . get_lang('View my statistics')
        .                 '</a>'
        ;

        // display request course creator status
        if ( get_conf('can_request_course_creator_status') )
        {
            $profile_menu[] = '<a class="claroCmd" href="' . $_SERVER['PHP_SELF'] . '?cmd=reqCCstatus">' . get_lang('Request course creation status') . '</a>';
        }

        // display user revoquation
        if ( get_conf('can_request_revoquation') )
        {
            $profile_menu[] = '<a class="claroCmd" href="' . $_SERVER['PHP_SELF'] . '?cmd=reqRevoquation">' . get_lang('Delete my account') . '</a>' ;
        }

        echo claro_html_menu_horizontal($profile_menu);

        break;

    case DISP_REQUEST_COURSE_CREATOR_STATUS :
    {
        if ( get_conf('can_request_course_creator_status') )
        {
            echo '<p>' . get_lang('Fill the area to explain your motivation and submit your request. An e-mail will be sent to platform adminisrator(s).') . '</p>';

            // display request course creator form
            echo '<form action="' . $_SERVER['PHP_SELF'] . '" method="post">'
            .    '<input type="hidden" name="cmd" value="exCCstatus" />'
            .    '<table>'
            .    '<tr valign="top">'
            .    '<td><label for="explanation">' . get_lang('Comment') . ': </label></td>'
            .    '<td><textarea cols="60" rows="6" name="explanation" id="explanation"></textarea></td>'
            .    '</tr>'
            .    '<tr valign="top">'
            .    '<td>' . get_lang('Submit') . ': </td>'
            .    '<td><input type="submit" value="' . get_lang('Ok') . '"> '
            .    claro_html_button($_SERVER['PHP_SELF'], get_lang('Cancel'))
            .    '</td></tr>'
            .    '</table>'
            .    '</form>'
            ;
        }
    }    break;

    case DISP_REQUEST_REVOQUATION :
    {

        if ( get_conf('can_request_revoquation') )
        {

            echo '<form action="' . $_SERVER['PHP_SELF'] . '" method="post">'
            .    '<input type="hidden" name="cmd" value="exRevoquation" />'
            .    '<table>'
            .    '<tr valign="top">'
            .    '<td>' . get_lang('Username') . ': </td>'
            .    '<td><input type="text" name="loginToDelete" ></td>'
            .    '</tr>'
            .    '<tr valign="top">'
            .    '<td>' . get_lang('Password') . ': </td>'
            .    '<td><input type="password" name="passwordToDelete" ></td>'
            .    '</tr>'
            .    '<tr valign="top">'
            .    '<td><label for="explanation">' . get_lang('Comment') . ': </label></td>'
            .    '<td><textarea cols="60" rows="6" name="explanation" id="explanation"></textarea></td>'
            .    '</tr>'
            .    '<tr valign="top">'
            .    '<td>' . get_lang('Delete my account') . ': </td>'
            .    '<td><input type="submit" value="' . get_lang('Ok') . '"> '
            .    claro_html_button($_SERVER['PHP_SELF'], get_lang('Cancel'))
            .    '</td></tr>'
            .    '</table>'
            .    '</form>'
            ;
        }
    }   break;

} // end switch display

// display footer
include $includePath . '/claro_init_footer.inc.php';

?>
