<?php  // $Id$
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.6
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2004 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
 */
 
require '../inc/claro_init_global.inc.php';

$tbl_mdb_names = claro_sql_get_main_tbl();
$tbl_rel_course_user         = $tbl_mdb_names['rel_course_user'  ];
$tbl_user                    = $tbl_mdb_names['user'             ];
$tbl_track_e_login           = $tbl_mdb_names['track_e_login'];

$tbl_cdb_names = claro_sql_get_course_tbl();
$tbl_group_rel_team_user     = $tbl_cdb_names['group_rel_team_user'    ];
$tbl_track_e_downloads       = $tbl_cdb_names['track_e_downloads'      ];
$tbl_track_e_access          = $tbl_cdb_names['track_e_access'         ];

include($includePath."/lib/statsUtils.lib.inc.php");

$is_allowedToTrack = $is_groupTutor; // allowed to track only user of one group
// following line added by RH to allow a user to see its own course stats
if (isset($uInfo) && isset($_uid)) $is_allowedToTrack = $is_allowedToTrack || ($uInfo == $_uid); 
$is_allowedToTrackEverybodyInCourse = $is_courseAdmin; // allowed to track all student in course

// check if uid is tutor of this group

$interbredcrump[]= array ("url"=>"../user/userInfo.php?uInfo=".$uInfo, "name"=> $langUsers);
$interbredcrump[]= array ("url"=>"../tracking/userLog.php?uInfo=".$uInfo, "name"=> $langStatsOfUser);

$nameTools = $langStatistics." : ".$langLoginsAndAccessTools;
include($includePath."/claro_init_header.inc.php");

claro_disp_tool_title($nameTools);

//if( ( $is_allowedToTrack || $is_allowedToTrackEverybodyInCourse ) && $is_trackingEnabled )
if( ($is_allowedToTrackEverybodyInCourse || $is_allowedToTrack ) && $is_trackingEnabled )
{
    if( $is_allowedToTrackEverybodyInCourse  || ($uInfo == $_uid)  )
    {
        $sql = "SELECT `u`.`prenom`,`u`.`nom`, `u`.`email`
                FROM `".$tbl_rel_course_user."` cu , `".$tbl_user."` u 
                    WHERE `cu`.`user_id` = `u`.`user_id`
                        AND `cu`.`code_cours` = '".$_cid."'
                        AND `u`.`user_id` = '".$uInfo."'";
    }
    else // user is a tutor
    {
        $sql = "SELECT `u`.`prenom`,`u`.`nom`, `u`.`email`
                    FROM `".$tbl_group_rel_team_user."` gu , `".$tbl_user."` u 
                    WHERE `gu`.`user` = `u`.`user_id`
                        AND `gu`.`team` = '".$_gid."'
                        AND `u`.`user_id` = '".$uInfo."'";
    }
    $query = claro_sql_query($sql);
    $res = mysql_fetch_array($query);
    if(is_array($res))
    {
        $res[2] == '' ? $res2 = $langNoEmail : $res2 = $res[2];
            
        
        echo $langUser.' : <br />'
            .'<ul>'."\n"
            .'<li>'.$langFirstName.' : '.$res[1].'</li>'."\n"
            .'<li>'.$langLastName.' : '.$res[0].'</li>'."\n"
            .'<li>'.$langEmail.' : '.$res2.'</li>'."\n"
            .'</ul>'."\n"
			;
                
        /******* MENU ********/
        echo '<small>'."\n"
            .'[<a href="userLog.php?uInfo='.$uInfo.'">'.$langBack.'</a>]'."\n"
            .'&nbsp;&nbsp;&nbsp;||&nbsp;&nbsp;&nbsp;'."\n"
            .'[<a href="'.$_SERVER['PHP_SELF'].'?uInfo='.$uInfo.'&amp;period=week&amp;reqdate='.$reqdate.'">'.$langPeriodWeek.'</a>]'."\n"
            .'[<a href="'.$_SERVER['PHP_SELF'].'?uInfo='.$uInfo.'&amp;period=month&amp;reqdate='.$reqdate.'">'.$langPeriodMonth.'</a>]'."\n"
            .'&nbsp;&nbsp;&nbsp;||&nbsp;&nbsp;&nbsp;'."\n"
			;
                
        switch($period)
        {
            case "week" :
                // previous and next date must be evaluated
                $previousReqDate = $reqdate - 7*86400;
                $nextReqDate = $reqdate + 7*86400;
                echo '[<a href="'.$_SERVER['PHP_SELF'].'?uInfo='.$uInfo.'&amp;period=week&amp;reqdate='.$previousReqDate.'">'.$langPreviousWeek.'</a>]'."\n" 
                    .'[<a href="'.$_SERVER['PHP_SELF'].'?uInfo='.$uInfo.'&amp;period=week&amp;reqdate='.$nextReqDate.'">'.$langNextWeek.'</a>]'."\n"
					;
                break;
            default :
                $period = "month";
            case "month" :
                // previous and next date must be evaluated
                // 30 days should be a good approximation
                $previousReqDate = mktime(1,1,1,date("m",$reqdate)-1,1,date("Y",$reqdate));
                $nextReqDate = mktime(1,1,1,date("m",$reqdate)+1,1,date("Y",$reqdate));
                echo '[<a href="'.$_SERVER['PHP_SELF'].'?uInfo='.$uInfo.'&amp;period=month&amp;reqdate='.$previousReqDate.'">'.$langPreviousMonth.'</a>]'."\n" 
                    .'[<a href="'.$_SERVER['PHP_SELF'].'?uInfo='.$uInfo.'&amp;period=month&amp;reqdate='.$nextReqDate.'">'.$langNextMonth.'</a>]'."\n"
					;
                break;
    
        
        }
        echo '</small>'."\n\n";
        /******* END OF MENU ********/
        
        if( !isset($reqdate) )
            $reqdate = time();
        switch($period)
        {
            case "month" : 
                $sql = "SELECT `login_date`
                            FROM `".$tbl_track_e_login."`
                            WHERE `login_user_id` = '$uInfo'
                                AND MONTH(`login_date`) = MONTH( FROM_UNIXTIME('".$reqdate."') )
                                AND YEAR(`login_date`) = YEAR(FROM_UNIXTIME(".$reqdate."))
                            ORDER BY `login_date` ASC ";
                $displayedDate = $langMonthNames['long'][date("n", $reqdate)-1].date(" Y", $reqdate);
                break;
            case "week" : 
                $sql = "SELECT `login_date`
                            FROM `".$tbl_track_e_login."`
                            WHERE `login_user_id` = '".$uInfo."'
                                AND WEEK(`login_date`) = WEEK( FROM_UNIXTIME('".$reqdate."') )
                                AND YEAR(`login_date`) = YEAR(FROM_UNIXTIME(".$reqdate."))
                            ORDER BY `login_date` ASC ";
                $weeklowreqdate = ($reqdate-(86400*date("w" , $reqdate)));
                $weekhighreqdate = ($reqdate+(86400*(6-date("w" , $reqdate)) ));
                $displayedDate = $langFrom." ".date("d " , $weeklowreqdate).$langMonthNames['long'][date("n", $weeklowreqdate)-1].date(" Y" , $weeklowreqdate)
                                ." ".$langToDate." ".date("d " , $weekhighreqdate ).$langMonthNames['long'][date("n", $weekhighreqdate)-1].date(" Y" , $weekhighreqdate);
                break;
        }
  
        $results = getManyResults1Col($sql);
        /*** display of the displayed period  ***/
        echo '<table class="claroTable" width="100%" cellpadding="4" cellspacing="1">';
        echo '<tr class="headerX"><th>'.$displayedDate.'</th></tr><tbody>';
        if (is_array($results))
        {             
            for ($j = 0 ; $j < sizeof($results); $j++)
            {
                $timestamp = strtotime($results[$j]);
                //$beautifulDate = $langDay_of_weekNames['long'][date("w" , $timestamp)].date(" d " , $timestamp);
                //$beautifulHour = date("H : i" , $timestamp);
                $beautifulDateTime = claro_disp_localised_date($dateTimeFormatLong,$timestamp);
                echo '<tr>'."\n"
                    .'<td><small>'.$beautifulDateTime.'</small></td>'."\n"
                    .'</tr>'."\n"
					;
                // $limit is used to select only results between $results[$j] (current login) and next one
                if( $j == ( sizeof($results) - 1 ) )
                    $limit = date("Y-m-d H:i:s",$nextReqDate);
                else
                    $limit = $results[$j+1];
                // select all access to tool between displayed date and next displayed date or now() if 
                $sql = "SELECT count(`access_tid`), `access_tlabel`
                            FROM `".$tbl_track_e_access."`
                            WHERE `access_user_id` = '".$uInfo."'
                                AND `access_tid` IS NOT NULL
                                AND `access_date` > '".$results[$j]."'
                                AND `access_date` < '".$limit."'
                            GROUP BY `access_tid`
                            ORDER BY `access_tid` ASC";
                $results2 = getManyResults2Col($sql);
                
                if (is_array($results2))
                { 
                    echo '<tr>'."\n"
					    .'<td colspan="2">'."\n"
                        .'<table width="50%" cellpadding="0" cellspacing="0" border="0">'."\n"
						;
                    for($k = 0 ; $k < count($results2) ; $k++)
                    {                     
                        echo '<tr>'."\n"
                            .'<td width="70%"><small>'.$toolNameList[$results2[$k][1]].'</small></td>'."\n"
                            .'<td width="30%" align="right"><small>'.$results2[$k][0].' '.$langVisits.'</small></td>'."\n"
                            .'</tr>'."\n"
							;
    
                    }
                    echo '</table>'."\n"
                        .'</td></tr>'."\n\n"
						;
                }
                $previousDate = $value;
            }
        
        }
        else
        {
            echo '<tr>'."\n"
                .'<td colspan="2">'
				.'<div align="center" class="highlight">'.$langNoResult.'</div>'
				.'</td>'."\n"
                .'</tr>'."\n"
				;
        }
        echo '</tbody></table>'."\n";
    }
    else
    {
        echo $langErrorUserNotInGroup;
    }    
    
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

include($includePath."/claro_init_footer.inc.php");
?>
