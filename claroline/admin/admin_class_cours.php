<?php //$Id$
/**
 * CLAROLINE
 *
 * this tool manage the 
 *
 * @version 1.0 
 *
 * @copyright (c) 2001-2005 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @author Damien Garros <dgarros@univ-catholyon.fr>
 */
$userPerPage = 20; // numbers of cours to display on the same page

// initialisation of global variables and used libraries
require '../inc/claro_init_global.inc.php';
	
require_once $includePath . '/lib/pager.lib.php';
require_once $includePath . '/lib/class.lib.php';
require_once $includePath . '/lib/user.lib.php';
require_once $includePath . '/lib/admin.lib.inc.php';

// Security check
if ( ! $_uid ) claro_disp_auth_form();
if ( ! $is_platformAdmin ) claro_die(get_lang('Not allowed'));

/**#@+
 * DB tables definition
 * @var $tbl_mdb_names array table name for the central database
 */

$tbl_mdb_names = claro_sql_get_main_tbl();
$tbl_cours       		= $tbl_mdb_names['course'];
$tbl_cours_class      	= $tbl_mdb_names['rel_course_class'];
$tbl_class      		= $tbl_mdb_names['class'];

if ((isset($_REQUEST['cidToEdit']) && $_REQUEST['cidToEdit']=='') || !isset($_REQUEST['cidToEdit']))
{
    unset($cidToEdit);
}

//----------------------------------------------
//  USED SESSION VARIABLES
//----------------------------------------------

// deal with session variables for search criteria

if (isset($_REQUEST['dir'])) {$_SESSION['admin_user_class_dir']  = ($_REQUEST['dir']=='DESC'?'DESC':'ASC');}


if(file_exists($includePath.'/currentVersion.inc.php')) include ($includePath.'/currentVersion.inc.php');

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

// Deal with interbredcrumps
$interbredcrump[]= array ('url' => $rootAdminWeb, 'name' => get_lang('Administration'));
$interbredcrump[]= array ('url' => $rootAdminWeb . 'admin_class.php', 'name' => get_lang('Classes') );
$nameTools = get_lang('Class members');
//TODO, changer le nom du chapitre

//SESSION VARIABLES

if (isset($_REQUEST['class']))
{
    $_SESSION['admin_cours_class_id'] = $_REQUEST['class'];
	$_SESSION['admin_user_class_id'] = $_SESSION['admin_cours_class_id'];
}

//------------------------------------
// Execute COMMAND section
//------------------------------------
if (isset($_REQUEST['cmd'])) $cmd = $_REQUEST['cmd'];
else                         $cmd = null;

switch ($cmd)
{
    case 'unsubscribe' :
  	{ 
		unregister_class_to_course($_SESSION['admin_cours_class_id'], $_REQUEST['coursid']);
		
    }break;

    default :
        // No command
}

//----------------------------------
// Build query and find info in db
//----------------------------------

//find info about the class

$sqlclass = "SELECT *
             FROM `" . $tbl_class . "`
             WHERE `id`='". (int)$_SESSION['admin_cours_class_id']."'";
list($classinfo) = claro_sql_query_fetch_all($sqlclass);

//find this class current content
  	
     $sql = "SELECT distinct (A.`cours_id`), B.`code`, B.`languageCourse` ,B.`intitule`,B.`faculte`,B.`titulaires`
				FROM `".$tbl_cours_class."` A, `".$tbl_cours."` B
				WHERE B.`cours_id` = A.`cours_id`
				AND A.`class_id` = '".$_SESSION['admin_cours_class_id']."'";

  	                
 //first see is direction must be changed 

if (isset($_REQUEST['chdir']) && ($_REQUEST['chdir']=="yes"))
{
    if     ($_SESSION['admin_cours_class_dir'] == 'ASC')  {$_SESSION['admin_cours_class_dir']='DESC';}
    elseif ($_SESSION['admin_cours_class_dir'] == 'DESC') {$_SESSION['admin_cours_class_dir']='ASC';}
}
elseif (!isset($_SESSION['admin_cours_class_dir']))
{
    $_SESSION['admin_cours_class_dir'] = 'DESC';
}

// deal with REORDER

if (isset($_REQUEST['order_crit']))
{
    $_SESSION['admin_cours_class_order_crit'] = $_REQUEST['order_crit'];
    if ($_REQUEST['order_crit']=='user_id')
    {
        $_SESSION['admin_cours_class_order_crit'] = 'U`.`user_id';
    }
}

if (isset($_SESSION['admin_cours_class_order_crit']))
{
    $toAdd = " ORDER BY `".$_SESSION['admin_cours_class_order_crit'] . "` " . $_SESSION['admin_cours_class_dir'];
    $sql.=$toAdd;

}

//Build pager with SQL request
if (!isset($_REQUEST['offset'])) $offset = "0";
else                             $offset = $_REQUEST['offset'];

$myPager = new claro_sql_pager($sql, $offset, $userPerPage);
$resultList = $myPager->get_result_list();

//------------------------------------
// DISPLAY
//------------------------------------

if (!isset($addToUrl)) $addToUrl ='';

//Header
include $includePath . '/claro_init_header.inc.php';

// Display tool title

echo claro_html_tool_title(get_lang('Course list') . ' : ' . $classinfo['name']);

//Display Forms or dialog box(if needed)

if (isset($dialogBox))
{
    echo claro_html_message_box($dialogBox);
    echo '<br />';
}

//TOOL LINKS

echo '<a class="claroCmd" href="'.$clarolineRepositoryWeb.'auth/courses.php'
.    '?cmd=rqReg&amp;fromAdmin=class">'
.    '<img src="'.$imgRepositoryWeb.'enroll.gif" border="0" /> '
.    get_lang('Register class for course')
.    '</a>'
.    '<br /><br />'
;

   //Pager

$myPager->disp_pager_tool_bar($_SERVER['PHP_SELF']);


// Display list of cours

// start table...

echo '<table class="claroTable emphaseLine" width="100%" border="0" cellspacing="2">'
.    '<thead>'
.    '<tr class="headerX" align="center" valign="top">'
.    '<th><a href="' . $_SERVER['PHP_SELF'] . '?order_crit=code&amp;chdir=yes">' . get_lang('Course code') . '</a></th>'
.    '<th><a href="' . $_SERVER['PHP_SELF'] . '?order_crit=intitule&amp;chdir=yes">' . get_lang('Course title') . '</a></th>'
.    '<th><a href="' . $_SERVER['PHP_SELF'] . '?order_crit=faculte&amp;chdir=yes">' . get_lang('Category') . '</a></th>'
.	 '<th>' . get_lang('Course settings') . '</th>'
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
    .    '<td align="center" >' . $list['code']      . '</td>'
    .    '<td align="left" >'   . $list['intitule']          . '</td>'
    .    '<td align="left" >'   . $list['faculte']       . '</td>'
	.	 '<td align="center">' 
    .    '<a href="../course_info/infocours.php?adminContext=1'
    .    '&amp;cidReq=' . $list['code'] . '&amp;cfrom=xxx">'
    .    '<img src="' . $imgRepositoryWeb . 'settings.gif" alt="' . get_lang('Course settings') . '" />'
    .    '</a>'
    .    '</td>'
    .    '<td align="center">'
    .    '<a href="'.$_SERVER['PHP_SELF']
    .    '?cmd=unsubscribe'.$addToUrl.'&amp;offset='.$offset.'&amp;coursid='.$list['code'].'" '
    .    ' onClick="return confirmationUnReg(\''.clean_str_for_javascript($list['code']).'\');">'
    .    '<img src="' . $imgRepositoryWeb . 'unenroll.gif" border="0" alt="" />'
    .    '</a>'
    .    '</td>'
	.    '</tr>';

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

$myPager->disp_pager_tool_bar($_SERVER['PHP_SELF']);

include $includePath . '/claro_init_footer.inc.php';

?>
