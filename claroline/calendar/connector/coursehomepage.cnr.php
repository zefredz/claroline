<?php // $Id$

// vim: expandtab sw=4 ts=4 sts=4:

/**
* CLAROLINE
*
* Course home page: MyCalendar portlet
*
* @version      $Revision$
* @copyright    (c) 2001-2010, Universite catholique de Louvain (UCL)
* @license      http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
* @package      CLCHP
 * @author      Antonin Bourguignon <antonin.bourguignon@claroline.net>
* @author       Claroline team <info@claroline.net>
* @since        1.10
*/

require_once get_module_path( 'CLCAL' ) . '/lib/agenda.lib.php';

class CLCAL_Portlet extends CourseHomePagePortlet
{
    private $courseCode;
    
    public function __construct()
    {
        $this->courseCode = $courseCode = claro_get_current_course_id();
    }
    
    public function renderContent()
    {
        $output = '<div id="portletMycalendar">' . "\n"
            . '<img src="'.get_icon_url('loading').'" alt="" />' . "\n"
            . '</div>' . "\n"
            . '<div style="clear:both;"></div>' . "\n"
            ;
        
        $output .= "<script type=\"text/javascript\">
$(document).ready( function(){
    $('#portletMycalendar').load('"
        .get_module_url('CLCAL')."/ajaxHandler.php', { location : 'coursehomepage', courseCode : '".$this->courseCode."' });
});
</script>";
        
        return $output;
    }
    
    public function renderTitle()
    {
        return get_lang('My calendar');
    }
}