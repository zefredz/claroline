<?php # $Id$
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
/*
  DESCRIPTION
  -------------------
  This file display the detailled informations about the use of tool in a course
  Nothing is displayed if cid is not set and if user is not the courseAdmin

*/

require '../inc/claro_init_global.inc.php';

$interbredcrump[]= array ("url"=>"courseLog.php", "name"=> $langStatistics);

$nameTools = $langDetails;

include($includePath."/claro_init_header.inc.php");

claro_disp_tool_title($nameTools);

// main page
include($includePath."/lib/statsUtils.lib.inc.php");

?>
<table width="100%" cellpadding="2" cellspacing="0" border="0">
<?php 
    
	$tbl_cdb_names = claro_sql_get_course_tbl();
    $TABLETRACK_ACCESS = $tbl_cdb_names['track_e_access'];
    
    if(isset($_cid)) //stats for the current course
    {
        // to see stats of one course user must be courseAdmin of this course
        $is_allowedToTrack = $is_courseAdmin;
    }
    else
    {
        // cid has to be set here else it probably means that the user has directly access this page by url
        $is_allowedToTrack = false;
    }
    if( $is_allowedToTrack && $is_trackingEnabled)
    {
        // list of all tools
        if (!isset($_REQUEST['tool']))
        {
            $sql = "SELECT `access_tid`, count( access_tid ), `access_tlabel`
                        FROM `$TABLETRACK_ACCESS`
                        WHERE `access_tid` IS NOT NULL
                        GROUP BY `access_tid`";
            
            echo "<tr><td>";  
            echo "<tr>
                    <td>
                    <b>$langToolList</b>";
            if(isset($_cid)) echo " for <b>$_cid</b>";
            echo "       </td>
                </tr>
            ";
    
            $results = getManyResults3Col($sql);
            echo "<table class='claroTable' cellpadding='0' cellspacing='0' border='0' align=center>";
            echo "<tr class='headerX'>
                    <th width='70%'>
                    $langToolTitleToolnameColumn
                    </th>
                    <th width='30%'>
                    $langToolTitleCountColumn
                    </th>
                </tr><tbody>";
            if (is_array($results))
            { 
                for($j = 0 ; $j < count($results) ; $j++)
                { 
                        echo "<tr>"
                              ."<td><a href='toolaccess_details.php?tool=".$results[$j][0]."&label=".$results[$j][2]."'>".$toolNameList[$results[$j][2]]."</a></td>"
                              ."<td align='right'>".$results[$j][1]."</td>"
                              ."</tr>";
                }
            
            }
            else
            {
                echo "<tr>"
                      ."<td colspan='2'><center>".$langNoResult."</center></td>"
                      ."</tr>";
            }
            echo "</tbody></table></td></tr>";
        }
        else
        {
            $tool = htmlspecialchars($_REQUEST['tool']);
            if( !isset($reqdate) )
                $reqdate = time();
            echo "<tr>
                    <td>
                    <b>".$toolNameList[$_REQUEST['label']]."</b>
                    </td>
            </tr>";
            
            /* ------ display ------ */
            // displayed period
            echo "<tr><td>";
            switch($period)
            {
                case "month" : 
                    echo $langMonthNames['long'][date("n", $reqdate)-1].date(" Y", $reqdate);
                    break;
                case "week" : 
                    $weeklowreqdate = ($reqdate-(86400*date("w" , $reqdate)));
                    $weekhighreqdate = ($reqdate+(86400*(6-date("w" , $reqdate)) ));
                    echo "<b>".$langFrom."</b> ".date("d " , $weeklowreqdate).$langMonthNames['long'][date("n", $weeklowreqdate)-1].date(" Y" , $weeklowreqdate);
                    echo " <b>".$langToDate."</b> ".date("d " , $weekhighreqdate ).$langMonthNames['long'][date("n", $weekhighreqdate)-1].date(" Y" , $weekhighreqdate);
                    break;
                // default == day
                default :
                    $period = "day";            
                case "day" : 
                    echo $langDay_of_weekNames['long'][date("w" , $reqdate)].date(" d " , $reqdate).$langMonthNames['long'][date("n", $reqdate)-1].date(" Y" , $reqdate);
                    break;
            }
            echo "</tr></td>";
            // periode choice
            echo "<tr>
                    <td>
                    <small>
                    [<a href='".$_SERVER['PHP_SELF']."?tool=$tool&amp;period=day&amp;reqdate=$reqdate'>$langPeriodDay</a>] 
                    [<a href='".$_SERVER['PHP_SELF']."?tool=$tool&amp;period=week&amp;reqdate=$reqdate'>$langPeriodWeek</a>]
                    [<a href='".$_SERVER['PHP_SELF']."?tool=$tool&amp;period=month&amp;reqdate=$reqdate'>$langPeriodMonth</a>]
                    &nbsp;&nbsp;&nbsp;||&nbsp;&nbsp;&nbsp;
                    
                    ";
            switch($period)
            {
                case "month" :
                    // previous and next date must be evaluated
                    // 30 days should be a good approximation
                    $previousReqDate = mktime(1,1,1,date("m",$reqdate)-1,1,date("Y",$reqdate));
                    $nextReqDate = mktime(1,1,1,date("m",$reqdate)+1,1,date("Y",$reqdate));
                    echo   "
                        [<a href='".$_SERVER['PHP_SELF']."?tool=$tool&amp;period=month&amp;reqdate=$previousReqDate'>$langPreviousMonth</a>] 
                        [<a href='".$_SERVER['PHP_SELF']."?tool=$tool&amp;period=month&amp;reqdate=$nextReqDate'>$langNextMonth</a>]
                    ";
                    break;
                case "week" :
                    // previous and next date must be evaluated
                    $previousReqDate = $reqdate - 7*86400;
                    $nextReqDate = $reqdate + 7*86400;
                    echo   "
                        [<a href='".$_SERVER['PHP_SELF']."?tool=$tool&amp;period=week&amp;reqdate=$previousReqDate'>$langPreviousWeek</a>] 
                        [<a href='".$_SERVER['PHP_SELF']."?tool=$tool&amp;period=week&amp;reqdate=$nextReqDate'>$langNextWeek</a>]
                    ";
                    break;
                case "day" :
                    // previous and next date must be evaluated
                    $previousReqDate = $reqdate - 86400;
                    $nextReqDate = $reqdate + 86400;
                    echo   "
                        [<a href='".$_SERVER['PHP_SELF']."?tool=$tool&amp;period=day&amp;reqdate=$previousReqDate'>$langPreviousDay</a>] 
                        [<a href='".$_SERVER['PHP_SELF']."?tool=$tool&amp;period=day&amp;reqdate=$nextReqDate'>$langNextDay</a>]
                    ";
                    break;
            }
            
            echo"   &nbsp;&nbsp;&nbsp;||&nbsp;&nbsp;&nbsp;
                    [<a href='./courseLog.php?view=0010000'>$langViewToolList</a>]
                    </small>
                    </td>
                </tr>
            ";
            // display information about this period
            switch($period)
            {
                // all days
                case "month" :
                    $sql = "SELECT UNIX_TIMESTAMP(`access_date`)
                            FROM `$TABLETRACK_ACCESS`
                            WHERE `access_tid` = '".$_REQUEST['tool']."' 
                                AND MONTH(`access_date`) = MONTH(FROM_UNIXTIME($reqdate))
                                AND YEAR(`access_date`) = YEAR(FROM_UNIXTIME($reqdate))
                                ORDER BY `access_date` ASC";
                    
                    $days_array = daysTab($sql);
                    makeHitsTable($days_array,$langDay);
                    break;
                // all days
                case "week" :
                    $sql = "SELECT UNIX_TIMESTAMP(`access_date`)
                            FROM `$TABLETRACK_ACCESS`
                            WHERE `access_tid` = '".$_REQUEST['tool']."' 
                                AND WEEK(`access_date`) = WEEK(FROM_UNIXTIME($reqdate))
                                AND YEAR(`access_date`) = YEAR(FROM_UNIXTIME($reqdate))
                                ORDER BY `access_date` ASC";
    
                    $days_array = daysTab($sql);
                    makeHitsTable($days_array,$langDay);
                    break;
                // all hours
                case "day"  :
                    $sql = "SELECT UNIX_TIMESTAMP(`access_date`)
                                FROM `$TABLETRACK_ACCESS`
                                WHERE `access_tid` = '".$_REQUEST['tool']."' 
                                    AND DAYOFYEAR(`access_date`) = DAYOFYEAR(FROM_UNIXTIME($reqdate))
                                    AND YEAR(`access_date`) = YEAR(FROM_UNIXTIME($reqdate))
                                ORDER BY `access_date` ASC";
                    
                    $hours_array = hoursTab($sql,$reqdate);
                    makeHitsTable($hours_array,$langHour);
                    break;
            }
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
// footer
include($includePath."/claro_init_footer.inc.php");
?>
