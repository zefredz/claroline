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

$tbl_cdb_names = claro_sql_get_course_tbl();
$tbl_wrk_assignment    = $tbl_cdb_names['wrk_assignment'  ];
$tbl_wrk_submission   = $tbl_cdb_names['wrk_submission'   ];    


$currentUserFirstName       = $_user['firstName'];
$currentUserLastName        = $_user['lastName'];


if ( !$_cid ) claro_disp_select_course();
if ( ! $is_courseAllowed)	claro_disp_auth_form();

event_access_tool($_tid, $_SESSION['_courseTool']['label']);



include($includePath."/lib/fileUpload.lib.php");
include($includePath."/lib/fileDisplay.lib.php"); // need format_url function
include($includePath."/lib/fileManage.lib.php"); // need my_delete



/*============================================================================
                     BASIC VARIABLES DEFINITION
  =============================================================================*/
$currentCourseRepositorySys = $coursesRepositorySys.$_course["path"]."/";
$currentCourseRepositoryWeb = $coursesRepositoryWeb.$_course["path"]."/";

$fileAllowedSize = CONFVAL_MAX_FILE_SIZE_PER_WORKS ;    //file size in bytes
$wrkDir           = $currentCourseRepositorySys.'work/'; //directory path to create assignment dirs

// use with strip_tags function when strip_tags is used to check if a text is empty
// but a 'text' with only an image don't have to be considered as empty 
$allowedTags = '<img>';

// permission
$is_allowedToEdit           = $is_courseAdmin;

/*============================================================================
                     CLEAN INFORMATIONS SEND BY USER
  =============================================================================*/
stripSubmitValue($HTTP_POST_VARS);
stripSubmitValue($HTTP_GET_VARS);
stripSubmitValue($_REQUEST);

$cmd = $_REQUEST['cmd'];

/*============================================================================
                HANDLING FORM DATA : CREATE/EDIT ASSIGNMENT
  =============================================================================*/
// execute this after a form has been send
// this instruction bloc will set some vars that will be used in the corresponding queries
if( isset($_REQUEST['submitAssignment']) ) 
{
    $formCorrectlySent = true;
    
    // title is a mandatory element     
    $title = trim( strip_tags($_REQUEST['assigTitle']) );
            
    if( empty($title) )
    {
      $dialogBox .= $langAssignmentTitleRequired."<br />";
      $formCorrectlySent = false;
    }
    else
    {
      // check if title already exists
      if( isset($_REQUEST['assigId']) )
      {
        // if assigId isset it means we are modifying an assignment
        // and assignment can have the same title as itself
        $sql = "SELECT `title`
                 FROM `".$tbl_wrk_assignment."`
                WHERE `title` = '".claro_addslashes($title)."'
                  AND `id` != ".$_REQUEST['assigId'];
      }
      else
      {
        // creating an assignment
        $sql = "SELECT `title`
                 FROM `".$tbl_wrk_assignment."`
                WHERE `title` = '".claro_addslashes($title)."'";
      }
      
      $query = claro_sql_query($sql);
      
      if(mysql_num_rows($query) != 0 )
      {
        $dialogBox .= $langAssignmentTitleAlreadyExists."<br />";
        $formCorrectlySent = false;
      }
      else
      {
        $wrkForm['assigTitle'] = $_REQUEST['assigTitle'];
        // $formCorrectlySent stays true
      }
    }
    
    // authorized type
    if( isset($_REQUEST['authorizeText']) && isset($_REQUEST['authorizeFile']) )
    {
      $authorizedContent = "TEXTFILE";
    }
    elseif($_REQUEST['authorizeText'])
    {
      $authorizedContent = "TEXT";       
    }
    elseif($_REQUEST['authorizeFile'])
    {
      $authorizedContent = "FILE";       
    }
      
    // description
    if( trim( strip_tags($_REQUEST['assigDesc'], $allowedTags ) ) == "" ) 
    {
      $assigDesc = ""; // avoid multiple br tags to be added when editing an empty form
    }
    else
    {
      $assigDesc = claro_addslashes( trim($_REQUEST['assigDesc']) );
    }
    
    // dates : check if start date is lower than end date else we will have a paradox
    $unixStartDate = mktime( $_REQUEST['startHour'], $_REQUEST['startMinute'], 0, $_REQUEST['startMonth'],$_REQUEST['startDay'], $_REQUEST['startYear'] );
    $unixEndDate = mktime( $_REQUEST['endHour'], $_REQUEST['endMinute'], 0, $_REQUEST['endMonth'],$_REQUEST['endDay'], $_REQUEST['endYear'] );
    
    if( $unixEndDate <= $unixStartDate )
    {
      $dialogBox .= $langIncorrectDate."<br />";
      $formCorrectlySent = false;
    }
    else
    {
      $composedStartDate = $_REQUEST['startYear']."-"
                        .$_REQUEST['startMonth']."-"
                        .$_REQUEST['startDay']." "
                        .$_REQUEST['startHour'].":"
                        .$_REQUEST['startMinute'].":00";
      
      $composedEndDate = $_REQUEST['endYear']."-"
                        .$_REQUEST['endMonth']."-"
                        .$_REQUEST['endDay']." "
                        .$_REQUEST['endHour'].":"
                        .$_REQUEST['endMinute'].":00";
    }
} // if( isset($_REQUEST['submitAssignment']) ) // handling form data 


/*============================================================================
                HANDLING FORM DATA : CREATE/EDIT ASSIGNMENT FEEDBACK
  =============================================================================*/
// execute this after a form has been send
// this instruction bloc will set some vars that will be used in the corresponding queries
if( isset($_REQUEST['submitFeedback']) )
{

    $formCorrectlySent = true;
    // Feedback 
    // check if there is text in it 
    if( trim( strip_tags($_REQUEST['prefillText'], $allowedTags ) ) == "" )
    {
      $prefillText = "";
    }
    else
    {
      $prefillText = claro_addslashes( trim($_REQUEST['prefillText']) );
    }

    if ( is_uploaded_file($_FILES['prefillDocPath']['tmp_name']) )
    {      
          if ($_FILES['prefillDocPath']['size'] > $fileAllowedSize)
          {
                $dialogBox .= $langTooBig."<br />";
                $formCorrectlySent = false;
          }
          else
          {     
                // add file extension if it doesn't have one
                $newFileName = add_ext_on_mime($_FILES['prefillDocPath']['name']);

                // Replace dangerous characters
                $newFileName = replace_dangerous_char($newFileName);
                
                // Transform any .php file in .phps fo security
                $newFileName = php2phps($newFileName);
                // compose a unique file name to avoid any conflict
                
                $prefillDocPath = uniqid('')."_".$newFileName;
                
                // if edit mode ...
                if( isset($_REQUEST['assigId']) )
                {
                    // if edit of an assignment we know its assigId so we don't have to use a tmp directory
                    $tmpWorkUrl = "assig_".$_REQUEST['assigId']."/".$prefillDocPath;
                    
                    if( ! @copy($_FILES['prefillDocPath']['tmp_name'], $wrkDir.$tmpWorkUrl) )
                    {
                          $dialogBox .= $langCannotCopyFile."<br />";
                          $formCorrectlySent = false;
                    }
                    
                    // remove the previous file if there was one
                    if( isset($_REQUEST['currentPrefillDocPath']) )
                    {
                          @unlink($wrkDir."assig_".$_REQUEST['assigId']."/".$_REQUEST['currentPrefillDocPath']);
                    }
                }
                else
                {
                    // put the file in a tmp location, remove it from there at the end of the script
                    // we don't know the assignment id yet so we cannot already put it in the right folder
                    $tmpWorkUrl = "tmp/".$prefillDocPath;
                    
                    if( !is_dir( $wrkDir."tmp" ) )
                    {
                          mkdir( $wrkDir."tmp" , 0777 );
                    }
                    
                    if( ! @copy($_FILES['prefillDocPath']['tmp_name'], $wrkDir.$tmpWorkUrl) )
                    {
                          $dialogBox .= $langCannotCopyFile."<br />";
                          $formCorrectlySent = false;
                    }
                }    
                
                // else : file sending shows no error
                // $formCorrectlySent stay true;
          }
    }
    elseif( isset($_REQUEST['currentPrefillDocPath']) && !isset($_REQUEST['delFeedbackFile']) )
    {
      // reuse the old file as none has been uploaded and no delete was asked
      $prefillDocPath = $_REQUEST['currentPrefillDocPath'];
    }
    elseif( isset($_REQUEST['currentPrefillDocPath']) && isset($_REQUEST['delFeedbackFile']) )
    {
      // delete hte file was requested
      $prefillDocPath = ""; // empty DB field
      @unlink($wrkDir."assig_".$_REQUEST['assigId']."/".$_REQUEST['currentPrefillDocPath']); // physically remove the file
    }
    else
    {
      $prefillDocPath = "";
    }
    
    $prefillSubmit = $_REQUEST['prefillSubmit'];
}


if($is_allowedToEdit)
{
  /*--------------------------------------------------------------------
                        CHANGE VISIBILITY
  --------------------------------------------------------------------*/

  // change visibility of an assignment
  if( $cmd == 'exChVis' )
  {
    if( isset($_REQUEST['vis']) )
    {
      $_REQUEST['vis'] == "v" ? $visibility = 'VISIBLE' : $visibility = 'INVISIBLE';
      
      $sql = "UPDATE `".$tbl_wrk_assignment."`
                 SET `visibility` = '$visibility'
               WHERE `id` = ".$_REQUEST['assigId']."
                 AND `visibility` != '$visibility'";
      claro_sql_query ($sql);
      
    }
  }

  /*--------------------------------------------------------------------
                        DELETE AN ASSIGNMENT
  --------------------------------------------------------------------*/

  // delete/remove an assignment
  if( $cmd == 'exRmAssig' )
  {
    // delete all works in this assignment if the delete of the files worked
    if( my_delete($wrkDir."assig_".$_REQUEST['assigId']) )
    {
      $sql = "DELETE FROM `".$tbl_wrk_submission."`
              WHERE `assignment_id` = ".$_REQUEST['assigId'];
      claro_sql_query($sql);
    }    
    
    $sql = "DELETE FROM `".$tbl_wrk_assignment."`
            WHERE `id` = ".$_REQUEST['assigId'];

    claro_sql_query($sql);
    
    $dialogBox .= $langAssignmentDeleted;
    
  }
  /*--------------------------------------------------------------------
                    MODIFY An ASSIGNMENT FEEDBACK
  --------------------------------------------------------------------*/
  /*-----------------------------------
      STEP 2 : check & query
  -------------------------------------*/
  // edit an assignment / form has been sent
  if( $cmd == 'exEditFeedback' )
  {
    // form data have been handled before this point if the form was sent
    if( isset($_REQUEST['assigId']) && $formCorrectlySent )
    {
          $sql = "UPDATE `".$tbl_wrk_assignment."`
                  SET `prefill_text` = \"".$prefillText."\",
                      `prefill_doc_path` = \"".$prefillDocPath."\",
                      `prefill_submit` = \"".$prefillSubmit."\"
                  WHERE `id` = ".$_REQUEST['assigId'];
          claro_sql_query($sql);
          $dialogBox .= $langFeedbackEdited;
    } 
    else
    {
      $cmd = 'rqEditFeedback';
    }
  }
  /*-----------------------------------
      STEP 1 : display form
  -------------------------------------*/
  // edit aassignment / display the form
  if( $cmd == 'rqEditFeedback' )
  {
    include($includePath."/lib/form.lib.php");
    
    // check if it was already sent
    if( !isset($_REQUEST['submitAssignment'] ) )
    {
        // get current settings to fill in the form
        $sql = "SELECT `prefill_text` , `prefill_doc_path`, `prefill_submit`,
                      UNIX_TIMESTAMP(`end_date`) as `unix_end_date`
                FROM `".$tbl_wrk_assignment."`
                WHERE `id` = ".$_REQUEST['assigId'];
        list($modifiedAssignment) = claro_sql_query_fetch_all($sql);

        // feedback
        $form['prefillText'       ] = $modifiedAssignment['prefill_text'];
        $form['currentPrefillDocPath'] = $modifiedAssignment['prefill_doc_path'];
        $form['prefillSubmit'     ] = $modifiedAssignment['prefill_submit'];
        
        // end date (as a reminder for the "after end date" option
        $form['unix_end_date'     ] = $modifiedAssignment['unix_end_date'];
    }
    else
    {
      // there was an error in the form 
      $form['prefillText'       ] = $_REQUEST['prefillText'];
      $form['currentPrefillDocPath'] = $_REQUEST['currentPrefillDocPath'];
      $form['prefillSubmit'     ] = $_REQUEST['prefillSubmit'];
    }
    // ask the display of the form
    $displayFeedbackForm = true;
  }
  
  
  /*--------------------------------------------------------------------
                        MODIFY An ASSIGNMENT
  --------------------------------------------------------------------*/
  /*-----------------------------------
      STEP 2 : check & query
  -------------------------------------*/
  // edit an assignment / form has been sent
  if( $cmd == 'exEditAssig' )
  {
    // form data have been handled before this point if the form was sent
    if( isset($_REQUEST['assigId']) && $formCorrectlySent )
    {
          $sql = "UPDATE `".$tbl_wrk_assignment."`
                  SET `title` = \"".$title."\",
                      `description` = \"".$assigDesc."\", 
                      `assignment_type` = \"".$_REQUEST['assignmentType']."\", 
                      `authorized_content` = \"".$authorizedContent."\",  
                      `authorize_anonymous` = \"".$_REQUEST['allowAnonymous']."\",
                      `start_date` = \"".$composedStartDate."\", 
                      `end_date` = \"".$composedEndDate."\", 
                      `def_submission_visibility` = \"".$_REQUEST['defSubVis']."\", 
                      `allow_late_upload` = \"".$_REQUEST['allowLateUpload']."\"
                  WHERE `id` = ".$_REQUEST['assigId'];
          claro_sql_query($sql);
          $dialogBox .= $langAssignmentEdited;
    } 
    else
    {
      $cmd = 'rqEditAssig';
    }
  }
  /*-----------------------------------
      STEP 1 : display form
  -------------------------------------*/
  // edit aassignment / display the form
  if( $cmd == 'rqEditAssig' )
  {
    include($includePath."/lib/form.lib.php");
    
    // check if it was already sent
    if( !isset($_REQUEST['submitAssignment'] ) )
    {
        // get current settings to fill in the form
        $sql = "SELECT * 
                FROM `".$tbl_wrk_assignment."`
                WHERE `id` = ".$_REQUEST['assigId'];
        list($modifiedAssignment) = claro_sql_query_fetch_all($sql);
        
    
        // set values to pre-fill the form
        $form['assigTitle'          ] = $modifiedAssignment['title'];
        $form['assigDesc'           ] = $modifiedAssignment['description'];
        
        list($form['startDate'], $form['startTime']) = split(' ', $modifiedAssignment['start_date']);
        list($form['endDate'], $form['endTime']) = split(' ', $modifiedAssignment['end_date']);
        
        $form['authorizedContent' ] = $modifiedAssignment['authorized_content'];
        $form['defSubVis'         ] = $modifiedAssignment['def_submission_visibility'];
        $form['assignmentType'    ] = $modifiedAssignment['assignment_type'];
        $form['allowAnonymous'    ] = $modifiedAssignment['authorize_anonymous'];
        $form['allowLateUpload'   ] = $modifiedAssignment['allow_late_upload'];

    }
    else
    {
      // there was an error in the form so display it with already modified values
      $form['assigTitle'          ] = $_REQUEST['assigTitle'];
      $form['assigDesc'           ] = $_REQUEST['assigDesc'];
      $form['authorizedContent' ] = $_REQUEST['authorizedContent'];      
      $form['startDate'         ] = $_REQUEST['startYear']."-".$_REQUEST['startMonth']."-".$_REQUEST['startDay'];
      $form['startTime'         ] = $_REQUEST['startHour'].":".$_REQUEST['startMinute'].":00";
      $form['endDate'           ] = $_REQUEST['endYear']."-".$_REQUEST['endMonth']."-".$_REQUEST['endDay'];
      $form['endTime'           ] = $_REQUEST['endHour'].":".$_REQUEST['endMinute'].":00";
      $form['defSubVis'         ] = $_REQUEST['defSubVis'];
      $form['assignmentType'    ] = $_REQUEST['assignmentType'];
      $form['allowAnonymous'    ] = $_REQUEST['allowAnonymous'];
      $form['allowLateUpload'   ] = $_REQUEST['allowLateUpload'];
    }
    // modify the command 'cmd' sent by the form
    $cmdToSend = "exEditAssig";
    // ask the display of the form
    $displayAssigForm = true;
  }
  
  /*--------------------------------------------------------------------
                        CREATE NEW ASSIGNMENT
  --------------------------------------------------------------------*/
  
  /*-----------------------------------
      STEP 2 : check & query
  -------------------------------------*/
  //--- create an assignment / form has been sent
  if( $cmd == 'exMkAssig' )
  {
    // form data have been handled before this point if the form was sent
    if( $formCorrectlySent )
    {
          $sql = "INSERT INTO `".$tbl_wrk_assignment."`
                  ( `title`,`description`, 
                    `authorized_content`, `authorize_anonymous`,
                    `start_date`, `end_date`, 
                    `def_submission_visibility`, `allow_late_upload`)
                  VALUES
                  ( \"".$title."\", \"".$assigDesc."\",
                    \"".$authorizedContent."\", \"".$_REQUEST['allowAnonymous']."\",
                    \"".$composedStartDate."\", \"".$composedEndDate."\",
                    \"".$_REQUEST['defSubVis']."\", \"".$_REQUEST['allowLateUpload']."\")";
    
          // execute the creation query and return id of inserted assignment
          $lastassigId = claro_sql_query_insert_id($sql);
          
          // create the assignment directory if query was successfull and dir not already exists
          $wrkAssigDir = $wrkDir."assig_".$lastassigId;
          if( $lastassigId && !is_dir( $wrkAssigDir ) )
          {
            mkdir( $wrkAssigDir , 0777 );
          }
          
          // move the uploaded file from temporary folder to the work assignment folder
          if( isset($prefillDocPath) && $prefillDocPath != "" )
          {
            if( ! @rename($wrkDir.$tmpWorkUrl, $wrkAssigDir."/".$prefillDocPath) )
            {
                  $dialogBox .= $langCannotCopyFile."<br />";
                  $formCorrectlySent = false;
            }
            // remove the temporary file
            @unlink($wrkDir.$tmpWorkUrl);
          }
          
          // confirmation message
          $dialogBox .= $langAssignmentAdded;
    }
    else
    {
      $cmd = 'rqMkAssig';
    }
  }
  
  /*-----------------------------------
      STEP 1 : display form
  -------------------------------------*/
  //--- create an assignment / display form
  if( $cmd == 'rqMkAssig' )
  {
    include($includePath."/lib/form.lib.php");
    
    if( !isset($_REQUEST['submitAssignment']) )
    {
      // set default values to prefill the form if nothing was posted
      $form['assigTitle'             ] = "";
      $form['assigDesc'           ] = "";
      $form['authorizedContent' ] = "FILE";
      $form['startDate'         ] = date("Y-m-d", mktime( 0,0,0,date("m"), date("d"), date("Y") ) );
      $form['startTime'         ] = date("H:i:00", mktime( date("H"),date("i"),0) );
      $form['endDate'           ] = date("Y-m-d", mktime( 0,0,0,date("m"), date("d"), date("Y")+1 ) );
      $form['endTime'           ] = date("H:i:00", mktime( date("H"),date("i"),0) );
      $form['defSubVis'         ] = "VISIBLE";
      $form['assignmentType'    ] = "INDIVIDUAL";
      $form['allowAnonymous'    ] = "YES";
      $form['allowLateUpload'   ] = "NO";
    }
    else
    {
      // there was an error in the form so display it with already modified values
      $form['assigTitle'          ] = $_REQUEST['assigTitle'];
      $form['assigDesc'           ] = $_REQUEST['assigDesc'];
      $form['authorizedContent' ] = $_REQUEST['authorizedContent'];      
      $form['startDate'         ] = $_REQUEST['startYear']."-".$_REQUEST['startMonth']."-".$_REQUEST['startDay'];
      $form['startTime'         ] = $_REQUEST['startHour'].":".$_REQUEST['startMinute'].":00";
      $form['endDate'           ] = $_REQUEST['endYear']."-".$_REQUEST['endMonth']."-".$_REQUEST['endDay'];
      $form['endTime'           ] = $_REQUEST['endHour'].":".$_REQUEST['endMinute'].":00";
      $form['defSubVis'         ] = $_REQUEST['defSubVis'];
      $form['assignmentType'    ] = $_REQUEST['assignmentType'];
      $form['allowAnonymous'    ] = $_REQUEST['allowAnonymous'];
      $form['allowLateUpload'   ] = $_REQUEST['allowLateUpload'];
    }
    
    // modify the command 'cmd' sent by the form
    $cmdToSend = "exMkAssig";
    // ask the display of the form
    $displayAssigForm = true;
  }
}

/*================================================================
                      DISPLAY
  ================================================================*/

/*--------------------------------------------------------------------
                            HEADER
  --------------------------------------------------------------------*/
$htmlHeadXtra[] =
"<script>
function confirmation (name)
{
	if (confirm(\" $langAreYouSureToDelete \"+ name + \" ? $langDeleteCaution \" ))
		{return true;}
	else
		{return false;}
}
</script>";

if( ( isset($displayAssigForm) && $displayAssigForm ) || ( isset($displayFeedbackForm) && $displayFeedbackForm ) )
{
      // bredcrump to return to the list when in a form
      $interbredcrump[]= array ("url"=>"../work/work.php", "name"=> $langAssignments);
      $nameTools = $langAssignment;
}
else
{
  $nameTools = $langAssignments;
  // to prevent parameters to be added in the breadcrumb
  $QUERY_STRING='';
}

include($includePath.'/claro_init_header.inc.php');

/*--------------------------------------------------------------------
                    TOOL TITLE
    --------------------------------------------------------------------*/

claro_disp_tool_title($nameTools);
  
 
if($is_allowedToEdit)
{

  /*--------------------------------------------------------------------
                          DIALOG BOX SECTION
    --------------------------------------------------------------------*/

  if ($dialogBox)
  {
          claro_disp_message_box($dialogBox);
  }

  /*--------------------------------------------------------------------
                        CREATE AND EDIT FORM
    --------------------------------------------------------------------*/
  if ( $displayAssigForm ) 
  {
?>
    <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data">
    <input type="hidden" name="cmd" value="<?php echo $cmdToSend; ?>">
<?php
  if( isset($_REQUEST['assigId']) )
  {
?>
    <input type="hidden" name="assigId" value="<?php echo $_REQUEST['assigId']; ?>">
<?php
  }
?>
    <table cellpadding="5">
      <tr>
        <td valign="top"><label for="assigTitle"><?php echo $langAssignmentTitle; ?>&nbsp;:</label></td>
        <td><input type="text" name="assigTitle" id="assigTitle" size="50" maxlength="200" value="<?php echo htmlentities($form['assigTitle']); ?>"></td>
      </tr>

      <tr>
        <td valign="top"><label for="assigDesc"><?php echo $langAssignmentDescription; ?>&nbsp;:<br /></label></td>
        <td>
<?php          
      claro_disp_html_area('assigDesc', $form['assigDesc']);
?> 
        </td>
      </tr>
      
      <tr>
        <td valign="top"><?php echo $langSubmissionType; ?>&nbsp;:</td>
        <td>
          <input type="radio" name="authorizedContent" id="authorizeFile" value="FILE" <?php if( $form['authorizedContent'] == "FILE" ) echo 'checked="checked"'; ?>>
            <label for="authorizeFile">&nbsp;<?php echo $langFileOnly; ?></label>
            <br />
          <input type="radio" name="authorizedContent" id="authorizeText" value="TEXT" <?php if( $form['authorizedContent'] == "TEXT" ) echo 'checked="checked"'; ?>>
            <label for="authorizeText">&nbsp;<?php echo $langTextOnly; ?></label>
            <br />
          <input type="radio" name="authorizedContent" id="authorizeTextFile" value="TEXTFILE" <?php if( $form['authorizedContent'] == "TEXTFILE" ) echo 'checked="checked"'; ?>>
            <label for="authorizeTextFile">&nbsp;<?php echo $langTextFile; ?></label>
            <br />
        </td>
      </tr>
      
      <tr>
        <td valign="top"><?php echo $langStartDate; ?>&nbsp;:</td>
        <td>
<?php
         echo claro_disp_date_form("startDay", "startMonth", "startYear", $form['startDate'])." ".claro_disp_time_form("startHour", "startMinute", $form['startTime']);
         echo "&nbsp;<small>".$langChooseDateHelper."</small>";
?>      
        </td>
      </tr>    
      
      <tr>
        <td valign="top"><?php echo $langEndDate; ?>&nbsp;:</td>
        <td>
<?php
         echo claro_disp_date_form("endDay", "endMonth", "endYear", $form['endDate'])." ".claro_disp_time_form("endHour", "endMinute", $form['endTime']);
         echo "&nbsp;<small>".$langChooseDateHelper."</small>";
?>      
        </td>
      </tr>
      
      <tr>
        <td valign="top"><?php echo $langDefSubVisibility; ?>&nbsp;:</td>
        <td>
          <input type="radio" name="defSubVis" id="visible" value="VISIBLE" <?php if($form['defSubVis'] == "VISIBLE") echo 'checked="checked"'; ?>>
            <label for="visible">&nbsp;<?php echo $langVisible; ?></label>
            <br />
          <input type="radio" name="defSubVis" id="invisible" value="INVISIBLE" <?php if($form['defSubVis'] == "INVISIBLE") echo 'checked="checked"'; ?>>
            <label for="invisible">&nbsp;<?php echo $langInvisible; ?></label>
            <br />
        </td>
      </tr>
      
      <tr>
        <td valign="top"><?php echo $langAssignmentType; ?>&nbsp;:</td>
        <td>
          <input type="radio" name="assignmentType" id="individual" value="INDIVIDUAL" <?php if($form['assignmentType'] == "INDIVIDUAL") echo 'checked="checked"'; ?>>
            <label for="individual">&nbsp;<?php echo $langIndividual; ?></label>
            <br />
          <input type="radio" name="assignmentType" id="group" value="GROUP" <?php if($form['assignmentType'] == "GROUP") echo 'checked="checked"'; ?>>
            <label for="group">&nbsp;<?php echo $langGroupAssignment; ?></label>
            <br />
        </td>
      </tr> 

      <tr>
        <td valign="top"><?php echo $langAllowAnonymous; ?>&nbsp;:</td>
        <td>
        <input type="radio" name="allowAnonymous" id="anonAllowed" value="YES" <?php if($form['allowAnonymous'] == "YES") echo 'checked="checked"'; ?>>
          <label for="anonAllowed">&nbsp;<?php echo $langAnonAllowed; ?></label>
          <br />
        <input type="radio" name="allowAnonymous" id="anonNotAllowed" value="NO" <?php if($form['allowAnonymous'] == "NO") echo 'checked="checked"'; ?>>
          <label for="anonNotAllowed">&nbsp;<?php echo $langAnonNotAllowed; ?></label>
          <br />
        </td>
      </tr>

      <tr>
        <td valign="top"><?php echo $langAllowLateUploadShort; ?>&nbsp;:</td>
        <td>
        <input type="radio" name="allowLateUpload" id="allowUpload" value="YES" <?php if($form['allowLateUpload'] == "YES") echo 'checked="checked"'; ?>>
          <label for="allowUpload">&nbsp;<?php echo $langAllowLateUpload; ?></label>
          <br />
        <input type="radio" name="allowLateUpload" id="preventUpload" value="NO" <?php if($form['allowLateUpload'] == "NO") echo 'checked="checked"'; ?>>
          <label for="preventUpload">&nbsp;<?php echo $langPreventLateUpload; ?></label>
          <br />
        </td>
      </tr>
      <tr>
        <td>&nbsp;</td>
        <td>
          <input type="submit" name="submitAssignment" value="<?php echo $langOk; ?>">
<?php
          claro_disp_button($_SERVER['PHP_SELF'], $langCancel);
?>
        </td>
      </tr>
      </table>
    </form>
<?php
  }
    /*--------------------------------------------------------------------
                        FEEDBACK FORM
    --------------------------------------------------------------------*/
  if( $displayFeedbackForm )
  {
?>
    <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data">
    <input type="hidden" name="cmd" value="exEditFeedback">
<?php
  if( isset($_REQUEST['assigId']) )
  {
?>
    <input type="hidden" name="assigId" value="<?php echo $_REQUEST['assigId']; ?>">
<?php
  }
?>
    <table cellpadding="5">
      <tr>
        <td valign="top" colspan="2"><b><?php echo $langFeedback; ?></b><p><?php echo $langFeedbackHelp; ?></p></td>
      </tr>
      <tr>
        <td valign="top"><label for="prefillText"><?php echo $langFeedbackText; ?>&nbsp;:<br /></label></td>
        <td>
<?php          
      claro_disp_html_area('prefillText', $form['prefillText']);
?> 
        </td>
      </tr>
<?php
    if( isset($form['currentPrefillDocPath']) && $form['currentPrefillDocPath'] != "" )
    {
          echo "<tr>\n"
               ."<td valign=\"top\">"
               .$langCurrentFeedbackFile;
          // display the name of the file, with a link to it, an explanation of what to to to replace it and a checkbox to delete it
          $completeFileUrl = $currentCourseRepositoryWeb."work/assig_".$_REQUEST['assigId']."/".$form['currentPrefillDocPath'];
          echo "&nbsp;:<input type=\"hidden\" name=\"currentPrefillDocPath\" value=\"".$form['currentPrefillDocPath']."\">"
                ."</td>\n"
                ."<td>"
                ."<a href=\"".$completeFileUrl."\">".$form['currentPrefillDocPath']."</a>"
                ."<br /><input type=\"checkBox\" name=\"delFeedbackFile\" id=\"delFeedbackFile\">"
                ."<label for=\"delFeedbackFile\">".$langExplainDeleteFile." ".$langExplainReplaceFile."</label> "
                ."</td>\n"
                ."</tr>\n\n";
    }
?>
      <tr>
        <td valign="top"><label for="prefillDocPath"><?php echo $langFeedbackFile; ?>&nbsp;:<br /></label></td>
        <td>
        <input type="file" name="prefillDocPath" id="prefillDocPath" size="30">
        </td>
      </tr>
 
      <tr>
        <td valign="top"><?php echo $langFeedbackSubmit; ?>&nbsp;:</td>
        <td>
        <input type="radio" name="prefillSubmit" id="prefillSubmitEndDate" value="ENDDATE" <?php if($form['prefillSubmit'] == "ENDDATE") echo 'checked="checked"'; ?>>
          <label for="prefillSubmitEndDate">&nbsp;<?php echo $langSubmitFeedbackAfterEndDate." (".claro_disp_localised_date($dateTimeFormatLong, $form['unix_end_date']).")"; ?></label>
          <br />
        <input type="radio" name="prefillSubmit" id="prefillSubmitAfterPost" value="AFTERPOST" <?php if($form['prefillSubmit'] == "AFTERPOST") echo 'checked="checked"'; ?>>
          <label for="prefillSubmitAfterPost">&nbsp;<?php echo $langSubmitFeedbackAfterPost; ?></label>
          <br />
        </td>
      </tr>
      
      <tr>
        <td>&nbsp;</td>
        <td>
          <input type="submit" name="submitFeedback" value="<?php echo $langOk; ?>">
<?php
          claro_disp_button($_SERVER['PHP_SELF'], $langCancel);
?>          
        </td>
      </tr>
      </table>
    </form>
<?php
  }
}

/*--------------------------------------------------------------------
                            ASSIGNMENT LIST
    --------------------------------------------------------------------*/
if( !$displayAssigForm && !$displayFeedbackForm )
{
    /*--------------------------------------------------------------------
                        INTRODUCTION SECTION
      --------------------------------------------------------------------*/
    
    $moduleId = $course_tool['id']; // Id of the Student Paper introduction Area
    $helpAddIntroText=$langIntroWork;
    include($includePath."/introductionSection.inc.php");  

    /*--------------------------------------------------------------------
                        ADMIN LINKS
      --------------------------------------------------------------------*/
    if( $is_allowedToEdit )
    {
      // link to create a new assignment
      echo "&nbsp;<a href=\"".$_SERVER['PHP_SELF']."?cmd=rqMkAssig\">".$langCreateAssignment."</a>\n";
    }

    /*--------------------------------------------------------------------
                                  LIST
      --------------------------------------------------------------------*/
    // if user come from a group
    if( isset($_gid) && isset($is_groupAllowed) && $is_groupAllowed ) 
    {
      $sql = "SELECT `id`, `title`, `visibility` 
              FROM `".$tbl_wrk_assignment."`
              WHERE `assignment_type` = 'GROUP'
              ORDER BY `title` ASC";    
    }
    else
    {
      $sql = "SELECT `id`, `title`, `visibility`
              FROM `".$tbl_wrk_assignment."` 
              ORDER BY `title` ASC";
    }          
    $assignmentList = claro_sql_query_fetch_all($sql);

    echo "<table class=\"claroTable\" width=\"100%\">\n"
          ."<thead>\n"
          ."<tr class=\"headerX\">\n"
          ."<th>".$langAssignmentTitle."</th>\n";
          
    if ( $is_allowedToEdit ) 
    {
        echo  "<th>".$langModify."</th>\n"
              ."<th>".$langEditFeedback."</th>\n"
              ."<th>".$langDelete."</th>\n"
              ."<th>".$langVisibility."</th>\n";
    }
    echo "</tr>\n"
        ."</thead>\n\n"
        ."<tbody>\n";
    foreach($assignmentList as $anAssignment)
    {
    
      if ($anAssignment['visibility'] == "INVISIBLE")
			{
				if ($is_allowedToEdit)
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
      
      echo "<tr align=\"center\"".$style.">\n"
          ."<td align=\"left\"><a href=\"workList.php?assigId=".$anAssignment['id']."\">".$anAssignment['title']."</a></td>\n";
      if( $is_allowedToEdit )
      {
        echo "<td><a href=\"".$_SERVER['PHP_SELF']."?cmd=rqEditAssig&assigId=".$anAssignment['id']."\"><img src=\"".$clarolineRepositoryWeb."img/edit.gif\" border=\"0\" alt=\"$langModify\"></a></td>\n"
            ."<td><a href=\"".$_SERVER['PHP_SELF']."?cmd=rqEditFeedback&assigId=".$anAssignment['id']."\"><img src=\"".$clarolineRepositoryWeb."img/edit.gif\" border=\"0\" alt=\"$langModify\"></a></td>\n"
            ."<td><a href=\"".$_SERVER['PHP_SELF']."?cmd=exRmAssig&assigId=".$anAssignment['id']."\" onClick=\"return confirmation('",addslashes($anAssignment['title']),"');\">"
            ."<img src=\"".$clarolineRepositoryWeb."img/delete.gif\" border=\"0\" alt=\"$langDelete\"></a></td>\n"
            ."<td>";
        if ($anAssignment['visibility'] == "INVISIBLE")
        {
            echo	"<a href=\"".$_SERVER['PHP_SELF']."?cmd=exChVis&assigId=".$anAssignment['id']."&vis=v\">"
                  ."<img src=\"".$clarolineRepositoryWeb."img/invisible.gif\" border=\"0\" alt=\"$langMakeVisible\">"
                  ."</a>";
        }
        else
        {
            echo	"<a href=\"".$_SERVER['PHP_SELF']."?cmd=exChVis&assigId=".$anAssignment['id']."&vis=i\">"
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
