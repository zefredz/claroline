<?php // $Id$
$langFile = "admin";
require '../inc/claro_init_global.inc.php';
include($includePath."/lib/admin.lib.inc.php");
//SECURITY CHECK
if (!$is_platformAdmin) claro_disp_auth_form();

?><PRE>
<?
var_dump($HTTP_POST_VARS);
?></PRE>
