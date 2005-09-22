<?php // $Id$
/*
+----------------------------------------------------------------------+
| CLAROLINE 1.6
+----------------------------------------------------------------------+
| Copyright (c) 2001, 2005 Universite catholique de Louvain (UCL)      |
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

require '../inc/claro_init_global.inc.php';

if ( !$_cid || !$is_courseAllowed ) claro_disp_auth_form(true);

include($includePath."/lib/pager.lib.php");

$is_allowedToEdit = $is_courseAdmin;

// attached file path
$attachedFilePathWeb = $coursesRepositoryWeb.$_course['path'].'/exercise';
$attachedFilePathSys = $coursesRepositorySys.$_course['path'].'/exercise';

$tbl_cdb_names = claro_sql_get_course_tbl();
$tbl_quiz_rel_test_question = $tbl_cdb_names['quiz_rel_test_question'];
$tbl_quiz_test         	= $tbl_cdb_names['quiz_test'];
$tbl_quiz_question      = $tbl_cdb_names['quiz_question'];
$tbl_quiz_answer		= $tbl_cdb_names['quiz_answer'];

// maximum number of questions on a same page
$questionsPerPage = 25;

if($is_allowedToEdit)
{
	// deletes a question from the data base and all exercises
	if( !empty($_REQUEST['delete']) )
	{
		// construction of the Question object
		$objQuestionTmp = new Question();

		// if the question exists
		if( $objQuestionTmp->read($_REQUEST['delete']) )
		{
			// deletes the question from all exercises
			$objQuestionTmp->delete();
		}

		// destruction of the Question object
		unset($objQuestionTmp);
	}
	// gets an existing question and copies it into a new exercise
	elseif( !empty($_REQUEST['recup']) && !empty($_REQUEST['fromExercise']) )
	{
		// construction of the Question object
		$objQuestionTmp = new Question();

		// if the question exists
		if($objQuestionTmp->read($_REQUEST['recup']))
		{
			// adds the exercise ID represented by $_REQUEST['fromExercise'] into the list of exercises for the current question
			$objQuestionTmp->addToList($_REQUEST['fromExercise']);
		}

		// destruction of the Question object
		unset($objQuestionTmp);

		// adds the question ID represented by $_REQUEST['recup'] into the list of questions for the current exercise
		$_SESSION['objExercise']->addToList($_REQUEST['recup']);

		header("Location: admin.php?editQuestion=".http_response_splitting_workaround($_REQUEST['recup']));
		exit();
	}
	// Export a question in IMS/QTI
	elseif( isset($_REQUEST['export']) && isset($enableExerciseExportQTI) && $enableExerciseExportQTI == true )
	{
		include('question_export.php');
		
		// contruction of XML flow
		$xml = export_question($_REQUEST['export']);
		
		if (!empty($xml))
		{
			header("Content-type: application/xml");
			header('Content-Disposition: attachment; filename="question_'. http_response_splitting_workaround($_REQUEST['export']) . '.xml"');
			echo $xml;
			exit;
		}
	}
}

$nameTools = $langQuestionPool;

$interbredcrump[] = array("url" => "exercice.php","name" => $langExercices);

include($includePath.'/claro_init_header.inc.php');

// if admin of course
if($is_allowedToEdit)
{
	echo claro_disp_tool_title($nameTools);
?>

<form method="get" action="<?php echo $_SERVER['PHP_SELF']; ?>">
<input type="hidden" name="fromExercise" value="<?php echo (isset($_REQUEST['fromExercise']))?$_REQUEST['fromExercise']:''; ?>">
<p align="right">
	<label for="exerciseId"><?php echo $langFilter; ?></label> : 
	
	<select id="exerciseId" name="exerciseId">
		<option value="0">-- <?php echo $langAllExercises; ?> --</option>
		<option value="-1" <?php if( isset($_REQUEST['exerciseId']) && $_REQUEST['exerciseId'] == -1 ) echo 'selected="selected"'; ?>>-- <?php echo $langOrphanQuestions; ?> --</option>

<?php
	$sql = "SELECT `id`, `titre` as `title`
			FROM `".$tbl_quiz_test."` ";
			
	if(isset($_REQUEST['fromExercise']))
    {
		$sql .= " WHERE `id` <> '". (int)$_REQUEST['fromExercise']."'";
	}
	$sql .= " ORDER BY `id`";
			
	$exerciseList = claro_sql_query_fetch_all($sql);

	// shows a list-box allowing to filter questions
	foreach( $exerciseList as $exercise )
	{
		echo '<option value="'.$exercise['id'].'" ' ;
		
		if( isset($_REQUEST['exerciseId']) && $_REQUEST['exerciseId'] == $exercise['id'] )
		    echo ' selected="selected"';

		echo '>'.$exercise['title'].'</option>';
	}
?>

    </select>
	<input type="submit" value="<?php echo $langOk; ?>">
</p>
<?php

	// if we have selected an exercise in the list-box 'Filter'
	if( isset($_REQUEST['exerciseId']) && $_REQUEST['exerciseId'] > 0 )
	{
		$sql = "SELECT `id`, `question`, `type`
				FROM `".$tbl_quiz_rel_test_question."`,`".$tbl_quiz_question."`
				WHERE `question_id` = `id`
				AND `exercice_id`= '". (int)$_REQUEST['exerciseId']."'
				ORDER BY `q_position`";
	}
	// if we have selected the option 'Orphan questions' in the list-box 'Filter'
	elseif( isset($_REQUEST['exerciseId']) && $_REQUEST['exerciseId'] == -1 )
	{
		$sql = "SELECT `id`, `question`, `type`
				FROM `".$tbl_quiz_question."`
					LEFT JOIN `".$tbl_quiz_rel_test_question."`
					ON `question_id` = `id`
				WHERE `exercice_id` IS NULL
				ORDER BY `question`";
	}
	// if we have not selected any option in the list-box 'Filter'
	else
	{
		$sql = "SELECT `id`, `question`, `type`
				FROM `".$tbl_quiz_question."`
					LEFT JOIN `".$tbl_quiz_rel_test_question."`
					ON `question_id` = `id`";

		if(isset($_REQUEST['fromExercise']))
        {
			$sql .= " WHERE `exercice_id` IS NULL
					OR `exercice_id` <> '". (int)$_REQUEST['fromExercise']."'";
		}
		$sql .=	" GROUP BY `id`
				ORDER BY `question`";

		// forces the value to 0
		$exerciseId = 0;
	}

?>

<p>

<?php
	if( !empty($_REQUEST['fromExercise']) )
	{
?>

		<small><a href="admin.php">&lt;&lt; <?php echo $langGoBackToEx; ?></a></small>

<?php
	}
	else
	{
?>

		<a class="claroCmd" href="admin.php?newQuestion=yes"><?php echo $langNewQu; ?></a>
<?php
	}
?>
</p>

<?php

if (!isset($_REQUEST['offset']))	$offset = 0;
else 								$offset = $_REQUEST['offset'];

$myPager = new claro_sql_pager($sql, $offset, $questionsPerPage);
$questionList = $myPager->get_result_list();

?>

<table class="claroTable" border="0" align="center" cellpadding="2" cellspacing="2" width="100%">
<tr class="headerX">

<?php
	if( !empty($_REQUEST['fromExercise']) )
	{
?>

  <th width="80%" align="center"><?php echo $langQuestion; ?></th>
  <th width="20%" align="center"><?php echo $langReuse; ?></th>

<?php
	}
	else
	{
?>

  <th width="70%" align="center"><?php echo $langQuestion; ?></th>
  <th width="10%" align="center"><?php echo $langModify; ?></th>
  <th width="10%" align="center"><?php echo $langDelete; ?></th>
<?php
		if( isset($enableExerciseExportQTI) && $enableExerciseExportQTI == true )
		{
  			echo '  <th width="10%" align="center">'.$langExport.'</th>'."\n";
		}

	}
?>

</tr>

<?php

 	foreach( $questionList as $question )
	{
  		// if we come from the exercise administration to get a question, doesn't show the question already used by that exercise
		if( empty($_REQUEST['fromExercise']) || !$_SESSION['objExercise']->isInList($question['id']) )
		{
?>

<tr>
  <td><a href="admin.php?editQuestion=<?php echo $question['id']; ?>&fromExercise=<?php echo (isset($_REQUEST['fromExercise']))?$_REQUEST['fromExercise']:''; ?>"><?php echo $question['question']; ?></a></td>
  <td align="center">

<?php
			if( empty($_REQUEST['fromExercise']) )
			{
?>

	<a href="admin.php?editQuestion=<?php echo $question['id']; ?>"><img src="<?php echo $imgRepositoryWeb ?>edit.gif" border="0" alt="<?php echo $langEditQuestion; ?>"></a>

<?php
			}
			else
			{
?>

	<a href="<?php echo $_SERVER['PHP_SELF']; ?>?recup=<?php echo $question['id']; ?>&fromExercise=<?php echo (isset($_REQUEST['fromExercise']))?$_REQUEST['fromExercise']:''; ?>"><img src="<?php echo $imgRepositoryWeb ?>enroll.gif" border="0" alt="<?php echo $langReuse; ?>"></a>

<?php
			}
?>

  </td>

<?php
			if( empty($_REQUEST['fromExercise']) )
			{
?>

  <td align="center">
    <a href="<?php echo $_SERVER['PHP_SELF']; ?>?exerciseId=<?php echo $exerciseId; ?>&delete=<?php echo $question['id']; ?>" onclick="javascript:if(!confirm('<?php echo clean_str_for_javascript($langConfirmDeleteQuestion); ?>')) return false;"><img src="<?php echo $imgRepositoryWeb ?>delete.gif" border="0" alt="<?php echo $langDelete; ?>"></a>
  </td>
<?php
                if( isset($enableExerciseExportQTI) && $enableExerciseExportQTI == true )
                {
  					echo '<td align="center">'
    					.'<a href="'.$_SERVER['PHP_SELF'].'?export='.$question['id'].'"><img src="'.$clarolineRepositoryWeb.'img/export.gif" border="0"'
      					.'alt="'.$langExport.'"></a>'
  						.'</td>'."\n";
				}

			}
?>

</tr>

<?php
		}
	}

	if( !is_array($questionList) || count($questionList) == 0 )
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
include($includePath.'/claro_init_footer.inc.php');
?>
