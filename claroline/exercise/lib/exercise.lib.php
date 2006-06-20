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

function get_localized_question_type()
{
    $questionType['MCUA']         = get_lang('Multiple choice (Unique answer)');
    $questionType['MCMA']         = get_lang('Multiple choice (Multiple answers)');
    $questionType['TF']         = get_lang('True/False');
    $questionType['FIB']         = get_lang('Fill in blanks');
    $questionType['MATCHING']     = get_lang('Matching');
    
    return $questionType;
}

?>