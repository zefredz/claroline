<?php // $Id$
/**
 * CLAROLINE
 *
 * The script works with the 'annoucement' tables in the main claroline table
 *
 * DB Table structure:
 * ---
 *
 * id         : announcement id
 * contenu    : announcement content
 * temps      : date of the announcement introduction / modification
 * title      : optionnal title for an announcement
 * ordre      : order of the announcement display
 *              (the announcements are display in desc order)
 *
 * Script Structure:
 * ---
 *
 *        commands
 *            move up and down announcement
 *            delete announcement
 *            delete all announcements
 *            modify announcement
 *            submit announcement (new or modified)
 *
 *        display
 *            title
 *          button line
 *          form
 *            announcement list
 *            form to fill new or modified announcement
 *
 * @version 1.8 $Revision$
 *
 * @copyright (c) 2001-2007 Universite catholique de Louvain (UCL)
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @package CLANN
 *
 * @author Claro Team <cvs@claroline.net>
 */

/*
* Originally written  by Thomas Depraetere <depraetere@ipm.ucl.ac.be> 15 January 2002.
* Partially rewritten by Hugues Peeters <peeters@ipm.ucl.ac.be> 19 April 2002.
* Rewritten again     by Hugues Peeters <peeters@ipm.ucl.ac.be> 5 April 2004
*/
define('CONFVAL_LOG_ANNOUNCEMENT_INSERT', FALSE);
define('CONFVAL_LOG_ANNOUNCEMENT_DELETE', FALSE);
define('CONFVAL_LOG_ANNOUNCEMENT_UPDATE', FALSE);

/**
 *  CLAROLINE MAIN SETTINGS
 */

$tlabelReq = 'CLANN';
$gidReset = true;

require '../inc/claro_init_global.inc.php';

if ( ! claro_is_in_a_course()  || ! claro_is_course_allowed() ) claro_disp_auth_form(true);
$context = claro_get_current_context(CLARO_CONTEXT_COURSE);

// local lib
require_once './lib/announcement.lib.php';

// get some shared lib
require_once get_path('incRepositorySys') . '/lib/sendmail.lib.php';
// require_once get_path('clarolineRepositorySys') . '/linker/linker.inc.php';

FromKernel::uses('core/linker.lib');
ResourceLinker::init();

// get specific conf file
require claro_get_conf_repository() . 'ical.conf.php';
require claro_get_conf_repository() . 'rss.conf.php';

claro_set_display_mode_available(TRUE);

//set flag following init settings
$is_allowedToEdit = claro_is_allowed_to_edit();

$courseId         = claro_get_current_course_id();

$userLastLogin    = claro_get_current_user_data('lastLogin');

/**
 * DB tables definition
 */

$tbl_cdb_names   = claro_sql_get_main_tbl();
$tbl_course_user = $tbl_cdb_names['rel_course_user'];
$tbl_user        = $tbl_cdb_names['user'];

// DEFAULT DISPLAY

$displayForm = FALSE;
$displayList = TRUE;

$subTitle = '';

$dialogBox = new DialogBox();

/**
 *                    COMMANDS SECTION (COURSE MANAGER ONLY)
 */

$id  = isset($_REQUEST['id'])  ? (int) $_REQUEST['id']   : 0;
$cmd = isset($_REQUEST['cmd']) ? $cmd = $_REQUEST['cmd'] : '';
$cmdList=array();

if($is_allowedToEdit) // check teacher status
{
    if( isset($_REQUEST['cmd'])
          && ($_REQUEST['cmd'] == 'rqCreate' || $_REQUEST['cmd'] == 'rqEdit')  )
    {
        if ( 'rqEdit' == $_REQUEST['cmd'] )
        {
            $currentLocator = ResourceLinker::$Navigator->getCurrentLocator(
                    array( 'id' => (int) $_REQUEST['id'] ) );
            
            ResourceLinker::setCurrentLocator( $currentLocator );
        }
    }

    $autoExportRefresh = FALSE;
    if ( !empty($cmd) )
    {
        /**
         * MOVE UP AND MOVE DOWN COMMANDS
         */
        if ( 'exMvDown' == $cmd  )
        {
            move_entry($id,'DOWN');
        }
        if ( 'exMvUp' == $cmd )
        {
            move_entry($id,'UP');
        }


        /**
         * DELETE ANNOUNCEMENT COMMAND
         */
        if ( 'exDelete' == $cmd )
        {
            if ( announcement_delete_item($id) )
            {
                $dialogBox->success( get_lang('Announcement has been deleted') );

                if ( CONFVAL_LOG_ANNOUNCEMENT_DELETE ) $claroline->log('ANNOUNCEMENT',array('DELETE_ENTRY'=>$id));
                $eventNotifier->notifyCourseEvent('anouncement_deleted', claro_get_current_course_id(), claro_get_current_tool_id(), $id, claro_get_current_group_id(), '0');
                $autoExportRefresh = TRUE;

                // linker_delete_resource();
            }
            else
            {
                $dialogBox->error( get_lang('Cannot delete announcement') );
            }
        }

        /**
         * DELETE ALL ANNOUNCEMENTS COMMAND
         */

        if ( 'exDeleteAll' == $cmd )
        {
            $announcementList = announcement_get_item_list($context);
            if ( announcement_delete_all_items() )
            {
                $dialogBox->success( get_lang('Announcements list has been cleared up') );

                if ( CONFVAL_LOG_ANNOUNCEMENT_DELETE ) $claroline->log('ANNOUNCEMENT',array ('DELETE_ENTRY' => 'ALL'));
                $eventNotifier->notifyCourseEvent('all_anouncement_deleted', claro_get_current_course_id(), claro_get_current_tool_id(), $announcementList , claro_get_current_group_id(), '0');
                $autoExportRefresh = TRUE;

                // linker_delete_all_tool_resources();
            }
            else
            {
                $dialogBox->error( get_lang('Cannot delete announcement list') );
            }
        }

        /**
         * EDIT ANNOUNCEMENT COMMAND
        --------------------------------------------------------------------------*/

        if ( 'rqEdit' == $cmd  )
        {
            $subTitle = get_lang('Modifies this announcement');
            claro_set_display_mode_available(false);

            // RETRIEVE THE CONTENT OF THE ANNOUNCEMENT TO MODIFY
            $announcementToEdit = announcement_get_item($id);
            $displayForm = TRUE;
            $nextCommand = 'exEdit';

        }

        /*-------------------------------------------------------------------------
        EDIT ANNOUNCEMENT VISIBILITY
        ---------------------------------------------------------------------------*/


        if ( 'mkShow' == $cmd || 'mkHide' == $cmd )
        {
            if ('mkShow' == $cmd )
            {
                $eventNotifier->notifyCourseEvent('anouncement_visible', claro_get_current_course_id(), claro_get_current_tool_id(), $id, claro_get_current_group_id(), '0');
                $visibility = 'SHOW';
            }
            if ('mkHide' == $cmd )
            {
                $eventNotifier->notifyCourseEvent('anouncement_invisible', claro_get_current_course_id(), claro_get_current_tool_id(), $id, claro_get_current_group_id(), '0');
                $visibility = 'HIDE';
            }
            if (announcement_set_item_visibility($id,$visibility))
            {
                // $dialogBox->success( get_lang('Visibility modified') );
            }
            $autoExportRefresh = TRUE;
        }

        /*------------------------------------------------------------------------
        CREATE NEW ANNOUNCEMENT COMMAND
        ------------------------------------------------------------------------*/

        if ( 'rqCreate' == $cmd )
        {
            $subTitle = get_lang('Add announcement');
            claro_set_display_mode_available(false);
            $displayForm = TRUE;
            $nextCommand = 'exCreate';
            $announcementToEdit=array();
        }

        /*------------------------------------------------------------------------
        SUBMIT ANNOUNCEMENT COMMAND
        -------------------------------------------------------------------------*/

        if ( 'exCreate' == $cmd  || 'exEdit' == $cmd )
        {

            $title       = isset($_REQUEST['title'])      ? trim($_REQUEST['title']) : '';
            $content     = isset($_REQUEST['newContent']) ? trim($_REQUEST['newContent']) : '';
            $emailOption = isset($_REQUEST['emailOption'])? (int) $_REQUEST['emailOption'] : 0;

            /* MODIFY ANNOUNCEMENT */

            if ( 'exEdit' == $cmd  ) // there is an Id => the announcement already exists => udpate mode
            {

                if ( announcement_update_item((int) $_REQUEST['id'], $title, $content) )
                {
                    $dialogBox->success( get_lang('Announcement has been modified') );

                    $currentLocator = ResourceLinker::$Navigator->getCurrentLocator(
                        array( 'id' => (int) $_REQUEST['id'] ) );
                    
                    $resourceList =  isset($_REQUEST['resourceList'])
                        ? $_REQUEST['resourceList']
                        : array()
                        ;
                        
                    ResourceLinker::updateLinkList( $currentLocator, $resourceList );

                    $eventNotifier->notifyCourseEvent('anouncement_modified', claro_get_current_course_id(), claro_get_current_tool_id(), $id, claro_get_current_group_id(), '0');
                    if (CONFVAL_LOG_ANNOUNCEMENT_UPDATE) $claroling->log('ANNOUNCEMENT', array ('UPDATE_ENTRY'=>$_REQUEST['id']));
                    $autoExportRefresh = TRUE;
                }
            }

            /* CREATE NEW ANNOUNCEMENT */

            elseif ( 'exCreate' == $cmd )
            {
                // DETERMINE THE ORDER OF THE NEW ANNOUNCEMENT

                $insert_id = announcement_add_item($title,$content) ;
                if ( $insert_id )
                {
                    $dialogBox->success( get_lang('Announcement has been added') );

                    $currentLocator = ResourceLinker::$Navigator->getCurrentLocator(
                        array( 'id' => (int) $insert_id ) );
                    
                    $resourceList =  isset($_REQUEST['resourceList'])
                        ? $_REQUEST['resourceList']
                        : array()
                        ;
                        
                    ResourceLinker::updateLinkList( $currentLocator, $resourceList );

                    $eventNotifier->notifyCourseEvent('anouncement_added',claro_get_current_course_id(), claro_get_current_tool_id(), $insert_id, claro_get_current_group_id(), '0');
                    if (CONFVAL_LOG_ANNOUNCEMENT_INSERT) $claroline->log('ANNOUNCEMENT',array ('INSERT_ENTRY'=>$insert_id));
                    $autoExportRefresh = TRUE;
                }
//                else
//                {
//                    //error on insert
//                    //claro_failure::set_failure('CLANN:announcement can be insert '.mysql_error());
//                }

            } // end elseif cmd == exCreate

            /* SEND EMAIL (OPTIONAL) */

            if ( 1 == $emailOption )
            {
                $courseSender = claro_get_current_user_data('firstName') . ' ' . claro_get_current_user_data('lastName');
                
                $courseOfficialCode = claro_get_current_course_data('officialCode');
                
                $subject = '';
                if ( !empty($title) ) $subject .= $title ;
                else                  $subject .= get_lang('Message from your lecturer');
                
                $msgContent = $content;
                                               
                // attached resource
                $body = $msgContent . "\n" .
                    "\n" .
                    ResourceLinker::renderLinkList( $currentLocator, true );
                ;
              
                require_once dirname(__FILE__) . '/../messaging/lib/message/messagetosend.lib.php';
                require_once dirname(__FILE__) . '/../messaging/lib/recipient/courserecipient.lib.php';
                
                $courseRecipient = new CourseRecipient(claro_get_current_course_id());
                
                $message = new MessageToSend(claro_get_current_user_id(),$subject,$body);
                $message->setCourse(claro_get_current_course_id());
                $message->setTools('CLANN');
                
                $messageId = $courseRecipient->sendMessage($message);
                
                if ( $failure = claro_failure::get_last_failure() )
                {
                    $dialogBox->warning( $failure );
                }
                
            }   // end if $emailOption==1
        }   // end if $submit Announcement

        if ($autoExportRefresh)
        {
            /**
             * in future, the 2 following calls would be pas by event manager.
             */
            // rss update
            if ( get_conf('enableRssInCourse',1))
            {
                require_once get_path('incRepositorySys') . '/lib/rss.write.lib.php';
                build_rss( array('course' => claro_get_current_course_id()));
            }

            // ical update
            if (get_conf('enableICalInCourse', 1)  )
            {
                require_once get_path('incRepositorySys') . '/lib/ical.write.lib.php';
                buildICal( array('course' => claro_get_current_course_id()));
            }
        }

    } // end if isset $_REQUEST['cmd']

} // end if is_allowedToEdit


// PREPARE DISPLAYS
if ($displayList)
{
    // list
    $announcementList = announcement_get_item_list($context);
    $bottomAnnouncement = $announcementQty = count($announcementList);
}



$displayButtonLine = (bool) $is_allowedToEdit && ( empty($cmd) || $cmd != 'rqEdit' || $cmd != 'rqCreate' ) ;

if ( $displayButtonLine )
{
    $cmdList[] = '<a class="claroCmd" href="'
        . htmlspecialchars(Url::Contextualize( $_SERVER['PHP_SELF'] . '?cmd=rqCreate' )) . '">'
        . '<img src="' . get_icon_url('announcement_new') . '" alt="" />'
        . get_lang('Add announcement')
        . '</a>' . "\n"
        ;
    
    if ( claro_is_course_manager() )
    {
        $cmdList[] = '<a class="claroCmd" href="'
            . htmlspecialchars(Url::Contextualize( get_path('clarolineRepositoryWeb') . 'messaging/messagescourse.php?from=clann')) . '">'
            . '<img src="' . get_icon_url('mail_close') . '" alt="" />'
            . get_lang('Messages to selected users')
            . '</a>' . "\n"
            ;
    }
    
    if (($announcementQty > 0 ))
    {
        $cmdList[] = '<a class="claroCmd" href="'
            . htmlspecialchars(Url::Contextualize( $_SERVER['PHP_SELF'] . '?cmd=exDeleteAll' )) . '" '
            . ' onclick="if (confirm(\'' . clean_str_for_javascript(get_lang('Clear up list of announcements')) . ' ?\')){return true;}else{return false;}">'
            . '<img src="' . get_icon_url('delete') . '" alt="" />'
            . get_lang('Clear up list of announcements')
            . '</a>' . "\n"
            ;
    }
    else
    {
        $cmdList[] = '<span class="claroCmdDisabled" >'
            . '<img src="' . get_icon_url('delete') . '" alt="" />'
            . get_lang('Clear up list of announcements')
            . '</span>' . "\n"
            ;
    }

}

/**
 *  DISPLAY SECTION
 */


$nameTools = get_lang('Announcements');
$noQUERY_STRING = true;

$output = '';

if ( !empty( $subTitle ) )
{
    $output .= claro_html_tool_title(array('mainTitle' => $nameTools, 'subTitle' => $subTitle));
}
else
{
    $output .= claro_html_tool_title( $nameTools );
}

$output .= $dialogBox->render();

$output .= '<p>'
.    claro_html_menu_horizontal($cmdList)
.    '</p>'
;

/*----------------------------------------------------------------------------
FORM TO FILL OR MODIFY AN ANNOUNCEMENT
----------------------------------------------------------------------------*/

if ( $displayForm )
{

    // DISPLAY ADD ANNOUNCEMENT COMMAND

    $output .= '<form method="post" action="' . htmlspecialchars( $_SERVER['PHP_SELF'] ) . '">'."\n"
    .    claro_form_relay_context()
    .    '<input type="hidden" name="claroFormId" value="' . uniqid('') . '" />'
    .    '<input type="hidden" name="cmd" value="' . $nextCommand . '" />'
    .    (isset( $announcementToEdit['id'] )
         ? '<input type="hidden" name="id" value="' . $announcementToEdit['id'] . '" />' . "\n"
         : ''
         )
    .    '<table cellpadding="5">'
    .    '<tr>'
    .    '<td valign="top"><label for="title">' . get_lang('Title') . ' : </label></td>' . "\n"
    .    '<td>'
    .    '<input type="text" id="title" name="title" value = "'
    .    ( isset($announcementToEdit['title']) ? htmlspecialchars($announcementToEdit['title']) : '' )
    .    '" size="80" />'
    .    '</td>' . "\n"
    .    '</tr>' . "\n"
    .    '<tr>'
    .    '<td valign="top">'
    .    '<label for="newContent">'
    .    get_lang('Content')
    .    ' : '
    .    '</label>'
    .    '</td>' . "\n"
    .    '<td>'
    .     claro_html_textarea_editor('newContent', !empty($announcementToEdit) ? $announcementToEdit['content'] : '',12,67)
    .    '</td>' . "\n"
    .    '</tr>' . "\n"
    ;

   $output .= '<tr>'
    .    '<td>&nbsp;</td>' . "\n"
    .    '<td>'
    .    '<input type="checkbox" value="1" name="emailOption" id="emailOption" />'
    .    '<label for="emailOption">'
    .    get_lang('Send this announcement by internal message to registered students')
    .    '</label>' . "\n"
    .    '</td>' . "\n"
    .    '</tr>' . "\n"
    ;

    $output .= '<tr>'
    .    '<td>&nbsp;</td>' . "\n"
    .    '<td>' . "\n"
;

    //---------------------
    // linker

    if ( 'rqEdit' == $_REQUEST['cmd'] )
    {
        ResourceLinker::setCurrentLocator(
            ResourceLinker::$Navigator->getCurrentLocator(
                array( 'id' => (int) $_REQUEST['id'] ) ) );
    }
    
    $output .= ResourceLinker::renderLinkerBlock();

    $output .= '</td>' . "\n"
    .    '</tr>' . "\n"
    .    '<tr>'
    .    '<td>&nbsp;</td>' . "\n"
    .    '<td>' . "\n"
    ;

    $output .= '<input type="submit" class="claroButton" name="submitEvent" value="' . get_lang('Ok') . '" />'."\n";

    $output .= claro_html_button(htmlspecialchars(Url::Contextualize($_SERVER['PHP_SELF'])), get_lang('Cancel'))
        . '</td>'
        . '</tr>' . "\n"
        . '</table>'
        . '</form>' . "\n"
        ;
}


/**
 * ANNOUNCEMENT LIST
 */


if ($displayList)
{
    $iterator = 1;

    if ($announcementQty < 1)
    {
        $output .= '<br /><blockquote>' . get_lang('No announcement') . '</blockquote>' . "\n";
    }

    else
    {
        if (claro_is_user_authenticated()) $date = $claro_notifier->get_notification_date(claro_get_current_user_id()); //get notification date

        foreach ( $announcementList as $thisAnnouncement)
        {

            if (($thisAnnouncement['visibility']=='HIDE' && $is_allowedToEdit) || $thisAnnouncement['visibility'] == 'SHOW')
            {
                //modify style if the event is recently added since last login
                if (claro_is_user_authenticated() && $claro_notifier->is_a_notified_ressource(claro_get_current_course_id(), $date, claro_get_current_user_id(), claro_get_current_group_id(), claro_get_current_tool_id(), $thisAnnouncement['id']))
                {
                    $cssItem = 'item hot';
                }
                else
                {
                    $cssItem = 'item';
                }
                
                $cssInvisible = '';
                if ($thisAnnouncement['visibility'] == 'HIDE')
                {
                    $cssInvisible = ' invisible';
                }

                $title = $thisAnnouncement['title'];

                $content = make_clickable(claro_parse_user_text($thisAnnouncement['content']));
                $last_post_date = $thisAnnouncement['time']; // post time format date de mysql

                $output .= '<div class="claroBlock">' . "\n"
                .   '<h4 class="claroBlockHeader">'
                .   '<span class="'. $cssItem . $cssInvisible .'">' . "\n"
                .   '<img src="' . get_icon_url('announcement') . '" alt="" /> '
                .   get_lang('Published on')
                .   ' : ' . claro_html_localised_date( get_locale('dateFormatLong'), strtotime($last_post_date))
                .   '</span>' . "\n"
                .   '</h4>' . "\n"
                
                .   '<div class="claroBlockContent">' . "\n"
                .   '<a href="#" name="ann' . $thisAnnouncement['id'] . '"></a>'. "\n"

                .   '<div class="' . $cssInvisible . '">' . "\n"
                .   ($title ? '<p><strong>' . htmlspecialchars($title) . '</strong></p>' . "\n"
                    : ''
                    )
                .   claro_parse_user_text($content) . "\n"
                .   '</div>' . "\n"
                
                ;
                
                $currentLocator = ResourceLinker::$Navigator->getCurrentLocator( array('id' => $thisAnnouncement['id'] ) );
                $output .= ResourceLinker::renderLinkList( $currentLocator );

                if ($is_allowedToEdit)
                {
                    $output .= '<div class="claroBlockCmd">'
                        // EDIT Request LINK
                        . '<a href="'
                        . htmlspecialchars(Url::Contextualize( $_SERVER['PHP_SELF']
                            . '?cmd=rqEdit&amp;id=' . $thisAnnouncement['id'] ))
                        . '">'
                        . '<img src="' . get_icon_url('edit') . '" alt="'
                        . get_lang('Modify') . '" />'
                        . '</a>' . "\n"
                        // DELETE  Request LINK
                        . '<a href="'
                        . htmlspecialchars(Url::Contextualize( $_SERVER['PHP_SELF']
                            . '?cmd=exDelete&amp;id=' . $thisAnnouncement['id'] ))
                        . '" '
                        . ' onclick="javascript:if(!confirm(\'' . clean_str_for_javascript(get_lang('Please confirm your choice')) . '\')) return false;">'
                        . '<img src="' . get_icon_url('delete') . '" alt="' . get_lang('Delete') . '" />'
                        . '</a>' . "\n"
                        ;

                    // DISPLAY MOVE UP COMMAND only if it is not the top announcement

                    if( $iterator != 1 )
                    {
                        // $output .=    "<a href=\"".$_SERVER['PHP_SELF']."?cmd=exMvUp&amp;id=",$thisAnnouncement['id'],"#ann",$thisAnnouncement['id'],"\">",
                        // the anchor dont refreshpage.
                        $output .= '<a href="'. htmlspecialchars(Url::Contextualize( $_SERVER['PHP_SELF'] . '?cmd=exMvUp&amp;id=' . $thisAnnouncement['id'] )) . '">'
                            . '<img src="' . get_icon_url('move_up') . '" alt="' . get_lang('Move up') . '" />'
                            . '</a>' . "\n"
                            ;
                    }

                    // DISPLAY MOVE DOWN COMMAND only if it is not the bottom announcement

                    if($iterator < $bottomAnnouncement)
                    {
                        // $output .=    "<a href=\"".$_SERVER['PHP_SELF']."?cmd=exMvDown&amp;id=",$thisAnnouncement['id'],"#ann",$thisAnnouncement['id'],"\">",
                        // the anchor dont refreshpage.
                        $output .= '<a href="'
                            . htmlspecialchars(Url::Contextualize( $_SERVER['PHP_SELF'] . '?cmd=exMvDown&amp;id=' . $thisAnnouncement['id'] )) . '">'
                            . '<img src="' . get_icon_url('move_down') . '" alt="' . get_lang('Move down') . '" />'
                            . '</a>' . "\n"
                            ;
                    }

                    //  Visibility
                    if ($thisAnnouncement['visibility']=='SHOW')
                    {
                        $output .= '<a href="' . htmlspecialchars(Url::Contextualize( $_SERVER['PHP_SELF'] . '?cmd=mkHide&amp;id=' . $thisAnnouncement['id'] )) . '">'
                        .    '<img src="' . get_icon_url('visible') . '" alt="' . get_lang('Visible').'" />'
                        .    '</a>' . "\n"
                        ;
                    }
                    else
                    {
                        $output .= '<a href="' . htmlspecialchars(Url::Contextualize( $_SERVER['PHP_SELF'] . '?cmd=mkShow&amp;id=' . $thisAnnouncement['id'] )) . '">'
                        .    '<img src="' . get_icon_url('invisible') . '" alt="' . get_lang('Invisible') . '" />'
                        .    '</a>' . "\n"
                        ;
                    }
                    
                    $output .= '</div>' . "\n"; // claroBlockCmd

                } // end if is_AllowedToEdit
                
                $output .= '</div>' . "\n" // claroBlockContent
                .    '</div>' . "\n\n"; // claroBlock
            }

            $iterator ++;
        }    // end foreach ( $announcementList as $thisAnnouncement)
    }

} // end if displayList

Claroline::getDisplay()->body->appendContent( $output );

echo Claroline::getDisplay()->render();
