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

		/*>>>>>>>>>>>>>>>>>>>> STATEMENT ADMINISTRATION <<<<<<<<<<<<<<<<<<<<*/

/**
 * This script allows to manage statement of questions
 *
 * It is included from the script admin.php
 */

// ALLOWED_TO_INCLUDE is defined in admin.php
if(!defined('ALLOWED_TO_INCLUDE'))
{
	exit();
}

// the question form has been submitted
if($submitQuestion)
{
	$questionName=trim($questionName);
	$questionDescription=trim($questionDescription);
        $imageUpload_name=strtolower($imageUpload_name);

	// no name given
	if(empty($questionName))
	{
		$msgErr=$langGiveQuestion;
	}
	// checks if the question is used in several exercises
	elseif($exerciseId && !$modifyIn && $objQuestion->selectNbrExercises() > 1)
	{
		$usedInSeveralExercises=1;

        // if a picture has been set
        if($imageUpload_size)
        {
            // saves the picture into a temporary file
            $objQuestion->setTmpPicture($imageUpload,$imageUpload_name);
        }
	}
	else
	{
        // if the user has chosed to modify the question only in the current exercise
        if($modifyIn == 'thisExercise')
        {
        	// duplicates the question
        	$questionId=$objQuestion->duplicate();

            // deletes the old question
            $objQuestion->delete($exerciseId);

            // removes the old question ID from the question list of the Exercise object
            $objExercise->removeFromList($modifyQuestion);

            $nbrQuestions--;

            // construction of the duplicated Question
            $objQuestion=new Question();

            $objQuestion->read($questionId);

			// adds the exercise ID into the exercise list of the Question object
            $objQuestion->addToList($exerciseId);

            // construction of the Answer object
            $objAnswerTmp=new Answer($modifyQuestion);

            // copies answers from $modifyQuestion to $questionId
            $objAnswerTmp->duplicate($questionId);

            // destruction of the Answer object
            unset($objAnswerTmp);
        }

		$objQuestion->updateTitle($questionName);
		$objQuestion->updateDescription($questionDescription);
		$objQuestion->updateType($answerType);
                $objQuestion->save($exerciseId);


		// if a picture has been set or checkbox "delete" has been checked
		if($imageUpload_size || $deletePicture)
		{
			// we remove the picture
			$objQuestion->removePicture();

			// if we add a new picture
			if($imageUpload_size)
			{
                // image is already saved in a temporary file
                if($modifyIn)
                {
                    $objQuestion->getTmpPicture();
                }
                // saves the picture coming from POST FILE
                else
                {
                    $objQuestion->uploadPicture($imageUpload,$imageUpload_name);
                }
			}
                
                $objQuestion->save($exerciseId);
                        
		}

		$questionId=$objQuestion->selectId();

		if($exerciseId)
		{
			// adds the question ID into the question list of the Exercise object
			if($objExercise->addToList($questionId))
			{
				$objExercise->save();

				$nbrQuestions++;
			}
		}

		if($newQuestion)
		{
			// goes to answer administration
			$modifyAnswers=$questionId;
		}
		else
		{
			// goes to exercise viewing
			$editQuestion=$questionId;
		}

		unset($newQuestion,$modifyQuestion);
	}
}
else
{
	// if we don't come here after having cancelled the warning message "used in serveral exercises"
	if(!$buttonBack)
	{
		$questionName=$objQuestion->selectTitle();
		$questionDescription=$objQuestion->selectDescription();
		$answerType=$objQuestion->selectType();
                $pictureName=$objQuestion->selectPictureName();
	}
        
        $okPicture=empty($pictureName)?false:true;
}

if(($newQuestion || $modifyQuestion) && !$usedInSeveralExercises)
{
?>

<h3>
  <?php echo $questionName; ?>
</h3>

<form enctype="multipart/form-data" method="post" action="<?php echo $PHP_SELF; ?>?modifyQuestion=<?php echo $modifyQuestion; ?>&newQuestion=<?php echo $newQuestion; ?>">
<table border="0" cellpadding="5">

<?php
	if($okPicture)
	{
?>

<tr>
  <td colspan="2" align="center"><img src="<?php echo $picturePathWeb.'/'.$pictureName; ?>" border="0"></td>
</tr>

<?php
	}

	// if there is an error message
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
  <td><label for="questionName"><?php echo $langQuestion; ?> :</label></td>
  <td><input type="text" name="questionName" id="questionName" size="50" maxlength="200" value="<?php echo htmlentities($questionName); ?>" style="width:400px;"></td>
</tr>
<tr>
  <td valign="top"><label for="questionDescription"><?php echo $langQuestionDescription; ?> :</label></td>
  <td><textarea wrap="virtual" name="questionDescription" id="questionDescription" cols="50" rows="4" style="width:400px;"><?php echo htmlentities($questionDescription); ?></textarea></td>
</tr>
<tr>
  <td><label for="imageUpload"><?php echo $okPicture?$langReplacePicture:$langAddPicture; ?> :</label></td>
  <td><input type="file" name="imageUpload" id="imageUpload" size="30" style="width:390px;">

<?php
	if($okPicture)
	{
?>

	<br><input type="checkbox" name="deletePicture" id="deletePicture" value="1" <?php if($deletePicture) echo 'checked="checked"'; ?>> <label for="deletePicture"><?php echo $langDeletePicture; ?></label>

<?php
	}
?>

  </td>
</tr>
<tr>
  <td valign="top"><?php echo $langAnswerType; ?> :</td>
  <td><input type="radio" name="answerType" id="answerType1" value="1" <?php if($answerType <= 1) echo 'checked="checked"'; ?>> <label for="answerType1"><?php echo $langUniqueSelect; ?></label><br>
	  <input type="radio" name="answerType" id="answerType2" value="2" <?php if($answerType == 2) echo 'checked="checked"'; ?>> <label for="answerType2"><?php echo $langMultipleSelect; ?></label><br>
	  <input type="radio" name="answerType" id="answerType4" value="4" <?php if($answerType >= 4) echo 'checked="checked"'; ?>> <label for="answerType4"><?php echo $langMatching; ?></label><br>
	  <input type="radio" name="answerType" id="answerType3" value="3" <?php if($answerType == 3) echo 'checked="checked"'; ?>> <label for="answerType3"><?php echo $langFillBlanks; ?></label>
  </td>
</tr>
<tr>
  <td colspan="2" align="center">
	<input type="submit" name="cancelQuestion" value="<?php echo $langCancel; ?>">
	&nbsp;&nbsp;<input type="submit" name="submitQuestion" value="<?php echo $langOk; ?>">
  </td>
</tr>
</table>
</form>

<?php
}
?>
