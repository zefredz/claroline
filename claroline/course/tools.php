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
 * @package CLHOME
 *
 * old version : http://cvs.claroline.net/cgi-bin/viewcvs.cgi/claroline/claroline/course_home/course_home_edit.php
 *
 * @author Claro Team <cvs@claroline.net>
 */

$gidReset = true; // If user is here. It means he isn't in any group space now.
                  // So it's careful to to reset the group setting

require '../inc/claro_init_global.inc.php';

$nameTools  = get_lang('Edit Tool list');
$noPHP_SELF = TRUE;

if ( ! claro_is_in_a_course() || ! claro_is_user_authenticated() ) claro_disp_auth_form(true);

if ( claro_is_course_manager() ) $is_allowedToEdit = TRUE;
else                   claro_die(get_lang('Not allowed'));

// Prepare menu for claro_html_tabs_bar
$sectionList = array(
    'toolRights' => get_lang('Manage tool access rights'),
    'extLinks' => get_lang('Manage external links'),
    'toolList' => get_lang('Add or remove tools')
);

$currentSection = isset( $_REQUEST['section'] )
    && in_array( $_REQUEST['section'], array_keys($sectionList) )
    ? $_REQUEST['section']
    : 'toolRights'
    ;

$htmlHeadXtra[] =
'<script type="text/javascript">
function confirmation (name)
{
    if (confirm(\''.clean_str_for_javascript(get_lang('Are you sure to delete')).'\'+ name + \' ?\'))
        {return true;}
    else
        {return false;}
}
</script>';

$toolRepository = '../';

$currentCourseRepository = claro_get_course_path();
$dialogBox = new DialogBox();

// Library
require_once get_path('incRepositorySys') . '/lib/course_home.lib.php';
require_once get_path('incRepositorySys') . '/lib/right/courseProfileToolAction.class.php';
require_once get_path('incRepositorySys') . '/lib/right/profileToolRightHtml.class.php';
require_once get_path('incRepositorySys') . '/lib/module/manage.lib.php';

/*
 * Language initialisation of the tool names
 */

$toolNameList = claro_get_tool_name_list();

/*============================================================================
 COMMAND SECTION
============================================================================*/

$cmd = isset($_REQUEST['cmd'])?$_REQUEST['cmd']:null;
$tool_id = isset($_REQUEST['tool_id'])?(int)$_REQUEST['tool_id']:null;
$profile_id = isset($_REQUEST['profile_id'])?$_REQUEST['profile_id']:null;
$right_value = isset($_REQUEST['right_value'])?$_REQUEST['right_value']:null;
$toolLabel = isset($_REQUEST['toolLabel'])?$_REQUEST['toolLabel']:null;

$externalLinkName = isset($_REQUEST['toolName'])?$_REQUEST['toolName']:null;
$externalLinkUrl = isset($_REQUEST['toolUrl'])?$_REQUEST['toolUrl']:null;

/*----------------------------------------------------------------------------
 Manage Profile
----------------------------------------------------------------------------*/

if ( !empty($profile_id) )
{
    // load profile
    $profile = new RightProfile();

    if ( $profile->load($profile_id) )
    {
        // load profile tool right
        $courseProfileRight = new RightCourseProfileToolRight();
        $courseProfileRight->setCourseId(claro_get_current_course_id());
        $courseProfileRight->load($profile);

        if ( ! $profile->isLocked() )
        {
            if ( $cmd == 'set_right' && !empty($tool_id) )
            {
                $courseProfileRight->setToolRight($tool_id,$right_value);
                $courseProfileRight->save();
            }
        }
    }
    else
    {
        $profile_id = null;
    }
}

/*----------------------------------------------------------------------------
 SET THE TOOL ACCESSES
----------------------------------------------------------------------------*/

if ( $cmd == 'exVisible' || $cmd == 'exInvisible' )
{
    if ( $cmd == 'exVisible' )
    {
        set_course_tool_visibility($tool_id,true);
    }
    else
    {
        set_course_tool_visibility($tool_id,false);
    }

    // notify that tool list has been changed

    $eventNotifier->notifyCourseEvent('toollist_changed', claro_get_current_course_id(), '0', '0', '0', '0');
}

/*----------------------------------------------------------------------------
 ADD AN EXTERNAL TOOL
----------------------------------------------------------------------------*/

if ( $cmd == 'exAdd' )
{
    if ( ! empty($externalLinkName) && ! empty($externalLinkUrl))
    {
        if( insert_local_course_tool($externalLinkName, $externalLinkUrl) !== FALSE )
        {
            // notify that tool list has been changed
            $eventNotifier->notifyCourseEvent('toollist_changed', claro_get_current_course_id(), "0", "0", "0", '0');

            $dialogBox->success( get_lang('External Tool added') );

            $cidReset = TRUE;
            $cidReq   = claro_get_current_course_id();

            include get_path('incRepositorySys') . '/claro_init_local.inc.php';
            $noQUERY_STRING = true;
        }
        else
        {
            $dialogBox->error( get_lang('Unable to add external tool') );
        }
    }
    else
    {
        $dialogBox->error( get_lang('Missing value') );
        $cmd = 'rqAdd';
    }
}

/**
 * UPDATE EXTERNAL TOOL SETTINGS
 */

if ($cmd == 'exEdit')
{
    if ( ! empty($externalLinkName) && ! empty($externalLinkUrl))
    {
        if ( set_local_course_tool($_REQUEST['externalToolId'],$externalLinkName,$externalLinkUrl) !== false )
        {
            // notify that tool list has been changed

            $eventNotifier->notifyCourseEvent('toollist_changed', claro_get_current_course_id(), "0", "0", "0", '0');

            $dialogBox->success( get_lang('External tool updated') );
            $cidReset = TRUE;
            $cidReq   = claro_get_current_course_id();

            include get_path('incRepositorySys') . '/claro_init_local.inc.php';
            $noQUERY_STRING = true;

        }
        else
        {
            $dialogBox->error( get_lang('Unable to update external tool') );
        }
    }
    else
    {
        $dialogBox->error( get_lang('Missing value') );
        $cmd = 'rqEdit';
    }

}

/*----------------------------------------------------------------------------
 DELETE EXTERNAL TOOL
----------------------------------------------------------------------------*/

if ($cmd == 'exDelete')
{
    if ($_REQUEST['externalToolId'])
    {
        if (delete_course_tool($_REQUEST['externalToolId']) !== false)
        {
            $dialogBox->success( get_lang('External tool deleted') );
            $cidReset = TRUE;
            $cidReq   = claro_get_current_course_id();

            include get_path('incRepositorySys') . '/claro_init_local.inc.php';
            $noQUERY_STRING = true;

        }
        else
        {
            $dialogBox->error( get_lang('Unable to delete external tool') );
        }
    }
    else
    {
        $dialogBox->error( get_lang('Unable to delete external tool') );
    }

}

/*----------------------------------------------------------------------------
 REQUEST AN EXTERNAL TOOL CHANGE OR ADD
----------------------------------------------------------------------------*/

if ($cmd == 'rqAdd' || $cmd == 'rqEdit')
{
    if ( isset($_REQUEST['externalToolId']) )
    {
        $externalToolId = $_REQUEST['externalToolId'];

        if ( empty($externalLinkName) || empty($externalLinkUrl) )
        {
            $toolSettingList = get_course_tool_settings($externalToolId);
            $externalLinkName = $toolSettingList['name'];
            $externalLinkUrl  = $toolSettingList['url'];
        }
    }
    else
    {
        $externalToolId = null;
    }

    $form = "\n".'<form action="'.htmlspecialchars( $_SERVER['PHP_SELF'] ).'" method="post">'."\n"
    .       claro_form_relay_context()
    .       '<input type="hidden" name="claroFormId" value="'.uniqid('').'" />'."\n"
    .       '<input type="hidden" name="section" value="'.htmlspecialchars($currentSection).'" />'."\n"
    .       '<input type="hidden" name="cmd" value="'.($externalToolId ? 'exEdit' : 'exAdd').'" />'."\n";

    if ($externalToolId)
    {
        $form .= '<input type="hidden" name="externalToolId" value="' . $externalToolId . '" />' . "\n";
    }

    $form .= '<label for="toolName">' . get_lang('Name link') . '</label>'
    .       '<br />' . "\n"
    .       '<input type="text" name="toolName" id="toolName" value="'.htmlspecialchars($externalLinkName).'" />'
    .       '<br />' . "\n"
    .       '<label for="toolUrl">'.get_lang('URL link').'</label><br />'."\n"
    .       '<input type="text" name="toolUrl" id="toolUrl" value="'.htmlspecialchars($externalLinkUrl).'" />'
    .       '<br /><br />' . "\n"
    .       '<input class="claroButton" type="submit" value="'.get_lang('Ok').'" />'
    .       '&nbsp; ' . "\n"
    .       claro_html_button($_SERVER['PHP_SELF'], get_lang('Cancel'))."\n"
    .       '</form>' . "\n"
    ;
    
    $dialogBox->form($form);
}

/*----------------------------------------------------------------------------
 ADD OR REMOVE A TOOL
----------------------------------------------------------------------------*/

$undeactivable_tool_array = array('CLDOC',
                                  'CLGRP',
                                  'CLUSR'
                                  );

if ( 'exRmTool' == $cmd )
{
    if ( is_null( $toolLabel ) )
    {
        $dialogBox->error( get_lang('Missing tool label') );
    }
    elseif ( in_array( $toolLabel, $undeactivable_tool_array ) )
    {
        $dialogBox->error( 'This tool cannot be removed' );
    }
    else
    {
        // get tool id
        $toolId = get_tool_id_from_module_label( $toolLabel );
        
        if ( $toolId )
        {
            // update course_tool.activated
            if ( update_course_tool_activation_in_course( $toolId,
                                                         claro_get_current_course_id(),
                                                         false ) )
            {
                $dialogBox->success(get_lang('Tool removed from course') );
                $cidReset = TRUE;
                $cidReq   = claro_get_current_course_id();
    
                include get_path('incRepositorySys') . '/claro_init_local.inc.php';
            }
            else
            {
                $dialogBox->error( get_lang('Cannot remove tool from course') );
            }
        }
        else
        {
            $dialogBox->error( get_lang('Not a valid tool') );
        }
    }
}

if ( 'exAddTool' == $cmd )
{
    if ( is_null( $toolLabel ) )
    {
        $dialogBox->error( get_lang('Missing tool label') );
    }
    else
    {
        // get tool id
        $toolId = get_tool_id_from_module_label( $toolLabel );
        
        if ( $toolId )
        {
            // update course_tool.activated
            if ( update_course_tool_activation_in_course( $toolId,
                                                         claro_get_current_course_id(),
                                                         true ) )
            {
                $dialogBox->success( get_lang('Tool added to course') );
                $cidReset = TRUE;
                $cidReq   = claro_get_current_course_id();

                include get_path('incRepositorySys') . '/claro_init_local.inc.php';
            }
            else
            {
                $dialogBox->error( get_lang('Cannot add tool to course') );
            }
        }
        else
        {
            $dialogBox->error( get_lang('Not a valid tool') );
        }
    }
}

// Build course tool list

// $_profileId is set in claro_init_local
// get all tools for the course

$toolList = claro_get_course_tool_list(
    claro_get_current_course_id(), $_profileId, true, true, false );

$displayToolList = array() ;

// Split course tool

foreach ( $toolList as $thisTool )
{
    $tid = $thisTool['id'];

    if ( ! empty($thisTool['label']) )
    {
        $main_tid = $thisTool['tool_id'];
        // course_tool
        $displayToolList[$main_tid]['tid'] = $tid;
        $displayToolList[$main_tid]['icon'] = get_module_url($thisTool['label']) .'/'. $thisTool['icon'];
        $displayToolList[$main_tid]['visibility'] = (bool) $thisTool['visibility'] ;
        $displayToolList[$main_tid]['activation'] = (bool) $thisTool['activation'] ;
    }
}

// Get external link list
$courseExtLinkList = claro_get_course_external_link_list();

/*============================================================================
    DISPLAY
 ============================================================================*/

// Display header
include get_path('incRepositorySys') . '/claro_init_header.inc.php';

echo claro_html_tool_title(get_lang('Edit Tool list'));


echo claro_html_tab_bar($sectionList,$currentSection);

echo $dialogBox->render();

if ( $currentSection == 'toolRights' )
{
    // echo claro_html_tool_title(get_lang('Manage tool access rights'));
    echo '<p>'
        . get_lang('Select the tools you want to make visible for your user.')
        . get_lang('An invisible tool will be greyed out on your personal interface.')
        . '<br />'
        . get_lang('You can also change the access rights for the different user profiles.')
        .'</p>'."\n"
        ;
    
    // Display course tool list
    
    // Get all profile
    
    $profileNameList = claro_get_all_profile_name_list();
    $display_profile_list = array_keys($profileNameList);
    
    $profileRightHtml = new RightProfileToolRightHtml();
    $profileRightHtml->addUrlParam('section', htmlspecialchars($currentSection));
    $profileRightHtml->setCourseToolInfo($displayToolList);
    
    $profileLegend = array();
    
    foreach ( $display_profile_list as $profileId )
    {
        $profile = new RightProfile();
        if ( $profile->load($profileId) )
        {
            $profileRight = new RightCourseProfileToolRight();
            $profileRight->setCourseId(claro_get_current_course_id());
            $profileRight->load($profile);
            $profileRightHtml->addRightProfileToolRight($profileRight);
            $profileLegend[] = get_lang($profileNameList[$profileId]['name'])
                . ' : <em>' . get_lang($profileNameList[$profileId]['description']) . '</em>' ;
        }
    }
    
    echo '<p><small><span style="text-decoration: underline">' . get_lang('Right list') . '</span> : '
        . '<img src="' . get_icon_url('forbidden') . '" alt="' . get_lang('None') . '" /> '
        . get_lang('No access') . ' - '
        . '<img src="' . get_icon_url('user') . '" alt="' . get_lang('User') . '" />'
        . get_lang('Access allowed') . ' - '
        . '<img src="' . get_icon_url('manager') . '" alt="' . get_lang('Manager') . '" /> '
        . get_lang('Edition allowed')
        . '.</small></p>'
        ;
    
    echo '<p><small><span style="text-decoration: underline">' . get_lang('Profile list')
        . '</span> : ' . implode($profileLegend,' - ') . '.</small></p>'
        ;
    
    echo '<blockquote>' . "\n"
        . $profileRightHtml->displayProfileToolRightList()
        . '</blockquote>' . "\n"
        ;
}
elseif ( $currentSection == 'extLinks' )
{
    // Display external link list
    
    // echo claro_html_tool_title(get_lang('Manage external links'));
    echo '<p>'.get_lang('Add external links to your course').'</p>'."\n" ;
    
    echo '<blockquote>' . "\n"
    .    '<p>' . "\n"
    .    '<a class="claroCmd" href="'
    .    htmlspecialchars(Url::Contextualize( $_SERVER['PHP_SELF']
    .    '?cmd=rqAdd&section='.htmlspecialchars($currentSection) )).'">'
    .    '<img src="' . get_icon_url('link') . '" alt="" />'
    .    get_lang('Add external link')
    .    '</a>' . "\n"
    .    '</p>' . "\n"
    
    .    '<table class="claroTable" >'."\n\n"
    .    '<thead>'."\n"
    .    '<tr class="headerX">'."\n"
    .    '<th>'.get_lang('Tools').'</th>'."\n"
    .    '<th>'.get_lang('Visibility').'</th>'."\n"
    .    '<th>'.get_lang('Edit').'</th>'."\n"
    .    '<th>'.get_lang('Delete').'</th>'."\n"
    .    '</tr>'."\n"
    .    '</thead>'."\n\n"
    .    '<tbody>'."\n"
    ;
    
    if ( !empty( $courseExtLinkList ) )
    {
        foreach ( $courseExtLinkList as $linkId => $link )
        {
            echo '<tr>'."\n";
        
            echo '<td ' . ($link['visibility']?'':'class="invisible"') . '>'
            . '<img src="' . get_icon_url( 'link' ) . '" alt="" />' .$link['name']
            . '</td>';
        
            echo '<td align="center">' ;
        
            if ( $link['visibility'] == true )
            {
                echo '<a href="' . htmlspecialchars(Url::Contextualize( $_SERVER['PHP_SELF'] . '?cmd=exInvisible&amp;tool_id=' . $linkId . '&amp;section='.htmlspecialchars($currentSection) )).'" >'
                . '<img src="' . get_icon_url('visible') . '" alt="' . get_lang('Visible') . '" />'
                . '</a>';
            }
            else
            {
                echo '<a href="' . htmlspecialchars(Url::Contextualize( $_SERVER['PHP_SELF'] . '?cmd=exVisible&amp;tool_id=' . $linkId .'&amp;section='.htmlspecialchars($currentSection) )).'" >'
                . '<img src="' . get_icon_url('invisible') . '" alt="' . get_lang('Invisible') . '" />'
                . '</a>';
        
            }
        
            echo '</td>'."\n";
        
            echo '<td align="center">'
            . '<a href="'.htmlspecialchars(Url::Contextualize( $_SERVER['PHP_SELF'].'?cmd=rqEdit&amp;externalToolId='.$linkId.'&amp;section='.htmlspecialchars($currentSection) )).'">'
            . '<img src="' . get_icon_url('edit') . '" alt="'.get_lang('Modify').'" />'
            . '</a></td>' . "\n" ;
        
            echo '<td align="center">'
            .'<a href="'.htmlspecialchars(Url::Contextualize( $_SERVER['PHP_SELF'].'?cmd=exDelete&amp;externalToolId='.$linkId.'&amp;section='.htmlspecialchars($currentSection) )).'"'
            .' onclick="return confirmation(\''.clean_str_for_javascript($link['name']).'\');">'
            .'<img src="' . get_icon_url('delete') . '" alt="'.get_lang('Delete').'" />'
            .'</a></td>'."\n";
        
            echo '</tr>'."\n";
        }
    }
    else
    {
        echo '<tr><td colspan="4">'.get_lang('Empty').'</td></tr>' . "\n";
    }
    
    echo '</tbody>' . "\n"
        . '</table>'."\n\n"
        . '</blockquote>'
        . "\n"
        ;
}
elseif ( $currentSection == 'toolList' )
{
    // echo claro_html_tool_title(get_lang('Add or remove tools'));
    echo '<p>'.get_lang('Add or remove tools from your course').'</p>'."\n" ;
    
    $activeCourseToolList = module_get_course_tool_list(
        claro_get_current_course_id(), true, true );
    
    $inactiveCourseToolList = module_get_course_tool_list(
        claro_get_current_course_id(), true, false );
    
    $platformCourseToolList = claro_get_main_course_tool_list(true);
    
    $completeInactiveToolList = array();
    
    foreach ( $inactiveCourseToolList as $inactiveCourseTool )
    {
        // var_dump($inactiveCourseTool);
        
        $completeInactiveToolList[] = array(
            'id' =>  $inactiveCourseTool['id'],
            'tool_id' => $inactiveCourseTool['tool_id'],
            'label' => $inactiveCourseTool['label'],
            'icon' => get_module_url($inactiveCourseTool['label']) . '/' . $inactiveCourseTool['icon']
        );
    }
    
    // var_dump( $platformCourseToolList );
    
    foreach ( $platformCourseToolList as $toolId => $platformCourseTool )
    {
        $found = false;
        foreach ( $activeCourseToolList as $activeCourse )
        {
            if ( $activeCourse['label'] == $platformCourseTool['label'] )
            {
                $found = true;
                break;
            }
        }
        
        $alreadyThere = false;
        foreach ( $inactiveCourseToolList as $inactiveCourseTool )
        {
            if ( $inactiveCourseTool['label'] == $platformCourseTool['label'] )
            {
                $alreadyThere = true;
                break;
            }
        }
        
        if ( $platformCourseTool['activation'] == true && ! $found && ! $alreadyThere )
        {
            $completeInactiveToolList[] = array(
                'tool_id' => $toolId,
                'label' => $platformCourseTool['label'],
                'icon' => $platformCourseTool['icon']
            );
        }
    }
    
    echo '<h3>' . get_lang('Tools currently in your course') . '</h3>' . "\n";
    
    echo '<blockquote>' . "\n"
        . '<table class="claroTable emphaseLine" style="width: 100%" >'."\n\n"
        . '<thead>'."\n"
        . '<tr class="headerX">'."\n"
        . '<th>'.get_lang('Tool').'</th>'."\n"
        . '<th>'.get_lang('Remove from course').'</th>'."\n"
        . '</tr>'."\n"
        . '</thead>'."\n\n"
        . '<tbody>'."\n"
        ;
    
    if ( !empty( $activeCourseToolList ) )
    {
        foreach ( $activeCourseToolList as $activeTool )
        {
            if ( ! in_array( $activeTool['label'], $undeactivable_tool_array ) )
            {
                $action_link = '<a href="' . htmlspecialchars(Url::Contextualize( $_SERVER['PHP_SELF']
                    . '?cmd=exRmTool&amp;toolLabel='
                    . htmlspecialchars($activeTool['label'])
                    .'&amp;section='.htmlspecialchars($currentSection) )).'" '
                    . 'title="'.get_lang('Remove').'">'
                    . '<img src="' . get_icon_url('delete') . '" border="0" alt="'. get_lang('Remove') . '"/>'
                    . '</a>'
                    ;
            }
            else
            {
                $action_link = '-';
            }
            echo '<tr>'
                . '<td><img src="'
                . get_module_url($activeTool['label']) . '/' . $activeTool['icon'] . '" alt="" /> '
                . get_lang(claro_get_tool_name($activeTool['tool_id'])).'</td>'
                . '<td>'.$action_link.'</td>'
                . '</tr>' . "\n"
                ;
        }
    }
    else
    {
        echo '<tr><td colspan="2">'.get_lang('Empty').'</td></tr>' . "\n";
    }
    
    echo '</tbody>' . "\n"
        . '</table>'."\n\n"
        . '</blockquote>'
        . "\n"
        ;
        
    echo '<h3>' . get_lang('Available tools to add to your course') . '</h3>' . "\n";
        
    echo '<blockquote>' . "\n"
        . '<table class="claroTable emphaseLine" style="width: 100%" >'."\n\n"
        . '<thead>'."\n"
        . '<tr class="headerX">'."\n"
        . '<th>'.get_lang('Tool').'</th>'."\n"
        . '<th>'.get_lang('Add to course').'</th>'."\n"
        . '</tr>'."\n"
        . '</thead>'."\n\n"
        . '<tbody>'."\n"
        ;
    
    if ( !empty( $completeInactiveToolList ) )
    {
        foreach ( $completeInactiveToolList as $inactiveTool )
        {
            $action_link = '<a href="' . htmlspecialchars(Url::Contextualize( $_SERVER['PHP_SELF']
                . '?cmd=exAddTool&amp;toolLabel='
                . htmlspecialchars($inactiveTool['label'])
                .'&amp;section='.htmlspecialchars($currentSection) )).'" '
                . 'title="'.get_lang('Add').'">'
                . '<img src="' . get_icon_url('select') . '" alt="'. get_lang('Add') . '"/>'
                . '</a>'
                ;
                
            echo '<tr>'
                . '<td><img src="'
                . $inactiveTool['icon'] . '" alt="" /> '
                . get_lang(claro_get_tool_name($inactiveTool['tool_id'])).'</td>'
                . '<td>'.$action_link.'</td>'
                . '</tr>' . "\n"
                ;
        }
    }
    else
    {
        echo '<tr><td colspan="2">'.get_lang('Empty').'</td></tr>' . "\n";
    }
    
    echo '</tbody>' . "\n"
        . '</table>'."\n\n"
        . '</blockquote>'
        . "\n"
        ;
}
else
{
    // should never happen
    echo get_lang('Invalid section');
}


// Display footer

include get_path('incRepositorySys') . '/claro_init_footer.inc.php';

?>