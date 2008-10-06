<?php // $Id$
/**
 * CLAROLINE
 *
 * Main script for work tool
 *
 * @version 1.8 $Revision$
 *
 * @copyright 2001-2007 Universite catholique de Louvain (UCL)
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

if ( ! claro_is_in_a_course() || ! claro_is_course_allowed() ) claro_disp_auth_form(true);

require_once './lib/assignment.class.php';

require_once get_path('incRepositorySys') . '/lib/pager.lib.php';
require_once get_path('incRepositorySys') . '/lib/fileUpload.lib.php';
require_once get_path('incRepositorySys') . '/lib/fileDisplay.lib.php'; // need format_url function
require_once get_path('incRepositorySys') . '/lib/fileManage.lib.php'; // need claro_delete_file


$tbl_cdb_names = claro_sql_get_course_tbl();
$tbl_wrk_assignment = $tbl_cdb_names['wrk_assignment'];
$tbl_wrk_submission = $tbl_cdb_names['wrk_submission'];

$currentCoursePath =  claro_get_current_course_data('path');

// 'step' of pager
$assignmentsPerPage = get_conf('assignmentsPerPage', 20);

// use viewMode
claro_set_display_mode_available(TRUE);

/*============================================================================
                     BASIC VARIABLES DEFINITION
  =============================================================================*/
$currentCourseRepositorySys = get_path('coursesRepositorySys') . $currentCoursePath . '/';
$currentCourseRepositoryWeb = get_path('coursesRepositoryWeb') . $currentCoursePath . '/';

$fileAllowedSize = get_conf('max_file_size_per_works') ;    //file size in bytes

// use with strip_tags function when strip_tags is used to check if a text is empty
// but a 'text' with only an image don't have to be considered as empty
$allowedTags = '<img>';

// initialise dialog box to an empty string, all dialog will be concat to it
$dialogBox = new DialogBox();

// permission
$is_allowedToEdit = claro_is_allowed_to_edit();

/*============================================================================
                     CLEAN INFORMATIONS SENT BY USER
  =============================================================================*/

$acceptedCmdList = array( 'rqDownload', 'exDownload', 'exChVis', 'exRmAssig', 'exEditAssig', 'rqEditAssig', 'exMkAssig', 'rqMkAssig' );

if( isset($_REQUEST['cmd']) && in_array($_REQUEST['cmd'], $acceptedCmdList) )   $cmd = $_REQUEST['cmd'];
else                                                                            $cmd = null;


if( isset($_REQUEST['downloadMode']) )    $downloadMode = $_REQUEST['downloadMode'];
else                                    $downloadMode = 'all';

/*============================================================================
                HANDLING FORM DATA : CREATE/EDIT ASSIGNMENT
  =============================================================================*/
if( !is_null($cmd) )
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
            $cmd = null;
            $_REQUEST['assigId'] = NULL;
        }
    }


    if( isset($_REQUEST['submitAssignment']) && !is_null($cmd) )
    {
        // form submitted
        if ( isset($_REQUEST['title']) )                        $assignment->setTitle(strip_tags(trim($_REQUEST['title'])));

        if( !isset($_REQUEST['description']) || trim( strip_tags($_REQUEST['description'], $allowedTags ) ) == '' )
        {
            $assignment->setDescription(''); // avoid multiple br tags to be added when editing an empty form
        }
        else
        {
            $assignment->setDescription(trim( $_REQUEST['description'] ));

        }

        if ( isset($_REQUEST['def_submission_visibility']) )     $assignment->setDefaultSubmissionVisibility($_REQUEST['def_submission_visibility']);
        if ( isset($_REQUEST['assignment_type']) )                $assignment->setAssignmentType($_REQUEST['assignment_type']);
        if ( isset($_REQUEST['authorized_content']) )             $assignment->setSubmissionType($_REQUEST['authorized_content']);
        if ( isset($_REQUEST['allow_late_upload']) )             $assignment->setAllowLateUpload($_REQUEST['allow_late_upload']);


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

        $assignment_data['end_date']     = $unixEndDate;
    }
    else
    {
        // create new assignment
        // add date format used to pre fill the form
        $assignment_data['start_date'] = $assignment->getStartDate();
        $assignment_data['end_date']     = $assignment->getEndDate();
    }
}

/*============================================================================
                DOWNLOAD SUBMISSIONS (thanks to UJM)
  =============================================================================*/
if( $is_allowedToEdit && $cmd == 'exDownload' && get_conf('allow_download_all_submissions') ) // UJM
{
    require_once('lib/zip.lib.php');

    $zipfile = new zipfile();

    if( $downloadMode == 'from')
    {
        if( isset($_REQUEST['hour']) && is_numeric($_REQUEST['hour']) )       $hour = (int) $_REQUEST['hour'];
        else                                                                  $hour = 0;
        if( isset($_REQUEST['minute']) && is_numeric($_REQUEST['minute']) ) $minute = (int) $_REQUEST['minute'];
        else                                                                  $minute = 0;

        if( isset($_REQUEST['month']) && is_numeric($_REQUEST['month']) )   $month = (int) $_REQUEST['month'];
        else                                                                  $month = 0;
        if( isset($_REQUEST['day']) && is_numeric($_REQUEST['day']) )       $day = (int) $_REQUEST['day'];
        else                                                                  $day = 0;
        if( isset($_REQUEST['year']) && is_numeric($_REQUEST['year']) )       $year = (int) $_REQUEST['year'];
        else                                                                  $year = 0;

        $unixRequestDate = mktime( $hour, $minute, '00', $month, $day, $year );

        if( $unixRequestDate >= time() )
        {
            $dialogBox->error( get_lang('Chosen date is in the future') );
        }

        $downloadRequestDate = date('Y-m-d G:i:s', $unixRequestDate);

        $wanted = '_' . replace_dangerous_char(get_lang('From')) . '_' . date('Y_m_d', $unixRequestDate) . '_'
        . replace_dangerous_char(get_lang('to')) . '_' . date('Y_m_d')
        ;
        $sqlDateCondition = " AND `last_edit_date` >= '" . $downloadRequestDate . "' ";
    }
    else // download all
    {
        $wanted = '';

        $sqlDateCondition = '';
    }

    $sql = "SELECT `id`,
            `assignment_id`,
             `authors`,
             `submitted_text`,
             `submitted_doc_path`,
             `title`,
             `creation_date`,
             `last_edit_date`
            FROM  `" . $tbl_wrk_submission . "`
            WHERE `parent_id` IS NULL
            " . $sqlDateCondition . "
            ORDER BY `creation_date`";

    $results = claro_sql_query_fetch_all($sql);

    if( is_array($results) && !empty($results) )
    {
        $previousAuthors = '';
        $i = 1;

        $assignmentDir = replace_dangerous_char($_cid) . '_' . replace_dangerous_char(get_lang('Assignments')) . $wanted . '/';

        foreach($results as $row => $result)
        {
            //  count author's submissions for the name of directory
            if( $result['authors'] != $previousAuthors )
            {
                $i = 1;
                $previousAuthors = $result['authors'];
            }
            else
            {
                $i++;
            }

            $path = $coursesRepositorySys . $_course['path'] . '/work/assig_' . (int) $result['assignment_id'] . '/';

            $workDir = $assignmentDir
            . replace_dangerous_char(get_lang('Assignment')) . '_' . (int) $result['assignment_id'] . '/'
            ;

            $authorsDir = replace_dangerous_char($result['authors']) . '/';

            $submissionPrefix = $authorsDir . replace_dangerous_char(get_lang('Submission')) . '_' . $i . '_';

            // attached file
            if(!empty($result['submitted_doc_path']))
            {
                if(file_exists($path . $result['submitted_doc_path']))
                    $zipfile->addFile(file_get_contents($path . $result['submitted_doc_path']),
                                    $workDir . '/' . $submissionPrefix . $result['submitted_doc_path']);
            }

            // description file
            $txtFileName = replace_dangerous_char(get_lang('Description')) . '.html';

            $htmlContent = '<html><head></head><body>' . "\n"
            .     get_lang('Title') . ' : ' . $result['title'] . '<br />' . "\n"
            .     get_lang('First submission date') . ' : ' . $result['creation_date']. '<br />' . "\n"
            .     get_lang('Last edit date') . ' : ' . $result['last_edit_date'] . '<br />' . "\n"
            ;

            if( !empty($result['submitted_doc_path']) )
            {
                $htmlContent .= get_lang('Attached file') . ' : ' . $submissionPrefix . $result['submitted_doc_path']. '<br />' . "\n";
            }

            $htmlContent .= '<div>' . "\n"
            .     '<h3>' . get_lang('Description') . '</h3>' . "\n"
            .     $result['submitted_text']
            .     '</div>' . "\n"
            .     '</body></html>';

            $zipfile->addFile($htmlContent,
                            $workDir . '/' . $submissionPrefix . $txtFileName);
        }

        header('Content-type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . $assignmentDir . '.zip');
        echo $zipfile->file();

        exit;
    }
    else
    {
        $dialogBox->error( get_lang('There is no submission available for download with these settings.') );
    }
}

// Submission download requested
if( $is_allowedToEdit && $cmd == 'rqDownload' && get_conf('allow_download_all_submissions') )
{
    include($includePath . '/lib/form.lib.php');
    
    $dialogBox->title( get_lang('Download') );
    $dialogBox->form( '<form action="' . $_SERVER['PHP_SELF'] . '" method="POST">' . "\n"
    .    claro_form_relay_context()
    .    '<input type="hidden" name="cmd" value="exDownload" />' . "\n"
    .     '<input type="radio" name="downloadMode" id="downloadMode_from" value="from" checked /><label for="downloadMode_from">' . get_lang('Submissions posted or modified after date :') . '</label><br />' . "\n"
    .     claro_html_date_form('day', 'month', 'year', time(), 'long') . ' '
    .     claro_html_time_form('hour', 'minute', time() - fmod(time(), 86400) - 3600) . '<small>' . get_lang('(d/m/y hh:mm)') . '</small>' . '<br /><br />' . "\n"
    .     '<input type="radio" name="downloadMode" id="downloadMode_all" value="all" /><label for="downloadMode_all">' . get_lang('All submissions') . '</label><br /><br />' . "\n"
    .     '<input type="submit" value="'.get_lang('OK').'" />&nbsp;' . "\n"
    .    claro_html_button('work.php', get_lang('Cancel'))
    .     '</form>'."\n"
    );
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
                $eventNotifier->notifyCourseEvent('work_visible', claro_get_current_course_id(), claro_get_current_tool_id(), $_REQUEST['assigId'], claro_get_current_group_id(), '0');
            }
            else
            {
                $eventNotifier->notifyCourseEvent('work_invisible', claro_get_current_course_id(), claro_get_current_tool_id(), $_REQUEST['assigId'], claro_get_current_group_id(), '0');
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
        $eventNotifier->notifyCourseEvent('work_deleted', claro_get_current_course_id(), claro_get_current_tool_id(), $_REQUEST['assigId'], claro_get_current_group_id(), '0');

        $dialogBox->success( get_lang('Assignment deleted') );
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

            $dialogBox->success( get_lang('Assignment modified') );
        }
        else
        {
            if(claro_failure::get_last_failure() == 'assignment_no_title')
               $dialogBox->error( get_lang('Assignment title required') );
            if(claro_failure::get_last_failure() == 'assignment_title_already_exists')
               $dialogBox->error( get_lang('Assignment title already exists') );
            if(claro_failure::get_last_failure() == 'assignment_incorrect_dates')
               $dialogBox->error( get_lang('Start date must be before end date ...') );

            $cmd = 'rqEditAssig';
        }
    }
    /*-----------------------------------
    STEP 1 : display form
    -------------------------------------*/
    // edit assignment / display the form
    if( $cmd == 'rqEditAssig' )
    {
        include(get_path('incRepositorySys') . '/lib/form.lib.php');
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
            $dialogBox->success( get_lang('New assignment created') );

            if($lastAssigId)
            {
                //notify eventmanager that a new assignement is created
                $eventNotifier->notifyCourseEvent("work_added",claro_get_current_course_id(), claro_get_current_tool_id(), $lastAssigId, claro_get_current_group_id(), "0");
            }
        }
        else
        {
            if(claro_failure::get_last_failure() == 'assignment_no_title')
               $dialogBox->error( get_lang('Assignment title required') );
            if(claro_failure::get_last_failure() == 'assignment_title_already_exists')
               $dialogBox->error( get_lang('Assignment title already exists') );
            if(claro_failure::get_last_failure() == 'assignment_incorrect_dates')
               $dialogBox->error( get_lang('Start date must be before end date ...') );

            $cmd = 'rqMkAssig';
        }
    }

    /*-----------------------------------
        STEP 1 : display form
    -------------------------------------*/
    //--- create an assignment / display form
    if( $cmd == 'rqMkAssig' )
    {
        include(get_path('incRepositorySys') . '/lib/form.lib.php');
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
    if ( claro_is_in_a_group() && claro_is_group_allowed() )
    {
        // select only the group assignments
        $sql = "SELECT `id`,
                        `title`,
                        `def_submission_visibility`,
                          `visibility`,
                        `assignment_type`,
                        `authorized_content`,
                        unix_timestamp(`start_date`) as `start_date_unix`,
                        unix_timestamp(`end_date`) as `end_date_unix`
                FROM `" . $tbl_wrk_assignment . "`
                WHERE `assignment_type` = 'GROUP'";

            if ( isset($_GET['sort']) ) $sortKeyList[$_GET['sort']] = isset($_GET['dir']) ? $_GET['dir'] : SORT_ASC;

            $sortKeyList['end_date']    = SORT_ASC;
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

        $sortKeyList['end_date']    = SORT_ASC;
    }

    $offset = (isset($_REQUEST['offset']) && !empty($_REQUEST['offset']) ) ? $_REQUEST['offset'] : 0;
    $assignmentPager = new claro_sql_pager($sql, $offset, $assignmentsPerPage);

    foreach($sortKeyList as $thisSortKey => $thisSortDir)
    {
        $assignmentPager->add_sort_key( $thisSortKey, $thisSortDir);
    }

    $assignmentList = $assignmentPager->get_result_list();


include get_path('incRepositorySys') . '/claro_init_header.inc.php' ;

/*--------------------------------------------------------------------
                    TOOL TITLE
    --------------------------------------------------------------------*/

echo claro_html_tool_title($nameTools, $is_allowedToEdit ? 'help_work.php' : false);


if ($is_allowedToEdit)
{

    /*--------------------------------------------------------------------
                            DIALOG BOX SECTION
      --------------------------------------------------------------------*/

    echo $dialogBox->render();

    /*--------------------------------------------------------------------
                          CREATE AND EDIT FORM
      --------------------------------------------------------------------*/
    if ( isset($displayAssigForm) && $displayAssigForm )
    {
?>
    <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data">
    <input type="hidden" name="claroFormId" value="<?php echo uniqid(''); ?>" />
    <input type="hidden" name="cmd" value="<?php echo $cmdToSend; ?>" />
<?php
    if( isset($_REQUEST['assigId']) )
    {
?>
    <input type="hidden" name="assigId" value="<?php echo $_REQUEST['assigId']; ?>" />
<?php
    }
?>
    <table cellpadding="5" width="100%">
      <tr>
        <td valign="top"><label for="title"><?php echo get_lang('Assignment title'); ?>&nbsp;:</label></td>
        <td><input type="text" name="title" id="title" size="50" maxlength="200" value="<?php echo htmlspecialchars($assignment->getTitle()); ?>" /></td>
      </tr>

      <tr>
        <td valign="top"><label for="description"><?php echo get_lang('Description'); ?>&nbsp;:<br /></label></td>
        <td>
<?php
    echo claro_html_textarea_editor('description', $assignment->getDescription());
?>
        </td>
      </tr>

      <tr>
        <td valign="top"><?php echo get_lang('Submission type'); ?>&nbsp;:</td>
        <td>
          <input type="radio" name="authorized_content" id="authorizeFile" value="FILE" <?php if( $assignment->getSubmissionType() == "FILE" ) echo 'checked="checked"'; ?> />
            <label for="authorizeFile">&nbsp;<?php echo get_lang('File (file required, description text optional)'); ?></label>
            <br />
          <input type="radio" name="authorized_content" id="authorizeText" value="TEXT" <?php if( $assignment->getSubmissionType() == "TEXT" ) echo 'checked="checked"'; ?> />
            <label for="authorizeText">&nbsp;<?php echo get_lang('Text only (text required, no file)'); ?></label>
            <br />
          <input type="radio" name="authorized_content" id="authorizeTextFile" value="TEXTFILE" <?php if( $assignment->getSubmissionType() == "TEXTFILE" ) echo 'checked="checked"'; ?> />
            <label for="authorizeTextFile">&nbsp;<?php echo get_lang('Text with attached file (text required, file optional)'); ?></label>
            <br />
        </td>
      </tr>

      <tr>
        <td valign="top"><?php echo get_lang('Assignment type'); ?>&nbsp;:</td>
        <td>
          <input type="radio" name="assignment_type" id="individual" value="INDIVIDUAL" <?php if($assignment->getAssignmentType() == "INDIVIDUAL") echo 'checked="checked"'; ?> />
            <label for="individual">&nbsp;<?php echo get_lang('Individual'); ?></label>
            <br />
          <input type="radio" name="assignment_type" id="group" value="GROUP" <?php if($assignment->getAssignmentType() == "GROUP") echo 'checked="checked"'; ?> />
            <label for="group">&nbsp;<?php echo get_lang('Groups (from groups tool, only group members can post)'); ?></label>
            <br />
        </td>
      </tr>

      <tr>
        <td valign="top"><?php echo get_lang('Start date'); ?>&nbsp;:</td>
        <td>
<?php
    echo claro_html_date_form('startDay', 'startMonth', 'startYear', $assignment_data['start_date'], 'long') . ' ' . claro_html_time_form('startHour', 'startMinute', $assignment_data['start_date']);
    echo '&nbsp;<small>' . get_lang('(d/m/y hh:mm)') . '</small>';
?>
        </td>
      </tr>

      <tr>
        <td valign="top"><?php echo get_lang('End date'); ?>&nbsp;:</td>
        <td>
<?php
    echo claro_html_date_form('endDay', 'endMonth', 'endYear', $assignment_data['end_date'], 'long') . ' ' . claro_html_time_form('endHour', 'endMinute', $assignment_data['end_date']);
    echo '&nbsp;<small>' . get_lang('(d/m/y hh:mm)') . '</small>';
?>
        </td>
      </tr>

      <tr>
        <td valign="top"><?php echo get_lang('Allow late upload'); ?>&nbsp;:</td>
        <td>
        <input type="radio" name="allow_late_upload" id="allowUpload" value="YES" <?php if($assignment->getAllowLateUpload() == "YES") echo 'checked="checked"'; ?> />
          <label for="allowUpload">&nbsp;<?php echo get_lang('Yes, allow users to submit works after end date'); ?></label>
          <br />
        <input type="radio" name="allow_late_upload" id="preventUpload" value="NO" <?php if($assignment->getAllowLateUpload() == "NO") echo 'checked="checked"'; ?> />
          <label for="preventUpload">&nbsp;<?php echo get_lang('No, prevent users submitting work after the end date'); ?></label>
          <br />
        </td>
      </tr>

      <tr>
        <td valign="top"><?php echo get_lang('Default works visibility'); ?>&nbsp;:</td>
        <td>
          <input type="radio" name="def_submission_visibility" id="visible" value="VISIBLE" <?php if($assignment->getDefaultSubmissionVisibility() == "VISIBLE") echo 'checked="checked"'; ?> />
            <label for="visible">&nbsp;<?php echo get_lang('Visible for all users'); ?></label>
            <br />
          <input type="radio" name="def_submission_visibility" id="invisible" value="INVISIBLE" <?php if($assignment->getDefaultSubmissionVisibility() == "INVISIBLE") echo 'checked="checked"'; ?> />
            <label for="invisible">&nbsp;<?php echo get_lang('Only visible for teacher(s) and submitter(s)'); ?></label>
            <br />
        </td>
      </tr>

      <tr>
        <td>&nbsp;</td>
        <td>
          <input type="submit" name="submitAssignment" value="<?php echo get_lang('Ok'); ?>" />&nbsp;
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
        $cmdMenu[] =  claro_html_cmd_link( $_SERVER['PHP_SELF']
                                         . '?cmd=rqMkAssig'
                                         . claro_url_relay_context('&amp;')
                                         , '<img src="' . get_icon_url('assignment') . '" alt="" />'
                                         . get_lang('Create a new assignment')
                                         );

        if( get_conf('allow_download_all_submissions') )
        {
            $cmdMenu[] = '<a class="claroCmd" href="' . $_SERVER['PHP_SELF']
            .      '?cmd=rqDownload">'
            .     '<img src="' . get_icon_url('save') . '" alt="" />'.get_lang('Download submissions').'</a>'
            .     "\n"
            ;
        }
    }

    if( !empty($cmdMenu) ) echo '<p>' . claro_html_menu_horizontal($cmdMenu) . '</p>' . "\n";

    $headerUrl = $assignmentPager->get_sort_url_list($_SERVER['PHP_SELF']);

    echo $assignmentPager->disp_pager_tool_bar($_SERVER['PHP_SELF']);

    echo '<table class="claroTable" width="100%">' . "\n"
    .     '<tr class="headerX">'
    .     '<th><a href="' . $headerUrl['title'] . '">' . get_lang('Title') . '</a></th>' . "\n"
    .     '<th><a href="' . $headerUrl['assignment_type'] . '">' . get_lang('Type') . '</a></th>' . "\n"
    .     '<th><a href="' . $headerUrl['start_date_unix'] . '">' . get_lang('Start date') . '</a></th>' . "\n"
    .     '<th><a href="' . $headerUrl['end_date_unix'] . '">' . get_lang('End date') . '</a></th>' . "\n";

    $colspan = 4;

    if( isset($_REQUEST['submitGroupWorkUrl']) && !empty($_REQUEST['submitGroupWorkUrl']) )
    {
        echo '<th>' . get_lang('Publish') . '</th>' . "\n";
        $colspan++;
    }

    if( $is_allowedToEdit )
    {
        echo '<th>' . get_lang('Edit') . '</th>' . "\n"
        .     '<th>' . get_lang('Delete') . '</th>' . "\n"
        .     '<th>' . get_lang('Visibility') . '</th>' . "\n";
        $colspan += 3;
    }


    echo '</tr>' . "\n"
    .     '<tbody>' . "\n\n";


    $atLeastOneAssignmentToShow = false;

    if (claro_is_user_authenticated()) $date = $claro_notifier->get_notification_date(claro_get_current_user_id());

    foreach ( $assignmentList as $anAssignment )
    {
        //modify style if the file is recently added since last login and that assignment tool is used with visible default mode for submissions.
        $classItem='';
        if( claro_is_user_authenticated() )
        {
            if ( $claro_notifier->is_a_notified_ressource(claro_get_current_course_id(), $date, claro_get_current_user_id(), '',  claro_get_current_tool_id(), $anAssignment['id'],FALSE) && ($anAssignment['def_submission_visibility']=="VISIBLE"  || $is_allowedToEdit))
        {
            $classItem=' hot';
        }
            else //otherwise just display its name normally and tell notifier that every ressources are seen (for tool list notification consistancy)
        {
            $claro_notifier->is_a_notified_ressource(claro_get_current_course_id(), $date, claro_get_current_user_id(), '', claro_get_current_tool_id(), $anAssignment['id']);
        }
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
        .    '<td>' . "\n";
        
        $assignmentUrl = 'workList.php?assigId=' . $anAssignment['id'];    
        
        if ( isset($_REQUEST['submitGroupWorkUrl']) && !empty($_REQUEST['submitGroupWorkUrl']) )
        {
            if( !isset($anAssignment['authorized_content']) || $anAssignment['authorized_content'] != 'TEXT' )
            {
                $assignmentUrl = 'workList.php?cmd=rqSubWrk&amp;assigId=' . $anAssignment['id'] 
                . '&amp;submitGroupWorkUrl=' . urlencode($_REQUEST['submitGroupWorkUrl']) 
                . '&amp;gidReq=' . claro_get_current_group_id();
            }
        }
        
        echo '<a href="'.$assignmentUrl.'" class="item' . $classItem . '">'
        .    '<img src="' . get_icon_url('assignment') . '" alt="" /> '
        .    $anAssignment['title']
        .    '</a>' . "\n"
        .    '</td>' . "\n"
        ;

        echo '<td align="center">';

        if( $anAssignment['assignment_type'] == 'INDIVIDUAL' )
            echo '<img src="' . get_icon_url('user') . '" alt="' . get_lang('Individual') . '" />' ;
        elseif( $anAssignment['assignment_type'] == 'GROUP' )
            echo '<img src="' . get_icon_url('group') . '" alt="' . get_lang('Groups (from groups tool, only group members can post)') . '" />' ;
        else
            echo '&nbsp;';

        echo '</td>' . "\n"
        .    '<td><small>' . claro_html_localised_date(get_locale('dateTimeFormatLong'),$anAssignment['start_date_unix']) . '</small></td>' . "\n"
        .    '<td><small>' . claro_html_localised_date(get_locale('dateTimeFormatLong'),$anAssignment['end_date_unix']) . '</small></td>' . "\n";
        if ( isset($_REQUEST['submitGroupWorkUrl']) && !empty($_REQUEST['submitGroupWorkUrl']) )
        {
            if( !isset($anAssignment['authorized_content']) || $anAssignment['authorized_content'] != 'TEXT' )
            {
                echo '<td align="center">'
                .     '<a href="'.$assignmentUrl.'">'
                .      '<small>' . get_lang('Publish') . '</small>'
                .     '</a>'
                .     '</td>' . "\n";
            }
            else
            {
                echo '<td align="center">'
                .      '<small>-</small>'
                .     '</td>' . "\n"
                ;
            }
        }

        if ( $is_allowedToEdit )
        {
                        echo '<td align="center">'
            .    '<a href="' . $_SERVER['PHP_SELF'] . '?cmd=rqEditAssig&amp;assigId=' . $anAssignment['id'] . '">'
            .    '<img src="' . get_icon_url('edit') . '" alt="' . get_lang('Modify') . '" /></a>'
            .    '</td>' . "\n"
            .    '<td align="center">'
            .    '<a href="' . $_SERVER['PHP_SELF'] . '?cmd=exRmAssig&amp;assigId=' . $anAssignment['id'] . '" onclick="return confirmation(\'' . clean_str_for_javascript($anAssignment['title']) . '\');">'
            .    '<img src="' . get_icon_url('delete') . '" alt="' . get_lang('Delete') . '" /></a>'
            .    '</td>' . "\n"
            .    '<td align="center">'
            ;

            if ( $anAssignment['visibility'] == "INVISIBLE" )
            {
                echo '<a href="' . $_SERVER['PHP_SELF']
                .    '?cmd=exChVis&amp;assigId=' . $anAssignment['id']
                .    '&amp;vis=v">'
                .    '<img src="' . get_icon_url('invisible') . '" alt="' . get_lang('Make visible') . '" />'
                .    '</a>'
                      ;
            }
            else
            {
                echo '<a href="' . $_SERVER['PHP_SELF'] . '?cmd=exChVis&amp;assigId=' . $anAssignment['id'] . '&amp;vis=i">'
                .    '<img src="' . get_icon_url('visible') . '" alt="' . get_lang('Make invisible') . '" />'
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
    .     '</table>' . "\n\n";


}
// FOOTER
include get_path('incRepositorySys') . '/claro_init_footer.inc.php';
?>