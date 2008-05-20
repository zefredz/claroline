<?php // $Id$
/**
 * CLAROLINE
 *
 * @version 1.9 $Revision$
 *
 * @copyright (c) 2001-2007 Universite catholique de Louvain (UCL)
 *
 * @author Sebastien Piraux <piraux_seb@hotmail.com>
 *
 * @package CLSTAT
 */

require_once dirname( __FILE__ ) . '../../inc/claro_init_global.inc.php';


/*
 * Init request vars
 */

if( isset($_REQUEST['userId']) && is_numeric($_REQUEST['userId']) )   $userId = (int) $_REQUEST['userId'];
else                                                                  $userId = null;

if( isset($_REQUEST['courseId']) && !empty($_REQUEST['courseId']) )
{
    $courseId = $_REQUEST['courseId'];
}
else
{
    if( claro_is_in_a_course() ) $courseId = claro_get_current_course_id();
    else                         $courseId = null;
}

if( isset($_REQUEST['exId']) && is_numeric($_REQUEST['exId']) )   $exId = (int) $_REQUEST['exId'];
else                                                              $exId = null;

uses( 'core/claroline.lib', 'courselist.lib', 'user.lib', 'course_utils.lib');
$claroline = Claroline::getInstance();

/*
 * Permissions
 */
$is_allowedToTrack = false;
$canSwitchCourses = false;

if( !is_null($userId) && claro_is_user_authenticated() )
{
    if(  $userId == claro_get_current_user_id() )
    {
        $is_allowedToTrack = true;
        $canSwitchCourses = true;
    }
}

if( claro_is_course_manager() || claro_is_platform_admin() )
{
    $is_allowedToTrack = true;

    if( claro_is_platform_admin() )
    {
        $canSwitchCourses = true;
    }
}

if( claro_is_in_a_course() )
{
    $canSwitchCourses = false;
}

/*
 * Init some other vars
 */

$dialogBox = '';

// user's course list
if( $canSwitchCourses )
{
    // get all
    $userCourseList = get_user_course_list($userId, true);

    if( !is_array($userCourseList) )
    {
        $userCourseList = array();
    }
}

// user's data
$userData = user_get_properties($userId);

if( !is_array($userData) )
{
    $dialogBox .= get_lang('Cannot find user.') ;
}



/*
 * Output
 */
$cssLoader = CssLoader::getInstance();
$cssLoader->load( 'tracking', 'screen');

// initialize output
$claroline->setDisplayType( CL_PAGE );

//-- Content
$nameTools = get_lang('User statistics');

/*
 * Part zero : prepare to fight
 */
$html = '';

/*
 * Part one : user information
 */
$html .= '<div id="userCart">' . "\n"
.     ' <div id="picture"><div style="border: 1px solid #AAA; background-color: #DDD; width: 100px; height: 125px; margin: auto;font-size: small; color: #AAA;"><br /><br /><br />No picture</div></div>' . "\n"
.     ' <div id="details">'
.     '  <p><span>' . get_lang('Last name') . '</span><br /> ' . htmlspecialchars($userData['lastname']) . '</p>'
.     '  <p><span>' . get_lang('First name') . '</span><br /> ' . htmlspecialchars($userData['firstname']) . '</p>'
.     '  <p><span>' . get_lang('Email') . '</span><br /> ' . htmlspecialchars($userData['email']) . '</p>'
.     ' </div>' . " \n"
.     '</div>' . "\n"
.     '<div class="spacer"></div>' . "\n";

/*
 * Part two : course list if needed
 */
if( $canSwitchCourses )
{
    $html .= '<ul id="navlist">' . "\n"
    .     ' <li><a '.(empty($courseId)?'class="current"':'').' href="userLog.php?userId='.$userId.'">'.get_lang('Platform').'</a></li>' . "\n";


    foreach( $userCourseList as $course )
    {
        if( $course['sysCode'] == $courseId )     $class = 'class="current"';
        else                                        $class = '';

        $html .= ' <li>'
        .     '<a '.$class.' href=userLog.php?userId='.$userId.'&amp;courseId='.$course['sysCode'].'>'.$course['title'].'</a>'
        .     '</li>' . "\n";
    }

    $html .= '</ul>' . "\n\n";
}
else
{
    $html .= '<p>'
    .     '<a href="'.get_path('url').'/claroline/user/user.php' . claro_url_relay_context('?') . '"><small>'
    .    '&lt;&lt;&nbsp;'
    .    get_lang('Back to user list')
    .    '</small></a>' . "\n"
    .     '</p>' . "\n";
}

/*
 * Part three : output of tracking
 * A is when we have a courseId, B when we have to show platform
 */
if( !empty($courseId) )
{
    /*
     * Part three - One : access to course
     */


    $courseAccess = getUserCourseAccess($userId, $courseId);

    $header = get_lang('Access to course and tools');

    $content = '<table class="claroTable emphaseLine" cellpadding="2" cellspacing="1" border="0" align="center">' . "\n"
    .    '<tr class="headerX">' . "\n"
    .    '<th>' . get_lang('Month') . '</th>' . "\n"
    .    '<th>' . get_lang('Number of access') . '</th>' . "\n"
    .    '</tr>' . "\n"
    .    '<tbody>' . "\n"
    ;

    $total = 0;
    if( !empty($courseAccess) && is_array($courseAccess) )
    {
        $langLongMonthNames = get_lang_month_name_list('long');
        foreach( $courseAccess as $access )
        {
            $content .= '<tr>' . "\n"
            .    '<td>' . "\n"
            .    '<a href="logins_details.php?uInfo='.$userId . '&amp;reqdate='.$access['unix_date'].'">' . $langLongMonthNames[date('n', $access['unix_date'])-1].' '.date('Y', $access['unix_date']).'</a>' . "\n"
            .    '</td>' . "\n"
            .    '<td valign="top" align="right">'
            .    $access['nbr_access']
            .    '</td>' . "\n"
            .    '</tr>' . "\n";

            $total = $total + $access['nbr_access'];
        }
        $content .= '</tbody>' . "\n"
        .    '<tfoot>' . "\n"
        .    '<tr>' . "\n"
        .    '<td>'
        .    get_lang('Total')
        .    '</td>' . "\n"
        .    '<td align="right">'
        .    $total
        .    '</td>' . "\n"
        .    '</tr>' . "\n"
        .    '</tfoot>' . "\n";
    }
    else
    {
        $content .= '<tfoot>' . "\n"
        .    '<tr>' . "\n"
        .    '<td colspan="2">' . "\n"
        .    '<center>'
        .    get_lang('No result')
        .    '</center>' . "\n"
        .    '</td>' . "\n"
        .    '</tr>' . "\n"
        .    '</tfoot>' . "\n";
    }
    $content .= '</table>' . "\n"
    .    '</td></tr>' . "\n";

    $footer = get_lang('Click on the month name for more details');

    $html .= renderStatBlock($header, $content, $footer);

    /*
     * Part three - Two : exercise results
     */
    $exerciseResults = getUserExerciseResults($userId, $courseId);

    $header = get_lang('Exercises results');

    $content = '<table class="claroTable emphaseLine" cellpadding="2" cellspacing="1" border="0" align="center">' . "\n"
    .    '<tr class="headerX">' . "\n"
    .    '<th>' . get_lang('Exercises').'</th>' . "\n"
    .    '<th>' . get_lang('Worst score').'</th>' . "\n"
    .    '<th>' . get_lang('Best score').'</th>' . "\n"
    .    '<th>' . get_lang('Average score').'</th>' . "\n"
    .    '<th>' . get_lang('Average Time').'</th>' . "\n"
    .    '<th>' . get_lang('Attempts').'</th>' . "\n"
    .    '<th>' . get_lang('Last attempt').'</th>' . "\n"
    .    '</tr>'
    ;

    if( !empty($exerciseResults) && is_array($exerciseResults) )
    {
        $content .= '<tbody>' . "\n";
        foreach( $exerciseResults as $result )
        {
            $content .= '<tr>' . "\n"
            .    '<td><a href="userLog.php?userId='.$userId.'&amp;cidReq='.$courseId.'&amp;exId='.$result['id'].'">'.$result['title'].'</td>' . "\n"
            .    '<td>'.$result['minimum'].'</td>' . "\n"
            .    '<td>'.$result['maximum'].'</td>' . "\n"
            .    '<td>'.(round($result['average']*10)/10).'</td>' . "\n"
            .    '<td>'.claro_html_duration(floor($result['avgTime'])).'</td>' . "\n"
            .    '<td>'.$result['attempts'].'</td>' . "\n"
            .    '<td>'.$result['lastAttempt'].'</td>' . "\n"
            .    '</tr>' . "\n";

            // display details of the exercise, all attempts
            if ( isset($exId) && $exId == $result['id'])
            {
                $exerciseDetails = getUserExerciceDetails($userId, $exId);

                $content .= '<tr>'
                .    '<td class="noHover">&nbsp;</td>' . "\n"
                .    '<td colspan="6" class="noHover">' . "\n"
                .    '<table class="claroTable emphaseLine" cellspacing="1" cellpadding="2" border="0" width="100%">' . "\n"
                .    '<tr class="headerX">' . "\n"
                .    '<th><small>' . get_lang('Date').'</small></th>' . "\n"
                .    '<th><small>' . get_lang('Score').'</small></th>' . "\n"
                .    '<th><small>' . get_lang('Time').'</small></th>' . "\n"
                .    '</tr>' . "\n"
                .    '<tbody>' . "\n";

                foreach ( $exerciseDetails as $details )
                {
                    $content .= '<tr>' . "\n"
                    .    '<td><small><a href="user_exercise_details.php?trackedExId='.$details['exe_id'].'">'.$details['exe_date'].'</a></small></td>' . "\n"
                    .    '<td><small>'.$details['exe_result'].'/'.$details['exe_weighting'].'</small></td>' . "\n"
                    .    '<td><small>'.claro_html_duration($details['exe_time']).'</small></td>' . "\n"
                    .    '</tr>' . "\n";
                }
                $content .= '</tbody>' . "\n"
                .    '</table>' . "\n\n"
                .    '</td>' . "\n"
                .    '</tr>' . "\n";

            }

        }
        $content .= '</tbody>' . "\n";
    }
    else
    {
        $content .= '<tfoot>' . "\n"
        .    '<tr>' . "\n"
        .    '<td colspan="7" align="center">' . get_lang('No result').'</td>' . "\n"
        .    '</tr>' . "\n"
        .    '</tfoot>' . "\n";
    }
    $content .= '</table>' . "\n\n";

    $footer = get_lang('Click on exercise title for more details');

    $html .= renderStatBlock($header, $content, $footer);


    /*
     * Part three - Three : learning paths
     */

    // TODO display LP progression

    /*
     * Part three - Four : Works
     */

    $submittedWorks = getUserWorks($userId, $courseId);

    $header = get_lang('Submissions');

    $content = '<table class="claroTable emphaseLine" cellpadding="2" cellspacing="1" border="0" align="center">' . "\n"
    .    '<tr class="headerX">' . "\n"
    .    '<th>' . get_lang('Assignment').'</th>' . "\n"
    .    '<th>' . get_lang('Work title').'</th>' . "\n"
    .    '<th>' . get_lang('Author(s)').'</th>' . "\n"
    .    '<th>' . get_lang('Score').'</th>' . "\n"
    .    '<th>' . get_lang('Date').'</th>' . "\n"
    .    '</tr>' . "\n";

    if( !empty($submittedWorks) && is_array($submittedWorks) )
    {
        $content .= '<tbody>' . "\n";

        $prevAssignmentTitle = "";
        foreach($submittedWorks as $work)
        {
            if( $work['a_title'] == $prevAssignmentTitle )
            {
                $assignmentTitle = "&nbsp;";
            }
            else
            {
                $assignmentTitle = $work['a_title'];
                $prevAssignmentTitle = $work['a_title'];
            }

            if( $work['score'] != 0 )
            {
                $displayedScore = $work['score']." %";
            }
            else
            {
                $displayedScore  = get_lang('No score');
            }

            if( isset($work['g_name']) )
            {
                $authors = $work['authors']."( ".$work['g_name']." )";
            }
            else
            {
                $authors = $work['authors'];
            }

            $timestamp = strtotime($work['last_edit_date']);
            $beautifulDate = claro_html_localised_date(get_locale('dateTimeFormatLong'),$timestamp);


            $content .= '<tr>' . "\n"
            .    '<td>'.$assignmentTitle.'</td>' . "\n"
            .    '<td>'.$work['s_title'].'</td>' . "\n"
            .    '<td>'.$authors.'</td>' . "\n"
            .    '<td>'.$displayedScore.'</td>' . "\n"
            .    '<td><small>'.$beautifulDate.'</small></td>' . "\n"
            .    '</tr>' . "\n";
        }
        $content .= '</tbody>' . "\n";

    }
    else
    {
        $content .= '<tfoot><tr>' . "\n"
        .    '<td colspan="5" align="center">' . get_lang('No result').'</td>' . "\n"
        .    '</tr></tfoot>' . "\n";
    }
    $content .= '</table>' . "\n";


    $footer = get_lang('Works uploaded by the student in the name of \'Authors\'');

    $html .= renderStatBlock($header, $content, $footer);

    /*
     * Part three - Five : Downloads
     */
    $documentDownloads = getUserDocumentDownloads($userId, $courseId);

    $header = get_lang('Documents');

    $content = '<table class="claroTable emphaseLine" cellpadding="2" cellspacing="1" border="0" align="center">' . "\n"
    .    '<tr class="headerX">' . "\n"
    .    '<th>' . get_lang('Document').'</th>' . "\n"
    .    '<th>' . get_lang('Last download').'</th>' . "\n"
    .    '<th>' . get_lang('Downloads').'</th>' . "\n"
    .    '</tr>';

    if( !empty($documentDownloads) && is_array($documentDownloads) )
    {
        $content .= '<tbody>' . "\n";
        foreach( $documentDownloads as $download )
        {
            // make document path shorter if needed
            $content .= '<tr>' . "\n"
            .    '<td>'.$download['document'].'</td>' . "\n"
            .    '<td>'.claro_html_localised_date( get_locale('dateFormatLong'), $download['unix_date']).'</td>' . "\n"
            .    '<td>'.$download['downloads'].'</td>' . "\n"
            .    '</tr>' . "\n";
        }
        $content .= '</tbody>' . "\n";
    }
    else
    {
        $content .= '<tfoot>' . "\n"
        .    '<tr>' . "\n"
        .    '<td colspan="3" align="center">' . get_lang('No result').'</td>' . "\n"
        .    '</tr>' . "\n"
        .    '</tfoot>' . "\n";
    }
    $content .= '</table>' . "\n\n";

    $footer = get_lang('Documents downloaded by the student');

    $html .= renderStatBlock($header, $content, $footer);




    /*
     * Part three - six : Forum
     */
    $lastUserPosts = getUserLastTenPosts($userId, $courseId);

    $header = get_lang('Forums activity');

    $content = '<table class="claroTable emphaseLine" cellpadding="2" cellspacing="1" border="0" align="center">' . "\n"
    .    '<tr class="headerX">' . "\n"
    .    '<th>' . get_lang('Topic').'</th>' . "\n"
    .    '<th>' . get_lang('Last message').'</th>' . "\n"
    .    '</tr>' . "\n";

    if( !empty($lastUserPosts) && is_array($lastUserPosts) )
    {
        $content .= '<tbody>' . "\n";
        foreach( $lastUserPosts as $result )
        {
            $content .= '<tr>' . "\n"
            .    '<td><a href="../phpbb/viewtopic.php?topic='.$result['topic_id'].'">'.$result['topic_title'].'</a></td>' . "\n"
            .    '<td>'.$result['last_message'].'</td>' . "\n"
            .    '</tr>' . "\n";
        }
        $content .= '</tbody>' . "\n";

    }
    else
    {
        $content .= '<tfoot>' . "\n"
        .    '<tr>' . "\n"
        .    '<td align="center" colspan="2">' . get_lang('No result').'</td>' . "\n"
        .    '</tr>' . "\n"
        .    '</tfoot>' . "\n";
    }
    $content .= '</table>' . "\n";

    $footer = get_lang('Messages posted') . ' : ' . getUserTotalForumPost($userId, $courseId) . '<br />' . "\n"
    .     get_lang('Topics started') . ' : ' . getUserTotalForumTopics($userId, $courseId) . '<br />' . "\n";

    $html .= renderStatBlock($header, $content, $footer);
}
else // no courseId so we show platform stats
{
    /*
     * Part three B - Logins
     */
    $userLogins = getUserLogins($userId);

    $header = get_lang('Access to platform');

    $content = '<table class="claroTable emphaseLine" cellpadding="2" cellspacing="1" border="0" align="center">' . "\n"
    .    '<tr class="headerX">' . "\n"
    .    '<th>' . get_lang('Month') . '</th>' . "\n"
    .    '<th>' . get_lang('Number of logins') . '</th>' . "\n"
    .    '</tr>' . "\n"
    .    '<tbody>' . "\n";

    $total = 0;
    if( !empty($userLogins) && is_array($userLogins) )
    {
        $langLongMonthNames = get_lang_month_name_list('long');
        foreach( $userLogins as $result )
        {
            $content .= '<tr>' . "\n"
            .    '<td>' . "\n"
            .    '<a href="logins_details.php?uInfo='.$userId . '&amp;reqdate='.$result['unix_date'].'">' . $langLongMonthNames[date('n', $result['unix_date'])-1].' '.date('Y', $result['unix_date']).'</a>' . "\n"
            .    '</td>' . "\n"
            .    '<td valign="top" align="right">'
            .    $result['nbr_login']
            .    '</td>' . "\n"
            .    '</tr>' . "\n";

            $total = $total + $result['nbr_login'];
        }
        $content .=  '</tbody>' . "\n"
        .    '<tfoot>' . "\n"
        .    '<tr>' . "\n"
        .    '<td>'
        .    get_lang('Total')
        .    '</td>' . "\n"
        .    '<td align="right">'
        .    $total
        .    '</td>' . "\n"
        .    '</tr>' . "\n"
        .    '</tfoot>' . "\n";
    }
    else
    {
        $content .=  '<tfoot>' . "\n"
        .    '<tr>' . "\n"
        .    '<td colspan="2">' . "\n"
        .    '<center>'
        .    get_lang('No result')
        .    '</center>' . "\n"
        .    '</td>' . "\n"
        .    '</tr>' . "\n"
        .    '</tfoot>' . "\n";
    }
    $content .=  '</table>' . "\n";

    $footer = '';
    $html .= renderStatBlock($header, $content, $footer);
}

/*
 * Part Four - set and output content of the page
 */
$claroline->display->body->setContent($html);

echo $claroline->display->render();







function renderStatBlock($header,$content,$footer)
{
    $html = '<div class="statBlock">' . "\n"
    .     ' <div class="blockHeader">' . "\n"
    .     $header
    .     ' </div>' . "\n"
    .     ' <div class="blockContent">' . "\n"
    .     $content
    .     ' </div>' . "\n"
    .     ' <div class="blockFooter">' . "\n"
    .     $footer
    .     ' </div>' . "\n"
    .     '</div>' . "\n";

    return $html;
}

function getUserPlatformAccess($userId, $courseId)
{
    $tbl_mdb_names = claro_sql_get_main_tbl(getCourseDbNameGlu($courseId));
    $tbl_track_e_login = $tbl_mdb_names['track_e_login'];

    $sql = "SELECT UNIX_TIMESTAMP(`login_date`) AS `unix_date`,
               count(`login_date`)          AS `nbr_login`
            FROM `" . $tbl_track_e_login . "`
            WHERE `login_user_id` = " . (int) $userId . "
            GROUP BY MONTH(`login_date`), YEAR(`login_date`)
            ORDER BY `login_date` ASC";

    $results = claro_sql_query_fetch_all($sql);

    return $results;
}

function getUserCourseAccess($userId, $courseId)
{
    $tbl_cdb_names = claro_sql_get_course_tbl(getCourseDbNameGlu($courseId));
    $tbl_track_e_access       = $tbl_cdb_names['track_e_access'];

    $sql = "SELECT UNIX_TIMESTAMP(`access_date`) AS `unix_date`,
               count(`access_date`)          AS `nbr_access`
            FROM `" . $tbl_track_e_access . "`
            WHERE `access_user_id` = " . (int) $userId . "
            GROUP BY MONTH(`access_date`), YEAR(`access_date`)
            ORDER BY `access_date` ASC";

    $results = claro_sql_query_fetch_all($sql);

    return $results;

}

function getUserExerciseResults($userId, $courseId)
{
    $tbl_cdb_names = claro_sql_get_course_tbl(getCourseDbNameGlu($courseId));
    $tbl_qwz_exercise = $tbl_cdb_names['qwz_exercise'];
    $tbl_track_e_exercises = $tbl_cdb_names['track_e_exercices']; // todo rename this table in english ...

    $sql = "SELECT `E`.`title`,
                   `E`.`id`,
                   MIN(`TEX`.`exe_result`)    AS `minimum`,
                   MAX(`TEX`.`exe_result`)    AS `maximum`,
                   AVG(`TEX`.`exe_result`)    AS `average`,
                   MAX(`TEX`.`exe_weighting`) AS `weighting`,
                   COUNT(`TEX`.`exe_user_id`) AS `attempts`,
                   MAX(`TEX`.`exe_date`)      AS `lastAttempt`,
                   AVG(`TEX`.`exe_time`)      AS `avgTime`
              FROM `" . $tbl_qwz_exercise . "` AS `E`
                 , `" . $tbl_track_e_exercises . "` AS `TEX`
        WHERE `TEX`.`exe_user_id` = " . (int) $userId . "
            AND `TEX`.`exe_exo_id` = `E`.`id`
        GROUP BY `TEX`.`exe_exo_id`
        ORDER BY `E`.`title` ASC";

    $results = claro_sql_query_fetch_all($sql);

    return $results;
}


function getUserExerciceDetails($userId, $exerciseId, $courseId)
{
    $tbl_cdb_names = claro_sql_get_course_tbl(getCourseDbNameGlu($courseId));
    $tbl_track_e_exercises = $tbl_cdb_names['track_e_exercices']; // todo rename this table in english ...

    $sql = "SELECT `exe_id`, `exe_date`, `exe_result`, `exe_weighting`, `exe_time`
            FROM `" . $tbl_track_e_exercises . "`
            WHERE `exe_exo_id` = ". (int) $exerciseId."
            AND `exe_user_id` = ". (int) $userId."
            ORDER BY `exe_date` ASC";

    $results = claro_sql_query_fetch_all($sql);

    return $results;
}

function getUserDocumentDownloads($userId, $courseId)
{
    $tbl_cdb_names = claro_sql_get_course_tbl(getCourseDbNameGlu($courseId));
    $tbl_track_e_downloads = $tbl_cdb_names['track_e_downloads'];

    $sql = "SELECT `down_doc_path` AS `document`,
                UNIX_TIMESTAMP(`down_date`) AS `unix_date`,
                COUNT(`down_user_id`) AS `downloads`
            FROM `" . $tbl_track_e_downloads . "`
            WHERE `down_user_id` = '". (int) $userId."'
            GROUP BY `down_doc_path`
            ORDER BY `down_doc_path` ASC,`down_date` ASC";

    $results = claro_sql_query_fetch_all($sql);

    return $results;
}

function getUserTotalForumPost($userId, $courseId)
{
    $tbl_cdb_names = claro_sql_get_course_tbl(getCourseDbNameGlu($courseId));
    $tbl_bb_posts = $tbl_cdb_names['bb_posts'];

    $sql = "SELECT count(`post_id`)
                FROM `" . $tbl_bb_posts . "`
                WHERE `poster_id` = '". (int) $userId . "'";

    $value = claro_sql_query_get_single_value($sql);

    if( is_numeric($value) )    return $value;
    else                         return 0;
}

function getUserTotalForumTopics($userId, $courseId)
{
    $tbl_cdb_names = claro_sql_get_course_tbl(getCourseDbNameGlu($courseId));
    $tbl_bb_topics = $tbl_cdb_names['bb_topics'];

    $sql = "SELECT count(`topic_title`)
                FROM `" . $tbl_bb_topics . "`
                WHERE `topic_poster` = '". (int) $userId . "'";

    $value = claro_sql_query_get_single_value($sql);

    if( is_numeric($value) )    return $value;
    else                         return 0;
}

function getUserLastTenPosts($userId, $courseId)
{
    $tbl_cdb_names = claro_sql_get_course_tbl(getCourseDbNameGlu($courseId));
    $tbl_bb_posts = $tbl_cdb_names['bb_posts'];
    $tbl_bb_topics = $tbl_cdb_names['bb_topics'];

    $sql = "SELECT `bb_t`.`topic_id`,
                    `bb_t`.`topic_title`,
                    max(`bb_t`.`topic_time`) AS `last_message`
                FROM `" . $tbl_bb_posts . "`  AS `bb_p`
                   , `" . $tbl_bb_topics . "` AS `bb_t`
                WHERE `bb_p`.`poster_id` = '". (int) $userId."'
                  AND `bb_t`.`topic_id` = `bb_p`.`topic_id`
                GROUP BY `bb_t`.`topic_title`
                ORDER BY `bb_p`.`post_time` DESC
                LIMIT 10";

    $results = claro_sql_query_fetch_all($sql);

    return $results;
}

function getUserWorks($userId, $courseId)
{
    $tbl_cdb_names = claro_sql_get_course_tbl(getCourseDbNameGlu($courseId));
    $tbl_wrk_assignment = $tbl_cdb_names['wrk_assignment'];
    $tbl_wrk_submission = $tbl_cdb_names['wrk_submission'];
    $tbl_group_team = $tbl_cdb_names['group_team'];

    $sql = "SELECT `A`.`title` AS `a_title`,
               `A`.`assignment_type`,
               `S`.`id`, `S`.`title` AS `s_title`,
               `S`.`group_id`, `S`.`last_edit_date`, `S`.`authors`,
               `S`.`score`,
               `S`.`parent_id`,
               `G`.`name` AS `g_name`
          FROM `" . $tbl_wrk_assignment . "` AS `A` ,
               `" . $tbl_wrk_submission . "` AS `S`
          LEFT JOIN `" . $tbl_group_team . "` AS `G`
                 ON `G`.`id` = `S`.`group_id`
         WHERE `A`.`id` = `S`.`assignment_id`
           AND ( `S`.`user_id` = ". (int) $userId."
                  OR ( `S`.`parent_id` IS NOT NULL AND `S`.`parent_id` ) )
                AND `A`.`visibility` = 'VISIBLE'
         ORDER BY `A`.`title` ASC, `S`.`last_edit_date` ASC";

    $results = claro_sql_query_fetch_all($sql);

    $submissionList = array();

    // store submission details in list
    foreach( $results as $submission )
    {
        if( empty($submission['parent_id']) )
        {
            // is a submission
            $submissionList[$submission['id']] = $submission;
        }
    }

    // get scores
    foreach( $results as $submission )
    {
        if( !empty($submission['parent_id']) && isset($submissionList[$submission['parent_id']]) && is_array($submissionList[$submission['parent_id']]) )
        {
            // is a feedback
            $submissionList[$submission['parent_id']]['score'] = $submission['score'];
        }
    }

    return $submissionList;
}


function getUserLogins($userId)
{
    $tbl_mdb_names = claro_sql_get_main_tbl();
    $tbl_track_e_login           = $tbl_mdb_names['track_e_login'    ];

    $sql = "SELECT UNIX_TIMESTAMP(`login_date`) AS `unix_date`,
                   count(`login_date`)          AS `nbr_login`
                FROM `" . $tbl_track_e_login . "`
                WHERE `login_user_id` = " . (int) $userId . "
                GROUP BY MONTH(`login_date`), YEAR(`login_date`)
                ORDER BY `login_date` ASC";

    $results = claro_sql_query_fetch_all($sql);

    return $results;
}

/*
 * To avoid a lot of useless queries to get course data each time we want
 * only the course dbNameGlue we use some of tricky static method to get it
 * as less as possible
 */
function getCourseDbNameGlu($courseId)
{
    static $requestedCourseDbNameGlu = '';
    static $requestedCourseId = '';

    if( !empty($courseId) && $requestedCourseId != $courseId )
    {
        // cache last requested course to be able to retrieve
        // new dbNameGlu is courseId is different from previous request
        $requestedCourseId = $courseId;

        $courseInfoArray = get_info_course($courseId);
        $requestedCourseDbNameGlu = $courseInfoArray["dbNameGlu"];
    }

    return $requestedCourseDbNameGlu;
}
?>