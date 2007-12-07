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

$cidReset=TRUE;
$gidReset=TRUE;
$tidReset=TRUE;

// clean session if we come from a course

session_unregister('_cid');
unset($_cid);

require '../inc/claro_init_global.inc.php';

/* ************************************************************************** */
/*  Security Check
/* ************************************************************************** */

if (!$is_platformAdmin) claro_disp_auth_form();

/* ************************************************************************** */
/*  Initialise variables and include libraries
/* ************************************************************************** */

$userPerPage = 20; // numbers of user to display on the same page

// initialisation of global variables and used libraries

$iconForCuStatus['STUDENT']        = "user.gif";
$iconForCuStatus['COURSE_MANAGER'] = "manager.gif";

include($includePath."/lib/pager.lib.php");
include($includePath."/lib/admin.lib.inc.php");

if ($cidToEdit=="") {unset($cidToEdit);}
if ($cidToEdit=="") {$dialogBox ="ERROR : NO USER SET!!!";}

// javascript confirm pop up declaration

$htmlHeadXtra[] =
         "<script>
         function confirmationReg (name)
         {
             if (confirm(\"".clean_str_for_javascript($langAreYouSureToUnsubscribe)." \"+ name + \" ? \"))
                 {return true;}
             else
                 {return false;}
         }
         </script>";

// See SESSION variables used for reorder criteria :

if (isset($_REQUEST['order_crit']))   
                                 {$_SESSION['admin_course_user_order_crit']   = trim($_REQUEST['order_crit']) ;}
if (isset($_REQUEST['dir']))     {$_SESSION['admin_course_user_dir']          = ($_REQUEST['dir']=='DESC'?'DESC':'ASC');}

// Deal with interbredcrumps

$interbredcrump[]= array ("url"=>$rootAdminWeb, "name"=> $langAdministration);
$nameTools = $langAllUsersOfThisCourse;

//Header

include($includePath."/claro_init_header.inc.php");

//TABLES
$tbl_mdb_names   = claro_sql_get_main_tbl();
$tbl_user        = $tbl_mdb_names['user'  ];
$tbl_courses     = $tbl_mdb_names['course'];
$tbl_admin       = $tbl_mdb_names['admin' ];
$tbl_course_user = $tbl_mdb_names['rel_course_user' ];
$tbl_track_default = $tbl_mdb_names['track_e_default' ];

//------------------------------------
// Execute COMMAND section
//------------------------------------

switch ($cmd)
{
  case "delete" :
        delete_user($user_id);
        $dialogBox = $langUserDelete;
        break;

  case "unsub" :
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


$sql = "SELECT *, IF(CU.statut=1,'COURSE_MANAGER','STUDENT') `stat`
        FROM  `".$tbl_user."` AS U
        ";

$toAdd = ", `".$tbl_course_user."` AS CU WHERE CU.`user_id` = U.`user_id`
          AND CU.`code_cours` = '".$cidToEdit."'
        ";

$sql.=$toAdd;

//deal with LETTER classification call

if (isset($_REQUEST['letter']))
{
    $toAdd = "
             AND U.`nom` LIKE '".$_REQUEST['letter']."%'
             ";
    $sql.=$toAdd;
}

//deal with KEY WORDS classification call

if (isset($_REQUEST['search']))
{
    $toAdd = " AND ((U.`nom` LIKE '%".$_REQUEST['search']."%'
              OR U.`username` LIKE '%".$_REQUEST['search']."%'
              OR U.`prenom` LIKE '%".$_REQUEST['search']."%')) ";

    $sql.=$toAdd;
}

// deal with REORDER

  if (isset($_SESSION['admin_course_user_order_crit']))
{
	switch ($_SESSION['admin_course_user_order_crit'])
	{
		case 'uid'       : $fieldSort = 'U`.`user_id'; break;
		case 'name'      : $fieldSort = 'U`.`nom';     break;
		case 'firstname' : $fieldSort = 'U`.`prenom';  break;
		case 'cu_status' : $fieldSort = 'CU`.`statut'; break;
//		case 'email'  : $fieldSort = 'email';       
	}
    $toAdd = " ORDER BY `".$fieldSort."` ".$_SESSION['admin_course_user_dir'];
	$order[$_SESSION['admin_course_user_order_crit']] = ($_SESSION['admin_course_user_dir']=='ASC'?'DESC':'ASC');
    $sql.=$toAdd;
}

//echo $sql."<br>";

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
echo "<form name=\"indexform\" action=\"",$_SERVER['PHP_SELF'],"\" method=\"GET\">
             ";

            if (isset($cidToEdit)) {$toAdd = "cidToEdit=".$cidToEdit;} else {$toAdd = "";}

            echo "<a href=\"",$_SERVER['PHP_SELF'],"?".$toAdd."\"><b> ".$langAll."</b></a> | ";

            echo "<a href=\"",$_SERVER['PHP_SELF'],"?letter=A&".$toAdd."\">A</a> | ";
            echo "<a href=\"",$_SERVER['PHP_SELF'],"?letter=B&".$toAdd."\">B</a> | ";
            echo "<a href=\"",$_SERVER['PHP_SELF'],"?letter=C&".$toAdd."\">C</a> | ";
            echo "<a href=\"",$_SERVER['PHP_SELF'],"?letter=D&".$toAdd."\">D</a> | ";
            echo "<a href=\"",$_SERVER['PHP_SELF'],"?letter=E&".$toAdd."\">E</a> | ";
            echo "<a href=\"",$_SERVER['PHP_SELF'],"?letter=F&".$toAdd."\">F</a> | ";
            echo "<a href=\"",$_SERVER['PHP_SELF'],"?letter=G&".$toAdd."\">G</a> | ";
            echo "<a href=\"",$_SERVER['PHP_SELF'],"?letter=H&".$toAdd."\">H</a> | ";
            echo "<a href=\"",$_SERVER['PHP_SELF'],"?letter=I&".$toAdd."\">I</a> | ";
            echo "<a href=\"",$_SERVER['PHP_SELF'],"?letter=J&".$toAdd."\">J</a> | ";
            echo "<a href=\"",$_SERVER['PHP_SELF'],"?letter=K&".$toAdd."\">K</a> | ";
            echo "<a href=\"",$_SERVER['PHP_SELF'],"?letter=L&".$toAdd."\">L</a> | ";
            echo "<a href=\"",$_SERVER['PHP_SELF'],"?letter=M&".$toAdd."\">M</a> | ";
            echo "<a href=\"",$_SERVER['PHP_SELF'],"?letter=N&".$toAdd."\">N</a> | ";
            echo "<a href=\"",$_SERVER['PHP_SELF'],"?letter=O&".$toAdd."\">O</a> | ";
            echo "<a href=\"",$_SERVER['PHP_SELF'],"?letter=P&".$toAdd."\">P</a> | ";
            echo "<a href=\"",$_SERVER['PHP_SELF'],"?letter=Q&".$toAdd."\">Q</a> | ";
            echo "<a href=\"",$_SERVER['PHP_SELF'],"?letter=R&".$toAdd."\">R</a> | ";
            echo "<a href=\"",$_SERVER['PHP_SELF'],"?letter=S&".$toAdd."\">S</a> | ";
            echo "<a href=\"",$_SERVER['PHP_SELF'],"?letter=T&".$toAdd."\">T</a> | ";
            echo "<a href=\"",$_SERVER['PHP_SELF'],"?letter=U&".$toAdd."\">U</a> | ";
            echo "<a href=\"",$_SERVER['PHP_SELF'],"?letter=V&".$toAdd."\">V</a> | ";
            echo "<a href=\"",$_SERVER['PHP_SELF'],"?letter=W&".$toAdd."\">W</a> | ";
            echo "<a href=\"",$_SERVER['PHP_SELF'],"?letter=X&".$toAdd."\">X</a> | ";
            echo "<a href=\"",$_SERVER['PHP_SELF'],"?letter=Y&".$toAdd."\">Y</a> | ";
            echo "<a href=\"",$_SERVER['PHP_SELF'],"?letter=Z&".$toAdd."\">Z</a>";
            echo "
            <input type=\"text\" name=\"search\">
            <input type=\"hidden\" name=\"cidToEdit\" value=\"".$cidToEdit."\">
            <input type=\"submit\" value=\"".$langSearch."\">

      </form>
     ";
*/
     //TOOL LINKS
echo "<a class=\"claroCmd\" href=\"adminregisteruser.php?cidToEdit=$cidToEdit\">$langEnrollUser</a>";

if (isset($cfrom) && ($cfrom=="clist"))
{
    echo " | <a class=\"claroCmd\" href=\"admincourses.php\">$langBackToCourseList</a>";
}

//Pager

$myPager->disp_pager_tool_bar($_SERVER['PHP_SELF']."?cidToEdit=".$cidToEdit);

// Display list of users

   // start table...

echo '<table class="claroTable emphaseLine" width="100%" border="0" cellspacing="2">
<thead >
<caption>
			<small>
			<img src="'.$imgRepositoryWeb.$iconForCuStatus['STUDENT'].'" alt="STUDENT" border="0" title="statut" > Student 
            <wbr>
			<img src="'.$imgRepositoryWeb.$iconForCuStatus['COURSE_MANAGER'].'" alt="course manager" border="0" title="statut" > Course Manager 
			</nobr>
			</small>
</caption>
    <tr class="headerX" align="center" valign="top">
       <th><a href="'.$_SERVER['PHP_SELF'].'?order_crit=uid&amp;dir='.$order['uid'].'&amp;cidToEdit='.$cidToEdit."\">".$langUserid.'</a></th>
       <th><a href="'.$_SERVER['PHP_SELF'].'?order_crit=name&amp;dir='.$order['name'].'&amp;cidToEdit='.$cidToEdit.'">'.$langLastName.'</a></th>
       <th><a href="'.$_SERVER['PHP_SELF'].'?order_crit=firstname&amp;dir='.$order['firstname'].''.$dir.'&amp;cidToEdit='.$cidToEdit.'">'.$langFirstName.'</a></th>
       <th>
           <a href="'.$_SERVER['PHP_SELF'].'?order_crit=cu_status&amp;dir='.$order['code'].''.$dir.'&amp;cidToEdit='.$cidToEdit.'">'.$langStatus.'</a>
	   </th>
      <th>'.$langUnsubscribe.'</th>
      </tr>
</thead><tbody>';


// Start the list of users...

foreach($resultList as $list)
{
     echo '<tr>';

     //  Id

     echo '<td align="center">'
         .$list['user_id']
         .'</td>';

     // lastname

     echo "<td >".$list['nom']."</td>";

     //  Firstname

     echo "<td >".$list['prenom']."</td>";

     //  course manager

     echo '<td align="center">'
	      .'<a href="adminUserCourseSettings.php?cidToEdit='.$cidToEdit.'&amp;uidToEdit='.$list['user_id'].'&amp;ccfrom=culist">'
          .'<img src="'.$imgRepositoryWeb.$iconForCuStatus[$list['stat']].'" alt="'.$list['stat'].'" border="0"  hspace="4" title="'.$list['stat'].'" >'
		  .'</a>'
		  .'</td>';
     // Unregister

     if (isset($cidToEdit))
     {
        echo  '<td align="center">'."\n"
            . '<a href="'.$_SERVER['PHP_SELF'].'?cidToEdit='.$cidToEdit.'&amp;cmd=unsub&amp;user_id='.$list['user_id'].'&amp;offset='.$offset.'" '
            . 'onClick="return confirmationReg(\''.clean_str_for_javascript($list['username']).'\');">'."\n"
            . '<img src="'.$imgRepositoryWeb.'unenroll.gif" border="0" alt="'.$langUnsubscribe.'" />'."\n"
            . '</a>'."\n"
            . '</td>'."\n";
     }

     echo "</tr>";
}

   // end display users table

echo "</tbody>

</table>";

//Pager

$myPager->disp_pager_tool_bar($_SERVER['PHP_SELF']."?cidToEdit=".$cidToEdit);

include($includePath."/claro_init_footer.inc.php");
?>
