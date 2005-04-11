<?php // $Id$
/**
 * @version CLAROLINE 1.6.*
 * ----------------------------------------------------------------------
 * @copyright (c) 2001-2005 Universite catholique de Louvain (UCL)
 * ----------------------------------------------------------------------
 * @license GENERAL PUBLIC LICENSE (GPL)
 * This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
 * as published by the FREE SOFTWARE FOUNDATION. The GPL is available
 * through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
 * ----------------------------------------------------------------------
 * @author Sébastien Piraux <pir@claroline.net>
 * @author Christophe Gesché <moosh@claroline.net>
 * ----------------------------------------------------------------------
 * This tool run some check to detect abnormal situation
 */

require '../inc/claro_init_global.inc.php';
$interbredcrump[]= array ("url"=>"index.php", "name"=> "Admin");

$nameTools = $langViewPlatFormError;

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
	'mainTitle'=>$nameTools	)
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
	$levelView=-1;
	
    /***************************************************************************
     *		Main
     ***************************************************************************/
    $tempView = $view;
	$levelView++;
    echo "<p>\n";
    if($view[$levelView] == '1')
    {
        $tempView[$levelView] = '0';
        echo '- &nbsp;&nbsp;<b>'.$langMultipleLogins.'</b>&nbsp;&nbsp;&nbsp;<small>[<a href="'.$_SERVER['PHP_SELF'].'?view='.$tempView.'">'.$langClose.'</a>]</small><br />'."\n";
        //--  multiple logins | 
        //--     multiple logins are not possible in the new version but this page can be used with previous versions
        $sql = "SELECT DISTINCT username , count(*) as nb 
                    FROM `".$tbl_user."` 
                    GROUP BY username 
                    HAVING nb > 1
                    ORDER BY nb DESC";
    
        buildTabDefcon(getManyResults2Col($sql));
    

        echo '<br />'."\n";
    }
    else
    {
        $tempView[$levelView] = '1';
        echo '+&nbsp;&nbsp;&nbsp;<a href="'.$_SERVER['PHP_SELF'].'?view='.$tempView.'">'.$langMultipleLogins.'</a>'."\n";
    }
    echo '</p>'."\n\n";

    /***************************************************************************
     *
     *		Platform access and logins
     *
     ***************************************************************************/
    $tempView = $view;
	$levelView++;
    echo '<p>'."\n";
    if($view[$levelView] == '1')
    {
        $tempView[$levelView] = '0';
        echo '- &nbsp;&nbsp;<b>'.$langMultipleEmails.'</b>&nbsp;&nbsp;&nbsp;<small>[<a href="'.$_SERVER['PHP_SELF'].'?view='.$tempView.'">'.$langClose.'</a>]</small><br />'."\n";
		//--  multiple account with same email
        
        $sql = "SELECT DISTINCT email , count(*) as nb 
                    FROM `".$tbl_user."` 
                    GROUP BY email 
                    HAVING nb > 1  
                    ORDER BY nb DESC";
    
        buildTabDefcon(getManyResults2Col($sql));
        

    }
    else
    {
        $tempView[$levelView] = '1';
        echo '+&nbsp;&nbsp;&nbsp;<a href="'.$_SERVER['PHP_SELF'].'?view='.$tempView.'">'.$langMultipleEmails.'</a>';
    }
    echo '</p>'."\n";


    $tempView = $view;
	$levelView++;
    echo "<p>\n";
    if($view[$levelView] == '1')
    {
        $tempView[$levelView] = '0';
        //--  courses without professor
        echo '- &nbsp;&nbsp;<b>'.$langCourseWithoutProf.'</b>&nbsp;&nbsp;&nbsp;<small>[<a href="'.$_SERVER['PHP_SELF'].'?view='.$tempView.'">'.$langClose.'</a>]</small><br />'."\n";

        $sql = "SELECT c.code, count( cu.user_id ) nbu
                    FROM `".$tbl_course."` c
                    LEFT JOIN `".$tbl_rel_course_user."` cu
                        ON c.code = cu.code_cours 
                        AND cu.statut = 1
                    GROUP BY c.code, statut
                    HAVING nbu = 0
                    ORDER BY code_cours";

        buildTabDefcon(getManyResults2Col($sql));
        
    }
    else
    {
        $tempView[$levelView] = '1';
        echo '+&nbsp;&nbsp;&nbsp;<a href="'.$_SERVER['PHP_SELF'].'?view='.$tempView.'">'.$langCourseWithoutProf.'</a>';
    }
    echo "</p>\n\n";

    $tempView = $view;
	$levelView++;
    echo "<p>\n";
    if($view[$levelView] == '1')
    {
        $tempView[$levelView] = '0';
        //-- courses without students
        echo '- &nbsp;&nbsp;<b>'.$langCourseWithoutStudents.'</b>&nbsp;&nbsp;&nbsp;<small>[<a href="'.$_SERVER['PHP_SELF'].'?view='.$tempView.'">'.$langClose.'</a>]</small><br />'."\n";

        $sql = "SELECT c.code, count( cu.user_id ) nbu
                    FROM `".$tbl_course."` c
                    LEFT JOIN `".$tbl_rel_course_user."` cu
                        ON c.code = cu.code_cours 
                        AND cu.statut = 5 
                    GROUP BY c.code, statut
                    HAVING nbu = 0
                    ORDER BY code_cours";

        buildTabDefcon(getManyResults2Col($sql));
        
    }
    else
    {
        $tempView[$levelView] = '1';
        echo '+&nbsp;&nbsp;&nbsp;<a href="'.$_SERVER['PHP_SELF'].'?view='.$tempView.'">'.$langCourseWithoutStudents.'</a>';
    }
    echo '</p>'."\n\n";


    $tempView = $view;
	$levelView++;
    echo "<p>\n";
    if($view[$levelView] == '1')
    {
        $tempView[$levelView] = '0';
        //-- logins not used for $limitBeforeUnused
        echo '- &nbsp;&nbsp;<b>'.$langLoginWithoutAccess.'</b>&nbsp;&nbsp;&nbsp;<small>[<a href="'.$_SERVER['PHP_SELF'].'?view='.$tempView.'">'.$langClose.'</a>]</small><br />'."\n";

        $sql = "SELECT `us`.`username`, MAX(`lo`.`login_date`)
                    FROM `".$tbl_user."` AS us 
                    LEFT JOIN `".$tbl_track_e_login."` AS lo
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
        $tempView[$levelView] = '1';
        echo '+&nbsp;&nbsp;&nbsp;<a href="'.$_SERVER['PHP_SELF'].'?view='.$tempView.'">'.$langLoginWithoutAccess.'</a>';
    }
    echo '</p>'."\n\n";


    $tempView = $view;
	$levelView++;
    echo "<p>\n";
    if($view[$levelView] == '1')
    {
        $tempView[$levelView] = '0';
           //--  multiple account with same username AND same password (for compatibility with previous versions) 
        echo '- &nbsp;&nbsp;<b>'.$langMultipleUsernameAndPassword.'</b>&nbsp;&nbsp;&nbsp;<small>[<a href="'.$_SERVER['PHP_SELF'].'?view='.$tempView.'">'.$langClose.'</a>]</small><br />'."\n";

        $sql = "SELECT DISTINCT CONCAT(username, \" -- \", password) as paire, count(*) as nb
                    FROM `".$tbl_user."`
                    GROUP BY paire
                    HAVING nb > 1
                    ORDER BY nb DESC";

        buildTabDefcon(getManyResults2Col($sql));
    }
    else
    {
        $tempView[$levelView] = '1';
        echo '+&nbsp;&nbsp;&nbsp;<a href="'.$_SERVER['PHP_SELF'].'?view='.$tempView.'">'.$langMultipleUsernameAndPassword.'</a>';
    }
    echo '</p>'."\n\n";


    $tempView = $view;
	$levelView++;
    echo "<p>\n";
    if($view[$levelView] == '1')
    {
        $tempView[$levelView] = '0';
        //-- courses without access, not used for $limitBeforeUnused
        echo '- &nbsp;&nbsp;<b>'.$langCourseWithoutAccess.'</b>&nbsp;&nbsp;&nbsp;<small>[<a href="'.$_SERVER['PHP_SELF'].'?view='.$tempView.'">'.$langClose.'</a>]</small><br />'."\n";
        $sql ="SELECT code, dbName
	                                   FROM    `".$tbl_course."`
                                     ORDER BY code ASC";
        $resCourseList = claro_sql_query($sql);
        $i = 0;
        while ( $course = mysql_fetch_array($resCourseList) )
        {
            $TABLEACCESSCOURSE = $courseTablePrefix . $course['dbName'] . $dbGlu . "track_e_access";
            $sql = "SELECT IF( MAX(`access_date`)  < (NOW() - ".$limitBeforeUnused." ), MAX(`access_date`) , 'recentlyUsedOrNull' )  as lastDate, count(`access_date`) as nbrAccess
                        FROM `".$TABLEACCESSCOURSE."`";
            $coursesNotUsedResult = claro_sql_query($sql);
            
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
        
    }
    else
    {
        $tempView[$levelView] = '1';
        echo '+&nbsp;&nbsp;&nbsp;<a href="'.$_SERVER['PHP_SELF'].'?view='.$tempView.'">'.$langCourseWithoutAccess.'</a>';
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