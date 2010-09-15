<?php // $Id$
/**
 * CLAROLINE
 *
 * @version 1.9 $Revision$
 *
 * @copyright (c) 2001-2010, Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @package CLUSR
 *
 * @author claro team <cvs@claroline.net>
 * @author Guillaume Lederer <lederer@claroline.net>
 * @author Christophe Gesché <moosh@claroline.net>
 * @author Dimitri Rambout <dimitri.rambout@uclouvain.be>
 */

$cidReset = TRUE;$gidReset = TRUE;$tidReset = TRUE;

require '../inc/claro_init_global.inc.php';

// Security check
if ( ! claro_is_user_authenticated() ) claro_disp_auth_form();
if ( ! claro_is_platform_admin() ) claro_die(get_lang('Not allowed'));

include_once(get_path('incRepositorySys') . '/lib/admin.lib.inc.php');
include_once(get_path('incRepositorySys') . '/lib/form.lib.php');

//-----------------------------------------------------------------------------------------------------------
//  USED SESSION VARIABLES
//-----------------------------------------------------------------------------------------------------------
// deal with session variables clean session variables from previous search


unset($_SESSION['admin_user_letter']);
unset($_SESSION['admin_user_search']);
unset($_SESSION['admin_user_firstName']);
unset($_SESSION['admin_user_lastName']);
unset($_SESSION['admin_user_userName']);
unset($_SESSION['admin_user_officialCode']);
unset($_SESSION['admin_user_mail']);
unset($_SESSION['admin_user_action']);
unset($_SESSION['admin_order_crit']);

//declare needed tables
$tbl_mdb_names    = claro_sql_get_main_tbl();
$tbl_course_nodes = $tbl_mdb_names['category'];

// Deal with interbredcrumps  and title variable

ClaroBreadCrumbs::getInstance()->prepend( get_lang('Administration'), get_path('rootAdminWeb') );
$nameTools = get_lang('Advanced user search');

//retrieve needed parameters from URL to prefill search form

if (isset($_REQUEST['action']))    $action    = $_REQUEST['action'];    else $action = '';
if (isset($_REQUEST['lastName']))  $lastName  = $_REQUEST['lastName'];  else $lastName = '';
if (isset($_REQUEST['firstName'])) $firstName = $_REQUEST['firstName']; else $firstName = '';
if (isset($_REQUEST['userName']))  $userName  = $_REQUEST['userName'];  else $userName = '';
if (isset($_REQUEST['officialCode']))  $userName  = $_REQUEST['officialCode'];  else $officialCode = '';
if (isset($_REQUEST['mail']))      $mail      = $_REQUEST['mail'];      else $mail = '';

$action_list[get_lang('All')] = 'all';
$action_list[get_lang('Student')] = 'followcourse';
$action_list[get_lang('Course creator')] = 'createcourse';
$action_list[get_lang('Platform administrator')] = 'plateformadmin';

//header and bredcrump display

/////////////
// OUTPUT

$out = '';
$out .= claro_html_tool_title($nameTools . ' : ');

$tpl = new PhpTemplate( get_path( 'incRepositorySys' ) . '/templates/advancedUserSearch.tpl.php' );
$tpl->assign('lastName', $lastName);
$tpl->assign('firstName', $firstName);
$tpl->assign('userName', $userName);
$tpl->assign('officialCode', $officialCode);
$tpl->assign('mail', $mail);
$tpl->assign('action', $action);
$tpl->assign('action_list', $action_list);

$out .= $tpl->render();

$claroline->display->body->appendContent($out);

echo $claroline->display->render();

?>