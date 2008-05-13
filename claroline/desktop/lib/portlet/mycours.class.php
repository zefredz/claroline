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

require_once get_path( 'includePath' ) . '/lib/courselist.lib.php';
# require_once dirname(__FILE__) . '/../../../../claroline/inc/lib/core/notify.lib.php';
 
class MyCours extends Portlet
{
    function __construct()
    {
    }
    
    function renderContent()
    {
        
        global $platformLanguage;
        
        $output = '';
        
        $personnalCourseList = get_user_course_list(claro_get_current_user_id());

        // get the list of personnal courses marked as contening new events
        
        if ( count($personnalCourseList) )
        {
            $output .= '<dl>'."\n";

            foreach($personnalCourseList as $thisCourse)
            {            
                if ($thisCourse['isCourseManager'] == 1)
                {
                    $userStatusImg = '&nbsp;&nbsp;<img src="' . get_icon_url('manager') . '" alt="'.get_lang('Course manager').'" />';
                }
                else
                {
                    $userStatusImg = '';
                }
                
                // show course language if not the same of the platform
                if ( $platformLanguage != $thisCourse['language'] )
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
                
/*            
                if ( get_conf('course_order_by') == 'official_code' )
                {
                    $course_order_by = $thisCourse['officialCode'] . ' - ' . $thisCourse['title'];
                }
                else
                {
                    $course_order_by = $thisCourse['title'] . ' (' . $thisCourse['officialCode'] . ')';
                }
                */
                
                $course_order_by = $thisCourse['title'];
                
                $url = get_path('url') . '/claroline/course/index.php?cid=' 
                .    htmlspecialchars($thisCourse['sysCode'])
                ;

                $output .= '<dt>' . "\n"
                .    '<img class="iconDefinitionList" src="' . get_icon_url('course') . '" alt="' . get_lang('Icon course') . '" />'
                .    '<small>'
                .    '<a href="' . $url . '">'
                .    $course_order_by
                .    $userStatusImg
                .    '</a>' . "\n"
                .    '</small>' . "\n"
                .    '</dt>' . "\n"
                .    '<dd>'
                .    '<small>'
                .    '<a href="' . $url . '">'
                .    $thisCourse['officialCode'] 
                .    '</a>' . "\n"
                .    '<small>' . "\n"
                .    ' : ' . $thisCourse['titular'] . $course_language_txt
                .    '</small>' . "\n"
                .    '</small>' . "\n"
                .    '</dd>' . "\n"
                ;
            }

            $output .= '</dl>' . "\n";
        }
        else
        {
            $output .= "\n"
            .    '<dl>' . "\n"
            .    '<dt>' . "\n"
            .    '<img class="iconDefinitionList" src="' . get_icon_url('course') . '" alt="' . get_lang('Icon course') . '" />'
            .    get_lang('No courses !') . "\n"
            .    '</dt>' . "\n"
            .    '</dl>' . "\n"
            ;
            
            
            
        }
                
        $this->content = $output;

        return $this->content;
    }
    
    function renderTitle()
    {
        return $this->title = get_lang('My course list');
    }
}

?>