<?php // $Id$
/**
 * CLAROLINE
 *
 * @version 1.8 $Revision$
 *
 * @copyright (c) 2001-2007 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @author Claro Team <cvs@claroline.net>
 *
 */

$tlabelReq = 'CLQWZ';

require '../inc/claro_init_global.inc.php';

if ( ! claro_is_in_a_course() || !claro_is_course_allowed() ) claro_disp_auth_form(true);

claro_set_display_mode_available(true);

$is_allowedToEdit = claro_is_allowed_to_edit();

// tool libraries
include_once './lib/exercise.class.php';
include_once './lib/question.class.php';
include_once './lib/exercise.lib.php';

// following includes are not really clean as the question object already includes the one it needs
// but for the moment it is required by unserialize
include_once './lib/answer_truefalse.class.php';
include_once './lib/answer_multiplechoice.class.php';
include_once './lib/answer_fib.class.php';
include_once './lib/answer_matching.class.php';

// claroline libraries
include_once get_path('incRepositorySys') . '/lib/htmlxtra.lib.php';
include_once get_path('incRepositorySys') . '/lib/form.lib.php';
include_once get_path('incRepositorySys') . '/lib/module.lib.php';

// TODO find a better way to get table from this module and from LP module
$tblList = get_module_course_tbl( array( 'qwz_tracking' ), claro_get_current_course_id() );
$tbl_qwz_tracking = $tblList['qwz_tracking'];

$tbl_cdb_names = claro_sql_get_course_tbl();

// learning path 
// new module CLLP
$inLP = (claro_called_from() == 'CLLP')? true : false;
// old learning path tool 
if( isset($_SESSION['inPathMode']) && $_SESSION['inPathMode'] )
{
    require_once get_path('incRepositorySys') . '/lib/learnPath.lib.inc.php';

    $tbl_lp_learnPath            = $tbl_cdb_names['lp_learnPath'           ];
    $tbl_lp_rel_learnPath_module = $tbl_cdb_names['lp_rel_learnPath_module'];
    $tbl_lp_user_module_progress = $tbl_cdb_names['lp_user_module_progress'];
    $tbl_lp_module               = $tbl_cdb_names['lp_module'              ];
    $tbl_lp_asset                = $tbl_cdb_names['lp_asset'               ];

    $claroline->setDisplayType(Claroline::FRAME);
}



/*
 * Execute commands
 */
if( isset($_REQUEST['exId']) && is_numeric($_REQUEST['exId']) ) $exId = (int) $_REQUEST['exId'];
else                                                            $exId = null;

if( isset($_REQUEST['step']) && is_numeric($_REQUEST['step']) ) $step = (int) $_REQUEST['step'];
else                                                            $step = 0;


/**
 * Handle SESSION
 * - refresh data in session if required
 * - copy session content locally to use local var in script
 * - 
 */
$resetQuestionList = false;


// if exercise is not in session try to load it.
// if exId has been defined in request force refresh of exercise in session
if( !isset($_SESSION['serializedExercise']) || !is_null($exId) )
{
    // clean previous exercise if any
    unset($_SESSION['serializedExercise']);
    
    $exercise = new Exercise();

    if( is_null($exId) || !$exercise->load($exId) )
    {
        // exercise is required
        header("Location: ./exercise.php");
        exit();
    }
    else
    {
        // load successfull
        // exercise must be visible or in learning path to be displayed to a student
        if( $exercise->getVisibility() != 'VISIBLE' && !$is_allowedToEdit 
        && ( ! isset($_SESSION['inPathMode']) || ! $_SESSION['inPathMode'] || ! $inLP )
         )
        {
            header("Location: ./exercise.php");
            exit();
        }
        else
        {
            $_SESSION['serializedExercise'] = serialize($exercise);
            $resetQuestionList = true;
        }
    }
}
else
{
    // get it back from session
    $exercise = unserialize($_SESSION['serializedExercise']);
    $exId = $exercise->getId();
}

//-- get question list
if( $resetQuestionList || !isset($_SESSION['serializedQuestionList']) || !is_array($_SESSION['serializedQuestionList']) )
{
    if( $exercise->getShuffle() == 0 )
    {
        $qList = $exercise->getQuestionList();
    }
    else
    {
        $qList = $exercise->getRandomQuestionList();
    }

    $questionList = array();
    $_SESSION['serializedQuestionList'] = array();
    // get all question objects and store them serialized in session
    foreach( $qList as $question )
    {
        $questionObj = new Question();
        $questionObj->setExerciseId($exId);

        if( $questionObj->load($question['id']) )
        {
            $_SESSION['serializedQuestionList'][] = serialize($questionObj);
            $questionList[] = $questionObj;
        }
        unset($questionObj);
    }
}
else
{
    $questionList = array();
    foreach( $_SESSION['serializedQuestionList'] as $serializedQuestion )
    {
        $questionList[] = unserialize($serializedQuestion);
    }
}

$questionCount = count($questionList);


$now = time();

if( !isset($_SESSION['exeStartTime']) )
{
    $_SESSION['exeStartTime'] = $now;
    $currentTime = 0;
}
else
{
    $currentTime = $now - $_SESSION['exeStartTime'];
}

//-- exercise properties
$dialogBox = new DialogBox();

if( claro_is_user_authenticated() )
{
    // count number of attempts of the user
    $sql="SELECT count(`result`) AS `tryQty`
            FROM `".$tbl_qwz_tracking."`
           WHERE `user_id` = '".(int) claro_get_current_user_id()."'
             AND `exo_id` = ".(int) $exId."
           GROUP BY `user_id`";

    $userAttemptCount = claro_sql_query_get_single_value($sql);

    if( $userAttemptCount )    $userAttemptCount++;
    else                     $userAttemptCount = 1; // first try
}
else
{
    $userAttemptCount = 1;
}


$exerciseIsAvailable = true;

if( !$is_allowedToEdit )
{
    // do the checks only if user has no edit right
    // check if exercise can be displayed
    if( $exercise->getStartDate() > $now
        || ( !is_null($exercise->getEndDate()) && $exercise->getEndDate() < $now )
       )
    {
        // not yet available, no more available
        $dialogBox->error( get_lang('Exercise not available') );
        $exerciseIsAvailable = false;
    }
    elseif( $exercise->getAttempts() > 0 && $userAttemptCount > $exercise->getAttempts() ) // attempt #
    {
        $dialogBox->error( get_lang('You have reached the maximum number of allowed attempts.') );
        $exerciseIsAvailable = false;
    }
}



// exercise is submitted - GRADE EXERCISE
if( isset($_REQUEST['cmdOk']) && $_REQUEST['cmdOk'] && $exerciseIsAvailable )
{
    $timeToCompleteExe =  $currentTime;
    $recordResults = true;

    // the time limit is set and the user take too much time to complete exercice
    if ( $exercise->getTimeLimit() > 0 && $exercise->getTimeLimit() < $timeToCompleteExe )
    {
        $showAnswers = false;
        $recordResults = false;
    }
    else
    {
        if ( $exercise->getShowAnswers()  == 'ALWAYS' )
        {
            $showAnswers = true;
        }
        elseif ( $exercise->getShowAnswers() == 'LASTTRY' && $userAttemptCount >= $exercise->getAttempts() )
        {
            $showAnswers = true;
        }
        else
        {
            // $exercise->getShowAnswers()  == 'NEVER'
            $showAnswers = false;
        }
    }
    
    // clean session to avoid receiving same exercise next time
    unset($_SESSION['serializedExercise']);
    unset($_SESSION['serializedQuestionList']);
    unset($_SESSION['exeStartTime']);
    
    $showResult = true;
    $showSubmitForm = false;

    if( $recordResults )
    {
        // compute scores
        $totalResult = 0;
        $totalGrade = 0;

        for( $i = 0 ; $i < count($questionList); $i++)
        {
            // required by getGrade and getQuestionFeedbackHtml
            $questionList[$i]->answer->extractResponseFromRequest();

            $questionResult[$i] = $questionList[$i]->answer->gradeResponse();
            $questionGrade[$i] = $questionList[$i]->getGrade();

            // sum of score
            $totalResult += $questionResult[$i];
            $totalGrade += $questionGrade[$i];
        }
        
        //-- tracking
        // if anonymous attempts are authorised : record anonymous user stats, record authentified user stats without uid
        if ( $exercise->getAnonymousAttempts() == 'ALLOWED' )
        {
            $exerciseTrackId = track_exercice($exId,$totalResult,$totalGrade,$timeToCompleteExe );
        }
        elseif( claro_is_in_a_course() ) // anonymous attempts not allowed, record stats with uid only if uid is set
        {
            $exerciseTrackId = track_exercice($exId,$totalResult,$totalGrade,$timeToCompleteExe, claro_get_current_user_id() );
        }

        if( isset($exerciseTrackId) && $exerciseTrackId && !empty($questionList) )
        {
            $i = 0;
            foreach ( $questionList as $question )
            {
                track_exercise_details($exerciseTrackId,$question->getId(),$question->answer->getTrackingValues(),$questionResult[$i]);
                $i++;
            }
        }

        // learning path 
        // new module CLLP
        if( $inLP )
        {
            // include some utils functions
            include_once get_module_path('CLLP') . '/lib/utils.lib.php';
            if( $totalGrade > 0 )
            {
                $scoreRaw = $totalResult / $totalGrade * 100;
                $scoreMin = 0;
                $scoreMax = 100;
            }
            else
            {
                $scoreRaw = $scoreMin = $scoreMax = 0;
                $completionStatus = 'incomplete';
            }

            if( $scoreRaw > 50 )
            {
                $completionStatus = 'completed';
            }
            else
            {
                $completionStatus = 'incomplete';
            }
            
            $sessionTime = unixToScormTime($timeToCompleteExe);
            
            $jsForLP = ''
            .   'doSetValue("cmi.score.raw","'.$scoreRaw.'");' . "\n"
            .   'doSetValue("cmi.score.min","'.$scoreMin.'");' . "\n"
            .   'doSetValue("cmi.score.max","'.$scoreMax.'");' . "\n"
            .   'doSetValue("cmi.session_time","'.$sessionTime.'");' . "\n"
            .   'doSetValue("cmi.completion_status","'.$completionStatus.'");' . "\n"
            
            .   'doCommit();' . "\n"
            .   'doTerminate();' . "\n";
        }
        // old learning path tool
        if( isset($_SESSION['inPathMode']) && $_SESSION['inPathMode'] )
        {
            set_learning_path_progression($totalResult,$totalGrade,$timeToCompleteExe,claro_get_current_user_id());
        }
    }
}
elseif( ! $exerciseIsAvailable )
{
    $showResult = false;
    $showSubmitForm = false;
}
else
{
    $showResult = false;
    $showSubmitForm = true;
}
//-- update step
if( isset($_REQUEST['cmdBack']) )     $step--;
else                                $step++;






/*
 * Output
 */

// learning path 
// new module CLLP
if( $inLP )
{
    $jsloader = JavascriptLoader::getInstance();
    $jsloader->load('jquery');
    // load functions required to be able to discuss with API
    $jsloader->loadFromModule('CLLP', 'connector13');

    $jsloader->load('cllp.cnr');
    
    if( !empty($jsForLP) )
    {
        $claroline->display->header->addInlineJavascript($jsForLP);
    }
}


ClaroBreadCrumbs::getInstance()->prepend( get_lang('Exercises'), 'exercise.php' );

$out = '';

$nameTools = $exercise->getTitle();


//-- title
if( $showResult )
{
    $out .= claro_html_tool_title(get_lang('Exercise results') . ' : ' . $nameTools);
}
else
{
    $out .= claro_html_tool_title(get_lang('Exercise') . ' : ' . $nameTools);
}

//-- display properties
if( trim($exercise->getDescription()) != '' )
{
    $out .= '<blockquote>' . "\n" . claro_parse_user_text($exercise->getDescription()) . "\n" . '</blockquote>' . "\n";
}

$out .= '<ul style="font-size:small">' . "\n";
if( $exercise->getDisplayType() == 'SEQUENTIAL' )
{
    $out .= '<li>' . get_lang('Current time')." : ". claro_html_duration($currentTime) . '</li>' . "\n";
}

if( $exercise->getTimeLimit() > 0 )
{
    $out .= '<li>' . get_lang('Time limit')." : ".claro_html_duration($exercise->getTimeLimit()) . '</li>' . "\n";
}
else
{
    $out .= '<li>' . get_lang('No time limitation') . '</li>' . "\n";
}

if( claro_is_user_authenticated() && isset($userAttemptCount) )
{
    $out .= '<li>';
    if ( $exercise->getAttempts() > 0 )
    {
        $out .= get_lang('Attempt %attemptCount on %attempts', array('%attemptCount'=> $userAttemptCount, '%attempts' =>$exercise->getAttempts())) ;
    }
    else
    {
        $out .= get_lang('Attempt %attemptCount', array('%attemptCount'=> $userAttemptCount)) ;
    }
    $out .= '</li>' . "\n";
}

$out .= '<li>'
.    get_lang('Available from %startDate', array('%startDate' => claro_html_localised_date(get_locale('dateTimeFormatLong'), $exercise->getStartDate())));

if( !is_null($exercise->getEndDate()) )
{
    $out .= ' ' . get_lang('Until') . ' ' . claro_html_localised_date(get_locale('dateTimeFormatLong'),$exercise->getEndDate());
}
$out .= '</li>' . "\n";

$out .= '</ul>' .  "\n\n";


if( $showResult )
{
    if( isset($_SESSION['inPathMode']) && $_SESSION['inPathMode'] )
    {
        // old learning path tool
        $out .= '<form method="get" action="../learnPath/navigation/backFromExercise.php">' . "\n"
        .    '<input type="hidden" name="op" value="finish" />';
    }
    elseif( !$inLP )
    {
        // standard exercise mode
        $out .= '<form method="get" action="exercise.php">';
    } // if inLP do not allow to navigate away : user should use LP navigation to go to another module

    $out .= "\n" . '<table width="100%" border="0" cellpadding="1" cellspacing="0" class="claroTable">' . "\n\n";

    //-- question(s)
    if( !empty($questionList) )
    {
        // foreach question
        $questionIterator = 1;
        $i = 0;

        foreach( $questionList as $question )
        {
            if( $showAnswers )
            {
                $out .= '<tr class="headerX">' . "\n"
                .     '<th>'
                .     get_lang('Question') . ' ' . $questionIterator
                .     '</th>' . "\n"
                .     '</tr>' . "\n\n";

                $out .= '<tr>'
                .     '<td>' . "\n";

                $out .= $question->getQuestionFeedbackHtml();

                $out .= '</td>' . "\n"
                .     '</tr>' . "\n\n"

                .     '<tr>'
                .     '<td align="right">' . "\n"
                .     '<strong>'.get_lang('Score').' : '.$questionResult[$i].'/'.$questionGrade[$i].'</strong>'
                .     '</td>' . "\n"
                .     '</tr>' . "\n\n";
            }
            $questionIterator++;
            $i++;
        }
    }

    // table footer, form footer
    $out .= '<tr>' . "\n"
    .     '<td align="center">'
    .     get_lang('Your time is %time', array('%time' => claro_html_duration($timeToCompleteExe)) )
    .     '<br />' . "\n"
    .     '<strong>';

    if( $recordResults )
    {
        $out .= get_lang('Your total score is %score', array('%score' => $totalResult."/".$totalGrade ) );
    }
    else
    {
        $out .= get_lang('Time is over, results not submitted.');
    }

    $out .= '</strong>'
    .     '</td>' . "\n"
    .     '</tr>' . "\n\n"
    .     '<tr>' . "\n"
    .     '<td align="center">';
    
    if( !$inLP )
    {
        $out .= '<input type="submit" value="'.get_lang('Finish').'" />';
    }
    else
    {
        $out .= get_lang('Exercise done, choose a module in the list to continue.');
    }
    
    $out .= '</td>' . "\n"
    .     '</tr>' . "\n\n"
    .     '</table>' . "\n\n";

    if( !$inLP )
    {
        $out .= '</form>' . "\n\n";
    }

}
elseif( $showSubmitForm )
{
    //-- question(s)
    if( !empty($questionList) )
    {
        // form header, table header
        $out .= '<form method="post" action="./exercise_submit.php">' . "\n"
        .   claro_form_relay_context() . "\n";

        if( $exercise->getDisplayType() == 'SEQUENTIAL' )
        {
            $out .= '<input type="hidden" name="step" value="'.$step.'" />' . "\n";
        }

        $out .= "\n" . '<table width="100%" border="0" cellpadding="1" cellspacing="0" class="claroTable">' . "\n\n";

        // foreach question
        $questionIterator = 0;

        foreach( $questionList as $question )
        {
            $questionIterator++;

            if( $exercise->getDisplayType() == 'SEQUENTIAL' )
            {
                // get response if something has already been sent
                $question->answer->extractResponseFromRequest();

                if( $step != $questionIterator )
                {
                    // only echo hidden form field
                    $out .= $question->answer->getHiddenAnswerHtml();
                }
                else
                {
                    $out .= '<tr class="headerX">' . "\n"
                    .     '<th>'
                    .     get_lang('Question') . ' ' . $questionIterator
                    .     ' / '.$questionCount
                    .     '</th>' . "\n"
                    .     '</tr>' . "\n\n";

                    $out .= '<tr>'
                    .     '<td>' . "\n"

                    .     $question->getQuestionAnswerHtml()

                    .     '</td>' . "\n"
                    .     '</tr>' . "\n\n";
                }
            }
            else // all questions on on page
            {
                $out .= '<tr class="headerX">' . "\n"
                .     '<th>'
                .     get_lang('Question') . ' ' . $questionIterator
                .     '</th>' . "\n"
                .     '</tr>' . "\n\n";

                $out .= '<tr>'
                .     '<td>' . "\n"

                .     $question->getQuestionAnswerHtml()

                .     '</td>' . "\n"
                .     '</tr>' . "\n\n";
            }

        }
        // table footer, form footer
        $out .= '<tr>' . "\n"
        .     '<td align="center">';

        if( $exercise->getDisplayType() == 'SEQUENTIAL' )
        {
            if( $step > 1 )
            {
                $out .= '<input type="submit" name="cmdBack" value="&lt; '.get_lang('Previous question').'" />&nbsp;' . "\n";
            }

            if( $step < $questionCount )
            {
                $out .= '<input type="submit" name="cmdNext" value="'.get_lang('Next question').' &gt;" />' . "\n";
            }

            $out .= '<p><input type="submit" name="cmdOk" value="'.get_lang('Submit all and finish').'" /></p>' . "\n";
        }
        else
        {
            $out .= '<input type="submit" name="cmdOk" value="'.get_lang('Finish the test').'" />' . "\n";
        }

        $out .= '</td>' . "\n"
        .     '</tr>' . "\n\n"
        .     '</table>' . "\n\n"
        .     '</form>' . "\n\n";

    }
}
else // ! $showSubmitForm
{
    if( (!isset($_SESSION['inPathMode']) || !$_SESSION['inPathMode']) && !$inLP )
    {
        $dialogBox->info('<a href="./exercise.php">&lt;&lt; '.get_lang('Back').'</a>');
    }
    $out .= $dialogBox->render();
}

$claroline->display->body->appendContent($out);

echo $claroline->display->render();

?>