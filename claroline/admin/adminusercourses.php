<?php # $Id$
/**
 * Claroline
 *
 * This  tools admin courses subscription of one user
 *
 * @version 1.8 $Revision$
 *
 * @copyright (c) 2001-2006 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @package CLADMIN
 *
 * @author Claro Team <cvs@claroline.net>
 * @author Guillaume Lederer <guim@claroline.net>
 * @author Christophe Gesché <moosh@claroline.net>
 *
 */

$coursePerPage= 20;

$cidReset = TRUE;$gidReset = TRUE;$tidReset = TRUE;

require '../inc/claro_init_global.inc.php';

include_once $includePath . '/lib/admin.lib.inc.php';
include_once $includePath . '/lib/user.lib.php';

include $includePath . '/conf/user_profile.conf.php';

// Security check
if ( ! $_uid ) claro_disp_auth_form();
if ( ! $is_platformAdmin ) claro_die(get_lang('Not allowed'));

$iconForCuStatus['STUDENT']        = 'user.gif';
$iconForCuStatus['COURSE_MANAGER'] = 'manager.gif';

$tbl_mdb_names       = claro_sql_get_main_tbl();
$tbl_user            = $tbl_mdb_names['user'  ];
$tbl_course          = $tbl_mdb_names['course'];
$tbl_admin           = $tbl_mdb_names['admin' ];
$tbl_rel_course_user = $tbl_mdb_names['rel_course_user' ];

// See SESSION variables used for reorder criteria :

if ( isset($_REQUEST['order_crit']) )
{
    $_SESSION['admin_user_course_order_crit'] = trim($_REQUEST['order_crit']) ;
}

if ( isset($_REQUEST['dir']) )
{
    $_SESSION['admin_user_course_dir'] = ($_REQUEST['dir']=='DESC'?'DESC':'ASC');
}

//set the reorder parameters for colomuns titles

if (!isset($order['code']))     $order['code']     = '';
if (!isset($order['label']))    $order['label']    = '';
if (!isset($order['titular']))  $order['titular']  = '';
if (!isset($order['cuStatus'])) $order['cuStatus'] = '';

//find which user is concerned in URL parameters

$dialogBox = '';
$nameTools = get_lang('UserCourse list');
$interbredcrump[]= array ( 'url' => $rootAdminWeb, 'name' => get_lang('Administration'));

if ((isset($_REQUEST['uidToEdit']) && $_REQUEST['uidToEdit'] == '') || !isset($_REQUEST['uidToEdit']))
{
    unset($_REQUEST['uidToEdit']);
    $dialogBox .= 'ERROR : NO USER SET!!!';
    $uidToEdit = -1;

}
else
{
    $uidToEdit = (int) $_REQUEST['uidToEdit'];
}

// initialisation of global variables and used libraries

include_once $includePath . '/lib/pager.lib.php';

if ( empty($uidToEdit) ) $dialogBox .= 'ERROR : NO USER SET!!!';
//----------------------------------
// EXECUTE COMMAND
//----------------------------------

$cmd = (isset($_REQUEST['cmd'])) ? $_REQUEST['cmd'] : null;

switch ($cmd)
{
    case 'unsubscribe' :

    if ( user_remove_from_course($uidToEdit,$_REQUEST['code'],true) )
    {
        $dialogBox .= get_lang('The user has been successfully unregistered');
    }
    else
    {
        switch ( claro_failure::get_last_failure() )
        {
            case 'cannot_unsubscribe_the_last_course_manager' :
            {
                $dialogBox .= get_lang('You cannot unsubscribe the last course manager of the course');
            }   break;
            case 'course_manager_cannot_unsubscribe_himself' :
            {
                $dialogBox .= get_lang('Course manager cannot unsubscribe himself');
            }   break;
            default :
            {

            }
        }
    }
    break;
}

//----------------------------------
// PREPARE VIEW
//----------------------------------



//----------------------------------
// Build query and find info in db
//----------------------------------

// needed to display the name of the user we are watching

$userData = user_get_data( $uidToEdit );

// main query to know what must be displayed in the list

$sql = "SELECT  * , IF(CU.statut=1,'COURSE_MANAGER','STUDENT') cu_statut
            FROM `".$tbl_course."` AS C ";

$toAdd = ", `".$tbl_rel_course_user."` AS CU ";
$toAdd .=" WHERE CU.`code_cours` = C.`code`  AND CU.`user_id` = " . (int)$uidToEdit;

$sql .= $toAdd;


//deal with LETTER classification call

if (isset($_REQUEST['letter']))
{
    $toAdd = " AND C.`intitule` LIKE '%". addslashes($_REQUEST['letter']) ."%' ";
    $sql.=$toAdd;
}

//deal with KEY WORDS classification call

if (isset($_REQUEST['search']))
{
    $toAdd = " AND (    C.`intitule` LIKE '%". addslashes($_REQUEST['search']) ."%'
                         OR C.`code` LIKE '%". addslashes($_REQUEST['search']) ."%'
                       )";
    $sql.=$toAdd;
}

// deal with REORDER
//first see is direction must be changed

// deal with REORDER
if (isset($_SESSION['admin_user_course_order_crit']))
{
    switch ($_SESSION['admin_user_course_order_crit'])
    {
        case 'uid'      : $fieldSort = 'CU`.`user_id';  break;
        case 'label'    : $fieldSort = 'C`.`intitule';  break;
        case 'titular'  : $fieldSort = 'C`.`titulaires';break;
        case 'code'     : $fieldSort = 'C`.`code';      break;
        case 'cuStatus' : $fieldSort = 'CU`.`statut';   break;
        //        case 'email'  : $fieldSort = 'email';
    }
    $toAdd = " ORDER BY `".$fieldSort."` ".$_SESSION['admin_user_course_dir'];
    $order[$_SESSION['admin_user_course_order_crit']] = ($_SESSION['admin_user_course_dir']=='ASC'?'DESC':'ASC');
    $sql.=$toAdd;
}

//Build SQL query

$offset = (int) (!isset($_REQUEST['offset'])) ? 0 :  $_REQUEST['offset'];

$myPager = new claro_sql_pager($sql, $offset, $coursePerPage);
$resultList = $myPager->get_result_list();


if (!isset($addToUrl)) $addToUrl ='';


//display title

$nameTools .= ' : ' . $userData['firstname'] . ' ' . $userData['lastname'];

// javascript confirm pop up declaration
$htmlHeadXtra[] =
"<script>
            function confirmationUnReg (name)
            {
                if (confirm(\"".clean_str_for_javascript(get_lang('Are you sure you want to unregister '))." \"+ name + \"? \"))
                    {return true;}
                else
                    {return false;}
            }
            </script>";

$cmdList[] =  '<a class="claroCmd" href="adminprofile.php?uidToEdit=' . $uidToEdit . '\">' . get_lang('See user settings') . '</a>';
$cmdList[] =  '<a class="claroCmd"  href="../auth/courses.php?cmd=rqReg&amp;uidToEdit=' . $uidToEdit . '&amp;category=&amp;fromAdmin=usercourse">' . get_lang('Enrol to a new course') . '</a>';

if (isset($cfrom) && $cfrom == 'ulist')  //if we come from user list, we must display go back to list
{
    $cmdList[] = '<a class="claroCmd" href="adminusers.php">' . get_lang('Back to user list') . '</a>';
    $addToUrl = '&amp;cfrom=ulist';
}


//----------------------------------
// DISPLAY VIEWS
//----------------------------------

include $includePath . '/claro_init_header.inc.php';
echo claro_disp_tool_title($nameTools);

// display forms and dialogBox, alphabetic choice,...

if ( !empty($dialogBox) )
{
    echo claro_html::message_box($dialogBox);
}


echo claro_html::menu_horizontal($cmdList);

//Pager

echo $myPager->disp_pager_tool_bar($_SERVER['PHP_SELF'] . '"?uidToEdit=' . $uidToEdit);

// display User's course list

// table

echo '<table class="claroTable emphaseLine" width="100%" border="0" cellspacing="2" >'
.    '<thead>'
.    '<caption >'
.    '<img src="'.$imgRepositoryWeb.$iconForCuStatus['STUDENT'].'" alt="STUDENT" border="0" title="statut" > Student '
.    '<img src="'.$imgRepositoryWeb.$iconForCuStatus['COURSE_MANAGER'].'" alt="course manager" border="0" title="statut" > Course Manager '
.    '</caption>'
.    '<tr class="headerX" align="center" valign="top">'

//    add titles for the table
.    '<th><a href="' . $_SERVER['PHP_SELF'] . '?order_crit=code&amp;dir=' . $order['code'] . '&amp;uidToEdit=' . $uidToEdit . '">' . get_lang('Course code') . '</a></th>'
.    '<th><a href="' . $_SERVER['PHP_SELF'] . '?order_crit=label&amp;dir=' . $order['label'] . '&amp;uidToEdit=' . $uidToEdit . '">' . get_lang('Course title') . '</a></th>'
.    '<th><a href="' . $_SERVER['PHP_SELF'] . '?order_crit=titular&amp;dir=' . $order['titular'] . '&amp;uidToEdit=' . $uidToEdit . '">' . get_lang('Course manager') . '</a></th>'
.    '<th><a href="' . $_SERVER['PHP_SELF'] . '?order_crit=cuStatus&amp;dir=' . $order['cuStatus'] . '&amp;uidToEdit=' . $uidToEdit . '">' . get_lang('Role') . '</a></th>'
.    '<th>' . get_lang('Unregister user') . '</th>'
.    '</tr>'
.    '</thead>' . "\n"
;

// Display list of the course of the user :

echo '<tbody>'."\n";

if(is_array($resultList))
foreach($resultList as $list)
{
    echo '<tr>';

    //  Code

    echo '<td>' . $list['fake_code'] . '</td>';

    // title

    echo '<td align="left"><a href="'. $clarolineRepositoryWeb . 'course/index.php?cid=' . htmlspecialchars($list['code']) . '">'.$list['intitule'].'</a></td>';

    //  Titular

    echo '<td align="left">'.$list['titulaires'].'</td>';

    //  Status
    echo '<td align="center">'
    .    '<a href="adminUserCourseSettings.php?cidToEdit='.$list['code'].'&amp;uidToEdit='.$uidToEdit.'&amp;ccfrom=uclist">'
    .    '<img src="'.$imgRepositoryWeb.$iconForCuStatus[$list['cu_statut']].'" alt="'.$list['cu_statut'].'" border="0" title="'.$list['cu_statut'].'" >'
    .    '</a>'
    .    '</td>'
    ;
    // Edit user course settings

    //  Unsubscribe link
    echo '<td align="center">'."\n"
    .    '<a href="' . $_SERVER['PHP_SELF']
    .    '?uidToEdit=' . $uidToEdit
    .    '&amp;cmd=unsubscribe' . $addToUrl
    .    '&amp;code=' . $list['code']
    .    '&amp;offset=' . $offset . '"'
    .    ' onClick="return confirmationUnReg(\''.clean_str_for_javascript($userData['firstname'] . ' ' . $userData['lastname']).'\');">' . "\n"
    .    '<img src="' . $imgRepositoryWeb . 'unenroll.gif" border="0" alt="' . get_lang('Delete') . '" />' . "\n"
    .    '</a>' . "\n"
    .    '</td>' . "\n"
    .    '</tr>'
    ;

    $atLeastOne = TRUE;
}

if (!isset($atLeastOne) || !$atLeastOne)
{
    echo '<tr>'
    .    '<td colspan="5" align="center">'
    .    get_lang('No course to display')
    .    '</td>'
    .    '</tr>'
    ;
}

echo '<tbody>'
.    '</table>'
;


echo $myPager->disp_pager_tool_bar($_SERVER['PHP_SELF'] . '?uidToEdit=' . $uidToEdit);

// display footer
include $includePath . '/claro_init_footer.inc.php';
?>