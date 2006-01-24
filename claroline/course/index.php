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

if ( isset($_REQUEST['cid']) )
{
    $cidReq = $_REQUEST['cid']; 

    require '../inc/claro_init_global.inc.php';

    include $includePath . '/lib/course_home.lib.php';
    include $includePath . '/conf/rss.conf.php';

    if ( ! $is_courseAllowed ) claro_disp_auth_form();

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

    /**
     * TOOL LIST
     */

    $is_allowedToEdit = claro_is_allowed_to_edit();
    $disp_edit_command = $is_allowedToEdit;

    if     ($is_platformAdmin) $reqAccessLevel = 'PLATFORM_ADMIN' ;
    elseif ($is_courseAdmin  ) $reqAccessLevel = 'COURSE_ADMIN'   ;
    elseif ($is_courseTutor  ) $reqAccessLevel = 'COURSE_TUTOR'   ;
    elseif ($is_groupTutor   ) $reqAccessLevel = 'GROUP_TUTOR'    ;
    elseif ($is_groupMember  ) $reqAccessLevel = 'GROUP_MEMBER'   ;
    elseif ($is_courseMember ) $reqAccessLevel = 'COURSE_MEMBER'  ;
    elseif ($_uid            ) $reqAccessLevel = 'PLATFORM_MEMBER';
    else                       $reqAccessLevel = 'ALL';

    $toolList = claro_get_course_tool_list($_cid, $reqAccessLevel);

    // get tool id where new events have been recorded since last login

    if (isset($_uid))
    {
        $date = $claro_notifier->get_notification_date($_uid);
        $modified_tools = $claro_notifier->get_notified_tools($_cid, $date, $_uid);
    }
    else $modified_tools = array();

    ?>
    <table border="0" cellspacing="10" cellpadding="10" width="100%">
    <tr>
    <td valign="top" style="border-right: gray solid 1px;" width="220">
    <?php

    foreach($toolList as $thisTool)
    {
        $toolName = $thisTool['name'];
        $url      = trim($thisTool['url']);
        $icon     = $imgRepositoryWeb . $thisTool['icon'];

        if ($accessLevelList[$thisTool['access']] > $accessLevelList['ALL'])
        {
            $style = 'invisible ';
        }
        else
        {
            $style = '';
        }

        // see if tool name must be displayed as "containing new item" or not

        if (isset($_uid) && in_array($thisTool['id'], $modified_tools))
        {
            $classItem = " hot";
        }
        else // otherwise just display its name normally
        {
            $classItem = "";
        }

            //deal with specific case of group tool

        if (isset($_uid) && ($thisTool['label']=="CLGRP___"))
        {
            // we must notify if there is at least one group containing notification

            $groups = $claro_notifier->get_notified_groups($_cid, $date);
            $classItem = ( ! empty($groups) ) ? ' hot' : '';
        }

        if ( ! empty($url) )
        {
            echo ' <a class="' . $style . 'item'.$classItem.'" href="' . $url . '">'
            .    '<img src="' . $icon . '" alt="">'
            .    $toolName
            .    '</a>'
            .    '<br />' . "\n"
            ;
        }
        else
        {
            echo '<span ' . $style . '>'
            .    '<img src="' . $icon . '" alt="">'
            .    $toolName
            .    '</span><br />' . "\n"
            ;
        }
    }

    if ($disp_edit_command)
    {
        /*----------------------------------------------------------------------------
        COURSE ADMINISTRATION SECTION
        ----------------------------------------------------------------------------*/

        echo '<p>' . "\n"
        .    '<a class="claroCmd" href="' . $clarolineRepositoryWeb . 'course/tools.php">'
        .    '<img src="' . $imgRepositoryWeb . 'edit.gif" alt=""> '
        .    get_lang('EditToolList')
        .    '</a><br />' . "\n"
        .    '<a class="claroCmd" href="' . $toolRepository . 'course/settings.php">'
        .    '<img src="' . $imgRepositoryWeb . 'settings.gif" alt=""> '
        .    get_lang('Course settings')
        .    '</a><br />' . "\n"
        .    '<a class="claroCmd" href="' . $toolRepository . 'tracking/courseLog.php">'
        .    '<img src="' . $imgRepositoryWeb . 'statistics.gif" alt=""> '
        .    get_lang('Statistics')
        .    '</a>' . "\n"
        .    '</p>'
        ;
    }

    if ( isset($_uid) ) echo '<br /><small><span class="item hot"> '. get_lang('NewLegend') . '</span></small>';

    ?>
    </td>
    <td width="20">
    &nbsp;
    </td>
    <td valign="top">
    <?php




    /*----------------------------------------------------------------------------
    INTRODUCTION TEXT SECTION
    ----------------------------------------------------------------------------*/

    // the module id for course_home equal -1 (course_home is not a tool in tool_list)
    $moduleId = -1;
    $helpAddIntroText=get_lang('IntroCourse');
    include($includePath . '/introductionSection.inc.php');
    ?>
    </td>
    </tr>
    </table>


    <?php
    include $includePath . '/claro_init_footer.inc.php';
}
else
{


}

?>
