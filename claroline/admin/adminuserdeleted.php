<?php
// $Id$
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

define ('USER_SELECT_FORM'        , 1);
define ('USER_DATA_FORM'          , 2);

$langFile='admin';
$cidReset = true;
require '../inc/claro_init_global.inc.php';
include $includePath.'/lib/text.lib.php';
include $includePath.'/lib/admin.lib.inc.php';
include $includePath.'/conf/profile.conf.inc.php'; // find this file to modify values.


//SECURITY CHECK

if (!$is_platformAdmin) treatNotAuthorized();

$nameTools=$langModifOneProfile;

$interbredcrump[]= array ("url"=>$rootAdminWeb, "name"=> $langAdministrationTools);

$htmlHeadXtra[] = "<style type=\"text/css\">
<!--
	body,h1,h2,h3,h4,h5,h6,p,blockquote,td,ol,ul {font-family: Arial, Helvetica, sans-serif; }
-->
</STYLE>";


$tbl_log 	= $mainDbName."`.`loginout";
$tbl_user 	= $mainDbName."`.`user";
$tbl_admin  = $mainDbName."`.`admin";
$tbl_course = $mainDbName."`.`cours";
$tbl_course_user = $mainDbName."`.`cours_user";

include($includePath.'/claro_init_header.inc.php');

// see which user we are working with ...

$user_id = $_GET['uidToEdit'];

//echo $user_id."<br>";

//------------------------------------
// Execute COMMAND section
//------------------------------------

if (isset($cmd) && $is_platformAdmin)
{
    if ($cmd=="delete")
    {
        delete_user($user_id);
        $dialogBox = $langUserDelete;
    }

}

//------------------------------------
// DISPLAY
//------------------------------------


// Display tool title

claro_disp_tool_title($langDeleteUser);

//Display Forms or dialog box(if needed)

if($dialogBox)
  {
    claro_disp_message_box($dialogBox);
  }


// display TOOL links :


claro_disp_button("index.php",$langBackToAdmin);
claro_disp_button("adminusers.php",$langBackToUserList);

// display footer

include($includePath."/claro_init_footer.inc.php");
?>