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
 
require '../inc/claro_init_global.inc.php';

if ( !$_cid || !$is_courseAllowed ) claro_disp_auth_form(true);

claro_set_display_mode_available(false);

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

$tbl_cdb_names = claro_sql_get_course_tbl();
$tbl_track_e_exercises         = $tbl_cdb_names['track_e_exercices'];

if( isset($_SESSION['inPathMode']) && $_SESSION['inPathMode'] )
{
	require_once $includePath . '/lib/learnPath.lib.inc.php';
	
	$tbl_lp_learnPath            = $tbl_cdb_names['lp_learnPath'           ];
	$tbl_lp_rel_learnPath_module = $tbl_cdb_names['lp_rel_learnPath_module'];
	$tbl_lp_user_module_progress = $tbl_cdb_names['lp_user_module_progress'];
	$tbl_lp_module               = $tbl_cdb_names['lp_module'              ];
	$tbl_lp_asset                = $tbl_cdb_names['lp_asset'               ];
	
	$hide_banner = true;
	$hide_footer = true;	
}


/*
 * Execute commands
 */
if( isset($_REQUEST['exId']) && is_numeric($_REQUEST['exId']) ) $exId = (int) $_REQUEST['exId'];
else															$exId = null;

if( isset($_REQUEST['step']) && is_numeric($_REQUEST['step']) ) $step = (int) $_REQUEST['step'];
else															$step = 0;

if( !isset($_SESSION['serializedExercise']) )
{
	$exercise = new Exercise();
	
	if( is_null($exId) || !$exercise->load($exId) )
	{
		// exercise is required 
		unset($_SESSION['serializedExercise']);
		
		header("Location: ./exercise.php");
		exit();		
	}
	else
	{
		// load successfull
		// exercise must be visible or in learning path to be displayed to a student
		if( $exercise->getVisibility() != 'VISIBLE' && !$is_allowedToEdit && ( ! isset($_SESSION['inPathMode']) || ! $_SESSION['inPathMode'] ) )
		{
			// exercise is required 
			unset($_SESSION['serializedExercise']);
			
			header("Location: ./exercise.php");
			exit();		
		}
		else
		{
			$_SESSION['serializedExercise'] = serialize($exercise);
		}
	}
}
else
{
	$exercise = unserialize($_SESSION['serializedExercise']);
}

//-- get question list
if( !isset($_SESSION['serializedQuestionList']) || !is_array($_SESSION['serializedQuestionList']) )
{ 
	if( $exercise->getShuffle() == 0 )
	{
		$qList = $exercise->getQuestionList();
	}
	else
	{
		$qList = $exercise->getRandomQuestionList();
	}
	
	$questionList = array();
	$_SESSION['serializedQuestionList'] = array();
	// get all question objects and store them serialized in session
	foreach( $qList as $question )
	{
		$questionObj = new Question();
		
		if( $questionObj->load($question['id']) )
		{
			$_SESSION['serializedQuestionList'][] = serialize($questionObj);
			$questionList[] = $questionObj;
		}
		unset($questionObj);
	}
}
else
{
	$questionList = array();
	foreach( $_SESSION['serializedQuestionList'] as $serializedQuestion )
	{
		$questionList[] = unserialize($serializedQuestion);
	}
}

$questionCount = count($questionList);


//-- exercise properties
$dialogBox = '';
$now = time();

if( $_uid )
{
	// count number of attempts of the user
	$sql="SELECT count(`exe_result`) AS `tryQty`
	        FROM `".$tbl_track_e_exercises."`
	       WHERE `exe_user_id` = '".(int) $_uid."'
	         AND `exe_exo_id` = ".(int) $exId."
	       GROUP BY `exe_user_id`";
	
	$userAttemptCount = claro_sql_query_get_single_value($sql);
	
	if( $userAttemptCount )	$userAttemptCount++;
	else                 	$userAttemptCount = 1; // first try
}
else
{
	$userAttemptCount = 1;
}


$exerciseIsAvailable = true;

if( !$is_allowedToEdit )
{ 
	// do the checks only if user has no edit right
	
	// check if exercise can be displayed
	if( $exercise->getStartDate() > $now 
		|| ( !is_null($exercise->getEndDate()) && $exercise->getEndDate() < $now )
	   )
	{
		// not yet available, no more available
		$dialogBox .= get_lang('Exercise not available') . '<br />' . "\n";
		$exerciseIsAvailable = false;
	}
	elseif( $exercise->getAttempts() > 0 && $userAttemptCount > $exercise->getAttempts() ) // attempt #
	{
		$dialogBox .= get_lang('You have reached the maximum number of allowed attempts.') . '<br />' . "\n";
		$exerciseIsAvailable = false;
	}
}


if(!isset($_SESSION['exeStartTime']) )
{
    $_SESSION['exeStartTime'] = $now;
}

// exercise is submitted - GRADE EXERCISE
if( isset($_REQUEST['cmdOk']) && $_REQUEST['cmdOk'] && $exerciseIsAvailable )
{	
	$timeToCompleteExe =  $now - $_SESSION['exeStartTime'];
	$recordResults = true;
	
    // the time limit is set and the user take too much time to complete exercice
	if ( $exercise->getTimeLimit() > 0 && $exercise->getTimeLimit() < $timeToCompleteExe )
	{
	    $showAnswers = false;
	    $recordResults = false;
	}
	else
	{	
	    if ( $exercise->getShowAnswers()  == 'ALWAYS' )
	    {
	        $showAnswers = true;
	    }
	    elseif ( $exercise->getShowAnswers() == 'LASTTRY' && $exercise->getAttempts() == $userAttemptCount )
	    {
	        $showAnswers = true;
	    }
	    else
	    {
	        // $exercise->getShowAnswers()  == 'NEVER'
	        $showAnswers = false;
	    }
	}
		 
	$showResult = true;
	$showSubmitForm = false;
	
	// compute scores
	$totalResult = 0;
    $totalGrade = 0;
    
    for( $i = 0 ; $i < count($questionList); $i++)
	{
		// required by getGrade and getQuestionFeedbackHtml
		$questionList[$i]->answer->extractResponseFromRequest();
		
		$questionResult[$i] = $questionList[$i]->answer->gradeResponse();
		$questionGrade[$i] = $questionList[$i]->getGrade();			
		
		// sum of score
		$totalResult += $questionResult[$i];
		$totalGrade += $questionGrade[$i];
	}
	
	// if tracking is enabled
	if( $is_trackingEnabled )
	{
	    // if anonymous attempts are authorised : record anonymous user stats, record authentified user stats without uid
	    if ( $exercise->getAnonymousAttempts() == 'ALLOWED' )
	    {
	        $exerciseTrackId = event_exercice($exId,$totalResult,$totalGrade,$timeToCompleteExe );
	    }
	    elseif( $_uid ) // anonymous attempts not allowed, record stats with uid only if uid is set
	    {
	        $exerciseTrackId = event_exercice($exId,$totalResult,$totalGrade,$timeToCompleteExe, $_uid );
	    }

	    if( isset($exerciseTrackId) && $exerciseTrackId && !empty($questionList) )
	    {
	    	$i = 0;
			foreach ( $questionList as $question )
			{	
				event_exercise_details($exerciseTrackId,$question->getId(),$question->answer->getTrackingValues(),$questionResult[$i]);
				$i++;
			}
	    }
	}
	
	if( isset($_SESSION['inPathMode']) && $_SESSION['inPathMode'] )
	{
		set_learning_path_progression($totalResult,$totalGrade,$timeToCompleteExe,$_uid);
	}
}
else
{
	$showResult = false;
	$showSubmitForm = true;
} 
//-- update step
if( isset($_REQUEST['cmdBack']) ) 	$step--;
else								$step++;






/*
 * Output
 */

$interbredcrump[] = array("url" => "exercise.php","name" => get_lang('Exercises'));

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
	
if( $showResult )
{
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
	if( !empty($questionList) )
	{
		// foreach question
		$questionIterator = 1;
		$i = 0;

	    foreach( $questionList as $question )
		{
			if( $showAnswers )
			{
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
				.	 '<strong>'.get_lang('Score').' : '.$questionResult[$i].'/'.$questionGrade[$i].'</strong>'			
				.	 '</td>' . "\n"
				.	 '</tr>' . "\n\n";
			}
			$questionIterator++;
			$i++;
		}	
	}
		
	// table footer, form footer
	echo '<tr>' . "\n"
	.	 '<td align="center">'
	.	 get_lang('Your time is %time', array('%time' => claro_disp_duration($timeToCompleteExe)) )
	.	 '<br />' . "\n"
	.	 '<strong>';
	
	if( $recordResults )
	{
		echo get_lang('Your total score is %score', array('%score' => $totalResult."/".$totalGrade ) );
	}
	else
	{
		echo get_lang('Time is over, results not submitted.');
	}
    
    echo '</strong>'
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
	if( !empty($questionList) )
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

		foreach( $questionList as $question )
		{
			$questionIterator++;

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
				echo '<input type="submit" name="cmdBack" value="&lt; '.get_lang('Previous question').'" />&nbsp;' . "\n";
			}
			
			if( $step < $questionCount )
			{
				echo '<input type="submit" name="cmdNext" value="'.get_lang('Next question').' &gt;" />' . "\n";
			}
			
			echo '<p><input type="submit" name="cmdOk" value="'.get_lang('Submit all and finish').'" /></p>' . "\n";
		}
		else
		{
			echo '<input type="submit" name="cmdOk" value="'.get_lang('Finish the test').'" />' . "\n";
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
