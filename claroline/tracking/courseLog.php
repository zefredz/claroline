<?php // $Id$
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.6
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2004 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
      |  Authors : see CREDITS.txt					     |
      +----------------------------------------------------------------------+
 */
 
$langFile = "tracking";
require '../inc/claro_init_global.inc.php';
include($includePath."/lib/statsUtils.lib.inc.php");


$tbl_mdb_names = claro_sql_get_main_tbl();
$tbl_rel_course_user         = $tbl_mdb_names['rel_course_user'  ];
$tbl_user                    = $tbl_mdb_names['user'             ];
$tbl_track_e_login           = $tbl_mdb_names['track_e_login'];

$tbl_cdb_names = claro_sql_get_course_tbl();
$tbl_group_rel_team_user     = $tbl_cdb_names['group_rel_team_user'    ];
$tbl_track_e_downloads       = $tbl_cdb_names['track_e_downloads'      ];
$tbl_track_e_access          = $tbl_cdb_names['track_e_access'         ];
$tbl_track_e_exercises       = $tbl_cdb_names['track_e_exercices'      ];
$tbl_quiz_test               = $tbl_cdb_names['quiz_test'              ];

// regroup table names for maintenance purpose

$is_allowedToTrack = $is_courseAdmin;

$nameTools = $langStatistics;
include($includePath."/claro_init_header.inc.php");
claro_disp_tool_title(
	array(
		'mainTitle' => $nameTools,
		'subTitle'  => $langStatsOfCourse." : ".$_course['officialCode']
	)
);

// check if uid is prof of this group

if($is_allowedToTrack && $is_trackingEnabled)
{
    // in $view, a 1 in X posof the $view string means that the 'category' number X
    // will be show, 0 means don't show
    echo '<small>'
        .'[<a href="'.$_SERVER['PHP_SELF'].'?view=1111111">'.$langShowAll.'</a>]'
        .'&nbsp;[<a href="'.$_SERVER['PHP_SELF'].'?view=0000000">'.$langShowNone.'</a>]'
        .'</small>'."\n\n"
		;

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
        echo '-&nbsp;&nbsp;<b>'.$langUsers.'</b>&nbsp;&nbsp;&nbsp;<small>[<a href="'.$_SERVER['PHP_SELF'].'?view='.$tempView.'">'.$langClose.'</a>]</small><br />'."\n";   
        
        //-- total number of user in the course
        $sql = "SELECT count(*)
                    FROM `".$tbl_rel_course_user."`
                    WHERE code_cours = '".$_cid."'";
        $count = getOneResult($sql);
        echo "&nbsp;&nbsp;&nbsp;".$langCountUsers." : ".$count."<br />\n";
        
        //--  student never connected
        $sql = "SELECT  U.`user_id`, U.`nom`, U.`prenom`
            FROM `".$tbl_user."` AS U, `".$tbl_rel_course_user."` AS CU
            LEFT JOIN `".$tbl_track_e_access."` AS A
            ON A.`access_user_id` = CU.`user_id`
            WHERE U.`user_id` = CU.`user_id`
            AND CU.`code_cours` = '".$_cid."'
            AND A.`access_user_id` IS NULL
            "; 
        echo "&nbsp;&nbsp;&nbsp;".$langNeverConnectedStudents;
    
        $results = getManyResults3Col($sql);
        if (is_array($results))
        { 
            echo '<ul>'."\n";
            for($j = 0 ; $j < count($results) ; $j++)
            { 
                echo '<li>' 
                    .'<a href="../user/userInfo.php?uInfo='.$results[$j][0].'">'.$results[$j][2].' '.$results[$j][1].'</a>'
                    .'</li>'."\n";
            }
            echo '</ul>'."\n";
        }
        else
        {
            echo '<small>'.$langNoResult.'</small><br />'."\n";
        }
        //-- student not connected for 1 month
        $sql = "SELECT U.`user_id`, U.`nom`, U.`prenom`, MAX(A.`access_date`) as max_access_date
            FROM `".$tbl_user."` AS U, `".$tbl_rel_course_user."` AS CU, `".$tbl_track_e_access."` AS A
            WHERE U.`user_id` = CU.`user_id`
            AND CU.`code_cours` = '".$_cid."'
            AND U.`user_id` = A.`access_user_id`
            GROUP BY A.`access_user_id`
            HAVING `max_access_date` < ( NOW() - INTERVAL 15 DAY ) 
            ORDER BY A.`access_date` ASC
            ";
        echo '&nbsp;&nbsp;&nbsp;'.$langNotRecentlyConnectedStudents;
    
        $results = getManyResultsXCol($sql,4);
        if (is_array($results))
        { 
            echo '<ul>';
            for($j = 0 ; $j < count($results) ; $j++)
            { 
                    echo '<li>' 
                        .'<a href="../user/userInfo.php?uInfo='.$results[$j][0]."\">".$results[$j][2]." ".$results[$j][1]."</a> ( ".$langLastAccess." : ".$results[$j][3]." )";
                    echo"</li>";
            }
            echo '</ul>';
        }
        else
        {
            echo '<small>'.$langNoResult.'</small><br />'."\n";
        }
    }
    else
    {
        $tempView[0] = '1';
        echo '+&nbsp;&nbsp;&nbsp;'
		    .'<a href="'.$_SERVER['PHP_SELF'].'?view='.$tempView.'">'
			.$langUsers
			.'</a>'
			;
    }
    echo '</p>'
	    ."\n\n"
		;
    /***************************************************************************
     *		Access to this course
     ***************************************************************************/
    $tempView = $view;
    echo "<p>\n";
    if($view[1] == '1')
    {
        $tempView[1] = '0';
        echo "-&nbsp;&nbsp;<b>".$langCourseAccess."</b>&nbsp;&nbsp;&nbsp;<small>[<a href='".$_SERVER['PHP_SELF']."?view=".$tempView."'>".$langClose."</a>]</small><br />\n";
        
        $sql = "SELECT count(*)
                    FROM `".$tbl_track_e_access."`
                    WHERE access_tid IS NULL";
        $count = getOneResult($sql);
        echo "&nbsp;&nbsp;&nbsp;".$langCountToolAccess." : ".$count."<br />\n";
        
        // last 31 days
        $sql = "SELECT count(*) 
                    FROM `".$tbl_track_e_access."` 
                    WHERE (access_date > DATE_ADD(CURDATE(), INTERVAL -31 DAY))
                        AND access_tid IS NULL";
        $count = getOneResult($sql);
        echo "&nbsp;&nbsp;&nbsp;".$langLast31days." : ".$count."<br />\n";
        
        // last 7 days
        $sql = "SELECT count(*) 
                    FROM `".$tbl_track_e_access."` 
                    WHERE (access_date > DATE_ADD(CURDATE(), INTERVAL -7 DAY))
                        AND access_tid IS NULL";
        $count = getOneResult($sql);
        echo "&nbsp;&nbsp;&nbsp;".$langLast7Days." : ".$count."<br />\n";
        
        // today
        $sql = "SELECT count(*) 
                    FROM `".$tbl_track_e_access."` 
                    WHERE ( access_date > CURDATE() )
                        AND access_tid IS NULL";
        $count = getOneResult($sql);
        echo '&nbsp;&nbsp;&nbsp;'
		    .$langThisday
			.' : '
			.$count
			.'<br />'."\n";
        
        //-- view details of traffic
        echo '&nbsp;&nbsp;&nbsp;'
		    .'<a href="course_access_details.php">'.$langTrafficDetails.'</a><br />'
			."\n"
			;
    
    }
    else
    {
        $tempView[1] = '1';
        echo '+&nbsp;&nbsp;&nbsp;<a href="'.$_SERVER['PHP_SELF'].'?view='.$tempView.'">'.$langCourseAccess.'</a>';
        
    }
    echo '</p>'."\n\n";
    /***************************************************************************
     *              
     *		Tools
     *
     ***************************************************************************/
    $tempView = $view;
    echo '<p>'."\n";
    if($view[2] == '1')
    {
        $tempView[2] = '0';
        echo '-&nbsp;&nbsp;<b>'.$langToolsAccess.'</b>&nbsp;&nbsp;&nbsp;<small>[<a href="'.$_SERVER['PHP_SELF'].'?view='.$tempView.'">'.$langClose.'</a>]</small><br />'."\n";   
        
        $sql = "SELECT `access_tid`, COUNT(DISTINCT `access_user_id`),count( `access_tid` ), `access_tlabel`
                    FROM `".$tbl_track_e_access."`
                    WHERE `access_tid` IS NOT NULL
                      AND `access_tid` <> ''
                    GROUP BY `access_tid`";
        
        $results = getManyResultsXCol($sql,4);
        echo '<table class="claroTable" cellpadding="2" cellspacing="1" border="0" align="center">'."\n"
            .'<tr class="headerX">'."\n"
            .'<th>&nbsp;'.$langToolTitleToolnameColumn.'&nbsp;</th>'."\n"
            .'<th>&nbsp;'.$langToolTitleUsersColumn.'&nbsp;</th>'."\n"
            .'<th>&nbsp;'.$langToolTitleCountColumn.'&nbsp;</th>'."\n"
            .'</tr>'."\n"
            .'<tbody>'."\n"
            ;
        if (is_array($results))
        { 
            for($j = 0 ; $j < count($results) ; $j++)
            {                 
                echo "<tr>\n"
                    ."<td><a href=\"toolaccess_details.php?tool=".$results[$j][0]."&amp;label=".$results[$j][3]."\">".$toolNameList[$results[$j][3]]."</a></td>\n"
                    ."<td align=\"right\"><a href=\"user_access_details.php?cmd=tool&amp;data=".$results[$j][0]."&amp;label=".$results[$j][3]."\">".$results[$j][1]."</a></td>\n"
                    ."<td align=\"right\">".$results[$j][2]."</td>\n"
                    ."</tr>\n\n";
            }
        
        }
        else
        {
            echo '<tr>'."\n" 
                .'<td colspan="3"><div align="center">'.$langNoResult.'</div></td>'."\n"
                .'</tr>'."\n"
				;
        }
        echo '</tbody>'
		    .'</table>'."\n"
			;
    }
    else
    {
        $tempView[2] = '1';
        echo "+&nbsp;&nbsp;&nbsp;<a href='".$_SERVER['PHP_SELF']."?view=".$tempView."'>$langToolsAccess</a>";
    }
    echo "</p>\n\n";

    /***************************************************************************
     *              
     *		Documents
     *
     ***************************************************************************/
    $tempView = $view;
    echo "<p>\n";
    if($view[4] == '1')
    {
        $tempView[4] = '0';
        echo '-&nbsp;&nbsp;<b>'.$langDocumentsAccess.'</b>&nbsp;&nbsp;&nbsp;<small>[<a href="'.$_SERVER['PHP_SELF'].'?view='.$tempView.'">'.$langClose.'</a>]</small><br />'."\n";   
        
        $sql = "SELECT `down_doc_path`, COUNT(DISTINCT `down_user_id`), COUNT(`down_doc_path`)
                    FROM `".$tbl_track_e_downloads."`
                    GROUP BY `down_doc_path`";
    
        $results = getManyResults3Col($sql);
        echo '<table class="claroTable" cellpadding="2" cellspacing="1" border="0" align="center">'."\n"
            .'<tr class="headerX">'."\n"
            .'<th>&nbsp;'.$langDocumentsTitleDocumentColumn.'&nbsp;</th>'."\n"
            .'<th>&nbsp;'.$langDocumentsTitleUsersColumn.'&nbsp;</th>'."\n"
            .'<th>&nbsp;'.$langDocumentsTitleCountColumn.'&nbsp;</th>'."\n"
            .'</tr>'."\n"
            .'<tbody>'."\n"
            ;
        if (is_array($results))
        { 
            for($j = 0 ; $j < count($results) ; $j++)
            { 
                    echo '<tr>'."\n"
                        .'<td>'.$results[$j][0].'</td>'."\n"
                        .'<td align="right"><a href="user_access_details.php?cmd=doc&amp;data='.urlencode($results[$j][0]).'">'.$results[$j][1].'</a></td>'."\n"
                        .'<td align="right">'.$results[$j][2].'</td>'."\n"
                        .'</tr>'."\n\n"
						;
            }
        
        }
        else
        {
            echo '<tr>'."\n" 
                .'<td colspan="3"><div align="center">'.$langNoResult.'</div></td>'."\n"
                .'</tr>'."\n"
				;
        }
        echo '</tbody>'
		    .'</table>'."\n"
			;
    }
    else
    {
        $tempView[4] = '1';
        echo "+&nbsp;&nbsp;&nbsp;<a href='".$_SERVER['PHP_SELF']."?view=".$tempView."'>$langDocumentsAccess</a>";
    }
    echo "</p>\n\n";
    
    /***************************************************************************
     *		Exercises
     ***************************************************************************/
    $tempView = $view;
    echo "<p>\n";
    if($view[5] == '1')
    {
        $tempView[5] = '0';
        echo '-&nbsp;&nbsp;<b>'.$langExercises.'</b>&nbsp;&nbsp;&nbsp;<small>[<a href="'.$_SERVER['PHP_SELF'].'?view='.$tempView.'" >'.$langClose.'</a>]</small><br />'."\n";   
        
        $sql = "SELECT TEX.`exe_exo_id`, COUNT(DISTINCT TEX.`exe_user_id`), COUNT(TEX.`exe_exo_id`), EX.`titre`
                    FROM `".$tbl_track_e_exercises."` AS TEX, `".$tbl_quiz_test."` AS EX
                    WHERE TEX.`exe_exo_id` = EX.`id`
                    GROUP BY TEX.`exe_exo_id`";
    
        $results = getManyResultsXCol($sql,4);
        echo '<table class="claroTable" cellpadding="2" cellspacing="1" border="0" align="center">'."\n"
            .'<tr class="headerX">'."\n"
            .'<th>&nbsp;'.$langExercisesTitleExerciseColumn.'&nbsp;</th>'."\n"
            .'<th>&nbsp;'.$langExerciseUsersAttempts.'&nbsp;</th>'."\n"
            .'<th>&nbsp;'.$langExerciseTotalAttempts.'&nbsp;</th>'."\n"
            .'</tr>'."\n"
            .'<tbody>'."\n"
			;
                
        if (is_array($results))
        { 
            for($j = 0 ; $j < count($results) ; $j++)
            { 
                    echo '<tr>'."\n"
                        .'<td><a href="exercises_details.php?exo_id='.$results[$j][0].'">'.$results[$j][3].'</a></td>'."\n"
                        .'<td align="right">'.$results[$j][1].'</td>'."\n"
                        .'<td align="right">'.$results[$j][2].'</td>'."\n"
                        .'</tr>'."\n\n"
						;
            }
        }
        else
        {
            echo '<tr>'."\n" 
                .'<td colspan="3"><div align="center">'.$langNoResult.'</div></td>'."\n"
                .'</tr>'."\n"
				;
        }
        echo '</tbody>'."\n"
			.'</table>'."\n"
			;
    }
    else
    {
        $tempView[5] = '1';
        echo '+&nbsp;&nbsp;&nbsp;<a href="'.$_SERVER['PHP_SELF'].'?view='.$tempView.'">'.$langExercises.'</a>';
    }
    echo '</p>'."\n\n";
}
// not allowed
else
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
<hr />
<a class="claroButton" href="delete_course_stats.php">
<img src="<?php echo $clarolineRepositoryWeb ?>img/delete.gif" alt="">
<?php echo $langDelCourseStats; ?>
</a>
<?
include($includePath."/claro_init_footer.inc.php");
?>
