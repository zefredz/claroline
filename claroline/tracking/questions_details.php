<?php // $Id$
/**
 * CLAROLINE
 *
 * @version 1.8 $Revision$ 
 * @copyright 2001-2006 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @package CLTRACK
 *
 * @author Claro Team <cvs@claroline.net>
 * @author Sébastien Piraux <piraux@claroline.net>
 *
 */
 
require '../inc/claro_init_global.inc.php';

// check if no anonymous
if ( !$_cid || !$_uid ) claro_disp_auth_form(true);

// answer types
define('UNIQUE_ANSWER',  1);
define('MULTIPLE_ANSWER',2);
define('FILL_IN_BLANKS', 3);
define('MATCHING',     4);
define('TRUEFALSE',     5);

// exo_id is required
if( empty($_REQUEST['exo_id']) )
{
    header("Location: ../exercice/exercice.php");
    exit();
}

include('../exercice/question.class.php');
include('../exercice/answer.class.php');

/**
 * DB tables definition
 */

$tbl_mdb_names = claro_sql_get_main_tbl();
$tbl_rel_course_user = $tbl_mdb_names['rel_course_user'  ];
$tbl_user            = $tbl_mdb_names['user'             ];

$tbl_cdb_names = claro_sql_get_course_tbl();
$tbl_quiz_test          = $tbl_cdb_names['quiz_test'              ];
$tbl_quiz_question      = $tbl_cdb_names['quiz_question'          ];
$tbl_quiz_answer        = $tbl_cdb_names['quiz_answer'          ];
$tbl_quiz_rel_test_question  = $tbl_cdb_names['quiz_rel_test_question' ];
$tbl_track_e_exercises     = $tbl_cdb_names['track_e_exercices'];
$tbl_track_e_exe_details = $tbl_cdb_names['track_e_exe_details'];
$tbl_track_e_exe_answers = $tbl_cdb_names['track_e_exe_answers'];

$is_allowedToTrack = $is_courseAdmin;
 
// bredcrump
if( isset($_REQUEST['src']) && $_REQUEST['src'] == 'ex' )
{
    $interbredcrump[]= array ('url'=>'../exercice/exercice.php', 'name'=> get_lang('Exercices'));
    $src = '&src=ex';
}
else
{
 $interbredcrump[]= array ('url'=>'courseLog.php', 'name'=> get_lang('Statistics'));
    $src = '';
}
$interbredcrump[]= array ('url'=>'exercises_details.php?exo_id='.$_REQUEST['exo_id'].$src, 'name'=> get_lang('StatsOfExercise'));
$nameTools = get_lang('StatsOfQuestion');


// if the question_id is not set display the stats of all questions of this exercise
if( empty($_REQUEST['question_id']) )
{
    // show the list of all question when no one is specified
    // a contribution of Jérémy Audry
    $sql = "SELECT `question_id`
            FROM `".$tbl_quiz_rel_test_question."`
            WHERE `exercice_id` = ".(int)$_REQUEST['exo_id'];
            
    $questionList = claro_sql_query_fetch_all($sql);
    // store all question_id for the selected exercise in a tab
    foreach ( $questionList as $question )
    {
        $questionIdsToShow[] = $question['question_id'];
    }
}
// display only the stats of the requested question
else
{
    $questionIdsToShow[0] = (int) $_REQUEST['question_id'];
}


include($includePath."/claro_init_header.inc.php");
// display title
$titleTab['mainTitle'] = $nameTools;
echo claro_disp_tool_title($titleTab);

// build back link
$backLink = "\n\n".'<small><a href="./exercises_details.php?exo_id='.$_REQUEST['exo_id'].$src.'">&lt;&lt;&nbsp;'.get_lang('Back').'</a></small>'."\n\n";
echo $backLink;

if($is_allowedToTrack && $is_trackingEnabled)
{

    foreach( $questionIdsToShow as $questionId )
    {

        $question_id = $questionId;

        // get infos about the question
        $question = new Question();
        $question->read($question_id);
        
        // prepare list to display
        if( $question->selectType() == UNIQUE_ANSWER
            || $question->selectType() == MULTIPLE_ANSWER
            || $question->selectType() == TRUEFALSE )
        {
            // get the list of all possible answer and the number of times it was choose
            $sql = "SELECT `A`.`id`, `A`.`reponse`, `A`.`correct` , COUNT(`TEA`.`answer`) as `nbr`
                        FROM `".$tbl_quiz_question."` AS `Q` ,
                            `".$tbl_quiz_rel_test_question."` AS `RTQ` ,
                            `".$tbl_quiz_answer."` AS `A`
                    LEFT JOIN `".$tbl_track_e_exercises."` AS `TE`
                        ON `TE`.`exe_exo_id` = `RTQ`.`exercice_id`
                    LEFT JOIN `".$tbl_track_e_exe_details."` AS `TED`
                        ON `TED`.`exercise_track_id` = `TE`.`exe_id`
                        AND `TED`.`question_id` = `Q`.`id`
                    LEFT JOIN `".$tbl_track_e_exe_answers."` AS `TEA`
                        ON `TEA`.`details_id` = `TED`.`id`
                        AND `TEA`.`answer` = `A`.`id`
                    WHERE `Q`.`id` = `RTQ`.`question_id`
                        AND `Q`.`id` = `A`.`question_id`
                        AND `Q`.`id` = ".(int)$question->selectId()."
                        AND `RTQ`.`exercice_id` = ".(int)$_REQUEST['exo_id']."
                        AND (`TEA`.`answer` = `A`.`id`
                        OR `TEA`.`answer` IS NULL)
                    GROUP BY `A`.`id`
                    ORDER BY `A`.`r_position` ASC";

              $results = claro_sql_query_fetch_all($sql);

            // we need to know the total number of answer given
            $multipleChoiceTotal = 0;
            foreach( $results as $result )
            {
                $multipleChoiceTotal += $result['nbr'];
            }

            $displayedStatement = $question->selectDescription();
        }
        elseif( $question->selectType() == FILL_IN_BLANKS )
        {
            // get the list of all word used in each blank
            // we take id to have a unique key for answer, answer with same id are
            // from the same attempt
            $sql = "SELECT `TED`.`id`,`Q`.`question`,`TEA`.`answer`
                    FROM `".$tbl_quiz_question."` AS `Q`,
                        `".$tbl_quiz_rel_test_question."` AS `RTQ`,
                        `".$tbl_quiz_answer."` AS `A`,
                        `".$tbl_track_e_exercises."` AS `TE`,
                        `".$tbl_track_e_exe_details."` AS `TED`,
                        `".$tbl_user."` AS `U`
                    LEFT JOIN `".$tbl_track_e_exe_answers."` AS `TEA`
                        ON `TEA`.`details_id` = `TED`.`id`
                    WHERE `RTQ`.`question_id` = `Q`.`id`
                        AND `Q`.`id` = `A`.`question_id`
                        AND `Q`.`id` = `TED`.`question_id`
                        AND `RTQ`.`exercice_id` = `TE`.`exe_exo_id`
                        AND `TE`.`exe_id` = `TED`.`exercise_track_id`
                        AND `U`.`user_id` = `TE`.`exe_user_id`
                        AND `Q`.`id` = ".(int)$question->selectId()."
                        AND `RTQ`.`exercice_id` = '".(int)$_REQUEST['exo_id']."'
                    ORDER BY `TED`.`id` ASC, `TEA`.`id` ASC";

             $answers_details = claro_sql_query_fetch_all($sql);

             //-- we need the blanks of the question
            $objAnswer = new Answer($question->selectId());
            $answer = $objAnswer->selectAnswer(1);

            $explodedResponse = explode( '::',$answer);
            $answer = (isset($explodedResponse[0]))?$explodedResponse[0]:'';

            // we save the answer because it will be modified
            $temp = $answer;

            // blanks will be put into an array
            $blanks = Array();

            $i = 1;

            // the loop will stop at the end of the text
            while(1)
            {
                // quits the loop if there are no more blanks
                if(($pos = strpos($temp,'[')) === false)
                {
                    break;
                }

                // removes characters till '['
                $temp = substr($temp,$pos+1);

                // quits the loop if there are no more blanks
                if(($pos = strpos($temp,']')) === false)
                {
                    break;
                }

                // stores the found blank into the array
                $blanks[$i++] = substr($temp,0,$pos);

                // removes the character ']'
                $temp = substr($temp,$pos+1);
            }

              $nbrBlanks = count($blanks);
              $i = 1;
            $fillInBlanksTotal = array();
            $results = array();
              // in $answers_details we have the list of answers given, each line is one blank filling
              // all blanks of each answers are in the list so we have
              // attempt-blank1 ; attempt1-blank2; attempt2-blank1; attempt2-blank2; ...
              // so we will have to extract and group all blank1 and blank2
            foreach( $answers_details as $detail )
            {
                if( !isset($results[$i][$detail['answer']]) )
                {
                    $results[$i][$detail['answer']]['answer'] = $detail['answer'];
                    $results[$i][$detail['answer']]['nbr'] = 1;
                }
                else
                {
                    $results[$i][$detail['answer']]['nbr']++;
                }

                // for each blank we need to compute the number of answers
                if( !isset($fillInBlanksTotal[$i]) )     $fillInBlanksTotal[$i] = 1;
                else                                     $fillInBlanksTotal[$i]++;

                // change blank number until we have meet all blank for the same answer
                if( $i == $nbrBlanks )     $i = 1;
                else                    $i++;
            }

            $displayedStatement = $question->selectDescription().'<br /><br />'."\n".'<i>'.$answer.'</i>'."\n";
        }
        elseif( $question->selectType() == MATCHING )
        {
              $displayedStatement = $question->selectDescription();

            // get left and right proposals
            $objAnswer = new Answer($question->selectId());
            $nbrAnswers = $objAnswer->selectNbrAnswers();

            $nbrColumn = 1; // at least one column for headers
            $nbrRow = 1; // at least one row for headers
            for($answerId = 1;$answerId <= $nbrAnswers;$answerId++)
            {
                $answer = $objAnswer->selectAnswer($answerId);
                $answerCorrect = $objAnswer->isCorrect($answerId);

                if(!$answerCorrect)
                {
                    // right column , will be displayed in top headers
                    $columnTitlePosition[$answerId] = $nbrColumn++;// to know in which column is which id
                    $results[0][$columnTitlePosition[$answerId]] = $answer;
                   }
                   else
                   {
                    // left column , will be displayed in left headers
                    $rowTitlePosition[$answerId] = $nbrRow++; // to know in which row is which id
                    $results[$rowTitlePosition[$answerId]][0] = $answer;
                }
               }
               // cancel last iteration
            $nbrColumn--; $nbrRow--;

              // get given answers
            $sql = "SELECT `TEA`.`answer`, COUNT(`TEA`.`answer`) as `nbr`
                        FROM `".$tbl_quiz_question."` AS `Q` ,
                            `".$tbl_quiz_rel_test_question."` AS `RTQ` ,
                            `".$tbl_quiz_answer."` AS `A`
                    LEFT JOIN `".$tbl_track_e_exercises."` AS `TE`
                        ON `TE`.`exe_exo_id` = `RTQ`.`exercice_id`
                    LEFT JOIN `".$tbl_track_e_exe_details."` AS `TED`
                        ON `TED`.`exercise_track_id` = `TE`.`exe_id`
                        AND `TED`.`question_id` = `Q`.`id`
                    LEFT JOIN `".$tbl_track_e_exe_answers."` AS `TEA`
                        ON `TEA`.`details_id` = `TED`.`id`
                    WHERE `Q`.`id` = `RTQ`.`question_id`
                        AND `Q`.`id` = `A`.`question_id`
                        AND `Q`.`id` = ".(int)$question->selectId()."
                        AND `RTQ`.`exercice_id` = ".(int)$_REQUEST['exo_id']."
                        AND (`TEA`.`answer` = `A`.`id`
                        OR `TEA`.`answer` IS NULL)
                    GROUP BY `TEA`.`answer`
                    ORDER BY `A`.`r_position` ASC";

             $answers_details = claro_sql_query_fetch_all($sql);

             foreach( $answers_details as $answer_details )
             {
                if( !is_null($answer_details['answer']) )
                {
                    list($leftAnswerId,$rightAnswerId) = explode('-',$answer_details['answer']);
                    if( !empty($leftAnswerId) && !empty($rightAnswerId) )
                        $results[$rowTitlePosition[$leftAnswerId]][$columnTitlePosition[$rightAnswerId]] = $answer_details['nbr'];
                   }
            }
        }


        //-- DISPLAY (common)
          //-- display a resume of the selected question
        echo '<p><b>'.$question->selectTitle().'</b></p>'."\n"
            .'<blockquote>'.$displayedStatement.'</blockquote>'."\n\n"
            .'<center>';
        //-- DISPLAY (by question type)
        // prepare list to display
        if( $question->selectType() == UNIQUE_ANSWER || $question->selectType() == MULTIPLE_ANSWER || $question->selectType() == TRUEFALSE )
        {
            // display tab header
            echo '<table class="claroTable emphaseLine" width="100%" border="0" cellspacing="2">'."\n"
                .'<tr class="headerX" align="center" valign="top">'."\n"
                .'<th>'.get_lang('ExpectedChoice').'</th>'."\n"
                .'<th width="60%">'.get_lang('Answer').'</th>'."\n"
                .'<th colspan="2">#</th>'."\n"
                  .'</tr>'."\n"
                  .'<tbody>'."\n\n";

            // display tab content
            foreach( $results as $result )
            {
                echo      '<tr>'."\n"
                        .'<td align="center">';
                // expected choice image
                echo '<img src="'.$imgRepositoryWeb;
                // choose image to display
                if ($question->selectType() != MULTIPLE_ANSWER) echo 'radio';
                else                                            echo'checkbox';
                if( $result['correct'] )    echo '_on';
                else                        echo '_off';
                echo '.gif" />';

                // compute pourcentage
                if( $result['nbr'] == 0 )    $pourcent = 0;
                else                        $pourcent = round(100 * $result['nbr'] / $multipleChoiceTotal);

                echo '</td>'."\n"
                          .'<td>'.$result['reponse'].'</td>'."\n"
                          .'<td align="right">'.claro_disp_progress_bar($pourcent,1).'</td>'."\n"
                        .'<td align="left"><small>'.$result['nbr'].'&nbsp;(&nbsp;'.$pourcent.'%&nbsp;)</small></td>'."\n"
                        .'</tr>'."\n";
            }

            // foot of table
            echo '</tbody>'."\n".'</table>'."\n\n";

        }
        elseif( $question->selectType() == FILL_IN_BLANKS )
        {
            $i = 1;
            foreach( $blanks as $blank )
            {
                  echo '<table class="claroTable emphaseLine" width="100%" border="0" cellspacing="2">'."\n"
                      .'<tr class="headerX">'."\n"
                    .'<th>'.$blank.'</th>'."\n"
                    .'<th width="20%" colspan="2">#</th>'."\n"
                      .'</tr>'."\n";

                if( isset($results[$i]) )
                {
                    // sort array on answer given
                    ksort($results[$i]);
                    foreach( $results[$i] as $result )
                    {
                        // check if we need to use the 'correct' css class
                        if( $result['answer'] == $blank )    $class = ' class="correct" ';
                        else                                $class = '';

                        echo '<tr >'
                            .'<td '.$class.'>';
                        if( empty($result['answer']) )     echo '('.get_lang('Empty').')';
                        else                             echo $result['answer'];

                        if($result['nbr'] == 0 )    $pourcent = 0;
                        else                        $pourcent = round(100 * $result['nbr'] / $fillInBlanksTotal[$i]);

                        echo '</td>'."\n"
                            .'<td align="right">'.claro_disp_progress_bar($pourcent,1).'</td>'."\n"
                            .'<td align="left"><small>'.$result['nbr'].'&nbsp;(&nbsp;'.$pourcent.'%&nbsp;)</small></td>'."\n"
                            .'</tr>';
                    }
                   }
                   else
                   {
                    echo '<tr >'
                        .'<td colspan="2" align="center">'.get_lang('NoResult').'</td>'."\n"
                        .'</tr>';
                }
                echo '</table>'."\n\n"
                    .'<br />'."\n\n";

                $i++;
            }
        }
        elseif( $question->selectType() == MATCHING )
        {
            // for each left proposal display the number of time each right proposal has been choosen
            echo '<table class="claroTable emphaseLine" border="0" cellspacing="2">'."\n"
                  .'<tr class="headerX">'."\n"
                .'<td>&nbsp;</td>'."\n";

            // these two values are used for numbering of columns and lines
            $letter = 'A';
            $number = 1;
            // display top headers
            for( $i = 1; $i <= $nbrColumn; $i++ )
            {
                echo '<th><b>'.$letter++.'.</b> '.$results[0][$i].'</th>'."\n";
            }

            echo '</tr>'."\n";

            for( $i = 1; $i <= $nbrRow; $i++ )
            {
                echo '<tr class="headerY">'."\n\n"
                    .'<th><b>'.$number++.'.</b> '.$results[$i][0].'</th>'."\n";

                for( $j = 1; $j <= $nbrColumn; $j++ )
                {
                    echo '<td align="center">';
                    if( !empty($results[$i][$j]) ) echo $results[$i][$j]; else echo '0';
                    echo '</td>'."\n";
                }
                echo '</tr>'."\n\n";
            }
            echo '</table>'."\n\n";
        }
         echo '</center>'."\n".'<br />'."\n";
    } // end of foreach( $questionIdsToShow as $questionId )

    echo $backLink;
}
// not allowed
else
{
    if(!$is_trackingEnabled)
    {
        echo get_lang('TrackingDisabled');
    }
    else
    {
        echo get_lang('Not allowed');
    }
}

include($includePath."/claro_init_footer.inc.php");
?>
