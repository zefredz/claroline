<?php // $Id$

//----------------------------------------------------------------------
// CLAROLINE 1.6
//----------------------------------------------------------------------
// Copyright (c) 2001-2004 Universite catholique de Louvain (UCL)
//----------------------------------------------------------------------
// This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
// as published by the FREE SOFTWARE FOUNDATION. The GPL is available 
// through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
//----------------------------------------------------------------------
// Authors: see 'credits' file
//----------------------------------------------------------------------

$langFile = "work";

$tlabelReq = "CLWRK___";
require '../inc/claro_init_global.inc.php';

include($includePath.'/lib/events.lib.inc.php');
include($includePath.'/conf/work.conf.inc.php');

$tbl_mdb_names = claro_sql_get_main_tbl();
$tbl_user            = $tbl_mdb_names['user'];

$tbl_cdb_names = claro_sql_get_course_tbl();
$tbl_wrk_assignment   = $tbl_cdb_names['wrk_assignment'   ];
$tbl_wrk_submission   = $tbl_cdb_names['wrk_submission'   ];    

$tbl_group_team       = $tbl_cdb_names['group_team'       ];


$currentUserFirstName       = $_user['firstName'];
$currentUserLastName        = $_user['lastName'];


//if (!$_cid) 	claro_disp_select_course();

if ( ! $is_courseAllowed)
	claro_disp_auth_form();
event_access_tool($_tid, $_SESSION['_courseTool']['label']);



include($includePath."/lib/fileUpload.lib.php");
include($includePath."/lib/fileDisplay.lib.php"); // need format_url function
include($includePath."/lib/learnPath.lib.inc.php");



/*============================================================================
                     BASIC VARIABLES DEFINITION
  =============================================================================*/
$currentCourseRepositorySys = $coursesRepositorySys.$_course["path"]."/";
$currentCourseRepositoryWeb = $coursesRepositoryWeb.$_course["path"]."/";

$fileAllowedSize = CONFVAL_MAX_FILE_SIZE_PER_WORKS ;    //file size in bytes
$wrkDirSys          = $currentCourseRepositorySys."work/"; // systeme work directory
$wrkDirWeb          = $currentCourseRepositoryWeb."work/"; // web work directory

// use with strip_tags function when strip_tags is used to check if a text is empty
// but a 'text' with only an image don't have to be considered as empty 
$allowedTags = '<img>';
/*============================================================================
                     CLEAN INFORMATIONS SEND BY USER
  =============================================================================*/
stripSubmitValue($HTTP_POST_VARS);
stripSubmitValue($HTTP_GET_VARS);
stripSubmitValue($_REQUEST);

$cmd = $_REQUEST['cmd'];

/*============================================================================
                          PREREQUISITES
  =============================================================================*/

  /*--------------------------------------------------------------------
                  ASSIGNMENT INFORMATIONS
  --------------------------------------------------------------------*/
if( isset($_REQUEST['assigId']) && !empty($_REQUEST['assigId']) )
{
      // we need to know the assignment settings
      $sql = "SELECT *,
                UNIX_TIMESTAMP(`start_date`) AS `unix_start_date`,
                UNIX_TIMESTAMP(`end_date`) AS `unix_end_date`
                FROM `".$tbl_wrk_assignment."`
                WHERE `id` = ".$_REQUEST['assigId'];
      
      list($assignment) = claro_sql_query_fetch_all($sql);
      
      $assigDirSys = $wrkDirSys."assig_".$_REQUEST['assigId']."/";
      $assigDirWeb = $wrkDirWeb."assig_".$_REQUEST['assigId']."/";
}

// assignment not requested or not found
if( is_null($assignment) )
{
      // we NEED to know in which assignment we are, so if assigId is not set
      // relocate the user to the previous page
      header("Location: work.php");
}


  /*--------------------------------------------------------------------
                        WORK INFORMATIONS
  --------------------------------------------------------------------*/
if( isset($_REQUEST['wrkId']) && !empty($_REQUEST['wrkId']) )
{
      // we need to know the settings of the work asked to 
      //  - know if the user has the right to edit
      //  - prefill the form in edit mode
      $sql = "SELECT *, 
                  UNIX_TIMESTAMP(`creation_date`) AS `unix_creation_date`,
                  UNIX_TIMESTAMP(`last_edit_date`) AS `unix_last_edit_date`                  
                  FROM `".$tbl_wrk_submission."`
                  WHERE `id` = ".$_REQUEST['wrkId'];

      list($wrk) = claro_sql_query_fetch_all($sql);
}

// if a command is requested, that work was not requested or requested and not found
// and that this is not a creation command
if( isset($cmd) && $cmd != 'rqSubWrk' && $cmd != 'exSubWrk' && is_null($wrk))
{
      // unset cmd so that it will display the list of submissions
      unset($cmd);
}
  /*--------------------------------------------------------------------
                        ASSIGNMENT CONTENT
  --------------------------------------------------------------------*/
if( $assignment['authorized_content'] == "TEXTFILE" 
      || ( $is_courseAdmin && !empty($wrk['parent_id']) ) 
      || ( $is_courseAdmin && ( $cmd == 'rqGradeWrk' || $cmd == 'exGradeWrk') )
  )
{
      // IF text file is the default assignment type
      // OR this is a teacher modifying a feedback
      // OR this is a teacher feedback a work
      $assignmentContent = "TEXTFILE";
}
elseif( $assignment['authorized_content'] == "FILE" )
{
      $assignmentContent = "FILE";
}
else //if( $assignment['authorized_content'] == "TEXT" )
{
      $assignmentContent = "TEXT";
}


/*============================================================================
                          PERMISSIONS
  =============================================================================*/

// is between start and end date or after and date and late upload is allowed
if( $assignment['unix_start_date'] <= time() )
{
      $afterStartDate = true;
}
else
{
      $afterStartDate = false;
}
// assignment is invisible 
if( $assignment['visibility'] == "VISIBLE" )
{
      $assignmentIsVisible = true;
}
else
{
      $assignmentIsVisible = false;
}

//  anonymous post are allowed
if( $assignment['authorize_anonymous'] == "YES" )
{
      $anonCanPost = true;
}
else
{
      $anonCanPost = false;
}

// 3 rights levels 

// can make everything : submit, edit, delete
// IF course admin or platform admin
$is_allowedToEditAll  = (bool) $is_courseAdmin;


//-- is_allowedToEdit
// upload or update is allowed between start and end date or after end date if late upload is allowed
$uploadDateIsOk = (bool) $afterStartDate 
                              && ( time() < $assignment['unix_end_date'] || $assignment['allow_late_upload'] == "YES" );
                              
// a work is set, user is authed and the work is his work
$userCanEdit = (bool) ( isset($wrk) && isset($_uid) && $wrk['user_id'] == $_uid );

$is_allowedToEdit = (bool)    ( $assignmentIsVisible && $uploadDateIsOk && $userCanEdit )
                              || $is_allowedToEditAll;


//-- is_allowedToSubmit

// user is anonyme , anonymous users can post and user is course allowed or user is authed and allowed
$userCanPost = (bool) ( !isset($_uid) && $anonCanPost && $is_courseAllowed ) 
                  || ( isset($_uid) && $is_courseAllowed );

$is_allowedToSubmit   = (bool) ( $assignmentIsVisible  && $uploadDateIsOk  && $userCanPost )
                                    || $is_allowedToEdit
                                    || $is_allowedToEditAll;
                     
//-- is_allowedToView                     
// allowed to display work list and work details                     
$is_allowedToView = (bool) ($assignmentIsVisible && $afterStartDate) || $is_allowedToEditAll;

/*============================================================================
                          HANDLING FORM DATA
  =============================================================================*/
// execute this after a form has been send
// this instruction bloc will set some vars that will be used in the corresponding queries
// $wrkForm['fileName'] , $wrkForm['title'] , $wrkForm['authors']
if( isset($_REQUEST['submitWrk']) ) 
{

      $formCorrectlySent = true;
      
      // if authorized_content is TEXT or TEXTFILE, a text is required !
      if( $assignmentContent == "TEXT" || $assignmentContent == "TEXTFILE" )
      {
            if( !isset( $_REQUEST['wrkTxt'] ) || trim( strip_tags( $_REQUEST['wrkTxt'] ), $allowedTags ) == "" )
            {
                  $dialogBox .= $langAnswerRequired."<br />";
                  $formCorrectlySent = false;
            }
            else
            {
                  $submittedText = trim(claro_addslashes( $_REQUEST['wrkTxt'] ));
            }
      }
      elseif( $assignmentContent == "FILE" )
      {
            // if authorized_content is FILE we don't have to check if txt is empty (not required)
            // but we have to check that the text is not only useless html tags
            if( !isset( $_REQUEST['wrkTxt'] ) || trim( strip_tags( $_REQUEST['wrkTxt'] ), $allowedTags ) == "" )
            {
                  $submittedText = "";
            }
            else
            {
                  $submittedText = trim(claro_addslashes( $_REQUEST['wrkTxt'] ));
            }
      }
      
      // check if a title has been given
      if( ! isset($_REQUEST['wrkTitle']) || trim(claro_addslashes($_REQUEST['wrkTitle'])) == "" )
      {
        $dialogBox .= $langWrkTitleRequired."<br />";
        $formCorrectlySent = false;
      }
      else
      {
        // check if the name is already in use
        $sql = "SELECT `title` 
                  FROM `".$tbl_wrk_submission."`
                  WHERE `title` = \"".trim(claro_addslashes($_REQUEST['wrkTitle']))."\"
                    AND `assignment_id` = ".$_REQUEST['assigId']."
                    AND `id` <> \"".$_REQUEST['wrkId']."\"";
                    
        $result = claro_sql_query($sql);
        
        if( mysql_num_rows($result) != 0 )
        {
            $dialogBox .= $langWrkTitleAlreadyExists."<br />";
            $formCorrectlySent = false;
        }
        else
        {
            $wrkForm['title'] = $_REQUEST['wrkTitle'];
            // $formCorrectlySent stay true;
        }
      }
      
      // check if a author name has been given
      if ( ! isset($_REQUEST['wrkAuthors']) || trim($_REQUEST['wrkAuthors']) == "")
      {
            if( isset($_uid) )
            {
                  $wrkForm['authors'] = $currentUserFirstName." ".$currentUserLastName;
                  // $formCorrectlySent stay true;
            }
            else
            {
                  $dialogBox .= $langWrkAuthorsRequired."<br />";
                  $formCorrectlySent = false;
            }
      }
      else
      {
        $wrkForm['authors'] = $_REQUEST['wrkAuthors'];
        // $formCorrectlySent stay true;
      }
      
      // check if the score is between 0 and 100
      // no need to check if the value is not setted, it probably means that it is not a correction
      if ( isset($_REQUEST['wrkScore']) )
      {
            if( $_REQUEST['wrkScore'] < -1 || $_REQUEST['wrkScore'] > 100 )
            {
                  $dialogBox .= $langWrkScoreRequired."<br />";
                  $formCorrectlySent = false;
            }
            else
            {
                  $wrkForm['wrkScore'] = $_REQUEST['wrkScore'];
            }
            
      }
      
      // no need to check and/or upload the file if there is already an error
      if($formCorrectlySent)
      {
            if ( is_uploaded_file($_FILES['wrkFile']['tmp_name']) && $assignmentContent != "TEXT" )
            {        
                  if ($_FILES['wrkFile']['size'] > $fileAllowedSize)
                  {
                        $dialogBox .= $langTooBig."<br />";
                        $formCorrectlySent = false;
                  }
                  else
                  {     
                        // add file extension if it doesn't have one
                        $newFileName = add_ext_on_mime($_FILES['wrkFile']['name']);
        
                        // Replace dangerous characters
                        $newFileName = replace_dangerous_char($newFileName);
                        
                        // Transform any .php file in .phps fo security
                        $newFileName = php2phps($newFileName);
                        // compose a unique file name to avoid any conflict
                        
                        $wrkForm['fileName'] = uniqid('')."_".$newFileName;
                        
                        if( !is_dir( $assigDirSys ) )
                        {
                              mkdir( $assigDirSys , 0777 );
                        }
                        
                        if( ! @copy($_FILES['wrkFile']['tmp_name'], $assigDirSys.$wrkForm['fileName']) )
                        {
                              $dialogBox .= $langCannotCopyFile."<br />";
                              $formCorrectlySent = false;
                        }
                        
                        // remove the previous file if there was one
                        if( isset($_REQUEST['currentWrkUrl']) )
                        {
                              @unlink($assigDirSys.$_REQUEST['currentWrkUrl']);
                        }
                        // else : file sending shows no error
                        // $formCorrectlySent stay true;
                  }
            }
            elseif( $assignmentContent == "FILE" )
            {
                  if( isset($_REQUEST['currentWrkUrl']) )
                  {
                        // if there was already a file and nothing was provided to replace it, reuse it of course
                        $wrkForm['fileName'] = $_REQUEST['currentWrkUrl'];
                  }
                  else
                  {
                        // if the main thing to provide is a file and that no file was sent
                        $dialogBox .= $langFileRequired."<br />";
                        $formCorrectlySent = false;
                  }
            }
            elseif( $assignmentContent == "TEXTFILE" )
            {
                  // attached file is optionnal if work type is TEXT AND FILE
                  // so the attached file can be deleted only in this mode
                  
                  // if delete of the file is required
                  if(isset($_REQUEST['delAttacheDFile']) )
                  {
                        $wrkForm['fileName'] = ""; // empty DB field
                        @unlink($assigDirSys.$_REQUEST['currentWrkUrl']); // physically remove the file
                  }
            }
      }// if($formCorrectlySent)
            
} //end if($_REQUEST['submitWrk'])







/*============================================================================
                          DISPLAY FLAGS
  =============================================================================*/
  // the following flags will be setted in command execution to 
  // choose what will be shown after the execution
  // set a flag to true will show the element, false will hide the element
  
  // flag to order the display of the work form (create/edit)
  $dispWrkForm = false;
  // flag to order the display of the work details
  $dispWrkDet = false;
  // flag to order the display of the assignment details
  $dispAssigDet = false;
  // flag to order the display of the list of the works in this assignment
  $dispWrkLst = false;
  // flag to order the display of the score field in the work form
  $dispWrkFormScore = false;


/*============================================================================
                          ADMIN ONLY COMMANDS
  =============================================================================*/
if($is_allowedToEditAll)
{
  /*--------------------------------------------------------------------
                        CHANGE VISIBILITY
  --------------------------------------------------------------------*/
  // change visibility of a work
  if( $cmd == 'exChVis' )
  {
    if( isset($_REQUEST['vis']) )
    {
      $_REQUEST['vis'] == "v" ? $visibility = 'VISIBLE' : $visibility = 'INVISIBLE';
      
      $sql = "UPDATE `".$tbl_wrk_submission."`
                 SET `visibility` = '$visibility'
               WHERE `id` = ".$_REQUEST['wrkId']."
                 AND `visibility` != '$visibility'";
      claro_sql_query ($sql);
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
                  WHERE `id` = ".$_REQUEST['wrkId']."
                     OR `parent_id` = ".$_REQUEST['wrkId'];
      
      $filesToDelete = claro_sql_query_fetch_all($sql);
      
      foreach($filesToDelete as $fileToDelete)
      {
            // delete the file
            @unlink($assigDirSys.$fileToDelete['submitted_doc_path']);
            
            // delete the database data of this work
            $sqlDelete = "DELETE FROM `".$tbl_wrk_submission."`
                              WHERE `id` = ".$fileToDelete['id'];
            claro_sql_query($sqlDelete);      
      }
  }
  /*--------------------------------------------------------------------
                        CORRECTION OF A WORK
  --------------------------------------------------------------------*/
  /*-----------------------------------
            STEP 2 : check & quey
  -------------------------------------*/
  if( $cmd == "exGradeWrk" && isset($_REQUEST['wrkId']) )
  {
       if( isset($formCorrectlySent) && $formCorrectlySent )
      {      
            // check if user id is set
            if( isset($_uid) )
            {
                  $uidString = "`user_id`= ".$_uid.",";
            }
            else
            {
                  $uidString = "";
            }
            
            $sqlAddWork = "INSERT INTO `".$tbl_wrk_submission."`
                           SET `submitted_doc_path` = \"".$wrkForm['fileName']."\",
                              `assignment_id` = ".$_REQUEST['assigId'].",
                              `parent_id` = ".$_REQUEST['wrkId'].","
                              .$uidString
                              ."`visibility` = \"".$assignment['def_submission_visibility']."\",
                              `title`       = \"".trim(claro_addslashes( $wrkForm['title'] ))."\",
                              `submitted_text` = \"".trim(claro_addslashes( $_REQUEST['wrkTxt'] ))."\",
                              `authors`     = \"".trim(claro_addslashes( $wrkForm['authors'] ))."\",
                              `score` = \"".$wrkForm['wrkScore']."\",
                              `creation_date` = NOW(),
                              `last_edit_date` = NOW()";
                              
            claro_sql_query($sqlAddWork);
                        
            $dialogBox .= $langFeedbackAdded;
            
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
  if( $cmd == "rqGradeWrk" && isset($_REQUEST['wrkId']) )
  {
      // prepare fields
      if( !$_REQUEST['submitWrk'] )
      {
            // prefill some fields of the form
            $form['wrkTitle'  ] = $wrk['title']." (".$langFeedback.")";
            $form['wrkAuthors'] = $currentUserFirstName." ".$currentUserLastName;
            $form['wrkScore'  ] = -1; 
      }
      else
      {
            // there was an error in the form so display it with already modified values
            $form['wrkTitle'] = $_REQUEST['wrkTitle'];
            $form['wrkAuthors'] = $_REQUEST['wrkAuthors'];
            $form['wrkTxt'] = $_REQUEST['wrkTxt'];
            $form['wrkScore'] = $_REQUEST['wrkScore'];
      }
      
      $cmdToSend = "exGradeWrk";
      
      $txtForFormTitle = $langAddFeedback;
      $isGrade = true;
      
      // display flags
      $dispWrkForm  = true; 
      $dispWrkDet   = true;
      $dispWrkFormScore = true;
      
  }  
} // if($is_allowedToEdit)
/*============================================================================
                        ADMIN AND AUTHED USER COMMANDS
  =============================================================================*/  
if( $is_allowedToEdit )
{
  /*--------------------------------------------------------------------
                        EDIT A WORK
  --------------------------------------------------------------------*/
  /*-----------------------------------
            STEP 2 : check & quey
  -------------------------------------*/
  if( $cmd == "exEditWrk" && isset($_REQUEST['wrkId']) )
  {
      // if there is no error update database
      if( isset($formCorrectlySent) && $formCorrectlySent )
      {
            // for corrections
            if( isset($wrkForm['wrkScore']) )
            {
                  $sqlScore = " `score` = \"".$wrkForm['wrkScore']."\",";
            }
            else
            {
                  $sqlScore = "";
            }
            $sqlEditWork = "UPDATE `".$tbl_wrk_submission."`
                           SET `submitted_doc_path` = \"".$wrkForm['fileName']."\",
                              `title`       = \"".trim(claro_addslashes( $wrkForm['title'] ))."\",
                              `submitted_text` = \"".$submittedText."\",
                              `authors`     = \"".trim(claro_addslashes( $wrkForm['authors'] ))."\","
                              .$sqlScore
                              ."`last_edit_date` = NOW()
                              WHERE `id` = ".$_REQUEST['wrkId'];

            $lastWrkId = claro_sql_query($sqlEditWork);
                        
            $dialogBox .= $langWrkEdited;
            
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
      if( !$_REQUEST['submitWrk'] )
      {
            // prefill some fields of the form
            $form['wrkTitle'] = $wrk['title'];
            $form['wrkAuthors'] = $wrk['authors'];
            $form['wrkTxt'] = $wrk['submitted_text'];
            $form['wrkUrl'] = $wrk['submitted_doc_path'];
            $form['wrkScore'] = $wrk['score'];
      }
      else
      {
            // there was an error in the form so display it with already modified values
            $form['wrkTitle'] = $_REQUEST['wrkTitle'];
            $form['wrkAuthors'] = $_REQUEST['wrkAuthors'];
            $form['wrkTxt'] = $_REQUEST['wrkTxt'];
            $form['wrkScore'] = $_REQUEST['wrkScore'];
      }
      $cmdToSend = "exEditWrk";
      // fill the title of the page
      $txtForFormTitle = $langEditWork;
      
      // display flags
      $dispWrkForm  = true;
      // only if this is a correction 
      if( !empty($wrk['parent_id']) )
      {
            $dispWrkFormScore = true;
      }
  }
}
/*============================================================================
 COMMANDS FOR : ADMIN, AUTHED USERS, ANONYMOUS USERS (if assignment cfg allows them)
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
            // check if user id is set
            if( isset($_uid) )
            {
                  $uidString = "`user_id`= ".$_uid.",";
            }
            else
            {
                  $uidString = "";
            }
            
            $sqlAddWork = "INSERT INTO `".$tbl_wrk_submission."`
                           SET `submitted_doc_path` = \"".$wrkForm['fileName']."\",
                              `assignment_id` = ".$_REQUEST['assigId'].","
                              .$uidString
                              ."`visibility` = \"".$assignment['def_submission_visibility']."\",
                              `title`       = \"".trim(claro_addslashes( $wrkForm['title'] ))."\",
                              `submitted_text` = \"".trim(claro_addslashes( $_REQUEST['wrkTxt'] ))."\",
                              `authors`     = \"".trim(claro_addslashes( $wrkForm['authors'] ))."\",
                              `creation_date` = NOW(),
                              `last_edit_date` = NOW()";
            
            claro_sql_query($sqlAddWork);
                        
            $dialogBox .= $langWrkAdded;
            
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
      if( !$_REQUEST['submitWrk'] )
      {
            // prefill som fields of the form
            $form['wrkAuthors'] = $currentUserFirstName." ".$currentUserLastName;
      }
      else
      {
            // there was an error in the form so display it with already modified values
            $form['wrkTitle'] = $_REQUEST['wrkTitle'];
            $form['wrkAuthors'] = $_REQUEST['wrkAuthors'];
            $form['wrkTxt'] = $_REQUEST['wrkTxt'];
      }
    
  
    // request the form with correct cmd
    $cmdToSend = "exSubWrk";
    // fill the title of the page
    $txtForFormTitle = $langSubmitWork;
    
    // display flags
    $dispWrkForm  = true;
  }
} // if is_allowedToSubmit
/*============================================================================
                        OTHER COMMANDS
  =============================================================================*/ 

  /*--------------------------------------------------------------------
                        SHOW DETAILS OF A WORK
  --------------------------------------------------------------------*/
if( $cmd == "exShwDet" )
{
      // display flags
      $dispWrkDet = true;
} 
  
  
  
  
  
  
  
/*============================================================================
                          DISPLAY
  =============================================================================*/
if( !$dispWrkForm && !$dispWrkDet && !$dispWrkLst )
{
      // display flags
      $dispAssigDet = true;
      $dispWrkLst = true;
}

/*--------------------------------------------------------------------
                    HEADER
    --------------------------------------------------------------------*/

$htmlHeadXtra[] =
"<script>
function confirmation (name)
{
	if (confirm(\" $langAreYouSureToDelete \"+ name + \" ?  \" ))
		{return true;}
	else
		{return false;}
}
</script>";

$interbredcrump[]= array ("url"=>"../work/work.php", "name"=> $langAssignments);

if( $dispWrkDet || $dispWrkForm )
{
      // bredcrump to return to the list when in a form
      $interbredcrump[]= array ("url"=>"../work/workList.php?assigId=".$_REQUEST['assigId'], "name"=> $langAssignment);
      // add parameters in query string to prevent the 'refresh' interbredcrump link to display the list of works instead of the form
      $QUERY_STRING = "assigId=".$_REQUEST['assigId']."&wrkId=".$_REQUEST['wrkId']."&cmd=".$_REQUEST['cmd'];
      $nameTools = $langSubmittedWork;
}
else
{
      $nameTools = $langAssignment;
      // to prevent parameters to be added in the breadcrumb
      $QUERY_STRING = 'assigId='.$_REQUEST['assigId']; 
}

include($includePath.'/claro_init_header.inc.php');

  
/*--------------------------------------------------------------------
                    TOOL TITLE
    --------------------------------------------------------------------*/

$pageTitle['mainTitle'  ] = $nameTools;
$pageTitle['subTitle'   ] = $assignment['title'];
claro_disp_tool_title($pageTitle);


/*--------------------------------------------------------------------
                          ASSIGNMENT INFOS
  --------------------------------------------------------------------*/
// display assignment infos only when displaying the works list  
if( isset($dispWrkLst) && $dispWrkLst )
{
      // end date
      echo "\n<p>\n"
            ."<b>".$langEndDate."</b><br />\n"
            .claro_disp_localised_date($dateTimeFormatLong, $assignment['unix_end_date'])
            ."\n</p>\n\n";
      // description of assignment
      if( !empty($assignment['description']) )
      {
            echo "\n<p>\n"
                  ."<b>".$langAssignmentDescription."</b><br />"
                  .claro_parse_user_text($assignment['description'])
                  ."\n</p>\n\n";
      }
      
      // SHOW FEEDBACK
      // only if :
      //      - there is a text OR a file 
      //    AND 
      //          feedback must be shown after end date and end date is past
      //      OR  feedback must be shown directly after a post (from the time a work was uploaded by the student)
      
      // there is a prefill_ file or text, so there is something to show
      $textOrFilePresent = (boolean) !empty($assignment['prefill_text']) || !empty($assignment['prefill_doc_path']);
      // feedback must be shown after end date and end date is past
      $showAfterEndDate = (boolean) ($assignment['prefill_submit'] == "ENDDATE" && $assignment['unix_end_date'] < time());

      // feedback must be shown directly after a post
      // check if user has already posted a work
      if( !isset($_uid) )
      {
            // do not show to anonymous users because we can't know if the user already uploaded a work
            $showAfterPost = false;
      }
      else
      {      
            $sql = "SELECT count(`id`) 
                       FROM `".$tbl_wrk_submission."`
                      WHERE `user_id` = ".$_uid."
                        AND `assignment_id` = ".$_REQUEST['assigId'];
            $nbrWorksOfUser = claro_sql_query_get_single_value($sql);
            
            $showAfterPost = (boolean) ( $assignment['prefill_submit'] == "AFTERPOST" && $nbrWorksOfUser >= 1 );
      }
      
      // show to authenticated and anonymous users
      
      if( $textOrFilePresent &&  ( $showAfterEndDate || $showAfterPost ) )
      {
            echo "<fieldset>\n"
                  ."<legend><b>".$langFeedback."</b></legend>";
            if( !empty($assignment['prefill_text']) )
            {
                  echo claro_parse_user_text($assignment['prefill_text']);
            }
            
            if( !empty($assignment['prefill_doc_path']) && !empty($assignment['prefill_text']) )
            {
                  echo  "<p><a href=\"".$assigDirWeb.$assignment['prefill_doc_path']."\">".$assignment['prefill_doc_path']."</a></p>";
            }
            elseif( !empty($assignment['prefill_doc_path']) )
            {
                  echo  "<a href=\"".$assigDirWeb.$assignment['prefill_doc_path']."\">".$assignment['prefill_doc_path']."</a>";
            }
            echo "</fieldset><br />";
      }
}

/*--------------------------------------------------------------------
                          WORK DETAILS
  --------------------------------------------------------------------*/
if( $dispWrkDet && $is_allowedToView )
{
      if( isset($wrk['parent_id']) )
      {
            // a correction
            echo "<h4>".$langFeedbackDetails."</h4>\n\n";
      }
      else
      {
            // a work
            echo "<h4>".$langWorkDetails."</h4>\n\n";
      }
      
      if( empty($wrk['user_id']) )
      {
            $userToDisplay = $langAnonymousUser;
      }
      else
      {
            $sql = "SELECT `nom`, `prenom`
                        FROM `".$tbl_user."`
                        WHERE `user_id` = ".$wrk['user_id'];
            list($wrk['userDet']) = claro_sql_query_fetch_all($sql);
                        
            $userToDisplay = "<a href=\"../user/userInfo.php?uInfo=".$wrk['user_id']."\">".$wrk['userDet']['prenom']." ".$wrk['userDet']['nom']."</a>";
      }
      
      // check if the work has a correction 
      // take the latest edited if there is many
      $sql = "SELECT `id`
                  FROM `".$tbl_wrk_submission."`
                  WHERE `parent_id` = ".$wrk['id']."
               ORDER BY `last_edit_date` DESC
                  LIMIT 1";
      // corection of this work (NULL if there is no corection yet)
      $gradeId = claro_sql_query_get_single_value($sql);
     
      // if the work is a correction
      if( !empty($wrk['parent_id']) )
      {
            if( $is_allowedToEditAll )
            {
                  // admin has the right to edit it
                  echo "<a href=\"".$_SERVER['PHP_SELF']."?cmd=rqEditWrk&assigId=".$_REQUEST['assigId']."&wrkId=".$wrk['id']."\">"
                        ."<img src=\"".$clarolineRepositoryWeb."img/edit.gif\" border=\"0\" alt=\"$langModify\"></a>";
            }

            // anybody that can see the correction is probably authorised to see the work, so display the link to anybody
            echo "&nbsp;<a href=\"workList.php?assigId=".$_REQUEST['assigId']."&wrkId=".$wrk['parent_id']."&cmd=exShwDet\">".$langShowWork."</a>";
      }
      // if the work is a ... work
      if( empty($wrk['parent_id']) )
      {
            // if user is allowed to edit, display the link to edit it
            if( $is_allowedToEdit )
            {
                  // the work can be edited 
                  echo "<a href=\"".$_SERVER['PHP_SELF']."?cmd=rqEditWrk&assigId=".$_REQUEST['assigId']."&wrkId=".$wrk['id']."\">"
                        ."<img src=\"".$clarolineRepositoryWeb."img/edit.gif\" border=\"0\" alt=\"$langModify\"></a>";
            }
            
            if( !empty($gradeId) )
            {
                  // if there is already a correction display the link to show it
                  echo "&nbsp;<a href=\"workList.php?assigId=".$_REQUEST['assigId']."&wrkId=".$gradeId."&cmd=exShwDet\">".$langShowFeedback."</a>";
            }
            elseif( $is_allowedToEditAll )
            {
                  // if there is no correction yet show the link to add a correction if user is course admin
                  echo "&nbsp;<a href=\"".$_SERVER['PHP_SELF']."?cmd=rqGradeWrk&assigId=".$_REQUEST['assigId']."&wrkId=".$wrk['id']."\">".$langAddFeedback."</a>";
            }
      }
      
      // change some displayed text depending on the context
      if( $assignmentContent == "TEXTFILE" )
      {
            $txtForFile = $langAttachedFile;
            $txtForText = $langAnswer;
      }
      elseif( $assignmentContent == "TEXT" )
      {
            $txtForText = $langAnswer;
      }
      elseif( $assignmentContent == "FILE" )
      {
            $txtForFile = $langUploadedFile;
            $txtForText = $langFileDesc;
      }
      echo "<table>"
            ."<tr>\n"
            ."<td valign=\"top\">".$langWrkTitle."&nbsp;:</td>\n"
            ."<td>".$wrk['title']."</td>\n"
            ."</tr>\n\n"
            ."<tr>\n"
            ."<td valign=\"top\">".$langWrkAuthors."&nbsp;:</td>\n"
            ."<td>".$wrk['authors']." ( ".$langSubmittedBy." ".$userToDisplay." )</td>\n"
            ."</tr>\n\n";
            
      if( $assignmentContent != "TEXT" )
      {
            if( !empty($wrk['submitted_doc_path']) )
            {
                  $completeWrkUrl = $assigDirWeb.$wrk['submitted_doc_path'];
                  // show file if this is not a TEXT only work
                  echo "<tr>\n"
                        ."<td valign=\"top\">".$txtForFile."&nbsp;:</td>\n"
                        ."<td><a href=\"".$completeWrkUrl."\">".$wrk['submitted_doc_path']."</a></td>\n"
                        ."</tr>\n\n";
            }
            else
            {
                  echo "<tr>\n"
                        ."<td valign=\"top\">".$txtForFile."&nbsp;:</td>\n"
                        ."<td>".$langNoFile."</td>\n"
                        ."</tr>\n\n";
            }
      }
      
      if( isset($wrk['score']) )
      {
            echo "<tr>\n"
                  ."<td valign=\"top\">".$langScore."&nbsp;:</td>\n"
                  ."<td>";
            if( $wrk['score'] == -1 )
            {
                  echo $langNoScore;
            }
            else
            {
                  echo $wrk['score']." %";
            }
            echo  " </td>\n"
                  ."</tr>\n\n";
      }
      
      
      // display an alert if work was submitted after end date and work is not a correction !
      if( $assignment['unix_end_date'] < $wrk['unix_creation_date'] && empty($wrk['parent_id']) )
      {
            $lateUploadAlert = "<img src=\"".$clarolineRepositoryWeb."img/caution.gif\" border=\"0\" alt=\"".$langLateUpload."\">";
      }
      else
      {
            $lateUploadAlert = "";
      }
      
      echo "<tr>\n"
            ."<td valign=\"top\">".$txtForText."&nbsp;:</td>\n"
            ."<td>".$wrk['submitted_text']."</td>\n"
            ."</tr>\n\n"
            ."<tr>\n"
            ."<td valign=\"top\">".$langSubmissionDate."&nbsp;:</td>\n"
            ."<td>".claro_disp_localised_date($dateTimeFormatLong, $wrk['unix_creation_date'])
            ."&nbsp;".$lateUploadAlert
            ."</td>\n"
            ."</tr>\n\n";
      if( $wrk['unix_creation_date'] != $wrk['unix_last_edit_date'] )
      {
            // display an alert if work was submitted after end date and work is not a correction !
            if( $assignment['unix_end_date'] < $wrk['unix_last_edit_date'] && empty($wrk['parent_id']) )
            {
                  $lateEditAlert = "<img src=\"".$clarolineRepositoryWeb."img/caution.gif\" border=\"0\" alt=\"".$langLateUpload."\">";
            }
            else
            {
                  $lateEditAlert = "";
            }
            
            echo "<tr>\n"
                  ."<td valign=\"top\">".$langLastEditDate."&nbsp;:</td>\n"
                  ."<td>".claro_disp_localised_date($dateTimeFormatLong, $wrk['unix_last_edit_date'])
                  ."&nbsp;".$lateEditAlert
                  ."</td>\n"
                  ."</tr>\n\n";
      }
      echo "</table>";
}


/*--------------------------------------------------------------------
                          FORMS
  --------------------------------------------------------------------*/
if( $is_allowedToSubmit )
{
      if ($dialogBox)
      {
            claro_disp_message_box($dialogBox);
      }
      echo "<br />";
      if( $dispWrkForm )
      {
            echo "<h4>".$txtForFormTitle."</h4>"
                  ."<form method=\"post\" action=\"".$_SERVER['PHP_SELF']."\" enctype=\"multipart/form-data\">"
                  ."<input type=\"hidden\" name=\"assigId\" value=\"".$_REQUEST['assigId']."\">"
                  ."<input type=\"hidden\" name=\"cmd\" value=\"".$cmdToSend."\">";

            if( isset($_REQUEST['wrkId']) )
            {
                  echo "<input type=\"hidden\" name=\"wrkId\" value=\"".$_REQUEST['wrkId']."\">";
            }
            
            echo  "<table>\n"
                  ."<tr>\n"
                  ."<td valign=\"top\"><label for=\"wrkTitle\">".$langWrkTitle."&nbsp;*&nbsp;:</label></td>\n"
                  ."<td><input type=\"text\" name=\"wrkTitle\" id=\"wrkTitle\" size=\"50\" maxlength=\"200\" value=\"".htmlentities($form['wrkTitle'])."\"></td>\n"
                  ."</tr>\n\n"
                  ."<tr>\n"
                  ."<td valign=\"top\"><label for=\"wrkAuthors\">".$langWrkAuthors."&nbsp;*&nbsp;:</label></td>\n"
                  ."<td><input type=\"text\" name=\"wrkAuthors\" id=\"wrkAuthors\" size=\"50\" maxlength=\"200\" value=\"".htmlentities($form['wrkAuthors'])."\"></td>\n"
                  ."</tr>\n\n";

            // display file box
            if( $assignmentContent == "FILE" || $assignmentContent == "TEXTFILE" )
            {
                  // if we are in edit mode and that a file can be edited : display the url of the current file and the file box to change it
                  if( isset($form['wrkUrl']) )
                  {
                        echo "<tr>\n"
                              ."<td valign=\"top\">";
                              // display a different text according to the context
                        if( $assignment['authorize_text'] == "YES" )
                        {
                              // if text is required, file is considered as a an attached document
                              echo $langCurrentAttachedDoc;
                        }
                        else
                        {
                              // if the file is required and the text is only a description of the file
                              echo $langCurrentDoc;
                        }
                        if( !empty($form['wrkUrl']) )
                        {
                              // display the name of the file, with a link to it, an explanation of what to to to replace it and a checkbox to delete it
                              $completeWrkUrl = $assigDirWeb.$form['wrkUrl'];
                              echo "&nbsp;:<input type=\"hidden\" name=\"currentWrkUrl\" value=\"".$form['wrkUrl']."\">"
                                    ."</td>\n"
                                    ."<td>"
                                    ."<a href=\"".$completeWrkUrl."\">".$form['wrkUrl']."</a>"
                                    ."<br />";
                              if( $assignmentContent == "TEXTFILE" )
                              {
                                    // we can remove the file only if we are in a TEXTFILE context, in file context the file is required !
                                    echo "<input type=\"checkBox\" name=\"delAttacheDFile\" id=\"delAttachedFile\">"
                                    ."<label for=\"delAttachedFile\">".$langExplainDeleteFile."</label> ";
                              }
                              echo $langExplainReplaceFile."</td>\n"
                                    ."</tr>\n\n";
                        }
                        else
                        {
                              echo "&nbsp;:"
                                    ."</td>\n"
                                    ."<td>"
                                    .$langNoFile
                                    ."</td>\n"
                                    ."</tr>\n\n";
                        }
                  }
                  
                  echo "<tr>\n"
                        ."<td valign=\"top\"><label for=\"wrkFile\">";
                  // display a different text according to the context
                  if( $assignmentContent == "TEXTFILE" )
                  {
                        // if text is required, file is considered as a an attached document
                        echo $langAttachDoc;
                  }
                  else
                  {
                        // if the file is required and the text is only a description of the file
                        echo $langUploadDoc."&nbsp;*";
                  }
                  echo "&nbsp;:</label></td>\n"
                        ."<td><input type=\"file\" name=\"wrkFile\" id=\"wrkFile\" size=\"30\"></td>\n"
                        ."</tr>\n\n";
            }
            
            if( $assignmentContent == "FILE" )
            {
                  // display standard html textarea
                  // used for description of an uploaded file
                  echo "<tr>\n"
                        ."<td valign=\"top\">"
                        ."<label for=\"wrkTxt\">"
                        .$langFileDesc
                        ."&nbsp;:<br /></label></td>"
                        ."<td>\n"
                        ."<textarea name=\"wrkTxt\" cols=\"30\" rows=\"3\">".$form['wrkTxt']."</textarea>"
                        ."</td>\n"
                        ."</tr>\n\n";
            }
            elseif( $assignmentContent == "TEXT" || $assignmentContent == "TEXTFILE" )
            {
                  // display enhanced textarea using claro_disp_html_area
                  echo "<tr>\n"
                        ."<td valign=\"top\">"
                        ."<label for=\"wrkTxt\">"
                        .$langAnswer
                        ."&nbsp;*&nbsp;:<br /></label></td>\n"
                        ."<td>";
                  claro_disp_html_area('wrkTxt', $form['wrkTxt']);
                  echo "</td>\n"
                        ."</tr>\n\n";
            }
            
            if( $dispWrkFormScore )
            {
                  // if this is a correction we have to add an input for the score/grade/results/points
                  $wrkScoreField = "<select name=\"wrkScore\" id=\"wrkScore\">\n"
                                    ."<option value=\"-1\"";
                  // add selected attribute if needed
                  if( $form['wrkScore'] == -1 )
                  {
                        $wrkScoreField .= " selected=\"true\"";
                  }                  
                  $wrkScoreField .= ">".$langNoScore."</option>\n";
                  
                  for($i=0;$i <= 100; $i++)
                  {
                        $wrkScoreField .= "<option value=\"".$i."\"";
                        if($i == $form['wrkScore'])
                        {
                        $wrkScoreField .= " selected=\"true\"";
                        }
                        $wrkScoreField .= ">".$i."</option>\n";
                  }
                  $wrkScoreField .= "</select> %";
                  echo "<tr>\n"
                        ."<td valign=\"top\"><label for=\"wrkScore\">".$langScore."&nbsp;&nbsp;:</label></td>\n"
                        ."<td>"
                        .$wrkScoreField
                        ."</td>"
                        ."</tr>\n\n";
            }
            
            echo "<tr>\n"
                  ."<td>&nbsp;</td>\n"
                  ."<td>"
                  ."<input type=\"submit\" name=\"submitWrk\" value=\"".$langOk."\">\n";
                  
                  claro_disp_button($_SERVER['PHP_SELF']."?assigId=".$_REQUEST['assigId'], $langCancel);    
                  
            echo "</td>\n"
                  ."</tr>\n\n"
                  ."</table>\n\n"
                  ."</form>"
                  ."<small>* : ".$langRequired."</small>";
      }
}
  

/*--------------------------------------------------------------------
                          WORK LIST
  --------------------------------------------------------------------*/
// display the list when there is no form to show
if( $dispWrkLst && $is_allowedToView )
{
    /*--------------------------------------------------------------------
                        PREPARE WORK LIST
      --------------------------------------------------------------------*/
    if( $is_allowedToEditAll ) // courseAdmin
    {    
      $sql = "SELECT `id`, `title`, 
                  `parent_id`, `user_id`,
                  `visibility`, `authors`, 
                  UNIX_TIMESTAMP(`last_edit_date`) as `unix_last_edit_date`
            FROM `".$tbl_wrk_submission."`
            WHERE `assignment_id` = ".$assignment['id']."
            ORDER BY `last_edit_date` ASC";
    }
    elseif( isset($_uid) ) // course member
    {
      // show all my works and all their visible corrections
       $sql = "SELECT `id`, `title`, 
                  `parent_id`, `user_id`,
                  `visibility`, `authors`, 
                  UNIX_TIMESTAMP(`last_edit_date`) as `unix_last_edit_date`
            FROM `".$tbl_wrk_submission."`
            WHERE `assignment_id` = ".$assignment['id']."
              AND (`visibility` = 'VISIBLE' OR `user_id` = ".$_uid.")
            ORDER BY `last_edit_date` ASC";  
    }
    else // anonymous user
    {
      // show visible works that are not corrections 
      $sql = "SELECT `id`, `title`, 
                  `parent_id`, `user_id`,
                  `visibility`, `authors`, 
                  UNIX_TIMESTAMP(`last_edit_date`) as `unix_last_edit_date`
            FROM `".$tbl_wrk_submission."`
            WHERE `assignment_id` = ".$assignment['id']."
              AND `visibility` = 'VISIBLE'
              AND `parent_id` IS NULL
            ORDER BY `last_edit_date` ASC";
    }
        
    $workList = claro_sql_query_fetch_all($sql);
    
    // tree of the works and their corrections
    $treeElementList = build_element_list($workList, 'parent_id', 'id');
    
    // We have to prevent the display of visible corrections of works of another students
    // anonymous user never see it, corrections are never shown to theù
    // admin user can always see it
    // authenticated user ca see only the corrections of his works
    // SO, in this last case, we remove the children of works when user_id is different from $_uid
    if( isset($_uid) && !$is_allowedToEditAll )
    {
      for ($i=0 ; $i < sizeof($treeElementList) ; $i++)
      {
      if( $treeElementList[$i]['user_id'] != $_uid ) unset($treeElementList[$i]['children']) ;
      }
    }
    
    $flatElementList = build_display_element_list( $treeElementList );

    // look for maxDeep
    $maxDeep = 1; // used to compute colspan of <td> cells
    for ($i=0 ; $i < sizeof($flatElementList) ; $i++)
    {
      if( $flatElementList[$i]['children'] > $maxDeep ) $maxDeep = $flatElementList[$i]['children'] ;
    }

    /*--------------------------------------------------------------------
                        ADMIN LINKS
      --------------------------------------------------------------------*/
    if( $is_allowedToSubmit )
    {
      // link to create a new assignment
      echo "&nbsp;<a href=\"".$_SERVER['PHP_SELF']."?cmd=rqSubWrk&assigId=".$_REQUEST['assigId']."\">".$langSubmitWork."</a>\n";
    }

    /*--------------------------------------------------------------------
                                  LIST
      --------------------------------------------------------------------*/
    echo "<table class=\"claroTable\" width=\"100%\">\n"
          ."<thead>\n"
          ."<tr class=\"headerX\">\n"
          ."<th colspan=\"".($maxDeep+1)."\">".$langWrkTitle."</th>\n"
          ."<th>".$langWrkAuthors."</th>\n"
          ."<th>".$langLastEditDate."</th>\n";
          
    if ( $is_allowedToEditAll ) 
    {
        echo  "<th>".$langAddFeedback."</th>\n"
            ."<th>".$langModify."</th>\n"
            ."<th>".$langDelete."</th>\n"
            ."<th>".$langVisibility."</th>\n";
    }
    echo "</tr>\n"
        ."</thead>\n\n"
        ."<tbody>\n";
    foreach($flatElementList as $thisWrk)
    {
      // display an alert if work was submitted after end date and work is not a correction !
      if( $assignment['unix_end_date'] < $thisWrk['unix_last_edit_date'] && empty($thisWrk['parent_id']) )
      {
            $lateUploadAlert = "&nbsp;<img src=\"".$clarolineRepositoryWeb."img/caution.gif\" border=\"0\" alt=\"".$langLateUpload."\">";
      }
      else
      {
            $lateUploadAlert = "";
      }
      
      if ($thisWrk['visibility'] == "INVISIBLE")
			{
				if ($is_allowedToEditAll || $thisWrk['user_id'] == $_uid)
				{
					$style=' class="invisible"';
				}
        else
				{
					continue; // skip the display of this file
				}
			}
			else 
			{
				$style='';
			}
      
      $spacingString = "";
      for($i = 0; $i < $thisWrk['children']; $i++)
        $spacingString .= "<td width=\"5\">&gt;</td>";
      $colspan = $maxDeep - $thisWrk['children']+1;
      
      
      echo "<tr align=\"center\"".$style." >\n"
          .$spacingString
          ."<td colspan=\"".$colspan."\" align=\"left\">"
          ."<a href=\"workList.php?assigId=".$_REQUEST['assigId']."&wrkId=".$thisWrk['id']."&cmd=exShwDet\">"
          .$thisWrk['title']."</a></td>\n"
          ."<td>".$thisWrk['authors']."</td>\n"
          ."<td><small>"
          .claro_disp_localised_date($dateTimeFormatShort, $thisWrk['unix_last_edit_date'])
          .$lateUploadAlert
          ."</small></td>\n";
      
      
      if( $is_allowedToEditAll )
      { 
        if( empty($thisWrk['parent_id']) )
        {
            $gradeString  = "<a href=\"".$_SERVER['PHP_SELF']."?cmd=rqGradeWrk&assigId=".$_REQUEST['assigId']."&wrkId=".$thisWrk['id']."\">".$langAddFeedback."</a>";
        }
        else
        {
            $gradeString = "&nbsp;";
        }
        
        echo "<td>".$gradeString."</td>" 
            ."<td><a href=\"".$_SERVER['PHP_SELF']."?cmd=rqEditWrk&assigId=".$_REQUEST['assigId']."&wrkId=".$thisWrk['id']."\">"
            ."<img src=\"".$clarolineRepositoryWeb."img/edit.gif\" border=\"0\" alt=\"$langModify\"></a></td>\n"
            ."<td><a href=\"".$_SERVER['PHP_SELF']."?cmd=exRmWrk&assigId=".$_REQUEST['assigId']."&wrkId=".$thisWrk['id']."\" onClick=\"return confirmation('",addslashes($thisWrk['title']),"');\">"
            ."<img src=\"".$clarolineRepositoryWeb."img/delete.gif\" border=\"0\" alt=\"$langDelete\"></a></td>\n"
            ."<td>";
            
        if ($thisWrk['visibility'] == "INVISIBLE")
        {
            echo	"<a href=\"".$_SERVER['PHP_SELF']."?cmd=exChVis&assigId=".$_REQUEST['assigId']."&wrkId=".$thisWrk['id']."&vis=v\">"
                  ."<img src=\"".$clarolineRepositoryWeb."img/invisible.gif\" border=\"0\" alt=\"$langMakeVisible\">"
                  ."</a>";
        }
        else
        {
            echo	"<a href=\"".$_SERVER['PHP_SELF']."?cmd=exChVis&assigId=".$_REQUEST['assigId']."&wrkId=".$thisWrk['id']."&vis=i\">"
                  ."<img src=\"".$clarolineRepositoryWeb."img/visible.gif\" border=\"0\" alt=\"$langMakeInvisible\">"
                  ."</a>";
        }          
        echo "</td>\n";
      }
      echo "</tr>\n\n";
    }

    echo "</tbody>\n</table>\n\n";

}


// FOOTER
include($includePath."/claro_init_footer.inc.php"); 
?>
