<?php # $Id$
/*
      +----------------------------------------------------------------------+
      | Authors: Thomas Depraetere <depraetere@ipm.ucl.ac.be>                |
      |          Hugues Peeters    <peeters@ipm.ucl.ac.be>                   |
      |          Christophe Gesché <gesche@ipm.ucl.ac.be>                    |
      |          Sebastien Piraux  <piraux_seb@hotmail.com>
      +----------------------------------------------------------------------+
 */

$langFile = "tracking";
require '../inc/claro_init_global.inc.php';
//include($includePath.'/lib/stats.lib.inc.php');
$interbredcrump[]= array ("url"=>"index.php", "name"=> "Admin");

$nameTools = $langStatsOfCampus;

$htmlHeadXtra[] = "
<style media='print' type='text/css'>
<!--
TD {border-bottom: thin dashed Gray;}
-->
</style>";

// regroup table names for maintenance purpose
/*
 * DB tables definition
 */

$tbl_mdb_names 			= claro_sql_get_main_tbl();
$tbl_cdb_names 			= claro_sql_get_course_tbl();
$TABLECOURS 			= $tbl_mdb_names['course'           ];
$TABLECOURSUSER	 		= $tbl_mdb_names['rel_course_user'  ];
$TABLEUSER 				= $tbl_mdb_names['user'             ];
$TABLECOURSE_DOCUMENTS 	= $tbl_cdb_names['document'         ];
$TABLECOURSE_LINKS 		= $tbl_cdb_names['link'             ];

$TABLETRACK_ACCESS = $statsDbName."`.`track_e_access";
$TABLETRACK_LOGIN = $statsDbName."`.`track_e_login";
$TABLETRACK_OPEN = $statsDbName."`.`track_e_open";
$TABLETRACK_LINKS = $statsDbName."`.`track_e_links";
$TABLETRACK_DOWNLOADS = $statsDbName."`.`track_e_downloads";


$toolNameList = array('CLANN___' => $langAnnouncement,
                      'CLFRM___' => $langForum,
                      'CLCAL___' => $langAgenda,
                      'CLCHT___' => $langChat,
                      'CLDOC___' => $langDocument,
                      'CLDSC___' => $langDescriptionCours,
                      'CLGRP___' => $langGroups,
                      'CLLNP___' => $langLearnPath,
                      'CLQWZ___' => $langExercise,
                      'CLWRK___' => $langWork,
                      'CLUSR___' => $langUser);

include($includePath."/lib/statsUtils.lib.inc.php");

// used in strange cases, a course is unused if not used since $limitBeforeUnused
// INTERVAL SQL expr. see http://www.mysql.com/doc/en/Date_and_time_functions.html
$limitBeforeUnused = "INTERVAL 6 MONTH";

$is_allowedToTrack 	= $is_platformAdmin || $PHP_AUTH_USER;

include($includePath."/claro_init_header.inc.php");
claro_disp_tool_title(
	array(
	'mainTitle'=>$nameTools,
	'subTitle'=>$PHP_AUTH_USER." - ".$siteName." - ".$clarolineVersion
	)
	);

if( $is_allowedToTrack && $is_trackingEnabled)
{
    // in $view, a 1 in X posof the $view string means that the 'category' number X
    // will be show, 0 means don't show
    echo "\n<small>"
            ."[<a href=\"$PHP_SELF?view=1111111\">$langShowAll</a>]"
            ."&nbsp;[<a href=\"$PHP_SELF?view=0000000\">$langShowNone</a>]"
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
        echo "-&nbsp;&nbsp;<b>".$langPlatformStats."</b>&nbsp;&nbsp;&nbsp;<small>[<a href=\"$PHP_SELF?view=".$tempView."\">".$langClose."</a>]</small><br />\n";   
        //---- COURSES
        echo "\n<br />&nbsp;&nbsp;&nbsp;<b>".$langCourses."</b><br />\n";
        //--  number of courses
        $sql = "SELECT count(*)
                    FROM `$TABLECOURS`";
        $count = getOneResult($sql);
        echo "&nbsp;&nbsp;&nbsp;".$langCountCours." : ".$count."<br />\n";
        
        //--  number of courses by faculte
        $sql = "SELECT `faculte`, count( * )
                    FROM `$TABLECOURS`
                    WHERE `faculte` IS NOT NULL
                    GROUP BY `faculte`";
        $results = getManyResults2Col($sql);
        echo "&nbsp;&nbsp;&nbsp;".$langCountCourseByFaculte." : <br />\n";
        buildTab2Col($results);
        echo "<br />\n";

        //--  number of courses by language
        $sql = "SELECT `languageCourse`, count( * )
                    FROM `".$TABLECOURS."`
                    WHERE `languageCourse` IS NOT NULL
                    GROUP BY `languageCourse`";
        $results = getManyResults2Col($sql);
        echo "&nbsp;&nbsp;&nbsp;".$langCountCourseByLanguage." : <br />\n";
        buildTab2Col($results);
        echo "<br />\n";
        //--  number of courses by visibility
        $sql = "SELECT `visible`, count( * )
                    FROM `".$TABLECOURS."`
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
                    FROM `".$TABLEUSER."`";
        $count = getOneResult($sql);
        echo "&nbsp;&nbsp;&nbsp;".$langCountUsers." : ".$count."<br />\n";

        //--  number of users by course
        $sql = "SELECT C.`code`, count( CU.user_id ) as nb
                    FROM `".$TABLECOURS."` C, `".$TABLECOURSUSER."` CU
                    WHERE CU.`code_cours` = C.`code`
                        AND `code` IS NOT NULL
                    GROUP BY C.`code`
                    ORDER BY nb DESC";
        $results = getManyResults2Col($sql);
        echo "&nbsp;&nbsp;&nbsp;".$langCountUsersByCourse." : <br />\n";
        buildTab2Col($results);
        echo "<br />\n";

        //--  number of users by faculte
        $sql = "SELECT C.`faculte`, count( CU.user_id )
                    FROM `".$TABLECOURS."` C, `".$TABLECOURSUSER."` CU
                    WHERE CU.`code_cours` = C.`code`
                        AND C.`faculte` IS NOT NULL
                    GROUP BY C.`faculte`";
        $results = getManyResults2Col($sql);
        echo "&nbsp;&nbsp;&nbsp;".$langCountUsersByFaculte." : <br />\n";
        buildTab2Col($results);
        echo "<br />\n";

        //--  number of users by status
        $sql = "SELECT `statut`, count( `user_id` )
                    FROM `$TABLEUSER`
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
        echo "+&nbsp;&nbsp;&nbsp;<a href=\"$PHP_SELF?view=".$tempView."\">$langPlatformStats</a>\n";
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
        echo "-&nbsp;&nbsp;<b>".$langPlatformAccess."</b>&nbsp;&nbsp;&nbsp;<small>[<a href=\"$PHP_SELF?view=".$tempView."\">".$langClose."</a>]</small><br />\n";
        
        //----------------------------  access
        echo "\n<br />&nbsp;&nbsp;&nbsp;<b>".$langAccess."</b> ".$langAccessExplain."<br />\n";
        
        //--  all
        $sql = "SELECT count(*)
                    FROM `$TABLETRACK_OPEN`";
        $count = getOneResult($sql);
        echo "&nbsp;&nbsp;&nbsp;".$langTotalPlatformAccess." : ".$count."<br />\n";
        
        //--  last 31 days
        $sql = "SELECT count(*)
                    FROM `$TABLETRACK_OPEN`
                    WHERE (open_date > DATE_ADD(CURDATE(), INTERVAL -31 DAY))";
        $count = getOneResult($sql);
        echo "&nbsp;&nbsp;&nbsp;".$langLast31days." : ".$count."<br />\n";
        
        //--  last 7 days
        $sql = "SELECT count(*)
                    FROM `$TABLETRACK_OPEN`
                    WHERE (open_date > DATE_ADD(CURDATE(), INTERVAL -7 DAY))";
        $count = getOneResult($sql);
        echo "&nbsp;&nbsp;&nbsp;".$langLast7days." : ".$count."<br />\n";
        
        //--  today
        $sql = "SELECT count(*)
                    FROM `$TABLETRACK_OPEN` 
                    WHERE (open_date > CURDATE() )"; 
        $count = getOneResult($sql);
        echo "&nbsp;&nbsp;&nbsp;".$langThisday." : ".$count."<br />\n";
        
        //---------------------------- view details of traffic
        echo "\n<br />&nbsp;&nbsp;&nbsp;<b>".$langTrafficDetails."</b><br />\n"
                ."&nbsp;&nbsp;&nbsp;<a href='traffic_details.php'>".$langStatsDatabaseLink."</a><br />\n";
                
                
        //----------------------------  logins
        echo "\n<br />&nbsp;&nbsp;&nbsp;<b>".$langLogins."</b><br />\n";
        
        //--  all
        $sql = "SELECT count(*)
                    FROM `$TABLETRACK_LOGIN`";
        $count = getOneResult($sql);
        echo "&nbsp;&nbsp;&nbsp;".$langTotalPlatformLogin." : ".$count."<br />\n";
        
        //--  last 31 days
        $sql = "SELECT count(*)
                    FROM `$TABLETRACK_LOGIN`
                    WHERE (login_date > DATE_ADD(CURDATE(), INTERVAL -31 DAY))";
        $count = getOneResult($sql);
        echo "&nbsp;&nbsp;&nbsp;".$langLast31days." : ".$count."<br />\n";
        
        //--  last 7 days
        $sql = "SELECT count(*)
                    FROM `$TABLETRACK_LOGIN`
                    WHERE (login_date > DATE_ADD(CURDATE(), INTERVAL -7 DAY))";
        $count = getOneResult($sql);
        echo "&nbsp;&nbsp;&nbsp;".$langLast7days." : ".$count."<br />\n";
        
        //--  today
        $sql = "SELECT count(*)
                    FROM `$TABLETRACK_LOGIN`
                    WHERE (login_date > CURDATE() )";
        $count = getOneResult($sql);
        echo "&nbsp;&nbsp;&nbsp;".$langThisday." : ".$count."<br />\n";

    }
    else
    {
        $tempView[1] = '1';
        echo "+&nbsp;&nbsp;&nbsp;<a href=\"$PHP_SELF?view=".$tempView."\">$langPlatformAccess</a>";
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
        echo "-&nbsp;&nbsp;<b>".$langPlatformCoursesAccess."</b>&nbsp;&nbsp;&nbsp;<small>[<a href=\"$PHP_SELF?view=".$tempView."\">".$langClose."</a>]</small><br />\n";  
        // display list of course of the student with links to the corresponding userLog
      $resCourseList = mysql_query("SELECT code, dbName
	                                   FROM    `".$TABLECOURS."`
                                     ORDER BY code ASC");
      $i=0;                               
      while ( $course = mysql_fetch_array($resCourseList) )
      {
          $TABLEACCESSCOURSE = $courseTablePrefix . $course['dbName'] . $dbGlu . "track_e_access";
          $sql = "SELECT count( `access_id` ) AS nb
                      FROM `$TABLEACCESSCOURSE`
                      WHERE `access_tid` IS NULL
                      ORDER BY nb DESC";
          $result = mysql_query($sql);
          $count = mysql_fetch_array($result);
          
          $resultsArray[$i][0] = $course['code'];
          $resultsArray[$i][1] = $count['nb'];
          $i++;
      }

        echo "\n<br />&nbsp;&nbsp;&nbsp;<b>".$langAccess."</b><br />\n";
        buildTab2Col($resultsArray);
    }
    else
    {
        $tempView[2] = '1';
        echo "+&nbsp;&nbsp;&nbsp;<a href=\"$PHP_SELF?view=".$tempView."\">$langPlatformCoursesAccess</a>";
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
         echo "-&nbsp;&nbsp;<b>".$langPlatformToolAccess."</b>&nbsp;&nbsp;&nbsp;<small>[<a href=\"$PHP_SELF?view=".$tempView."\">".$langClose."</a>]</small><br />\n";   
      // display list of course of the student with links to the corresponding userLog
      $resCourseList = mysql_query("SELECT code, dbName
	                                   FROM    `".$TABLECOURS."`
                                     ORDER BY code ASC");
    
      while ( $course = mysql_fetch_array($resCourseList) )
      {
          $TABLEACCESSCOURSE = $courseTablePrefix . $course['dbName'] . $dbGlu . "track_e_access";
          $sql = "SELECT count( `access_id` ) AS nb, `access_tlabel`
                      FROM `$TABLEACCESSCOURSE`
                      WHERE `access_tid` IS NOT NULL
                      GROUP BY `access_tid`";

          $result = mysql_query($sql);

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
      
      echo "<table cellpadding=\"2\" cellspacing=\"1\" class=\"claroTable\" align=\"center\">"
              ."<tr class=\"headerX\">\n"
                ."<th>&nbsp;".$langToolTitleToolnameColumn."</th>\n"
                ."<th>&nbsp;".$langToolTitleCountColumn."</th>\n"
                ."</tr>\n";
                
      if (is_array($resultsTools))
      {
          arsort($resultsTools); // 
          foreach( $resultsTools as $tool => $nbr)
          {
              echo "<tr>\n"
                      ."<td>".$toolNameList[$tool]."</td>\n"
                      ."<td>".$nbr."</td>\n"
                      ."</tr>\n\n";
          }

      }
      else
      {
          echo "<tr>\n"
                  ."<td colspan=\"2\"><center>".$langNoResult."</center></td>\n"
                  ."</tr>\n";
      }
      echo "</table>\n\n";
      
    }
    else
    {
        $tempView[3] = '1';
        echo "+&nbsp;&nbsp;&nbsp;<a href=\"$PHP_SELF?view=".$tempView."\">$langPlatformToolAccess</a>";
    }
    echo "</p>\n\n";
    /***************************************************************************
     *              
     *		Strange cases 
     *
     ***************************************************************************/
    $tempView = $view;
    echo "<p>\n";
    if($view[4] == '1')
    {
        $tempView[4] = '0';
        echo "-&nbsp;&nbsp;<b>".$langStrangeCases."</b>&nbsp;&nbsp;&nbsp;<small>[<a href=\"$PHP_SELF?view=".$tempView."\">".$langClose."</a>]</small><br />\n";
        //--  multiple logins | 
        //--     multiple logins are not possible in the new version but this page can be used with previous versions
        echo "\n<br />&nbsp;&nbsp;&nbsp;<b>".$langMultipleLogins."</b><br />\n";

        $sql = "SELECT DISTINCT username , count(*) as nb 
                    FROM `$TABLEUSER` 
                    GROUP BY username 
                    HAVING nb > 1
                    ORDER BY nb DESC";
    
        buildTabDefcon(getManyResults2Col($sql));
    

        //--  multiple account with same email
        echo "\n<br />&nbsp;&nbsp;&nbsp;<b>".$langMultipleEmails."</b><br />\n";
        
        $sql = "SELECT DISTINCT email , count(*) as nb 
                    FROM `$TABLEUSER` 
                    GROUP BY email 
                    HAVING nb > 1  
                    ORDER BY nb DESC";
    
        buildTabDefcon(getManyResults2Col($sql));
        
        //--  multiple account with same username AND same password (for compatibility with previous versions) 
        echo "\n<br />&nbsp;&nbsp;&nbsp;<b>".$langMultipleUsernameAndPassword."</b><br />\n";

        $sql = "SELECT DISTINCT CONCAT(username, \" -- \", password) as paire, count(*) as nb
                    FROM `$TABLEUSER`
                    GROUP BY paire
                    HAVING nb > 1
                    ORDER BY nb DESC";

        buildTabDefcon(getManyResults2Col($sql));

        //--  courses without professor
        echo "\n<br />&nbsp;&nbsp;&nbsp;<b>".$langCourseWithoutProf."</b><br />\n";

        $sql = "SELECT c.code, count( cu.user_id ) nbu
                    FROM `$TABLECOURS` c
                    LEFT JOIN `$TABLECOURSUSER` cu
                        ON c.code = cu.code_cours 
                        AND cu.statut = 1
                    GROUP BY c.code, statut
                    HAVING nbu = 0
                    ORDER BY code_cours";

        buildTabDefcon(getManyResults2Col($sql));
        
        //-- courses without students
        echo "\n<br />&nbsp;&nbsp;&nbsp;<b>".$langCourseWithoutStudents."</b><br />\n";

        $sql = "SELECT c.code, count( cu.user_id ) nbu
                    FROM `$TABLECOURS` c
                    LEFT JOIN `$TABLECOURSUSER` cu
                        ON c.code = cu.code_cours 
                        AND cu.statut = 5 
                    GROUP BY c.code, statut
                    HAVING nbu = 0
                    ORDER BY code_cours";

        buildTabDefcon(getManyResults2Col($sql));
        
        //-- courses without access, not used for $limitBeforeUnused
        echo "\n<br />&nbsp;&nbsp;&nbsp;<b>".$langCourseWithoutAccess."</b><br />\n";

      $resCourseList = mysql_query("SELECT code, dbName
	                                   FROM    `".$TABLECOURS."`
                                     ORDER BY code ASC");
        $i = 0;
        while ( $course = mysql_fetch_array($resCourseList) )
        {
            $TABLEACCESSCOURSE = $courseTablePrefix . $course['dbName'] . $dbGlu . "track_e_access";
            $sql = "SELECT IF( MAX(`access_date`)  < (NOW() - ".$limitBeforeUnused." ), MAX(`access_date`) , 'recentlyUsedOrNull' )  as lastDate, count(`access_date`) as nbrAccess
                        FROM `$TABLEACCESSCOURSE`";
            $coursesNotUsedResult = mysql_query($sql);
            
            if( $courseAccess = mysql_fetch_array($coursesNotUsedResult) )
            {
                if ( $courseAccess['lastDate'] == 'recentlyUsedOrNull' && $courseAccess['nbrAccess'] != 0 ) continue;
                $courseWithoutAccess[$i][0] = $course['code'];
                if ( $courseAccess['lastDate'] == 'recentlyUsedOrNull') // if no records found ,course was never accessed
                {
                    $courseWithoutAccess[$i][1] = $langNeverUsed;
                }
                else
                {
                    $courseWithoutAccess[$i][1] = $courseAccess['lastDate'];
                }
            }
            
        $i++;
        }

        buildTabDefcon($courseWithoutAccess);
        
        //-- logins not used for $limitBeforeUnused
        echo "\n<br />&nbsp;&nbsp;&nbsp;<b>".$langLoginWithoutAccess."</b><br />\n";

        $sql = "SELECT `us`.`username`, MAX(`lo`.`login_date`)
                    FROM `$TABLEUSER` AS us 
                    LEFT JOIN `$TABLETRACK_LOGIN` AS lo
                    ON`lo`.`login_user_id` = `us`.`user_id`
                    GROUP BY `us`.`username`
                    HAVING ( MAX(`lo`.`login_date`) < (NOW() - ".$limitBeforeUnused." ) ) OR MAX(`lo`.`login_date`) IS NULL";
        

        $loginWithoutAccessResults = getManyResults2Col($sql);
        for($i = 0; $i < sizeof($loginWithoutAccessResults); $i++)
        {
            if ( !isset($loginWithoutAccessResults[$i][1]) )
            {            
                $loginWithoutAccessResults[$i][1] = $langNeverUsed;
            }
        }
        buildTabDefcon($loginWithoutAccessResults);

    }
    else
    {
        $tempView[4] = '1';
        echo "+&nbsp;&nbsp;&nbsp;<a href=\"$PHP_SELF?view=".$tempView."\">$langStrangeCases</a>";
    }
    echo "</p>\n\n";
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
