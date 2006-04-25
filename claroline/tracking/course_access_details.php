<?php // $Id$
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.6.*
      +----------------------------------------------------------------------+
      | Copyright (c) 2001-2006 Universite catholique de Louvain (UCL)
      +----------------------------------------------------------------------+
      |   Authors : see CREDITS.txt
      +----------------------------------------------------------------------+
 */

require '../inc/claro_init_global.inc.php';

if ( ! $_cid && ! $is_courseAllowed ) claro_disp_auth_form(true);
if ( ! $is_courseAdmin ) claro_die(get_lang('Not allowed'));

include_once $includePath . '/lib/statsUtils.lib.inc.php';

$interbredcrump[]= array ('url' => 'courseLog.php', 'name' => get_lang('Statistics'));

$nameTools = get_lang('Traffic Details');

$tbl_cdb_names = claro_sql_get_course_tbl();
$TABLETRACK_ACCESS = $tbl_cdb_names['track_e_access'];



include $includePath . '/claro_init_header.inc.php';


echo claro_html_tool_title(
    array(
    'mainTitle'=>$nameTools,
    )
);

?>
<table width="100%" cellpadding="2" cellspacing="3" border="0">
<?php

if ( $is_trackingEnabled )
{
    if( !isset($_REQUEST['reqdate']) || $_REQUEST['reqdate'] < 0 || $_REQUEST['reqdate'] > 2149372861 )
        $reqdate = time();  // default value
    else
        $reqdate = (int)$_REQUEST['reqdate'];

    if( isset($_REQUEST['period']) )    $period = $_REQUEST['period'];
    else                                $period = 'day'; // default value

    if( isset($_REQUEST['displayType']) )   $displayType = $_REQUEST['displayType'];
    else                                    $displayType = ''; // default value

    //** dislayed period
    echo '<tr>' . "\n"
    .    '<td>'
    .    '<b>'
    ;
    switch($period)
    {
        case 'year' :
            echo date(' Y', $reqdate);
            break;
        case 'month' :
            echo claro_disp_localised_date('%B %Y',$reqdate);
          break;
        // default == day
        default :
            $period = 'day';
        case 'day' :
            echo claro_disp_localised_date('%A %d %B %Y',$reqdate);
          break;
    }
echo '</b></td>'."\n".'</tr>'."\n\n";
    //** menu
    echo '<tr>'."\n".'<td><small>'."\n";
    echo get_lang('Period').' : [<a href="'.$_SERVER['PHP_SELF'].'?period=year&reqdate='.$reqdate.'&displayType=month">'.get_lang('Year').'</a>]'."\n"
        .'[<a href="'.$_SERVER['PHP_SELF'].'?period=month&reqdate='.$reqdate.'&displayType=day">'.get_lang('Month').'</a>]'."\n"
        .'[<a href="'.$_SERVER['PHP_SELF'].'?period=day&reqdate='.$reqdate.'">'.get_lang('Day').'</a>]'."\n"
        .'&nbsp;&nbsp;&nbsp;||&nbsp;&nbsp;&nbsp;'."\n"
        .get_lang('View by').' : ';

    switch($period)
    {
        case 'year' :
                //-- if period is "year" display can be by month, day or hour
                echo '  [<a href="'.$_SERVER['PHP_SELF'].'?period='.$period.'&reqdate='.$reqdate.'&displayType=month">'.get_lang('Month').'</a>]'."\n";
        case 'month' :
                //-- if period is "month" display can be by day or hour
                echo '  [<a href="'.$_SERVER['PHP_SELF'].'?period='.$period.'&reqdate='.$reqdate.'&displayType=day">'.get_lang('Day').'</a>]'."\n";
        case 'day' :
                //-- if period is "day" display can only be by hour
                echo '  [<a href="'.$_SERVER['PHP_SELF'].'?period='.$period.'&reqdate='.$reqdate.'&displayType=hour">'.get_lang('Hour').'</a>]'."\n";
                break;
    }

    echo '&nbsp;&nbsp;&nbsp;||&nbsp;&nbsp;&nbsp;'."\n";

    switch($period)
    {
        case 'year' :
            // previous and next date must be evaluated
            // 30 days should be a good approximation
            $previousReqDate = mktime(1,1,1,1,1,date('Y',$reqdate)-1);
            $nextReqDate = mktime(1,1,1,1,1,date('Y',$reqdate)+1);
            echo '[<a href="'.$_SERVER['PHP_SELF'].'?period='.$period.'&reqdate='.$previousReqDate.'&displayType='.$displayType.'">'.get_lang('Previous year').'</a>]'."\n"
                .'[<a href="'.$_SERVER['PHP_SELF'].'?period='.$period.'&reqdate='.$nextReqDate.'&displayType='.$displayType.'">'.get_lang('Next year').'</a>]'."\n";
            break;
        case 'month' :
            // previous and next date must be evaluated
            // 30 days should be a good approximation
            $previousReqDate = mktime(1,1,1,date("m",$reqdate)-1,1,date("Y",$reqdate));
            $nextReqDate = mktime(1,1,1,date("m",$reqdate)+1,1,date("Y",$reqdate));
            echo '[<a href="'.$_SERVER['PHP_SELF'].'?period='.$period.'&reqdate='.$previousReqDate.'&displayType='.$displayType.'">'.get_lang('Previous month').'</a>]'."\n"
                .'[<a href="'.$_SERVER['PHP_SELF'].'?period='.$period.'&reqdate='.$nextReqDate.'&displayType='.$displayType.'">'.get_lang('Next month').'</a>]'."\n";
            break;
        case 'day' :
            // previous and next date must be evaluated
            $previousReqDate = $reqdate - 86400;
            $nextReqDate = $reqdate + 86400;
            echo '[<a href="'.$_SERVER['PHP_SELF'].'?period='.$period.'&reqdate='.$previousReqDate.'&displayType='.$displayType.'">'.get_lang('Previous day').'</a>]'."\n"
                .'[<a href="'.$_SERVER['PHP_SELF'].'?period='.$period.'&reqdate='.$nextReqDate.'&displayType='.$displayType.'">'.get_lang('Next day').'</a>]'."\n";
            break;
    }
    echo '</small>'."\n".'</td>'."\n".'</tr>'."\n\n";
    //**
    // display information about this period
    switch($period)
    {
        // all days
        case "year" :
            $sql = "SELECT UNIX_TIMESTAMP( `access_date` )
                        FROM `$TABLETRACK_ACCESS`
                        WHERE YEAR( `access_date` ) = YEAR( FROM_UNIXTIME( $reqdate ) )
                        AND `access_tid` IS NULL ";
            if($displayType == "month")
            {
                $sql .= "ORDER BY UNIX_TIMESTAMP( `access_date`)";
                $month_array = monthTab($sql);
                makeHitsTable($month_array,get_lang('Month'));
            }
            elseif($displayType == "day")
            {
                $sql .= "ORDER BY DAYOFYEAR( `access_date`)";
                $days_array = daysTab($sql);
                makeHitsTable($days_array,get_lang('Day'));
            }
            else // by hours by default
            {
                $sql .= "ORDER BY HOUR( `access_date`)";
                $hours_array = hoursTab($sql);
                makeHitsTable($hours_array,get_lang('Hour'));
            }
            break;
        // all days
        case "month" :
            $sql = "SELECT UNIX_TIMESTAMP( `access_date` )
                        FROM `$TABLETRACK_ACCESS`
                        WHERE MONTH(`access_date`) = MONTH (FROM_UNIXTIME( $reqdate ) )
                        AND YEAR( `access_date` ) = YEAR( FROM_UNIXTIME( $reqdate ) )
                        AND `access_tid` IS NULL ";
            if($displayType == "day")
            {
                $sql .= "ORDER BY DAYOFYEAR( `access_date`)";
                $days_array = daysTab($sql);
                makeHitsTable($days_array,get_lang('Day'));
            }
            else // by hours by default
            {
                $sql .= "ORDER BY HOUR( `access_date`)";
                $hours_array = hoursTab($sql);
                makeHitsTable($hours_array,get_lang('Hour'));
            }
            break;
        // all hours
        case "day"  :
            $sql = "SELECT UNIX_TIMESTAMP( `access_date` )
                        FROM `$TABLETRACK_ACCESS`
                        WHERE DAYOFMONTH(`access_date`) = DAYOFMONTH(FROM_UNIXTIME( $reqdate ) )
                        AND MONTH(`access_date`) = MONTH (FROM_UNIXTIME( $reqdate ) )
                        AND YEAR( `access_date` ) = YEAR( FROM_UNIXTIME( $reqdate ) )
                        AND `access_tid` IS NULL
                        ORDER BY HOUR( `access_date` )";
            $hours_array = hoursTab($sql,$reqdate);
            makeHitsTable($hours_array,get_lang('Hour'));
            break;
    }
}
else // tracking not enable
{
    echo get_lang('Tracking has been disabled by system administrator.');
}

?>
</table>

<?php
include($includePath."/claro_init_footer.inc.php");
?>
