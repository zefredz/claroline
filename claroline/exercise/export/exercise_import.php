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

//SECURITY CHECK

if ( ! $_uid ) claro_disp_auth_form();
if ( ! $is_platformAdmin ) claro_die(get_lang('Not allowed'));

//DECLARE NEEDED LIBRARIES

require_once $includePath . '/lib/fileManage.lib.php';
require_once $includePath . '/lib/fileUpload.lib.php';

require_once 'exercise_import.inc.php';
include_once '../lib/exercise.class.php';
include_once '../lib/question.class.php';
include_once 'qti/qti_classes.php';

//SQL table name

$tbl_name        = claro_sql_get_course_tbl();

$tbl_exercise              = $tbl_name['qwz_exercise'];
$tbl_question              = $tbl_name['qwz_question'];
$tbl_rel_exercise_question = $tbl_name['qwz_rel_exercise_question'];

// tool libraries

include_once '../lib/exercise.class.php';

//Tool title

$nameTools = get_lang('Import exercise');

//bredcrump

$interbredcrump[]= array ('url' => '../exercise.php','name' => get_lang('Exercises'));

//----------------------------------
// EXECUTE COMMAND
//----------------------------------

$cmd = (isset($_REQUEST['cmd'])? $_REQUEST['cmd'] : 'show_import');

switch ( $cmd )
{
    case 'show_import' :
    {
        $display = '<p>'
        .            get_lang('Imported exercises must consist of a zip or an XML file (IMS-QTI) and be compatible with your Claroline version.') . '<br>'
        .            '</p>'
        .            '<form enctype="multipart/form-data" action="" method="post">'
        .            '<input name="cmd" type="hidden" value="import" />'
        .            '<input name="uploadedExercise" type="file" /><br><br>'
        .            get_lang('Import exercise') . ' : '
        .            '<input value="' . get_lang('Ok') . '" type="submit" /> '
        .            claro_html_button( $_SERVER['PHP_SELF'], get_lang('Cancel'))
        .            '<br><br>'
        .            '<small>' . get_lang('Max file size') . ' :  2&nbsp;MB</small>'
        .            '</form>';
    }
    break;

    case 'import' :
    {
        //include needed librabries for treatment

        $result_log = import_exercise($_FILES['uploadedExercise']['name']);
       
        //display the result message (fail or success)

        $dialogBox = '';

        foreach ($result_log as $log)
        {
            $dialogBox .= $log . '<br>';
        }

    }
    break;
}

//----------------------------------
// FIND INFORMATION
//----------------------------------

//empty!

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