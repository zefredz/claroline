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

include($includePath.'/lib/events.lib.inc.php');
include($includePath.'/conf/work.conf.inc.php');

$tbl_cdb_names = claro_sql_get_course_tbl();
$tbl_wrk_session      = $tbl_cdb_names['wrk_session'      ];
$tbl_wrk_submission   = $tbl_cdb_names['wrk_submission'   ];    


$currentUserFirstName       = $_user['firstName'];
$currentUserLastName        = $_user['lastName'];


$nameTools = $langWorks;
// to prevent parameters to be added in the breadcrumb
$QUERY_STRING=''; 

include($includePath.'/claro_init_header.inc.php');
//if (!$_cid) 	claro_disp_select_course();

if ( ! $is_courseAllowed)
	claro_disp_auth_form();
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
$wrkDir           = $currentCourseRepositorySys.'work/'; //directory path to create session dirs

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
                          HANDLING FORM DATA
  =============================================================================*/
// execute this after a form has been send
// this instruction bloc will set some vars that will be used in the corresponding queries
if( isset($_REQUEST['submitSession']) ) 
{
    $formCorrectlySent = true;
    
    // title is a mandatory element     
    $title = trim( strip_tags($_REQUEST['sesTitle']) );
    // session id is another one
        
    if( empty($title) )
    {
      $dialogBox .= $langSessionTitleRequired."<br />";
      $formCorrectlySent = false;
    }
    else
    {
      // check if title already exists
      if( isset($_REQUEST['sesId']) )
      {
        // if sesId isset it means we are modifying a session
        // and a session can have the same title as itself
        $sql = "SELECT `title`
                 FROM `".$tbl_wrk_session."`
                WHERE `title` = '".claro_addslashes($title)."'
                  AND `id` != ".$_REQUEST['sesId'];
      }
      else
      {
        // creating a session
        $sql = "SELECT `title`
                 FROM `".$tbl_wrk_session."`
                WHERE `title` = '".claro_addslashes($title)."'";
      }
      
      $query = claro_sql_query($sql);
      
      if(mysql_num_rows($query) != 0 )
      {
        $dialogBox .= $langSesTitleAlreadyExists."<br />";
        $formCorrectlySent = false;
      }
      else
      {
        $wrkForm['sesTitle'] = $_REQUEST['sesTitle'];
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
    if( trim( strip_tags($_REQUEST['sesDesc']), $allowedTags ) == "" ) 
    {
      $sesDesc = ""; // avoid multiple br tags to be added when editing an empty form
    }
    else
    {
      $sesDesc = claro_addslashes( trim($_REQUEST['sesDesc']) );
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
    
    // standard grading 
    // check if there is text in it 
    if( trim( strip_tags($_REQUEST['prefillText']), $allowedTags ) == "" ) 
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
                if( isset($_REQUEST['sesId']) )
                {
                    // if edit of a session we know its sesId so we don't have to use a tmp directory
                    $tmpWorkUrl = "ws".$_REQUEST['sesId']."/".$prefillDocPath;
                    
                    if( ! @copy($_FILES['prefillDocPath']['tmp_name'], $wrkDir.$tmpWorkUrl) )
                    {
                          $dialogBox .= $langCannotCopyFile."<br />";
                          $formCorrectlySent = false;
                    }
                    
                    // remove the previous file if there was one
                    if( isset($_REQUEST['currentPrefillDocPath']) )
                    {
                          @unlink($wrkDir."ws".$_REQUEST['sesId']."/".$_REQUEST['currentPrefillDocPath']);
                    }
                }
                else
                {
                    // put the file in a tmp location, remove it from there at the end of the script
                    // we don't know the session id yet so we cannot already put it in the right folder
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
    elseif( isset($_REQUEST['currentPrefillDocPath']) && !isset($_REQUEST['delGradingFile']) )
    {
      // reuse the old file as none has been uploaded and no delete was asked
      $prefillDocPath = $_REQUEST['currentPrefillDocPath'];
    }
    elseif( isset($_REQUEST['currentPrefillDocPath']) && isset($_REQUEST['delGradingFile']) )
    {
      // delete hte file was requested
      $prefillDocPath = ""; // empty DB field
      @unlink($wrkDir."ws".$_REQUEST['sesId']."/".$_REQUEST['currentPrefillDocPath']); // physically remove the file
    }
    else
    {
      $prefillDocPath = "";
    }
    
    $composedPrefillDate = $_REQUEST['prefillYear']."-"
                        .$_REQUEST['prefillMonth']."-"
                        .$_REQUEST['prefillDay']." "
                        .$_REQUEST['prefillHour'].":"
                        .$_REQUEST['prefillMinute'].":00";

} // if( isset($_REQUEST['submitSession']) ) // handling form data 




if($is_allowedToEdit)
{
  /*--------------------------------------------------------------------
                        CHANGE VISIBILITY
  --------------------------------------------------------------------*/

  // change visibility of a work session
  if( $cmd == 'exChVis' )
  {
    if( isset($_REQUEST['vis']) )
    {
      $_REQUEST['vis'] == "v" ? $visibility = 'VISIBLE' : $visibility = 'INVISIBLE';
      
      $sql = "UPDATE `".$tbl_wrk_session."`
                 SET `sess_visibility` = '$visibility'
               WHERE `id` = ".$_REQUEST['sesId']."
                 AND `sess_visibility` != '$visibility'";
      claro_sql_query ($sql);
      
    }
  }

  /*--------------------------------------------------------------------
                        DELETE A SESSION
  --------------------------------------------------------------------*/

  // delete/remove a work session
  if( $cmd == 'exRmSes' )
  {
    // delete all works in this session if the delete of the files worked
    if( my_delete($wrkDir."ws".$_REQUEST['sesId']) )
    {
      $sql = "DELETE FROM `".$tbl_wrk_submission."`
              WHERE `session_id` = ".$_REQUEST['sesId'];
      claro_sql_query($sql);
    }    
    
    $sql = "DELETE FROM `".$tbl_wrk_session."`
            WHERE `id` = ".$_REQUEST['sesId'];

    claro_sql_query($sql);
    
    $dialogBox .= $langSessionDeleted;
    
  }
  
  /*--------------------------------------------------------------------
                        MODIFY A SESSION
  --------------------------------------------------------------------*/
  /*-----------------------------------
      STEP 2 : check & query
  -------------------------------------*/
  // edit a work session / form has been sent
  if( $cmd == 'exEditSes' )
  {
    // form data have been handled before this point if the form was sent
    if( isset($_REQUEST['sesId']) && $formCorrectlySent )
    {
          $sql = "UPDATE `".$tbl_wrk_session."`
                  SET `title` = \"".$title."\",
                      `description` = \"".$sesDesc."\", 
                      `session_type` = \"".$_REQUEST['sessionType']."\", 
                      `authorized_content` = \"".$authorizedContent."\",  
                      `authorize_anonymous` = \"".$_REQUEST['allowAnonymous']."\",
                      `start_date` = \"".$composedStartDate."\", 
                      `end_date` = \"".$composedEndDate."\", 
                      `def_submission_visibility` = \"".$_REQUEST['defSubVis']."\", 
                      `allow_late_upload` = \"".$_REQUEST['allowLateUpload']."\",
                      `prefill_text` = \"".$prefillText."\",
                      `prefill_doc_path` = \"".$prefillDocPath."\",
                      `prefill_date` = \"".$composedPrefillDate."\"
                  WHERE `id` = ".$_REQUEST['sesId'];
          claro_sql_query($sql);
          $dialogBox .= $langSessionEdited;
    } 
    else
    {
      $cmd = 'rqEditSes';
    }
  }
  /*-----------------------------------
      STEP 1 : display form
  -------------------------------------*/
  // edit a work session / display the form
  if( $cmd == 'rqEditSes' )
  {
    include($includePath."/lib/form.lib.php");
    
    // check if it was already sent
    if( !isset($_REQUEST['submitSession'] ) )
    {
        // get current settings to fill in the form
        $sql = "SELECT * 
                FROM `".$tbl_wrk_session."`
                WHERE `id` = ".$_REQUEST['sesId'];
        list($modifiedSession) = claro_sql_query_fetch_all($sql);
        
    
        // set values to pre-fill the form
        $form['sesTitle'          ] = $modifiedSession['title'];
        $form['sesDesc'       ] = $modifiedSession['description'];
        
        list($form['startDate'], $form['startTime']) = split(' ', $modifiedSession['start_date']);
        list($form['endDate'], $form['endTime']) = split(' ', $modifiedSession['end_date']);
        
        // following if statements could have been writted in a shorter way but this way they are 
        // ready for a db change, or 
        if( $modifiedSession['authorized_content'] == "TEXTFILE" )
        {
          $form['authorizedContent' ] = "TEXTFILE";
        }
        elseif( $modifiedSession['authorized_content'] == "TEXT" )
        {
          $form['authorizedContent' ] = "TEXT";
        }
        elseif( $modifiedSession['authorized_content'] == "FILE" )
        {
          $form['authorizedContent' ] = "FILE";
        }
        
        if( $modifiedSession['def_submission_visibility'] == "VISIBLE" )
        {
          $form['defSubVis'] = "VISIBLE";
        }
        else
        {
          $form['defSubVis'] = "INVISIBLE";
        }
        
        if( $modifiedSession['session_type'] == "INDIVIDUAL" )
        {
          $form['sessionType'] = "INDIVIDUAL";
        }
        else
        {
          $form['sessionType'] = "GROUP";
        }
        
        if( $modifiedSession['authorize_anonymous'] == "YES" )
        {
          $form['allowAnonymous'] = "YES";
        }
        else
        {
          $form['allowAnonymous'] = "NO";
        }
        
        if( $modifiedSession['allow_late_upload'] == "YES" )
        {
          $form['allowLateUpload'] = "YES";
        }
        else
        {
          $form['allowLateUpload'] = "NO";
        }
        
        // standard grading
        $form['prefillText'       ] = $modifiedSession['prefill_text'];
        $form['currentPrefillDocPath'] = $modifiedSession['prefill_doc_path'];
        list($form['prefillDate'], $form['prefillTime']) = split(' ', $modifiedSession['prefill_date']);
    }
    else
    {
      // there was an error in the form so display it with already modified values
      $form['sesTitle'          ] = $_REQUEST['sesTitle'];
      $form['sesDesc'           ] = $_REQUEST['sesDesc'];
      $form['authorizedContent' ] = $_REQUEST['authorizedContent'];      
      $form['startDate'         ] = $_REQUEST['startYear']."-".$_REQUEST['startMonth']."-".$_REQUEST['startDay'];
      $form['startTime'         ] = $_REQUEST['startHour'].":".$_REQUEST['startMinute'].":00";
      $form['endDate'           ] = $_REQUEST['endYear']."-".$_REQUEST['endMonth']."-".$_REQUEST['endDay'];
      $form['endTime'           ] = $_REQUEST['endHour'].":".$_REQUEST['endMinute'].":00";
      $form['defSubVis'         ] = $_REQUEST['defSubVis'];
      $form['sessionType'       ] = $_REQUEST['sessionType'];
      $form['allowAnonymous'    ] = $_REQUEST['allowAnonymous'];
      $form['allowLateUpload'   ] = $_REQUEST['allowLateUpload'];
      $form['prefillText'       ] = $_REQUEST['prefillText'];
      $form['currentPrefillDocPath'] = $_REQUEST['currentPrefillDocPath'];
      $form['prefillDate'       ] = $_REQUEST['prefillYear']."-".$_REQUEST['prefillMonth']."-".$_REQUEST['prefillDay'];
      $form['prefillTime'       ] = $_REQUEST['prefillHour'].":".$_REQUEST['prefillMinute'].":00";
    }
    // modify the command 'cmd' sent by the form
    $cmdToSend = "exEditSes";
    // ask the display of the form
    $displaySesForm = true;
  }
  
  /*--------------------------------------------------------------------
                        CREATE NEW SESSION
  --------------------------------------------------------------------*/
  
  /*-----------------------------------
      STEP 2 : check & query
  -------------------------------------*/
  //--- create a work session / form has been sent
  if( $cmd == 'exMkSes' )
  {
    // form data have been handled before this point if the form was sent
    if( $formCorrectlySent )
    {
          $sql = "INSERT INTO `".$tbl_wrk_session."`
                  ( `title`,`description`, `session_type`, 
                    `authorized_content`, `authorize_anonymous`,
                    `start_date`, `end_date`, 
                    `def_submission_visibility`, `allow_late_upload`,
                    `prefill_text`,`prefill_doc_path`,`prefill_date`)
                  VALUES
                  ( \"".$title."\", \"".$sesDesc."\", \"".$_REQUEST['sessionType']."\",
                    \"".$authorizedContent."\", \"".$_REQUEST['allowAnonymous']."\",
                    \"".$composedStartDate."\", \"".$composedEndDate."\",
                    \"".$_REQUEST['defSubVis']."\", \"".$_REQUEST['allowLateUpload']."\",
                    \"".$prefillText."\", \"".$prefillDocPath."\", \"".$composedPrefillDate."\")";
    
          // execute the creation query and return id of inserted session
          $lastSesId = claro_sql_query_insert_id($sql);
          
          // create the session directory if query was successfull and dir not already exists
          $wrkSessDir = $wrkDir."ws".$lastSesId;
          if( $lastSesId && !is_dir( $wrkSessDir ) )
          {
            mkdir( $wrkSessDir , 0777 );
          }
          
          // move the uploaded file from temporary folder to the work session folder
          if( isset($prefillDocPath) && $prefillDocPath != "" )
          {
            if( ! @rename($wrkDir.$tmpWorkUrl, $wrkSessDir."/".$prefillDocPath) )
            {
                  $dialogBox .= $langCannotCopyFile."<br />";
                  $formCorrectlySent = false;
            }
            // remove the temporary file
            @unlink($wrkDir.$tmpWorkUrl);
          }
          
          // confirmation message
          $dialogBox .= $langSessionAdded;
    }
    else
    {
      $cmd = 'rqMkSes';
    }
  }
  
  /*-----------------------------------
      STEP 1 : display form
  -------------------------------------*/
  //--- create a work session / display form
  if( $cmd == 'rqMkSes' )
  {
    include($includePath."/lib/form.lib.php");
    
    if( !isset($_REQUEST['submitSession']) )
    {
      // set default values to prefill the form if nothing was posted
      $form['sesTitle'             ] = "";
      $form['sesDesc'           ] = "";
      $form['authorizedContent' ] = "FILE";
      $form['startDate'         ] = date("Y-m-d", mktime( 0,0,0,date("m"), date("d"), date("Y") ) );
      $form['startTime'         ] = date("H:i:00", mktime( date("H"),date("i"),0) );
      $form['endDate'           ] = date("Y-m-d", mktime( 0,0,0,date("m"), date("d"), date("Y")+1 ) );
      $form['endTime'           ] = date("H:i:00", mktime( date("H"),date("i"),0) );
      $form['defSubVis'         ] = "VISIBLE";
      $form['sessionType'       ] = "INDIVIDUAL";
      $form['allowAnonymous'    ] = "YES";
      $form['allowLateUpload' ] = "NO";
      $form['prefillText'       ] = "";
      $form['prefillDate'       ] = date("Y-m-d", mktime( 0,0,0,date("m"), date("d"), date("Y")+1 ) );
      $form['prefillTime'       ] = date("H:i:00", mktime( date("H"),date("i"),0) );
    }
    else
    {
      // there was an error in the form so display it with already modified values
      $form['sesTitle'          ] = $_REQUEST['sesTitle'];
      $form['sesDesc'           ] = $_REQUEST['sesDesc'];
      $form['authorizedContent' ] = $_REQUEST['authorizedContent'];      
      $form['startDate'         ] = $_REQUEST['startYear']."-".$_REQUEST['startMonth']."-".$_REQUEST['startDay'];
      $form['startTime'         ] = $_REQUEST['startHour'].":".$_REQUEST['startMinute'].":00";
      $form['endDate'           ] = $_REQUEST['endYear']."-".$_REQUEST['endMonth']."-".$_REQUEST['endDay'];
      $form['endTime'           ] = $_REQUEST['endHour'].":".$_REQUEST['endMinute'].":00";
      $form['defSubVis'         ] = $_REQUEST['defSubVis'];
      $form['sessionType'       ] = $_REQUEST['sessionType'];
      $form['allowAnonymous'    ] = $_REQUEST['allowAnonymous'];
      $form['allowLateUpload'   ] = $_REQUEST['allowLateUpload'];
      $form['prefillText'       ] = $_REQUEST['prefillText'];
      $form['prefillDate'       ] = $_REQUEST['prefillYear']."-".$_REQUEST['prefillMonth']."-".$_REQUEST['prefillDay'];
      $form['prefillTime'       ] = $_REQUEST['prefillHour'].":".$_REQUEST['prefillMinute'].":00";
    }
    
    // modify the command 'cmd' sent by the form
    $cmdToSend = "exMkSes";
    // ask the display of the form
    $displaySesForm = true;
  }
}

/*================================================================
                      DISPLAY
  ================================================================*/

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
  if ( $displaySesForm ) 
  {
?>
    <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data">
    <input type="hidden" name="cmd" value="<?php echo $cmdToSend; ?>">
<?php
  if( isset($_REQUEST['sesId']) )
  {
?>
    <input type="hidden" name="sesId" value="<?php echo $_REQUEST['sesId']; ?>">
<?php
  }
?>
    <table cellpadding="5">
      <tr>
        <td valign="top"><label for="sesTitle"><?php echo $langSessionTitle; ?>&nbsp;:</label></td>
        <td><input type="text" name="sesTitle" id="sesTitle" size="50" maxlength="200" value="<?php echo htmlentities($form['sesTitle']); ?>"></td>
      </tr>

      <tr>
        <td valign="top"><label for="sesDesc"><?php echo $langSessionDescription; ?>&nbsp;:<br /></label></td>
        <td>
<?php          
      claro_disp_html_area('sesDesc', $form['sesDesc']);
?> 
        </td>
      </tr>
      
      <tr>
        <td valign="top"><?php echo $langSubmissionType; ?>&nbsp;:</td>
        <td>
          <input type="radio" name="authorizedContent" id="authorizeFile" value="FILE" <?php if( $form['authorizedContent'] == "FILE" ) echo 'checked="checked"'; ?>><label for="authorizeFile">&nbsp;<?php echo $langFileOnly; ?></label><br />
          <input type="radio" name="authorizedContent" id="authorizeText" value="TEXT" <?php if( $form['authorizedContent'] == "TEXT" ) echo 'checked="checked"'; ?>><label for="authorizeText">&nbsp;<?php echo $langTextOnly; ?></label><br />
          <input type="radio" name="authorizedContent" id="authorizeTextFile" value="TEXTFILE" <?php if( $form['authorizedContent'] == "TEXTFILE" ) echo 'checked="checked"'; ?>><label for="authorizeTextFile">&nbsp;<?php echo $langTextFile; ?></label><br />
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
          <input type="radio" name="defSubVis" id="visible" value="VISIBLE" <?php if($form['defSubVis'] == "VISIBLE") echo 'checked="checked"'; ?>><label for="visible">&nbsp;<?php echo $langVisible; ?></label><br />
          <input type="radio" name="defSubVis" id="invisible" value="INVISIBLE" <?php if($form['defSubVis'] == "INVISIBLE") echo 'checked="checked"'; ?>><label for="invisible">&nbsp;<?php echo $langInvisible; ?></label><br />
        </td>
      </tr>
      <!--
      <tr>
        <td valign="top"><?php echo $langSessionType; ?>&nbsp;:</td>
        <td>
          <input type="radio" name="sessionType" id="individual" value="INDIVIDUAL" <?php if($form['sessionType'] == "INDIVIDUAL") echo 'checked="checked"'; ?>><label for="individual">&nbsp;<?php echo $langIndividual; ?></label><br />
          <input type="radio" name="sessionType" id="group" value="GROUP" <?php if($form['sessionType'] == "GROUP") echo 'checked="checked"'; ?>><label for="group">&nbsp;<?php echo $langGroup; ?></label><br />
        </td>
      </tr> 
      -->
      <tr>
        <td valign="top"><?php echo $langAllowAnonymous; ?>&nbsp;:</td>
        <td>
        <input type="radio" name="allowAnonymous" id="anonAllowed" value="YES" <?php if($form['allowAnonymous'] == "YES") echo 'checked="checked"'; ?>><label for="anonAllowed">&nbsp;<?php echo $langAnonAllowed; ?></label><br />
        <input type="radio" name="allowAnonymous" id="anonNotAllowed" value="NO" <?php if($form['allowAnonymous'] == "NO") echo 'checked="checked"'; ?>><label for="anonNotAllowed">&nbsp;<?php echo $langAnonNotAllowed; ?></label><br />
        </td>
      </tr>

      <tr>
        <td valign="top"><?php echo $langAllowLateUploadShort; ?>&nbsp;:</td>
        <td>
        <input type="radio" name="allowLateUpload" id="allowUpload" value="YES" <?php if($form['allowLateUpload'] == "YES") echo 'checked="checked"'; ?>><label for="allowUpload">&nbsp;<?php echo $langAllowLateUpload; ?></label><br />
        <input type="radio" name="allowLateUpload" id="preventUpload" value="NO" <?php if($form['allowLateUpload'] == "NO") echo 'checked="checked"'; ?>><label for="preventUpload">&nbsp;<?php echo $langPreventLateUpload; ?></label><br />
        </td>
      </tr>
      
      
      <tr>
        <td valign="top" colspan="2"><b><?php echo $langStandardGrading; ?></b><p><?php echo $langStandardGradingHelp; ?></p></td>
      </tr>
      <tr>
        <td valign="top"><label for="prefillText"><?php echo $langStandardGradingText; ?>&nbsp;:<br /></label></td>
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
             .$langCurrentStandardGradingFile;
        // display the name of the file, with a link to it, an explanation of what to to to replace it and a checkbox to delete it
        $completeFileUrl = $currentCourseRepositoryWeb."work/ws".$_REQUEST['sesId']."/".$form['currentPrefillDocPath'];
        echo "&nbsp;:<input type=\"hidden\" name=\"currentPrefillDocPath\" value=\"".$form['currentPrefillDocPath']."\">"
              ."</td>\n"
              ."<td>"
              ."<a href=\"".$completeFileUrl."\">".$form['currentPrefillDocPath']."</a>"
              ."<br /><input type=\"checkBox\" name=\"delGradingFile\" id=\"delGradingFile\">"
              ."<label for=\"delGradingFile\">".$langExplainModifyAttachedfile."</label> "
              ."</td>\n"
              ."</tr>\n\n";
  }
?>
      <tr>
        <td valign="top"><label for="prefillDocPath"><?php echo $langStandardGradingFile; ?>&nbsp;:<br /></label></td>
        <td>
        <input type="file" name="prefillDocPath" id="prefillDocPath" size="30">
        </td>
      </tr>
      <tr>
        <td valign="top"><?php echo $langStandardGradingDate; ?>&nbsp;:</td>
        <td>
<?php
         echo claro_disp_date_form("prefillDay", "prefillMonth", "prefillYear", $form['prefillDate'])." ".claro_disp_time_form("prefillHour", "prefillMinute", $form['prefillTime']);
         echo "&nbsp;<small>".$langChooseDateHelper."</small>";
?>      
        </td>
      </tr>  
      
      
      <tr>
        <td colspan="2" align="center">
        <input type="submit" name="submitSession" value="<?php echo $langOk; ?>">
        </td>
      </tr>
      </table>
    </form>
<?php
  }
}

/*--------------------------------------------------------------------
                            SESSION LIST
    --------------------------------------------------------------------*/
if( !$displaySesForm )
{
    /*--------------------------------------------------------------------
                        INTRODUCTION SECTION
      --------------------------------------------------------------------*/
    
    $moduleId = $course_tool['id']; // Id of the Student Paper introduction Area
    $langHelpAddIntroText=$langIntroWork;
    include($includePath."/introductionSection.inc.php");  

    /*--------------------------------------------------------------------
                        ADMIN LINKS
      --------------------------------------------------------------------*/
    if( $is_allowedToEdit )
    {
      // link to create a new session
      echo "&nbsp;<a href=\"".$_SERVER['PHP_SELF']."?cmd=rqMkSes\">".$langCreateSession."</a>\n";
    }

    /*--------------------------------------------------------------------
                                  LIST
      --------------------------------------------------------------------*/
    //if user come from a group
    if( isset($_gid) && isset($is_groupAllowed) && $is_groupAllowed ) 
    {
      $sql = "SELECT `id`, `title`, `sess_visibility` 
              FROM `".$tbl_wrk_session."`
              WHERE `session_type` = 'GROUP'
              ORDER BY `title` ASC";    
    }
    else
    {
      $sql = "SELECT `id`, `title`, `sess_visibility`
              FROM `".$tbl_wrk_session."` 
              ORDER BY `title` ASC";
    }          
    $sessionList = claro_sql_query_fetch_all($sql);

    echo "<table class=\"claroTable\" width=\"100%\">\n"
          ."<tr class=\"headerX\">\n"
          ."<th>".$langSessionTitle."</th>\n";
          
    if ( $is_allowedToEdit ) 
    {
        echo  "<th>".$langModify."</th>\n"
              ."<th>".$langDelete."</th>\n"
              ."<th>".$langVisibility."</th>\n";
    }
    echo "</tr>\n\n"
        ."<tbody>\n";
    foreach($sessionList as $wrkSession)
    {
    
      if ($wrkSession['sess_visibility'] == "INVISIBLE")
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
          ."<td align=\"left\"><a href=\"workList.php?sesId=".$wrkSession['id']."\">".$wrkSession['title']."</a></td>\n";
      if( $is_allowedToEdit )
      {
        echo "<td><a href=\"".$_SERVER['PHP_SELF']."?cmd=rqEditSes&sesId=".$wrkSession['id']."\"><img src=\"".$clarolineRepositoryWeb."img/edit.gif\" border=\"0\" alt=\"$langModify\"></a></td>\n"
            ."<td><a href=\"".$_SERVER['PHP_SELF']."?cmd=exRmSes&sesId=".$wrkSession['id']."\" onClick=\"return confirmation('",addslashes($wrkSession['title']),"');\"><img src=\"".$clarolineRepositoryWeb."img/delete.gif\" border=\"0\" alt=\"$langDelete\"></a></td>\n"
            ."<td>";
        if ($wrkSession['sess_visibility'] == "INVISIBLE")
        {
            echo	"<a href=\"".$_SERVER['PHP_SELF']."?cmd=exChVis&sesId=".$wrkSession['id']."&vis=v\">"
                  ."<img src=\"".$clarolineRepositoryWeb."img/invisible.gif\" border=\"0\" alt=\"$langMakeVisible\">"
                  ."</a>";
        }
        else
        {
            echo	"<a href=\"".$_SERVER['PHP_SELF']."?cmd=exChVis&sesId=".$wrkSession['id']."&vis=i\">"
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
