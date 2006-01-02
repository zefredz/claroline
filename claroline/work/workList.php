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
$usersPerPage = get_conf('usersPerPage',20);

event_access_tool($_tid, $_courseTool['label']);

// use viewMode
claro_set_display_mode_available(true);

/*============================================================================
BASIC VARIABLES DEFINITION
=============================================================================*/

$fileAllowedSize = get_conf('max_file_size_per_works') ;    //file size in bytes (from config file)
$wrkDirSys       = get_conf('coursesRepositorySys').$_course['path'] . '/' . 'work/'; // systeme work directory
$wrkDirWeb       = get_conf('coursesRepositoryWeb').$_course['path'] . '/'  .'work/'; // web work directory
$maxFilledSpace  = get_conf('maxFilledSpace', 100000000);

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
                    )
                    ? (int) $_REQUEST['assigId']
                    : false;

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
 * @todo $_REQUEST['submitGroupWorkUrl'] must be treated in  filter process
 */
if ( isset($_REQUEST['submitGroupWorkUrl']) && !empty($_REQUEST['submitGroupWorkUrl']) && isset($_gid) )
{
    header( 'Location: userWork.php?authId='
    .       $_gid
    .       '&cmd=rqSubWrk'
    .       '&assigId=' . $req['assigmentId']
    .       '&submitGroupWorkUrl=' . urlencode($_REQUEST['submitGroupWorkUrl'])
    );
    exit();
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

if (isset($_uid))
{
    // call this function to set the __assignment__ as seen, all the submission as seen
    $claro_notifier->is_a_notified_ressource($_cid, $claro_notifier->get_notification_date($_uid), $_uid, $_gid, $_tid, $req['assigmentId']);
}


if( $assignment['assignment_type'] == 'GROUP' )
{
    $userGroupList = REL_GROUP_USER::get_user_group_list($_uid);
}

/* Prepare submission and feedback SQL filters - remove hidden item from count */

$submissionConditionList = array();
$feedbackConditionList = array();

if( ! $is_allowedToEditAll )
{
    $submissionConditionList[] = "`S`.`visibility` = 'VISIBLE'";
    $feedbackConditionList[]   = "(`S`.`visibility` = 'VISIBLE' AND `FB`.`visibility` = 'VISIBLE')";

    if( isset($userGroupList)  )
    {
        $submissionConditionList[] = "S.group_id IN ("  . implode(', ', array_map( 'intval', $userGroupList) ) . ")";
        $feedbackConditionList[]   = "FB.group_id IN (" . implode(', ', array_map( 'intval', $userGroupList) ) . ")";
    }
    elseif ( isset($_uid)      )
    {
        $submissionConditionList[] = "`S`.`user_id` = "      . (int) $_uid;
        $feedbackConditionList[]   = "`FB`.`original_id` = " . (int) $_uid;
    }
}

$submissionFilterSql = implode(' OR ', $submissionConditionList);
if (!empty($submissionFilterSql) ) $submissionFilterSql = ' AND ('.$submissionFilterSql.') ';

$feedbackFilterSql = implode(' OR ', $feedbackConditionList);
if ( ! empty($feedbackFilterSql) ) $feedbackFilterSql = ' AND ('.$feedbackFilterSql.')';

if( $assignment['assignment_type'] == 'INDIVIDUAL' )
{
    // user is authed and allowed
    $userCanPost = (bool) ( isset($_uid) && $is_courseAllowed );

    $sql = "SELECT `U`.`user_id`                        AS `authId`,
                   CONCAT(`U`.`nom`, ' ', `U`.`prenom`) AS `name`,
                   `S`.`title`,
                   COUNT(`S`.`id`)                      AS `submissionCount`,
                   COUNT(`FB`.`id`)                     AS `feedbackCount`
            #GET USER LIST
            FROM  `" . $tbl_user . "` AS `U`

            #ONLY FROM COURSE
            INNER JOIN  `" . $tbl_rel_course_user . "` AS `CU`
                    ON  `U`.`user_id` = `CU`.`user_id`
                   AND `CU`.`code_cours` = '" . addslashes($_cid) . "'

            # SEARCH ON SUBMISSIONs
            LEFT JOIN `" . $tbl_wrk_submission . "` AS `S`
                   ON ( `S`.`assignment_id` = " . (int) $req['assigmentId'] . " OR `S`.`assignment_id` IS NULL)
                  AND `S`.`user_id` = `U`.`user_id`
                  AND `S`.`original_id` IS NULL
            " . $submissionFilterSql . "

             # SEARCH ON FEEDBACKS
            LEFT JOIN `".$tbl_wrk_submission."` as `FB`
                   ON `FB`.`parent_id` = `S`.`id`
             " . $feedbackFilterSql . "
             
			GROUP BY `U`.`user_id`,
                     `S`.`original_id`
        	
        	HAVING `submissionCount` > 0
";

    if ( isset($_GET['sort']) ) $sortKeyList[$_GET['sort']] = isset($_GET['dir']) ? $_GET['dir'] : SORT_ASC;

    $sortKeyList['CU.statut'] = SORT_ASC;
    $sortKeyList['CU.tutor']  = SORT_DESC;
    $sortKeyList['U.nom']     = SORT_ASC;
    $sortKeyList['U.prenom']  = SORT_ASC;

}
else  // $assignment['assignment_type'] == 'GROUP'
{
    /**
     * USER GROUP INFORMATIONS
     */
    $userCanPost = (bool) ! ( isset($userGroupList) && count($userGroupList) <= 0 );

    $sql = "SELECT `G`.`id`            AS `authId`,
                   `G`.`name`,
                   `S`.`title`,
                   COUNT(`S`.`id`)     AS `submissionCount`,
                   COUNT(`FB`.`id`)    AS `feedbackCount`

        FROM `" . $tbl_group_team . "` AS `G`

        # SEARCH ON SUBMISSIONS
        LEFT JOIN `".$tbl_wrk_submission."` AS `S`
               ON `S`.`group_id` = `G`.`id`
              AND (`S`.`assignment_id` = " . $req['assigmentId'] . " OR `S`.`assignment_id` IS NULL )
              AND `S`.`original_id` IS NULL
        " . $submissionFilterSql . "

        # SEARCH ON FEEBACKS
        LEFT JOIN `" . $tbl_wrk_submission . "` as `FB`
               ON `FB`.`parent_id` = `S`.`id`
        " . $feedbackFilterSql . "

        GROUP BY `G`.`id`,          # group by 'group'
                 `S`.`original_id`

		HAVING `submissionCount` > 0 OR `feedbackCount` > 0

        ";

    if ( isset($_GET['sort']) ) $sortKeyList[$_GET['sort']] = isset($_GET['dir']) ? $_GET['dir'] : SORT_ASC;
    $sortKeyList['G.name'] = SORT_ASC;

}
$is_allowedToSubmit   = (bool) ( $assignmentIsVisible  && $uploadDateIsOk  && $userCanPost ) || $is_allowedToEditAll;

/*--------------------------------------------------------------------
WORK LIST
--------------------------------------------------------------------*/
$offset = (isset($_REQUEST['offset']) && !empty($_REQUEST['offset']) ) ? $_REQUEST['offset'] : 0;
$workPager = new claro_sql_pager($sql,$offset, $usersPerPage);

foreach($sortKeyList as $thisSortKey => $thisSortDir)
{
    $workPager->add_sort_key( $thisSortKey, $thisSortDir);
}


$workList = $workPager->get_result_list();
foreach ( $workList as $workId => $thisWrk )
{

    $thisWrk['is_mine'] = (  ($assignment['assignment_type'] == 'INDIVIDUAL' && $thisWrk['authId'] == $_uid)
                          || ($assignment['assignment_type'] == 'GROUP'      && in_array($thisWrk['authId'], $userGroupList)));

    if ($thisWrk['is_mine']) $workList[$workId]['name'] = '<b>' . $thisWrk['name'] . '</b>';

    $workList[$workId]['name'] = '<a class="item" href="userWork.php'
    .                            '?authId=' . $thisWrk['authId']
    .                            '&amp;assigId=' . $req['assigmentId'] . '">'
    .                            $workList[$workId]['name']
    .                            '</a>'
    ;

}

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
$pageTitle['mainTitle'] = $nameTools;
$pageTitle['subTitle' ] = $assignment['title'];


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
                    CLWRK_LIST::get_wrk_submission_of_user($req['assigmentId']) >= 1
                 );




 /**
  * OUTPUT
  *
  * 3 parts in this output
  * - A detail about the current assgnment
  * - "Command" links to commands
  * - A list of user relating submission and feedback
  *
  */

include $includePath . '/claro_init_header.inc.php';
echo claro_disp_tool_title($pageTitle);

/**
 * ASSIGNMENT INFOS
 */

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

/**
 * COMMAND LINKS
 */
echo '<p>';
if ( $is_allowedToSubmit && ($assignment['assignment_type'] != 'GROUP' ) )
{
    // link to create a new assignment
    echo '<a class="claroCmd" href="userWork.php'
    .    '?authId=' . $_uid
    .    '&amp;cmd=rqSubWrk'
    .    '&amp;assigId=' . $req['assigmentId'] . '">'
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


/**
 * Submiter (User or group) listing
 */
$headerUrl = $workPager->get_sort_url_list($_SERVER['PHP_SELF']."?assigId=".$req['assigmentId']);

echo $workPager->disp_pager_tool_bar($_SERVER['PHP_SELF']."?assigId=".$req['assigmentId'])



.    '<table class="claroTable emphaseLine" width="100%">' . "\n"
.    '<thead>' . "\n"
.    '<tr class="headerX">' . "\n"
.    '<th scope="col" id="n">'
.    '<a href="' . $headerUrl['name'] . '">'
.    get_lang('WrkAuthors')
.    '</a>'
.    '</th>' . "\n"
.    '<th scope="col" id="t">'
.    '<a href="' . $headerUrl['title'] . '">'
.    get_lang('FirstSubmission')
.    '</a>'
.    '</th>' . "\n"
.    '<th scope="col" id="s">'
.    '<a href="' . $headerUrl['submissionCount'] . '">'
.    get_lang('Submissions')
.    '</a>'
.    '</th>' . "\n"
.    '<th scope="col" id="fb">'
.    '<a href="' . $headerUrl['feedbackCount'] . '">'
.    get_lang('Feedbacks')
.    '</a>'
.    '</th>' . "\n"
.    '</tr>' . "\n"
.    '</thead>' . "\n"
.    '<tbody>'
;


foreach ( $workList as $thisWrk )
{

    echo '<tr align="center">' . "\n"
    .    '<td align="left" headers="n" id="a'.$thisWrk['authId'].'" scope="row" >'
    .     $thisWrk['name']
    .    '</td>' . "\n"
    .    '<td headers="t a' . $thisWrk['authId'] . '">'
    .    $thisWrk['title']
    .    '</td>' . "\n"
    .    '<td headers="s a' . $thisWrk['authId'] . '">'
    .    $thisWrk['submissionCount']
    .    '</td>' . "\n"
    .    '<td headers="fb a' . $thisWrk['authId'] . '">'
    .    $thisWrk['feedbackCount']
    .    '</td>' . "\n"
    .    '</tr>' . "\n\n"
    ;
}

echo '</tbody>' . "\n"
.    '</table>' . "\n\n"

.    $workPager->disp_pager_tool_bar($_SERVER['PHP_SELF']."?assigId=".$req['assigmentId']);

include $includePath . '/claro_init_footer.inc.php';


?>