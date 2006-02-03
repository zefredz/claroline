<?php  // $Id$
/**
 * CLAROLINE
 *
 * @version 1.8 $Revision$
 *
 * @copyright (c) 2001-2006 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @see http://www.claroline.net/wiki/CLCRS/
 *
 * @package CLSTAT
 *
 * @author Claro Team <cvs@claroline.net>
 *
 * @todo to factorise sql
 * @todo to split work and output
 *
 */

define('DISP_TRACKING_DISABLED' ,__LINE__);
define('DISP_NOT_ALLOWED'       ,__LINE__);
define('DISP_TRACKING_RESULT'   ,__LINE__);

require '../inc/claro_init_global.inc.php';
require_once $includePath . '/lib/statsUtils.lib.inc.php';

// uInfo is required, back to user list if there is none
if( empty($_REQUEST['uInfo']) )
{
    header('Location: ../user/user.php');
    exit();
}
else $uInfo = (int) $_REQUEST['uInfo'];

if( !empty($_REQUEST['reqdate']) ) $reqdate = (int)$_REQUEST['reqdate'];
else                               $reqdate = time();

$tbl_mdb_names = claro_sql_get_main_tbl();
$tbl_rel_course_user         = $tbl_mdb_names['rel_course_user'  ];
$tbl_user                    = $tbl_mdb_names['user'             ];
$tbl_track_e_login           = $tbl_mdb_names['track_e_login'];

$tbl_cdb_names = claro_sql_get_course_tbl();
$tbl_group_rel_team_user     = $tbl_cdb_names['group_rel_team_user'    ];
$tbl_track_e_downloads       = $tbl_cdb_names['track_e_downloads'      ];
$tbl_track_e_access          = $tbl_cdb_names['track_e_access'         ];

$toolNameList = claro_get_tool_name_list();
$is_allowedToTrack = $is_groupTutor; // allowed to track only user of one group

if ( isset($_uid) ) $is_allowedToTrack = $is_allowedToTrack || ($uInfo == $_uid);

$is_allowedToTrackEverybodyInCourse = $is_courseAdmin; // allowed to track all student in course

// check if uid is tutor of this group

$interbredcrump[]= array ('url'=>'../user/user.php', 'name'=> get_lang('Users'));
$interbredcrump[]= array ('url'=>'../tracking/userLog.php?uInfo=' . $uInfo, 'name'=> get_lang('StatsOfUser'));
$_SERVER['QUERY_STRING'] = 'uInfo=' . $uInfo . '&amp;reqdate=' . $reqdate;

$nameTools = get_lang('Statistics') . ' : ' . get_lang('LoginsAndAccessTools');


if(!get_conf('is_trackingEnabled',false)) $display = DISP_TRACKING_DISABLED;
elseif( ($is_allowedToTrackEverybodyInCourse || $is_allowedToTrack ))
{
    if( $is_allowedToTrackEverybodyInCourse  || ($uInfo == $_uid)  )
    {
        $sql = "SELECT `u`.`prenom` AS `firstname`,
                       `u`.`nom`    AS `lastname`,
                       `u`.`email`  AS `email`
                FROM `" . $tbl_rel_course_user . "` AS cu
                   , `" . $tbl_user            . "` AS u
                    WHERE `cu`.`user_id` = `u`.`user_id`
                        AND `cu`.`code_cours` = '" . $_cid . "'
                        AND `u`.`user_id` = " . (int) $uInfo;
    }
    else // user is a tutor
    {
        $sql = "SELECT `u`.`prenom` AS `firstname`,
                       `u`.`nom`    AS `lastname`,
                       `u`.`email`  AS `email`
                    FROM `" . $tbl_group_rel_team_user . "` AS gu ,
                         `" . $tbl_user                ."`  AS u
                    WHERE `gu`.`user` = `u`.`user_id`
                      AND `gu`.`team` = " . (int) $_gid . "
                      AND `u`.`user_id` = " . (int) $uInfo ;
    }
    $userDetails = claro_sql_query_get_single_row($sql);
    if( is_array($userDetails) && !empty($userDetails) )
    {
        $period = empty($_REQUEST['period'])?'month':$_REQUEST['period']  ;
        if( $period == 'week' )
        {
            $previousReqDate = $reqdate - 7 * 86400; // 86400=24*60*60
            $nextReqDate     = $reqdate + 7 * 86400;

            $sqlloginDates = "SELECT `login_date`
                    FROM `" . $tbl_track_e_login . "`
                    WHERE `login_user_id` = " . (int) $uInfo . "
                      AND WEEK(`login_date`) = WEEK( FROM_UNIXTIME('" . $reqdate . "') )
                      AND YEAR(`login_date`) = YEAR( FROM_UNIXTIME(" . $reqdate . ") )
                    ORDER BY `login_date` ASC ";
            $weeklowreqdate = ($reqdate-(86400*date("w" , $reqdate)));
            $weekhighreqdate = ($reqdate+(86400*(6-date("w" , $reqdate)) ));
            $displayedDate = get_lang('From')." ".date("d " , $weeklowreqdate).$langMonthNames['long'][date("n", $weeklowreqdate)-1].date(" Y" , $weeklowreqdate)
            ." ".get_lang('ToDate')." ".date("d " , $weekhighreqdate ). $langMonthNames['long'][date("n", $weekhighreqdate)-1].date(" Y" , $weekhighreqdate);
        }
        else // month
        {
            // previous and next date must be evaluated
            // 30 days should be a good approximation
            $previousReqDate = mktime(1,1,1,date('m',$reqdate)-1,1,date('Y',$reqdate));
            $nextReqDate = mktime(1,1,1,date('m',$reqdate)+1,1,date('Y',$reqdate));
            $sqlloginDates = "SELECT `login_date`
                              FROM `".$tbl_track_e_login."`
                              WHERE `login_user_id` = ". (int)$uInfo ."
                                  AND MONTH(`login_date`) = MONTH( FROM_UNIXTIME('" . $reqdate . "') )
                                  AND YEAR(`login_date`) = YEAR( FROM_UNIXTIME(" . $reqdate . ") )
                              ORDER BY `login_date` ASC ";
            $displayedDate = $langMonthNames['long'][date("n", $reqdate)-1].date(" Y", $reqdate);
        }
        $loginDates = claro_sql_query_fetch_all($sqlloginDates);

    }
    $display = DISP_TRACKING_RESULT;
}
else $display = DISP_NOT_ALLOWED;



/**
 * DISPLAY
 */

include $includePath . '/claro_init_header.inc.php';
echo claro_disp_tool_title($nameTools);

switch ($display)
{
    case DISP_TRACKING_RESULT :
    {

        if( is_array($userDetails) && !empty($userDetails) )
        {
            if( empty($userDetails['email']) ) $userDetails['email'] = get_lang('NoEmail');
            echo get_lang('User').' : <br />'
            .   '<ul>' . "\n"
            .   '<li>' . get_lang('LastName')  . ' : ' . $userDetails['lastname']  . '</li>' . "\n"
            .   '<li>' . get_lang('FirstName') . ' : ' . $userDetails['firstname'] . '</li>' . "\n"
            .   '<li>' . get_lang('Email')     . ' : ' . $userDetails['email']     . '</li>' . "\n"
            .   '</ul>' . "\n"

            /******* MENU ********/
            .   '<small>'."\n"
            .   '[<a href="userLog.php?uInfo=' . $uInfo . '">' . get_lang('Back') . '</a>]' . "\n"
            .   '&nbsp;&nbsp;&nbsp;||&nbsp;&nbsp;&nbsp;'."\n"
            .   '[<a href="' . $_SERVER['PHP_SELF'].'?uInfo=' . $uInfo . '&amp;period=week&amp;reqdate='.$reqdate.'">'.get_lang('PeriodWeek').'</a>]'."\n"
            .   '[<a href="' . $_SERVER['PHP_SELF'].'?uInfo=' . $uInfo . '&amp;period=month&amp;reqdate='.$reqdate.'">'.get_lang('PeriodMonth').'</a>]'."\n"
            .   '&nbsp;&nbsp;&nbsp;||&nbsp;&nbsp;&nbsp;'."\n"
            ;

            if( $period == 'week' )
            {
                // previous and next date must be evaluated
                echo '[<a href="' . $_SERVER['PHP_SELF'] . '?uInfo=' . $uInfo . '&amp;period=week&amp;reqdate=' . $previousReqDate . '">' . get_lang('PreviousWeek') . '</a>]' . "\n"
                .    '[<a href="' . $_SERVER['PHP_SELF'] . '?uInfo=' . $uInfo . '&amp;period=week&amp;reqdate=' . $nextReqDate . '">' . get_lang('NextWeek') . '</a>]' . "\n"
                ;
            }
            else // month
            {
                echo '[<a href="' . $_SERVER['PHP_SELF'] . '?uInfo=' . $uInfo . '&amp;period=month&amp;reqdate=' . $previousReqDate . '">' . get_lang('PreviousMonth') . '</a>]' . "\n"
                .    '[<a href="' . $_SERVER['PHP_SELF'] . '?uInfo=' . $uInfo . '&amp;period=month&amp;reqdate=' . $nextReqDate . '">' . get_lang('NextMonth') . '</a>]' . "\n"
                ;
            }

            echo '</small>' . "\n\n";
            /******* END OF MENU ********/


            /*** display of the displayed period  ***/
            echo '<table class="claroTable" width="100%" cellpadding="4" cellspacing="1">';
            echo '<tr class="headerX"><th>'.$displayedDate.'</th></tr><tbody>';
            if( !empty($loginDates) && is_array($loginDates) )
            {
                $i = 0;
                while( $i < sizeof($loginDates) )
                {
                    echo '<tr>' . "\n"
                    .    '<td><small>' . claro_disp_localised_date( $dateTimeFormatLong, strtotime($loginDates[$i]['login_date']) ) . '</small></td>' . "\n"
                    .    '</tr>' . "\n"
                    ;
                    // $limit is used to select only results between current login and next one
                    if( $i == ( sizeof($loginDates) - 1 ) || !isset($loginDates[$i+1]['login_date']) )
                    $limit = date("Y-m-d H:i:s",$nextReqDate);
                    else
                    $limit = $loginDates[$i+1]['login_date'];

                    // select all access in the displayed date range
                    $sql = "SELECT `access_tlabel`, count(`access_tid`) AS `nbr_access`
                            FROM `".$tbl_track_e_access."`
                            WHERE `access_user_id` = '". (int)$uInfo."'
                                AND `access_tid` IS NOT NULL
                                AND `access_date` > '" . $loginDates[$i]['login_date'] . "'
                                AND `access_date` < '" . $limit . "'
                            GROUP BY `access_tid`
                            ORDER BY `access_tid` ASC";
                    $toolAccess = claro_sql_query_fetch_all($sql);

                    if( !empty($toolAccess) && is_array($toolAccess) )
                    {
                        echo '<tr>' . "\n"
                        .    '<td colspan="2">' . "\n"
                        .    '<table width="50%" cellpadding="0" cellspacing="0" border="0">' . "\n"
                        ;
                        foreach( $toolAccess as $aToolAccess )
                        {
                            echo '<tr>' . "\n"
                            .    '<td width="70%"><small>' . $toolNameList[$aToolAccess['access_tlabel']] . '</small></td>' . "\n"
                            .    '<td width="30%" align="right"><small>' . $aToolAccess['nbr_access'] . ' ' . get_lang('Visits').'</small></td>' . "\n"
                            .    '</tr>' . "\n"
                            ;

                        }
                        echo '</table>' . "\n"
                        .    '</td></tr>' . "\n\n"
                        ;
                    }

                    $i++;
                }

            }
            else
            {
                echo '<tr>' . "\n"
                .    '<td colspan="2">'
                .    '<div align="center">' . get_lang('NoResult') . '</div>'
                .    '</td>'."\n"
                .    '</tr>' . "\n"
                ;
            }
            echo '</tbody></table>' . "\n";
        }
        else
        {
            echo get_lang('ErrorUserNotInGroup');
        }



    } break;
    case DISP_NOT_ALLOWED :
    {
        echo get_lang('Not allowed');
    } break;
    case DISP_TRACKING_DISABLED :
    {
        echo get_lang('TrackingDisabled');
    } break;
}


include $includePath . '/claro_init_footer.inc.php';
?>