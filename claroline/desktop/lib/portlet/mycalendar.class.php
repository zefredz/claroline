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
        //$this->year = $
    }
    
    function renderContent()
    {
        $today = getdate();

        $year = isset($_REQUEST['year']) ? (int) $_REQUEST['year' ] : $today['year'];
        $month = isset($_REQUEST['month']) ? (int) $_REQUEST['month' ] : $today['mon'];
        
        $userCourseList = claro_get_user_course_list();
        $agendaItemList = get_agenda_items($userCourseList, $month, $year);
        $langMonthNames = get_locale('langMonthNames');
        $langDay_of_weekNames = get_locale('langDay_of_weekNames');

        $monthName = $langMonthNames['long'][$month-1];        
        
        $output = ''
        .    '<div id="portletMycalendar">' . "\n"
        .	 ' <div class="calendar">' . claro_html_monthly_calendar($agendaItemList, $month, $year, $langDay_of_weekNames['init'], $monthName, true) . '</div>' . "\n"
        .	 ' <div class="details">'
        .	 '  <p><span>' . get_lang('Item 1') . '</span><br /></p>' . "\n"
        .	 '  <p><span>' . get_lang('Item 2') . '</span><br /></p>' . "\n"
        .	 '  <p><span>' . get_lang('Item 3') . '</span><br /></p>' . "\n"
        .	 '  <p><span>' . get_lang('Item 4') . '</span><br /></p>' . "\n"
        .	 '  <p><span>' . get_lang('Item 5') . '</span><br /></p>' . "\n"
        .	 '  <p><span>' . get_lang('Item 6') . '</span><br /></p>' . "\n"
        .	 '  <p><span>' . get_lang('Item 7') . '</span><br /></p>' . "\n"
        .	 '  <p><span>' . get_lang('Item 8') . '</span><br /></p>' . "\n"
        .	 '  <p><span>' . get_lang('Item 9') . '</span><br /></p>' . "\n"
        .	 ' </div>' . "\n"
        .	 '</div>' . "\n"
        .	 '<div style="clear:both;"></div>' . "\n"
        ;
        
        
        $this->title = get_lang('My calendar');
        $this->content = $output;
        
        return $this->content;
    }
    
    function renderTitle()
    {
        return $this->title;
    }
}

?>