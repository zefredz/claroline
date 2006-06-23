<?php // $Id$

/******************************************************************************
 * CLAROLINE
 ******************************************************************************
 * This module displays the course list of a the current authenticated user
 *
 * @version 1.8 $Revision$
 * @copyright (c) 2001-2006 Universite catholique de Louvain (UCL)
 * @license (GPL) GENERAL PUBLIC LICENSE - http://www.gnu.org/copyleft/gpl.html
 * @package CLINDEX
 ******************************************************************************/

// Prevent direct reference to the script by external browser
if ((bool) stristr($_SERVER['PHP_SELF'], basename(__FILE__))) die();

if ( ! isset($_uid) ) claro_disp_auth_form();

$personnalCourseList = get_user_course_list($_uid);

// get the list of personnal courses marked as contening new events
$date            = $claro_notifier->get_notification_date($_uid);
$modified_course = $claro_notifier->get_notified_courses($date,$_uid);



/******************************************************************************
                                    DISPLAY
******************************************************************************/

echo claro_html_tool_title(get_lang('My course list'));

//display list

echo '<ul style="list-style-image:url(claroline/img/course.gif);list-style-position:inside">'."\n";

foreach($personnalCourseList as $thisCourse)
{
    // If the course contains new things to see since last user login, 
    // The course name will be displayed with the 'hot' class style in the list.
    // Otherwise it will name normally be displaied

    if (in_array ($thisCourse['sysCode'], $modified_course)) $classItem = ' hot';
    else                                                     $classItem = '';

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

    echo '<li class="item' . $classItem . '">' . "\n"
    .    '<a href="' . $urlAppend . '/claroline/course/index.php?cid=' . htmlspecialchars($thisCourse['sysCode']) . '">';

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
        $userStatusImg = '<img src="'.$imgRepositoryWeb.'manager.gif" alt="'.get_lang('Course manager').'">';
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

//display legend

echo '<br />'
.    '<small><span class="item hot"> '.get_lang('denotes new items').'</span></small>';
echo '</td>' . "\n";


?>
