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

$tlabelReq = 'CLQWZ';

require '../inc/claro_init_global.inc.php';

if ( !claro_is_in_a_course() || !claro_is_course_allowed() ) claro_disp_auth_form(true);

claro_set_display_mode_available(true);

$is_allowedToEdit = claro_is_allowed_to_edit();
$is_allowedToTrack = claro_is_allowed_to_edit() && get_conf('is_trackingEnabled');

// tool libraries
include_once './lib/exercise.class.php';
include_once './lib/exercise.lib.php';

// claroline libraries
include_once get_path('incRepositorySys').'/lib/pager.lib.php';

/*
 * DB tables definition
 */

$tbl_cdb_names = get_module_course_tbl( array( 'qwz_exercise', 'qwz_question', 'qwz_rel_exercise_question' ), claro_get_current_course_id() );
$tbl_quiz_exercise = $tbl_cdb_names['qwz_exercise'];

$tbl_cdb_names = claro_sql_get_course_tbl();
$tbl_lp_module = $tbl_cdb_names['lp_module'];
$tbl_lp_asset = $tbl_cdb_names['lp_asset'];


$_SESSION['inPathMode'] = false;

// init request vars
if ( isset($_REQUEST['cmd']) ) $cmd = $_REQUEST['cmd'];
else                           $cmd = null;

if( isset($_REQUEST['exId']) && is_numeric($_REQUEST['exId']) ) $exId = (int) $_REQUEST['exId'];
else                                                            $exId = null;

// init other vars
$maxFilledSpace = 100000000;
$courseDir = get_path('coursesRepositorySys') . claro_get_current_course_data('path');

$dialogBox = new DialogBox();

if( $is_allowedToEdit && !is_null($cmd) )
{
    //-- import
    if( $cmd == 'exImport')
    {
        require_once get_path('incRepositorySys') . '/lib/fileManage.lib.php';
        require_once get_path('incRepositorySys') . '/lib/fileDisplay.lib.php';
        require_once get_path('incRepositorySys') . '/lib/fileUpload.lib.php';
        require_once get_path('incRepositorySys') . '/lib/file.lib.php';

        require_once './export/exercise_import.inc.php';
        require_once './lib/question.class.php';
        require_once './export/qti2/qti2_classes.php';
        require_once get_path('incRepositorySys') . '/lib/backlog.class.php';

        if ( !isset($_FILES['uploadedExercise']['name']) )
        {
            $dialogBox->error( get_lang('Error : no file uploaded') );
        }
        else
        {
            $backlog = new Backlog();
            $importedExId = import_exercise($_FILES['uploadedExercise']['name'], $backlog);

            if( $importedExId )
            {
                $dialogBox->success( '<strong>' . get_lang('Import done') . '</strong>' );
            }
            else
            {
                $dialogBox->error( '<strong>' . get_lang('Import failed') . '</strong>' );
                $cmd = 'rqImport';
            }
            $dialogBox->info( $backlog->output() );
        }
    }

    if( $cmd == 'rqImport' )
    {
        require_once get_path('incRepositorySys') . '/lib/fileDisplay.lib.php';
        require_once get_path('incRepositorySys') . '/lib/fileUpload.lib.php';

        $dialogBox->form("\n"
        .            '<strong>' . get_lang('Import exercise') . '</strong><br />' . "\n"
        .            get_lang('Imported exercises must be an ims-qti zip file.') . '<br />' . "\n"
        .            '<form enctype="multipart/form-data" action="./exercise.php" method="post">' . "\n"
        .            '<input type="hidden" name="claroFormId" value="'.uniqid('').'">'."\n"
        .            '<input name="cmd" type="hidden" value="exImport" />' . "\n"
        .            '<input name="uploadedExercise" type="file" /><br />' . "\n"
        .            '<small>' . get_lang('Max file size') .  ' : ' . format_file_size( get_max_upload_size($maxFilledSpace,$courseDir) ) . '</small>' . "\n"
        .            '<p>' . "\n"
        .            '<input value="' . get_lang('Import exercise') . '" type="submit" /> ' . "\n"
        .            claro_html_button( './exercise.php', get_lang('Cancel'))
        .            '</p>' . "\n"
        .            '</form>' );
    }

    //-- export
    if( $cmd == 'exExport' && get_conf('enableExerciseExportQTI') && $exId )
    {
        include_once './lib/question.class.php';

        require_once './export/qti2/qti2_export.php';
        require_once get_path('incRepositorySys') . '/lib/fileManage.lib.php';
        require_once get_path('incRepositorySys') . '/lib/file.lib.php';

        //find exercise informations

        $exercise= new Exercise();
        $exercise->load($exId);
        $questionList = $exercise->getQuestionList();

        $filePathList = array();

        //prepare xml file of each question

        foreach ($questionList as $question)
        {
            
            $quId = $question['id'];            
            $questionObj = new Qti2Question();
            $questionObj->load($quId);
            
            // contruction of XML flow
            $xml = $questionObj->export();
            // remove trailing slash
            if( substr($questionObj->questionDirSys, -1) == '/' )
            {
                $questionObj->questionDirSys = substr($questionObj->questionDirSys, 0, -1);
            }

            //save question xml file
            if( !file_exists($questionObj->questionDirSys) )
            {
                claro_mkdir($questionObj->questionDirSys,CLARO_FILE_PERMISSIONS);
            }

            if( $fp = @fopen($questionObj->questionDirSys."/question_".$quId.".xml", 'w') )
            {
                fwrite($fp, $xml);
                fclose($fp);
            }
            else
            {
                // interrupt process
            }

            // list of dirs to add in archive
            $filePathList[] = $questionObj->questionDirSys;
        }

        if( !empty($filePathList) )
        {
            require_once get_path('incRepositorySys') . '/lib/thirdparty/pclzip/pclzip.lib.php';

            // build and send the zip
            // TODO use $courseDir ?

            if( sendZip($exercise->title, $filePathList, get_conf('coursesRepositorySys').claro_get_current_course_data('path') . '/exercise/' ) )
            {
                exit();
            }
            else
            {
                $dialogBox->error( get_lang("Unable to create zip file") );
            }
        }
    }

    //-- delete
    if( $cmd == 'exDel' && $exId )
    {
        $exercise = new Exercise();
        $exercise->load($exId);

        $exercise->delete();

        //notify manager that the exercise is deleted

        $eventNotifier->notifyCourseEvent("exercise_deleted",claro_get_current_course_id(), claro_get_current_tool_id(), $exId, claro_get_current_group_id(), "0");

    }

    //-- change visibility
    if( $cmd == 'exMkVis' && $exId )
    {
        Exercise::updateExerciseVisibility($exId,'VISIBLE');
        $eventNotifier->notifyCourseEvent("exercise_visible",claro_get_current_course_id(), claro_get_current_tool_id(), $exId, claro_get_current_group_id(), "0");
    }

    if( $cmd == 'exMkInvis' && $exId )
    {
        Exercise::updateExerciseVisibility($exId,'INVISIBLE');
        $eventNotifier->notifyCourseEvent("exercise_invisible",claro_get_current_course_id(), claro_get_current_tool_id(), $exId, claro_get_current_group_id(), "0");
    }
}

// Save question list
if( $cmd == 'exSaveQwz' )
{
    if( is_null( $exId) )
    {
        $dialogBox->error( get_lang('Error : unable to save the questions list') );
    }
    else
    {
        $exercise = new Exercise();
        if( ! $exercise->load( $exId ) )
        {
            $dialogBox->error( get_lang('Error: unable to load exercise') );
        }
        elseif( isset( $_SESSION['lastRandomQuestionList'] ) )
        {
            
            if ( !$exercise->saveRandomQuestionList( $_SESSION['_user']['userId'], $exercise->getId(), @unserialize($_SESSION['lastRandomQuestionList'])))
            {
                $dialogBox->error( get_lang('Error: unable to save this questions list') );
            }
            else
            {
                $dialogBox->success( get_lang('The list of questions has been saved') );
            }
            unset( $_SESSION['lastRandomQuestionList'] );
        }
        else
        {
            $dialogBox->error( get_lang('Error: no questions list in memory') );
        }
    }
}
/*
 * Get list
 */
// pager initialisation
if( !isset($_REQUEST['offset']) )    $offset = 0;
else                                $offset = $_REQUEST['offset'];

// prepare query
if($is_allowedToEdit)
{
    // we need to check if exercise is used as a module in a learning path
    // to display a more complete confirm message for delete aciton
    $sql = "SELECT E.`id`, E.`title`, E.`visibility`, M.`module_id`
              FROM `".$tbl_quiz_exercise."` AS E
             LEFT JOIN `".$tbl_lp_asset."` AS A
             ON (A.`path` = E.`id` OR A.`path` IS NULL)
             LEFT JOIN `".$tbl_lp_module."` AS M
             ON A.`module_id` = M.`module_id`
                 AND M.`contentType` = 'EXERCISE'
             ORDER BY `id`";
}
// only for students
else
{
  if (claro_is_user_authenticated())
  {
    $sql = "SELECT `id`, `title`
              FROM `".$tbl_quiz_exercise."`
              WHERE `visibility` = 'VISIBLE'
              ORDER BY `id`";
  }
  else // anonymous user
  {
    $sql = "SELECT `id`, `title`
              FROM `".$tbl_quiz_exercise."`
              WHERE `visibility` = 'VISIBLE'
                AND `anonymousAttempts` = 'ALLOWED'
              ORDER BY `id`";
  }
}

$myPager = new claro_sql_pager($sql, $offset, get_conf('exercisesPerPage',25));
$exerciseList = $myPager->get_result_list();


/*
 * Output
 */

$nameTools = get_lang('Exercises');

$noQUERY_STRING = true;
include(get_path('incRepositorySys').'/claro_init_header.inc.php');

echo claro_html_tool_title($nameTools, $is_allowedToEdit ? 'help_exercise.php' : false);

//-- dialogBox
echo $dialogBox->render();

//-- claroCmd
$cmd_menu = array();
if(get_conf('is_trackingEnabled') && claro_is_user_authenticated())
{
    $cmd_menu[] = '<a class="claroCmd" href="../tracking/userReport.php?userId='.claro_get_current_user_id().'"><img src="' . get_icon_url('statistics') . '" alt="" />'.get_lang('My results').'</a>';
}

if($is_allowedToEdit)
{
    $cmd_menu[] = '<a class="claroCmd" href="admin/edit_exercise.php?cmd=rqEdit"><img src="' . get_icon_url('quiz_new') . '" alt="" />' . get_lang('New exercise').'</a>';
    $cmd_menu[] = '<a class="claroCmd" href="admin/question_pool.php"><img src="' . get_icon_url('question_pool') . '" alt="" />'.get_lang('Question pool').'</a>';
    $cmd_menu[] = '<a class="claroCmd" href="exercise.php?cmd=rqImport"><img src="' . get_icon_url('import') . '" alt="" />'.get_lang('Import exercise').'</a>';
}

echo '<p>' . claro_html_menu_horizontal($cmd_menu) . '</p>' . "\n";

//-- pager
echo $myPager->disp_pager_tool_bar($_SERVER['PHP_SELF']);

//-- list

echo '<table class="claroTable emphaseLine" border="0" align="center" cellpadding="2" cellspacing="2" width="100%">' . "\n\n"
.     '<thead>' . "\n"
.     '<tr class="headerX">' . "\n"
.     '<th>' . get_lang('Exercise title') . '</th>' . "\n";

$colspan = 1;

if( $is_allowedToEdit )
{
    echo '<th>' . get_lang('Modify') . '</th>' . "\n"
    .     '<th>' . get_lang('Delete') . '</th>' . "\n"
    .     '<th>' . get_lang('Visibility') . '</th>' . "\n";
    $colspan = 4;

    if( get_conf('enableExerciseExportQTI') )
    {
        echo '<th>' . get_lang('Export') . '</th>' . "\n";
        $colspan++;
    }

    if( $is_allowedToTrack )
    {
        echo '<th>' . get_lang('Statistics') . '</th>' . "\n";
        $colspan++;
    }
}

echo '</tr>' . "\n"
.     '</thead>' . "\n\n"
.     '<tbody>' . "\n\n";

if( claro_is_user_authenticated() ) $notificationDate = $claro_notifier->get_notification_date(claro_get_current_user_id());

if( !empty($exerciseList) )
{
    foreach( $exerciseList as $anExercise )
    {
        if( $is_allowedToEdit && $anExercise['visibility'] == 'INVISIBLE' )
        {
            $invisibleClass = ' class="invisible"';
        }
        else
        {
            $invisibleClass = '';
        }

        //modify style if the file is recently added since last login
        if( claro_is_user_authenticated() && $claro_notifier->is_a_notified_ressource(claro_get_current_course_id(), $notificationDate, claro_get_current_user_id(), claro_get_current_group_id(), claro_get_current_tool_id(), $anExercise['id']) )
        {
            $appendToStyle = ' hot';
        }
        else
        {
            $appendToStyle = '';
        }

        echo '<tr'.$invisibleClass.'>' . "\n"
        .     '<td>'
        .     '<a href="exercise_submit.php?exId='.$anExercise['id'].'" class="item'.$appendToStyle.'">'
        .     '<img src="' . get_icon_url('quiz') . '" alt="" />'
        .     $anExercise['title']
        .     '</a>'
        .     '</td>' . "\n";

        if( $is_allowedToEdit )
        {
            echo '<td align="center">'
            .     '<a href="admin/edit_exercise.php?exId='.$anExercise['id'].'">'
            .     '<img src="' . get_icon_url('edit') . '" alt="'.get_lang('Modify').'" />'
            .     '</a>'
            .     '</td>' . "\n";

            $confirmString = '';
            if( !is_null($anExercise['module_id']) )
            {
                $confirmString .= get_block('blockUsedInSeveralPath') . " ";
            }
            $confirmString .= get_lang('Are you sure you want to delete this exercise ?');

            echo '<td align="center">'
            .     '<a href="exercise.php?exId='.$anExercise['id'].'&amp;cmd=exDel" onclick="javascript:if(!confirm(\''.clean_str_for_javascript($confirmString).'\')) return false;">'
            .     '<img src="' . get_icon_url('delete') . '" alt="'.get_lang('Delete').'" />'
            .     '</a>'
            .     '</td>' . "\n";

            if( $anExercise['visibility'] == 'VISIBLE' )
            {
                echo '<td align="center">'
                .     '<a href="exercise.php?exId='.$anExercise['id'].'&amp;cmd=exMkInvis">'
                .     '<img src="' . get_icon_url('visible') . '" alt="'.get_lang('Make invisible').'" />'
                .     '</a>'
                .     '</td>' . "\n";
            }
            else
            {
                echo '<td align="center">'
                .     '<a href="exercise.php?exId='.$anExercise['id'].'&amp;cmd=exMkVis">'
                .     '<img src="' . get_icon_url('invisible') . '" alt="'.get_lang('Make visible').'" />'
                .     '</a>'
                .     '</td>' . "\n";
            }

            if( get_conf('enableExerciseExportQTI') )
            {
                echo '<td align="center">'
                .     '<a href="exercise.php?exId='.$anExercise['id'].'&amp;cmd=exExport">'
                .     '<img src="' . get_icon_url('export') . '" alt="'.get_lang('Export').'" />'
                .     '</a>'
                .     '</td>' . "\n";
            }

            if( $is_allowedToTrack )
            {
                echo '<td align="center">'
                .     '<a href="track_exercises.php?exId='.$anExercise['id'].'&amp;src=ex">'
                .     '<img src="' . get_icon_url('statistics') . '" alt="'.get_lang('Statistics').'" />'
                .     '</a>'
                .     '</td>' . "\n";
            }
        }

        echo '</tr>' . "\n\n";
    }
}
else
{
    echo '<tr>' . "\n"
    .     '<td colspan="'.$colspan.'">' . get_lang('Empty') . '</td>' . "\n"
    .     '</tr>' . "\n\n";
}

echo '</tbody>' . "\n\n"
.     '</table>' . "\n\n";

//-- pager
echo $myPager->disp_pager_tool_bar($_SERVER['PHP_SELF']);


include(get_path('incRepositorySys').'/claro_init_footer.inc.php');
?>
