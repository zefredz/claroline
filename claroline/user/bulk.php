<?php
$langFile="registration";
require '../inc/claro_init_global.inc.php';
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML>
<HEAD>
<TITLE>Bulk subscribe</TITLE>

<frameset cols="100%" rows="30,*" border="0" frameborder="0" framespacing="2">

    <frame name="top" src="upper.php"
marginwidth="1" marginheight="1" scrolling="no" frameborder="no" noresize>   

    <frame name="bottom" src="<?php echo "$phpMyAdminWeb/ldi_table.php?server=1&db=$mainDbName&table=user"; ?>" marginwidth="3"
marginheight="3" scrolling="yes" frameborder="no" noresize>
         </frameset>
</HEAD>
<BODY>
Frameset
</BODY>
</HTML>
