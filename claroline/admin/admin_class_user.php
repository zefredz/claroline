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

// initialisation of global variables and used libraries

require '../inc/claro_init_global.inc.php';
include($includePath."/lib/pager.lib.php");
include($includePath."/lib/admin.lib.inc.php");

//SECURITY CHECK

if (!$is_platformAdmin) treatNotAuthorized();

if ($cidToEdit=="") {unset($cidToEdit);}

$userPerPage = 20; // numbers of user to display on the same page

//------------------------------------------------------------------------------------------------------------------------
//  USED SESSION VARIABLES
//------------------------------------------------------------------------------------------------------------------------

// deal with session variables for search criteria

if (isset($_GET['order_crit'])){$_SESSION['admin_user_order_crit'] = $_GET['order_crit'];}
if (isset($_GET['dir']))       {$_SESSION['admin_user_dir'] = $_GET['dir'];}


@include ($includePath."/installedVersion.inc.php");

// javascript confirm pop up declaration

   $htmlHeadXtra[] =
            "<script>
            function confirmationUnReg (name)
            {
                if (confirm(\"".$langAreYouSureToUnsubscribe."\"+ name + \"? \"))
                    {return true;}
                else
                    {return false;}
            }
            </script>";

//----------------------LANG TO ADD -------------------------------------------------------------------------------
	    	    
$langListClassUser             = "Class users";
$langClass                     = "Classes";
$langClassRegisterUser         = "Register a user to this class";
$langClassRegisterWholeClass   = "Register the whole class to a course";
$langUserUnregisteredFromClass = "User has been sucessfully unregistered from the class";
$langUnsubscribeClass          = "Unregister user from class";


//----------------------LANG TO ADD -------------------------------------------------------------------------------
    
// Deal with interbredcrumps
$interbredcrump[]= array ("url"=>$rootAdminWeb, "name"=> $langAdministrationTools);
$interbredcrump[]= array ("url"=>$rootAdminWeb."admin_class.php", "name"=> $langClass);
$nameTools = $langListClassUser;

//Header

include($includePath."/claro_init_header.inc.php");

/*
 * DB tables definition
 */
$tbl_mdb_names = claro_sql_get_main_tbl();
$tbl_user                  = $tbl_mdb_names['user'];
$tbl_class                 = $tbl_mdb_names['user_category'];
$tbl_class_user            = $tbl_mdb_names['user_rel_profile_category'];

//SESSION VARIABLES

if (isset($_REQUEST['class'])) 
{
    $_SESSION['admin_user_class_id'] = $_REQUEST['class'];
}


//------------------------------------
// Execute COMMAND section
//------------------------------------
switch ($cmd)
{

  case "unsubscribe" :
        $sql = "DELETE FROM `".$tbl_class_user."` WHERE `user_id`='".$_REQUEST['userid']."'";
	claro_sql_query($sql);
	$dialogBox = $langUserUnregisteredFromClass;
        break;
}

//----------------------------------
// Build query and find info in db
//----------------------------------

//find info about the class

$sqlclass = "SELECT * FROM `".$tbl_class."` WHERE `id`='".$_SESSION['admin_user_class_id']."'";
list($classinfo) = claro_sql_query_fetch_all($sqlclass);

//find this current content

$sql = "SELECT *
        FROM `".$tbl_user."` AS U 
	LEFT JOIN `".$tbl_class_user."` AS CU
	ON U.`user_id`= CU.`user_id`
	WHERE `class_id`='".$_SESSION['admin_user_class_id']."'
        ";


//first see is direction must be changed

if (isset($chdir) && ($chdir=="yes"))
{
  if ($_SESSION['admin_user_class_dir'] == "ASC") {$_SESSION['admin_user_class_dir']="DESC";}
  elseif ($_SESSION['admin_user_class_dir'] == "DESC") {$_SESSION['admin_user_class_dir']="ASC";}
  else $_SESSION['admin_user_class_dir'] = "DESC";
}

// deal with REORDER

if (isset($_REQUEST['order_crit']))
{
    $_SESSION['admin_user_class_order_crit'] = $_REQUEST['order_crit'];
    if ($_REQUEST['order_crit']=='user_id')
    {
        $_SESSION['admin_user_class_order_crit'] = "U`.`user_id";
    }
}
if (isset($_SESSION['admin_user_class_order_crit']))
{
    $toAdd = " ORDER BY `".$_SESSION['admin_user_class_order_crit']."` ".$_SESSION['admin_user_class_dir'];
    $sql.=$toAdd;

}

//echo $sql."<br>";

$myPager = new claro_sql_pager($sql, $offset, $userPerPage);
$resultList = $myPager->get_result_list();


//------------------------------------
// DISPLAY
//------------------------------------

// Display tool title

claro_disp_tool_title($nameTools." : ".$classinfo['name']);

//Display Forms or dialog box(if needed)

if($dialogBox)
  {
    claro_disp_message_box($dialogBox);
    echo "<br>";
  }

//TOOL LINKS

claro_disp_button($clarolineRepositoryWeb."admin/admin_class_register.php?class=".$classinfo['id'], $langClassRegisterUser);
claro_disp_button($clarolineRepositoryWeb."auth/courses.php?cmd=rqReg&fromAdmin=class&uidToEdit=-1&category=", $langClassRegisterWholeClass);

   //Pager

$myPager->disp_pager_tool_bar($PHP_SELF);


// Display list of users

   // start table...

echo "<table class=\"claroTable\" width=\"100%\" border=\"0\" cellspacing=\"2\">

     <tr class=\"headerX\" align=\"center\" valign=\"top\">
          <th><a href=\"",$PHP_SELF,"?order_crit=user_id&chdir=yes\">".$langUserid."</a></th>
          <th><a href=\"",$PHP_SELF,"?order_crit=nom&chdir=yes\">".$langName."</a></th>
          <th><a href=\"",$PHP_SELF,"?order_crit=prenom&chdir=yes\">".$langFirstName."</a></th>
          <th><a href=\"",$PHP_SELF,"?order_crit=officialCode&chdir=yes\">".$langOfficialCode."</a></th>";
echo     "<th>".$langEmail."</th>";
echo     "<th>".$langUnsubscribeClass."</th>";
echo "</tr><tbody> ";

   // Start the list of users...
foreach($resultList as $list)
//while ($list = mysql_fetch_array($query))
{
     echo "<tr>";

     //  Id

     echo "<td align=\"center\">".$list['user_id']."
           </td>";

     // name

     echo "<td align=\"left\">".$list['nom']."</td>";

     //  Firstname

     echo "<td align=\"left\">".$list['prenom']."</td>";
 
     //  Official code

     if (isset($list['officialCode'])) 
     { 
         $toAdd = $list['officialCode']; 
     } 
     else 
     {
         $toAdd = " - ";
     }
     echo "<td align=\"center\">".$toAdd."</td>";

     // mail

     echo "<td align=\"left\">".$list['email']."</td>";
     
     //  Unsubscribe link

     echo   "<td align=\"center\">\n"
           ."  <a href=\"",$PHP_SELF,"?cmd=unsubscribe".$addToUrl."&offset=".$offset."&userid=".$list['user_id']."\" "
           ."      onClick=\"return confirmationUnReg('",addslashes($list['prenom']." ".$list['nom']),"');\">\n"
           ."      <img src=\"".$clarolineRepositoryWeb."/img/unenroll.gif\" border=\"0\" alt=\"\" />\n"
           ."  </a>\n"
           ."</td>\n";
     
     $atLeastOne= true;
}
   // end display users table
if (!$atLeastOne)
{
   echo "<tr>
          <td colspan=\"8\" align=\"center\">
            ".$langNoUserResult."<br>
            <a href=\"".$clarolineRepositoryWeb."admin/admin_class.php".$addtoAdvanced."\">".$langBack."</a>
          </td>
         </tr>";
}
echo "</tbody></table>";

//Pager

$myPager->disp_pager_tool_bar($PHP_SELF);

include($includePath."/claro_init_footer.inc.php");

?>