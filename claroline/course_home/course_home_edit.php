<?php // $Id$
/**
 * CLAROLINE
 *
 * @version 1.7 $Revision$
 *
 * @copyright (c) 2001-2005 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @package CLHOME
 *
 * @author Claro Team <cvs@claroline.net>
 */


$course_homepage = TRUE;

$gidReset = true; // If user is here. It means he isn't in any group space now.
                  // So it's careful to to reset the group setting

require '../inc/claro_init_global.inc.php';

if ( !$_cid || !$_uid ) claro_disp_auth_form(true);

if ( $is_courseAdmin ) $is_allowedToEdit = TRUE;
else                   claro_die(get_lang('NotAllowed'));

$htmlHeadXtra[] =
'<script type="text/javascript">
function confirmation (name)
{
    if (confirm(\''.clean_str_for_javascript(get_lang('AreYouSureToDelete')).'\'+ name + \' ?\'))
        {return true;}
    else
        {return false;}
}
</script>';

$toolRepository = '../';

$currentCourseRepository = $_course['path'];

include $includePath . '/claro_init_header.inc.php';
include $includePath . '/lib/course_home.lib.php';

/*
 * set access level of the user
 */

if     ($is_platformAdmin)   $reqAccessLevel = 'PLATFORM_ADMIN';
elseif ($is_courseAdmin  )   $reqAccessLevel = 'COURSE_ADMIN';

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

/*============================================================================
 COMMAND SECTION
============================================================================*/

if ( isset($_REQUEST['cmd']) ) $cmd = $_REQUEST['cmd'];
else                           $cmd = '';

$msg = '';

/*----------------------------------------------------------------------------
 SET THE TOOL ACCESSES
----------------------------------------------------------------------------*/

if ($cmd == 'exSetToolAccess')
{
    $enablableToolList  = array();
    $disablableToolList = array();

    $currentToolStateList = get_course_home_tool_list($reqAccessLevel);

    foreach($currentToolStateList as $thisCurrentToolState)
    {

        if ( isset($_REQUEST['toolAccessList']) && is_array($_REQUEST['toolAccessList'])
             && in_array($thisCurrentToolState['id'],$_REQUEST['toolAccessList'])
            )
        {
            $enablableToolList[] = $thisCurrentToolState['id'];
        }
        else
        {
            $disablableToolList[] = $thisCurrentToolState['id'];
        }
    }

    $enableToolQuerySucceed  = enable_course_tool($enablableToolList);
    $disableToolQuerySucceed = disable_course_tool($disablableToolList);

    if ($enableToolQuerySucceed !== FALSE && $disableToolQuerySucceed !== FALSE)
    {
        // notify that tool list has been changed

        $eventNotifier->notifyCourseEvent('toollist_changed', $_cid, '0', '0', '0', '0');
        $msg .= get_lang('ChangedTool');
    }
    else
    {
        $msg .= get_lang('UnableChangedTool');
    }

}

/*----------------------------------------------------------------------------
 ADD AN EXTERNAL TOOL
----------------------------------------------------------------------------*/

if ($cmd == 'exAdd')
{
    if ( ! empty ($_REQUEST['toolName']) && ! empty ($_REQUEST['toolUrl']))
    {
        if (insert_local_course_tool($_REQUEST['toolName'], $_REQUEST['toolUrl']) !== FALSE )
        {

         // notify that tool list has been changed

         $eventNotifier->notifyCourseEvent('toollist_changed', $_cid, "0", "0", "0", '0');

         $msg .= get_lang('AddedExternalTool');
        }
        else
        {
            $msg .= get_lang('UnableAddExternalTool');
        }
    }
    else
    {
        $msg .= get_lang('MissingValue');
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

            $msg .= get_lang('UpdatedExternalTool');
        }
        else
        {
            $msg .= get_lang('UnableUpdateExternalTool');
        }
    }
    else
    {
        $msg .= get_lang('MissingValue');
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
            $msg .= get_lang('DeletedExternalTool');
        }
        else
        {
            $msg .= get_lang('UnableDeleteExternalTool');
        }
    }
    else
    {
        $msg .= get_lang('UnableDeleteExternalTool');
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

    $msg .= '<label for="toolName">'.get_lang('ExternalToolName').'</label><br />'."\n"
            .'<input type="text" name="toolName" id="toolName" value="'.htmlspecialchars($toolName).'"><br />'."\n"
            .'<label for="toolUrl">'.get_lang('ExternalToolUrl').'</label><br />'."\n"
            .'<input type="text" name="toolUrl" id="toolUrl" value="'.htmlspecialchars($toolUrl).'"><br /><br />'."\n"
            .'<input class="claroButton" type="submit" value="'.get_lang('Ok').'">&nbsp;'."\n"
            .claro_disp_button($_SERVER['PHP_SELF'], get_lang('Cancel'))."\n"
            .'</form>'."\n" ;
}

$backLink = '<p>'
            .'<small>'
            .'<a href="'.$coursesRepositoryWeb . $currentCourseRepository.'/index.php?cidReset=true&amp;cidReq='.$_cid.'">'
            .'&lt;&lt;&nbsp;'.get_lang('Home').'</a>'
            .'</small>'
            .'</p>'."\n\n" ;

/*============================================================================
    DISPLAY
 ============================================================================*/

echo $backLink;

echo claro_disp_tool_title(get_lang('EditToolList'));

if ($msg) echo claro_disp_message_box($msg);

echo '<p>'.get_lang('IntroEditToolList').'</p>'."\n"
    .'<blockquote>'."\n"
    .'<form action="'.$_SERVER['PHP_SELF'].'" method="post">'."\n"
    .'<input type="hidden" name="cmd" value="exSetToolAccess" >'."\n"
    ;

$toolList = get_course_home_tool_list($reqAccessLevel);

echo '<table class="claroTable" >'."\n\n"
    . '<thead>'."\n"
    . '<tr class="headerX">'."\n"
    . '<th>'.get_lang('Tools').'</th>'."\n"
    . '<th>'.get_lang('Activate').'</th>'."\n"
    . '</tr>'."\n"
    . '</thead>'."\n\n"
    . '<tbody>'."\n"
    ;

foreach($toolList as $thisTool)
{
    // get name and url from course or main database

    if ( ! empty($thisTool['label'])) // standart claroline tool
    {
        $toolName      = $toolNameList[ $thisTool['label'] ];
        $url           = trim($toolRepository.$thisTool['url']);
        $removableTool = false;
    }
    else                            // external tool added by course manager
    {
        if ( ! is_null($thisTool['name']) ||  ! is_null($thisTool['url']) )
        {
            $removableTool = true;

            if ( ! empty($thisTool['name'])) $toolName = $thisTool['name'];
            else                             $toolName = '<i>no name</i>';

            $url = trim($thisTool['url']);
        }
    }

    if (! empty($thisTool['icon']))
    {
        $icon = $imgRepositoryWeb.$thisTool['icon'];
    }
    else
    {
        $icon = $imgRepositoryWeb.'tool.gif'; // default icon if none defined
    }

    if ($accessLevelList[$thisTool['access']] > $accessLevelList['ALL'])
    {
        $checkState = '';
    }
    else
    {
        $checkState = ' checked';
    }

    echo '<tr>'."\n";

    echo '<td >'."\n"
        .'<label for="toolAccessList'.$thisTool['id'].'">'
        .'<img src="'.$icon.'" alt="" />'
        .$toolName.'</label>'."\n"
        .'</td>'."\n"
        .'<td>'."\n"
        .'<input type="checkbox" name="toolAccessList[]" id="toolAccessList'.$thisTool['id'].'" value="'.$thisTool['id'].'"'.$checkState.'>'."\n";

    if ($removableTool)
    {
        echo '<a href="'.$_SERVER['PHP_SELF'].'?cmd=rqEdit&amp;externalToolId='.$thisTool['id'].'">'
            .'<img src="'.$imgRepositoryWeb.'edit.gif" alt="'.get_lang('Modify').'" />'
            .'</a>'."\n"
            .'<a href="'.$_SERVER['PHP_SELF'].'?cmd=exDelete&amp;externalToolId='.$thisTool['id'].'"'
            .' onClick="return confirmation(\''.clean_str_for_javascript($toolName).'\');">'
            .'<img src="'.$imgRepositoryWeb.'delete.gif" alt="'.get_lang('Delete').'" />'
            .'</a>'."\n";

    }

    echo '</td>'."\n".'</tr>'."\n\n";
}

echo '</tbody>'."\n"
    . '</table>'."\n\n"
    . '<input class="claroButton" type="submit" value="' . get_lang('Ok') . '" >'."\n"
    . claro_disp_button($coursesRepositoryWeb . $_course['path'], get_lang('Cancel'))
    . '</form>'."\n"
    . '</blockquote>' . "\n"
    . '<hr size="1" noshade="noshade" >' . "\n\n"
    . '<a class="claroCmd" href="' . $_SERVER['PHP_SELF'] . '?cmd=rqAdd">' . get_lang('AddExternalTool') . '</a>' . "\n"
    . $backLink;

include $includePath . '/claro_init_footer.inc.php';

?>
