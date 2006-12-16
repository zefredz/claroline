<?php // $Id$
require '../inc/claro_init_global.inc.php';

$nameTools = get_lang('Groups help');
$hide_banner = true;
$hide_footer = true;
include get_path('incRepositorySys') . '/claro_init_header.inc.php';

?>
<table width="100%" border="0" cellpadding="1" cellspacing="1">
<tr>
  <td align="left" valign="top">

    <?php echo '<h4>' . get_lang('Groups help') . '</h4>'; ?>

  </td>
  <td align="right" valign="top">
    <a href="javascript:window.close();"><?php echo get_lang('Close window'); ?></a>
  </td>
</tr>
<tr>
  <td colspan="2">

    <?php echo get_block('blockGroupsHelp'); ?>

  </td>
</tr>
<tr>
  <td colspan="2">
    <br />
    <center><a href="javascript:window.close();"><?php echo get_lang('Close window'); ?></a></center>
  </td>
</tr>
</table>
<?php
include get_path('incRepositorySys') . '/claro_init_footer.inc.php';
?>