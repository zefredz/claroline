<?php // $Id$
/**
 * CLAROLINE
 *
 * This script is used to delete a user from the platform in the admin
 * tool from the page to visualize the user profile (adminprofile.php)
 * and display a confirmation message to the admin.
 *
 * @version 1.8 $Revision$
 * @copyright 2001-2005 Universite catholique de Louvain (UCL)
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
if ( ! $_uid ) claro_disp_auth_form();
if ( ! $is_platformAdmin ) claro_die(get_lang('Not allowed'));

require_once $includePath . '/lib/admin.lib.inc.php';
require_once $includePath . '/lib/user.lib.php';
require_once $includePath . '/conf/user_profile.conf.php'; // find this file to modify values.

$nameTools=get_lang('UserSettings');
$interbredcrump[]= array ('url' => get_conf($rootAdminWeb), 'name' => get_lang('Administration'));

//------------------------------------
// Execute COMMAND section
//------------------------------------

$cmd = (isset($_REQUEST['cmd']) ? $_REQUEST['cmd'] : null );

$req['uidToEdit'] = (isset($_REQUEST['uidToEdit']) && ctype_digit($_REQUEST['uidToEdit']))
? (int) $_REQUEST['uidToEdit']
: false;
if ( $cmd=='delete' && $req['uidToEdit'] )
{
    if(false!== $deletionResult = user_delete($req['uidToEdit']))
    $dialogBox =   get_lang('UserDelete');
    else
    {
        switch (claro_failure::get_last_failure())
        {
            case 'user_cannot_remove_himself'  :
            {
                $dialogBox = get_lang('NotUnregYourself');
            } break;
            default :  $dialogBox = get_lang('Deletetion unable');
        }
    }
}
else $dialogBox = get_lang('Deletetion unable');
//------------------------------------
// DISPLAY
//------------------------------------

include $includePath . '/claro_init_header.inc.php';

// Display tool title

echo claro_disp_tool_title(get_lang('DeleteUser'));

//Display Forms or dialog box(if needed)

if ( isset($dialogBox) ) echo claro_disp_message_box($dialogBox);

// display TOOL links :

echo '<a class="claroCmd" href="index.php" >' . get_lang('BackToAdmin') . '</a> | '
.    '<a class="claroCmd" href="adminusers.php" >' . get_lang('BackToUserList') . '</a>'
;

// display footer
include $includePath . '/claro_init_footer.inc.php';

?>