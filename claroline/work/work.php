<?php // $Id$
/**
 * CLAROLINE
 *
 * Main script for work tool
 *
 * @version 1.8 $Revision$
 *
 * @copyright 2001-2006 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @see http://www.claroline.net/wiki/CLWRK/
 *
 * @author Claro Team <cvs@claroline.net>
 *
 * @package CLWRK
 *
 */

$tlabelReq = 'CLWRK';
require '../inc/claro_init_global.inc.php';

if ( ! $_cid || ! $is_courseAllowed ) claro_disp_auth_form(true);

require_once './lib/assignment.class.php';

require_once $includePath . '/lib/assignment.lib.php';
require_once $includePath . '/lib/pager.lib.php';
require_once $includePath . '/lib/fileUpload.lib.php';
require_once $includePath . '/lib/fileDisplay.lib.php'; // need format_url function
require_once $includePath . '/lib/fileManage.lib.php'; // need claro_delete_file


$tbl_cdb_names = claro_sql_get_course_tbl();
$tbl_wrk_assignment = $tbl_cdb_names['wrk_assignment'];
$tbl_wrk_submission = $tbl_cdb_names['wrk_submission'];

event_access_tool($_tid, $_courseTool['label']);

// 'step' of pager
$assignmentsPerPage = get_conf('assignmentsPerPage', 20);

// use viewMode
claro_set_display_mode_available(TRUE);

/*============================================================================
                     BASIC VARIABLES DEFINITION
  =============================================================================*/
$currentCourseRepositorySys = $coursesRepositorySys . $_course['path'] . '/';
$currentCourseRepositoryWeb = $coursesRepositoryWeb . $_course['path'] . '/';

$fileAllowedSize = get_conf('max_file_size_per_works') ;    //file size in bytes

// use with strip_tags function when strip_tags is used to check if a text is empty
// but a 'text' with only an image don't have to be considered as empty
$allowedTags = '<img>';

// initialise dialog box to an empty string, all dialog will be concat to it
$dialogBox = '';

// permission
$is_allowedToEdit = claro_is_allowed_to_edit();

/*============================================================================
                     CLEAN INFORMATIONS SENT BY USER
  =============================================================================*/

$cmd = ( isset($_REQUEST['cmd']) )?$_REQUEST['cmd']:'';

/*============================================================================
                HANDLING FORM DATA : CREATE/EDIT ASSIGNMENT
  =============================================================================*/
if( !empty($cmd) )
{
	// instanciate assignment object
	$assignment = new Assignment();
	
	if( isset($_REQUEST['assigId']) )
    {
        // we handle a particular assignment, no form has been posted (delete, change visibility , ask for edition)
        // read assignment        
        if( ! $assignment->load($_REQUEST['assigId']) )
        {
        	// could not read assignment
			$cmd = '';
			$_REQUEST['assigId'] == NULL;
        }
    }
    
    
	if( isset($_REQUEST['submitAssignment']) && $cmd != '' )
    {
    	// form submitted
        if ( isset($_REQUEST['title']) )        				$assignment->setTitle(strip_tags(trim($_REQUEST['title'])));
                
        if( !isset($_REQUEST['description']) || trim( strip_tags($_REQUEST['description'], $allowedTags ) ) == "" )
        {
            $assignment->setDescription(''); // avoid multiple br tags to be added when editing an empty form
        }
        else
        {
            $assignment->setDescription(trim( $_REQUEST['description'] ));

        }
        
        if ( isset($_REQUEST['def_submission_visibility']) ) 	$assignment->setDefaultSubmissionVisibility($_REQUEST['def_submission_visibility']);
        if ( isset($_REQUEST['assignment_type']) )    			$assignment->setAssignmentType($_REQUEST['assignment_type']);
        if ( isset($_REQUEST['authorized_content']) ) 			$assignment->setSubmissionType($_REQUEST['authorized_content']);
		if ( isset($_REQUEST['allow_late_upload']) ) 			$assignment->setAllowLateUpload($_REQUEST['allow_late_upload']);
        

		$unixStartDate = mktime( $_REQUEST['startHour'],
								$_REQUEST['startMinute'],
								'00',
								$_REQUEST['startMonth'],
								$_REQUEST['startDay'],
								$_REQUEST['startYear']);
        $assignment->setStartDate($unixStartDate);
        
       	$unixEndDate = mktime( $_REQUEST['endHour'],
								$_REQUEST['endMinute'],
								'00',
								$_REQUEST['endMonth'],
								$_REQUEST['endDay'],
								$_REQUEST['endYear']);
        $assignment->setEndDate($unixEndDate);

        $assignment_data['start_date'] = $unixStartDate;
        												
        $assignment_data['end_date'] 	= $unixEndDate;
    }
    else
    {
    	// create new assignment
        // add date format used to pre fill the form
        $assignment_data['start_date'] = $assignment->getStartDate();
        $assignment_data['end_date'] 	= $assignment->getEndDate();
    }
}


if ($is_allowedToEdit)
{
    /*--------------------------------------------------------------------
                          CHANGE VISIBILITY
    --------------------------------------------------------------------*/

    // change visibility of an assignment
    if ( $cmd == 'exChVis' )
    {
        if ( isset($_REQUEST['vis']) )
        {
        	$_REQUEST['vis'] == 'v' ? $visibility = 'VISIBLE' : $visibility = 'INVISIBLE';
        	
            Assignment::updateAssignmentVisibility($_REQUEST['assigId'], $visibility);

            // notify eventmanager

            if ( $_REQUEST['vis'] == 'v')
            {
                $eventNotifier->notifyCourseEvent('work_visible', $_cid, $_tid, $_REQUEST['assigId'], $_gid, '0');
            }
            else
            {
                $eventNotifier->notifyCourseEvent('work_invisible', $_cid, $_tid, $_REQUEST['assigId'], $_gid, '0');
            }
        }
    }

    /*--------------------------------------------------------------------
                          DELETE AN ASSIGNMENT
    --------------------------------------------------------------------*/

    // delete/remove an assignment
    if ( $cmd == 'exRmAssig' )
    {
        $assignment->delete();

        //notify eventmanager
        $eventNotifier->notifyCourseEvent('work_deleted', $_cid, $_tid, $_REQUEST['assigId'], $_gid, '0');

        $dialogBox .= get_lang('Assignment deleted');
    }

    /*--------------------------------------------------------------------
                          MODIFY AN ASSIGNMENT
    --------------------------------------------------------------------*/
    /*-----------------------------------
        STEP 2 : check & query
    -------------------------------------*/

    // edit an assignment / form has been sent
    if ( $cmd == 'exEditAssig' )
    {
        // check validity of the data
        if ( isset($_REQUEST['assigId']) && $assignment->validate() )
        {
            $assignment->save();

            $dialogBox .= get_lang('Assignment modified');
        }
        else
        {
			if(claro_failure::get_last_failure() == 'assignment_no_title')
               $dialogBox .= get_lang('Assignment title required').'<br />';
            if(claro_failure::get_last_failure() == 'assignment_title_already_exists')
                $dialogBox .= get_lang('Assignment title already exists').'<br />';
            if(claro_failure::get_last_failure() == 'assignment_incorrect_dates')
                $dialogBox .= get_lang('Start date must be before end date ...')."<br />";

            $cmd = 'rqEditAssig';
        }
    }
    /*-----------------------------------
    STEP 1 : display form
    -------------------------------------*/
    // edit assignment / display the form
    if( $cmd == 'rqEditAssig' )
    {
        include($includePath . '/lib/form.lib.php');
        // modify the command 'cmd' sent by the form
        $cmdToSend = 'exEditAssig';
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
        if( $assignment->validate() )
        {
            $lastAssigId = $assignment->save();
            // confirmation message
            $dialogBox .= get_lang('New assignment created');

            if($lastAssigId)
            {
                //notify eventmanager that a new assignement is created
                $eventNotifier->notifyCourseEvent("work_added",$_cid, $_tid, $lastAssigId, $_gid, "0");
            }
        }
        else
        {
        	if(claro_failure::get_last_failure() == 'assignment_no_title')
               $dialogBox .= get_lang('Assignment title required').'<br />';
            if(claro_failure::get_last_failure() == 'assignment_title_already_exists')
                $dialogBox .= get_lang('Assignment title already exists').'<br />';
            if(claro_failure::get_last_failure() == 'assignment_incorrect_dates')
                $dialogBox .= get_lang('Start date must be before end date ...')."<br />";

            $cmd = 'rqMkAssig';
        }
    }

    /*-----------------------------------
        STEP 1 : display form
    -------------------------------------*/
    //--- create an assignment / display form
    if( $cmd == 'rqMkAssig' )
    {
        include($includePath . '/lib/form.lib.php');
        // modify the command 'cmd' sent by the form
        $cmdToSend = 'exMkAssig';
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
'<script type="text/javascript">
function confirmation (name)
{
    if (confirm("' . clean_str_for_javascript(get_lang('Are you sure to delete')) . ' "+ name + " ? ' . clean_str_for_javascript(get_lang('This will also delete all works submitted in this assignment !')) . ' " ))
        {return true;}
    else
        {return false;}
}
</script>';

if ( ( isset($displayAssigForm) && $displayAssigForm ) )
{
    // bredcrump to return to the list when in a form
    $interbredcrump[]= array ('url' => '../work/work.php', 'name' => get_lang('Assignments'));
    $nameTools = get_lang('Assignment');
}
else
{
	$noQUERY_STRING = true;
    $nameTools = get_lang('Assignments');
}



    /*--------------------------------------------------------------------
                                  LIST
      --------------------------------------------------------------------*/
    // if user come from a group
    if ( isset($_gid) && isset($is_groupAllowed) && $is_groupAllowed )
    {
        // select only the group assignments
        $sql = "SELECT `id`, 
        				`title`, 
        				`def_submission_visibility`,
	      				`visibility`, 
        				`assignment_type`, 
        				unix_timestamp(`start_date`) as `start_date_unix`, 
        				unix_timestamp(`end_date`) as `end_date_unix`
		        FROM `" . $tbl_wrk_assignment . "`
		        WHERE `assignment_type` = 'GROUP'";
	        
	        if ( isset($_GET['sort']) ) $sortKeyList[$_GET['sort']] = isset($_GET['dir']) ? $_GET['dir'] : SORT_ASC;
			
			$sortKeyList['end_date']	= SORT_ASC;
    }
    else
    {
        $sql = "SELECT `id`, 
        				`title`, 
        				`def_submission_visibility`, 
        				`visibility`, 
        				`assignment_type`,
        				unix_timestamp(`start_date`) as `start_date_unix`, 
        				unix_timestamp(`end_date`) as `end_date_unix`
            	FROM `" . $tbl_wrk_assignment . "`";

		if ( isset($_GET['sort']) ) $sortKeyList[$_GET['sort']] = isset($_GET['dir']) ? $_GET['dir'] : SORT_ASC;
					
		$sortKeyList['end_date']	= SORT_ASC;
    }
    
    $offset = (isset($_REQUEST['offset']) && !empty($_REQUEST['offset']) ) ? $_REQUEST['offset'] : 0;
	$assignmentPager = new claro_sql_pager($sql, $offset, $assignmentsPerPage);
	
	foreach($sortKeyList as $thisSortKey => $thisSortDir)
	{
	    $assignmentPager->add_sort_key( $thisSortKey, $thisSortDir);
	}

	$assignmentList = $assignmentPager->get_result_list();


include $includePath . '/claro_init_header.inc.php' ;

/*--------------------------------------------------------------------
                    TOOL TITLE
    --------------------------------------------------------------------*/

echo claro_html_tool_title($nameTools, $is_allowedToEdit ? 'help_work.php' : false);


if ($is_allowedToEdit)
{

    /*--------------------------------------------------------------------
                            DIALOG BOX SECTION
      --------------------------------------------------------------------*/

    if ( isset($dialogBox) && !empty($dialogBox) )
    {
        echo claro_html_message_box($dialogBox);
    }

    /*--------------------------------------------------------------------
                          CREATE AND EDIT FORM
      --------------------------------------------------------------------*/
    if ( isset($displayAssigForm) && $displayAssigForm )
    {
?>
    <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data">
    <input type="hidden" name="claroFormId" value="<?php echo uniqid(''); ?>">
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
        <td valign="top"><label for="title"><?php echo get_lang('Assignment title'); ?>&nbsp;:</label></td>
        <td><input type="text" name="title" id="title" size="50" maxlength="200" value="<?php echo htmlspecialchars($assignment->getTitle()); ?>"></td>
      </tr>

      <tr>
        <td valign="top"><label for="description"><?php echo get_lang('Description'); ?>&nbsp;:<br /></label></td>
        <td>
<?php
    echo claro_html_textarea_editor('description', htmlspecialchars($assignment->getDescription()));
?>
        </td>
      </tr>

      <tr>
        <td valign="top"><?php echo get_lang('Submission type'); ?>&nbsp;:</td>
        <td>
          <input type="radio" name="authorized_content" id="authorizeFile" value="FILE" <?php if( $assignment->getSubmissionType() == "FILE" ) echo 'checked="checked"'; ?>>
            <label for="authorizeFile">&nbsp;<?php echo get_lang('File (file required, description text optional)'); ?></label>
            <br />
          <input type="radio" name="authorized_content" id="authorizeText" value="TEXT" <?php if( $assignment->getSubmissionType() == "TEXT" ) echo 'checked="checked"'; ?>>
            <label for="authorizeText">&nbsp;<?php echo get_lang('Text only (text required, no file)'); ?></label>
            <br />
          <input type="radio" name="authorized_content" id="authorizeTextFile" value="TEXTFILE" <?php if( $assignment->getSubmissionType() == "TEXTFILE" ) echo 'checked="checked"'; ?>>
            <label for="authorizeTextFile">&nbsp;<?php echo get_lang('Text with attached file (text required, file optional)'); ?></label>
            <br />
        </td>
      </tr>

      <tr>
        <td valign="top"><?php echo get_lang('Assignment type'); ?>&nbsp;:</td>
        <td>
          <input type="radio" name="assignment_type" id="individual" value="INDIVIDUAL" <?php if($assignment->getAssignmentType() == "INDIVIDUAL") echo 'checked="checked"'; ?>>
            <label for="individual">&nbsp;<?php echo get_lang('Individual'); ?></label>
            <br />
          <input type="radio" name="assignment_type" id="group" value="GROUP" <?php if($assignment->getAssignmentType() == "GROUP") echo 'checked="checked"'; ?>>
            <label for="group">&nbsp;<?php echo get_lang('Groups (from groups tool, only group members can post)'); ?></label>
            <br />
        </td>
      </tr>
      
      <tr>
        <td valign="top"><?php echo get_lang('Start date'); ?>&nbsp;:</td>
        <td>
<?php
    echo claro_disp_date_form('startDay', 'startMonth', 'startYear', $assignment_data['start_date'], 'long') . ' ' . claro_disp_time_form('startHour', 'startMinute', $assignment_data['start_date']);
    echo '&nbsp;<small>' . get_lang('(d/m/y hh:mm)') . '</small>';
?>
        </td>
      </tr>

      <tr>
        <td valign="top"><?php echo get_lang('End date'); ?>&nbsp;:</td>
        <td>
<?php
    echo claro_disp_date_form('endDay', 'endMonth', 'endYear', $assignment_data['end_date'], 'long') . ' ' . claro_disp_time_form('endHour', 'endMinute', $assignment_data['end_date']);
    echo '&nbsp;<small>' . get_lang('(d/m/y hh:mm)') . '</small>';
?>
        </td>
      </tr>

      <tr>
        <td valign="top"><?php echo get_lang('Allow late upload'); ?>&nbsp;:</td>
        <td>
        <input type="radio" name="allow_late_upload" id="allowUpload" value="YES" <?php if($assignment->getAllowLateUpload() == "YES") echo 'checked="checked"'; ?>>
          <label for="allowUpload">&nbsp;<?php echo get_lang('Yes, allow users to submit works after end date'); ?></label>
          <br />
        <input type="radio" name="allow_late_upload" id="preventUpload" value="NO" <?php if($assignment->getAllowLateUpload() == "NO") echo 'checked="checked"'; ?>>
          <label for="preventUpload">&nbsp;<?php echo get_lang('No, prevent users submitting work after the end date'); ?></label>
          <br />
        </td>
      </tr>
      
      <tr>
        <td valign="top"><?php echo get_lang('Default works visibility'); ?>&nbsp;:</td>
        <td>
          <input type="radio" name="def_submission_visibility" id="visible" value="VISIBLE" <?php if($assignment->getDefaultSubmissionVisibility() == "VISIBLE") echo 'checked="checked"'; ?>>
            <label for="visible">&nbsp;<?php echo get_lang('Visible to other users'); ?></label>
            <br />
          <input type="radio" name="def_submission_visibility" id="invisible" value="INVISIBLE" <?php if($assignment->getDefaultSubmissionVisibility() == "INVISIBLE") echo 'checked="checked"'; ?>>
            <label for="invisible">&nbsp;<?php echo get_lang('Invisible to other users'); ?></label>
            <br />
        </td>
      </tr>

      <tr>
        <td>&nbsp;</td>
        <td>
          <input type="submit" name="submitAssignment" value="<?php echo get_lang('Ok'); ?>">
          <?php echo claro_html_button((isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:'.'), get_lang('Cancel')); ?>
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
if ( (!isset($displayAssigForm) || !$displayAssigForm) )
{
    /*--------------------------------------------------------------------
                        ADMIN LINKS
      --------------------------------------------------------------------*/
    $cmdMenu = array();
    if( $is_allowedToEdit )
    {
        // link to create a new assignment
        $cmdMenu[] = '<a class="claroCmd" href="' . $_SERVER['PHP_SELF'] . '?cmd=rqMkAssig">'
        .    '<img src="' . $imgRepositoryWeb . 'assignment.gif" alt="" />' . get_lang('Create a new assignment')
        .    '</a>'. "\n"
        ;
    }
    
    if( !empty($cmdMenu) ) echo '<p>' . claro_html_menu_horizontal($cmdMenu) . '</p>' . "\n";

	$headerUrl = $assignmentPager->get_sort_url_list($_SERVER['PHP_SELF']);

	echo $assignmentPager->disp_pager_tool_bar($_SERVER['PHP_SELF']);

    echo '<table class="claroTable" width="100%">' . "\n"
    .	 '<tr class="headerX">'
    .	 '<th><a href="' . $headerUrl['title'] . '">' . get_lang('Title') . '</a></th>' . "\n"
    .	 '<th><a href="' . $headerUrl['assignment_type'] . '">' . get_lang('Type') . '</a></th>' . "\n"
    .	 '<th><a href="' . $headerUrl['start_date_unix'] . '">' . get_lang('Available from') . '</a></th>' . "\n"
    .	 '<th><a href="' . $headerUrl['end_date_unix'] . '">' . get_lang('Until') . '</a></th>' . "\n";
    
    $colspan = 4;
    
    if( isset($_REQUEST['submitGroupWorkUrl']) && !empty($_REQUEST['submitGroupWorkUrl']) )
    {
    	echo '<th>' . get_lang('Publish') . '</th>' . "\n";
    	$colspan++;	
    }
    
    if( $is_allowedToEdit )
    {
    	echo '<th>' . get_lang('Edit') . '</th>' . "\n"
    	.	 '<th>' . get_lang('Delete') . '</th>' . "\n"
    	.	 '<th>' . get_lang('Visibility') . '</th>' . "\n";
    	$colspan += 3;
    }
    
    
    echo '</tr>' . "\n"
    .	 '<tbody>' . "\n\n";
    

    $atLeastOneAssignmentToShow = false;

    if (isset($_uid)) $date = $claro_notifier->get_notification_date($_uid);

    foreach ( $assignmentList as $anAssignment )
    {
        //modify style if the file is recently added since last login and that assignment tool is used with visible default mode for submissions.
        $classItem='';
        if (isset($_uid) && $claro_notifier->is_a_notified_ressource($_cid, $date, $_uid, '', $_tid, $anAssignment['id'],FALSE) && ($anAssignment['def_submission_visibility']=="VISIBLE"  || $is_allowedToEdit))
        {
            $classItem=' hot';
        }
        elseif( isset($_uid) ) //otherwise just display its name normally and tell notifier that every ressources are seen (for tool list notification consistancy)
        {
            $claro_notifier->is_a_notified_ressource($_cid, $date, $_uid, '', $_tid, $anAssignment['id']);
        }


        if ( $anAssignment['visibility'] == "INVISIBLE" )
        {
            if ( $is_allowedToEdit )
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

        echo '<tr ' . $style . '>'."\n"
        .    '<td>' . "\n"
    	.	 '<a href="workList.php?assigId=' . $anAssignment['id'] . '" class="item' . $classItem . '">'        
        .	 '<img src="' . $imgRepositoryWeb . 'assignment.gif" alt="" /> '
        .    $anAssignment['title']
        .    '</a>' . "\n"
        .    '</td>' . "\n"
        ;

		echo '<td align="center">';
		
		if( $anAssignment['assignment_type'] == 'INDIVIDUAL' ) 
			echo '<img src="' . $imgRepositoryWeb . 'user.gif" border="0" alt="' . get_lang('Individual') . '" />' ;
        elseif( $anAssignment['assignment_type'] == 'GROUP' ) 
        	echo '<img src="' . $imgRepositoryWeb . 'group.gif" border="0" alt="' . get_lang('Groups (from groups tool, only group members can post)') . '" />' ;
        else 
        	echo '&nbsp;';
        	
        echo '</td>' . "\n";
        
        echo '<td><small>' . claro_disp_localised_date($dateTimeFormatLong,$anAssignment['start_date_unix']) . '</small></td>' . "\n"
        .	 '<td><small>' . claro_disp_localised_date($dateTimeFormatLong,$anAssignment['end_date_unix']) . '</small></td>' . "\n";
        
        if ( isset($_REQUEST['submitGroupWorkUrl']) && !empty($_REQUEST['submitGroupWorkUrl']) )
        {
            echo '<td align="center">'
			.	 '<a href="workList.php?cmd=rqSubWrk&amp;assigId=' . $anAssignment['id'] . '&amp;submitGroupWorkUrl=' . urlencode($_REQUEST['submitGroupWorkUrl']) . '">'
			. 	 '<small>' . get_lang('Publish') . '</small>'
			.	 '</a>'
			.	 '</td>' . "\n";
        }
        
        if ( $is_allowedToEdit )
        {
            echo '<td align="center">'
			.	 '<a href="' . $_SERVER['PHP_SELF'] . '?cmd=rqEditAssig&amp;assigId=' . $anAssignment['id'] . '">'
			.	 '<img src="' . $imgRepositoryWeb . 'edit.gif" border="0" alt="' . get_lang('Modify') . '"></a>'
			.	 '</td>' . "\n"
			.	 '<td align="center">'
			.	 '<a href="' . $_SERVER['PHP_SELF'] . '?cmd=exRmAssig&amp;assigId=' . $anAssignment['id'] . '" onClick="return confirmation(\'' . clean_str_for_javascript($anAssignment['title']) . '\');">'
			.	 '<img src="' . $imgRepositoryWeb . 'delete.gif" border="0" alt="' . get_lang('Delete') . '"></a>'
			.	 '</td>' . "\n"
			.	 '<td align="center">';
			
            if ( $anAssignment['visibility'] == "INVISIBLE" )
            {
                echo "<a href=\"".$_SERVER['PHP_SELF']."?cmd=exChVis&amp;assigId=".$anAssignment['id']."&amp;vis=v\">"
                      ."<img src=\"".$imgRepositoryWeb."invisible.gif\" border=\"0\" alt=\"".get_lang('Make visible')."\" />"
                      ."</a>"
                      ;
            }
            else
            {
                echo '<a href="' . $_SERVER['PHP_SELF'] . '?cmd=exChVis&amp;assigId=' . $anAssignment['id'] . '&amp;vis=i">'
                .    '<img src="' . $imgRepositoryWeb . 'visible.gif" border="0" alt="' . get_lang('Make invisible') . '" />'
                .    '</a>'
                ;
            }
            echo '</td>' . "\n"
            .    '</tr>' . "\n\n"
            ;
        }
        
        $atLeastOneAssignmentToShow = true;
    }

    if ( ! $atLeastOneAssignmentToShow )
    {
        echo '<tr>' . "\n"
        .    '<td colspan=' . $colspan . '>' . "\n"
        .    get_lang('There is no assignment at the moment')
        .    '</td>' . "\n"
        .    '</tr>' . "\n"
        ;
    }
    echo '</tbody>' . "\n"
	.	 '</table>' . "\n\n";


}
// FOOTER
include $includePath . '/claro_init_footer.inc.php';
?>
