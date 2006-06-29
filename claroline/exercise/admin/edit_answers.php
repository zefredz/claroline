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
// answer class should be inclulde by question class

include_once '../lib/exercise.lib.php'; 

// claroline libraries
include_once $includePath . '/lib/form.lib.php';
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

if( is_null($quId) || !$question->load($quId) ) 	
{
	header("Location: ../exercise.php");
	exit();	
}

if( !is_null($exId) )
{
	$exercise = new Exercise();
	// if exercise cannot be load set exId to null , it probably don't exist
	if( !$exercise->load($exId) ) $exId = null;	
}

if( $cmd == 'exEdit' )
{
	// add or remove answer, change step,...
	// should return true if form is really submitted	
	if( $question->answer->handleForm() )
	{
		// form has to be saved, check input validity 
		if( $question->answer->validate() )
		{
			if( $question->answer->save() )
			{
				// update grade in question
				$question->setGrade($question->answer->getGrade());
				$question->save();
				
				header("Location: ./edit_question.php?exId=".$exId."&quId=".$quId);
				exit();	
			}
		}	
	}

	if( $question->answer->getErrorList() )
	{
		$dialogBox = implode('<br />' . "\n", $question->answer->getErrorList());
	}
	// if we were not redirected it means form must be displayed
	$cmd =	'rqEdit';
}

/*
 * Output
 */
 
$interbredcrump[] = array ('url' => '../exercise.php', 'name' => get_lang('Exercises'));

if( !is_null($exId) ) 	$interbredcrump[] = array ('url' => './edit_exercise.php?exId='.$exId, 'name' => get_lang('Exercise').' : '.$exercise->getTitle()); 	
else					$interbredcrump[] = array ('url' => './question_pool.php', 'name' => get_lang('Question pool'));

if( !is_null($quId) ) 	$_SERVER['QUERY_STRING'] = 'exId='.$exId.'&amp;quId='.$quId;
else					$_SERVER['QUERY_STRING'] = '';  

$nameTools = get_lang('Edit answers');
 
include($includePath.'/claro_init_header.inc.php');
 
echo claro_html_tool_title($nameTools);



echo $question->getQuestionHtml();

// dialog box if required 
if( !empty($dialogBox) ) echo claro_html_message_box($dialogBox);

echo $question->answer->getFormHtml();	



include($includePath.'/claro_init_footer.inc.php');

?>
