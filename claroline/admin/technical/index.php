<?php // $Id$
/**
 * CLAROLINE
 *
 * This is the index page of sdk tools
 *
 * @version 1.9 $Revision$
 *
 * @copyright (c) 2001-2009 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @package SDK
 *
 * @author Claro Team <cvs@claroline.net>
 * @author Christophe Gesché <moosh@claroline.net>
 * @author Dimitri Rambout <dimitri.rambout@uclouvain.be>
 *
 */

require '../../inc/claro_init_global.inc.php';
if(file_exists(get_path('rootSys').'platform/currentVersion.inc.php')) include (get_path('rootSys').'platform/currentVersion.inc.php');
$is_allowedToUseSDK = claro_is_platform_admin();

if (! $is_allowedToUseSDK) claro_disp_auth_form();

$nameTools = get_lang('Technical Tools');

ClaroBreadCrumbs::getInstance()->prepend( get_lang('Administration'), get_path('rootAdminWeb') );

$out = '';

$out .= claro_html_tool_title(
    array(
    'mainTitle'=>$nameTools
    )
    );


// TODO : cuse claro disp menu v
$out .= '<ul>
  <li><a href="./diskUsage.php">' . get_lang('Disk usage') . '</a></li>
  <li><a href="./phpInfo.php">' . get_lang('PHP system information') . '</a></li>
 </ul>'
 ;

$claroline->display->body->appendContent($out);

echo $claroline->display->render();

?>