<?php // $Id$
/*
      +----------------------------------------------------------------------+
      | Authors: Thomas Depraetere <depraetere@ipm.ucl.ac.be>                |
      |          Hugues Peeters    <peeters@ipm.ucl.ac.be>                   |
      |          Christophe Gesché <gesche@ipm.ucl.ac.be>                    |
      |          Sebastien Piraux  <piraux_seb@hotmail.com>
      +----------------------------------------------------------------------+
 */

require '../inc/claro_init_global.inc.php';
$interbredcrump[]= array ("url"=>"index.php", "name"=> "Admin");

$nameTools = $langStatsOfCampus;

// regroup table names for maintenance purpose
/*
 * DB tables definition
 */

$tbl_mdb_names 			= claro_sql_get_main_tbl();
$tbl_cdb_names 			= claro_sql_get_course_tbl();
$tbl_course 			= $tbl_mdb_names['course'           ];
$tbl_rel_course_user	= $tbl_mdb_names['rel_course_user'  ];
$tbl_user 				= $tbl_mdb_names['user'             ];
$tbl_track_e_default    = $tbl_mdb_names['track_e_default'];
$tbl_track_e_login      = $tbl_mdb_names['track_e_login'];
$tbl_track_e_open       = $tbl_mdb_names['track_e_open'];

$tbl_document 	        = $tbl_cdb_names['document'         ];

$toolNameList = array('CLANN___' => $langAnnouncement,
                      'CLFRM___' => $langForums,
                      'CLCAL___' => $langAgenda,
                      'CLCHT___' => $langChat,
                      'CLDOC___' => $langDocument,
                      'CLDSC___' => $langDescriptionCours,
                      'CLGRP___' => $langGroups,
                      'CLLNP___' => $langLearningPath,
                      'CLQWZ___' => $langExercises,
                      'CLWRK___' => $langWork,
                      'CLUSR___' => $langUsers);

include($includePath."/lib/statsUtils.lib.inc.php");

// used in strange cases, a course is unused if not used since $limitBeforeUnused
// INTERVAL SQL expr. see http://www.mysql.com/doc/en/Date_and_time_functions.html
$limitBeforeUnused = "INTERVAL 6 MONTH";

$is_allowedToTrack 	= $is_platformAdmin;

include($includePath."/claro_init_header.inc.php");
claro_disp_tool_title(
	array(
	'mainTitle'=>$nameTools,
	)
	);

if( $is_allowedToTrack && $is_trackingEnabled)
{
    // in $view, a 1 in X posof the $view string means that the 'category' number X
    // will be show, 0 means don't show
    echo "\n<small>"
            ."[<a href=\"".$_SERVER['PHP_SELF']."?view=1111111\">$langShowAll</a>]"
            ."&nbsp;[<a href=\"".$_SERVER['PHP_SELF']."?view=0000000\">$langShowNone</a>]"
            ."</small>\n\n";

    if(!isset($view)) $view ="0000000";

    /***************************************************************************
     *
     *		Main
     *
     ***************************************************************************/
    $tempView = $view;
    echo "<p>\n";
    if($view[0] == '1')
    {
        $tempView[0] = '0';
        echo "-&nbsp;&nbsp;<b>".$langPlatformStats."</b>&nbsp;&nbsp;&nbsp;<small>[<a href=\"".$_SERVER['PHP_SELF']."?view=".$tempView."\">".$langClose."</a>]</small><br />\n";   
        //---- COURSES
        echo "\n<br />&nbsp;&nbsp;&nbsp;<b>".$langCourses."</b><br />\n";
        //--  number of courses
        $sql = "SELECT count(*)
                    FROM `".$tbl_course."`";
        $count = getOneResult($sql);
        echo "&nbsp;&nbsp;&nbsp;".$langCountCours." : ".$count."<br />\n";
        
        //--  number of courses by faculte
        $sql = "SELECT `faculte`, count( * )
                    FROM `".$tbl_course."`
                    WHERE `faculte` IS NOT NULL
                    GROUP BY `faculte`";
        $results = getManyResults2Col($sql);
        echo "&nbsp;&nbsp;&nbsp;".$langCountCourseByFaculte." : <br />\n";
        buildTab2Col($results);
        echo "<br />\n";

        //--  number of courses by language
        $sql = "SELECT `languageCourse`, count( * )
                    FROM `".$tbl_course."`
                    WHERE `languageCourse` IS NOT NULL
                    GROUP BY `languageCourse`";
        $results = getManyResults2Col($sql);
        echo "&nbsp;&nbsp;&nbsp;".$langCountCourseByLanguage." : <br />\n";
        buildTab2Col($results);
        echo "<br />\n";
        //--  number of courses by visibility
        $sql = "SELECT `visible`, count( * )
                    FROM `".$tbl_course."`
                    WHERE `visible` IS NOT NULL
                    GROUP BY `visible`";
        
        $results = getManyResults2Col($sql);
        $results = changeResultOfVisibility($results);
        echo "&nbsp;&nbsp;&nbsp;".$langCountCourseByVisibility." : <br />\n";
        buildTab2Col($results);
        echo "<br />\n";
        
        //-- USERS
        echo "\n<br />&nbsp;&nbsp;&nbsp;<b>".$langUsers."</b><br />";

        //--  total number of users
        $sql = "SELECT count(*)
                    FROM `".$tbl_user."`";
        $count = getOneResult($sql);
        echo "&nbsp;&nbsp;&nbsp;".$langCountUsers." : ".$count."<br />\n";

        //--  number of users by course
        $sql = "SELECT C.`code`, count( CU.user_id ) as nb
                    FROM `".$tbl_course."` C, `".$tbl_rel_course_user."` CU
                    WHERE CU.`code_cours` = C.`code`
                        AND `code` IS NOT NULL
                    GROUP BY C.`code`
                    ORDER BY nb DESC";
        $results = getManyResults2Col($sql);
        echo "&nbsp;&nbsp;&nbsp;".$langCountUsersByCourse." : <br />\n";
        buildTab2Col($results);
        echo '<br />'."\n";

        //--  number of users by faculte
        $sql = "SELECT C.`faculte`, count( CU.user_id )
                    FROM `".$tbl_course."` C, `".$tbl_rel_course_user."` CU
                    WHERE CU.`code_cours` = C.`code`
                        AND C.`faculte` IS NOT NULL
                    GROUP BY C.`faculte`";
        $results = getManyResults2Col($sql);
        echo "&nbsp;&nbsp;&nbsp;".$langCountUsersByFaculte." : <br />\n";
        buildTab2Col($results);
        echo '<br />'."\n";

        //--  number of users by status
        $sql = "SELECT `statut`, count( `user_id` )
                    FROM `".$tbl_user."`
                    WHERE `statut` IS NOT NULL
                    GROUP BY `statut`";
        $results = getManyResults2Col($sql);
        echo "&nbsp;&nbsp;&nbsp;".$langCountUsersByStatus." : <br />\n";
        buildTab2Col($results);
        echo "<br />\n";
    }
    else
    {
        $tempView[0] = '1';
        echo "+&nbsp;&nbsp;&nbsp;<a href=\"".$_SERVER['PHP_SELF']."?view=".$tempView."\">$langPlatformStats</a>\n";
    }
    echo "</p>\n\n";

    /***************************************************************************
     *
     *		Platform access and logins
     *
     ***************************************************************************/
    $tempView = $view;
    echo "<p>\n";
    if($view[1] == '1')
    {
        $tempView[1] = '0';
        echo "-&nbsp;&nbsp;<b>".$langPlatformAccess."</b>&nbsp;&nbsp;&nbsp;<small>[<a href=\"".$_SERVER['PHP_SELF']."?view=".$tempView."\">".$langClose."</a>]</small><br />\n";
        
        //----------------------------  access
        echo "\n<br />&nbsp;&nbsp;&nbsp;<b>".$langAccess."</b> ".$langAccessExplain."<br />\n";
        
        //--  all
        $sql = "SELECT count(*)
                    FROM `".$tbl_track_e_open."`";
        $count = getOneResult($sql);
        echo "&nbsp;&nbsp;&nbsp;".$langTotalPlatformAccess." : ".$count."<br />\n";
        
        //--  last 31 days
        $sql = "SELECT count(*)
                    FROM `".$tbl_track_e_open."`
                    WHERE (`open_date` > DATE_ADD(CURDATE(), INTERVAL -31 DAY))";
        $count = getOneResult($sql);
        echo "&nbsp;&nbsp;&nbsp;".$langLast31days." : ".$count."<br />\n";
        
        //--  last 7 days
        $sql = "SELECT count(*)
                    FROM `".$tbl_track_e_open."`
                    WHERE (`open_date` > DATE_ADD(CURDATE(), INTERVAL -7 DAY))";
        $count = getOneResult($sql);
        echo "&nbsp;&nbsp;&nbsp;".$langLast7Days." : ".$count."<br />\n";
        
        //--  yesterday
        $sql = "SELECT count(*)
                    FROM `".$tbl_track_e_open."` 
                    WHERE (`open_date` > DATE_ADD(CURDATE(), INTERVAL -1 DAY))
                      AND (`open_date` < CURDATE() )";
        $count = getOneResult($sql);
        echo "&nbsp;&nbsp;&nbsp;".$langYesterday." : ".$count."<br />\n";
        
        //--  today
        $sql = "SELECT count(*)
                    FROM `".$tbl_track_e_open."` 
                    WHERE (`open_date` > CURDATE() )"; 
        $count = getOneResult($sql);
        echo "&nbsp;&nbsp;&nbsp;".$langThisday." : ".$count."<br />\n";
        
        //---------------------------- view details of traffic
        echo "\n<br />&nbsp;&nbsp;&nbsp;<b>".$langTrafficDetails."</b><br />\n"
                ."&nbsp;&nbsp;&nbsp;<a href='traffic_details.php'>".$lang_click_here."</a><br />\n";
                
                
        //----------------------------  logins
        echo "\n<br />&nbsp;&nbsp;&nbsp;<b>".$langLogins."</b><br />\n";
        
        //--  all
        $sql = "SELECT count(*)
                    FROM `".$tbl_track_e_login."`";
        $count = getOneResult($sql);
        echo "&nbsp;&nbsp;&nbsp;".$langTotalPlatformLogin." : ".$count."<br />\n";
        
        //--  last 31 days
        $sql = "SELECT count(*)
                    FROM `".$tbl_track_e_login."`
                    WHERE (`login_date` > DATE_ADD(CURDATE(), INTERVAL -31 DAY))";
        $count = getOneResult($sql);
        echo "&nbsp;&nbsp;&nbsp;".$langLast31days." : ".$count."<br />\n";
        
        //--  last 7 days
        $sql = "SELECT count(*)
                    FROM `".$tbl_track_e_login."`
                    WHERE (`login_date` > DATE_ADD(CURDATE(), INTERVAL -7 DAY))";
        $count = getOneResult($sql);
        echo "&nbsp;&nbsp;&nbsp;".$langLast7Days." : ".$count."<br />\n";
        
        //--  yesterday
        $sql = "SELECT count(*)
                    FROM `".$tbl_track_e_login."` 
                    WHERE (`login_date` > DATE_ADD(CURDATE(), INTERVAL -1 DAY))
                      AND (`login_date` < CURDATE() )";
        $count = getOneResult($sql);
        echo "&nbsp;&nbsp;&nbsp;".$langYesterday." : ".$count."<br />\n";
        
        //--  today
        $sql = "SELECT count(*)
                    FROM `".$tbl_track_e_login."`
                    WHERE (`login_date` > CURDATE() )";
        $count = getOneResult($sql);
        echo "&nbsp;&nbsp;&nbsp;".$langThisday." : ".$count."<br />\n";

    }
    else
    {
        $tempView[1] = '1';
        echo "+&nbsp;&nbsp;&nbsp;<a href=\"".$_SERVER['PHP_SELF']."?view=".$tempView."\">$langPlatformAccess</a>";
    }
    echo "</p>\n\n";

    /***************************************************************************
     *
     *		Access to courses
     *     // due to the moving of access tables in course DB this part of the code exec (nbCourser+1) queries 
     *     // this can create heavy overload on servers ... should be reconsidered     
     *
     ***************************************************************************/
    $tempView = $view;
    echo "<p>\n";
    if($view[2] == '1')
    {
        $tempView[2] = '0';
        echo "-&nbsp;&nbsp;<b>".$langPlatformCoursesAccess."</b>&nbsp;&nbsp;&nbsp;<small>[<a href=\"".$_SERVER['PHP_SELF']."?view=".$tempView."\">".$langClose."</a>]</small><br />\n";  
        // display list of course of the student with links to the corresponding userLog
        $sql = "SELECT `fake_code`, `dbName` FROM    `".$tbl_course."` ORDER BY code ASC";
        $resCourseList = claro_sql_query($sql);
        $i=0;                               
        while ( $course = mysql_fetch_array($resCourseList) )
        {
            $TABLEACCESSCOURSE = $courseTablePrefix . $course['dbName'] . $dbGlu . "track_e_access";
            $sql = "SELECT count( `access_id` ) AS nb
                      FROM `".$TABLEACCESSCOURSE."`
                      WHERE `access_tid` IS NULL
                      ORDER BY nb DESC";
            $result = claro_sql_query($sql);
            $count = mysql_fetch_array($result);

            $resultsArray[$i][0] = $course['fake_code'];
            $resultsArray[$i][1] = $count['nb'];
            $i++;
        }

        echo "\n<br />&nbsp;&nbsp;&nbsp;<b>".$langAccess."</b><br />\n";
        buildTab2Col($resultsArray);
    }
    else
    {
        $tempView[2] = '1';
        echo "+&nbsp;&nbsp;&nbsp;<a href=\"".$_SERVER['PHP_SELF']."?view=".$tempView."\">".$langPlatformCoursesAccess."</a>";
    }
    echo "</p>\n\n";

    /***************************************************************************
     *
     *		Access to tools 
     *     // due to the moving of access tables in course DB this part of the code exec (nbCourser+1) queries 
     *     // this can create heavy overload on servers ... should be reconsidered
     *
     ***************************************************************************/
    $tempView = $view;
    echo "<p>\n";
    if($view[3] == '1')
    {
        $tempView[3] = '0';
         echo "-&nbsp;&nbsp;<b>".$langToolsAccess."</b>&nbsp;&nbsp;&nbsp;<small>[<a href=\"".$_SERVER['PHP_SELF']."?view=".$tempView."\">".$langClose."</a>]</small><br />\n";   
      // display list of course of the student with links to the corresponding userLog
      $resCourseList = claro_sql_query("SELECT code, dbName
	                                   FROM    `".$tbl_course."`
                                     ORDER BY code ASC");
    
      while ( $course = mysql_fetch_array($resCourseList) )
      {
          $TABLEACCESSCOURSE = $courseTablePrefix . $course['dbName'] . $dbGlu . "track_e_access";
          $sql = "SELECT count( `access_id` ) AS nb, `access_tlabel`
                      FROM `".$TABLEACCESSCOURSE."`
                      WHERE `access_tid` IS NOT NULL
                      GROUP BY `access_tid`";

          $result = claro_sql_query($sql);

          // look for each tool of the course in re
          while( $count = mysql_fetch_array($result) )
          {
               if (!$resultsTools[$count['access_tlabel']])
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
         . '<th>&nbsp;'.$langToolTitleToolnameColumn.'</th>'."\n"
         . '<th>&nbsp;'.$langToolTitleCountColumn.'</th>'."\n"
         . '</tr>'
         . '</thead>'."\n"
         . '<tbody>'."\n"
         ;

      if (is_array($resultsTools))
      {
          arsort($resultsTools);
          foreach( $resultsTools as $tool => $nbr)
          {
              echo '<tr>'."\n"
                 . '<td>'.$toolNameList[$tool].'</td>'."\n"
                 . '<td>'.$nbr.'</td>'."\n"
                 . '</tr>'."\n\n"
                 ;
          }
      }
      else
      {
          echo '<tr>'."\n"
             . '<td colspan="2"><center>'.$langNoResult.'</center></td>'."\n"
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
        echo '+&nbsp;&nbsp;&nbsp;<a href="'.$_SERVER['PHP_SELF'].'?view='.$tempView.'">'.$langToolsAccess.'</a>';
    }
    echo '</p>'."\n\n";
}
else // not allowed to track
{
    if(!$is_trackingEnabled)
    {
        echo $langTrackingDisabled;
    }
    else
    {
        echo $langNotAllowed;
    }
}

include($includePath."/claro_init_footer.inc.php");
?>
