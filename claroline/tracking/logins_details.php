<?php  // $Id$
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.6
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2004 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
 */
 
require '../inc/claro_init_global.inc.php';

// uInfo is required, back to user list if there is none
if( empty($_REQUEST['uInfo']) ) 
{
    header("Location: ../user/user.php");
    die();
}
else
{
    $uInfo = (int) $_REQUEST['uInfo'];
}

if( !empty($_REQUEST['reqdate']) ) 	$reqdate = (int)$_REQUEST['reqdate'];
else                                $reqdate = time();

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

if ( isset($_uid) )
	$is_allowedToTrack = $is_allowedToTrack || ($uInfo == $_uid);

$is_allowedToTrackEverybodyInCourse = $is_courseAdmin; // allowed to track all student in course

// check if uid is tutor of this group

$interbredcrump[]= array ("url"=>"../user/userInfo.php?uInfo=".$uInfo, "name"=> $langUsers);
$interbredcrump[]= array ("url"=>"../tracking/userLog.php?uInfo=".$uInfo, "name"=> $langStatsOfUser);
$_SERVER['QUERY_STRING'] = 'uInfo='.$uInfo."&amp;reqdate=".$reqdate;

$nameTools = $langStatistics." : ".$langLoginsAndAccessTools;
include($includePath."/claro_init_header.inc.php");

echo claro_disp_tool_title($nameTools);

if( ($is_allowedToTrackEverybodyInCourse || $is_allowedToTrack ) && $is_trackingEnabled )
{
    if( $is_allowedToTrackEverybodyInCourse  || ($uInfo == $_uid)  )
    {
        $sql = "SELECT `u`.`prenom` AS `firstname`,
						`u`.`nom` AS `lastname`,
						`u`.`email`
                FROM `".$tbl_rel_course_user."` cu , `".$tbl_user."` u 
                    WHERE `cu`.`user_id` = `u`.`user_id`
                        AND `cu`.`code_cours` = '".$_cid."'
                        AND `u`.`user_id` = '". (int)$uInfo ."'";
    }
    else // user is a tutor
    {
        $sql = "SELECT `u`.`prenom` as `firstname`,
						`u`.`nom`, `u`.`email`
                    FROM `".$tbl_group_rel_team_user."` gu , `".$tbl_user."` u 
                    WHERE `gu`.`user` = `u`.`user_id`
                        AND `gu`.`team` = '".$_gid."'
                        AND `u`.`user_id` = '". (int)$uInfo ."'";
    }
    $userDetails = claro_sql_query_get_single_row($sql);

    if( is_array($userDetails) && !empty($userDetails) )
    {
        echo $langUser.' : <br />'
            .'<ul>'."\n"
            .'<li>'.$langLastName.' : '.$userDetails['lastname'].'</li>'."\n"
            .'<li>'.$langFirstName.' : '.$userDetails['firstname'].'</li>'."\n"
            .'<li>'.$langEmail.' : ';
        if( empty($userDetails['email']) ) echo $langNoEmail; else echo $userDetails['email'];
		echo '</li>'."\n"
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

		if( !empty($_REQUEST['period']) ) 	$period = $_REQUEST['period'];
		else                                $period = "";
		
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
                            WHERE `login_user_id` = ". (int)$uInfo ."
                                AND MONTH(`login_date`) = MONTH( FROM_UNIXTIME('".$reqdate."') )
                                AND YEAR(`login_date`) = YEAR( FROM_UNIXTIME(".$reqdate.") )
                            ORDER BY `login_date` ASC ";
                $displayedDate = $langMonthNames['long'][date("n", $reqdate)-1].date(" Y", $reqdate);
                break;
            case "week" : 
                $sql = "SELECT `login_date`
                            FROM `".$tbl_track_e_login."`
                            WHERE `login_user_id` = '". (int)$uInfo ."'
                                AND WEEK(`login_date`) = WEEK( FROM_UNIXTIME('".$reqdate."') )
                                AND YEAR(`login_date`) = YEAR( FROM_UNIXTIME(".$reqdate.") )
                            ORDER BY `login_date` ASC ";
                $weeklowreqdate = ($reqdate-(86400*date("w" , $reqdate)));
                $weekhighreqdate = ($reqdate+(86400*(6-date("w" , $reqdate)) ));
                $displayedDate = $langFrom." ".date("d " , $weeklowreqdate).$langMonthNames['long'][date("n", $weeklowreqdate)-1].date(" Y" , $weeklowreqdate)
                                ." ".$langToDate." ".date("d " , $weekhighreqdate ).$langMonthNames['long'][date("n", $weekhighreqdate)-1].date(" Y" , $weekhighreqdate);
                break;
        }

        $loginDates = claro_sql_query_fetch_all($sql);
        
        /*** display of the displayed period  ***/
        echo '<table class="claroTable" width="100%" cellpadding="4" cellspacing="1">';
        echo '<tr class="headerX"><th>'.$displayedDate.'</th></tr><tbody>';
        if( !empty($loginDates) && is_array($loginDates) )
        {
            $i = 0;
            while( $i < sizeof($loginDates) )
            {
                echo '<tr>'."\n"
                    .'<td><small>'.claro_disp_localised_date( $dateTimeFormatLong, strtotime($loginDates[$i]['login_date']) ).'</small></td>'."\n"
                    .'</tr>'."\n"
					;
                // $limit is used to select only results between current login and next one
                if( $i == ( sizeof($loginDates) - 1 ) || !isset($loginDates[$i+1]['login_date']) )
                    $limit = date("Y-m-d H:i:s",$nextReqDate);
                else
                    $limit = $loginDates[$i+1]['login_date'];

                // select all access in the displayed date range
	            $sql = "SELECT `access_tlabel`, count(`access_tid`) AS `nbr_access`
	                        FROM `".$tbl_track_e_access."`
	                        WHERE `access_user_id` = '". (int)$uInfo."'
	                            AND `access_tid` IS NOT NULL
	                            AND `access_date` > '".$loginDates[$i]['login_date']."'
	                            AND `access_date` < '".$limit."'
	                        GROUP BY `access_tid`
	                        ORDER BY `access_tid` ASC";
	            $toolAccess = claro_sql_query_fetch_all($sql);
	            
                if( !empty($toolAccess) && is_array($toolAccess) )
                { 
                    echo '<tr>'."\n"
					    .'<td colspan="2">'."\n"
                        .'<table width="50%" cellpadding="0" cellspacing="0" border="0">'."\n"
						;
                    foreach( $toolAccess as $aToolAccess )
                    {                     
                        echo '<tr>'."\n"
                            .'<td width="70%"><small>'.$toolNameList[$aToolAccess['access_tlabel']].'</small></td>'."\n"
                            .'<td width="30%" align="right"><small>'.$aToolAccess['nbr_access'].' '.$langVisits.'</small></td>'."\n"
                            .'</tr>'."\n"
							;
    
                    }
                    echo '</table>'."\n"
                        .'</td></tr>'."\n\n"
						;
                }
                
                $i++;
            }
        
        }
        else
        {
            echo '<tr>'."\n"
                .'<td colspan="2">'
				.'<div align="center">'.$langNoResult.'</div>'
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
