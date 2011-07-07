<?php // $Id$

// vim: expandtab sw=4 ts=4 sts=4:

/**
 * CLAROLINE
 *
 * Course home page: Announcements portlet
 *
 * @version     $Revision$
 * @copyright   (c) 2001-2011, Universite catholique de Louvain (UCL)
 * @license     http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @package     CLCHP
 * @author      Antonin Bourguignon <antonin.bourguignon@claroline.net>
 * @author      Claroline team <info@claroline.net>
 * @since       1.10
 */

require_once get_module_path( 'CLANN' ) . '/lib/announcement.lib.php';

class CLANN_Portlet extends CourseHomePagePortlet
{
    public function renderContent()
    {
        $output = '';
        $course = claro_get_current_course_data();
        $course['db'] = $course['dbName'];
        
        $announcementList = announcement_get_course_item_list_portlet($course);
        
        // Manage announcement's datas
        if($announcementList)
        {
            $output .= '<dl id="portletAnnouncements">' . "\n";
            
            $i = 0;
            foreach($announcementList as $announcementItem)
            {
                // Generate announcement URL
                $announcementItem['url'] = get_path('url')
                    . '/claroline/announcements/announcements.php?cidReq='
                    . $course['sysCode'];
                
                // Generate announcement title and content
                $announcementItem['title'] = trim(strip_tags($announcementItem['title']));
                if ( $announcementItem['title'] == '' )
                {
                    $announcementItem['title'] = substr($announcementItem['title'], 0, 60) . (strlen($announcementItem['title']) > 60 ? ' (...)' : '');
                }
                
                $announcementItem['content'] = trim(strip_tags($announcementItem['content']));
                if ( $announcementItem['content'] == '' )
                {
                    $announcementItem['content'] = substr($announcementItem['content'], 0, 60) . (strlen($announcementItem['content']) > 60 ? ' (...)' : '');
                }
                
                // Don't display hidden and expired elements
                $isVisible = (bool) ($announcementItem['visibility'] == 'SHOW') ? (1) : (0);
                $isOffDeadline = (bool)
                    (
                        (isset($announcementItem['visibleFrom'])
                            && strtotime($announcementItem['visibleFrom']) > time()
                        )
                        ||
                        (isset($announcementItem['visibleUntil'])
                            && time() >= strtotime($announcementItem['visibleUntil'])
                        )
                    ) ? (1) : (0);
                
                // Prepare the render
                $displayChar = 250;
                
                if (strlen($announcementItem['content']) > $displayChar)
                {
                    $content = substr($announcementItem['content'], 0, $displayChar)
                             . '... <a href="'
                             . htmlspecialchars(Url::Contextualize($announcementItem['url'])) . '">'
                             . '<b>' . get_lang('Read more &raquo;') . '</b></a>';
                }
                else
                {
                    $content = $announcementItem['content'];
                }
                
                if ( $isVisible && !$isOffDeadline )
                {
                    $output .= '<dt>' . "\n"
                             . '<h2><img class="iconDefinitionList" src="' . get_icon_url('announcement', 'CLANN') . '" alt="'.get_lang('Announcement').'" /> '
                             . '<a href="' . $announcementItem['url'] . '">'
                             . $announcementItem['title']
                             . '</a></h2>' . "\n"
                             . '</dt>' . "\n"
                             . '<dd'.($i == count($announcementList)-1?' class="last"':'').'>' . "\n"
                             . $content . "\n"
                             . '</dd>' . "\n";
                }
                
                $i++;
            }
            
            $output .= '</dl>';
        }
        else
        {
            $output .= "\n"
                     . '<dl>' . "\n"
                     . '<dt></dt>' . "\n"
                     . '<dd class="last">'
                     . '<img class="iconDefinitionList" src="' . get_icon_url('announcement', 'CLANN') . '" alt="Announcement icon" />'
                     . ' ' . get_lang('No announcement') . "\n"
                     . '</dd>' . "\n"
                     . '</dl>' . "\n\n";
        }
        
        return $output;
    }
    
    public function renderTitle()
    {
        $output = get_lang('Latest announcements');
        
        if (claro_is_allowed_to_edit())
        {
            $output .= ' <span class="separator">|</span> <a href="'
                     . htmlspecialchars(Url::Contextualize(get_module_url( 'CLANN' ) . '/announcements.php'))
                     . '">'
                     . '<img src="' . get_icon_url('settings') . '" alt="'.get_lang('Settings').'" /> '
                     . get_lang('Manage').'</a>';
        }
        
        return $output;
    }
}