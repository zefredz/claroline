<?php // $Id$
/**
 * CLAROLINE
 *
 * This file display the detailled informations
 * about the use of tool in a course
 * Nothing is displayed if cid is not set and if user is not the courseAdmin
 *
 * @version 1.8 $Revision$
 *
 * @copyright (c) 2001-2007 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @author see 'credits' file
 *
 * @package CLTRACK
 *
 */

require '../inc/claro_init_global.inc.php';

$interbredcrump[]= array ("url"=>"courseReport.php", "name"=> get_lang('Statistics'));

if ( ! claro_is_user_authenticated() || ! claro_is_in_a_course()) claro_disp_auth_form(true);

$nameTools = get_lang('Details');
$langMonthNames = get_locale('langMonthNames');

// main page
include(get_path('incRepositorySys')."/lib/statsUtils.lib.inc.php");


$tbl_cdb_names = claro_sql_get_course_tbl(claro_get_course_db_name_glued(claro_get_current_course_id()));
$tbl_course_tracking_event = $tbl_cdb_names['tracking_event'];

if( claro_is_in_a_course()) //stats for the current course
{
    // to see stats of one course user must be courseAdmin of this course
    $is_allowedToTrack = claro_is_course_manager();
}
else
{
    // cid has to be set here else it probably means that the user has directly access this page by url
    $is_allowedToTrack = false;
}

if( $is_allowedToTrack && get_conf('is_trackingEnabled') )
{
    // toolId is required, go to the tool list if it is missing
    if( empty($_REQUEST['toolId']) )
    {
        claro_redirect("./courseReport.php");
        exit();
    }
    else
    {
    	// FIXME what if tool do not exists anymore ? is not in course tool list ? is deactivated ?
        $toolId = (int)$_REQUEST['toolId'];
    }


      if( !isset($_REQUEST['reqdate']) || $_REQUEST['reqdate'] < 0 || $_REQUEST['reqdate'] > 2149372861 )
        $reqdate = time();  // default value
    else
        $reqdate = (int)$_REQUEST['reqdate'];

    if( isset($_REQUEST['period']) )    $period = $_REQUEST['period'];
    else                                $period = "day"; // default value



    include get_path('incRepositorySys') . '/claro_init_header.inc.php';
    
    $title['mainTitle'] = $nameTools;
    $title['subTitle'] = claro_get_tool_name($toolId);;

    echo claro_html_tool_title( $title )
    .    '<table width="100%" cellpadding="2" cellspacing="0" border="0">'."\n\n"
    /* ------ display ------ */
    // displayed period
    .    '<tr>' . "\n" . '<td>' . "\n"
    ;
    $langDay_of_weekNames = get_locale('langDay_of_weekNames');
    switch($period)
    {
        case "month" :
            echo $langMonthNames['long'][date("n", $reqdate)-1].date(" Y", $reqdate);
            break;
        case "week" :
            $weeklowreqdate = ($reqdate-(86400*date("w" , $reqdate)));
            $weekhighreqdate = ($reqdate+(86400*(6-date("w" , $reqdate)) ));
            echo '<b>'.get_lang('From').'</b> '.date('d ' , $weeklowreqdate).$langMonthNames['long'][date('n', $weeklowreqdate)-1].date(' Y' , $weeklowreqdate)."\n";
            echo ' <b>'.get_lang('to').'</b> '.date('d ' , $weekhighreqdate ).$langMonthNames['long'][date('n', $weekhighreqdate)-1].date(' Y' , $weekhighreqdate)."\n";
            break;
        // default == day
        default :
            $period = "day";
        case "day" :
            echo $langDay_of_weekNames['long'][date('w' , $reqdate)].date(' d ' , $reqdate).$langMonthNames['long'][date('n', $reqdate)-1].date(' Y' , $reqdate)."\n";
            break;
    }

    echo '</td>' . "\n"
    .    '</tr>' . "\n"
    // periode choice
    .    '<tr>' . "\n"
    .    '<td>' . "\n"
    .    '<small>' . "\n"
    .   '[<a href="'.$_SERVER['PHP_SELF'].'?toolId='.$toolId.'&amp;period=month&amp;reqdate='.$reqdate.'">'
    .   ( $period == 'month' ? '<strong>' . get_lang('Month') . '</strong>' : get_lang('Month') )
    .   '</a>]'."\n"
    .   '[<a href="'.$_SERVER['PHP_SELF'].'?toolId='.$toolId.'&amp;period=week&amp;reqdate='.$reqdate.'">'
    .   ( $period == 'week' ? '<strong>' . get_lang('Week') . '</strong>' : get_lang('Week') )
    .   '</a>]'."\n"
    .   '[<a href="' . $_SERVER['PHP_SELF'] . '?toolId=' . $toolId . '&amp;period=day&amp;reqdate='.$reqdate.'">'
    .   ( $period == 'day' ? '<strong>' . get_lang('Day') . '</strong>' : get_lang('Day') )
    .   '</a>]'."\n"
    .   '&nbsp;&nbsp;&nbsp;||&nbsp;&nbsp;&nbsp;'."\n";

    switch($period)
    {
        case "month" :
            // previous and next date must be evaluated
            $previousReqDate = mktime(1,1,1,date("m",$reqdate)-1,1,date("Y",$reqdate));
            $nextReqDate = mktime(1,1,1,date("m",$reqdate)+1,1,date("Y",$reqdate));
            echo '[<a href="'.$_SERVER['PHP_SELF'].'?toolId='.$toolId.'&amp;period=month&amp;reqdate='.$previousReqDate.'">'.get_lang('Previous month').'</a>]'."\n"
                .'[<a href="'.$_SERVER['PHP_SELF'].'?toolId='.$toolId.'&amp;period=month&amp;reqdate='.$nextReqDate.'">'.get_lang('Next month').'</a>]'."\n";
            break;
        case "week" :
            // previous and next date must be evaluated
            $previousReqDate = $reqdate - 7*86400;
            $nextReqDate = $reqdate + 7*86400;
            echo '[<a href="'.$_SERVER['PHP_SELF'].'?toolId='.$toolId.'&amp;period=week&amp;reqdate='.$previousReqDate.'">'.get_lang('Previous week').'</a>]'."\n"
                .'[<a href="'.$_SERVER['PHP_SELF'].'?toolId='.$toolId.'&amp;period=week&amp;reqdate='.$nextReqDate.'">'.get_lang('Next week').'</a>]'."\n";
            break;
        case "day" :
            // previous and next date must be evaluated
            $previousReqDate = $reqdate - 86400;
            $nextReqDate = $reqdate + 86400;
            echo '[<a href="'.$_SERVER['PHP_SELF'].'?toolId='.$toolId.'&amp;period=day&amp;reqdate='.$previousReqDate.'">'.get_lang('Previous day').'</a>]'."\n"
                .'[<a href="'.$_SERVER['PHP_SELF'].'?toolId='.$toolId.'&amp;period=day&amp;reqdate='.$nextReqDate.'">'.get_lang('Next day').'</a>]'."\n";
            break;
    }

    echo '&nbsp;&nbsp;&nbsp;||&nbsp;&nbsp;&nbsp;'."\n"
        .'[<a href="./courseReport.php">'.get_lang('View list of all tools').'</a>]'."\n"
        .'</small>'."\n"
        .'</td>'."\n"
        .'</tr>'."\n"."\n";
    // display information about this period
    switch($period)
    {
        // all days
        case "month" :
            $sql = "SELECT UNIX_TIMESTAMP(`date`)
                    FROM `".$tbl_course_tracking_event."`
                    WHERE `type` = 'tool_access'
                      AND `tool_id` = '". (int) $toolId ."'
                      AND MONTH(`date`) = MONTH(FROM_UNIXTIME($reqdate))
                      AND YEAR(`date`) = YEAR(FROM_UNIXTIME($reqdate))
                    ORDER BY `date` ASC";

            $days_array = daysTab($sql);
            makeHitsTable($days_array,get_lang('Day'));
            break;
        // all days
        case "week" :
            $sql = "SELECT UNIX_TIMESTAMP(`date`)
                    FROM `".$tbl_course_tracking_event."`
                    WHERE `type` = 'tool_access'
                      AND `tool_id` = '". (int)$toolId ."'
                      AND WEEK(`date`) = WEEK(FROM_UNIXTIME($reqdate))
                      AND YEAR(`date`) = YEAR(FROM_UNIXTIME($reqdate))
                    ORDER BY `date` ASC";

            $days_array = daysTab($sql);
            makeHitsTable($days_array,get_lang('Day'));
            break;
        // all hours
        case "day"  :
            $sql = "SELECT UNIX_TIMESTAMP(`date`)
                        FROM `".$tbl_course_tracking_event."`
                        WHERE `type` = 'tool_access'
                          AND `tool_id` = '". $toolId ."'
                          AND DAYOFYEAR(`date`) = DAYOFYEAR(FROM_UNIXTIME($reqdate))
                          AND YEAR(`date`) = YEAR(FROM_UNIXTIME($reqdate))
                        ORDER BY `date` ASC";

            $hours_array = hoursTab($sql,$reqdate);
            makeHitsTable($hours_array,get_lang('Hour'));
            break;
    }
}
else // not allowed to track
{
    if(!get_conf('is_trackingEnabled'))
    {
        echo get_lang('Tracking has been disabled by system administrator.');
    }
    else
    {
        echo get_lang('Not allowed');
    }
}


echo "\n"
.    '</table>' . "\n\n"
;
// footer
include get_path('incRepositorySys') . '/claro_init_footer.inc.php';
?>