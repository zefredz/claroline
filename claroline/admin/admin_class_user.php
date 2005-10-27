<?php //$Id$
/**
 * CLAROLINE
 *
 * this tool manage the
 *
 * @version 1.7 $Revision$
 *
 * @copyright (c) 2001-2005 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @author Claro Team <cvs@claroline.net>
 */
$userPerPage = 20; // numbers of user to display on the same page

// initialisation of global variables and used libraries
require '../inc/claro_init_global.inc.php';

require_once $includePath.'/lib/pager.lib.php';
require_once $includePath.'/lib/class.lib.php';
require_once $includePath.'/lib/admin.lib.inc.php';

// Security check
if ( ! $_uid ) claro_disp_auth_form();
if ( ! $is_platformAdmin ) claro_die($langNotAllowed);

if ((isset($_REQUEST['cidToEdit']) && $_REQUEST['cidToEdit']=='') || !isset($_REQUEST['cidToEdit']))
{
    unset($cidToEdit);
}


//------------------------
//  USED SESSION VARIABLES
//------------------------

// deal with session variables for search criteria

if (isset($_REQUEST['dir'])) {$_SESSION['admin_user_class_dir']  = ($_REQUEST['dir']=='DESC'?'DESC':'ASC');}


if(file_exists($includePath.'/currentVersion.inc.php')) include ($includePath.'/currentVersion.inc.php');

// javascript confirm pop up declaration

   $htmlHeadXtra[] =
            "<script>
            function confirmationUnReg (name)
            {
                if (confirm(\"".clean_str_for_javascript($langAreYouSureToUnsubscribe)."\"+ name + \"? \"))
                    {return true;}
                else
                    {return false;}
            }
            </script>";

// Deal with interbredcrumps
$interbredcrump[]= array ('url' => $rootAdminWeb, 'name' => $langAdministration);
$interbredcrump[]= array ('url' => $rootAdminWeb.'admin_class.php', 'name' => $langClass);
$nameTools = $langClassMembers;

//Header

include($includePath.'/claro_init_header.inc.php');

/**#@+
 * DB tables definition
 * @var $tbl_mdb_names array table name for the central database
 */
$tbl_mdb_names = claro_sql_get_main_tbl();
$tbl_user       = $tbl_mdb_names['user'];
$tbl_class      = $tbl_mdb_names['user_category'];
$tbl_class_user = $tbl_mdb_names['user_rel_profile_category'];
/**#@-*/

//SESSION VARIABLES

if (isset($_REQUEST['class']))
{
    $_SESSION['admin_user_class_id'] = $_REQUEST['class'];
}


//------------------------------------
// Execute COMMAND section
//------------------------------------
if (isset($_REQUEST['cmd'])) $cmd = $_REQUEST['cmd'];
else                         $cmd = null;

switch ($cmd)
{
    case 'unsubscribe' :
        $sql = "DELETE FROM `".$tbl_class_user."`
                WHERE `user_id`='" . (int) $_REQUEST['userid'] . "'";
        claro_sql_query($sql);
        $dialogBox = $langUserUnregisteredFromClass;
        break;

    default :
        // No command

}

//----------------------------------
// Build query and find info in db
//----------------------------------

//find info about the class

$sqlclass = "SELECT *
             FROM `".$tbl_class."`
             WHERE `id`='". (int)$_SESSION['admin_user_class_id']."'";
list($classinfo) = claro_sql_query_fetch_all($sqlclass);

//find this current content

$sql = "SELECT *
        FROM `".$tbl_user."` AS U
            LEFT JOIN `".$tbl_class_user."` AS CU
            ON U.`user_id`= CU.`user_id`
        WHERE `class_id`='" . (int)$_SESSION['admin_user_class_id'] . "'
        ";


//first see is direction must be changed

if (isset($_REQUEST['chdir']) && ($_REQUEST['chdir']=="yes"))
{
    if     ($_SESSION['admin_user_class_dir'] == 'ASC')  {$_SESSION['admin_user_class_dir']='DESC';}
    elseif ($_SESSION['admin_user_class_dir'] == 'DESC') {$_SESSION['admin_user_class_dir']='ASC';}
}
elseif (!isset($_SESSION['admin_user_class_dir']))
{
    $_SESSION['admin_user_class_dir'] = 'DESC';
}

// deal with REORDER

if (isset($_REQUEST['order_crit']))
{
    $_SESSION['admin_user_class_order_crit'] = $_REQUEST['order_crit'];
    if ($_REQUEST['order_crit']=='user_id')
    {
        $_SESSION['admin_user_class_order_crit'] = 'U`.`user_id';
    }
}
if (isset($_SESSION['admin_user_class_order_crit']))
{
    $toAdd = " ORDER BY `".$_SESSION['admin_user_class_order_crit']."` ".$_SESSION['admin_user_class_dir'];
    $sql.=$toAdd;

}

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
$resultList = $myPager->get_result_list();

//------------------------------------
// DISPLAY
//------------------------------------

if (!isset($addToUrl)) $addToUrl ='';

// Display tool title

echo claro_disp_tool_title($nameTools . ' : ' . $classinfo['name']);

//Display Forms or dialog box(if needed)

if (isset($dialogBox))
{
    echo claro_disp_message_box($dialogBox);
    echo '<br>';
}

//TOOL LINKS

echo '<a class="claroCmd" href="' . $clarolineRepositoryWeb . 'admin/admin_class_register.php'
.    '?class='.$classinfo['id'].'">'
.    '<img src="'.$imgRepositoryWeb . 'enroll.gif" border="0"/> '
.    $langClassRegisterUser . '</a>'
.    ' | '
.    '<a class="claroCmd" href="'.$clarolineRepositoryWeb.'auth/courses.php'
.    '?cmd=rqReg&amp;fromAdmin=class">'
.    '<img src="'.$imgRepositoryWeb.'enroll.gif" border="0" /> '
.    $langClassRegisterWholeClass
.    '</a>'
.    ' | '
.    '<a class="claroCmd" href="'.$clarolineRepositoryWeb.'user/AddCSVusers.php'
.    '?AddType=adminClassTool">'
.    '<img src="'.$imgRepositoryWeb.'importlist.gif" border="0" /> '
.    $langAddCSVUsersInClass
.    '</a>'
.    '<br><br>'
;

   //Pager

$myPager->disp_pager_tool_bar($_SERVER['PHP_SELF']);


// Display list of users

   // start table...

echo '<table class="claroTable emphaseLine" width="100%" border="0" cellspacing="2">'
   . '<thead>'
   . '<tr class="headerX" align="center" valign="top">'
   . '<th><a href="'.$_SERVER['PHP_SELF'].'?order_crit=user_id&amp;chdir=yes">'.$langUserid.'</a></th>'
   . '<th><a href="'.$_SERVER['PHP_SELF'].'?order_crit=nom&amp;chdir=yes">'.$langLastName.'</a></th>'
   . '<th><a href="'.$_SERVER['PHP_SELF'].'?order_crit=prenom&amp;chdir=yes">'.$langFirstName.'</a></th>'
   . '<th><a href="'.$_SERVER['PHP_SELF'].'?order_crit=officialCode&amp;chdir=yes">'.$langOfficialCode.'</a></th>'
   . '<th>'.$langEmail.'</th>'
   . '<th>'.$langUnsubscribeClass.'</th>'
   . '</tr>'
   . '</thead>'
   . '<tbody>'
   ;

   // Start the list of users...
foreach($resultList as $list)
{
     $list['officialCode'] = (isset($list['officialCode']) ? $list['officialCode'] :' - ');

     echo '<tr>'
     .    '<td align="center" >' . $list['user_id']      .'</td>'
     .    '<td align="left" >'   . $list['nom']          .'</td>'
     .    '<td align="left" >'   . $list['prenom']       .'</td>'
     .    '<td align="center">'  . $list['officialCode'] . '</td>'
     .    '<td align="left">'    . $list['email']        . '</td>'
     .    '<td align="center">'  ."\n"
     .    '<a href="'.$_SERVER['PHP_SELF']
     .    '?cmd=unsubscribe'.$addToUrl.'&amp;offset='.$offset.'&amp;userid='.$list['user_id'].'" '
     .    ' onClick="return confirmationUnReg(\''.clean_str_for_javascript($list['prenom'] . ' ' . $list['nom']).'\');">' . "\n"
     .    '<img src="' . $imgRepositoryWeb . 'unenroll.gif" border="0" alt="" />' . "\n"
     .    '</a>' . "\n"
     .    '</td>' . "\n"
     ;

     $atLeastOne= TRUE;
}
// end display users table
if (isset($atLeastOne) && !$atLeastOne)
{
    echo '<tr>'
    .    '<td colspan="8" align="center">'
    .    $langNoUserResult
    .    '<br>'
    .    '<a href="' . $clarolineRepositoryWeb . 'admin/admin_class.php' . $addtoAdvanced . '">'
    .    $langBack
    .    '</a>'
    .    '</td>'
    .    '</tr>'
    ;
}
echo '</tbody>'."\n"
.    '</table>'."\n"
;

//Pager

$myPager->disp_pager_tool_bar($_SERVER['PHP_SELF']);

include $includePath . '/claro_init_footer.inc.php';

?>