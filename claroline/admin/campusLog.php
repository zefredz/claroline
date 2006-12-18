<?php // $Id$
/**
 * CLAROLINE
 * This tool run some check to detect abnormal situation
 *
 * @version 1.8 $Revision$
 *
 * @copyright 2001-2006 Universite catholique de Louvain (UCL)
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
if ( ! claro_is_user_authenticated() ) claro_disp_auth_form();
if ( ! claro_is_platform_admin() ) claro_die(get_lang('Not allowed'));

$interbredcrump[]= array ('url' => 'index.php', 'name' => get_lang('Administration'));

$nameTools = get_lang('Platform statistics');

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

$tbl_document        = $tbl_cdb_names['document'];

$toolNameList = claro_get_tool_name_list();

require_once get_path('incRepositorySys') . '/lib/statsUtils.lib.inc.php';

// used in strange cases, a course is unused if not used since $limitBeforeUnused
// INTERVAL SQL expr. see http://www.mysql.com/doc/en/Date_and_time_functions.html
$limitBeforeUnused = "INTERVAL 6 MONTH";

$is_allowedToTrack     = claro_is_platform_admin();

include get_path('incRepositorySys') . '/claro_init_header.inc.php';
echo claro_html_tool_title(
    array(
    'mainTitle'=>$nameTools,
    )
    );

if( $is_allowedToTrack && get_conf('is_trackingEnabled'))
{
    // in $view, a 1 in X posof the $view string means that the 'category' number X
    // will be show, 0 means don't show
    echo "\n".'<small>'
            .'[<a href="'.$_SERVER['PHP_SELF'].'?view=1111111">'.get_lang('Show all').'</a>]'
            .'&nbsp;[<a href="'.$_SERVER['PHP_SELF'].'?view=0000000">'.get_lang('Show none').'</a>]'
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
        echo '-&nbsp;&nbsp;<b>'.get_lang('Platform statistics').'</b>&nbsp;&nbsp;&nbsp;<small>[<a href="'.$_SERVER['PHP_SELF'].'?view='.$tempView.'">'.get_lang('Close').'</a>]</small><br />'."\n";
        //---- COURSES
        echo "\n".'<br />&nbsp;&nbsp;&nbsp;<b>'.get_lang('Courses').'</b><br />'."\n";
        //--  number of courses
        $sql = "SELECT count(*)
                    FROM `".$tbl_course."`";
        $count = claro_sql_query_get_single_value($sql);
        echo '&nbsp;&nbsp;&nbsp;'.get_lang('Number of courses').' : '.$count.'<br />'."\n";

        //--  number of courses by faculte
        $sql = "SELECT `faculte`, count( * ) AS `nbr`
                    FROM `".$tbl_course."`
                    WHERE `faculte` IS NOT NULL
                    GROUP BY `faculte`";
        $results = claro_sql_query_fetch_all($sql);
        echo '&nbsp;&nbsp;&nbsp;'.get_lang('Number of courses by faculty').' : <br />'."\n";
        buildTab2Col($results);
        echo '<br />'."\n";

        //--  number of courses by language
        $sql = "SELECT `languageCourse`, count( * ) AS `nbr`
                    FROM `".$tbl_course."`
                    WHERE `languageCourse` IS NOT NULL
                    GROUP BY `languageCourse`";
        $results = claro_sql_query_fetch_all($sql);
        echo '&nbsp;&nbsp;&nbsp;'.get_lang('Number of courses by language').' : <br />'."\n";
        buildTab2Col($results);
        echo '<br />'."\n";
        //--  number of courses by visibility
        $sql = "SELECT `visible`, count( * ) AS `nbr`
                    FROM `".$tbl_course."`
                    WHERE `visible` IS NOT NULL
                    GROUP BY `visible`";

        $results = claro_sql_query_fetch_all($sql);
        $results = changeResultOfVisibility($results);
        echo '&nbsp;&nbsp;&nbsp;'.get_lang('Number of courses by visibility').' : <br />'."\n";
        buildTab2Col($results);
        echo '<br />'."\n";

        //-- USERS
        echo "\n".'<br />&nbsp;&nbsp;&nbsp;<b>'.get_lang('Users').'</b><br />'."\n";

        //--  total number of users
        $sql = "SELECT count(*)
                    FROM `".$tbl_user."`";
        $count = claro_sql_query_get_single_value($sql);
        echo '&nbsp;&nbsp;&nbsp;'.get_lang('Number of users').' : '.$count.'<br />'."\n";

        //--  number of users by course
        $sql = "SELECT C.`code`, count( CU.user_id ) as `nb`
                    FROM `".$tbl_course."` C, `".$tbl_rel_course_user."` CU
                    WHERE CU.`code_cours` = C.`code`
                        AND `code` IS NOT NULL
                    GROUP BY C.`code`
                    ORDER BY nb DESC";
        $results = claro_sql_query_fetch_all($sql);
        echo '&nbsp;&nbsp;&nbsp;'.get_lang('Number of users by course').' : <br />'."\n";
        buildTab2Col($results);
        echo '<br />'."\n";

        //--  number of users by faculte
        $sql = "SELECT C.`faculte`, count( CU.`user_id` ) AS `nbr`
                    FROM `".$tbl_course."` C, `".$tbl_rel_course_user."` CU
                    WHERE CU.`code_cours` = C.`code`
                        AND C.`faculte` IS NOT NULL
                    GROUP BY C.`faculte`";
        $results = claro_sql_query_fetch_all($sql);
        echo '&nbsp;&nbsp;&nbsp;'.get_lang('Number of users by faculty').' : <br />'."\n";
        buildTab2Col($results);
        echo '<br />'."\n";

        //--  number of users by status
        $sql = "SELECT `isCourseCreator`, count( `user_id` ) AS `nbr`
                    FROM `".$tbl_user."`
                    WHERE `isCourseCreator` IS NOT NULL
                    GROUP BY `isCourseCreator`";
        $results = claro_sql_query_fetch_all($sql);
        echo '&nbsp;&nbsp;&nbsp;'.get_lang('Number of users by status').' : <br />'."\n";
        buildTab2Col($results);
        echo '<br />'."\n";
    }
    else
    {
        $tempView[0] = '1';
        echo '+&nbsp;&nbsp;&nbsp;<a href="'.$_SERVER['PHP_SELF'].'?view='.$tempView.'">'.get_lang('Platform statistics').'</a>'."\n";
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
        echo '-&nbsp;&nbsp;<b>'.get_lang('Access to campus').'</b>&nbsp;&nbsp;&nbsp;<small>[<a href="'.$_SERVER['PHP_SELF'].'?view='.$tempView.'">'.get_lang('Close').'</a>]</small><br />'."\n";

        //----------------------------  access
        echo "\n".'<br />&nbsp;&nbsp;&nbsp;<b>'.get_lang('Access').'</b> '.get_lang('(When an user open the index of the campus)').'<br />'."\n";

        //--  all
        $sql = "SELECT count(*)
                    FROM `".$tbl_track_e_open."`";
        $count = claro_sql_query_get_single_value($sql);
        echo '&nbsp;&nbsp;&nbsp;'.get_lang('Total').' : '.$count.'<br />'."\n";

        //--  last 31 days
        $sql = "SELECT count(*)
                    FROM `".$tbl_track_e_open."`
                    WHERE (`open_date` > DATE_ADD(CURDATE(), INTERVAL -31 DAY))";
        $count = claro_sql_query_get_single_value($sql);
        echo '&nbsp;&nbsp;&nbsp;'.get_lang('Last 31 days').' : '.$count.'<br />'."\n";

        //--  last 7 days
        $sql = "SELECT count(*)
                    FROM `".$tbl_track_e_open."`
                    WHERE (`open_date` > DATE_ADD(CURDATE(), INTERVAL -7 DAY))";
        $count = claro_sql_query_get_single_value($sql);
        echo '&nbsp;&nbsp;&nbsp;'.get_lang('Last 7 days').' : '.$count.'<br />'."\n";

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
        echo '&nbsp;&nbsp;&nbsp;'.get_lang('This day').' : '.$count.'<br />'."\n";

        //---------------------------- view details of traffic
        echo "\n".'<br />&nbsp;&nbsp;&nbsp;<b>'.get_lang('Traffic Details').'</b><br />'."\n"
                .'&nbsp;&nbsp;&nbsp;<a href="traffic_details.php">'.get_lang('click here').'</a><br />'."\n";


        //----------------------------  logins
        echo "\n".'<br />&nbsp;&nbsp;&nbsp;<b>'.get_lang('Logins').'</b><br />'."\n";

        //--  all
        $sql = "SELECT count(*)
                    FROM `".$tbl_track_e_login."`";
        $count = claro_sql_query_get_single_value($sql);
        echo '&nbsp;&nbsp;&nbsp;'.get_lang('Total').' : '.$count.'<br />'."\n";

        //--  last 31 days
        $sql = "SELECT count(*)
                    FROM `".$tbl_track_e_login."`
                    WHERE (`login_date` > DATE_ADD(CURDATE(), INTERVAL -31 DAY))";
        $count = claro_sql_query_get_single_value($sql);
        echo '&nbsp;&nbsp;&nbsp;'.get_lang('Last 31 days').' : '.$count.'<br />'."\n";

        //--  last 7 days
        $sql = "SELECT count(*)
                    FROM `".$tbl_track_e_login."`
                    WHERE (`login_date` > DATE_ADD(CURDATE(), INTERVAL -7 DAY))";
        $count = claro_sql_query_get_single_value($sql);
        echo '&nbsp;&nbsp;&nbsp;'.get_lang('Last 7 days').' : '.$count.'<br />'."\n";

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
        echo '&nbsp;&nbsp;&nbsp;'.get_lang('This day').' : '.$count.'<br />'."\n";

    }
    else
    {
        $tempView[1] = '1';
        echo '+&nbsp;&nbsp;&nbsp;<a href="'.$_SERVER['PHP_SELF'].'?view='.$tempView.'">'.get_lang('Access to campus').'</a>'."\n";
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
        echo '-&nbsp;&nbsp;<b>'.get_lang('Access to courses').'</b>&nbsp;&nbsp;&nbsp;<small>[<a href="'.$_SERVER['PHP_SELF'].'?view='.$tempView.'">'.get_lang('Close').'</a>]</small><br />'."\n";
        // display list of course of the student with links to the corresponding userLog
        $sql = "SELECT `fake_code`, `dbName`
                FROM    `".$tbl_course."`
                ORDER BY code ASC";
        $resCourseList = claro_sql_query_fetch_all($sql);
        $i=0;
        foreach( $resCourseList as $course )
        {
// TODO : use claro_sql_get_course_tbl_name
            $TABLEACCESSCOURSE = get_conf('courseTablePrefix') . $course['dbName'] . get_conf('dbGlu') . "track_e_access";
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
        echo '+&nbsp;&nbsp;&nbsp;<a href="'.$_SERVER['PHP_SELF'].'?view='.$tempView.'">'.get_lang('Access to courses').'</a>'."\n";
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
         echo '-&nbsp;&nbsp;<b>'.get_lang('Access to tools').'</b>&nbsp;&nbsp;&nbsp;<small>[<a href="'.$_SERVER['PHP_SELF'].'?view='.$tempView.'">'.get_lang('Close').'</a>]</small><br />'."\n";
        // display list of course of the student with links to the corresponding userLog
        $sql = "SELECT code, dbName
              FROM    `".$tbl_course."`
              ORDER BY code ASC";

        $resCourseList = claro_sql_query_fetch_all($sql);
        $resultsTools=array();
        foreach ( $resCourseList as $course )
        {
            // TODO : use claro_sql_get_course_tbl_name
            $TABLEACCESSCOURSE = get_conf('courseTablePrefix') . $course['dbName'] . get_conf('dbGlu') . "track_e_access";
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
         . '<th>&nbsp;'.get_lang('Name of the tool').'</th>'."\n"
         . '<th>&nbsp;'.get_lang('Total Clicks').'</th>'."\n"
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
             . '<td colspan="2"><center>'.get_lang('No result').'</center></td>'."\n"
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
        echo '+&nbsp;&nbsp;&nbsp;<a href="'.$_SERVER['PHP_SELF'].'?view='.$tempView.'">'.get_lang('Access to tools').'</a>';
    }
    echo '</p>'."\n\n";
}
else // not allowed to track
{
    if(!get_conf('is_trackingEnabled')) echo get_lang('Tracking has been disabled by system administrator.');
    else                     echo get_lang('Not allowed');
}

include get_path('incRepositorySys') . '/claro_init_footer.inc.php';
?>