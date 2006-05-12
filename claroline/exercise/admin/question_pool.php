<?php // $Id$
/**
 * CLAROLINE
 *
 * @version 1.8 $Revision$
 *
 * @copyright (c) 2001-2006 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @author Claro Team <cvs@claroline.net>
 *
 */
 
$tlabelReq = 'CLQWZ___';
 
require '../../inc/claro_init_global.inc.php';

if ( !$_cid || !$is_courseAllowed ) claro_disp_auth_form(true);

$is_allowedToEdit = claro_is_allowed_to_edit();

// courseadmin reserved page
if( !$is_allowedToEdit )
{
	header("Location: ../exercise.php");
	exit();	
}

// tool libraries
include_once '../lib/exercise.class.php';
include_once '../lib/question.class.php';
include_once '../lib/exercise.lib.php'; 

// claroline libraries
include_once $includePath.'/lib/form.lib.php';
include_once $includePath.'/lib/pager.lib.php';
include_once $includePath.'/lib/fileManage.lib.php';

/*
 * DB tables definition for list query
 */
$tbl_cdb_names = claro_sql_get_course_tbl();
$tbl_quiz_exercise = $tbl_cdb_names['qwz_exercise'];
$tbl_quiz_question = $tbl_cdb_names['qwz_question'];
$tbl_quiz_rel_exercise_question = $tbl_cdb_names['qwz_rel_exercise_question'];
 
/*
 * Handle request
 */
if ( isset($_REQUEST['cmd']) )	$cmd = $_REQUEST['cmd'];
else							$cmd = '';

if( isset($_REQUEST['exId']) && is_numeric($_REQUEST['exId']) ) $exId = (int) $_REQUEST['exId'];
else															$exId = null;

$exercise = new Exercise();
if( !is_null($exId) )
{
	$exercise->load($exId);
}

if( isset($_REQUEST['quId']) && is_numeric($_REQUEST['quId']) ) $quId = (int) $_REQUEST['quId'];
else															$quId = null;

if( isset($_REQUEST['filter']) ) 	$filter = $_REQUEST['filter'];
else								$filter = 'all';

/*
 * Execute commands
 */
// use question in exercise
if( $cmd == 'rqUse' && !is_null($quId) && !is_null($exId) )
{
	if( $exercise->addQuestion($quId) )
	{
		// TODO show confirmation and back link
		header('Location: edit_exercise.php?exId='.$exId);	
	}
}
 
// delete question
if( $cmd == 'delQu' && !is_null($quId) )
{
	$question = new Question();
	if( $question->load($quId) )
	{
		if( !$question->delete() )
		{
			// TODO show confirmation and list	
		}	
	}
}

// export question
if( $cmd == 'exExport' && get_conf('enableExerciseExportQTI') )
{
    include('../export/question_export.php');

    // contruction of XML flow
    $xml = export_question($quId);

    if (!empty($xml))
    {
        header("Content-type: application/xml");
        header('Content-Disposition: attachment; filename="question_'. http_response_splitting_workaround($quId) . '.xml"');
        echo $xml;
        exit();
    }
}
/*
 * Get list
 */
//-- pager init
if( !isset($_REQUEST['offset']) )	$offset = 0;
else								$offset = $_REQUEST['offset'];

//-- filters handling
$filterList = get_filter_list();

// in exercise context remove exercise from filters
if( !is_null($exId) )
{
	if( isset($filterList[$exId]) ) unset($filterList[$exId]); 	
}

if( is_numeric($filter) )
{
	$filterCondition = " AND REQ.`exerciseId` = ".$filter;	
}
elseif( $filter == 'orphan' )
{
	$filterCondition = " AND REQ.`exerciseId` IS NULL ";	
}
else // $filter == 'all'
{
	$filterCondition = "";
}

//-- prepare query
if( !is_null($exId) )
{
	$questionList = $exercise->getQuestionList();
	
	if( is_array($questionList) && !empty($questionList) )
	{
		foreach( $questionList as $aQuestion )
		{
			$questionIdList[] = $aQuestion['id'];
		}
	    $questionCondition = " AND Q.`id` NOT IN ("  . implode(', ', array_map( 'intval', $questionIdList) ) . ") ";
	}
	else
	{
		$questionCondition = "";
	}
	
// TODO probably need to adapt query with a left join on rel_exercise_question for filter	
	
	$sql = "SELECT Q.`id`, Q.`title`, Q.`type`
			  FROM `".$tbl_quiz_question."` AS Q
			  LEFT JOIN `".$tbl_quiz_rel_exercise_question."` AS REQ
			  ON REQ.`questionId` = Q.`id`
			  WHERE 1 = 1 
			 " . $questionCondition . "
			 " . $filterCondition . "
		  GROUP BY Q.`id`
		  ORDER BY Q.`title`";

}
else
{
	$sql = "SELECT Q.`id`, Q.`title`, Q.`type`
			  FROM `".$tbl_quiz_question."` AS Q
			  LEFT JOIN `".$tbl_quiz_rel_exercise_question."` AS REQ
			  ON REQ.`questionId` = Q.`id`			  
			  WHERE 1 = 1 
			 " . $filterCondition . "
		  GROUP BY Q.`id`
		  ORDER BY Q.`title`";
}

// get list
$myPager = new claro_sql_pager($sql, $offset, get_conf('questionPoolPager',25));
$questionList = $myPager->get_result_list();
 
/*
 * Output
 */ 
$interbredcrump[]= array ('url' => '../exercise.php', 'name' => get_lang('Exercises'));
if( !is_null($exId) )
{
	$interbredcrump[]= array ('url' => './edit_exercise.php?exId='.$exId, 'name' => get_lang('Exercise').' : '.$exercise->getTitle());	
}

$nameTools = get_lang('Question pool');

include($includePath.'/claro_init_header.inc.php');

echo claro_html_tool_title($nameTools);

//-- filter listbox
$attr['onchange'] = 'filterForm.submit()';

echo "\n" 
.	 '<form method="get" name="filterForm" action="question_pool.php">' . "\n"
.	 '<input type="hidden" name="exId" value="'.$exId.'" />' . "\n"
.	 '<p align="right">' . "\n"
.	 '<label for="filter">'.get_lang('Filter').'&nbsp;:&nbsp;</label>' . "\n"
.	 claro_html_form_select('filter',$filterList, $filter, $attr) . "\n"
.	 '<noscript>' . "\n"
.	 '<input type="submit" value="'.get_lang('ok').'" />' . "\n"
.	 '</noscript>' . "\n"
.	 '</p>' . "\n"
.	 '</form>' . "\n\n";

if( !is_null($exId) )
{
	$cmd_menu[] = '<a class="claroCmd" href="./edit_exercise.php?exId='.$exId.'">&lt;&lt; '.get_lang('Go back to the exercise').'</a>';
}
$cmd_menu[] = '<a class="claroCmd" href="./edit_question.php?cmd=rqEdit">'.get_lang('New question').'</a>';

echo claro_html_menu_horizontal($cmd_menu);

//-- pager
echo $myPager->disp_pager_tool_bar($_SERVER['PHP_SELF']);

//-- list
echo '<table class="claroTable emphaseLine" border="0" align="center" cellpadding="2" cellspacing="2" width="100%">' . "\n\n"
.	 '<thead>' . "\n"
.	 '<tr class="headerX">' . "\n"
.	 '<th>' . get_lang('Question') . '</th>' . "\n"
.	 '<th>' . get_lang('Answer type') . '</th>' . "\n";
$colspan = 2;
if( !is_null($exId) )
{
	echo '<th>' . get_lang('Reuse') . '</th>' . "\n";
	$colspan++;
}
else
{
	echo '<th>' . get_lang('Modify') . '</th>' . "\n"
	.	 '<th>' . get_lang('Delete') . '</th>' . "\n";
	$colspan += 2;
	
	if( get_conf('enableExerciseExportQTI') ) 
	{
		echo '<th colspan="2">' . get_lang('Export') . '</th>' . "\n";
		$colspan++;
	}
}

echo '</tr>' . "\n"
.	 '</thead>' . "\n\n"		
.	 '<tbody>' . "\n";

if( !empty($questionList) )
{		
	$questionTypeLang['MCUA'] = get_lang('Multiple choice (Unique answer)');
	$questionTypeLang['MCMA'] = get_lang('Multiple choice (Multiple answers)');
	$questionTypeLang['TF'] = get_lang('True/False');
	$questionTypeLang['FIB'] = get_lang('Fill in blanks');
	$questionTypeLang['MATCHING'] = get_lang('Matching');
	
	foreach( $questionList as $question )
	{
		echo '<tr>'
		.	 '<td>'.$question['title'].'</td>' . "\n";

		// answer type			
		echo '<td><small>'.$questionTypeLang[$question['type']].'</small></td>' . "\n";
		
		if( !is_null($exId) )
		{
			// reuse
			echo '<td align="center">'
			.	 '<a href="question_pool.php?exId='.$exId.'&amp;cmd=rqUse&amp;quId='.$question['id'].'">'
			.	 '<img src="'.$clarolineRepositoryWeb.'img/enroll.gif" border="0" alt="'.get_lang('Modify').'" />'
			.	 '</a>'
			.	 '</td>' . "\n";			
		}
		else
		{
			// edit
			echo '<td align="center">'
			.	 '<a href="edit_question.php?quId='.$question['id'].'">'
			.	 '<img src="'.$clarolineRepositoryWeb.'img/edit.gif" border="0" alt="'.get_lang('Modify').'" />'
			.	 '</a>'
			.	 '</td>' . "\n";
			
			// delete question from database
			$confirmString = get_lang('Are you sure you want to completely delete this question ?');		
			
			echo '<td align="center">'
			.	 '<a href="question_pool.php?exId='.$exId.'&amp;cmd=delQu&amp;quId='.$question['id'].'" onclick="javascript:if(!confirm(\''.clean_str_for_javascript($confirmString).'\')) return false;">'
			.	 '<img src="'.$clarolineRepositoryWeb.'img/delete.gif" border="0" alt="'.get_lang('Delete').'" />'
			.	 '</a>'
			.	 '</td>' . "\n";
			
			if( get_conf('enableExerciseExportQTI') )
			{
				// export
				echo '<td align="center">'
				.	 '<a href="question_pool.php?exId='.$exId.'&amp;cmd=exExport&amp;quId='.$question['id'].'">'
				.	 '<img src="'.$clarolineRepositoryWeb.'img/export.gif" border="0" alt="'.get_lang('Export').'" />'
				.	 '</a>'
				.	 '</td>' . "\n";
			}
		}
		echo '</tr>';
		
	}
	
}
else 
{
	echo '<tr>' . "\n"
	.	 '<td colspan="'.$colspan.'">' . get_lang('Empty') . '</td>' . "\n"
	.	 '</tr>' . "\n\n";	
}
echo '</tbody>' . "\n\n"
.	 '</table>' . "\n\n";

//-- pager
echo $myPager->disp_pager_tool_bar($_SERVER['PHP_SELF']);

include($includePath.'/claro_init_footer.inc.php');

?>

