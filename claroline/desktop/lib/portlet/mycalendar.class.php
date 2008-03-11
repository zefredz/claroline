<?php // $Id$
/**
 * CLAROLINE
 *
 * This script prupose to user to edit his own profile
 *
 * @version 1.8 $Revision$
 *
 * @copyright 2001-2007 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @see http://www.claroline.net/wiki/Auth/
 *
 * @author Claro Team <cvs@claroline.net>
 *
 * @package Auth
 *
 */

 
require_once dirname(__FILE__) . '/../../../../claroline/calendar/lib/agenda.lib.php';

class mycalendar extends portlet
{
    //protected $userId, $year, $month, $today;
    
    function __construct( /*$data*/ )
    {
        //$this->userId = $data['userId'];
        //$this->year = $data['year'];
    }
    
    function renderContent()
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
        .	 ' <div class="calendar">' . claro_html_monthly_calendar($agendaItemList, $month, $year, $langDay_of_weekNames['init'], $monthName, true) . '</div>' . "\n"
        .	 ' <div class="details">'
        ;
        
        if($agendaItemList)
        {
        
            $output .= '<dl>';
            
            
            foreach($agendaItemList as $agendaItem)
            {
                $output .= '<dt>' . "\n"
                .    '<img class="iconDefinitionList" src="' . get_icon('agenda') . '" alt="' . get_lang('Icon agenda') . '" />'
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
            $output .= '<dl>'
            .    '<dt>' . "\n"
            .    '<img class="iconDefinitionList" src="' . get_icon('agenda') . '" alt="' . get_lang('Icon agenda') . '" />'
            .    '<small>'
            .    get_lang('No event to display') . "\n"
            .    '</small>' . "\n"
            .    '</dt>' . "\n"
            ;
        }
    
        $output .= ''
        .	 ' </div>' . "\n"
        .	 '</div>' . "\n"
        .	 '<div style="clear:both;"></div>' . "\n"
        ;
        
        $this->content = $output;
        
        return $this->content;
    }
    
    function renderTitle()
    {
        return $this->title = get_lang('My calendar');
    }
}

?>