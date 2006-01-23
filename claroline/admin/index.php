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

include $includePath . '/lib/debug.lib.inc.php';
$is_allowedToAdmin     = $is_platformAdmin;

// ----- is install visible ----- begin
if ( file_exists('../install/index.php') && ! file_exists('../install/.htaccess'))
{
     $controlMsg['warning'][] = get_lang('NoticeInstallFolderBrowsable');
}
// ----- is install visible ----- end

include $includePath . '/claro_init_header.inc.php';
echo claro_disp_tool_title($nameTools)
.    claro_disp_msg_arr( $controlMsg,1) . "\n\n"
;

echo '<table cellspacing="5" align="center">' . "\n"
.    '<tr valign="top">' . "\n"
.    '<td nowrap="nowrap">' . "\n"
.    claro_disp_tool_title('<img src="' . $imgRepositoryWeb . 'user.gif" alt="" />&nbsp;'.get_lang('Users'))
.    claro_disp_menu_vlist($menuAdminUser)
.    '</td>' . "\n"
.    '<td nowrap="nowrap">'
.    claro_disp_tool_title('<img src="' . $imgRepositoryWeb . 'course.gif" alt="" />&nbsp;'.get_lang('Courses'))
.    claro_disp_menu_vlist($menuAdminCourse) . "\n"
.    '</td>' . "\n"
.    '</tr>' . "\n"
.    '<tr valign="top">' . "\n"
.    '<td nowrap="nowrap">' . "\n"
.    claro_disp_tool_title('<img src="' . $imgRepositoryWeb . 'settings.gif" alt="" />&nbsp;'.get_lang('Platform')) . "\n"
.    claro_disp_menu_vlist($menuAdminPlatform) . "\n"
.    '</td>' . "\n"
.    '<td nowrap="nowrap">' . "\n"
.    claro_disp_tool_title('<img src="' . $imgRepositoryWeb . 'claroline.gif" alt="" />&nbsp;Claroline.net')
.    claro_disp_menu_vlist($menuAdminClaroline)
.    '</td>' . "\n"
.    '</tr>' . "\n"
;

if ( ( defined('DEVEL_MODE') && DEVEL_MODE == TRUE )
|| ( defined('CLAROLANG') && CLAROLANG == 'TRANSLATION') )
{
    echo '<tr valign="top">'
    .    '<td nowrap="nowrap">'
    .    claro_disp_tool_title('<img src="' . $imgRepositoryWeb . 'exe.gif" alt="" />&nbsp;'.get_lang('SDK')) . "\n"
    .    claro_disp_menu_vlist($menuAdminSDK)
    .    '<ul>'
    ;
?>
</ul>
</td>
<td nowrap="nowrap">&nbsp;</td>
</tr>
<?php
}
?>
</table>
<?php
include $includePath . '/claro_init_footer.inc.php';

function claro_disp_menu_vlist($itemList)
{
    // class="toollink"
    $htmlStream = '<UL class="menu vmenu">' . "\n";
    foreach ($itemList as $item )
    {
        $htmlStream .= '<LI>' . "\n";
        switch($item['type'])
        {
            case 'link' :
            {
                $htmlStream .= '<a href="' . $item['url'] . '" ' . $item['attribute'] . ' >'
                .              $item['label']
                .              '</a>'
                ;

            } break;
            case 'free' :
            {
                $htmlStream .= $item['stream'];
            } break;

        }
        $htmlStream .= '</LI>' . "\n"
        ;
    }
    $htmlStream .= '</UL>' . "\n";
    return $htmlStream;
}


function get_menu_item_list($type)
{

    $menuAdminUser[] =  array('type'=>'free', 'stream'=>'<form name="searchUser" action="AdminUser.php" method="GET" >' . "\n"
                                              . '<label for="search_user">' . get_lang('User') . '</label>'
                                              . ' : '
                                              . '<input name="search" id="search_user" />'
                                              . '<input type="submit" value="' . get_lang('Search') . '" />'
                                              . '&nbsp;&nbsp;'
                                              . '<small><a href="advancedUserSearch.php">' . get_lang('Advanced') . '</small></a>'
                                              . '</form>');
$menuAdminUser[] =  array('type'=>'link', 'url'=>'AdminUser.php',       'attribute'=>'class="toollink"', 'label'=>get_lang('ListUsers'));
$menuAdminUser[] =  array('type'=>'link', 'url'=>'adminaddnewuser.php',       'attribute'=>'class="toollink"', 'label'=> get_lang('CreateUser'));
$menuAdminUser[] =  array('type'=>'link', 'url'=>'admin_class.php',       'attribute'=>'class="toollink"', 'label'=>get_lang('ManageClasses'));
$menuAdminUser[] =  array('type'=>'link', 'url'=>'../user/AddCSVusers.php?AddType=adminTool',       'attribute'=>'class="toollink"', 'label'=> get_lang('AddCSVUsers'));


$menuAdminCourse[] =  array('type'=>'free', 'stream'=>'<form name="searchCourse" action="AdminCourse.php" method="GET" >' . "\n"
                                              . '<label for="search_course">' . get_lang('Course') . '</label> :' . "\n"
                                              . '<input name="search" id="search_course" />' . "\n"
                                              . '<input type="submit" value="' . get_lang('Search'). '" />' . "\n"
                                              . '&nbsp; &nbsp;<small><a href="advancedCourseSearch.php">' . get_lang('Advanced') . '</a></small>' . "\n"
                                              . '</form>');
$menuAdminCourse[] =  array('type'=>'link', 'url'=>'AdminUser.php',       'attribute'=>'class="toollink"', 'label'=>get_lang('ListUsers'));



$menuAdminCourse[] =  array('type'=>'link', 'url'=>'AdminCourse.php',       'attribute'=>'class="toollink"', 'label'=>get_lang('CourseList'));
$menuAdminCourse[] =  array('type'=>'link', 'url'=>'../create_course/add_course.php?fromAdmin=yes',       'attribute'=>'class="toollink"', 'label'=>get_lang('CreateCourse'));
$menuAdminCourse[] =  array('type'=>'link', 'url'=>'admincats.php',       'attribute'=>'class="toollink"', 'label'=>get_lang('ManageCourseCategories'));



$menuAdminPlatform[] =  array('type'=>'link', 'url'=>'tool/config_list.php',       'attribute'=>'class="toollink"', 'label'=>get_lang('Configuration'));
$menuAdminPlatform[] =  array('type'=>'link', 'url'=>'managing/editFile.php',      'attribute'=>'class="toollink"', 'label'=>get_lang('Home page text zones'));
$menuAdminPlatform[] =  array('type'=>'link', 'url'=>'campusLog.php',              'attribute'=>'class="toollink"', 'label'=>get_lang('Platform statistics'));
$menuAdminPlatform[] =  array('type'=>'link', 'url'=>'campusProblem.php',          'attribute'=>'class="toollink"', 'label'=>get_lang('Scan technical fault'));
$menuAdminPlatform[] =  array('type'=>'link', 'url'=>'maintenance/repaircats.php', 'attribute'=>'class="toollink"', 'label'=>get_lang('CategoriesRepairs'));
$menuAdminPlatform[] =  array('type'=>'link', 'url'=>'upgrade/index.php',          'attribute'=>'class="toollink"', 'label'=>get_lang('Upgrade'));


$menuAdminClaroline[] =  array('type'=>'link', 'url'=>'registerCampus.php',            'attribute'=>'class="toollink"', 'label'=>get_lang('RegisterMyCampus'));
$menuAdminClaroline[] =  array('type'=>'link', 'url'=>'http://www.claroline.net/forum','attribute'=>'class="extlink"','label'=>get_lang('SupportForum'));
$menuAdminClaroline[] =  array('type'=>'link', 'url'=>'clarolinenews.php',             'attribute'=>'class="extlink"','label'=>get_lang('Claroline.net news'));


if ( defined('CLAROLANG') && CLAROLANG == 'TRANSLATION') $menuAdminSDK[] =  array('type'=>'link', 'url'=>'xtra/sdk/translation_index.php', 'attribute'=>'class="toollink"', 'label'=>get_lang('TranslationTools'));
if ( defined('DEVEL_MODE') && DEVEL_MODE == TRUE )
{
    $menuAdminSDK[] =  array('type'=>'link', 'url' => 'devTools', 'attribute'=>'class="toollink"', 'label'=>get_lang('DevTools'));
    $menuAdminSDK[] =  array('type'=>'link', 'url' => '../tracking/allCourseLog.php', 'attribute'=>'class="toollink"', 'label'=>get_lang('refresh tracking datas'));
    $menuAdminSDK[] =  array('type'=>'link', 'url' => '../tracking/allCourseLogToCsv.php', 'attribute'=>'class="toollink"', 'label'=>get_lang('get csv of tracking'));
}


    switch ($type)
    {
        case 'AdminUser'      : { $item_list = $menuAdminUser;      } break;
        case 'AdminCourse'    : { $item_list = $menuAdminCourse;    } break;
        case 'AdminClaroline' : { $item_list = $menuAdminClaroline; } break;
        case 'AdminPlatform'  : { $item_list = $menuAdminPlatform;  } break;
        case 'AdminSDK'       : { $item_list = $menuAdminSDK;       } break;
        default : $item_list=array();
    }
    return $item_list;
}
?>