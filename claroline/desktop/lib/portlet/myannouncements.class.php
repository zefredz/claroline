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

require_once get_path( 'clarolineRepositorySys' ) . '/announcements/lib/announcement.lib.php';
require_once get_path( 'includePath' ) . '/lib/courselist.lib.php';

class MyAnnouncements extends Portlet
{
    public function __construct()
    {
        if (file_exists(claro_get_conf_repository() . 'CLANN.conf.php'))
        {
            include claro_get_conf_repository() . 'CLANN.conf.php';
        }
    }

    public function renderContent()
    {
        $personnalCourseList = get_user_course_list(claro_get_current_user_id());

        $announcementEventList = announcement_get_items_portlet($personnalCourseList);

        $output = '';

        if($announcementEventList)
        {
            $output .= '<dl>';
            foreach($announcementEventList as $announcementItem)
            {

                $output .= '<dt>' . "\n"
                .    '<img class="iconDefinitionList" src="' . get_icon_url('announcement') . '" alt="" />'
                .    '<small>'
                .    '<a href="' . $announcementItem['url'] . '">'
                .    $announcementItem['title']
                .    '</a>' . "\n"
                .    '</small>' . "\n"
                .    '</dt>' . "\n"
                ;

                foreach($announcementItem['eventList'] as $announcementEvent)
                {
                    $output .= '<dd>'
                    .    '<small>'  . "\n"
                    .    '<a href="' . $announcementItem['url'] . '">'
                    .    $announcementItem['courseOfficialCode']
                    .    '</a> : ' . "\n"
                    .    '<small>'  . "\n"
                    .    $announcementEvent['content'] . "\n"
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
            .    '<img class="iconDefinitionList" src="' . get_icon_url('announcement') . '" alt="" />'
            .    '<small>'
            .    get_lang('No event to display') . "\n"
            .    '</small>' . "\n"
            .    '</dt>' . "\n"
            .    '</dl>' . "\n"
            ;
        }

        return $output;
    }

    public function renderTitle()
    {
        return get_lang('Latest announcements');
    }
}
?>