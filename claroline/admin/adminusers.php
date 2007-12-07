<?php //$Id$
/**
 * CLAROLINE 
 * @version 1.6 $Revision$
 *
 * @copyright (c) 2001-2005 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE 
 * 
 * @package ADMIN
 *
 * @author Guillaume Lederer <lederer@claroline.net>
 */
$cidReset = TRUE;$gidReset = TRUE;$tidReset = TRUE;
$userPerPage = 20; // numbers of user to display on the same page
$delayToConsiderAsSleeper = 3600*24*50; //delay in second to be mark as sleeper and not active

// initialisation of global variables and used libraries
DEFINE('COURSE_CREATOR',1);
require '../inc/claro_init_global.inc.php';
include($includePath."/lib/pager.lib.php");
include($includePath."/lib/admin.lib.inc.php");

//SECURITY CHECK

if (!$is_platformAdmin) claro_disp_auth_form();

if ($cidToEdit=="") {unset($cidToEdit);}


//------------------------------------------------------------------------------------------------------------------------
//  USED SESSION VARIABLES
//------------------------------------------------------------------------------------------------------------------------

// clean session if needed

if ($_REQUEST['newsearch']=="yes")
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

if (isset($_REQUEST['letter']))    {$_SESSION['admin_user_letter']     = trim($_REQUEST['letter'])     ;}
if (isset($_REQUEST['search']))    {$_SESSION['admin_user_search']     = trim($_REQUEST['search'])     ;}
if (isset($_REQUEST['firstName'])) {$_SESSION['admin_user_firstName']  = trim($_REQUEST['firstName'])  ;}
if (isset($_REQUEST['lastName']))  {$_SESSION['admin_user_lastName']   = trim($_REQUEST['lastName'])   ;}
if (isset($_REQUEST['userName']))  {$_SESSION['admin_user_userName']   = trim($_REQUEST['userName'])   ;}
if (isset($_REQUEST['mail']))      {$_SESSION['admin_user_mail']       = trim($_REQUEST['mail'])       ;}
if (isset($_REQUEST['action']))    {$_SESSION['admin_user_action']     = trim($_REQUEST['action'])     ;}
if (isset($_REQUEST['order_crit'])){$_SESSION['admin_user_order_crit'] = trim($_REQUEST['order_crit']) ;}
if (isset($_REQUEST['dir']))       {$_SESSION['admin_user_dir'] = ($_REQUEST['dir']=='DESC'?'DESC':'ASC');}

// clean session if we come from a course

session_unregister('_cid');
unset($_cid);

if(file_exists($includePath.'/currentVersion.inc.php')) include ($includePath.'/currentVersion.inc.php');


//TABLES
//declare needed tables
$tbl_mdb_names = claro_sql_get_main_tbl();
$tbl_admin            = $tbl_mdb_names['admin'          ];
$tbl_course           = $tbl_mdb_names['course'         ];
$tbl_course_user      = $tbl_mdb_names['rel_course_user'];
//$tbl_course_nodes     = $tbl_mdb_names['category'         ];
$tbl_user             = $tbl_mdb_names['user'];
$tbl_rel_class_user   = $tbl_mdb_names['rel_class_user' ];
$tbl_track_default    = $tbl_mdb_names['track_e_default'];
$tbl_track_login      = $tbl_mdb_names['track_e_login'  ];

// javascript confirm pop up declaration
  $htmlHeadXtra[] =
            "<script>
            function confirmation (name)
            {
		if (confirm(\"".clean_str_for_javascript($langAreYouSureToDelete)."\"+ name + \"? \"))
                    {return true;}
                else
                    {return false;}
            }
            </script>";

// Deal with interbredcrumps

$interbredcrump[]= array ("url"=>$rootAdminWeb, "name"=> $langAdministration);
$nameTools = $langListUsers;
//TABLES

//------------------------------------
// Execute COMMAND section
//------------------------------------
switch ($cmd)
{
  case "delete" :
        if ($_uid != $user_id)
	{	    
	    delete_user($user_id);
	    $dialogBox = $langUserDelete;
	}
	else
	{
	    $dialogBox = $langNotUnregYourself;
	}
        break;
}

//----------------------------------
// Build query and find info in db
//----------------------------------


$sql = "SELECT 
       `U`.`user_id`     ,
       `U`.`nom`         ,
       `U`.`prenom`      ,
#      `U`.`username`    ,
#      `U`.`password`    ,
       `U`.`authSource`  ,
       `U`.`email`       ,
       `U`.`statut`      ,
       `U`.`officialCode`,
       `U`.`phoneNumber` ,
       `U`.`pictureUri`  ,
       `U`.`creatorId` creator_id, 
#       IF(`U`.`user_id`=`U`.`creatorId`,'ACTIVE','GHOST') `activity` ,
#       max(UNIX_TIMESTAMP(`login`.`login_date`)) `login_date`,
#       now()-`login_date` `login_idle` ,
       IF(`U`.`statut`=".COURSE_CREATOR.",'COURSE_CREATOR','ORDINARY') `statut` ,
       count(DISTINCT `CU`.`code_cours`) `qty_course`
       FROM  `".$tbl_user."` AS `U`";

//deal with admin user search only (PART ONE)	
	
if ($_SESSION['admin_user_action']=="plateformadmin")
{
    $sql .= ", `".$tbl_admin."` AS `AD`";
}

// join with course table to find course numbers of each user and last login

$sql.= " 
#       LEFT JOIN `".$tbl_track_login."` `login`
#       ON `U`.`user_id`  = `login`.`login_user_id`
       LEFT JOIN `".$tbl_course_user."` AS `CU` 
       ON `CU`.`user_id` = `U`.`user_id`

       WHERE 1=1 ";

//deal with admin user search only (PART TWO)

if ($_SESSION['admin_user_action']=="plateformadmin")
{
    $sql .= " AND `AD`.`idUser` = `U`.`user_id` ";
}       
       
//deal with LETTER classification call

if (isset($_SESSION['admin_user_letter']))
{
    $sql .= 'AND U.`nom` LIKE "'.$_SESSION['admin_user_letter'].'%" ';
}

//deal with KEY WORDS classification call

if (isset($_SESSION['admin_user_search']))
{
    $sql .= " AND (U.`nom` LIKE '%".pr_star_replace($_SESSION['admin_user_search'])."%'
              OR U.`prenom` LIKE '%".pr_star_replace($_SESSION['admin_user_search'])."%' ";
    $sql .= " OR U.`email` LIKE '%".pr_star_replace($_SESSION['admin_user_search'])."%')";
}

//deal with ADVANCED SEARCH parameters call

if (isset($_SESSION['admin_user_firstName']))
{
    $sql .= " AND (U.`prenom` LIKE '".pr_star_replace($_SESSION['admin_user_firstName'])."%') ";
}

if (isset($_SESSION['admin_user_lastName']))
{
	$sql .= " AND (U.`nom` LIKE '".pr_star_replace($_SESSION['admin_user_lastName'])."%') ";
}

if (isset($_SESSION['admin_user_userName']))
{
    $sql.= " AND (U.`username` LIKE '".pr_star_replace($_SESSION['admin_user_userName'])."%') ";
}

if (isset($_SESSION['admin_user_mail']))
{
    $sql.= " AND (U.`email` LIKE '".pr_star_replace($_SESSION['admin_user_mail'])."%') ";
}

if (   isset($_SESSION['admin_user_action']) 
         && (    $_SESSION['admin_user_action']=="createcourse" 
		      || $_SESSION['admin_user_action']=="plateformadmin")
            )
{
    $sql.=' AND (U.`statut`='.COURSE_CREATOR.')';
}

$sql.=" GROUP BY U.`user_id` ";

// deal with REORDER
if (isset($_SESSION['admin_user_order_crit']))
{
	// set the name of culomn to sort following $_SESSION['admin_user_order_crit'] value
	switch ($_SESSION['admin_user_order_crit'])
	{
		case 'uid'          : $fieldSort = 'U`.`user_id';      break;
		case 'name'         : $fieldSort = 'U`.`nom';          break;
		case 'firstname'    : $fieldSort = 'U`.`prenom';       break;
		case 'officialCode' : $fieldSort = 'U`.`officialCode'; break;
		case 'email'        : $fieldSort = 'U`.`email';        break;
		case 'status'       : $fieldSort = 'U`.`statut';       break;
		#case 'activity'     : $fieldSort = 'login_idle';       break;
		case 'courseqty'    : $fieldSort = 'qty_course';

	}
    $sql.= " ORDER BY `".$fieldSort."` ".$_SESSION['admin_user_dir'];
	$order[$_SESSION['admin_user_order_crit']] = ($_SESSION['admin_user_dir']=='ASC'?'DESC':'ASC');
}

//$dialogBox = '<pre>'.$sql."</pre><br>"; //debug

$myPager = new claro_sql_pager($sql, $offset, $userPerPage);
$userList = $myPager->get_result_list();

//$dialogBox .= '<pre>'.var_export($userList,1)."</pre><br>"; //debug

//Display search form
//see passed search parameters :

if ($_SESSION['admin_user_search']!="")               { $isSearched .= $_SESSION['admin_user_search']."* ";}
if ($_SESSION['admin_user_firstName']!="")            { $isSearched .= $langFirstName."=".$_SESSION['admin_user_firstName']."* ";}
if ($_SESSION['admin_user_lastName']!="")             { $isSearched .= $langLastName."=".$_SESSION['admin_user_lastName']."* ";}
if ($_SESSION['admin_user_userName']!="")             { $isSearched .= $langUserName."=".$_SESSION['admin_user_userName']."* ";}
if ($_SESSION['admin_user_mail']!="")                 { $isSearched .= $langEmail."=".$_SESSION['admin_user_mail']."* ";}
if ($_SESSION['admin_user_action']=="createcourse")   { $isSearched .= "<b> <br>".$langCourseCreator."  </b> ";}
if ($_SESSION['admin_user_action']=="plateformadmin") { $isSearched .= "<b> <br>".$langPlatformAdministrator."  </b> ";}

     //see what must be kept for advanced links

$addtoAdvanced  = "?firstName=".$_SESSION['admin_user_firstName'];
$addtoAdvanced .= "&amp;lastName=".$_SESSION['admin_user_lastName'];
$addtoAdvanced .= "&amp;userName=".$_SESSION['admin_user_userName'];
$addtoAdvanced .= "&amp;mail=".$_SESSION['admin_user_mail'];
$addtoAdvanced .= "&amp;action=".$_SESSION['admin_user_action'];

    //finaly, form itself

if (($isSearched=="") || !isset($isSearched)) {$title = "";} else {$title = $langSearchOn." : ";}



//---------
// DISPLAY
//---------
//Header
include($includePath."/claro_init_header.inc.php");

// Display tool title
claro_disp_tool_title($nameTools);

//Display Forms or dialog box(if needed)

if($dialogBox)
{
    claro_disp_message_box($dialogBox);
}

//Display selectbox and advanced search link

//TOOL LINKS

   //Display search form

echo '<table width="100%">
        <tr>
          <td align="left">
             <b>'.$title.'</b>
             <small>
             '.$isSearched.'
             </small>
          </td>
          <td align="right">
            <form action="'.$_SERVER['PHP_SELF'].'">
            <label for="search">'.$langMakeNewSearch.'</label>
            <input type="text" value="'.$_REQUEST['search'].'" name="search" id="search" >
            <input type="submit" value=" '.$langOk.' ">
            <input type="hidden" name="newsearch" value="yes">
            [<a class="claroCmd" href="advancedUserSearch.php'.$addtoAdvanced.'" >'.$langAdvanced.'</a>]
            </form>
          </td>
        </tr>
      </table>
       ';

   //Pager

$myPager->disp_pager_tool_bar($_SERVER['PHP_SELF']);

// Display list of users

   // start table...

echo "<table class=\"claroTable emphaseLine\" width=\"100%\" border=\"0\" cellspacing=\"2\">
     <thead>
     <tr class=\"headerX\" align=\"center\" valign=\"top\">
          <th><a href=\"".$_SERVER['PHP_SELF']."?order_crit=uid&amp;dir=".$order['uid']."\">".$langNumero."</a></th>
          <th><a href=\"".$_SERVER['PHP_SELF']."?order_crit=name&amp;dir=".$order['name']."\">".$langLastName."</a></th>
          <th><a href=\"".$_SERVER['PHP_SELF']."?order_crit=firstname&amp;dir=".$order['firstname']."\">".$langFirstName."</a></th>
          <th><a href=\"".$_SERVER['PHP_SELF']."?order_crit=officialCode&amp;dir=".$order['officialCode']."\">".$langOfficialCode."</a></th>
          <th><a href=\"".$_SERVER['PHP_SELF']."?order_crit=email&dir=".$order['email']."\">".$langEmail."</a></th>
          <th><a href=\"".$_SERVER['PHP_SELF']."?order_crit=status&amp;dir=".$order['status']."\">".$langUserStatus."</a></th>
          <th>".$langUserSettings."</th>
          <th><a href=\"".$_SERVER['PHP_SELF']."?order_crit=courseqty&amp;dir=".$order['courseqty']."\">".$langCourses."</a></th>
          <th>".$langDelete."</th>";
echo "</tr><tbody> ";

   // Start the list of users...
foreach($userList as $list)
//while ($list = mysql_fetch_array($query))
{
     echo '<tr>';

     //  Id

    echo "<td align=\"center\">"
	     .$list['user_id']
	     ."</td>";

     if (isset($_SESSION['admin_user_search'])&& ($_SESSION['admin_user_search']!="")) {  //trick to prevent "//1" display when no keyword used in search 
	 
         $bold_search = str_replace("*",".*",$_SESSION['admin_user_search']);
     
         // name
	 	 
	 $bolded_name = eregi_replace("(".$bold_search.")",'<b>\\1</b>', $list['nom']);	 
         echo "<td align=\"left\">".$bolded_name."</td>";

         //  Firstname
	 
	 $bolded_firstname = eregi_replace("(".$bold_search.")",'<b>\\1</b>', $list['prenom']);
         echo "<td align=\"left\">".$bolded_firstname."</td>";
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
	 
	 $bolded_email = eregi_replace("(".$bold_search.")",'<b>\\1</b>', $list['email']);
         echo "<td align=\"left\">".$bolded_email."</td>";

     }
     else
     {
         // mail
         echo "<td align=\"left\">".$list['email']."</td>";
     }

     // Status
    $userStatus = ($list['statut']=='COURSE_CREATOR'?$langCourseCreator:$langNormalUser);
    if (isAdminUser($list['user_id']))
    {
        $userStatus .= '<br /><font color="red">'.$langAdministrator.'</font>';
    }

    echo '<td align="center">'."\n"
        .$userStatus
        .'</td>'."\n"
        ;
     // Modify link

     echo     "<td align=\"center\">\n",
                        "<a href=\"adminprofile.php?uidToEdit=".$list['user_id']."&amp;cfrom=ulist".$addToURL."\">\n
                         <img src=\"".$imgRepositoryWeb."usersetting.gif\" border=\"0\" alt=\"".$langUserSettings."\" />\n",
                        "</a>\n",
                        "</td>\n";

     // All course of this user

    echo '<td align="center">'
        .'<a href="adminusercourses.php?uidToEdit='.$list['user_id'].'&amp;cfrom=ulist'.$addToURL.'">'."\n"
        .sprintf(($list['qty_course']>1?$lang_p_d_courses:$lang_p_d_course),$list['qty_course'])."\n"
        .'</a>'."\n"
        .'</td>'."\n";

     //  Delete link

     echo '<td align="center">'
         .'<a href="'.$_SERVER['PHP_SELF'].'?cmd=delete&amp;user_id='.$list['user_id'].'&amp;offset='.$offset.$addToURL.'" '
         .' onClick="return confirmation(\''.clean_str_for_javascript(' '.$list['prenom'].' '.$list['nom']).'\');">'."\n"
         .'<img src="'.$imgRepositoryWeb.'deluser.gif" border="0" alt="'.$langDelete.'" />'."\n"
         .'</a> '."\n"
         .'</td>'."\n"
         .'</tr>';
     $atLeastOne= TRUE;
}
   // end display users table
if (!$atLeastOne)
{
   echo '<tr>
          <td colspan="9" align="center">
            '.$langNoUserResult.'<br>
            <a href="advancedUserSearch.php'.$addtoAdvanced.'">'.$langSearchAgain.'</a>
          </td>
         </tr>';
}
echo "</tbody>\n</table>\n";

//Pager

$myPager->disp_pager_tool_bar($_SERVER['PHP_SELF']);
include($includePath."/claro_init_footer.inc.php");
/*******************/
// END OF SCRIPT
/*******************/

function isAdminUser($user_id)
{
    global $tbl_admin;
    static $admin_list = array();

    if ( count($admin_list) == 0 )

    {
		$sql = "SELECT `idUser` `admin_id` FROM `".$tbl_admin."` ";
    	$result = claro_sql_query_fetch_all($sql);
		foreach($result as $admin_id)
		{
			$admin_list[]=$admin_id['admin_id'];
		}
	}
	return (in_array($user_id,$admin_list));
}
function date_diff($earlierDate, $laterDate) 
{
  //returns an array of numeric values representing days, hours, minutes & seconds respectively
  $ret=array('idle'=>0,'year'=>0,'week'=>0,'days'=>0,'hours'=>0,'minutes'=>0,'seconds'=>0);

  $totalsec = $laterDate - $earlierDate;
  $ret['idle']=$totalsec;
  if ($totalsec >= 31536000) {
   $ret['year'] = floor($totalsec/31536000);
   $totalsec = $totalsec % 31536000;
  }
  if ($totalsec >= 604800) {
   $ret['week'] = floor($totalsec/604800);
   $totalsec = $totalsec % 604800;
  }
  if ($totalsec >= 86400) {
   $ret['days'] = floor($totalsec/86400);
   $totalsec = $totalsec % 86400;
  }
  if ($totalsec >= 3600) {
   $ret['hours'] = floor($totalsec/3600);
   $totalsec = $totalsec % 3600;
  }
  if ($totalsec >= 60) {
   $ret['minutes'] = floor($totalsec/60);
  }
  $ret['seconds'] = $totalsec % 60;
  return $ret;
}
?>
