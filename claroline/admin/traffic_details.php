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

$interbredcrump[]= array ("url"=>"index.php", "name"=> "Admin");
$interbredcrump[]= array ("url"=>"campusLog.php", "name"=> $langStatsOfCampus);

$nameTools = $langTrafficDetails;

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

$TABLETRACK_OPEN = $statsDbName."`.`track_e_open";

@include($includePath."/claro_init_header.inc.php");
@include($includePath."/lib/statsUtils.lib.inc.php");

$is_allowedToTrack = $is_platformAdmin;

?>
<h3>
    <?php echo $nameTools ?>
</h3>
<table width="100%" cellpadding="2" cellspacing="3" border="0">
<?php
    if( $is_allowedToTrack && $is_trackingEnabled)
    {
        if( !isset($reqdate) || $reqdate < 0 || $reqdate > 2149372861 )
                $reqdate = time();
        //** dislayed period
        echo "<tr><td><b>";
            switch($period)
            {
                case "year" : 
                    echo date(" Y", $reqdate);
                    break;
                case "month" : 
                    echo $langMonthNames['long'][date("n", $reqdate)-1].date(" Y", $reqdate);
                    break;
                // default == day
                default :
                    $period = "day";            
                case "day" : 
                    echo $langDay_of_weekNames['long'][date("w" , $reqdate)].date(" d " , $reqdate).$langMonthNames['long'][date("n", $reqdate)-1].date(" Y" , $reqdate);
                    break;
            }
        echo "</b></tr></td>";
        //** menu
        echo "<tr>
                <td>
                <small>
        ";
        echo "  $langPeriodToDisplay : [<a href='$PHP_SELF?period=year&reqdate=$reqdate' class='specialLink'>$langPeriodYear</a>]
                [<a href='$PHP_SELF?period=month&reqdate=$reqdate' class='specialLink'>$langPeriodMonth</a>]
                [<a href='$PHP_SELF?period=day&reqdate=$reqdate' class='specialLink'>$langPeriodDay</a>]
                &nbsp;&nbsp;&nbsp;||&nbsp;&nbsp;&nbsp;
                $langDetailView :
        ";
        switch($period)
        {
            case "year" : 
                    //-- if period is "year" display can be by month, day or hour
                    echo "  [<a href='$PHP_SELF?period=$period&reqdate=$reqdate&displayType=month' class='specialLink'>$langPeriodMonth</a>]";
            case "month" : 
                    //-- if period is "month" display can be by day or hour
                    echo "  [<a href='$PHP_SELF?period=$period&reqdate=$reqdate&displayType=day' class='specialLink'>$langPeriodDay</a>]";
            case "day" : 
                    //-- if period is "day" display can only be by hour
                    echo "  [<a href='$PHP_SELF?period=$period&reqdate=$reqdate&displayType=hour' class='specialLink'>$langPeriodHour</a>]";
                    break;
        }
        
        echo "&nbsp;&nbsp;&nbsp;||&nbsp;&nbsp;&nbsp;";
        
        switch($period)
        {
            case "year" :
                // previous and next date must be evaluated
                // 30 days should be a good approximation
                $previousReqDate = mktime(1,1,1,1,1,date("Y",$reqdate)-1);
                $nextReqDate = mktime(1,1,1,1,1,date("Y",$reqdate)+1);
                echo   "
                    [<a href='$PHP_SELF?period=$period&reqdate=$previousReqDate&displayType=$displayType' class='specialLink'>$langPreviousYear</a>]
                    [<a href='$PHP_SELF?period=$period&reqdate=$nextReqDate&displayType=$displayType' class='specialLink'>$langNextYear</a>] 
                ";
                break;
            case "month" :
                // previous and next date must be evaluated
                // 30 days should be a good approximation
                $previousReqDate = mktime(1,1,1,date("m",$reqdate)-1,1,date("Y",$reqdate));
                $nextReqDate = mktime(1,1,1,date("m",$reqdate)+1,1,date("Y",$reqdate));
                echo   "
                    [<a href='$PHP_SELF?period=$period&reqdate=$previousReqDate&displayType=$displayType' class='specialLink'>$langPreviousMonth</a>]
                    [<a href='$PHP_SELF?period=$period&reqdate=$nextReqDate&displayType=$displayType' class='specialLink'>$langNextMonth</a>] 
                ";
                break;
            case "day" :
                // previous and next date must be evaluated
                $previousReqDate = $reqdate - 86400;
                $nextReqDate = $reqdate + 86400;
                echo   "
                    [<a href='$PHP_SELF?period=$period&reqdate=$previousReqDate&displayType=$displayType' class='specialLink'>$langPreviousDay</a>] 
                    [<a href='$PHP_SELF?period=$period&reqdate=$nextReqDate&displayType=$displayType' class='specialLink'>$langNextDay</a>] 
                ";
                break;
        }
        echo "  </small>
                </td>
              </tr>
        ";
        //**
        // display information about this period
        switch($period)
        {
            // all days
            case "year" :
                $sql = "SELECT UNIX_TIMESTAMP( `open_date` ) 
                            FROM `$TABLETRACK_OPEN`
                            WHERE YEAR( `open_date` ) = YEAR( FROM_UNIXTIME( $reqdate ) ) ";
                if($displayType == "month")
                {
                    $sql .= "ORDER BY UNIX_TIMESTAMP( `open_date`)";
                    $month_array = monthTab($sql);
                    makeHitsTable($month_array,$langPeriodMonth);
                }
                elseif($displayType == "day")
                {
                    $sql .= "ORDER BY DAYOFYEAR( `open_date`)";
                    $days_array = daysTab($sql);
                    makeHitsTable($days_array,$langPeriodDay);
                }
                else // by hours by default
                {
                    $sql .= "ORDER BY HOUR( `open_date`)";
                    $hours_array = hoursTab($sql);
                    makeHitsTable($hours_array,$langPeriodHour);
                }
                break;
            // all days
            case "month" :
                $sql = "SELECT UNIX_TIMESTAMP( `open_date` ) 
                            FROM `$TABLETRACK_OPEN`
                            WHERE MONTH(`open_date`) = MONTH (FROM_UNIXTIME( $reqdate ) ) 
                                AND YEAR( `open_date` ) = YEAR( FROM_UNIXTIME( $reqdate ) ) ";
                if($displayType == "day")
                {
                    $sql .= "ORDER BY DAYOFYEAR( `open_date`)";
                    $days_array = daysTab($sql);
                    makeHitsTable($days_array,$langPeriodDay);
                }
                else // by hours by default
                {
                    $sql .= "ORDER BY HOUR( `open_date`)";
                    $hours_array = hoursTab($sql);
                    makeHitsTable($hours_array,$langPeriodHour);
                }
                break;
            // all hours
            case "day"  :
                $sql = "SELECT UNIX_TIMESTAMP( `open_date` ) 
                            FROM `$TABLETRACK_OPEN`
                            WHERE DAYOFMONTH(`open_date`) = DAYOFMONTH(FROM_UNIXTIME( $reqdate ) ) 
                                AND MONTH(`open_date`) = MONTH (FROM_UNIXTIME( $reqdate ) ) 
                                AND YEAR( `open_date` ) = YEAR( FROM_UNIXTIME( $reqdate ) ) 
                            ORDER BY HOUR( `open_date` )";
                $hours_array = hoursTab($sql,$reqdate);
                makeHitsTable($hours_array,$langPeriodHour);
                break;
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

<?
@include($includePath."/claro_init_footer.inc.php");
?>
