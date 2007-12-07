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
$cidReset = TRUE;$gidReset = TRUE;$tidReset = TRUE;
$coursePerPage= 20;

require '../inc/claro_init_global.inc.php';

$tbl_log             = $mainDbName."`.`loginout";
$tbl_user            = $mainDbName."`.`user";
$tbl_admin           = $mainDbName."`.`admin";
$tbl_course          = $mainDbName."`.`cours";
$tbl_rel_course_user = $mainDbName."`.`cours_user";

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

if (!$is_platformAdmin) claro_disp_auth_form();

include($includePath.'/claro_init_header.inc.php');
$is_allowedToAdmin     = $is_platformAdmin;

//------------------------------------------------------------------------------------------------------------------------
//  USED SESSION VARIABLES
//------------------------------------------------------------------------------------------------------------------------
// deal with session variables for search criteria, it depends where we come from :
// 1 ) we must be able to get back to the list that concerned the criteria we previously used (with out re entering them)
// 2 ) we must be able to arrive with new critera for a new search.

// clean session if needed from  previous search

if ($_REQUEST['newsearch']=="yes")
{
    session_unregister('admin_course_code');
    session_unregister('admin_course_letter');
    session_unregister('admin_course_intitule');
    session_unregister('admin_course_category');
    session_unregister('admin_course_language');
    session_unregister('admin_course_access');
    session_unregister('admin_course_subscription');
    session_unregister('admin_course_dir');
    session_unregister('admin_course_order_crit');
}

if (isset($_REQUEST['code']))    {$_SESSION['admin_course_code']         = trim($_REQUEST['code']);}
if (isset($_REQUEST['letter']))  {$_SESSION['admin_course_letter']       = trim($_REQUEST['letter']);}
if (isset($_REQUEST['search']))  {$_SESSION['admin_course_search']       = trim($_REQUEST['search']);}
if (isset($_REQUEST['intitule'])){$_SESSION['admin_course_intitule']     = trim($_REQUEST['intitule']);}
if (isset($_REQUEST['category'])){$_SESSION['admin_course_category']     = trim($_REQUEST['category']);}
if (isset($_REQUEST['language'])){$_SESSION['admin_course_language']     = trim($_REQUEST['language']);}
if (isset($_REQUEST['access']))  {$_SESSION['admin_course_access']       = trim($_REQUEST['access']);}
if (isset($_REQUEST['subscription'])) 
                                 {$_SESSION['admin_course_subscription'] = trim($_REQUEST['subscription']);}
if (isset($_REQUEST['order_crit']))   
                                 {$_SESSION['admin_course_order_crit']   = trim($_REQUEST['order_crit']) ;}
if (isset($_REQUEST['dir']))     {$_SESSION['admin_course_dir']          = ($_REQUEST['dir']=='DESC'?'DESC':'ASC');}

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
        $delCode = $_REQUEST['delCode'];
        $sql = "SELECT `code` `sysCode`
                FROM `".$tbl_course."` `cours`
                WHERE `code` = '".$delCode."'";
        $result = claro_sql_query($sql);
        $the_course = mysql_fetch_array($result);

        if (isset($the_course[0])) 
        {
	        delete_course($the_course['sysCode']);
	        $dialogBox = $langCourseDelete;
        }
        break;
}

//----------------------------------
// Build query and find info in db
//----------------------------------

   // main query to know what must be displayed in the list

$sql = "SELECT  C.*,
				C.`fake_code` `officialCode`, 
				C.`code`      `sysCode`, 
				C.`directory` `repository`, 
				count(IF(`CU`.`statut`=5,1,null)) `qty_stu` , 
				#count only lines where statut of user is 5
				
				count(IF(`CU`.`statut`=1,1,null)) `qty_cm` 
				#count only lines where statut of user is 1

        FROM `".$tbl_course."` AS C 
        LEFT JOIN `".$tbl_rel_course_user."` AS CU
			ON `CU`.`code_cours` = `C`.`code` 
		WHERE 1=1 ";

//deal with LETTER classification call

if (isset($_SESSION['admin_course_letter']))
{
    $toAdd = " AND C.`intitule` LIKE '".$_SESSION['admin_course_letter']."%' ";
    $sql.=$toAdd;
}

//deal with KEY WORDS classification call
if (isset($_SESSION['admin_course_search']))
{
    $toAdd = " AND (      C.`intitule`  LIKE '%".pr_star_replace($_SESSION['admin_course_search'])."%' 
                       OR C.`fake_code` LIKE '%".pr_star_replace($_SESSION['admin_course_search'])."%'
                       OR C.`faculte`   LIKE '%".pr_star_replace($_SESSION['admin_course_search'])."%' 
               )";
    $sql.=$toAdd;

}

//deal with ADVANCED SEARCH parmaters call

if (isset($_SESSION['admin_course_intitule']))    // title of the course keyword is used
{
    $toAdd = " AND (C.`intitule` LIKE '%".pr_star_replace($_SESSION['admin_course_intitule'])."%') ";
    $sql.=$toAdd;

}

if (isset($_SESSION['admin_course_code']))        // code keyword is used
{
    $toAdd = " AND (C.`fake_code` LIKE '%".pr_star_replace($_SESSION['admin_course_code'])."%') ";
    $sql.=$toAdd;

}

if (isset($_SESSION['admin_course_category']))     // course category keyword is used
{
    $toAdd = " AND (C.`faculte` LIKE '%".pr_star_replace($_SESSION['admin_course_category'])."%') ";
    $sql.=$toAdd;

}

if (isset($_SESSION['admin_course_language']))    // language filter is used
{
    $toAdd = " AND (C.`languageCourse` LIKE '%".pr_star_replace($_SESSION['admin_course_language'])."%') ";
    $sql.=$toAdd;

}

if (isset($_SESSION['admin_course_access']))     // type of access to course filter is used
{
    $toAdd = "";
    if ($_SESSION['admin_course_access']=="private")
    {
       $toAdd = " AND NOT (C.`visible`=2 OR C.`visible`=3) ";
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
       $toAdd = " AND (C.`visible`=1 OR C.`visible`=2) ";
    }
    elseif ($_SESSION['admin_course_subscription']=="denied")
    {
       $toAdd = " AND NOT (C.`visible`=1 OR C.`visible`=2) ";
    }

    $sql.=$toAdd;

}
    $sql.=' GROUP BY C.code';

// deal with REORDER
if (isset($_SESSION['admin_course_order_crit']))
{
	switch ($_SESSION['admin_course_order_crit'])
	{
		case 'code'    : $fieldSort = 'fake_code'; break;
		case 'label'   : $fieldSort = 'intitule';  break;
		case 'cat'     : $fieldSort = 'faculte';   break;
		case 'titular' : $fieldSort = 'titulaire'; break;
		case 'email'   : $fieldSort = 'email';
	}
    $toAdd = " ORDER BY `".$fieldSort."` ".$_SESSION['admin_course_dir'];
	$order[$_SESSION['admin_course_order_crit']] = ($_SESSION['admin_course_dir']=='ASC'?'DESC':'ASC');
    $sql.=$toAdd;
}

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

if ($_REQUEST['search']!="")              {$isSearched .= trim($_REQUEST['search'])." ";}
if ($_REQUEST['code']!="")                {$isSearched .= $langCode." = ".$_REQUEST['code']." ";}
if ($_REQUEST['intitule']!="")            {$isSearched .= $langCourseTitle." = ".$_REQUEST['intitule']." ";}
if ($_REQUEST['category']!="")            {$isSearched .= $langCategory." = ".$_REQUEST['category']." ";}
if ($_REQUEST['language']!="")            {$isSearched .= $langLanguage." : ".$_REQUEST['language']." ";}
if ($_REQUEST['access']=="public")        {$isSearched .= " <b><br>".$langPublicOnly." </b> ";}
if ($_REQUEST['access']=="private")       {$isSearched .= " <b><br>".$langPrivateOnly." </b>  ";}
if ($_REQUEST['subscription']=="allowed") {$isSearched .= " <b><br>".$langSubscriptionAllowedOnly." </b>  ";}
if ($_REQUEST['subscription']=="denied")  {$isSearched .= " <b><br>".$langSubscriptionDeniedOnly." </b>  ";}

     //see what must be kept for advanced links

$addtoAdvanced = "?code=".$_REQUEST['code'];
$addtoAdvanced .="&intitule=".$_REQUEST['intitule'];
$addtoAdvanced .="&category=".$_REQUEST['category'];
$addtoAdvanced .="&language=".$_REQUEST['language'];
$addtoAdvanced .="&access=".$_REQUEST['access'];
$addtoAdvanced .="&subscription=".$_REQUEST['subscription'];

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
            <form action=\"",$_SERVER['PHP_SELF'],"\">
            <label for=\"search\">".$langMakeNewSearch."</label>
            <input type=\"text\" value=\"".trim($_REQUEST['search'])."\" name=\"search\" id=\"search\"\">
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

echo  '<th><a href="'.$_SERVER['PHP_SELF'].'?order_crit=code&amp;dir='.$order['code'].'">'.$langCode.'</a></th>'
     .'<th><a href="'.$_SERVER['PHP_SELF'].'?order_crit=label&amp;dir='.$order['label'].'">'.$langCourseTitle.'</a></th>'
     .'<th><a href="'.$_SERVER['PHP_SELF'].'?order_crit=cat&amp;dir='.$order['cat'].'">'.$langCategory.'</a></th>';

echo "<th>".$langAllUsersOfThisCourse."</th>";
echo "<th>".$langCourseSettings."</th>"
     ."<th>".$langDelete."</th>";

   // Display list of the course of the user :

foreach($resultList as $courseLine)
{
    echo "<tr>";


    if (isset($_SESSION['admin_course_search'])&& ($_SESSION['admin_course_search']!="")) //trick to prevent "//1" display when no keyword used in search
    {
        $bold_search = str_replace("*",".*",$_SESSION['admin_course_search']); 
	 
	 //  Code
	 
	$bold_code = eregi_replace("(".$bold_search.")","<b>\\1</b>", $courseLine['officialCode']);
        
	echo '<td >';
	echo $bold_code;
	echo '</td>';
	
         // title
	 
	$bold_title = eregi_replace("(".$bold_search.")","<b>\\1</b>", $courseLine['intitule']); 
	 
        echo "<td align=\"left\"><a href=\"".$coursesRepositoryWeb.$courseLine['directory']."\">".$bold_title."</a></td>";

         //  Category
	 
	 $bold_cat = eregi_replace("(".$bold_search.")","<b>\\1</b>", $courseLine['faculte']);
	 
         echo "<td align=\"left\">".$bold_cat."</td>";
     }
     else
     {
          //  Code

         echo "<td >
		 			".$courseLine['officialCode']."
               </td>";

         // title

         echo "<td align=\"left\"><a href=\"".$coursesRepositoryWeb.$courseLine['directory']."\">".$courseLine['intitule']."</a></td>";

         //  Category

         echo "<td align=\"left\">".$courseLine['faculte']."</td>";
    }



     //  All users of this course

     echo  '<td align="right">'."\n"
          .'<a href="admincourseusers.php?cidToEdit='.$courseLine['code'].$addToURL.'&amp;cfrom=clist">'
          .sprintf(($courseLine['qty_cm']+$courseLine['qty_stu']>1?$lang_p_d_course_members:$lang_p_d_course_member),($courseLine['qty_stu']+$courseLine['qty_cm']))
          .'</a>'
          .'<br><small><small>'
          .sprintf(($courseLine['qty_cm']>1?$lang_p_d_course_managers:$lang_p_d_course_manager),$courseLine['qty_cm'])."\n"
          .sprintf(($courseLine['qty_stu']>1?$lang_p_d_students:$lang_p_d_student),$courseLine['qty_stu'])."\n"
          .'</small></small>'
		  .'</td>'."\n";

    // Modify course settings

    echo  "<td align=\"center\">\n
           <a href=\"../course_info/infocours.php?cidReq=".$courseLine['code'].$addToURL."&cfrom=clist\"><img src=\"".$clarolineRepositoryWeb."img/referencement.gif\" alt=\"$langCourseSettings\"></a>
           </td>\n";

    //  Delete link


    echo   "<td align=\"center\">\n",
                "<a href=\"",$PHP_SELF,"?cmd=delete&delCode=".$courseLine['code'].$addToURL."\" ",
                "onClick=\"return confirmation('",addslashes($courseLine['intitule']),"');\">\n",
                "<img src=\"".$clarolineRepositoryWeb."/img/delete.gif\" border=\"0\" alt=\"$langDelete\" />\n",
                "</a>\n",
            "</td>\n";
     echo "</tr>";
     $atleastOneResult = TRUE;
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
