<?php # -$Id$

/******************************************************************************
 * CLAROLINE
 ******************************************************************************
 * This module displays a cross course digest for the current authenticated user
 *
 * @version 1.8 $Revision$
 * @copyright (c) 2001-2006 Universite catholique de Louvain (UCL)
 * @license (GPL) GENERAL PUBLIC LICENSE - http://www.gnu.org/copyleft/gpl.html
 * @package CLINDEX
 ******************************************************************************/


if ((bool) stristr($_SERVER['PHP_SELF'], basename(__FILE__))) die('---');
if ( ! isset($_uid) ) claro_disp_auth_form();

$courseDigestList = array('courseSysCode'      => array(),
                          'courseOfficialCode' => array(),
                          'toolLabel'          => array(),
                          'date'               => array(),
                          'content'            => array());

$personnalCourseList = get_user_course_list($_uid);

foreach($personnalCourseList as $thisCourse)
{
    /*
     * ANNOUNCEMENTS : get announcements of this course since last user loggin
     */

    $tableAnn = $courseTablePrefix . $thisCourse['db'] . $dbGlu . 'announcement';

    $sql = "SELECT '" . addslashes($thisCourse['sysCode']     ) ."' AS `courseSysCode`,
                   '" . addslashes($thisCourse['officialCode']) ."' AS `courseOfficialCode`,
                   'CLANN___'                                       AS `toolLabel`,
                   CONCAT(`temps`, ' ', '00:00:00')                 AS `date`,
                   CONCAT(`title`,' - ',`contenu`)                  AS `content`

            FROM `" . $tableAnn . "`
            WHERE    CONCAT(`title`, `contenu`) != ''
              AND    DATE_FORMAT( `temps`, '%Y %m %d') >= '".date('Y m d', $_user['lastLogin'])."'

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

    $sql = "SELECT '". addslashes($thisCourse['sysCode']     ) ."' AS `courseSysCode`,
                   '". addslashes($thisCourse['officialCode']) ."' AS `courseOfficialCode`,
                   'CLCAL___' AS `toolLabel`,
            CONCAT(`day`, ' ',`hour`) AS `date`,
            CONCAT(`titre`,' - ',`contenu`) AS `content`
            FROM `" . $tableCal . "`
            WHERE CONCAT(`day`, ' ',`hour`) >= CURDATE()
              AND CONCAT(`titre`, `contenu`) != ''
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

/******************************************************************************
                                    DISPLAY
 ******************************************************************************/

    $title = '';

for( $i=0, $itemCount = count($courseDigestList['toolLabel']); $i < $itemCount; $i++)
{
    switch ($courseDigestList['toolLabel'][$i])
    {
        case 'CLANN___':
            $itemIcon = 'announcement.gif';
            $url = 'claroline/announcements/announcements.php?cidReq='
                 . $courseDigestList['courseSysCode'][$i];
            $name = get_lang('Valvas');
            break;

        case 'CLCAL___':
            $itemIcon = 'agenda.gif';
            $url = 'claroline/calendar/agenda.php?cidReq='
                 . $courseDigestList['courseSysCode'][$i];
            $name = get_lang('AgendaNextEvents');
            break;
    }

    if ($title != $name)
    {
        $title = $name;
        echo "<h4>".$title."</h4>\n";
    }

    $courseDigestList['content'][$i] = preg_replace('/<br( \/)?>/', ' ', $courseDigestList['content'][$i]);
    $courseDigestList['content'][$i] = strip_tags($courseDigestList['content'][$i]);
    $courseDigestList['content'][$i] = substr($courseDigestList['content'][$i],0, get_conf('max_char_from_content') );

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