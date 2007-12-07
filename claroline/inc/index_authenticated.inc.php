<?php // $Id$
if ( ! defined('CLARO_INCLUDE_ALLOWED') ) die('---');
/**
 * CLAROLINE
 *
 * this  is  the  home page  of a campus  for an authenticated user
 * this  page  list of users subscribed courses
 * when the user is anonymous, index anonymous.inc.php
 * is load instead of this code.
 *
 * @version 1.7 $Revision$
 *
 * @copyright (c) 2001-2005 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @author claroline Team <cvs@claroline.net>
 *
 * @package CLINDEX
 *
 */

if ( ! isset($_uid) ) claro_disp_auth_form();

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
                 AND course_user.user_id = '" . (int) $_uid . "'";

if ( empty($course_order_by) || $course_order_by == 'official_code' )
{
    $sql .= " ORDER BY UPPER(`fake_code`), `title`";
}
else
{
    $sql .= " ORDER BY `title`, UPPER(`fake_code`)";
}

$personnalCourseList = claro_sql_query_fetch_all($sql);

/*
 * get a digest of announcement and calendar of each of these courses
 */

$courseDigestList = array('courseSysCode' => array(),
                          'courseOfficialCode' => array(),
                          'toolLabel' => array(),
                          'date' => array(),
                          'content' => array());

foreach($personnalCourseList as $thisCourse)
{
    /*
     * ANNOUNCEMENTS : get announcements of this course since last user loggin
     */

    $tableAnn = $courseTablePrefix . $thisCourse['db'] . $dbGlu . 'announcement';

    $sql = "SELECT '" . addslashes($thisCourse['sysCode']) ."' AS `courseSysCode`,
                   '" . addslashes($thisCourse['officialCode']) ."' AS `courseOfficialCode`,
                   'CLANN___' AS `toolLabel`,
                   CONCAT(`temps`, ' ', '00:00:00') AS `date`,
                   CONCAT(`title`,' - ',`contenu`) AS `content`
            FROM `" . $tableAnn . "`
            WHERE    CONCAT(`title`, `contenu`) != ''
              AND    DATE_FORMAT( `temps`, '%Y %m %d') >= '".date('Y m d', $_user['lastLogin'])."'
              AND    visibility = 'SHOW'
            ORDER BY `date` DESC
            LIMIT     1";

    $resultList = claro_sql_query_fetch_all_cols($sql);

    foreach($resultList as $colName => $colValue)
    {
        if (count($colValue) == 0) break;
        $courseDigestList[$colName] = array_merge($courseDigestList[$colName], $colValue);
    }

    /*
     * AGENDA : get the next agenda entries of this course from now
     */

    $tableCal = $courseTablePrefix . $thisCourse['db'] . $dbGlu . 'calendar_event';

    $sql = "SELECT '". addslashes($thisCourse['sysCode']) ."' AS `courseSysCode`,
                   '". addslashes($thisCourse['officialCode']) ."' AS `courseOfficialCode`,
                   'CLCAL___' AS `toolLabel`,
            CONCAT(`day`, ' ',`hour`) AS `date`,
            CONCAT(`titre`,' - ',`contenu`) AS `content`
            FROM `" . $tableCal . "`
            WHERE CONCAT(`day`, ' ',`hour`) >= CURDATE()
              AND CONCAT(`titre`, `contenu`) != ''
              AND    visibility = 'SHOW'
            ORDER BY `date`
            LIMIT 1";

    $resultList = claro_sql_query_fetch_all_cols($sql);

    foreach($resultList as $colName => $colValue)
    {
        if (count($colValue) == 0) break;
        $courseDigestList[$colName] = array_merge($courseDigestList[$colName], $colValue);
    }

} // end foreach($personnalCourseList as $thisCourse)



/*
 * Sort all these digest by date
 */

array_multisort( $courseDigestList['toolLabel'         ],
                 $courseDigestList['date'              ],
                 $courseDigestList['courseOfficialCode'],
                 $courseDigestList['courseSysCode'     ],
                 $courseDigestList['content'           ] );


          /*> > > > > > > > > > > > DISPLAY < < < < < < < < < < < < */

echo '<table width="100%" border="0" cellpadding="4" >' . "\n\n"
.    '<tr valign="top">' . "\n"
.    '<td><!-- LEFT COLUMN -->' . "\n"
;

@include './textzone_top.inc.html'; // Introduction message if needed

if ($is_platformAdmin)
{
    echo '&nbsp;'
    .    '<a style="font-size: smaller" href="claroline/admin/managing/editFile.php?cmd=edit&amp;file=0">'
    .    '<img src="claroline/img/edit.gif" alt="" />' . $langEditTextZone
    .    '</a>' . "\n"
    ;
}

echo claro_disp_tool_title($langMyCourses);

/*
 * Commands line
 */

echo "<p>"
    ."<small>\n"
    ."<b>";

    if ($is_allowedCreateCourse) /* 'Create Course Site' command.
                                     Only available for teacher. */
    {
        echo '<a href="claroline/create_course/add_course.php">'
        .    '<img src="' . $imgRepositoryWeb . 'course.gif" alt="" /> '
        .    $langCourseCreate
        .    '</a>'
        ;
        if ($allowToSelfEnroll) echo '&nbsp;|&nbsp;';
    }

    if ($allowToSelfEnroll)
    {
        echo '<a href="claroline/auth/courses.php?cmd=rqReg&amp;category=">'
        .    '<img src="'.$imgRepositoryWeb.'enroll.gif" alt="" /> '
        .    $lang_enroll_to_a_new_course
        .    '</a>'
        .    '&nbsp;|&nbsp;'

        .    '<a href="claroline/auth/courses.php?cmd=rqUnreg">'
        .    '<img src="'.$imgRepositoryWeb.'unenroll.gif" alt="" /> '
        .    $lang_remove_course_enrollment
        .    '</a>'
        .    '</b>'
        .    '</small>' . "\n"
        .    '</p>' . "\n"
        ;
    }

/*
 * Course List
 */


// get the list of personnal courses marked as contening new events

$date = $claro_notifier->get_notification_date($_uid);

$modified_course = $claro_notifier->get_notified_courses($date,$_uid);

//display list

echo '<ul style="list-style-image:url(claroline/img/course.gif);list-style-position:inside">'."\n";

foreach($personnalCourseList as $thisCourse)
{

    // if the course contains new things to see since last login, its name will be displayed in bold text in the list

    if (in_array ($thisCourse['sysCode'], $modified_course))
    {
        $classItem = " hot";
    }
    else // otherwise just display its name normally
    {
        $classItem = '';
    }

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

    if ( empty($course_order_by) || $course_order_by == 'official_code' )
    {
        echo $thisCourse['officialCode'] . ' - ' . $thisCourse['title'];
    }
    else
    {
        echo $thisCourse['title'] . ' (' . $thisCourse['officialCode'] . ')';
    }

    echo '</a>'
    .    '<br />'
    .    '<small>' . $thisCourse['titular'] . $course_language_txt . '</small>' . "\n"
    .    '</li>' ."\n"
    ;

} // end foreach($personnalCourseList as $thisCourse)

echo '</ul>' . "\n";

//display legend

echo "<br /><small><span class=\"item hot\"> ".$langNewLegend."</span></small>";
echo '</td>' . "\n";


//display right menu

echo '<td width="200" class="claroRightMenu"><!-- RIGHT COLUMN -->' . "\n";

    $title = '';

    for( $i=0, $itemCount = count($courseDigestList['toolLabel']); $i < $itemCount; $i++)
    {
        switch ($courseDigestList['toolLabel'][$i])
        {
            case 'CLANN___':
                $itemIcon = 'announcement.gif';
                $url = 'claroline/announcements/announcements.php?cidReq='
                     . $courseDigestList['courseSysCode'][$i];
                $name = $langValvas;
                break;


            case 'CLCAL___':
                $itemIcon = 'agenda.gif';
                $url = 'claroline/calendar/agenda.php?cidReq='
                     . $courseDigestList['courseSysCode'][$i];
                $name = $langAgendaNextEvents;
                break;
        }

        if ($title != $name)
        {
            $title = $name;
            echo "<h4>".$title."</h4>\n";
        }

        $courseDigestList['content'][$i] = preg_replace('/<br( \/)?>/', ' ', $courseDigestList['content'][$i]);
        $courseDigestList['content'][$i] = strip_tags($courseDigestList['content'][$i]);
        $courseDigestList['content'][$i] = substr($courseDigestList['content'][$i],0, $max_char_from_content);

        echo '<p>' . "\n"
        .    '<small>'
        .    '<a href="' . $url . '">'
        .    '<img src="' . $imgRepositoryWeb . $itemIcon . '" alt="" />'
        .    '</a>'

        .    claro_disp_localised_date( $dateFormatLong,
                                     strtotime($courseDigestList['date'][$i]) )
        .    '<br />' . "\n"
        .    '<a href="' . $url . '">'
        .    $courseDigestList['courseOfficialCode'][$i]
        .    '</a> : ' . "\n"
        .    '<small>' . $courseDigestList['content'][$i] . '</small>'
        .    '</small>'
        .    '</p>' . "\n"
        ;
    } // end for( $i=0, ... $i < $itemCount; $i++)

?>
<div align="center">
<a href="claroline/calendar/myagenda.php"><?php echo $langMyAgenda ?></a>
</div>

<hr noshade size="1">

<p>
<a href="http://www.claroline.net/documentation.htm"><?php echo $langDocumentation ?></a>
</p>

<?php
    if ($is_platformAdmin) /* Admin Section links.
                            Only available for platform administrator */
    {
?>
<p><a href="claroline/admin/"><?php echo $langPlatformAdministration ?></a></p>

<?php
    } // end if is_platformAdmin


if (file_exists('./textzone_right.inc.html')) include './textzone_right.inc.html';

if ($is_platformAdmin)
{
    echo '&nbsp;'
    .    '<a style="font-size: smaller" href="claroline/admin/managing/editFile.php?cmd=edit&amp;file=1">'
    .    '<img src="claroline/img/edit.gif" alt="" />' . $langEditTextZone
    .    '</a>' . "\n"
    ;
}


?>

</td>

</tr>
</table>
