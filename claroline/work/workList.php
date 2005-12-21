<?php // $Id$
/**
 * CLAROLINE
 *
 * @version 1.8 $Revision$
 *
 * @copyright (c) 2001-2005 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @see http://www.claroline.net/wiki/CLWRK/
 *
 * @package CLWRK
 *
 * @author Claro Team <cvs@claroline.net>
 *
 */

$tlabelReq = 'CLWRK___';
require '../inc/claro_init_global.inc.php';

if ( ! $_cid || ! $is_courseAllowed ) claro_disp_auth_form(true);

include_once $includePath . '/lib/fileManage.lib.php';
include_once $includePath . '/lib/pager.lib.php';
include_once $includePath . '/lib/assignment.lib.php';

$tbl_mdb_names = claro_sql_get_main_tbl();
$tbl_user                = $tbl_mdb_names['user'];
$tbl_rel_course_user     = $tbl_mdb_names['rel_course_user'];

$tbl_cdb_names = claro_sql_get_course_tbl();
$tbl_wrk_submission      = $tbl_cdb_names['wrk_submission'   ];
$tbl_group_team          = $tbl_cdb_names['group_team'       ];
$tbl_group_rel_team_user = $tbl_cdb_names['group_rel_team_user'];

$currentUserFirstName = $_user['firstName'];
$currentUserLastName  = $_user['lastName'];

// 'step' of pager
$usersPerPage = 50;

event_access_tool($_tid, $_courseTool['label']);

// use viewMode
claro_set_display_mode_available(true);

/*============================================================================
BASIC VARIABLES DEFINITION
=============================================================================*/

$currentCourseRepositorySys = $coursesRepositorySys.$_course['path'] . '/';
$currentCourseRepositoryWeb = $coursesRepositoryWeb.$_course['path'] . '/';

$fileAllowedSize     = get_conf('max_file_size_per_works') ;    //file size in bytes (from config file)
$wrkDirSys          = $currentCourseRepositorySys . 'work/'; // systeme work directory
$wrkDirWeb          = $currentCourseRepositoryWeb  .'work/'; // web work directory
$maxFilledSpace = get_conf('maxFilledSpace', 100000000);

// initialise dialog box to an empty string, all dialog will be concat to it
$dialogBox = '';

/*============================================================================
CLEAN INFORMATIONS SEND BY USER
=============================================================================*/
unset ($req);

// Probably deletable line
// $req['cmd'] = ( isset($_REQUEST['cmd']) )?$_REQUEST['cmd']:'';

$req['assigmentId'] = ( isset($_REQUEST['assigId'])
&& !empty($_REQUEST['assigId'])
&& ctype_digit($_REQUEST['assigId'])
) ? (int) $_REQUEST['assigId'] : false;

/*============================================================================
PREREQUISITES
=============================================================================*/

/*--------------------------------------------------------------------
ASSIGNMENT INFORMATIONS
--------------------------------------------------------------------*/

if ( $req['assigmentId'] )
{
    $assignment = CLWRK_LIST::get_assignement_data($req['assigmentId']);
    $assignment['dirSys'] = $wrkDirSys . 'assig_' . $req['assigmentId'] . '/';
    $assignment['dirWeb'] = $wrkDirWeb . 'assig_' . $req['assigmentId'] . '/';
}

// assignment not requested or not found
if ( !isset($assignment) || is_null($assignment) )
{
    // we NEED to know in which assignment we are, so if assigId is not set
    // relocate the user to the previous page
    header('Location: work.php');
    exit();
}

/*============================================================================
GROUP 'publish' option
=============================================================================*/
// redirect to the submission form prefilled with a .url document targetting the published document

/**
 * @todo $_REQUEST['submitGroupWorkUrl'] mus be treated in  filter process
 */
if ( isset($_REQUEST['submitGroupWorkUrl']) && !empty($_REQUEST['submitGroupWorkUrl']) && isset($_gid) )
{
    header( 'Location: userWork.php?authId='
    .       $_gid
    .       '&cmd=rqSubWrk'
    .       '&assigId=' . $_REQUEST['assigId']
    .       '&submitGroupWorkUrl=' . urlencode($_REQUEST['submitGroupWorkUrl'])
    );
    exit();
}
/*--------------------------------------------------------------------
USER GROUP INFORMATIONS
--------------------------------------------------------------------*/
// if this is a group assignement we will need some group infos about the user
if ( $assignment['assignment_type'] == 'GROUP' && isset($_uid) )
{
    // get the list of group the user is in
    $userGroupList = REL_GROUP_USER::get_user_group_list($_uid);
}

/*============================================================================
PERMISSIONS
=============================================================================*/
// assignment opening period is started
$afterStartDate      = (bool) ( $assignment['unix_start_date'] <= time() );

// assignment is invisible
$assignmentIsVisible = (bool) ( $assignment['visibility'] == 'VISIBLE' );

$is_allowedToEditAll = (bool) claro_is_allowed_to_edit();

if( !$assignmentIsVisible && !$is_allowedToEditAll )
{
    // if assignment is not visible and user is not course admin or upper
    header('Location: work.php');
    exit();
}

// upload or update is allowed between start and end date or after end date if late upload is allowed
$uploadDateIsOk      = (bool) ( $afterStartDate
                              && ( time() < $assignment['unix_end_date']
                                 || $assignment['allow_late_upload'] == 'YES'
                                 )
                              );

if( $assignment['assignment_type'] == 'INDIVIDUAL' )
{
    // user is authed and allowed
    $userCanPost = (bool) ( isset($_uid) && $is_courseAllowed );
}
else
{
    $userCanPost = (bool) ! ( isset($userGroupList) && count($userGroupList) <= 0 );
}

$is_allowedToSubmit   = (bool) ( $assignmentIsVisible  && $uploadDateIsOk  && $userCanPost ) || $is_allowedToEditAll;

/**
 * HEADER
 */

$interbredcrump[]= array ('url' => '../work/work.php', 'name' => get_lang('Work'));

$nameTools = get_lang('Assignment');

// to prevent parameters to be added in the breadcrumb
$_SERVER['QUERY_STRING'] = 'assigId=' . $req['assigmentId'];

/**
 * TOOL TITLE
 */
$pageTitle['mainTitle'  ] = $nameTools;
$pageTitle['subTitle'   ] = $assignment['title'];


// SHOW FEEDBACK
// only if :
//      - there is a text OR a file in automatic feedback
//    AND
//          feedback must be shown after end date and end date is past
//      OR  feedback must be shown directly after a post (from the time a work was uploaded by the student)

// there is a prefill_ file or text, so there is something to show
$textOrFilePresent = (bool) !empty($assignment['prefill_text']) || !empty($assignment['prefill_doc_path']);

// feedback must be shown after end date and end date is past
$showAfterEndDate = (bool) (  $assignment['prefill_submit'] == 'ENDDATE'
                           && $assignment['unix_end_date'] < time()
                           );


// feedback must be shown directly after a post
// check if user has already posted a work
// do not show to anonymous users because we can't know
// if the user already uploaded a work
$showAfterPost = (bool)
                 isset($_uid)
                 &&
                 (  $assignment['prefill_submit'] == 'AFTERPOST'
                    &&
                    CLWRK_LIST::get_wrk_submission_od_user($req['assigmentId']) >= 1
                 );


include $includePath . '/claro_init_header.inc.php';
echo claro_disp_tool_title($pageTitle);

/*--------------------------------------------------------------------
ASSIGNMENT INFOS
--------------------------------------------------------------------*/

// end date
echo '<p>' . "\n"
.    '<b>' . get_lang('EndDate') . '</b><br />' . "\n"
.    claro_disp_localised_date($dateTimeFormatLong, $assignment['unix_end_date'])
.    '</p>' . "\n"
;
// description of assignment
if( !empty($assignment['description']) )
{
    echo '<div>' . "\n"
    .    '<b>' . get_lang('AssignmentDescription') . '</b>'
    .    '<br />'
    .    claro_parse_user_text($assignment['description'])
    .    '</div>' . "\n"
    .    '<br />' . "\n"
    ;
}

// show to authenticated and anonymous users

if( $textOrFilePresent &&  ( $showAfterEndDate || $showAfterPost ) )
{
    echo '<fieldset>' . "\n"
    .    '<legend>'
    .    '<b>' . get_lang('Feedback') . '</b>'
    .    '</legend>'
    ;

    if( !empty($assignment['prefill_text']) )
    {
        echo claro_parse_user_text($assignment['prefill_text']);
    }

    if(  !empty($assignment['prefill_doc_path'])
    && !empty($assignment['prefill_text']) )
    {
        echo  '<p>'
        .     '<a href="' . $assignment['dirWeb'] . $assignment['prefill_doc_path'] . '">'
        .     $assignment['prefill_doc_path']
        .     '</a>'
        .     '</p>'
        ;
    }
    elseif( !empty($assignment['prefill_doc_path']) )
    {
        echo  '<a href="' . $assignment['dirWeb'] . $assignment['prefill_doc_path'] . '">'
        .     $assignment['prefill_doc_path']
        .     '</a>'
        ;
    }
    echo '</fieldset>'
    .    '<br />'
    ;
}
/*--------------------------------------------------------------------
WORK LIST
--------------------------------------------------------------------*/
if( $assignment['assignment_type'] == 'GROUP' )
{
    // do not count invisible work and feedbacks if the user is not courseAdmin
    if( $is_allowedToEditAll )
    {
        $checkVisible = " ";
    }
    elseif( isset($userGroupList) )
    {
        $checkVisible = " AND (`S`.`visibility` = 'VISIBLE' ";
        foreach( $userGroupList as $userGroup )
        {
            $checkVisible .= " OR `group_id` = ". (int) $userGroup['id'];
        }
        $checkVisible .= ") ";
    }
    else
    $checkVisible = " AND `S`.`visibility` = 'VISIBLE' ";

    $sql = "SELECT `G`.`id` as `authId`,`G`.`name`,
            count(`S`.`id`) as `submissionCount`, `S`.`title`
        FROM `".$tbl_group_team."` as `G`
        LEFT JOIN `".$tbl_wrk_submission."` as `S`
            ON `S`.`group_id` = `G`.`id`
                AND (
                    `S`.`assignment_id` = ".$_REQUEST['assigId']."
                    OR `S`.`assignment_id` IS NULL
                    )
                AND `S`.`original_id` IS NULL
                ".$checkVisible."
        GROUP BY `G`.`id`
        ORDER BY `G`.`name` ASC
        ";
}
else // INDIVIDUAL
{
    // do not count invisible work and feedbacks if the user is not courseAdmin
    if( $is_allowedToEditAll )
    {
        $checkVisible = " ";
    }
    elseif( isset($_uid) )
    {
        $checkVisible = " AND (`S`.`visibility` = 'VISIBLE' OR `S`.`user_id` = ". (int)$_uid.") ";
    }
    else
    {
        $checkVisible = " AND `S`.`visibility` = 'VISIBLE' ";
    }

    $sql = "SELECT `U`.`user_id` as `authId`, concat(`U`.`nom`, ' ', `U`.`prenom`) as `name`,
            count(`S`.`id`) as `submissionCount`, `S`.`title`, MIN(`S`.`creation_date`)
        FROM (`".$tbl_rel_course_user."` as `CU`, `".$tbl_user."` as `U`)
        LEFT JOIN `".$tbl_wrk_submission."` as `S`
            ON `S`.`user_id` = `U`.`user_id`
                AND (
                    `S`.`assignment_id` = ". (int)$_REQUEST['assigId']."
                    OR `S`.`assignment_id` IS NULL
                    )
                AND `S`.`original_id` IS NULL
                ".$checkVisible."
        WHERE `U`.`user_id` = `CU`.`user_id`
            AND `CU`.`code_cours` = '". addslashes($_cid)."'
        GROUP BY `U`.`user_id`
        ORDER BY `CU`.`statut` ASC, `CU`.`tutor` DESC,
                `U`.`nom` ASC, `U`.`prenom` ASC
        ";
}
$offset = (isset($_REQUEST['offset']) && !empty($_REQUEST['offset']) ) ? $_REQUEST['offset'] : 0;
$workPager = new claro_sql_pager($sql,$offset, $usersPerPage);

$workList = $workPager->get_result_list();

// get the number of feedback for submissions of each displayed user/group
$parentCondition = "";
foreach( $workList as $wrk )
{
    $parentCondition .= " OR `S`.`original_id` = ".$wrk['authId']; // wrk['id'] = user_id or group_id, according to the session context
}

if( $is_allowedToEditAll )
{
    $checkVisible = " ";
}
elseif( isset($_uid) && !isset($userGroupList) )
{
    $checkVisible = " AND `S`.`visibility` = 'VISIBLE'
                    AND ( `S2`.`visibility` = 'VISIBLE'
                    OR `S2`.`user_id` = ". (int) $_uid . ") ";
}
elseif( isset($userGroupList) )
{
    // work and his feedback must be visible OR the user is member of concerned group
    $checkVisible = " AND ( (`S`.`visibility` = 'VISIBLE'
                    AND `S2`.`visibility` = 'VISIBLE') ";
    foreach( $userGroupList as $userGroup )
    {
        $checkVisible .= " OR `S2`.`group_id` = ". (int) $userGroup['id'];
    }
    $checkVisible .= ") ";
}
else
{
    $checkVisible = " AND `S`.`visibility` = 'VISIBLE'
                    AND `S2`.`visibility` = 'VISIBLE' ";
}

$sql = "SELECT `S`.`original_id`, count(`S`.`id`) as `nbrFeedback`
        FROM `".$tbl_wrk_submission."` as `S`
        LEFT JOIN `".$tbl_wrk_submission."` as `S2`
            ON `S`.`parent_id` = `S2`.`id`
        WHERE `S`.`assignment_id` = ". (int)$_REQUEST['assigId']
.$checkVisible
." AND ( 0 = 1 "
.$parentCondition
." ) GROUP BY `S`.`original_id`";

$feedbackCounter = claro_sql_query_fetch_all($sql);
foreach( $feedbackCounter as $counter )
{
    $feedbackNbrList[$counter['original_id']] = $counter['nbrFeedback'];
}
// end of 'get the number of feedback for submissions of each displayed user/group'
/*--------------------------------------------------------------------
ADMIN LINKS
--------------------------------------------------------------------*/
echo '<p>';
if ( $is_allowedToSubmit && ($assignment['assignment_type'] != 'GROUP' ) )
{
    // link to create a new assignment
    echo '<a class="claroCmd" href="userWork.php'
    .    '?authId=' . $_uid
    .    '&amp;cmd=rqSubWrk'
    .    '&amp;assigId=' . $_REQUEST['assigId'] . '">'
    .    get_lang('SubmitWork')
    .    '</a>' . "\n"
    ;

    if( $is_allowedToEditAll ) echo ' | ';
}

if ( $is_allowedToEditAll )
{
    echo '<a class="claroCmd" href="feedback.php'
    .    '?cmd=rqEditFeedback'
    .    '&amp;assigId=' . $assignment['id'] . '">'
    .    get_lang('EditFeedback')
    .    '</a>' . "\n"
    ;
}
echo '</p>';
echo $workPager->disp_pager_tool_bar($_SERVER['PHP_SELF']."?assigId=".$_REQUEST['assigId']);
/*--------------------------------------------------------------------
LIST
--------------------------------------------------------------------*/
echo '<table class="claroTable emphaseLine" width="100%">' . "\n"
.    '<thead>' . "\n"
.    '<tr class="headerX">' . "\n"
.    '<th>' . get_lang('WrkAuthors') . '</th>' . "\n"
.    '<th>' . get_lang('FirstSubmission') . '</th>' . "\n"
.    '<th>' . get_lang('Submissions') . '</th>' . "\n"
.    '<th>' . get_lang('Feedbacks') . '</th>' . "\n"
.    '</tr>' . "\n"
.    '</thead>' . "\n"
.    '<tbody>'
;

if (isset($_uid))
{
    $date = $claro_notifier->get_notification_date($_uid);
    // call this function to set the __assignment__ as seen, all the submission as seen
    $claro_notifier->is_a_notified_ressource($_cid, $date, $_uid, $_gid, $_tid, $_REQUEST['assigId']);
}

foreach ( $workList as $thisWrk )
{

    echo '<tr align="center">' . "\n"
    .    '<td align="left">'
    .    '<a class="item" href="userWork.php'
    .    '?authId=' . $thisWrk['authId']
    .    '&amp;assigId=' . $_REQUEST['assigId'] . '">'
    ;

    if ( ($assignment['assignment_type'] != 'GROUP'
    && $thisWrk['authId'] == $_uid)
    )
    {
        echo '<b>' . $thisWrk['name'] . '</b>';
    }
    elseif($assignment['assignment_type'] == 'GROUP' && isset($userGroupList) && is_array($userGroupList) && array_key_exists($thisWrk['authId'],$userGroupList))
    {
        echo '<b>' . $thisWrk['name'] . '</b>';
    }
    else
    {
        echo $thisWrk['name'];
    }


    echo '</a>'
    .    '</td>' . "\n"
    .    '<td>';
    if( empty($thisWrk['title']) )
    echo '&nbsp;';
    else
    echo $thisWrk['title'];

    echo '</td>' . "\n"
    .    '<td>' . $thisWrk['submissionCount'] . '</td>' . "\n"
    .    '<td>'
    ;

    if( isset($feedbackNbrList) && is_array($feedbackNbrList) && array_key_exists($thisWrk['authId'],$feedbackNbrList))
    {
        echo $feedbackNbrList[$thisWrk['authId']] ;
    }
    else
    {
        echo '0';
    }


    echo '</td>' . "\n"
    .    '</tr>' . "\n\n"
    ;
}

echo '</tbody>' . "\n"
.    '</table>' . "\n\n"
;

echo $workPager->disp_pager_tool_bar($_SERVER['PHP_SELF']."?assigId=".$_REQUEST['assigId']);

// FOOTER
include $includePath . '/claro_init_footer.inc.php';


?>