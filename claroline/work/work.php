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
    $title = strip_tags( trim($_REQUEST['sesTitle']) );
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
        $dialogBox .= $langSesTitleAlreadyExists."titleexists<br />";
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
    if( trim( strip_tags($_REQUEST['sesDesc']) ) == "" ) 
    {
      $sesDesc = ""; // avoid multiple br tags to be added when editing an empty form
    }
    else
    {
      $sesDesc = claro_parse_user_text( claro_addslashes( trim($_REQUEST['sesDesc']) ) );
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
                      `allow_late_upload` = \"".$_REQUEST['allowLateUpload']."\"
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
        
    
        // set values to prefill the form
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
      $form['allowLateUpload' ] = $_REQUEST['allowLateUpload'];
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
                    `def_submission_visibility`, `allow_late_upload`)
                  VALUES
                  ( \"".$title."\", \"".$description."\", \"".$_REQUEST['sessionType']."\",
                    \"".$authorizedContent."\", \"".$_REQUEST['allowAnonymous']."\",
                    \"".$composedStartDate."\", \"".$composedEndDate."\",
                    \"".$_REQUEST['defSubVis']."\", \"".$_REQUEST['allowLateUpload']."\" )";
    
          // execute the creation query and return id of inserted session
          $lastSesId = claro_sql_query_insert_id($sql);
          
          // create the session directory if query was successfull and dir not already exists
          if( $lastSesId && !is_dir( $wrkDir."ws".$lastSesId ) )
          {
            mkdir( $wrkDir."ws".$lastSesId , 0777 );
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
      $form['allowLateUpload' ] = $_REQUEST['allowLateUpload'];
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
    <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
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
          <input type="radio" name="authorizedContent" id="authorizeFile" value="FILE" <?php if( $form['authorizedContent'] == "FILE" ) echo 'checked="checked"'; ?>><label for="authorizeFile">&nbsp;<?php echo $langFile; ?></label><br />
          <input type="radio" name="authorizedContent" id="authorizeText" value="TEXT" <?php if( $form['authorizedContent'] == "TEXT" ) echo 'checked="checked"'; ?>><label for="authorizeText">&nbsp;<?php echo $langText; ?></label><br />
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
      $sql = "SELECT `id`, `title`, `sess_visibility`
              FROM `".$tbl_wrk_session."` 
              ORDER BY `title` ASC";
              
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
