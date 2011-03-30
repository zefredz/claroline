<?php // $Id$

/**
 * CLAROLINE
 *
 * Management tools for new users.
 *
 * @version     $Revision$
 * @copyright   (c) 2001-2011, Universite catholique de Louvain (UCL)
 * @license     http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @author      Claro Team <cvs@claroline.net>
 */

define('DISP_REGISTRATION_SUCCEED','DISP_REGISTRATION_SUCCEED');
define('DISP_REGISTRATION_FORM','DISP_REGISTRATION_FORM');
$cidReset = true;
$gidReset = true;
$tidReset = true;
require '../inc/claro_init_global.inc.php';

// Security Check
if ( ! claro_is_user_authenticated() ) claro_disp_auth_form();
if ( ! claro_is_platform_admin() ) claro_die(get_lang('Not allowed'));

// Include library
require claro_get_conf_repository() . 'user_profile.conf.php';

require_once get_path('incRepositorySys') . '/lib/user.lib.php';
require_once get_path('incRepositorySys') . '/lib/sendmail.lib.php';

// Initialise variables
$nameTools = get_lang('Create a new user');
$error = false;
$messageList = array();
$display = DISP_REGISTRATION_FORM;

$dialogBox = new DialogBox;

/*=====================================================================
  Main Section
 =====================================================================*/

// Initialise field variable from subscription form
$user_data = user_initialise();

if ( isset($_REQUEST['cmd']) ) $cmd = $_REQUEST['cmd'];
else                           $cmd = '';

if ( $cmd == 'registration' )
{
    // get params from the form
    
    if ( isset($_REQUEST['lastname']) )      $user_data['lastname']      = strip_tags(trim($_REQUEST['lastname'])) ;
    if ( isset($_REQUEST['firstname']) )     $user_data['firstname']     = strip_tags(trim($_REQUEST['firstname'])) ;
    if ( isset($_REQUEST['officialCode']) )  $user_data['officialCode']  = strip_tags(trim($_REQUEST['officialCode'])) ;
    if ( isset($_REQUEST['username']) )      $user_data['username']      = strip_tags(trim($_REQUEST['username']));
    if ( isset($_REQUEST['password']) )      $user_data['password']      = trim($_REQUEST['password']);
    if ( isset($_REQUEST['password_conf']) ) $user_data['password_conf'] = trim($_REQUEST['password_conf']);
    if ( isset($_REQUEST['email']) )         $user_data['email']         = strip_tags(trim($_REQUEST['email'])) ;
    if ( isset($_REQUEST['language']) )      $user_data['language']   = trim($_REQUEST['language']);
    if ( isset($_REQUEST['phone']) )         $user_data['phone']         = trim($_REQUEST['phone']);
    if ( isset($_REQUEST['isCourseCreator']) ) $user_data['isCourseCreator'] = (int) $_REQUEST['isCourseCreator'];
    
    $user_data['language'] = null;
    // validate forum params
    
    $messageList = user_validate_form_registration($user_data);
    
    if ( count($messageList) == 0 )
    {
        // register the new user in the claroline platform
        $inserted_uid = user_create($user_data);
        if (false===$inserted_uid)
        {
            $dialogBox->error( claro_failure::get_last_failure() );
        }
        else
        {
            $dialogBox->success( get_lang('The new user has been sucessfully created') );
            
            $newUserMenu[]= claro_html_cmd_link( '../auth/courses.php?cmd=rqReg&amp;uidToEdit=' . $inserted_uid . '&amp;category=&amp;fromAdmin=settings'
                                               , get_lang('Register this user to a course'));
            $newUserMenu[]= claro_html_cmd_link( 'admin_profile.php?uidToEdit=' . $inserted_uid . '&amp;category='
                                               , get_lang('User settings'));
            $newUserMenu[]= claro_html_cmd_link( 'adminaddnewuser.php'
                                               , get_lang('Create another new user'));
            $newUserMenu[]= claro_html_cmd_link( 'index.php'
                                               , get_lang('Back to administration page'));
            
            $display = DISP_REGISTRATION_SUCCEED;
            // send a mail to the user
            if (false !== user_send_registration_mail($inserted_uid,$user_data))
            {
                $dialogBox->success( get_lang('Mail sent to user') );
            }
            else
            {
                $dialogBox->warning( get_lang('No mail sent to user') );
                // TODO  display in a popup "To Print" with  content to give to user.
            };
        }
    }
    else
    {
        // user validate form return error messages
        if( is_array($messageList) && !empty($messageList) )
        {
            foreach( $messageList as $message )
            {
                $dialogBox->error($message);
            }
        }
        $error = true;
    }
}

/*=====================================================================
  Display Section
 =====================================================================*/
/* hack to prevent autocompletion from browser */
JavascriptLoader::getInstance()->load('jquery');

$htmlHeadXtra[] =
'<script type="text/javascript">
    $(document).ready(
        function() {
            $("#password").val("");
        }
    );
</script>';
/* end of hack */

ClaroBreadCrumbs::getInstance()->prepend( get_lang('Administration'), get_path('rootAdminWeb') );
$noQUERY_STRING   = true;

if ( $display == DISP_REGISTRATION_FORM )
{
    $dialogBox->info( get_lang('New users will receive an e-mail with their user name and password') );
}

$out = '';

// Display title

$out .= claro_html_tool_title( array('mainTitle'=>$nameTools ) )
      . $dialogBox->render();

if ( $display == DISP_REGISTRATION_SUCCEED )
{
    $out .= claro_html_list($newUserMenu);
}
else // $display == DISP_REGISTRATION_FORM;
{
    //  if registration failed display error message
    $out .= user_html_form();
}

$claroline->display->body->appendContent($out);

echo $claroline->display->render();