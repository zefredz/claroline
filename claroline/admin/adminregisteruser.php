<?php //$Id$
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

if ($cidToEdit=="") {$dialogBox ="ERROR : NO USER SET!!!";}


@include ($includePath."/installedVersion.inc.php");

// Deal with interbredcrumps

$interbredcrump[]= array ("url"=>$rootAdminWeb, "name"=> $langAdministrationTools);
$nameTools = $langEnrollUser;

//Header

include($includePath."/claro_init_header.inc.php");

//TABLES

$tbl_user          = $mainDbName."`.`user";
$tbl_courses       = $mainDbName."`.`cours";
$tbl_course_user   = $mainDbName."`.`cours_user";
$tbl_admin         = $mainDbName."`.`admin";
$tbl_todo          = $mainDbName."`.`todo";
$tbl_track_default = $statsDbName."`.`track_e_default";// default_user_id
$tbl_track_login   = $statsDbName."`.`track_e_login";    // login_user_id

// See SESSION variables used for reorder criteria :

if (isset($_GET['dir']))       {$_SESSION['admin_register_dir'] = $_GET['dir'];}
if (isset($_GET['order_crit'])){$_SESSION['admin_register_order_crit'] = $_GET['order_crit'];}



//------------------------------------
// Execute COMMAND section
//------------------------------------

switch ($cmd)
{
  case "sub" : //execute subscription command...
        if ($subas=="teach")   //  ... as teacher
        {
           if (!isRegisteredTo($user_id, $cidToEdit))    //..add user and set as teacher
           {
               $done = add_user_to_course($user_id, $cidToEdit);
               $properties['status'] = 1;
               $properties['role']   = "Professor";
               $properties['tutor']  = 1;
               update_user_course_properties($user_id, $cidToEdit, $properties);
           }
           else                        //.. only set as teacher
           {
               $properties['status'] = 1;
               $properties['role']   = "Professor";
               $properties['tutor']  = 1;
               update_user_course_properties($user_id, $cidToEdit, $properties);
           }
        }
        elseif ($subas=="stud")  // ... as student
        {
          if (!isRegisteredTo($user_id, $cidToEdit)) //add new user (student is default)
          {
                $done = add_user_to_course($user_id, $cidToEdit);

          }
          else                   // only set as student
          {
               if ($user_id==$_uid) {$dialogBox = $langNotUnregYourself;}
               $properties['status'] = 5;
               $properties['role']   = "Student";
               $properties['tutor']  = 0;
               update_user_course_properties($user_id, $cidToEdit, $properties);
          }
        }

        //set dialogbox message

        if ($done)
        {
           $dialogBox =$langUserSubscribed;
        }
        break;

  case "unsubscribe" :
        $done = remove_user_from_course($user_id, $cidToEdit);
        if ($done)
        {
           $dialogBox =$langUserUnsubscribed;
        }
        else
        {
           $dialogBox =$langUserNotUnsubscribed;
        }
        break;
}

//build and call DB to get info about current course (for title) if needed :

$sql = "SELECT *
        FROM  `".$tbl_courses."`
        WHERE `code`='".$cidToEdit."'
        ";
$queryCourse =  claro_sql_query($sql);
$resultCourse = mysql_fetch_array($queryCourse);

//----------------------------------
// Build query and find info in db
//----------------------------------

$sql = "
SELECT 
    U.nom, U.prenom, U.`user_id` AS ID, 
    CU.*,CU.`user_id` AS Register
FROM  `".$tbl_user."` AS U";

$toAdd = "
LEFT JOIN `".$tbl_course_user."` AS CU 
    ON             CU.`user_id`=U.`user_id` 
            AND CU.`code_cours` = '".$cidToEdit."'
        ";

$sql.=$toAdd;

//deal with LETTER classification call

if (isset($_GET['letter']))
{
    $toAdd = "
            AND U.`nom` LIKE '".$_GET['letter']."%' ";
    $sql.=$toAdd;
}

//deal with KEY WORDS classification call

if (isset($_GET['search']) && $_GET['search']!="")
{
    $toAdd = " WHERE (U.`nom` LIKE '".$_GET['search']."%'
              OR U.`username` LIKE '".$_GET['search']."%'
              OR U.`prenom` LIKE '".$_GET['search']."%') ";

    $sql.=$toAdd;
}

// deal with REORDER

  //first see is direction must be changed

if (isset($chdir) && ($chdir=="yes"))
{
  if ($_SESSION['admin_register_dir'] == "ASC") {$_SESSION['admin_register_dir']="DESC";}
  elseif ($_SESSION['admin_register_dir'] == "DESC") {$_SESSION['admin_register_dir']="ASC";}
  else $_SESSION['admin_register_dir'] = "DESC";
}

if (isset($_SESSION['admin_register_order_crit']))
{
    if ($_SESSION['admin_register_order_crit']=="user_id")
    {
        $toAdd = " ORDER BY `U`.`user_id` ".$_SESSION['admin_register_dir'];
    }
    else
    {
        $toAdd = " ORDER BY `".$_SESSION['admin_register_order_crit']."` ".$_SESSION['admin_register_dir'];
    }
    $sql.=$toAdd;
}

$myPager = new claro_sql_pager($sql, $offset, $userPerPage);
$resultList = $myPager->get_result_list();


//------------------------------------
// DISPLAY
//------------------------------------

// Display tool title

$nameTools .= " : ".$resultCourse['intitule'];

claro_disp_tool_title($nameTools);

// Display Forms or dialog box(if needed)

if($dialogBox)
  {
    claro_disp_message_box($dialogBox);
  }

//Display selectbox, alphabetic choice, and advanced search link search

  // ALPHABETIC SEARCH
/*
echo "<form name=\"indexform\" action=\"".$_SERVER['PHP_SELF']."\" method=\"GET\">
             ";

            if (isset($cidToEdit)) {$toAdd = "cidToEdit=".$cidToEdit;} else {$toAdd = "";}

            echo "<a href=\"".$_SERVER['PHP_SELF']."?".$toAdd."\"><b> ".$langAll."</b></a> | ";

            echo "<a href=\"".$_SERVER['PHP_SELF']."?letter=A&".$toAdd."\">A</a> | ";
            echo "<a href=\"".$_SERVER['PHP_SELF']."?letter=B&".$toAdd."\">B</a> | ";
            echo "<a href=\"".$_SERVER['PHP_SELF']."?letter=C&".$toAdd."\">C</a> | ";
            echo "<a href=\"".$_SERVER['PHP_SELF']."?letter=D&".$toAdd."\">D</a> | ";
            echo "<a href=\"".$_SERVER['PHP_SELF']."?letter=E&".$toAdd."\">E</a> | ";
            echo "<a href=\"".$_SERVER['PHP_SELF']."?letter=F&".$toAdd."\">F</a> | ";
            echo "<a href=\"".$_SERVER['PHP_SELF']."?letter=G&".$toAdd."\">G</a> | ";
            echo "<a href=\"".$_SERVER['PHP_SELF']."?letter=H&".$toAdd."\">H</a> | ";
            echo "<a href=\"".$_SERVER['PHP_SELF']."?letter=I&".$toAdd."\">I</a> | ";
            echo "<a href=\"".$_SERVER['PHP_SELF']."?letter=J&".$toAdd."\">J</a> | ";
            echo "<a href=\"".$_SERVER['PHP_SELF']."?letter=K&".$toAdd."\">K</a> | ";
            echo "<a href=\"".$_SERVER['PHP_SELF']."?letter=L&".$toAdd."\">L</a> | ";
            echo "<a href=\"".$_SERVER['PHP_SELF']."?letter=M&".$toAdd."\">M</a> | ";
            echo "<a href=\"".$_SERVER['PHP_SELF']."?letter=N&".$toAdd."\">N</a> | ";
            echo "<a href=\"".$_SERVER['PHP_SELF']."?letter=O&".$toAdd."\">O</a> | ";
            echo "<a href=\"".$_SERVER['PHP_SELF']."?letter=P&".$toAdd."\">P</a> | ";
            echo "<a href=\"".$_SERVER['PHP_SELF']."?letter=Q&".$toAdd."\">Q</a> | ";
            echo "<a href=\"".$_SERVER['PHP_SELF']."?letter=R&".$toAdd."\">R</a> | ";
            echo "<a href=\"".$_SERVER['PHP_SELF']."?letter=S&".$toAdd."\">S</a> | ";
            echo "<a href=\"".$_SERVER['PHP_SELF']."?letter=T&".$toAdd."\">T</a> | ";
            echo "<a href=\"".$_SERVER['PHP_SELF']."?letter=U&".$toAdd."\">U</a> | ";
            echo "<a href=\"".$_SERVER['PHP_SELF']."?letter=V&".$toAdd."\">V</a> | ";
            echo "<a href=\"".$_SERVER['PHP_SELF']."?letter=W&".$toAdd."\">W</a> | ";
            echo "<a href=\"".$_SERVER['PHP_SELF']."?letter=X&".$toAdd."\">X</a> | ";
            echo "<a href=\"".$_SERVER['PHP_SELF']."?letter=Y&".$toAdd."\">Y</a> | ";
            echo "<a href=\"".$_SERVER['PHP_SELF']."?letter=Z&".$toAdd."\">Z</a>";
            echo "
            <input type=\"text\" name=\"search\">
            <input type=\"hidden\" name=\"cidToEdit\" value=\"".$cidToEdit."\">
            <input type=\"submit\" value=\"".$langSearch."\">

      </form>
     ";
*/
     //TOOL LINKS

claro_disp_button("admincourseusers.php?cidToEdit=".$cidToEdit,$langAllUsersOfThisCourse);

       // search form
       
if ($_GET['search']!="")    {$isSearched .= $_GET['search']."* ";}
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
            <form action=\"".$_SERVER['PHP_SELF']."\">
            <label for=\"search\">".$langMakeSearch."</label> :
            <input type=\"text\" value=\"".$_GET['search']."\" name=\"search\" id=\"search\" >
            <input type=\"submit\" value=\" ".$langOk." \">
            <input type=\"hidden\" name=\"newsearch\" value=\"yes\">
            <input type=\"hidden\" name=\"cidToEdit\" value=\"".$cidToEdit."\">
            </form>
          </td>
        </tr>
      </table>
       ";


//Pager

if (isset($_GET['order_crit']))
{
  $addToURL = "&order_crit=".$_GET['order_crit']."&dir=".$_GET['dir'];
}

$myPager->disp_pager_tool_bar($PHP_SELF."?cidToEdit=".$cidToEdit.$addToURL);

// Display list of users
   // start table...
   //columns titles...

echo "<table class=\"claroTable\" width=\"100%\" border=\"0\" cellspacing=\"2\">

    <tr class=\"headerX\" align=\"center\" valign=\"top\">
       <th><a href=\"".$_SERVER['PHP_SELF']."?order_crit=user_id&chdir=yes&search=".$search."&cidToEdit=".$cidToEdit."\">".$langUserid."</a></th>
       <th><a href=\"".$_SERVER['PHP_SELF']."?order_crit=nom&chdir=yes&search=".$search."&cidToEdit=".$cidToEdit."\">".$langName."</a></th>
       <th><a href=\"".$_SERVER['PHP_SELF']."?order_crit=prenom&chdir=yes&search=".$search."&cidToEdit=".$cidToEdit."\">".$langFirstName."</a></th>";

echo "<th>".$langEnrollAsStudent."</th>
      <th>".$langEnrollAsManager."</th>";

echo "</tr><tbody> ";

   // Start the list of users...

if (isset($order_crit))
{
    $addToURL = "&order_crit=".$order_crit;
}
if (isset($offset))
{
    $addToURL = "&offset=".$offset;
}
foreach($resultList as $list)
{
     echo "<tr>";

     //  Id

     echo "<td align=\"center\">".$list['ID']."
           </td>";

    if (isset($_GET['search'])&& ($_GET['search']!="")) {

         // name

         echo "<td align=\"left\">".eregi_replace("^(".$_GET['search'].")",'<b>\\1</b>', $list['nom'])."</td>";

         //  Firstname

         echo "<td align=\"left\">".eregi_replace("^(".$_GET['search'].")","<b>\\1</b>", $list['prenom'])."</td>";
     }
     else
     {
         // name

         echo "<td align=\"left\">".$list['nom']."</td>";

         //  Firstname

         echo "<td align=\"left\">".$list['prenom']."</td>";
     }

     if ($list['statut'] == null)  // user is not enrolled
     {
         // Register as user

         echo  "<td align=\"center\">\n",
                    "<a href=\"".$_SERVER['PHP_SELF']."?cidToEdit=".$cidToEdit."&cmd=sub&search=".$search."&user_id=".$list['ID']."&subas=stud".$addToURL."\" ",
                    ">\n",
                    "<img src=\"".$clarolineRepositoryWeb."img/enroll.gif\" border=\"0\" alt=\"$langSubscribeUser\" />\n",
                    "</a>\n",
                "</td>\n";

         //register as teacher

         echo  "<td align=\"center\">\n",
                        "<a href=\"".$_SERVER['PHP_SELF']."?cidToEdit=".$cidToEdit."&cmd=sub&search=".$search."&user_id=".$list['ID']."&subas=teach".$addToURL."\" ",
                        ">\n",
                        "<img src=\"".$clarolineRepositoryWeb."img/enroll.gif\" border=\"0\" alt=\"$langSubscribeUser\" />\n",
                        "</a>\n",
                    "</td>\n";

     }
     elseif ($list['statut'] == "1") // user is already enrolled but as teacher
     {
        //Register as user

        echo  "<td align=\"center\">\n",
                    "<a href=\"".$_SERVER['PHP_SELF']."?cidToEdit=".$cidToEdit."&cmd=sub&search=".$search."&user_id=".$list['ID']."&subas=stud".$addToURL."\" ",
                    ">\n",
                    "<img src=\"".$clarolineRepositoryWeb."img/enroll.gif\" border=\"0\" alt=\"".$langSubscribeUser."\" />\n",
                    "</a>\n",
                "</td>\n";

        // already enrolled as teacher

         echo  "<td align=\"center\" >\n
                 <small>",
                 $langAlreadyEnrolled,
                "</small>
               </td>
              \n";

     }

     elseif ($list['statut'] == "5")  // user is already enrolled but as student
     {

        // already enrolled as student

        echo  "<td align=\"center\" >\n
                 <small>",
                 $langAlreadyEnrolled,
                "</small>
               </td>
              \n";

        //register as teacher

         echo  "<td align=\"center\">\n",
                        "<a href=\"".$_SERVER['PHP_SELF']."?cidToEdit=".$cidToEdit."&cmd=sub&search=".$search."&user_id=".$list['ID']."&subas=teach".$addToURL."\" ",
                        ">\n",
                        "<img src=\"".$clarolineRepositoryWeb."img/enroll.gif\" border=\"0\" alt=\"".$langSubscribeUser."\" />\n",
                        "</a>\n",
                    "</td>\n";

      }

     echo "</tr>";
}

   // end display users table

echo "</tbody></table>";

//Pager

$myPager->disp_pager_tool_bar($PHP_SELF."?cidToEdit=".$cidToEdit.$addToURL);

?>

<?
include($includePath."/claro_init_footer.inc.php");
?>