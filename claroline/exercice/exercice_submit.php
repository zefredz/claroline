<?php // $Id$
/**
 * CLAROLINE 
 *
 * This script allows to run an exercise. According to the exercise type, questions
 * can be on an unique page, or one per page with a Next button.
 *
 * One exercise may contain different types of answers (unique or multiple selection,
 * matching and fill in blanks).
 *
 * Questions are selected randomly or not.
 *
 * When the user has answered all questions and clicks on the button "Ok",
 * it goes to exercise_result.php
 *
 * Notice : This script is also used to show a question before modifying it by
 * the administrator
 *
 * @version version 1.8 $Revision$
 *
 * @copyright 2001, 2005 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE 
 *
 * @author claro team <info@claroline.net>
 */

include('exercise.class.php');
include('question.class.php');
include('answer.class.php');

include('exercise.lib.php');

// answer types
define('UNIQUE_ANSWER',    1);
define('MULTIPLE_ANSWER',    2);
define('FILL_IN_BLANKS',    3);
define('MATCHING',        4);
define('TRUEFALSE',     5);

// for help display in fill in blanks questions
define('TEXTFIELD_FILL', 1);
define('LISTBOX_FILL',    2);

require '../inc/claro_init_global.inc.php';

if ( !$_cid || !$is_courseAllowed ) claro_disp_auth_form(true);

$attachedFilePathWeb = $coursesRepositoryWeb.$_course['path'].'/exercise';
$attachedFilePathSys = $coursesRepositorySys.$_course['path'].'/exercise';

claro_set_display_mode_available(true);
$is_allowedToEdit = claro_is_allowed_to_edit();

$tbl_cdb_names = claro_sql_get_course_tbl();
$tbl_quiz_answer             = $tbl_cdb_names['quiz_answer'            ];
$tbl_quiz_question           = $tbl_cdb_names['quiz_question'          ];
$tbl_quiz_rel_test_question  = $tbl_cdb_names['quiz_rel_test_question' ];
$tbl_quiz_test               = $tbl_cdb_names['quiz_test'              ];
$tbl_track_e_exercises       = $tbl_cdb_names['track_e_exercices'      ];

// if the object is not in the session or if an exercise id is specified in url
if( !empty($_REQUEST['exerciseId']) || !isset($_SESSION['objExercise']) || !is_object($_SESSION['objExercise']) )
{
    // construction of Exercise
    $objExercise = new Exercise();

    // exercise not found if
    //  - no exerciseId given
    //    - read error with exerciseId
    //  -
    if( empty($_REQUEST['exerciseId'])
        || ! $objExercise->read($_REQUEST['exerciseId'])
        || ( ! $objExercise->selectStatus() &&  ! $is_allowedToEdit
                && ( ! isset($_SESSION['inPathMode']) || ! $_SESSION['inPathMode'] )
            )
       )
    {
        include ($includePath.'/claro_init_header.inc.php');
        echo '<br />'.claro_disp_message_box(get_lang('ExerciseNotFound')).'<br />';
        include ($includePath.'/claro_init_footer.inc.php');
        die(); 
    }

    // saves the object into the session
    $_SESSION['objExercise'] = $objExercise;
    // clear the session, the values are probably those of another exercise
    unset($_SESSION['objQuestion'    ]);
    unset($_SESSION['objAnswer'        ]);
    unset($_SESSION['questionList'    ]);
    unset($_SESSION['exerciseResult']);
    unset($_SESSION['exeStartTime'    ]);

} // else session recorded objExercise

// if questionNum comes from POST and not from GET
// set $questionNum only if the exercise type requires it
if($_SESSION['objExercise']->selectType() == 2)  // sequential exercise
{
    if( isset($_POST['questionNum']) )
        $questionNum = $_POST['questionNum'] + 1;
    else
        $questionNum = 1;
}

// deal with the learning path mode
if( isset($_SESSION['inPathMode']) && $_SESSION['inPathMode'] )
{
     $is_allowedToEdit = false; // do not allow to be in admin mode during a path progression

    if( isset($_REQUEST['buttonCancel']) )
    {
        // returns to the module presentation page
        $backUrl = $clarolineRepositoryWeb."learnPath/navigation/backFromExercise.php?op=cancel";
        header('Location: '.$backUrl);
        exit();
    }
}
else
{    // if the user has clicked on the "Cancel" button
    if( isset($_REQUEST['buttonCancel']) )
    {
        // returns to the exercise list
        header('Location: exercice.php');
        exit();
    }
}


// if the user has submitted the form
if( isset($_REQUEST['formSent']) )
{
    // initializing
    if( !isset($_SESSION['exerciseResult']) || !is_array($_SESSION['exerciseResult']))
    {
        $_SESSION['exerciseResult'] = array();
    }

    // if the user has answered at least one question
    if( isset($_REQUEST['choice']) && is_array($_REQUEST['choice']) )
    {
        if( $_SESSION['objExercise']->selectType() == 1 )
        {
            // $exerciseResult receives the content of the form.
            // Each choice of the student is stored into the array $choice
            $_SESSION['exerciseResult'] = $_REQUEST['choice'];
        }
        else
        {
            // gets the question ID from $choice. It is the key of the array
            list($key) = array_keys($_REQUEST['choice']);

            // if the user didn't already answer this question
            if( !isset($_SESSION['exerciseResult'][$key]) )
            {
                // stores the user answer into the array
                $_SESSION['exerciseResult'][$key] = $_REQUEST['choice'][$key];
            }
        }
    }

    // the script "exercise_result.php" will take the variable $exerciseResult from the session
    if (!isset($_SESSION['exerciseResult']) ) $_SESSION['exerciseResult'] = $exerciseResult ;

    // if it is the last question (only for a sequential exercise)
    // or we have an exercise on one page only
    if( $_SESSION['objExercise']->selectType() == 1 || $questionNum > $_REQUEST['nbrQuestions'] )
    {
        // goes to the script that will show the result of the exercise
        header('Location: exercise_result.php');
        exit();
    }
}

// get infos about the current exercise
$exerciseTitle        = $_SESSION['objExercise']->selectTitle();
$exerciseDescription= $_SESSION['objExercise']->selectDescription();
$randomQuestions    = $_SESSION['objExercise']->isRandom();
$exerciseType        = $_SESSION['objExercise']->selectType();
$exerciseMaxTime     = $_SESSION['objExercise']->get_max_time();
$exerciseMaxAttempt    = $_SESSION['objExercise']->get_max_attempt();

// count number of attempts of the user 
$sql="SELECT count(`exe_result`) AS `tryQty`
        FROM `".$tbl_track_e_exercises."`
       WHERE `exe_user_id` = '". (int)$_uid."'
         AND `exe_exo_id` = ". (int)$_SESSION['objExercise']->selectId()."
       GROUP BY `exe_user_id`";
$result = claro_sql_query_fetch_all($sql);

if( isset($result[0]['tryQty']) )    $userTryQty = $result[0]['tryQty'] + 1; // +1 to count this attempt too
else                                $userTryQty = 1;
// end of count of attempts of the user

if(!isset($_SESSION['questionList']))
{
    // selects the list of question ID
    if( isset($randomQuestions) && $randomQuestions )
        $questionList = $_SESSION['objExercise']->selectRandomList();
    else
        $questionList = $_SESSION['objExercise']->selectQuestionList();

    // saves the question list into the session
    //session_register('questionList');
    $_SESSION['questionList'] = $questionList;
}
// start time of the exercise (use session because in post it could be modified
// easily by user using a development bar in mozilla for an example)
// need to check if it already exists in session for sequential exercises
if(!isset($_SESSION['exeStartTime']) )
{
    $_SESSION['exeStartTime'] = time();
}
if( isset($_SESSION['questionList']) && is_array($_SESSION['questionList']) )
    $nbrQuestions = sizeof($_SESSION['questionList']);
else
    $nbrQuestions = 0;

$nameTools = $_SESSION['objExercise']->exercise;

if( isset($questionNum) )
{
    $_SERVER['QUERY_STRING'] = "questionNum=$questionNum";
}

if ( isset($_SESSION['inPathMode']) && $_SESSION['inPathMode'] )
{
    $hide_banner = true;
}
else
{
  $interbredcrump[] = array("url" => "exercice.php","name" => get_lang('Exercices'));
}
include($includePath.'/claro_init_header.inc.php');

// EXERCISE  PROPERTIES HANDLING
$statusMsg = "<p>";
$errMsg = "";
$showExerciseForm = true;
// MAX ALLOWED TIME
// display actual time only if exercise is sequential, it will always be
// zero in non sequential mode 

if($exerciseType == 2) 
{ 
    $statusMsg .= get_lang('CurrentTime')." : ".(time() - $_SESSION['exeStartTime']);
}

if($exerciseMaxTime != 0)
{
  $statusMsg .= " ".get_lang('MaxAllowedTime')." : ".claro_disp_duration($exerciseMaxTime);
}
else
{
  $statusMsg .= " ".get_lang('NoTimeLimit');
}
    
// MAX ALLOWED ATTEMPTS
// display maximum attempts number only if != 0 (0 means unlimited attempts)
// always display user attempts count
// do not show attempts for anonymous user
if( isset($_uid) )
{
  $statusMsg .= "<br />".get_lang('Attempt')." ".$userTryQty." ";
  if( $exerciseMaxAttempt )
  {
    $statusMsg .= get_lang('On')." ".$exerciseMaxAttempt;
    if( $userTryQty > $exerciseMaxAttempt )
    {
        $showExerciseForm = false;
        $errMsg .=  "<br/>".get_lang('NoMoreAttemptsAvailable');
    }
  }
}
// AVAILABILITY DATES
// check if the exercise is available (between opening and closing  dates)
$mktimeNow      = mktime();
$timeStartDate  = $_SESSION['objExercise']->get_start_date('timestamp');

$statusMsg  .= "<br />".get_lang('Available from')." "
                    .claro_disp_localised_date($dateTimeFormatLong,$timeStartDate);

if($_SESSION['objExercise']->get_end_date() != "9999-12-31 23:59:59")
{
    $timeEndDate    = $_SESSION['objExercise']->get_end_date('timestamp');
    $statusMsg   .= " ".get_lang('Until')." "
                        .claro_disp_localised_date($dateTimeFormatLong,$timeEndDate);
}
                      
if( $timeStartDate > $mktimeNow )
{
    $showExerciseForm = false;
    $errMsg .= "<br />".get_lang('ExerciseNotAvailable');
}
elseif( ($_SESSION['objExercise']->get_end_date() != "9999-12-31 23:59:59") && ($timeEndDate < $mktimeNow) )
{
    $showExerciseForm = false;
    $errMsg .= "<br />".get_lang('ExerciseNoMoreAvailable');
}

// concat errmsg to status msg before displaying it
$statusMsg .= "<br /><b>".$errMsg."</b>";
echo claro_disp_tool_title(get_lang('Exercise')." : ".$exerciseTitle);

if( $showExerciseForm || $is_allowedToEdit )
{
?>
  <p>
  <?php echo claro_parse_user_text($exerciseDescription) ; ?>
  <small>
  <?php echo $statusMsg;  ?>
  </small>
  </p>
<?php
    if( $is_allowedToEdit && ( !isset($_SESSION['inPathMode']) || !$_SESSION['inPathMode']) )
    {
        echo '<a class="claroCmd" href="admin.php?exerciseId='.$_SESSION['objExercise']->selectId().'"><img src="'.$imgRepositoryWeb.'edit.gif" border="0" alt="" />'.get_lang('ModifyExercise').'</a>';
    }    
?>
  <table width="100%" border="0" cellpadding="1" cellspacing="0">
  <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>?<?php echo SID ?>" autocomplete="off">
  <input type="hidden" name="formSent" value="1">
<?php
    if($_SESSION['objExercise']->selectType() == 2)
        echo '<input type="hidden" name="questionNum" value="'.$questionNum.'">';
?>
  <input type="hidden" name="nbrQuestions" value="<?php echo $nbrQuestions; ?>">
  <tr>
    <td>
  
  <?php
  $i=0;
  
  foreach( $_SESSION['questionList'] as $questionId )
  {
    $i++;
  
    // for sequential exercises
    if($exerciseType == 2)
    {
        // if it is not the right question, goes to the next loop iteration
        if( $questionNum && $questionNum != $i )
        {
            continue;
        }
        else
        {
            // if the user has already answered this question
            if( isset($_SESSION['exerciseResult'][$questionId]) )
            {
                // construction of the Question object
                $objQuestionTmp = new Question();

                // reads question informations
                $objQuestionTmp->read($questionId);

                $questionName = $objQuestionTmp->selectTitle();

                // destruction of the Question object
                unset($objQuestionTmp);

                echo '<tr><td>'.get_lang('AlreadyAnswered').' &quot;'.$questionName.'&quot;</td></tr>';

                break;
            }
        }
    }
  ?>
  <table width="100%" cellpadding="4" cellspacing="2" border="0" class="claroTable">
    <tr class="headerX">
      <th valign="top" colspan="2">
      <?php echo get_lang('Question'); ?> <?php echo $i; if($exerciseType == 2) echo ' / '.$nbrQuestions; ?>
      </th>
    </tr>
   <tfoot>
  <?php
    // shows the question and its answers
    showQuestion($questionId);
  ?>
    </tfoot>
    </table>
  <?php
    // for sequential exercises
    if($exerciseType == 2)
    {
      // quits the loop
      break;
    }
  }    // end foreach()
  ?>
  
    </td>
  </tr>
  <tr>
    <td align="center"><br />
    <input type="submit" value="<?php echo ($exerciseType == 1 || $nbrQuestions == $questionNum )?get_lang('Ok'):get_lang('Next').' &gt;'; ?>">
    </td>
  </tr>
  </form>
  </table>

<?php
} //end of if ($showExerciseForm)
else
{
  echo "<small>".$statusMsg."</small>";
}

if( isset($_SESSION['inPathMode']) && $_SESSION['inPathMode'] )
{    
    // echo minimal html footer so that the page is valid
    $hide_footer = true;
}
include($includePath.'/claro_init_footer.inc.php');
?>
