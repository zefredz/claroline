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

// Library
require_once get_path('incRepositorySys') . '/lib/course_home.lib.php';
require_once get_path('incRepositorySys') . '/lib/right/courseProfileToolAction.class.php';
require_once get_path('incRepositorySys') . '/lib/right/profileToolRightHtml.class.php';

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

$externalLinkName = isset($_REQUEST['toolName'])?$_REQUEST['toolName']:null;
$externalLinkUrl = isset($_REQUEST['toolUrl'])?$_REQUEST['toolUrl']:null;

$msg = '';

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

            $msg .= get_lang('External Tool added');

            $cidReset = TRUE;
            $cidReq   = claro_get_current_course_id();

            include get_path('incRepositorySys') . '/claro_init_local.inc.php';
            $noQUERY_STRING = true;
        }
        else
        {
            $msg .= get_lang('Unable to add external tool');
        }
    }
    else
    {
        $msg .= get_lang('Missing value');
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

            $msg .= get_lang('External tool updated');
            $cidReset = TRUE;
            $cidReq   = claro_get_current_course_id();

            include get_path('incRepositorySys') . '/claro_init_local.inc.php';
            $noQUERY_STRING = true;

        }
        else
        {
            $msg .= get_lang('Unable to update external tool');
        }
    }
    else
    {
        $msg .= get_lang('Missing value');
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
            $msg .= get_lang('External tool deleted');
            $cidReset = TRUE;
            $cidReq   = claro_get_current_course_id();

            include get_path('incRepositorySys') . '/claro_init_local.inc.php';
            $noQUERY_STRING = true;

        }
        else
        {
            $msg .= get_lang('Unable to delete external tool');
        }
    }
    else
    {
        $msg .= get_lang('Unable to delete external tool');
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

    $msg .= "\n".'<form action="'.$_SERVER['PHP_SELF'].'" method="post">'."\n"
    .       claro_form_relay_context()
    .       '<input type="hidden" name="claroFormId" value="'.uniqid('').'">'."\n"
    .       '<input type="hidden" name="cmd" value="'.($externalToolId ? 'exEdit' : 'exAdd').'">'."\n";

    if ($externalToolId)
    {
        $msg .= '<input type="hidden" name="externalToolId" value="' . $externalToolId . '">' . "\n";
    }

    $msg .= '<label for="toolName">'.get_lang('Name link').'</label><br />'."\n"
    .'<input type="text" name="toolName" id="toolName" value="'.htmlspecialchars($externalLinkName).'"><br />'."\n"
    .'<label for="toolUrl">'.get_lang('URL link').'</label><br />'."\n"
    .'<input type="text" name="toolUrl" id="toolUrl" value="'.htmlspecialchars($externalLinkUrl).'"><br /><br />'."\n"
    .'<input class="claroButton" type="submit" value="'.get_lang('Ok').'">&nbsp; '."\n"
    .claro_html_button($_SERVER['PHP_SELF'], get_lang('Cancel'))."\n"
    .'</form>'."\n" ;
}

$backLink = '<p>'
            .'<small>'
            .'<a href="'. get_path('clarolineRepositoryWeb') . 'course/index.php?cidReset=true&amp;cid=' . htmlspecialchars(claro_get_current_course_id()) . '">'
            .'&lt;&lt;&nbsp;'.get_lang('Back to Home page').'</a>'
            .'</small>'
            .'</p>'."\n\n" ;

// Build course tool list

// TODO add comment about were is set $_profileId

$toolList = claro_get_course_tool_list(claro_get_current_course_id(),$_profileId);

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

if ($msg) echo claro_html_message_box($msg);

echo '<p>'.get_block('blockCourseHomePageIntroduction').'</p>'."\n" ;

// Display course tool list

// Get all profile

$profileNameList = claro_get_all_profile_name_list();
$display_profile_list = array_keys($profileNameList);

$profileRightHtml = new RightProfileToolRightHtml();
$profileRightHtml->setCourseToolInfo($displayToolList);

foreach ( $display_profile_list as $profileId )
{
    $profile = new RightProfile();
    if ( $profile->load($profileId) )
    {
        $profileRight = new RightCourseProfileToolRight();
        $profileRight->setCourseId(claro_get_current_course_id());
        $profileRight->load($profile);
        $profileRightHtml->addRightProfileToolRight($profileRight);
    }
}

echo '<blockquote>' . "\n"
    . $profileRightHtml->displayProfileToolRightList()
    . '</blockquote>' . "\n" ;

// Display external link list

echo claro_html_tool_title(get_lang('Manage External link'));

echo '<blockquote>' . "\n"
.    '<p>' . "\n"
.    '<a class="claroCmd" href="'
.    $_SERVER['PHP_SELF']
.    '?cmd=rqAdd' . claro_url_relay_context('&amp;') . '">'
.    '<img src="' . get_path('imgRepositoryWeb') . 'link.gif" alt="">'
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
.    '<tbody>'."\n" ;

foreach ( $courseExtLinkList as $linkId => $link )
{
    echo '<tr>'."\n";

    echo '<td ' . ($link['visibility']?'':'class="invisible"') . '>'
    . '<img src="'.$link['icon'].'" alt="" />' .$link['name']
    . '</td>';

    echo '<td align="center">' ;

    if ( $link['visibility'] == true )
    {
        echo '<a href="' . $_SERVER['PHP_SELF'] . '?cmd=exInvisible&tool_id=' . $linkId . '" >'
        . '<img src="' . get_path('imgRepositoryWeb') . 'visible.gif" alt="' . get_lang('Visible') . '" />'
        . '</a>';
    }
    else
    {
        echo '<a href="' . $_SERVER['PHP_SELF'] . '?cmd=exVisible&tool_id=' . $linkId .'" >'
        . '<img src="' . get_path('imgRepositoryWeb') . 'invisible.gif" alt="' . get_lang('Invisible') . '" />'
        . '</a>';

    }

    echo '</td>'."\n";

    echo '<td align="center">'
    . '<a href="'.$_SERVER['PHP_SELF'].'?cmd=rqEdit&amp;externalToolId='.$linkId.'">'
    . '<img src="' . get_path('imgRepositoryWeb') . 'edit.gif" alt="'.get_lang('Modify').'" />'
    . '</a></td>' . "\n" ;

    echo '<td align="center">'
    .'<a href="'.$_SERVER['PHP_SELF'].'?cmd=exDelete&amp;externalToolId='.$linkId.'"'
    .' onClick="return confirmation(\''.clean_str_for_javascript($link['name']).'\');">'
    .'<img src="' . get_path('imgRepositoryWeb') . 'delete.gif" alt="'.get_lang('Delete').'" />'
    .'</a></td>'."\n";

    echo '</tr>'."\n";
}

echo '</tbody>'."\n"
.    '</table>'."\n\n"
.    '</blockquote>' . "\n"
.    '<hr size="1" noshade="noshade" >' . "\n\n"
.    $backLink
;

// Display footer

include get_path('incRepositorySys') . '/claro_init_footer.inc.php';

?>