<?php // $Id$
/**
 * CLAROLINE
 *
 * This  page show  to the user, the course description
 *
 * If ist's the admin, he can access to the editing
 *
 * @version 1.7 $Revision$
 *
 * @copyright 2001-2005 Universite catholique de Louvain (UCL)
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

require '../inc/claro_init_global.inc.php';

$messageList = array();
$display = '';
$error = false;

// include configuration files
include $includePath . '/conf/user_profile.conf.php'; // find this file to modify values.

// include library files
include $includePath . '/lib/user.lib.php';
include $includePath . '/lib/profile.lib.php';
include $includePath . '/lib/claro_mail.lib.inc.php';
include $includePath . '/lib/fileManage.lib.php';
//include $includePath.'/lib/auth.lib.inc.php';

$nameTools = $langModifyProfile;

// DB tables definition
$tbl_mdb_names = claro_sql_get_main_tbl();
$tbl_user      = $tbl_mdb_names['user'];

// define display
define('DISP_PROFILE_FORM',__LINE__);
define('DISP_REQUEST_COURSE_CREATOR_STATUS',__LINE__);
define('DISP_REQUEST_REVOQUATION',__LINE__);

$display = DISP_PROFILE_FORM;

/*=====================================================================
 Main Section
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

    // validate forum params

    $messageList = user_validate_form_profile($user_data, $_uid);
    
    if ( count($messageList) == 0 )
    {

        // if no error update use setting 
        user_update ($_uid, $user_data); 

        // re-init the system to take new settings in account

        $uidReset = true;
        include('../inc/claro_init_local.inc.php');
        $messageList[] = $langProfileReg . '<br>' . "\n"
        .                '<a href="../../index.php">' . $langHome . '</a>'
        ;

    } // end if $userSettingChangeAllowed
    else
    {
        // user validate form return error messages
        $error = true;
    }

    // Initialise
    $user_data = user_get_data($_uid);

}
elseif (    $can_request_course_creator_status // FROM CONFIG
         && $cmd == 'exCCstatus' ) 
{
    // send a request for course creator status
    profile_send_request_course_creator_status($_REQUEST['explanation']);
    $messageList[] = $langYourRequestToBeCourseManagerIsSent;
}
elseif (    $can_request_revoquation // FROM CONFIG
         && $cmd == 'exRevoquation' )
{
    // send a request for revoquation
    profile_send_request_revoquation($_REQUEST['explanation'], $_REQUEST['loginToDelete'],$_REQUEST['passwordToDelete']);
    $messageList[] = $langYourRequestToRemoveYourAccountIsSent;
}
elseif (    $can_request_course_creator_status  // FROM CONFIG
         && $cmd == 'reqCCstatus' )
{
    // display course creator status form
    $noQUERY_STRING = TRUE;
    $display = DISP_REQUEST_COURSE_CREATOR_STATUS;
    $nameTools = $langRequestOfCourseCreatorStatus;
} 
elseif ( $can_request_revoquation // FROM CONFIG 
         && $cmd == 'reqRevoquation' )
{
    // display revoquation form
    $noQUERY_STRING = TRUE;
    $display = DISP_REQUEST_REVOQUATION;
}

// Initialise
$user_data = user_get_data($_uid);

/*=====================================================================
  Display Section
 =====================================================================*/ 

// display header
include($includePath.'/claro_init_header.inc.php');

echo claro_disp_tool_title($nameTools);
        
if ( count($messageList) > 0 ) 
{
    echo claro_disp_message_box( implode('<br />', $messageList) );
}

switch ( $display )
{
    case DISP_PROFILE_FORM :

        // display form profile
        user_display_form_profile($user_data);

        // display user tracking link
        echo '<p>'
        .    '<a class="claroCmd" href="' . $urlAppend . '/claroline/tracking/personnalLog.php">'
        .    '<img src="' . $clarolineRepositoryWeb . '/img/statistics.gif">' . $langMyStats
        .    '</a>'
        ;

        // display request course creator status
        if ( $can_request_course_creator_status )
        {
            echo ' | <a class="claroCmd" href="' . $_SERVER['PHP_SELF'] . '?cmd=reqCCstatus">' . $langRequestOfCourseCreatorStatus . '</a>';
        }
        
        // display user revoquation
        if ( $can_request_revoquation )
        {
            echo ' | <a class="claroCmd" href="' . $_SERVER['PHP_SELF'] . '?cmd=reqRevoquation">' . $langDeleteMyAccount . '</a>' ;
        }
        
        echo '</p>' . "\n" ;

        break;

    case DISP_REQUEST_COURSE_CREATOR_STATUS :

        if ( $can_request_course_creator_status )
        {
            echo '<p>' . $langFillTheAreaToExplainTheMotivations . '</p>';

            // display request course creator form
            echo '<form action="' . $_SERVER['PHP_SELF'] . '" method="post">'
                . '<input type="hidden" name="cmd" value="exCCstatus" />'
                . '<table>'
                . '<tr valign="top">'
                . '<td><label for="explanation">' . $langComment . ': </label></td>'
                . '<td><textarea cols="60" rows="6" name="explanation" id="explanation"></textarea></td>'
                . '</tr>'
                . '<tr valign="top">' 
                . '<td>' . $langSubmit . ': </td>'
                . '<td><input type="submit" value="' . $langOk . '"> '
                . claro_disp_button($_SERVER['PHP_SELF'], $langCancel)
                . '</td></tr>'
                . '</table>'
                . '</form>';
        }
        break;

    case DISP_REQUEST_REVOQUATION :
    
        if ( $can_request_revoquation )
        {

            echo '<form action="' . $_SERVER['PHP_SELF'] . '" method="post">'
                . '<input type="hidden" name="cmd" value="exRevoquation" />'
                . '<table>'
                . '<tr valign="top">'
                . '<td>' . $langUserName . ': </td>'
                . '<td><input type="text" name="loginToDelete" ></td>'
                . '</tr>'
                . '<tr valign="top">'
                . '<td>' . $langPassword . ': </td>'
                . '<td><input type="password" name="passwordToDelete" ></td>'
                . '</tr>'
                . '<tr valign="top">'
                . '<td><label for="explanation">' . $langComment . ': </label></td>'
                . '<td><textarea cols="60" rows="6" name="explanation" id="explanation"></textarea></td>'
                . '</tr>'
                . '<tr valign="top">' 
                . '<td>' . $langSubmit . ': </td>'
                . '<td><input type="submit" value="' . $langDeleteMyAccount . '"> '
                . claro_disp_button($_SERVER['PHP_SELF'], $langCancel)
                . '</td></tr>'
                . '</table>'
                . '</form>';
        }
        break;

} // end switch display

// display footer
include($includePath."/claro_init_footer.inc.php");

?>
