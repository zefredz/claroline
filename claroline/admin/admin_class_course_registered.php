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

$langFile='admin';

//----------------------LANG TO ADD -------------------------------------------------------------------------------
 
$langClassRegistered              = "Class registered";
$langClassRegisterWholeClassAgain = "Register whole class to another course";
$langBackToClassMembers           = "Back to class members";

//----------------------LANG TO ADD -------------------------------------------------------------------------------
 

require '../inc/claro_init_global.inc.php';
include $includePath.'/lib/text.lib.php';
include $includePath."/lib/admin.lib.inc.php";
include $includePath.'/conf/profile.conf.inc.php'; // find this file to modify values.


//SECURITY CHECK

if (!$is_platformAdmin) treatNotAuthorized();

//bredcrump

$nameTools=$langClassRegistered;
$interbredcrump[]= array ("url"=>$rootAdminWeb, "name"=> $langClassRegistered);

/*
 * DB tables definition
 */
$tbl_mdb_names = claro_sql_get_main_tbl();
$tbl_user                  = $tbl_mdb_names['user'];
$tbl_course                = $tbl_mdb_names['course'];
$tbl_course_user           = $tbl_mdb_names['rel_course_user'];
$tbl_class                 = $tbl_mdb_names['user_category'];
$tbl_class_user            = $tbl_mdb_names['user_rel_profile_category'];

include($includePath.'/claro_init_header.inc.php');

//find info about the class

$sqlclass = "SELECT * FROM `".$tbl_class."` WHERE `id`='".$_SESSION['admin_user_class_id']."'";
list($classinfo) = claro_sql_query_fetch_all($sqlclass);

//------------------------------------
// Execute COMMAND section
//------------------------------------

if (isset($cmd) && $is_platformAdmin)
{
    if ($cmd=="exReg")
    {
        $resultLog = register_class_to_course($_REQUEST['class'], $_REQUEST['course']);
    }
}

//------------------------------------
// DISPLAY
//------------------------------------


// Display tool title

claro_disp_tool_title($langClassRegistered." : ".$classinfo['name']);

//Display Forms or dialog box(if needed)

if($dialogBox)
  {
    claro_disp_message_box($dialogBox);
  }

// display log

echo $resultLog."<br>";

// display TOOL links :


claro_disp_button("index.php",$langBackToAdmin);
claro_disp_button($clarolineRepositoryWeb."admin/admin_class_user.php?class=".$classinfo['id'], $langBackToClassMembers);
claro_disp_button($clarolineRepositoryWeb."auth/courses.php?cmd=rqReg&fromAdmin=class&uidToEdit=-1&category=", $langClassRegisterWholeClassAgain);

// display footer

include($includePath."/claro_init_footer.inc.php");

function register_class_to_course($class_id, $course_code) 
{
    global $tbl_class_user;     
    global $tbl_user; 
    //get the list of users in this class 
    
    $sql = "SELECT * FROM `".$tbl_class_user."` `rel_c_u`, `".$tbl_user."` `u` 
                    WHERE `class_id`='".$class_id."' 
		      AND `rel_c_u`.`user_id` = `u`.`user_id`";
    $result = claro_sql_query_fetch_all($sql);
    
    //subscribe the users each by each
    
    $resultLog .= "";
    
    foreach ($result as $user)
    {
        $done = add_user_to_course($user['user_id'], $course_code); 
	if ($done)
	{
	    $resultLog .= "[<font color=\"green\">OK</font>] ".$user['prenom']." ".$user['nom']." has been sucessfully registered to the course.<br>";
	}
	else
	{
	    $resultLog .= "[<font color=\"red\">KO</font>] ".$user['prenom']." ".$user['nom']." was NOT registered to the course.<br>";
	}   
    }
    
    $users = "";
    
    return $resultLog."<br> END OF REGISTRATION <br>";
}

?>