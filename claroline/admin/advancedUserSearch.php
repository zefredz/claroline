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
$langFile = "admin";
include('../inc/claro_init_global.inc.php');

@include ($includePath."/installedVersion.inc.php");

$htmlHeadXtra[] = "<style type=\"text/css\">
<!--
    ul { font-size : small }
-->
</STYLE>";

//------------------------------------------------------------------------------------------------------------------------
//  USED SESSION VARIABLES
//------------------------------------------------------------------------------------------------------------------------
// deal with session variables clean session variables from previous search


session_unregister('admin_user_letter');
session_unregister('admin_user_search');
session_unregister('admin_user_firstName');
session_unregister('admin_user_lastName');
session_unregister('admin_user_userName');
session_unregister('admin_user_mail');
session_unregister('admin_user_action');
session_unregister('admin_order_crit');


//declare needed tables

$tbl_faculty      = $mainDbName.'`.`faculte';

// Deal with interbredcrumps  and title variable

$interbredcrump[]= array ("url"=>$rootAdminWeb, "name"=> $langAdministrationTools);
$nameTools = $langSearchUserAdvanced." : ";

// Search needed info in db to creat the right formulaire

$sql_searchfaculty = "select * FROM `$tbl_faculty` order by `treePos`";
$arrayFaculty=claro_sql_query_fetch_all($sql_searchfaculty);


//header and bredcrump display

include($includePath."/claro_init_header.inc.php");

if (! $_uid) exit("<center>You're not logged in !!</center></body>");

//tool title

claro_disp_tool_title($nameTools);

?>
<small><?=$langYouCanUsefields?> : </small><br>
<form action="adminusers.php" method="GET" >
<table border="0">

<tr>
  <td>
   <?=$langLastName?> : <br>
  </td>
  <td>
    <input type="text" name="lastName" value="<?=$_GET['lastName']?>"/>
  </td>
</tr>

<tr>
  <td>
   <?=$langFirstName?> : <br>
  </td>
  <td>
    <input type="text" name="firstName" value="<?=$_GET['firstName']?>"/>
  </td>
</tr>

<tr>
  <td>
   <?=$langUsername?> :  <br>
  </td>
  <td>
    <input type="text" name="userName" value="<?=$_GET['userName']?>"/>
  </td>
</tr>

<tr>
  <td>
   <?=$langEmail?> : <br>
  </td>
  <td>
    <input type="text" name="mail" value="<?=$_GET['mail']?>"/>
  </td>
</tr>

<tr>
  <td>
   <?=$langAction?> : <br>
  </td>
  <td>
    <select name="action">
        <option value="followcourse" <?if ($_GET['action']=="followcourse") echo "selected";?>><?=$langFollowCourse?></option>
        <option value="createcourse" <?if ($_GET['action']=="createcourse") echo "selected";?>><?=$langCreateCourse?></option>
        <option value="plateformadmin" <?if ($_GET['action']=="plateformadmin") echo "selected";?>><?=$langPlatformAdmin?></option>
    </select>
  </td>
</tr>

<tr>
  <td>

  </td>
  <td>
    <input type="submit" class="claroButton" value="<?=$langSearchUser?>" ></input>
  </td>
</tr>
</table>
</form>
<?php
include($includePath."/claro_init_footer.inc.php");

?>