<?php // $Id$
/**
 * CLAROLINE
 *
 * This tool list user of a course but in admin section
 *
 * @version 1.7 $Revision$
 * @copyright 2001-2005 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @see http://www.claroline.net/wiki/index.php/CLUSR
 *
 * @package CLUSR
 *
 * @author Claro Team <cvs@claroline.net>
 *
 */

$cidReset=true;
$gidReset=true;
$tidReset=true;
$userPerPage = 20; // numbers of user to display on the same page

require '../inc/claro_init_global.inc.php';

/* ************************************************************************** */
/*  Security Check
/* ************************************************************************** */

// Security check
if ( ! $_uid ) claro_disp_auth_form();
if ( ! $is_platformAdmin ) claro_die(get_lang('NotAllowed'));

/* ************************************************************************** */
/*  Initialise variables and include libraries
/* ************************************************************************** */
$dialogBox = '';
// initialisation of global variables and used libraries
$iconForCuStatus['STUDENT']        = "user.gif";
$iconForCuStatus['COURSE_MANAGER'] = "manager.gif";
include_once $includePath . '/lib/pager.lib.php';
include_once $includePath . '/lib/admin.lib.inc.php';
include_once $includePath . '/lib/user.lib.php';
include $includePath . '/conf/user_profile.conf.php';
//find which course is concerned in URL parameters
if ((isset($_REQUEST['cidToEdit']) && $_REQUEST['cidToEdit'] == '') || !isset($_REQUEST['cidToEdit']))
{
    unset($_REQUEST['cidToEdit']);
    $dialogBox .= 'ERROR : NO COURSE SET!!!';
}
else
{
   $cidToEdit = $_REQUEST['cidToEdit'];
}
// javascript confirm pop up declaration
$htmlHeadXtra[] =
         "<script>
         function confirmationReg (name)
         {
             if (confirm(\"".clean_str_for_javascript(get_lang('AreYouSureToUnsubscribe'))." \"+ name + \" ? \"))
                 {return true;}
             else
                 {return false;}
         }
         </script>";
// See SESSION variables used for reorder criteria :
if (isset($_REQUEST['order_crit']))
{
    $_SESSION['admin_course_user_order_crit']   = trim($_REQUEST['order_crit']) ;
}

if (isset($_REQUEST['dir']))
{
    $_SESSION['admin_course_user_dir'] = $_REQUEST['dir']=='DESC'?'DESC':'ASC';
}

//set the reorder parameters for colomuns titles
if (!isset($order['uid']))              $order['uid']          = '';
if (!isset($order['name']))             $order['name']         = '';
if (!isset($order['firstname']))        $order['firstname']    = '';
if (!isset($order['cu_status']))        $order['cu_status']    = '';
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

if ( isset($_REQUEST['cmd']) ) $cmd = $_REQUEST['cmd'];
else                           $cmd = null;

if ( $cmd == 'unsub' )
{
    if ( user_remove_from_course($_REQUEST['user_id'], $_REQUEST['cidToEdit'], true) )
    {
        $dialogBox .= get_lang('UserUnsubscribed');
    }
    else
    {
        switch ( claro_failure::get_last_failure() )
        {
            case 'cannot_unsubscribe_the_last_course_manager' :
                $dialogBox .= get_lang('CannotUnsubscribeLastCourseManager');
                break;
            case 'course_manager_cannot_unsubscribe_himself' :
                $dialogBox .= get_lang('CourseManagerCannotUnsubscribeHimself');
                break;
            default :
        }
    }
}
// build and call DB to get info about current course (for title) if needed :
$courseData = claro_get_course_data($cidToEdit);

//----------------------------------
// Build query and find info in db
//----------------------------------
$sql = "SELECT *, IF(CU.statut=1,'COURSE_MANAGER','STUDENT') `stat`
        FROM  `" . $tbl_user . "` AS U
        ";
$toAdd = ", `" . $tbl_course_user . "` AS CU
          WHERE CU.`user_id` = U.`user_id`
            AND CU.`code_cours` = '" . addslashes($cidToEdit) . "'
        ";
$sql.=$toAdd;

//deal with LETTER classification call
if (isset($_REQUEST['letter']))
{
    $toAdd = "
             AND U.`nom` LIKE '" . addslashes($_REQUEST['letter']) . "%'
             ";
    $sql.=$toAdd;
}
//deal with KEY WORDS classification call
if (isset($_REQUEST['search']))
{
    $toAdd = " AND ((U.`nom` LIKE '%" . addslashes($_REQUEST['search']) . "%'
              OR U.`username` LIKE '%" . addslashes($_REQUEST['search']) . "%'
              OR U.`prenom` LIKE '%" . addslashes($_REQUEST['search']) . "%')) ";
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
//        case 'email'  : $fieldSort = 'email';
    }
    $toAdd = " ORDER BY `" . $fieldSort . "` " . $_SESSION['admin_course_user_dir'];
    $order[$_SESSION['admin_course_user_order_crit']] = ($_SESSION['admin_course_user_dir']=='ASC'?'DESC':'ASC');
    $sql.=$toAdd;
}

//Build SQL query
if (!isset($_REQUEST['offset']))
{
    $offset = '0';
}
else
{
    $offset = $_REQUEST['offset'];
}

$myPager = new claro_sql_pager($sql, $offset, $userPerPage);
$resultList = $myPager->get_result_list();





//------------------------------------
// DISPLAY
//------------------------------------
// Display tool title

$nameTools = get_lang('AllUsersOfThisCourse');
$nameTools .= " : ".$courseData['name'];
// Deal with interbredcrumps
$interbredcrump[]= array ('url' => $rootAdminWeb, 'name' => get_lang('Administration'));

//Header
include($includePath . '/claro_init_header.inc.php');

echo claro_disp_tool_title($nameTools);

// Display Forms or dialog box(if needed)

if ( !empty($dialogBox) )
{
    echo claro_disp_message_box($dialogBox);
}

//Display selectbox, alphabetic choice, and advanced search link search
echo '<a class="claroCmd" href="adminregisteruser.php'
.    '?cidToEdit=' . $cidToEdit . '">'
.    get_lang('EnrollUser')
.    '</a>'
;

if (isset($cfrom) && ($cfrom=='clist'))
{
    echo ' | <a class="claroCmd" href="admincourses.php">' . get_lang('BackToCourseList') . '</a>';
}

//Pager
$myPager->disp_pager_tool_bar($_SERVER['PHP_SELF'] . '?cidToEdit=' . $cidToEdit);


// Display list of users
   // start table...
echo '<table class="claroTable emphaseLine" width="100%" border="0" cellspacing="2">'
.    '<thead >'
.    '<caption>'
.    '<small>'
.    '<img src="' . $imgRepositoryWeb . $iconForCuStatus['STUDENT'] . '" '
.    ' alt="STUDENT" border="0" title="statut" />'
.    ' Student '
.    '<wbr>'
.    '<img src="' . $imgRepositoryWeb . $iconForCuStatus['COURSE_MANAGER'].'" '
.    ' alt="course manager" border="0" title="statut" />'
.    'Course Manager'
.    '</nobr>'
.    '</small>'
.    '</caption>'
.    '<tr class="headerX" align="center" valign="top">'

.    '<th>'
.    '<a href="' . $_SERVER['PHP_SELF']
.    '?order_crit=uid&amp;dir=' . $order['uid']
.    '&amp;cidToEdit=' . $cidToEdit."\">"
.    get_lang('Userid')
.    '</a>'
.    '</th>'

.    '<th>'
.    '<a href="' . $_SERVER['PHP_SELF']
.    '?order_crit=name&amp;dir=' . $order['name']
.    '&amp;cidToEdit='.$cidToEdit.'">'
.    get_lang('LastName')
.    '</a>'
.    '</th>'

.    '<th>'
.    '<a href="' . $_SERVER['PHP_SELF']
.    '?order_crit=firstname&amp;dir=' . $order['firstname']
.    '&amp;cidToEdit=' . $cidToEdit.  '">'
.    get_lang('FirstName')
.    '</a>'
.    '</th>'

.    '<th>'
.    '<a href="'.$_SERVER['PHP_SELF']
.    '?order_crit=cu_status'
.    '&amp;dir=' . $order['cu_status']
.    '&amp;cidToEdit=' . $cidToEdit . '">'
.    get_lang('Status')
.    '</a>'
.    '</th>'
.    '<th>' . get_lang('Unsubscribe') . '</th>'
.    '</tr>'
.    '</thead>'
.    '<tbody>'
;

// Start the list of users...
foreach($resultList as $list)
{
     echo '<tr>';
     //  Id
     echo '<td align="center">'
     .    $list['user_id']
     .    '</td>'
     // lastname
     .    '<td >' . $list['nom'] . '</td>'
     //  Firstname
     .    '<td >' . $list['prenom'] . '</td>'
     //  course manager
     .    '<td align="center">'
     .    '<a href="adminUserCourseSettings.php'
     .    '?cidToEdit=' . $cidToEdit
     .    '&amp;uidToEdit=' . $list['user_id'] . '&amp;ccfrom=culist">'
     .    '<img src="' . $imgRepositoryWeb . $iconForCuStatus[$list['stat']] . '" '
     .    ' alt="' . $list['stat'] . '" border="0"  hspace="4" title="' . $list['stat'] . '" />'
     .    '</a>'
     .    '</td>'
     ;

     // Unregister
     if (isset($cidToEdit))
     {
        echo  '<td align="center">' . "\n"
        .     '<a href="' . $_SERVER['PHP_SELF']
        .     '?cidToEdit=' . $cidToEdit
        .     '&amp;cmd=unsub&amp;user_id=' . $list['user_id']
        .     '&amp;offset=' . $offset . '" '
        .     ' onClick="return confirmationReg(\'' . clean_str_for_javascript($list['username']) . '\');">' . "\n"
        .     '<img src="' . $imgRepositoryWeb . 'unenroll.gif" border="0" alt="' . get_lang('Unsubscribe') . '" />' . "\n"
        .     '</a>' . "\n"
        .     '</td>' . "\n"
        ;
     }

     echo '</tr>';
} // end display users table
echo '</tbody>'
.    '</table>'
;

//Pager
$myPager->disp_pager_tool_bar($_SERVER['PHP_SELF'] . '?cidToEdit=' . $cidToEdit);
include $includePath . '/claro_init_footer.inc.php';
?>
