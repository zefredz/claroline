<?php // $Id$
if ( count( get_included_files() ) == 1 ) die( '---' );

/******************************************************************************
 * CLAROLINE
 ******************************************************************************
 * This module displays the course list of a the current authenticated user
 *
 * @version 1.9 $Revision$
 * @copyright (c) 2001-2008 Universite catholique de Louvain (UCL)
 * @license (GPL) GENERAL PUBLIC LICENSE - http://www.gnu.org/copyleft/gpl.html
 * @package CLINDEX
 ******************************************************************************/

if ( ! claro_is_user_authenticated() ) claro_disp_auth_form();

$personnalCourseList = get_user_course_list(claro_get_current_user_id());

// get the list of personnal courses marked as contening new events
$date            = $claroline->notification->get_notification_date(claro_get_current_user_id());
$modified_course = $claroline->notification->get_notified_courses($date,claro_get_current_user_id());


/******************************************************************************
                                    DISPLAY
******************************************************************************/

echo claro_html_tool_title(get_lang('My course list'));

//display list

if (count($personnalCourseList))
{
echo '<ul style="list-style-image:url(claroline/img/course.gif);list-style-position:inside">'."\n";

foreach($personnalCourseList as $thisCourse)
{
    // If the course contains new things to see since last user login,
    // The course name will be displayed with the 'hot' class style in the list.
    // Otherwise it will name normally be displaied

    if (in_array ($thisCourse['sysCode'], $modified_course)) $classItem = ' hot';
    else                                                     $classItem = '';

    // show course language if not the same of the platform
    if ( get_conf('platformLanguage') != $thisCourse['language'] )
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

    echo '<li class="item' . $classItem . '">' . "\n"
    .    '<a href="' .  get_path('url') . '/claroline/course/index.php?cid=' . htmlspecialchars($thisCourse['sysCode']) . '">';

    if ( get_conf('course_order_by') == 'official_code' )
    {
        echo $thisCourse['officialCode'] . ' - ' . $thisCourse['title'];
    }
    else
    {
        echo $thisCourse['title'] . ' (' . $thisCourse['officialCode'] . ')';
    }

    if ($thisCourse['isCourseManager'] == 1)
    {
        $userStatusImg = '<img src="' . get_icon_url('manager') . '" alt="'.get_lang('Course manager').'" />';
    }
    else
    {
        $userStatusImg = '';
    }


    echo '</a>'
    .    $userStatusImg
    .    '<br />'
    .    '<small>' . $thisCourse['titular'] . $course_language_txt . '</small>' . "\n"
    .    '</li>' ."\n"
    ;

} // end foreach($personnalCourseList as $thisCourse)

echo '</ul>' . "\n";
}
//display legend if required
if( !empty($modified_course) )
{
    echo '<br />'
    .    '<small><span class="item hot"> '.get_lang('denotes new items').'</span></small>'
    .     '</td>' . "\n";
}
