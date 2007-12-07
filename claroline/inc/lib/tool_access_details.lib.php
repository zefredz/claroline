<?php 
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.3.0 $Revision$                            |
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2002 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
      |   $Id$         |
      +----------------------------------------------------------------------+
      |   This program is free software; you can redistribute it and/or      |
      |   modify it under the terms of the GNU General Public License        |
      |   as published by the Free Software Foundation; either version 2     |
      |   of the License, or (at your option) any later version.             |
      |                                                                      |
      |   This program is distributed in the hope that it will be useful,    |
      |   but WITHOUT ANY WARRANTY; without even the implied warranty of     |
      |   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the      |
      |   GNU General Public License for more details.                       |
      |                                                                      |
      |   You should have received a copy of the GNU General Public License  |
      |   along with this program; if not, write to the Free Software        |
      |   Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA          |
      |   02111-1307, USA. The GNU GPL license is also available through     |
      |   the world-wide-web at http://www.gnu.org/copyleft/gpl.html         |
      +----------------------------------------------------------------------+
      | Authors: Thomas Depraetere <depraetere@ipm.ucl.ac.be>                |
      |          Hugues Peeters    <peeters@ipm.ucl.ac.be>                   |
      |          Christophe Gesché <gesche@ipm.ucl.ac.be>                    |
      |          Sebastien Piraux  <piraux_seb@hotmail.com>
      +----------------------------------------------------------------------+
 */
 
@include($includePath."/lib/statsUtils.lib.inc.php");

?>
<table width="100%" cellpadding="2" cellspacing="0" border="0">
<?php 
    
    
    $TABLETRACK_ACCESS = $statsDbName."`.`track_e_access";
    
    if(isset($_cid)) //stats for the current course
    {
        // to see stats of one course user must be courseAdmin of this course
        $is_allowedToTrack = $is_courseAdmin;
        $courseCodeEqualcidIfNeeded = "AND `access_cours_code` = '$_cid'";
    }
    else // stats for all courses
    {
        // to see stats of all courses user must be platformAdmin
        $is_allowedToTrack = $is_platformAdmin;
        $courseCodeEqualcidIfNeeded = "";
    }
    if( $is_allowedToTrack && $is_trackingEnabled)
    {
        // list of all tools
        if (!isset($tool))
        {
            $sql = "SELECT `access_tid`, count( access_tid ) 
                        FROM `$TABLETRACK_ACCESS`
                        WHERE `access_tid` IS NOT NULL
                            ".$courseCodeEqualcidIfNeeded."
                        GROUP BY `access_tid`";
            echo "<tr><td>";  
            echo "<tr>
                    <td>
                    <b>$langToolList</b>";
            if(isset($_cid)) echo " for <b>$_cid</b>";
            echo "       </td>
                </tr>
            ";
    
            $results = getManyResults2Col($sql);
            echo "<table cellpadding='0' cellspacing='0' border='0' align=center>";
            echo "<tr bgcolor='#E6E6E6'>
                    <td width='70%'>
                    $langToolTitleToolnameColumn
                    </td>
                    <td width='30%'>
                    $langToolTitleCountColumn
                    </td>
                </tr>";
            if (is_array($results))
            { 
                for($j = 0 ; $j < count($results) ; $j++)
                { 
                        echo "<tr>"; 
                        echo "<td><a href='toolaccess_details.php?tool=".urlencode($results[$j][0])."'>".$results[$j][0]."</a></td>";
                        echo "<td align='right'>".$results[$j][1]."</td>";
                        echo"</tr>";
                }
            
            }
            else
            {
                echo "<tr>"; 
                echo "<td colspan='2'><center>".$langNoResult."</center></td>";
                echo"</tr>";
            }
            echo "</table></td></tr>";
        }
        else
        {
            // this can prevent bug if there is special chars in $tool
            $encodedTool = urlencode($tool);
            $tool = urldecode($tool);
            
            if( !isset($reqdate) )
                $reqdate = time();
            echo "<tr>
                    <td>
                    <b>$tool</b>";
            if(isset($_cid)) echo " for <b>$_cid</b>";
            echo " </td>
                </tr>
            ";
            
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
                    echo " <b>".$langTo."</b> ".date("d " , $weekhighreqdate ).$langMonthNames['long'][date("n", $weekhighreqdate)-1].date(" Y" , $weekhighreqdate);
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
                    [<a href='$PHP_SELF?tool=$encodedTool&period=day&reqdate=$reqdate' class='specialLink'>$langPeriodDay</a>] 
                    [<a href='$PHP_SELF?tool=$encodedTool&period=week&reqdate=$reqdate' class='specialLink'>$langPeriodWeek</a>]
                    [<a href='$PHP_SELF?tool=$encodedTool&period=month&reqdate=$reqdate' class='specialLink'>$langPeriodMonth</a>]
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
                        [<a href='$PHP_SELF?tool=$encodedTool&period=month&reqdate=$previousReqDate' class='specialLink'>$langPreviousMonth</a>] 
                        [<a href='$PHP_SELF?tool=$encodedTool&period=month&reqdate=$nextReqDate' class='specialLink'>$langNextMonth</a>]
                    ";
                    break;
                case "week" :
                    // previous and next date must be evaluated
                    $previousReqDate = $reqdate - 7*86400;
                    $nextReqDate = $reqdate + 7*86400;
                    echo   "
                        [<a href='$PHP_SELF?tool=$encodedTool&period=week&reqdate=$previousReqDate' class='specialLink'>$langPreviousWeek</a>] 
                        [<a href='$PHP_SELF?tool=$encodedTool&period=week&reqdate=$nextReqDate' class='specialLink'>$langNextWeek</a>]
                    ";
                    break;
                case "day" :
                    // previous and next date must be evaluated
                    $previousReqDate = $reqdate - 86400;
                    $nextReqDate = $reqdate + 86400;
                    echo   "
                        [<a href='$PHP_SELF?tool=$encodedTool&period=day&reqdate=$previousReqDate' class='specialLink'>$langPreviousDay</a>] 
                        [<a href='$PHP_SELF?tool=$encodedTool&period=day&reqdate=$nextReqDate' class='specialLink'>$langNextDay</a>]
                    ";
                    break;
            }
            
            echo"   &nbsp;&nbsp;&nbsp;||&nbsp;&nbsp;&nbsp;
                    [<a href='$PHP_SELF' class='specialLink'>$langViewToolList</a>]
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
                            WHERE `access_tid` = '$tool' 
                                ".$courseCodeEqualcidIfNeeded."
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
                            WHERE `access_tid` = '$tool' 
                                ".$courseCodeEqualcidIfNeeded."
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
                                WHERE `access_tid` = '$tool' 
                                    ".$courseCodeEqualcidIfNeeded."
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