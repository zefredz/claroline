<?php // $Id$
/**
 * CLAROLINE
 *
 * This is the index page of sdk tools
 *
 * @version 1.8 $Revision$
 *
 * @copyright (c) 2001-2006 Universite catholique de Louvain (UCL)
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
if (file_exists($rootSys.'platform/currentVersion.inc.php')) include ($rootSys.'platform/currentVersion.inc.php');

$is_allowedToUseSDK = $is_platformAdmin;

if (! $is_allowedToUseSDK) claro_disp_auth_form();

if ( get_conf('DEVEL_MODE',false))
{
    $devtoolsList = array();
    if (file_exists('./fillUser.php'))        $devtoolsList[] = claro_html_tool_link('fillUser.php',  get_lang('Create fake users'));
    if (file_exists('./fillCourses.php'))     $devtoolsList[] = claro_html_tool_link('fillCourses.php',  get_lang('Create fake courses'));
    if (file_exists('./fillTree.php'))        $devtoolsList[] = claro_html_tool_link('fillTree.php',  get_lang('Create fake categories'));
    if (file_exists('./fillToolCourses.php')) $devtoolsList[] = claro_html_tool_link('fillToolCourses.php',  get_lang('Create item into courses tools'));
}

$nameTools = get_lang('Devel Tools');

$interbredcrump[]= array ('url' => '../index.php', 'name' => get_lang('Admin'));

include($includePath.'/claro_init_header.inc.php');

echo claro_html_tool_title($nameTools);

// TODO use claro_disp_title
?>
<h4><?php echo get_lang('Translations') ?></h4>
<ul>
    <li><a href="../xtra/sdk/translation_index.php"><?php echo get_lang('Translations') ?></a></li>
</ul>
<?php
if ( 0 < count($devtoolsList))
{
    echo  claro_html_tool_title(get_lang('Filling'))
    .     claro_html_menu_vertical($devtoolsList)
    ;
}
include $includePath . '/claro_init_footer.inc.php';
?>