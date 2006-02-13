<?php // $Id$
/**
 * Claroline
 *
 * @version 1.8 $Revision$
 *
 * @copyright (c) 2001-2006 Universite catholique de Louvain (UCL)
 */

require '../inc/claro_init_global.inc.php';

// Security check
if ( ! $_uid ) claro_disp_auth_form();
if ( ! $is_platformAdmin ) claro_die(get_lang('Not allowed'));

require_once $includePath . '/lib/statsUtils.lib.inc.php';
$tbl_mdb_names    = claro_sql_get_main_tbl();
$tbl_track_e_open = $tbl_mdb_names['track_e_open'];

$is_allowedToTrack = $is_platformAdmin;

$interbredcrump[]= array ('url' => "index.php", 'name' => get_lang('Administration'));
$interbredcrump[]= array ('url' => "campusLog.php", 'name' => get_lang('Platform Statistics'));

$nameTools = get_lang('Traffic Details');

include $includePath . '/claro_init_header.inc.php';
echo claro_disp_tool_title(array('mainTitle'=>$nameTools))
.    '<table width="100%" cellpadding="2" cellspacing="3" border="0">'
;
if( $is_allowedToTrack && $is_trackingEnabled)
{
    if( !isset($_REQUEST['reqdate']) || $_REQUEST['reqdate'] < 0 || $_REQUEST['reqdate'] > 2149372861 )
    {
        $reqdate = time();  // default value
    }
    else $reqdate = (int)$_REQUEST['reqdate'];

    if( isset($_REQUEST['period']) ) $period = $_REQUEST['period'];
    else                             $period = 'day'; // default value

    if( isset($_REQUEST['displayType']) ) $displayType = $_REQUEST['displayType'];
    else                                  $displayType = ''; // default value

    // dislayed period
    echo '<tr><td><b>';

    switch($period)
    {
        case 'year' :
        {
            echo date('Y', $reqdate);
        }   break;
        case 'month' :
        {
            echo claro_disp_localised_date('%B %Y',$reqdate);
        }   break;
        default :
        {
            $period = 'day'; // if $period has no correct value
        }
        case 'day' :
        {
            echo claro_disp_localised_date('%A %d %B %Y',$reqdate);
        }   break;
    }

    echo '</b></tr></td>' . "\n"
    .    '<tr>' . "\n"
    .    '<td>' . "\n"
    .    '<small>' . "\n"
    .    get_lang('Period')
    .    ' : ' . "\n"
    .    '[<a href="' . $_SERVER['PHP_SELF'] . '?period=year&amp;reqdate=' . $reqdate . '" >' . get_lang('Year') . '</a>]' . "\n"
    .    '[<a href="' . $_SERVER['PHP_SELF'] . '?period=month&amp;reqdate=' . $reqdate . '" >' . get_lang('Month') . '</a>]' . "\n"
    .    '[<a href="' . $_SERVER['PHP_SELF'] . '?period=day&amp;reqdate=' . $reqdate . ' ">' . get_lang('Day') . '</a>]' . "\n"
    .    '&nbsp;&nbsp;&nbsp;||&nbsp;&nbsp;&nbsp;' . "\n"
    .    get_lang('View by') . "\n"
    .    ' :' . "\n"
    ;
    switch($period)
    {
        case 'year' :
        {
        //-- if period is "year" display can be by month, day or hour
        echo '[<a href="' . $_SERVER['PHP_SELF'] . '?period=' . $period . '&amp;reqdate=' . $reqdate . '&amp;displayType=month" >' . get_lang('Month') . '</a>]';
        }
        case 'month' :
        {
            //-- if period is "month" display can be by day or hour
            echo '[<a href="' . $_SERVER['PHP_SELF'] . '?period=' . $period . '&amp;reqdate=' . $reqdate . '&amp;displayType=day" >' . get_lang('Day') . '</a>]';
        }
        case 'day' :
        {
            //-- if period is "day" display can only be by hour
            echo '[<a href="' . $_SERVER['PHP_SELF'] . '?period=' . $period . '&amp;reqdate=' . $reqdate . '&amp;displayType=hour" >' . get_lang('Hour') . '</a>]';
        } break;
    }

    echo '&nbsp;&nbsp;&nbsp;||&nbsp;&nbsp;&nbsp;';

    switch($period)
    {
        case 'year' :
        {
            // previous and next date must be evaluated
            // 30 days should be a good approximation
            $previousReqDate = mktime(1,1,1,1,1,date("Y",$reqdate)-1);
            $nextReqDate = mktime(1,1,1,1,1,date("Y",$reqdate)+1);
            echo '[<a href="' . $_SERVER['PHP_SELF'].  '?period=' . $period . '&amp;reqdate=' . $previousReqDate . '&amp;displayType=' . $displayType . '" >' . get_lang('Previous Year') . '</a>]'
            .    '[<a href="' . $_SERVER['PHP_SELF'] . '?period=' . $period . '&amp;reqdate=' . $nextReqDate . '&amp;displayType=' . $displayType . '" >' . get_lang('Next Year') . '</a>]'
            ;
        }   break;
        case 'month' :
        {
        // previous and next date must be evaluated
        // 30 days should be a good approximation
        $previousReqDate = mktime(1,1,1,date("m",$reqdate)-1,1,date("Y",$reqdate));
        $nextReqDate = mktime(1,1,1,date("m",$reqdate)+1,1,date("Y",$reqdate));
        echo   "
                    [<a href='".$_SERVER['PHP_SELF']."?period=".$period."&reqdate=".$previousReqDate."&displayType=".$displayType."' >".get_lang('Previous Month')."</a>]
                    [<a href='".$_SERVER['PHP_SELF']."?period=".$period."&reqdate=".$nextReqDate."&displayType=".$displayType."' >".get_lang('Next Month')."</a>]
                ";
        }   break;
        case 'day' :
        {
        // previous and next date must be evaluated
        $previousReqDate = $reqdate - 86400;
        $nextReqDate = $reqdate + 86400;
        echo   "
                    [<a href='".$_SERVER['PHP_SELF']."?period=".$period."&reqdate=".$previousReqDate."&displayType=" . $displayType . "' >" . get_lang('Previous Day') . "</a>]
                    [<a href='".$_SERVER['PHP_SELF']."?period=".$period."&reqdate=".$nextReqDate."&displayType=" . $displayType . "' >" . get_lang('Next Day') . "</a>]
                   ";
        }   break;
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
            makeHitsTable($month_array,get_lang('Month'));
        }
        elseif( $displayType == "day" )
        {
            $sql .= "ORDER BY DAYOFYEAR( `open_date`)";
            $days_array = daysTab($sql);
            makeHitsTable($days_array,get_lang('Day'));
        }
        else // by hours by default
        {
            $sql .= "ORDER BY HOUR( `open_date`)";
            $hours_array = hoursTab($sql);
            makeHitsTable($hours_array,get_lang('Hour'));
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
            makeHitsTable($days_array,get_lang('Day'));
        }
        else // by hours by default
        {
            $sql .= "ORDER BY HOUR( `open_date`)";
            $hours_array = hoursTab($sql);
            makeHitsTable($hours_array,get_lang('Hour'));
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
        makeHitsTable($hours_array,get_lang('Hour'));
        break;
    }
}
else // not allowed to track
{
    if(!$is_trackingEnabled)
    {
        echo get_lang('Tracking has been disabled by system administrator.');
    }
    else
    {
        echo get_lang('Not allowed');
    }
}


echo '</table>';
include $includePath . '/claro_init_footer.inc.php';
?>