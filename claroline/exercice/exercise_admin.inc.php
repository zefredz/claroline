<?php // $Id$
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.4.0 $Revision$                            |
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2003 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
      |   This program is free software; you can redistribute it and/or      |
      |   modify it under the terms of the GNU General Public License        |
      |   as published by the Free Software Foundation; either version 2     |
      |   of the License, or (at your option) any later version.             |
      +----------------------------------------------------------------------+
      | Authors: Olivier Brouckaert <oli.brouckaert@skynet.be>               |
      +----------------------------------------------------------------------+
*/

		/*>>>>>>>>>>>>>>>>>>>> EXERCISE ADMINISTRATION <<<<<<<<<<<<<<<<<<<<*/

/**
 * This script allows to manage an exercise
 *
 * It is included from the script admin.php
 */

// ALLOWED_TO_INCLUDE is defined in admin.php
if(!defined('ALLOWED_TO_INCLUDE'))
{
	exit();
}

// the exercise form has been submitted
if($_REQUEST['submitExercise'])
{
	$exerciseTitle=trim($_REQUEST['exerciseTitle']);
	$exerciseDescription=trim($_REQUEST['exerciseDescription']);
	$randomQuestions=$_REQUEST['randomQuestions']?$_REQUEST['questionDrawn']:0;

	// no title given
	if(empty($_REQUEST['exerciseTitle']))
	{
		$msgErr=$langGiveExerciseName;
	}
	else
	{
		$objExercise->updateTitle($_REQUEST['exerciseTitle']);
		$objExercise->updateDescription($_REQUEST['exerciseDescription']);
		$objExercise->updateType($_REQUEST['exerciseType']);
		$objExercise->set_max_time($_REQUEST['exerciseMaxTime']);
		$objExercise->set_max_tries($_REQUEST['exerciseMaxTries']);
		if($_REQUEST['exerciseShowAnon'] == "show") 
		{
			$objExercise->set_show_anon();	
		}
		else
		{
			$objExercise->set_hide_anon();
		}
		$objExercise->set_show_answer($_REQUEST['exerciseShowAnswer']);
		$objExercise->setRandom($randomQuestions);
		$objExercise->save();

		// reads the exercise ID (only usefull for a new exercise)
		$exerciseId=$objExercise->selectId();

		unset($_REQUEST['modifyExercise']);
	}
}
else
{
	$exerciseTitle		= $objExercise->selectTitle();
	$exerciseDescription= $objExercise->selectDescription();
	$exerciseType		= $objExercise->selectType();
	$randomQuestions	= $objExercise->isRandom();
	$maxTime			= $objExercise->get_max_time();
	$maxTries			= $objExercise->get_max_tries();
	$showAnon			= $objExercise->get_show_anon();
	$showAnswer			= $objExercise->get_show_answer();
}

// shows the form to modify the exercise
if($_REQUEST['modifyExercise'])
{
?>

<form method="post" action="<?php echo $PHP_SELF; ?>?modifyExercise=<?php echo $modifyExercise; ?>">
<table border="0" cellpadding="5">

<?php
	if(!empty($msgErr))
	{
?>

<tr>
  <td colspan="2">
	<table border="0" cellpadding="3" align="center" width="400" bgcolor="#FFCC00">
	<tr>
	  <td><?php echo $msgErr; ?></td>
	</tr>
	</table>
  </td>
</tr>

<?php
	}
?>

<tr>
  <td>
  <label for="exerciseTitle"><?php echo $langExerciseName; ?> :</label>
  </td>
  <td><input type="text" name="exerciseTitle" id="exerciseTitle" size="50" maxlength="200" value="<?php echo htmlentities($exerciseTitle); ?>" style="width:400px;"></td>
</tr>
<tr>
  <td valign="top">
  <label for="exerciseDescription"><?php echo $langExerciseDescription; ?> :</label>
  </td>
  <td>
  <!--<textarea wrap="virtual" name="exerciseDescription" cols="50" rows="4" style="width:400px;"><?php //echo htmlentities($exerciseDescription); ?></textarea></td>-->
  <?php claro_disp_html_area('exerciseDescription', $exerciseDescription) ?>
</tr>
<tr>
  <td valign="top"><?php echo $langExerciseType; ?> :</td>
  <td><input type="radio" name="exerciseType" id="exerciseType1" value="1" <?php if($exerciseType <= 1) echo 'checked="checked"'; ?>> <label for="exerciseType1"><?php echo $langSimpleExercise; ?></label><br>
      <input type="radio" name="exerciseType" id="exerciseType2" value="2" <?php if($exerciseType >= 2) echo 'checked="checked"'; ?>> <label for="exerciseType2"><?php echo $langSequentialExercise; ?></td></label>
</tr>

<tr>
  <td><label for="exerciseMaxTime"><?php echo $langAllowedTime; ?> :</label></td>
  <td>
	<input type="text" name="exerciseMaxTime" id="exerciseMaxTime" size="4" maxlength="4" value="<?php echo $maxTime; ?>">
	( '0' pour 'aucune limite' )
  </td>
</tr>

<tr>
  <td><label for="exerciseMaxTries"><?php echo $langAllowedAttempts; ?> :</label></td>
  <td>
	<input type="text" name="exerciseMaxTries" id="exerciseMaxTries" size="2" maxlength="2" value="<?php echo $maxTries; ?>">
	( '0' pour 'nombre de tentatives illimité' )
  </td>
</tr>

<tr>
  <td valign="top"><?php echo $langAnonymousVisibility; ?> : </td>
  <td>	<input type="radio" name="exerciseShowAnon" id="showAnon" value="show" <?php if($showAnon) echo 'checked="checked"'; ?>>
		<label for="showAnon"><?php echo $langShow; ?></label>
		<br />
  		<input type="radio" name="exerciseShowAnon" id="hideAnon" value="hide" <?php if(!$showAnon) echo 'checked="checked"'; ?>>
		<label for="hideAnon"><?php echo $langHide; ?></label>
  </td>
</tr>

<tr>
  <td><label for="exerciseShowAnswer"><?php echo $langShowAnswers; ?> : </label></td>
  <td><select name="exerciseShowAnswer" id="exerciseShowAnswer">
		<option value="ALWAYS" <?php if($showAnswer == 'ALWAYS') echo 'selected="selected"';?>><?php echo $langAlways; ?></option>
		<option value="NEVER" <?php if($showAnswer == 'NEVER') echo 'selected="selected"'; ?>><?php echo $langNever; ?></option>
		<option value="LASTTRY" <?php if($showAnswer == 'LASTTRY') echo 'selected="selected"'; ?>><?php echo $langAfterLastTry; ?></option>
		<option value="ENDDATE" <?php if($showAnswer == 'ENDDATE') echo 'selected="selected"'; ?>><?php echo $langAfterEndDate; ?></option>
		</select>
  </td>
</tr>
<?php
	if($exerciseId && $nbrQuestions)
	{
?>

<tr>
  <td valign="top"><label for="randomQuestions"><?php echo $langRandomQuestions; ?> :</label></td>
  <td><input type="checkbox" name="randomQuestions" id="randomQuestions" value="1" <?php if($randomQuestions) echo 'checked="checked"'; ?>> <label for="randomQuestions"><?php echo $langYes; ?></label>, <label for="questionDrawn"><?php echo $langTake; ?></label>
    <select name="questionDrawn" id="questionDrawn">

<?php
		for($i=1;$i <= $nbrQuestions;$i++)
		{
?>

	<option value="<?php echo $i; ?>" <?php if(($formSent && $questionDrawn == $i) || (!$formSent && ($randomQuestions == $i || ($randomQuestions <= 0 && $i == $nbrQuestions)))) echo 'selected="selected"'; ?>><?php echo $i; ?></option>

<?php
		}
?>

	</select> <label for="questionDrawn"><?php echo strtolower($langQuestions).' '.$langAmong.' '.$nbrQuestions; ?></label>
  </td>
</tr>

<?php
	}
?>

<tr>
  <td colspan="2" align="center">
	<input type="submit" name="submitExercise" value="<?php echo $langOk; ?>">
	&nbsp;&nbsp;<input type="submit" name="cancelExercise" value="<?php echo $langCancel; ?>">
  </td>
</tr>
</table>
</form>

<?php
}
else
{
?>

<h3>
  <?php echo $exerciseTitle; ?>
</h3>

<blockquote>
  <?php echo claro_parse_user_text($exerciseDescription); ?>
</blockquote>

<a href="<?php echo $PHP_SELF; ?>?modifyExercise=yes"><img src="<?php echo $clarolineRepositoryWeb ?>img/edit.gif" border="0" align="absmiddle" alt="<?php echo $langModify; ?>"></a>

<?php
}
?>
