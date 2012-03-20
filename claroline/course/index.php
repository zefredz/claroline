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

require_once get_path('incRepositorySys') . '/lib/course_home.lib.php';
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

        $url = htmlspecialchars( Url::Contextualize( get_module_url($thisTool['label']) . '/' . $thisTool['url'] ) );
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
        $toolLinkList[] = '<a '.$htmlId.'class="' . $style . 'item' . $classItem . '" href="' . $url . '">'
        . '<img class="clItemTool"  src="' . $icon . '" alt="" />&nbsp;'
        . $toolName
        . '</a>' . "\n"
        ;
    }
    else
    {
        $toolLinkList[] = '<span ' . $style . '>'
        . '<img class="clItemTool" src="' . $icon . '" alt="" />&nbsp;'
        . $toolName
        . '</span>' . "\n"
        ;
    }
}

$courseManageToolLinkList[] = '<a class="claroCmd" href="' . htmlspecialchars(Url::Contextualize( get_path('clarolineRepositoryWeb')  . 'course/tools.php' )) . '">'
. '<img src="' . get_icon_url('edit') . '" alt="" /> '
. get_lang('Edit Tool list')
. '</a>'
;
$courseManageToolLinkList[] = '<a class="claroCmd" href="' . htmlspecialchars(Url::Contextualize( $toolRepository . 'course/settings.php' )) . '">'
. '<img src="' . get_icon_url('settings') . '" alt="" /> '
. get_lang('Course settings')
. '</a>'
;

if( get_conf('is_trackingEnabled') )
{
    $courseManageToolLinkList[] =  '<a class="claroCmd" href="' . htmlspecialchars(Url::Contextualize( $toolRepository . 'tracking/courseReport.php' )) . '">'
    . '<img src="' . get_icon_url('statistics') . '" alt="" /> '
    . get_lang('Statistics')
    . '</a>'
    ;
}

//@since Claroline 1.9.10
$extraManageToolList = get_course_manage_module_list(true);

if ( $extraManageToolList )
{
    foreach ( $extraManageToolList as $extraManageTool )
    {
        $courseManageToolLinkList[] =  '<a class="claroCmd" href="' . htmlspecialchars(Url::Contextualize( get_module_entry_url($extraManageTool['label'] ) ) ) . '">'
        . '<img src="' . get_module_icon_url($extraManageTool['label'], $extraManageTool['icon'], 'settings') . '" alt="" /> '
        . get_lang($extraManageTool['name'])
        . '</a>'
        ;
    }
}

// Display header

$template = new CoreTemplate('course_index.tpl.php');
$template->assign('toolLinkList', $toolLinkList);
$template->assign('courseManageToolLinkList', $courseManageToolLinkList);

$claroline->display->body->setContent($template->render());


echo $claroline->display->render();
