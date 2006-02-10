<?php # $Id$
/**
 * CLAROLINE
 *
 * @version 1.8 $Revision$
 *
 * @copyright (c) 2001-2006 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * old version : http://cvs.claroline.net/cgi-bin/viewcvs.cgi/claroline/claroline/course_info/delete_course.php
 *
 * @package CLCRS
 *
 * @author Claro Team <cvs@claroline.net>
 */
$_tid='deletecourse';


define('DISP_CONFIRM_DELETE', __LINE__);
define('DISP_DELETE_RESULT',  __LINE__);
define('DISP_NOT_ALLOWED',    __LINE__);

require '../inc/claro_init_global.inc.php';
if ( ! $_cid || ! $_uid) claro_disp_auth_form(true);

//check user right
$isAllowedToDelete = $is_courseAdmin;

if ( ! $isAllowedToDelete ) claro_die(get_lang('Not allowed'));

require_once $includePath . '/lib/fileManage.lib.php';
require_once $includePath . '/lib/admin.lib.inc.php';

// in case of admin access (from admin tool) to the script, we must determine which course we are working with
$addToURL = '';
if ( isset($cidToEdit) && ($is_platformAdmin) )
{
    $current_cid       = $cidToEdit;
    $currentCourseId   = $cidToEdit;
    $cidReq            = $cidToEdit;
    $isAllowedToDelete = true;
    $addToURL          = '&amp;cidToEdit=' . $cidToEdit;
    $addToURL         .= '&amp;cfrom=' . $cfrom;
}
else
{
    $current_cid = $_course['sysCode'];
}

//find needed info in db

$course_to_delete = claro_get_course_data($current_cid);
$currentCourseCode = $course_to_delete['officialCode'];
$currentCourseName = $course_to_delete['name'];

$nameTools = get_lang('Delete the whole course website');
$interbredcrump[] = array('url' => 'settings.php?' . $addToURL, 'name' => get_lang('Course settings'));

if ( isset($_REQUEST['delete']) && $_REQUEST['delete'] )
{
    // DO DELETE
    delete_course($current_cid);
    event_default( 'DELETION COURSE' , array ('courseName' => addslashes($currentCourseName), 'uid' => $_uid));

    $display = DISP_DELETE_RESULT;
} // end if $delete
else
{
    $display = DISP_CONFIRM_DELETE;
}        // end else if $delete

include $includePath . '/claro_init_header.inc.php';
echo claro_disp_tool_title($nameTools);

switch ($display)
{
    case DISP_DELETE_RESULT :
    {
        $cmd_menu[] = '<a href="'.$urlAppend.'">'.get_lang('Back to Homepage of %site_name',array('%site_name'=>$siteName)).'</a>';

        if ( isset($cidToEdit) ) //we can suppose that script is accessed from admin tool in this case
        {
            $cmd_menu[] = '<a href="'.$urlAppend.'admin/index.php">'.get_lang('Back to administration page').'</a>';
        }

        echo '<p>'
        .    get_lang('Course "%course_name" (%course_code) has been deleted' , array( '%course_name' => $currentCourseName,
                                                                                       '%course_code' => $currentCourseCode))
        .    '</p>'
        .    '<p>'
        .    claro_html::menu_horizontal($cmd_menu)
        .    '</p>'
        ;
    }   break;

    // ASK DELETE CONFIRMATION TO THE USER
    case DISP_CONFIRM_DELETE :
    {
        $url_delete = 'delete.php?cmd=exDelete'.$addToURL;
        $url_cancel = 'settings.php?'.$addToURL ;

        echo '<p>'
        .    '<font color="#CC0000">'
        .    get_lang('Deleting this course will permanently delete all its documents and unenroll all its students.')
        .    get_lang('Do you really want to delete the course "%course_name" ( %course_code ) ?', array('%course_name' => $currentCourseName,
                                                                                                         '%course_code' => $currentCourseCode ))
        .    '</font>'
        .    '</p>'
        .    '<p>'
        .    '<font color="#CC0000">'
        .    '<a href="'.$url_delete.'">'.get_lang('Yes').'</a>'
        .    '&nbsp;|&nbsp;'
        .    '<a href="'.$url_cancel.'">'.get_lang('No').'</a>'
        .    '</font>'
        .    '</p>'
        ;
    }   break;
}

include $includePath . '/claro_init_footer.inc.php';
?>
