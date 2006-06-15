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

if( isset($_REQUEST['exId']) && is_numeric($_REQUEST['exId']) ) 
{	
	$exId = (int) $_REQUEST['exId'];
}
else															
{
	header("Location: ../exercise/exercise.php");
    exit();
}


include('../exercise/lib/question.class.php');
//include('../exercise/lib/answer.class.php');

/**
 * DB tables definition
 */

$tbl_mdb_names = claro_sql_get_main_tbl();
$tbl_rel_course_user = $tbl_mdb_names['rel_course_user'  ];
$tbl_user            = $tbl_mdb_names['user'             ];

$tbl_cdb_names = claro_sql_get_course_tbl();
$tbl_qwz_question 				= $tbl_cdb_names['qwz_question'];
$tbl_qwz_rel_exercise_question 	= $tbl_cdb_names['qwz_rel_exercise_question'];
//$tbl_quiz_answer        = $tbl_cdb_names['quiz_answer'          ];
$tbl_qwz_answer_multiple_choice 	= $tbl_cdb_names['qwz_answer_multiple_choice'];
$tbl_qwz_answer_truefalse 			= $tbl_cdb_names['qwz_answer_truefalse'];
$tbl_qwz_answer_fib 				= $tbl_cdb_names['qwz_answer_fib'];
$tbl_qwz_answer_matching 			= $tbl_cdb_names['qwz_answer_matching'];

$tbl_track_e_exercises     = $tbl_cdb_names['track_e_exercices'];
$tbl_track_e_exe_details = $tbl_cdb_names['track_e_exe_details'];
$tbl_track_e_exe_answers = $tbl_cdb_names['track_e_exe_answers'];

$is_allowedToTrack = $is_courseAdmin;
 
// bredcrump
if( isset($_REQUEST['src']) && $_REQUEST['src'] == 'ex' )
{
    $interbredcrump[]= array ('url'=>'../exercise/exercise.php', 'name'=> get_lang('Exercises'));
    $src = '&src=ex';
}
else
{
 $interbredcrump[]= array ('url'=>'courseLog.php', 'name'=> get_lang('Statistics'));
    $src = '';
}
$interbredcrump[]= array ('url'=>'exercises_details.php?exId='.$exId.$src, 'name'=> get_lang('Statistics of exercise'));
$nameTools = get_lang('Statistics of question');


// if the question_id is not set display the stats of all questions of this exercise
if( empty($_REQUEST['question_id']) )
{
    // show the list of all question when no one is specified
    // a contribution of Jérémy Audry
    $sql = "SELECT `questionId`
            FROM `".$tbl_qwz_rel_exercise_question."`
            WHERE `exerciceId` = ".(int) $exId;
            
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
echo claro_html_tool_title($titleTab);

// build back link
$backLink = "\n\n".'<small><a href="./exercises_details.php?exId='.$exId.$src.'">&lt;&lt;&nbsp;'.get_lang('Back').'</a></small>'."\n\n";
echo $backLink;

if($is_allowedToTrack && get_conf('is_trackingEnabled'))
{

    foreach( $questionIdsToShow as $questionId )
    {
        // get infos about the question
        $question = new Question();
        
        if( !$question->load($questionId) ) break; 
        
        // prepare list to display
        if( $question->getType() == 'MCUA'
            || $question->getType() == 'MCMA' )
        {
            // get the list of all possible answer and the number of times it was choose
            $sql = "SELECT `A`.`id`, `A`.`answer`, `A`.`correct` , COUNT(`TEA`.`answer`) as `nbr`
                        FROM (`".$tbl_qwz_question."` AS `Q` ,
                            `".$tbl_qwz_rel_exercise_question."` AS `RTQ` ,
                            `".$tbl_qwz_answer_multiple_choice."` AS `A`)
                    LEFT JOIN `".$tbl_track_e_exercises."` AS `TE`
                        ON `TE`.`exe_exo_id` = `RTQ`.`exerciseId`
                    LEFT JOIN `".$tbl_track_e_exe_details."` AS `TED`
                        ON `TED`.`exercise_track_id` = `TE`.`exe_id`
                        AND `TED`.`question_id` = `Q`.`id`
                    LEFT JOIN `".$tbl_track_e_exe_answers."` AS `TEA`
                        ON `TEA`.`details_id` = `TED`.`id`
                        AND `TEA`.`answer` = `A`.`id`
                    WHERE `Q`.`id` = `RTQ`.`questionId`
                        AND `Q`.`id` = `A`.`questionId`
                        AND `Q`.`id` = ".(int) $questionId."
                        AND `RTQ`.`exerciseId` = ".(int) $exId."
                        AND (`TEA`.`answer` = `A`.`id`
                        OR `TEA`.`answer` IS NULL)
                    GROUP BY `A`.`id`";

			$results = claro_sql_query_fetch_all($sql);

            // we need to know the total number of answer given
            $multipleChoiceTotal = 0;
            foreach( $results as $result )
            {
                $multipleChoiceTotal += $result['nbr'];
            }

            $displayedStatement = $question->getDescription();
        }
        elseif( $question->getType() == 'TF' )
        {
            // get the list of all possible answer and the number of times it was choose
            $sql = "SELECT `TEA`.`answer`, COUNT(`TEA`.`answer`) as `nbr`
                        FROM (`".$tbl_qwz_question."` AS `Q` ,
                            `".$tbl_qwz_rel_exercise_question."` AS `RTQ`)
                    LEFT JOIN `".$tbl_track_e_exercises."` AS `TE`
                        ON `TE`.`exe_exo_id` = `RTQ`.`exerciseId`
                    LEFT JOIN `".$tbl_track_e_exe_details."` AS `TED`
                        ON `TED`.`exercise_track_id` = `TE`.`exe_id`
                        AND `TED`.`question_id` = `Q`.`id`
                    LEFT JOIN `".$tbl_track_e_exe_answers."` AS `TEA`
                        ON `TEA`.`details_id` = `TED`.`id`
                    WHERE `Q`.`id` = `RTQ`.`questionId`
                        AND `Q`.`id` = ".(int) $questionId."
                        AND `RTQ`.`exerciseId` = ".(int) $exId."
						AND ( `TEA`.`answer` = 'TRUE' OR `TEA`.`answer` = 'FALSE' )
                    GROUP BY `TEA`.`answer`";

			$results = claro_sql_query_fetch_all($sql);
			
            // we need to know the total number of answer given
            $multipleChoiceTotal = 0;
            foreach( $results as $result )
            {
                $multipleChoiceTotal += $result['nbr'];
            }

            $displayedStatement = $question->getDescription();
        }
        elseif( $question->getType() == 'FIB' )
        {
            // get the list of all word used in each blank
            // we take id to have a unique key for answer, answer with same id are
            // from the same attempt
            $sql = "SELECT `TED`.`id`,`TEA`.`answer`
                    FROM ( 
                        `".$tbl_qwz_rel_exercise_question."` AS `RTQ`,
                        `".$tbl_qwz_answer_fib."` AS `A`,
                        `".$tbl_track_e_exercises."` AS `TE`,
                        `".$tbl_track_e_exe_details."` AS `TED`,
                        `".$tbl_user."` AS `U`
                       )
                    LEFT JOIN `".$tbl_track_e_exe_answers."` AS `TEA`
                        ON `TEA`.`details_id` = `TED`.`id`
                    WHERE `RTQ`.`questionId` = ".(int) $questionId."
                        AND `RTQ`.`questionId` = `A`.`questionId`
                        AND `RTQ`.`questionId` = `TED`.`question_id`
                        AND `RTQ`.`exerciseId` = `TE`.`exe_exo_id`
                        AND `TE`.`exe_id` = `TED`.`exercise_track_id`
                        AND `U`.`user_id` = `TE`.`exe_user_id`
                        AND `RTQ`.`exerciseId` = '".(int) $exId."'
                    ORDER BY `TED`.`id` ASC, `TEA`.`id` ASC";

            $answers_details = claro_sql_query_fetch_all($sql);
			
			$answerText = $question->answer->answerText;
			$answerList = $question->answer->answerList;
			
			$nbrBlanks = count($answerList);
			
			
            $fillInBlanksTotal = array();
            $results = array();
			// in $answers_details we have the list of answers given, each line is one blank filling
			// all blanks of each answers are in the list so we have
			// attempt-blank1 ; attempt1-blank2; attempt2-blank1; attempt2-blank2; ...
			// so we will have to extract and group all blank1 and blank2
			$i = 1;
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
                if( $i == $nbrBlanks )  $i = 1;
                else                    $i++;
            }

            $displayedStatement = $question->getDescription().'<br /><br />'."\n".'<i>'.$answerText.'</i>'."\n";
        }
        elseif( $question->getType() == 'MATCHING' )
        {
			$displayedStatement = $question->getDescription();

            // get left and right proposals
			$leftList = $question->answer->leftList;
			$rightList = $question->answer->rightList;

            $nbrColumn = 0; // at least one column for headers
            $nbrRow = 0; // at least one row for headers
            
            foreach( $rightList as $rightElt )
            {
            	$nbrColumn++;
            	
                // right column , will be displayed in top headers
                $columnTitlePosition[$rightElt['code']] = $nbrColumn;// to know in which column is which id
                $results[0][$nbrColumn] = $rightElt['answer'];
			}
			
			foreach( $leftList as $leftElt )
			{
				$nbrRow++;
				
                // left column , will be displayed in left headers
                $rowTitlePosition[$leftElt['code']] = $nbrRow; // to know in which row is which id
                $results[$nbrRow][0] = $leftElt['answer'];
			}


			// get given answers
            $sql = "SELECT `TEA`.`answer`, COUNT(`TEA`.`answer`) as `nbr`
                        FROM (`".$tbl_qwz_question."` AS `Q` ,
                            `".$tbl_qwz_rel_exercise_question."` AS `RTQ`)
                    LEFT JOIN `".$tbl_track_e_exercises."` AS `TE`
                        ON `TE`.`exe_exo_id` = `RTQ`.`exerciseId`
                    LEFT JOIN `".$tbl_track_e_exe_details."` AS `TED`
                        ON `TED`.`exercise_track_id` = `TE`.`exe_id`
                        AND `TED`.`question_id` = `Q`.`id`
                    LEFT JOIN `".$tbl_track_e_exe_answers."` AS `TEA`
                        ON `TEA`.`details_id` = `TED`.`id`
                    WHERE `Q`.`id` = `RTQ`.`questionId`
                        AND `Q`.`id` = ".(int) $questionId."
                        AND `RTQ`.`exerciseId` = ".(int) $exId."
                    GROUP BY `TEA`.`answer`";

             $answers_details = claro_sql_query_fetch_all($sql);

             foreach( $answers_details as $answer_details )
             {
                if( !is_null($answer_details['answer']) )
                {
                    list($leftAnswerId,$rightAnswerId) = explode('-',$answer_details['answer']);
                    
                    if( !empty($leftAnswerId) && !empty($rightAnswerId) )
                    {
                        $results[$rowTitlePosition[$leftAnswerId]][$columnTitlePosition[$rightAnswerId]] = $answer_details['nbr'];
                    }
				}
            }
        }


        //-- DISPLAY (common)
          //-- display a resume of the selected question
        echo '<p><b>'.$question->getTitle().'</b></p>'."\n"
            .'<blockquote>'.$displayedStatement.'</blockquote>'."\n\n"
            .'<center>';
        //-- DISPLAY (by question type)
        // prepare list to display
        if( $question->getType() == 'MCUA' || $question->getType() == 'MCMA' )
        {
            // display tab header
            echo '<table class="claroTable emphaseLine" width="100%" border="0" cellspacing="2">'."\n"
                .'<tr class="headerX" align="center" valign="top">'."\n"
                .'<th>'.get_lang('Expected choice').'</th>'."\n"
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
                if ($question->getType() != 'MCMA') echo 'radio';
                else                                echo 'checkbox';
                
                if( $result['correct'] )    echo '_on';
                else                        echo '_off';
                
                echo '.gif" />';

                // compute pourcentage
                if( $result['nbr'] == 0 )	$pourcent = 0;
                else                        $pourcent = round(100 * $result['nbr'] / $multipleChoiceTotal);

                echo '</td>'."\n"
                          .'<td>'.$result['answer'].'</td>'."\n"
                          .'<td align="right">'.claro_html_progress_bar($pourcent,1).'</td>'."\n"
                        .'<td align="left"><small>'.$result['nbr'].'&nbsp;(&nbsp;'.$pourcent.'%&nbsp;)</small></td>'."\n"
                        .'</tr>'."\n";
            }

            // foot of table
            echo '</tbody>'."\n".'</table>'."\n\n";

        }
        elseif( $question->getType() == 'TF' )
        {
            // display tab header
            echo '<table class="claroTable emphaseLine" width="100%" border="0" cellspacing="2">'."\n"
                .'<tr class="headerX" align="center" valign="top">'."\n"
                .'<th>'.get_lang('Expected choice').'</th>'."\n"
                .'<th width="60%">'.get_lang('Answer').'</th>'."\n"
                .'<th colspan="2">#</th>'."\n"
                  .'</tr>'."\n"
                  .'<tbody>'."\n\n";

			$truePourcent = 0; $trueSelected = 0;
			$falsePourcent = 0; $falseSelected = 0; 
            foreach( $results as $result )
            {
            	if( $result['answer'] == 'TRUE' )
            	{
            		// compute pourcentage
		            if( $result['nbr'] > 0 ) $truePourcent = round(100 * $result['nbr'] / $multipleChoiceTotal);
		            $trueSelected = $result['nbr'];

            	}
            	elseif( $result['answer'] == 'FALSE' )
            	{
            		// compute pourcentage
		            if( $result['nbr'] > 0 ) $falsePourcent = round(100 * $result['nbr'] / $multipleChoiceTotal);
		            $falseSelected = $result['nbr'];
            	}
            	// else ignore
            }
            
            // TRUE
            echo      '<tr>'."\n"
                    .'<td align="center">';
            // expected choice image
            echo '<img src="'.$imgRepositoryWeb;
            // choose image to display
            
            if( $question->answer->correctAnswer == 'TRUE' )    echo 'radio_on.gif';
            else												echo 'radio_off.gif';
            
            echo '" />';

            

            echo '</td>'."\n"
                      .'<td>'.get_lang('True').'</td>'."\n"
                      .'<td align="right">'.claro_html_progress_bar($truePourcent,1).'</td>'."\n"
                    .'<td align="left"><small>'.$trueSelected.'&nbsp;(&nbsp;'.$truePourcent.'%&nbsp;)</small></td>'."\n"
                    .'</tr>'."\n";
            
            // FALSE
            echo      '<tr>'."\n"
                    .'<td align="center">';
            // expected choice image
            echo '<img src="'.$imgRepositoryWeb;
            // choose image to display
            
            if( $question->answer->correctAnswer == 'FALSE' )    echo 'radio_on.gif';
            else												echo 'radio_off.gif';
            
            echo '" />';

            

            echo '</td>'."\n"
                      .'<td>'.get_lang('False').'</td>'."\n"
                      .'<td align="right">'.claro_html_progress_bar($falsePourcent,1).'</td>'."\n"
                    .'<td align="left"><small>'.$falseSelected.'&nbsp;(&nbsp;'.$falsePourcent.'%&nbsp;)</small></td>'."\n"
                    .'</tr>'."\n";
            
                    
            // foot of table
            echo '</tbody>'."\n".'</table>'."\n\n";

        }
        elseif( $question->getType() == 'FIB' )
        {
            $i = 1;
            foreach( $answerList as $blank )
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
                        if( $result['answer'] == $blank )   $class = ' class="correct" ';
                        else                                $class = '';

                        echo '<tr >'
                            .'<td '.$class.'>';
                        if( empty($result['answer']) )     echo '('.get_lang('Empty').')';
                        else                             echo $result['answer'];

                        if($result['nbr'] == 0 )    $pourcent = 0;
                        else                        $pourcent = round(100 * $result['nbr'] / $fillInBlanksTotal[$i]);

                        echo '</td>'."\n"
                            .'<td align="right">'.claro_html_progress_bar($pourcent,1).'</td>'."\n"
                            .'<td align="left"><small>'.$result['nbr'].'&nbsp;(&nbsp;'.$pourcent.'%&nbsp;)</small></td>'."\n"
                            .'</tr>';
                    }
                   }
                   else
                   {
                    echo '<tr >'
                        .'<td colspan="2" align="center">'.get_lang('No result').'</td>'."\n"
                        .'</tr>';
                }
                echo '</table>'."\n\n"
                    .'<br />'."\n\n";

                $i++;
            }
        }
        elseif( $question->getType() == 'MATCHING' )
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
    if(!get_conf('is_trackingEnabled'))
    {
        echo get_lang('Tracking has been disabled by system administrator.');
    }
    else
    {
        echo get_lang('Not allowed');
    }
}

include($includePath."/claro_init_footer.inc.php");
?>
