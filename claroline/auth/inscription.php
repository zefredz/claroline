<?php // $Id$
/**
 * CLAROLINE
 *
 * @version 1.9 $Revision$
 *
 * @copyright (c) 2001-2010, Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @package CLAUTH
 *
 * @author Claro Team <cvs@claroline.net>
 * @author Dimitri Rambout <dimitri.rambout@uclouvain.be>
 */

define('DISP_REGISTRATION_FORM',__LINE__);
define('DISP_REGISTRATION_SUCCEED',__LINE__);
define('DISP_REGISTRATION_AGREEMENT',__LINE__);
define('DISP_REGISTRATION_NOT_ALLOWED',__LINE__);

require '../inc/claro_init_global.inc.php';

// Already logged
if ( claro_is_user_authenticated() )
{
    claro_redirect(get_conf('urlAppend') . '/index.php');
    exit;
}

// include profile library
include claro_get_conf_repository() . 'user_profile.conf.php';
include_once get_path('incRepositorySys') . '/lib/user.lib.php';
include_once get_path('incRepositorySys') . '/lib/sendmail.lib.php';

$agreementText = claro_text_zone::get_content('textzone_inscription');
if ( '' == $agreementText && file_exists('./textzone_inscription.inc.html'))
{
    $agreementText = file_get_contents('./textzone_inscription.inc.html'); // Introduction message if needed
    if ( '' == trim(strip_tags($agreementText,'<img><embed><object>'))) $agreementText = '';

}


if ( get_conf('allowSelfReg',false) )
{
    // Initialise variables
    $error = false;
    $messageList = array();
    $mailSent = false;

    // Initialise field variable from subscription form

    $user_data = user_initialise();

    if ( isset($_REQUEST['cmd']) ) $cmd = $_REQUEST['cmd'];
    else                           $cmd = '';

    /**
     * Main Section
     */

    if ( 'registration' == $cmd )
    {
        // get params from the form

        if ( isset($_REQUEST['lastname']) )      $user_data['lastname']      = strip_tags(trim($_REQUEST['lastname'])) ;
        if ( isset($_REQUEST['firstname']) )     $user_data['firstname']     = strip_tags(trim($_REQUEST['firstname'])) ;
        if ( isset($_REQUEST['officialCode']) )  $user_data['officialCode']  = strip_tags(trim($_REQUEST['officialCode'])) ;
        if ( isset($_REQUEST['username']) )      $user_data['username']      = strip_tags(trim($_REQUEST['username']));
        if ( isset($_REQUEST['password']) )      $user_data['password']      = trim($_REQUEST['password']);
        if ( isset($_REQUEST['password_conf']) ) $user_data['password_conf'] = trim($_REQUEST['password_conf']);
        if ( isset($_REQUEST['email']) )         $user_data['email']         = strip_tags(trim($_REQUEST['email'])) ;
        if ( isset($_REQUEST['officialEmail']) ) $user_data['officialEmail'] = strip_tags(trim($_REQUEST['officialEmail'])) ;
        if ( isset($_REQUEST['phone']) )         $user_data['phone']  = trim($_REQUEST['phone']);
        if ( isset($_REQUEST['isCourseCreator']))$user_data['isCourseCreator'] = (int) $_REQUEST['isCourseCreator'];
        if ( isset($_REQUEST['language']) )      $user_data['language'] = $_REQUEST['language'];

        // validate forum params

        $messageList = user_validate_form_registration($user_data);

        if ( count($messageList) == 0 )
        {
            // register the new user in the claroline platform

            $_uid = user_create($user_data);

            if ( claro_is_user_authenticated() )
            {

                // add value in session
                $_user = user_get_properties(claro_get_current_user_id());
                $_user['firstName'] = $_user['firstname'];
                $_user['lastName' ] = $_user['lastname'];
                $_user['mail'     ] = $_user['email'];
                $_user['lastLogin'] = claro_time() - (24 * 60 * 60); // DATE_SUB(CURDATE(), INTERVAL 1 DAY)
                $is_allowedCreateCourse = ($user_data['isCourseCreator'] == 1) ? TRUE : FALSE ;

                $_SESSION['_uid'] = claro_get_current_user_id();
                $_SESSION['_user'] = $_user;
                $_SESSION['is_allowedCreateCourse'] = $is_allowedCreateCourse;

                // track user login
                $claroline->notifier->event( 'user_login', array('data' => array('ip' => $_SERVER['REMOTE_ADDR']) ) );

                // last user login date is now
                $user_last_login_datetime = 0; // used as a unix timestamp it will correspond to : 1 1 1970
                $_SESSION['user_last_login_datetime'] = $user_last_login_datetime;

                // send info to user by email
                $mailSent = user_send_registration_mail(claro_get_current_user_id(), $user_data);

            } // if _uid
            else
            {
                if('MISSING_DATA' == claro_failure::get_last_failure())
                {
                    $messageList[] = get_lang('Data missing');
                }
                else
                {
                    $messageList[] = get_lang('Unknown error');
                }

            }

        } // end register user
        else
        {
            // user validate form return error messages
            $error = true;
        }

    }

    if ( 'registration' == $cmd && $error == false )
    {
        $display = DISP_REGISTRATION_SUCCEED;
    }
    elseif ( 'agree' == $cmd
        || ! get_conf('show_agreement_panel')
        || 'registration' == $cmd
        || '' == $agreementText)
    {
        $display = DISP_REGISTRATION_FORM;
        $subscriptionText = claro_text_zone::get_content('textzone_inscription_form');

    }
    else
    {
        $display = DISP_REGISTRATION_AGREEMENT;
    }
}
elseif (! get_conf('show_agreement_panel'))
{
    // This  section is not use actually.
    // it's only when selfReg =false so  It's need another textZoneContent
    $display = DISP_REGISTRATION_AGREEMENT;
}
else
{
    $display = DISP_REGISTRATION_NOT_ALLOWED;
}

/*= = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =
Display Section
= = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = */

ClaroBreadCrumbs::getInstance()->append( get_lang('Create user account'), 'inscription.php' );

$out = '';

// Display Title

$out .= claro_html_tool_title(get_lang('Create user account'));

if ( DISP_REGISTRATION_SUCCEED == $display )
{
    // registration succeeded


    $out .= '<p>'  . "\n"
    .    get_lang('Dear %firstname %lastname, your personal settings have been registered.', array('%firstname'=>$user_data['firstname'],'%lastname'=>$user_data['lastname']))  . "\n"
    ;

    if ( $mailSent ) $out .= '<br />' . "\n" . get_lang('An email has been sent to help you remember your user name and password.');
    $out .= '</p>' . "\n";

    if ( claro_is_allowed_to_create_course() ) $out .= '<p>' . get_lang('You can now create your  course') . '</p>' . "\n";
    else                                       $out .= '<p>' . get_lang('You can now select, in the list, the courses you want to access') . '</p>' . "\n";

    $out .= '<form action="../../index.php?cidReset=1" >'
    .    '<input type="submit" name="next" value="' . get_lang('Next') . '" />' . "\n"
    .    '</form>' . "\n"
    ;
}
elseif ( DISP_REGISTRATION_AGREEMENT == $display )
{


    if ( trim ($agreementText) != '')
    {
        $out .= '<div class="info">'
        .    $agreementText
        .    '</div>'
        ;
    }

    $out .= '<br />'
    .    '<form action="' . $_SERVER['PHP_SELF'] . '" >'
    .    '<input type="hidden" name="cmd" value="agree" />' . "\n"
    .    '<input type="submit" name="next" value="' . get_lang('Ok') . '" />&nbsp;' . "\n"
    .    claro_html_button( get_conf('urlAppend') . '/index.php', get_lang('Cancel') )
    .    '</form>' . "\n"
    ;
}
elseif (  DISP_REGISTRATION_NOT_ALLOWED == $display )
{

    $out .= claro_html_msg_list(array(array('info'=>    get_lang('Subscription not allowed'))));

    $out .= '<br />'
    .    '<form action="' . get_conf('rootWeb','/') . '" >'
    .    '<input type="submit" name="next" value="' . get_lang('Ok') . '" />' . "\n"
    .    '</form>' . "\n"
    ;
}
elseif ( DISP_REGISTRATION_FORM == $display  )
{
    //  if registration failed display error message

    if ( count($messageList) > 0 )
    {
        $dialogBox = new DialogBox();
        $dialogBox->error( implode('<br />', $messageList) );
        $out .= $dialogBox->render();
    }

    if ( trim ($subscriptionText) != '')
    {
        $out .= '<div class="info subscribe">'
        .    $subscriptionText
        .    '</div>'
        ;
    }

    $out .= user_html_form_registration($user_data);
}
else
{
    // DISPLAY ERROR
}

$claroline->display->body->appendContent($out);

echo $claroline->display->render();

?>
