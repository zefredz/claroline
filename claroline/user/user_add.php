<?php // $Id$
/**
 * CLAROLINE
 *
 * This tool allow to add a user in his course (an din the platform)
 *
 * @version 1.8 $Revision$
 *
 * @copyright 2001-2006 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @see http://www.claroline.net/wiki/index.php/CLUSR
 *
 * @author Claro Team <cvs@claroline.net>
 *
 * @package CLUSR
 *
 */
/*=====================================================================
 Init Section
 =====================================================================*/

$tlabelReq = 'CLUSR___';

require '../inc/claro_init_global.inc.php';

// Security check
if ( ! $_cid || ! $is_courseAllowed ) claro_disp_auth_form(true);
if ( ! $is_courseAdmin ) claro_die(get_lang('Not allowed'));

// include configuration file
include($includePath."/conf/user_profile.conf.php");

// include libraries
require_once $includePath . '/lib/user.lib.php';
require_once $includePath . '/lib/claro_mail.lib.inc.php';

// Initialise variables
$nameTools        = get_lang('Add a user');
$interbredcrump[] = array ('url' => 'user.php', 'name' => get_lang('Users'));

$messageList = array();

$platformRegSucceed = false;
$courseRegSucceed = false;

/*=====================================================================
 Main Section
 =====================================================================*/

// Initialise field variable from subscription form
$user_data = user_initialise();
$user_data['is_coursemanager'] = STUDENT;
$user_data['is_tutor'] = 0;

if ( isset($_REQUEST['cmd']) ) $cmd = $_REQUEST['cmd'];
else                           $cmd = '';

if ((isset($_REQUEST['applySearch']) && ($_REQUEST['applySearch'] != "")))
{
    $cmd = "applySearch";
}

if ( !empty($cmd) )
{
    // get params from the form

    if ( isset($_REQUEST['lastname']) )      $user_data['lastname'] = strip_tags(trim($_REQUEST['lastname'])) ;
    if ( isset($_REQUEST['firstname']) )     $user_data['firstname']  = strip_tags(trim($_REQUEST['firstname'])) ;
    if ( isset($_REQUEST['officialCode']) )  $user_data['officialCode']  = strip_tags(trim($_REQUEST['officialCode'])) ;
    if ( isset($_REQUEST['username']) )      $user_data['username']  = strip_tags(trim($_REQUEST['username']));
    if ( isset($_REQUEST['password']) )      $user_data['password']  = trim($_REQUEST['password']);
    if ( isset($_REQUEST['password_conf']) ) $user_data['password_conf']  = trim($_REQUEST['password_conf']);
    if ( isset($_REQUEST['email']) )         $user_data['email']  = strip_tags(trim($_REQUEST['email'])) ;
    if ( isset($_REQUEST['phone']) )         $user_data['phone']  = trim($_REQUEST['phone']);
    if ( isset($_REQUEST['status']) )        $user_data['status']  = (int) $_REQUEST['status'];

    if ( isset($_REQUEST['is_coursemanager'])) $user_data['is_coursemanager'] = (int) $_REQUEST['is_coursemanager'];
    if ( isset($_REQUEST['is_tutor']))         $user_data['is_tutor'] = (int) $_REQUEST['is_tutor'];
}

$displayResultTable = FALSE;

switch ( $cmd )
{
    case 'registration':

        // validate forum params
        $messageList = user_validate_form_registration($user_data);

        if ( count($messageList) == 0 )
        {
            // register the new user in the claroline platform
            $user_id = user_add($user_data);

            if ( $user_id )
            {
                $platformRegSucceed = true;

                // add user to course
                if ( user_add_to_course($user_id, $_cid) )
                {
                    // update course manager and tutor status
                    user_update_course_manager_status($user_id, $_cid, $user_data['is_coursemanager']);
                    user_update_course_tutor_status($user_id, $_cid, $user_data['is_tutor']);
                    $courseRegSucceed = true;
                }
            }
        }
        else
        {
            // user validate form return error messages
            $error = true;
        }

        break;

    case 'applySearch':

        // search on username, official_code, ...

        $displayResultTable = TRUE;

        $user_data['lastname']     = str_replace('%', '', $user_data['lastname']);
        $user_data['email']        = str_replace('%', '', $user_data['email']);
        $user_data['officialCode'] = str_replace('%', '', $user_data['officialCode']);

        if (!(empty($user_data['lastname']) && empty($user_data['email']) && empty($user_data['officialCode'])))
        {
            $users = user_search($user_data['lastname'], $user_data['email'], $user_data['officialCode'],$_cid);
        }
        else
            $users = array();
        break;

    case 'subscribe_to_course':

        if ( isset($_REQUEST['user_id']) )
        {
            $user_id = $_REQUEST['user_id'];

            // add user to course
            user_add_to_course($user_id, $_cid);

            // get user info
            $user_data = user_get_data($user_id);

            $courseRegSucceed = true;
        }
        else
        {
            $error = true;
        }
        break;

    default:
        // do nothing
        break;

} // end switch cmd


// Send mail notification

if ( $platformRegSucceed || $courseRegSucceed )
{

    if ( $platformRegSucceed )
       {
        // Send message and login and password
        user_send_registration_mail($user_id, $user_data);
    }

    if ( $courseRegSucceed )
    {
        // Send enroll to course message
        user_send_enroll_to_course_mail ($user_id, $user_data);
    }

    // display message
    $messageList[]= get_lang('%firstname %lastname has been registered to your course', array ( '%firstname' => $user_data['firstname'],
                                                                                                '%lastname' => $user_data['lastname']) ) ;
}

/*=====================================================================
 Display Section
 =====================================================================*/

// display header
include($includePath.'/claro_init_header.inc.php');

echo claro_html_tool_title(array('mainTitle' =>$nameTools, 'supraTitle' => get_lang('Users')),
                'help_user.php');

// message box

if ( count($messageList) > 0 )
{
    echo claro_html_message_box( implode('<br />', $messageList) );
}

if ( $platformRegSucceed )
{
    echo '<p><a href="user.php"><< ' .  get_lang('Back to user list') . '</a></p>' . "\n";
}
else
{
    //display result of search (if any)

    if ($displayResultTable)
    {
        //displkay a search legend first

        if ( get_conf('allowSearchInAddUser') ) $enclose_field = '*';
        else                                    $enclose_field = '';

        echo get_lang('Search on') . ' : ';

        if ($user_data['lastname'] != '')
        {
            echo get_lang('Last name') . '=' . $user_data['lastname'] . $enclose_field . ' ';
        }
        if ($user_data['email'] != '')
        {
            echo get_lang('Email') . '=' . $user_data['email'] . $enclose_field . ' ';
        }
        if ($user_data['officialCode'] != '')
        {
            echo get_lang('Administrative code') . "=" . $user_data['officialCode'] . " ";
        }
        echo '<br /><br />'
        .    '<table class="claroTable emphaseLine" border="0" cellspacing="2">' . "\n"
        .    '<thead>' . "\n"
        .    '<tr class="headerX" align="center" valign="top">' . "\n"
        .    '<th>' . get_lang('Last name')     . '</th>' . "\n"
        .    '<th>' . get_lang('First name')    . '</th>' . "\n"
        .    '<th>' . get_lang('Email')        . '</th>' . "\n"
        .    '<th>' . get_lang('Administrative code') . '</th>' . "\n"
        .    '<th>' . get_lang('Register')     . '</th>' . "\n"
        .    '</tr>' . "\n"
        .    '</thead>' . "\n"
        .    '<tbody>' . "\n"
        ;

        foreach ($users as $user)
        {
           echo '<tr>'
           .    '<td>'
           .    $user['nom']
           .    '</td>'
           .    '<td>'
           .    $user['prenom']
           .    '</td>'
           .    '<td>'
           .    $user['email']
           .    '</td>'
           .    '<td>'
           .    $user['officialCode']
           .    '</td>'
           .    '<td align="center" valign="top">'
           ;

                // deal with already registered users found in result

                if (empty($user['registered']))
                {
                    echo '<a href="user.php?cmd=register&amp;user_id=' . $user['user'] . '">'
                    .    '<img src="' . $imgRepositoryWeb . 'enroll.gif" border="0" />'
                    ;
                }
                else
                {
                    echo '<small>'
                    .    '<span class="highlight">'
                    .    get_lang('Already enroled')
                    .    '</span>'
                    .    '</small>'
                    ;
                }

                echo "  </td>"
                    ."</tr>";
        }

        if (sizeof($users)==0)
        {
            echo '<td align="center" colspan="5">' . get_lang('No user found') . '</td>';
        }
        echo '</body>'
        .    '</table><br />'
        ;
    }

    //display form to add a user

    echo get_lang('Add user manually')." :";
    echo '<p>' . get_lang('He or she will receive email confirmation with login and password') . '</p>' . "\n";

    user_display_form_add_new_user($user_data);

}

// display footer
include $includePath . '/claro_init_footer.inc.php';
?>
