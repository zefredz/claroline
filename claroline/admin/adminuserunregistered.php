<?php // $Id$
/**
 * CLAROLINE
 *
 * @version 1.8 $Revision$
 * @copyright 2001-2006 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @see http://www.claroline.net/wiki/index.php/CLUSR
 *
 * @package CLUSR
 * @package CLCOURSES
 *
 * @author Claro Team <cvs@claroline.net>
 *
 */

$cidReset = TRUE;$gidReset = TRUE;$tidReset = TRUE;

require '../inc/claro_init_global.inc.php';

require_once $includePath . '/lib/admin.lib.inc.php';
require_once $includePath . '/lib/user.lib.php';

include $includePath . '/conf/user_profile.conf.php';

// Security check
if ( ! $_uid ) claro_disp_auth_form();
if ( ! $is_platformAdmin ) claro_die(get_lang('Not allowed'));

$nameTools = get_lang('User settings');
$dialogBox = '';

$interbredcrump[]= array ('url' => $rootAdminWeb, 'name' => get_lang('Administration'));
$user_id = $_REQUEST['uidToEdit'];

//------------------------------------
// Execute COMMAND section
//------------------------------------

if ( isset($_REQUEST['cmd'] ) && $is_platformAdmin )
{
    if ( $_REQUEST['cmd'] == 'UnReg' )
    {
        if ( user_remove_from_course($user_id, $_REQUEST['cidToEdit'],true) )
        {
            $dialogBox .= get_lang('The user has been successfully unregistered');
        }
        else
        {
            switch ( claro_failure::get_last_failure() )
            {
                case 'cannot_unsubscribe_the_last_course_manager' :
                    $dialogBox .= get_lang('You cannot unsubscribe the last course manager of the course');
                    break;
                case 'course_manager_cannot_unsubscribe_himself' :
                    $dialogBox .= get_lang('Course manager cannot unsubscribe himself');
                    break;
                default :
            }
        }
    }
}

/**
 * PREPARE DISPLAY
 */

$cmd_menu[] = '<a class="claroCmd" href="index.php">' . get_lang('Back to admin page') . '</a>';
$cmd_menu[] = '<a class="claroCmd" href="adminusercourses.php?uidToEdit=' . $user_id.'">' . get_lang('Back to course list') . '</a>';

/**
 * DISPLAY
 */

include $includePath . '/claro_init_header.inc.php';

echo claro_disp_tool_title(get_lang('User unregistered'));

// Display Forms or dialog box(if needed)

if ( !empty($dialogBox) )
{
    echo claro_html::message_box($dialogBox);
}

echo claro_html::menu_horizontal($cmd_menu);

// Display footer

include $includePath . '/claro_init_footer.inc.php';

?>