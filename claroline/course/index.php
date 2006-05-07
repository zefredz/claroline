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
include $includePath . '/conf/rss.conf.php';

if ( !$_cid || !$is_courseAllowed ) claro_disp_auth_form(true);

$toolRepository = $clarolineRepositoryWeb;
claro_set_display_mode_available(TRUE);

// Add feed RSS in header
if ( get_conf('enable_rss_in_course') )
{
    $htmlHeadXtra[] = '<link rel="alternate" type="application/rss+xml" title="' . htmlspecialchars($_course['name'] . ' - ' . $siteName) . '"'
            .' href="' . $rootWeb . 'claroline/rss/?cidReq=' . $_cid . '" />';
}

// Display header
include($includePath . '/claro_init_header.inc.php');

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
/*
* Initialisation for the access level types
*/

$accessLevelList = array( 'ALL'            => 0
                        , 'COURSE_MEMBER'  => 1
                        , 'GROUP_TUTOR'    => 2
                        , 'COURSE_ADMIN'   => 3
                        , 'PLATFORM_ADMIN' => 4
                        );
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


if     ($is_platformAdmin && $is_allowedToEdit) $reqAccessLevel   = 'PLATFORM_ADMIN';
elseif ($is_allowedToEdit                     ) $reqAccessLevel   = 'COURSE_ADMIN';
else                                            $reqAccessLevel   = 'ALL';

$toolList = claro_get_course_tool_list($_cid, $reqAccessLevel, true);

foreach($toolList as $thisTool)
{
    $toolName = $thisTool['name'];
    $url      = trim($thisTool['url']);
    $icon     = $imgRepositoryWeb . $thisTool['icon'];

    $style = ($accessLevelList[$thisTool['access']] > $accessLevelList['ALL']) ? 'invisible ' : '';
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
        .                 '<img src="' . $icon . '" alt="">'
        .                 $toolName
        .                 '</a>' . "\n"
        ;
    }
    else
    {
        $toolLinkList[] = '<span ' . $style . '>'
        .                 '<img src="' . $icon . '" alt="">'
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


echo '<table border="0" cellspacing="10" cellpadding="10" width="100%">' . "\n"
.    '<tr>' . "\n"
.    '<td valign="top" style="border-right: gray solid 1px;" width="220">' . "\n"
.    claro_html_menu_vertical_br($toolLinkList, array('id'=>'commonToolList'))
.    '<br />'
;

if ($disp_edit_command) echo claro_html_menu_vertical_br($courseManageToolLinkList,  array('id'=>'adminToolList'));

if ( !is_null(get_init('_uid') )) echo '<br /><small><span class="item hot"> '. get_lang('denotes new items') . '</span></small>';

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
$helpAddIntroText=get_block('blockIntroCourse');
include($includePath . '/introductionSection.inc.php');

echo '</td>' . "\n"
.    '</tr>' . "\n"
.    '</table>' . "\n"
;

include $includePath . '/claro_init_footer.inc.php';
?>