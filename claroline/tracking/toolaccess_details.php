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

$interbredcrump[]= array ("url"=>"courseLog.php", "name"=> get_lang('Statistics'));

if ( !$_uid || !$_cid) claro_disp_auth_form(true);

$nameTools = get_lang('Details');

// main page
include($includePath."/lib/statsUtils.lib.inc.php");


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

if( $is_allowedToTrack && get_conf('is_trackingEnabled') )
{
    // toolId is required, go to the tool list if it is missing
    if( empty($_REQUEST['toolId']) ) 
    {
        header("Location: ./courseLog.php?view=0010000");
        exit();
    }
    else
    {
        $toolId = (int)$_REQUEST['toolId'];
    }
    

      if( !isset($_REQUEST['reqdate']) || $_REQUEST['reqdate'] < 0 || $_REQUEST['reqdate'] > 2149372861 )
        $reqdate = time();  // default value
    else
        $reqdate = (int)$_REQUEST['reqdate'];

    if( isset($_REQUEST['period']) )    $period = $_REQUEST['period'];
    else                                $period = "day"; // default value


    $sql = "SELECT `access_tlabel` as `label`
            FROM `".$TABLETRACK_ACCESS."`
            WHERE `access_tid` = ". (int)$toolId ."
            GROUP BY `access_tid`" ;

    $result = claro_sql_query_fetch_all($sql);
    
    include($includePath."/claro_init_header.inc.php");
    $title['mainTitle'] = $nameTools;
    
    if( isset($result[0]['label']) )
        if( isset($toolNameList[$result[0]['label']]) )
            $title['subTitle'] = $toolNameList[$result[0]['label']];

    echo claro_html_tool_title( $title );

    echo '<table width="100%" cellpadding="2" cellspacing="0" border="0">'."\n\n";


    /* ------ display ------ */
    // displayed period
    echo '<tr>'."\n".'<td>'."\n";
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

    echo '</td>'."\n".'</tr>'."\n";
    // periode choice
    echo '<tr>'."\n"
        .'<td>'."\n"
        .'<small>'."\n"
        .'[<a href="'.$_SERVER['PHP_SELF'].'?toolId='.$toolId.'&amp;period=day&amp;reqdate='.$reqdate.'">'.get_lang('Day').'</a>]'."\n"
        .'[<a href="'.$_SERVER['PHP_SELF'].'?toolId='.$toolId.'&amp;period=week&amp;reqdate='.$reqdate.'">'.get_lang('Week').'</a>]'."\n"
        .'[<a href="'.$_SERVER['PHP_SELF'].'?toolId='.$toolId.'&amp;period=month&amp;reqdate='.$reqdate.'">'.get_lang('Month').'</a>]'."\n"
        .'&nbsp;&nbsp;&nbsp;||&nbsp;&nbsp;&nbsp;'."\n";

    switch($period)
    {
        case "month" :
            // previous and next date must be evaluated
            // 30 days should be a good approximation
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
        .'[<a href="./courseLog.php?view=0010000">'.get_lang('View list of all tools').'</a>]'."\n"
        .'</small>'."\n"
        .'</td>'."\n"
        .'</tr>'."\n"."\n";
    // display information about this period
    switch($period)
    {
        // all days
        case "month" :
            $sql = "SELECT UNIX_TIMESTAMP(`access_date`)
                    FROM `$TABLETRACK_ACCESS`
                    WHERE `access_tid` = '". (int)$toolId ."'
                        AND MONTH(`access_date`) = MONTH(FROM_UNIXTIME($reqdate))
                        AND YEAR(`access_date`) = YEAR(FROM_UNIXTIME($reqdate))
                        ORDER BY `access_date` ASC";
            
            $days_array = daysTab($sql);
            makeHitsTable($days_array,get_lang('Day'));
            break;
        // all days
        case "week" :
            $sql = "SELECT UNIX_TIMESTAMP(`access_date`)
                    FROM `$TABLETRACK_ACCESS`
                    WHERE `access_tid` = '". (int)$toolId ."'
                        AND WEEK(`access_date`) = WEEK(FROM_UNIXTIME($reqdate))
                        AND YEAR(`access_date`) = YEAR(FROM_UNIXTIME($reqdate))
                        ORDER BY `access_date` ASC";

            $days_array = daysTab($sql);
            makeHitsTable($days_array,get_lang('Day'));
            break;
        // all hours
        case "day"  :
            $sql = "SELECT UNIX_TIMESTAMP(`access_date`)
                        FROM `$TABLETRACK_ACCESS`
                        WHERE `access_tid` = '". $toolId ."'
                            AND DAYOFYEAR(`access_date`) = DAYOFYEAR(FROM_UNIXTIME($reqdate))
                            AND YEAR(`access_date`) = YEAR(FROM_UNIXTIME($reqdate))
                        ORDER BY `access_date` ASC";
            
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
    
    
echo "\n".'</table>'."\n\n";
// footer
include($includePath."/claro_init_footer.inc.php");
?>
