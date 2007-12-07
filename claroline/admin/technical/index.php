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
if(file_exists($includePath . '/currentVersion.inc.php')) include ($includePath . '/currentVersion.inc.php');
$is_allowedToUseSDK = $is_platformAdmin;

if (! $is_allowedToUseSDK) claro_disp_auth_form();

$nameTools = $langTechnical;

$interbredcrump[]= array ('url'=>'../index.php', 'name'=> $langAdmin);

include($includePath . '/claro_init_header.inc.php');

echo claro_disp_tool_title(
    array(
    'mainTitle'=>$nameTools
    )
    );

?>
<ul>
 <li><a href="./diskUsage.php"><?php echo $langDiskUsage ?></a></li>
 <li><a href="./phpInfo.php"><?php echo $lang_php_info ?></a></li>
</ul>

<?php
include ($includePath . '/claro_init_footer.inc.php');
?>
