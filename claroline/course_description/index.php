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
// TODO add config var to allow multiple post of same type
$tlabelReq = 'CLDSC';

require '../inc/claro_init_global.inc.php';

if ( ! claro_is_in_a_course() || ! claro_is_course_allowed() ) claro_disp_auth_form(true);

claro_set_display_mode_available(true);

$is_allowedToEdit = claro_is_allowed_to_edit();

//-- Tool libraries
include_once get_module_path($tlabelReq) . '/lib/courseDescription.class.php';
include_once get_module_path($tlabelReq) . '/lib/courseDescription.lib.php';

//-- Get $tipList
$tipList = get_tiplistinit();


event_access_tool(claro_get_current_tool_id(), claro_get_current_course_tool_data('label'));

/*
 * init request vars
 */
$acceptedCmdList = array('rqEdit', 'exEdit', 'exDelete', 'mkVis','mkInvis');
if ( isset($_REQUEST['cmd']) && in_array($_REQUEST['cmd'], $acceptedCmdList) )   $cmd = $_REQUEST['cmd'];
else                                                                             $cmd = null;

if ( isset($_REQUEST['descId']) && is_numeric($_REQUEST['descId']) ) $descId = (int) $_REQUEST['descId'];
else                                                                 $descId = null;

if ( isset($_REQUEST['category']) && $_REQUEST['category'] >= 0 )    $category = $_REQUEST['category'];
else                                                                 $category = -1;

/*
 * init other vars
 */
$messageList = array();

if ( $is_allowedToEdit && !is_null($cmd) )
{
    $description = new CourseDescription();

    if ( !is_null($descId) && !$description->load($descId) )
    {
        // description must be load but cannot, cancel any command
        $cmd = null;
        $descId = null;
    }

    /*> > > > > > > > > > > > COMMANDS < < < < < < < < < < < < */


    if ( $cmd == 'exEdit' )
    {
        if ( isset($_REQUEST['descTitle']) )     $description->setTitle($_REQUEST['descTitle']);
        if ( isset($_REQUEST['descContent']) )   $description->setContent($_REQUEST['descContent']);
        if ( isset($_REQUEST['descCategory']) )  $description->setCategory($_REQUEST['descCategory']);

        if ( $description->validate() )
        {
            // Update description
            if ( $description->save() )
            {
                if ( $descId )
                {
                    $eventNotifier->notifyCourseEvent('course_description_modified', claro_get_current_course_id(), claro_get_current_tool_id(), $descId, claro_get_current_group_id(), '0');
                    $messageList['info'][] = '<p>' . get_lang('Description updated') . '</p>';
                }
                else
                {
                    $eventNotifier->notifyCourseEvent('course_description_added', claro_get_current_course_id(), claro_get_current_tool_id(), $descId, claro_get_current_group_id(), '0');
                    $messageList['info'][] = '<p>' . get_lang('Description added') . '</p>';
                }
            }
            else
            {
                $messageList['info'][] = '<p>' . get_lang('Unable to update') . '</p>';
            }
        }
        else
        {
            $messageList['info'][] = '<p>' . get_lang('Check input') . '</p>';
            $cmd = 'rqEdit';
        }
    }

    /*-------------------------------------------------------------------------
        REQUEST DESCRIPTION ITEM EDITION
    -------------------------------------------------------------------------*/

    if ( $cmd == 'rqEdit' )
    {
        claro_set_display_mode_available(false);

        if ( isset($tipList[$category]['isEditable']) )  $tipIsTitleEditable = $tipList[$category]['isEditable'];
        else                                            $tipIsTitleEditable = true;

        if ( !empty($tipList[$category]['title']) )      $tipPresetTitle = $tipList[$category]['title'];
        else                                            $tipPresetTitle = '';

        if ( !empty($tipList[$category]['question']) )   $tipQuestion = $tipList[$category]['question'];
        else                                            $tipQuestion = '';

        if ( !empty($tipList[$category]['information']) )$tipInformation = $tipList[$category]['information'];
        else                                            $tipInformation = '';


        $displayForm = true;
    }

    /*-------------------------------------------------------------------------
        DELETE DESCRIPTION ITEM
    -------------------------------------------------------------------------*/
    if ( $cmd == 'exDelete' )
    {
        if ( $description->delete() )
        {
            $eventNotifier->notifyCourseEvent('course_description_deleted',claro_get_current_course_id(), claro_get_current_tool_id(), $descId, claro_get_current_group_id(), '0');
            $messageList['info'][] = '<p>' . get_lang("Description deleted.") . '</p>';
        }
        else
        {
            $messageList['info'][] = '<p>' . get_lang("Unable to delete") . '</p>';
        }
    }


    /*-------------------------------------------------------------------------
        EDIT  VISIBILITY DESCRIPTION ITEM
    -------------------------------------------------------------------------*/
    if ( $cmd == 'mkVis' )
    {
        $description->setVisibility('VISIBLE');

        if ( $description->save() )
        {
            $eventNotifier->notifyCourseEvent('course_description_visible',claro_get_current_course_id(), claro_get_current_tool_id(), $descId, claro_get_current_group_id(), '0');
        }
    }

    if ( $cmd == 'mkInvis' )
    {
        $description->setVisibility('INVISIBLE');

        $description->save();
    }

}

/*---------------------------------------------------------------------------*/



/*
 * Load the description elements
 */

$descList = course_description_get_item_list();

/*
 * Output
 */

$nameTools = get_lang('Course description');

$noQUERY_STRING = true; // to remove parameters in the last breadcrumb link

include get_path('incRepositorySys') . '/claro_init_header.inc.php';

echo claro_html_tool_title($nameTools);

//-- dialogBox
echo claro_html_msg_list($messageList);

if ( $is_allowedToEdit )
{
    /**************************************************************************
    EDIT FORM DISPLAY
    **************************************************************************/

    if ( isset($displayForm) && $displayForm )
    {
        echo '<form  method="post" action="' . $_SERVER['PHP_SELF'] . '">' . "\n"
        .    claro_form_relay_context() . "\n"
        .    '<input type="hidden" name="cmd" value="exEdit" />' . "\n"
        .    '<input type="hidden" name="claroFormId" value="' . uniqid('') . '" />' . "\n";

        if ( !is_null($descId) )
        {
            echo '<input type="hidden" name="descId" value="' . $descId . '" />' . "\n"
            .    '<input type="hidden" name="descCategory" value="' . $description->getCategory() . '" />' . "\n";
        }
        else
        {
             echo '<input type="hidden" name="descCategory" value="' . $category . '" />' . "\n";
        }

        echo "\n" . '<table border="0">' . "\n"
        .    '<tr>' . "\n"
        .    '<td colspan="2">' . "\n\n"

        .    '<p>' . "\n"
        .    '<label for="descTitle">' . "\n"
        .    '<b>' . get_lang('Title') . ' : </b>' . "\n"
        .    '</label>' . "\n"
        .    '</p>' . "\n"

        .    '<p>' . "\n";

        if ( $tipIsTitleEditable )
        {
            echo '<input type="text" name="descTitle" id="descTitle" size="50" value="' . htmlspecialchars($description->getTitle()) . '" />' . "\n";
        }
        else
        {
            echo htmlspecialchars($tipPresetTitle) . "\n"
            .    '<input type="hidden" name="descTitle" value="'. htmlspecialchars($tipPresetTitle) .'" />' . "\n";
        }

        echo '</p>' . "\n\n"

        .    '<p>' . "\n"
        .    '<label for="descContent">' . "\n"
        .    '<b>' . get_lang('Content') . ' : </b>' . "\n"
        .    '</label>' . "\n"
        .    '</p>' . "\n\n"

        .    '</td>' . "\n"
        .    '</tr>' . "\n"

        .    '<tr>' . "\n"
        .    '<td>'."\n"
        .    claro_html_textarea_editor('descContent', $description->getContent(), 20, 80 )."\n"

        .    '<p>' . "\n"
        .    '<input type="submit" name="save" value="' . get_lang('Ok') . '" />&nbsp; ' . "\n"
        .    claro_html_button($_SERVER['PHP_SELF'], get_lang('Cancel'))
        .    '</p>' . "\n"

        .    '</td>'  . "\n"

        .    '<td valign="top">' . "\n"
        ;

        if ( !empty($tipQuestion) )
        {
            echo "\n" . '<h4>' . get_lang("Question to lecturer") . '</h4>' . "\n"
            .    '<p>' . $tipQuestion . '</p>' . "\n\n"
            ;
        }

        if ( !empty($tipInformation) )
        {
            echo "\n" . '<h4>' . get_lang("Information to give to students") . '</h4>' . "\n"
            .    '<p>' . $tipInformation . '</p>' . "\n\n"
            ;
        }


        echo '</td>' . "\n"
        .    '</tr>'   . "\n"
        .    '</table>'. "\n"
        .    '</form>' . "\n"
        ;

    } // end if display form
    else
    {

        /**************************************************************************
        ADD FORM DISPLAY
        **************************************************************************/

        echo "\n\n"
        .    '<br />' . "\n"
        .    '<form method="post" action="' . $_SERVER['PHP_SELF'] . '">' . "\n"
        .    claro_form_relay_context()
        .    '<input type="hidden" name="cmd" value="rqEdit" />' . "\n"
        .    '<select name="category">' . "\n"
        ;

        if ( is_array($tipList) && !empty($tipList) )
        {
            foreach ( $tipList as $key => $tip )
            {
                $alreadyUsed = false;
                foreach ( $descList as $thisDesc )
                {
                    if ( $thisDesc['category'] == $key )
                    {
                        $alreadyUsed = true;
                        break;
                    }
                }

                if ( ($alreadyUsed) == false)
                {
                    echo '<option value="' . $key . '">' . htmlspecialchars($tip['title']) . '</option>' . "\n";
                }
            }
        }

        echo '<option value="-1">' . get_lang("Other") . '</option>' . "\n"
        .    '</select>' . "\n"
        .    '<input type="submit" name="add" value="' . get_lang('Add') . '" />' . "\n"
        .    '</form>' . "\n"
        .    '<br />' . "\n"
        ;
    }
} // end if is_allowedToEdit

/******************************************************************************
DESCRIPTION LIST DISPLAY
******************************************************************************/
$hasDisplayedItems = false;

if ( count($descList) )
{
    if (claro_is_user_authenticated()) $date = $claro_notifier->get_notification_date(claro_get_current_user_id());

    echo '<table class="claroTable" width="100%">' . "\n";

    foreach ( $descList as $thisDesc )
    {
        //modify style if the file is recently added since last login
        if (claro_is_user_authenticated() && $claro_notifier->is_a_notified_ressource(claro_get_current_course_id(), $date, claro_get_current_user_id(), claro_get_current_group_id(), claro_get_current_tool_id(), $thisDesc['id']))
        {
            $cssItem = 'item hot';
        }
        else
        {
            $cssItem = 'item';
        }

        if (($thisDesc['visibility'] == 'INVISIBLE' && $is_allowedToEdit) || $thisDesc['visibility'] == 'VISIBLE')
        {
            $cssInvisible = '';
            if ($thisDesc['visibility'] == 'INVISIBLE')
            {
                $cssInvisible = ' invisible';
            }

            echo '<tr class="superHeader">'
            .    '<th>'
            .    '<span class="'. $cssItem . $cssInvisible .'">';

            if ( trim($thisDesc['title']) == '' )
                echo '&nbsp;';
            else
                echo htmlspecialchars($thisDesc['title']);

            echo '</span>'
            .    '</th>'
            .    '</tr>' . "\n"
            .    '<tr>'
            .    '<td>'
            .    '<div '. ( !empty($cssInvisible) ? 'class="'.$cssInvisible.'"' : '' ) .'>' . "\n"
            .    claro_parse_user_text($thisDesc['content'])
            .    '</div>';

            $hasDisplayedItems = true;

            if ( $is_allowedToEdit )
            {
                echo '<p>' . "\n"
                // edit
                .    '<a href="' . $_SERVER['PHP_SELF'] . '?cmd=rqEdit&amp;descId=' . $thisDesc['id'] . '">'
                .    '<img src="' . get_path('imgRepositoryWeb') . 'edit.gif" alt="' . get_lang('Modify') . '" />'
                .    '</a>' . "\n"
                // delete
                .    '<a href="' . $_SERVER['PHP_SELF'] . '?cmd=exDelete&amp;descId=' . $thisDesc['id'] . '"'
                .    ' onClick="if (!confirm(\'' . clean_str_for_javascript(get_lang('Are you sure to delete'))
                .    ' : ' . $thisDesc['title'] . ' ?\')){ return false}">'
                .    '<img src="' . get_path('imgRepositoryWeb') . 'delete.gif" alt="' . get_lang('Delete') . '" />'
                .    '</a>' . "\n";

                // visibility
                if ($thisDesc['visibility'] == 'VISIBLE')
                {
                    echo '<a href="' . $_SERVER['PHP_SELF'] . '?cmd=mkInvis&amp;descId=' . $thisDesc['id'] . '">'
                    .    '<img src="' . get_path('imgRepositoryWeb') . 'visible.gif" alt="' . get_lang('Invisible') . '" />'
                    .    '</a>' . "\n";
                }
                else
                {
                    echo '<a href="' . $_SERVER['PHP_SELF'] . '?cmd=mkVis&amp;descId=' . $thisDesc['id'] . '">'
                    .    '<img src="' . get_path('imgRepositoryWeb') . 'invisible.gif" alt="' . get_lang('Visible') . '" />'
                    .    '</a>' . "\n";
                }

                echo '</p>' . "\n";
            }

            echo '</td>'
            .    '</tr>' . "\n" . "\n";
        }

    }
    echo '</table>'."\n\n";
}

if ( !$hasDisplayedItems )
{
    echo "\n" . '<p>' . get_lang("This course is currently not described") . '</p>' . "\n";
}

include get_path('incRepositorySys') . '/claro_init_footer.inc.php';

?>