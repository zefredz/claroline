<?php # -$Id$

//----------------------------------------------------------------------
// CLAROLINE
//----------------------------------------------------------------------
// Copyright (c) 2001-2003 Universite catholique de Louvain (UCL)
//----------------------------------------------------------------------
// This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
// as published by the FREE SOFTWARE FOUNDATION. The GPL is available
// through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
//----------------------------------------------------------------------
// Authors: see 'credits' file
//----------------------------------------------------------------------

if ( ! isset($_uid) ) die ('not allowed');

$sql = "SELECT course.code           `sysCode`, 
               course.directory      `directory`, 
               course.fake_code      `officialCode`, 
               course.dbName         `db`,
               course.intitule       `title`, 
               course.titulaires     `titular`,
               course.languageCourse `language`,
               course_user.statut    `userSatus`

               FROM `".$tbl_courses."`           course,
                    `".$tbl_link_user_courses."` course_user
                                 
               WHERE course.code         = course_user.code_cours
                 AND course_user.user_id = '".$_uid."'
               ORDER BY UPPER(fake_code)";

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

    $sql = "SELECT '".$thisCourse['sysCode'     ]."'  `courseSysCode`,
                   '".$thisCourse['officialCode']."'  `courseOfficialCode`,
                   'CLANN___'                         `toolLabel`,
                   CONCAT(`temps`, ' ', '00:00:00')        `date`, 
                   CONCAT(`title`,' - ',`contenu`)      `content`
            FROM `".$tableAnn."`
            WHERE    CONCAT(`title`, `contenu`) != ''
              AND    DATE_FORMAT( `temps`, '%Y %m %d') >= '".date('Y m d', $_user['lastLogin'])."'
            ORDER BY `date` DESC
            LIMIT     1";

    $resultList = claro_sql_query_fetch_all_cols($sql);

    $tableCal = $courseTablePrefix . $thisCourse['db'] . $dbGlu . 'calendar_event';

    foreach($resultList as $colName => $colValue)
    {
        if (count($colValue) == 0) break;
        $courseDigestList[$colName] = array_merge($courseDigestList[$colName], $colValue);
    }

    /*
     * AGENDA : get the next agenda entries of this course from now
     */

    $sql = "SELECT '".$thisCourse['sysCode'     ]."'  `courseSysCode`,
                   '".$thisCourse['officialCode']."'  `courseOfficialCode`,
                   'CLCAL___'               `toolLabel`,
            CONCAT(`day`, ' ',`hour`)       `date`,
            CONCAT(`titre`,' - ',`contenu`)  `content`
            FROM `".$tableCal."`
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


          /*> > > > > > > > > > > > DISPLAY < < < < < < < < < < < < */

echo "<table width=\"100%\" border=\"0\" cellpadding=\"4\" >\n\n"

    ."<tr valign=\"top\">\n"

    ."<td><!-- LEFT COLUMN -->\n";

claro_disp_tool_title($langMyCourses);

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
            .$langCourseCreate
            .'</a>'
            .'&nbsp;|&nbsp;';
    }

    echo '<a href="claroline/auth/courses.php?cmd=rqReg&category=">'
        .$lang_enroll_to_a_new_course
        .'</a>'
        .'&nbsp;|&nbsp;'

        .'<a href="claroline/auth/courses.php?cmd=rqUnreg">'
        .$lang_remove_course_enrollment
        .'</a>'

        ."</b>"
        ."</small>\n"
        ."</p>\n";

/*
 * Course List
 */

echo "<ul>\n";

foreach($personnalCourseList as $thisCourse)
{

    echo "<li>\n"
         ."<a href=\"".$coursesRepositoryWeb.$thisCourse['directory']."/\">"
         .$thisCourse['title']
         ."</a>"
         ."<br>"
         ."<small>"
         .$thisCourse['officialCode']." - ".$thisCourse['titular']
         ."</small>\n"
         ."</li>\n";
} // end foreach($personnalCourseList as $thisCourse)

echo "</ul>\n"

    ."</td>\n"


    ."<td width=\"200\" class=\"claroRightMenu\"><!-- RIGHT COLUMN -->\n";

    $title = '';

    for( $i=0, $itemCount = count($courseDigestList['toolLabel']); $i < $itemCount; $i++)
    {
        switch ($courseDigestList['toolLabel'][$i])
        {
            case 'CLANN___': 
                $itemIcon = 'valves.gif';
                $url = 'claroline/announcements/announcements.php?cidReq='
                       .$courseDigestList['courseSysCode'][$i]; 
                $name = $langValvas;
                break;


            case 'CLCAL___': 
                $itemIcon = 'agenda.gif';
                $url = 'claroline/calendar/agenda.php?cidReq='
                       .$courseDigestList['courseSysCode'][$i];
                $name =  $langAgenda;
                break;
        }
        
        if ($title != $name)
        {
            $title = $name;
            echo "<h4>".$title."</h4>\n";
        }
        

        echo "<p>\n"
            ."<small>"
            ."<a href=\"".$url."\">"
            ."<img src=\"".$clarolineRepositoryWeb."/img/".$itemIcon."\">"
            ."</a>"

            .  claro_format_locale_date( $dateFormatLong,
                                     strtotime($courseDigestList['date'][$i]) )
            ."<br>\n"
            ."<a href=\"".$url."\">"
            .  $courseDigestList['courseOfficialCode'][$i]
            ."</a> : \n"
            ." <small>".strip_tags($courseDigestList['content'][$i])."</small>"
            ."</small>"
            ."</p>\n";
    } // end for( $i=0, ... $i < $itemCount; $i++)

?>
<div align="center">
<a href="claroline/calendar/myagenda.php"><?php echo $langSeeAgenda ?></a>
</div>

<hr noshade size="1">

<p>
<a href="#" onClick="MyWindow=window.open('claroline/help/help_claroline.php','MyWindow','toolbar=no,location=no,directories=no,status=yes,menubar=no,scrollbars=yes,resizable=yes,width=400,height=500,left=300,top=10'); return false;"><?php echo $langHelp ?></a>
</p>
<?php
//---------------------------------------------------------------------------
// 'Conseil pédagogique' link, added from a suggestion of Marcel Lebrun.
// Only valid on iCampus not for Claroline. Thomas, 30.9.2002.
//
//	if ($statut==1)
//	{
//		echo	"<p><a href=\"#\"",
//				"onClick=\"MyWindow=window.open",
//				"('conseil.htm','MyWindow','toolbar=no,location=no,directories=no,status=yes,",
//				"menubar=no,scrollbars=yes,resizable=yes,width=400,height=500,left=300,top=10');",
//				" return false;\">",$langAdvises,"</a></p>";
//	}
//---------------------------------------------------------------------------
?>

<p>
<a href="http://www.claroline.net/documentation.htm"><?= $langDoc ?></a>
</p>

<?php
	if ($is_platformAdmin) /* Admin Section links.
	                        Only available for platform administrator */
	{
?>
<hr noshade size="1">


<p><a href="claroline/admin/"><?= $langPlatformAdmin ?></a></p>

<?php
	} // end if is_platformAdmin
?>


</td>

</tr>
</table>