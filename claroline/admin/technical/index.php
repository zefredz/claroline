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
if(file_exists($includePath . '/currentVersion.inc.php')) include ($includePath . '/currentVersion.inc.php');
$is_allowedToUseSDK = $is_platformAdmin;

if (! $is_allowedToUseSDK) claro_disp_auth_form();

$nameTools = get_lang('Technical Tools');

$interbredcrump[]= array ('url' => '../index.php', 'name' => get_lang('Admin'));

include($includePath . '/claro_init_header.inc.php');

echo claro_disp_tool_title(
    array(
    'mainTitle'=>$nameTools
    )
    );

?>
<ul>
 <li><a href="./diskUsage.php"><?php echo get_lang('Disk Usage') ?></a></li>
 <li><a href="./phpInfo.php"><?php echo get_lang('PHP system information') ?></a></li>
</ul>

<?php
include ($includePath . '/claro_init_footer.inc.php');
?>
