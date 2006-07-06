<?php // $Id$
if ( count( get_included_files() ) == 1 ) die( '---' );
/*
+----------------------------------------------------------------------+
| CLAROLINE 1.6
+----------------------------------------------------------------------+
| Copyright (c) 2001-2006 Universite catholique de Louvain (UCL)      |
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

include_once ($includePath.'/lib/fileUpload.lib.php');
include_once ($includePath.'/lib/fileDisplay.lib.php');

// the question form has been submitted
if(isset($_REQUEST['submitQuestion']))
{
    $questionName = trim($_REQUEST['questionName']);
    $questionDescription = trim($_REQUEST['questionDescription']);

    // no name given
    if(empty($questionName))
    {
        $msgErr = get_lang('Please give the question');
    }
    // checks if the question is used in several exercises
    elseif($exerciseId && !isset($modifyIn) && $_SESSION['objQuestion']->selectNbrExercises() > 1)
    {
        $usedInSeveralExercises = 1;

        // if a file has been set
        if(is_uploaded_file($_FILES['fileUpload']['tmp_name']))
        {
            // saves the file into a temporary file
            $_SESSION['objQuestion']->setTmpAttachedFile($_FILES['fileUpload']['tmp_name'],get_secure_file_name($_FILES['fileUpload']['name']));
        }
    }
    else
    {
        // if the user has chosed to modify the question only in the current exercise
        if(isset($modifyIn) && $modifyIn == 'thisExercise')
        {
            // duplicates the question
            $questionId = $_SESSION['objQuestion']->duplicate();

            // tempAttachedFile object var isnot handled by duplicate because not stored in db
            $tmpFile = $_SESSION['objQuestion']->selectTempAttachedFile();

            // deletes the old question
            $_SESSION['objQuestion']->delete($exerciseId);

            // removes the old question ID from the question list of the Exercise object
            $_SESSION['objExercise']->removeFromList($modifyQuestion);

            $nbrQuestions--;

            // construction of the duplicated Question
            $_SESSION['objQuestion'] = new Question();

            $_SESSION['objQuestion']->read($questionId);
            $_SESSION['objQuestion']->updateTempAttachedFile($tmpFile);

            // adds the exercise ID into the exercise list of the Question object
            $_SESSION['objQuestion']->addToList($exerciseId);

            // construction of the Answer object
            $objAnswerTmp=new Answer($modifyQuestion);

            // copies answers from $modifyQuestion to $questionId
            $objAnswerTmp->duplicate($questionId);

            // destruction of the Answer object
            unset($objAnswerTmp);
        }

        $_SESSION['objQuestion']->updateTitle($questionName);
        $_SESSION['objQuestion']->updateDescription($questionDescription);
        $_SESSION['objQuestion']->updateType($_REQUEST['answerType']);
        $_SESSION['objQuestion']->save($exerciseId);

        // if a file has been set or checkbox "delete" has been checked
        if(
            ( isset($_FILES['fileUpload']) && is_uploaded_file($_FILES['fileUpload']['tmp_name']) && $_FILES['fileUpload']['size'] > 0 )
            ||( isset($_REQUEST['hasTempAttachedFile']) && $_REQUEST['hasTempAttachedFile'] )
            ||( isset($_REQUEST['deleteAttachedFile']) && $_REQUEST['deleteAttachedFile'] )
          )
        {
            // we remove the attached file
            $_SESSION['objQuestion']->removeAttachedFile();

            // if we add a new attached file
            if(
                ( isset($_FILES['fileUpload']) && is_uploaded_file($_FILES['fileUpload']['tmp_name']) && $_FILES['fileUpload']['size'] > 0 )
                ||( isset($_REQUEST['hasTempAttachedFile']) && $_REQUEST['hasTempAttachedFile'] )
              )
            {
                // image is already saved in a temporary file
                if( isset($_REQUEST['hasTempAttachedFile']) )
                {
                    $_SESSION['objQuestion']->getTmpAttachedFile();
                    // clean this var to prevent clash if a question used in several exercises is
                    // modified several times without cleaning the session of objExercise
                    $_SESSION['objQuestion']->updateTempAttachedFile('');
                }
                // saves the file coming from POST FILE
                else
                {
                    $_SESSION['objQuestion']->uploadAttachedFile($_FILES['fileUpload']['tmp_name'],get_secure_file_name($_FILES['fileUpload']['name']));
                }
            }

            $_SESSION['objQuestion']->save($exerciseId);

        }

        $questionId = $_SESSION['objQuestion']->selectId();

        if($exerciseId)
        {
            // adds the question ID into the question list of the Exercise object
            if($_SESSION['objExercise']->addToList($questionId))
            {
                $_SESSION['objExercise']->save();

                $nbrQuestions++;
            }
        }

        if( isset($newQuestion) )
        {
            // goes to answer administration
            $modifyAnswers = $questionId;
        }
        else
        {
            // goes to exercise viewing
            $editQuestion = $questionId;
        }

        unset($newQuestion,$modifyQuestion);
    }

}
else
{
    // if we don't come here after having cancelled the warning message "used in serveral exercises"
    if(!isset($_REQUEST['buttonBack']))
    {
        $questionName = $_SESSION['objQuestion']->selectTitle();
        $questionDescription = $_SESSION['objQuestion']->selectDescription();
        $answerType = $_SESSION['objQuestion']->selectType();
        $attachedFile = $_SESSION['objQuestion']->selectAttachedFile();
    }
}

$aFileIsAttached = empty($attachedFile)?false:true;

$maxUploadSizeInBytes = get_max_upload_size(100000000,$attachedFilePathSys);

if((isset($newQuestion) || (isset($modifyQuestion))) && !isset($usedInSeveralExercises))
{

?>

<h3>
  <?php echo $questionName; ?>
</h3>

<?php
    if(isset($modifyQuestion))    $addform = "modifyQuestion=".$modifyQuestion;
    else                        $addform = "";
    if(isset($newQuestion))     $addform .= "&newQuestion=".$newQuestion;
?>

<form enctype="multipart/form-data" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>?<?php echo $addform;?>">
<table border="0" cellpadding="5">

<?php
    // if there is an error message
    if(!empty($msgErr))
    {
?>

<tr>
  <td colspan="2">
    <table border="0" cellpadding="3" align="center" width="400">
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
  <td><label for="questionName"><?php echo get_lang('Question title'); ?> :</label></td>
  <td><input type="text" name="questionName" id="questionName" size="50" maxlength="200" value="<?php echo htmlspecialchars($questionName); ?>" style="width:400px;"></td>
</tr>
<tr>
  <td valign="top"><label for="questionDescription"><?php echo get_lang('Statement'); ?> :</label></td>
  <td>
  <?php echo claro_html_textarea_editor('questionDescription', htmlspecialchars($questionDescription),15) ?>
  </td>
</tr>
<tr>
  <td valign="top"><label for="fileUpload"><?php echo $aFileIsAttached?get_lang('Replace attached file'):get_lang('Attach a file'); ?> :</label></td>
  <td>
  <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo $maxUploadSizeInBytes; ?>" />
  <input type="file" name="fileUpload" id="fileUpload" size="30" style="width:390px;"><br />
  <small><?php echo get_lang('Max file size'); ?> :<?php echo format_file_size( $maxUploadSizeInBytes ); ?></small>

<?php
    if($aFileIsAttached)
    {
?>

    <br /><input type="checkbox" name="deleteAttachedFile" id="deleteAttachedFile" value="1" <?php if(isset($_REQUEST['deleteAttachedFile'])) echo 'checked="checked"'; ?>> <label for="deleteAttachedFile"><?php echo get_lang('Delete attached file'); ?></label>

<?php
    }
?>

  </td>
</tr>
<tr>
  <td valign="top"><?php echo get_lang('Answer type'); ?> :</td>
  <td><input type="radio" name="answerType" id="answerType1" value="<?php echo UNIQUE_ANSWER; ?>" <?php if((isset($answerType) && $answerType <= UNIQUE_ANSWER)|| !isset($answerType)) echo 'checked="checked"'; ?>> <label for="answerType1"><?php echo get_lang('Multiple choice (Unique answer)'); ?></label><br />
      <input type="radio" name="answerType" id="answerType2" value="<?php echo MULTIPLE_ANSWER; ?>" <?php if(isset($answerType) &&$answerType == MULTIPLE_ANSWER) echo 'checked="checked"'; ?>> <label for="answerType2"><?php echo get_lang('Multiple choice (Multiple answers)'); ?></label><br />
      <input type="radio" name="answerType" id="answerType4" value="<?php echo MATCHING; ?>" <?php if(isset($answerType) &&$answerType == MATCHING) echo 'checked="checked"'; ?>> <label for="answerType4"><?php echo get_lang('Matching'); ?></label><br />
      <input type="radio" name="answerType" id="answerType3" value="<?php echo FILL_IN_BLANKS; ?>" <?php if(isset($answerType) &&$answerType == FILL_IN_BLANKS) echo 'checked="checked"'; ?>> <label for="answerType3"><?php echo get_lang('Fill in blanks'); ?></label><br />
      <input type="radio" name="answerType" id="answerType5" value="<?php echo TRUEFALSE; ?>" <?php if(isset($answerType) &&$answerType >= TRUEFALSE) echo 'checked="checked"'; ?>> <label for="answerType5"><?php echo get_lang('True/False'); ?></label>
  </td>
</tr>
<tr>
  <td>&nbsp;</td>
  <td colspan="2">
    <input type="submit" name="submitQuestion" value="<?php echo get_lang('Ok'); ?>">&nbsp;&nbsp;
    <input type="submit" name="cancelQuestion" value="<?php echo get_lang('Cancel'); ?>">
  </td>
</tr>
</table>
</form>

<?php
}
?>
