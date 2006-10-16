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

include $includePath . '/lib/course_home.lib.php';
include claro_get_conf_repository() . 'rss.conf.php';

require_once $clarolineRepositorySys . '/linker/linker.inc.php';


if ( !$_cid || !$is_courseAllowed ) claro_disp_auth_form(true);

$toolRepository = $clarolineRepositoryWeb;
claro_set_display_mode_available(TRUE);

// Add feed RSS in header
if ( get_conf('enableRssInCourse') )
{
    $htmlHeadXtra[] = '<link rel="alternate" type="application/rss+xml" title="' . htmlspecialchars($_course['name'] . ' - ' . $siteName) . '"'
            .' href="' . get_conf('rootWeb') . 'claroline/rss/?cidReq=' . $_cid . '" />';
}

/*
 * Load javascript for management of the linker into the main text zone
 * see 'introductionSection.inc.php' file included later in the script
 */

if (      isset( $_REQUEST['introCmd'] )
     && ( $_REQUEST['introCmd']== 'rqEd' || $_REQUEST['introCmd'] == 'rqAdd') )
{
    $introId = isset ($_REQUEST['introId']) ? $_REQUEST['introId'] : null;
    linker_init_session();
    if ($jpspanEnabled)
    {
        linker_set_local_crl( isset ($_REQUEST['introId']), 'CLINTRO_' );
    }
    linker_html_head_xtra();
}

/*
* Tracking - Count only one time by course and by session
*/
// following instructions are used to prevent statistics to be recorded more than needed
// for course access
// check if the user as already visited this course during this session (
if ( ! isset($_SESSION['tracking']['coursesAlreadyVisited'][$_cid]))
{
    event_access_course();
    $_SESSION['tracking']['coursesAlreadyVisited'][$_cid] = 1;
}
// for tool access
// unset the label of the last visited tool
unset($_SESSION['tracking']['lastUsedTool']);
// end of tracking

/*
* Language initialisation of the tool names
*/

$toolNameList = claro_get_tool_name_list();

// get tool id where new events have been recorded since last login

if (isset($_uid))
{
    $date = $claro_notifier->get_notification_date($_uid);
    $modified_tools = $claro_notifier->get_notified_tools($_cid, $date, $_uid);
}
else $modified_tools = array();

/**
 * TOOL LIST
 */

$is_allowedToEdit = claro_is_allowed_to_edit();
$disp_edit_command = $is_allowedToEdit;

$toolList = claro_get_course_tool_list($_cid,$_profileId,true);
$toolLinkList = array();

foreach ($toolList as $thisTool)
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

        $url = get_module_url($thisTool['label']) . '/' . $thisTool['url'];
        $icon = get_module_url($thisTool['label']) .'/'. $thisTool['icon'];
        $removableTool = false;
    }
    else   // external tool added by course manager
    {
        if ( ! empty($thisTool['external_name'])) $toolName = $thisTool['external_name'];
        else $toolName = '<i>no name</i>';
        $url           = trim($thisTool['url']);
        $icon = $imgRepositoryWeb .'/tool.gif';
        $removableTool = true;
    }

    $style = !$thisTool['visibility']? 'invisible ' : '';
    $classItem = (in_array($thisTool['id'], $modified_tools)) ? ' hot' : '';

    //deal with specific case of group tool
    if (is_null('_uid') && ('CLGRP___' == $thisTool['label']))
    {
        // we must notify if there is at least one group containing notification
        $groups = $claro_notifier->get_notified_groups($_cid, $date);
        $classItem = ( ! empty($groups) ) ? ' hot ' : '';
    }

    if ( ! empty($url) )
    {
        $toolLinkList[] = '<a class="' . $style . 'item' . $classItem . '" href="' . $url . '">'
        .                 '<img src="' . $icon . '" alt="">&nbsp;'
        .                 $toolName
        .                 '</a>' . "\n"
        ;
    }
    else
    {
        $toolLinkList[] = '<span ' . $style . '>'
        .                 '<img src="' . $icon . '" alt="">&nbsp;'
        .                 $toolName
        .                 '</span>' . "\n"
        ;
    }
}

    $courseManageToolLinkList[] = '<a class="claroCmd" href="' . $clarolineRepositoryWeb . 'course/tools.php">'
    .                             '<img src="' . $imgRepositoryWeb . 'edit.gif" alt=""> '
    .                             get_lang('Edit Tool list')
    .                             '</a>'
    ;
    $courseManageToolLinkList[] = '<a class="claroCmd" href="' . $toolRepository . 'course/settings.php">'
    .                             '<img src="' . $imgRepositoryWeb . 'settings.gif" alt=""> '
    .                             get_lang('Course settings')
    .                             '</a>'
    ;

    $courseManageToolLinkList[] =  '<a class="claroCmd" href="' . $toolRepository . 'tracking/courseLog.php">'
    .                             '<img src="' . $imgRepositoryWeb . 'statistics.gif" alt=""> '
    .                             get_lang('Statistics')
    .                             '</a>'
    ;

// Display header

include($includePath . '/claro_init_header.inc.php');

echo '<table border="0" cellspacing="10" cellpadding="10" width="100%">' . "\n"
.    '<tr>' . "\n"
.    '<td valign="top" style="border-right: gray solid 1px;" width="220">' . "\n"
.    claro_html_menu_vertical_br($toolLinkList, array('id'=>'commonToolList'))
.    '<br />'
;

if ($disp_edit_command) echo claro_html_menu_vertical_br($courseManageToolLinkList,  array('id'=>'courseManageToolList'));

if ( !is_null(get_init('_uid')) && !empty($modified_tools) ) echo '<br /><small><span class="item hot"> '. get_lang('denotes new items') . '</span></small>';

echo '</td>' . "\n"
.    '<td width="20">' . "\n"
.    '&nbsp;' . "\n"
.    '</td>' . "\n"
.    '<td valign="top">' . "\n"
;

/*----------------------------------------------------------------------------
INTRODUCTION TEXT SECTION
----------------------------------------------------------------------------*/
// the module id for course_home equal -1 (course_home is not a tool in tool_list)

$moduleId = -1;
$helpAddIntroText = get_block('blockIntroCourse');
include($includePath . '/introductionSection.inc.php');

echo '</td>' . "\n"
.    '</tr>' . "\n"
.    '</table>' . "\n"
;

include $includePath . '/claro_init_footer.inc.php';
?>