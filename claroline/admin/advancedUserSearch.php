<?php // $Id$
/**
 * CLAROLINE 
 *
 * @version 1.8 $Revision$
 *
 * @copyright (c) 2001-2006 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE 
 * 
 * @package CLUSR
 *
 * @author claro team <cvs@claroline.net>
 * @author Guillaume Lederer <lederer@claroline.net>
 * @author Christophe Gesché <moosh@claroline.net>
 */

$cidReset = TRUE;$gidReset = TRUE;$tidReset = TRUE;

require '../inc/claro_init_global.inc.php';

// Security check
if ( ! $_uid ) claro_disp_auth_form();
if ( ! $is_platformAdmin ) claro_die(get_lang('Not allowed'));

include_once($includePath . '/lib/admin.lib.inc.php');
include_once($includePath . '/lib/form.lib.php');

//-----------------------------------------------------------------------------------------------------------
//  USED SESSION VARIABLES
//-----------------------------------------------------------------------------------------------------------
// deal with session variables clean session variables from previous search


unset($_SESSION['admin_user_letter']);
unset($_SESSION['admin_user_search']);
unset($_SESSION['admin_user_firstName']);
unset($_SESSION['admin_user_lastName']);
unset($_SESSION['admin_user_userName']);
unset($_SESSION['admin_user_mail']);
unset($_SESSION['admin_user_action']);
unset($_SESSION['admin_order_crit']);

//declare needed tables
$tbl_mdb_names    = claro_sql_get_main_tbl();
$tbl_course_nodes = $tbl_mdb_names['category'];

// Deal with interbredcrumps  and title variable

$interbredcrump[]= array ('url' => $rootAdminWeb, 'name' => get_lang('Administration'));
$nameTools = get_lang('Advanced user search');

//retrieve needed parameters from URL to prefill search form

if (isset($_REQUEST['action']))    $action    = $_REQUEST['action'];    else $action = '';
if (isset($_REQUEST['lastName']))  $lastName  = $_REQUEST['lastName'];  else $lastName = '';
if (isset($_REQUEST['firstName'])) $firstName = $_REQUEST['firstName']; else $firstName = '';
if (isset($_REQUEST['userName']))  $userName  = $_REQUEST['userName'];  else $userName = '';
if (isset($_REQUEST['mail']))      $mail      = $_REQUEST['mail'];      else $mail = '';

//header and bredcrump display









/////////////
// OUTPUT

include($includePath . '/claro_init_header.inc.php');
echo claro_html::tool_title($nameTools . ' : ');
?>
<form action="adminusers.php" method="GET" >
<table border="0">
    <tr>
        <td align="right">
            <label for="lastName"><?php echo get_lang('Last name')?></label>
            : <br />
        </td>
        <td>
            <input type="text" name="lastName" id="lastName" value="<?php echo htmlspecialchars($lastName); ?>"/>
        </td>
    </tr>

    <tr>
        <td align="right">
            <label for="firstName"><?php echo get_lang('First name')?></label>
            : <br />
        </td>
        <td>
            <input type="text" name="firstName" id="firstName" value="<?php echo htmlspecialchars($firstName) ?>"/>
        </td>
    </tr>
    
    <tr>
        <td align="right">
            <label for="userName"><?php echo get_lang('Username') ?></label> 
            :  <br />
        </td>
        <td>
            <input type="text" name="userName" id="userName" value="<?php echo htmlspecialchars($userName); ?>"/>
        </td>
    </tr>

    <tr>
        <td align="right">
            <label for="mail"><?php echo get_lang('Email') ?></label> 
            : <br />
        </td>
        <td>
            <input type="text" name="mail" id="mail" value="<?php echo htmlspecialchars($mail); ?>"/>
        </td>
    </tr>

<tr>
  <td align="right">
   <label for="action"><?php echo get_lang('Action') ?></label> : <br />
  </td>
  <td>
<?php 

$action_list['all'] = get_lang('All');
$action_list['followcourse'] = get_lang('Student');
$action_list['createcourse'] =  get_lang('Course creator');
$action_list['plateformadmin'] = get_lang('Platform Administrator');


echo claro_html_form_select( 'action'
                            , $action_list
                            , $action
                            , array('id'=>'action'))
                                     ; ?>

    </td>
</tr>
<tr>
    <td>
    </td>
    <td>
        <input type="submit" class="claroButton" value="<?php echo get_lang('Search user')?>" >
    </td>
</tr>
</table>
</form>
<?php
include $includePath . '/claro_init_footer.inc.php';
?>
