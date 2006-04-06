<?php //$Id$
/**
 * CLAROLINE
 *
 * this tool manage the
 *
 * @version 1.8 $Revision$
 *
 * @copyright (c) 2001-2006 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @author Claro Team <cvs@claroline.net>
 * @author  Guillaume Lederer <lederer@cerdecam.be>
 */
// initialisation of global variables and used libraries

require '../inc/claro_init_global.inc.php';

require_once $includePath . '/lib/pager.lib.php';
require_once $includePath . '/lib/user.lib.php';
require_once $includePath . '/lib/class.lib.php';
require_once $includePath . '/lib/admin.lib.inc.php';

// Security check
if ( ! $_uid ) claro_disp_auth_form();
if ( ! $is_platformAdmin ) claro_die(get_lang('Not allowed'));

$userPerPage = 20; // numbers of user to display on the same page

if(file_exists($includePath.'/currentVersion.inc.php')) include ($includePath.'/currentVersion.inc.php');

/*
 * DB tables definition
 */
$tbl_mdb_names  = claro_sql_get_main_tbl();
$tbl_user       = $tbl_mdb_names['user'];
$tbl_class      = $tbl_mdb_names['user_category'];
$tbl_class_user = $tbl_mdb_names['user_rel_profile_category'];

//find info about the class

$sqlclass = "SELECT *
             FROM `".$tbl_class."`
             WHERE `id`='".$_SESSION['admin_user_class_id']."'";

list($classinfo) = claro_sql_query_fetch_all($sqlclass);

// See SESSION variables used for reorder criteria :

if (isset($_REQUEST['dir'])) $_SESSION['admin_class_reg_user_order_crit'] = ($_REQUEST['dir']=='DESC'?'DESC':'ASC');
else                         $_REQUEST['dir'] = 'ASC';

//------------------------------------
// Execute COMMAND section
//------------------------------------

if (isset($_REQUEST['cmd'])) $cmd = $_REQUEST['cmd'];
else                         $cmd = null;

switch ($cmd)
{
    case 'subscribe' :
    {
        if (user_add_to_class($_REQUEST['user_id'],$classinfo['id']))
        {
            $dialogBox = get_lang('User has been sucessfully registered to the class');
        }
    } break;

    case 'unsubscribe' :
    {
        $sql ="DELETE FROM `".$tbl_class_user."`
           WHERE `user_id` = ". (int) $_REQUEST['user_id']."
             AND `class_id` = ". (int) $classinfo['id'];
        claro_sql_query($sql);

        $dialogBox = get_lang('User has been sucessfully unregistered from the class');
    } break;

}



//----------------------------------
// Build query and find info in db
//----------------------------------


$sql = "SELECT *, U.`user_id`
        FROM  `" . $tbl_user . "` AS U
        LEFT JOIN `" . $tbl_class_user . "` AS CU
               ON  CU.`user_id` = U.`user_id`
              AND CU.`class_id` = " . (int) $classinfo['id'];

// deal with REORDER

// See SESSION variables used for reorder criteria :

if (isset($_REQUEST['order_crit']))
{
    $_SESSION['admin_class_reg_user_order_crit'] = $_REQUEST['order_crit'];
    if ($_REQUEST['order_crit']=="user_id")
    {
        $_SESSION['admin_class_reg_user_order_crit'] = "U`.`user_id";
    }
}
if (isset($_REQUEST['class']))
{
    $_SESSION['admin_user_class_id'] = $_REQUEST['class'];
}
if (!isset($_SESSION['admin_user_class_id']))
{
    $dialogBox ="ERROR : NO CLASS SET!!!";
}

  //first if direction must be changed

if (isset($_REQUEST['chdir']) && ($_REQUEST['chdir']=="yes"))
{
  if ($_SESSION['admin_class_reg_user_dir'] == "ASC") {$_SESSION['admin_class_reg_user_dir']="DESC";}
  elseif ($_SESSION['admin_class_reg_user_dir'] == "DESC") {$_SESSION['admin_class_reg_user_dir']="ASC";}
}
elseif (!isset($_SESSION['admin_class_reg_user_dir']))
{
    $_SESSION['admin_class_reg_user_dir'] = 'DESC';
}

if (isset($_SESSION['admin_class_reg_user_order_crit']))
{
    if ($_SESSION['admin_class_reg_user_order_crit']=="user_id")
    {
        $toAdd = " ORDER BY CU.`user_id` ".$_SESSION['admin_class_reg_user_dir'];
    }
    else
    {
        $toAdd = " ORDER BY `".$_SESSION['admin_class_reg_user_order_crit']."` ".$_SESSION['admin_class_reg_user_dir'];
    }
    $sql.=$toAdd;
}

// Deal with interbredcrumps

$interbredcrump[]= array ('url' => $rootAdminWeb, 'name' => get_lang('Administration'));
$interbredcrump[]= array ('url' => $rootAdminWeb . 'admin_class.php', 'name' => get_lang('Classes'));
$interbredcrump[]    = array ('url' => $rootAdminWeb . 'admin_class_user.php', 'name' => get_lang('Class users'));
$nameTools = get_lang('Register user to class');

//Header
include $includePath . '/claro_init_header.inc.php';

//Build pager with SQL request

if (!isset($_REQUEST['offset'])) $offset = '0';
else                             $offset = $_REQUEST['offset'];


$myPager = new claro_sql_pager($sql, $offset, $userPerPage);
$resultList = $myPager->get_result_list();


//------------------------------------
// DISPLAY
//------------------------------------

// Display tool title

echo claro_html_tool_title($nameTools . ' : ' . $classinfo['name']);

// Display Forms or dialog box(if needed)

if(isset($dialogBox)) echo claro_html_message_box($dialogBox);

//TOOL LINKS

echo '<a class="claroCmd" href="' . $clarolineRepositoryWeb . 'admin/admin_class_user.php?class=' . $classinfo['id'] . '">' . get_lang('Class members') . '</a>'
.    '<br /><br />'
;

if (isset($cfrom) && ($cfrom=="clist")) echo claro_html_button('admincourses.php', get_lang('Back to course list'));

//Pager

echo $myPager->disp_pager_tool_bar($_SERVER['PHP_SELF']);

// Display list of users
// start table...

echo '<table class="claroTable emphaseLine" width="100%" border="0" cellspacing="2">' . "\n"
.    '<thead>' . "\n"
.    '<tr class="headerX" align="center" valign="top">'
.    '<th><a href="' . $_SERVER['PHP_SELF'] . '?order_crit=user_id&amp;chdir=yes">' . get_lang('User Id') . '</a></th>' . "\n"
.    '<th><a href="' . $_SERVER['PHP_SELF'] . '?order_crit=nom&amp;chdir=yes"    >' . get_lang('Last name') . '</a></th>' . "\n"
.    '<th><a href="' . $_SERVER['PHP_SELF'] . '?order_crit=prenom&amp;chdir=yes" >' . get_lang('First name') . '</a></th>' . "\n"
.    '<th>' . get_lang('Register to the class') . '</th>'
.    '<th>' . get_lang('Unregister from class') . '</th>'
.    '</tr>' . "\n"
.    '</thead>' . "\n"
.    '<tbody>' . "\n"
;

   // Start the list of users...

foreach($resultList as $list)
{
     echo '<tr>'
     .    '<td align="center">'
     .    '<a name="u' . $list['user_id'] . '"></a>' // no label in the a it's a target.
     .    $list['user_id'] . '</td>' . "\n"
     .    '<td align="left">' . $list['nom']    . '</td>' . "\n"
     .    '<td align="left">' . $list['prenom'] . '</td>' . "\n"
     ;
     // Register

     if ($list['id']==null)
     {
         echo '<td align="center">' . "\n"
         .    '<a href="' . $_SERVER['PHP_SELF'] . '?class=' . $classinfo['id'] . '&amp;cmd=subscribe&user_id=' . $list['user_id'].'&amp;offset=' . $offset . '#u' . $list['user_id'] . '">' . "\n"
         .    '<img src="' . $imgRepositoryWeb . 'enroll.gif" border="0" alt="' . get_lang('Register to the class') . '" />' . "\n"
         .    '</a>' . "\n"
         .    '</td>' . "\n"
         ;
     }
     else
     {
         echo '<td align="center">' . "\n"
         .    '<small>' . get_lang('User already in class') . '</small>' . "\n"
         .    '</td>' . "\n"
         ;
     }

// Unregister

     if ($list['id']!=null)
     {
         echo '<td align="center">' . "\n"
         .    '<a href="'.$_SERVER['PHP_SELF'].'?class='.$classinfo['id'].'&amp;cmd=unsubscribe&user_id='.$list['user_id'].'&amp;offset='.$offset.'#u'.$list['user_id'].'">' . "\n"
         .    '<img src="' . $imgRepositoryWeb . 'unenroll.gif" border="0" alt="' . get_lang('Unregister from class').'" />' . "\n"
         .    '</a>' . "\n"
         .    '</td>' . "\n"
         ;
     }
     else
     {
         echo '<td align="center">' . "\n"
         .    '<small>' . get_lang('User not in the class') . '</small>' . "\n"
         .    '</td>' . "\n"
         ;
     }
     echo '</tr>' . "\n";
}

   // end display users table

echo '</tbody>' . "\n"
.    '</table>' . "\n"
;

//Pager

echo $myPager->disp_pager_tool_bar($_SERVER['PHP_SELF']);

include $includePath . '/claro_init_footer.inc.php';
?>
