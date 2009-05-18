<?php // $Id$
/**
 * CLAROLINE
 *
 * @version 1.9 $Revision$
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

require_once get_path('incRepositorySys') . '/lib/course_user.lib.php';

include claro_get_conf_repository() . 'user_profile.conf.php';

// Security check
if ( ! claro_is_user_authenticated() ) claro_disp_auth_form();
if ( ! claro_is_platform_admin() ) claro_die(get_lang('Not allowed'));

$nameTools = get_lang('User settings');
$dialogBox = '';

// BC
ClaroBreadCrumbs::getInstance()->prepend( get_lang('Administration'), get_path('rootAdminWeb') );

$user_id = $_REQUEST['uidToEdit'];

//------------------------------------
// Execute COMMAND section
//------------------------------------

if ( isset($_REQUEST['cmd'] ) && claro_is_platform_admin() )
{
    if ( $_REQUEST['cmd'] == 'UnReg' )
    {
        if ( user_remove_from_course($user_id, $_REQUEST['cidToEdit'],true, false) )
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

$cmdList[] = '<a class="claroCmd" href="index.php">' . get_lang('Back to administration page') . '</a>';
$cmdList[] = '<a class="claroCmd" href="adminusercourses.php?uidToEdit=' . $user_id.'">' . get_lang('Back to course list') . '</a>';

/**
 * DISPLAY
 */

$out = '';

$out .= claro_html_tool_title(get_lang('User unregistered'));

// Display Forms or dialog box(if needed)

if ( !empty($dialogBox) )
{
    $out .= claro_html_message_box($dialogBox);
}

$out .= '<p>'
.    claro_html_menu_horizontal($cmdList)
.    '</p>'
;

$claroline->display->body->appendContent($out);

echo $claroline->display->render();

?>