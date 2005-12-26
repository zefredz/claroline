<?php // $Id$
/**
 * CLAROLINE 
 *
 * This page display global information about
 *
 * @version 1.7 $Revision$
 *
 * @copyright 2001, 2005 Universite catholique de Louvain (UCL)
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @author claro team <info@claroline.net>
 *
 */
require '../inc/claro_init_global.inc.php';

include('../exercice/question.class.php');
include('../exercice/answer.class.php');
include('../exercice/exercise.lib.php');

// all I need from REQUEST is the track_id and it is required
if( !isset($_REQUEST['track_id']) )  header("Location: ../exercice/exercice.php");

// answer types
define('UNIQUE_ANSWER',     1);
define('MULTIPLE_ANSWER',2);
define('FILL_IN_BLANKS', 3);
define('MATCHING',         4);
define('TRUEFALSE',     5);

/**
 * DB tables definition
 */
$tbl_mdb_names = claro_sql_get_main_tbl();
$tbl_rel_course_user = $tbl_mdb_names['rel_course_user'  ];
$tbl_user            = $tbl_mdb_names['user'             ];

$tbl_cdb_names = claro_sql_get_course_tbl();
$tbl_quiz_test      = $tbl_cdb_names['quiz_test'              ];
$tbl_quiz_answer             = $tbl_cdb_names['quiz_answer'            ];
$tbl_quiz_question           = $tbl_cdb_names['quiz_question'          ];
$tbl_quiz_rel_test_question  = $tbl_cdb_names['quiz_rel_test_question' ];
$tbl_track_e_exercices = $tbl_cdb_names['track_e_exercices'];
$tbl_track_e_exe_details = $tbl_cdb_names['track_e_exe_details'];
$tbl_track_e_exe_answers = $tbl_cdb_names['track_e_exe_answers'];

include ($includePath . '/lib/statsUtils.lib.inc.php');

//-- get infos
// get infos about the exercise
// get infos about the user
// get infos about the exercise attempt
$sql = "SELECT `E`.`titre`, `E`.`show_answer`, `E`.`max_attempt`,
                `U`.`user_id`, `U`.`nom` as `lastname`, `U`.`prenom` as `firstname`,
                `TE`.`exe_exo_id`, `TE`.`exe_result`, `TE`.`exe_time`, `TE`.`exe_weighting`,
                UNIX_TIMESTAMP(`TE`.`exe_date`) AS `unix_exe_date`
        FROM `".$tbl_quiz_test."` as `E`, `".$tbl_track_e_exercices."` as `TE`, `".$tbl_user."` as `U`
        WHERE `E`.`id` = `TE`.`exe_exo_id`
        AND `TE`.`exe_user_id` = `U`.`user_id`
        AND `TE`.`exe_id` = ".(int)$_REQUEST['track_id'];

$result = claro_sql_query_fetch_all($sql);

if( $result )
{
    $thisAttemptDetails = $result[0];
}
else
{
    // sql error, let's get out of here !
    header("Location: ../exercice/exercice.php");
    die();
}

//-- permissions
// if a user want to see its own results the teacher must have allowed the students
// to see the answers at the end of the exercise
$is_allowedToTrack = false;

if( isset($_uid) )
{
      if( $is_courseAdmin )
    {
        $is_allowedToTrack = true;
    }
    elseif( $_uid == $thisAttemptDetails['user_id'] )
    {
        if( $thisAttemptDetails['show_answer'] == 'ALWAYS' )
        {
            $is_allowedToTrack = true;
        }
        elseif( $thisAttemptDetails['show_answer'] == 'LASTTRY' )
        {
            // we must check that user has at least "max_attempt" results
            $sql = "SELECT COUNT(`exe_id`)
                    FROM `".$tbl_track_e_exercices."`
                    WHERE `exe_user_id` = ".$_uid."
                    AND `exe_exo_id` = ".$thisAttemptDetails['exe_exo_id'];
            $userAttempts = claro_sql_query_get_single_value($sql);

            if( $userAttempts >= $thisAttemptDetails['max_attempt'] )
            {
                $is_allowedToTrack = true;
            }
            else
            {
                $dialogBox = get_lang('TrackNotEnoughAttempts');
            }

        }
        else
        {
              // user cannot see its full results if show_answer == 'NEVER'
            $dialogBox = get_lang('CannotSeeExerciseDetails');
        }
    }
}


$interbredcrump[]= array ('url'=>'../exercice/exercice.php', 'name'=> get_lang('Exercices'));

$backLink = '<p><small><a href="../exercice/exercice.php">&lt;&lt;&nbsp;' . get_lang('Back') . '</a></small></p>' . "\n\n";

$nameTools = get_lang('StatsOfExerciseAttempt');

include($includePath . '/claro_init_header.inc.php');
// display title
$titleTab['mainTitle'] = $nameTools;

echo claro_disp_tool_title($titleTab);

echo $backLink;

if( $is_allowedToTrack && $is_trackingEnabled )
{
    // display infos about the details ...
    echo '<ul>' . "\n"
    .    '<li>' . get_lang('LastName') . ' : '.$thisAttemptDetails['lastname'] . '</li>' . "\n"
    .    '<li>' . get_lang('FirstName') . ' : '.$thisAttemptDetails['firstname'] . '</li>' . "\n"
    .    '<li>' . get_lang('Date') . ' : ' . claro_disp_localised_date($dateTimeFormatLong,$thisAttemptDetails['unix_exe_date']) . '</li>' . "\n"
    .    '<li>' . get_lang('Score') . ' : ' . $thisAttemptDetails['exe_result'] . '/' . $thisAttemptDetails['exe_weighting'] . '</li>' . "\n"
    .    '<li>' . get_lang('ExeTime') . ' : ' . claro_disp_duration($thisAttemptDetails['exe_time']) . '</li>' . "\n"
    .    '</ul>' . "\n\n"
    ;

    // get all question that user get for this attempt
    $sql = "SELECT TD.`id`, TD.`question_id`, TD.`result`
            FROM `".$tbl_track_e_exe_details."` as TD
            WHERE `exercise_track_id` = ".(int)$_REQUEST['track_id'];

    $questions = claro_sql_query_fetch_all($sql);

    $i = 0;
    $totalScore = 0;
    $totalWeighting = 0;
    
    // for each question the user get
    foreach( $questions as $question )
    {
        $objQuestionTmp = new Question();

        // read question and skip display if question doesn't not exists
        if( !$objQuestionTmp->read($question['question_id']) ) continue;

        $questionTitle = $objQuestionTmp->selectTitle();
        $questionStatement = $objQuestionTmp->selectDescription();
        $attachedFile = $objQuestionTmp->selectAttachedFile();
        $questionWeighting = $objQuestionTmp->selectWeighting();
        $answerType = $objQuestionTmp->selectType();

        // destruction of the Question object
        unset($objQuestionTmp);
        
        if($answerType == UNIQUE_ANSWER || $answerType == MULTIPLE_ANSWER || $answerType == TRUEFALSE)
        {
            $colspan = 4;
        }
        elseif($answerType == MATCHING)
        {
            $colspan = 2;
        }
        else
        {
            $colspan = 1;
        }
?>

  <table width="100%" cellpadding="4" cellspacing="2" border="0" class="claroTable">
  <tr class="headerX">
  <th colspan="<?php echo $colspan; ?>">
    <?php echo get_lang('Question').' '.($i+1); ?>
  </th>
</tr>
<tfoot>
<tr>
  <td colspan="<?php echo $colspan; ?>">
    <?php echo $questionTitle; ?>
    <blockquote>
    <?php
        echo $questionStatement;

        if( !empty($attachedFile) )
        {
            echo '<br />' . display_attached_file($attachedFile);
        }
    ?>
    </blockquote>
  </td>
</tr>

<?php
        if($answerType == UNIQUE_ANSWER || $answerType == MULTIPLE_ANSWER || $answerType == TRUEFALSE)
        {
?>

<tr>
  <td width="5%" valign="top" align="center" nowrap="nowrap">
    <small><i><?php echo get_lang('Choice'); ?></i></small>
  </td>
  <td width="5%" valign="top" nowrap="nowrap">
    <small><i><?php echo get_lang('ExpectedChoice'); ?></i></small>
  </td>
  <td width="45%" valign="top">
    <small><i><?php echo get_lang('Answer'); ?></i></small>
  </td>
  <td width="45%" valign="top">
    <small><i><?php echo get_lang('Comment'); ?></i></small>
  </td>
</tr>

<?php
        }
        elseif($answerType == FILL_IN_BLANKS)
        {
?>

<tr>
  <td>
    <small><i><?php echo get_lang('Answer'); ?></i></small>
  </td>
</tr>

<?php
        }
        else
        {
?>

<tr>
  <td width="50%">
    <small><i><?php echo get_lang('ElementList'); ?></i></small>
  </td>
  <td width="50%">
    <small><i><?php echo get_lang('CorrespondsTo'); ?></i></small>
  </td>
</tr>

<?php
        }
        
        $objAnswerTmp = new Answer($question['question_id']);

          $nbrAnswers = $objAnswerTmp->selectNbrAnswers();

        // get the answers the user has gaven for this question
        $sql = "SELECT `answer`
                FROM `" . $tbl_track_e_exe_answers . "`
                WHERE `details_id` = " . (int) $question['id'];

        $answers = claro_sql_query_fetch_all($sql);
        
        if( $answerType == MULTIPLE_ANSWER || $answerType == MATCHING || $answerType == FILL_IN_BLANKS )
            $choice = array();
        elseif( $answerType == UNIQUE_ANSWER || $answerType == TRUEFALSE )
            $choice = "";
        
        
        foreach( $answers as $answer )
        {
            switch($answerType)
            {
                case TRUEFALSE : // no break, execute UNIQUE_ANSWER instructions
                case UNIQUE_ANSWER :      $choice = $answer['answer'];
                                        break;
                case MULTIPLE_ANSWER :  $choice[$answer['answer']] = 1;
                                        break;
                case FILL_IN_BLANKS  :  $choice[] = $answer['answer'];
                                        break;
                case MATCHING :         list($leftProp,$userChoice) = explode('-',$answer['answer']);
                                        $choice[$leftProp] = $userChoice;
                                        break;
            }
        }

        $questionScore = 0;
        
        for($answerId = 1;$answerId <= $nbrAnswers;$answerId++)
        {
            $answer = $objAnswerTmp->selectAnswer($answerId);
            $answerComment = $objAnswerTmp->selectComment($answerId);
            $answerCorrect = $objAnswerTmp->isCorrect($answerId);
            $answerWeighting = $objAnswerTmp->selectWeighting($answerId);

            $studentChoice = ''; // init to empty string, will be overwritten when a answer has been given
            
            switch($answerType)
            {
                 // for unique answer or true/false (true/false IS a unique answer exercise)
                case TRUEFALSE : // no break, execute UNIQUE_ANSWER instructions
                case UNIQUE_ANSWER :    $studentChoice = ($choice == $answerId)?1:0;

                                        if($studentChoice)
                                        {
                                            // if this answer has been selected by the user
                                              $questionScore += $answerWeighting;
                                            $totalScore += $answerWeighting;
                                        }
                                        break;
                // for multiple answers
                case MULTIPLE_ANSWER :  if( isset( $choice[$answerId]) ) $studentChoice = $choice[$answerId];
                                        if($studentChoice)
                                        {
                                            $questionScore += $answerWeighting;
                                            $totalScore += $answerWeighting;
                                        }

                                        break;
                // for fill in the blanks
                case FILL_IN_BLANKS :    // splits text and weightings that are joined with the character '::'
                                        list($answer,$answerWeighting) = explode('::',$answer);

                                        // splits weightings that are joined with a comma
                                        $answerWeighting = explode(',',$answerWeighting);

                                        // we save the answer because it will be modified
                                        $temp = $answer;

                                        $answer = '';

                                        $j = 0;

                                        // the loop will stop at the end of the text
                                        while(1)
                                        {
                                            // quits the loop if there are no more blanks
                                            if(($pos = strpos($temp,'[')) === false)
                                            {
                                                // adds the end of the text
                                                $answer .= $temp;
                                                break;
                                            }

                                            // adds the piece of text that is before the blank and ended by [
                                            $answer .= substr($temp,0,$pos+1);

                                            $temp = substr($temp,$pos+1);

                                            // quits the loop if there are no more blanks
                                            if(($pos = strpos($temp,']')) === false)
                                            {
                                                // adds the end of the text
                                                $answer .= $temp;
                                                break;
                                            }

                                               if( !isset($choice[$j]) ) $choice[$j] = '';

                                            // if the word entered by the student IS the same as the one defined by the professor
                                            if(strtolower(substr($temp,0,$pos)) == strtolower($choice[$j]))
                                            {
                                                // gives the related weighting to the student
                                                $questionScore += $answerWeighting[$j];

                                                // increments total score
                                                $totalScore += $answerWeighting[$j];

                                                // adds the word in green at the end of the string
                                                $answer .= $choice[$j];

                                            }
                                            // else if the word entered by the student IS NOT the same as the one defined by the professor
                                            elseif(!empty($choice[$j]))
                                            {
                                                // adds the word in red at the end of the string, and strikes it
                                                $answer .= '<span class="error"><s>' . $choice[$j] . '</s></span>';
                                            }
                                            else
                                            {
                                                // adds a tabulation if no word has been typed by the student
                                                $answer .= '&nbsp;&nbsp;&nbsp;';
                                            }

                                            // adds the correct word, followed by ] to close the blank
                                            $answer .= ' / <span class="correct"><b>' . substr($temp,0,$pos) . '</b></span>]';

                                            $j++;

                                            $temp = substr($temp,$pos+1);
                                        }

                                        break;
                // for matching
                case MATCHING :         // in matching when $answerCorrect is true ( != 0 )
                                        // it means that the answer is a LEFT column proposal
                                        if($answerCorrect)
                                        {
                                            if( isset($choice[$answerId]) && $answerCorrect == $choice[$answerId] )
                                            {
                                                $questionScore += $answerWeighting;
                                                $totalScore += $answerWeighting;
                                                $choice[$answerId] = $matching[$choice[$answerId]];

                                            }
                                            elseif(!isset($choice[$answerId]))
                                            {
                                                $choice[$answerId] = '&nbsp;&nbsp;&nbsp;';
                                            }
                                            elseif( isset($choice[$answerId]) && isset($matching[$choice[$answerId]])  )
                                            {
                                                $choice[$answerId] = '<span class="error"><s>' . $matching[$choice[$answerId]] . '</s></span>';
                                            }
                                        }
                                        else
                                        {
                                            // in matching $answerCorrect == 0 it means that the answer is a
                                            // right column proposal
                                            $matching[$answerId] = $answer;
                                        }
                                        break;
            }    // end switch()


            if( $answerType != MATCHING || $answerCorrect )
            {
                if($answerType == UNIQUE_ANSWER || $answerType == MULTIPLE_ANSWER || $answerType == TRUEFALSE)
                {

?>

<tr>
  <td width="5%" align="center">
    <img src="<?php echo $imgRepositoryWeb ?><?php echo ($answerType != MULTIPLE_ANSWER)?'radio':'checkbox'; echo $studentChoice?'_on':'_off'; ?>.gif" border="0">
  </td>
  <td width="5%" align="center">
    <img src="<?php echo $imgRepositoryWeb ?><?php echo ($answerType != MULTIPLE_ANSWER)?'radio':'checkbox'; echo $answerCorrect?'_on':'_off'; ?>.gif" border="0">
  </td>
  <td width="45%">
    <?php echo $answer; ?>
  </td>
  <td width="45%">
    <?php if($studentChoice) echo claro_parse_user_text($answerComment); else echo '&nbsp;'; ?>
  </td>
</tr>

<?php
                }
                elseif($answerType == FILL_IN_BLANKS)
                {
?>

<tr>
  <td>
    <?php echo claro_parse_user_text($answer); ?>
  </td>
</tr>

<?php
                }
                else
                {
?>

<tr>
  <td width="50%">
    <?php echo $answer; ?>
  </td>
  <td width="50%">
    <?php echo $choice[$answerId]; ?> / <span class="correct"><b><?php echo $matching[$answerCorrect]; ?></b></span>
  </td>
</tr>

<?php
                }
            } // end of if( $answerType != MATCHING || $answerCorrect )
        }    // end for()
?>
<tr>
  <td colspan="<?php echo $colspan; ?>" align="right">
    <b><?php echo get_lang('Score')." : ".$questionScore."/".$questionWeighting; ?></b>
  </td>
</tr>
</tfoot>
</table>

<?php
        // destruction of Answer
        unset($objAnswerTmp);

        $i++;

        $totalWeighting += $questionWeighting;
    }    // end foreach of questions

    // if there is no question (it is a old exercise attempt (before introduction of improved exo stats))
    if( $i == 0 )
    {
        echo '<p>'.get_lang('NoTrackingForExerciseAttempt').'</p>'."\n";
    }

    echo $backLink;

    // check if computed score is the same than the recorded total score, same for weighting
    // a difference could show a integrity error (i.e. an exercise that have been modified after the attempt)
    if( $thisAttemptDetails['exe_weighting'] != $totalWeighting || $thisAttemptDetails['exe_result'] != $totalScore )
    {
        // display msg of integrity problem
        echo '<p align="center">' . get_lang('TrackExerciseError') . '</p>' . "\n";
    }
}
// not allowed
else
{
    if(!$is_trackingEnabled)
    {
        $dialogBox = get_lang('TrackingDisabled');
    }

    echo claro_disp_message_box($dialogBox);
}

include $includePath . '/claro_init_footer.inc.php';
?>
