<?php # $Id$
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

$langFile='admin';
$cidReset = true;
require '../inc/claro_init_global.inc.php';

$tbl_log    = $mainDbName."`.`loginout";
$tbl_user   = $mainDbName."`.`user";
$tbl_admin  = $mainDbName."`.`admin";
$tbl_course = $mainDbName."`.`cours";


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
$nameTools = $langCourseList;


// initialisation of global variables and used libraries

include($includePath."/lib/pager.lib.php");
include($includePath."/lib/admin.lib.inc.php");

//SECURITY CHECK

if (!$is_platformAdmin) treatNotAuthorized();

include($includePath.'/claro_init_header.inc.php');
$is_allowedToAdmin     = $is_platformAdmin;
$coursePerPage= 20;

//------------------------------------------------------------------------------------------------------------------------
//  USED SESSION VARIABLES
//------------------------------------------------------------------------------------------------------------------------
// deal with session variables for search criteria, it depends where we come from :
// 1 ) we must be able to get back to the list that concerned the criteria we previously used (with out re entering them)
// 2 ) we must be able to arrive with new critera for a new search.


if (isset($_GET['code'])) {$_SESSION['admin_course_code'] = $_GET['code'];}
if (isset($_GET['letter'])) {$_SESSION['admin_course_letter'] = $_GET['letter'];}
if (isset($_GET['search'])) {$_SESSION['admin_course_search'] = $_GET['search'];}
if (isset($_GET['intitule'])) {$_SESSION['admin_course_intitule'] = $_GET['intitule'];}
if (isset($_GET['category'])) {$_SESSION['admin_course_category'] = $_GET['category'];}
if (isset($_GET['language'])) {$_SESSION['admin_course_language'] = $_GET['language'];}
if (isset($_GET['access'])) {$_SESSION['admin_course_access'] = $_GET['access'];}
if (isset($_GET['subscription'])) {$_SESSION['admin_course_subscription'] = $_GET['subscription'];}
if (isset($_GET['order_crit'])) {$_SESSION['admin_course_order_crit'] = $_GET['order_crit'];}
if (isset($_GET['dir']))       {$_SESSION['admin_course_dir'] = $_GET['dir'];}

// clean session if we come from a course

session_unregister('_cid');
unset($_cid);


// Set parameters to add to URL to know where we come from and what options will be given to the user

$addToURL = "";

if (!isset($cfrom) || $cfrom!="clist") //offset not kept when come from another list
{
   $addToURL .= "&offsetC=".$offsetC;
}


//----------------------------------
// EXECUTE COMMAND
//----------------------------------

switch ($cmd)
{
  case "delete" :
        delete_course($delCode);
        $dialogBox = $langCourseDelete;
        break;
}

//----------------------------------
// Build query and find info in db
//----------------------------------

   // main query to know what must be displayed in the list

$sql = "SELECT  *
        FROM `".$tbl_course."` AS C WHERE 1=1 ";

//deal with LETTER classification call

if (isset($_SESSION['admin_course_letter']))
{
    $toAdd = " AND C.`intitule` LIKE '".$_SESSION['admin_course_letter']."%' ";
    $sql.=$toAdd;

}

//deal with KEY WORDS classification call

if (isset($_SESSION['admin_course_search']))
{
    $toAdd = " AND (C.`intitule` LIKE '%".$_SESSION['admin_course_search']."%' OR C.`code` LIKE '%".$_SESSION['admin_course_search']."%'
               OR C.`faculte` LIKE '%".$_SESSION['admin_course_search']."%'
               )";
    $sql.=$toAdd;

}

//deal with ADVANCED SEARCH parmaters call

if (isset($_SESSION['admin_course_intitule']))    // title of the course keyword is used
{
    $toAdd = " AND (C.`intitule` LIKE '".$_SESSION['admin_course_intitule']."%') ";
    $sql.=$toAdd;

}

if (isset($_SESSION['admin_course_code']))        // code keyword is used
{
    $toAdd = " AND (C.`fake_code` LIKE '".$_SESSION['admin_course_code']."%') ";
    $sql.=$toAdd;

}

if (isset($_SESSION['admin_course_category']))     // course category keyword is used
{
    $toAdd = " AND (C.`faculte` LIKE '".$_SESSION['admin_course_category']."%') ";
    $sql.=$toAdd;

}

if (isset($_SESSION['admin_course_language']))    // language filter is used
{
    $toAdd = " AND (C.`languageCourse` LIKE '".$_SESSION['admin_course_language']."%') ";
    $sql.=$toAdd;

}

if (isset($_SESSION['admin_course_access']))     // type of access to course filter is used
{
    $toAdd = "";
    if ($_SESSION['admin_course_access']=="private")
    {
       $toAdd = " AND (C.`visible`=1 OR C.`visible`=0) ";
    }
    elseif ($_SESSION['admin_course_access']=="public")
    {
       $toAdd = " AND (C.`visible`=2 OR C.`visible`=3) ";
    }

    $sql.=$toAdd;

}

if (isset($_SESSION['admin_course_subscription']))   // type of subscription allowed is used
{
    $toAdd = "";
    if ($_SESSION['admin_course_subscription']=="allowed")
    {
       $toAdd = " AND (C.`visible`=2 OR C.`visible`=0) ";
    }
    elseif ($_SESSION['admin_course_subscription']=="denied")
    {
       $toAdd = " AND (C.`visible`=1 OR C.`visible`=3) ";
    }

    $sql.=$toAdd;

}

  //first see is direction must be changed

if (isset($chdir) && ($chdir=="yes"))
{
  if ($_SESSION['admin_course_dir'] == "ASC") {$_SESSION['admin_course_dir']="DESC";}
  elseif ($_SESSION['admin_course_dir'] == "DESC") {$_SESSION['admin_course_dir']="ASC";}
  else $_SESSION['admin_course_dir'] = "DESC";
}

// deal with REORDER

if (isset($_SESSION['admin_course_order_crit']))
{
    $toAdd = " ORDER BY `".$_SESSION['admin_course_order_crit']."` ".$_SESSION['admin_course_dir'];
}
else
{
    $toAdd = " ORDER BY `cours_id` ASC";
}

$sql.=$toAdd;


//echo $sql."<br>";

//USE PAGER

$myPager = new claro_sql_pager($sql, $offsetC, $coursePerPage);
$myPager->set_pager_call_param_name('offsetC');
$resultList = $myPager->get_result_list();

//----------------------------------
// DISPLAY
//----------------------------------

//display title

claro_disp_tool_title($nameTools);

// display forms and dialogBox, alphabetic choice,...

if($dialogBox)
{
   claro_disp_message_box($dialogBox);
}

/*
//Display selectbox, alphabetic choice, and advanced search link search

  // ALPHABETIC

echo "<form name=\"indexform\" action=\"",$PHP_SELF,"\" method=\"GET\">
             ";
            if (isset($uidToEdit)) {$toAdd = "uidToEdit=".$uidToEdit."&";} else {$toAdd = "";}
            if (isset($doRegister)) {$toAdd .= "doRegister=true&";}

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
            <input type=\"hidden\" name=\"uidToEdit\" value=\"".$uidToEdit."\">
            <input type=\"submit\" value=\"".$langSearch."\">

      </form>
     ";
 */
     //TOOL LINKS

   //Display search form


      //see passed search parameters :

if ($_GET['search']!="")    {$isSearched .= $_GET['search']."* ";}
if ($_GET['code']!="") {$isSearched .= $langCode." = ".$_GET['code']."* ";}
if ($_GET['intitule']!="")  {$isSearched .= $langCourseTitle." = ".$_GET['intitule']."* ";}
if ($_GET['category']!="")  {$isSearched .= $langCategory." = ".$_GET['category']." ";}
if ($_GET['language']!="")      {$isSearched .= $langLanguage." : ".$_GET['language']." ";}
if ($_GET['access']=="public")    {$isSearched .= " <b><br>".$langPublicOnly." </b> ";}
if ($_GET['access']=="private")    {$isSearched .= " <b><br>".$langPrivateOnly." </b>  ";}
if ($_GET['subscription']=="allowed")    {$isSearched .= " <b><br>".$langSubscriptionAllowedOnly." </b>  ";}
if ($_GET['subscription']=="denied")    {$isSearched .= " <b><br>".$langSubscriptionDeniedOnly." </b>  ";}

     //see what must be kept for advanced links

$addtoAdvanced = "?code=".$_GET['code'];
$addtoAdvanced .="&intitule=".$_GET['intitule'];
$addtoAdvanced .="&category=".$_GET['category'];
$addtoAdvanced .="&language=".$_GET['language'];
$addtoAdvanced .="&access=".$_GET['access'];
$addtoAdvanced .="&subscription=".$_GET['subscription'];

    //fianly, form itself

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
            <input type=\"text\" value=\"".$_GET['search']."\" name=\"search\" id=\"search\"\">
            <input type=\"submit\" value=\" ".$langOk." \">
            <input type=\"hidden\" name=\"newsearch\" value=\"yes\">
            [<a href=\"advancedCourseSearch.php".$addtoAdvanced."\"><small>".$langAdvanced."</small></a>]
            </form>
          </td>
        </tr>
      </table>
       ";


   //Pager

$myPager->disp_pager_tool_bar($PHP_SELF);

// display list

echo "<table class=\"claroTable\" width=\"100%\" border=\"0\" cellspacing=\"2\">
       <tr class=\"headerX\" align=\"center\" valign=\"top\">
       ";

     //add titles for the table

echo "<th><a href=\"",$PHP_SELF,"?order_crit=fake_code&chdir=yes\">".$langCode."</a></th>"
     ."<th><a href=\"",$PHP_SELF,"?order_crit=intitule&chdir=yes\">".$langCourseTitle."</a></th>"
     ."<th><a href=\"",$PHP_SELF,"?order_crit=faculte&chdir=yes\">".$langCategory."</a></th>";

echo "<th>".$langAllUsersOfThisCourse."</th>";
echo "<th>".$langCourseSettings."</th>"
     ."<th>".$langDelete."</th>";

   // Display list of the course of the user :

foreach($resultList as $list)
{
    echo "<tr>";


    if (isset($_SESSION['admin_course_search'])&& ($_SESSION['admin_course_search']!="")) //trick to prevent "//1" display when no keyword used in search
    {

         //  Code

         echo "<td >".eregi_replace("(".$_SESSION['admin_course_search'].")","<b>\\1</b>", $list['fake_code'])."
               </td>";

         // title

         echo "<td align=\"left\"><a href=\"".$coursesRepositoryWeb.$list['directory']."\">".eregi_replace("(".$_SESSION['admin_course_search'].")","<b>\\1</b>", $list['intitule'])."</a></td>";

         //  Category

         echo "<td align=\"left\">".eregi_replace("(".$_SESSION['admin_course_search'].")","<b>\\1</b>", $list['faculte'])."</td>";
     }
     else
     {
          //  Code

         echo "<td >".$list['fake_code']."
               </td>";

         // title

         echo "<td align=\"left\"><a href=\"".$coursesRepositoryWeb.$list['directory']."\">".$list['intitule']."</a></td>";

         //  Category

         echo "<td align=\"left\">".$list['faculte']."</td>";
    }



     //  All users of this course

     echo     "<td align=\"center\">\n",
                        "<a href=\"admincourseusers.php?cidToEdit=".$list['code'].$addToURL."&cfrom=clist\"><img src=\"".$clarolineRepositoryWeb."/img/membres.gif\" border=\"0\" alt=\"$langAllUsersOfThisCourse\" />\n
                         \n",
                        "</a>\n",
                        "</td>\n";

    // Modify course settings

    echo  "<td align=\"center\">\n
           <a href=\"../course_info/infocours.php?cidToEdit=".$list['code'].$addToURL."&cfrom=clist\"><img src=\"".$clarolineRepositoryWeb."img/referencement.gif\" alt=\"$langCourseSettings\"></a>
           </td>\n";

    //  Delete link


    echo   "<td align=\"center\">\n",
                "<a href=\"",$PHP_SELF,"?cmd=delete&delCode=".$list['code'].$addToURL."\" ",
                "onClick=\"return confirmation('",addslashes($list['intitule']),"');\">\n",
                "<img src=\"".$clarolineRepositoryWeb."/img/delete.gif\" border=\"0\" alt=\"$langDelete\" />\n",
                "</a>\n",
            "</td>\n";
     echo "</tr>";
     $atleastOneResult = true;
}

if ($atleastOneResult != true)
{
   echo "<tr>
          <td colspan=\"6\" align=\"center\">
            ".$langNoCourseResult."<br>
            <a href=\"advancedCourseSearch.php".$addtoAdvanced."\">".$langSearchAgain."</a>
          </td>
         </tr>";
}
echo "<tbody></table>";

//Pager

$myPager->disp_pager_tool_bar($PHP_SELF);

// display footer

include($includePath."/claro_init_footer.inc.php");

?>