<?php 
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.5.*			                            |
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2002 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
      |   $Id$         |
      +----------------------------------------------------------------------+
      |   Authors : see CREDITS.txt					|
      +----------------------------------------------------------------------+
 */
 
$langFile = "tracking";
require '../inc/claro_init_global.inc.php';

$interbredcrump[]= array ("url"=>"courseLog.php", "name"=> "$langToolName");

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

$TABLETRACK_ACCESS = $_course['dbNameGlu']."track_e_access";

@include($includePath."/claro_init_header.inc.php");
@include($includePath."/lib/statsUtils.lib.inc.php");

$is_allowedToTrack = $is_platformAdmin || $is_courseAdmin;

claro_disp_tool_title($nameTools);

?>
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
        echo "  $langPeriodToDisplay : [<a href='$PHP_SELF?period=year&reqdate=$reqdate&displayType=month' class='specialLink'>$langPeriodYear</a>]
                [<a href='$PHP_SELF?period=month&reqdate=$reqdate&displayType=day' class='specialLink'>$langPeriodMonth</a>]
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
                $sql = "SELECT UNIX_TIMESTAMP( `access_date` )
                            FROM `$TABLETRACK_ACCESS`
                            WHERE YEAR( `access_date` ) = YEAR( FROM_UNIXTIME( $reqdate ) )
                            AND `access_tool` IS NULL ";
                if($displayType == "month")
                {
                    $sql .= "ORDER BY UNIX_TIMESTAMP( `access_date`)";
                    $month_array = monthTab($sql);
                    makeHitsTable($month_array,$langPeriodMonth);
                }
                elseif($displayType == "day")
                {
                    $sql .= "ORDER BY DAYOFYEAR( `access_date`)";
                    $days_array = daysTab($sql);
                    makeHitsTable($days_array,$langPeriodDay);
                }
                else // by hours by default
                {
                    $sql .= "ORDER BY HOUR( `access_date`)";
                    $hours_array = hoursTab($sql);
                    makeHitsTable($hours_array,$langPeriodHour);
                }
                break;
            // all days
            case "month" :
                $sql = "SELECT UNIX_TIMESTAMP( `access_date` )
                            FROM `$TABLETRACK_ACCESS`
                            WHERE MONTH(`access_date`) = MONTH (FROM_UNIXTIME( $reqdate ) )
                            AND YEAR( `access_date` ) = YEAR( FROM_UNIXTIME( $reqdate ) )
                            AND `access_tool` IS NULL ";
                if($displayType == "day")
                {
                    $sql .= "ORDER BY DAYOFYEAR( `access_date`)";
                    $days_array = daysTab($sql);
                    makeHitsTable($days_array,$langPeriodDay);
                }
                else // by hours by default
                {
                    $sql .= "ORDER BY HOUR( `access_date`)";
                    $hours_array = hoursTab($sql);
                    makeHitsTable($hours_array,$langPeriodHour);
                }
                break;
            // all hours
            case "day"  :
                $sql = "SELECT UNIX_TIMESTAMP( `access_date` )
                            FROM `$TABLETRACK_ACCESS`
                            WHERE DAYOFMONTH(`access_date`) = DAYOFMONTH(FROM_UNIXTIME( $reqdate ) )
                            AND MONTH(`access_date`) = MONTH (FROM_UNIXTIME( $reqdate ) )
                            AND YEAR( `access_date` ) = YEAR( FROM_UNIXTIME( $reqdate ) )
                            AND `access_tool` IS NULL
                            ORDER BY HOUR( `access_date` )";
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
