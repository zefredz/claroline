<?php //$Id$
//----------------------------------------------------------------------
// CLAROLINE 1.6
//----------------------------------------------------------------------
// Copyright (c) 2001-2004 Universite catholique de Louvain (UCL)
//----------------------------------------------------------------------
// This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
// as published by the FREE SOFTWARE FOUNDATION. The GPL is available
// through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
//----------------------------------------------------------------------
// Authors: see 'credits' file
//----------------------------------------------------------------------

// Lang files needed :

$langFile = "admin";

//----------------------LANG TO ADD -------------------------------------------------------------------------------
   
$langRegisterUserToClass       = "Register user to class"; 
$langSubscribeClass            = "Register to the class";
$langUnsubscribeClass          = "Unregister from class";
$langUserAlreadyInClass        = "User already in class";
$langUserNotInClass            = "User not in the class";
$langUserRegisteredClass       = "User has been sucessfully registered to the class";
$langUserUnregisteredClass     = "User has been sucessfully unregistered from the class";
$langClassMembers              = "Class members";
$langClass                     = "Classes";

//----------------------LANG TO ADD -------------------------------------------------------------------------------
   
// initialisation of global variables and used libraries

require '../inc/claro_init_global.inc.php';
include($includePath."/lib/pager.lib.php");
include($includePath."/lib/admin.lib.inc.php");


//SECURITY CHECK

if (!$is_platformAdmin) treatNotAuthorized();

$is_allowedToAdmin     = $is_platformAdmin;
$userPerPage = 20; // numbers of user to display on the same page

@include ($includePath."/installedVersion.inc.php");


/*
 * DB tables definition
 */
$tbl_mdb_names  = claro_sql_get_main_tbl();
$tbl_user       = $tbl_mdb_names['user'];
$tbl_class      = $tbl_mdb_names['user_category'];
$tbl_class_user = $tbl_mdb_names['user_rel_profile_category'];

//find info about the class

$sqlclass = "SELECT * FROM `".$tbl_class."` WHERE `id`='".$_SESSION['admin_user_class_id']."'";
list($classinfo) = claro_sql_query_fetch_all($sqlclass);

//------------------------------------
// Execute COMMAND section
//------------------------------------

switch ($cmd)
{
  case "subscribe" :
        $sql ="INSERT INTO `".$tbl_class_user."` 
	       SET `user_id` = '".$_REQUEST['user_id']."',
	           `class_id` = '".$classinfo['id']."' "; 
	claro_sql_query($sql); 
	$dialogBox = $langUserRegisteredClass;       
        break;
	
  case "unsubscribe" :
  	$sql ="DELETE FROM `".$tbl_class_user."` 
	       WHERE `user_id` = '".$_REQUEST['user_id']."'
	         AND `class_id` = '".$classinfo['id']."'
	       ";
	claro_sql_query($sql);
	$dialogBox = $langUserUnregisteredClass; 
	break;
	
}



//----------------------------------
// Build query and find info in db
//----------------------------------


$sql = "SELECT *, U.`user_id` 
        FROM  `".$tbl_user."` AS U       
        LEFT JOIN `".$tbl_class_user."` AS CU 
               ON  CU.`user_id` = U.`user_id` 
              AND CU.`class_id` = '".$classinfo['id']."'";

// deal with REORDER

// See SESSION variables used for reorder criteria :

if (isset($_REQUEST['order_crit']))
{
    $_SESSION['admin_class_reg_user_order_crit'] = $_REQUEST['order_crit'];
    if ($_REQUEST['order_crit']=="user_id")
    {  
        $_SESSION['admin_class_reg_user_order_crit'] = "U`.`user_id";
    }
}
if (isset($_REQUEST['class'])) 
{
    $_SESSION['admin_user_class_id'] = $_REQUEST['class'];
}
if (!isset($_SESSION['admin_user_class_id'])) 
{
    $dialogBox ="ERROR : NO CLASS SET!!!";
}

  //first if direction must be changed

if (isset($_REQUEST['chdir']) && ($chdir=="yes"))
{
  if ($_SESSION['admin_class_reg_user_dir'] == "ASC") {$_SESSION['admin_class_reg_user_dir']="DESC";}
  elseif ($_SESSION['admin_class_reg_user_dir'] == "DESC") {$_SESSION['admin_class_reg_user_dir']="ASC";}
  else $_SESSION['admin_class_reg_user_dir'] = "DESC";
}

if (isset($_SESSION['admin_class_reg_user_order_crit']))
{
    if ($_SESSION['admin_classdefine ("USER_SELECT_FORM", 1);
define ("USER_DATA_FORM", 2);_reg_user_order_crit']=="user_id")
    {
        $toAdd = " ORDER BY CU.`user_id` ".$_SESSION['admin_class_reg_user_dir'];
    }
    else
    {
        $toAdd = " ORDER BY `".$_SESSION['admin_class_reg_user_order_crit']."` ".$_SESSION['admin_class_reg_user_dir'];
    }
    $sql.=$toAdd;
}

// Deal with interbredcrumps

$interbredcrump[]= array ("url"=>$rootAdminWeb, "name"=> $langAdministrationTools);
$interbredcrump[]= array ("url"=>$rootAdminWeb."/admin_class.php", "name"=> $langClass);
$nameTools = $langRegisterUserToClass;

//Header
include($includePath."/claro_init_header.inc.php");


$myPager = new claro_sql_pager($sql, $offset, $userPerPage);
$resultList = $myPager->get_result_list();


//------------------------------------
// DISPLAY
//------------------------------------

// Display tool title

claro_disp_tool_title($nameTools." : ".$classinfo['name']);

// Display Forms or dialog box(if needed)

if($dialogBox)
{
    claro_disp_message_box($dialogBox);
}

//TOOL LINKS

claro_disp_button($clarolineRepositoryWeb."admin/admin_class_user.php?class=".$classinfo['id'], $langClassMembers);

if (isset($cfrom) && ($cfrom=="clist"))
{
    claro_disp_button("admincourses.php", $langBackToCourseList);
}

//Pager

$myPager->disp_pager_tool_bar($_SERVER['PHP_SELF']."?cidToEdit=".$cidToEdit);

// Display list of users

   // start table...

echo "<table class=\"claroTable\" width=\"100%\" border=\"0\" cellspacing=\"2\">"
    ."<tr class=\"headerX\" align=\"center\" valign=\"top\">"
    ."  <th><a href=\"".$_SERVER['PHP_SELF']."?order_crit=user_id&chdir=yes\">".$langUserid."</a></th>"
    ."  <th><a href=\"".$_SERVER['PHP_SELF']."?order_crit=nom&chdir=yes\">".$langName."</a></th>"
    ."  <th><a href=\"".$_SERVER['PHP_SELF']."?order_crit=prenom&chdir=yes".$dir."\">".$langFirstName."</a></th>"
    ."  <th>".$langSubscribeClass."</th>"
    ."  <th>".$langUnsubscribeClass."</th>"
    ."</tr>"
    ."<tbody> ";

   // Start the list of users...

foreach($resultList as $list)
{
     echo "<tr>";

     //  Id

     echo "<td align=\"center\"><a name=\"u".$list['user_id']."\"></a>".$list['user_id']."
           </td>";

     // lastname

     echo '<td align="left">'.$list['nom'].'</td>'."\n";

     //  Firstname

     echo '<td align="left">'.$list['prenom'].'</td>'."\n";
  
     // Register

     if ($list['id']==null)
     {
        echo  '<td align="center">'."\n"
             .'<a href="'.$_SERVER['PHP_SELF'].'?class='.$classinfo['id'].'&cmd=subscribe&user_id='.$list['user_id'].'&offset='.$offset.'#u'.$list['user_id'].'">'."\n"
             .'<img src="'.$clarolineRepositoryWeb.'img/enroll.gif" border="0" alt="'.$langSubscribeClass.'" />'."\n"
             .'</a>'."\n"
             .'</td>'."\n";
     }
     else
     {
        echo  '<td align="center">'."\n"
             .'<small>'.$langUserAlreadyInClass.'</small>'."\n"
             .'</td>'."\n";
     }

     // Unregister

     if ($list['id']!=null)
     {
        echo  '<td align="center">'."\n"
             .'<a href="'.$_SERVER['PHP_SELF'].'?class='.$classinfo['id'].'&cmd=unsubscribe&user_id='.$list['user_id'].'&offset='.$offset.'#u'.$list['user_id'].'">'."\n"
             .'<img src="'.$clarolineRepositoryWeb.'img/unenroll.gif" border="0" alt="'.$langUnsubscribeClass.'" />'."\n"
             .'</a>'."\n"
             .'</td>'."\n";
     }
     else
     {
        echo  '<td align="center">'."\n"
             .'<small>'.$langUserNotInClass.'</small>'."\n"
             .'</td>'."\n";
     }

     echo "</tr>";
}

   // end display users table

echo "</tbody></table>";

//Pager

$myPager->disp_pager_tool_bar($_SERVER['PHP_SELF']."?cidToEdit=".$cidToEdit);

include($includePath."/claro_init_footer.inc.php");
?>