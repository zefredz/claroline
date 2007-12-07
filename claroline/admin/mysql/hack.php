<?php # $Id$
//----------------------------------------------------------------------
// CLAROLINE
//----------------------------------------------------------------------
// Copyright (c) 2001-2003 Universite catholique de Louvain (UCL)
//----------------------------------------------------------------------
// This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
// as published by the FREE SOFTWARE FOUNDATION. The GPL is available
// through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
//----------------------------------------------------------------------
// Authors: see 'credits' file
//----------------------------------------------------------------------

$langFile = "trad4all";
require '../../inc/claro_init_global.inc.php';

$nameTools = $langMysql;
$interbredcrump[]= array ("url"=>$rootAdminWeb, "name"=> $langAdministrationTools);

include($includePath."/lib/text.lib.php");
include($includePath."/lib/debug.lib.inc.php");
$dateNow 			= claro_format_locale_date($dateTimeFormatLong);
$is_allowedToAdmin 	= $is_platformAdmin || $PHP_AUTH_USER;

// make here some  test
// $checkMsgs[] = array("level" => 5, "target" => "test 1 ", "content" => "this is  just  a  warning test 1 ");

// ----- is install visible ----- begin
 if (file_exists("../../install/index.php") && !file_exists("../../install/.htaccess"))
 {
	 $controlMsg["warning"][]="install is not protected";
 }
// ----- is install visible ----- end



$str = highlight_file("./config.inc.php",true);
$str = preg_replace(
           '{([\w_]+)(\s*</font>)(\s*<font\s+color="'
                   .ini_get('highlight.keyword').'">\s*\()}m',
           '<a class="phpfunction" target="_blank" title="doc PHP $1"'
                   .' href="http://www.php.net/$1">$1</a>$2$3',
           $str);



include($includePath."/claro_init_header.inc.php");

claro_disp_tool_title(
	array(
	'mainTitle'=>$nameTools,
	'subTitle'=> $PHP_AUTH_USER." - ".$siteName." - ".$clarolineVersion." - ".$dateNow
	)
	);
claro_disp_msg_arr($controlMsg);
?>
<P>
PHP MY ADMIN is no longer provide with Claroline.
<br>
Because  
<oL>
<li>
you have probably already one.
</li>
<li>
You hope that new admin interface is enough for management adminstration.
</li>
</oL>
</P>
<P>This is the config hack wich make PMA eable to read parameter of  claroline config file</P>
<?php

echo $str;

include($includePath."/claro_init_footer.inc.php");
?>
