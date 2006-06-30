<?php // $Id$
/**
 * CLAROLINE
 * @version 1.8 $Revision$
 *
 * @copyright (c) 2001-2006 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @package ADMIN
 *
 * @author claro team <cvs@claroline.net>
 */
$cidReset=true;
$gidReset=true;
require '../inc/claro_init_global.inc.php';

//SECURITY CHECK

if ( ! $_uid ) claro_disp_auth_form();
if ( ! $is_platformAdmin ) claro_die(get_lang('Not allowed'));

require_once $includePath . '/lib/admin.lib.inc.php';

//------------------------
//  USED SESSION VARIABLES
//------------------------
// clean session of possible previous search information. : COURSE

unset($_SESSION['admin_course_code']);
unset($_SESSION['admin_course_search']);
unset($_SESSION['admin_course_intitule']);
unset($_SESSION['admin_course_category']);
unset($_SESSION['admin_course_language']);
unset($_SESSION['admin_course_access']);
unset($_SESSION['admin_course_subscription']);
unset($_SESSION['admin_course_order_crit']);


// deal with session variables clean session variables from previous search : USER

unset($_SESSION['admin_user_search']);
unset($_SESSION['admin_user_firstName']);
unset($_SESSION['admin_user_lastName']);
unset($_SESSION['admin_user_userName']);
unset($_SESSION['admin_user_mail']);
unset($_SESSION['admin_user_action']);
unset($_SESSION['admin_order_crit']);

$controlMsg = array();

$menuAdminUser      = get_menu_item_list('AdminUser');
$menuAdminCourse    = get_menu_item_list('AdminCourse');
$menuAdminClaroline = get_menu_item_list('AdminClaroline');
$menuAdminPlatform  = get_menu_item_list('AdminPlatform');
$menuAdminSDK       = get_menu_item_list('AdminSDK');

//----------------------------------
// DISPLAY
//----------------------------------

// Deal with interbredcrumps  and title variable

$nameTools = get_lang('Administration');

include_once $includePath . '/lib/debug.lib.inc.php';
$is_allowedToAdmin     = $is_platformAdmin;

// ----- is install visible ----- begin
if ( file_exists('../install/index.php') && ! file_exists('../install/.htaccess'))
{
     $controlMsg['warning'][] = get_lang('<b>Notice :</b> The directory containing your Claroline installation process (<code>claroline/install/</code>) is still browsable by the web. It means anyone can reinstall Claroline and crush your previous installation. We highly recommend to protect this directory or to remove it from your server');
}

// ----- is install visible ----- end

if ( ini_get('register_globals') )
{
    $controlMsg['warning'][] = get_lang('<b>Security :</b> We recommend to set register_globals to off in php.ini');
}

include $includePath . '/claro_init_header.inc.php';
echo claro_html_tool_title($nameTools)
.    claro_html_msg_list( $controlMsg,1) . "\n\n"
;

echo '<table cellspacing="5" align="center">' . "\n"
.    '<tr valign="top">' . "\n"
.    '<td nowrap="nowrap">' . "\n"
.    claro_html_tool_title('<img src="' . $imgRepositoryWeb . 'user.gif" alt="" />&nbsp;'.get_lang('Users'))
.    claro_html_menu_vertical($menuAdminUser)
.    '</td>' . "\n"
.    '<td nowrap="nowrap">'
.    claro_html_tool_title('<img src="' . $imgRepositoryWeb . 'course.gif" alt="" />&nbsp;'.get_lang('Courses'))
.    claro_html_menu_vertical($menuAdminCourse) . "\n"
.    '</td>' . "\n"
.    '</tr>' . "\n"
.    '<tr valign="top">' . "\n"
.    '<td nowrap="nowrap">' . "\n"
.    claro_html_tool_title('<img src="' . $imgRepositoryWeb . 'settings.gif" alt="" />&nbsp;'.get_lang('Platform')) . "\n"
.    claro_html_menu_vertical($menuAdminPlatform) . "\n"
.    '</td>' . "\n"
.    '<td nowrap="nowrap">' . "\n"
.    claro_html_tool_title('<img src="' . $imgRepositoryWeb . 'claroline.gif" alt="" />&nbsp;Claroline.net')
.    claro_html_menu_vertical($menuAdminClaroline)
.    '</td>' . "\n"
.    '</tr>' . "\n"
.    '<tr valign="top">' . "\n";

if ( ( get_conf('DEVEL_MODE', false) == TRUE )
|| ( defined('CLAROLANG') && CLAROLANG == 'TRANSLATION') )
{
    echo '<td nowrap="nowrap">'
    .    claro_html_tool_title('<img src="' . $imgRepositoryWeb . 'exe.gif" alt="" />&nbsp;'.get_lang('SDK')) . "\n"
    .    claro_html_menu_vertical($menuAdminSDK)
    .    '</td>'
    ;
}
echo '</tr>';
?>

</table>
<?php
include $includePath . '/claro_init_footer.inc.php';

function get_menu_item_list($type)
{

    $menuAdminUser[] =  '<form name="searchUser" action="adminusers.php" method="GET" >' . "\n"
    .                   '<label for="search_user">' . get_lang('User') . '</label>'
    .                   ' : '
    .                   '<input name="search" id="search_user" />'
    .                   '<input type="submit" value="' . get_lang('Search') . '" />'
    .                   '&nbsp;&nbsp;'
    .                   '<small>'
    .                   '<a href="advancedUserSearch.php">'
    .                   get_lang('Advanced')
    .                   '</a>'
    .                   '</small>'
    .                   '</form>'
    ;

$menuAdminUser[] = claro_html_tool_link('adminusers.php',       get_lang('User list'));
$menuAdminUser[] = claro_html_tool_link('adminaddnewuser.php',  get_lang('Create user'));
$menuAdminUser[] = claro_html_tool_link('admin_class.php',      get_lang('Manage classes'));
$menuAdminUser[] = claro_html_tool_link('../user/AddCSVusers.php?AddType=adminTool', get_lang('Add a user list'));
$menuAdminUser[] = claro_html_tool_link('right/profile_list.php', get_lang('Right profile list'));


$menuAdminCourse[] = '<form name="searchCourse" action="admincourses.php" method="GET" >' . "\n"
.                    '<label for="search_course">' . get_lang('Course') . '</label> :' . "\n"
.                    '<input name="search" id="search_course" />' . "\n"
.                    '<input type="submit" value="' . get_lang('Search'). '" />' . "\n"
.                    '&nbsp; &nbsp;<small><a href="advancedCourseSearch.php">' . get_lang('Advanced') . '</a></small>' . "\n"
.                    '</form>'
;

$menuAdminCourse[] = claro_html_tool_link('admincourses.php',                   get_lang('Course list'));
$menuAdminCourse[] = claro_html_tool_link('../course/create.php?fromAdmin=yes', get_lang('Create course'));
$menuAdminCourse[] = claro_html_tool_link('admincats.php',                      get_lang('Manage course categories'));


$menuAdminPlatform[] = claro_html_tool_link('tool/config_list.php', get_lang('Configuration'));
$menuAdminPlatform[] = claro_html_tool_link('managing/editFile.php',get_lang('Home page text zones'));
$menuAdminPlatform[] = claro_html_tool_link('campusLog.php',        get_lang('Platform statistics'));
$menuAdminPlatform[] = claro_html_tool_link('campusProblem.php',    get_lang('Scan technical fault'));
$menuAdminPlatform[] = claro_html_tool_link('module/module_list.php', get_lang('Module list'));

$menuAdminPlatform[] = claro_html_tool_link('maintenance/repaircats.php', get_lang('Repair category structure'));
$menuAdminPlatform[] = claro_html_tool_link('upgrade/index.php',    get_lang('Upgrade'));


$menuAdminClaroline[] = claro_html_tool_link('registerCampus.php',  get_lang('Register my campus'));
$menuAdminClaroline[] = claro_html_tool_link('http://www.claroline.net/forum', get_lang('Support forum'));
$menuAdminClaroline[] = claro_html_tool_link('clarolinenews.php',              get_lang('Claroline.net news'));



if ( defined('CLAROLANG') && CLAROLANG == 'TRANSLATION') $menuAdminSDK[] = claro_html_tool_link('xtra/sdk/translation_index.php', get_lang('Translation Tools'));
if ( get_conf('DEVEL_MODE', false) == TRUE )
{
    $menuAdminSDK[] =  claro_html_tool_link('devTools', get_lang('Devel Tools'));
}


    switch ($type)
    {
        case 'AdminUser'      : { $item_list = $menuAdminUser;      } break;
        case 'AdminCourse'    : { $item_list = $menuAdminCourse;    } break;
        case 'AdminClaroline' : { $item_list = $menuAdminClaroline; } break;
        case 'AdminPlatform'  : { $item_list = $menuAdminPlatform;  } break;
        case 'AdminSDK'       : { $item_list = $menuAdminSDK;       } break;
        case 'AdminModule'    : { $item_list = $menuAdminModule;    } break;
        default : $item_list=array();
    }
    return $item_list;
}

?>