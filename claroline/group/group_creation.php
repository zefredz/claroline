<?php // $Id$
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.4.0 $Revision$                            |
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2003 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
      | Authors: Thomas Depraetere <depraetere@ipm.ucl.ac.be>                |
      |          Hugues Peeters    <peeters@ipm.ucl.ac.be>                   |
      |          Christophe Gesché <gesche@ipm.ucl.ac.be>                    |
      +----------------------------------------------------------------------+
	  |  This is  just a script tou  print out the for.                      |
	  |  There is no data working.                                           |
      +----------------------------------------------------------------------+
 */
/**************************************
       CLAROLINE MAIN SETTINGS
**************************************/
$langFile = "group";
@include('../inc/claro_init_global.inc.php'); 

$nameTools = $langGroupCreation;
$interbredcrump[]= array ("url"=>"group.php", "name"=> $langGroupManagement);
@include($includePath."/claro_init_header.inc.php");

claro_disp_tool_title(array('mainTitle' => $langGroup, 
                            'subTitle' => $nameTools));

?>
<form method="post" action="group.php">
<table>
<tr valign="top">
<td>
<?php echo $langCreate?>
</td>
<td>
<input type="text" name="group_quantity" size="3" value="1">
<?php echo $langNewGroups ?>
</td>
</tr>
<tr valign="top">
<td>
<small><?php echo $langMax ?></small>
</td>
<td><input type="text" name="group_max" size="3" value="8">
<small><?php echo $langPlaces ?></small>
</tr>
<tr>
<td>
</td>
<td>
<input type="submit" value=<?php echo $langCreate ?> name="creation">
</td>
</tr>
</table>
</form>
<?php
@include($includePath."/claro_init_footer.inc.php");
?>
