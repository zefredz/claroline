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

$tlabelReq = 'CLQWZ___';
 
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
		 
		include('./export/qti/qti_export.php');
    
	    // Get the corresponding XML
	    $xml = export_exercise($_REQUEST['exId']);

	    // Send it if we got something. Otherwise, just continue as if nothing happened.
	    if(!empty($xml))
	    {
	        header("Content-type: application/xml");
	        header('Content-Disposition: attachment; filename="quiz_'. http_response_splitting_workaround( $_REQUEST['exId'] ) . '.xml"');
	        echo $xml;
	        exit();
	    }
	}
	
	//-- delete
	if( $_REQUEST['cmd'] == 'exDel' )
	{
		$exercise = new Exercise();
		$exercise->load($_REQUEST['exId']);
		
		$exercise->delete();
	}
		
	//-- change visibility
	if( $_REQUEST['cmd'] == 'exMkVis' )
	{
		Exercise::updateExerciseVisibility($_REQUEST['exId'],'VISIBLE');
	}
	
	if( $_REQUEST['cmd'] == 'exMkInvis' )
	{
		Exercise::updateExerciseVisibility($_REQUEST['exId'],'INVISIBLE');
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
				.	 '<a href="../tracking/exercises_details.php?exo_id='.$anExercise['id'].'&amp;src=ex">'
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

