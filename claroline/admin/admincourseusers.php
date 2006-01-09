<?php // $Id$
/**
 * CLAROLINE
 *
 * This tool list user of a course but in admin section
 *
 * @version 1.8 $Revision$
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

$cidReset=true;$gidReset=true;$tidReset=true;
$iconForCuStatus['STUDENT']        = 'user.gif';
$iconForCuStatus['COURSE_MANAGER'] = 'manager.gif';

require '../inc/claro_init_global.inc.php';

/* ************************************************************************** */
/*  Security Check
/* ************************************************************************** */

// Security check
if ( ! $_uid ) claro_disp_auth_form();
if ( ! $is_platformAdmin ) claro_die(get_lang('Not allowed'));

/* ************************************************************************** */
/*  Initialise variables and include libraries
/* ************************************************************************** */
$dialogBox = '';
// initialisation of global variables and used libraries
require_once $includePath . '/lib/pager.lib.php';
require_once $includePath . '/lib/admin.lib.inc.php';
require_once $includePath . '/lib/user.lib.php';
require_once $includePath . '/lib/datagrid.lib.php';

include $includePath . '/conf/user_profile.conf.php';

//TABLES
$tbl_mdb_names   = claro_sql_get_main_tbl();
$tbl_user        = $tbl_mdb_names['user'  ];
$tbl_course_user = $tbl_mdb_names['rel_course_user' ];

/**
 * Manage incoming.
 */
if ((isset($_REQUEST['cidToEdit']) && $_REQUEST['cidToEdit'] == '') || !isset($_REQUEST['cidToEdit']))
{
    unset($_REQUEST['cidToEdit']);
    $dialogBox .= 'ERROR : NO COURSE SET!!!';
}
else $cidToEdit = $_REQUEST['cidToEdit'];
// See SESSION variables used for reorder criteria :
if ( isset($_REQUEST['cmd']) ) $cmd = $_REQUEST['cmd'];
else                           $cmd = null;
$pager_offset =  isset($_REQUEST['pager_offset'])?$_REQUEST['pager_offset'] :'0';

/**
 * COMMAND
 */
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
            {
                $dialogBox .= get_lang('CannotUnsubscribeLastCourseManager');
            }   break;
            case 'course_manager_cannot_unsubscribe_himself' :
            {
                $dialogBox .= get_lang('CourseManagerCannotUnsubscribeHimself');
            }   break;
            default :
        }
    }
}
// build and call DB to get info about current course (for title) if needed :
$courseData = claro_get_course_data($cidToEdit);

//----------------------------------
// Build query and find info in db
//----------------------------------
$sql = "SELECT u.user_id  AS user_id,
               u.nom      AS name,
               u.prenom   AS firstname,
               u.username AS username,
               IF(CU.statut=1,'COURSE_MANAGER','STUDENT') AS `status`
        FROM  `" . $tbl_user . "` AS U
            , `" . $tbl_course_user . "` AS CU
          WHERE CU.`user_id` = U.`user_id`
            AND CU.`code_cours` = '" . addslashes($cidToEdit) . "'";

$myPager = new claro_sql_pager($sql, $pager_offset, get_conf('userPerPage',20));

$sortKey = isset($_GET['sort']) ? $_GET['sort'] : 'user_id';
$sortDir = isset($_GET['dir' ]) ? $_GET['dir' ] : SORT_ASC;
$myPager->set_sort_key($sortKey, $sortDir);
$myPager->set_pager_call_param_name('pager_offset');

$userList = $myPager->get_result_list();

// Start the list of users...
foreach($userList as $lineId => $user)
{
     $userDataList[$lineId]['user_id']         = $user['user_id'];
     $userDataList[$lineId]['name']            = $user['name'];
     $userDataList[$lineId]['firstname']       = $user['firstname'];
     $userDataList[$lineId]['cmd_cu_setting']  = '<a href="adminUserCourseSettings.php'
     .                                           '?cidToEdit=' . $cidToEdit
     .                                           '&amp;uidToEdit=' . $user['user_id'] . '&amp;ccfrom=culist">'
     .                                           '<img src="' . get_conf('imgRepositoryWeb') . $iconForCuStatus[$user['status']] . '" '
     .                                           ' alt="' . $user['status'] . '" border="0"  hspace="4" title="' . $user['status'] . '" />'
     .                                           '</a>';
     $userDataList[$lineId]['cmd_cu_unenroll']  = '<a href="' . $_SERVER['PHP_SELF']
     .                                            '?cidToEdit=' . $cidToEdit
     .                                            '&amp;cmd=unsub&amp;user_id=' . $user['user_id']
     .                                            '&amp;pager_offset=' . $pager_offset . '" '
     .                                            ' onClick="return confirmationReg(\'' . clean_str_for_javascript($user['username']) . '\');">' . "\n"
     .                                            '<img src="' . get_conf('imgRepositoryWeb') . 'unenroll.gif" border="0" alt="' . get_lang('Unsubscribe') . '" />' . "\n"
     .                                            '</a>' . "\n";

} // end display users table

/**
 * Prepare output
 */

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

// Config Datagrid

$sortUrlList = $myPager->get_sort_url_list($_SERVER['PHP_SELF'] . '?cidToEdit=' . $cidToEdit);

$dg_opt_list['idLineType'] = 'none';
$dg_opt_list['colTitleList'] = array ( 'user_id'  => '<a href="' . $sortUrlList['user_id'] . '">' . get_lang('Userid') . '</a>'
                                     , 'name'     => '<a href="' . $sortUrlList['name'] . '">' . get_lang('LastName') . '</a>'
                                     , 'firstname'=> '<a href="' . $sortUrlList['firstname'] . '">' . get_lang('FirstName') . '</a>'
                                     , 'cmd_cu_setting'  => '<a href="' . $sortUrlList['status'] . '">' . get_lang('Action') . '</a>'
                                     , 'cmd_cu_unenroll' => get_lang('Unsubscribe')
);

$dg_opt_list['colAttributeList'] = array ( 'user_id'   => array ('align' => 'center')
                                         , 'cmd_cu_setting'    => array ('align' => 'center')
                                         , 'cmd_cu_unenroll' => array ('align' => 'center')
);

$dg_opt_list['caption'] = '<small>'
.                         '<img src="' . get_conf('imgRepositoryWeb') . $iconForCuStatus['STUDENT'] . '" '
.                         ' alt="STUDENT" border="0" title="statut Student" />'
.                         get_lang('Student')
.                         '<wbr>'
.                         '<img src="' . get_conf('imgRepositoryWeb') . $iconForCuStatus['COURSE_MANAGER'].'" '
.                         ' alt="course manager" border="0" title="statut Course manager" />'
.                         get_lang('Course Manager')
.                         '</nobr>'
.                         '</small>'
;

$nameTools = get_lang('AllUsersOfThisCourse');
$nameTools .= " : ".$courseData['name'];
// Deal with interbredcrumps
$interbredcrump[]= array ('url' => $rootAdminWeb, 'name' => get_lang('Administration'));

//------------------------------------
// DISPLAY
//------------------------------------
// Display tool title
include($includePath . '/claro_init_header.inc.php');
echo claro_disp_tool_title($nameTools);
if ( !empty($dialogBox) ) echo claro_disp_message_box($dialogBox);

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

/** DISPLAY : LIST of data */
echo $myPager->disp_pager_tool_bar($_SERVER['PHP_SELF'] . '?cidToEdit=' . $cidToEdit);
echo claro_disp_datagrid($userDataList, $dg_opt_list);
echo $myPager->disp_pager_tool_bar($_SERVER['PHP_SELF'] . '?cidToEdit=' . $cidToEdit);

include $includePath . '/claro_init_footer.inc.php';
?>