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
 * @author Claro Team <cvs@claroline.net>
 *
 */
 
$tlabelReq = 'CLQWZ';
 
require '../../inc/claro_init_global.inc.php';

if ( !$_cid || !$is_courseAllowed ) claro_disp_auth_form(true);

$is_allowedToEdit = claro_is_allowed_to_edit();

// courseadmin reserved page
if( !$is_allowedToEdit )
{
	header("Location: ../exercise.php");
	exit();	
}

// tool libraries
include_once '../lib/exercise.class.php';
include_once '../lib/question.class.php';

include_once '../lib/exercise.lib.php'; 

// claroline libraries
include_once $includePath . '/lib/form.lib.php';
include_once $includePath . '/lib/fileDisplay.lib.php';
include_once $includePath . '/lib/fileUpload.lib.php';
include_once $includePath . '/lib/fileManage.lib.php';
include_once $includePath . '/lib/htmlxtra.lib.php';

/*
 * Execute commands
 */
if ( isset($_REQUEST['cmd']) )	$cmd = $_REQUEST['cmd'];
else							$cmd = '';

if( isset($_REQUEST['exId']) && is_numeric($_REQUEST['exId']) ) $exId = (int) $_REQUEST['exId'];
else															$exId = null;

if( isset($_REQUEST['quId']) && is_numeric($_REQUEST['quId']) ) $quId = (int) $_REQUEST['quId'];
else															$quId = null;

$question = new Question();

if( !is_null($quId) && !$question->load($quId) ) 	
{
	// question cannot be load, display new question creation form
	$cmd = 'rqEdit';  
	$quId = null;
}

if( !is_null($exId) )
{
	$exercise = new Exercise();
	// if exercise cannot be load set exId to null , it probably don't exist
	if( !$exercise->load($exId) ) $exId = null;	
}

$askDuplicate = false;
// quId and exId have been specified and load operations worked 
if( !is_null($quId) && !is_null($exId) )
{
	// do not duplicate when there is no $exId,
	// it means that we modify the question from pool
	
	// do not duplicate when there is no $quId,
	// it means that question is a new one
	
	// check that question is used in several exercises
	if( count_exercise_using_question($quId) > 1 )
    {
        if( isset($_REQUEST['duplicate']) && $_REQUEST['duplicate'] == 'true' )
        {
            // duplicate object if used in several exercises
            $duplicated = $question->duplicate();

        	// make exercise use the new created question object instead of the new one
        	$exercise->removeQuestion($quId);
            $quId = $duplicated->getId(); // and reset $quId
            $exercise->addQuestion($quId);
            
            $question = $duplicated;
        }
        else
        {
            $askDuplicate = true;
        }
    }    
}

$displayForm = false; 

if( $cmd == 'exEdit' )
{
	// if quId is null it means that we create a new question
	
	$question->setTitle($_REQUEST['title']);
	$question->setDescription($_REQUEST['description']);
	
	if( is_null($quId) ) $question->setType($_REQUEST['type']);
	
	// delete previous file if required
	if( isset($_REQUEST['delAttachment']) && !is_null($quId) )
	{
		$question->deleteAttachment(); 	
	}
	
	if( $question->validate() )
	{
		// handle uploaded file after validation of other fields		
		if( isset($_FILES['attachment']['tmp_name']) && is_uploaded_file($_FILES['attachment']['tmp_name']) )
		{
			if( !$question->setAttachment($_FILES['attachment']) )
			{
				// throw error
				echo claro_failure::get_last_failure();	
			}		
		}
		
		$insertedId = $question->save();
		if( $insertedId )
		{
			// if create a new question in exercise context 
			if( is_null($quId) && !is_null($exId) )
			{
				$exercise->addQuestion($insertedId);
			}
			
			// create a new question
			if( is_null($quId) )
			{
				// Go to answer edition
				header('Location: edit_answers.php?exId='.$exId.'&quId='.$insertedId);
				exit();	
			}
		}
		else
		{
			// sql error in save() ?
			$cmd = 'rqEdit';	
		}		
	}
	else
	{
		if( claro_failure::get_last_failure() == 'question_no_title' )
		{
			$dialogBox = get_lang('Field \'%name\' is required', array('%name' => get_lang('Title')));
		}
		$cmd = 'rqEdit';		
	}	
	
}

if( $cmd == 'rqEdit' )
{
	$form['title'] 				= $question->getTitle();
	$form['description'] 		= $question->getDescription();
	$form['attachment']			= $question->getAttachment();
	$form['type'] 				= $question->getType();
	
	$displayForm = true;
}  

/*
 * Output
 */

$interbredcrump[] = array ('url' => '../exercise.php', 'name' => get_lang('Exercises'));

if( !is_null($exId) ) 	$interbredcrump[] = array ('url' => './edit_exercise.php?exId='.$exId, 'name' => get_lang('Exercise').' : '.$exercise->getTitle()); 	
else					$interbredcrump[] = array ('url' => './question_pool.php', 'name' => get_lang('Question pool'));

if( !is_null($quId) ) 	$_SERVER['QUERY_STRING'] = 'exId='.$exId.'&amp;quId='.$quId;
else					$_SERVER['QUERY_STRING'] = '';  

if( is_null($quId) )		$nameTools = get_lang('New question');
elseif( $cmd == 'rqEdit' )	$nameTools = get_lang('Edit question');
else						$nameTools = get_lang('Question');

 
include($includePath.'/claro_init_header.inc.php');
 
echo claro_html_tool_title($nameTools);

// dialog box if required 
if( !empty($dialogBox) ) echo claro_html_message_box($dialogBox);


$localizedQuestionType = get_localized_question_type();	
	
if( $displayForm )
{
	echo '<form method="post" action="./edit_question.php?quId='.$quId.'&amp;exId='.$exId.'" enctype="multipart/form-data">' . "\n\n"
	.	 '<input type="hidden" name="cmd" value="exEdit" />' . "\n"
	.	 '<input type="hidden" name="claroFormId" value="'.uniqid('').'">' . "\n";
	
    echo '<table border="0" cellpadding="5">' . "\n";
	
    if( $askDuplicate )
    {
        echo '<tr>' . "\n"
        .	 '<td>&nbsp;</td>' . "\n"
        .    '<td valign="top">'
        .    html_ask_duplicate()
        .    '</td>' . "\n"
        .    '</tr>' . "\n\n";
    }
	//-- 
	// title
	echo '<tr>' . "\n"
	.	 '<td valign="top"><label for="title">'.get_lang('Title').'&nbsp;<span class="required">*</span>&nbsp;:</label></td>' . "\n"
	.	 '<td><input type="text" name="title" id="title" size="60" maxlength="200" value="'.$form['title'].'" /></td>' . "\n"
	.	 '</tr>' . "\n\n";
	
	// description
	echo '<tr>' . "\n"
	.	 '<td valign="top"><label for="description">'.get_lang('Description').'&nbsp;:</label></td>' . "\n"
	.	 '<td>'.claro_html_textarea_editor('description', $form['description']).'</td>' . "\n"
	.	 '</tr>' . "\n\n";	

	// attached file
	if( !empty($form['attachment']) )
	{
		echo '<tr>' . "\n"
		.	 '<td valign="top">'.get_lang('Current file').'&nbsp;:</td>' . "\n"
		.	 '<td>'
		.	 '<a href="'.$question->getQuestionDirWeb().$form['attachment'].'" target="_blank">'.$form['attachment'].'</a><br />'
		.	 '<input type="checkbox" name="delAttachment" id="delAttachment" /><label for="delAttachment"> '.get_lang('Delete attached file').'</label>'
		.	 '</td>' . "\n"
		.	 '</tr>' . "\n\n";	
	}
	
	echo '<tr>' . "\n"
	.	 '<td valign="top"><label for="description">'.get_lang('Attached file').'&nbsp;:</label></td>' . "\n"
	.	 '<td><input type="file" name="attachment" id="attachment" size="30" /></td>' . "\n"
	.	 '</tr>' . "\n\n";	
	
	// answer type, only if new question
	if( is_null($quId) )
	{
		echo '<tr>' . "\n"
		.	 '<td valign="top">'.get_lang('Answer type').'&nbsp;:</td>' . "\n"
		.	 '<td>' . "\n"
		.	 '<input type="radio" name="type" id="MCUA" value="MCUA"'
		.	 ( $form['type'] == 'MCUA'?' checked="checked"':' ') . '>'
		.	 ' <label for="MCUA">'.get_lang('Multiple choice (Unique answer)').'</label>'
		.	 '<br />' . "\n"
		.	 '<input type="radio" name="type" id="MCMA" value="MCMA"'
		.	 ( $form['type'] == 'MCMA'?' checked="checked"':' ') . '>'
		.	 ' <label for="MCMA">'.get_lang('Multiple choice (Multiple answers)').'</label>'
		.	 '<br />' . "\n"
		.	 '<input type="radio" name="type" id="TF" value="TF"'
		.	 ( $form['type'] == 'TF'?' checked="checked"':' ') . '>'
		.	 ' <label for="TF">'.get_lang('True/False').'</label>'
		.	 '<br />' . "\n"
		.	 '<input type="radio" name="type" id="FIB" value="FIB"'
		.	 ( $form['type'] == 'FIB'?' checked="checked"':' ') . '>'
		.	 ' <label for="FIB">'.get_lang('Fill in blanks').'</label>'
		.	 '<br />' . "\n"
		.	 '<input type="radio" name="type" id="MATCHING" value="MATCHING"'
		.	 ( $form['type'] == 'MATCHING'?' checked="checked"':' ') . '>'
		.	 ' <label for="MATCHING">'.get_lang('Matching').'</label>'
		.	 "\n"
		.	 '</td>' . "\n"
		.	 '</tr>' . "\n\n";
	}
	else
	{
		echo '<tr>' . "\n"
		.	 '<td valign="top">'.get_lang('Answer type').'&nbsp;:</td>' . "\n"
		.	 '<td>';

		if( isset($localizedQuestionType[$form['type']]) ) echo $localizedQuestionType[$form['type']];
		
		echo '</td>' . "\n"
		.	 '</tr>' . "\n\n";
	}

	//--
	echo '<tr>' . "\n"
	.	 '<td>&nbsp;</td>' . "\n"
	.	 '<td><small>' . get_lang('<span class="required">*</span> denotes required field') . '</small></td>' . "\n"
	.	 '</tr>' . "\n\n";
		
	//-- buttons
	echo '<tr>' . "\n"
	.	 '<td>&nbsp;</td>' . "\n"
	.	 '<td>'
	.	 '<input type="submit" name="" id="" value="'.get_lang('Ok').'" />&nbsp;&nbsp;';
	if( !is_null($exId) )	echo claro_html_button('./edit_exercise.php?exId='.$exId, get_lang("Cancel") );
	else					echo claro_html_button('./question_pool.php', get_lang("Cancel") );
	echo '</td>' . "\n"
	.	 '</tr>' . "\n\n";

	echo '</table>' . "\n\n"
	.	 '</form>' . "\n\n";
}
else
{
	$cmd_menu = array();
	$cmd_menu[] = '<a class="claroCmd" href="./edit_exercise.php?exId='.$exId.'">'
				. '&lt;&lt; ' . get_lang('Back to the question list')
				. '</a>';
	$cmd_menu[] = '<a class="claroCmd" href="./edit_question.php?exId='.$exId.'&amp;cmd=rqEdit&amp;quId='.$quId.'">'
				. '<img src="'.$clarolineRepositoryWeb.'img/edit.gif" border="0" alt="" />'
				. get_lang('Edit question')
				. '</a>';
	$cmd_menu[] = '<a class="claroCmd" href="./edit_answers.php?exId='.$exId.'&amp;cmd=rqEdit&amp;quId='.$quId.'">'
				. '<img src="'.$clarolineRepositoryWeb.'img/edit.gif" border="0" alt="" />'
				. get_lang('Edit answers')
				. '</a>';
	
	echo claro_html_menu_horizontal($cmd_menu);
				
	echo $question->getQuestionAnswerHtml();
	

} 

include($includePath.'/claro_init_footer.inc.php');

?>
