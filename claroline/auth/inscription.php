<?php // $Id$
/**
 * CLAROLINE
 *
 * @version 1.8 $Revision$
 *
 * @copyright (c) 2001-2006 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @package CLAUTH
 *
 * @author Claro Team <cvs@claroline.net>
 */

define('DISP_REGISTRATION_FORM',__LINE__);
define('DISP_REGISTRATION_SUCCEED',__LINE__);
define('DISP_REGISTRATION_AGREEMENT',__LINE__);

require '../inc/claro_init_global.inc.php';

// Already logged
if ( isset($_uid) )
{
    header('Location: ' . $urlAppend . '/index.php');
    exit;
}

// include profile library
include $includePath . '/conf/user_profile.conf.php';
include_once $includePath . '/lib/user.lib.php';
include_once $includePath . '/lib/sendmail.lib.php';


if ( get_conf('allowSelfReg',false) )
{
    // Initialise variables
    $error = false;
    $messageList = array();


    // Initialise field variable from subscription form

    $user_data = user_initialise();

    if ( isset($_REQUEST['cmd']) ) $cmd = $_REQUEST['cmd'];
    else                           $cmd = '';

    /**
     * Main Section
     */

    if ( $cmd == 'registration' )
    {
        // get params from the form

        if ( isset($_REQUEST['lastname']) )      $user_data['lastname'] = strip_tags(trim($_REQUEST['lastname'])) ;
        if ( isset($_REQUEST['firstname']) )     $user_data['firstname']  = strip_tags(trim($_REQUEST['firstname'])) ;
        if ( isset($_REQUEST['officialCode']) )  $user_data['officialCode']  = strip_tags(trim($_REQUEST['officialCode'])) ;
        if ( isset($_REQUEST['username']) )      $user_data['username']  = strip_tags(trim($_REQUEST['username']));
        if ( isset($_REQUEST['password']) )      $user_data['password']  = trim($_REQUEST['password']);
        if ( isset($_REQUEST['password_conf']) ) $user_data['password_conf']  = trim($_REQUEST['password_conf']);
        if ( isset($_REQUEST['email']) )         $user_data['email'] = strip_tags(trim($_REQUEST['email'])) ;
        if ( isset($_REQUEST['phone']) )         $user_data['phone']  = trim($_REQUEST['phone']);
        if ( isset($_REQUEST['status']) )        $user_data['status'] = (int) $_REQUEST['status'];
        if ( isset($_REQUEST['language']) )      $user_data['language'] = $_REQUEST['language'];

        // validate forum params

        $messageList = user_validate_form_registration($user_data);

        if ( count($messageList) == 0 )
        {
            // register the new user in the claroline platform

            $_uid = user_create($user_data);

            if ( $_uid )
            {
                // add value in session
                $_user['firstName']     = $user_data['firstname'];
                $_user['lastName' ]     = $user_data['lastname'];
                $_user['mail'     ]     = $user_data['email'];
                $_user['lastLogin']     = time() - (24 * 60 * 60); // DATE_SUB(CURDATE(), INTERVAL 1 DAY)
                $is_allowedCreateCourse = ($user_data['status'] == 1) ? TRUE : FALSE ;

                $_SESSION['_uid'] = $_uid;
                $_SESSION['_user'] = $_user;
                $_SESSION['is_allowedCreateCourse'] = $is_allowedCreateCourse;

                // track user login

                $eventNotifier->notifyEvent('user_login', array('uid' => $_uid));
                event_login();

                // last user login date is now
                $user_last_login_datetime = 0; // used as a unix timestamp it will correspond to : 1 1 1970
                $_SESSION['user_last_login_datetime'] = $user_last_login_datetime;

                // send info to user by email
                user_send_registration_mail($_uid, $user_data);

            } // if _uid
            else
            {
                if('MISSING_DATA' == claro_failure::get_last_failure())
                $messageList[][] = get_lang('DataMissing');
            }

        } // end register user
        else
        {
            // user validate form return error messages
            $error = true;
        }

    }

    if ( $cmd == 'registration' && $error == false )
    {
        $display = DISP_REGISTRATION_SUCCEED;
    }
    elseif ( $cmd == 'agree' || ! get_conf('show_agreement_panel') || $cmd == 'registration' )
    {
        $display = DISP_REGISTRATION_FORM;
    }
    else
    {
        $display = DISP_REGISTRATION_AGREEMENT;
    }
}
else
{
    $display = DISP_REGISTRATION_AGREEMENT;
}


/*= = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =
Display Section
= = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = */

$interbredcrump[]= array ('url' => 'inscription.php', 'name' => get_lang('Create user account'));

// Display Header

include $includePath . '/claro_init_header.inc.php';

// Display Title

echo claro_html_tool_title(get_lang('Create user account'));

if ( DISP_REGISTRATION_SUCCEED == $display )
{
    // registration succeeded

    echo get_lang('Dear %firstname %lastname. Your personal settings have been registered and an email has been sent to help you remember your user name and password.', array('%firstname'=>$user_data['firstname'],'%lastname'=>$user_data['lastname']));

    if ( $is_allowedCreateCourse ) echo '<p>' . get_lang('You can now create your  course') . '</p>' . "\n";
    else                           echo '<p>' . get_lang('You can now select, in the list, the courses you want to access') . '</p>' . "\n";

    echo '<form action="../../index.php?cidReset=1" >'
    .    '<input type="submit" name="next" value="' . get_lang('Next') . '" />' . "\n"
    .    '</form>' . "\n"
    ;
}
elseif ( DISP_REGISTRATION_AGREEMENT == $display )
{

    if (file_exists('./textzone_inscription.inc.html'))
    {
        echo '<div class="info">';
        readfile('./textzone_inscription.inc.html'); // Introduction message if needed
        echo '</div>';
    }

    if ($is_platformAdmin)
    {
        echo '&nbsp;'
        .    '<a style="font-size: smaller" href="claroline/admin/managing/editFile.php?cmd=edit&amp;file=2">'
        .    '<img src="claroline/img/edit.gif" />' . get_lang('Edit text zone')
        .    '</a>' . "\n"
        .    '<br />' . "\n"
        ;
    }

    echo '<br />'
    .    '<form action="' . $_SERVER['PHP_SELF'] . '" >'
    .    '<input type="hidden" name="cmd" value="agree" />' . "\n"
    .    '<input type="submit" name="next" value="' . get_lang('Ok') . '" />' . "\n"
    .    claro_html_button( $urlAppend.'/index.php', get_lang('Cancel') )
    .    '</form>' . "\n"
    ;
}
elseif ( DISP_REGISTRATION_FORM == $display  )
{
    //  if registration failed display error message

    if ( count($messageList) > 0 )
    {
        echo claro_html_message_box( implode('<br />', $messageList) );
    }

    user_display_form_registration($user_data);
}
else
{
    // DISPLAY ERROR
}

// Display Footer

include $includePath . '/claro_init_footer.inc.php';

?>
