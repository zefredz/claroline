<?php // $Id$
/**
 * CLAROLINE
 * This tool run some check to detect abnormal situation
 *
 * @version 1.7 $Revision$
 *
 * @copyright 2001-2005 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @see http://www.claroline.net/wiki/index.php/ADMIN
 *
 * @author Claro Team <cvs@claroline.net>
 * @author Sébastien Piraux <pir@claroline.net>
 * @author Christophe Gesché <moosh@claroline.net>
 *
 */

require '../inc/claro_init_global.inc.php';

// Security check
if ( ! $_uid ) claro_disp_auth_form();
if ( ! $is_platformAdmin ) claro_die(get_lang('NotAllowed'));

$interbredcrump[]= array ('url' => 'index.php', 'name' => get_lang('Administration'));

$nameTools = get_lang('StatsOfCampus');

// regroup table names for maintenance purpose
/*
 * DB tables definition
 */

$tbl_mdb_names       = claro_sql_get_main_tbl();
$tbl_cdb_names       = claro_sql_get_course_tbl();
$tbl_course          = $tbl_mdb_names['course'           ];
$tbl_rel_course_user = $tbl_mdb_names['rel_course_user'  ];
$tbl_user            = $tbl_mdb_names['user'             ];
$tbl_track_e_default = $tbl_mdb_names['track_e_default'];
$tbl_track_e_login   = $tbl_mdb_names['track_e_login'];
$tbl_track_e_open    = $tbl_mdb_names['track_e_open'];

$tbl_document        = $tbl_cdb_names['document'         ];

$toolNameList = claro_get_tool_name_list();

require_once $includePath . '/lib/statsUtils.lib.inc.php';

// used in strange cases, a course is unused if not used since $limitBeforeUnused
// INTERVAL SQL expr. see http://www.mysql.com/doc/en/Date_and_time_functions.html
$limitBeforeUnused = "INTERVAL 6 MONTH";

$is_allowedToTrack     = $is_platformAdmin;

include $includePath . '/claro_init_header.inc.php';
echo claro_disp_tool_title(
    array(
    'mainTitle'=>$nameTools,
    )
    );

if( $is_allowedToTrack && $is_trackingEnabled)
{
    // in $view, a 1 in X posof the $view string means that the 'category' number X
    // will be show, 0 means don't show
    echo "\n".'<small>'
            .'[<a href="'.$_SERVER['PHP_SELF'].'?view=1111111">'.get_lang('ShowAll').'</a>]'
            .'&nbsp;[<a href="'.$_SERVER['PHP_SELF'].'?view=0000000">'.get_lang('ShowNone').'</a>]'
            .'</small>'."\n\n";

    if( isset($_REQUEST['view']) )  $view = $_REQUEST['view'];
    else                            $view = "0000000";

    /***************************************************************************
     *
     *        Main
     *
     ***************************************************************************/
    $tempView = $view;
    echo '<p>'."\n";
    if($view[0] == '1')
    {
        $tempView[0] = '0';
        echo '-&nbsp;&nbsp;<b>'.get_lang('PlatformStats').'</b>&nbsp;&nbsp;&nbsp;<small>[<a href="'.$_SERVER['PHP_SELF'].'?view='.$tempView.'">'.get_lang('Close').'</a>]</small><br />'."\n";
        //---- COURSES
        echo "\n".'<br />&nbsp;&nbsp;&nbsp;<b>'.get_lang('Courses').'</b><br />'."\n";
        //--  number of courses
        $sql = "SELECT count(*)
                    FROM `".$tbl_course."`";
        $count = claro_sql_query_get_single_value($sql);
        echo '&nbsp;&nbsp;&nbsp;'.get_lang('CountCours').' : '.$count.'<br />'."\n";

        //--  number of courses by faculte
        $sql = "SELECT `faculte`, count( * ) AS `nbr`
                    FROM `".$tbl_course."`
                    WHERE `faculte` IS NOT NULL
                    GROUP BY `faculte`";
        $results = claro_sql_query_fetch_all($sql);
        echo '&nbsp;&nbsp;&nbsp;'.get_lang('CountCourseByFaculte').' : <br />'."\n";
        buildTab2Col($results);
        echo '<br />'."\n";

        //--  number of courses by language
        $sql = "SELECT `languageCourse`, count( * ) AS `nbr`
                    FROM `".$tbl_course."`
                    WHERE `languageCourse` IS NOT NULL
                    GROUP BY `languageCourse`";
        $results = claro_sql_query_fetch_all($sql);
        echo '&nbsp;&nbsp;&nbsp;'.get_lang('CountCourseByLanguage').' : <br />'."\n";
        buildTab2Col($results);
        echo '<br />'."\n";
        //--  number of courses by visibility
        $sql = "SELECT `visible`, count( * ) AS `nbr`
                    FROM `".$tbl_course."`
                    WHERE `visible` IS NOT NULL
                    GROUP BY `visible`";

        $results = claro_sql_query_fetch_all($sql);
        $results = changeResultOfVisibility($results);
        echo '&nbsp;&nbsp;&nbsp;'.get_lang('CountCourseByVisibility').' : <br />'."\n";
        buildTab2Col($results);
        echo '<br />'."\n";

        //-- USERS
        echo "\n".'<br />&nbsp;&nbsp;&nbsp;<b>'.get_lang('Users').'</b><br />'."\n";

        //--  total number of users
        $sql = "SELECT count(*)
                    FROM `".$tbl_user."`";
        $count = claro_sql_query_get_single_value($sql);
        echo '&nbsp;&nbsp;&nbsp;'.get_lang('CountUsers').' : '.$count.'<br />'."\n";

        //--  number of users by course
        $sql = "SELECT C.`code`, count( CU.user_id ) as `nb`
                    FROM `".$tbl_course."` C, `".$tbl_rel_course_user."` CU
                    WHERE CU.`code_cours` = C.`code`
                        AND `code` IS NOT NULL
                    GROUP BY C.`code`
                    ORDER BY nb DESC";
        $results = claro_sql_query_fetch_all($sql);
        echo '&nbsp;&nbsp;&nbsp;'.get_lang('CountUsersByCourse').' : <br />'."\n";
        buildTab2Col($results);
        echo '<br />'."\n";

        //--  number of users by faculte
        $sql = "SELECT C.`faculte`, count( CU.`user_id` ) AS `nbr`
                    FROM `".$tbl_course."` C, `".$tbl_rel_course_user."` CU
                    WHERE CU.`code_cours` = C.`code`
                        AND C.`faculte` IS NOT NULL
                    GROUP BY C.`faculte`";
        $results = claro_sql_query_fetch_all($sql);
        echo '&nbsp;&nbsp;&nbsp;'.get_lang('CountUsersByFaculte').' : <br />'."\n";
        buildTab2Col($results);
        echo '<br />'."\n";

        //--  number of users by status
        $sql = "SELECT `statut`, count( `user_id` ) AS `nbr`
                    FROM `".$tbl_user."`
                    WHERE `statut` IS NOT NULL
                    GROUP BY `statut`";
        $results = claro_sql_query_fetch_all($sql);
        echo '&nbsp;&nbsp;&nbsp;'.get_lang('CountUsersByStatus').' : <br />'."\n";
        buildTab2Col($results);
        echo '<br />'."\n";
    }
    else
    {
        $tempView[0] = '1';
        echo '+&nbsp;&nbsp;&nbsp;<a href="'.$_SERVER['PHP_SELF'].'?view='.$tempView.'">'.get_lang('PlatformStats').'</a>'."\n";
    }
    echo '</p>'."\n\n";

    /***************************************************************************
     *
     *        Platform access and logins
     *
     ***************************************************************************/
    $tempView = $view;
    echo '<p>'."\n";
    if($view[1] == '1')
    {
        $tempView[1] = '0';
        echo '-&nbsp;&nbsp;<b>'.get_lang('PlatformAccess').'</b>&nbsp;&nbsp;&nbsp;<small>[<a href="'.$_SERVER['PHP_SELF'].'?view='.$tempView.'">'.get_lang('Close').'</a>]</small><br />'."\n";

        //----------------------------  access
        echo "\n".'<br />&nbsp;&nbsp;&nbsp;<b>'.get_lang('Access').'</b> '.get_lang('AccessExplain').'<br />'."\n";

        //--  all
        $sql = "SELECT count(*)
                    FROM `".$tbl_track_e_open."`";
        $count = claro_sql_query_get_single_value($sql);
        echo '&nbsp;&nbsp;&nbsp;'.get_lang('TotalPlatformAccess').' : '.$count.'<br />'."\n";

        //--  last 31 days
        $sql = "SELECT count(*)
                    FROM `".$tbl_track_e_open."`
                    WHERE (`open_date` > DATE_ADD(CURDATE(), INTERVAL -31 DAY))";
        $count = claro_sql_query_get_single_value($sql);
        echo '&nbsp;&nbsp;&nbsp;'.get_lang('Last31days').' : '.$count.'<br />'."\n";

        //--  last 7 days
        $sql = "SELECT count(*)
                    FROM `".$tbl_track_e_open."`
                    WHERE (`open_date` > DATE_ADD(CURDATE(), INTERVAL -7 DAY))";
        $count = claro_sql_query_get_single_value($sql);
        echo '&nbsp;&nbsp;&nbsp;'.get_lang('Last7Days').' : '.$count.'<br />'."\n";

        //--  yesterday
        $sql = "SELECT count(*)
                    FROM `".$tbl_track_e_open."`
                    WHERE (`open_date` > DATE_ADD(CURDATE(), INTERVAL -1 DAY))
                      AND (`open_date` < CURDATE() )";
        $count = claro_sql_query_get_single_value($sql);
        echo '&nbsp;&nbsp;&nbsp;'.get_lang('Yesterday').' : '.$count.'<br />'."\n";

        //--  today
        $sql = "SELECT count(*)
                    FROM `".$tbl_track_e_open."`
                    WHERE (`open_date` > CURDATE() )";
        $count = claro_sql_query_get_single_value($sql);
        echo '&nbsp;&nbsp;&nbsp;'.get_lang('Thisday').' : '.$count.'<br />'."\n";

        //---------------------------- view details of traffic
        echo "\n".'<br />&nbsp;&nbsp;&nbsp;<b>'.get_lang('TrafficDetails').'</b><br />'."\n"
                .'&nbsp;&nbsp;&nbsp;<a href="traffic_details.php">'.get_lang('_click_here').'</a><br />'."\n";


        //----------------------------  logins
        echo "\n".'<br />&nbsp;&nbsp;&nbsp;<b>'.get_lang('Logins').'</b><br />'."\n";

        //--  all
        $sql = "SELECT count(*)
                    FROM `".$tbl_track_e_login."`";
        $count = claro_sql_query_get_single_value($sql);
        echo '&nbsp;&nbsp;&nbsp;'.get_lang('TotalPlatformLogin').' : '.$count.'<br />'."\n";

        //--  last 31 days
        $sql = "SELECT count(*)
                    FROM `".$tbl_track_e_login."`
                    WHERE (`login_date` > DATE_ADD(CURDATE(), INTERVAL -31 DAY))";
        $count = claro_sql_query_get_single_value($sql);
        echo '&nbsp;&nbsp;&nbsp;'.get_lang('Last31days').' : '.$count.'<br />'."\n";

        //--  last 7 days
        $sql = "SELECT count(*)
                    FROM `".$tbl_track_e_login."`
                    WHERE (`login_date` > DATE_ADD(CURDATE(), INTERVAL -7 DAY))";
        $count = claro_sql_query_get_single_value($sql);
        echo '&nbsp;&nbsp;&nbsp;'.get_lang('Last7Days').' : '.$count.'<br />'."\n";

        //--  yesterday
        $sql = "SELECT count(*)
                    FROM `".$tbl_track_e_login."`
                    WHERE (`login_date` > DATE_ADD(CURDATE(), INTERVAL -1 DAY))
                      AND (`login_date` < CURDATE() )";
        $count = claro_sql_query_get_single_value($sql);
        echo '&nbsp;&nbsp;&nbsp;'.get_lang('Yesterday').' : '.$count.'<br />'."\n";

        //--  today
        $sql = "SELECT count(*)
                    FROM `".$tbl_track_e_login."`
                    WHERE (`login_date` > CURDATE() )";
        $count = claro_sql_query_get_single_value($sql);
        echo '&nbsp;&nbsp;&nbsp;'.get_lang('Thisday').' : '.$count.'<br />'."\n";

    }
    else
    {
        $tempView[1] = '1';
        echo '+&nbsp;&nbsp;&nbsp;<a href="'.$_SERVER['PHP_SELF'].'?view='.$tempView.'">'.get_lang('PlatformAccess').'</a>'."\n";
    }
    echo '</p>'."\n\n";

    /***************************************************************************
     *
     *        Access to courses
     *     // due to the moving of access tables in course DB this part of the code exec (nbCourser+1) queries
     *     // this can create heavy overload on servers ... should be reconsidered
     *
     ***************************************************************************/
    $tempView = $view;
    echo '<p>'."\n";
    if($view[2] == '1')
    {
        $tempView[2] = '0';
        echo '-&nbsp;&nbsp;<b>'.get_lang('PlatformCoursesAccess').'</b>&nbsp;&nbsp;&nbsp;<small>[<a href="'.$_SERVER['PHP_SELF'].'?view='.$tempView.'">'.get_lang('Close').'</a>]</small><br />'."\n";
        // display list of course of the student with links to the corresponding userLog
        $sql = "SELECT `fake_code`, `dbName`
                FROM    `".$tbl_course."`
                ORDER BY code ASC";
        $resCourseList = claro_sql_query_fetch_all($sql);
        $i=0;
        foreach( $resCourseList as $course )
        {
            $TABLEACCESSCOURSE = $courseTablePrefix . $course['dbName'] . $dbGlu . "track_e_access";
            $sql = "SELECT count( `access_id` ) AS nb
                      FROM `".$TABLEACCESSCOURSE."`
                      WHERE `access_tid` IS NULL
                      ORDER BY nb DESC";
            $count = claro_sql_query_get_single_value($sql);

            $resultsArray[$i][0] = $course['fake_code'];
            $resultsArray[$i][1] = $count;
            $i++;
        }

        echo "\n".'<br />&nbsp;&nbsp;&nbsp;<b>'.get_lang('Access').'</b><br />'."\n";
        buildTab2Col($resultsArray);
    }
    else
    {
        $tempView[2] = '1';
        echo '+&nbsp;&nbsp;&nbsp;<a href="'.$_SERVER['PHP_SELF'].'?view='.$tempView.'">'.get_lang('PlatformCoursesAccess').'</a>'."\n";
    }
    echo '</p>'."\n\n";

    /***************************************************************************
     *
     *        Access to tools
     *     // due to the moving of access tables in course DB this part of the code exec (nbCourser+1) queries
     *     // this can create heavy overload on servers ... should be reconsidered
     *
     ***************************************************************************/
    $tempView = $view;
    echo '<p>'."\n";
    if($view[3] == '1')
    {
        $tempView[3] = '0';
         echo '-&nbsp;&nbsp;<b>'.get_lang('ToolsAccess').'</b>&nbsp;&nbsp;&nbsp;<small>[<a href="'.$_SERVER['PHP_SELF'].'?view='.$tempView.'">'.get_lang('Close').'</a>]</small><br />'."\n";
        // display list of course of the student with links to the corresponding userLog
        $sql = "SELECT code, dbName
              FROM    `".$tbl_course."`
              ORDER BY code ASC";

        $resCourseList = claro_sql_query_fetch_all($sql);

        foreach ( $resCourseList as $course )
        {
            $TABLEACCESSCOURSE = $courseTablePrefix . $course['dbName'] . $dbGlu . "track_e_access";
            $sql = "SELECT count( `access_id` ) AS nb, `access_tlabel`
                    FROM `".$TABLEACCESSCOURSE."`
                    WHERE `access_tid` IS NOT NULL
                    GROUP BY `access_tid`";

            $access = claro_sql_query_fetch_all($sql);

            // look for each tool of the course in re
            foreach( $access as $count )
            {
                if ( !isset($resultsTools[$count['access_tlabel']]) )
                {
                    $resultsTools[$count['access_tlabel']] = $count['nb'];
                }
                else
                {
                    $resultsTools[$count['access_tlabel']] += $count['nb'];
                }
            }
        }

      echo '<table cellpadding="2" cellspacing="1" class="claroTable" align="center">'
         . '<thead>'
         . '<tr class="headerX">'."\n"
         . '<th>&nbsp;'.get_lang('ToolTitleToolnameColumn').'</th>'."\n"
         . '<th>&nbsp;'.get_lang('ToolTitleCountColumn').'</th>'."\n"
         . '</tr>'
         . '</thead>'."\n"
         . '<tbody>'."\n"
         ;

      if (is_array($resultsTools))
      {
          arsort($resultsTools);
          foreach( $resultsTools as $tool => $nbr)
          {
              echo '<tr>' . "\n"
                 . '<td>' . $toolNameList[$tool].'</td>'."\n"
                 . '<td>' . $nbr.'</td>'."\n"
                 . '</tr>' . "\n\n"
                 ;
          }
      }
      else
      {
          echo '<tr>'."\n"
             . '<td colspan="2"><center>'.get_lang('NoResult').'</center></td>'."\n"
             . '</tr>'."\n"
             ;
      }

      echo '</tbody>'."\n"
         . '</table>'."\n\n"
         ;
    }
    else
    {
        $tempView[3] = '1';
        echo '+&nbsp;&nbsp;&nbsp;<a href="'.$_SERVER['PHP_SELF'].'?view='.$tempView.'">'.get_lang('ToolsAccess').'</a>';
    }
    echo '</p>'."\n\n";
}
else // not allowed to track
{
    if(!$is_trackingEnabled) echo get_lang('TrackingDisabled');
    else                     echo get_lang('NotAllowed');
}

include $includePath . '/claro_init_footer.inc.php';
?>