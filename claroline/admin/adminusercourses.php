<?php
$langFile='admin';
$cidReset = true;
include('../inc/claro_init_global.inc.php');

$tbl_log     = $mainDbName."`.`loginout";
$tbl_user     = $mainDbName."`.`user";
$tbl_admin  = $mainDbName."`.`admin";
$tbl_course = $mainDbName."`.`cours";
$tbl_course_user = $mainDbName."`.`cours_user";

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
            function confirmationUnReg (name)
            {
                if (confirm(\"".$langAreYouSureToUnsubscribe."\"+ name + \"? \"))
                    {return true;}
                else
                    {return false;}
            }
            </script>";

// Deal with interbredcrumps

$interbredcrump[]= array ("url"=>$rootAdminWeb, "name"=> $langAdministrationTools);
$nameTools = $langUserCourseList;


// initialisation of global variables and used libraries

include($includePath.'/claro_init_header.inc.php');
include($includePath."/lib/admin.lib.inc.php");
include($includePath."/lib/pager.lib.php");

if (! $_uid) exit("<center>You're not logged in !!</center></body>");
if ($uidToEdit=="") {$dialogBox ="ERROR : NO USER SET!!!";}

$coursePerPage= 20;

//----------------------------------
// EXECUTE COMMAND
//----------------------------------

switch ($cmd)
{
  case "unsubscribe" :
        $done = remove_user_from_course($uidToEdit, $code);
        if ($done)
        {
           $dialogBox = $langUserUnsubscribed;
        }
        else
        {
           $dialogBox = $langUserNotUnsubscribed;
        }
        break;
}

//----------------------------------
// Build query and find info in db
//----------------------------------

  // needed to display the name of the user we are watching

$sqlTitle = "SELECT *
             FROM `".$tbl_user."`
             WHERE `user_id` = ".$uidToEdit."
             ";
$queryTitle = claro_sql_query($sqlTitle);
$resultTitle = mysql_fetch_array($queryTitle);

   // main query to know what must be displayed in the list

$sql = "SELECT  *
        FROM `".$tbl_course."` AS C ";

$toAdd = ", `".$tbl_course_user."` AS CU ";
$toAdd .="WHERE CU.`code_cours` = C.`code`  AND CU.`user_id` = ".$uidToEdit;

$sql.=$toAdd;


//deal with LETTER classification call

if (isset($_GET['letter']))
{
    $toAdd = " AND C.`intitule` LIKE '".$_GET['letter']."%' ";
    $sql.=$toAdd;

}

//deal with KEY WORDS classification call

if (isset($_GET['search']))
{

    $toAdd = " AND (C.`intitule` LIKE '".$_GET['search']."%' OR C.`code` LIKE '".$_GET['search']."%')";
    $sql.=$toAdd;

}

// deal with REORDER

if (isset($_GET['order_crit']))
{
    $toAdd = " ORDER BY `".$_GET['order_crit']."` ".$_GET['dir'];
}
else
{
    $toAdd = " ORDER BY C.`cours_id` ASC";
}

$sql.=$toAdd;


//echo $sql."<br>";

$myPager = new claro_sql_pager($sql, $offset, $coursePerPage);
$resultList = $myPager->get_result_list();


//----------------------------------
// DISPLAY
//----------------------------------

  //display title

$nameTools .= " : ".$resultTitle['prenom']." ".$resultTitle['nom'];

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
            <input type=\"text\" name=\"search\"></input>
            <input type=\"hidden\" name=\"uidToEdit\" value=\"".$uidToEdit."\">
            <input type=\"submit\" value=\"".$langSearch."\">

      </form>
     ";
 */
     //TOOL LINKS

echo "<a class=\"claroButton\" href=\"adminprofile.php?uidToEdit=".$uidToEdit."\" > See user settings</a>";
echo "<a class=\"claroButton\" href=\"../auth/courses.php?cmd=rqReg&uidToEdit=".$uidToEdit."&category=\" > Enroll to a new course</a>";

if (isset($cfrom) && $cfrom=="ulist")  //if we come form user list, we must display go back to list
{
    echo "<a class=\"claroButton\" href=\"adminusers.php\"> ".$langBackToUserList." </a>\n";
}

//Pager

$myPager->disp_pager_tool_bar($PHP_SELF."?uidToEdit=".$uidToEdit);

// display User's course list

     // table

echo "<table class=\"claroTable\" width=\"100%\" border=\"0\" cellspacing=\"2\">
       <tr class=\"headerX\" align=\"center\" valign=\"top\">
       ";

     //add titles for the table

echo "<th>".$langOfficialCode."</th>".
     "<th>".$langCourseTitle."</th>";
echo "<th>".$langTitular."</th>";
echo "<th>".$langEditUserCourseSetting."</th>";
echo "<th>".$langUnsubscribe."</th>";


   // Display list of the course of the user :

foreach($resultList as $list)
{
    echo "<tr>";

     //  Code

     echo "<td align=\"center\">".$list['fake_code']."</td>";

     // title

     echo "<td align=\"left\">".$list['intitule']."</td>";

     //  Titular

     echo "<td align=\"left\">".$list['titulaires']."</td>";

    // Edit user course settings

    echo  "<td align=\"center\">\n
           <a href=\"adminUserCourseSettings.php?cidToEdit=".$list['code']."&uidToEdit=".$uidToEdit."&ccfrom=uclist\"><img src=\"../img/edit.gif\"></a>
           </td>\n";

    //  Unsubscribe link

    echo   "<td align=\"center\">\n";


             if (isset($list['user_id']))
             {
                if (isset($doRegister)) {$toAdd = "&doRegister=true";} else {$toAdd ="";}
                echo
                 "<a href=\"",$PHP_SELF,"?uidToEdit=".$uidToEdit."".$toAdd."&cmd=unsubscribe&code=".$list['code']."\" ",
                     "onClick=\"return confirmationUnReg('",addslashes($resultTitle['prenom']." ".$resultTitle['nom']),"');\">\n
                     <img src=\"".$clarolineRepositoryWeb."/img/unenroll.gif\" border=\"0\" alt=\"$langDelete\" />\n
                  </a>\n";
             }
             else
             {
                echo " - ";
             }

            "</td>\n";
     echo "</tr>";
     $atLeastOne = true;
}

if (!$atLeastOne)
{
    echo "<tr>
           <td colspan=\"5\" align=\"center\">
           ".$langUserHasNoCourse."
           </td>
          </tr>";
}


echo "<tbody></table>";

//Pager

$myPager->disp_pager_tool_bar($PHP_SELF."?uidToEdit=".$uidToEdit);

// display footer

include($includePath."/claro_init_footer.inc.php");

?>