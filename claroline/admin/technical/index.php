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
if(file_exists(get_path('rootSys').'platform/currentVersion.inc.php')) include (get_path('rootSys').'platform/currentVersion.inc.php');
$is_allowedToUseSDK = claro_is_platform_admin();

if (! $is_allowedToUseSDK) claro_disp_auth_form();

$nameTools = get_lang('Technical Tools');

ClaroBreadCrumbs::getInstance()->prepend( get_lang('Administration'), get_path('rootAdminWeb') );

include get_path('incRepositorySys') . '/claro_init_header.inc.php';

echo claro_html_tool_title(
    array(
    'mainTitle'=>$nameTools
    )
    );


// TODO : cuse claro disp menu v
?>
<ul>
 <li><a href="./diskUsage.php"><?php echo get_lang('Disk usage') ?></a></li>
 <li><a href="./phpInfo.php"><?php echo get_lang('PHP system information') ?></a></li>
</ul>

<?php
include (get_path('incRepositorySys') . '/claro_init_footer.inc.php');
?>