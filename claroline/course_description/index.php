<?php // $Id$

/**
 * CLAROLINE
 *
 * This page displays the course's description to the user.
 *
 * @version     $Revision$
 * @copyright   (c) 2001-2011, Universite catholique de Louvain (UCL)
 * @license     http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @see         http://www.claroline.net/wiki/CLDSC/
 * @author      Claro Team <cvs@claroline.net>
 * @package     CLDSC
 * @since       1.9
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

/*
 * init request vars
 */
$acceptedCmdList = array('rqEdit', 'exEdit', 'exDelete', 'mkVis','mkInvis');

if ( isset($_REQUEST['cmd']) && in_array($_REQUEST['cmd'], $acceptedCmdList) )
{
    $cmd = $_REQUEST['cmd'];
}
else
{
    $cmd = null;
}

if ( isset($_REQUEST['descId']) && is_numeric($_REQUEST['descId']) )
{
    $descId = (int) $_REQUEST['descId'];
}
else
{
    $descId = null;
}

if ( isset($_REQUEST['category']) && $_REQUEST['category'] >= 0 )
{
    $category = $_REQUEST['category'];
}
else
{
    $category = -1;
}

/*
 * init other vars
 */
$dialogBox = new DialogBox();

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
        if ( isset($_REQUEST['descTitle']) )
        {
            $description->setTitle($_REQUEST['descTitle']);
        }
        
        if ( isset($_REQUEST['descContent']) )
        {
            $description->setContent($_REQUEST['descContent']);
        }
        
        if ( isset($_REQUEST['descCategory']) )
        {
            $description->setCategory($_REQUEST['descCategory']);
        }
        
        if ( $description->validate() )
        {
            // Update description
            if ( $description->save() )
            {
                if ( $descId )
                {
                    $eventNotifier->notifyCourseEvent('course_description_modified', claro_get_current_course_id(), claro_get_current_tool_id(), $descId, claro_get_current_group_id(), '0');
                    $dialogBox->success( get_lang('Description updated') );
                }
                else
                {
                    $eventNotifier->notifyCourseEvent('course_description_added', claro_get_current_course_id(), claro_get_current_tool_id(), $descId, claro_get_current_group_id(), '0');
                    $dialogBox->success( get_lang('Description added') );
                }
            }
            else
            {
                $dialogBox->error( get_lang('Unable to update') );
            }
        }
        else
        {
            // $dialogBox->error( get_lang('Unkown problem') );
            $cmd = 'rqEdit';
        }
    }
    
    
    /*-------------------------------------------------------------------------
        REQUEST DESCRIPTION ITEM EDITION
    -------------------------------------------------------------------------*/
    
    if ( $cmd == 'rqEdit' )
    {
        claro_set_display_mode_available(false);
        
        // Manage the tips
        $tips['isTitleEditable']    = isset($tipList[$category]['isEditable']) ? $tipList[$category]['isEditable'] : true;
        $tips['presetTitle']        = !empty($tipList[$category]['title']) ? htmlspecialchars($tipList[$category]['title']) : '';
        $tips['question']           = !empty($tipList[$category]['question']) ? $tipList[$category]['question'] : '';
        $tips['information']        = !empty($tipList[$category]['information']) ? $tipList[$category]['information'] : '';
        
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
            $dialogBox->success( get_lang("Description deleted.") );
        }
        else
        {
            $dialogBox->error( get_lang("Unable to delete") );
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



/*
 * Load the description elements
 */

$descList = course_description_get_item_list();

/*
 * Output
 */

$nameTools = get_lang('Course description');

$noQUERY_STRING = true; // to remove parameters in the last breadcrumb link

// include get_path('incRepositorySys') . '/claro_init_header.inc.php';

$out = '';

$out .= claro_html_tool_title($nameTools);

//-- dialogBox
$out .= $dialogBox->render();

if ( $is_allowedToEdit )
{
    /**************************************************************************
    EDIT FORM DISPLAY
    **************************************************************************/
    
    if ( isset($displayForm) && $displayForm )
    {
        $template = new CoreTemplate('course_description_form.tpl.php');
        $template->assign('formAction', htmlspecialchars($_SERVER['PHP_SELF']));
        $template->assign('relayContext', claro_form_relay_context());
        $template->assign('descId', (int) $descId);
        $template->assign('category', $category);
        $template->assign('tips', $tips);
        $template->assign('description', $description);
        
        $out .= $template->render();
    } // end if display form
    else
    {
        /**************************************************************************
        ADD FORM DISPLAY
        **************************************************************************/
        
        $out .= "\n\n"
              . '<br />' . "\n"
              . '<form method="post" action="' . htmlspecialchars( $_SERVER['PHP_SELF'] ) . '">' . "\n"
              . claro_form_relay_context()
              . '<input type="hidden" name="cmd" value="rqEdit" />' . "\n"
              . '<select name="category">' . "\n";
        
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
                    $out .= '<option value="' . $key . '">' . htmlspecialchars($tip['title']) . '</option>' . "\n";
                }
            }
        }
        
        $out .= '<option value="-1">' . get_lang("Other") . '</option>' . "\n"
              . '</select>' . "\n"
              . '<input type="submit" name="add" value="' . get_lang('Add') . '" />' . "\n"
              . '</form>' . "\n"
              . '<br />' . "\n";
    }
} // end if is_allowedToEdit


/******************************************************************************
DESCRIPTION LIST DISPLAY
******************************************************************************/

$hasDisplayedItems = false;

if ( count($descList) )
{
    if (claro_is_user_authenticated()) $date = $claro_notifier->get_notification_date(claro_get_current_user_id());

    foreach ( $descList as $thisDesc )
    {
        if (($thisDesc['visibility'] == 'INVISIBLE' && $is_allowedToEdit) || $thisDesc['visibility'] == 'VISIBLE')
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
            
            $cssInvisible = '';
            if ($thisDesc['visibility'] == 'INVISIBLE')
            {
                $cssInvisible = ' invisible';
            }
            
            $out .= '<div class="claroBlock">' . "\n"
            .   '<h3 class="blockHeader">'
            .   '<span class="'. $cssItem . $cssInvisible .'">' . "\n"
            .   (!empty($thisDesc['title'])?htmlspecialchars($thisDesc['title']):'')
            .   '</span>' . "\n"
            .   '</h3>' . "\n"
            
            .   '<div class="claroBlockContent">' . "\n"
            .   '<a href="#" name="ann' . $thisDesc['id'] . '"></a>'. "\n"
            
            .   '<div class="' . $cssInvisible . '">' . "\n"
            .   claro_parse_user_text($thisDesc['content']) . "\n"
            .   '</div>' . "\n";
            
            $hasDisplayedItems = true;
            
            if ( $is_allowedToEdit )
            {
                $out .= '<div class="claroBlockCmd">' . "\n"
                // edit
                .    '<a href="' . htmlspecialchars(Url::Contextualize( $_SERVER['PHP_SELF'] . '?cmd=rqEdit&amp;descId=' . (int) $thisDesc['id'] )) . '">'
                .    '<img src="' . get_icon_url('edit') . '" alt="' . get_lang('Modify') . '" />'
                .    '</a>' . "\n"
                // delete
                .    '<a href="' . htmlspecialchars(Url::Contextualize( $_SERVER['PHP_SELF'] . '?cmd=exDelete&amp;descId=' . (int) $thisDesc['id'] )) . '"'
                .    ' onclick="javascript:if(!confirm(\'' . clean_str_for_javascript(get_lang('Are you sure to delete "%title" ?', array('%title' => $thisDesc['title']))) . '\')) return false;">'
                .    '<img src="' . get_icon_url('delete') . '" alt="' . get_lang('Delete') . '" />'
                .    '</a>' . "\n";
                
                // visibility
                if ($thisDesc['visibility'] == 'VISIBLE')
                {
                    $out .= '<a href="' . htmlspecialchars(Url::Contextualize( $_SERVER['PHP_SELF'] . '?cmd=mkInvis&amp;descId=' . (int) $thisDesc['id'] )) . '">'
                    .    '<img src="' . get_icon_url('visible') . '" alt="' . get_lang('Invisible') . '" />'
                    .    '</a>' . "\n";
                }
                else
                {
                    $out .= '<a href="' . htmlspecialchars(Url::Contextualize( $_SERVER['PHP_SELF'] . '?cmd=mkVis&amp;descId=' . (int) $thisDesc['id'] )) . '">'
                    .    '<img src="' . get_icon_url('invisible') . '" alt="' . get_lang('Visible') . '" />'
                    .    '</a>' . "\n";
                }
                
                $out .= '</div>' . "\n"; // claroBlockCmd
            }
            
            $out .= '</div>' . "\n" // claroBlockContent
            .    '</div>' . "\n\n"; // claroBlock
        }
    }
}

if ( !$hasDisplayedItems )
{
    $out .= "\n" . '<p>' . get_lang("This course is currently not described") . '</p>' . "\n";
}

Claroline::getInstance()->display->setContent($out);

echo Claroline::getInstance()->display->render();