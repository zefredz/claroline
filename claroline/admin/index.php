<?php // $Id$
/**
 * CLAROLINE
 * @version 1.7 $Revision$
 *
 * @copyright (c) 2001-2005 Universite catholique de Louvain (UCL)
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
if ( ! $is_platformAdmin ) claro_die($langNotAllowed);

require_once $includePath . '/lib/admin.lib.inc.php';

//------------------------------------------------------------------------------------------------------------------------
//  USED SESSION VARIABLES
//------------------------------------------------------------------------------------------------------------------------
// clean session of possible previous search information. : COURSE

unset($_SESSION['admin_course_code']);
unset($_SESSION['admin_course_letter']);
unset($_SESSION['admin_course_search']);
unset($_SESSION['admin_course_intitule']);
unset($_SESSION['admin_course_category']);
unset($_SESSION['admin_course_language']);
unset($_SESSION['admin_course_access']);
unset($_SESSION['admin_course_subscription']);
unset($_SESSION['admin_course_order_crit']);


// deal with session variables clean session variables from previous search : USER

unset($_SESSION['admin_user_letter']);
unset($_SESSION['admin_user_search']);
unset($_SESSION['admin_user_firstName']);
unset($_SESSION['admin_user_lastName']);
unset($_SESSION['admin_user_userName']);
unset($_SESSION['admin_user_mail']);
unset($_SESSION['admin_user_action']);
unset($_SESSION['admin_order_crit']);

$controlMsg = array();
//----------------------------------
// DISPLAY
//----------------------------------

// Deal with interbredcrumps  and title variable

$nameTools = $langAdministration;

include $includePath . '/lib/debug.lib.inc.php';
$is_allowedToAdmin     = $is_platformAdmin;

// ----- is install visible ----- begin
if ( file_exists('../install/index.php') && ! file_exists('../install/.htaccess'))
{
     $controlMsg['warning'][] = $langNoticeInstallFolderBrowsable;
}
// ----- is install visible ----- end

$register_globals_value = ini_get('register_globals');

if ( ! empty($register_globals_value) && strtolower($register_globals_value) != 'off' )
{
    $controlMsg['warning'][] = '<b>Security :</b> We recommend to set register_globals to off in php.ini';
}

include $includePath . '/claro_init_header.inc.php';
echo claro_disp_tool_title($nameTools)
.    claro_disp_msg_arr( $controlMsg,1) . "\n\n"
;

?>
<table cellspacing="5" align="center">

<tr valign="top">

<td nowrap="nowrap">
<h4><img src="<?php echo $imgRepositoryWeb; ?>user.gif" alt="" /> <?php echo $langUsers?></h4>
<ul>
<li>
<form name="searchUser" action="adminusers.php" method="GET" >
<label for="search_user"><?php echo $langUser; ?></label>
:
<input name="search" id="search_user" />
<input type="submit" value="<?php echo $langSearch; ?>" />
&nbsp;&nbsp;<small><a href="advancedUserSearch.php"><?php echo $langAdvanced?></small></a>
</form>
<li>
<a href="adminusers.php" class="toollink"><?php echo $langListUsers?></a>
</li>
<li>
<a href="adminaddnewuser.php" class="toollink"><?php echo $langCreateUser?></a>
</li>
<li>
<a href="admin_class.php" class="toollink"><?php echo $langManageClasses?></a>
</li>
<li>
<a href="../user/AddCSVusers.php?AddType=adminTool" class="toollink"><?php echo $langAddCSVUsers?></a>
</li>
</ul>
</td>

<td nowrap="nowrap">
<h4><img src="<?php echo $imgRepositoryWeb; ?>course.gif" alt="" /> <?php echo $langCourses?></h4>
<ul>
<li>
<form name="searchCourse" action="admincourses.php" method="GET" >
<label for="search_course"><?php echo $langCourse; ?></label> :
<input name="search" id="search_course" />
<input type="submit" value="<?php echo $langSearch; ?>" />
&nbsp; &nbsp;<small><a href="advancedCourseSearch.php"><?php echo $langAdvanced?></a></small>
</form>
</li>
<li>
<a href="admincourses.php" class="toollink"><?php echo $langCourseList?></a>
</li>
<li>
<a href="../create_course/add_course.php?fromAdmin=yes" class="toollink"><?php echo $langCreateCourse?></a><br />
</li>
<li>
<a href="admincats.php" class="toollink"><?php echo $langManageCourseCategories?></a>
</li>
</ul>
</td>

</tr>
<tr valign="top">

<td nowrap="nowrap">
<h4><img src="<?php echo $imgRepositoryWeb; ?>settings.gif" alt="" /> <?php echo $langPlatform?></h4>
<ul>
<li>
<a href="tool/config_list.php" class="toollink"><?php echo $langConfiguration?></a>
</li>
<li>
<a href="managing/editFile.php" class="toollink"><?php echo $langHomePageTextZone ?></a>
</li>
<li>
<a href="campusLog.php" class="toollink"><?php echo $langViewPlatFormStatistics?></a>
</li>
<li>
<a href="campusProblem.php" class="toollink"><?php echo $langViewPlatFormError ?></a>
</li>
<li>
<a href="maintenance/repaircats.php" class="toollink"><?php echo $langCategoriesRepairs ?></a>
</li>
<li>
<a href="upgrade/index.php" class="toollink"><?php echo $langUpgrade?></a>
</li>
</ul>
</td>

<td nowrap="nowrap">
<h4><img src="<?php echo $imgRepositoryWeb; ?>claroline.gif" alt="" />&nbsp;Claroline.net</h4>
<ul>
<li>
<a href="registerCampus.php"  class="toollink" ><?php echo $langRegisterMyCampus; ?></a>
</li>
<li>
<a href="http://www.claroline.net/forum/" class="extlink" ><?php echo $langSupportForum; ?></a>
</li>
<li>
<a href="clarolinenews.php" class="extlink" ><?php echo $langClarolineNetNews; ?></a>
</li>
</ul>
</td>

</tr>

<?php
if ( ( defined('DEVEL_MODE') && DEVEL_MODE == TRUE )
|| ( defined('CLAROLANG') && CLAROLANG == 'TRANSLATION') )
{
    echo '<tr valign="top">'
    .    '<td nowrap="nowrap">'
    .    '<h4>'
    .    $langSDK
    .    '</h4>'
    .    '<ul>'
    ;
}

if ( defined('CLAROLANG') && CLAROLANG == 'TRANSLATION')
{
    echo '<li>'
    .    '<a href="xtra/sdk/translation_index.php" class="toollink">'
    .    $langTranslationTools
    .    '</a>'
    .    '</li>'
    ;
}
if ( defined('DEVEL_MODE') && DEVEL_MODE == TRUE )
{
    echo '<li>'
    .    '<a href="devTools/" class="toollink">'
    .    $langDevTools
    .    '</a>'
    .    '</li>'
    ;
}

if ( ( defined('DEVEL_MODE') && DEVEL_MODE == TRUE )
|| ( defined('CLAROLANG') && CLAROLANG == 'TRANSLATION') )
{
    // close opened tag if needed
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
?>
