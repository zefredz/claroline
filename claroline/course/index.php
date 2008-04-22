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

include get_path('incRepositorySys') . '/lib/course_home.lib.php';
include claro_get_conf_repository() . 'rss.conf.php';

require_once get_path('clarolineRepositorySys') . '/linker/linker.inc.php';

if ( !claro_is_in_a_course()  || !claro_is_course_allowed() ) claro_disp_auth_form(true);

$toolRepository = get_path('clarolineRepositoryWeb');
claro_set_display_mode_available(TRUE);

/*
 * Load javascript for management of the linker into the main text zone
 * see 'introductionSection.inc.php' file included later in the script
 */

if (      isset( $_REQUEST['introCmd'] )
     && ( $_REQUEST['introCmd']== 'rqEd' || $_REQUEST['introCmd'] == 'rqAdd') )
{
    $introId = isset ($_REQUEST['introId']) ? $_REQUEST['introId'] : null;
    linker_init_session();
    if (claro_is_jpspan_enabled())
    {
        linker_set_local_crl( isset ($_REQUEST['introId']), 'CLINTRO_' );
    }
    linker_html_head_xtra();
}

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
else $modified_tools = array();

/**
 * TOOL LIST
 */

$is_allowedToEdit = claro_is_allowed_to_edit();
$disp_edit_command = $is_allowedToEdit;

$toolList = claro_get_course_tool_list(claro_get_current_course_id(),$_profileId,true);
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
        $icon = get_path('imgRepositoryWeb') . '/tool.gif';
        $removableTool = true;
    }

    $style = !$thisTool['visibility']? 'invisible ' : '';
    $classItem = (in_array($thisTool['id'], $modified_tools)) ? ' hot' : '';

    //deal with specific case of group tool

    // TODO : get_notified_groups can know itself if $_uid is set
    if ( claro_is_user_authenticated() && ('CLGRP' == $thisTool['label']))
    {
        // we must notify if there is at least one group containing notification
        $groups = $claro_notifier->get_notified_groups(claro_get_current_course_id(), $date);
        $classItem = ( ! empty($groups) ) ? ' hot ' : '';
    }

    if ( ! empty($url) )
    {
        $toolLinkList[] = '<a id="' . $thisTool['label'] . '" class="' . $style . 'item' . $classItem . '" href="' . $url . '">'
        .                 '<img class="clItemTool"  src="' . $icon . '" alt="" />&nbsp;'
        .                 $toolName
        .                 '</a>' . "\n"
        ;
    }
    else
    {
        $toolLinkList[] = '<span ' . $style . '>'
        .                 '<img class="clItemTool" src="' . $icon . '" alt="" />&nbsp;'
        .                 $toolName
        .                 '</span>' . "\n"
        ;
    }
}

    $courseManageToolLinkList[] = '<a class="claroCmd" href="' . get_path('clarolineRepositoryWeb')  . 'course/tools.php' . claro_url_relay_context('?') . '">'
    .                             '<img src="' . get_path('imgRepositoryWeb') . 'edit.gif" alt="" /> '
    .                             get_lang('Edit Tool list')
    .                             '</a>'
    ;
    $courseManageToolLinkList[] = '<a class="claroCmd" href="' . $toolRepository . 'course/settings.php' . claro_url_relay_context('?') . '">'
    .                             '<img src="' . get_path('imgRepositoryWeb') . 'settings.gif" alt="" /> '
    .                             get_lang('Course settings')
    .                             '</a>'
    ;

    if( get_conf('is_trackingEnabled') )
    {
        $courseManageToolLinkList[] =  '<a class="claroCmd" href="' . $toolRepository . 'tracking/courseLog.php' . claro_url_relay_context('?') . '">'
        .                             '<img src="' . get_path('imgRepositoryWeb') . 'statistics.gif" alt="" /> '
        .                             get_lang('Statistics')
        .                             '</a>'
        ;
    }

// Display header

include(get_path('incRepositorySys') . '/claro_init_header.inc.php');

echo '<table border="0" cellspacing="10" cellpadding="10" width="100%">' . "\n"
.    '<tr>' . "\n"
.    '<td valign="top" style="border-right: gray solid 1px;" width="220">' . "\n"
.    claro_html_menu_vertical_br($toolLinkList, array('id'=>'commonToolList'))
.    '<br />'
;

if ($disp_edit_command) echo claro_html_menu_vertical_br($courseManageToolLinkList,  array('id'=>'courseManageToolList'));

if ( claro_is_user_authenticated() && !empty($modified_tools) )
{
    echo '<br /><small><span class="item hot"> '
    .    get_lang('denotes new items')
    .    '</span></small>'
    ;
}

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
include(get_path('incRepositorySys') . '/introductionSection.inc.php');

echo '</td>' . "\n"
.    '</tr>' . "\n"
.    '</table>' . "\n"
;

include get_path('incRepositorySys') . '/claro_init_footer.inc.php';
?>