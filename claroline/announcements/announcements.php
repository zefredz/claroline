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
 * @copyright (c) 2001-2005 Universite catholique de Louvain (UCL)
 *
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
define('HIDE_LIST_WHEN_DISP_FORM', FALSE);

/**
 *  CLAROLINE MAIN SETTINGS
 */

$tlabelReq = 'CLANN___';

require '../inc/claro_init_global.inc.php';

if ( ! $_cid || ! $is_courseAllowed ) claro_disp_auth_form(true);

require_once $includePath . '/lib/announcement.lib.php';
require_once $includePath . '/lib/claro_mail.lib.inc.php';
require_once $clarolineRepositorySys . '/linker/linker.inc.php';
require_once $includePath . '/conf/rss.conf.php';

claro_set_display_mode_available(TRUE);

//set flag following init settings
$is_allowedToEdit = claro_is_allowed_to_edit();

$courseId         = $_course['sysCode'];
$userLastLogin    = $_user['lastLogin'];

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

/** 
 *                    COMMANDS SECTION (COURSE MANAGER ONLY)
 */

$id  = isset($_REQUEST['id'])  ? (int) $_REQUEST['id']   : 0;
$cmd = isset($_REQUEST['cmd']) ? $cmd = $_REQUEST['cmd'] : '';


if($is_allowedToEdit) // check teacher status
{
    //------------------------
    //linker
    
    if ( !isset($_REQUEST['cmd']) )
    {
        linker_init_session();
    }
    
    if( $jpspanEnabled )
    {
           linker_set_local_crl( isset ($_REQUEST['id']) );
    }
   
    if( isset($_REQUEST['cmd']) 
          && ($_REQUEST['cmd'] == 'rqCreate' || $_REQUEST['cmd'] == 'rqEdit')  )
    {
        linker_html_head_xtra();
    }
    //linker
    //------------------------

    $ex_rss_refresh = FALSE;
    if ( !empty($cmd) )
    {
        /**
         * MOVE UP AND MOVE DOWN COMMANDS
         */
        if ( $cmd == 'exMvDown' )
        {
            move_entry($id,'DOWN');
        }
        if ( $cmd == 'exMvUp' )
        {
            move_entry($id,'UP');
        }


        /**
         * DELETE ANNOUNCEMENT COMMAND
         */
        if ( $cmd == 'exDelete')
        {
            
            if ( announcement_delete_item($id) )
            {
                $message = get_lang('AnnDel');
                if ( CONFVAL_LOG_ANNOUNCEMENT_DELETE ) event_default('ANNOUNCEMENT',array('DELETE_ENTRY'=>$id));
                $eventNotifier->notifyCourseEvent('anouncement_deleted', $_cid, $_tid, $id, $_gid, '0');
                $ex_rss_refresh = TRUE;
                
                linker_delete_resource();
            }
//          else
//          {
//              //error on delete
//              //claro_failure::set_failure('CLANN:announcement '.var_dump((int) $_REQUEST['id']).' can be delete '.mysql_error());
//          }
        }

        /**
         * DELETE ALL ANNOUNCEMENTS COMMAND
         */

        if ( $cmd == 'exDeleteAll' )
        {
            if ( announcement_delete_all_items() )
            {
                $message = get_lang('AnnEmpty');
                if ( CONFVAL_LOG_ANNOUNCEMENT_DELETE ) event_default('ANNOUNCEMENT',array ('DELETE_ENTRY' => 'ALL'));
                $ex_rss_refresh = TRUE;
                
                linker_delete_all_tool_resources();
            }
//          else
//          {
//              //error on delete
//              //claro_failure::set_failure('CLANN:announcement can delete all items '.mysql_error());
//          }
        }

        /**
         * EDIT ANNOUNCEMENT COMMAND
        --------------------------------------------------------------------------*/

        if ( $cmd == 'rqEdit' )
        {
            $subTitle = get_lang('ModifAnn');
            claro_set_display_mode_available(false);

            // RETRIEVE THE CONTENT OF THE ANNOUNCEMENT TO MODIFY
            $announcementToEdit = announcement_get_item($id);
            $displayForm = TRUE;
            $nextCommand = 'exEdit';

        }

        /*-------------------------------------------------------------------------
        EDIT ANNOUNCEMENT VISIBILITY
        ---------------------------------------------------------------------------*/


        if ($cmd == 'mkShow'|| $cmd == 'mkHide')
        {
            if ($cmd == 'mkShow')  
            {
                $eventNotifier->notifyCourseEvent('anouncement_visible', $_cid, $_tid, $id, $_gid, '0');
                $visibility = 'SHOW';
            }
            if ($cmd == 'mkHide')  
            {
                $eventNotifier->notifyCourseEvent('anouncement_invisible', $_cid, $_tid, $id, $_gid, '0');
                $visibility = 'HIDE';
            }

            if (announcement_set_item_visibility($id,$visibility))
            {
                $message = get_lang('ViMod');
            }
        }

        /*------------------------------------------------------------------------
        CREATE NEW ANNOUNCEMENT COMMAND
        ------------------------------------------------------------------------*/

        if ( $cmd == 'rqCreate')
        {
            $subTitle = get_lang('AddAnn');
            claro_set_display_mode_available(false);
            $displayForm = TRUE;
            $nextCommand = 'exCreate';
            $announcementToEdit=array();
        }

        /*------------------------------------------------------------------------
        SUBMIT ANNOUNCEMENT COMMAND
        -------------------------------------------------------------------------*/

        if ( $cmd == 'exCreate' || $cmd == 'exEdit')
        {

            $title       = isset($_REQUEST['title'])      ? trim($_REQUEST['title']) : '';
            $content     = isset($_REQUEST['newContent']) ? trim($_REQUEST['newContent']) : '';
            $emailOption = isset($_REQUEST['emailOption'])? (int) $_REQUEST['emailOption'] : 0;

            /* MODIFY ANNOUNCEMENT */

            if ( $cmd == 'exEdit' ) // there is an Id => the announcement already exists => udpate mode
            {
                
                if ( announcement_update_item((int) $_REQUEST['id'], $title, $content) )
                {
                    $message = get_lang('AnnModify');
                    $message .= linker_update();
                    $eventNotifier->notifyCourseEvent('anouncement_modified', $_cid, $_tid, $id, $_gid, '0');
                    if (CONFVAL_LOG_ANNOUNCEMENT_UPDATE)event_default('ANNOUNCEMENT', array ('UPDATE_ENTRY'=>$_REQUEST['id']));
                    $ex_rss_refresh = TRUE;
                }
//                else
//                {
//                    //error on UPDATE
//                    //claro_failure::set_failure('CLANN:announcement can be update '.mysql_error());
//                }
            }

            /* CREATE NEW ANNOUNCEMENT */

            elseif ($_REQUEST['cmd'] == 'exCreate')
            {
                // DETERMINE THE ORDER OF THE NEW ANNOUNCEMENT

                $insert_id = announcement_add_item($title,$content) ;
                if ( $insert_id )
                {
                    // notify that a new anouncement is present in this course
                    $eventNotifier->notifyCourseEvent('anouncement_added',$_cid, $_tid, $insert_id, $_gid, '0');
                    $message  = get_lang('AnnAdd');
                    $message .= linker_update();
                    if (CONFVAL_LOG_ANNOUNCEMENT_INSERT) event_default('ANNOUNCEMENT',array ('INSERT_ENTRY'=>$insert_id));
                    $ex_rss_refresh = TRUE;
                }
//                else
//                {
//                    //error on insert
//                    //claro_failure::set_failure('CLANN:announcement can be insert '.mysql_error());
//                }

            } // end elseif cmd == exCreate

            /* SEND EMAIL (OPTIONAL) */

            if ( $emailOption == 1 )
            {
                // sender name and email
                $courseSender =  $_user['firstName'] . ' ' . $_user['lastName'];

                // email subject
                $emailSubject = '[' . $siteName . ' - ' . $_course['officialCode'] . '] ';
                if ( !empty($title) ) $emailSubject .= $title ;
                else                  $emailSubject .= get_lang('ProfessorMessage');

                // email message
                $msgContent = $content;
                $msgContent = preg_replace('/<br( \/)?>/',"\n",$msgContent);

                $str_to_search = array('<p>','<li>','<ul>','<ol>','</li>','</ul>','</ol>');
                $str_to_replace = array("\n\n","\t* ","\n","\n","\n","\n","\n");                
                $msgContent = str_replace($str_to_search,$str_to_replace,$msgContent);

                // Transform string like this : click <a hre="http://www.claroline.net">here</a>
                // in string like that : click here [ http://www.claroline.net ]

                $msgContent = preg_replace('|< *a +href *= *["\']([^"\']+)["\'][^>]*>([^<]+)</a>|', '$2 [ $1 ]', $msgContent);                
                $msgContent = str_replace('  ',' ',$msgContent);
                $msgContent = unhtmlentities($msgContent);
                $msgContent = strip_tags($msgContent);                

                // attached resource
                $msgAttachement = linker_email_resource();

                $emailBody = $msgContent . "\n" .
                "\n" .
                '--' . "\n" .
                $msgAttachement . "\n" .
                $courseSender . "\n" .
                $_course['name'] . ' (' . $_course['categoryName'] . ')' . "\n" .
                $siteName . "\n";

                // Select students id list
                $sql = "SELECT u.user_id
                        FROM `" . $tbl_course_user . "` cu , `" . $tbl_user . "` u
                        WHERE code_cours='" . $courseId . "'
                        AND cu.user_id = u.user_id";
                $studentIdList = claro_sql_query_fetch_all($sql);

                // count
                $countEmail = (is_array($studentIdList)) ? sizeof($studentIdList) : 0;
                $countUnvalid = 0;
                $messageFailed = '';

                // send email one by one to avoid antispam
                foreach ( $studentIdList as $student )
                {
                    if (!claro_mail_user($student['user_id'], $emailBody, $emailSubject, $_user['mail'], $courseSender))
                    {
                        $messageFailed.= claro_failure::get_last_failure() . '<br />' . "\n";
                        $countUnvalid++;
                    }
                }
                $messageUnvalid = get_lang('On').' '.$countEmail.' '.get_lang('RegUser').', '.$countUnvalid.' '.get_lang('Unvalid');
                $message .= ' '.get_lang('EmailSent').'<br /><b>'.$messageUnvalid.'</b><br />';
                $message .= $messageFailed;

            }   // end if $emailOption==1
        }   // end if $submit Announcement

        // rss update
        if ( get_conf('enable_rss_in_course')
               && $ex_rss_refresh 
               && file_exists('./announcements.rssgen.inc.php')
           )
        {
            include('./announcements.rssgen.inc.php');
        }

    } // end if isset $_REQUEST['cmd']

} // end if is_allowedToEdit


if ($displayForm && HIDE_LIST_WHEN_DISP_FORM) $displayList = FALSE;
            
if ($displayList)
{
    // list
    $announcementList = announcement_get_item_list();
    $bottomAnnouncement = $announcementQty = count($announcementList);
    //stats
}

event_access_tool($_tid, $_courseTool['label']);

/**
 *  DISPLAY SECTION
 */


$nameTools = get_lang('Announcement');
$noQUERY_STRING = true;

// Add feed RSS in header
if ( get_conf('enable_rss_in_course') )
{
    $htmlHeadXtra[] = '<link rel="alternate" type="application/rss+xml" title="' . htmlspecialchars($_course['name'] . ' - ' . $siteName) . '"'
            .' href="' . $rootWeb . 'claroline/rss/?cidReq=' . $_cid . '" />';
}

// Display header
include $includePath . '/claro_init_header.inc.php' ;

/*----------------------------------------------------------------------------
TOOL TITLE
----------------------------------------------------------------------------*/

echo claro_disp_tool_title(array('mainTitle' => $nameTools, 'subTitle' => $subTitle));

/*----------------------------------------------------------------------------
ACTION MESSAGE
----------------------------------------------------------------------------*/

if ( !empty($message) )
{
    echo claro_disp_message_box($message);
}

/*----------------------------------------------------------------------------
MAIN COMMANDS LINE
----------------------------------------------------------------------------*/

$displayButtonLine = (bool) $is_allowedToEdit && ( empty($cmd) || $cmd != 'rqEdit' || $cmd != 'rqCreate' ) ;

if ( $displayButtonLine )
{
    echo '<p>' . "\n"
    .    '<a class="claroCmd" href="' . $_SERVER['PHP_SELF'] . '?cmd=rqCreate">'
    .    '<img src="' . $imgRepositoryWeb . 'announcement.gif" alt="" />'
    .    get_lang('AddAnn')
    .    '</a>' . "\n"
    .    ' | ' . "\n"
    .    '<a class="claroCmd" href="messages.php">'
    .    '<img src="' . $imgRepositoryWeb . 'email.gif" alt="" />'
    .    get_lang('MessageToSelectedUsers')
    .    '</a>' . "\n"
    .    ' | ' . "\n";
    if (($announcementQty > 0 ))
    {   
        echo '<a class="claroCmd" href="' . $_SERVER['PHP_SELF'] . '?cmd=exDeleteAll" '
        .    ' onclick="if (confirm(\'' . clean_str_for_javascript(get_lang('EmptyAnn')) . ' ?\')){return true;}else{return false;}">'
        .    '<img src="' . $imgRepositoryWeb . 'delete.gif" alt="" />'
        .    get_lang('EmptyAnn')
        .    '</a>' . "\n";
    }
    else
    {   
        echo '<span class="claroCmdDisabled" >'
        .    '<img src="' . $imgRepositoryWeb . 'delete.gif" alt="" />'
        .    get_lang('EmptyAnn')
        .    '</span>' . "\n";
    }
    echo '</p>' . "\n";
}



/*----------------------------------------------------------------------------
FORM TO FILL OR MODIFY AN ANNOUNCEMENT
----------------------------------------------------------------------------*/

if ( $displayForm )
{

    // DISPLAY ADD ANNOUNCEMENT COMMAND

    echo '<form method="post" action="' . $_SERVER['PHP_SELF'] . '">'."\n"
    .    '<input type="hidden" name="claroFormId" value="' . uniqid('') . '" />'
    .    '<input type="hidden" name="cmd" value="' . $nextCommand . '" />'
    .    (isset( $announcementToEdit['id'] ) 
         ? '<input type="hidden" name="id" value="' . $announcementToEdit['id'] . '" />' . "\n"
         : ''
         )
    .    '<table>'
    .    '<tr>'
    .    '<td valign="top"><label for="title">' . get_lang('Title') . ' : </label></td>'
    .    '<td>'
    .    '<input type="text" id="title" name="title" value = "'
    .    ( isset($announcementToEdit['title']) ? htmlspecialchars($announcementToEdit['title']) : '' )
    .    '" size="80" />'
    .    '</td>'
    .    '</tr>' . "\n"
    .    '<tr>'
    .    '<td valign="top">'
    .    '<label for="newContent">'
    .    'Content'
    .    ' : '
    .    '</label>'
    .    '</td>'
    .    '<td>'
    .     claro_disp_html_area('newContent', !empty($announcementToEdit) ? htmlspecialchars($announcementToEdit['content']) : '',12,67, $optAttrib=' wrap="virtual"')
    .    '</td>'
    .    '</tr>' . "\n"
    .    '<tr>' 
    .    '<td></td>'
    .    '<td>'
    .    '<input type=checkbox value="1" name="emailOption" id="emailOption" />'
    .    '<label for="emailOption">' . get_lang('EmailOption') . '</label><hr />' . "\n"
    ;

    //---------------------
    // linker

    if( $jpspanEnabled )
    {
        linker_set_local_crl( isset ($_REQUEST['id']) );
        linker_set_display();
        echo '<input type="submit" onClick="linker_confirm();" class="claroButton" name="submitEvent" value="' . get_lang('Ok') . '" />'."\n";
    }
    else // popup mode
    {
        if(isset($_REQUEST['id'])) linker_set_display($_REQUEST['id']);
        else                       linker_set_display();
        
        echo '<input type="submit" class="claroButton" name="submitEvent" value="' . get_lang('Ok') . '" />'."\n";
    }

    echo claro_disp_button ($_SERVER['PHP_SELF'], 'Cancel');

    echo '</td>'
    .    '<tr>' . "\n"
    .    '</table>'
    .    '</form>' . "\n"
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
        echo '<br /><blockquote>' . get_lang('NoAnnouncement') . '</blockquote>' . "\n";
    }

    echo '<table class="claroTable" width="100%">';
    
    if (isset($_uid)) $date = $claro_notifier->get_notification_date($_uid); //get notification date
    
    foreach ( $announcementList as $thisAnnouncement)
    {
        //modify style if the file is recently added since last login

        if (isset($_uid) && $claro_notifier->is_a_notified_ressource($_cid, $date, $_uid, $_gid, $_tid, $thisAnnouncement['id']))
        {
            $classItem=' hot';
        }
        else // otherwise just display its name normally
        {
            $classItem='';
        }
              
        if (($thisAnnouncement['visibility']=='HIDE' && $is_allowedToEdit) || $thisAnnouncement['visibility']=='SHOW')
        {
            $style = ($thisAnnouncement['visibility'] == 'HIDE') ? 'invisible' :'';
            $title = $thisAnnouncement['title'];
            
            $content = make_clickable(claro_parse_user_text($thisAnnouncement['content']));
            $last_post_date = $thisAnnouncement['time']; // post time format date de mysql

            $imageFile = 'announcement.gif';
            $altImg    = '';

            echo '<tr>'."\n"
            .    '<th class="headerX item'.$classItem.'">'."\n"
            .    '<a href="#" name="ann' . $thisAnnouncement['id'] . '"></a>'. "\n"
            .    '<img src="' . $imgRepositoryWeb . $imageFile . '" alt="' . $altImg . '" />' . "\n"
            .    get_lang('Publ')
            .    ' : ' . claro_disp_localised_date($dateFormatLong, strtotime($last_post_date))
            .    '</th>' . "\n"
            .    '</tr>' . "\n"
            .    '<tr>' . "\n"
            .    '<td>' . "\n"
            .    '<div class="content ' . $style . '">' . "\n"
            .    ($title ? '<p><strong>' . htmlspecialchars($title) . '</strong></p>' . "\n"
                 : ''
                 )
            .    claro_parse_user_text($content) . "\n"
            .    '</div>' . "\n"
            ;

            linker_display_resource();
        }
        if ($is_allowedToEdit)
        {
            echo '<p>'
            // EDIT Request LINK
            .    '<a href="' . $_SERVER['PHP_SELF'] . '?cmd=rqEdit&amp;id=' . $thisAnnouncement['id'] . '">'
            .    '<img src="' . $imgRepositoryWeb . 'edit.gif" alt="' . get_lang('Modify') . '" />'
            .    '</a>' . "\n"
            // DELETE  Request LINK
            .    '<a href="' . $_SERVER['PHP_SELF'] . '?cmd=exDelete&amp;id=' . $thisAnnouncement['id'] . '" '
            .    ' onclick="javascript:if(!confirm(\'' . clean_str_for_javascript(get_lang('ConfirmYourChoice')) . '\')) return false;">'
            .    '<img src="' . $imgRepositoryWeb . 'delete.gif" alt="' . get_lang('Delete') . '" border="0" />'
            .    '</a>' . "\n"
            ;

            // DISPLAY MOVE UP COMMAND only if it is not the top announcement

            if( $iterator != 1 )
            {
                // echo    "<a href=\"".$_SERVER['PHP_SELF']."?cmd=exMvUp&amp;id=",$thisAnnouncement['id'],"#ann",$thisAnnouncement['id'],"\">",
                // the anchor dont refreshpage.
                echo '<a href="' . $_SERVER['PHP_SELF'] . '?cmd=exMvUp&amp;id=' . $thisAnnouncement['id'] . '">'
                .    '<img src="' . $imgRepositoryWeb . 'up.gif" alt="' . get_lang('OrderUp') . '" />'
                .    '</a>' . "\n"
                ;
            }

            // DISPLAY MOVE DOWN COMMAND only if it is not the bottom announcement

            if($iterator < $bottomAnnouncement)
            {
                // echo    "<a href=\"".$_SERVER['PHP_SELF']."?cmd=exMvDown&amp;id=",$thisAnnouncement['id'],"#ann",$thisAnnouncement['id'],"\">",
                // the anchor dont refreshpage.
                echo '<a href="' . $_SERVER['PHP_SELF'] . '?cmd=exMvDown&amp;id=' . $thisAnnouncement['id'] . '">'
                .    '<img src="' . $imgRepositoryWeb . 'down.gif" alt="' . get_lang('Down') . '" />'
                .    '</a>' . "\n"
                ;
            }

            //  Visibility
            if ($thisAnnouncement['visibility']=='SHOW')
            {
                echo '<a href="' . $_SERVER['PHP_SELF'] . '?cmd=mkHide&amp;id=' . $thisAnnouncement['id'] . '">'
                .    '<img src="' . $imgRepositoryWeb . 'visible.gif" alt="' . get_lang('Invisible').'" />'
                .    '</a>' . "\n"
                ;
            }
            else
            {
                echo '<a href="' . $_SERVER['PHP_SELF'] . '?cmd=mkShow&amp;id=' . $thisAnnouncement['id'] . '">'
                .    '<img src="' . $imgRepositoryWeb . 'invisible.gif" alt="' . get_lang('Visible') . '" />'
                .    '</a>' . "\n"
                ;
            }
            echo '</p>'."\n";

        } // end if is_AllowedToEdit

        echo '</td>' . "\n"
        .    '</tr>' . "\n"
        ;

        $iterator ++;
    }    // end foreach ( $announcementList as $thisAnnouncement)

    echo '</table>';

} // end if displayList

include $includePath . '/claro_init_footer.inc.php';
?>