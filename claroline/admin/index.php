<?php // $Id$

/**
 * CLAROLINE
 *
 * Admin panel.
 *
 * @version     $Revision$
 * @copyright   (c) 2001-2011, Universite catholique de Louvain (UCL)
 * @license     http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @package     ADMIN
 * @author      claro team <cvs@claroline.net>
 */

$cidReset = true;
$gidReset = true;
require '../inc/claro_init_global.inc.php';

// Security check
if ( !claro_is_user_authenticated() ) claro_disp_auth_form();
if ( !claro_is_platform_admin() ) claro_die(get_lang('Not allowed'));

require_once get_path('incRepositorySys') . '/lib/admin.lib.inc.php';

//------------------------
//  USED SESSION VARIABLES
//------------------------

// Clean session of possible previous search information (COURSE)
unset($_SESSION['admin_course_code']);
unset($_SESSION['admin_course_search']);
unset($_SESSION['admin_course_intitule']);
unset($_SESSION['admin_course_category']);
unset($_SESSION['admin_course_language']);
unset($_SESSION['admin_course_access']);
unset($_SESSION['admin_course_subscription']);
unset($_SESSION['admin_course_order_crit']);


// Deal with session variables clean session variables from previous search (USER)

// TODO : these unset should disappear
unset($_SESSION['admin_user_search']);
unset($_SESSION['admin_user_firstName']);
unset($_SESSION['admin_user_lastName']);
unset($_SESSION['admin_user_userName']);
unset($_SESSION['admin_user_mail']);
unset($_SESSION['admin_user_action']);
unset($_SESSION['admin_order_crit']);

$dialogBox = new DialogBox();

// Set the administration menus
// ============================

// Users' administration menu
$menu['AdminUser'][] = '<form name="searchUser" action="admin_users.php" method="get" >' . "\n"
                     . '<label for="search_user">' . get_lang('User') . '</label><br />'
                     . '<input name="search" id="search_user" />&nbsp;'
                     . '<input type="submit" value="' . get_lang('Search') . '" />'
                     . '&nbsp;'
                     . '<small>'
                     . '<a href="advanced_user_search.php">'
                     . get_lang('Advanced')
                     . '</a>'
                     . '</small>'
                     . '</form>';

$menu['AdminUser'][] = claro_html_tool_link('admin_users.php', get_lang('User list'));
$menu['AdminUser'][] = claro_html_tool_link('../messaging/sendmessage.php?cmd=rqMessageToAllUsers', get_lang('Send a message to all users'));
$menu['AdminUser'][] = claro_html_tool_link('adminaddnewuser.php', get_lang('Create user'));
$menu['AdminUser'][] = claro_html_tool_link('../user/AddCSVusers.php?AddType=adminTool', get_lang('Add a user list'));
$menu['AdminUser'][] = claro_html_tool_link('admin_class.php', get_lang('Manage classes'));
$menu['AdminUser'][] = claro_html_tool_link('right/profile_list.php', get_lang('Right profile list'));
$menu['AdminUser'][] = claro_html_tool_link('../desktop/config.php', get_lang('Manage user desktop'));
$menu['AdminUser'][] = claro_html_tool_link('adminmergeuser.php', get_lang('Merge user accounts') );

// Courses' administration menu
$menu['AdminCourse'][] = '<form name="searchCourse" action="admin_courses.php" method="get" >' . "\n"
                       . '<label for="search_course">' . get_lang('Course') . '</label><br />' . "\n"
                       . '<input name="search" id="search_course" />&nbsp;'
                       . '<input type="submit" value="' . get_lang('Search'). '" />'
                       . '&nbsp;<small><a href="advanced_course_search.php">' . get_lang('Advanced') . '</a></small>' . "\n"
                       . '</form>';

$menu['AdminCourse'][] = claro_html_tool_link('admin_courses.php', get_lang('Course list'));
$menu['AdminCourse'][] = claro_html_tool_link('../course/create.php?adminContext=1', get_lang('Create course'));
$menu['AdminCourse'][] = claro_html_tool_link('admin_category.php', get_lang('Manage course categories'));

// Platform's administration menu
$menu['AdminPlatform'][] = claro_html_tool_link('tool/config_list.php', get_lang('Configuration'));
$menu['AdminPlatform'][] = claro_html_tool_link('managing/editFile.php',get_lang('Edit text zones'));
$menu['AdminPlatform'][] = claro_html_tool_link('module/module_list.php', get_lang('Modules'));
$menu['AdminPlatform'][] = claro_html_tool_link('adminmailsystem.php', get_lang('Manage administrator email notifications'));
$menu['AdminPlatform'][] = claro_html_tool_link('../tracking/platformReport.php', get_lang('Platform statistics'));
$menu['AdminPlatform'][] = claro_html_tool_link('campusProblem.php', get_lang('Scan technical fault'));

if (file_exists(dirname(__FILE__) . '/maintenance/checkmails.php'))
{
    $menu['AdminPlatform'][] = claro_html_tool_link('maintenance/checkmails.php', get_lang('Check and Repair emails of users'));
}
$menu['AdminPlatform'][] = claro_html_tool_link('upgrade/index.php', get_lang('Upgrade'));

// Claroline's administration menu
$menu['AdminClaroline'][] = claro_html_tool_link('registerCampus.php', get_lang('Register my campus'));
$menu['AdminClaroline'][] = claro_html_tool_link('http://forum.claroline.net/', get_lang('Support forum'));
$menu['AdminClaroline'][] = claro_html_tool_link('clarolinenews.php', get_lang('Claroline.net news'));

// Technical's administration menu
$menu['AdminTechnical'][] = claro_html_tool_link('technical/phpInfo.php', get_lang('System Info'));
$menu['AdminTechnical'][] = claro_html_tool_link('technical/files_stats.php', get_lang('Files Info'));

if ( get_conf('DEVEL_MODE', false) == true )
{
    $menu['AdminTechnical'][] = claro_html_tool_link('xtra/sdk/translation_index.php', get_lang('Translation Tools'));
    $menu['AdminTechnical'][] = claro_html_tool_link('devTools', get_lang('Devel Tools'));
}

// Communication's administration menu
$menu['Communication'][] = '<a href="../messaging/admin.php">'.get_lang('Internal messaging').'</a>';

// Extra tools' administration menu
$tbl = claro_sql_get_main_tbl();

$sql = "SELECT `label`, `name`\n"
     . "FROM `{$tbl['module']}`\n"
     . "WHERE `type` = 'admin'\n"
     . "AND `activation` = 'activated'";

$adminModuleList = Claroline::getDatabase()->query($sql);

if ($adminModuleList->count() > 0)
{
    foreach ( $adminModuleList as $module )
    {
        language::load_module_translation($module['label']);
        $menu['ExtraTools'][] = '<a href="'.get_module_entry_url($module['label']).'">'.get_lang($module['name']).'</a>';
    }
}

// Deal with interbreadcrumbs and title variable
$nameTools = get_lang('Administration');

// No sense because not allowed with claro_is_platform_admin(),
// but claro_is_platform_admin() should be later replaced by
// get_user_property ('can view admin menu')
$is_allowedToAdmin     = claro_is_platform_admin();

// Is our installation system accessible ?
if (file_exists('../install/index.php') && ! file_exists('../install/.htaccess'))
{
    // If yes, warn the administrator
    $dialogBox->warning(get_block('blockWarningRemoveInstallDirectory'));
}

$register_globals_value = ini_get('register_globals');

// Is the php 'register_globals' param enable ?
if (!empty($register_globals_value) && strtolower($register_globals_value) != 'off')
{
    // If yes, warn the administrator
    $dialogBox->warning(get_lang('<b>Security :</b> We recommend to set register_globals to off in php.ini'));
}

$template = new CoreTemplate('admin_panel.tpl.php');
$template->assign('dialogBox', $dialogBox);
$template->assign('menu', $menu);

$claroline->display->body->appendContent($template->render());

echo $claroline->display->render();