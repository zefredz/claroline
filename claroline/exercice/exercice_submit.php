<?php // $Id$
/*
+----------------------------------------------------------------------+
| CLAROLINE 1.6
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
      |            Sébastien Piraux <piraux@cerdecam.be>                     |
      +----------------------------------------------------------------------+
*/

		/*>>>>>>>>>>>>>>>>>>>> EXERCISE SUBMISSION <<<<<<<<<<<<<<<<<<<<*/

/**
 * This script allows to run an exercise. According to the exercise type, questions
 * can be on an unique page, or one per page with a Next button.
 *
 * One exercise may contain different types of answers (unique or multiple selection,
 * matching and fill in blanks).
 *
 * Questions are selected randomly or not.
 *
 * When the user has answered all questions and clicks on the button "Ok",
 * it goes to exercise_result.php
 *
 * Notice : This script is also used to show a question before modifying it by
 * the administrator
 */

include('exercise.class.php');
include('question.class.php');
include('answer.class.php');

include('exercise.lib.php');

// answer types
define('UNIQUE_ANSWER',	1);
define('MULTIPLE_ANSWER',	2);
define('FILL_IN_BLANKS',	3);
define('MATCHING',		4);

require '../inc/claro_init_global.inc.php';


$attachedFilePathWeb = $coursesRepositoryWeb.$_course['path'].'/exercise';
$attachedFilePathSys = $coursesRepositorySys.$_course['path'].'/exercise';



$is_allowedToEdit = $is_courseAdmin;

$tbl_cdb_names = claro_sql_get_course_tbl();
$tbl_quiz_answer             = $tbl_cdb_names['quiz_answer'            ];
$tbl_quiz_question           = $tbl_cdb_names['quiz_question'          ];
$tbl_quiz_rel_test_question  = $tbl_cdb_names['quiz_rel_test_question' ];
$tbl_quiz_test               = $tbl_cdb_names['quiz_test'              ];

$TBL_EXERCICE_QUESTION = $tbl_quiz_rel_test_question;
$TBL_EXERCICES         = $tbl_quiz_test;
$TBL_QUESTIONS         = $tbl_quiz_question;
$TBL_REPONSES          = $tbl_quiz_answer;

$TBL_TRACK_EXERCISES    = $tbl_cdb_names['track_e_exercices'];

// deal with the learning path mode
if ($_SESSION['inPathMode'] == true)
{
     $is_allowedToEdit = false; // do not allow to be in admin mode during a path progression

    if($buttonCancel)
    {
        // returns to the module presentation page
        $backUrl = $clarolineRepositoryWeb."learnPath/navigation/backFromExercise.php?op=cancel";
        header('Location: '.$backUrl);
        exit();
    }
}
else
{    // if the user has clicked on the "Cancel" button
    if($buttonCancel)
    {
        // returns to the exercise list
        header('Location: exercice.php');
        exit();
    }
}


// if the user has submitted the form
if($formSent)
{
	// initializing
	if(!is_array($exerciseResult))
	{
		$exerciseResult=array();
	}

	// if the user has answered at least one question
	if(is_array($choice))
	{
		if($exerciseType == 1)
		{
			// $exerciseResult receives the content of the form.
			// Each choice of the student is stored into the array $choice
			$exerciseResult=$choice;
		}
		else
		{
			// gets the question ID from $choice. It is the key of the array
			list($key)=array_keys($choice);

			// if the user didn't already answer this question
			if(!isset($exerciseResult[$key]))
			{
				// stores the user answer into the array
				$exerciseResult[$key]=$choice[$key];
			}
		}
	}

	// the script "exercise_result.php" will take the variable $exerciseResult from the session
	if (!isset($_SESSION['exerciseResult']) )
    $_SESSION['exerciseResult'] =$exerciseResult ;

	// if it is the last question (only for a sequential exercise)
	if($exerciseType == 1 || $questionNum >= $nbrQuestions)
	{
		// goes to the script that will show the result of the exercise
		header('Location: exercise_result.php');
		exit();
	}
}
// if the object is not in the session
if( !empty($exerciseId) || !isset($_SESSION['objExercise']) )
{
	// construction of Exercise
	$objExercise=new Exercise();

	// if the specified exercise doesn't exist or is disabled
	if(!$objExercise->read($exerciseId) || (!$objExercise->selectStatus() && !$is_allowedToEdit && !$_SESSION['inPathMode']))
	{
		die($langExerciseNotFound);
	}

	// saves the object into the session
	//session_register('objExercise');
    $_SESSION['objExercise'] = $objExercise;
    
    unset($_SESSION['objQuestion'	]);
	unset($_SESSION['objAnswer'		]);
	unset($_SESSION['questionList'	]);
	unset($_SESSION['exerciseResult']);
	unset($_SESSION['exeStartTime'	]);

	// for older php versions
	// clear the session, the values are probably those of another exercise
 	session_unregister('objQuestion');
	session_unregister('objAnswer');
	session_unregister('questionList');
	session_unregister('exerciseResult');
	session_unregister('exeStartTime');
}
// get infos about the current exercise
$exerciseTitle		= $objExercise->selectTitle();
$exerciseDescription= $objExercise->selectDescription();
$randomQuestions	= $objExercise->isRandom();
$exerciseType		= $objExercise->selectType();
$exerciseMaxTime 	= $objExercise->get_max_time();
$exerciseMaxAttempt	= $objExercise->get_max_attempt();

// count number of attempts of the user 
$sql="SELECT count(`exe_result`) AS `tryQty`
        FROM `$TBL_TRACK_EXERCISES`
       WHERE `exe_user_id` = '$_uid'
         AND `exe_exo_id` = ".$objExercise->selectId()."
       GROUP BY `exe_user_id`";
$result = claro_sql_query_fetch_all($sql);
$userTryQty = $result[0]['tryQty']+1; // +1 to count this attempt too
// end of count of attempts of the user

if(!isset($_SESSION['questionList']))
{
	// selects the list of question ID
	$questionList=$randomQuestions?$objExercise->selectRandomList():$objExercise->selectQuestionList();

	// saves the question list into the session
	//session_register('questionList');
  $_SESSION['questionList'] = $questionList;
}
// start time of the exercise (use session because in post it could be modified
// to easily by user using a development bar in mozilla for an example)
// need to check if it already exists in session for sequential exercises
if(!isset($_SESSION['exeStartTime']) )
{
	$_SESSION['exeStartTime'] = time();
}
$nbrQuestions=sizeof($questionList);

// if questionNum comes from POST and not from GET
if(!$questionNum || $_REQUEST['questionNum'])
{
	// only used for sequential exercises (see $exerciseType)
	if(!$questionNum)
	{
		$questionNum=1;
	}
	else
	{
		$questionNum++;
	}
}


$nameTools=$objExercise->exercise;


if($_REQUEST['questionNum'])
{
	$QUERY_STRING="questionNum=$questionNum";
}

if ($_SESSION['inPathMode'] == true) 
{
	$hide_banner = true;
}
else
{
  $interbredcrump[]=array("url" => "exercice.php","name" => $langExercices);
}
include($includePath.'/claro_init_header.inc.php');

// EXERCISE  PROPERTIES HANDLING
$statusMsg = "<p>";
$errMsg = "";
$showExerciseForm = true;
// MAX ALLOWED TIME
// display actual time only if exercise is sequential, it will always be
// zero in non sequential mode 

if($exerciseType == 2) 
{ 
	$statusMsg .= $langCurrentTime." : ".(time()-$_SESSION['exeStartTime']); 
}

if($exerciseMaxTime != 0)
{
  $statusMsg .= " ".$langMaxAllowedTime." : ".disp_minutes_seconds($exerciseMaxTime);
}
else
{
  $statusMsg .= " ".$langNoTimeLimit;
}
	
// MAX ALLOWED ATTEMPTS
// display maximum attempts number only if != 0 (0 means unlimited attempts)
// always display user attempts count
// do not show attempts for anonymous user
if($_uid)
{
  $statusMsg .= "<br />".$langAttempt." ".$userTryQty." ";
  if( $exerciseMaxAttempt )
  {
    $statusMsg .= $langOn." ".$exerciseMaxAttempt;
    if( $userTryQty > $exerciseMaxAttempt )
    {
        $showExerciseForm = false;
        $errMsg .=  "<br/>".$langNoMoreAttemptsAvailable;
    }
  }
}
// AVAILABILITY DATES
// check if the exercise is available (between opening and closing  dates)
$mktimeNow      = mktime();
$timeStartDate  = $objExercise->get_start_date('timestamp');

$statusMsg  .= "<br />".$langAvailableFrom." "
                    .claro_disp_localised_date($dateTimeFormatLong,$timeStartDate);

if($objExercise->get_end_date() != "9999-12-31 23:59:59")
{
    $timeEndDate    = $objExercise->get_end_date('timestamp');
    $statusMsg   .= " ".$langUntil." "
                        .claro_disp_localised_date($dateTimeFormatLong,$timeEndDate);
}
                      
if( $timeStartDate > $mktimeNow )
{
    $showExerciseForm = false;
    $errMsg .= "<br />".$langExerciseNotAvailable;
}
elseif( ($objExercise->get_end_date() != "9999-12-31 23:59:59") && ($timeEndDate < $mktimeNow) )
{
    $showExerciseForm = false;
    $errMsg .= "<br />".$langExerciseNoMoreAvailable;
}

// concat errmsg to status msg before displaying it
$statusMsg .= "<br /><b>".$errMsg."</b>";
claro_disp_tool_title($langExercise." : ".$exerciseTitle);

if( $showExerciseForm || $is_courseAdmin )
{
?>
  <p>
  <?php echo claro_parse_user_text($exerciseDescription) ; ?>
  <small>
  <?php echo $statusMsg;  ?>
  </small>
  </p>
<?php
	if( $is_courseAdmin && $_SESSION['inPathMode'] != true )
	{
		echo '<a class="claroCmd" href="admin.php?exerciseId='.$objExercise->selectId().'">'.$langEditExercise.'</a>';
	}	
?>
  <table width="100%" border="0" cellpadding="1" cellspacing="0">
  <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>?<?php echo SID ?>" autocomplete="off">
  <input type="hidden" name="formSent" value="1">
  <input type="hidden" name="exerciseType" value="<?php echo $exerciseType; ?>">
  <input type="hidden" name="questionNum" value="<?php echo $questionNum; ?>">
  <input type="hidden" name="nbrQuestions" value="<?php echo $nbrQuestions; ?>">
  <tr>
    <td>
  
  <?php
  $i=0;
  
  foreach($questionList as $questionId)
  {
    $i++;
  
    // for sequential exercises
    if($exerciseType == 2)
    {
      // if it is not the right question, goes to the next loop iteration
      if($questionNum != $i)
      {
        continue;
      }
      else
      {
        // if the user has already answered this question
        if(isset($exerciseResult[$questionId]))
        {
          // construction of the Question object
          $objQuestionTmp=new Question();
  
          // reads question informations
          $objQuestionTmp->read($questionId);
  
          $questionName=$objQuestionTmp->selectTitle();
  
          // destruction of the Question object
          unset($objQuestionTmp);
  
          echo '<tr><td>'.$langAlreadyAnswered.' &quot;'.$questionName.'&quot;</td></tr>';
  
          break;
        }
      }
    }
  ?>
  <table width="100%" cellpadding="4" cellspacing="2" border="0" class="claroTable">
    <tr class="headerX">
      <th valign="top" colspan="2">
      <?php echo $langQuestion; ?> <?php echo $i; if($exerciseType == 2) echo ' / '.$nbrQuestions; ?>
      </th>
    </tr>
   <tfoot>
  <?php
    // shows the question and its answers
    showQuestion($questionId);
  ?>
    </tfoot>
    </table>
  <?php
    // for sequential exercises
    if($exerciseType == 2)
    {
      // quits the loop
      break;
    }
  }	// end foreach()
  ?>
  
    </td>
  </tr>
  <tr>
    <td align="center"><br />
    <input type="submit" value="<?php echo ($exerciseType == 1 || $nbrQuestions == $questionNum)?$langOk:$langNext.' &gt;'; ?>">
	</td>
  </tr>
  </form>
  </table>

<?php

} //end of if ($showExerciseForm)
else
{
  echo "<small>".$statusMsg."</small>";
}

if ($_SESSION['inPathMode'] == true) 
{	
	// echo minimal html footer so that the page is valid
	$hide_footer = true;
}
include($includePath.'/claro_init_footer.inc.php');
?>
