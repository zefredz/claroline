<?php // $Id$
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
 * @author  Guillaume Lederer <lederer@cerdecam.be>
 */

require '../inc/claro_init_global.inc.php';
require_once $includePath . '/lib/admin.lib.inc.php';
require_once $includePath . '/lib/class.lib.php';
require_once $includePath . '/lib/user.lib.php';
include $includePath . '/conf/user_profile.conf.php'; // find this file to modify values.

// Security check
if ( ! $_uid ) claro_disp_auth_form();
if ( ! $is_platformAdmin ) claro_die(get_lang('NotAllowed'));

//bredcrump

$nameTools=get_lang('ClassRegistered');
$interbredcrump[]= array ('url' => $rootAdminWeb, 'name' => get_lang('ClassRegistered'));
/**#@+
 * DB tables definition
 * @var $tbl_mdb_names array table name for the central database
 */
$tbl_mdb_names = claro_sql_get_main_tbl();
$tbl_user        = $tbl_mdb_names['user'];
$tbl_course      = $tbl_mdb_names['course'];
$tbl_course_user = $tbl_mdb_names['rel_course_user'];
$tbl_class       = $tbl_mdb_names['user_category'];
$tbl_class_user  = $tbl_mdb_names['user_rel_profile_category'];
/**#@-*/

//find info about the class

$sqlclass = "SELECT *
             FROM `" . $tbl_class . "`
             WHERE `id`='". (int) $_SESSION['admin_user_class_id'] . "'";
list($classinfo) = claro_sql_query_fetch_all($sqlclass);

//------------------------------------
// Execute COMMAND section
//------------------------------------

if ( isset($_REQUEST['cmd']) ) $cmd = $_REQUEST['cmd'];
else                           $cmd = null;

if (isset($cmd) && $is_platformAdmin)
{
    if ($cmd == 'exReg')
    {
        $resultLog = register_class_to_course($_REQUEST['class'], $_REQUEST['course']);
        $outputResultLog = '';

        if ( isset($resultLog['OK']) && is_array($resultLog['OK']) )
        {
            foreach($resultLog['OK'] as $userSubscribed)
            {
                $outputResultLog .= '[<font color="green">OK</font>] ' . sprintf(get_lang('_p_s_s_has_been_sucessfully_registered_to_the_course_p_name_firstname'),$userSubscribed['prenom'], $userSubscribed['nom']) . '<br />';
            }
        }

        if ( isset($resultLog['KO']) && is_array($resultLog['KO']) )
        {
            foreach($resultLog['KO'] as $userSubscribedKo)
            {
                $outputResultLog .= '[<font color="red">KO</font>] ' . sprintf(get_lang('_p_s_s_has_not_been_sucessfully_registered_to_the_course_p_name_firstname'), $userSubscribedKo['prenom'], $userSubscribedKo['nom']).'<br />';
            }
        }
    }

}

//------------------------------------
// DISPLAY
//------------------------------------

include $includePath . '/claro_init_header.inc.php';
// Display tool title

echo claro_disp_tool_title(get_lang('ClassRegistered') . ' : ' . $classinfo['name']);

//Display Forms or dialog box(if needed)

// display log
if ( !empty($outputResultLog) )
{
    $dialogBox = $outputResultLog;
}

if ( !empty($dialogBox) )
{
    echo claro_disp_message_box($dialogBox);
}

// display TOOL links :

echo '<p><a class="claroCmd" href="index.php">' . get_lang('BackToAdmin') . '</a> | ';
echo '<a class="claroCmd" href="' . 'admin_class_user.php?class=' . $classinfo['id'] . '">' . get_lang('BackToClassMembers') . '</a> | ';
echo '<a class="claroCmd" href="' . $clarolineRepositoryWeb . 'auth/courses.php?cmd=rqReg&amp;fromAdmin=class' . '">' . get_lang('ClassRegisterWholeClassAgain') . '</a></p>';

// display footer

include $includePath . '/claro_init_footer.inc.php';
?>