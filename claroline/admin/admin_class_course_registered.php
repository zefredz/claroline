<?php // $Id$
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
 * @author Guillaume Lederer <lederer@cerdecam.be>
 * @author Christophe Gesché <moosh@claroline.net>
 */

require '../inc/claro_init_global.inc.php';

require_once $includePath . '/lib/admin.lib.inc.php';
require_once $includePath . '/lib/class.lib.php';
require_once $includePath . '/lib/user.lib.php';

include $includePath . '/conf/user_profile.conf.php'; // find this file to modify values.

// Security check
if ( ! $_uid ) claro_disp_auth_form();
if ( ! $is_platformAdmin ) claro_die(get_lang('Not allowed'));

//bredcrump

$nameTools=get_lang('Class registered');
$interbredcrump[]= array ('url' => $rootAdminWeb, 'name' => get_lang('Class registered'));

if ( isset($_REQUEST['cmd']) ) $cmd = $_REQUEST['cmd'];
else                           $cmd = null;



//------------------------------------
// Execute COMMAND section
//------------------------------------
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
                $outputResultLog .= '[<font color="red">KO</font>] ' . sprintf(get_lang('_p_s_s_has_not_been_sucessfully_registered_to_the_course_p_name_firstname'), $userSubscribedKo['prenom'], $userSubscribedKo['nom']) . '<br />';
            }
        }
    }

}

/**
 * PREPARE DISPLAY
 */

$classinfo =  get_class_info_by_id($_SESSION['admin_user_class_id']);

if ( !empty($outputResultLog) ) $dialogBox = $outputResultLog;
$cmd_menu[] =  '<p><a class="claroCmd" href="index.php">' . get_lang('BackToAdmin') . '</a>';
$cmd_menu[] =  '<a class="claroCmd" href="' . 'admin_class_user.php?class=' . $classinfo['id'] . '">' . get_lang('BackToClassMembers') . '</a>';
$cmd_menu[] =  '<a class="claroCmd" href="' . $clarolineRepositoryWeb . 'auth/courses.php?cmd=rqReg&amp;fromAdmin=class' . '">' . get_lang('Register class for course') . '</a></p>';


/**
 * DISPLAY
 */
include $includePath . '/claro_init_header.inc.php';

echo claro_disp_tool_title(get_lang('Class registered') . ' : ' . $classinfo['name']);

if ( !empty($dialogBox) ) echo claro_html::message_box($dialogBox);

echo claro_html::menu_horizontal($cmd_menu);

include $includePath . '/claro_init_footer.inc.php';

/**
 * get info about a class
 *
 * @param integer $class_id
 * @return array (id, name)
 */
function get_class_info_by_id($class_id)
{

    $tbl_mdb_names = claro_sql_get_main_tbl();
    $tbl_class       = $tbl_mdb_names['user_category'];
    $sqlclass = "SELECT id,
                        name
                 FROM `" . $tbl_class . "`
                 WHERE `id`='". (int) $class_id . "'";
    return claro_sql_query_get_single_row($sqlclass);
}


?>