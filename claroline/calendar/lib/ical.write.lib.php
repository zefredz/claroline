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
 * @package CLCAL
 *
 * @author Claro Team <cvs@claroline.net>
 */

function CLCAL_write_ical()
{
    global $_course, $_cid;

    include_once( dirname(__FILE__) . '/../../inc/lib/icalendar/class.iCal.inc.php');
    require_once dirname(__FILE__) . '/../../inc/lib/fileManage.lib.php';
    require_once dirname(__FILE__) . '/../../inc/lib/agenda.lib.php';
    $toolNameList = claro_get_tool_name_list();
    $eventList    = agenda_get_item_list('ASC', $GLOBALS['_cid']);

    $organizer = (array) array($_course['titular'], $_course['email']);
    $attendees = array();
    $categories = array( get_conf('siteName'),
    $_course['officialCode'],
    trim($toolNameList[str_pad('CLCAL',8,'_')]),
    $_course['categoryCode']
    );

    $iCalRepositorySys =  get_conf('rootSys') . get_conf('iCalRepository','iCal/');
    if (file_exists($iCalRepositorySys) || claro_mkdir($iCalRepositorySys, CLARO_FILE_PERMISSIONS, true))
    {
        $iCal = (object) new iCal('', 0, $iCalRepositorySys ); // (ProgrammID, Method (1 = Publish | 0 = Request), Download Directory)
        foreach ($eventList as $thisEvent)
        {
            if($thisEvent['visibility'] == 'SHOW')
            {
                $eventDuration = (isset($thisEvent['duration'])?$thisEvent['duration']:get_conf('defaultEventDuration','60'));
                $startDate = strtotime($thisEvent['day'] . ' ' . $thisEvent['hour'] ); // Start Time (timestamp; for an allday event the startdate has to start at YYYY-mm-dd 00:00:00)
                $endDate = $startDate + $eventDuration;

                $iCal->addEvent($organizer, // Organizer
                $startDate, //timestamp
                $endDate, //timestamp
                '', // Location
                0, // Transparancy (0 = OPAQUE | 1 = TRANSPARENT)
                $categories, // Array with Strings
                $thisEvent['content'], // Description
                $thisEvent['title'], // Title
                1, // Class (0 = PRIVATE | 1 = PUBLIC | 2 = CONFIDENTIAL)
                $attendees, // Array (key = attendee name, value = e-mail, second value = role of the attendee [0 = CHAIR | 1 = REQ | 2 = OPT | 3 =NON])
                5, // Priority = 0-9
                0, // frequency: 0 = once, secoundly - yearly = 1-7
                0, // recurrency end: ('' = forever | integer = number of times | timestring = explicit date)
                0, // Interval for frequency (every 2,3,4 weeks...)
                array(), // Array with the number of the days the event accures (example: array(0,1,5) = Sunday, Monday, Friday
                1, // Startday of the Week ( 0 = Sunday - 6 = Saturday)
                '', // exeption dates: Array with timestamps of dates that should not be includes in the recurring event
                0,  // Sets the time in minutes an alarm appears before the event in the programm. no alarm if empty string or 0
                1, // Status of the event (0 = TENTATIVE, 1 = CONFIRMED, 2 = CANCELLED)
                get_conf('clarolineRepositoryWeb') . 'calendar/agenda.php?cidReq=' . $_cid . '&amp;l#event' . $thisEvent['id'], // optional URL for that event
                get_conf('iso639_1_code'), // Language of the Strings
                '' // Optional UID for this event
                );
            }
        }
    }
    $iCalFilePath = $iCalRepositorySys . '/' . $_cid . '.ics';
    $fpICal = fopen($iCalFilePath, 'w');
    fwrite($fpICal, $iCal->getOutput('ics'));
    fclose($fpICal);
    return $iCalFilePath;
}
?>