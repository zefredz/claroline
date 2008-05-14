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
    function __construct()
    {
        if (file_exists(claro_get_conf_repository() . 'CLANN.conf.php'))
            include claro_get_conf_repository() . 'CLANN.conf.php';
    }

    function renderContent()
    {
        $personnalCourseList = get_user_course_list(claro_get_current_user_id());

        $annoncementEventList = announcement_get_items_portlet($personnalCourseList);

        $output = '';

        if($annoncementEventList)
        {
            $output .= '<dl>';
            foreach($annoncementEventList as $annoncementItem)
            {

                $output .= '<dt>' . "\n"
                .    '<img class="iconDefinitionList" src="' . get_icon_url('announcement') . '" alt="' . get_lang('Icon announcement') . '" />'
                .    '<small>'
                .    '<a href="' . $annoncementItem['url'] . '">'
                .    $annoncementItem['title']
                .    '</a>' . "\n"
                .    '</small>' . "\n"
                .    '</dt>' . "\n"
                ;

                foreach($annoncementItem['eventList'] as $annoncementEvent)
                {
                    $output .= '<dd>'
                    .    '<small>'  . "\n"
                    .    '<a href="' . $annoncementItem['url'] . '">'
                    .    $annoncementItem['courseOfficialCode']
                    .    '</a> : ' . "\n"
                    .    '<small>'  . "\n"
                    .    $annoncementEvent['content'] . "\n"
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
            .    '<img class="iconDefinitionList" src="' . get_icon_url('announcement') . '" alt="' . get_lang('Icon announcement') . '" />'
            .    '<small>'
            .    get_lang('No event to display') . "\n"
            .    '</small>' . "\n"
            .    '</dt>' . "\n"
            ;
        }


        return $output;
    }

    function renderTitle()
    {
        return $this->title = get_lang('Latest announcements');
    }
}
?>