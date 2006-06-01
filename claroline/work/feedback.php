<?php // $Id$
/**
 * CLAROLINE
 *
 * @version 1.8 $Revision$
 *
 * @copyright (c) 2001-2006 Universite catholique de Louvain (UCL)
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

if ( ! $_cid || ! $is_courseAllowed ) claro_disp_auth_form(true);

require_once './lib/assignment.class.php';

include_once $includePath . '/lib/fileUpload.lib.php';
include_once $includePath . '/lib/fileDisplay.lib.php'; // need format_url function
include_once $includePath . '/lib/fileManage.lib.php'; // need claro_delete_file

/*= = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =
BASIC VARIABLES DEFINITION
= = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = */
$fileAllowedSize = get_conf('max_file_size_per_works') ;    //file size in bytes

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
$cmd = ( isset($_REQUEST['cmd']) )?$_REQUEST['cmd']:'';

$isFeedbackSubmitted = (bool) isset($_REQUEST['submitFeedback']);

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
ASSIGNMENT INFORMATIONS
--------------------------------------------------------------------*/
$assignment = new Assignment();

if ( !$assignmentId || !$assignment->load($assignmentId) )
{
    // we NEED to know in which assignment we are, so if assigId is not set
    // relocate the user to the previous page
    header('Location: work.php');
    exit();
}

/*= = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =
HANDLING FORM DATA : CREATE/EDIT ASSIGNMENT FEEDBACK
= = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = */
// execute this after a form has been send
// this instruction bloc will set some vars that will be used in the corresponding queries
// do not execute if there is no assignment ID
if( $is_allowedToEdit && $isFeedbackSubmitted && $assignmentId  )
{
    $formCorrectlySent = true;
    // Feedback
    // check if there is text in it
    if( trim( strip_tags($_REQUEST['autoFeedbackText'], $allowedTags ) ) == '' )
    {
        $autoFeedbackText = '';
    }
    else
    {
        $autoFeedbackText = trim($_REQUEST['autoFeedbackText']);
    }

    // uploaded file come from the feedback form
    if ( is_uploaded_file($_FILES['autoFeedbackFilename']['tmp_name']) )
    {
        if ($_FILES['autoFeedbackFilename']['size'] > $fileAllowedSize)
        {
            $dialogBox .= get_lang('You didnt choose any file to send, or it is too big') . '<br />';
            $formCorrectlySent = false;
            $autoFeedbackFilename = $assignment->getAutoFeedbackFilename();
        }
        else
        {
            // add file extension if it doesn't have one
            $newFileName  = $_FILES['autoFeedbackFilename']['name'];
            $newFileName .= add_extension_for_uploaded_file($_FILES['autoFeedbackFilename']);

            // Replace dangerous characters
            $newFileName = replace_dangerous_char($newFileName);

            // Transform any .php file in .phps fo security
            $newFileName = get_secure_file_name($newFileName);


            // -- create a unique file name to avoid any conflict
            // there can be only one automatic feedback but the file is put in the
            // assignments directory
			$autoFeedbackFilename = $assignment->createUniqueFilename($newFileName);

            $tmpWorkUrl = $assignment->getAssigDirSys().$autoFeedbackFilename;

            if( move_uploaded_file($_FILES['autoFeedbackFilename']['tmp_name'], $tmpWorkUrl) )
            {
                chmod($tmpWorkUrl, CLARO_FILE_PERMISSIONS);
            }
            else
            {
                $dialogBox .= get_lang('Cannot copy the file') . '<br />';
                $formCorrectlySent = false;
            }

            // remove the previous file if there was one
            if( $assignment->getAutoFeedbackFilename() != '' )
            {
                if( file_exists($assignment->getAssigDirSys().$assignment->getAutoFeedbackFilename()) )
                {
                	claro_delete_file($assignment->getAssigDirSys().$assignment->getAutoFeedbackFilename());
                }
            }

            // else : file sending shows no error
            // $formCorrectlySent stay true;
        }
    }
    elseif( isset($_REQUEST['delFeedbackFile']) )
    {
        // delete the file was requested
		if( file_exists($assignment->getAssigDirSys().$assignment->getAutoFeedbackFilename()) )
        {
        	claro_delete_file($assignment->getAssigDirSys().$assignment->getAutoFeedbackFilename());
        }
        $autoFeedbackFilename = '';
    }
    else
    {
        $autoFeedbackFilename = $assignment->getAutoFeedbackFilename();
    }

    $autoFeedbackSubmitMethod = $_REQUEST['autoFeedbackSubmitMethod'];
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
    	$assignment->setAutoFeedbackText($autoFeedbackText);
    	$assignment->setAutoFeedbackFilename($autoFeedbackFilename);
    	$assignment->setAutoFeedbackSubmitMethod($autoFeedbackSubmitMethod);

        // form data have been handled before this point if the form was sent
        if( $formCorrectlySent && $assignment->save() )
        {
            $dialogBox .= get_lang('Feedback Edited') . '<br /><br />';
            $dialogBox .= '<a href="./workList.php?assigId=' . $_REQUEST['assigId'] . '">';
            $dialogBox .= get_lang('Back');
            $dialogBox .= '</a>';

            $displayFeedbackForm = false;

            //report event to eventmanager "feedback_posted"
            $eventNotifier->notifyCourseEvent("work_feedback_posted",$_cid, $_tid, $_REQUEST['assigId'], '0', '0');
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
        if( !$isFeedbackSubmitted )
        {
            // feedback
            $form['autoFeedbackText'] 			= $assignment->getAutoFeedbackText();
            $form['autoFeedbackFilename'] 		= $assignment->getAutoFeedbackFilename();
            $form['autoFeedbackSubmitMethod'] 	= $assignment->getAutoFeedbackSubmitMethod();

            // end date (as a reminder for the "after end date" option
        	$form['unix_end_date']				= $assignment->getEndDate();
        }
        else
        {
            // there was an error in the form
            $form['autoFeedbackText'] 			= $_REQUEST['autoFeedbackText'];
        	$form['autoFeedbackFilename'] 		= $_REQUEST['autoFeedbackFilename'];
            $form['autoFeedbackSubmitMethod'] 	= $_REQUEST['autoFeedbackSubmitMethod'];
        }

        // ask the display of the form
        if($form['autoFeedbackSubmitMethod'] == 'ENDDATE')
        {
        	$prefillSubmitEndDateCheckStatus 	= 'checked="checked"';
        	$prefillSubmitAfterPostCheckStatus 	= '';
        }
        elseif($form['autoFeedbackSubmitMethod'] == 'AFTERPOST')
        {
        	$prefillSubmitEndDateCheckStatus 	= '';
        	$prefillSubmitAfterPostCheckStatus 	= 'checked="checked"';
        }

        $displayFeedbackForm = true;
    }

}

/**
 * DISPLAY
 */

/**
 * HEADER
 */

// bredcrump to return to the list when in a form
$interbredcrump[]= array ('url' => './work.php', 'name' => get_lang('Assignments'));
$interbredcrump[]= array ('url' => './workList.php?assigId=' . $_REQUEST['assigId'], 'name' => get_lang('Assignment'));
$nameTools = get_lang('Feedback');

include $includePath . '/claro_init_header.inc.php';

echo claro_html_tool_title($nameTools);

if ($dialogBox) echo claro_html_message_box($dialogBox);
/**
 * FEEDBACK FORM
 */
if( isset($displayFeedbackForm) && $displayFeedbackForm )
{
    echo '<form method="post" action="' . $_SERVER['PHP_SELF'] . '" enctype="multipart/form-data">' . "\n"
    .    '<input type="hidden" name="cmd" value="exEditFeedback" />' . "\n"
    ;

    if( isset($_REQUEST['assigId']) )
    {
        echo '<input type="hidden" name="assigId" value="' . $_REQUEST['assigId'] . '" />' . "\n";
    }
    echo '<table cellpadding="5" width="100%">' . "\n\n"
    .    '<tr>' . "\n"
    .    '<td valign="top" colspan="2">' . "\n"
    .    '<p>' . "\n"
    .    get_block('blockFeedbackHelp') . "\n"
    .    '</p>' . "\n"
    .    '</td>' . "\n"
    .    '</tr>' . "\n\n"
    .    '<tr>' . "\n"
    .    '<td valign="top">' . "\n"
    .    '<label for="autoFeedbackText">' . "\n"
    .    get_lang('Feedback text') . "\n"
    .    '&nbsp;:' . "\n"
    .    '<br />' . "\n"
    .    '</label>' . "\n"
    .    '</td>' . "\n"
    .    '<td>' . "\n"
    .    claro_html_textarea_editor('autoFeedbackText', htmlspecialchars($form['autoFeedbackText']))
    .    '</td>' . "\n"
    .    '</tr>' . "\n\n"
    ;

    if( isset($form['autoFeedbackFilename']) && $form['autoFeedbackFilename'] != '' )
    {
    	$target = ( get_conf('open_submitted_file_in_new_window') ? 'target="_blank"' : '');
        $completeFileUrl = $assignment->getAssigDirWeb() . $form['autoFeedbackFilename'];

        echo '<tr>' . "\n"
        .    '<td valign="top">'
        .    get_lang('Current feedback file')

        // display the name of the file, with a link to it, an explanation of what to to to replace it and a checkbox to delete it
        .    '&nbsp;:'
        .    '<input type="hidden" name="currentAutoFeedbackFilename" value="' . $form['autoFeedbackFilename'] . '" />'
        .    '</td>' . "\n"
        .    '<td>'
        .    '<a href="' . $completeFileUrl . '" ' . $target . '>' . $assignment->getAutoFeedbackFilename() . '</a>'
        .    '<br />'
        .    '<input type="checkBox" name="delFeedbackFile" id="delFeedbackFile" />'
        .    '<label for="delFeedbackFile">'
        .    get_lang('Check this box to delete the attached file') . ' ' . get_lang('Upload a new file to replace the file')
        .    '</label> '
        .    '</td>' . "\n"
        .    '</tr>' . "\n\n"
        ;
    }

    echo '<tr>' . "\n"
    .    '<td valign="top">' . "\n"
    .    '<label for="autoFeedbackFilename">' . "\n"
    .    get_lang('Feedback file')
    .    '&nbsp;:<br />' . "\n"
    .    '</label>' . "\n"
    .    '</td>' . "\n"
    .    '<td>' . "\n"
    .    '<input type="file" name="autoFeedbackFilename" id="autoFeedbackFilename" size="30" />' . "\n"
    .    '</td>' . "\n"
    .    '</tr>' . "\n\n"
    .    '<tr>' . "\n"
    .    '<td valign="top">' . "\n"
    .    get_lang('Submit feedback')
    .    '&nbsp;:</td>' . "\n"
    .    '<td>' . "\n"
    .    '<input type="radio" name="autoFeedbackSubmitMethod" id="prefillSubmitEndDate" value="ENDDATE" '
    .    $prefillSubmitEndDateCheckStatus . '/>' . "\n"
    .    '<label for="prefillSubmitEndDate">' . "\n"
    .    '&nbsp;' . "\n"
    .    get_lang('Automatically, after end date')
    .    ' (' . claro_disp_localised_date($dateTimeFormatLong, $form['unix_end_date']) . ')' . "\n"
    .    '</label>' . "\n"
    .    '<br />' . "\n"
    .    '<input type="radio" name="autoFeedbackSubmitMethod" id="prefillSubmitAfterPost" value="AFTERPOST" '
    .    $prefillSubmitAfterPostCheckStatus . '>' . "\n"
    .    '<label for="prefillSubmitAfterPost">&nbsp;' . "\n"
    .    get_lang('Automatically, after each submission') . "\n"
    .    '</label>' . "\n"
    .    '<br />' . "\n"
    .    '</td>' . "\n"
    .    '</tr>' . "\n\n"
    .    '<tr>' . "\n"
    .    '<td>&nbsp;</td>' . "\n"
    .    '<td>' . "\n"
    .    '<input type="submit" name="submitFeedback" value="' . get_lang('Ok') . '">' . "\n"
    .    claro_html_button($_SERVER['PHP_SELF'], get_lang('Cancel')) . "\n"
    .    '</td>' . "\n"
    .    '</tr>' . "\n\n"
    .    '</table>' . "\n"
    .    '</form>' . "\n"
    ;

}

// FOOTER
include $includePath . '/claro_init_footer.inc.php';
?>