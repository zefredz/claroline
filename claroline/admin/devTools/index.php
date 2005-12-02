<?php // $Id$
/**
 * CLAROLINE
 *
 * This is the index page of sdk tools
 *
 * @version 1.7 $Revision$
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
if(file_exists($includePath.'/currentVersion.inc.php')) include ($includePath.'/currentVersion.inc.php');

$is_allowedToUseSDK = $is_platformAdmin;

if (! $is_allowedToUseSDK) claro_disp_auth_form(); 

$nameTools = get_lang('DevTools');

$interbredcrump[]= array ('url' => '../index.php', 'name' => get_lang('Admin'));

include($includePath.'/claro_init_header.inc.php');

echo claro_disp_tool_title(
    array(
    'mainTitle'=>$nameTools
    )
    );

?>
<h4><?php echo get_lang('Translations') ?></h4>
<ul>
    <li><a href="../xtra/sdk/translation_index.php"><?php echo get_lang('Translations') ?></a></li>
</ul>
<h4><?php echo get_lang('Filling') ?></h4>
<ul>
 <li><a href="./fillUser.php"><?php echo get_lang('FillUsers') ?></a></li>
 <li><a href="./fillCourses.php"><?php echo get_lang('FillCourses') ?></a>(and  subscribe some existing students)</li>
 <li><a href="./fillTree.php"><?php echo get_lang('FillTree') ?></a></li>
 <li><a href="./fillToolCourses.php"><?php echo get_lang('FillToolCourses') ?></a></li>
</ul>

<?php
include $includePath . '/claro_init_footer.inc.php';
?>
