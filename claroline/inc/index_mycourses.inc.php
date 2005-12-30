<?php // $Id$

/******************************************************************************
 * CLAROLINE
 ******************************************************************************
 * This module displays the course list of a the current authenticated user
 *
 * @version 1.8 $Revision$
 * @copyright (c) 2001-2005 Universite catholique de Louvain (UCL)
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

echo claro_disp_tool_title(get_lang('MyCourses'));

/*
 * Commands line
 */

echo '<p>'
.    '<small>'. "\n"
    .'<b>';

    if ($is_allowedCreateCourse) /* 'Create Course Site' command.
                                     Only available for teacher. */
    {
        echo '<a href="claroline/create_course/add_course.php">'
        .    '<img src="' . $imgRepositoryWeb . 'course.gif" alt="" /> '
        .    get_lang('CourseCreate')
        .    '</a>'
        ;
        if ($allowToSelfEnroll) echo '&nbsp;|&nbsp;';
    }

    if ($allowToSelfEnroll)
    {
        echo '<a href="claroline/auth/courses.php?cmd=rqReg&amp;category=">'
        .    '<img src="'.$imgRepositoryWeb.'enroll.gif" alt="" /> '
        .    get_lang('_enroll_to_a_new_course')
        .    '</a>'
        .    '&nbsp;|&nbsp;'

        .    '<a href="claroline/auth/courses.php?cmd=rqUnreg">'
        .    '<img src="'.$imgRepositoryWeb.'unenroll.gif" alt="" /> '
        .    get_lang('_remove_course_enrollment')
        .    '</a>'
        .    '</b>'
        .    '</small>' . "\n"
        .    '</p>'     . "\n"
        ;
    }

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

    echo '<li class="item' . $classItem . '">' ."\n"
    .    '<a href="' . $coursesRepositoryWeb . $thisCourse['directory'] . '/">';

    if ( get_conf('course_order_by') == 'official_code' )
    {
        echo $thisCourse['officialCode'] . ' - ' . $thisCourse['title'];
    }
    else
    {
        echo $thisCourse['title'] . ' (' . $thisCourse['officialCode'] . ')';
    }

    if ($thisCourse['userSatus'] == 1)
    {
        $userStatusImg = '<img src="'.$imgRepositoryWeb.'manager.gif" alt="'.get_lang('Manager').'">';
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
.    '<small><span class="item hot"> '.get_lang('NewLegend').'</span></small>';
echo '</td>' . "\n";



//////////////////////////////////////////////////////////////////////////////


function get_user_course_list($userId, $renew = false)
{
    static $uid = null, $userCourseList = null;

    if ($uid != $userId || is_null($userCourseList) || $renew)
    {
        $uid = $userId;

        $tbl_mdb_names         = claro_sql_get_main_tbl();
        $tbl_courses           = $tbl_mdb_names['course'           ];
        $tbl_link_user_courses = $tbl_mdb_names['rel_course_user'  ];

        $sql = "SELECT course.code           `sysCode`,
                       course.directory      `directory`,
                       course.fake_code      `officialCode`,
                       course.dbName         `db`,
                       course.intitule       `title`,
                       course.titulaires     `titular`,
                       course.languageCourse `language`,
                       course_user.statut    `userSatus`

                       FROM `" . $tbl_courses . "`           course,
                            `" . $tbl_link_user_courses . "` course_user

                       WHERE course.code         = course_user.code_cours
                         AND course_user.user_id = '" . (int) $userId . "'";

        if ( get_conf('course_order_by') == 'official_code' )
        {
            $sql .= " ORDER BY UPPER(`fake_code`), `title`";
        }
        else
        {
            $sql .= " ORDER BY `title`, UPPER(`fake_code`)";
        }

        $userCourseList = claro_sql_query_fetch_all($sql);
    }
    
    return $userCourseList;
}


?>