<?php // $Id$
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.3.2 $Revision$                            |
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2003 Universite catholique de Louvain (UCL)      |
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

$langFile='exercice';

require '../inc/claro_init_global.inc.php';

@include($includePath.'/lib/text.lib.php');

$picturePathWeb = $coursesRepositoryWeb.$_course['path'].'/image';
$picturePathSys = $coursesRepositorySys.$_course['path'].'/image';



$is_allowedToEdit=$is_courseAdmin;

$TBL_EXERCICE_QUESTION = $_course['dbNameGlu'].'quiz_rel_test_question';
$TBL_EXERCICES         = $_course['dbNameGlu'].'quiz_test';
$TBL_QUESTIONS         = $_course['dbNameGlu'].'quiz_question';
$TBL_REPONSES          = $_course['dbNameGlu'].'quiz_answer';

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
if(!isset($_SESSION['objExercise']))
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
}

$exerciseTitle=$objExercise->selectTitle();
$exerciseDescription=$objExercise->selectDescription();
$randomQuestions=$objExercise->isRandom();
$exerciseType=$objExercise->selectType();

if(!isset($_SESSION['questionList']))
{
	// selects the list of question ID
	$questionList=$randomQuestions?$objExercise->selectRandomList():$objExercise->selectQuestionList();

	// saves the question list into the session
	//session_register('questionList');
  $_SESSION['questionList'] = $questionList;
}

$nbrQuestions=sizeof($questionList);

// if questionNum comes from POST and not from GET
if(!$questionNum || $HTTP_POST_VARS['questionNum'])
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

$nameTools=$langExercice;


if($HTTP_POST_VARS['questionNum'])
{
	$QUERY_STRING="questionNum=$questionNum";
}

if ($_SESSION['inPathMode'] == true) 
{
	// echo minimal html page header so that the page is valid
	echo '<html>
		<head>
			<title>'.$exerciseTitle.'</title>
			<link rel="stylesheet" type="text/css" href="'.$clarolineRepositoryWeb.'css/default.css"  />
		</head>
		<body>';
}
else
{
  $interbredcrump[]=array("url" => "exercice.php","name" => $langExercices);
  include($includePath.'/claro_init_header.inc.php');
}
?>

<h3><?php echo $exerciseTitle; ?></h3>

<p><?php echo claro_parse_user_text(make_clickable($exerciseDescription)); ?></p>

<table width="100%" border="0" cellpadding="1" cellspacing="0">
<form method="post" action="<?php echo $PHP_SELF; ?>?<?= SID ?>" autocomplete="off">
<input type="hidden" name="formSent" value="1">
<input type="hidden" name="exerciseType" value="<?php echo $exerciseType; ?>">
<input type="hidden" name="questionNum" value="<?php echo $questionNum; ?>">
<input type="hidden" name="nbrQuestions" value="<?php echo $nbrQuestions; ?>">
<tr>
  <td>
	<table width="100%" cellpadding="4" cellspacing="2" border="0">

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

	<tr bgcolor="#DDDEBC">
	  <td valign="top" colspan="2">
		<?php echo $langQuestion; ?> <?php echo $i; if($exerciseType == 2) echo ' / '.$nbrQuestions; ?>
	  </td>
	</tr>

<?php
	// shows the question and its answers
	showQuestion($questionId);

	// for sequential exercises
	if($exerciseType == 2)
	{
		// quits the loop
		break;
	}
}	// end foreach()
?>

	</table>
  </td>
</tr>
<tr>
  <td align="center"><br><input type="submit" name="buttonCancel" value="<?php echo $langCancel; ?>">
  &nbsp;&nbsp;<input type="submit" value="<?php echo ($exerciseType == 1 || $nbrQuestions == $questionNum)?$langOk:$langNext.' &gt;'; ?>"></td>
</tr>
</form>
</table>

<?php
if ($_SESSION['inPathMode'] == true) 
{	
	// echo minimal html footer so that the page is valid
	echo '		</body>
		</html>';
}
else
{
 	include($includePath.'/claro_init_footer.inc.php');
}
?>
