<?php //$Id$
//----------------------------------------------------------------------
// CLAROLINE 1.6.*
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

// clean session if needed

if ($_GET['newsearch']=="yes")
{
    session_unregister('admin_user_letter');
    session_unregister('admin_user_search');
    session_unregister('admin_user_firstName');
    session_unregister('admin_user_lastName');
    session_unregister('admin_user_userName');
    session_unregister('admin_user_mail');
    session_unregister('admin_user_action');
    session_unregister('admin_order_crit');
}

// deal with session variables for search criteria, it depends where we come from :
// 1 ) we must be able to get back to the list that concerned the criteria we previously used (with out re entering them)
// 2 ) we must be able to arrive with new critera for a new search.

if (isset($_GET['letter']))    {$_SESSION['admin_user_letter'] = $_GET['letter'];}
if (isset($_GET['search']))    {$_SESSION['admin_user_search'] = $_GET['search'];}
if (isset($_GET['firstName'])) {$_SESSION['admin_user_firstName'] = $_GET['firstName'];}
if (isset($_GET['lastName']))  {$_SESSION['admin_user_lastName'] = $_GET['lastName'];}
if (isset($_GET['userName']))  {$_SESSION['admin_user_userName'] = $_GET['userName'];}
if (isset($_GET['mail']))      {$_SESSION['admin_user_mail'] = $_GET['mail'];}
if (isset($_GET['action']))    {$_SESSION['admin_user_action'] = $_GET['action'];}
if (isset($_GET['order_crit'])){$_SESSION['admin_user_order_crit'] = $_GET['order_crit'];}
if (isset($_GET['dir']))       {$_SESSION['admin_user_dir'] = $_GET['dir'];}

// clean session if we come from a course

session_unregister('_cid');
unset($_cid);


@include ($includePath."/installedVersion.inc.php");

// javascript confirm pop up declaration

  $htmlHeadXtra[] =
         "<style type=text/css>
         <!--
         .comment { margin-left: 30px}
         .invisible {color: #999999}
         .invisible a {color: #999999}
         -->
         </style>";
  $htmlHeadXtra[] =
            "<script>
            function confirmation (name)
            {
                if (confirm(\"".$langAreYouSureToDelete."\"+ name + \"? \"))
                    {return true;}
                else
                    {return false;}
            }
            </script>";

// Deal with interbredcrumps

$interbredcrump[]= array ("url"=>$rootAdminWeb, "name"=> $langAdministrationTools);
$nameTools = $langListUsers;

//Header

include($includePath."/claro_init_header.inc.php");

//TABLES

$tbl_user             = $mainDbName."`.`user";
$tbl_courses        = $mainDbName."`.`cours";
$tbl_course_user    = $mainDbName."`.`cours_user";
$tbl_admin            = $mainDbName."`.`admin";
$tbl_track_default    = $statsDbName."`.`track_e_default";// default_user_id
$tbl_track_login    = $statsDbName."`.`track_e_login";    // login_user_id

//------------------------------------
// Execute COMMAND section
//------------------------------------
switch ($cmd)
{
  case "delete" :
        delete_user($user_id);
        $dialogBox = $langUserDelete;
        break;
}

//----------------------------------
// Build query and find info in db
//----------------------------------


$sql = "SELECT *
        FROM  `".$tbl_user."` AS U WHERE 1=1
        ";

//deal with LETTER classification call

if (isset($_SESSION['admin_user_letter']))
{
    $toAdd = "
             AND U.`nom` LIKE '".$_SESSION['admin_user_letter']."%'
             ";
    $sql.=$toAdd;

}

//deal with KEY WORDS classification call

if (isset($_SESSION['admin_user_search']))
{
    $toAdd = " AND (U.`nom` LIKE '%".$_SESSION['admin_user_search']."%'
              OR U.`prenom` LIKE '%".$_SESSION['admin_user_search']."%' ";
    $toAdd .= " OR U.`email` LIKE '%".$_SESSION['admin_user_search']."%')";
    $sql.=$toAdd;

}

//deal with ADVANCED SEARCH parameters call

if (isset($_SESSION['admin_user_firstName']))
{
    $toAdd = " AND (U.`prenom` LIKE '".$_SESSION['admin_user_firstName']."%') ";
    $sql.=$toAdd;

}

if (isset($_SESSION['admin_user_lastName']))
{
    $toAdd = " AND (U.`nom` LIKE '".$_SESSION['admin_user_lastName']."%') ";
    $sql.=$toAdd;

}

if (isset($_SESSION['admin_user_userName']))
{
    $toAdd = " AND (U.`username` LIKE '".$_SESSION['admin_user_userName']."%') ";
    $sql.=$toAdd;

}

if (isset($_SESSION['admin_user_mail']))
{
    $toAdd = " AND (U.`email` LIKE '".$_SESSION['admin_user_mail']."%') ";
    $sql.=$toAdd;

}
if (isset($_SESSION['admin_user_action']))
{
    if ($_SESSION['admin_user_action']=="createcourse")
    {
       $toAdd = " AND (U.`statut`=1) ";
    }
    if ($_SESSION['admin_user_action']=="plateformadmin")
    {
       $toAdd = " AND (U.`statut`=1) ";
    }
    $sql.=$toAdd;

}


  //first see is direction must be changed

if (isset($chdir) && ($chdir=="yes"))
{
  if ($_SESSION['admin_user_dir'] == "ASC") {$_SESSION['admin_user_dir']="DESC";}
  elseif ($_SESSION['admin_user_dir'] == "DESC") {$_SESSION['admin_user_dir']="ASC";}
  else $_SESSION['admin_user_dir'] = "DESC";
}

// deal with REORDER

if (isset($_SESSION['admin_user_order_crit']))
{
    $toAdd = " ORDER BY `".$_SESSION['admin_user_order_crit']."` ".$_SESSION['admin_user_dir'];
    $sql.=$toAdd;

}

//echo $sql."<br>";

$myPager = new claro_sql_pager($sql, $offset, $userPerPage);
$resultList = $myPager->get_result_list();

//------------------------------------
// DISPLAY
//------------------------------------

// Display tool title

claro_disp_tool_title($nameTools);

//Display Forms or dialog box(if needed)

if($dialogBox)
  {
    claro_disp_message_box($dialogBox);
  }

//Display selectbox, alphabetic choice, and advanced search link search

  // ALPHABETIC SEARCH
/*
echo "<form name=\"indexform\" action=\"",$PHP_SELF,"\" method=\"GET\">
             ";

            if (isset($cidToEdit)) {$toAdd = "cidToEdit=".$cidToEdit;} else {$toAdd = "";}

            echo "<a href=\"",$PHP_SELF,"?".$toAdd."\"><b> ".$langAll."</b></a> | ";

            echo "<a href=\"",$PHP_SELF,"?letter=A&".$toAdd."\">A</a> | ";
            echo "<a href=\"",$PHP_SELF,"?letter=B&".$toAdd."\">B</a> | ";
            echo "<a href=\"",$PHP_SELF,"?letter=C&".$toAdd."\">C</a> | ";
            echo "<a href=\"",$PHP_SELF,"?letter=D&".$toAdd."\">D</a> | ";
            echo "<a href=\"",$PHP_SELF,"?letter=E&".$toAdd."\">E</a> | ";
            echo "<a href=\"",$PHP_SELF,"?letter=F&".$toAdd."\">F</a> | ";
            echo "<a href=\"",$PHP_SELF,"?letter=G&".$toAdd."\">G</a> | ";
            echo "<a href=\"",$PHP_SELF,"?letter=H&".$toAdd."\">H</a> | ";
            echo "<a href=\"",$PHP_SELF,"?letter=I&".$toAdd."\">I</a> | ";
            echo "<a href=\"",$PHP_SELF,"?letter=J&".$toAdd."\">J</a> | ";
            echo "<a href=\"",$PHP_SELF,"?letter=K&".$toAdd."\">K</a> | ";
            echo "<a href=\"",$PHP_SELF,"?letter=L&".$toAdd."\">L</a> | ";
            echo "<a href=\"",$PHP_SELF,"?letter=M&".$toAdd."\">M</a> | ";
            echo "<a href=\"",$PHP_SELF,"?letter=N&".$toAdd."\">N</a> | ";
            echo "<a href=\"",$PHP_SELF,"?letter=O&".$toAdd."\">O</a> | ";
            echo "<a href=\"",$PHP_SELF,"?letter=P&".$toAdd."\">P</a> | ";
            echo "<a href=\"",$PHP_SELF,"?letter=Q&".$toAdd."\">Q</a> | ";
            echo "<a href=\"",$PHP_SELF,"?letter=R&".$toAdd."\">R</a> | ";
            echo "<a href=\"",$PHP_SELF,"?letter=S&".$toAdd."\">S</a> | ";
            echo "<a href=\"",$PHP_SELF,"?letter=T&".$toAdd."\">T</a> | ";
            echo "<a href=\"",$PHP_SELF,"?letter=U&".$toAdd."\">U</a> | ";
            echo "<a href=\"",$PHP_SELF,"?letter=V&".$toAdd."\">V</a> | ";
            echo "<a href=\"",$PHP_SELF,"?letter=W&".$toAdd."\">W</a> | ";
            echo "<a href=\"",$PHP_SELF,"?letter=X&".$toAdd."\">X</a> | ";
            echo "<a href=\"",$PHP_SELF,"?letter=Y&".$toAdd."\">Y</a> | ";
            echo "<a href=\"",$PHP_SELF,"?letter=Z&".$toAdd."\">Z</a>";
            echo "
            <input type=\"text\" name=\"search\">
            <input type=\"hidden\" name=\"cidToEdit\" value=\"".$cidToEdit."\">
            <input type=\"submit\" value=\"".$langSearch."\">

      </form>
     ";
*/
//TOOL LINKS

   //Display search form


      //see passed search parameters :

if ($_SESSION['admin_user_search']!="")    {$isSearched .= $_SESSION['admin_user_search']."* ";}
if ($_SESSION['admin_user_firstName']!="") {$isSearched .= $langFirstName."=".$_SESSION['admin_user_firstName']."* ";}
if ($_SESSION['admin_user_lastName']!="")  {$isSearched .= $langLastName."=".$_SESSION['admin_user_lastName']."* ";}
if ($_SESSION['admin_user_userName']!="")  {$isSearched .= $langUsername."=".$_SESSION['admin_user_userName']."* ";}
if ($_SESSION['admin_user_mail']!="")      {$isSearched .= $langEmail."=".$_SESSION['admin_user_mail']."* ";}
if ($_SESSION['admin_user_action']=="createcourse")    {$isSearched .=  "<b> <br>".$langCourseCreator."  </b> ";}
if ($_SESSION['admin_user_action']=="plateformadmin")    {$isSearched .= "<b> <br>".$langPlatformAdmin."  </b> ";}

     //see what must be kept for advanced links

$addtoAdvanced = "?firstName=".$_SESSION['admin_user_firstName'];
$addtoAdvanced .="&lastName=".$_SESSION['admin_user_lastName'];
$addtoAdvanced .="&userName=".$_SESSION['admin_user_userName'];
$addtoAdvanced .="&mail=".$_SESSION['admin_user_mail'];
$addtoAdvanced .="&action=".$_SESSION['admin_user_action'];

    //finaly, form itself

if (($isSearched=="") || !isset($isSearched)) {$title = "";} else {$title = $langSearchOn." : ";}

echo "<table width=\"100%\">
        <tr>
          <td align=\"left\">
             <b>".$title."</b>
             <small>
             ".$isSearched."
             </small>
          </td>
          <td align=\"right\">
            <form action=\"",$PHP_SELF,"\">
            <label for=\"search\">".$langMakeNewSearch."</label>
            <input type=\"text\" value=\"".$_GET['search']."\" name=\"search\" id=\"search\" >
            <input type=\"submit\" value=\" ".$langOk." \">
            <input type=\"hidden\" name=\"newsearch\" value=\"yes\">
            [<a href=\"advancedUserSearch.php".$addtoAdvanced."\"><small>".$langAdvanced."</small></a>]
            </form>
          </td>
        </tr>
      </table>
       ";

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
echo     "<th>".$langUserStatus."</th>";
echo     "<th>".$langAllUserOfThisCourse."</th>
          <th>".$langEditUserSettings."</th>
          <th>".$langDelete."</th>";
echo "</tr><tbody> ";

   // Start the list of users...
foreach($resultList as $list)
//while ($list = mysql_fetch_array($query))
{
     echo "<tr>";

     //  Id

     echo "<td align=\"center\">".$list['user_id']."
           </td>";


     if (isset($_SESSION['admin_user_search'])&& ($_SESSION['admin_user_search']!="")) {  //trick to prevent "//1" display when no keyword used in search 

         // name

         echo "<td align=\"left\">".eregi_replace("(".$_SESSION['admin_user_search'].")",'<b>\\1</b>', $list['nom'])."</td>";

         //  Firstname

         echo "<td align=\"left\">".eregi_replace("(".$_SESSION['admin_user_search'].")","<b>\\1</b>", $list['prenom'])."</td>";
     }
     else
     {
         // name

         echo "<td align=\"left\">".$list['nom']."</td>";

         //  Firstname

         echo "<td align=\"left\">".$list['prenom']."</td>";
     }

     //  Official code

     if (isset($list['officialCode'])) { $toAdd = $list['officialCode']; } else $toAdd = " - ";
     echo "<td align=\"center\">".$toAdd."</td>";


     if (isset($_SESSION['admin_user_search'])&& ($_SESSION['admin_user_search']!="")) {

         // mail

         echo "<td align=\"left\">".eregi_replace("(".$_SESSION['admin_user_search'].")",'<b>\\1</b>', $list['email'])."</td>";

     }
     else
     {
         // mail

         echo "<td align=\"left\">".$list['email']."</td>";

     }

     // Status

     if (isAdminUser($list['user_id']))
     {
        $userStatus = $langAdministrator;
     }
     else
     {
        if ($list['statut']==1)
        {
          $userStatus = $langCourseCreator;
        }
        else
        {
          $userStatus = $langStudent;
        }
     }

     echo     "<td align=\"center\">\n
                         ".$userStatus.
              "</td>\n";

     // All course of this user

     echo     "<td align=\"center\">\n",
                        "<a href=\"adminusercourses.php?uidToEdit=".$list['user_id']."&cfrom=ulist".$addToURL."\">\n
                         ".$langViewList."\n",
                        "</a>\n",
                        "</td>\n";

     // Modify link

     echo     "<td align=\"center\">\n",
                        "<a href=\"adminprofile.php?uidToEdit=".$list['user_id']."&cfrom=ulist".$addToURL."\">\n
                         <img src=\"".$clarolineRepositoryWeb."img/usersetting.gif\" border=\"0\" alt=\"".$langEditUserSettings."\" />\n",
                        "</a>\n",
                        "</td>\n";

     //  Delete link

     echo   "<td align=\"center\">\n",
                "<a href=\"",$PHP_SELF,"?cmd=delete&user_id=".$list['user_id']."&offset=".$offset."".$addToURL."\" ",
                "onClick=\"return confirmation('",addslashes($list['username']),"');\">\n",
                "<img src=\"".$clarolineRepositoryWeb."img/deluser.gif\" border=\"0\" alt=\"".$langDelete."\" />\n",
                "</a>\n",
            "</td>\n";
     echo "</tr>";
     $atLeastOne= true;
}
   // end display users table
if (!$atLeastOne)
{
   echo "<tr>
          <td colspan=\"8\" align=\"center\">
            ".$langNoUserResult."<br>
            <a href=\"advancedUserSearch.php".$addtoAdvanced."\">".$langSearchAgain."</a>
          </td>
         </tr>";
}
echo "</tbody></table>";

//Pager

$myPager->disp_pager_tool_bar($PHP_SELF);

?>

<?
function isAdminUser($user_id)
{
    global $tbl_admin;

    $sql = "SELECT * FROM `".$tbl_admin."` WHERE `idUser`=".$user_id."";
    $result = mysql_query($sql);
    if (mysql_num_rows($result)>0)
    {
      return true;
    }
    else
    {
      return false;
    }
}

include($includePath."/claro_init_footer.inc.php");

?>