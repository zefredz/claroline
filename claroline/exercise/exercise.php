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

if ( !$_cid || !$is_courseAllowed ) claro_disp_auth_form(true);

claro_set_display_mode_available(true);

$is_allowedToEdit = claro_is_allowed_to_edit();
$is_allowedToTrack = claro_is_allowed_to_edit() && get_conf('is_trackingEnabled');

// tool libraries
include_once './lib/exercise.class.php'; 

// claroline libraries
include_once $includePath.'/lib/pager.lib.php';

/*
 * DB tables definition
 */
 
$tbl_cdb_names = claro_sql_get_course_tbl();
$tbl_quiz_exercise = $tbl_cdb_names['qwz_exercise'];

$tbl_lp_module = $tbl_cdb_names['lp_module'];
$tbl_lp_asset = $tbl_cdb_names['lp_asset'];

event_access_tool($_tid, $_courseTool['label']);


/*
 * Execute commands
 */
unset($_SESSION['exercise']);
unset($_SESSION['questionList']);
unset($_SESSION['exeStartTime']);

// prevent inPathMode to be used when browsing an exercise in the exercise tool
$_SESSION['inPathMode'] = false;


if ( isset($_REQUEST['cmd']) ) $cmd = $_REQUEST['cmd'];
else                           $cmd = null;

// each command require exId
if( $is_allowedToEdit && !is_null($cmd) && isset($_REQUEST['exId']) && is_numeric($_REQUEST['exId']) )
{
	//-- export
	if( $_REQUEST['cmd'] == 'exExport' && get_conf('enableExerciseExportQTI') )
	{
		include_once './lib/question.class.php';
		 
		include('./export/qti2/qti2_export.php');
        include_once $includePath.'/lib/fileManage.lib.php';

        //find exercise informations
        
        $exercise= new Exercise();
        $exercise->load($_REQUEST['exId']);
        $questionList = $exercise->getQuestionList();

        $filePathList = array();

        //prepare xml file of each question

        foreach ($questionList as $question)
        {
            $quId = $question['id'];
            $questionObj = new Question();
            $questionObj->load($quId);

            // contruction of XML flow
            $xml = export_question($quId);

            //save question xml file
            $handle = fopen($questionObj->questionDirSys."question_".$quId.".xml", 'w');
            fwrite($handle, $xml);
            fclose($handle);

            //prepare list of file to put in archive

               //do not take the last char if it is a '/'

            $lastChar = $questionObj->questionDirSys{(strlen($questionObj->questionDirSys)-1)};
            if ($lastChar == "/")
            {
                $questionObj->questionDirSys = substr($questionObj->questionDirSys,0,-1);
            }

            $array_file_question = array($questionObj->questionDirSys);
            $filePathList = array_merge($filePathList, $array_file_question);
        }

        /*
         * BUILD THE ZIP ARCHIVE
         */

        require_once $includePath . '/lib/pclzip/pclzip.lib.php';

        //prepare zip

        $downloadPlace = claro_get_data_path(array(CLARO_CONTEXT_COURSE=>$_cid, CLARO_CONTEXT_TOOLLABEL=>'CLQWZ' ));
        $downloadArchivePath = $downloadPlace.'/'.uniqid(true).'.zip';
        $downloadArchiveName = basename('exercise_'.$_REQUEST['exId']).'.zip';
        $downloadArchiveName = str_replace('/', '', $downloadArchiveName);
        
        $downloadArchive     = new PclZip($downloadArchivePath);
        $downloadArchive->add($filePathList,
                          PCLZIP_OPT_REMOVE_PATH,
                          $downloadPlace);

        if ( file_exists($downloadArchivePath) )
        {

            $downloadArchiveSize = filesize($downloadArchivePath);
    
            /*
            * SEND THE ZIP ARCHIVE FOR DOWNLOAD
            */
            
            header('Expires: Wed, 01 Jan 1990 00:00:00 GMT');
            header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
            header('Cache-Control: no-cache, must-revalidate');
            header('Pragma: no-cache');
            header('Content-type: application/zip');
            header('Content-Length: '.$downloadArchiveSize);
            header('Content-Disposition: attachment; filename="'.$downloadArchiveName.'";');
            readfile($downloadArchivePath);
            unlink($downloadArchivePath);
            exit();
        }
        else
        {
            $dialogBox .= get_lang("Unable to create zip file");
        }

	}
	
	//-- delete
	if( $_REQUEST['cmd'] == 'exDel' )
	{
		$exercise = new Exercise();
		$exercise->load($_REQUEST['exId']);
		
		$exercise->delete();

        //notify manager that the exercise is deleted
                                
        $eventNotifier->notifyCourseEvent("exercise_deleted",$_cid, $_tid, $_REQUEST['exId'], $_gid, "0");

	}
		
	//-- change visibility
	if( $_REQUEST['cmd'] == 'exMkVis' )
	{
		Exercise::updateExerciseVisibility($_REQUEST['exId'],'VISIBLE');
        $eventNotifier->notifyCourseEvent("exercise_visible",$_cid, $_tid, $_REQUEST['exId'], $_gid, "0");
	}
	
	if( $_REQUEST['cmd'] == 'exMkInvis' )
	{
		Exercise::updateExerciseVisibility($_REQUEST['exId'],'INVISIBLE');
        $eventNotifier->notifyCourseEvent("exercise_invisible",$_cid, $_tid, $_REQUEST['exId'], $_gid, "0");
	}
}

/*
 * Get list
 */
// pager initialisation
if( !isset($_REQUEST['offset']) )	$offset = 0;
else								$offset = $_REQUEST['offset'];

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
  if ($_uid)
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

include($includePath.'/claro_init_header.inc.php');
 
echo claro_html_tool_title($nameTools, $is_allowedToEdit ? 'help_exercise.php' : false);

//-- claroCmd
$cmd_menu = array();
if(get_conf('is_trackingEnabled') && $_uid)
{
   $cmd_menu[] = '<a class="claroCmd" href="../tracking/userLog.php?uInfo='.$_uid.'&amp;view=0100000">'.get_lang('My results').'</a>';
}

if($is_allowedToEdit)
{
    $cmd_menu[] = '<a class="claroCmd" href="admin/edit_exercise.php?cmd=rqEdit">'.get_lang('New exercise').'</a>';
    $cmd_menu[] = '<a class="claroCmd" href="admin/question_pool.php">'.get_lang('Question pool').'</a>';
    $cmd_menu[] = '<a class="claroCmd" href="export/exercise_import.php">'.get_lang('Import an exercise').'</a>';
}

echo claro_html_menu_horizontal($cmd_menu);

//-- pager
echo $myPager->disp_pager_tool_bar($_SERVER['PHP_SELF']);

//-- list 

echo '<table class="claroTable emphaseLine" border="0" align="center" cellpadding="2" cellspacing="2" width="100%">' . "\n\n"
.	 '<thead>' . "\n"
.	 '<tr class="headerX">' . "\n"
.	 '<th>' . get_lang('Exercise title') . '</th>' . "\n";

$colspan = 1;

if( $is_allowedToEdit )
{
	echo '<th>' . get_lang('Modify') . '</th>' . "\n"
	.	 '<th>' . get_lang('Delete') . '</th>' . "\n"
	.	 '<th>' . get_lang('Visibility') . '</th>' . "\n";
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
.	 '</thead>' . "\n\n"
.	 '<tbody>' . "\n\n";

if( isset($_uid) ) $notificationDate = $claro_notifier->get_notification_date($_uid);

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
	    if( isset($_uid) && $claro_notifier->is_a_notified_ressource($_cid, $notificationDate, $_uid, $_gid, $_tid, $anExercise['id']) )
	    {
	        $appendToStyle = ' hot';
	    }
	    else
	    {
	        $appendToStyle = '';
	    }
	    
		echo '<tr'.$invisibleClass.'>' . "\n"
		.	 '<td class="item'.$appendToStyle.'">'
		.	 '<img src="'.$imgRepositoryWeb.'quiz.gif" alt="" />'
		.	 '<a href="exercise_submit.php?exId='.$anExercise['id'].'">' . $anExercise['title'] . '</a>'
		.	 '</td>' . "\n";
		
		if( $is_allowedToEdit )
		{
			echo '<td align="center">'
			.	 '<a href="admin/edit_exercise.php?exId='.$anExercise['id'].'">'
			.	 '<img src="'.$clarolineRepositoryWeb.'img/edit.gif" border="0" alt="'.get_lang('Modify').'" />'
			.	 '</a>'
			.	 '</td>' . "\n";
			
			$confirmString = '';
			if( !is_null($anExercise['module_id']) )
			{
				$confirmString .= get_block('blockUsedInSeveralPath') . " ";
			}
			$confirmString .= get_lang('Are you sure you want to delete this exercise ?');
			
			echo '<td align="center">'
			.	 '<a href="exercise.php?exId='.$anExercise['id'].'&amp;cmd=exDel" onclick="javascript:if(!confirm(\''.clean_str_for_javascript($confirmString).'\')) return false;">'
			.	 '<img src="'.$clarolineRepositoryWeb.'img/delete.gif" border="0" alt="'.get_lang('Delete').'" />'
			.	 '</a>'
			.	 '</td>' . "\n";
			
			if( $anExercise['visibility'] == 'VISIBLE' )
			{
				echo '<td align="center">'
				.	 '<a href="exercise.php?exId='.$anExercise['id'].'&amp;cmd=exMkInvis">'
				.	 '<img src="'.$clarolineRepositoryWeb.'img/visible.gif" border="0" alt="'.get_lang('Make invisible').'" />'
				.	 '</a>'
				.	 '</td>' . "\n";
			}
			else
			{
				echo '<td align="center">'
				.	 '<a href="exercise.php?exId='.$anExercise['id'].'&amp;cmd=exMkVis">'
				.	 '<img src="'.$clarolineRepositoryWeb.'img/invisible.gif" border="0" alt="'.get_lang('Make visible').'" />'
				.	 '</a>'
				.	 '</td>' . "\n";
			}
			
			if( get_conf('enableExerciseExportQTI') )
		    {
				echo '<td align="center">'
				.	 '<a href="exercise.php?exId='.$anExercise['id'].'&amp;cmd=exExport">'
				.	 '<img src="'.$clarolineRepositoryWeb.'img/export.gif" border="0" alt="'.get_lang('Export').'" />'
				.	 '</a>'
				.	 '</td>' . "\n";				
		    }
		    
		    if( $is_allowedToTrack )
		    {
		    	echo '<td align="center">'
				.	 '<a href="../tracking/exercises_details.php?exId='.$anExercise['id'].'&amp;src=ex">'
				.	 '<img src="'.$clarolineRepositoryWeb.'img/statistics.gif" border="0" alt="'.get_lang('Statistics').'" />'
				.	 '</a>'
				.	 '</td>' . "\n";
		    }
		}
		
		echo '</tr>' . "\n\n";
	}
}
else
{
	echo '<tr>' . "\n"
	.	 '<td colspan="'.$colspan.'">' . get_lang('Empty') . '</td>' . "\n"
	.	 '</tr>' . "\n\n";	
}

echo '</tbody>' . "\n\n"
.	 '</table>' . "\n\n";

//-- pager
echo $myPager->disp_pager_tool_bar($_SERVER['PHP_SELF']);


include($includePath.'/claro_init_footer.inc.php');
?>

