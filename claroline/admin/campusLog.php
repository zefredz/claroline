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

$htmlHeadXtra[] = "<style type='text/css'>
<!--
.secLine {color : #000000;background-color : $colorMedium;padding-left : 15px;padding-right : 15px;}
.content {padding-left : 25px; }
.specialLink{color : #0000FF; font-size : 15px;}
-->
</style>
<STYLE media='print' type='text/css'>
<!--
TD {border-bottom: thin dashed Gray;}
-->
</STYLE>";

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
?>
<table width="100%" cellpadding="2" cellspacing="0" border="0">
<?php
if( $is_allowedToTrack && $is_trackingEnabled)
{
        // show all : number of 1 is equal to or bigger than number of categories
        // show none : number of 0 is equal to or bigger than number of categories
        echo "
	<tr>
		<td class='minilink'>
			<small>
				[<a href='$PHP_SELF?view=1111111'>$langShowAll</a>]
				[<a href='$PHP_SELF?view=0000000'>$langShowNone</a>]
			</small>
		</td>
	</tr>";

    if(!isset($view)) $view ="0000000";

    /***************************************************************************
     *
     *		Main
     *
     ***************************************************************************/
    $tempView = $view;
    if($view[0] == '1')
    {
        $tempView[0] = '0';
        echo "
	<tr>
		<td valign='top'>
			-&nbsp;&nbsp;&nbsp;<b>".$langPlatformStats."</b>&nbsp;&nbsp;&nbsp;<small>[<a href='$PHP_SELF?view=".$tempView."'>".$langClose."</a>]</small>
		</td>
	</tr>";
        echo "
	<tr>
		<td style='padding-left : 40px;' valign='top'>
			<b>".$langCourses."</b>
		</td>
	</tr>
        ";
        //--  number of courses
        $sql = "SELECT count(*)
                    FROM `$TABLECOURS`";
        $count = getOneResult($sql);
        echo "
	<tr>
		<td style='padding-left : 40px;' valign='top'>
			".$langCountCours." : ".$count."
		</td>
	</tr>
        ";
        //--  number of courses by faculte
        $sql = "SELECT `faculte`, count( * )
                    FROM `$TABLECOURS`
                    WHERE `faculte` IS NOT NULL
                    GROUP BY `faculte`";
        echo "
	<tr>
		<td style='padding-left : 40px;' valign='top'>
			".$langCountCourseByFaculte." : ";
        $results = getManyResults2Col($sql);
        buildTab2ColNoTitle($results);
        echo "
		</td>
	</tr>";

        //--  number of courses by language
        $sql = "SELECT `languageCourse`, count( * )
                    FROM `".$TABLECOURS."`
                    WHERE `languageCourse` IS NOT NULL
                    GROUP BY `languageCourse`";
        echo "
	<tr>
		<td style='padding-left : 40px;' valign='top'>
			".$langCountCourseByLanguage." : ";
        $results = getManyResults2Col($sql);
        buildTab2ColNoTitle($results);
        echo "
		</td>
	</tr>";

        //--  number of courses by visibility
        $sql = "SELECT `visible`, count( * )
                    FROM `".$TABLECOURS."`
                    WHERE `visible` IS NOT NULL
                    GROUP BY `visible`";
        echo "
		<tr>
			<td style='padding-left : 40px;' valign='top'>
				".$langCountCourseByVisibility." : ";
        $results = getManyResults2Col($sql);
		$results = changeResultOfVisibility($results);
		buildTab2ColNoTitle($results);
        echo "
			</td>
		</tr>";
        echo "
	<tr>
		<td style='padding-left : 40px;' valign='top'>
			<b>".$langUsers."</b>
		</td>
	</tr>";
        //--  total number of users
        $sql = "SELECT count(*)
                    FROM `".$TABLEUSER."`";
        $count = getOneResult($sql);
        echo "
	<tr>
		<td style='padding-left : 40px;' valign='top'>
			".$langCountUsers." : ".$count."
		</td>
	</tr>
        ";

        //--  number of users by course
        $sql = "SELECT C.`code`, count( CU.user_id ) as nb
                    FROM `".$TABLECOURS."` C, `".$TABLECOURSUSER."` CU
                    WHERE CU.`code_cours` = C.`code`
                        AND `code` IS NOT NULL
                    GROUP BY C.`code`
                    ORDER BY nb DESC";
        echo "
	<tr>
		<td style='padding-left : 40px;' valign='top'>
			".$langCountUsersByCourse." : ";
        $results = getManyResults2Col($sql);
        buildTab2ColNoTitle($results);
        echo "
		</td>
	</tr>";

        //--  number of users by faculte
        $sql = "SELECT C.`faculte`, count( CU.user_id )
                    FROM `".$TABLECOURS."` C, `".$TABLECOURSUSER."` CU
                    WHERE CU.`code_cours` = C.`code`
                        AND C.`faculte` IS NOT NULL
                    GROUP BY C.`faculte`";
        echo "
	<tr>
		<td style='padding-left : 40px;' valign='top'>
			".$langCountUsersByFaculte." : ";
        $results = getManyResults2Col($sql);
        buildTab2ColNoTitle($results);
        echo "
		</td>
	</tr>";

        //--  number of users by status
        $sql = "SELECT `statut`, count( `user_id` )
                    FROM `$TABLEUSER`
                    WHERE `statut` IS NOT NULL
                    GROUP BY `statut`";
        echo "
	<tr>
		<td style='padding-left : 40px;' valign='top'>
			".$langCountUsersByStatus." : ";
        $results = getManyResults2Col($sql);
        buildTab2ColNoTitle($results);
        echo "
		</td>
	</tr>";

    }
    else
    {
        $tempView[0] = '1';
        echo "
	<tr>
		<td valign='top'>
			+&nbsp;&nbsp;<a href='$PHP_SELF?view=".$tempView."' class='specialLink'>$langPlatformStats</a>
		</td>
	</tr>
        ";
    }

    /***************************************************************************
     *
     *		Platform access and logins
     *
     ***************************************************************************/
    $tempView = $view;
    if($view[1] == '1')
    {
        $tempView[1] = '0';
        echo "
	<tr>
		<td valign='top'>
			-&nbsp;&nbsp;&nbsp;<b>".$langPlatformAccess."</b>&nbsp;&nbsp;&nbsp;<small>[<a href='$PHP_SELF?view=".$tempView."'>".$langClose."</a>]</small>
		</td>
	</tr>
            ";
        // ** access
        echo "
	<tr>
		<td style='padding-left : 40px;' valign='top'>
			<b>".$langAccess."</b> ".$langAccessExplain."
		</td>
	</tr>
        ";
        //--  all
        $sql = "SELECT count(*)
                    FROM `$TABLETRACK_OPEN`";
        $count = getOneResult($sql);
        echo "
	<tr>
		<td style='padding-left : 40px;' valign='top'>"
			.$langTotalPlatformAccess." : ".$count."
		</td>
	</tr>
        ";
        //--  last 31 days
        $sql = "SELECT count(*)
                    FROM `$TABLETRACK_OPEN`
                    WHERE (open_date > DATE_ADD(CURDATE(), INTERVAL -31 DAY))";
        $count = getOneResult($sql);
        echo "
	<tr>
		<td style='padding-left : 40px;' valign='top'>
			".$langLast31days." : ".$count."
		</td>
	</tr>
        ";
        //--  last 7 days
        $sql = "SELECT count(*)
                    FROM `$TABLETRACK_OPEN`
                    WHERE (open_date > DATE_ADD(CURDATE(), INTERVAL -7 DAY))";
        $count = getOneResult($sql);
        echo "
	<tr>
		<td style='padding-left : 40px;' valign='top'>
			".$langLast7days." : ".$count."
		</td>
	</tr>
        ";
        //--  today
        $sql = "SELECT count(*)
                    FROM `$TABLETRACK_OPEN` 
                    WHERE (open_date > CURDATE() )"; 
        $count = getOneResult($sql);
        echo "
	<tr>
		<td style='padding-left : 40px;' valign='top'>
			".$langThisday." : ".$count."
		</td>
	</tr>
        ";
        //-- view details of traffic
        echo "
	<tr>
		<td style='padding-left : 40px;' valign='top'>
			<b>".$langTrafficDetails."</b>
		</td>
	</tr>
        ";
        echo "
	<tr>
		<td style='padding-left : 40px;' valign='top'>
			<a href='traffic_details.php'>".$langStatsDatabaseLink."</a>
		</td>
	</tr>
        ";
        // **  logins
        echo "
	<tr>
		<td style='padding-left : 40px;' valign='top'>
			<b>".$langLogins."</b>
		</td>
	</tr>
        ";
        //--  all
        $sql = "SELECT count(*)
                    FROM `$TABLETRACK_LOGIN`";
        $count = getOneResult($sql);
        echo "
	<tr>
		<td style='padding-left : 40px;' valign='top'>"
			.$langTotalPlatformLogin." : ".$count."
		</td>
	</tr>
        ";
        //--  last 31 days
        $sql = "SELECT count(*)
                    FROM `$TABLETRACK_LOGIN`
                    WHERE (login_date > DATE_ADD(CURDATE(), INTERVAL -31 DAY))";
        $count = getOneResult($sql);
        echo "
	<tr>
		<td style='padding-left : 40px;' valign='top'>
			".$langLast31days." : ".$count."
		</td>
	</tr>
        ";
        //--  last 7 days
        $sql = "SELECT count(*)
                    FROM `$TABLETRACK_LOGIN`
                    WHERE (login_date > DATE_ADD(CURDATE(), INTERVAL -7 DAY))";
        $count = getOneResult($sql);
        echo "
	<tr>
		<td style='padding-left : 40px;' valign='top'>
			".$langLast7days." : ".$count."
		</td>
	</tr>
        ";
        //--  today
        $sql = "SELECT count(*)
                    FROM `$TABLETRACK_LOGIN`
                    WHERE (login_date > CURDATE() )";
        $count = getOneResult($sql);
        echo "
	<tr>
		<td style='padding-left : 40px;' valign='top'>
			".$langThisday." : ".$count."
		</td>
	</tr>
        ";

    }
    else
    {
        $tempView[1] = '1';
        echo "
	<tr>
		<td valign='top'>
			+&nbsp;&nbsp;<a href='$PHP_SELF?view=".$tempView."' class='specialLink'>$langPlatformAccess</a>
		</td>
	</tr>
        ";
    }

    /***************************************************************************
     *
     *		Access to courses
     *     // due to the moving of access tables in course DB this part of the code exec (nbCourser+1) queries 
     *     // this can create heavy overload on servers ... should be reconsidered     
     *
     ***************************************************************************/
    $tempView = $view;
    if($view[2] == '1')
    {
        $tempView[2] = '0';
        echo "
	<tr>
		<td valign='top'>
			-&nbsp;&nbsp;&nbsp;<b>".$langPlatformCoursesAccess."</b>&nbsp;&nbsp;&nbsp;<small>[<a href='$PHP_SELF?view=".$tempView."'>".$langClose."</a>]</small>
		</td>
	</tr>
            ";
            
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

        echo "
            <tr>
                <td style='padding-left : 40px;' valign='top'>
                <b>".$langAccess."</b>
                </td>
            </tr>
        ";
        echo "<tr><td style='padding-left : 40px;' valign='top'>";  
        //$results = getManyResults2Col($sql);
        buildTab2ColNoTitle($resultsArray);
        echo "</td></tr>";
    }
    else
    {
        $tempView[2] = '1';
        echo "
	<tr>
		<td valign='top'>
			+&nbsp;&nbsp;<a href='$PHP_SELF?view=".$tempView."' class='specialLink'>$langPlatformCoursesAccess</a>
		</td>
	</tr>
        ";
    }


    /***************************************************************************
     *
     *		Access to tools 
     *     // due to the moving of access tables in course DB this part of the code exec (nbCourser+1) queries 
     *     // this can create heavy overload on servers ... should be reconsidered
     *
     ***************************************************************************/
    $tempView = $view;
    if($view[3] == '1')
    {
        $tempView[3] = '0';
        echo "
	<tr>
		<td valign='top'>
			-&nbsp;&nbsp;&nbsp;<b>".$langPlatformToolAccess."</b>&nbsp;&nbsp;&nbsp;<small>[<a href='$PHP_SELF?view=".$tempView."'>".$langClose."</a>]</small>
		</td>
	</tr>
            ";
            
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
        echo "<tr><td style='padding-left : 40px;' valign='top'>";
        
        echo "<table cellpadding='2' cellspacing='1' border='0' align=center>";
        if (is_array($resultsTools))
        {
            arsort($resultsTools); // 
            foreach( $resultsTools as $tool => $nbr)
            {
                echo "
            <tr>
              <td bgcolor='#eeeeee'>
                ".$toolNameList[$tool]."
              </td>
              <td align='right'>
                ".$nbr."
              </td>
            </tr>";
            }

        }
        else
        {
            echo "<tr>";
            echo "<td colspan='2'><center>".$langNoResult."</center></td>";
            echo"</tr>";
        }
        echo "</table>";
        echo "</td></tr>";
    }
    else
    {
        $tempView[3] = '1';
        echo "
            <tr>
                    <td valign='top'>
                    +&nbsp;&nbsp;<a href='$PHP_SELF?view=".$tempView."' class='specialLink'>$langPlatformToolAccess</a>
                    </td>
            </tr>
        ";
    }
    /***************************************************************************
     *
     *		Statistics concerning browser, provider, country, OS and referer
     *
     ***************************************************************************/
     /*
    $tempView = $view;
    if($view[4] == '1')
    {
        $tempView[4] = '0';
        echo "
	<tr>
		<td valign='top'>
			-&nbsp;&nbsp;&nbsp;<b>".$langHardAndSoftUsed."</b>&nbsp;&nbsp;&nbsp;<small>[<a href='$PHP_SELF?view=".$tempView."'>".$langClose."</a>]</small>
		</td>
	</tr>
            ";
        //** decoding of all open event not already decoded
        //decodeOpenInfos();
        //--  country
        echo "
	<tr>
		<td style='padding-left : 40px;' valign='top'>
			<b>".$langCountries."</b>
		</td>
	</tr>
        ";
        $sql = "SELECT country, counter
                    FROM `$TABLESTATS_COUNTRIES`
                    WHERE counter > '0'";

        echo "
	<tr>
		<td style='padding-left : 40px;' valign='top'>";
        $results = getManyResults2Col($sql);
        buildTab2ColNoTitle($results);
        echo "
		</td>
	</tr>";

        //--  Providers
        echo "
	<tr>
		<td style='padding-left : 40px;' valign='top'>
			<b>".$langProviders."</b>
		</td>
	</tr>";
        $sql = "SELECT provider, counter
                    FROM `$TABLESTATS_PROVIDERS`
                    WHERE counter > '0'";

        echo "<tr><td style='padding-left : 40px;' valign='top'>";
        $results = getManyResults2Col($sql);
        buildTab2ColNoTitle($results);
        echo "</td></tr>";

        //--  Browsers
        echo "
	<tr>
		<td style='padding-left : 40px;' valign='top'>
			<b>".$langBrowsers."</b>
		</td>
	</tr>
        ";
        $sql = "SELECT browser, counter
                    FROM `$TABLESTATS_BROWSERS`
                    WHERE counter > '0'";

        echo "
	<tr>
		<td style='padding-left : 40px;' valign='top'>";
        $results = getManyResults2Col($sql);
        buildTab2ColNoTitle($results);
        echo "
		</td>
	</tr>";

        //--  OS
        echo "
	<tr>
		<td style='padding-left : 40px;' valign='top'>
			<b>".$langOS."</b>
		</td>
	</tr>";
        $sql = "SELECT os, counter
                    FROM `$TABLESTATS_OS`
                    WHERE counter > '0'";

        echo "
	<tr>
		<td style='padding-left : 40px;' valign='top'>";
        $results = getManyResults2Col($sql);
        buildTab2ColNoTitle($results);
        echo "
		</td>
	</tr>";

        //--  Referers
        echo "
            <tr>
                <td style='padding-left : 40px;' valign='top'>
                <b>".$langReferers."</b>
                </td>
            </tr>
        ";
        $sql = "SELECT referer, counter
                    FROM `$TABLESTATS_REFERERS`
                    WHERE counter > '0'";
    
        echo "<tr><td style='padding-left : 40px;' valign='top'>";  
        $results = getManyResults2Col($sql);
        buildTab2ColNoTitle($results);
        echo "</td></tr>";
    }
    else
    {
        $tempView[4] = '1';
        echo "
            <tr>
                    <td valign='top'>
                    +&nbsp;&nbsp;<a href='$PHP_SELF?view=".$tempView."' class='specialLink'>$langHardAndSoftUsed</a>
                    </td>
            </tr>
        ";
    }
    */
    /***************************************************************************
     *              
     *		Strange cases 
     *
     ***************************************************************************/
    $tempView = $view;
    if($view[5] == '1')
    {
        $tempView[5] = '0';
        echo "
                <tr>
                        <td valign='top'>
                        -&nbsp;&nbsp;&nbsp;<b>".$langStrangeCases."</b>&nbsp;&nbsp;&nbsp;<small>[<a href='$PHP_SELF?view=".$tempView."'>".$langClose."</a>]</small>
                        </td>
                </tr>
            ";
        //--  multiple logins | 
        //--     multiple logins are not possible in the new version but this page can be used with previous versions
        echo "
            <tr>
                <td style='padding-left : 40px;' valign='top'>
                <b>".$langMultipleLogins."</b>
                </td>
            </tr>
        ";
        $sql = "SELECT DISTINCT username , count(*) as nb 
                    FROM `$TABLEUSER` 
                    GROUP BY username 
                    HAVING nb > 1
                    ORDER BY nb DESC";
    
        echo "<tr><td style='padding-left : 40px;' valign='top'>";  
        buildTabDefcon(getManyResults2Col($sql));
        echo "</td></tr>";

        //--  multiple account with same email
        echo "
            <tr>
                <td style='padding-left : 40px;' valign='top'>
                <b>".$langMultipleEmails."</b>
                </td>
            </tr>
        ";
        $sql = "SELECT DISTINCT email , count(*) as nb 
                    FROM `$TABLEUSER` 
                    GROUP BY email 
                    HAVING nb > 1  
                    ORDER BY nb DESC";
    
        echo "<tr><td style='padding-left : 40px;' valign='top'>";  
        buildTabDefcon(getManyResults2Col($sql));
        echo "</td></tr>";
        
        //--  multiple account with same username AND same password (for compatibility with previous versions) 
        echo "
      <tr>
        <td style='padding-left : 40px;' valign='top'>
          <b>".$langMultipleUsernameAndPassword."</b>
        </td>
      </tr>
            ";
        $sql = "SELECT DISTINCT CONCAT(username, \" -- \", password) as paire, count(*) as nb
                    FROM `$TABLEUSER`
                    GROUP BY paire
                    HAVING nb > 1
                    ORDER BY nb DESC";

        echo "<tr><td style='padding-left : 40px;' valign='top'>";
        buildTabDefcon(getManyResults2Col($sql));
        echo "</td></tr>";

        //--  courses without professor
        echo "
      <tr>
        <td style='padding-left : 40px;' valign='top'>
          <b>".$langCourseWithoutProf."</b>
        </td>
      </tr>
            ";
        $sql = "SELECT c.code, count( cu.user_id ) nbu
                    FROM `$TABLECOURS` c
                    LEFT JOIN `$TABLECOURSUSER` cu
                        ON c.code = cu.code_cours 
                        AND cu.statut = 1
                    GROUP BY c.code, statut
                    HAVING nbu = 0
                    ORDER BY code_cours";
        echo "<tr><td style='padding-left : 40px;' valign='top'>";  
        buildTabDefcon(getManyResults2Col($sql));
        echo "</td></tr>";
        
        //-- courses without students
        echo "
      <tr>
        <td style='padding-left : 40px;' valign='top'>
          <b>".$langCourseWithoutStudents."</b>
        </td>
      </tr>
        ";
        $sql = "SELECT c.code, count( cu.user_id ) nbu
                    FROM `$TABLECOURS` c
                    LEFT JOIN `$TABLECOURSUSER` cu
                        ON c.code = cu.code_cours 
                        AND cu.statut = 5 
                    GROUP BY c.code, statut
                    HAVING nbu = 0
                    ORDER BY code_cours";
        echo "<tr><td style='padding-left : 40px;' valign='top'>";  
        buildTabDefcon(getManyResults2Col($sql));
        echo "</td></tr>";
        //-- courses without access, not used for $limitBeforeUnused
        echo "
            <tr>
                <td style='padding-left : 40px;' valign='top'>
                <b>".$langCourseWithoutAccess."</b>
                </td>
            </tr>
        ";
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
        echo "<tr><td style='padding-left : 40px;' valign='top'>";
        buildTabDefcon($courseWithoutAccess);
        echo "</td></tr>";
        //-- logins not used for $limitBeforeUnused
        echo "
            <tr>
                <td style='padding-left : 40px;' valign='top'>
                <b>".$langLoginWithoutAccess."</b>
                </td>
            </tr>
        ";
        $sql = "SELECT `us`.`username`, MAX(`lo`.`login_date`)
                    FROM `$TABLEUSER` AS us 
                    LEFT JOIN `$TABLETRACK_LOGIN` AS lo
                    ON`lo`.`login_user_id` = `us`.`user_id`
                    GROUP BY `us`.`username`
                    HAVING ( MAX(`lo`.`login_date`) < (NOW() - ".$limitBeforeUnused." ) ) OR MAX(`lo`.`login_date`) IS NULL";
        
        echo "<tr><td style='padding-left : 40px;' valign='top'>";
        $loginWithoutAccessResults = getManyResults2Col($sql);
        for($i = 0; $i < sizeof($loginWithoutAccessResults); $i++)
        {
            if ( !isset($loginWithoutAccessResults[$i][1]) )
            {            
                $loginWithoutAccessResults[$i][1] = $langNeverUsed;
            }
        }
        buildTabDefcon($loginWithoutAccessResults);
        echo "</td></tr>";

    }
    else
    {
        $tempView[5] = '1';
        echo "
            <tr>
                    <td valign='top'>
                    +&nbsp;&nbsp;<a href='$PHP_SELF?view=".$tempView."' class='specialLink'>$langStrangeCases</a>
                    </td>
            </tr>
        ";
    }
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



?>

</table>

<?php
include($includePath."/claro_init_footer.inc.php");
?>
