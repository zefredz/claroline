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
	if (confirm(\" $langAreYouSureToDelete \"+ name + \" ?  \" ))
		{return true;}
	else
		{return false;}
}
</script>";

$interbredcrump[]= array ("url"=>"../work/work.php", "name"=> $langWorks);

include($includePath.'/lib/events.lib.inc.php');
include($includePath.'/conf/work.conf.inc.php');

$tbl_mdb_names = claro_sql_get_main_tbl();
$tbl_user            = $tbl_mdb_names['user'];

$tbl_cdb_names = claro_sql_get_course_tbl();
$tbl_wrk_session      = $tbl_cdb_names['wrk_session'      ];
$tbl_wrk_submission   = $tbl_cdb_names['wrk_submission'   ];    

$tbl_group_team       = $tbl_cdb_names['group_team'       ];


$currentUserFirstName       = $_user['firstName'];
$currentUserLastName        = $_user['lastName'];


$nameTools = $langWrkSession;
// to prevent parameters to be added in the breadcrumb
$QUERY_STRING='sesId='.$_REQUEST['sesId']; 

include($includePath.'/claro_init_header.inc.php');
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
$wrkDir           = $currentCourseRepositorySys."work/"; //directory path to upload

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
                  WORK SESSION INFORMATIONS
  --------------------------------------------------------------------*/
if( isset($_REQUEST['sesId']) && !empty($_REQUEST['sesId']) )
{
      // we need to know the session settings
      $sql = "SELECT *
                FROM `".$tbl_wrk_session."`
                WHERE `id` = ".$_REQUEST['sesId'];
      
      list($wrkSession) = claro_sql_query_fetch_all($sql);
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

  /*--------------------------------------------------------------------
                        WORK FORM DATA
  --------------------------------------------------------------------*/
// this instruction bloc will set some vars that will be used in the corresponding queries
// $wrkForm['fileName'] , $wrkForm['title'] , $wrkForm['authors']
if( isset($_REQUEST['submitWrk']) ) 
{

      $formCorrectlySent = true;
      
      if ( is_uploaded_file($_FILES['wrkFile']['tmp_name']) && $wrkSession['authorized_content'] != "TEXT" )
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
                  
                  $wrkForm['fileName'] = uniqid('').$newFileName;
                  
                  $wrkUrl = "ws".$_REQUEST['sesId']."/".$wrkForm['fileName'];
                  
                  if( !is_dir( $wrkDir."ws".$_REQUEST['sesId'] ) )
                  {
                        mkdir( $wrkDir."ws".$_REQUEST['sesId'] , 0777 );
                  }
                  
                  if( ! copy($_FILES['wrkFile']['tmp_name'], $wrkDir.$wrkUrl) )
                  {
                        $dialogBox .= $langCannotCopyFile."<br />";
                        $formCorrectlySent = false;
                  }
                  else
                  {
                        // file sending shows no error
                        // $formCorrectlySent stay true;
                  }
            }
      }
      elseif( $wrkSession['authorized_content'] == "FILE" )
      {
            if( isset($_REQUEST['currentWrkUrl']) )
            {
                  // if there was already a file and nothing was provided to replace it, reuse it off course
                  $wrkForm['fileName'] = $_REQUEST['currentWrkUrl'];
            }
            else
            {
                  // if the main thing to provide is a file and that no file was sent
                  $dialogBox .= $langFileRequired."<br />";
                  $formCorrectlySent = false;
            }
      }
      elseif( $wrkSession['authorized_content'] == "TEXTFILE" )
      {
            // attached file is optionnal if work type is TEXT and FILE
            // $formCorrectlySent stay true;
      }

      // if authorized_content is TEXT or TEXTFILE, a text is required !
      if( $wrkSession['authorized_content'] == "TEXT" || $wrkSession['authorized_content'] == "TEXTFILE" )
      {
            if( !isset( $_REQUEST['wrkTxt'] ) || trim( strip_tags( $_REQUEST['wrkTxt'] ) ) == "" )
            {
                  $dialogBox .= $langAnswerRequired."<br />";
                  $formCorrectlySent = false;
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
                    AND `session_id` = ".$_REQUEST['sesId']."
                    AND `id` <> \"".$_REQUEST['wrkId']."\"";
                    
        $result = claro_sql_query($sql);
        
        if( mysql_num_rows($result) > 0 )
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
} //end if($_REQUEST['submitWrk'])
/*============================================================================
                          PERMISSIONS
  =============================================================================*/

// 3 rights levels 
// can make everything : submit, edit, delete
$is_allowedToEditAll  = $is_courseAdmin;
// can add a work and modify it (std authed user) // can only modify its works !
$is_allowedToEdit = $is_allowedToEditAll; // courseAdmin can do everything
if( isset($wrk) && isset($_uid) && $wrk['user_id'] == $_uid )
{
      $is_allowedToEdit = true;
}
// allowed to submit a new work , CANNOT edit any work
$is_allowedToSubmit   = (bool) ($is_allowedToEdit 
                                    || ( !isset($_uid) && $wrkSession['authorize_anonymous'] == "YES" && $is_courseAllowed ) 
                                    || ( isset($_uid) && $is_courseAllowed ) 
                              );


/*============================================================================
                          ADMIN ONLY COMMANDS
  =============================================================================*/
if($is_allowedToEditAll)
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
  if( $cmd == "exRmWrk" )
  {
      // get name of file to delete
      $sql = "SELECT `submitted_doc_path`
                  FROM `".$tbl_wrk_submission."`
                  WHERE `id` = ".$_REQUEST['wrkId'];
      
      $fileToDelete = claro_sql_query_get_single_value($sql);
      
      // delete the file
      @unlink($wrkDir."ws".$_REQUEST['sesId']."/".$fileToDelete);
      
      // delete the database data of this work
      $sqlDelete = "DELETE FROM `".$tbl_wrk_submission."`
                        WHERE `id` = ".$_REQUEST['wrkId'];
      claro_sql_query($sqlDelete);
  }
  /*--------------------------------------------------------------------
                        CORRECTION OF A WORK
  --------------------------------------------------------------------*/
  /*-----------------------------------
            STEP 2 : check & quey
  -------------------------------------*/
  if( $cmd == "exGradeWrk" )
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
                              `session_id` = ".$_REQUEST['sesId'].",
                              `parent_id` = ".$_REQUEST['wrkId'].","
                              .$uidString
                              ."`visibility` = \"".$wrkSession['def_submission_visibility']."\",
                              `title`       = \"".trim(claro_addslashes( $wrkForm['title'] ))."\",
                              `submitted_text` = \"".trim(claro_addslashes( $_REQUEST['wrkTxt'] ))."\",
                              `authors`     = \"".trim(claro_addslashes( $wrkForm['authors'] ))."\",
                              `creation_date` = NOW(),
                              `last_edit_date` = NOW()";
            
            claro_sql_query($sqlAddWork);
                        
            $dialogBox .= $langGradeAdded;
            
            $dispWrkForm = false;
            $dispWrkDet = false;
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
  if( $cmd == "rqGradeWrk" )
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
      
      $cmdToSend = "exGradeWrk";
      
      $txtForFormTitle = $langGradeWork;
      
      $dispWrkForm = true;
      $dispWrkDet = true;
      $dispWrkLst = false;
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
  if( $cmd == "exEditWrk" )
  {
      // if there is no error update database
      if( isset($formCorrectlySent) && $formCorrectlySent )
      {
            $sqlAddWork = "UPDATE `".$tbl_wrk_submission."`
                           SET `submitted_doc_path` = \"".$wrkForm['fileName']."\",
                              `title`       = \"".trim(claro_addslashes( $wrkForm['title'] ))."\",
                              `submitted_text` = \"".trim(claro_addslashes( $_REQUEST['wrkTxt'] ))."\",
                              `authors`     = \"".trim(claro_addslashes( $wrkForm['authors'] ))."\",
                              `last_edit_date` = NOW()
                              WHERE `id` = ".$_REQUEST['wrkId'];
            
            $lastWrkId = claro_sql_query($sqlAddWork);
                        
            $dialogBox .= $langWrkEdited;
            
            $dispWrkForm = false;
            $dispWrkDet = false;
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
  if( $cmd == "rqEditWrk" )
  {
        // prepare fields
      if( !$_REQUEST['submitWrk'] )
      {
            // prefill some fields of the form
            $form['wrkTitle'] = $wrk['title'];
            $form['wrkAuthors'] = $wrk['authors'];
            $form['wrkTxt'] = $wrk['submitted_text'];
            $form['wrkUrl'] = $wrk['submitted_doc_path'];
      }
      else
      {
            // there was an error in the form so display it with already modified values
            $form['wrkTitle'] = $_REQUEST['wrkTitle'];
            $form['wrkAuthors'] = $_REQUEST['wrkAuthors'];
            $form['wrkTxt'] = $_REQUEST['wrkTxt'];
      }
      $cmdToSend = "exEditWrk";
      // fill the title of the page
      $txtForFormTitle = $langEditWork;
      
      $dispWrkForm = true;
      $dispWrkDet = false;
      $dispWrkLst = false;
  }
}
/*============================================================================
 COMMANDS FOR : ADMIN, AUTHED USERS, ANONYMOUS USERS (if session cfg allows them)
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
                              `session_id` = ".$_REQUEST['sesId'].","
                              .$uidString
                              ."`visibility` = \"".$wrkSession['def_submission_visibility']."\",
                              `title`       = \"".trim(claro_addslashes( $wrkForm['title'] ))."\",
                              `submitted_text` = \"".trim(claro_addslashes( $_REQUEST['wrkTxt'] ))."\",
                              `authors`     = \"".trim(claro_addslashes( $wrkForm['authors'] ))."\",
                              `creation_date` = NOW(),
                              `last_edit_date` = NOW()";
            
            claro_sql_query($sqlAddWork);
                        
            $dialogBox .= $langWrkAdded;
            
            $dispWrkForm = false;
            $dispWrkDet = false;
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
    
    $dispWrkForm = true;
    $dispWrkDet = false;
    $dispWrkLst = false;
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
      $dispWrkForm = false;
      $dispWrkDet = true;
      $dispWrkLst = false;
} 
  
  
  
  
  
  
  
/*============================================================================
                          DISPLAY
  =============================================================================*/
if( !isset($dispWrkForm) && !isset($dispWrkDet) && !isset($dispWrkLst) )
{
      // set default display values if there is nothing set
      $dispWrkForm = false;
      $dispWrkDet = false;
      $dispWrkLst = true;
}


  
/*--------------------------------------------------------------------
                    TOOL TITLE
    --------------------------------------------------------------------*/

$pageTitle['mainTitle'  ] = $nameTools;
$pageTitle['subTitle'   ] = $wrkSession['title'];
claro_disp_tool_title($pageTitle);


/*--------------------------------------------------------------------
                          SESSION INFOS
  --------------------------------------------------------------------*/
//

echo "<p>"
      .$wrkSession['description']
      ."</p>";


/*--------------------------------------------------------------------
                          FORMS
  --------------------------------------------------------------------*/
if( $is_allowedToSubmit)
{
      if ($dialogBox)
      {
            claro_disp_message_box($dialogBox);
      }
      
      if( $dispWrkForm )
      {

?>
    <h4><?php echo $txtForFormTitle; ?></h4>
    <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data">
    <input type="hidden" name="sesId" value="<?php echo $_REQUEST['sesId']; ?>">
    <input type="hidden" name="cmd" value="<?php echo $cmdToSend; ?>">
<?php
  if( isset($_REQUEST['wrkId']) )
  {
?>
    <input type="hidden" name="wrkId" value="<?php echo $_REQUEST['wrkId']; ?>">
<?php
  }
?>
    <table>
      <tr>
        <td valign="top"><label for="wrkTitle"><?php echo $langWrkTitle; ?>&nbsp;*&nbsp;:</label></td>
        <td><input type="text" name="wrkTitle" id="wrkTitle" size="50" maxlength="200" value="<?php echo htmlentities($form['wrkTitle']); ?>"></td>
      </tr>
      <tr>
        <td valign="top"><label for="wrkAuthors"><?php echo $langWrkAuthors; ?>&nbsp;*&nbsp;:</label></td>
        <td><input type="text" name="wrkAuthors" id="wrkAuthors" size="50" maxlength="200" value="<?php echo htmlentities($form['wrkAuthors']); ?>"></td>
      </tr>
<?php
      // display file box
      if( $wrkSession['authorized_content'] == "FILE" || $wrkSession['authorized_content'] == "TEXTFILE" )
      {
            // if we are in edit mode and that a file can be edited : display the url of the current file and the file box to change it
            if( isset($form['wrkUrl']) )
            {
                  $completeWrkUrl = $currentCourseRepositoryWeb."work/ws".$_REQUEST['sesId']."/".$form['wrkUrl'];
                  echo "<tr>\n"
                        ."<td valign=\"top\">";
                        // display a different text according to the context
                  if( $wrkSession['authorize_text'] == "YES" )
                  {
                        // if text is required, file is considered as a an attached document
                        echo $langCurrentAttachedDoc;
                  }
                  else
                  {
                        // if the file is required and the text is only a description of the file
                        echo $langCurrentDoc;
                  }
                  echo "&nbsp;:<input type=\"hidden\" name=\"currentWrkUrl\" value=\"".$form['wrkUrl']."\">"
                        ."</td>\n"
                        ."<td>"
                        ."<a href=\"".$completeWrkUrl."\">".$form['wrkUrl']."</a>"
                        ."<br /><small>".$langExplainReplaceFile."</small>"
                        ."</td>\n"
                        ."<tr>\n\n";
            }
            
            echo "<tr>\n"
                  ."<td valign=\"top\"><label for=\"wrkFile\">";
            // display a different text according to the context
            if( $wrkSession['authorized_content'] == "TEXTFILE" )
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
      
      if( $wrkSession['authorized_content'] == "FILE" )
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
                  ."</tr>";
      }
      elseif( $wrkSession['authorized_content'] == "TEXT" || $wrkSession['authorized_content'] == "TEXTFILE" )
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
?>     
      <tr>
        <td colspan="2" align="center">
        <input type="submit" name="submitWrk" value="<?php echo $langOk; ?>">
        </td>
      </tr>
    </table>
    </form>
    <small>* : <?php echo $langRequired; ?></small>
    

<?php
      }
}
  
  
/*--------------------------------------------------------------------
                          WORK DETAILS
  --------------------------------------------------------------------*/
if( $dispWrkDet )
{
      echo "<h4>".$langWorkDetails."</h4>\n\n";
      
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
      
      if( $is_allowedToEdit )
      {
            // allow the user to modify it's own work
            echo "<a href=\"".$_SERVER['PHP_SELF']."?cmd=rqEditWrk&sesId=".$_REQUEST['sesId']."&wrkId=".$wrk['id']."\">"
                  ."<img src=\"".$clarolineRepositoryWeb."img/edit.gif\" border=\"0\" alt=\"$langModify\"></a>";
      }
      
      if( $is_allowedToEditAll )
      {
            // correction / grading
            echo "&nbsp;[&nbsp;<a href=\"".$_SERVER['PHP_SELF']."?cmd=rqGradeWrk&sesId=".$_REQUEST['sesId']."&wrkId=".$wrk['id']."\">".$langGradeSubmission."grade</a>&nbsp;]";
      }
      
      $completeWrkUrl = $currentCourseRepositoryWeb."work/ws".$_REQUEST['sesId']."/".$wrk['submitted_doc_path'];
      
      if( $wrkSession['authorized_content'] == "TEXTFILE" )
      {
            $txtForFile = $langAttachedFile;
            $txtForText = $langAnswer;
      }
      elseif( $wrkSession['authorized_content'] == "TEXT" )
      {
            $txtForText = $langAnswer;
      }
      elseif( $wrkSession['authorized_content'] == "FILE" )
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
            
      if( $wrkSession['authorized_content'] != "TEXT" )
      {
            // show file if this is not a TEXT only work
            echo "<tr>\n"
                  ."<td valign=\"top\">".$txtForFile."&nbsp;:</td>\n"
                  ."<td><a href=\"".$completeWrkUrl."\">".$wrk['submitted_doc_path']."</a></td>\n"
                  ."</tr>\n\n";
      }
      
      echo "<tr>\n"
            ."<td valign=\"top\">".$txtForText."&nbsp;:</td>\n"
            ."<td>".$wrk['submitted_text']."</td>\n"
            ."</tr>\n\n"
            ."<tr>\n"
            ."<td valign=\"top\">".$langSubmissionDate."&nbsp;:</td>\n"
            ."<td>".claro_disp_localised_date($dateTimeFormatLong, $wrk['unix_creation_date'])."</td>\n"
            ."</tr>\n\n";
      if( $wrk['unix_creation_date'] != $wrk['unix_last_edit_date'] )
      {
            echo "<tr>\n"
                  ."<td valign=\"top\">".$langLastEditDate."&nbsp;:</td>\n"
                  ."<td>".claro_disp_localised_date($dateTimeFormatLong, $wrk['unix_last_edit_date'])."</td>\n"
                  ."</tr>\n\n";
      }
      echo "</table>";
}
  
  
/*--------------------------------------------------------------------
                          WORK LIST
  --------------------------------------------------------------------*/
// display the list when there is no form to show
if( $dispWrkLst )
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
            WHERE `session_id` = ".$wrkSession['id']."
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
            WHERE `session_id` = ".$wrkSession['id']."
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
            WHERE `session_id` = ".$wrkSession['id']."
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
      // link to create a new session
      echo "&nbsp;<a href=\"".$_SERVER['PHP_SELF']."?cmd=rqSubWrk&sesId=".$_REQUEST['sesId']."\">".$langSubmitWork."</a>\n";
    }

    /*--------------------------------------------------------------------
                                  LIST
      --------------------------------------------------------------------*/
    
    echo "<table class=\"claroTable\" width=\"100%\">\n"
          ."<tr class=\"headerX\">\n"
          ."<th colspan=\"".($maxDeep+1)."\">".$langWrkTitle."</th>\n"
          ."<th>".$langWrkAuthors."</th>\n"
          ."<th>".$langLastSubmissionDate."</th>\n";
          
    if ( $is_allowedToEditAll ) 
    {
        echo  "<th>".$langModify."</th>\n"
              ."<th>".$langDelete."</th>\n"
              ."<th>".$langVisibility."</th>\n";
    }
    echo "</tr>\n\n"
        ."<tbody>\n\n";
    foreach($flatElementList as $thisWrk)
    {
      if ($thisWrk['visibility'] == "INVISIBLE")
			{
				if ($is_allowedToEditAll || $thisWrk['user_id'] == $_uid )
				{
					$style=' class="invisible"';
				}
				else
				{
          echo "pek";
					continue; // skip the display of this file
				}
			}
			else 
			{
				$style='';
			}
      
      $spacingString = "";
      for($i = 0; $i < $thisWrk['children']; $i++)
        $spacingString .= "<td width=\"5\">&nbsp;</td>";
      $colspan = $maxDeep - $thisWrk['children']+1;
      
      echo "<tr align=\"center\"".$style." >\n"
          .$spacingString
          ."<td colspan=\"".$colspan."\" align=\"left\"><a href=\"workList.php?sesId=".$_REQUEST['sesId']."&wrkId=".$thisWrk['id']."&cmd=exShwDet\">".$thisWrk['title']."</a></td>\n"
          ."<td>".$thisWrk['authors']."</td>\n"
          ."<td><small>".claro_disp_localised_date($dateTimeFormatLong, $thisWrk['unix_last_edit_date'])."</small></td>\n";

      
      if( $is_allowedToEditAll )
      {
        echo "<td><a href=\"".$_SERVER['PHP_SELF']."?cmd=rqEditWrk&sesId=".$_REQUEST['sesId']."&wrkId=".$thisWrk['id']."\"><img src=\"".$clarolineRepositoryWeb."img/edit.gif\" border=\"0\" alt=\"$langModify\"></a></td>\n"
            ."<td><a href=\"".$_SERVER['PHP_SELF']."?cmd=exRmWrk&sesId=".$_REQUEST['sesId']."&wrkId=".$thisWrk['id']."\" onClick=\"return confirmation('",addslashes($thisWrk['title']),"');\"><img src=\"".$clarolineRepositoryWeb."img/delete.gif\" border=\"0\" alt=\"$langDelete\"></a></td>\n"
            ."<td>";
        if ($thisWrk['visibility'] == "INVISIBLE")
        {
            echo	"<a href=\"".$_SERVER['PHP_SELF']."?cmd=exChVis&sesId=".$_REQUEST['sesId']."&wrkId=".$thisWrk['id']."&vis=v\">"
                  ."<img src=\"".$clarolineRepositoryWeb."img/invisible.gif\" border=\"0\" alt=\"$langMakeVisible\">"
                  ."</a>";
        }
        else
        {
            echo	"<a href=\"".$_SERVER['PHP_SELF']."?cmd=exChVis&sesId=".$_REQUEST['sesId']."&wrkId=".$thisWrk['id']."&vis=i\">"
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
