<?php // $Id$
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.6
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2004 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
 */

require '../inc/claro_init_global.inc.php';

// Security check
if ( ! $_uid ) claro_disp_auth_form();
if ( ! $is_platformAdmin ) claro_die($langNotAllowed);

include($includePath."/lib/statsUtils.lib.inc.php");
$tbl_mdb_names 			= claro_sql_get_main_tbl();
$tbl_track_e_open       = $tbl_mdb_names['track_e_open'];

$is_allowedToTrack = $is_platformAdmin;

$interbredcrump[]= array ("url"=>"index.php", "name"=> $langAdministration);
$interbredcrump[]= array ("url"=>"campusLog.php", "name"=> $langStatsOfCampus);

$nameTools = $langTrafficDetails;

include($includePath."/claro_init_header.inc.php");
echo claro_disp_tool_title(
	array(
	'mainTitle'=>$nameTools,
	)
);
?>
<table width="100%" cellpadding="2" cellspacing="3" border="0">
<?php
    if( $is_allowedToTrack && $is_trackingEnabled)
    {
        if( !isset($_REQUEST['reqdate']) || $_REQUEST['reqdate'] < 0 || $_REQUEST['reqdate'] > 2149372861 )
        	$reqdate = time();  // default value
		else
		    $reqdate = (int)$_REQUEST['reqdate'];
		    
        if( isset($_REQUEST['period']) )    $period = $_REQUEST['period'];
        else                                $period = "day"; // default value
        
        if( isset($_REQUEST['displayType']) )   $displayType = $_REQUEST['displayType'];
        else                                	$displayType = ''; // default value

        // dislayed period
        echo "<tr><td><b>";

        switch($period)
        {
            case "year" : 
                echo date(" Y", $reqdate);
                break;
            case "month" : 
                echo $langMonthNames['long'][date("n", $reqdate)-1].date(" Y", $reqdate);
                break;
			default :
       			$period = "day"; // if $period has no correct value
            case "day" :
                echo $langDay_of_weekNames['long'][date("w" , $reqdate)].date(" d " , $reqdate).$langMonthNames['long'][date("n", $reqdate)-1].date(" Y" , $reqdate);
                break;
        }
        
        echo "</b></tr></td>";
        // menu
        echo "<tr>
                <td>
                <small>
        ";
        echo "  $langPeriodToDisplay : [<a href='".$_SERVER['PHP_SELF']."?period=year&reqdate=$reqdate' >$langPeriodYear</a>]
                [<a href='".$_SERVER['PHP_SELF']."?period=month&reqdate=$reqdate' >$langPeriodMonth</a>]
                [<a href='".$_SERVER['PHP_SELF']."?period=day&reqdate=$reqdate' >$langPeriodDay</a>]
                &nbsp;&nbsp;&nbsp;||&nbsp;&nbsp;&nbsp;
                $langDetailView :
        ";
        switch($period)
        {
            case "year" : 
                    //-- if period is "year" display can be by month, day or hour
                    echo "  [<a href='".$_SERVER['PHP_SELF']."?period=$period&reqdate=$reqdate&displayType=month' >$langPeriodMonth</a>]";
            case "month" : 
                    //-- if period is "month" display can be by day or hour
                    echo "  [<a href='".$_SERVER['PHP_SELF']."?period=$period&reqdate=$reqdate&displayType=day' >$langPeriodDay</a>]";
            case "day" : 
                    //-- if period is "day" display can only be by hour
                    echo "  [<a href='".$_SERVER['PHP_SELF']."?period=$period&reqdate=$reqdate&displayType=hour' >$langPeriodHour</a>]";
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
                    [<a href='".$_SERVER['PHP_SELF']."?period=".$period."&reqdate=".$previousReqDate."&displayType=".$displayType."' >".$langPreviousYear."</a>]
                    [<a href='".$_SERVER['PHP_SELF']."?period=".$period."&reqdate=".$nextReqDate."&displayType=".$displayType."' >".$langNextYear."</a>]
                ";
                break;
            case "month" :
                // previous and next date must be evaluated
                // 30 days should be a good approximation
                $previousReqDate = mktime(1,1,1,date("m",$reqdate)-1,1,date("Y",$reqdate));
                $nextReqDate = mktime(1,1,1,date("m",$reqdate)+1,1,date("Y",$reqdate));
                echo   "
                    [<a href='".$_SERVER['PHP_SELF']."?period=".$period."&reqdate=".$previousReqDate."&displayType=".$displayType."' >".$langPreviousMonth."</a>]
                    [<a href='".$_SERVER['PHP_SELF']."?period=".$period."&reqdate=".$nextReqDate."&displayType=".$displayType."' >".$langNextMonth."</a>]
                ";
                break;
            case "day" :
                // previous and next date must be evaluated
                $previousReqDate = $reqdate - 86400;
                $nextReqDate = $reqdate + 86400;
                echo   "
                    [<a href='".$_SERVER['PHP_SELF']."?period=".$period."&reqdate=".$previousReqDate."&displayType=".$displayType."' >".$langPreviousDay."</a>] 
                    [<a href='".$_SERVER['PHP_SELF']."?period=".$period."&reqdate=".$nextReqDate."&displayType=".$displayType."' >".$langNextDay."</a>] 
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
                            FROM `".$tbl_track_e_open."`
                            WHERE YEAR( `open_date` ) = YEAR( FROM_UNIXTIME( ".(int)$reqdate." ) ) ";
                if( $displayType == "month" )
                {
                    $sql .= "ORDER BY UNIX_TIMESTAMP( `open_date`)";
                    $month_array = monthTab($sql);
                    makeHitsTable($month_array,$langPeriodMonth);
                }
                elseif( $displayType == "day" )
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
                            FROM `".$tbl_track_e_open."`
                            WHERE MONTH(`open_date`) = MONTH (FROM_UNIXTIME( $reqdate ) ) 
                                AND YEAR( `open_date` ) = YEAR( FROM_UNIXTIME( $reqdate ) ) ";
                if( $displayType == "day" )
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
                            FROM `".$tbl_track_e_open."`
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

<?php
include($includePath."/claro_init_footer.inc.php");
?>
