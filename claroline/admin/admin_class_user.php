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
 */

// initialisation of global variables and used libraries
require '../inc/claro_init_global.inc.php';

require_once $includePath . '/lib/pager.lib.php';
require_once $includePath . '/lib/class.lib.php';
require_once $includePath . '/lib/admin.lib.inc.php';
require_once $includePath . '/lib/user.lib.php';

// Security check
if ( ! $_uid ) claro_disp_auth_form();
if ( ! $is_platformAdmin ) claro_die(get_lang('Not allowed'));

/**#@+
 * DB tables definition
 * @var $tbl_mdb_names array table name for the central database
 */
$tbl_mdb_names = claro_sql_get_main_tbl();
$tbl_user       = $tbl_mdb_names['user'];
$tbl_class      = $tbl_mdb_names['user_category'];
$tbl_class_user = $tbl_mdb_names['user_rel_profile_category'];
/**#@-*/

if ((isset($_REQUEST['cidToEdit']) && $_REQUEST['cidToEdit']=='') || !isset($_REQUEST['cidToEdit']))
{
    unset($cidToEdit);
}


//------------------------
//  USED SESSION VARIABLES
//------------------------

// deal with session variables for search criteria

if (isset($_REQUEST['dir']))
{
    $_SESSION['admin_user_class_dir']  = ($_REQUEST['dir']=='DESC'?'DESC':'ASC');
}


if(file_exists($includePath.'/currentVersion.inc.php')) include $includePath . '/currentVersion.inc.php';

// javascript confirm pop up declaration

   $htmlHeadXtra[] =
            "<script>
            function confirmationUnReg (name)
            {
                if (confirm(\"".clean_str_for_javascript(get_lang('Are you sure you want to unregister '))."\"+ name + \"? \"))
                    {return true;}
                else
                    {return false;}
            }
            </script>";

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

        $classes_list = getSubClasses($_SESSION['admin_user_class_id']);
        $classes_list[] = $_SESSION['admin_user_class_id'];

        $sql = "DELETE FROM `" . $tbl_class_user . "`
                WHERE `user_id` = " . (int) $_REQUEST['userid'] . "
                AND `class_id`
                 in (" . implode($classes_list,",") . ")";

        claro_sql_query($sql);
        $dialogBox = get_lang('User has been sucessfully unregistered from the class');
        break;

    default :
        // No command

}

//----------------------------------
// Build query and find info in db
//----------------------------------

//find info about the class

$sqlclass = "SELECT *
             FROM `" . $tbl_class . "`
             WHERE `id`=" . (int) $_SESSION['admin_user_class_id'];
$classinfo = claro_sql_query_get_single_row($sqlclass);

//find this class current content

$classes_list = getSubClasses($_SESSION['admin_user_class_id']);
$classes_list[] = $_SESSION['admin_user_class_id'];

$sql = "SELECT distinct U.user_id      AS user_id,
                        U.nom          AS nom,
                        U.prenom       AS prenom,
                        U.nom          AS lastname,
                        U.prenom       AS firstname,
                        U.email        AS email,
                        U.officialCode AS officialCode
        FROM `" . $tbl_user . "` AS U
        LEFT JOIN `" . $tbl_class_user . "` AS CU
            ON U.`user_id`= CU.`user_id`
        WHERE `CU`.`class_id`
            in (" . implode($classes_list,",") . ")";


//first see if direction must be changed

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
    $toAdd = " ORDER BY `".$_SESSION['admin_user_class_order_crit'] . "` " . $_SESSION['admin_user_class_dir'];
    $sql.=$toAdd;

}

//Build pager with SQL request
if (!isset($_REQUEST['offset'])) $offset = '0';
else                             $offset = $_REQUEST['offset'];

$myPager = new claro_sql_pager($sql, $offset, get_conf('userPerPage', 20) );
$resultList = $myPager->get_result_list();

/**
 * PREPARE DISPLAY
 */
// Deal with interbredcrumps
$interbredcrump[]= array ('url' => $rootAdminWeb, 'name' => get_lang('Administration'));
$interbredcrump[]= array ('url' => $rootAdminWeb . 'admin_class.php', 'name' => get_lang('Classes'));
$nameTools = get_lang('Class members');

$cmd_menu[] = '<a class="claroCmd" href="' . $clarolineRepositoryWeb . 'admin/admin_class_register.php'
.             '?class=' . $classinfo['id'] . '">'
.             '<img src="'.$imgRepositoryWeb . 'enroll.gif" border="0"/> '
.             get_lang('Register a user for this class') . '</a>'
;
$cmd_menu[] = '<a class="claroCmd" href="'.$clarolineRepositoryWeb.'auth/courses.php'
.             '?cmd=rqReg&amp;fromAdmin=class">'
.             '<img src="'.$imgRepositoryWeb.'enroll.gif" border="0" /> '
.             get_lang('Register class for course')
.             '</a>'
;
$cmd_menu[] = '<a class="claroCmd" href="'.$clarolineRepositoryWeb.'user/AddCSVusers.php'
.             '?AddType=adminClassTool">'
.             '<img src="'.$imgRepositoryWeb.'importlist.gif" border="0" /> '
.             get_lang('Add a user list in class')
.             '</a>'
;


//------------------------------------
// DISPLAY
//------------------------------------

if (!isset($addToUrl)) $addToUrl ='';

//Header
include $includePath . '/claro_init_header.inc.php';

echo claro_html_tool_title($nameTools . ' : ' . $classinfo['name']);

if (isset($dialogBox))  echo claro_html_message_box($dialogBox). '<br />';

echo claro_html_menu_horizontal($cmd_menu)
.    '<br /><br />'
.    $myPager->disp_pager_tool_bar($_SERVER['PHP_SELF'])


// Display list of users

// start table...

.    '<table class="claroTable emphaseLine" width="100%" border="0" cellspacing="2">'
.    '<thead>'
.    '<tr class="headerX" align="center" valign="top">'
.    '<th><a href="' . $_SERVER['PHP_SELF'] . '?order_crit=user_id&amp;chdir=yes">' . get_lang('Userid') . '</a></th>'
.    '<th><a href="' . $_SERVER['PHP_SELF'] . '?order_crit=nom&amp;chdir=yes">' . get_lang('Last name') . '</a></th>'
.    '<th><a href="' . $_SERVER['PHP_SELF'] . '?order_crit=prenom&amp;chdir=yes">' . get_lang('First name') . '</a></th>'
.    '<th><a href="' . $_SERVER['PHP_SELF'] . '?order_crit=officialCode&amp;chdir=yes">' . get_lang('Administrative code') . '</a></th>'
.    '<th>' . get_lang('Email') . '</th>'
.    '<th>' . get_lang('Unregister from class') . '</th>'
.    '</tr>'
.    '</thead>'
.    '<tbody>'
;

   // Start the list of users...
foreach($resultList as $list)
{
     $list['officialCode'] = (isset($list['officialCode']) ? $list['officialCode'] :' - ');

     echo '<tr>'
     .    '<td align="center" >' . $list['user_id']      . '</td>'
     .    '<td align="left" >'   . $list['nom']          . '</td>'
     .    '<td align="left" >'   . $list['prenom']       . '</td>'
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
    .    get_lang('No user to display')
    .    '<br />'
    .    '<a href="' . $clarolineRepositoryWeb . 'admin/admin_class.php' . $addtoAdvanced . '">'
    .    get_lang('Back')
    .    '</a>'
    .    '</td>'
    .    '</tr>'
    ;
}
echo '</tbody>'."\n"
.    '</table>'."\n"
;

//Pager

echo $myPager->disp_pager_tool_bar($_SERVER['PHP_SELF']);

include $includePath . '/claro_init_footer.inc.php';

?>
