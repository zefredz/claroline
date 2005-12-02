<?php // $Id$
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.6
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2004 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
      |   This program is free software; you can redistribute it and/or      |
      |   modify it under the terms of the GNU General Public License        |
      |   as published by the Free Software Foundation; either version 2     |
      |   of the License, or (at your option) any later version.             |
      +----------------------------------------------------------------------+
      | Authors: Olivier Brouckaert <oli.brouckaert@skynet.be>               |
      | Changed by Guillaume Lederer <lederer@cerdecam.be>                   |
      |              Sébastien Piraux <piraux@cerdecam.be>                   |
      +----------------------------------------------------------------------+
*/

		/*>>>>>>>>>>>>>>>>>>>> EXERCISE RESULT <<<<<<<<<<<<<<<<<<<<*/

/**
 * This script gets informations from the script "exercise_submit.php",
 * through the session, and calculates the score of the student for
 * that exercise.
 *
 * Then it shows results at screen.
 */
include('exercise.class.php');
include('question.class.php');
include('answer.class.php');

include('exercise.lib.php');

// answer types
define('UNIQUE_ANSWER',	 1);
define('MULTIPLE_ANSWER',2);
define('FILL_IN_BLANKS', 3);
define('MATCHING',		 4);
define('TRUEFALSE',	 5);

// for help display in fill in blanks questions
define('TEXTFIELD_FILL', 1);
define('LISTBOX_FILL',	2);

require '../inc/claro_init_global.inc.php';

if ( !$_cid || !$is_courseAllowed ) claro_disp_auth_form(true);

/*
 * paths definition
 */
$attachedFilePathWeb = $coursesRepositoryWeb.$_course['path'].'/exercise';
$attachedFilePathSys = $coursesRepositorySys.$_course['path'].'/exercise';
/*
 * DB tables definition
 */
$tbl_cdb_names = claro_sql_get_course_tbl();
$tbl_lp_learnPath            = $tbl_cdb_names['lp_learnPath'           ];
$tbl_lp_rel_learnPath_module = $tbl_cdb_names['lp_rel_learnPath_module'];
$tbl_lp_user_module_progress = $tbl_cdb_names['lp_user_module_progress'];
$tbl_lp_module               = $tbl_cdb_names['lp_module'              ];
$tbl_lp_asset                = $tbl_cdb_names['lp_asset'               ];
$tbl_quiz_answer             = $tbl_cdb_names['quiz_answer'            ];
$tbl_quiz_question           = $tbl_cdb_names['quiz_question'          ];
$tbl_quiz_rel_test_question  = $tbl_cdb_names['quiz_rel_test_question' ];
$tbl_quiz_test               = $tbl_cdb_names['quiz_test'              ];
$tbl_track_e_exercises		 = $tbl_cdb_names['track_e_exercices'];

if( isset($_SESSION['exerciseResult']) )   	$exerciseResult = $_SESSION['exerciseResult'];
elseif( isset($_REQUEST['exerciseResult']) )   $exerciseResult = $_REQUEST['exerciseResult'];

if( isset($_SESSION['questionList']) )   	$questionList = $_SESSION['questionList'];
elseif( isset($_REQUEST['questionList']) )   $questionList = $_REQUEST['questionList'];

// if the above variables are empty or incorrect, stops the script
if( !isset($exerciseResult) || !is_array($exerciseResult)
	|| !isset($questionList) || !is_array($questionList)
	|| !is_object($_SESSION['objExercise'])
	)
{
        include ($includePath.'/claro_init_header.inc.php');
		echo '<br />'.claro_disp_message_box(get_lang('ExerciseNotFound')).'<br />';
        include ($includePath.'/claro_init_footer.inc.php');
        die();
}

$exerciseTitle		= $_SESSION['objExercise']->selectTitle();
$showAnswers 		= $_SESSION['objExercise']->get_show_answer();
$exerciseMaxTime 	= $_SESSION['objExercise']->get_max_time();
$exerciseMaxAttempt	= $_SESSION['objExercise']->get_max_attempt();

$nameTools = $_SESSION['objExercise']->exercise;

// calculate time needed to complete the exercise
if (isset($_SESSION['exeStartTime']))
{
	$timeToCompleteExe =  time() - $_SESSION['exeStartTime'];
}

// deal with the learning path mode

if( isset($_SESSION['inPathMode']) && $_SESSION['inPathMode'] )          // learning path mode
{
	include($includePath."/lib/learnPath.lib.inc.php");
	$is_allowedToEdit = false; // do not allow to be in admin mode during a path progression
	// need to include the learningPath langfile for the added interbredcrump
	// echo minimal html page header so that the page is valid
	// don't display banner from init_header
	$hide_banner = true;
}
else                                        // normal exercise mode
{
	$is_allowedToEdit = true; // allow to be in admin mode
	$interbredcrump[] = array("url" => "exercice.php","name" => get_lang('Exercices'));
}
include($includePath.'/claro_init_header.inc.php');

echo claro_disp_tool_title( htmlspecialchars($exerciseTitle)." : ".get_lang('Result') );

    if( !isset($_SESSION['inPathMode']) || !$_SESSION['inPathMode'] ) // exercise mode
    {
            echo "<form method=\"get\" action=\"exercice.php\">";
    }
    else    // Learning path mode
    {
            echo "<form method=\"get\" action=\"../learnPath/navigation/backFromExercise.php\">\n
            <input type=\"hidden\" name=\"op\" value=\"finish\">";
    }

	$i = 0;
	$totalScore = 0;
	$totalWeighting = 0;
	
	// check if max allowed time has been respected 
	if ( $exerciseMaxTime > 0 && $exerciseMaxTime < $timeToCompleteExe )  
	{
		$displayAnswers 	= false;
		$displayScore 		= false;
	}
	else
	{
		$displayScore = true;
		
		// check if answers have to be shown
		// count number of attempts of the user 
		$sql="SELECT count(`exe_result`) AS `tryQty`
		        FROM `".$tbl_track_e_exercises."`
		       WHERE `exe_user_id` = '".(int)$_uid."'
		         AND `exe_exo_id` = ".(int)$_SESSION['objExercise']->selectId()."
		       GROUP BY `exe_user_id`";
		$result = claro_sql_query_fetch_all($sql);

		if( isset($result[0]['tryQty']) )	$userTryQty = $result[0]['tryQty']+1;
		else                                $userTryQty = 1; // first try
		
		if ( $showAnswers == 'ALWAYS' )
		{
			$displayAnswers = true;
		}
		elseif ( $showAnswers == 'LASTTRY' && $exerciseMaxAttempt == $userTryQty )
		{
			$displayAnswers = true;
		}
		else
		{
			// $showAnswers == 'NEVER'
			$displayAnswers = false;
		}
	} // end else of if ($timeToCompleteExe ...

	// for each question
	foreach($questionList as $questionId)
	{
		// gets the student choice for this question
		if( isset($exerciseResult[$questionId]) )
			$choice = $exerciseResult[$questionId];
		else
      		$choice = '';

		// creates a temporary Question object
		$objQuestionTmp = new Question();

		$objQuestionTmp->read($questionId);

		$questionTitle = $objQuestionTmp->selectTitle();
		$questionStatement = $objQuestionTmp->selectDescription();
		$attachedFile = $objQuestionTmp->selectAttachedFile();
		$questionWeighting = $objQuestionTmp->selectWeighting();
		$answerType = $objQuestionTmp->selectType();

		// destruction of the Question object
		unset($objQuestionTmp);

		if($answerType == UNIQUE_ANSWER || $answerType == MULTIPLE_ANSWER || $answerType == TRUEFALSE)
		{
			$colspan = 4;
		}
		elseif($answerType == MATCHING)
		{
			$colspan = 2;
		}
		else
		{
			$colspan = 1;
		}
		
		if($displayAnswers)
		{
?>

  <table width="100%" cellpadding="4" cellspacing="2" border="0" class="claroTable">
  <tr class="headerX">
  <th colspan="<?php echo $colspan; ?>">
	<?php echo get_lang('Question').' '.($i+1); ?>
  </th>
</tr>
<tfoot>
<tr>
  <td colspan="<?php echo $colspan; ?>">
	<?php echo $questionTitle; ?>	
	<blockquote>
	<?php 
		echo $questionStatement; 
		
		if( !empty($attachedFile) )
		{ 
			echo "<br />".display_attached_file($attachedFile);
		}
	?>
	</blockquote>
  </td>
</tr>

<?php
			if($answerType == UNIQUE_ANSWER || $answerType == MULTIPLE_ANSWER || $answerType == TRUEFALSE)
			{
?>

<tr>
  <td width="5%" valign="top" align="center" nowrap="nowrap">
	<small><i><?php echo get_lang('Choice'); ?></i></small>
  </td>
  <td width="5%" valign="top" nowrap="nowrap">
	<small><i><?php echo get_lang('ExpectedChoice'); ?></i></small>
  </td>
  <td width="45%" valign="top">
	<small><i><?php echo get_lang('Answer'); ?></i></small>
  </td>
  <td width="45%" valign="top">
	<small><i><?php echo get_lang('Comment'); ?></i></small>
  </td>
</tr>

<?php
			}
			elseif($answerType == FILL_IN_BLANKS)
			{
?>

<tr>
  <td>
	<small><i><?php echo get_lang('Answer'); ?></i></small>
  </td>
</tr>

<?php
			}
			else
			{
?>

<tr>
  <td width="50%">
	<small><i><?php echo get_lang('ElementList'); ?></i></small>
  </td>
  <td width="50%">
	<small><i><?php echo get_lang('CorrespondsTo'); ?></i></small>
  </td>
</tr>

<?php
			}
		} // end if ($displayAnswers)
		// construction of the Answer object
		$objAnswerTmp = new Answer($questionId);

		$nbrAnswers = $objAnswerTmp->selectNbrAnswers();

		$questionScore = 0;

        $valueToTrack = array();
        
		for($answerId = 1;$answerId <= $nbrAnswers;$answerId++)
		{
			// $answerId is NOT a unique id of the answer but a
			// unique id of the answer INSIDE the question
			// unique id of an answer in table is composed of id in question and id of the question
			$answer = $objAnswerTmp->selectAnswer($answerId);
			$answerComment = $objAnswerTmp->selectComment($answerId);
			$answerCorrect = $objAnswerTmp->isCorrect($answerId);
			$answerWeighting = $objAnswerTmp->selectWeighting($answerId);

			$studentChoice = ''; // init to empty string, will be overwritten when a answer has been given

			switch($answerType)
			{
     			// for unique answer or true/false (true/false IS a unique answer exercise)
				case TRUEFALSE : // no break, execute UNIQUE_ANSWER instructions
				case UNIQUE_ANSWER :	$studentChoice = ($choice == $answerId)?1:0;

										if($studentChoice)
										{
											// if this answer has been selected by the user
										  	$questionScore += $answerWeighting;
											$totalScore += $answerWeighting;
											// add answer in the value used for question tracking
											$valueToTrack[] = $choice;
										}
										break;
				// for multiple answers
				case MULTIPLE_ANSWER :	if( isset($choice[$answerId]) ) $studentChoice = $choice[$answerId];

										if($studentChoice)
										{
											$questionScore += $answerWeighting;
											$totalScore += $answerWeighting;
											// add all selected answers in the value used for question tracking
											$valueToTrack[] = $answerId;
										}

										break;
				// for fill in the blanks
				case FILL_IN_BLANKS :	// splits text and weightings that are joined with the character '::'

							            $explodedAnswer = explode( '::',$answer);
							            $answer = (isset($explodedAnswer[0]))?$explodedAnswer[0]:'';
							            $answerWeighting = (isset($explodedAnswer[1]))?$explodedAnswer[1]:'';
							            $fillType = (!empty($explodedAnswer[2]))?$explodedAnswer[2]:1;
							            // default value if value is invalid
							            if( $fillType != TEXTFIELD_FILL && $fillType != LISTBOX_FILL )  $fillType = TEXTFIELD_FILL;
							            
										// splits weightings that are joined with a comma
										$answerWeighting = explode(',',$answerWeighting);

										// we save the answer because it will be modified
										$temp = $answer;

										$answer = '';

										$j = 0;

										// the loop will stop at the end of the text
										while(1)
										{
											// quits the loop if there are no more blanks
											if(($pos = strpos($temp,'[')) === false)
											{
												// adds the end of the text
												$answer .= $temp;
												break;
											}

											// adds the piece of text that is before the blank and ended by [
											$answer .= substr($temp,0,$pos+1);

											$temp = substr($temp,$pos+1);

											// quits the loop if there are no more blanks
											if(($pos = strpos($temp,']')) === false)
											{
												// adds the end of the text
												$answer .= $temp;
												break;
											}

											// [ and ] are prohibited in answers because of the [word] system in
											// statement creation
											$charsToAvoid = array("[","]");

		   									if( isset($choice[$j]) )
												$choice[$j] = trim(htmlspecialchars(str_replace($charsToAvoid, "", $choice[$j])));
											else
											    $choice[$j] = '';

											// check answer validity
											if( $fillType == LISTBOX_FILL )
											{
												// case sensitive check when select box are used
                                                $answerIsCorrect = substr($temp,0,$pos) == $choice[$j];
											}
											else
											{
												// case insensitive check when text box are used
												$answerIsCorrect = strtolower(substr($temp,0,$pos)) == strtolower($choice[$j]);
											}
											// if the word entered by the student IS the same as the one defined by the professor
											if( $answerIsCorrect )
											{
												// gives the related weighting to the student
												$questionScore += $answerWeighting[$j];

												// increments total score
												$totalScore += $answerWeighting[$j];

												// adds the word in green at the end of the string
												$answer .= $choice[$j];
												
												$valueToTrack[] = $choice[$j];
											}
											// else if the word entered by the student IS NOT the same as the one defined by the professor
											elseif(!empty($choice[$j]))
											{
												// adds the word in red at the end of the string, and strikes it
												$answer .= "<span class=\"error\"><s>".$choice[$j]."</s></span>";

												$valueToTrack[] = $choice[$j];
											}
											else
											{
												// adds a tabulation if no word has been typed by the student
												$answer .= '&nbsp;&nbsp;&nbsp;';
												$valueToTrack[] = '';
											}

											// adds the correct word, followed by ] to close the blank
											$answer .= " / <span class=\"correct\"><b>".substr($temp,0,$pos)."</b></span>]";

											$j++;

											$temp = substr($temp,$pos+1);
										}

										break;
				// for matching
				case MATCHING :         // in matching when $answerCorrect is true ( != 0 )
										// it means that the answer is a LEFT column proposal
          								if($answerCorrect)
										{
											if( !empty($choice[$answerId]) && $answerCorrect == $choice[$answerId] )
											{
												// answer is correct
            									// add answer in the value used for question tracking
            						            $valueToTrack[] = $answerId.'-'.$choice[$answerId];
												$questionScore += $answerWeighting;
												$totalScore += $answerWeighting;
												$choice[$answerId] = $matching[$choice[$answerId]];
            								}
											elseif(empty($choice[$answerId]))
											{
												// no answer given
												$choice[$answerId] = '&nbsp;&nbsp;&nbsp;';
												// add answer in the value used for question tracking
												$valueToTrack[] = $answerId.'-';
											}
											elseif( !empty($choice[$answerId]) && isset($matching[$choice[$answerId]])  )
											{
												// answer is incorrect
                                                // add answer in the value used for question tracking
                                                $valueToTrack[] = $answerId.'-'.$choice[$answerId];
												$choice[$answerId] = "<span class=\"error\"><s>".$matching[$choice[$answerId]]."</s></span>";
											}
										}
										else
										{
											// in matching $answerCorrect == 0 it means that the answer is a
											// right column proposal
   											$matching[$answerId] = $answer;
										}
										break;
			}	// end switch()

			if( ($answerType != MATCHING || $answerCorrect) && $displayAnswers)
			{
				if($answerType == UNIQUE_ANSWER || $answerType == MULTIPLE_ANSWER || $answerType == TRUEFALSE)
				{
?>

<tr>
  <td width="5%" align="center">
	<img src="<?php echo $imgRepositoryWeb ?><?php echo ($answerType != MULTIPLE_ANSWER)?'radio':'checkbox'; echo $studentChoice?'_on':'_off'; ?>.gif" border="0">
  </td>
  <td width="5%" align="center">
	<img src="<?php echo $imgRepositoryWeb ?><?php echo ($answerType != MULTIPLE_ANSWER)?'radio':'checkbox'; echo $answerCorrect?'_on':'_off'; ?>.gif" border="0">
  </td>
  <td width="45%">
	<?php echo $answer; ?>
  </td>
  <td width="45%">
	<?php if($studentChoice) echo claro_parse_user_text($answerComment); else echo '&nbsp;'; ?>
  </td>
</tr>

<?php
				}
				elseif($answerType == FILL_IN_BLANKS)
				{
?>

<tr>
  <td>
	<?php echo claro_parse_user_text($answer); ?>
  </td>
</tr>

<?php
				}
				else
				{
?>

<tr>
  <td width="50%">
	<?php echo $answer; ?>
  </td>
  <td width="50%">
	<?php echo $choice[$answerId]; ?> / <span class="correct"><b><?php echo $matching[$answerCorrect]; ?></b></span>
  </td>
</tr>

<?php
				}
			} // end of if( ($answerType != MATCHING || $answerCorrect) && $displayAnswers)
		}	// end for()

		if($displayAnswers)
		{
?>
<tr>
  <td colspan="<?php echo $colspan; ?>" align="right">
	<b><?php echo get_lang('Score')." : ".$questionScore."/".$questionWeighting; ?></b>
  </td>
</tr>
</tfoot>
</table>

<?php
		}
		// destruction of Answer
		unset($objAnswerTmp);

        $answersToTrack[$i]['questionId'] = $questionId;
        $answersToTrack[$i]['values'] = $valueToTrack;
        $answersToTrack[$i]['questionResult'] = $questionScore;
		$i++;

		$totalWeighting+=$questionWeighting;
	}	// end foreach()
?>

<table width="100%" border="0" cellpadding="3" cellspacing="2">
<tr>
  <td align="center">
	<?php 
		echo get_lang('YourTime')." ".claro_disp_duration($timeToCompleteExe);
		if( $exerciseMaxTime > 0 )
		{
			echo "<br />".get_lang('MaxAllowedTime')." ".claro_disp_duration($exerciseMaxTime);
		}
	?>
  </td>
</tr>
<tr>
  <td align="center">
	<b>
<?php 
	if ( $displayScore )
	{
		echo get_lang('YourTotalScore')." ". $totalScore."/".$totalWeighting;
	}
	else
	{
		echo get_lang('TimeOver');
	}
?>
</b>
  </td>

</tr>
<tr>
<tr>
  <td align="center">
    <br>
	<input type="submit" value="<?php echo get_lang('Finish'); ?>">
  </td>
</tr>
</table>

</form>

<br>

<?php

/*******************************/
/* Tracking of results         */
/*******************************/

// if tracking is enabled
if($is_trackingEnabled && $displayScore)
{
    // if anonymousAttemps is true : record anonymous user stats, record authentified user stats without uid
    if ( $_SESSION['objExercise']->anonymous_attempts()  )
    {
        $exerciseTrackId = event_exercice($_SESSION['objExercise']->selectId(),$totalScore,$totalWeighting,$timeToCompleteExe );
    }
    elseif( $_uid ) // anonymous attempts not allowed, record stats with uid only if uid is set
    {
		$exerciseTrackId = event_exercice($_SESSION['objExercise']->selectId(),$totalScore,$totalWeighting,$timeToCompleteExe, $_uid );
    }

	if( isset($exerciseTrackId) && $exerciseTrackId && !empty($answersToTrack) )
	{
  		foreach( $answersToTrack as $answerToTrack )
  		{
            event_exercise_details($exerciseTrackId,$answerToTrack['questionId'],$answerToTrack['values'],$answerToTrack['questionResult']);
		}
	}
}

// record progression 
if( isset($_SESSION['inPathMode']) && $_SESSION['inPathMode'] && $displayScore ) // learning path mode
{
    // update raw in DB to keep the best one, so update only if new raw is better  AND if user NOT anonymous

    if($_uid)
    {
        // exercices can have a negative score, we don't accept that in LP
		// so if totalScore is negative use 0 as result
		$totalScore = max($totalScore, 0);
        if ( $totalWeighting != 0 )
        {
                $newRaw = @round($totalScore/$totalWeighting*100);
        }
        else
        {
                $newRaw = 0;
        }

        $scoreMin = 0;
        $scoreMax = $totalWeighting;
        // need learningPath_module_id and raw_to_pass value
        $sql = "SELECT LPM.`raw_to_pass`, LPM.`learnPath_module_id`, UMP.`total_time`, UMP.`raw`
                  FROM `".$tbl_lp_rel_learnPath_module."` AS LPM, `".$tbl_lp_user_module_progress."` AS UMP
                 WHERE LPM.`learnPath_id` = '".(int)$_SESSION['path_id']."'
                   AND LPM.`module_id` = '".(int)$_SESSION['module_id']."'
				   AND LPM.`learnPath_module_id` = UMP.`learnPath_module_id`
				   AND UMP.`user_id` = ".(int)$_uid;
        $query = claro_sql_query($sql);
        $row = mysql_fetch_array($query);

		$scormSessionTime = seconds_to_scorm_time($timeToCompleteExe);
        
		// build sql query
		$sql = "UPDATE `".$tbl_lp_user_module_progress."` SET ";
		// if recorded score is less then the new score => update raw, credit and status

		if ($row['raw'] < $totalScore)
		{ 
			// update raw
			$sql .= "`raw` = $totalScore,";
			// update credit and statut if needed ( score is better than raw_to_pass )
			if ( $newRaw >= $row['raw_to_pass'])
			{
				$sql .= "	`credit` = 'CREDIT',
					 		`lesson_status` = 'PASSED',";
			}
			else // minimum raw to pass needed to get credit 
			{
				$sql .= "	`credit` = 'NO-CREDIT',
							`lesson_status` = 'FAILED',";
			}
		}// else don't change raw, credit and lesson_status

		// default query statements
		$sql .= "	`scoreMin` 		= " . (int)$scoreMin . ",
					`scoreMax` 		= " . (int)$scoreMax . ",
					`total_time`	= '".addScormTime($row['total_time'], $scormSessionTime)."',
					`session_time`	= '".$scormSessionTime."'
                 WHERE `learnPath_module_id` = ". (int)$row['learnPath_module_id']."
                   AND `user_id` = " . (int)$_uid . "";
	    claro_sql_query($sql);
    }

}

if( isset($_SESSION['inPathMode']) && $_SESSION['inPathMode'] )
{
	// display minimal html footer
	$hide_footer = true;
	// clean exercise session vars only if in learning path mode
	// because I don't know why the original author of exercise tool did not unset these here
	unset($_SESSION['exerciseResult']);
	unset($_SESSION['questionList']);
}
include($includePath.'/claro_init_footer.inc.php');
?>
