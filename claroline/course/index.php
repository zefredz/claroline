<?php // $Id$
/**
 * CLAROLINE
 *
 * @version 1.9 $Revision$
 *
 * @copyright (c) 2001-2007 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * old version : http://cvs.claroline.net/cgi-bin/viewcvs.cgi/claroline/claroline/course_home/course_home.php
 *
 * @package CLHOME
 *
 * @author Claro Team <cvs@claroline.net>
 */

// If user is here, it means he isn't neither in specific group space
// nor a specific course tool now. So it's careful to to reset the group
// and tool settings

$gidReset = TRUE;
$tidReset = TRUE;

if ( isset($_REQUEST['cid']) ) $cidReq = $_REQUEST['cid'];

require '../inc/claro_init_global.inc.php';

include claro_get_conf_repository() . 'rss.conf.php';


if ( !claro_is_in_a_course()  || !claro_is_course_allowed() ) claro_disp_auth_form(true);

$toolRepository = get_path('clarolineRepositoryWeb');
claro_set_display_mode_available(TRUE);

/*
 * Language initialisation of the tool names
 */

$toolNameList = claro_get_tool_name_list();

// get tool id where new events have been recorded since last login

if (claro_is_user_authenticated())
{
    $date = $claro_notifier->get_notification_date(claro_get_current_user_id());
    $modified_tools = $claro_notifier->get_notified_tools(claro_get_current_course_id(), $date, claro_get_current_user_id());
}
else
{
    $modified_tools = array();
}

/**
 * TOOL LIST
 */

$is_allowedToEdit = claro_is_allowed_to_edit();

$courseSource = get_source_course(rectrieve_id_from_code(claro_get_current_course_id()));

if (isset($courseSource['sysCode']))
{
   // call a session course
   $_SESSION['courseSessionCode'][$courseSource['sysCode']]= claro_get_current_course_id();
   $courseCode['session'] =  claro_get_current_course_id();
   $courseCode['source'] = $courseSource['sysCode'];
}
else 
{
    if (isset($_SESSION['courseSessionCode'][claro_get_current_course_id()]) )
    {
        // call a source course
        $courseCode['source'] = claro_get_current_course_id();
        $courseCode['session'] =  $_SESSION['courseSessionCode'][$courseCode['source']];
    }
    else $courseCode['standAlone'] = claro_get_current_course_id();
}

$toolLinkList = array(
    'source' => array(),
    'session' => array(),
	'standAlone' => array()
);

// generate toollists
foreach ($courseCode as $key => $course)
{   
    $toolListSource = claro_get_course_tool_list($course,$_profileId,true);
    $toolLinkListSource = array();
    
    foreach ($toolListSource as $thisTool)
    {
        // special case when display mode is student and tool invisible doesn't display it
        if ( ( claro_get_tool_view_mode() == 'STUDENT' ) && ! $thisTool['visibility']  )
        {
            continue;
        }
    
        if (isset($thisTool['label'])) // standart claroline tool or module of type tool
        {
            $thisToolName = $thisTool['name'];
            $toolName = get_lang($thisToolName);
    
            //trick to find how to build URL, must be IMPROVED
    
            $url = htmlspecialchars( get_module_url($thisTool['label']) . '/' . $thisTool['url'] . '?cidReset=true&cidReq=' .$course);
            $icon = get_module_url($thisTool['label']) .'/'. $thisTool['icon'];
            $htmlId = 'id="' . $thisTool['label'] . '"';
            $removableTool = false;
        }
        else   // external tool added by course manager
        {
            if ( ! empty($thisTool['external_name'])) $toolName = $thisTool['external_name'];
            else $toolName = '<i>no name</i>';
            $url = htmlspecialchars( trim($thisTool['url']) );
            $icon = get_icon_url('link');
            $htmlId = '';
            $removableTool = true;
        }
    
        $style = !$thisTool['visibility']? 'invisible ' : '';
        $classItem = (in_array($thisTool['id'], $modified_tools)) ? ' hot' : '';
        
        if ( ! empty($url) )
        {
            $toolLinkList[$key][] = '<a '.$htmlId.'class="' . $style . 'item' . $classItem . '" href="' . $url . '">'
            .                 '<img class="clItemTool"  src="' . $icon . '" alt="" />&nbsp;'
            .                 $toolName
            .                 '</a>' . "\n"
            ;
        }
        else
        {
            $toolLinkList[$key][] = '<span ' . $style . '>'
            .                 '<img class="clItemTool" src="' . $icon . '" alt="" />&nbsp;'
            .                 $toolName
            .                 '</span>' . "\n"
            ;
        }
    }       
}
    
// generate toolList for managment of the course
$courseManageToolLinkList[] = '<a class="claroCmd" href="' . htmlspecialchars(Url::Contextualize( get_path('clarolineRepositoryWeb')  . 'course/tools.php' )) . '">'
.                             '<img src="' . get_icon_url('edit') . '" alt="" /> '
.                             get_lang('Edit Tool list')
.                             '</a>'
;
$courseManageToolLinkList[] = '<a class="claroCmd" href="' . htmlspecialchars(Url::Contextualize( $toolRepository . 'course/settings.php' )) . '">'
.                             '<img src="' . get_icon_url('settings') . '" alt="" /> '
.                             get_lang('Course settings')
.                             '</a>'
;

if( get_conf('is_trackingEnabled') )
{
    $courseManageToolLinkList[] =  '<a class="claroCmd" href="' . htmlspecialchars(Url::Contextualize( $toolRepository . 'tracking/courseReport.php' )) . '">'
    .                             '<img src="' . get_icon_url('statistics') . '" alt="" /> '
    .                             get_lang('Statistics')
    .                             '</a>'
    ;
}

// Display header
$template = new CoreTemplate('course_index.tpl.php');
$template->assign('toolLinkListSource', $toolLinkList['source']);
$template->assign('toolLinkListSession', $toolLinkList['session']);
$template->assign('toolLinkListStandAlone', $toolLinkList['standAlone']);
$template->assign('courseManageToolLinkList', $courseManageToolLinkList);

$claroline->display->body->setContent($template->render());


echo $claroline->display->render();
?>