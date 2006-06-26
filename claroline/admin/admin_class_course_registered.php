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
 * @author Christophe Gesch� <moosh@claroline.net>
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

$cmd = isset($_REQUEST['cmd'])?$_REQUEST['cmd']:null;
$class_id = isset($_REQUEST['class_id'])?$_REQUEST['class_id']:0;
$course_id = isset($_REQUEST['course_id'])?$_REQUEST['course_id']:null;

//------------------------------------
// Execute COMMAND section
//------------------------------------

if (isset($cmd) && $is_platformAdmin)
{
    if ($cmd == 'exReg')
    {
        $resultLog = register_class_to_course($class_id,$course_id);
        $outputResultLog = '';

        if ( isset($resultLog['OK']) && is_array($resultLog['OK']) )
        {
            foreach($resultLog['OK'] as $thisUser)
            {
                $outputResultLog .= '[<font color="green">OK</font>] ' . get_lang('<i>%firstname %lastname</i> has been sucessfully registered to the course',array('%firstname'=>$thisUser['firstname'], '%lastname'=>$thisUser['lastname'])) . '<br />';
            }
        }

        if ( isset($resultLog['KO']) && is_array($resultLog['KO']) )
        {
            foreach($resultLog['KO'] as $thisUser)
            {
                $outputResultLog .= '[<font color="red">KO</font>] ' . get_lang('<i>%firstname %lastname</i> has not been sucessfully registered to the course',array('%firstname'=>$thisUser['firstname'], '%lastname'=>$thisUser['lastname'])) . '<br />';
            }
        }
    }

}

/**
 * PREPARE DISPLAY
 */

$classinfo = class_get_properties($class_id);

if ( !empty($outputResultLog) ) $dialogBox = $outputResultLog;
$cmd_menu[] =  '<p><a class="claroCmd" href="index.php">' . get_lang('Back to administration page') . '</a>';
$cmd_menu[] =  '<a class="claroCmd" href="' . 'admin_class_user.php?class_id=' . $classinfo['id'] . '">' . get_lang('Back to class members') . '</a>';
$cmd_menu[] =  '<a class="claroCmd" href="' . $clarolineRepositoryWeb . 'auth/courses.php?cmd=rqReg&amp;fromAdmin=class' . '">' . get_lang('Register class for course') . '</a></p>';

/**
 * DISPLAY
 */
include $includePath . '/claro_init_header.inc.php';

echo claro_html_tool_title(get_lang('Class registered') . ' : ' . $classinfo['name']);

if ( !empty($dialogBox) ) echo claro_html_message_box($dialogBox);

echo claro_html_menu_horizontal($cmd_menu);

include $includePath . '/claro_init_footer.inc.php';

?>
