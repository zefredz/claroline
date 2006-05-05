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

function CLWRK_write_ical( & $iCal, $context)
{
    global $_course;
    if (is_array($context) && count($context)>0)
    {
        if (in_array('course',$context))
        {
            $courseCode = $context['course'];
        }
        else
        {
            $courseCode = $GLOBALS['_cid'];
        }
    }

    $toolNameList = claro_get_tool_name_list();
    $assignmentList = assignmentList();
    $organizer = (array) array($_course['titular'], $_course['email']);
    $attendees = array();
    $categories = array( get_conf('siteName'),
    $_course['officialCode'],
    trim($toolNameList[str_pad('CLWRK',8,'_')]),
    $_course['categoryCode']
    );

    $iCal = (object) new iCal('', 0, 'S:/cvs.claroline.net/clarolinedev/claroline.rss/'); // (ProgrammID, Method (1 = Publish | 0 = Request), Download Directory)
    foreach ($assignmentList as $thisAssignment)
    {
        if('VISIBLE' == $thisAssignment['visibility'])
        {

            $categories[] = $thisAssignment['assignment_type'];

            $iCal->addToDo(
            $thisAssignment['title'], // Title
            $thisAssignment['description'], // Description
            '', // Location
            (int) $thisAssignment['start_date_unix'], // Start time
            3600, //(($thisAssignment['end_date_unix']-$thisAssignment['start_date_unix'])/60), // Duration in minutes
            (int) $thisAssignment['end_date_unix'], // End time
            1, // Percentage complete
            5, // Priority = 0-9
            1, // Status of the event (0 = TENTATIVE, 1 = CONFIRMED, 2 = CANCELLED)
            1, // Class (0 = PRIVATE | 1 = PUBLIC | 2 = CONFIDENTIAL)
            $organizer, // Organizer
            $attendees, // Array (key = attendee name, value = e-mail, second value = role of the attendee [0 = CHAIR | 1 = REQ | 2 = OPT | 3 =NON])
            $categories, // Array with Strings
            time(), // Last Modification
            0, // Sets the time in minutes an alarm appears before the event in the programm. no alarm if empty string or 0
            0, // frequency: 0 = once, secoundly - yearly = 1-7
            0, // recurrency end: ('' = forever | integer = number of times | timestring = explicit date)
            0, // Interval for frequency (every 2,3,4 weeks...)
            array(), // Array with the number of the days the event accures (example: array(0,1,5) = Sunday, Monday, Friday
            1, // Startday of the Week ( 0 = Sunday - 6 = Saturday)
            '', // exeption dates: Array with timestamps of dates that should not be includes in the recurring event
            get_conf('clarolineRepositoryWeb') . 'work/workList.php?cidReq=' . $courseCode.'&amp;assigId=' . $thisAssignment['id'], // optional URL for that event
            get_conf('iso639_1_code'), // Language of the Strings
            '' // Optional UID for this ToDo
            );



        }
    }

    return $iCal;
}

function assignmentList()
{
        $tbl_cdb_names = claro_sql_get_course_tbl();
        $tbl_wrk_assignment = $tbl_cdb_names['wrk_assignment'];

        $sql = "SELECT `id`,
        				`title`,
        				`description`,
        				`def_submission_visibility`,
        				`visibility`,
        				`assignment_type`,
        				unix_timestamp(`start_date`) as `start_date_unix`,
        				unix_timestamp(`end_date`) as `end_date_unix`
            	FROM `" . $tbl_wrk_assignment . "`";

        return claro_sql_query_fetch_all_rows($sql);
}

?>