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

if ( ! claro_is_user_authenticated() ) claro_disp_auth_form();
if ( ! claro_is_platform_admin() ) claro_die(get_lang('Not allowed'));

require_once get_path('incRepositorySys') . '/lib/admin.lib.inc.php';

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

// TODO : this unset would disapear
unset($_SESSION['admin_user_search']);
unset($_SESSION['admin_user_firstName']);
unset($_SESSION['admin_user_lastName']);
unset($_SESSION['admin_user_userName']);
unset($_SESSION['admin_user_mail']);
unset($_SESSION['admin_user_action']);
unset($_SESSION['admin_order_crit']);

$dialogBox = new DialogBox();

$menu['AdminUser']      = get_menu_item_list('AdminUser');
$menu['AdminCourse']    = get_menu_item_list('AdminCourse');
$menu['AdminClaroline'] = get_menu_item_list('AdminClaroline');
$menu['AdminPlatform']  = get_menu_item_list('AdminPlatform');
$menu['AdminTechnical'] = get_menu_item_list('AdminTechnical');
$menu['Communication']  = get_menu_item_list('Communication');


//----------------------------------
// DISPLAY
//----------------------------------

// Deal with interbredcrumps  and title variable

$nameTools = get_lang('Administration');

//  no sense because not allowed with claro_is_platform_admin()
// but  claro_is_platform_admin() would be later replaced by get_user_property ('can view admin menu')
$is_allowedToAdmin     = claro_is_platform_admin();

// ----- is install visible ----- begin
if ( file_exists('../install/index.php') && ! file_exists('../install/.htaccess'))
{
    $dialogBox->warning( get_block('blockWarningRemoveInstallDirectory') );
}

// ----- is install visible ----- end

$register_globals_value = ini_get('register_globals');

if ( ! empty($register_globals_value) && strtolower($register_globals_value) != 'off' )
{
    $dialogBox->warning( get_lang('<b>Security :</b> We recommend to set register_globals to off in php.ini') );
}

include get_path('incRepositorySys') . '/claro_init_header.inc.php';
echo claro_html_tool_title($nameTools)
.    $dialogBox->render()
.    "\n\n"
;

echo '<table cellspacing="5" align="center">' . "\n"

.    '<tr valign="top">' . "\n"
.    '<td nowrap="nowrap">' . "\n"
.    claro_html_tool_title('<img src="' . get_icon_url('user') . '" alt="" />&nbsp;'.get_lang('Users'))
.    claro_html_menu_vertical($menu['AdminUser'])
.    '</td>' . "\n"
.    '<td nowrap="nowrap">'
.    claro_html_tool_title('<img src="' . get_icon_url('course') . '" alt="" />&nbsp;'.get_lang('Courses'))
.    claro_html_menu_vertical($menu['AdminCourse']) . "\n"
.    '</td>' . "\n"
.    '</tr>' . "\n"

.    '<tr valign="top">' . "\n"
.    '<td nowrap="nowrap">' . "\n"
.    claro_html_tool_title('<img src="' . get_icon_url('settings') . '" alt="" />&nbsp;'.get_lang('Platform')) . "\n"
.    claro_html_menu_vertical($menu['AdminPlatform']) . "\n"
.    '</td>' . "\n"
.    '<td nowrap="nowrap">' . "\n"
.    claro_html_tool_title('<img src="' . get_icon_url('claroline') . '" alt="" />&nbsp;Claroline.net')
.    claro_html_menu_vertical($menu['AdminClaroline'])
.    '</td>' . "\n"
.    '</tr>' . "\n"

.    '<tr valign="top">' . "\n"
.    '<td nowrap="nowrap">' . "\n"
.    claro_html_tool_title('<img src="' . get_icon_url('exe') . '" alt="" />&nbsp;' . get_lang('Tools'))
.    claro_html_menu_vertical($menu['AdminTechnical'])
.    '</td>' . "\n"
.    '<td nowrap="nowrap">' . "\n"
.    claro_html_tool_title('<img src="' . get_icon_url('mail_close') . '" alt="" />&nbsp;'.get_lang('Communication'))
.    claro_html_menu_vertical($menu['Communication'])
.    '</td>' . "\n"
.    '</tr>'
;

?>
</table>
<?php
include get_path('incRepositorySys') . '/claro_init_footer.inc.php';

function get_menu_item_list($type)
{

    static $menu = null;

    // set static menu
    if(is_null($menu))
    {

        $menu['AdminUser'][] =  '<form name="searchUser" action="adminusers.php" method="get" >' . "\n"
        .                   '<label for="search_user">' . get_lang('User') . '</label>'
        .                   ' : '
        .                   '<input name="search" id="search_user" />&nbsp;'
        .                   '<input type="submit" value="' . get_lang('Search') . '" />'
        .                   '&nbsp;'
        .                   '<small>'
        .                   '<a href="advancedUserSearch.php">'
        .                   get_lang('Advanced')
        .                   '</a>'
        .                   '</small>'
        .                   '</form>'
        ;

        $menu['AdminUser'][] = claro_html_tool_link('adminusers.php',       get_lang('User list'));
        $menu['AdminUser'][] = claro_html_tool_link('../messaging/sendmessage.php?cmd=rqMessageToAllUsers', get_lang('Send a message to all users'));
        $menu['AdminUser'][] = claro_html_tool_link('adminaddnewuser.php',  get_lang('Create user'));
        $menu['AdminUser'][] = claro_html_tool_link('../user/AddCSVusers.php?AddType=adminTool', get_lang('Add a user list'));
        $menu['AdminUser'][] = claro_html_tool_link('admin_class.php',      get_lang('Manage classes'));
        $menu['AdminUser'][] = claro_html_tool_link('right/profile_list.php', get_lang('Right profile list'));
        $menu['AdminUser'][] = claro_html_tool_link('../desktop/config.php', get_lang('Manage user desktop'));
        $menu['AdminUser'][] = claro_html_tool_link('adminmergeuser.php', get_lang('Merge user accounts ') . ' (EXPERIMENTAL !!!)');

        $menu['AdminCourse'][] = '<form name="searchCourse" action="admincourses.php" method="get" >' . "\n"
        .                    '<label for="search_course">' . get_lang('Course') . '</label> :' . "\n"
        .                    '<input name="search" id="search_course" />&nbsp;'
        .                    '<input type="submit" value="' . get_lang('Search'). '" />'
        .                    '&nbsp;<small><a href="advancedCourseSearch.php">' . get_lang('Advanced') . '</a></small>' . "\n"
        .                    '</form>'
        ;

        $menu['AdminCourse'][] = claro_html_tool_link('admincourses.php',                   get_lang('Course list'));
        $menu['AdminCourse'][] = claro_html_tool_link('../course/create.php?adminContext=1', get_lang('Create course'));
        $menu['AdminCourse'][] = claro_html_tool_link('admincats.php',                      get_lang('Manage course categories'));


        $menu['AdminPlatform'][] = claro_html_tool_link('tool/config_list.php', get_lang('Configuration'));
        $menu['AdminPlatform'][] = claro_html_tool_link('managing/editFile.php',get_lang('Edit text zones'));
        $menu['AdminPlatform'][] = claro_html_tool_link('module/module_list.php', get_lang('Modules'));
        $menu['AdminPlatform'][] = claro_html_tool_link('adminmailsystem.php', get_lang('Manage administrator email notifications'));
        $menu['AdminPlatform'][] = claro_html_tool_link('../tracking/platformReport.php',        get_lang('Platform statistics'));
        $menu['AdminPlatform'][] = claro_html_tool_link('campusProblem.php',    get_lang('Scan technical fault'));
        if (file_exists(dirname(__FILE__) . '/maintenance/checkmails.php'))
        $menu['AdminPlatform'][] = claro_html_tool_link('maintenance/checkmails.php', get_lang('Check and Repair emails of users'));
        // Broken $menu['AdminPlatform'][] = claro_html_tool_link('maintenance/repaircats.php', get_lang('Repair category structure'));
        //$menu['AdminPlatform'][] = claro_html_tool_link('adminmailsystem.php', get_lang('Choose messages dest'));
        $menu['AdminPlatform'][] = claro_html_tool_link('upgrade/index.php',    get_lang('Upgrade'));


        $menu['AdminClaroline'][] = claro_html_tool_link('registerCampus.php',  get_lang('Register my campus'));
        $menu['AdminClaroline'][] = claro_html_tool_link('http://forum.claroline.net/', get_lang('Support forum'));
        $menu['AdminClaroline'][] = claro_html_tool_link('clarolinenews.php',              get_lang('Claroline.net news'));

        $menu['AdminTechnical'][] = claro_html_tool_link('technical/diskUsage.php',  get_lang('Disk usage'));
        $menu['AdminTechnical'][] = claro_html_tool_link('technical/phpInfo.php',    get_lang('System Info'));

        if ( get_conf('DEVEL_MODE', false) == TRUE )
        {
            $menu['AdminTechnical'][] = claro_html_tool_link('xtra/sdk/translation_index.php', get_lang('Translation Tools'));
            $menu['AdminTechnical'][] =  claro_html_tool_link('devTools', get_lang('Devel Tools'));
        }

        $menu['Communication'][] = '<a href="../messaging/admin.php">'.get_lang('Internal messaging').'</a>';

    }



    if (array_key_exists($type,$menu )) $item_list = $menu[$type];
    else                                $item_list=array();


    return $item_list;
}

?>