<?php // $Id$
/**
 * CLAROLINE
 * @version 1.8
 *
 * @copyright (c) 2001-2006 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @package ADMIN
 *
 * @author claro team <cvs@claroline.net>
 */

require '../../inc/claro_init_global.inc.php';

if ( !$_cid || !$is_courseAllowed ) claro_disp_auth_form(true);

//SECURITY CHECK

if ( ! $_uid ) claro_disp_auth_form();
if ( ! $is_courseAdmin ) claro_die(get_lang('Not allowed'));

//DECLARE NEEDED LIBRARIES

require_once $includePath . '/lib/fileManage.lib.php';
require_once $includePath . '/lib/fileUpload.lib.php';
include_once $includePath . '/lib/fileDisplay.lib.php';

require_once 'exercise_import.inc.php';
include_once '../lib/exercise.class.php';
include_once '../lib/question.class.php';
include_once 'qti2/qti2_classes.php';

//SQL table name

$tbl_name        = claro_sql_get_course_tbl();

$tbl_exercise              = $tbl_name['qwz_exercise'];
$tbl_question              = $tbl_name['qwz_question'];
$tbl_rel_exercise_question = $tbl_name['qwz_rel_exercise_question'];

// directories
$maxFilledSpace = 100000000;

$courseDir = $coursesRepositorySys . $_course['path'];

// tool libraries

include_once '../lib/exercise.class.php';

//Tool title

$nameTools = get_lang('Import exercise');

//bredcrump

$interbredcrump[]= array ('url' => '../exercise.php','name' => get_lang('Exercises'));

//----------------------------------
// EXECUTE COMMAND
//----------------------------------

$cmd = (isset($_REQUEST['cmd'])? $_REQUEST['cmd'] : 'rqImport');

if( $cmd == 'exImport')
{
    $result_log = array();
    
    $importedExId = import_exercise($_FILES['uploadedExercise']['name'], $result_log);
    if( $importedExId )
    {
        $display = '<p>'
        .   '<a href="../exercise_submit.php?exId='.$importedExId.'">'.get_lang('See the exercise').'</a>'
        .   '</p>' . "\n";
    }
    else
    {
        $cmd = 'rqImport';
    }

    $dialogBox = '';
    foreach ($result_log as $log)
    {
        $dialogBox .= $log . '<br>';
    }
}

if( $cmd == 'rqImport' )
{
    $display = '<p>' . "\n"
    .            get_lang('Imported exercises must consist of a zip file (IMS-QTI).') . '<br />' . "\n"
    .            '</p>' . "\n"
    .            '<form enctype="multipart/form-data" action="" method="post">' . "\n"
    .            '<input name="cmd" type="hidden" value="exImport" />' . "\n"
    .            '<input name="uploadedExercise" type="file" /><br />' . "\n"
    .            '<small>' . get_lang('Max file size') .  ' : ' . format_file_size( get_max_upload_size($maxFilledSpace,$courseDir) ) . '</small>' . "\n"
    .            '<p>' . "\n"
    .            '<input value="' . get_lang('Import exercise') . '" type="submit" /> ' . "\n"
    .            claro_html_button( '../exercise.php', get_lang('Cancel'))
    .            '</p>' . "\n"
    .            '</form>';
}


//----------------------------------
// DISPLAY
//----------------------------------

include $includePath . '/claro_init_header.inc.php';

//display title

echo claro_html_tool_title($nameTools);

//Display Forms or dialog box(if needed)

if ( isset($dialogBox) ) echo claro_html_message_box($dialogBox);

//display content

if (isset($display) ) echo $display;

//footer display

include $includePath . '/claro_init_footer.inc.php';

?>
