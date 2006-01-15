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
+----------------------------------------------------------------------+
*/

        /*>>>>>>>>>>>>>>>>>>>> QUESTION LIST ADMINISTRATION <<<<<<<<<<<<<<<<<<<<*/

/**
 * This script allows to manage the question list
 *
 * It is included from the script admin.php
 */

// ALLOWED_TO_INCLUDE is defined in admin.php
if(!defined('ALLOWED_TO_INCLUDE'))
{
    exit();
}

// moves a question up in the list
if(isset($_REQUEST['moveUp']))
{
    $_SESSION['objExercise']->moveUp($_REQUEST['moveUp']);
    $_SESSION['objExercise']->save();
}

// moves a question down in the list
if(isset($_REQUEST['moveDown']))
{
    $_SESSION['objExercise']->moveDown($_REQUEST['moveDown']);
    $_SESSION['objExercise']->save();
}

// deletes a question from the exercise (not from the data base)
if( isset($deleteQuestion) )
{
    // construction of the Question object
    $objQuestionTmp = new Question();

    // if the question exists
    if($objQuestionTmp->read($deleteQuestion))
    {
        $objQuestionTmp->delete($exerciseId);

        // if the question has been removed from the exercise
        if($_SESSION['objExercise']->removeFromList($deleteQuestion))
        {
            $nbrQuestions--;
        }
    }

    // destruction of the Question object
    unset($objQuestionTmp);
}
?>

<hr size="1" noshade="noshade">

<a class="claroCmd" href="<?php echo $_SERVER['PHP_SELF']; ?>?newQuestion=yes"><?php echo get_lang('NewQu'); ?></a> | <a class="claroCmd" href="question_pool.php?fromExercise=<?php echo $exerciseId; ?>"><?php echo get_lang('GetExistingQuestion'); ?></a>

<br><br>

<b><?php echo get_lang('QuestionList'); ?></b>

<table border="0" align="center" cellpadding="2" cellspacing="2" width="100%">

<?php
if($nbrQuestions)
{
    $questionList = $_SESSION['objExercise']->selectQuestionList();

    $i = 1;

    foreach($questionList as $id)
    {
        $objQuestionTmp = new Question();

        $objQuestionTmp->read($id);
?>

<tr>
  <td><?php echo "$i. ".$objQuestionTmp->selectTitle(); ?><br><small><?php if (isset($aType[$objQuestionTmp->selectType()-1])) echo $aType[$objQuestionTmp->selectType()-1]; ?></small></td>
</tr>
<tr>
  <td>
    <a href="<?php echo $_SERVER['PHP_SELF']; ?>?editQuestion=<?php echo $id; ?>"><img src="<?php echo $imgRepositoryWeb ?>edit.gif" border="0" align="absmiddle" alt="<?php echo get_lang('EditQuestion'); ?>"></a>
    <a href="<?php echo $_SERVER['PHP_SELF']; ?>?deleteQuestion=<?php echo $id; ?>" onclick="javascript:if(!confirm('<?php echo clean_str_for_javascript(get_lang('Please confirm your choice')); ?>')) return false;"><img src="<?php echo $imgRepositoryWeb ?>delete.gif" border="0" align="absmiddle" alt="<?php echo get_lang('Delete'); ?>"></a>

<?php
        if($i != 1)
        {
?>

    <a href="<?php echo $_SERVER['PHP_SELF']; ?>?moveUp=<?php echo $id; ?>"><img src="<?php echo $imgRepositoryWeb ?>up.gif" border="0" align="absmiddle" alt="<?php echo get_lang('MoveUp'); ?>"></a>

<?php
        }

        if($i != $nbrQuestions)
        {
?>

    <a href="<?php echo $_SERVER['PHP_SELF']; ?>?moveDown=<?php echo $id; ?>"><img src="<?php echo $imgRepositoryWeb ?>down.gif" border="0" align="absmiddle" alt="<?php echo get_lang('MoveDown'); ?>"></a>

<?php
        }
?>

  </td>
</tr>

<?php
        $i++;

        unset($objQuestionTmp);
    }
}

if(!isset($i))
{
?>

<tr>
  <td><?php echo get_lang('NoQuestion'); ?></td>
</tr>

<?php
}
?>

</table>
