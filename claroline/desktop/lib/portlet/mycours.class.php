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

require_once dirname(__FILE__) . '/../../../../claroline/inc/lib/courselist.lib.php';
# require_once dirname(__FILE__) . '/../../../../claroline/inc/lib/core/notify.lib.php';
 
class mycours extends portlet
{
    function __construct()
    {
        
        
        $output = '';
        
        $personnalCourseList = get_user_course_list(claro_get_current_user_id());

        // get the list of personnal courses marked as contening new events
        # $date            = $claro_notifier->get_notification_date(claro_get_current_user_id());
        # $modified_course = $claro_notifier->get_notified_courses($date,claro_get_current_user_id());
        
        if (count($personnalCourseList))
        {
        $output .= '<ul style="list-style-image:url(claroline/img/course.gif);list-style-position:inside">'."\n";

        foreach($personnalCourseList as $thisCourse)
        {
            // If the course contains new things to see since last user login,
            // The course name will be displayed with the 'hot' class style in the list.
            // Otherwise it will name normally be displaied

            # if (in_array ($thisCourse['sysCode'], $modified_course)) $classItem = ' hot';
            # else                                                     $classItem = '';

            // show course language if not the same of the platform
            if ( $platformLanguage!=$thisCourse['language'] )
            {
                if ( !empty($langNameOfLang[$thisCourse['language']]) )
                {
                    $course_language_txt = ' - ' . ucfirst($langNameOfLang[$thisCourse['language']]);
                }
                else
                {
                    $course_language_txt = ' - ' . ucfirst($thisCourse['language']);
                }
            }
            else
            {
                $course_language_txt = '';
            }

            $output .= '<li class="item' . $classItem . '">' . "\n"
            .    '<a href="' .  get_path('url') . '/claroline/course/index.php?cid=' . htmlspecialchars($thisCourse['sysCode']) . '">';

            if ( get_conf('course_order_by') == 'official_code' )
            {
                $output .= $thisCourse['officialCode'] . ' - ' . $thisCourse['title'];
            }
            else
            {
                $output .= $thisCourse['title'] . ' (' . $thisCourse['officialCode'] . ')';
            }

            if ($thisCourse['isCourseManager'] == 1)
            {
                $userStatusImg = '<img src="' . get_path('imgRepositoryWeb') . 'manager.gif" alt="'.get_lang('Course manager').'" />';
            }
            else
            {
                $userStatusImg = '';
            }


            $output .= '</a>'
            .    $userStatusImg
            .    '<br />'
            .    '<small>' . $thisCourse['titular'] . $course_language_txt . '</small>' . "\n"
            .    '</li>' ."\n"
            ;

        } // end foreach($personnalCourseList as $thisCourse)

        $output .= '</ul>' . "\n";
        }
        //display legend if required
        
        # if( !empty($modified_course) )
        # {
        #     $output .= '<br />'
        #     .    '<small><span class="item hot"> '.get_lang('denotes new items').'</span></small>'
        #     .     '</td>' . "\n";
        # }
        
        
        
        
        
        
        
        $this->title = get_lang('My course list');
        $this->content = $output;
    }
    
    function renderContent()
    {
        return $this->content;
    }
    
    function renderTitle()
    {
        return $this->title;
    }
}

?>