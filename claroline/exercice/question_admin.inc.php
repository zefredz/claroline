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
*/

		/*>>>>>>>>>>>>>>>>>>>> QUESTION ADMINISTRATION <<<<<<<<<<<<<<<<<<<<*/

/**
 * This script allows to manage a question and its answers
 *
 * It is included from the script admin.php
 */

// ALLOWED_TO_INCLUDE is defined in admin.php
if(!defined('ALLOWED_TO_INCLUDE'))
{
	exit();
}

$attachedFile = $objQuestion->selectAttachedFile();
$hasTempAttachedFile = ($objQuestion->selectTempAttachedFile() != "") ? true:false;


// if the question we are modifying is used in several exercises
if($usedInSeveralExercises)
{
?>
	
<h3>
  <?php echo $questionName; ?>
</h3>

<form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>?modifyQuestion=<?php echo $modifyQuestion; ?>&amp;modifyAnswers=<?php echo $modifyAnswers; ?>">
<table border="0" cellpadding="5">
<tr>
  <td>

<?php

	// submit question
	if($submitQuestion)
	{
?>

    <input type="hidden" name="questionName" value="<?php echo htmlentities($questionName); ?>">
    <input type="hidden" name="questionDescription" value="<?php echo htmlentities($questionDescription); ?>">
    <input type="hidden" name="fileUpload_size" value="<?php echo $fileUpload_size; ?>">
    <input type="hidden" name="deleteAttachedFile" value="<?php echo $deletePicture; ?>">
    
    <input type="hidden" name="attachedFile" value="<?php echo htmlentities($attachedFile); ?>">
    <input type="hidden" name="hasTempAttachedFile" value="<?php echo $hasTempAttachedFile; ?>">

<?php
	}
	// submit answers
	else
	{
		if($answerType == UNIQUE_ANSWER || $answerType == MULTIPLE_ANSWER)
		{
?>

	<input type="hidden" name="correct" value="<?php echo htmlentities(serialize($correct)); ?>">
	<input type="hidden" name="reponse" value="<?php echo htmlentities(serialize($reponse)); ?>">
	<input type="hidden" name="comment" value="<?php echo htmlentities(serialize($comment)); ?>">
	<input type="hidden" name="weighting" value="<?php echo htmlentities(serialize($weighting)); ?>">
	<input type="hidden" name="nbrAnswers" value="<?php echo $nbrAnswers; ?>">

<?php
		}
		elseif($answerType == MATCHING)
		{
?>

	<input type="hidden" name="option" value="<?php echo htmlentities(serialize($option)); ?>">
	<input type="hidden" name="match" value="<?php echo htmlentities(serialize($match)); ?>">
	<input type="hidden" name="sel" value="<?php echo htmlentities(serialize($sel)); ?>">
	<input type="hidden" name="weighting" value="<?php echo htmlentities(serialize($weighting)); ?>">
	<input type="hidden" name="nbrOptions" value="<?php echo $nbrOptions; ?>">
	<input type="hidden" name="nbrMatches" value="<?php echo $nbrMatches; ?>">

<?php
		}
		else
		{
?>

	<input type="hidden" name="reponse" value="<?php echo htmlentities(serialize($reponse)); ?>">
	<input type="hidden" name="comment" value="<?php echo htmlentities(serialize($comment)); ?>">
	<input type="hidden" name="blanks" value="<?php echo htmlentities(serialize($blanks)); ?>">
	<input type="hidden" name="weighting" value="<?php echo htmlentities(serialize($weighting)); ?>">
	<input type="hidden" name="setWeighting" value="1">

<?php
		}
	} // end submit answers
?>

	<input type="hidden" name="answerType" value="<?php echo $answerType; ?>">

    <table border="0" cellpadding="3" align="center" width="400">
    <tr>
      <td><?php echo $langUsedInSeveralExercises.' :'; ?></td>
    </tr>
    <tr>
      <td><input type="radio" name="modifyIn" id="modifyInAll" value="allExercises" checked="checked"><label for="modifyInAll"><?php echo $langModifyInAllExercises; ?></label></td>
    </tr>
    <tr>
      <td><input type="radio" name="modifyIn" id="modifyIn1" value="thisExercise"><label for="modifyIn1"><?php echo $langModifyInThisExercise; ?></label></td>
    </tr>
    <tr>
	  <td>
      <input type="submit" name="<?php echo $submitQuestion?'submitQuestion':'submitAnswers'; ?>" value="<?php echo $langOk; ?>">&nbsp;&nbsp;
      <input type="submit" name="buttonBack" value="<?php echo $langCancel; ?>">
	  </td>
    </tr>
    </table>
  </td>
</tr>
</table>
</form>

<?php
}
else
{
	// we are in an exercise
	if($exerciseId)
	{
		$backLinkHtml = "\n".'<p><small><a href="'.$_SERVER['PHP_SELF'].'">&lt;&lt; '.$langGoBackToQuestionList.'</a></small></p>'."\n";
	}
	// we are not in an exercise, so we come from the question pool
	else
	{
		$backLinkHtml = "\n".'<p><small><a href="question_pool.php?fromExercise='.$fromExercise.'">&lt;&lt; '.$langGoBackToQuestionPool.'</a></small></p>'."\n";
	}

	// selects question informations
	$questionName=$objQuestion->selectTitle();
	$questionDescription=$objQuestion->selectDescription();

	// is attached file set ?
	$okAttachedFile = empty($attachedFile)?false:true;
?>

<h3>
  <?php echo $questionName; ?>
</h3>
<?php echo $backLinkHtml; ?>
<blockquote>
  <?php echo claro_parse_user_text($questionDescription); ?>
</blockquote>

<?php
	// show the attached file of the question
	if($okAttachedFile)
	{
      echo display_attached_file($attachedFile);
	}

	// doesn't show the edit link if we come from the question pool to pick a question for an exercise
	if(!$fromExercise)
	{
?>

<a class="claroCmd" href="<?php echo $_SERVER['PHP_SELF']; ?>?modifyQuestion=<?php echo $questionId; ?>"><img src="<?php echo $imgRepositoryWeb ?>edit.gif" border="0" align="absmiddle" alt=""><?php echo $langEditQuestion ; ?></a>

<?php
	}
?>

<hr size="1" noshade="noshade">

<br />
<b><?php echo $langQuestionAnswers; ?></b>

<br /><br />

<table border="0" align="center" cellpadding="2" cellspacing="2" width="100%">
<form>

<?php
	// shows answers of the question. 'true' means that we don't show the question, only answers
	if(!showQuestion($questionId,true))
	{
?>

<tr>
  <td><?php echo $langNoAnswer; ?></td>
</tr>

<?php
	}
?>

</form>
</table>

<br>

<?php
	// doesn't show the edit link if we come from the question pool to pick a question for an exercise
	if(!$fromExercise)
	{
?>

<a class="claroCmd" href="<?php echo $_SERVER['PHP_SELF']; ?>?modifyAnswers=<?php echo $questionId; ?>"><img src="<?php echo $imgRepositoryWeb ?>edit.gif" border="0" align="absmiddle" alt=""><?php echo $langEditAnswers; ?></a>

<?php
	}
echo $backLinkHtml;	
}
?>
