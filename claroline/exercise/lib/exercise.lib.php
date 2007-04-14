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

function set_learning_path_progression($totalResult,$totalGrade,$timeToCompleteExe,$_uid)
{
    $tbl_cdb_names = claro_sql_get_course_tbl();
    $tbl_lp_rel_learnPath_module = $tbl_cdb_names['lp_rel_learnPath_module'];
    $tbl_lp_user_module_progress = $tbl_cdb_names['lp_user_module_progress'];
    
    
    // update raw in DB to keep the best one, so update only if new raw is better  AND if user NOT anonymous
    if( $_uid )
    {
        // exercices can have a negative score, but we don't accept that in LP
        // so if totalScore is negative use 0 as result
        $totalResult = max($totalResult, 0);

        if ( $totalGrade != 0 )
        {
            $newRaw = @round($totalResult/$totalGrade*100);
        }
        else
        {
            $newRaw = 0;
        }

        $scoreMin = 0;
        $scoreMax = $totalGrade;
        $scormSessionTime = seconds_to_scorm_time($timeToCompleteExe);
        
        // need learningPath_module_id and raw_to_pass value
        $sql = "SELECT LPM.`raw_to_pass`, LPM.`learnPath_module_id`, UMP.`total_time`, UMP.`raw`
                  FROM `".$tbl_lp_rel_learnPath_module."` AS LPM, `".$tbl_lp_user_module_progress."` AS UMP
                 WHERE LPM.`learnPath_id` = '".(int)$_SESSION['path_id']."'
                   AND LPM.`module_id` = '".(int)$_SESSION['module_id']."'
                   AND LPM.`learnPath_module_id` = UMP.`learnPath_module_id`
                   AND UMP.`user_id` = ".(int) $_uid;
                   
        $lastProgression = claro_sql_query_get_single_row($sql);

        if( $lastProgression )
        {
            // build sql query
            $sql = "UPDATE `".$tbl_lp_user_module_progress."` SET ";
            // if recorded score is more than the new score => update raw, credit and status

            if( $lastProgression['raw'] < $totalResult )
            {
                // update raw
                $sql .= "`raw` = ".$totalResult.",";
                // update credit and statut if needed ( score is better than raw_to_pass )
                if ( $newRaw >= $lastProgression['raw_to_pass'])
                {
                    $sql .= "    `credit` = 'CREDIT',
                                 `lesson_status` = 'PASSED',";
                }
                else // minimum raw to pass needed to get credit
                {
                    $sql .= "    `credit` = 'NO-CREDIT',
                                `lesson_status` = 'FAILED',";
                }
            }// else don't change raw, credit and lesson_status

            // default query statements
            $sql .= "    `scoreMin`         = " . (int)$scoreMin . ",
                        `scoreMax`         = " . (int)$scoreMax . ",
                        `total_time`    = '".addScormTime($lastProgression['total_time'], $scormSessionTime)."',
                        `session_time`    = '".$scormSessionTime."'
                     WHERE `learnPath_module_id` = ". (int)$lastProgression['learnPath_module_id']."
                       AND `user_id` = " . (int)$_uid . "";
                       
            return claro_sql_query($sql);
        }
        else
        {
            return false;
        }
    }
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
    .    '<input type="radio" name="duplicate" id="doNotDuplicate" value="false"';
    
    if( !isset($_REQUEST['duplicate']) || $_REQUEST['duplicate'] != 'true')
    {
        $html .= ' checked="checked" ';
    }
    
    $html .= '/>'
    .    '<label for="doNotDuplicate">' . get_lang('Modify it in all exercises') . '</label><br />' . "\n"
    .    '<input type="radio" name="duplicate" id="duplicate" value="true"';
    
    if( isset($_REQUEST['duplicate']) && $_REQUEST['duplicate'] == 'true')
    {
        $html .= ' checked="checked" ';
    }
    
    $html .= '/>'
    .    '<label for="duplicate">' . get_lang('Modify it only in this exercise') . '</label>' . "\n";
    
    return $html;
}

/**
 * cast $value to a float with max 2 decimals
 *
 * @param string string to cast
 * @return string html code
 * @author Sebastien Piraux <pir@cerdecam.be>
 */
function castToFloat($value)
{
    // use dot as decimal separator
    $value = (float) str_replace(',','.',$value);
    // round to max 2 decimals
    $value = round($value*100)/100;
    
    return $value;
}
?>
