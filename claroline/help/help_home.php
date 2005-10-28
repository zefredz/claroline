<?php // $Id$
require '../inc/claro_init_global.inc.php';

$nameTools = $langHHome;
$hide_banner = true;
$hide_footer = true;
include $includePath . '/claro_init_header.inc.php';

?>
<table width="100%" border="0" cellpadding="1" cellspacing="1">
<tr>
  <td align="left" valign="top">

    <?php echo "<h4>$langHHome</h4>"; ?>

  </td>
  <td align="right" valign="top">
    <a href="javascript:window.close();"><?php echo $langCloseWindow; ?></a>
  </td>
</tr>
<tr>
  <td colspan="2">

    <?php echo $langHomeContent; ?>

  </td>
</tr>
<tr>
  <td colspan="2">
    <br />
    <center><a href="javascript:window.close();"><?php echo $langCloseWindow; ?></a></center>
  </td>
</tr>
</table>
<?php
include $includePath . '/claro_init_footer.inc.php';
?>