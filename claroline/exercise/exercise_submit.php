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

$tlabelReq = 'CLQWZ___';
 
require '../inc/claro_init_global.inc.php';

if ( !$_cid || !$is_courseAllowed ) claro_disp_auth_form(true);

claro_set_display_mode_available(true);

$is_allowedToEdit = claro_is_allowed_to_edit();

// tool libraries
include_once './lib/exercise.class.php';
include_once './lib/question.class.php';
include_once './lib/exercise.lib.php';

// following includes are not really clean as the question object already includes the one it needs
// but for the moment it is required by unserialize
include_once './lib/answer_truefalse.class.php';
include_once './lib/answer_multiplechoice.class.php';
include_once './lib/answer_fib.class.php';
include_once './lib/answer_matching.class.php';

// claroline libraries
include_once $includePath . '/lib/htmlxtra.lib.php';
include_once $includePath . '/lib/form.lib.php';


/*
 * Execute commands
 */
if( isset($_REQUEST['exId']) && is_numeric($_REQUEST['exId']) ) $exId = (int) $_REQUEST['exId'];
else															$exId = null;

if( isset($_REQUEST['step']) && is_numeric($_REQUEST['step']) ) $step = (int) $_REQUEST['step'];
else															$step = 0;

if( !isset($_SESSION['exercise']) )
{
	$exercise = new Exercise();
	
	if( is_null($exId) || !$exercise->load($exId) )
	{
		// exercise is required 
		unset($_SESSION['exercise']);
		
		header("Location: ./exercise.php");
		exit();		
	}
	else
	{
		$_SESSION['exercise'] = serialize($exercise);
	}
}
else
{
	$exercise = unserialize($_SESSION['exercise']);	
}

if( isset($_REQUEST['cmdOk']) && $_REQUEST['cmdOk'] )
{	
	$showResult = true;
	$showSubmitForm = false;
}
else
{
	$showResult = false;
	$showSubmitForm = true;
} 

//-- get question list
if( !isset($_SESSION['questionList']) || !is_array($_SESSION['questionList']) )
{ 
	if( $exercise->getShuffle() == 0 )
	{
		$questionList = $exercise->getQuestionList();
	}
	else
	{
		$questionList = $exercise->getRandomQuestionList();
	}
	
	foreach( $questionList as $question )
	{
		$questionObj = new Question();
		
		if( $questionObj->load($question['id']) )
		{
			$_SESSION['questionList'][] = serialize($questionObj);
		}
		
		unset($questionObj);
	}
}

$questionCount = count($_SESSION['questionList']);


if(!isset($_SESSION['exeStartTime']) )
{
    $_SESSION['exeStartTime'] = time();
}

//-- update step
if( isset($_REQUEST['cmdBack']) ) 	$step--;
else								$step++;

//-- exercise properties
$dialogBox = '';
$now = time();

$userAttemptCount = 1; // TODO get it dynamically

if( !$is_allowedToEdit )
{ 
	// check if exercise can be displayed
		
	if( $exercise->getStartDate() > $now 
		|| ( !is_null($exercise->getEndDate()) && $exercise->getEndDate() < $now ) 
	   )
	{
		$dialogBox .= get_lang('Exercise not available') . '<br />' . "\n";
		$showSubmitForm = false;
	}
	elseif( $exercise->getAttempts() > 0 && $userAttemptCount > $exercise->getAttempts() ) // attempt #
	{
		$dialogBox .= get_lang('You have reached the maximum number of allowed attempts.') . '<br />' . "\n";
		$showSubmitForm = false;
	}
}




/*
 * Output
 */
 
$interbredcrump[] = array ('url' => './exercise.php', 'name' => get_lang('Exercises'));

$nameTools = $exercise->getTitle();
 
include($includePath.'/claro_init_header.inc.php');

//-- title 
if( $showResult )
{
	echo claro_html_tool_title(get_lang('Exercise results') . ' : ' . $nameTools);
}
else
{
	echo claro_html_tool_title(get_lang('Exercise') . ' : ' . $nameTools);
}



//-- display properties
if( trim($exercise->getDescription()) != '' )
{
	echo '<blockquote>' . "\n" . claro_parse_user_text($exercise->getDescription()) . "\n" . '</blockquote>' . "\n";	
}

echo '<ul style="font-size:small">' . "\n";
if( $exercise->getDisplayType() == 'SEQUENTIAL' )
{
	echo '<li>' . get_lang('Current time')." : ". claro_disp_duration($now - $_SESSION['exeStartTime']) . '</li>' . "\n";	
}

if( $exercise->getTimeLimit() > 0 )
{
	echo '<li>' . get_lang('Time limit')." : ".claro_disp_duration($exercise->getTimeLimit()) . '</li>' . "\n";	
}
else
{
	echo '<li>' . get_lang('No time limitation') . '</li>' . "\n";	
}

if( isset($_uid) && isset($userAttemptCount) )
{
	echo '<li>' . get_lang('Attempt') . ' ' . $userAttemptCount;
	if( $exercise->getAttempts() > 0 )
	{
		echo ' ' . get_lang('On') . ' ' . $exercise->getAttempts();
	}
	echo '</li>' . "\n";
}

echo '<li>' . get_lang('Available from') . ' ' . claro_disp_localised_date($dateTimeFormatLong,$exercise->getStartDate());
if( !is_null($exercise->getEndDate()) )
{
	echo ' ' . get_lang('Until') . ' ' . claro_disp_localised_date($dateTimeFormatLong,$exercise->getEndDate());	
}
echo '</li>' . "\n";

echo '</ul>' .  "\n\n";
	

// admin link 
if( $is_allowedToEdit )
{
	echo '<a class="claroCmd" href="admin/edit_exercise.php?exId='.$exId.'">'
	.	 '<img src="'.$clarolineRepositoryWeb.'img/edit.gif" border="0" alt="" />'
	.	 get_lang('Modify exercise')
	.	 '</a>' . "\n";
}	
	
if( $showResult )
{
	$totalResult = 0;
    $totalGrade = 0;
    
	if( !isset($_SESSION['inPathMode']) || !$_SESSION['inPathMode'] ) 
    {
    	// Exercise mode
		echo '<form method="get" action="exercise.php">';
    }
    else
    {
    	// Learning path mode
		echo '<form method="get" action="../learnPath/navigation/backFromExercise.php">' . "\n"
		.    '<input type="hidden" name="op" value="finish" />'
		;
    }
    
    echo "\n" . '<table width="100%" border="0" cellpadding="1" cellspacing="0" class="claroTable">' . "\n\n";
    
    //-- question(s)
	if( !empty($_SESSION['questionList']) )
	{
		// foreach question
		$questionIterator = 0;
		foreach( $_SESSION['questionList'] as $serializedQuestion )
		{
			$questionIterator++;

			$question = unserialize($serializedQuestion);
			
			// required by getGrade and getQuestionFeedbackHtml
			$question->answer->extractResponseFromRequest();
			
			$questionResult = $question->answer->gradeResponse();
			$questionGrade = $question->getGrade();

			
			echo '<tr class="headerX">' . "\n"
			.	 '<th>'
			.	 get_lang('Question') . ' ' . $questionIterator
			.	 '</th>' . "\n"
			.	 '</tr>' . "\n\n";
				
			echo '<tr>'
			.	 '<td>' . "\n";
			
			echo $question->getQuestionFeedbackHtml();
						
			echo '</td>' . "\n"
			.	 '</tr>' . "\n\n"
			.	 '<tr>'
			.	 '<td align="right">' . "\n"
			.	 '<strong>'.get_lang('Score').' : '.$questionResult.'/'.$questionGrade.'</strong>'			
			.	 '</td>' . "\n"
			.	 '</tr>' . "\n\n";
			
			// sum of score
			$totalResult += $questionResult;
			$totalGrade += $questionGrade; 
		}	
	}
		
	// table footer, form footer
	echo '<tr>' . "\n"
	.	 '<td align="center">'
	.	 get_lang('Your time is %time', array('%time' => claro_disp_duration($timeToCompleteExe)) )
	.	 '<br />' . "\n"
	.	 '<strong>'.get_lang('Your total score is %score', array('%score' => $totalResult."/".$totalGrade ) ).'</strong>'
    .	 '</td>' . "\n"
	.	 '</tr>' . "\n\n" 
	.	 '<tr>' . "\n"
	.	 '<td align="center">'
	.	 '<input type="submit" value="'.get_lang('Finish').'" />'
	.	 '</td>' . "\n"
	.	 '</tr>' . "\n\n"
	.	 '</table>' . "\n\n"
	.	 '</form>' . "\n\n";
}
elseif( $showSubmitForm )
{
	//-- question(s)
	if( !empty($_SESSION['questionList']) )
	{
		// form header, table header
		echo '<form method="post" action="./exercise_submit.php?exId='.$exId.'">' . "\n";
		
		if( $exercise->getDisplayType() == 'SEQUENTIAL' )
		{
			echo '<input type="hidden" name="step" value="'.$step.'" />' . "\n";
		}
	
		echo "\n" . '<table width="100%" border="0" cellpadding="1" cellspacing="0" class="claroTable">' . "\n\n";

		// foreach question
		$questionIterator = 0;
		foreach( $_SESSION['questionList'] as $serializedQuestion )
		{
			$questionIterator++;

			$question = unserialize($serializedQuestion);

			if( $exercise->getDisplayType() == 'SEQUENTIAL' )
			{	
				// get response if something has already been sent			
				$question->answer->extractResponseFromRequest();
				
				if( $step != $questionIterator )
				{
					// only echo hidden form field
					echo $question->answer->getHiddenAnswerHtml();
				}
				else
				{
					echo '<tr class="headerX">' . "\n"
					.	 '<th>'
					.	 get_lang('Question') . ' ' . $questionIterator
					.	 ' / '.$questionCount
					.	 '</th>' . "\n"
					.	 '</tr>' . "\n\n";
					
					echo '<tr>'
					.	 '<td>' . "\n"					
					
					.	 $question->getQuestionAnswerHtml()
								
					.	 '</td>' . "\n"
					.	 '</tr>' . "\n\n";
				}
			}
			else // all questions on on page
			{
				echo '<tr class="headerX">' . "\n"
				.	 '<th>'
				.	 get_lang('Question') . ' ' . $questionIterator
				.	 '</th>' . "\n"
				.	 '</tr>' . "\n\n";
				
				echo '<tr>'
				.	 '<td>' . "\n"					
				
				.	 $question->getQuestionAnswerHtml()
							
				.	 '</td>' . "\n"
				.	 '</tr>' . "\n\n";
			}
			
		} 
		// table footer, form footer
		echo '<tr>' . "\n"
		.	 '<td align="center">';
		
		if( $exercise->getDisplayType() == 'SEQUENTIAL' )
		{
			if( $step > 1 )
			{
				echo '<input type="submit" name="cmdBack" value="&lt; '.get_lang('Back').'" />&nbsp;' . "\n";
			}
			
			if( $step < $questionCount )
			{
				echo '<input type="submit" name="cmdNext" value="'.get_lang('Next').' &gt;" />' . "\n";
			}
			else
			{
				echo '<input type="submit" name="cmdOk" value="'.get_lang('Ok').'" />' . "\n";	
			}
		}
		else
		{
			echo '<input type="submit" name="cmdOk" value="'.get_lang('Ok').'" />' . "\n";
		} 
		
		echo '</td>' . "\n"
		.	 '</tr>' . "\n\n"
		.	 '</table>' . "\n\n"
		.	 '</form>' . "\n\n";
		
	}
}
else // ! $showSubmitForm
{
	if( !isset($_SESSION['inPathMode']) || !$_SESSION['inPathMode'] )
	{
		$dialogBox .= '<br /><a href="./exercise.php">&lt;&lt; '.get_lang('Back').'</a><br />' . "\n";
	}
	if( !empty($dialogBox) ) echo claro_html_message_box($dialogBox);
	
}

include($includePath.'/claro_init_footer.inc.php');
?>