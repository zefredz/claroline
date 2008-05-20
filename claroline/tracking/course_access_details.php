<?php // $Id$
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.6.*
      +----------------------------------------------------------------------+
      | Copyright (c) 2001-2007 Universite catholique de Louvain (UCL)
      +----------------------------------------------------------------------+
      |   Authors : see CREDITS.txt
      +----------------------------------------------------------------------+
 */

require '../inc/claro_init_global.inc.php';

if ( ! claro_is_in_a_course() || ! claro_is_course_allowed() ) claro_disp_auth_form(true);
if ( ! claro_is_course_manager() ) claro_die(get_lang('Not allowed'));

include_once get_path('incRepositorySys') . '/lib/statsUtils.lib.inc.php';

$interbredcrump[]= array ('url' => 'courseReport.php', 'name' => get_lang('Statistics'));

$nameTools = get_lang('Traffic Details');

$tbl_cdb_names = claro_sql_get_course_tbl(claro_get_course_db_name_glued(claro_get_current_course_id()));
$tbl_course_tracking_event = $tbl_cdb_names['tracking_event'];


include get_path('incRepositorySys') . '/claro_init_header.inc.php';


echo claro_html_tool_title(
    array(
    'mainTitle'=>$nameTools,
    )
);

if ( get_conf('is_trackingEnabled') )
{
    if( !isset($_REQUEST['reqdate']) || $_REQUEST['reqdate'] < 0 || $_REQUEST['reqdate'] > 2149372861 )
        $reqdate = time();  // default value
    else
        $reqdate = (int)$_REQUEST['reqdate'];

    if( isset($_REQUEST['period']) )    $period = $_REQUEST['period'];
    else                                $period = 'day'; // default value

    $displayTypeList = array ('month','day','hour');

    if ( isset($_REQUEST['displayType']) && in_array($_REQUEST['displayType'],$displayTypeList) )
    {
        $displayType = $_REQUEST['displayType'];
    }
    else
    {
        $displayType = ''; // default value
    }

    //** dislayed period
    echo '<p><strong>';
    
    switch($period)
    {
        case 'year' :
            echo date(' Y', $reqdate);
            break;
        case 'month' :
            echo claro_html_localised_date('%B %Y',$reqdate);
          break;
        // default == day
        default :
            $period = 'day';
        case 'day' :
            echo claro_html_localised_date('%A %d %B %Y',$reqdate);
          break;
    }
    echo '</strong></p>'."\n\n";
    
    //** menu
    echo '<p><small>'."\n";
    echo get_lang('Period').' : ' 
    .   '[<a href="'.$_SERVER['PHP_SELF'].'?period=year&reqdate='.$reqdate.'&displayType=month">'
    .   ( $period == 'year' ? '<strong>' . get_lang('Year') . '</strong>' : get_lang('Year') )
    .    '</a>]'."\n"
    .   '[<a href="'.$_SERVER['PHP_SELF'].'?period=month&reqdate='.$reqdate.'&displayType=day">'
    .   ( $period == 'month' ? '<strong>' . get_lang('Month') . '</strong>' : get_lang('Month') )
    .   '</a>]'."\n"
    .   '[<a href="'.$_SERVER['PHP_SELF'].'?period=day&reqdate='.$reqdate.'">'
    .   ( $period == 'day' ? '<strong>' . get_lang('Day') . '</strong>' : get_lang('Day') )
    .   '</a>]'."\n"
    .   '&nbsp;&nbsp;&nbsp;||&nbsp;&nbsp;&nbsp;'."\n"
    .   get_lang('View by').' : ';

    switch($period)
    {
        case 'year' :
                //-- if period is "year" display can be by month, day or hour
                echo '  [<a href="'.$_SERVER['PHP_SELF'].'?period='.$period.'&reqdate='.$reqdate.'&displayType=month">'
                .   ( $displayType == 'month' ? '<strong>' . get_lang('Month') . '</strong>' : get_lang('Month') )
                .   '</a>]'."\n";
        case 'month' :
                //-- if period is "month" display can be by day or hour
                echo '  [<a href="'.$_SERVER['PHP_SELF'].'?period='.$period.'&reqdate='.$reqdate.'&displayType=day">'
                .   ( $displayType == 'day' ? '<strong>' . get_lang('Day') . '</strong>' : get_lang('Day') )
                .   '</a>]'."\n";
        case 'day' :
                //-- if period is "day" display can only be by hour
                echo '  [<a href="'.$_SERVER['PHP_SELF'].'?period='.$period.'&reqdate='.$reqdate.'&displayType=hour">'
                .   ( $displayType == 'hour' ? '<strong>' . get_lang('Hour') . '</strong>' : get_lang('Hour') )
                .   '</a>]'."\n";
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
    echo '</small></p>' . "\n\n";
    //**
    // display information about this period
    switch($period)
    {
        // all days
        case "year" :
            $sql = "SELECT UNIX_TIMESTAMP( `date` )
                        FROM `".$tbl_course_tracking_event."`
                        WHERE `type` = 'course_access'
                        AND YEAR( `date` ) = YEAR( FROM_UNIXTIME( $reqdate ) )";
            if($displayType == "month")
            {
                $sql .= "ORDER BY UNIX_TIMESTAMP( `date`)";
                $month_array = monthTab($sql);
                makeHitsTable($month_array,get_lang('Month'));
            }
            elseif($displayType == "day")
            {
                $sql .= "ORDER BY DAYOFYEAR( `date`)";
                $days_array = daysTab($sql);
                makeHitsTable($days_array,get_lang('Day'));
            }
            else // by hours by default
            {
                $sql .= "ORDER BY HOUR( `date`)";
                $hours_array = hoursTab($sql);
                makeHitsTable($hours_array,get_lang('Hour'));
            }
            break;
        // all days
        case "month" :
            $sql = "SELECT UNIX_TIMESTAMP( `date` )
                        FROM `".$tbl_course_tracking_event."`
                        WHERE `type` = 'course_access'
                        AND MONTH(`date`) = MONTH (FROM_UNIXTIME( $reqdate ) )
                        AND YEAR( `date` ) = YEAR( FROM_UNIXTIME( $reqdate ) )";
            if($displayType == "day")
            {
                $sql .= "ORDER BY DAYOFYEAR( `date`)";
                $days_array = daysTab($sql);
                makeHitsTable($days_array,get_lang('Day'));
            }
            else // by hours by default
            {
                $sql .= "ORDER BY HOUR( `date`)";
                $hours_array = hoursTab($sql);
                makeHitsTable($hours_array,get_lang('Hour'));
            }
            break;
        // all hours
        case "day"  :
            $sql = "SELECT UNIX_TIMESTAMP( `date` )
                        FROM `".$tbl_course_tracking_event."`
                        WHERE `type` = 'course_access'
                        AND DAYOFMONTH(`date`) = DAYOFMONTH(FROM_UNIXTIME( $reqdate ) )
                        AND MONTH(`date`) = MONTH (FROM_UNIXTIME( $reqdate ) )
                        AND YEAR( `date` ) = YEAR( FROM_UNIXTIME( $reqdate ) )
                        ORDER BY HOUR( `date` )";
            $hours_array = hoursTab($sql,$reqdate);
            makeHitsTable($hours_array,get_lang('Hour'));
            break;
    }
}
else // tracking not enable
{
    echo get_lang('Tracking has been disabled by system administrator.');
}

include get_path('incRepositorySys') . '/claro_init_footer.inc.php';
?>