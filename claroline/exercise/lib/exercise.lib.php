<?php // $Id$
if ( count( get_included_files() ) == 1 ) die( '---' );
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
 
/**
 * Build a list of available exercises that wil be used by claro_html_form_select to show a filter list
 * @param $excludeId an exercise id that doesn't have to be shown in  filter list
 * @return array 2d array where keys are the exercise name and value is the exercise id
 * @author Sebastien Piraux <pir@cerdecam.be>
 */
function get_filter_list($excludeId = '')
{
    $tbl_cdb_names = claro_sql_get_course_tbl();
    $tbl_quiz_exercise = $tbl_cdb_names['qwz_exercise'];

    $filterList[get_lang('All exercises')] = 'all';
    $filterList[get_lang('Orphan questions')] = 'orphan';
    
    // get exercise list
    $sql = "SELECT `id`, `title` 
              FROM `".$tbl_quiz_exercise."` 
              ORDER BY `title`";
    $exerciseList = claro_sql_query_fetch_all($sql);
    
    if( is_array($exerciseList) && !empty($exerciseList) )
    {
        foreach( $exerciseList as $anExercise )
        {
        	if( $excludeId != $anExercise['id'] )
            {
            	$filterList[$anExercise['title']] = $anExercise['id'];
            }
        }
    }     
    return $filterList;
}

/**
 * build a array making the correspondance between question type and its name
 * 
 * @return array array where key is the type and value is the corresponding translation
 * @author Sebastien Piraux <pir@cerdecam.be>
 */
function get_localized_question_type()
{
    $questionType['MCUA']         = get_lang('Multiple choice (Unique answer)');
    $questionType['MCMA']         = get_lang('Multiple choice (Multiple answers)');
    $questionType['TF']         = get_lang('True/False');
    $questionType['FIB']         = get_lang('Fill in blanks');
    $questionType['MATCHING']     = get_lang('Matching');
    
    return $questionType;
}

/**
 * return the number of exercises using question $quId
 *
 * @param $quId requested question id
 * @return number of exercises using question $quId
 * @author Sebastien Piraux <pir@cerdecam.be>
 */
function count_exercise_using_question($quId)
{
    $tbl_cdb_names = claro_sql_get_course_tbl();
    $tbl_quiz_rel_exercise_question = $tbl_cdb_names['qwz_rel_exercise_question']; 
    
    $sql = "SELECT COUNT(`exerciseId`)
            FROM `".$tbl_quiz_rel_exercise_question."`
            WHERE `questionId` = '".(int) $quId."'";
    
    $exerciseCount = claro_sql_query_get_single_value($sql);
    
    if( ! $exerciseCount )  return 0;
    else                    return $exerciseCount;    
}

/**
 * return html required to display the required form elements to ask the user if the question must be modified in 
 * all exercises or only the current one
 *
 * @return string html code
 * @author Sebastien Piraux <pir@cerdecam.be>
 */
function html_ask_duplicate()
{
    $html = '<strong>' . get_lang('This question is used in several exercises.') . '</strong><br />' . "\n"
    .    '<input type="radio" name="duplicate" id="doNotDuplicate" value="false" checked="checked" />'
    .    '<label for="doNotDuplicate">' . get_lang('Modify it in all exercises') . '</label><br />' . "\n"
    .    '<input type="radio" name="duplicate" id="duplicate" value="true" />'
    .    '<label for="duplicate">' . get_lang('Modify it only in this exercise') . '</label>' . "\n";
    
    return $html;
}
?>