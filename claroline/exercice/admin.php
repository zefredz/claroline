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
        /*>>>>>>>>>>>>>>>>>>>> EXERCISE ADMINISTRATION <<<<<<<<<<<<<<<<<<<<*/

/**
 * This script allows to manage (create, modify) an exercise and its questions
 *
 * Following scripts are includes for a best code understanding :
 *
 * - exercise.class.php : for the creation of an Exercise object
 * - question.class.php : for the creation of a Question object
 * - answer.class.php : for the creation of an Answer object
 *
 * - exercise.lib.php : functions used in the exercise tool
 *
 * - exercise_admin.inc.php : management of the exercise
 * - question_admin.inc.php : management of a question (statement & answers)
 * - statement_admin.inc.php : management of a statement
 * - answer_admin.inc.php : management of answers
 * - question_list_admin.inc.php : management of the question list
 *
 * Main variables used in this script :
 *
 * - $is_allowedToEdit : set to 1 if the user is allowed to manage the exercise
 *
 * - $objExercise : exercise object
 * - $objQuestion : question object
 * - $objAnswer : answer object
 *
 * - $aType : array with answer types
 * - $exerciseId : the exercise ID
 * - $attachedFilePath : the path of question attached files
 *
 * - $newQuestion : ask to create a new question
 * - $modifyQuestion : ID of the question to modify
 * - $editQuestion : ID of the question to edit
 * - $submitQuestion : ask to save question modifications
 * - $cancelQuestion : ask to cancel question modifications
 * - $deleteQuestion : ID of the question to delete
 * - $moveUp : ID of the question to move up
 * - $moveDown : ID of the question to move down
 * - $modifyExercise : ID of the exercise to modify
 * - $submitExercise : ask to save exercise modifications
 * - $cancelExercise : ask to cancel exercise modifications
 * - $modifyAnswers : ID of the question which we want to modify answers for
 * - $cancelAnswers : ask to cancel answer modifications
 * - $buttonBack : ask to go back to the previous page in answers of type "Fill in blanks"
 */

include('exercise.class.php');
include('question.class.php');
include('answer.class.php');

include('exercise.lib.php');

require '../inc/claro_init_global.inc.php';

if ( !$_cid || !$is_courseAllowed ) claro_disp_auth_form(true);
  
// answer types
define('UNIQUE_ANSWER',  1);
define('MULTIPLE_ANSWER',2);
define('FILL_IN_BLANKS', 3);
define('MATCHING',     4);
define('TRUEFALSE',     5);

// for help display in fill in blanks questions
define('TEXTFIELD_FILL', 1);
define('LISTBOX_FILL',    2);

// allows script inclusions
define('ALLOWED_TO_INCLUDE',1);

$is_allowedToEdit = $is_courseAdmin;

// attached files path
$attachedFilePathWeb = $coursesRepositoryWeb.$_course['path'].'/exercise';
$attachedFilePathSys = $coursesRepositorySys.$_course['path'].'/exercise';

// the 4 types of answers
$aType = array(get_lang('UniqueSelect'),get_lang('MultipleSelect'),get_lang('FillBlanks'),get_lang('Matching'),get_lang('TrueFalse'));

// tables used in the exercise tool
$tbl_cdb_names = claro_sql_get_course_tbl();
$tbl_quiz_rel_test_question = $tbl_cdb_names['quiz_rel_test_question'];
$tbl_quiz_test         = $tbl_cdb_names['quiz_test'];
$tbl_quiz_question     = $tbl_cdb_names['quiz_question'];
$tbl_quiz_answer       = $tbl_cdb_names['quiz_answer'];

//take parameters from URL or posted forms :

if (!empty($_REQUEST['modifyExercise'])) $modifyExercise  = $_REQUEST['modifyExercise']; else unset($modifyExercise);
if (!empty($_REQUEST['modifyAnswers']))  $modifyAnswers   = $_REQUEST['modifyAnswers'];  else unset($modifyAnswers);
if (!empty($_REQUEST['modifyQuestion'])) $modifyQuestion  = $_REQUEST['modifyQuestion']; else unset($modifyQuestion);
if (!empty($_REQUEST['editQuestion']))   $editQuestion    = $_REQUEST['editQuestion'];   else unset($editQuestion);
if (!empty($_REQUEST['newQuestion']))    $newQuestion     = $_REQUEST['newQuestion'];    else unset($newQuestion);
if (!empty($_REQUEST['deleteQuestion'])) $deleteQuestion  = $_REQUEST['deleteQuestion']; else unset($deleteQuestion);
if (!empty($_REQUEST['modifyIn']))       $modifyIn        = $_REQUEST['modifyIn'];       else unset($modifyIn);
if (!empty($_REQUEST['exerciseId']))     $exerciseId      = $_REQUEST['exerciseId'];    else unset($exerciseId);

if(!$is_allowedToEdit)
{
    die(get_lang('Not allowed'));
}

// intializes the Exercise object
if( !empty($_REQUEST['exerciseId']) || !isset($_SESSION['objExercise']) || !is_object($_SESSION['objExercise']))
{
    // construction of the Exercise object
    $objExercise = new Exercise();

    // creation of a new exercise if wrong or not specified exercise ID
    if(isset($exerciseId))
    {
        $objExercise->read($exerciseId);
    }

    // saves the object into the session
    $_SESSION['objExercise'] = $objExercise;
}// use session recorded objExercise

// doesn't select the exercise ID if we come from the question pool
if(!isset($_REQUEST['fromExercise']))
{
    // gets the right exercise ID, and if 0 creates a new exercise
    if(!$exerciseId = $_SESSION['objExercise']->selectId())
    {
        $modifyExercise = 'yes';
    }
}


$nbrQuestions = $_SESSION['objExercise']->selectNbrQuestions();

// intializes the Question object
if(isset($editQuestion) || isset($newQuestion) || (isset($modifyQuestion)) || isset($modifyAnswers))
{
    if(isset($editQuestion) || isset($newQuestion))
    {
        // construction of the Question object
        $objQuestion = new Question();

        // saves the object into the session
        $_SESSION['objQuestion'] = $objQuestion;

        // reads question data
        if(isset($editQuestion))
        {
            // question not found
            if(!$_SESSION['objQuestion']->read($editQuestion))
            {
                die(get_lang('QuestionNotFound'));
            }
        }
    }

    // checks if the object exists
    if(isset($_SESSION['objQuestion']) && is_object($_SESSION['objQuestion']))
    {
        // gets the question ID
        $questionId = $_SESSION['objQuestion']->selectId();
    }
    // question not found
    else
    {
        die(get_lang('QuestionNotFound'));
    }
}

// if cancelling an exercise
if( isset($_REQUEST['cancelExercise']) )
{
    // existing exercise
    if( isset($exerciseId) && $exerciseId )
    {
        unset($modifyExercise);
    }
    // new exercise
    else
    {
        // goes back to the exercise list
        header('Location: exercice.php');
        exit();
    }
}

// if cancelling question creation/modification
if(isset($_REQUEST['cancelQuestion']))
{
    // if we are creating a new question from the question pool
    if(!$exerciseId && !$questionId)
    {
        // goes back to the question pool
        header('Location: question_pool.php');
        exit();
    }
    else
    {
        // goes back to the question viewing
        if( isset($_REQUEST['modifyQuestion']) ) $editQuestion = $_REQUEST['modifyQuestion'];

        unset($newQuestion,$modifyQuestion);
    }
}

// if cancelling answer creation/modification
if(isset($_REQUEST['cancelAnswers']))
{
    // goes back to the question viewing
    $editQuestion = $modifyAnswers;

    unset($modifyAnswers);
}

$interbredcrump[] = array("url" => "exercice.php","name" => get_lang('Exercices'));

// modifies the query string that is used in the link of tool name
if(isset($editQuestion) || (isset($modifyQuestion)) || isset($newQuestion) || isset($modifyAnswers))
{
    $nameTools = get_lang('QuestionManagement');
        
    // shows a link to go back to the question pool
    if (isset($_REQUEST['fromExercise']))     $addFrom = "fromExercise=".$_REQUEST['fromExercise'];
    else                                     $addFrom = '';

    if( $_SESSION['objExercise']->selectTitle() == '' )
    {
        $interbredcrump[] = array("url" => "question_pool.php?".$addFrom,"name" => get_lang('QuestionPool'));
    }
    else
    {
        $interbredcrump[] = array("url" => "admin.php?fromExercise=".$addFrom,"name" => $_SESSION['objExercise']->selectTitle());
    }
    
    $_SERVER['QUERY_STRING'] = $questionId?'editQuestion='.$questionId.'&'.$addFrom:'newQuestion=yes';
}
else
{
    if( isset($exerciseId))
    {
        $nameTools = $_SESSION['objExercise']->selectTitle();
    }
    else
    {
        $nameTools = get_lang('ExerciseManagement');
    }
    $_SERVER['QUERY_STRING'] = '';
}

// if the question is duplicated, disable the link of tool name
if(isset($modifyIn) && $modifyIn == 'thisExercise')
{
    if( isset($_REQUEST['buttonBack']) )
    {
        $modifyIn = 'allExercises';
    }
    else
    {
        $noPHP_SELF = true;
    }
}

include($includePath.'/claro_init_header.inc.php');

echo claro_disp_tool_title($nameTools);

if (isset($newQuestion) || (isset($modifyQuestion)))
{
    // statement management
    include(dirname(__FILE__).'/statement_admin.inc.php');
}

if(isset($modifyAnswers) || (isset($modifyAnswers)))
{
    // answer management
    include(dirname(__FILE__).'/answer_admin.inc.php');
}

if(isset($editQuestion) || isset($usedInSeveralExercises))
{
    // question management
    include(dirname(__FILE__).'/question_admin.inc.php');
}

if(!isset($newQuestion) && !isset($modifyQuestion) && !isset($editQuestion) && !isset($modifyAnswers))
{
    // exercise management
    include(dirname(__FILE__).'/exercise_admin.inc.php');

    if( !isset($modifyExercise) )
    {
        // question list management
        include(dirname(__FILE__).'/question_list_admin.inc.php');
    }
}

include($includePath.'/claro_init_footer.inc.php');
?>