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

$tbl_cdb_names = claro_sql_get_course_tbl();
$tbl_wrk_assignment = $tbl_cdb_names['wrk_assignment'  ];
$tbl_wrk_submission = $tbl_cdb_names['wrk_submission'   ];    


$currentUserFirstName = $_user['firstName'];
$currentUserLastName  = $_user['lastName'];

if ( ! $_cid || ! $is_courseAllowed ) claro_disp_auth_form(true);

event_access_tool($_tid, $_courseTool['label']);

include($includePath . '/lib/fileUpload.lib.php');
include($includePath . '/lib/fileDisplay.lib.php'); // need format_url function
include($includePath . '/lib/fileManage.lib.php'); // need claro_delete_file

/*= = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = 
                     BASIC VARIABLES DEFINITION
  = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = */
$currentCourseRepositorySys = $coursesRepositorySys . $_course['path'] . '/';
$currentCourseRepositoryWeb = $coursesRepositoryWeb . $_course['path'] . '/';

$fileAllowedSize = $max_file_size_per_works ;    //file size in bytes
$wrkDir           = $currentCourseRepositorySys . 'work/'; //directory path to create assignment dirs

// use with strip_tags function when strip_tags is used to check if a text is empty
// but a 'text' with only an image don't have to be considered as empty 
$allowedTags = '<img>';

// initialise dialog box to an empty string, all dialog will be concat to it
$dialogBox = '';

// permission
$is_allowedToEdit = claro_is_allowed_to_edit();

/*= = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = 
                     CLEAN INFORMATIONS SEND BY USER
  = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = */

$cmd = ( isset($_REQUEST['cmd']) )?$_REQUEST['cmd']:'';


/*= = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = 
                HANDLING FORM DATA : CREATE/EDIT ASSIGNMENT FEEDBACK
  = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = */
// execute this after a form has been send
// this instruction bloc will set some vars that will be used in the corresponding queries
// do not execute if there is no assignment ID
if( isset($_REQUEST['submitFeedback']) && isset($_REQUEST['assigId']) && $is_allowedToEdit )
{
    $formCorrectlySent = true;
    // Feedback 
    // check if there is text in it 
    if( trim( strip_tags($_REQUEST['prefillText'], $allowedTags ) ) == '' )
    {
      $prefillText = '';
    }
    else
    {
      $prefillText = trim($_REQUEST['prefillText']);
    }
    // uploaded file come from the feedback form
    if ( is_uploaded_file($_FILES['prefillDocPath']['tmp_name']) )
    {      
          if ($_FILES['prefillDocPath']['size'] > $fileAllowedSize)
          {
                $dialogBox .= get_lang('TooBig') . '<br />';
                $formCorrectlySent = false;
          }
          else
          {     
                // add file extension if it doesn't have one
                $newFileName = $_FILES['prefillDocPath']['name']
                             . add_extension_for_uploaded_file($_FILES['prefillDocPath']);

                // Replace dangerous characters
                $newFileName = replace_dangerous_char($newFileName);
                
                // Transform any .php file in .phps fo security
                $newFileName = get_secure_file_name($newFileName);
                
                  // -- create a unique file name to avoid any conflict
                // there can be only one automatic feedback but the file is put in the
                // assignments directory
                $assigDirSys = $wrkDir . 'assig_' . $_REQUEST['assigId'] . '/';
                // split file and its extension
                $dotPosition = strrpos($newFileName, '.');
                if( $dotPosition !== false &&  $dotPosition != 0 )
                {
					// if a dot was found and not as first letter (case of files like .blah)
                	$filename = substr($newFileName, 0, $dotPosition );
                	$extension = substr($newFileName, $dotPosition);
				}
				else
				{
					// if we have no extension
					$filename = $newFileName;
					$extension = '';
				}

                $i = 0;
                while( file_exists($assigDirSys.$filename."_".$i.$extension) ) $i++;
                
                $prefillDocPath = $filename . '_' . $i . $extension;
                
                $tmpWorkUrl = $assigDirSys.$prefillDocPath;

                if( move_uploaded_file($_FILES['prefillDocPath']['tmp_name'], $tmpWorkUrl) )
                {
					chmod($tmpWorkUrl,CLARO_FILE_PERMISSIONS);
				}
				else
				{
                    $dialogBox .= get_lang('CannotCopyFile') . '<br />';
                    $formCorrectlySent = false;
                }

                // remove the previous file if there was one
                if( isset($_REQUEST['currentPrefillDocPath']) )
                {
                    if( file_exists($assigDirSys.$_REQUEST['currentPrefillDocPath']) )
                        claro_delete_file($assigDirSys.$_REQUEST['currentPrefillDocPath']);
                }
                
                //report event to eventmanager "feedback_posted"              
                $eventNotifier->notifyCourseEvent("work_feedback_posted",$_cid, $_tid, $_REQUEST['assigId'], '0', '0');
                
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
        // delete the file was requested
        $prefillDocPath = ''; // empty DB field
    
        if( file_exists($wrkDir . 'assig_' . $_REQUEST['assigId'] . '/' . $_REQUEST['currentPrefillDocPath']) )
            claro_delete_file($wrkDir . 'assig_' . $_REQUEST['assigId'] . '/' . $_REQUEST['currentPrefillDocPath']);
    }
    else
    {
      $prefillDocPath = '';
    }
    
    $prefillSubmit = $_REQUEST['prefillSubmit'];
}

if($is_allowedToEdit)
{

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
                SET `prefill_text` = '" . addslashes($prefillText) . "',
                    `prefill_doc_path` = '" . addslashes($prefillDocPath) . "',
                    `prefill_submit` = '" . addslashes($prefillSubmit) . "'
                WHERE `id` = ". (int)$_REQUEST['assigId'];
            claro_sql_query($sql);

            $dialogBox .= get_lang('FeedbackEdited') . '<br /><br />'
                       .  '<a href="./workList.php?assigId=' . $_REQUEST['assigId'] . '">' 
                       . get_lang('Back') 
                       . '</a>'
                       ;
                       
            $displayFeedbackForm = false;
        } 
        else
        {
            $cmd = 'rqEditFeedback';
        }
    }
    
    /*-----------------------------------
        STEP 1 : display form
    -------------------------------------*/
    // edit assignment / display the form
    if( $cmd == 'rqEditFeedback' )
    {
        include($includePath . '/lib/form.lib.php');
        
        // check if it was already sent
        if( !isset($_REQUEST['submitAssignment'] ) )
        {
            // get current settings to fill in the form
            $sql = "SELECT `prefill_text` , `prefill_doc_path`, `prefill_submit`,
                          UNIX_TIMESTAMP(`end_date`) as `unix_end_date`
                    FROM `".$tbl_wrk_assignment."`
                    WHERE `id` = ". (int)$_REQUEST['assigId'];
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

}

/*= = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =
                      DISPLAY
  = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =*/

/*--------------------------------------------------------------------
                            HEADER
  --------------------------------------------------------------------*/

// bredcrump to return to the list when in a form
$interbredcrump[]= array ('url' => "./work.php", 'name' => get_lang('Work'));
$interbredcrump[]= array ('url' => "./workList.php?assigId=".$_REQUEST['assigId'], 'name' => get_lang('Assignment'));
$nameTools = get_lang('Feedback');


include($includePath.'/claro_init_header.inc.php');

echo claro_disp_tool_title($nameTools);

if ($dialogBox)
{
    echo claro_disp_message_box($dialogBox);
}
    /*--------------------------------------------------------------------
                        FEEDBACK FORM
    --------------------------------------------------------------------*/
if( isset($displayFeedbackForm) && $displayFeedbackForm )
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
    <table cellpadding="5" width="100%">
      <tr>
        <td valign="top" colspan="2"><p><?php echo get_lang('FeedbackHelp'); ?></p></td>
      </tr>
      <tr>
        <td valign="top"><label for="prefillText"><?php echo get_lang('FeedbackText'); ?>&nbsp;:<br /></label></td>
        <td>
<?php          
        echo claro_disp_html_area('prefillText', htmlspecialchars($form['prefillText']));
?> 
        </td>
      </tr>
<?php
    if( isset($form['currentPrefillDocPath']) && $form['currentPrefillDocPath'] != "" )
    {
          $completeFileUrl = $currentCourseRepositoryWeb."work/assig_".$_REQUEST['assigId']."/".$form['currentPrefillDocPath'];
          echo '<tr>' . "\n"
          .    '<td valign="top">'
          .    get_lang('CurrentFeedbackFile')
          
          // display the name of the file, with a link to it, an explanation of what to to to replace it and a checkbox to delete it
          .    '&nbsp;:'
          .    '<input type="hidden" name="currentPrefillDocPath" value="' . $form['currentPrefillDocPath'] . '">'
          .    '</td>' . "\n"
          .    '<td>'
          .    '<a href="' . $completeFileUrl . '">' . $form['currentPrefillDocPath'] . '</a>'
          .    '<br />'
          .    '<input type="checkBox" name="delFeedbackFile" id="delFeedbackFile">'
          .    '<label for="delFeedbackFile">' 
          .    get_lang('ExplainDeleteFile') . ' ' . get_lang('ExplainReplaceFile') 
          .    '</label> '
          .    '</td>'."\n"
          .    '</tr>'
          ;
    }
?>
      <tr>
        <td valign="top"><label for="prefillDocPath"><?php echo get_lang('FeedbackFile'); ?>&nbsp;:<br /></label></td>
        <td>
        <input type="file" name="prefillDocPath" id="prefillDocPath" size="30">
        </td>
      </tr>
 
      <tr>
        <td valign="top"><?php echo get_lang('FeedbackSubmit'); ?>&nbsp;:</td>
        <td>
        <input type="radio" name="prefillSubmit" id="prefillSubmitEndDate" value="ENDDATE" <?php if($form['prefillSubmit'] == "ENDDATE") echo 'checked="checked"'; ?>>
          <label for="prefillSubmitEndDate">&nbsp;<?php echo get_lang('SubmitFeedbackAfterEndDate') . ' (' . claro_disp_localised_date($dateTimeFormatLong, $form['unix_end_date']) . ')'; ?></label>
          <br />
        <input type="radio" name="prefillSubmit" id="prefillSubmitAfterPost" value="AFTERPOST" <?php if($form['prefillSubmit'] == "AFTERPOST") echo 'checked="checked"'; ?>>
          <label for="prefillSubmitAfterPost">&nbsp;<?php echo get_lang('SubmitFeedbackAfterPost'); ?></label>
          <br />
        </td>
      </tr>
      
      <tr>
        <td>&nbsp;</td>
        <td>
          <input type="submit" name="submitFeedback" value="<?php echo get_lang('Ok'); ?>">
<?php echo claro_disp_button($_SERVER['PHP_SELF'], get_lang('Cancel')); ?>
        </td>
      </tr>
      </table>
    </form>
<?php
}

// FOOTER
include $includePath . '/claro_init_footer.inc.php'; 
?>
