<?php // $Id$
/**
 * @version CLAROLINE 1.6
 *
 * @copyright (c) 2001-2005 Universite catholique de Louvain (UCL)
 *
 * @license GENERAL PUBLIC LICENSE (GPL)
 *
 * @author Christophe Gesché <moosh@claroline.net>
 * 
 * This is the index page of sdk tools
*/
$langFillToolCourses = 'Fill tools of a course (lorem ipsum filler)';
require '../../inc/claro_init_global.inc.php';

$is_allowedToUseSDK 	= $is_platformAdmin;
if ($is_allowedToUseSDK) claro_disp_auth_form(); 

$nameTools = $langDevTools;
$interbredcrump[]= array ('url'=>'../index.php', 'name'=> $langAdmin);
@include('../checkIfHtAccessIsPresent.php');
include($includePath.'/claro_init_header.inc.php');
claro_disp_tool_title(
	array(
	'mainTitle'=>$nameTools,
	'subTitle'=>$siteName.' - '.$clarolineVersion
	)
	);
claro_disp_msg_arr($controlMsg);
if ($is_allowedToUseSDK)
{
?>
<H4>
	<?php echo $langTranslations ?>
</H4>
<ul>
	<LI>
		<a href="../xtra/sdk/translation_index.php"><?php echo $langTranslations ?></a>
	</LI>
</uL>
<H4><?php echo $langFilling ?></H4>
<UL>
	<LI>
		<a href="./fillUser.php"><?php echo $langFillUsers ?></a>
	</LI>
	<LI>
		<a href="./fillCourses.php"><?php echo $langFillCourses ?></a>(and  subscribe some existing students)
	</LI>
	<LI>
		<a href="./fillTree.php"><?php echo $langFillTree ?></a>
	</LI>
	<LI>
		<a href="./fillToolCourses.php"><?php echo $langFillToolCourses ?></a>
	</LI>
</UL>
<?php

}
else
{
	echo $lang_no_access_here;
}
include($includePath."/claro_init_footer.inc.php");
?>
