<?php # $Id$
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
$langMaintenance 			= "Maintenance";
$langUpgrade 				= "Upgrade claroline";
$langUpgradeAll 			= "Upgrade all the  platform.";
$langUpgradeCoursesDb 		= "Upgrade courses Databases.";
$langMakeBackupBefore 		= "Make a backup before !!!";
$langExplainUpgradeCourses 	= "Do it after the restore of an old course.";

$langFile = "admin.maintenance.menu";

@include ("../../inc/installedVersion.inc.php");
$thisClarolineVersion 	= $clarolineVersion;
$thisVersionDb 			= $versionDb;

require '../../inc/claro_init_global.inc.php';
include($includePath."/lib/text.lib.php");
include($includePath."/lib/debug.lib.inc.php");

$nameTools = $langMaintenance;
$dateNow 			= claro_format_locale_date($dateTimeFormatLong);
$interbredcrump[]= array ("url"=>$rootAdminWeb, "name"=> $langAdministrationTools);
// $htmlHeadXtra[] = '';
$is_allowedToAdmin 	= $is_platformAdmin || $PHP_AUTH_USER;
// make here some  test
// $checkMsgs[] = array("level" => 5, "target" => "test 1 ", "content" => "this is  just  a  warning test 1 ");
// ----- is install visible ----- begin
 if (file_exists("../install/index.php") && !file_exists("../install/.htaccess"))
 {
	 $controlMsg["warning"][]="install is not protected";
 }
// ----- is install visible ----- end




include($includePath."/claro_init_header.inc.php");
claro_disp_tool_title(
	array(
	'mainTitle'=>$nameTools,
	'subTitle'=>$langUpgrade." - ".$PHP_AUTH_USER." - ".$siteName." - ".$clarolineVersion." - ".$dateNow
	)
	);
claro_disp_msg_arr($controlMsg); ?>
<UL>
	<LI>
		<a href="upgrade.php" ><?php echo $langUpgradeAll; ?></a>
<?php
if ($thisClarolineVersion==$clarolineVersion)
{
	echo "<font style=\"success\" color=\"green\">all ready done</font>";
}
else
{
	echo "<font style=\"warn\" color=\"red\">Do it $thisClarolineVersion != $clarolineVersion</font>";
}
?>
<div class="error"><?php echo $langMakeBackupBefore ?></div>
	</LI>
</UL>
<?php
include($includePath."/claro_init_footer.inc.php");
?>
