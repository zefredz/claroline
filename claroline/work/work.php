<?php // $Id$
/**
 * CLAROLINE
 *
 * Main script for work tool
 *
 *
 * @version 1.6 $Revision$
 *
 * @copyright 2001-2005 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE 
 *
 * @see http://www.claroline.net/wiki/install/
 *
 * @author Claro Team <cvs@claroline.net>
 *
 * @package CLWRK
 * 
 */


$tlabelReq = 'CLWRK___';
require '../inc/claro_init_global.inc.php';

include($includePath.'/lib/events.lib.inc.php');

$tbl_cdb_names = claro_sql_get_course_tbl();
$tbl_wrk_assignment = $tbl_cdb_names['wrk_assignment'];
$tbl_wrk_submission = $tbl_cdb_names['wrk_submission'];    


$currentUserFirstName       = $_user['firstName'];
$currentUserLastName        = $_user['lastName'];


if ( ! $_cid ) claro_disp_select_course();
if ( ! $is_courseAllowed )    claro_disp_auth_form();

event_access_tool($_tid, $_courseTool['label']);



include($includePath.'/lib/fileUpload.lib.php');
include($includePath.'/lib/fileDisplay.lib.php'); // need format_url function
include($includePath.'/lib/fileManage.lib.php'); // need claro_delete_file

// use viewMode
claro_set_display_mode_available(TRUE);

/*============================================================================
                     BASIC VARIABLES DEFINITION
  =============================================================================*/
$currentCourseRepositorySys = $coursesRepositorySys.$_course['path'].'/';
$currentCourseRepositoryWeb = $coursesRepositoryWeb.$_course['path'].'/';

$fileAllowedSize = $max_file_size_per_works ;    //file size in bytes
$wrkDir           = $currentCourseRepositorySys.'work/'; //directory path to create assignment dirs

// use with strip_tags function when strip_tags is used to check if a text is empty
// but a 'text' with only an image don't have to be considered as empty 
$allowedTags = '<img>';

// initialise dialog box to an empty string, all dialog will be concat to it
$dialogBox = '';

// permission
$is_allowedToEdit = claro_is_allowed_to_edit();

/*============================================================================
                     CLEAN INFORMATIONS SEND BY USER
  =============================================================================*/
stripSubmitValue($_REQUEST);

$cmd = ( isset($_REQUEST['cmd']) )?$_REQUEST['cmd']:'';

/*============================================================================
                HANDLING FORM DATA : CREATE/EDIT ASSIGNMENT
  =============================================================================*/
// execute this after a form has been send
// this instruction bloc will set some vars that will be used in the corresponding queries
if( isset($_REQUEST['submitAssignment']) && $is_allowedToEdit ) 
{
    $formCorrectlySent = true;
    
    // title is a mandatory element     
    $title = trim( strip_tags($_REQUEST['assigTitle']) );
            
    if( empty($title) )
    {
        $dialogBox .= $langAssignmentTitleRequired.'<br />';
        $formCorrectlySent = FALSE;
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
                    WHERE `title` = '".addslashes($title)."'
                    AND `id` != ".$_REQUEST['assigId'];
        }
        else
        {
            // creating an assignment
            $sql = "SELECT `title`
                FROM `".$tbl_wrk_assignment."`
                WHERE `title` = '".addslashes($title)."'";
        }
        
        $query = claro_sql_query($sql);
        
        if(mysql_num_rows($query) != 0 )
        {
            $dialogBox .= $langAssignmentTitleAlreadyExists.'<br />';
            $formCorrectlySent = FALSE;
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
        $authorizedContent = 'TEXTFILE';
    }
    elseif( isset($_REQUEST['authorizeText']) && $_REQUEST['authorizeText'])
    {
        $authorizedContent = "TEXT";       
    }
    elseif( isset($_REQUEST['authorizeFile']) && $_REQUEST['authorizeFile'])
    {
        $authorizedContent = 'FILE';
    }
      
    // description
    if( trim( strip_tags($_REQUEST['assigDesc'], $allowedTags ) ) == "" ) 
    {
        $assigDesc = ''; // avoid multiple br tags to be added when editing an empty form
    }
    else
    {
        $assigDesc = addslashes( trim($_REQUEST['assigDesc']) );
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
        $composedStartDate = $_REQUEST['startYear'].'-'
                            .$_REQUEST['startMonth'].'-'
                            .$_REQUEST['startDay'].' '
                            .$_REQUEST['startHour'].':'
                            .$_REQUEST['startMinute'].':00';
      
        $composedEndDate = $_REQUEST['endYear'].'-'
                          .$_REQUEST['endMonth'].'-'
                          .$_REQUEST['endDay'].' ' 
                          .$_REQUEST['endHour'].':'
                          .$_REQUEST['endMinute'].':00';
    }
} // if( isset($_REQUEST['submitAssignment']) ) // handling form data 


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
            $_REQUEST['vis'] == 'v' ? $visibility = 'VISIBLE' : $visibility = 'INVISIBLE';
            
            $sql = "UPDATE `".$tbl_wrk_assignment."`
                       SET `visibility` = '".$visibility."'
                     WHERE `id` = ".$_REQUEST['assigId']."
                       AND `visibility` != '".$visibility."'";
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
        if( claro_delete_file($wrkDir."assig_".$_REQUEST['assigId']) )
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
            $sql = "SELECT title, description,  
                           start_date,
                           end_date,
                           authorized_content,
                           def_submission_visibility,
                           assignment_type,
                           allow_late_upload
         
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
                    ( `title`,`description`, `assignment_type`,
                    `authorized_content`,
                    `start_date`, `end_date`, 
                    `def_submission_visibility`, `allow_late_upload`)
                    VALUES
                    ( \"".$title."\", \"".$assigDesc."\", \"".$_REQUEST['assignmentType']."\",
                    \"".$authorizedContent."\",
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
            $form['allowLateUpload'   ] = "NO";
        }
        else
        {
            // there was an error in the form so display it with already modified values
            $form['assigTitle'        ] = $_REQUEST['assigTitle'];
            $form['assigDesc'         ] = $_REQUEST['assigDesc'];
            $form['authorizedContent' ] = $_REQUEST['authorizedContent'];      
            $form['startDate'         ] = $_REQUEST['startYear']."-".$_REQUEST['startMonth']."-".$_REQUEST['startDay'];
            $form['startTime'         ] = $_REQUEST['startHour'].":".$_REQUEST['startMinute'].":00";
            $form['endDate'           ] = $_REQUEST['endYear']."-".$_REQUEST['endMonth']."-".$_REQUEST['endDay'];
            $form['endTime'           ] = $_REQUEST['endHour'].":".$_REQUEST['endMinute'].":00";
            $form['defSubVis'         ] = $_REQUEST['defSubVis'];
            $form['assignmentType'    ] = $_REQUEST['assignmentType'];
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
    if (confirm(\"".clean_str_for_javascript($langAreYouSureToDelete)." \"+ name + \" ? ".clean_str_for_javascript($langDeleteCaution)." \" ))
        {return true;}
    else
        {return false;}
}
</script>";

if(isset($_gid))
{
    $interbredcrump[]= array ("url"=>"../group/group.php", "name"=> $langGroup);
    $interbredcrump[]= array ("url"=>"../group/group_space.php", "name"=> $langGroupSpace);
}

if( ( isset($displayAssigForm) && $displayAssigForm ) )
{
    // bredcrump to return to the list when in a form
    $interbredcrump[]= array ("url"=>"../work/work.php", "name"=> $langWork);
    $nameTools = $langAssignment;
}
else
{
    $nameTools = $langWork;
    // to prevent parameters to be added in the breadcrumb
    //$QUERY_STRING='';
}

include($includePath.'/claro_init_header.inc.php');

/*--------------------------------------------------------------------
                    TOOL TITLE
    --------------------------------------------------------------------*/

claro_disp_tool_title($nameTools, $is_allowedToEdit ? 'help_work.php' : false);
  
 
if($is_allowedToEdit)
{

    /*--------------------------------------------------------------------
                            DIALOG BOX SECTION
      --------------------------------------------------------------------*/
    
    if ( isset($dialogBox) && !empty($dialogBox) )
    {
        claro_disp_message_box($dialogBox);
    }

    /*--------------------------------------------------------------------
                          CREATE AND EDIT FORM
      --------------------------------------------------------------------*/
    if ( isset($displayAssigForm) && $displayAssigForm ) 
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
    <table cellpadding="5" width="100%">
      <tr>
        <td valign="top"><label for="assigTitle"><?php echo $langAssignmentTitle; ?>&nbsp;:</label></td>
        <td><input type="text" name="assigTitle" id="assigTitle" size="50" maxlength="200" value="<?php echo htmlspecialchars($form['assigTitle']); ?>"></td>
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
          <?php claro_disp_button($_SERVER['HTTP_REFERER'], $langCancel); ?>
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
// if we don't display assignment form    
if( (!isset($displayAssigForm) || !$displayAssigForm) )
{
    /*--------------------------------------------------------------------
                        INTRODUCTION SECTION
      --------------------------------------------------------------------*/
    
    $moduleId = $_tid; // Id of the Student Paper introduction Area
    $helpAddIntroText = $langIntroWork;
    include($includePath."/introductionSection.inc.php");  

    /*--------------------------------------------------------------------
                        ADMIN LINKS
      --------------------------------------------------------------------*/
    if( $is_allowedToEdit )
    {
        // link to create a new assignment
        echo "<p><a class=\"claroCmd\" href=\"".$_SERVER['PHP_SELF']."?cmd=rqMkAssig\"><img src=\"".$imgRepositoryWeb."assignment.gif\" alt=\"\" />".$langCreateAssignment."</a></p>\n";
    }

    /*--------------------------------------------------------------------
                                  LIST
      --------------------------------------------------------------------*/
    // if user come from a group
    if( isset($_gid) && isset($is_groupAllowed) && $is_groupAllowed ) 
    {
        // select only the group assignments
          $sql = "SELECT `id`, `title`, `visibility`, 
            `description`, `assignment_type`, `authorized_content`,
            unix_timestamp(`start_date`) as `start_date_unix`, unix_timestamp(`end_date`) as `end_date_unix`
            FROM `".$tbl_wrk_assignment."`
            WHERE `assignment_type` = 'GROUP'
            ORDER BY `end_date` ASC";    
    }
    else
    {
        $sql = "SELECT `id`, `title`, `visibility`, 
            `description`, `assignment_type`, `authorized_content`,
            unix_timestamp(`start_date`) as `start_date_unix`, unix_timestamp(`end_date`) as `end_date_unix`
            FROM `".$tbl_wrk_assignment."` 
            ORDER BY `end_date` ASC";
    }          
    $assignmentList = claro_sql_query_fetch_all($sql);

    echo "<table class=\"claroTable\" width=\"100%\">\n";

	$atLeastOneAssignmentToShow = false;

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

		$atLeastOneAssignmentToShow = true;

		echo "<tr>\n"
	  		."<th class=\"headerX\">\n";
		if( isset($_REQUEST['submitGroupWorkUrl']) && !empty($_REQUEST['submitGroupWorkUrl']) )
		{
			echo "<a href=\"workList.php?cmd=rqSubWrk&amp;assigId=".$anAssignment['id']."&amp;submitGroupWorkUrl=".$_REQUEST['submitGroupWorkUrl']."\">".$anAssignment['title']."</a>\n";
		}
		else
		{
			echo "<a href=\"workList.php?assigId=".$anAssignment['id']."\">".$anAssignment['title']."</a>\n";
		}
		echo "</th>"
			;

		echo "<tr".$style.">\n"
			."<td>\n";

		if( strlen($anAssignment['description']) > 500 )
			echo "<div>".substr($anAssignment['description'],0,455)." ... "."</div><br />\n";
		else
			echo "<div>".$anAssignment['description']."</div><br />\n";

		echo "<small>".$langAvailableFrom." ".claro_disp_localised_date($dateTimeFormatLong,$anAssignment['start_date_unix'])." ".$langUntil." <b>".claro_disp_localised_date($dateTimeFormatLong,$anAssignment['end_date_unix'])."</b></small><br />"
			."<small>"
			;
		// content type
		if( $anAssignment['authorized_content'] == 'TEXT' ) echo $langTextOnly;
		elseif( $anAssignment['authorized_content'] == 'FILE' ) echo $langFileOnly;
		elseif( $anAssignment['authorized_content'] == 'TEXTFILE' ) echo $langTextFile;

		echo "<br />";
		// assignment type
		if( $anAssignment['assignment_type'] == 'INDIVIDUAL' ) echo $langIndividual ;
		elseif( $anAssignment['assignment_type'] == 'GROUP' ) echo $langGroupAssignment;

		echo "</small>\n";

		echo "</td>\n"
			."</tr>\n\n";

		if( $is_allowedToEdit )
      	{
        	echo "<tr".$style.">\n"
				."<td>\n"
		  		."<a href=\"".$_SERVER['PHP_SELF']."?cmd=rqEditAssig&amp;assigId=".$anAssignment['id']."\"><img src=\"".$imgRepositoryWeb."edit.gif\" border=\"0\" alt=\"".$langModify."\"></a>\n"
				."<a href=\"".$_SERVER['PHP_SELF']."?cmd=exRmAssig&amp;assigId=".$anAssignment['id']."\" onClick=\"return confirmation('",clean_str_for_javascript($anAssignment['title']),"');\"><img src=\"".$imgRepositoryWeb."delete.gif\" border=\"0\" alt=\"".$langDelete."\"></a>\n"
				;
	        if ($anAssignment['visibility'] == "INVISIBLE")
	        {
	            echo "<a href=\"".$_SERVER['PHP_SELF']."?cmd=exChVis&amp;assigId=".$anAssignment['id']."&amp;vis=v\">"
	                  ."<img src=\"".$imgRepositoryWeb."invisible.gif\" border=\"0\" alt=\"".$langMakeVisible."\">"
	                  ."</a>"
					  ;
	        }
	        else
	        {
	            echo	"<a href=\"".$_SERVER['PHP_SELF']."?cmd=exChVis&amp;assigId=".$anAssignment['id']."&amp;vis=i\">"
	                  ."<img src=\"".$imgRepositoryWeb."visible.gif\" border=\"0\" alt=\"".$langMakeInvisible."\">"
	                  ."</a>"
					  ;
	        }
        	echo "</td>\n"
				."</tr>\n"
				;
		}

    }

	if( ! $atLeastOneAssignmentToShow )
	{
		echo "<tr>\n"
			."<td>\n"
			.$langNoVisibleAssignment
			."</td>\n"
			."</tr>\n";
	}
    echo "</table>\n\n";


}


// FOOTER
include($includePath."/claro_init_footer.inc.php"); 
?>