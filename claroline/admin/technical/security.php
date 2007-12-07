<?php # $Id$
/**
 *  This  script  is  young.
 *  Target :
 *  - check security
 *  - add protection
 *  - make suggestion
 *  - link to forum on claroline server.
 *
 *  default display
 * $DIPLAY_STATUS_OF_PROTECTION
 *
 * @version 1.6
 *
 * @copyright (c) 2001-2005 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE 
 *
 * @package SDK
 *
 * @author Claro Team <cvs@claroline.net>
 * @author Christophe Gesché <moosh@claroline.net>
 *
 */

require '../../inc/claro_init_global.inc.php';

$is_allowedToAdmin 	= $is_platformAdmin;
if ( ! $is_allowedToAdmin ) claro_disp_auth_form();

$nameTools = "Security";
$interbredcrump[]= array ("url"=>$rootAdminWeb, "name"=> $langAdministration);
$interbredcrump[]= array ("url"=>"index.php", "name"=> $langTechAdmin);

@include($includePath."/lib/debug.lib.inc.php");
include($includePath."/lib/fileManage.lib.php");
include ($includePath."/lib/auth.lib.inc.php");

$dateNow 			= claro_disp_localised_date($dateTimeFormatLong);
$is_allowedToAdmin 	= $is_platformAdmin || $PHP_AUTH_USER;

$htAccessName = ".htaccess";
$htPasswordPath = $clarolineAdminRepository;
$htPasswordName = ".htpasswd4admin";

$DIPLAY_STATUS_OF_PROTECTION = true;

$cmd = $_REQUEST['cmd'];

if ($cmd=="protectInc")
{

	$doProtectInc = true;

}

if ($doProtectInc)
{
	$htAccessIncPath 			= $clarolineRepository."inc/";
	if (placeHtAccessFile($htAccessIncPath, $htAccessName,$welcomeString="Restricted Area"))
	{
		$controlMsg['success'][]=$lang_htAccessIncPath_added;
	}
}

if ($doProtectAdmin)
{
	$htAccessAdminPath 			= $clarolineRepository."admin/";
	placeHtAccessFile($htAccessIncPath, $htAccessName,$welcomeString="Administration Claroline");
}

if ($doProtectCourse_home)
{
	$htAccessCourse_homePath 			= $clarolineRepository."course_home/";
	placeHtAccessFile($htAccessIncPath, $htAccessName,$welcomeString="Restricted Area");
}

if ($doProtectLang)
{
	$htAccessLangPath 			= $clarolineRepository."lang/";
	placeHtAccessFile($htAccessIncPath, $htAccessName,$welcomeString="Restricted Area");
}

if ($doProtectInstall)
{
	$htAccessInstallPath 			= $clarolineRepository."install/";
	placeHtAccessFile($htAccessIncPath, $htAccessName,$welcomeString="Restricted Area");
}

////////////DISPLAY/////////////
include($includePath."/claro_init_header.inc.php");
claro_disp_tool_title(
	array(
	'mainTitle'=>$nameTools
	)
	);
claro_disp_msg_arr($msg);

if ($DIPLAY_STATUS_OF_PROTECTION)
{
	?>


Some directory would be protect<br>
<UL>
	<LI>
		Important
		<UL>
			<LI><?php echo $clarolineRepositorySys."install/" ?></LI>
			<LI><?php echo $rootAdminSys ?></LI>
			<LI><?php echo $includePath?></LI>
			<LI><?php echo $garbageRepositorySys ?></LI>
			<LI><?php echo $clarolineRepositorySys."course_home/" ?></LI>
		</UL>
	</LI>
	<LI>
		<?php echo $langOptional ?>

		<UL>
			<LI><?php echo $clarolineRepositorySys."lang/" ?></LI>
		</UL>
	</LI>
</UL>
	<?php
}

include($includePath."/claro_init_footer.inc.php");
?>
