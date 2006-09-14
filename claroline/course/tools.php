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

if ( !$_cid || !$_uid ) claro_disp_auth_form(true);

if ( $is_courseAdmin ) $is_allowedToEdit = TRUE;
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

$currentCourseRepository = $_course['path'];

// Library
require_once $includePath . '/lib/course_home.lib.php';
require_once $includePath . '/lib/right/courseProfileToolAction.class.php' ;
require_once $includePath . '/lib/right/profileToolRightHtml.class.php' ;

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
        $courseProfileRight->setCourseId($_cid);
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

    $eventNotifier->notifyCourseEvent('toollist_changed', $_cid, '0', '0', '0', '0');
}

/*----------------------------------------------------------------------------
 ADD AN EXTERNAL TOOL
----------------------------------------------------------------------------*/

if ( $cmd == 'exAdd' )
{
    if ( ! empty ($_REQUEST['toolName']) && ! empty ($_REQUEST['toolUrl']))
    {
        if (insert_local_course_tool($_REQUEST['toolName'], $_REQUEST['toolUrl']) !== FALSE )
        {

            // notify that tool list has been changed
            $eventNotifier->notifyCourseEvent('toollist_changed', $_cid, "0", "0", "0", '0');

            $msg .= get_lang('External Tool added');

            $cidReset = TRUE;
            $cidReq   = $_cid;

            include $includePath . '/claro_init_local.inc.php';
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
    if ( ! empty ($_REQUEST['toolName']) && ! empty ($_REQUEST['toolUrl']))
    {
        if ( set_local_course_tool($_REQUEST['externalToolId'],$_REQUEST['toolName'],$_REQUEST['toolUrl']) !== false )
        {
            // notify that tool list has been changed

            $eventNotifier->notifyCourseEvent('toollist_changed', $_cid, "0", "0", "0", '0');

            $msg .= get_lang('External tool updated');
            $cidReset = TRUE;
            $cidReq   = $_cid;

            include $includePath . '/claro_init_local.inc.php';
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
            $cidReq   = $_cid;

            include $includePath . '/claro_init_local.inc.php';
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

        if ( isset ($_REQUEST['toolName']) && isset ($_REQUEST['toolUrl']))
        {
            $toolName = $_REQUEST['toolName'];
            $toolUrl  = $_REQUEST['toolUrl'];
        }
        else
        {
            $toolSettingList = get_course_tool_settings($externalToolId);
            $toolName = $toolSettingList['name'];
            $toolUrl  = $toolSettingList['url'];
        }
    }
    else
    {
        $externalToolId = null;

        $toolName = '';
        $toolUrl  = '';
    }

    $msg .= "\n".'<form action="'.$_SERVER['PHP_SELF'].'" method="post">'."\n"
    .'<input type="hidden" name="claroFormId" value="'.uniqid('').'">'."\n"
    .'<input type="hidden" name="cmd" value="'.($externalToolId ? 'exEdit' : 'exAdd').'">'."\n";

    if ($externalToolId)
    {
        $msg .= '<input type="hidden" name="externalToolId" value="' . $externalToolId . '">' . "\n";
    }

    $msg .= '<label for="toolName">'.get_lang('Name link').'</label><br />'."\n"
    .'<input type="text" name="toolName" id="toolName" value="'.htmlspecialchars($toolName).'"><br />'."\n"
    .'<label for="toolUrl">'.get_lang('URL link').'</label><br />'."\n"
    .'<input type="text" name="toolUrl" id="toolUrl" value="'.htmlspecialchars($toolUrl).'"><br /><br />'."\n"
    .'<input class="claroButton" type="submit" value="'.get_lang('Ok').'">&nbsp;'."\n"
    .claro_html_button($_SERVER['PHP_SELF'], get_lang('Cancel'))."\n"
    .'</form>'."\n" ;
}

$backLink = '<p>'
            .'<small>'
            .'<a href="'. $clarolineRepositoryWeb . 'course/index.php?cidReset=true&amp;cid=' . htmlspecialchars($_cid) . '">'
            .'&lt;&lt;&nbsp;'.get_lang('Back to Home page').'</a>'
            .'</small>'
            .'</p>'."\n\n" ;

// Build course tool list

$toolList = claro_get_course_tool_list($_cid,$_profileId);

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
include $includePath . '/claro_init_header.inc.php';

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
        $profileRight->setCourseId($_cid);
        $profileRight->load($profile);
        $profileRightHtml->addRightProfileToolRight($profileRight);
    }
}

echo '<blockquote>' . "\n"
    . $profileRightHtml->displayProfileToolRightList()
    . '</blockquote>' . "\n" ;

// Display external link list

echo claro_html_tool_title(get_lang('Manage External link'));

echo '<blockquote>'."\n"
    . '<p><a class="claroCmd" href="' . $_SERVER['PHP_SELF'] . '?cmd=rqAdd"><img src="' . $imgRepositoryWeb . 'link.gif" alt="">' . get_lang('Add external link') . '</a></p>' . "\n";

echo '<table class="claroTable" >'."\n\n"
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
        . '<img src="' . $imgRepositoryWeb . 'visible.gif" alt="' . get_lang('Visible') . '" />'
        . '</a>';
    }
    else
    {
        echo '<a href="' . $_SERVER['PHP_SELF'] . '?cmd=exVisible&tool_id=' . $linkId .'" >'
        . '<img src="' . $imgRepositoryWeb . 'invisible.gif" alt="' . get_lang('Invisible') . '" />'
        . '</a>';

    }

    echo '</td>'."\n";

    echo '<td align="center">'
    . '<a href="'.$_SERVER['PHP_SELF'].'?cmd=rqEdit&amp;externalToolId='.$linkId.'">'
    . '<img src="'.$imgRepositoryWeb.'edit.gif" alt="'.get_lang('Modify').'" />'
    . '</a></td>' . "\n" ;

    echo '<td align="center">'
    .'<a href="'.$_SERVER['PHP_SELF'].'?cmd=exDelete&amp;externalToolId='.$linkId.'"'
    .' onClick="return confirmation(\''.clean_str_for_javascript($link['name']).'\');">'
    .'<img src="'.$imgRepositoryWeb.'delete.gif" alt="'.get_lang('Delete').'" />'
    .'</a></td>'."\n";

    echo '</tr>'."\n";
}

echo '</tbody>'."\n"
    . '</table>'."\n\n"
    . '</blockquote>' . "\n"
    . '<hr size="1" noshade="noshade" >' . "\n\n"
    . $backLink ;

// Display footer

include $includePath . '/claro_init_footer.inc.php';

?>
