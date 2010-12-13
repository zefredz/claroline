<?php // $Id$
/**
 * CLAROLINE
 *
 * This script is used to delete a user from the platform in the admin
 * tool from the page to visualize the user profile (adminprofile.php)
 * and display a confirmation message to the admin.
 *
 * @version 1.9 $Revision$
 * @copyright 2001-2006 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @see http://www.claroline.net/wiki/index.php/CLTREE
 *
 * @package CLUSR
 *
 * @author Claro Team <cvs@claroline.net>
 *
 */

$cidReset = TRUE;$gidReset = TRUE;$tidReset = TRUE;

require '../inc/claro_init_global.inc.php';

// Security check
if ( ! claro_is_user_authenticated() ) claro_disp_auth_form();
if ( ! claro_is_platform_admin() ) claro_die(get_lang('Not allowed'));

require_once get_path('incRepositorySys') . '/lib/admin.lib.inc.php';
require_once get_path('incRepositorySys') . '/lib/user.lib.php';
include claro_get_conf_repository() . 'user_profile.conf.php'; // find this file to modify values.

ClaroBreadCrumbs::getInstance()->prepend( get_lang('Administration'), get_path('rootAdminWeb') );
$nameTools=get_lang('User settings');

//------------------------------------
// Execute COMMAND section
//------------------------------------

$cmd = (isset($_REQUEST['cmd']) ? $_REQUEST['cmd'] : null );

$req['uidToEdit'] = (isset($_REQUEST['uidToEdit']) && ctype_digit($_REQUEST['uidToEdit']))
? (int) $_REQUEST['uidToEdit']
: false;


$cmdList[] = '<a class="claroCmd" href="index.php" >' . get_lang('Back to administration page') . '</a>';
$cmdList[] = '<a class="claroCmd" href="adminusers.php" >' . get_lang('Back to user list') . '</a>';

$dialogBox = new DialogBox();

if ( $cmd == 'exDelete' && $req['uidToEdit'] )
{
    $claroline->log( 'DELETE_USER' , array ('USER' => $req['uidToEdit']) );
    if(false !== $deletionResult = user_delete($req['uidToEdit']))
    $dialogBox->success( get_lang('Deletion of the user was done sucessfully') );
    else
    {
        switch (claro_failure::get_last_failure())
        {
            case 'user_cannot_remove_himself'  :
            {
                $dialogBox->error( get_lang('You can not change your own settings!') );
            } break;
            default :  $dialogBox->error( get_lang('Unable to delete') );
        }
    }
}
elseif( $cmd == 'rqDelete' && $req['uidToEdit'] )
{
    $user_properties = user_get_properties( $req['uidToEdit'] );
    if( is_array( $user_properties) )
    {
        $dialogBox->question( get_lang('Are you sure to delete user %firstname %lastname', array('%firstname' => $user_properties['firstname'], '%lastname' => $user_properties['lastname'])).'<br/><br/>'."\n"
        .    '<a href="adminuserdeleted.php?cmd=exDelete&amp;uidToEdit='.$req['uidToEdit'].'">'.get_lang('Yes').'</a>'
        .    ' | '
        .    '<a href="adminprofile.php?uidToEdit='.$req['uidToEdit'].'">'.get_lang('No').'</a>'."\n");
    }
}
else $dialogBox->error( get_lang('Unable to delete') );
//------------------------------------
// DISPLAY
//------------------------------------

$out = '';

$out .= claro_html_tool_title(get_lang('Delete user'));

if ( isset($dialogBox) ) $out .= $dialogBox->render();

$out .= '<p>'
.    claro_html_menu_horizontal($cmdList)
.    '</p>'
;

$claroline->display->body->appendContent($out);

echo $claroline->display->render();

?>