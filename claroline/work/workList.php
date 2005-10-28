<?php // $Id$
/**
 * CLAROLINE
 *
 * @version 1.7 $Revision$
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

include_once($includePath . '/lib/fileManage.lib.php');
include_once($includePath . '/lib/pager.lib.php');

$tbl_mdb_names = claro_sql_get_main_tbl();
$tbl_user                = $tbl_mdb_names['user'];
$tbl_rel_course_user     = $tbl_mdb_names['rel_course_user'];

$tbl_cdb_names = claro_sql_get_course_tbl();
$tbl_wrk_assignment      = $tbl_cdb_names['wrk_assignment'   ];
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

$fileAllowedSize     = $max_file_size_per_works ;    //file size in bytes (from config file)
$wrkDirSys          = $currentCourseRepositorySys . 'work/'; // systeme work directory
$wrkDirWeb          = $currentCourseRepositoryWeb  .'work/'; // web work directory
$maxFilledSpace     = 100000000;

// initialise dialog box to an empty string, all dialog will be concat to it
$dialogBox = '';

/*============================================================================
                     CLEAN INFORMATIONS SEND BY USER
  =============================================================================*/

$cmd = ( isset($_REQUEST['cmd']) )?$_REQUEST['cmd']:'';

/*============================================================================
                          PREREQUISITES
  =============================================================================*/

/*--------------------------------------------------------------------
                ASSIGNMENT INFORMATIONS
  --------------------------------------------------------------------*/

if ( isset($_REQUEST['assigId']) && !empty($_REQUEST['assigId']) )
{
      // we need to know the assignment settings
      $sql = "SELECT *,
                UNIX_TIMESTAMP(`start_date`) AS `unix_start_date`,
                UNIX_TIMESTAMP(`end_date`) AS `unix_end_date`
                FROM `" . $tbl_wrk_assignment . "`
                WHERE `id` = " . (int) $_REQUEST['assigId'];
      
      list($assignment) = claro_sql_query_fetch_all($sql);
      
      $assigDirSys = $wrkDirSys . 'assig_' . $_REQUEST['assigId'] . '/';
      $assigDirWeb = $wrkDirWeb . 'assig_' . $_REQUEST['assigId'] . '/';
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
if ( isset($_REQUEST['submitGroupWorkUrl']) && !empty($_REQUEST['submitGroupWorkUrl']) && isset($_gid) )
{
    header( 'Location: userWork.php?authId='
          . $_gid 
          . '&cmd=rqSubWrk'
          . '&assigId=' . $_REQUEST['assigId']
          . '&submitGroupWorkUrl=' . urlencode($_REQUEST['submitGroupWorkUrl'])
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
	$sql = "SELECT `tu`.`team`, `t`.`name`
	        FROM `".$tbl_group_rel_team_user."` as `tu`, `".$tbl_group_team."` as `t`
	       WHERE `tu`.`user` = " . (int) $_uid . "
	         AND `tu`.`team` = `t`.`id`";
	$groupList = claro_sql_query_fetch_all($sql);
	if( is_array($groupList) && !empty($groupList) )
	{
		foreach( $groupList AS $group )
		{
			// yes it is redundant but it is for a easier user later in the script
			$userGroupList[$group['team']]['id'] = $group['team'];
			$userGroupList[$group['team']]['name'] = $group['name'];
		}
 	}
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
	header("Location: work.php");
	exit();
}

// upload or update is allowed between start and end date or after end date if late upload is allowed
$uploadDateIsOk      = (bool) (  $afterStartDate
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
    $userCanPost = ( isset($userGroupList) && count($userGroupList) <= 0 )?false:true;
}

$is_allowedToSubmit   = (bool) ( $assignmentIsVisible  && $uploadDateIsOk  && $userCanPost ) || $is_allowedToEditAll;
/*-------------------------------------------
                    HEADER
---------------------------------------------*/

if(isset($_gid))
{
    $interbredcrump[]= array ('url' => '../group/group.php', 'name' => $langGroup);
    $interbredcrump[]= array ('url' => '../group/group_space.php', 'name' => $langGroupSpace);
}

$interbredcrump[]= array ('url' => '../work/work.php', 'name' => $langWork);

$nameTools = $langAssignment;
// to prevent parameters to be added in the breadcrumb
$_SERVER['QUERY_STRING'] = 'assigId='.$_REQUEST['assigId']; 

include($includePath . '/claro_init_header.inc.php');
  
/*--------------------------------------------------------------------
                    TOOL TITLE
  --------------------------------------------------------------------*/

$pageTitle['mainTitle'  ] = $nameTools;
$pageTitle['subTitle'   ] = $assignment['title'];
echo claro_disp_tool_title($pageTitle);

/*--------------------------------------------------------------------
                          ASSIGNMENT INFOS
  --------------------------------------------------------------------*/
  
// end date
echo '<p>' . "\n"
.    '<b>' . $langEndDate . '</b><br />' . "\n"
.    claro_disp_localised_date($dateTimeFormatLong, $assignment['unix_end_date'])
.    '</p>' . "\n"
;    
// description of assignment
if( !empty($assignment['description']) )
{
    echo '<div>' . "\n"
    .    '<b>' . $langAssignmentDescription . '</b>'
    .    '<br />'
    .    claro_parse_user_text($assignment['description'])
    .    '</div>' . "\n"
    .    '<br />' . "\n"
    ;
}
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
if( !isset($_uid) )
{
    // do not show to anonymous users because we can't know 
    // if the user already uploaded a work
    $showAfterPost = false;
}
else
{
    $sql = "SELECT count(`id`)
                 FROM `".$tbl_wrk_submission."`
                WHERE `user_id` = ". (int)$_uid."
                  AND `assignment_id` = ". (int)$_REQUEST['assigId'];
      $nbrWorksOfUser = claro_sql_query_get_single_value($sql);
      
      $showAfterPost = (bool) (  $assignment['prefill_submit'] == 'AFTERPOST' 
                              && $nbrWorksOfUser >= 1 
                              );
}

// show to authenticated and anonymous users

if( $textOrFilePresent &&  ( $showAfterEndDate || $showAfterPost ) )
{
    echo '<fieldset>' . "\n"
    .    '<legend>'
    .    '<b>' . $langFeedback . '</b>'
    .    '</legend>'
    ;

    if( !empty($assignment['prefill_text']) )
    {
        echo claro_parse_user_text($assignment['prefill_text']);
    }

    if(  !empty($assignment['prefill_doc_path']) 
      && !empty($assignment['prefill_text']) )
    {
        echo  "<p><a href=\"".$assigDirWeb.$assignment['prefill_doc_path']."\">".$assignment['prefill_doc_path']."</a></p>";
    }
    elseif( !empty($assignment['prefill_doc_path']) )
    {
        echo  '<a href="' . $assigDirWeb . $assignment['prefill_doc_path'] . '">'
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
            $checkVisible .= " OR `group_id` = ". (int)$userGroup['id'];
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
        $checkVisible = " ";
    elseif( isset($_uid) )
        $checkVisible = " AND (`S`.`visibility` = 'VISIBLE' OR `S`.`user_id` = ". (int)$_uid.") ";
    else
        $checkVisible = " AND `S`.`visibility` = 'VISIBLE' ";
        
    $sql = "SELECT `U`.`user_id` as `authId`, concat(`U`.`nom`, ' ', `U`.`prenom`) as `name`, 
            count(`S`.`id`) as `submissionCount`, `S`.`title`, MIN(`S`.`creation_date`)
        FROM `".$tbl_user."` as `U`, `".$tbl_rel_course_user."` as `CU`
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
                    OR `S2`.`user_id` = ". (int)$_uid.") ";
}
elseif( isset($userGroupList) )
{
    // work and his feedback must be visible OR the user is member of concerned group
    $checkVisible = " AND ( (`S`.`visibility` = 'VISIBLE' 
                    AND `S2`.`visibility` = 'VISIBLE') ";
    foreach( $userGroupList as $userGroup )
    {
        $checkVisible .= " OR `S2`.`group_id` = ". (int)$userGroup['id'];
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
    .    $langSubmitWork
    .    '</a>' . "\n"
    ;
    
    if( $is_allowedToEditAll ) echo ' | ';
}

if ( $is_allowedToEditAll )
{
    echo '<a class="claroCmd" href="feedback.php'
    .    '?cmd=rqEditFeedback'
    .    '&amp;assigId=' . $assignment['id'] . '">'
    .    $langEditFeedback
    .    '</a>' . "\n"
    ;
}
echo '</p>';
$workPager->disp_pager_tool_bar($_SERVER['PHP_SELF']."?assigId=".$_REQUEST['assigId']);
/*--------------------------------------------------------------------
                                LIST
  --------------------------------------------------------------------*/
echo '<table class="claroTable emphaseLine" width="100%">' . "\n"
.    '<thead>' . "\n"
.    '<tr class="headerX">' . "\n"
.    '<th>' . $langWrkAuthors . '</th>' . "\n"
.    '<th>' . $langFirstSubmission . '</th>' . "\n"
.    '<th>' . $langSubmissions . '</th>' . "\n"
.    '<th>' . $langFeedbacks . '</th>' . "\n"
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

// FOOTER
include $includePath . '/claro_init_footer.inc.php'; 
?>
