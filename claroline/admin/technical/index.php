<?php # $Id$
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.4.1 $Revision$                             |
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2003 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
      | Authors: Thomas Depraetere <depraetere@ipm.ucl.ac.be>                |
      |          Hugues Peeters    <peeters@ipm.ucl.ac.be>                   |
      |          Christophe Gesché <gesche@ipm.ucl.ac.be>                    |
      +----------------------------------------------------------------------+
 */

$langFile = "admin.technical";
require '../../inc/claro_init_global.inc.php';

$nameTools = $langTechnical;
$interbredcrump[]= array ("url"=>$rootAdminWeb, "name"=> $langAdministrationTools);

$htmlHeadXtra[] = "<style type=\"text/css\">
<!--
-->
</STYLE>";
include($includePath."/lib/text.lib.php");
include($includePath."/lib/debug.lib.inc.php");
$dateNow 			= claro_format_locale_date($dateTimeFormatLong);
$is_allowedToAdmin 	= $is_platformAdmin || $PHP_AUTH_USER;

// make here some  test
// $checkMsgs[] = array("level" => 5, "target" => "test 1 ", "content" => "this is  just  a  warning test 1 ");

// ----- is install visible ----- begin
 if (file_exists("../../install/index.php") && !file_exists("../../install/.htaccess"))
 {
	 $controlMsg["warning"][]="install is not protected";
 }
// ----- is install visible ----- end


include($includePath."/claro_init_header.inc.php");
claro_disp_tool_title($nameTools);
include($rootAdminSys."checkIfHtAccessIsPresent.php");
claro_disp_msg_arr($controlMsg); ?>
	<UL>
		<LI>
			<a href="<?php echo $phpMyAdminWeb?>">PHP My Admin</a>
		</LI>
		<LI>
			<a href="config.php"><?php echo $langConfig ?></a>
		</LI>
				<?php
			if (!$stable||$dev)
			{
		?>
		<LI>
			<a href="checkCourseDatabase.php"><?php echo $langCheckCourseDatabase ?></a>
		</LI>
<?php
			}
?>
		<LI>
			<a href="phpInfo.php"><?php echo $langInfoServer ?></a>
		</LI>
	</UL>
<?php
include($includePath."/claro_init_footer.inc.php");
?>
