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
 
$langFile = "tracking";
require '../inc/claro_init_global.inc.php';

$interbredcrump[]= array ("url"=>"../user/userInfo.php?uInfo=".$uInfo, "name"=> $langBredCrumpUsers);
$interbredcrump[]= array ("url"=>"../tracking/userLog.php?uInfo=".$uInfo, "name"=> $langStatsOfUser);

$nameTools = $langToolName." : ".$langLoginsAndAccessTools;

$htmlHeadXtra[] = "<style type='text/css'>
<!--
.secLine {background-color : #E6E6E6;}
.content {padding-left : 15px;padding-right : 15px; }
.specialLink{color : #0000FF;}
-->
</style>
<STYLE media='print' type='text/css'>
<!--
TD {border-bottom: thin dashed Gray;}
-->
</STYLE>";

$TABLECOURSUSER	        = $mainDbName."`.`cours_user";
$TABLECOURSE_GROUPSUSER = $_course['dbNameGlu']."user_group";
$TABLEUSER	        = $mainDbName."`.`user";

$TABLETRACK_LOGIN       = $statsDbName."`.`track_e_login";
$TABLETRACK_ACCESS      = $_course['dbNameGlu']."track_e_access";

@include($includePath."/claro_init_header.inc.php");
@include($includePath."/lib/statsUtils.lib.inc.php");

$is_allowedToTrack = $is_groupTutor; // allowed to track only user of one group
// following line added by RH to allow a user to see its own course stats
if (isset($uInfo) && isset($_uid)) $is_allowedToTrack = $is_allowedToTrack || ($uInfo == $_uid); 
$is_allowedToTrackEverybodyInCourse = $is_courseAdmin; // allowed to track all student in course
?>
<h3>
    <?php echo $nameTools ?>
</h3>
<table width="100%" cellpadding="2" cellspacing="3" border="0">
<?
// check if uid is tutor of this group


//if( ( $is_allowedToTrack || $is_allowedToTrackEverybodyInCourse ) && $is_trackingEnabled )
if( ($is_allowedToTrackEverybodyInCourse || $is_allowedToTrack ) && $is_trackingEnabled )
{
    if( $is_allowedToTrackEverybodyInCourse  || ($uInfo == $_uid)  )
    {
        $sql = "SELECT `u`.`prenom`,`u`.`nom`, `u`.`email`
                    FROM `$TABLECOURSUSER` cu , `$TABLEUSER` u 
                    WHERE `cu`.`user_id` = `u`.`user_id`
                        AND `cu`.`code_cours` = '$_cid'
                        AND `u`.`user_id` = '$uInfo'";
    }
    else // user is a tutor
    {
        $sql = "SELECT `u`.`prenom`,`u`.`nom`, `u`.`email`
                    FROM `$TABLECOURSE_GROUPSUSER` gu , `$TABLEUSER` u 
                    WHERE `gu`.`user` = `u`.`user_id`
                        AND `gu`.`team` = '$_gid'
                        AND `u`.`user_id` = '$uInfo'";
    }
    $query = @mysql_query($sql);
    $res = @mysql_fetch_array($query);
    if(is_array($res))
    {
        $res[2] == "" ? $res2 = $langNoEmail : $res2 = $res[2];
            
        echo "<tr><td>";
        echo $informationsAbout." : <br>";
        echo "<ul>\n"
                ."<li>".$langFirstName." : ".$res[1]."</li>\n"
                ."<li>".$langLastName." : ".$res[0]."</li>\n"
                ."<li>".$langEmail." : ".$res2."</li>\n"
                ."</ul>";
        echo "</td></tr>";
        /******* MENU ********/
        echo "<tr>
                <td>
                <small>
                [<a href='userLog.php?uInfo=$uInfo'>".$langBack."</a>]
        ";
        echo "  &nbsp;&nbsp;&nbsp;||&nbsp;&nbsp;&nbsp;
                [<a href='$PHP_SELF?uInfo=$uInfo&period=week&reqdate=$reqdate' class='specialLink'>$langPeriodWeek</a>]
                [<a href='$PHP_SELF?uInfo=$uInfo&period=month&reqdate=$reqdate' class='specialLink'>$langPeriodMonth</a>]
                &nbsp;&nbsp;&nbsp;||&nbsp;&nbsp;&nbsp;
        ";
        switch($period)
        {
            case "week" :
                // previous and next date must be evaluated
                $previousReqDate = $reqdate - 7*86400;
                $nextReqDate = $reqdate + 7*86400;
                echo   "
                    [<a href='$PHP_SELF?uInfo=$uInfo&period=week&reqdate=$previousReqDate' class='specialLink'>$langPreviousWeek</a>] 
                    [<a href='$PHP_SELF?uInfo=$uInfo&period=week&reqdate=$nextReqDate' class='specialLink'>$langNextWeek</a>]
                ";
                break;
            default :
                $period = "month";
            case "month" :
                // previous and next date must be evaluated
                // 30 days should be a good approximation
                $previousReqDate = mktime(1,1,1,date("m",$reqdate)-1,1,date("Y",$reqdate));
                $nextReqDate = mktime(1,1,1,date("m",$reqdate)+1,1,date("Y",$reqdate));
                echo   "
                    [<a href='$PHP_SELF?uInfo=$uInfo&period=month&reqdate=$previousReqDate' class='specialLink'>$langPreviousMonth</a>] 
                    [<a href='$PHP_SELF?uInfo=$uInfo&period=month&reqdate=$nextReqDate' class='specialLink'>$langNextMonth</a>]
                ";
                break;
    
        
        }
        echo "</small>
                </td>
            </tr>
        ";
        /******* END OF MENU ********/
        
        if( !isset($reqdate) )
            $reqdate = time();
        switch($period)
        {
            case "month" : 
                $sql = "SELECT `login_date`
                            FROM `$TABLETRACK_LOGIN`
                            WHERE `login_user_id` = '$uInfo'
                                AND MONTH(`login_date`) = MONTH( FROM_UNIXTIME('$reqdate') )
                                AND YEAR(`login_date`) = YEAR(FROM_UNIXTIME($reqdate))
                            ORDER BY `login_date` ASC ";
                $displayedDate = $langMonthNames['long'][date("n", $reqdate)-1].date(" Y", $reqdate);
                break;
            case "week" : 
                $sql = "SELECT `login_date`
                            FROM `$TABLETRACK_LOGIN`
                            WHERE `login_user_id` = '$uInfo'
                                AND WEEK(`login_date`) = WEEK( FROM_UNIXTIME('$reqdate') )
                                AND YEAR(`login_date`) = YEAR(FROM_UNIXTIME($reqdate))
                            ORDER BY `login_date` ASC ";
                $weeklowreqdate = ($reqdate-(86400*date("w" , $reqdate)));
                $weekhighreqdate = ($reqdate+(86400*(6-date("w" , $reqdate)) ));
                $displayedDate = $langFrom." ".date("d " , $weeklowreqdate).$langMonthNames['long'][date("n", $weeklowreqdate)-1].date(" Y" , $weeklowreqdate)
                                ." ".$langTo." ".date("d " , $weekhighreqdate ).$langMonthNames['long'][date("n", $weekhighreqdate)-1].date(" Y" , $weekhighreqdate);
                break;
        }
        echo "<tr><td>";  
        $results = getManyResults1Col($sql);
        /*** display of the displayed period  ***/
        echo "<table class=\"claroTable\" width='100%' cellpadding='2' cellspacing='1' border='0' align=center>";
        echo "<tr class=\"headerX\"><th>".$displayedDate."</th></tr><body>";
        if (is_array($results))
        {             
            for ($j = 0 ; $j < sizeof($results); $j++)
            {
                $timestamp = strtotime($results[$j]);
                //$beautifulDate = $langDay_of_weekNames['long'][date("w" , $timestamp)].date(" d " , $timestamp);
                //$beautifulHour = date("H : i" , $timestamp);
                $beautifulDateTime = dateLocalizer($dateTimeFormatLong,$timestamp);
                echo "<tr>"; 
                echo "<td style='padding-left : 40px;' valign='top'><small>".$beautifulDateTime."</small></td>";
                echo"</tr>";
                // $limit is used to select only results between $results[$j] (current login) and next one
                if( $j == ( sizeof($results) - 1 ) )
                    $limit = date("Y-m-d H:i:s",$nextReqDate);
                else
                    $limit = $results[$j+1];
                // select all access to tool between displayed date and next displayed date or now() if 
                // displayed date is the last login date
                $sql = "SELECT `access_tool`, count(`access_tool`), `access_date`
                            FROM `$TABLETRACK_ACCESS`
                            WHERE `access_user_id` = '$uInfo'
                                AND `access_tool` IS NOT NULL
                                AND `access_date` > '".$results[$j]."'
                                AND `access_date` < '".$limit."'
                            GROUP BY `access_tool`
                            ORDER BY `access_tool` ASC";
                $results2 = getManyResults2Col($sql);
                
                if (is_array($results2))
                { 
                    echo "<tr><td colspan='2'>\n";  
                    echo "<table width='50%' cellpadding='0' cellspacing='0' border='0'>\n";
                    for($k = 0 ; $k < count($results2) ; $k++)
                    {                     
                            echo "<tr>\n";
                            echo "<td width='70%' style='padding-left : 60px;'><small>".$results2[$k][0]."</small></td>\n";
                            echo "<td width='30%' align='right' style='padding-right : 40px'><small>".$results2[$k][1]." ".$langVisits."</small></td>\n";
                            echo "</tr>";
    
                    }
                    echo "</table>\n";
                    echo "</td></tr>\n";
                }
                $previousDate = $value;
            }
        
        }
        else
        {
            echo "<tr>"; 
            echo "<td colspan='2' bgcolor='#eeeeee'><center>".$langNoResult."</center></td>";
            echo"</tr>";
        }
        echo "</body></table>";
        echo "</td></tr>";
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
?>

</table>

<?
@include($includePath."/claro_init_footer.inc.php");
?>
