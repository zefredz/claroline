<?php // $Id$
//----------------------------------------------------------------------
// CLAROLINE
//----------------------------------------------------------------------
// Copyright (c) 2001-2004 Universite catholique de Louvain (UCL)
//----------------------------------------------------------------------
// This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
// as published by the FREE SOFTWARE FOUNDATION. The GPL is available
// through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
//----------------------------------------------------------------------
// Authors: see 'credits' file
//----------------------------------------------------------------------
$langFile = "admin";
require '../inc/claro_init_global.inc.php';

@include ($includePath."/installedVersion.inc.php");
include($includePath."/lib/admin.lib.inc.php");

//SECURITY CHECK

if (!$is_platformAdmin) treatNotAuthorized();

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
$nameTools = $langSearchUserAdvanced;

// Search needed info in db to creat the right formulaire

$sql_searchfaculty = "select * FROM `$tbl_faculty` order by `treePos`";
$arrayFaculty=claro_sql_query_fetch_all($sql_searchfaculty);


//header and bredcrump display

include($includePath."/claro_init_header.inc.php");

//tool title

claro_disp_tool_title($nameTools." : ");

?>

<form action="adminusers.php" method="GET" >
<table border="0">
	<tr>
		<td align="right">
			<label for="lastName"><?php echo $langLastName?></label>
			: <br>
		</td>
		<td>
			<input type="text" name="lastName" id="lastName" value="<?php echo $_GET['lastName']?>"/>
		</td>
	</tr>

	<tr>
		<td align="right">
			<label for="firstName"><?php echo $langFirstName?></label>
			: <br>
		</td>
		<td>
			<input type="text" name="firstName" id="firstName" value="<?php echo $_GET['firstName']?>"/>
		</td>
	</tr>
	
	<tr>
		<td align="right">
			<label for="userName"><?php echo $langUsername ?></label> 
			:  <br>
		</td>
		<td>
			<input type="text" name="userName" id="userName" value="<?php echo $_GET['userName']?>"/>
		</td>
	</tr>

	<tr>
		<td align="right">
			<label for="mail"><?php echo $langEmail ?></label> 
			: <br>
		</td>
		<td>
			<input type="text" name="mail" id="mail" value="<?php echo $_GET['mail']?>"/>
		</td>
	</tr>

<tr>
  <td align="right">
   <label for="action"><?php echo $langAction?></label> : <br>
  </td>
  <td>
    <select name="action" id="action">
        <option value="followcourse" <?if ($_GET['action']=="followcourse") echo "selected";?>><?php echo $langFollowCourse?></option>
        <option value="createcourse" <?if ($_GET['action']=="createcourse") echo "selected";?>><?php echo $langCreateCourse?></option>
        <option value="plateformadmin" <?if ($_GET['action']=="plateformadmin") echo "selected";?>><?php echo $langPlatformAdmin?></option>
    </select>
  </td>
</tr>

<tr>
    <td>

    </td>
    <td>
        <input type="submit" class="claroButton" value="<?php echo $langSearchUser?>" >
    </td>
</tr>
</table>
</form>
<?php
include($includePath."/claro_init_footer.inc.php");

?>