<?php // $Id$

// vim: expandtab sw=4 ts=4 sts=4:

if ( count( get_included_files() ) == 1 )
{
    die( 'The file ' . basename(__FILE__) . ' cannot be accessed directly, use include instead' );
}

/**
* CLAROLINE
*
* User desktop : course list portlet
*
* @version      1.9 $Revision$
* @copyright    (c) 2001-2008 Universite catholique de Louvain (UCL)
* @license      http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
* @package      DESKTOP
* @author       Claroline team <info@claroline.net>
*
*/

uses('courselist.lib');

class MyCourseList extends UserDesktopPortlet
{
    public function renderContent()
    {
        global $platformLanguage;

        $output = '<a class="claroCmd" href="'.get_path('url')
            . '/index.php#myCourseList">'
            . '<img src="' . get_icon_url('edit') . '" alt="" /> '
            . get_lang('Edit')
            . '</a>'
            ;

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

                $course_order_by = $thisCourse['title'];

                $url = get_path('url') . '/claroline/course/index.php?cid='
                .    htmlspecialchars($thisCourse['sysCode'])
                ;

                $output .= '<dt>' . "\n"
                .    '<img class="iconDefinitionList" src="' . get_icon_url('course') . '" alt="" />'
                .    '<small>'
                .    '<a href="' . htmlspecialchars( $url ) . '">'
                .    $course_order_by
                .    $userStatusImg
                .    '</a>' . "\n"
                .    '</small>' . "\n"
                .    '</dt>' . "\n"
                .    '<dd>'
                .    '<small>'
                .    '<a href="' . $url . '">'
                .    htmlspecialchars( $thisCourse['officialCode'] )
                .    '</a>' . "\n"
                .    '<small>' . "\n"
                .    ' : ' . htmlspecialchars( $thisCourse['titular'] . $course_language_txt )
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
            .    '<img class="iconDefinitionList" src="' . get_icon_url('course') . '" alt="" />'
            .    get_lang('No courses') . "\n"
            .    '</dt>' . "\n"
            .    '</dl>' . "\n"
            ;
        }

        $this->content = $output;

        return $this->content;
    }

    public function renderTitle()
    {
        return get_lang('My course list');
    }
}
