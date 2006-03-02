<?php // $Id$
/**
 * CLAROLINE
 *
 * - For a Student -> View agenda Content
 * - For a Prof    -> - View agenda Content
 *         - Update/delete existing entries
 *         - Add entries
 *         - generate an "announce" entries about an entries
 *
 * @version 1.8 $Revision$
 *
 * @copyright (c) 2001-2006 Universite catholique de Louvain (UCL)
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @package CLCAL
 *
 * @author Claro Team <cvs@claroline.net>
 */

$tlabelReq = 'CLCAL___';

require '../inc/claro_init_global.inc.php';
require_once $clarolineRepositorySys . '/linker/linker.inc.php';
require_once $includePath . '/lib/agenda.lib.php';
require_once $includePath . '/lib/form.lib.php';
require_once $includePath . '/conf/rss.conf.php';

define('CONFVAL_LOG_CALENDAR_INSERT', FALSE);
define('CONFVAL_LOG_CALENDAR_DELETE', FALSE);
define('CONFVAL_LOG_CALENDAR_UPDATE', FALSE);

if ( !$_cid || !$is_courseAllowed ) claro_disp_auth_form(true);

$nameTools = get_lang('Agenda');

claro_set_display_mode_available(TRUE);

$is_allowedToEdit = $is_courseAdmin;


if ( $is_allowedToEdit )
{
    if ( !isset($_REQUEST['cmd']) )
    {
        linker_init_session();
    }

    if( $jpspanEnabled )
    {
        linker_set_local_crl( isset ($_REQUEST['id']) );
    }

    if ( isset($_REQUEST['cmd'])
    && ( $_REQUEST['cmd'] == 'rqAdd' || $_REQUEST['cmd'] == 'rqEdit' )
    )
    {
        linker_html_head_xtra();
    }
}


//stats
event_access_tool($_tid, $_courseTool['label']);


$tbl_c_names = claro_sql_get_course_tbl();
$tbl_calendar_event = $tbl_c_names['calendar_event'];

if ( isset($_REQUEST['cmd']) ) $cmd = $_REQUEST['cmd'];
else                           $cmd = null;

$dialogBox = '';

if     ( $cmd == 'rqAdd' ) $subTitle = get_lang('Add an event');
elseif ( $cmd == 'rqEdit') $subTitle = get_lang('Edit Event');
else                       $subTitle = '&nbsp;';

$orderDirection = isset($_REQUEST['order']) && $_REQUEST['order'] == 'desc' ?'DESC':'ASC';

$is_allowedToEdit = claro_is_allowed_to_edit();
/**
 * COMMANDS SECTION
 */

$display_form = FALSE;
$display_command = FALSE;

if ( $is_allowedToEdit )
{
    if ( isset($_REQUEST['id']) ) $id = (int) $_REQUEST['id'];
    else                          $id = 0;

    if ( isset($_REQUEST['title']) ) $title = trim($_REQUEST['title']);
    else                             $title = '';

    if ( isset($_REQUEST['content']) ) $content = trim($_REQUEST['content']);
    else                               $content = '';

    $lasting = ( isset($_REQUEST['content']) ? trim($_REQUEST['lasting']) : '');

    $ex_rss_refresh = FALSE;
    if ( $cmd == 'exAdd' )
    {
        $date_selection = $_REQUEST['fyear'] . '-' . $_REQUEST['fmonth'] . '-' . $_REQUEST['fday'];
        $hour           = $_REQUEST['fhour'] . ':' . $_REQUEST['fminute'] . ':00';

        $insert_id = agenda_add_item($title,$content, $date_selection, $hour, $lasting) ;
        if ( $insert_id != false )
        {
            $dialogBox .= '<p>' . get_lang('Event added to the agenda') . '</p>' . "\n";
            $dialogBox .= linker_update(); //return textual error msg

            if ( CONFVAL_LOG_CALENDAR_INSERT )
            {
                event_default('CALENDAR', array ('ADD_ENTRY' => $entryId));
            }

            // notify that a new agenda event has been posted

            $eventNotifier->notifyCourseEvent('agenda_event_added', $_cid, $_tid, $insert_id, $_gid, '0');
            $ex_rss_refresh = TRUE;

        }
        else
        {
            $dialogBox .= '<p>' . get_lang('Unable to add the event to the agenda') . '</p>' . "\n";
        }
    }

    /*------------------------------------------------------------------------
    EDIT EVENT COMMAND
    --------------------------------------------------------------------------*/


    if ( $cmd == 'exEdit' )
    {
        $date_selection = $_REQUEST['fyear'] . '-' . $_REQUEST['fmonth'] . '-' . $_REQUEST['fday'];
        $hour           = $_REQUEST['fhour'] . ':' . $_REQUEST['fminute'] . ':00';

        if ( !empty($id) )
        {
            if ( agenda_update_item($id,$title,$content,$date_selection,$hour,$lasting))
            {
                $dialogBox .= linker_update(); //return textual error msg
                $eventNotifier->notifyCourseEvent('agenda_event_modified', $_cid, $_tid, $id, $_gid, '0'); // notify changes to event manager
                $ex_rss_refresh = TRUE;
                $dialogBox .= '<p>' . get_lang('Event updated into the agenda') . '</p>' . "\n";
            }
            else
            {
                $dialogBox .= '<p>' . get_lang('Unable to update the event into the agenda') . '</p>' . "\n";
            }
        }
    }

    /*------------------------------------------------------------------------
    DELETE EVENT COMMAND
    --------------------------------------------------------------------------*/

    if ( $cmd == 'exDelete' && !empty($id) )
    {

        if ( agenda_delete_item($id) )
        {
            $dialogBox .= '<p>' . get_lang('Event deleted from the agenda') . '</p>' . "\n";

            $eventNotifier->notifyCourseEvent('agenda_event_deleted', $_cid, $_tid, $id, $_gid, '0'); // notify changes to event manager
            $ex_rss_refresh = TRUE;
            if ( CONFVAL_LOG_CALENDAR_DELETE )
            {
                event_default('CALENDAR',array ('DELETE_ENTRY' => $id));
            }
        }
        else
        {
            $dialogBox = '<p>' . get_lang('Unable to delete event from the agenda') . '</p>' . "\n";
        }

        linker_delete_resource();
    }

    /*----------------------------------------------------------------------------
    DELETE ALL EVENTS COMMAND
    ----------------------------------------------------------------------------*/

    if ( $cmd == 'exDeleteAll' )
    {
        if ( agenda_delete_all_items())
        {
            $dialogBox .= '<p>' . get_lang('Event deleted from the agenda') . '</p>' . "\n";

            if ( CONFVAL_LOG_CALENDAR_DELETE )
            {
                event_default('CALENDAR', array ('DELETE_ENTRY' => 'ALL') );
            }
        }
        else
        {
            $dialogBox = '<p>' . get_lang('Unable to delete event from the agenda') . '</p>' . "\n";
        }

        linker_delete_all_tool_resources();
    }
    /*-------------------------------------------------------------------------
    EDIT EVENT VISIBILITY
    ---------------------------------------------------------------------------*/


    if ($cmd == 'mkShow' || $cmd == 'mkHide')
    {
        if ($cmd == 'mkShow')
        {
            $visibility = 'SHOW';
            $eventNotifier->notifyCourseEvent('agenda_event_visible', $_cid, $_tid, $id, $_gid, '0'); // notify changes to event manager
            $ex_rss_refresh = TRUE;
        }

        if ($cmd == 'mkHide')
        {
            $visibility = 'HIDE';
            $eventNotifier->notifyCourseEvent('agenda_event_invisible', $_cid, $_tid, $id, $_gid, '0'); // notify changes to event manager
            $ex_rss_refresh = TRUE;
        }

        if ( agenda_set_item_visibility($id, $visibility)  )
        {
            $dialogBox = get_lang('Visibility modified');
        }
        //        else
        //        {
        //            //error on delete
        //        }
    }

    /*------------------------------------------------------------------------
    EVENT EDIT
    --------------------------------------------------------------------------*/

    if ( $cmd == 'rqEdit' || $cmd == 'rqAdd' )
    {
        claro_set_display_mode_available(false);

        if ( $cmd == 'rqEdit' && !empty($id) )
        {
            $editedEvent = agenda_get_item($id) ;
            $nextCommand = 'exEdit';
        }
        else
        {
            $editedEvent['id'            ] = '';
            $editedEvent['title'         ] = '';
            $editedEvent['content'       ] = '';
            $editedEvent['dayAncient'    ] = FALSE;
            $editedEvent['hourAncient'   ] = FALSE;
            $editedEvent['lastingAncient'] = FALSE;

            $nextCommand = 'exAdd';

        }
        $display_form =TRUE;
    } // end if cmd == 'rqEdit' && cmd == 'rqAdd'


    if ($cmd != 'rqEdit' && $cmd != 'rqAdd') // display main commands only if we're not in the event form
    {
        $display_command = TRUE;
    } // end if diplayMainCommands

    // rss update
    if ( get_conf('enable_rss_in_course')
    && $ex_rss_refresh && file_exists('./agenda.rssgen.inc.php')
    )
    {
        include './agenda.rssgen.inc.php';
    }

} // end id is_allowed to edit

/**
 *     DISPLAY SECTION
 *
 */

$noQUERY_STRING = true;

// Add feed RSS in header
if ( get_conf('enable_rss_in_course') )
{
    $htmlHeadXtra[] = '<link rel="alternate" type="application/rss+xml" title="' . htmlspecialchars($_course['name'] . ' - ' . $siteName) . '"'
    .' href="' . $rootWeb . 'claroline/rss/?cidReq=' . $_cid . '" />';
}

$eventList = agenda_get_item_list($orderDirection);

/**
     * Add event button
         */

$cmd_menu[]=  '<a class="claroCmd" href="' . $_SERVER['PHP_SELF'] . '?cmd=rqAdd">'
.    '<img src="' . $imgRepositoryWeb . 'agenda.gif" alt="" />'
.    get_lang('Add an event')
.    '</a>'
;

/*
* remove all event button
*/
if ( count($eventList) > 0 )
{
    $cmd_menu[]=  '<a class= "claroCmd" href="' . $_SERVER['PHP_SELF'] . '?cmd=exDeleteAll" '
    .    ' onclick="if (confirm(\'' . clean_str_for_javascript(get_lang('Clear up event list')) . ' ? \')){return true;}else{return false;}">'
    .    '<img src="' . $imgRepositoryWeb . 'delete.gif" alt="" />'
    .    get_lang('Clear up event list')
    .    '</a>'
    ;
}
else
{
    $cmd_menu[]=  '<span class="claroCmdDisabled" >'
    .    '<img src="' . $imgRepositoryWeb . 'delete.gif" alt="" />'
    .    get_lang('Clear up event list')
    .    '</span>'
    ;
}




// Display header
include $includePath . '/claro_init_header.inc.php';

echo claro_html::tool_title(array('mainTitle' => $nameTools, 'subTitle' => $subTitle));

if ( !empty($dialogBox) ) echo claro_html::message_box($dialogBox);


if ($display_form)
{

    $date = date('Y-m-d', mktime( 0,0,0,date('m'), date('d'), date('Y') ) );
    $time = date('H:i:00', mktime( date('H'),date('i'),0) );
    if ($editedEvent['hourAncient']) $time = $editedEvent['hourAncient'];
    if ($editedEvent['dayAncient']) $date = $editedEvent['dayAncient'];

    $title   = $editedEvent['title'];
    $content = $editedEvent['content'];

    echo '<form method="post" action="' . $_SERVER['PHP_SELF'] . '">'
    .    '<input type="hidden" name="claroFormId" value="' . uniqid('') . '" />'
    .    '<input type="hidden" name="cmd" value="' . $nextCommand . '" />'
    .    '<input type="hidden" name="id"  value="' . $editedEvent['id'] . '" />'
    .    '<table>' . "\n"
    .    '<tr>' . "\n"
    .    '<td>&nbsp;</td>' . "\n"
    .    '<td>' . "\n"
    .    '<tr valign="top">' . "\n"
    .    '<td align="right">' . get_lang('Date') . ' : '
    .    '</td>' . "\n"
    .    '<td>'
    .    claro_disp_date_form('fday', 'fmonth', 'fyear', $date, 'long' ) . ' '
    .    claro_disp_time_form('fhour','fminute', $time) . '&nbsp;'
    .    '<small>' . get_lang('(d/m/y hh:mm)') . '</small>'
    .    '</td>' . "\n"
    .    '</tr>' . "\n"
    .    '<tr>' . "\n"
    .    '<td align="right">'
    .    '<label for="lasting">' . get_lang('Lasting') . '</label> : '
    .    '</td>' . "\n"
    .    '<td>'
    .    '<input type="text" name="lasting" id="lasting" size="20" maxlength="20" value="' . htmlspecialchars($editedEvent['lastingAncient']) . '" />'
    .    '</td>' . "\n"
    .    '</tr>' . "\n"
    .    '<tr valign="top">' . "\n"
    .    '<td align="right">' . "\n"
    .    '<label for="title">' . "\n"
    .    get_lang('Title') . "\n"
    .    ' : </label>' . "\n"
    .    '</td>' . "\n"
    .    '<td>' . "\n"
    .    '<input size="80" type="text" name="title" id="title" value="'
    .    (isset($title) ? htmlspecialchars($title) : ''). '" />' . "\n"
    .    '</td>' . "\n"
    .    '</tr>' . "\n"
    .    '<tr valign="top">' . "\n"
    .    '<td align="right">' . "\n"
    .    '<label for="content">' . "\n"
    .    get_lang('Detail')
    .    ' : ' . "\n"
    .    '</label>' . "\n"
    .    '</td>' . "\n"
    .    '<td>' . "\n"
    .    claro_disp_html_area('content', htmlspecialchars($content), 12, 67, $optAttrib = ' wrap="virtual" ') . "\n"
    .    '<br />' . "\n"
    .    '</td>' . "\n"
    .    '</tr>' . "\n"
    .    '<tr valign="top">' . "\n"
    .    '<td>&nbsp;</td>' . "\n"
    .    '<td>' . "\n"
    ;


    //---------------------
    // linker

    if( $jpspanEnabled )
    {
        linker_set_local_crl( isset ($_REQUEST['id']) );
        linker_set_display();
    }
    else // popup mode
    {
        if(isset($_REQUEST['id'])) linker_set_display($_REQUEST['id']);
        else                       linker_set_display();
    }

    echo '</td></tr>' . "\n"
    .    '<tr valign="top"><td>&nbsp;</td><td>' . "\n"
    ;

    if( $jpspanEnabled )
    {
        echo '<input type="submit" onClick="linker_confirm();"  class="claroButton" name="submitEvent" value="' . get_lang('Ok') . '" />' . "\n";
    }
    else // popup mode
    {
        echo '<input type="submit" class="claroButton" name="submitEvent" value="' . get_lang('Ok') . '" />' . "\n";
    }

    // linker
    //---------------------
    echo claro_html::button($_SERVER['PHP_SELF'], 'Cancel') . "\n"
    .    '</td>' . "\n"
    .    '</tr>' . "\n"
    .    '</table>' . "\n"
    .    '</form>' . "\n"
    ;
}

if ( $display_command ) echo '<p>' . claro_html::menu_horizontal($cmd_menu) . '</p>';

$monthBar     = '';

if ( count($eventList) < 1 )
{
    echo "\n" . '<br /><blockquote>' . get_lang('No event in the agenda') . '</blockquote>' . "\n";
}
else
{
    if ( $orderDirection == 'DESC' )
    {
        echo '<a href="' . $_SERVER['PHP_SELF'] . '?order=asc" >' . get_lang('Oldest first') . '</a>' . "\n";
    }
    else
    {
        echo '<a href="' . $_SERVER['PHP_SELF'] . '?order=desc" >' . get_lang('Newest first') . '</a>' . "\n";
    }

    echo "\n" . '<table class="claroTable" width="100%">' . "\n";
}

$nowBarAlreadyShowed = FALSE;

if (isset($_uid)) $date = $claro_notifier->get_notification_date($_uid);

foreach ( $eventList as $thisEvent )
{

    if (($thisEvent['visibility']=='HIDE' && $is_allowedToEdit) || $thisEvent['visibility']=='SHOW')
    {
        $style = $thisEvent['visibility']=='HIDE' ?'invisible' : $style='';

        // TREAT "NOW" BAR CASE
        if ( ! $nowBarAlreadyShowed )
        if (( ( strtotime($thisEvent['day'] . ' ' . $thisEvent['hour'] ) > time() ) && $orderDirection == 'ASC'  )
        ||
        ( ( strtotime($thisEvent['day'] . ' ' . $thisEvent['hour'] ) < time() ) && $orderDirection == 'DESC' )
        )
        {
            if ($monthBar != date('m',time()))
            {
                $monthBar = date('m',time());

                echo '<tr>' . "\n"
                .    '<th class="superHeader" colspan="2" valign="top">' . "\n"
                .    ucfirst(claro_disp_localised_date('%B %Y', time()))
                .    '</th>' . "\n"
                .    '</tr>' . "\n"
                ;
            }


            // 'NOW' Bar

            echo '<tr>' . "\n"
            .    '<td>' . "\n"
            .    '<img src="' . $imgRepositoryWeb . 'pixel.gif" width="20" alt=" " />'
            .    '<span class="highlight">'
            .    '<i>'
            .    ucfirst(claro_disp_localised_date( $dateFormatLong)) . ' '
            .    ucfirst(strftime( $timeNoSecFormat))
            .    ' -- '
            .    get_lang('Now')
            .    '</i>'
            .    '</span>' . "\n"
            .    '</td>' . "\n"
            .    '</tr>' . "\n"
            ;

            $nowBarAlreadyShowed = true;
        }

        /*
        * Display the month bar when the current month
        * is different from the current month bar
        */

        if ( $monthBar != date( 'm', strtotime($thisEvent['day']) ) )
        {
            $monthBar = date('m', strtotime($thisEvent['day']));

            echo '<tr>' . "\n"
            .    '<th class="superHeader" valign="top">' . "\n"
            .    ucfirst(claro_disp_localised_date('%B %Y', strtotime( $thisEvent['day']) ))
            .    '</th>' . "\n"
            .    '</tr>' . "\n"
            ;
        }

        /*
        * Display the event date
        */

        //modify style if the event is recently added since last login

        if (isset($_uid) && $claro_notifier->is_a_notified_ressource($_cid, $date, $_uid, $_gid, $_tid, $thisEvent['id']))
        {
            $classItem=' hot';
        }
        else // otherwise just display its name normally
        {
            $classItem='';
        }


        echo '<tr class="headerX" valign="top">' . "\n"
        .    '<th class="item' . $classItem . '">' . "\n"
        .    '<a href="#form" name="event' . $thisEvent['id'] . '"></a>' . "\n"
        .    '<img src="' . $imgRepositoryWeb . 'agenda.gif" alt=" " />'
        .    ucfirst(claro_disp_localised_date( $dateFormatLong, strtotime($thisEvent['day']))) . ' '
        .    ucfirst( strftime( $timeNoSecFormat, strtotime($thisEvent['hour']))) . ' '
        .    ( empty($thisEvent['lasting']) ? '' : get_lang('Lasting') . ' : ' . $thisEvent['lasting'] );

        /*
        * Display the event content
        */

        echo '</th>' . "\n"
        .    '</tr>' . "\n"
        .    '<tr>' . "\n"
        .    '<td>' . "\n"
        .    '<div class="content ' . $style . '">' . "\n"
        .    ( empty($thisEvent['title']  ) ? '' : '<p><strong>' . htmlspecialchars($thisEvent['title']) . '</strong></p>' . "\n" )
        .    ( empty($thisEvent['content']) ? '' :  claro_parse_user_text($thisEvent['content']) )
        .    '</div>' . "\n"
        ;
        linker_display_resource();
    }
    if ($is_allowedToEdit)

    {
        echo '<a href="' . $_SERVER['PHP_SELF'].'?cmd=rqEdit&amp;id=' . $thisEvent['id'] . '">'
        .    '<img src="' . $imgRepositoryWeb.'edit.gif" border="O" alt="' . get_lang('Modify') . '">'
        .    '</a> '
        .    '<a href="' . $_SERVER['PHP_SELF'] . '?cmd=exDelete&amp;id=' . $thisEvent['id'] . '" '
        .    'onclick="javascript:if(!confirm(\''
        .    clean_str_for_javascript(get_lang('Delete') . ' ' . $thisEvent['title'].' ?')
        .    '\')) {document.location=\'' . $_SERVER['PHP_SELF'] . '\'; return false}" >'
        .    '<img src="' . $imgRepositoryWeb . 'delete.gif" border="0" alt="' . get_lang('Delete') . '" />'
        .    '</a>'
        ;

        //  Visibility
        if ($thisEvent['visibility']=='SHOW')
        {
            echo '<a href="' . $_SERVER['PHP_SELF'] . '?cmd=mkHide&amp;id=' . $thisEvent['id'] . '">'
            .    '<img src="' . $imgRepositoryWeb . 'visible.gif" alt="' . get_lang('Invisible') . '" />'
            .    '</a>' . "\n";
        }
        else
        {
            echo '<a href="' . $_SERVER['PHP_SELF'] . '?cmd=mkShow&amp;id=' . $thisEvent['id'] . '">'
            .    '<img src="' . $imgRepositoryWeb . 'invisible.gif" alt="' . get_lang('Visible') . '" />'
            .    '</a>' . "\n"
            ;
        }
    }
    echo '</td>'."\n"
    .    '</tr>'."\n"
    ;

}   // end while

if ( count($eventList) > 0 ) echo '</table>';

include $includePath . '/claro_init_footer.inc.php';

?>