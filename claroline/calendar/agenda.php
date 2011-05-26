<?php // $Id$

/**
 * CLAROLINE
 *
 * - For a Student -> View agenda content
 * - For a Prof    ->
 *         - View agenda content
 *         - Update/delete existing entries
 *         - Add entries
 *         - generate an "announce" entries about an entries
 *
 * @version     $Revision$
 * @copyright   (c) 2001-2011, Universite catholique de Louvain (UCL)
 * @license     http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @author      Claro Team <cvs@claroline.net>
 * @package     CLCAL
 */

$tlabelReq  = 'CLCAL';
$gidReset   = true;
require_once dirname(__FILE__) . '/../../claroline/inc/claro_init_global.inc.php';
$_user      = claro_get_current_user_data();
$_course    = claro_get_current_course_data();

//**//

if (claro_is_in_a_group()) $currentContext = claro_get_current_context(array('course','group'));
else                       $currentContext = claro_get_current_context('course');

//**/

FromKernel::uses('core/linker.lib');
ResourceLinker::init();

require_once './lib/agenda.lib.php';
require_once get_path('incRepositorySys') . '/lib/form.lib.php';

require claro_get_conf_repository() . 'ical.conf.php';
require claro_get_conf_repository() . 'rss.conf.php';

$context = claro_get_current_context(CLARO_CONTEXT_COURSE);
define('CONFVAL_LOG_CALENDAR_INSERT', false);
define('CONFVAL_LOG_CALENDAR_DELETE', false);
define('CONFVAL_LOG_CALENDAR_UPDATE', false);

if ( !claro_is_in_a_course() || !claro_is_course_allowed() ) claro_disp_auth_form(true);

$nameTools = get_lang('Agenda');

claro_set_display_mode_available(true);

$is_allowedToEdit = claro_is_course_manager();

if ( $is_allowedToEdit )
{
// 'rqAdd' ,'rqEdit', 'exAdd','exEdit', 'exDelete', 'exDeleteAll', 'mkShow', 'mkHide'

    if ( isset($_REQUEST['cmd'])
        && ( 'rqAdd' == $_REQUEST['cmd'] || 'rqEdit' == $_REQUEST['cmd'] )
    )
    {
        if ( 'rqEdit' == $_REQUEST['cmd'] )
        {
            $currentLocator = ResourceLinker::$Navigator->getCurrentLocator(
                    array( 'id' => (int) $_REQUEST['id'] ) );
            
            ResourceLinker::setCurrentLocator( $currentLocator );
        }
    }
}

$tbl_c_names = claro_sql_get_course_tbl();
$tbl_calendar_event = $tbl_c_names['calendar_event'];

$cmd = ( isset($_REQUEST['cmd']) ) ?$_REQUEST['cmd']: null;

$dialogBox = new DialogBox();

if     ( 'rqAdd' == $cmd )  $subTitle = get_lang('Add an event');
elseif ( 'rqEdit' == $cmd ) $subTitle = get_lang('Edit Event');
else                        $subTitle = '';

//-- order direction
if( !empty($_REQUEST['order']) )
    $orderDirection = strtoupper($_REQUEST['order']);
elseif( !empty($_SESSION['orderDirection']) )
    $orderDirection = strtoupper($_SESSION['orderDirection']);
else
    $orderDirection = 'ASC';

$acceptedValues = array('DESC','ASC');

if( ! in_array($orderDirection, $acceptedValues) )
{
    $orderDirection = 'ASC';
}

$_SESSION['orderDirection'] = $orderDirection;


$is_allowedToEdit = claro_is_allowed_to_edit();


/**
 * COMMANDS SECTION
 */

$display_form = false;

if ( $is_allowedToEdit )
{
    $id         = ( isset($_REQUEST['id']) ) ? ((int) $_REQUEST['id']) : (0);
    $title      = ( isset($_REQUEST['title']) ) ? (trim($_REQUEST['title'])) : ('');
    $content    = ( isset($_REQUEST['content']) ) ? (trim($_REQUEST['content'])) : ('');
    $lasting    = ( isset($_REQUEST['lasting']) ) ? (trim($_REQUEST['lasting'])) : ('');
    $speakers     = ( isset($_REQUEST['speakers']) ) ? (trim($_REQUEST['speakers'])) : ('');
    $location   = ( isset($_REQUEST['location']) ) ? (trim($_REQUEST['location'])) : ('');
    
    $autoExportRefresh = false;
    
    if ( 'exAdd' == $cmd )
    {
        $date_selection = $_REQUEST['fyear'] . '-' . $_REQUEST['fmonth'] . '-' . $_REQUEST['fday'];
        $hour           = $_REQUEST['fhour'] . ':' . $_REQUEST['fminute'] . ':00';

        $entryId = agenda_add_item($title, $content, $date_selection, $hour, $lasting, $speakers, $location) ;
        
        if ( $entryId != false )
        {
            $dialogBox->success( get_lang('Event added to the agenda') );
            
            $currentLocator = ResourceLinker::$Navigator->getCurrentLocator(
                array( 'id' => (int) $entryId ) );
            
            $resourceList =  isset($_REQUEST['resourceList'])
                ? $_REQUEST['resourceList']
                : array()
                ;
                
            ResourceLinker::updateLinkList( $currentLocator, $resourceList );

            if ( CONFVAL_LOG_CALENDAR_INSERT )
            {
                $claroline->log('CALENDAR', array ('ADD_ENTRY' => $entryId));
            }

            // notify that a new agenda event has been posted

            $eventNotifier->notifyCourseEvent('agenda_event_added', claro_get_current_course_id(), claro_get_current_tool_id(), $entryId, claro_get_current_group_id(), '0');
            $autoExportRefresh = true;

        }
        else
        {
            $dialogBox->error( get_lang('Unable to add the event to the agenda') );
        }
    }

    /*------------------------------------------------------------------------
    EDIT EVENT COMMAND
    --------------------------------------------------------------------------*/


    if ( 'exEdit' == $cmd )
    {
        $date_selection = $_REQUEST['fyear'] . '-' . $_REQUEST['fmonth'] . '-' . $_REQUEST['fday'];
        $hour           = $_REQUEST['fhour'] . ':' . $_REQUEST['fminute'] . ':00';

        if ( !empty($id) )
        {
            if ( agenda_update_item($id,$title,$content,$date_selection,$hour,$lasting,$speakers,$location) )
            {
                $dialogBox->success( get_lang('Event updated into the agenda') );
                
                $currentLocator = ResourceLinker::$Navigator->getCurrentLocator(
                    array( 'id' => (int) $id ) );
                
                $resourceList =  isset($_REQUEST['resourceList'])
                    ? $_REQUEST['resourceList']
                    : array()
                    ;
                    
                ResourceLinker::updateLinkList( $currentLocator, $resourceList );

                $eventNotifier->notifyCourseEvent('agenda_event_modified', claro_get_current_course_id(), claro_get_current_tool_id(), $id, claro_get_current_group_id(), '0'); // notify changes to event manager
                $autoExportRefresh = true;
            }
            else
            {
                $dialogBox->error( get_lang('Unable to update the event into the agenda') );
            }
        }
    }

    /*------------------------------------------------------------------------
    DELETE EVENT COMMAND
    --------------------------------------------------------------------------*/

    if ( 'exDelete' == $cmd && !empty($id) )
    {

        if ( agenda_delete_item($id) )
        {
            $dialogBox->success( get_lang('Event deleted from the agenda') );

            $eventNotifier->notifyCourseEvent('agenda_event_deleted', claro_get_current_course_id(), claro_get_current_tool_id(), $id, claro_get_current_group_id(), '0'); // notify changes to event manager
            $autoExportRefresh = true;
            if ( CONFVAL_LOG_CALENDAR_DELETE )
            {
                $claroline->log('CALENDAR',array ('DELETE_ENTRY' => $id));
            }
        }
        else
        {
            $dialogBox->error( get_lang('Unable to delete event from the agenda') );
        }

        // linker_delete_resource();
    }

    /*----------------------------------------------------------------------------
    DELETE ALL EVENTS COMMAND
    ----------------------------------------------------------------------------*/

    if ( 'exDeleteAll' == $cmd )
    {
        if ( agenda_delete_all_items())
        {
            $eventNotifier->notifyCourseEvent('agenda_event_list_deleted', claro_get_current_course_id(), claro_get_current_tool_id(), null, claro_get_current_group_id(), '0');

            $dialogBox->success( get_lang('All events deleted from the agenda') );

            if ( CONFVAL_LOG_CALENDAR_DELETE )
            {
                $claroline->log('CALENDAR', array ('DELETE_ENTRY' => 'ALL') );
            }
        }
        else
        {
            $dialogBox->error( get_lang('Unable to delete all events from the agenda') );
        }

        // linker_delete_all_tool_resources();
    }
    /*-------------------------------------------------------------------------
    EDIT EVENT VISIBILITY
    ---------------------------------------------------------------------------*/

    if ( 'mkShow' == $cmd  || 'mkHide' == $cmd )
    {
        if ($cmd == 'mkShow')
        {
            $visibility = 'SHOW';
            $eventNotifier->notifyCourseEvent('agenda_event_visible', claro_get_current_course_id(), claro_get_current_tool_id(), $id, claro_get_current_group_id(), '0'); // notify changes to event manager
            $autoExportRefresh = true;
        }

        if ($cmd == 'mkHide')
        {
            $visibility = 'HIDE';
            $eventNotifier->notifyCourseEvent('agenda_event_invisible', claro_get_current_course_id(), claro_get_current_tool_id(), $id, claro_get_current_group_id(), '0'); // notify changes to event manager
            $autoExportRefresh = true;
        }

        agenda_set_item_visibility($id, $visibility);
    }

    /*------------------------------------------------------------------------
    EVENT EDIT
    --------------------------------------------------------------------------*/

    if ( 'rqEdit' == $cmd  || 'rqAdd' == $cmd  )
    {
        claro_set_display_mode_available(false);

        if ( 'rqEdit' == $cmd  && !empty($id) )
        {
            $editedEvent = agenda_get_item($id) ;
            // get date as unixtimestamp for claro_dis_date_form and claro_html_time_form
            $editedEvent['date'] = strtotime($editedEvent['dayAncient'].' '.$editedEvent['hourAncient']);
            $nextCommand = 'exEdit';
        }
        else
        {
            $editedEvent['id'            ] = '';
            $editedEvent['title'         ] = '';
            $editedEvent['content'       ] = '';
            $editedEvent['date'] = time();
            $editedEvent['lastingAncient'] = false;
            $editedEvent['location'      ] = '';

            $nextCommand = 'exAdd';
        }
        $display_form =true;
    } // end if cmd == 'rqEdit' && cmd == 'rqAdd'

    if ( $autoExportRefresh)
    {
        // ical update
        if (get_conf('enableICalInCourse',1) )
        {
            require_once get_path('incRepositorySys') . '/lib/ical.write.lib.php';
            buildICal( array(CLARO_CONTEXT_COURSE => claro_get_current_course_id()));
        }
    }

} // end id is_allowed to edit

/**
 *     DISPLAY SECTION
 *
 */

$noQUERY_STRING = true;

$eventList = agenda_get_item_list($currentContext,$orderDirection);

// Build the tool list
$toolList = array();

$toolList[] = array(
    'name' => get_lang('Today'),
    'url' => htmlspecialchars(Url::Contextualize($_SERVER['PHP_SELF'] . '#today'))
);


if ( count($eventList) > 0 )
{
    if ( $orderDirection == 'DESC' )
    {
        $toolList[] = array(
            'img' => 'reverse',
            'name' => get_lang('Oldest first'),
            'url' => htmlspecialchars(Url::Contextualize($_SERVER['PHP_SELF'] . '?order=asc'))
        );
    }
    else
    {
        $toolList[] = array(
            'img' => 'reverse',
            'name' => get_lang('Newest first'),
            'url' => htmlspecialchars(Url::Contextualize($_SERVER['PHP_SELF'] . '?order=desc'))
        );
    }
}

$toolList[] = array(
    'img' => 'agenda_new',
    'name' => get_lang('Add an event'),
    'url' => htmlspecialchars(Url::Contextualize( $_SERVER['PHP_SELF'] . '?cmd=rqAdd' ))
);

if ( count($eventList) > 0 )
{
    $toolList[] = array(
        'img' => 'delete',
        'name' => get_lang('Clear up event list'),
        'url' => htmlspecialchars(Url::Contextualize($_SERVER['PHP_SELF'] . '?cmd=exDeleteAll')) . '" '
               . ' onclick="javascript:if(!confirm(\'' . clean_str_for_javascript(get_lang('Clear up event list ?')) . '\')) return false;'
    );
}

$titleParts = array('mainTitle' => $nameTools, 'subTitle' => $subTitle);


// Display
//TODO this tool could use a template

$output = '';
$output .= $dialogBox->render();
$output .= claro_html_tool_title($titleParts, null, $toolList);

if ($display_form)
{
    // Ressource linker
    if ( 'rqEdit' == $_REQUEST['cmd'] )
    {
        ResourceLinker::setCurrentLocator(
            ResourceLinker::$Navigator->getCurrentLocator(
                array( 'id' => (int) $_REQUEST['id'] ) ) );
    }
    
    $template = new ModuleTemplate($tlabelReq, 'form.tpl.php');
    $template->assign('formAction', htmlspecialchars($_SERVER['PHP_SELF']));
    $template->assign('relayContext', claro_form_relay_context());
    $template->assign('cmd', $nextCommand);
    $template->assign('event', $editedEvent);
    
    $output .= $template->render();
}

$monthBar     = '';

if ( count($eventList) < 1 )
{
    $output .= "\n" . '<blockquote>' . get_lang('No event in the agenda') . '</blockquote>' . "\n";
}

$nowBarAlreadyShowed = false;

if (claro_is_user_authenticated()) $date = $claro_notifier->get_notification_date(claro_get_current_user_id());

foreach ( $eventList as $thisEvent )
{
    if (('HIDE' == $thisEvent['visibility'] && $is_allowedToEdit)
        || 'SHOW' == $thisEvent['visibility'])
    {
        //modify style if the event is recently added since last login
        if (claro_is_user_authenticated()
            && $claro_notifier->is_a_notified_ressource(claro_get_current_course_id(), $date, claro_get_current_user_id(), claro_get_current_group_id(), claro_get_current_tool_id(), $thisEvent['id']))
        {
            $cssItem = 'item hot';
        }
        else
        {
            $cssItem = 'item';
        }

        $cssInvisible = '';
        if ($thisEvent['visibility'] == 'HIDE')
        {
            $cssInvisible = ' invisible';
        }

        // TREAT "NOW" BAR CASE
        if ( ! $nowBarAlreadyShowed )
        if (( ( strtotime($thisEvent['day'] . ' ' . $thisEvent['hour'] ) > time() ) &&  'ASC' == $orderDirection )
        ||
        ( ( strtotime($thisEvent['day'] . ' ' . $thisEvent['hour'] ) < time() ) &&  'DESC' == $orderDirection )
        )
        {
            // add monthbar is now bar is the first (or only one) item for this month
            // current time month monthBar display
            if ($monthBar != date('mY',time()))
            {
                $monthBar = date('mY',time());

                $output .= '<h2>' . "\n"
                         . ucfirst(claro_html_localised_date('%B %Y', time()))
                         . '</h2>' . "\n";
            }

            // 'NOW' bar
            $output .= '<h3 class="highlight">'
                     . '<a name="today">'
                     . '<i>'
                     . ucfirst(claro_html_localised_date( get_locale('dateFormatLong'))) . ' '
                     . ucfirst(strftime( get_locale('timeNoSecFormat')))
                     . ' -- '
                     . get_lang('Now')
                     . '</i>'
                     . '</a>'
                     . '</h3>' . "\n";
            
            $nowBarAlreadyShowed = true;
        }

        /*
         * Display the month bar when the current month
         * is different from the current month bar
         */

        if ( $monthBar != date( 'mY', strtotime($thisEvent['day']) ) )
        {
            $monthBar = date('mY', strtotime($thisEvent['day']));
            
            $output .= '<h2>'
                     . ucfirst(claro_html_localised_date('%B %Y', strtotime( $thisEvent['day']) ))
                     . '</h2>' . "\n";
        }

        /*
         * Display the event date
         */
        $output .= '<div class="item">' . "\n"
        .   '<h1 id = "event' . $thisEvent['id'] . '" class="blockHeader">'
        .   '<span class="'. $cssItem . $cssInvisible .'">' . "\n"
        .   '<img src="' . get_icon_url('agenda') . '" alt="" /> '
        .    ucfirst(claro_html_localised_date( get_locale('dateFormatLong'), strtotime($thisEvent['day']))) . ' '
        .    ucfirst( strftime( get_locale('timeNoSecFormat'), strtotime($thisEvent['hour'])))
        .    ( empty($thisEvent['lasting']) ? ('') : (' | '.get_lang('Lasting')) . ' : ' . $thisEvent['lasting'] )
        .    ( empty($thisEvent['location']) ? ('') : (' | '.get_lang('Location')) . ' : ' . $thisEvent['location'] )
        .    ( empty($thisEvent['speakers']) ? ('') : (' | '.get_lang('Speakers')) . ' : ' . $thisEvent['speakers'] )
        .   '</span>' . "\n"
        .   '</h1>' . "\n"
        
        /*
         * Display the event content
         */
        .   '<div class="content">' . "\n"

        .   '<div class="' . $cssInvisible . '">' . "\n"
        .    ( empty($thisEvent['title']  ) ? '' : '<p><strong>' . htmlspecialchars($thisEvent['title']) . '</strong></p>' . "\n" )
        .    ( empty($thisEvent['content']) ? '' :  claro_parse_user_text($thisEvent['content']) )
        .   '</div>' . "\n";
        
            $output .= '</div>' . "\n"; // content

        $currentLocator = ResourceLinker::$Navigator->getCurrentLocator( array('id' => $thisEvent['id'] ) );
        $output .= ResourceLinker::renderLinkList( $currentLocator );
    }

    if ($is_allowedToEdit)
    {
        $output .= '<div class="manageTools">'
        .    '<a href="' . htmlspecialchars(Url::Contextualize( $_SERVER['PHP_SELF'].'?cmd=rqEdit&amp;id=' . $thisEvent['id'] )) . '">'
        .    '<img src="' . get_icon_url('edit') . '" alt="' . get_lang('Modify') . '" />'
        .    '</a> '
        .    '<a href="' . htmlspecialchars(Url::Contextualize( $_SERVER['PHP_SELF'] . '?cmd=exDelete&amp;id=' . $thisEvent['id'] )) . '" '
        .    ' onclick="javascript:if(!confirm(\'' . clean_str_for_javascript(get_lang('Are you sure to delete "%title" ?', array('%title' => $thisEvent['title']))) . '\')) return false;">'
        .    '<img src="' . get_icon_url('delete') . '" alt="' . get_lang('Delete') . '" />'
        .    '</a>'
        ;

        //  Visibility
        if ('SHOW' == $thisEvent['visibility'])
        {
            $output .= '<a href="' . htmlspecialchars(Url::Contextualize( $_SERVER['PHP_SELF'] . '?cmd=mkHide&amp;id=' . $thisEvent['id'] )) . '">'
            .    '<img src="' . get_icon_url('visible') . '" alt="" />'
            .    '</a>' . "\n";
        }
        else
        {
            $output .= '<a href="' . htmlspecialchars(Url::Contextualize( $_SERVER['PHP_SELF'] . '?cmd=mkShow&amp;id=' . $thisEvent['id'] )) . '">'
            .    '<img src="' . get_icon_url('invisible') . '" alt="" />'
            .    '</a>' . "\n"
            ;
        }
        
        $output .= '</div>' . "\n"; // claroBlockCmd
    }
    
    $output .= '</div>' . "\n\n"; // item

} // end while

Claroline::getDisplay()->body->appendContent($output);

echo Claroline::getDisplay()->render();