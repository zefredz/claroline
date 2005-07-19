<?php // $Id$
//----------------------------------------------------------------------
// CLAROLINE
//----------------------------------------------------------------------
// Copyright (c) 2001-2004 Universite catholique de Louvain (UCL)
//----------------------------------------------------------------------
// This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
// as published by the FREE SOFTWARE FOUNDATION. The GPL is available
// through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
//----------------------------------------------------------------------
// Authors: see 'credits' file
//----------------------------------------------------------------------
$cidReset=TRUE;
$gidReset=TRUE;
require '../inc/claro_init_global.inc.php';

if(file_exists($includePath.'/currentVersion.inc.php')) include ($includePath.'/currentVersion.inc.php');
include($includePath.'/lib/admin.lib.inc.php');

//SECURITY CHECK

if (!$is_platformAdmin) claro_disp_auth_form();

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


// clean session if we come from a course

unset($_SESSION['_cid']);
unset($_cid);

//----------------------------------
// DISPLAY
//----------------------------------

// Deal with interbredcrumps  and title variable

$nameTools = $langAdministration;

include($includePath."/lib/debug.lib.inc.php");
$dateNow             = claro_disp_localised_date($dateTimeFormatLong);
$is_allowedToAdmin     = $is_platformAdmin;

// ----- is install visible ----- begin
if ( file_exists('../install/index.php') && ! file_exists('../install/.htaccess'))
{
     $controlMsg = '<p class="highlight">' . $langNoticeInstallFolderBrowsable . '</p>';
}
// ----- is install visible ----- end

include($includePath.'/claro_init_header.inc.php');
echo claro_disp_tool_title($nameTools);

if ( !empty($controlMsg) ) echo '<blockquote>' . $controlMsg . '</blockquote>';

?>
<h4><?php echo $langUsers?></h4>
<ul>
<li>
<form name="searchUser" action="adminusers.php" method="GET" >
<label for="search_user"><?php echo $langUser?></label> 
: 
<input name="search" id="search_user"> 
<input type="submit" value="<?php echo $langSearch?>">
&nbsp;&nbsp;[<a class="claroCmd" href="advancedUserSearch.php"><?php echo $langAdvanced?></a>]
</form>
<li>
<a href="adminusers.php"><?php echo $langListUsers?></a>
</li>
</li>
<li>
<a href="adminaddnewuser.php"><?php echo $langCreateUser?></a>
</li>
<li>
<a href="admin_class.php"><?php echo $langManageClasses?></a>
</li>
<li>
<a href="../user/AddCSVusers.php?AddType=adminTool"><?php echo $langAddCSVUsers?></a>
</li>
</ul>

<h4><?php echo $langCourses?></h4>
<ul>
<li>
<form name="searchCourse" action="admincourses.php" method="GET" >
<label for="search_course"><?php echo $langCourse?></label> : <input name="search" id="search_course"> <input type="submit" value="<?php echo $langSearch?>">
&nbsp; &nbsp;[<a class="claroCmd" href="advancedCourseSearch.php"><?php echo $langAdvanced?></a>]
</form>
</li>
<li>
<a href="admincourses.php"><?php echo $langCourseList?></a>
</li>
<li>
<a href="../create_course/add_course.php?fromAdmin=yes"><?php echo $langCreateCourse?></a><br>
</li>
<li>
<a href="admincats.php"><?php echo $langManageCourseCategories?></a>
</li>
</ul>

<h4><?php echo $langPlatform?></h4>
<ul>
<li>
<a href="tool/config_list.php"><?php echo $langConfiguration?></a>
</li>
<li>
<a href="managing/editFile.php"><?php echo $langHomePageTextZone ?></a>
</li>
<li>
<a href="campusLog.php"><?php echo $langViewPlatFormStatistics?></a>
</li>
<li>
<a href="campusProblem.php"><?php echo $langViewPlatFormError ?></a>
</li>
<li>
<a href="upgrade/index.php"><?php echo $langUpgrade?></a>
</li>
</ul>

<h4>Claroline.net</h4>
<ul>
<li>
<a href="registerCampus.php"><?php echo $langRegisterMyCampus; ?></a>
</li>
<li>
<a href="http://www.claroline.net/forum/"><?php echo $langSupportForum; ?></a>
</li>
<li>
<a href="clarolinenews.php"><?php echo $langClarolineNetNews; ?></a>
</li>
</ul>

<?php
if ( ( defined('DEVEL_MODE') && DEVEL_MODE == TRUE )
|| ( defined('CLAROLANG') && CLAROLANG == 'TRANSLATION') )
{
?>
    <h4><?php echo $langSDK?></h4>
    <ul>
<?php
}

if ( defined('CLAROLANG') && CLAROLANG == 'TRANSLATION')
{
?>
    <li><a href="xtra/sdk/translation_index.php"><?php echo $langTranslationTools?></a></li>
<?php
}
if ( defined('DEVEL_MODE') && DEVEL_MODE == TRUE )
{
?>
    <li><a href="devTools/"><?php echo $langDevTools ?></a></li>
    <li><a href="technical/"><?php echo $langTechnical ?></a></li>
<?php
}
?>
</ul>
<?php
include($includePath.'/claro_init_footer.inc.php');
?>
