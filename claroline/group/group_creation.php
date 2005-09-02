<?php // $Id$
/** 
 * CLAROLINE 
 *
 * This script display form to parameter a creation of group
 *
 * @version 1.7 $Revision$
 *
 * @copyright 2001-2005 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE 
 *
 * @see http://www.claroline.net/wiki/index.php/CLGRP
 *
 * @package CLGRP
 *
 * @author Claro Team <cvs@claroline.net>
 *
 */
require '../inc/claro_init_global.inc.php'; 
if ( ! $_cid) claro_disp_select_course();

$nameTools = $langGroupCreation;
$interbredcrump[]= array ('url' => 'group.php', 'name'=> $langGroups);
include($includePath . '/claro_init_header.inc.php');

echo claro_disp_tool_title(array('supraTitle' => $langGroups,
                            'mainTitle' => $nameTools));

?>
<form method="post" action="group.php">
<input type="hidden" name="claroFormId" value="<?php echo uniqid(''); ?>">

<table>

<tr valign="top">
<td>
<label for="group_quantity"><?php echo $langCreate?></label>
</td>
<td>
<input type="text" name="group_quantity" id="group_quantity" size="3" value="1">
<label for="group_quantity"><?php echo $langNewGroups ?></label>
</td>
</tr>

<tr valign="top">
<td>
<label for="group_max"><?php echo $langMax ?></label>
</td>
<td>
<input type="text" name="group_max" id="group_max" size="3" value="8">
<?php echo $langPlaces ?>
</td>
</tr>

<tr>
<td>
<label for="creation">
<?php echo $langCreate ?>
</label>
</td>
<td>
<input type="submit" value=<?php echo $langOk ?> name="creation" id="creation"> 
<?php echo claro_disp_button($_SERVER['HTTP_REFERER'], $langCancel); ?>
</td>
</tr>

</table>
</form>
<?php
include($includePath . '/claro_init_footer.inc.php');
?>