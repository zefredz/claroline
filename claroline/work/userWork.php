<?php // $Id$
/**
 * CLAROLINE
 *
 * @version 1.8 $Revision$
 *
 * @copyright (c) 2001-2007 Universite catholique de Louvain (UCL)
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

$tlabelReq = 'CLWRK';
require '../inc/claro_init_global.inc.php';

if ( ! claro_is_in_a_course() || ! claro_is_course_allowed() ) claro_disp_auth_form(true);

require_once './lib/assignment.class.php';
require_once './lib/submission.class.php';

require_once get_path('incRepositorySys') . '/lib/assignment.lib.php';
include_once get_path('incRepositorySys') . '/lib/fileManage.lib.php';
include_once get_path('incRepositorySys') . '/lib/fileUpload.lib.php';
include_once get_path('incRepositorySys') . '/lib/fileDisplay.lib.php';
include_once get_path('incRepositorySys') . '/lib/learnPath.lib.inc.php';
include_once get_path('incRepositorySys') . '/lib/sendmail.lib.php';

$tbl_mdb_names = claro_sql_get_main_tbl();
$tbl_user      = $tbl_mdb_names['user'];
$tbl_rel_cours_user    = $tbl_mdb_names['rel_course_user'];

$tbl_cdb_names = claro_sql_get_course_tbl();
$tbl_wrk_assignment   = $tbl_cdb_names['wrk_assignment'   ];
$tbl_wrk_submission   = $tbl_cdb_names['wrk_submission'   ];

$tbl_group_team       = $tbl_cdb_names['group_team'       ];
$tbl_group_rel_team_user  = $tbl_cdb_names['group_rel_team_user'];

event_access_tool(claro_get_current_tool_id(), claro_get_current_course_tool_data('label'));

// use viewMode
claro_set_display_mode_available(true);

/*============================================================================
                     BASIC VARIABLES DEFINITION
  =============================================================================*/

$fileAllowedSize = get_conf('max_file_size_per_works') ;    //file size in bytes
$maxFilledSpace  = get_conf('maxFilledSpace',100000000);

// use with strip_tags function when strip_tags is used to check if a text is empty
// but a 'text' with only an image don't have to be considered as empty
$allowedTags = '<img>';

// initialise dialog box to an empty string, all dialog will be concat to it
$dialogBox = '';
// initialise default view mode (values will be overwritten if needed)
$dispWrkLst = true;     // view list is default
$dispWrkForm = false;
$dispWrkDet   = false;
$dispFbkFields = false;
/*============================================================================
                     CLEAN INFORMATIONS SENT BY USER
  =============================================================================*/
$cmd = ( isset($_REQUEST['cmd']) )?$_REQUEST['cmd']:'';

$assignmentId = ( isset($_REQUEST['assigId'])
                    && !empty($_REQUEST['assigId'])
                    && ctype_digit($_REQUEST['assigId'])
                    )
                    ? (int) $_REQUEST['assigId']
                    : false;

/*============================================================================
                          PREREQUISITES
  =============================================================================*/

/*--------------------------------------------------------------------
                REQUIRED : ASSIGNMENT INFORMATIONS
  --------------------------------------------------------------------*/
$assignment = new Assignment();

if ( !$assignmentId || !$assignment->load($assignmentId) )
{
    // we need to know in which assignment we are
    claro_redirect('work.php');
    exit();
}


/*--------------------------------------------------------------------
                    REQUIRED : USER INFORMATIONS
  --------------------------------------------------------------------*/
if( isset($_REQUEST['authId']) && !empty($_REQUEST['authId']) )
{
      if( $assignment->getAssignmentType() == 'GROUP')
    {
        $sql = "SELECT `name`
                FROM `" . $tbl_group_team . "`
                WHERE `id` = " . (int) $_REQUEST['authId'];
        $authField = 'group_id';
    }
    else
    {
        $sql = "SELECT CONCAT(`nom`,' ',`prenom`) as `authName`
                FROM `" . $tbl_user . "`
                WHERE `user_id` = " . (int) $_REQUEST['authId'];
        $authField = 'user_id';
    }
    $authName = claro_sql_query_get_single_value($sql);
}

$_user = claro_get_current_user_data();
$currentUserFirstName       = $_user['firstName'];
$currentUserLastName        = $_user['lastName'];

/*--------------------------------------------------------------------
                    CHECK IF WE HAVE USER AND ASSIGNMENT
  --------------------------------------------------------------------*/
if( empty($authName) )
{
    // we also need a user/group
    claro_redirect("work.php");
    exit();
}

/*--------------------------------------------------------------------
                        WORK INFORMATIONS
  --------------------------------------------------------------------*/

$submission = new Submission();

// if user request a specific submission
if( isset($_REQUEST['wrkId']) && !empty($_REQUEST['wrkId']) )
{
    if( !$submission->load($_REQUEST['wrkId']) )
    {
        unset($cmd);
    }
}


  /*--------------------------------------------------------------------
                        ASSIGNMENT CONTENT
  --------------------------------------------------------------------*/
if( $assignment->getSubmissionType() == "TEXTFILE"
      || ( claro_is_course_manager() && (isset($wrk) && !empty($wrk['original_id']) ) )
      || ( claro_is_course_manager() && ( $cmd == 'rqGradeWrk' || $cmd == 'exGradeWrk') )
  )
{
    // IF text file is the default assignment type
    //    OR this is a teacher modifying a feedback
    //    OR this is a teacher giving feedback to a work
    $assignmentContent = "TEXTFILE";
}
elseif( $assignment->getSubmissionType() == "FILE" )
{
    $assignmentContent = "FILE";
}
else //if( $assignment->getSubmissionType() == "TEXT" )
{
    $assignmentContent = "TEXT";
}
  /*--------------------------------------------------------------------
                        USER GROUP INFORMATIONS
  --------------------------------------------------------------------*/
// if this is a group assignement we will need some group infos about the user
if( $assignment->getAssignmentType() == 'GROUP' && claro_is_user_authenticated() )
{
    // get complete group list
    $sql = "SELECT `id`, `name`
            FROM `" . $tbl_group_team . "`";

    $groupList = claro_sql_query_fetch_all($sql);
    if( is_array($groupList) && !empty($groupList) )
    {
        foreach( $groupList AS $group )
        {
            // yes it is redundant but it is for a easier user later in the script
            $allGroupList[$group['id']]['id'] = $group['id'];
            $allGroupList[$group['id']]['name'] = $group['name'];
        }
    }

    if( claro_is_course_manager() )
    {
        $userGroupList = $allGroupList;
    }
    elseif( !empty($groupList) )
    {
        // get the list of group the user is in (if there is at least one group in course ...)
        $userGroupList = REL_GROUP_USER::get_user_group_list(claro_get_current_user_id());
    }
    else
    {
        $userGroupList = array();
    }
}

/*============================================================================
                          PERMISSIONS
  =============================================================================*/

$assignmentIsVisible = (bool) ( $assignment->getVisibility() == 'VISIBLE' );

// --
$is_allowedToEditAll  = (bool) claro_is_allowed_to_edit(); // can submit, edit, delete

if( !$assignmentIsVisible && !$is_allowedToEditAll )
{
    // if assignment is not visible and user is not course admin or upper
    claro_redirect("work.php");
    exit();
}

// upload or update is allowed between start and end date or after end date if late upload is allowed
$uploadDateIsOk = $assignment->isUploadDateOk();

//-- is_allowedToEdit
// TODO check if submission has feedback

$autoFeedbackIsDisplayedForAuthId = (bool)
                                    ( trim(strip_tags($assignment->getAutoFeedbackText(),$allowedTags)) != '' || $assignment->getAutoFeedbackFilename() != '' )
                                    &&
                                    (
                                        $assignment->getAutoFeedbackSubmitMethod() == 'AFTERPOST' && count($assignment->getSubmissionList($_REQUEST['authId']) > 0)
                                        || ( $assignment->getAutoFeedbackSubmitMethod() == 'ENDDATE' && $assignment->getEndDate() <= time() )
                                    );

// if correction is automatically submitted user cannot edit his work
if( claro_is_user_authenticated() && !$autoFeedbackIsDisplayedForAuthId )
{
    if( $assignment->getAssignmentType() == 'GROUP' && claro_is_in_a_group() )
    {
        $userCanEdit = (bool) ( $submission->getGroupId() == claro_get_current_group_id() );
    }
    elseif( $assignment->getAssignmentType() == 'GROUP' )
    {
        // check if user is in the group that owns the work
        $userCanEdit = ( isset($userGroupList[$submission->getGroupId()]) );
    }
    elseif( $assignment->getAssignmentType() == 'INDIVIDUAL' )
    {
        // a work is set, assignment is individual, user is authed and the work is his work
        $userCanEdit = (bool) ( $submission->getUserId() == claro_get_current_user_id() );
    }
}
else
{
      // user not authed
      // OR a correction has already been made
      $userCanEdit = false;
}

$is_allowedToEdit = (bool)  (  ( $uploadDateIsOk && $userCanEdit ) || $is_allowedToEditAll );

//-- is_allowedToSubmit

if( $assignment->getAssignmentType() == 'INDIVIDUAL' )
{
    // user is authed and allowed
    $userCanPost = (bool) ( claro_is_user_authenticated() && claro_is_course_allowed() );
}
else
{
    $userCanPost = (bool) ( !empty($userGroupList) && isset($userGroupList[$_REQUEST['authId']]) );
}

$is_allowedToSubmit   = (bool) ( $assignmentIsVisible  && $uploadDateIsOk  && $userCanPost ) || $is_allowedToEditAll;


/*============================================================================
                          HANDLING FORM DATA
  =============================================================================*/
// execute this after a form has been send
// this instruction bloc will set some vars that will be used in the corresponding queries
// $wrkForm['filename'] , $wrkForm['wrkTitle'] , $wrkForm['authors'] ...
if( isset($_REQUEST['submitWrk']) )
{

    $formCorrectlySent = true;

    // if authorized_content is TEXT or TEXTFILE, a text is required !
    if( $assignmentContent == "TEXT" || $assignmentContent == "TEXTFILE" )
    {
        if( !isset( $_REQUEST['wrkTxt'] ) || trim( strip_tags( $_REQUEST['wrkTxt'] ), $allowedTags ) == "" )
        {
            $dialogBox .= get_lang('Answer is required')."<br />";
            $formCorrectlySent = false;
            $wrkForm['wrkTxt'] = '';
        }
        else
        {
            $wrkForm['wrkTxt'] = trim($_REQUEST['wrkTxt']);
        }
    }
    elseif( $assignmentContent == "FILE" )
    {
        // if authorized_content is FILE we don't have to check if txt is empty (not required)
        // but we have to check that the text is not only useless html tags
        if( !isset( $_REQUEST['wrkTxt'] ) || trim( strip_tags( $_REQUEST['wrkTxt'], $allowedTags )) == "" )
        {
            $wrkForm['wrkTxt'] = '';
        }
        else
        {
            $wrkForm['wrkTxt'] = trim($_REQUEST['wrkTxt']);
        }
    }


    // check if a title has been given
    if( ! isset($_REQUEST['wrkTitle']) || trim($_REQUEST['wrkTitle']) == "" )
    {
        $dialogBox .= get_lang('Work title required')."<br />";
        $formCorrectlySent = false;
        $wrkForm['wrkTitle'] = '';
    }
    else
    {
        // do not check if a title is already in use, title can be duplicate
        $wrkForm['wrkTitle'] = $_REQUEST['wrkTitle'];
    }


    // check if a author name has been given
    if ( ! isset($_REQUEST['wrkAuthors']) || trim($_REQUEST['wrkAuthors']) == "")
    {
        if( claro_is_user_authenticated() )
        {
            $wrkForm['wrkAuthors'] = $currentUserFirstName." ".$currentUserLastName;
        }
        else
        {
            $dialogBox .= get_lang('Author(s) is(are) required')."<br />";
            $formCorrectlySent = false;
            $wrkForm['wrkAuthors'] = '';
        }
    }
    else
    {
        $wrkForm['wrkAuthors'] = $_REQUEST['wrkAuthors'];
    }


    // check if the score is between 0 and 100
    if ( isset($_REQUEST['wrkScore']) && is_numeric($_REQUEST['wrkScore']) )
    {
        if( $_REQUEST['wrkScore'] < -1 || $_REQUEST['wrkScore'] > 100 )
        {
            $dialogBox .= get_lang('Score required')."<br />";
            $formCorrectlySent = false;
        }
        else
        {
            $wrkForm['wrkScore'] = $_REQUEST['wrkScore'];
        }
    }
    else
    {
        $wrkForm['wrkScore'] = '';
    }


    // check if a group id has been set if this is a group work type
    if( isset($_REQUEST['wrkGroup']) && $assignment->getAssignmentType() == "GROUP" )
    {
        // check that the group id is one of the student
        if( isset($userGroupList[$_REQUEST['wrkGroup']]) )
        {
            $wrkForm['wrkGroup'] = $_REQUEST['wrkGroup'];
        }
        else
        {
            $dialogBox .= get_lang('You are not a member of this group');
            $formCorrectlySent = false;
            $wrkForm['wrkGroup'] = '';
        }
    }
    else
    {
        $wrkForm['wrkGroup'] = '';
    }

    // check if a private feedback has been submitted
    if( isset($_REQUEST['wrkPrivFbk']) && trim(strip_tags($_REQUEST['wrkPrivFbk'], $allowedTags)) != '' )
    {
        $wrkForm['wrkPrivFbk'] = $_REQUEST['wrkPrivFbk'];
    }
    else
    {
        $wrkForm['wrkPrivFbk'] = '';
    }

    // no need to check and/or upload the file if there is already an error
    if($formCorrectlySent)
    {
        $wrkForm['filename'] = '';

        if ( isset($_FILES['wrkFile']['tmp_name'])
                && is_uploaded_file($_FILES['wrkFile']['tmp_name'])
                && $assignmentContent != "TEXT"
            )
        {
            if ($_FILES['wrkFile']['size'] > $fileAllowedSize)
            {
                $dialogBox .= get_lang('You didnt choose any file to send, or it is too big')."<br />";
                $formCorrectlySent = false;
            }
            else
            {
                $newFilename = $_FILES['wrkFile']['name'] . add_extension_for_uploaded_file($_FILES['wrkFile']);

                $newFilename = replace_dangerous_char($newFilename);

                $newFilename = get_secure_file_name($newFilename);

                $wrkForm['filename'] = $assignment->createUniqueFilename($newFilename);


                if( !is_dir( $assignment->getAssigDirSys() ) )
                {
                      claro_mkdir( $assignment->getAssigDirSys() , CLARO_FILE_PERMISSIONS );
                }

                if( move_uploaded_file($_FILES['wrkFile']['tmp_name'], $assignment->getAssigDirSys().$wrkForm['filename']) )
                {
                    chmod($assignment->getAssigDirSys().$wrkForm['filename'],CLARO_FILE_PERMISSIONS);
                }
                else
                {
                    $dialogBox .= get_lang('Cannot copy the file') . '<br />';
                    $formCorrectlySent = false;
                }

                // remove the previous file if there was one
                if( isset($_REQUEST['currentWrkUrl']) )
                {
                      @unlink($assignment->getAssigDirSys().$_REQUEST['currentWrkUrl']);
                }
            }
        }
        elseif( $assignmentContent == "FILE" )
        {
            if( isset($_REQUEST['currentWrkUrl']) )
            {
                // if there was already a file and nothing was provided to replace it, reuse it
                $wrkForm['filename'] = $_REQUEST['currentWrkUrl'];
            }
            elseif( isset($_REQUEST['submitGroupWorkUrl']) )
            {
                $wrkForm['filename'] = $assignment->createUniqueFilename(basename($_REQUEST['submitGroupWorkUrl']). '.url') ;
                $targetOfLink = get_path('coursesRepositorySys') . '/' . claro_get_course_path() . '/' . $_REQUEST['submitGroupWorkUrl'];

                create_link_file($assignment->getAssigDirSys().$wrkForm['filename'], $targetOfLink);
            }
            else
            {
                // if the main thing to provide is a file and that no file was sent
                $dialogBox .= get_lang('A file is required')."<br />";
                $formCorrectlySent = false;
            }
        }
        elseif( $assignmentContent == "TEXTFILE" )
        {
            // attached file is optionnal if work type is TEXT AND FILE
            // so the attached file can be deleted only in this mode
            if( isset($_REQUEST['submitGroupWorkUrl']) )
            {
                $wrkForm['filename'] = $assignment->createUniqueFilename(basename($_REQUEST['submitGroupWorkUrl'])) . '.url';

                create_link_file($assignment->getAssigDirSys().$wrkForm['filename'], get_path('coursesRepositoryWeb') . claro_get_course_path() . '/' . $_REQUEST['submitGroupWorkUrl']);
            }

            // if delete of the file is required
            if(isset($_REQUEST['delAttacheDFile']) )
            {
                $wrkForm['filename'] = ''; // empty DB field
                @unlink($assignment->getAssigDirSys().$_REQUEST['currentWrkUrl']); // physically remove the file
            }
        }
    }// if($formCorrectlySent)

} //end if($_REQUEST['submitWrk'])


/*============================================================================
                          ADMIN ONLY COMMANDS
  =============================================================================*/
if($is_allowedToEditAll)
{
    /*--------------------------------------------------------------------
                        CHANGE VISIBILITY
    --------------------------------------------------------------------*/
    // change visibility of a work
    if( $cmd == 'exChVis' && isset($_REQUEST['wrkId']) )
    {
        if( isset($_REQUEST['vis']) )
        {
            $_REQUEST['vis'] == 'v' ? $visibility = 'VISIBLE' : $visibility = 'INVISIBLE';

            Submission::updateSubmissionVisibility($_REQUEST['wrkId'],$visibility);
        }
    }
    /*--------------------------------------------------------------------
                        DELETE A WORK
    --------------------------------------------------------------------*/
    if( $cmd == "exRmWrk" && isset($_REQUEST['wrkId']) )
    {
        // get name of file to delete AND name of file of the feedback of this work
        $sql = "SELECT `id`, `submitted_doc_path`
                  FROM `".$tbl_wrk_submission."`
                  WHERE `id` = ". (int)$_REQUEST['wrkId']."
                     OR `parent_id` = ". (int)$_REQUEST['wrkId'];

        $filesToDelete = claro_sql_query_fetch_all($sql);

        foreach($filesToDelete as $fileToDelete)
        {
            // delete the file
            @unlink($assignment->getAssigDirSys().$fileToDelete['submitted_doc_path']);

            // delete the database data of this work
            $sqlDelete = "DELETE FROM `".$tbl_wrk_submission."`
                              WHERE `id` = ". (int)$fileToDelete['id'];
            claro_sql_query($sqlDelete);
        }
    }
    /*--------------------------------------------------------------------
                        CORRECTION OF A WORK
    --------------------------------------------------------------------*/
    /*-----------------------------------
            STEP 2 : check & query
    -------------------------------------*/
    if( $cmd == "exGradeWrk" && isset($_REQUEST['gradedWrkId']) )
    {
        if( isset($formCorrectlySent) && $formCorrectlySent )
        {
            $submission->setAssignmentId($assignmentId);
            $submission->setUserId(claro_get_current_user_id());
            $submission->setTitle($wrkForm['wrkTitle']);
            $submission->setAuthor($wrkForm['wrkAuthors']);
            $submission->setVisibility($assignment->getDefaultSubmissionVisibility());
            $submission->setSubmittedText($wrkForm['wrkTxt']);
            $submission->setSubmittedFilename($wrkForm['filename']);

            $submission->setParentId($_REQUEST['gradedWrkId']);
            $submission->setPrivateFeedback($wrkForm['wrkPrivFbk']);
            $submission->setOriginalId($_REQUEST['authId']);
            $submission->setScore($wrkForm['wrkScore']);

            $submission->save();

            $dialogBox .= get_lang('Feedback added');

            // notify eventmanager that a new correction has been posted
            $eventNotifier->notifyCourseEvent('work_correction_posted',claro_get_current_course_id(), claro_get_current_tool_id(), $_REQUEST['gradedWrkId'], '0', '0');
            // mail notification if required by configuration
            if( get_conf('mail_notification') )
            {
                // get owner(s) email
                $userIdList = array();
                if( $assignment->getAssignmentType() == 'GROUP' )
                {
                    $sql = "SELECT `user`
                            FROM `".$tbl_group_rel_team_user."`
                            WHERE `team` = ".(int)$_REQUEST['authId'];

                    $userIdList = claro_sql_query_fetch_all($sql);
                }
                else
                {
                    $userIdList[] = $_REQUEST['authId'];
                }

                if( is_array($userIdList) )
                {
                    // email subject
                    $emailSubject = '[' . get_conf('siteName') . ' - ' . claro_get_current_course_data('officialCode') . '] ' . get_lang('New assignment feedback posted');
                    if( $assignment->getAssignmentType() == 'GROUP' && isset($_REQUEST['wrkGroup']) )
                        $authId = $wrkForm['wrkGroup'];
                    else
                        $authId = $_REQUEST['authId'];

                    $url = get_module_url('CLWRK') .'/userWork.php?authId='.$authId.'&assigId='.$_REQUEST['assigId'].'&cidReq=' . claro_get_current_course_id();
                    // email content
                    $emailBody = get_lang('New assignment feedback posted') . "\n\n"
                    .            $currentUserFirstName.' '.$currentUserLastName . "\n"
                    .             $submission->getTitle() . "\n"
                    .             $url . "\n"
                    ;

                    claro_mail_user($userIdList, $emailBody, $emailSubject);
                }
            }
            // display flags
            $dispWrkLst = true;
        }
        else
        {
            // ask prepare form
            $cmd = "rqGradeWrk";
        }
    }
    /*-----------------------------------
            STEP 1 : prepare form
    -------------------------------------*/
    if( $cmd == "rqGradeWrk" && isset($_REQUEST['gradedWrkId']) )
    {
        $submissionToGrade = new Submission();
        $submissionToGrade->load($_REQUEST['gradedWrkId']);

        // prepare fields
        if( !isset($_REQUEST['submitWrk']) || !$_REQUEST['submitWrk'] )
        {
            // prefill some fields of the form
            $form['wrkTitle'  ] = $submissionToGrade->getTitle()." (".get_lang('Feedback').")";
            $form['wrkAuthors'] = $currentUserLastName." ".$currentUserFirstName;
            $form['wrkTxt'] = '';
            $form['wrkScore'  ] = -1;
            $form['wrkPrivFbk'] = '';
        }
        else
        {
            // there was an error in the form so display it with already modified values
            $form['wrkTitle'] = $_REQUEST['wrkTitle'];
            $form['wrkAuthors'] = $_REQUEST['wrkAuthors'];
            $form['wrkTxt'] = $_REQUEST['wrkTxt'];
            $form['wrkScore'] = $_REQUEST['wrkScore'];
            $form['wrkPrivFbk'] = $_REQUEST['wrkPrivFbk'];
        }

        $cmdToSend = "exGradeWrk";

        $txtForFormTitle = get_lang('Add feedback');
        $isGrade = true;

        // display flags
        $dispWrkLst = false;
        $dispWrkForm = true;
        $dispWrkDet   = true;
        $dispFbkFields = true;
      }
} // if($is_allowedToEditAll)

/*============================================================================
                        ADMIN AND AUTHED USER COMMANDS
  =============================================================================*/
if ( $is_allowedToEdit )
{
    /*--------------------------------------------------------------------
                        EDIT A WORK
    --------------------------------------------------------------------*/
    /*-----------------------------------
            STEP 2 : check & query
    -------------------------------------*/
    if ( $cmd == "exEditWrk" && isset($_REQUEST['wrkId']) )
    {
        // if there is no error update database
        if ( isset($formCorrectlySent) && $formCorrectlySent )
        {
            $submission->setTitle($wrkForm['wrkTitle']);
            $submission->setAuthor($wrkForm['wrkAuthors']);
            $submission->setSubmittedText($wrkForm['wrkTxt']);
            $submission->setSubmittedFilename($wrkForm['filename']);

            if( !empty($wrkForm['wrkPrivFbk']) )     $submission->setPrivateFeedback($wrkForm['wrkPrivFbk']);
            if( !empty($wrkForm['wrkScore']) )         $submission->setScore($wrkForm['wrkScore']);


            if( $assignment->getAssignmentType() == 'GROUP' && isset($wrkForm['wrkGroup']) )
            {
                $submission->setGroupId($wrkForm['wrkGroup']);
            }

            $submission->save();

            $dialogBox .= get_lang('Work modified');

            // display flags
            $dispWrkLst = true;
        }
        else
        {
            // ask prepare form
            $cmd = "rqEditWrk";
        }
    }

    /*-----------------------------------
        STEP 1 : prepare form
    -------------------------------------*/
    if( $cmd == "rqEditWrk" && isset($_REQUEST['wrkId']) )
    {
        // prepare fields
        if( !isset($_REQUEST['submitWrk']) || !$_REQUEST['submitWrk'] )
        {
            // prefill some fields of the form
            $form['wrkTitle'] = $submission->getTitle();
            $form['wrkAuthors'] = $submission->getAuthor();
            $form['wrkGroup'] = $submission->getGroupId();
            $form['wrkTxt'] = $submission->getSubmittedText();
            $form['wrkUrl'] = $submission->getSubmittedFilename();
            $form['wrkPrivFbk'] = $submission->getPrivateFeedback();
            $form['wrkScore'] = $submission->getScore();
        }
        else
        {
              // there was an error in the form so display it with already modified values
            $form['wrkTitle'] = $wrkForm['wrkTitle'];
            $form['wrkAuthors'] = $wrkForm['wrkAuthors'];
            $form['wrkGroup'] = $wrkForm['wrkGroup'];
            $form['wrkTxt'] = $wrkForm['wrkTxt'];
            $form['wrkUrl'] = (isset($_REQUEST['currentWrkUrl']))?$_REQUEST['currentWrkUrl']:'';
            $form['wrkPrivFbk'] = $wrkForm['wrkPrivFbk'];
            $form['wrkScore'] = $wrkForm['wrkScore'];
        }
        $cmdToSend = "exEditWrk";
        // fill the title of the page
        $txtForFormTitle = get_lang('Modify a work');

        // display flags
        $dispWrkLst = false;
        $dispWrkForm  = true;
        // only if this is a correction
        if( !is_null($submission->getOriginalId()) ) $dispFbkFields = true;
    }
}
/*============================================================================
 COMMANDS FOR : ADMIN, AUTHED USERS
  =============================================================================*/
if( $is_allowedToSubmit )
{
    /*--------------------------------------------------------------------
                          SUBMIT A WORK
      --------------------------------------------------------------------*/
    /*-----------------------------------
            STEP 2 : check & quey
      -------------------------------------*/
    if( $cmd == "exSubWrk" )
    {
        if( isset($formCorrectlySent) && $formCorrectlySent )
        {
            $submission->setAssignmentId($assignmentId);
            $submission->setUserId(claro_get_current_user_id());
            $submission->setTitle($wrkForm['wrkTitle']);
            $submission->setAuthor($wrkForm['wrkAuthors']);
            $submission->setVisibility($assignment->getDefaultSubmissionVisibility());
            $submission->setSubmittedText($wrkForm['wrkTxt']);
            $submission->setSubmittedFilename($wrkForm['filename']);

            if( $assignment->getAssignmentType() == 'GROUP' && isset($wrkForm['wrkGroup']) )
            {
                $submission->setGroupId($wrkForm['wrkGroup']);
            }

            $submission->save();

            $dialogBox .= get_lang('Work added');

            // notify eventmanager that a new submission has been posted
            $eventNotifier->notifyCourseEvent("work_submission_posted",claro_get_current_course_id(), claro_get_current_tool_id(), $_REQUEST['assigId'], '0', '0');

            if( get_conf('mail_notification') )
            {
                // get teacher(s) mail
                $sql = "SELECT `U`.`user_id`
                        FROM `".$tbl_rel_cours_user."` AS `CU`,`".$tbl_user."` AS `U`
                        WHERE `CU`.`user_id` = `U`.`user_id`
                        AND `CU`.`code_cours` = '".claro_get_current_course_id()."'
                        AND `CU`.`isCourseManager` = 1
                        AND `U`.`email` IS NOT NULL";

                $userIdList = claro_sql_query_fetch_all($sql);

                if( is_array($userIdList) )
                {
                    if( $assignment->getAssignmentType() == 'GROUP' && isset($_REQUEST['wrkGroup']) )
                        $authId = $wrkForm['wrkGroup'];
                    else
                        $authId = $_REQUEST['authId']; ;

                    // email subject
                    $emailSubject = '[' . get_conf('siteName') . ' - ' . claro_get_current_course_data('officialCode') . '] ' . get_lang('New submission posted in assignment tool.');
                    $url = get_module_url('CLWRK') . '/userWork.php?authId=' . $authId . '&assigId=' . $_REQUEST['assigId']
                    .      '&cidReq=' . claro_get_current_course_id();

                    // email content
                    $emailBody = get_lang('New submission posted in assignment tool.') . "\n\n"
                    .            $_user['firstName'] . ' ' .$_user['lastName'] . "\n"
                    .             $wrkForm['wrkTitle'] . "\n"
                    .             $url . "\n"
                    ;

                    claro_mail_user($userIdList, $emailBody, $emailSubject);
                }
            }

            // display flags
            $dispWrkLst = true;
        }
        else
        {
            // ask prepare form
            $cmd = "rqSubWrk";
        }

    }
  /*-----------------------------------
            STEP 1 : prepare form
  -------------------------------------*/
  if( $cmd == "rqSubWrk" )
  {
      // prepare fields
      if( !isset($_REQUEST['submitWrk']) || !$_REQUEST['submitWrk'] )
      {
            // prefill som fields of the form
            $form['wrkTitle'] = "";
            $form['wrkAuthors'] = $currentUserLastName." ".$currentUserFirstName;
            $form['wrkGroup'] = "";
            $form['wrkTxt'] = "";
      }
      else
      {
            // there was an error in the form so display it with already modified values
            $form['wrkTitle'] = (!empty($_REQUEST['wrkTitle']))?$_REQUEST['wrkTitle']:'';
            $form['wrkAuthors'] = (!empty($_REQUEST['wrkAuthors']))?$_REQUEST['wrkAuthors']:'';
            $form['wrkGroup'] = (!empty($_REQUEST['wrkGroup']))?$_REQUEST['wrkGroup']:'';
            $form['wrkTxt'] = (!empty($_REQUEST['wrkTxt']))?$_REQUEST['wrkTxt']:'';
      }


    // request the form with correct cmd
    $cmdToSend = "exSubWrk";
    // fill the title of the page
    $txtForFormTitle = get_lang('Submit a work');

    // display flags
    $dispWrkLst = false;
    $dispWrkForm  = true;
  }
} // if is_allowedToSubmit

/*============================================================================
                          DISPLAY
  =============================================================================*/
if( !$dispWrkForm && !$dispWrkDet )
{
      // display flags
      $dispWrkLst = true;
}

/*--------------------------------------------------------------------
                    HEADER
    --------------------------------------------------------------------*/

$htmlHeadXtra[] =
'<script type="text/javascript">
function confirmation (name)
{
    if (confirm(" '.clean_str_for_javascript(get_lang('Are you sure to delete')).' "+ name + " ?  " ))
        {return true;}
    else
        {return false;}
}
</script>';

$interbredcrump[]= array ('url' => "../work/work.php", 'name' => get_lang('Assignments'));
$interbredcrump[]= array ('url' => "../work/workList.php?authId=".$_REQUEST['authId']."&amp;assigId=".$_REQUEST['assigId'], 'name' => get_lang('Assignment'));

if( $dispWrkDet || $dispWrkForm )
{
      // bredcrump to return to the list when in a form
      $interbredcrump[]= array ('url' => "../work/userWork.php?authId=".$_REQUEST['authId']."&amp;assigId=".$_REQUEST['assigId'], "name" => $authName);

      // add parameters in query string to prevent the 'refresh' interbredcrump link to display the list of works instead of the form
      $_SERVER['QUERY_STRING'] = "authId=".$_REQUEST['authId']."&amp;assigId=".$_REQUEST['assigId'];
      $_SERVER['QUERY_STRING'] .= (isset($_REQUEST['wrkId']))?"&amp;wrkId=".$_REQUEST['wrkId']:"";
      $_SERVER['QUERY_STRING'] .= "&amp;cmd=".$cmd;
      $nameTools = get_lang('Submission');
}
else
{
      $nameTools = $authName;
      // to prevent parameters to be added in the breadcrumb
      $_SERVER['QUERY_STRING'] = 'authId='.$_REQUEST['authId'].'&amp;assigId='.$_REQUEST['assigId'];
}

include get_path('incRepositorySys') . '/claro_init_header.inc.php';


/*--------------------------------------------------------------------
                    TOOL TITLE
    --------------------------------------------------------------------*/

$pageTitle['mainTitle'] = get_lang('Assignment')." : ".$assignment->getTitle();

if( $assignment->getAssignmentType() == 'GROUP' )
{
    $pageTitle['subTitle'] = get_lang('Group') . ' : ' . $authName . "\n";
    if( $is_allowedToEditAll ) $pageTitle['subTitle'] .=  '<small>(<a href="../group/group_space.php?gidReq='.$_REQUEST['authId'].'">'.get_lang('View group data').'</a>)</small>'."\n";
}
else
{
    $pageTitle['subTitle'] = get_lang('User') . ' : ' . $authName . "\n";
    if( $is_allowedToEditAll ) $pageTitle['subTitle'] .=  '<small>(<a href="../user/userInfo.php?uInfo='.$_REQUEST['authId'].'">'.get_lang('View user data').'</a>)</small>'."\n";
}
echo claro_html_tool_title($pageTitle);

/*--------------------------------------------------------------------
                          FORMS
  --------------------------------------------------------------------*/
if( $is_allowedToSubmit )
{
    if ($dialogBox)
    {
        echo claro_html_message_box($dialogBox);
    }

    if( $dispWrkForm )
    {
            /**
             * ASSIGNMENT INFOS
             */

            echo '<p>' . "\n" . '<small>' . "\n"
            .    '<b>' . get_lang('Title') . '</b> : ' . "\n"
            .    $assignment->getTitle() . '<br />'  . "\n"
            .    get_lang('<b>From</b> %start_date <b>until</b> %end_date',
                   array ( '%start_date' => claro_html_localised_date(get_locale('dateTimeFormatLong'), $assignment->getStartDate()),
                           '%end_date' => claro_html_localised_date(get_locale('dateTimeFormatLong'), $assignment->getEndDate()) ) )
            .    '<br />'  .  "\n"
            .    '<b>' . get_lang('Submission type') . '</b> : ' . "\n";

            if( $assignment->getSubmissionType() == 'TEXT'  )
                echo get_lang('Text only (text required, no file)');
            elseif( $assignment->getSubmissionType() == 'TEXTFILE' )
                echo get_lang('Text with attached file (text required, file optional)');
            else
                echo get_lang('File (file required, description text optional)');


            echo '<br />'  .  "\n"

            .    '<b>' . get_lang('Submission visibility') . '</b> : ' . "\n"
            .    ($assignment->getDefaultSubmissionVisibility() == 'VISIBLE' ? get_lang('Visible for all users') : get_lang('Only visible for teacher(s) and submitter(s)'))

            .    '<br />'  .  "\n"

            .    '<b>' . get_lang('Assignment type') . '</b> : ' . "\n"
            .    ($assignment->getAssignmentType() == 'INDIVIDUAL' ? get_lang('Individual') : get_lang('Groups') )

            .    '<br />'  .  "\n"

            .    '<b>' . get_lang('Allow late upload') . '</b> : ' . "\n"
            .    ($assignment->getAllowLateUpload() == 'YES' ? get_lang('Users can submit after end date') : get_lang('Users can not submit after end date') )

            .    '</small>' . "\n" . '</p>' . "\n";

            // description of assignment
            if( trim($assignment->getDescription()) != '' )
            {
                echo '<b><small>' . get_lang('Description') . '</small></b><br />' . "\n"
                .    '<blockquote>' . "\n" . '<small>' . "\n"
                .    claro_parse_user_text($assignment->getDescription())
                .    '</small>' . "\n" . '</blockquote>' . "\n"
                .    '<br />' . "\n"
                ;
            }

            echo '<h4>'.$txtForFormTitle.'</h4>'."\n"
                  .'<p><small><a href="'.$_SERVER['SCRIPT_NAME'].'?authId='.$_REQUEST['authId'].'&amp;assigId='.$_REQUEST['assigId'].'">&lt;&lt;&nbsp;'.get_lang('Back').'</a></small></p>'."\n"
                  .'<form method="post" action="'.$_SERVER['PHP_SELF'].'?assigId='.$_REQUEST['assigId'].'&amp;authId='.$_REQUEST['authId'].'" enctype="multipart/form-data">'."\n"
                  .'<input type="hidden" name="claroFormId" value="'.uniqid('').'" />'."\n"
                  .'<input type="hidden" name="cmd" value="'.$cmdToSend.'" />'."\n";

            if( isset($_REQUEST['wrkId']) )
            {
                echo '<input type="hidden" name="wrkId" value="'.$_REQUEST['wrkId'].'" />'."\n";
            }
            elseif( isset($_REQUEST['gradedWrkId']) )
            {
                echo '<input type="hidden" name="gradedWrkId" value="'.$_REQUEST['gradedWrkId'].'" />'."\n";
            }

            echo  '<table width="100%">'."\n"
                  .'<tr>'."\n"
                  .'<td valign="top"><label for="wrkTitle">'.get_lang('Title').'&nbsp;*&nbsp;:</label></td>'."\n"
                  .'<td><input type="text" name="wrkTitle" id="wrkTitle" size="50" maxlength="200" value="'.htmlspecialchars($form['wrkTitle']).'" /></td>'."\n"
                  .'</tr>'."\n\n"
                  .'<tr>'."\n"
                  .'<td valign="top"><label for="wrkAuthors">'.get_lang('Author(s)').'&nbsp;*&nbsp;:</label></td>'."\n"
                  .'<td><input type="text" name="wrkAuthors" id="wrkAuthors" size="50" maxlength="200" value="'.htmlspecialchars($form['wrkAuthors']).'" /></td>'."\n"
                  .'</tr>'."\n\n";

            // display the list of groups of the user
            if( $assignment->getAssignmentType() == "GROUP" &&
                    !empty($userGroupList) || (claro_is_course_manager() && claro_is_in_a_group() )
                )
            {
                echo '<tr>'."\n"
                      .'<td valign="top"><label for="wrkGroup">'.get_lang('Group').'&nbsp;:</label></td>'."\n";

                if( claro_is_in_a_group() )
                {
                    echo '<td>'."\n"
                          .'<input type="hidden" name="wrkGroup" value="' . claro_get_current_group_id() . '" />'
                          .claro_get_current_group_data('name')
                          .'</td>'."\n";
                }
                elseif(isset($_REQUEST['authId']) )
                {
                    echo '<td>'."\n"
                          .'<input type="hidden" name="wrkGroup" value="'.$_REQUEST['authId'].'" />'
                          .$userGroupList[$_REQUEST['authId']]['name']
                          .'</td>'."\n";
                }
                else
                {
                    // this part is mainly for courseadmin as he have a link in the workList to submit a work
                    echo '<td>'."\n".'<select name="wrkGroup" id="wrkGroup">'."\n";
                    foreach( $userGroupList as $group )
                    {
                          echo '<option value="'.$group['id'].'"';
                          if( isset($form['wrkGroup']) && $form['wrkGroup'] == $group['id'] || $_REQUEST['authId'] == $group['id'] )
                          {
                                echo 'selected="selected"';
                          }
                          echo '>'.$group['name'].'</option>'."\n";
                    }
                    echo '</select>'."\n"
                          .'</td>'."\n";
                }
                echo '</tr>'."\n\n";
            }

            // display file box
            if( $assignmentContent == "FILE" || $assignmentContent == "TEXTFILE" )
            {
                  // if we are in edit mode and that a file can be edited : display the url of the current file and the file box to change it
                  if( isset($form['wrkUrl']) )
                  {
                        echo '<tr>'."\n"
                              .'<td valign="top">';
                        // display a different text according to the context
                        if( $assignmentContent == "TEXT"  )
                        {
                              // if text is required, file is considered as a an attached document
                              echo get_lang('Current attached file');
                        }
                        else
                        {
                              // if the file is required and the text is only a description of the file
                              echo get_lang('Current file');
                        }
                        if( !empty($form['wrkUrl']) )
                        {
                            $target = ( get_conf('open_submitted_file_in_new_window') ? 'target="_blank"' : '');
                            // display the name of the file, with a link to it, an explanation of what to to to replace it and a checkbox to delete it
                            $completeWrkUrl = $assignment->getAssigDirWeb().$form['wrkUrl'];
                            echo '&nbsp;:<input type="hidden" name="currentWrkUrl" value="'.$form['wrkUrl'].'" />'
                            .     '</td>'."\n"
                            .     '<td>'
                            .     '<a href="'.$completeWrkUrl.'" ' . $target . '>'.$form['wrkUrl'].'</a>'
                            .     '<br />';

                            if( $assignmentContent == "TEXTFILE" )
                            {
                                // we can remove the file only if we are in a TEXTFILE context, in file context the file is required !
                                echo '<input type="checkBox" name="delAttacheDFile" id="delAttachedFile" />' . "\n"
                                .     '<label for="delAttachedFile">'.get_lang('Check this box to delete the attached file').'</label>' . "\n";
                            }
                            echo get_lang('Upload a new file to replace the file').'</td>'."\n"
                            .     '</tr>'."\n\n";
                        }
                        else
                        {
                              echo '&nbsp;:'
                                    .'</td>'."\n"
                                    .'<td>'
                                    .get_lang('- none -')
                                    .'</td>'."\n"
                                    .'</tr>'."\n\n";
                        }
                  }

                echo '<tr>'."\n"
                    .'<td valign="top"><label for="wrkFile">';

                // display a different text according to the context
                if( $assignmentContent == "TEXTFILE" )
                {
                    // if text is required, file is considered as a an attached document
                    echo get_lang('Attach a file');
                }
                else
                {
                    // if the file is required and the text is only a description of the file
                    echo get_lang('Upload document').'&nbsp;*';
                }
                echo '&nbsp;:</label></td>'."\n";
                if( isset($_REQUEST['submitGroupWorkUrl']) && !empty($_REQUEST['submitGroupWorkUrl']) )
                {
                    echo '<td>'
                        .'<input type="hidden" name="submitGroupWorkUrl" value="'.$_REQUEST['submitGroupWorkUrl'].'" />'
                        .'<a href="'.get_path('coursesRepositoryWeb') . claro_get_course_path().'/'.$_REQUEST['submitGroupWorkUrl'].'">'.basename($_REQUEST['submitGroupWorkUrl']).'</a>'
                        .'</td>'."\n";
                }
                else
                {
                  $maxFileSize = min(get_max_upload_size($maxFilledSpace,$assignment->getAssigDirSys()), $fileAllowedSize);

                  echo '<td>' . "\n"
                  .    '<input type="file" name="wrkFile" id="wrkFile" size="30" /><br />'
                  .    '<small>'.get_lang('Max file size : %size', array( '%size' => format_file_size($maxFileSize))).'</small></td>'."\n"
                        .'</tr>'."\n\n";
                }
            }

            if( $assignmentContent == "FILE" )
            {
                  // display standard html textarea
                  // used for description of an uploaded file
                  echo '<tr>'."\n"
                        .'<td valign="top">'
                        .'<label for="wrkTxt">'
                        .get_lang('File description')
                        .'&nbsp;:<br /></label></td>'
                        .'<td>'."\n"
                        .'<textarea name="wrkTxt" cols="40" rows="10">'.$form['wrkTxt'].'</textarea>'
                        .'</td>'."\n"
                        .'</tr>'."\n\n";
            }
            elseif( $assignmentContent == "TEXT" || $assignmentContent == "TEXTFILE" )
            {
                  // display enhanced textarea using claro_html_textarea_editor
                  echo '<tr>'."\n"
                        .'<td valign="top">'
                        .'<label for="wrkTxt">'
                        .get_lang('Answer')
                        .'&nbsp;*&nbsp;:</label></td>'."\n"
                        .'<td>'
                        .claro_html_textarea_editor('wrkTxt', $form['wrkTxt'])
                        .'</td>'."\n"
                        .'</tr>'."\n\n";
            }

            if( $dispFbkFields )
            {
                echo '<tr>'."\n"
                        .'<td valign="top">'
                        .'<label for="wrkPrivFbk">'
                        .get_lang('Private feedback')
                        .'&nbsp;:<br />'
                        .'<small>'.get_lang('Course administrator only').'</small>'
                        .'</label></td>'
                        .'<td>'."\n"
                        .'<textarea name="wrkPrivFbk" cols="40" rows="10">'.$form['wrkPrivFbk'].'</textarea>'
                        .'</td>'."\n"
                        .'</tr>'."\n\n";
                  // if this is a correction we have to add an input for the score/grade/results/points
                  $wrkScoreField = '<select name="wrkScore" id="wrkScore">'."\n"
                                    .'<option value="-1"';
                  // add selected attribute if needed
                  if( $form['wrkScore'] == -1 )
                  {
                        $wrkScoreField .= ' selected="selected"';
                  }
                  $wrkScoreField .= '>'.get_lang('No score').'</option>'."\n";

                  for($i=0;$i <= 100; $i++)
                  {
                        $wrkScoreField .= '<option value="'.$i.'"';
                        if($i == $form['wrkScore'])
                        {
                            $wrkScoreField .= ' selected="selected"';
                        }
                        $wrkScoreField .= '>'.$i.'</option>'."\n";
                  }
                  $wrkScoreField .= '</select> %';
                  echo '<tr>'."\n"
                        .'<td valign="top"><label for="wrkScore">'.get_lang('Score').'&nbsp;&nbsp;:</label></td>'."\n"
                        .'<td>'
                        .$wrkScoreField
                        .'</td>'
                        .'</tr>'."\n\n";
            }

            echo '<tr>'."\n"
                    .'<td>&nbsp;</td>'."\n"
                    .'<td>'
                    .'<input type="submit" name="submitWrk" value="'.get_lang('Ok').'" />'."\n"
                    .'</td>'."\n"
                    .'</tr>'."\n\n"
                    .'</table>'."\n\n"
                    .'</form>'
                    .'<small>* : '.get_lang('Required').'</small>';
      }
}


/*--------------------------------------------------------------------
                          SUBMISSION LIST
  --------------------------------------------------------------------*/
if( $dispWrkLst )
{
    $showOnlyAuthorCondition = '';
    if( get_conf('show_only_author') && !$is_allowedToEditAll )
    {
        // security check to avoid a user to see others submissions if not permitted
        if( $assignment->getAssignmentType() == 'GROUP' && !isset($userGroupList[$_REQUEST['authId']]) )
        {
            if( ! isset($userGroupIdList) )
            {
                $userGroupIdList = array();
                foreach( $userGroupList as $userGroup )
                {
                    $userGroupIdList[] = $userGroup['id'];
                }
            }
            $showOnlyAuthorCondition = "AND `".$authField."` IN (".implode(',',$userGroupIdList).")";
        }
        elseif( $assignment->getAssignmentType() == 'INDIVIDUAL' && claro_get_current_user_id() != $_REQUEST['authId'] )
        {
            $showOnlyAuthorCondition = "AND `".$authField."` = ". (int)claro_get_current_user_id();
        }
    }


    // select all submissions from this user in this assignment (not feedbacks !)
    // TODO  * would be replace by fieldnames
    $sql = "SELECT *,
                UNIX_TIMESTAMP(`creation_date`) AS `unix_creation_date`,
                UNIX_TIMESTAMP(`last_edit_date`) as `unix_last_edit_date`
            FROM `".$tbl_wrk_submission."`
            WHERE `".$authField."` = ". (int)$_REQUEST['authId']."
                AND `original_id` IS NULL
                AND `assignment_id` = ".(int)$_REQUEST['assigId']."
                ". $showOnlyAuthorCondition . "
            ORDER BY `last_edit_date` ASC";

    $wrkLst = claro_sql_query_fetch_all($sql);

    // build 'parent_id' condition
    $parentCondition = ' ';
    foreach( $wrkLst as $thisWrk )
    {
        $parentCondition .= " OR `parent_id` = ". (int) $thisWrk['id'];
    }
    // select all feedback relating to the user submission in this assignment
    // TODO  * would be replace by fieldnames
    $sql = "SELECT *,
                UNIX_TIMESTAMP(`creation_date`) AS `unix_creation_date`,
                UNIX_TIMESTAMP(`last_edit_date`) as `unix_last_edit_date`
            FROM `".$tbl_wrk_submission."`
            WHERE 0 = 1
                AND `assignment_id` = ". (int) $_REQUEST['assigId'] . "
                " . $parentCondition;

    $feedbackLst = claro_sql_query_fetch_all($sql);

    $wrkAndFeedbackLst = array();
    // create an ordered list with all submission directly followed by the related correction(s)
    foreach( $wrkLst as $thisWrk )
    {
        $is_allowedToViewThisWrk = (bool)$is_allowedToEditAll || $thisWrk['user_id'] == claro_get_current_user_id() || isset($userGroupList[$thisWrk['group_id']]);

        if( $thisWrk['visibility'] == 'VISIBLE' || $is_allowedToViewThisWrk )
        {
            $wrkAndFeedbackLst[] = $thisWrk;
            foreach( $feedbackLst as $feedback )
            {
                if( $feedback['parent_id'] == $thisWrk['id']
                    && ( $feedback['visibility'] == 'VISIBLE' || $is_allowedToEditAll || $is_allowedToViewThisWrk )
                    )
                {
                    $wrkAndFeedbackLst[] = $feedback;
                }
            }
        }
    }

    if( isset($userGroupList[$_REQUEST['authId']]) || ($_REQUEST['authId'] == claro_get_current_user_id() && $is_allowedToSubmit) || $is_allowedToEditAll )
    {
        // link to create a new submission
        $cmdMenu = array();
        $cmdMenu[] = claro_html_cmd_link( $_SERVER['PHP_SELF']
                                        . '?authId=' . $_REQUEST['authId']
                                        . '&amp;assigId=' . $_REQUEST['assigId']
                                        . '&amp;cmd=rqSubWrk'
                                        . claro_url_relay_context('&amp;')
                                        , get_lang('Submit a work')
                                        );

        echo '<p>' . claro_html_menu_horizontal($cmdMenu) . '</p>' . "\n";
    }

    if( is_array($wrkAndFeedbackLst) && count($wrkAndFeedbackLst) > 0  )
    {
        $i = 0;
        foreach ( $wrkAndFeedbackLst as $thisWrk )
        {
            $is_feedback = !is_null($thisWrk['original_id']) && !empty($thisWrk['original_id']);

            $has_feedback =     !$is_feedback
                            &&     ( $autoFeedbackIsDisplayedForAuthId
                                ||     (isset($wrkAndFeedbackLst[$i+1]) && $wrkAndFeedbackLst[$i+1]['parent_id'] == $thisWrk['id'])
                                );

            $is_allowedToViewThisWrk = (bool)$is_allowedToEditAll || $thisWrk['user_id'] == claro_get_current_user_id() || isset($userGroupList[$thisWrk['group_id']]);


            $is_allowedToEditThisWrk =
                (bool) $is_allowedToEditAll
                || ( $is_allowedToViewThisWrk
                    && $uploadDateIsOk
                    && !$has_feedback
                    )
                ;

            if( $thisWrk['visibility'] == "INVISIBLE" && $is_allowedToEditAll )    $visStyle = ' class="invisible"';
            else                                                                $visStyle = '';

            if( $is_feedback )  $feedbackStyle = 'style="padding-left: 35px;"';
            else                $feedbackStyle = '';

            // change some displayed text depending on the context
            if( $assignmentContent == "TEXTFILE" || $is_feedback )
            {
                $txtForFile = get_lang('Attached file');
                if( $is_feedback )    $txtForText = get_lang('Public feedback');
                else                $txtForText = get_lang('Answer');
            }
            elseif( $assignmentContent == "TEXT" )
            {
                $txtForText = get_lang('Answer');
            }
            elseif( $assignmentContent == "FILE" )
            {
                $txtForFile = get_lang('Uploaded file');
                $txtForText = get_lang('File description');
            }

            if( !$is_feedback ) echo '<hr />';
            // title (and edit links)
            echo '<h3' . $visStyle . ' ' . $feedbackStyle . '>' . "\n"
            .    $thisWrk['title'] . "\n"
            .    '</h3>' . "\n"
            .    '<div' . $visStyle . ' ' . $feedbackStyle . '>' . "\n"
            ;

            // author
            echo get_lang('Author(s)') . '&nbsp;: ' . $thisWrk['authors'] . '<br />' . "\n";

            if( $assignment->getAssignmentType() == 'GROUP' && claro_is_user_authenticated() && !$is_feedback )
            {
                 // display group if this is a group assignment and if this is not a correction
                 echo get_lang('Group') . '&nbsp;: ' . $allGroupList[$thisWrk['group_id']]['name'].'<br />' . "\n";
            }

            if( $assignmentContent != 'TEXT' )
            {
                if( !empty($thisWrk['submitted_doc_path']) )
                {
                    $target = ( get_conf('open_submitted_file_in_new_window') ? 'target="_blank"' : '');
                    // show file if this is not a TEXT only work
                    echo $txtForFile . '&nbsp;: '
                    .    '<a href="' . $assignment->getAssigDirWeb().urlencode($thisWrk['submitted_doc_path']) . '" ' . $target . '>' . $thisWrk['submitted_doc_path'] . '</a>'
                    . ' <small>(' . format_file_size(claro_get_file_size($assignment->getAssigDirSys().$thisWrk['submitted_doc_path'])) . ')</small>'
                    .    '<br />' . "\n"
                    ;
                }
                else
                {
                     echo $txtForFile . '&nbsp;: '
                     .     get_lang('- none -')
                     .    '<br />' . "\n"
                     ;
                }
            }

            echo '<br />' . "\n"
            .    $txtForText . '&nbsp;: ' . '<br />' . "\n"
            .    '<blockquote>' . $thisWrk['submitted_text'] . '</blockquote>' . "\n"
            ;

            if( $is_feedback )
            {
                if( $is_allowedToEditAll )
                {
                    echo '<br />' . "\n"
                    .    '<div>'
                    .    get_lang('Private feedback') . '&nbsp;:<br />'
                    .    '<blockquote>' . $thisWrk['private_feedback'] . '</blockquote>' . "\n"
                    .    '</div>' . "\n"
                    ;
                }
                echo '<br />' . "\n" . get_lang('Score') . '&nbsp;: '
                .     ( ( $thisWrk['score'] == -1 ) ? get_lang('No score') : $thisWrk['score'].' %' )
                .     '<br />' . "\n";
            }
            echo '<p>' . get_lang('First submission date') . '&nbsp;: '
            .    claro_html_localised_date(get_locale('dateTimeFormatLong'), $thisWrk['unix_creation_date'])
            ;

            // display an alert if work was submitted after end date and work is not a correction !
            if( $assignment->getEndDate() < $thisWrk['unix_creation_date'] && !$is_feedback )
            {
                  echo ' <img src="' . get_path('imgRepositoryWeb') . 'caution.gif" border="0" alt="'.get_lang('Late upload').'" />';
            }
            echo '<br />' . "\n";

            if( $thisWrk['unix_creation_date'] != $thisWrk['unix_last_edit_date'] )
            {
                echo get_lang('Last edit date').'&nbsp;: '
                    .claro_html_localised_date(get_locale('dateTimeFormatLong'), $thisWrk['unix_last_edit_date']);
                // display an alert if work was submitted after end date and work is not a correction !
                if( $assignment->getEndDate() < $thisWrk['unix_last_edit_date'] && !$is_feedback )
                {
                    echo ' <img src="' . get_path('imgRepositoryWeb') . 'caution.gif" border="0" alt="'.get_lang('Late upload').'" />';
                }
            }
            echo '</p>'."\n";
            // if user is allowed to edit, display the link to edit it
            if( $is_allowedToEditThisWrk )
                {
                    // the work can be edited
                    echo '<a href="' . $_SERVER['PHP_SELF']
                    .    '?authId=' . $_REQUEST['authId']
                    .    '&amp;assigId='.$_REQUEST['assigId']
                    .    '&amp;cmd=rqEditWrk&amp;wrkId=' . $thisWrk['id'] . '">'
                    .    '<img src="' . get_path('imgRepositoryWeb') . 'edit.gif" border="0" alt="'.get_lang('Modify').'" />'
                    .    '</a>'
                    ;
                }

                if( $is_allowedToEditAll )
                {
                    echo '<a href="' . $_SERVER['PHP_SELF']
                    .    '?authId='.$_REQUEST['authId']
                    .    '&amp;cmd=exRmWrk&amp;assigId=' . $_REQUEST['assigId']
                    .    '&amp;wrkId=' . $thisWrk['id'] . '" '
                    .    'onClick="return confirmation(\'' . clean_str_for_javascript($thisWrk['title']) . '\');">'
                    .    '<img src="' . get_path('imgRepositoryWeb') . 'delete.gif" border="0" alt="'.get_lang('Delete').'" />'
                    .    '</a>'
                    ;

                if ($thisWrk['visibility'] == "INVISIBLE")
                {
                    echo '<a href="' . $_SERVER['PHP_SELF']
                    .    '?authId=' . $_REQUEST['authId']
                    .    '&amp;cmd=exChVis&amp;assigId='.$_REQUEST['assigId']
                    .    '&amp;wrkId='.$thisWrk['id']
                    .    '&amp;vis=v">'
                    .    '<img src="' . get_path('imgRepositoryWeb') . 'invisible.gif" border="0" alt="' . get_lang('Make visible') . '" />'
                    .    '</a>'
                    ;
                }
                else
                {
                    echo '<a href="' . $_SERVER['PHP_SELF']
                    .    '?authId=' . $_REQUEST['authId']
                    .    '&amp;cmd=exChVis&amp;assigId=' . $_REQUEST['assigId']
                    .    '&amp;wrkId='.$thisWrk['id']
                    .    '&amp;vis=i">'
                    .    '<img src="' . get_path('imgRepositoryWeb') . 'visible.gif" border="0" alt="' . get_lang('Make invisible') . '" />'
                    .    '</a>'
                    ;
                }
                if( !$is_feedback )
                {
                    // if there is no correction yet show the link to add a correction if user is course admin
                    echo '&nbsp;'
                    .    '<a href="' . $_SERVER['PHP_SELF']
                    .    '?authId=' . $_REQUEST['authId']
                    .    '&amp;assigId=' . $_REQUEST['assigId']
                    .    '&amp;cmd=rqGradeWrk&amp;gradedWrkId='.$thisWrk['id'] . '">'
                    .    get_lang('Add feedback')
                    .    '</a>'
                    ;
                }
            }

            echo '</div>' . "\n"
            .    '<br />' . "\n"
            ;

            $i++;
        }
    }
    else
    {
        echo claro_html_message_box(get_lang('No visible submission'));
    }
}
// FOOTER
include get_path('incRepositorySys') . '/claro_init_footer.inc.php';
?>
