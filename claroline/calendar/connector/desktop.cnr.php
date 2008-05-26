<?php // $Id$

// vim: expandtab sw=4 ts=4 sts=4:

if ( count( get_included_files() ) == 1 )
{
    die( 'The file ' . basename(__FILE__) . ' cannot be accessed directly, use include instead' );
}

/**
* CLAROLINE
*
* User desktop : MyCalendar portlet
* FIXME : move to calendar module
*
* @version      1.9 $Revision$
* @copyright    (c) 2001-2008 Universite catholique de Louvain (UCL)
* @license      http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
* @package      DESKTOP
* @author       Claroline team <info@claroline.net>
*
*/

require_once get_module_path( 'CLCAL' ) . '/lib/agenda.lib.php';

class CLCAL_Portlet extends UserDesktopPortlet
{

    public function renderContent()
    {
        $today = getdate();

        // **** Attention !!! A changer ...
        $year = isset($_REQUEST['year']) ? (int) $_REQUEST['year' ] : $today['year'];
        $month = isset($_REQUEST['month']) ? (int) $_REQUEST['month' ] : $today['mon'];
        // ****

        $userCourseList = claro_get_user_course_list();
        $agendaItemList = get_agenda_items_compact_mode($userCourseList, $month, $year);
        $langMonthNames = get_locale('langMonthNames');
        $langDay_of_weekNames = get_locale('langDay_of_weekNames');

        $monthName = $langMonthNames['long'][$month-1];

        $output = '';

        $output .= ''
        .    '<div id="portletMycalendar">' . "\n"
        .     ' <div class="calendar">' . claro_html_monthly_calendar($agendaItemList, $month, $year, $langDay_of_weekNames['init'], $monthName, true) . '</div>' . "\n"
        .     ' <div class="details">'
        ;

        if($agendaItemList)
        {
            $output .= '<dl>';

            foreach($agendaItemList as $agendaItem)
            {
                $output .= '<dt>' . "\n"
                .    '<img class="iconDefinitionList" src="' . get_icon_url('agenda') . '" alt="' . get_lang('Icon agenda') . '" />'
                .    '<small>'
                .    claro_html_localised_date( get_locale('dateFormatLong'),
                strtotime($agendaItem['date']) )
                .    '</small>' . "\n"
                .    '</dt>' . "\n"
                ;

                foreach($agendaItem['eventList'] as $agendaEvent)
                {
                    $output .= '<dd>'
                    .    '<small>'  . "\n"
                    .    '<a href="' . $agendaEvent['url'] . '">'
                    .    $agendaEvent['courseOfficialCode']
                    .    '</a> : ' . "\n"
                    .    '<small>'  . "\n"
                    .    $agendaEvent['content'] . "\n"
                    .    '</small>' . "\n"
                    .    '</small>' . "\n"
                    .    '</dd>' . "\n"
                    ;
                }
            }
            $output .= '</dl>';
        }
        else
        {
            $output .= "\n"
            .    '<dl>' . "\n"
            .    '<dt>' . "\n"
            .    '<img class="iconDefinitionList" src="' . get_icon_url('agenda') . '" alt="' . get_lang('Icon agenda') . '" />'
            .    '<small>'
            .    get_lang('No event to display') . "\n"
            .    '</small>' . "\n"
            .    '</dt>' . "\n"
            .    '</dl>' . "\n"
            ;
        }

        $output .= ''
        .     ' </div>' . "\n"
        .     '</div>' . "\n"
        .     '<div style="clear:both;"></div>' . "\n"
        ;

        return $output;
    }

    public function renderTitle()
    {
        return get_lang('My calendar');
    }
}
