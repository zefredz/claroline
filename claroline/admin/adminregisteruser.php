<?php // $Id$
//----------------------------------------------------------------------
// CLAROLINE 1.6
//----------------------------------------------------------------------
// Copyright (c) 2001-2005 Universite catholique de Louvain (UCL)
//----------------------------------------------------------------------
// This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
// as published by the FREE SOFTWARE FOUNDATION. The GPL is available
// through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
//----------------------------------------------------------------------
// Authors: see 'credits' file
//----------------------------------------------------------------------
// This script list member of campus and  propose to subscribe it to the given course


// Lang files needed :

$cidReset = TRUE;$gidReset = TRUE;$tidReset = TRUE;

// initialisation of global variables and used libraries
unset($includePath);
require '../inc/claro_init_global.inc.php';
include($includePath.'/lib/pager.lib.php');
include($includePath.'/lib/admin.lib.inc.php');
$canEditSubscription = $is_platformAdmin;

//SECURITY CHECK
if (!$canEditSubscription) claro_disp_auth_form();
if ((isset($_REQUEST['cidToEdit']) && $_REQUEST['cidToEdit']=="") || !isset($_REQUEST['cidToEdit']))
{
    unset($_REQUEST['cidToEdit']);
    $dialogBox ="ERROR : NO COURSE SET!!!";
    
}
else
{
   $cidToEdit = $_REQUEST['cidToEdit'];
}
$userPerPage = 20; // numbers of user to display on the same page

//get needed parameter from URL

if (isset($_REQUEST['user_id']))
	        $user_id = $_REQUEST['user_id'];
	else    $user_id = null;

if ($cidToEdit=="") {$dialogBox ='ERROR : NO USER SET!!!';}


// Deal with interbredcrumps
$interbredcrump[]= array ('url'=>$rootAdminWeb, 'name'=> $langAdministration);
$nameTools = $langEnrollUser;


//TABLES
$tbl_mdb_names   = claro_sql_get_main_tbl();
$tbl_user          = $tbl_mdb_names['user'  ];
$tbl_courses       = $tbl_mdb_names['course'];
$tbl_admin         = $tbl_mdb_names['admin' ];
$tbl_course_user   = $tbl_mdb_names['rel_course_user' ];
$tbl_track_default = $tbl_mdb_names['track_e_default' ];


// See SESSION variables used for reorder criteria :

if (isset($_REQUEST['dir']))       {$_SESSION['admin_register_dir'] = $_REQUEST['dir'];}
if (isset($_REQUEST['order_crit'])){$_SESSION['admin_register_order_crit'] = $_REQUEST['order_crit'];}

//------------------------------------
// Execute COMMAND section
//------------------------------------

if (isset($_REQUEST['cmd']))
     $cmd = $_REQUEST['cmd'];
else $cmd = null;

switch ($cmd)
{
    case 'sub' : //execute subscription command...
        
        if (!isRegisteredTo($user_id, $cidToEdit))
        {
            $done = add_user_to_course($user_id, $cidToEdit,true);
            // The user is add as student (default value in  add_user_to_course)
        }
        
        // Set status requested
        if ($_REQUEST['subas']=="teach")     // ... as teacher
        {
            $properties['status'] = 1;
            $properties['role']   = $langCourseManager;
            $properties['tutor']  = 1;
        }
        elseif ($_REQUEST['subas']=='stud')  // ... as student
        {
            $properties['status'] = 5;
            $properties['role']   = ""; 
            $properties['tutor']  = 0;
        }
        update_user_course_properties($user_id, $cidToEdit, $properties);

        //set dialogbox message

        if ($done)
        {
           $dialogBox = $langUserSubscribed;
        }
        break;

  case 'unsubscribe' :
        $done = remove_user_from_course($user_id, $cidToEdit);
        $dialogBox = ($done?$langUserUnsubscribed:$langUserNotUnsubscribed);
        
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

if (isset($_REQUEST['search']) && $_REQUEST['search']!="")
{
    $toAdd = " WHERE (U.`nom` LIKE '".$_REQUEST['search']."%'
              OR U.`username` LIKE '".$_REQUEST['search']."%'
              OR U.`prenom` LIKE '".$_REQUEST['search']."%') ";

    $sql.=$toAdd;
}

// deal with REORDER

  //first see is direction must be changed

if (isset($_REQUEST['chdir']) && ($_REQUEST['chdir']=="yes"))
{
  if ($_SESSION['admin_register_dir'] == "ASC") 
  {
      $_SESSION['admin_register_dir']="DESC";
  }
  else
  {
      $_SESSION['admin_register_dir']="ASC";
  }
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

//echo $sql; //debug

//Build pager with SQL request

if (!isset($_REQUEST['offset'])) 
{
    $offset = "0";
}
else
{
    $offset = $_REQUEST['offset'];
}

$myPager = new claro_sql_pager($sql, $offset, $userPerPage);
$userList = $myPager->get_result_list();

$isSearched ="";

//get the search keyword, if any

if (!isset($_REQUEST['search']))
{
   $search = "";
}
else
{
   $search = $_REQUEST['search'];
}

if (!isset($addToURL)) $addToURL ="";

//------------------------------------
// DISPLAY
//------------------------------------

// Display tool title

$nameTools .= " : ".$resultCourse['intitule'];

//Header
include($includePath.'/claro_init_header.inc.php');

claro_disp_tool_title($nameTools);

// Display Forms or dialog box(if needed)

if(isset($dialogBox))
{
    claro_disp_message_box($dialogBox);
}

// search form
       
if (isset($search) && $search!="")    {$isSearched .= $search."* ";}
if (($isSearched=="") || !isset($isSearched)) {$title = "";} else {$title = $langSearchOn." : ";}

echo '<table width="100%" >
        <tr>
          <td align="left">
             <b>'.$title.'</b>
             <small>
             '.$isSearched.'
             </small>
          </td>
          <td align="right">
            <form action="'.$_SERVER['PHP_SELF'].'">
            <label for="search">'.$langMakeSearch.'</label> :
            <input type="text" value="'.$search.'" name="search" id="search" >
            <input type="submit" value=" '.$langOk.' \">
            <input type="hidden" name="newsearch" value="yes">
            <input type="hidden" name="cidToEdit" value="'.$cidToEdit.'">
            </form>
          </td>
        </tr>
      </table>';

//TOOL LINKS

echo '<a class="claroCmd" href="admincourseusers.php?cidToEdit='.$cidToEdit.'">'.$langAllUsersOfThisCourse.'</a><br><br>';
      
//Pager

if (isset($_REQUEST['order_crit']))
{
    $addToURL = "&amp;order_crit=".$_SESSION['admin_register_order_crit']."&amp;dir=".$_SESSION['admin_register_dir'];
}

$myPager->disp_pager_tool_bar($_SERVER['PHP_SELF']."?cidToEdit=".$cidToEdit.$addToURL);

// Display list of users
// start table...
//columns titles...

echo '<table class="claroTable emphaseLine" width="100%" border="0" cellspacing="2">
<thead>
    <tr class="headerX" align="center" valign="top">
        <th><a href="'.$_SERVER['PHP_SELF'].'?order_crit=user_id&amp;chdir=yes&amp;search='.$search.'&amp;cidToEdit='.$cidToEdit.'">'.$langUserid.'</a></th>
        <th><a href="'.$_SERVER['PHP_SELF'].'?order_crit=nom&amp;chdir=yes&amp;search='.$search.'&amp;cidToEdit='.$cidToEdit.'">'.$langLastName.'</a></th>
        <th><a href="'.$_SERVER['PHP_SELF'].'?order_crit=prenom&amp;chdir=yes&amp;search='.$search.'&amp;cidToEdit='.$cidToEdit.'">'.$langFirstName.'</a></th>
        <th>'.$langEnrollAsStudent.'</th>
        <th>'.$langEnrollAsManager.'</th>
    </tr>
</thead>
<tbody>';

// Start the list of users...

if (isset($order_crit))
{
    $addToURL = '&amp;order_crit='.$order_crit;
}
if (isset($offset))
{
    $addToURL = '&amp;offset='.$offset;
}
foreach($userList as $user)
{
    if (isset($_GET['search'])&& ($_GET['search']!="")) 
    {
        $user['nom'] = eregi_replace("^(".$_GET['search'].")",'<b>\\1</b>', $user['nom']);
        $user['prenom'] = eregi_replace("^(".$_GET['search'].")","<b>\\1</b>", $user['prenom']);
    }
    
    echo '<tr>'."\n"
    //  Id
        .'<td align="center">'
        .$user['ID']
        .'</td>'."\n"
         // name
        .'<td align="left">'
        .$user['nom']
        .'</td>'
        //  Firstname
        .'<td align="left">'
        .$user['prenom']
        .'</td>'
        ;
    if ($user['statut'] != "5")  // user is already enrolled but as student
    {
        // Register as user
        echo '<td align="center">'."\n"
            .'<a href="'.$_SERVER['PHP_SELF']
            .'?cidToEdit='.$cidToEdit
            .'&amp;cmd=sub&amp;search='.$search
            .'&amp;user_id='.$user['ID']
            .'&amp;subas=stud'.$addToURL.'">'."\n"
            .'<img src="'.$imgRepositoryWeb.'enroll.gif" border="0" alt="'.$langSubscribeUser.'" />'."\n"
            .'</a>'."\n"
            .'</td>'."\n"
            ;
    }
    else
    {
        // already enrolled as student
        echo '<td align="center" >'."\n"
            .'<small>'
            .$lang_already_enrolled
            .'</small>'
            .'</td>'."\n"
            ;
    }
    if ($user['statut'] != "1")  // user is not enrolled
    {
            //register as teacher
        echo '<td align="center">'."\n"
            .'<a href="'.$_SERVER['PHP_SELF']
            .'?cidToEdit='.$cidToEdit
            .'&amp;cmd=sub&amp;search='.$search
            .'&amp;user_id='.$user['ID']
            .'&amp;subas=teach'.$addToURL.'">'
            .'<img src="'.$imgRepositoryWeb.'enroll.gif" border="0" alt="'.$langSubscribeUser.'" />'
            .'</a>'."\n"
            .'</td>'."\n"
            ;
    }
    else
    {
        // already enrolled as teacher
        echo '<td align="center" >'."\n"
            .'<small>'
            .$lang_already_enrolled
            .'</small>'
            .'</td>'."\n"
            ;
    }
    echo '</tr>';
}
// end display users table
echo '</tbody></table>';
//Pager
$myPager->disp_pager_tool_bar($_SERVER['PHP_SELF']."?cidToEdit=".$cidToEdit.$addToURL);
include($includePath."/claro_init_footer.inc.php");
?>
