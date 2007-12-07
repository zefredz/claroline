<?php // $Id$
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.5.*
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2005 Universite catholique de Louvain (UCL)
      +----------------------------------------------------------------------+
      |   This program is free software; you can redistribute it and/or
      |   modify it under the terms of the GNU General Public License
      |   as published by the Free Software Foundation; either version 2
      |   of the License, or (at your option) any later version.
      +----------------------------------------------------------------------+
      | Authors: Olivier Brouckaert
      |          Claroline core team
      +----------------------------------------------------------------------+
*/

		/*>>>>>>>>>>>>>>>>>>>> QUESTION POOL <<<<<<<<<<<<<<<<<<<<*/

/**
 * This script allows administrators to manage questions and add them
 * into their exercises.
 *
 * One question can be in several exercises.
 */

include('exercise.class.php');
include('question.class.php');
include('answer.class.php');

$langFile='exercice';

require '../inc/claro_init_global.inc.php';

$is_allowedToEdit=$is_courseAdmin;

// picture path
$picturePathWeb = $coursesRepositoryWeb.$_course['path'].'/image';
$picturePathSys = $coursesRepositorySys.$_course['path'].'/image';

$TBL_EXERCICE_QUESTION = $_course['dbNameGlu'].'quiz_rel_test_question';
$TBL_EXERCICES         = $_course['dbNameGlu'].'quiz_test';
$TBL_QUESTIONS         = $_course['dbNameGlu'].'quiz_question';
$TBL_REPONSES          = $_course['dbNameGlu'].'quiz_answer';

// maximum number of questions on a same page
$limitQuestPage=50;

if($is_allowedToEdit)
{
	// deletes a question from the data base and all exercises
	if($delete)
	{
		// construction of the Question object
		$objQuestionTmp=new Question();

		// if the question exists
		if($objQuestionTmp->read($delete))
		{
			// deletes the question from all exercises
			$objQuestionTmp->delete();
		}

		// destruction of the Question object
		unset($objQuestionTmp);
	}
	// gets an existing question and copies it into a new exercise
	elseif($recup && $fromExercise)
	{
		// construction of the Question object
		$objQuestionTmp=new Question();

		// if the question exists
		if($objQuestionTmp->read($recup))
		{
			// adds the exercise ID represented by $fromExercise into the list of exercises for the current question
			$objQuestionTmp->addToList($fromExercise);
		}

		// destruction of the Question object
		unset($objQuestionTmp);

		// adds the question ID represented by $recup into the list of questions for the current exercise
		$objExercise->addToList($recup);

		header("Location: admin.php?editQuestion=$recup");
		exit();
	}
}

$nameTools=$langQuestionPool;

$interbredcrump[]=array("url" => "exercice.php","name" => $langExercices);

include($includePath.'/claro_init_header.inc.php');

// if admin of course
if($is_allowedToEdit)
{
?>

<h3>
  <?php echo $nameTools; ?>
</h3>

<form method="get" action="<?php echo $_SERVER['PHP_SELF']; ?>">
<input type="hidden" name="fromExercise" value="<?php echo $fromExercise; ?>">
<p align="right">
	<label for="exerciseId"><?php echo $langFilter; ?></label> : 
	
	<select id="exerciseId" name="exerciseId">
		<option value="0">-- <?php echo $langAllExercises; ?> --</option>
		<option value="-1" <?php if($exerciseId == -1) echo 'selected="selected"'; ?>>-- <?php echo $langOrphanQuestions; ?> --</option>

<?php
	$sql="SELECT id,titre FROM `$TBL_EXERCICES` WHERE id<>'$fromExercise' ORDER BY id";
	$result=mysql_query($sql) or die("Error : SELECT at line ".__LINE__);

	// shows a list-box allowing to filter questions
	while($row=mysql_fetch_array($result))
	{
?>

		<option value="<?php echo $row['id']; ?>" <?php if($exerciseId == $row['id']) echo 'selected="selected"'; ?>><?php echo $row['titre']; ?></option>

<?php
	}
?>

    </select> <input type="submit" value="<?php echo $langOk; ?>">
</p>
<?php
	$from=$page*$limitQuestPage;

	// if we have selected an exercise in the list-box 'Filter'
	if($exerciseId > 0)
	{
		$sql="SELECT id,question,type FROM `$TBL_EXERCICE_QUESTION`,`$TBL_QUESTIONS` WHERE question_id=id AND exercice_id='$exerciseId' ORDER BY q_position LIMIT $from,".($limitQuestPage+1);
		$result=mysql_query($sql) or die("Error : SELECT at line ".__LINE__);
	}
	// if we have selected the option 'Orphan questions' in the list-box 'Filter'
	elseif($exerciseId == -1)
	{
		$sql="SELECT id,question,type FROM `$TBL_QUESTIONS` LEFT JOIN `$TBL_EXERCICE_QUESTION` ON question_id=id WHERE exercice_id IS NULL ORDER BY question LIMIT $from,".($limitQuestPage+1);
		$result=mysql_query($sql) or die("Error : SELECT at line ".__LINE__);
	}
	// if we have not selected any option in the list-box 'Filter'
	else
	{
		$sql="SELECT id,question,type FROM `$TBL_QUESTIONS` LEFT JOIN `$TBL_EXERCICE_QUESTION` ON question_id=id WHERE exercice_id IS NULL OR exercice_id<>'$fromExercise' GROUP BY id ORDER BY question LIMIT $from,".($limitQuestPage+1);
		$result=mysql_query($sql) or die("Error : SELECT at line ".__LINE__);

		// forces the value to 0
		$exerciseId=0;
	}

	$nbrQuestions=mysql_num_rows($result);
?>

	<table border="0" cellpadding="0" cellspacing="0" width="100%">
	<tr>
	  <td>

<?php
	if($fromExercise)
	{
?>

		<a href="admin.php">&lt;&lt; <?php echo $langGoBackToEx; ?></a>

<?php
	}
	else
	{
?>

		<a href="admin.php?newQuestion=yes"><?php echo $langNewQu; ?></a>

<?php
	}
?>

	  </td>
	  <td align="right">

<?php
	if($page)
	{
?>

	<small><a href="<?php echo $_SERVER['PHP_SELF']; ?>?exerciseId=<?php echo $exerciseId; ?>&amp;fromExercise=<?php echo $fromExercise; ?>&amp;page=<?php echo ($page-1); ?>">&lt;&lt; <?php echo $langPreviousPage; ?></a></small> |

<?php
	}
	elseif($nbrQuestions > $limitQuestPage)
	{
?>

	<small>&lt;&lt; <?php echo $langPreviousPage; ?> |</small>

<?php
	}

	if($nbrQuestions > $limitQuestPage)
	{
?>

	<small><a href="<?php echo $_SERVER['PHP_SELF']; ?>?exerciseId=<?php echo $exerciseId; ?>&amp;fromExercise=<?php echo $fromExercise; ?>&amp;page=<?php echo ($page+1); ?>"><?php echo $langNextPage; ?> &gt;&gt;</a></small>

<?php
	}
	elseif($page)
	{
?>

	<small><?php echo $langNextPage; ?> &gt;&gt;</small>

<?php
	}
?>

	  </td>
	</tr>
	</table>

<table class="claroTable" border="0" align="center" cellpadding="2" cellspacing="2" width="100%">
<tr class="headerX">

<?php
	if($fromExercise)
	{
?>

  <th width="80%" align="center"><?php echo $langQuestion; ?></th>
  <th width="20%" align="center"><?php echo $langReuse; ?></th>

<?php
	}
	else
	{
?>

  <th width="60%" align="center"><?php echo $langQuestion; ?></th>
  <th width="20%" align="center"><?php echo $langModify; ?></th>
  <th width="20%" align="center"><?php echo $langDelete; ?></th>

<?php
	}
?>

</tr>

<?php
	$i=1;

	while($row=mysql_fetch_array($result))
	{
		// if we come from the exercise administration to get a question, doesn't show the question already used by that exercise
		if(!$fromExercise || !$objExercise->isInList($row['id']))
		{
?>

<tr>
  <td><a href="admin.php?editQuestion=<?php echo $row['id']; ?>&amp;fromExercise=<?php echo $fromExercise; ?>"><?php echo $row['question']; ?></a></td>
  <td align="center">

<?php
			if(!$fromExercise)
			{
?>

	<a href="admin.php?editQuestion=<?php echo $row['id']; ?>"><img src="<?php echo $clarolineRepositoryWeb ?>img/edit.gif" border="0" alt="<?php echo $langModify; ?>"></a>

<?php
			}
			else
			{
?>

	<a href="<?php echo $_SERVER['PHP_SELF']; ?>?recup=<?php echo $row['id']; ?>&amp;fromExercise=<?php echo $fromExercise; ?>"><img src="<?php echo $clarolineRepositoryWeb ?>img/enroll.gif" border="0" alt="<?php echo $langReuse; ?>"></a>

<?php
			}
?>

  </td>

<?php
			if(!$fromExercise)
			{
?>

  <td align="center">
    <a href="<?php echo $_SERVER['PHP_SELF']; ?>?exerciseId=<?php echo $exerciseId; ?>&amp;delete=<?php echo $row['id']; ?>" onclick="javascript:if(!confirm('<?php echo addslashes(htmlentities($langConfirmDeleteQuestion)); ?>')) return false;"><img src="<?php echo $clarolineRepositoryWeb ?>img/delete.gif" border="0" alt="<?php echo $langDelete; ?>"></a>
  </td>

<?php
			}
?>

</tr>

<?php
			// skips the last question, that is only used to know if we have or not to create a link "Next page"
			if($i == $limitQuestPage)
			{
				break;
			}

			$i++;
		}
	}

	if(!$nbrQuestions)
	{
?>

<tr>
  <td colspan="<?php echo $fromExercise?2:3; ?>"><?php echo $langNoQuestion; ?></td>
</tr>

<?php
	}
?>

</table>
</form>

<?php
}
// if not admin of course
else
{
	echo $langNotAllowed;
}

@include($includePath.'/claro_init_footer.inc.php');
?>
