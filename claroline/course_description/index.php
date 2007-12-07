<?php // $Id$
/**
 * CLAROLINE
 *
 * This  page show  to the user, the course description
 *
 * If ist's the admin, he can access to the editing
 *
 * @version 1.8 $Revision$
 *
 * @copyright 2001-2006 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @see http://www.claroline.net/wiki/CLDSC/
 *
 * @author Claro Team <cvs@claroline.net>
 *
 * @package CLDSC
 *
 */

$tlabelReq = 'CLDSC';

require '../inc/claro_init_global.inc.php';
include_once $includePath . '/lib/courseDescription.lib.php';

if ( ! $_cid || ! $is_courseAllowed ) claro_disp_auth_form(true);

claro_set_display_mode_available(TRUE);
$nameTools = get_lang('Course description');

$noQUERY_STRING = TRUE; // to remove parameters in the last breadcrumb link

include 'tiplistinit.inc.php';

$dialogBox = '';

/******************************************************************************
UPDATE / ADD DESCRIPTION ITEM
******************************************************************************/

$is_allowedToEdit = claro_is_allowed_to_edit();

$acceptedCmdList = array('rqEdit', 'exEdit', 'exAdd', 'rqDelete', 'exDelete', 'mkShow','mkHide');
$cmd = ( isset($_REQUEST['cmd']) && in_array($_REQUEST['cmd'],$acceptedCmdList)) ? $_REQUEST['cmd'] : null;


if ( $is_allowedToEdit )
{

    /*> > > > > > > > > > > > COMMANDS < < < < < < < < < < < < */

    $descTitle   = isset($_REQUEST['descTitle'])   ? trim($_REQUEST['descTitle'])   : '';
    $descContent = isset($_REQUEST['descContent']) ? trim($_REQUEST['descContent']) : '';
    $descId      = isset($_REQUEST['id'])          ? (int) $_REQUEST['id']          : -1 ;

    if ( 'exEdit' == $cmd )
    {
        // Update description
        if ( course_description_set_item($descId, $descTitle, $descContent) != false )
        {
            $eventNotifier->notifyCourseEvent('course_description_modified', $_cid, $_tid, $descId, $_gid, '0');
            $dialogBox .= '<p>' . get_lang('Description updated') . '</p>';
        }
        else $dialogBox .= '<p>' . get_lang('Unable to update') . '</p>';

    }

    if ( 'exAdd' == $cmd)
    {
        // Add new description
        $descId = course_description_add_item($descId,$descTitle,$descContent,sizeof($titreBloc));
        $dialogBox .= '<p>' . ($descId !== false ? get_lang('Description added.') : get_lang("Unable to add description") ) . '</p>';

        $eventNotifier->notifyCourseEvent('course_description_added',$_cid, $_tid, $descId, $_gid, 0);

    }

    /******************************************************************************
    REQUEST DESCRIPTION ITEM EDITION
    ******************************************************************************/

    if ( 'rqEdit' == $cmd )
    {
        claro_set_display_mode_available(false);

        if ( isset($_REQUEST['tipsId']) && $_REQUEST['tipsId'] >= 0 )
        {
            $tipsId = $_REQUEST['tipsId'];
        }
        else $tipsId = -1; // initialise tipsId


        if ( isset($descId) && $descId >=0 )
        {
            $descItem = course_description_get_item($descId);
            $tipsId = course_description_get_tips_id($descId); // retrieve tips Id with desc title
        }
        else
        {
            $descItem['id'     ] = $tipsId;
            $descItem['title'  ] = '';
            $descItem['content'] = '';
        }

        // From tiplist.inc.php

        if ( $tipsId >= 0 && isset($titreBloc[$tipsId]) )
        {
            $descPresetTitle    = $titreBloc[$tipsId];
            $descNotEditable    = $titreBlocNotEditable[$tipsId];
            $descPresetQuestion = $questionPlan[$tipsId];
            $descPresetTip      = $info2Say[$tipsId];
        }
        else
        {
            $descPresetTitle    = null;
            $descNotEditable    = false;
            $descPresetQuestion = null;
            $descPresetTip      = null;
        }

        $displayForm = TRUE;
    }

    /******************************************************************************
    DELETE DESCRIPTION ITEM
    ******************************************************************************/
    if ( 'rqDelete' == $cmd )
    {
        $dialogBox .= '<p>' . get_lang('Are you sure to delete') . '</p>';
    }

    if ( 'exDelete' == $cmd  && $descId >=0 )
    {
        if ( course_description_delete_item($descId) )
        {
            $eventNotifier->notifyCourseEvent('course_description_deleted',$_cid, $_tid, $descId, $_gid, '0');
            $dialogBox .= '<p>' . get_lang("Description deleted.") . '</p>';
        }

        else
        {
            $dialogBox .= '<p>' . get_lang("Unable to delete") . '</p>';
        }
    }


    /******************************************************************************
    EDIT  VISIBILITY DESCRIPTION ITEM
    ******************************************************************************/


    if ( ( 'mkShow' == $cmd || 'mkHide' == $cmd ) && ($descId >= 0) )
    {
        if ( course_description_visibility_item($descId , $cmd) )
        {
            $dialogBox .= '<p>' . get_lang('Visibility modified'). '</p>';
        }

        //notify that an item is now visible

        if ( 'mkShow' == $cmd )
        {
            $eventNotifier->notifyCourseEvent('course_description_visible',$_cid, $_tid, $descId, $_gid, '0');
        }
    }
}

/*---------------------------------------------------------------------------*/

event_access_tool($_tid, $_courseTool['label']);

/******************************************************************************
LOAD THE DESCRIPTION LIST
******************************************************************************/

$descList = course_description_get_item_list();

/*> > > > > > > > > > > > OUTPUT < < < < < < < < < < < < */

require $includePath . '/claro_init_header.inc.php';

echo claro_html_tool_title($nameTools);

if ( isset($dialogBox) && ! empty($dialogBox) )
{
    echo claro_html_message_box($dialogBox)
    .    '<br />' . "\n"
    ;
}

$is_allowedToEdit = claro_is_allowed_to_edit();

if ( $is_allowedToEdit )
{

    /**************************************************************************
    EDIT FORM DISPLAY
    **************************************************************************/

    if ( isset($displayForm) && $displayForm )
    {
        $cmdForm = ($descItem['content'] ? 'exEdit' : 'exAdd' );
        if (!isset($descItem['id'])) $descItem['id']='';
        echo '<table border="0">' . "\n"
        .    '<tr>'               . "\n"
        .    '<td>'               . "\n"
        .    '<form  method="post" action="' . $_SERVER['PHP_SELF'] . '">' . "\n"
        .    '<input type="hidden" name="cmd" value="' . $cmdForm . '">'
        .    '<input type="hidden" name="claroFormId" value="' . uniqid('') . '" />' . "\n"
        .    '<input type="hidden" name="id" value="' . $descItem['id'] . '">'
        .    '<p>' . "\n"
        .    '<label for="descTitle">' . "\n"
        .    '<b>' . get_lang('Title') . ' : </b>' . "\n"
        .    '</label>' . "\n"
        .    '<br />' . "\n"
        .    '</p>' . "\n"
        .    ( $descNotEditable==true ? htmlspecialchars($descPresetTitle) . '<input type="hidden" name="descTitle" value="'. htmlspecialchars($descPresetTitle) .'">' : '<input type="text" name="descTitle" id="descTitle" size="50" value="' . htmlspecialchars($descItem['title']) . '">' . "\n")
        .    '<p>' . "\n"
        .    '<label for="descContent">' . "\n"
        .    '<b>' . get_lang('Content') . ' : </b>' . "\n"
        .    '</label>' . "\n"
        .    '<br />' . "\n"
        .    '</td>' . "\n"
        .    '</tr>' . "\n"
        .    '<tr>' . "\n"
        .    '<td>'."\n"
        .    claro_html_textarea_editor('descContent', $descItem['content'], 20, 80, $optAttrib=' wrap="virtual"')."\n"

        .    '<input type="submit" name="save" value="' . get_lang('Ok') . '" />&nbsp; ' . "\n"
        .    claro_html_button($_SERVER['PHP_SELF'], get_lang('Cancel'))
        .    '</form>' . "\n"
        .    '</td>'  . "\n"

        .    '<td valign="top">' . "\n"
        ;

        if ( $descPresetQuestion )
        {
            echo '<h4>' . get_lang("Question to lecturer") . '</h4>' . "\n"
            .    '<p>' . $descPresetQuestion . '</p>' . "\n"
            ;
        }

        if ($descPresetTip)
        {
            echo '<h4>' . get_lang("Information to give to students") . '</h4>' . "\n"
            .    '<p>' . $descPresetTip . '</p>' . "\n"
            ;
        }


        echo '</td>' . "\n"
        .    '</tr>'   . "\n"
        .    '</table>'. "\n"
        ;

    } // end if display form
    else
    {

        /**************************************************************************
        ADD FORM DISPLAY
        **************************************************************************/

        echo "\n\n"
        .    '<form method="get" action="' . $_SERVER['PHP_SELF'] . '?edIdBloc=add">' . "\n"
        .    '<input type="hidden" name="claroFormId" value="'.uniqid('').'" />'."\n"
        .    '<input type="hidden" name="cmd" value="rqEdit" />' . "\n"
        .    '<select name="tipsId">' . "\n"
        ;


        foreach ( $titreBloc as $key => $thisBlocTitle )
        {
            $alreadyUsed = false;
            foreach ( $descList as $thisDesc )
            {
                if ( $thisDesc['id'] == $key ) $alreadyUsed = true ;
            }

            if ( ($alreadyUsed)==false)
            {
                echo '<option value="' . $key . '">' . $thisBlocTitle . '</option>' . "\n";
            }
        }

        echo '<option value="-1">' . get_lang("Other") . '</option>' . "\n"
        .    '</select>' . "\n"
        .    '<input type="submit" name="add" value="' . get_lang('Add') . '">' . "\n"
        .    '</form>' . "\n\n"
        ;
    }
} // end if is_allowedToEdit

/******************************************************************************
DESCRIPTION LIST DISPLAY
******************************************************************************/
$hasDisplayedItems = false;

if ( count($descList) )
{

    if (isset($_uid)) $date = $claro_notifier->get_notification_date($_uid);

    echo '<table class="claroTable" width="100%">' . "\n";

    foreach ( $descList as $thisDesc )
    {

        //modify style if the file is recently added since last login

        if (isset($_uid) && $claro_notifier->is_a_notified_ressource($_cid, $date, $_uid, $_gid, $_tid, $thisDesc['id']))
        {
            $classItem=' hot';
        }
        else // otherwise just display its name normally
        {
            $classItem='';
        }

        if (($thisDesc['visibility']=='HIDE' && $is_allowedToEdit) || $thisDesc['visibility']=='SHOW')
        {
            if ($thisDesc['visibility']=='HIDE') $style = ' class="invisible"';  else $style='';

            //    echo "\n".''.
            echo '<tr class="superHeader">'
            .    '<th class="item' . $classItem . '">'
            .    '<div' . $style . '>';

            if( trim($thisDesc['title']) == '' )
                echo '&nbsp;';
            else
                echo htmlspecialchars($thisDesc['title']);

            echo '</div>'
            .    '</th>'
            .    '</tr>' . "\n"
            .    '<tr>'
            .    '<td>'
            .    '<div' . $style . '>'
            .    claro_parse_user_text($thisDesc['content'])
            .    '</div>'
            .    '</td>'
            .    '</tr>' . "\n" . "\n"
            ;
            $hasDisplayedItems = true;
        }
        echo '<tr>' . "\n"
        .    '<td>' . "\n"
        ;
        if ( $is_allowedToEdit )
        {
            echo '<a href="' . $_SERVER['PHP_SELF'] . '?cmd=rqEdit&amp;id=' . $thisDesc['id'] . '">'
            .    '<img src="' . $imgRepositoryWeb . 'edit.gif" alt="' . get_lang('Modify') . '">'
            .    '</a>' . "\n"
            .    '<a href="' . $_SERVER['PHP_SELF'] . '?cmd=exDelete&amp;id=' . $thisDesc['id'] . '"'
            .    ' onClick="if(!confirm(\'' . clean_str_for_javascript(get_lang('Are you sure to delete'))
            .    ' ' . $thisDesc['title'] . ' ?\')){ return false}">'
            .    '<img src="' . $imgRepositoryWeb . 'delete.gif" alt="' . get_lang('Delete') . '" />'
            .    '</a>' . "\n"
            ;
            if ($thisDesc['visibility'] == 'SHOW')
            {
                echo '<a href="' . $_SERVER['PHP_SELF'] . '?cmd=mkHide&amp;id=' . $thisDesc['id'] . '">'
                .    '<img src="' . $imgRepositoryWeb . 'visible.gif" alt="' . get_lang('Invisible') . '" />'
                .    '</a>' . "\n"
                ;
            }
            else
            {
                echo '<a href="' . $_SERVER['PHP_SELF'] . '?cmd=mkShow&amp;id=' . $thisDesc['id'] . '">'
                .    '<img src="' . $imgRepositoryWeb . 'invisible.gif" alt="' . get_lang('Visible') . '" />'
                .    '</a>' . "\n"
                ;
            }
        }
        echo '</td>'."\n"
        .    '</tr>'."\n\n"
        ;

    }
    echo '</table>'."\n\n";
}

if( !$hasDisplayedItems )
{
    echo "\n" . '<p>' . get_lang("This course is currently not described") . '</p>' . "\n";
}

include $includePath . '/claro_init_footer.inc.php';

?>
