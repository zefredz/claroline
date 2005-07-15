<?php // $Id$
/**
 * CLAROLINE
 *
 * @version 1.7
 *
 * @copyright 2001-2005 Universite catholique de Louvain (UCL)
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

// exo_id is required
if( empty($_REQUEST['exo_id']) ) header("Location: ../exercice/exercice.php");

include('../exercice/exercise.class.php');
/**
 * DB tables definition
 */
$tbl_mdb_names = claro_sql_get_main_tbl();
$tbl_rel_course_user = $tbl_mdb_names['rel_course_user'  ];
$tbl_user            = $tbl_mdb_names['user'             ];

$tbl_cdb_names = claro_sql_get_course_tbl();
$tbl_quiz_test      	= $tbl_cdb_names['quiz_test'              ];
$tbl_quiz_question      = $tbl_cdb_names['quiz_question'          ];
$tbl_quiz_rel_test_question  = $tbl_cdb_names['quiz_rel_test_question' ];
$tbl_track_e_exercices 	= $tbl_cdb_names['track_e_exercices'];
$tbl_track_e_exe_details = $tbl_cdb_names['track_e_exe_details'];
$tbl_track_e_exe_answers = $tbl_cdb_names['track_e_exe_answers'];


$is_allowedToTrack = $is_courseAdmin;

// get exercise details
$exercise = new Exercise();
$exercise->read($_REQUEST['exo_id']);


$interbredcrump[]= array ("url"=>"courseLog.php", "name"=> $langStatistics);
$nameTools = $langStatsOfExercise;

include($includePath."/claro_init_header.inc.php");
// display title
$titleTab['mainTitle'] = $nameTools;
$titleTab['subTitle'] = $exercise->selectTitle();
echo claro_disp_tool_title($titleTab);

if($is_allowedToTrack && $is_trackingEnabled) 
{

  // get global infos about scores in the exercise
  $sql = "SELECT  MIN(TEX.`exe_result`) AS `minimum`, 
                MAX(TEX.`exe_result`) AS `maximum`, 
                AVG(TEX.`exe_result`) AS `average`,
                MAX(TEX.`exe_weighting`) AS `weighting` ,
                COUNT(DISTINCT TEX.`exe_user_id`) AS `users`,
                COUNT(TEX.`exe_user_id`) AS `tusers`,
				AVG(`TEX`.`exe_time`) AS `avgTime`
        FROM `".$tbl_track_e_exercices."` AS TEX
        WHERE TEX.`exe_exo_id` = ".$exercise->selectId()."
                AND TEX.`exe_user_id` IS NOT NULL";
  
  $result = claro_sql_query($sql);
  $exo_scores_details = mysql_fetch_array($result);


	if ( ! isset($exo_scores_details['minimum']) )
	{
		$exo_scores_details['minimum'] = 0;
		$exo_scores_details['maximum'] = 0;
		$exo_scores_details['average'] = 0;
	}
	else
	{
		// round average number for a beautifuler display
		$exo_scores_details['average'] = (round($exo_scores_details['average']*100)/100);
	}

    if (isset($exo_score_details['weighting']) || $exo_scores_details['weighting'] != '')
		$displayedWeighting = '/'.$exo_scores_details['weighting'];
	else
    	$displayedWeighting = '';
		
  	echo '<ul>'."\n"
    .'<li>'.$langScoreMin.' : '.$exo_scores_details['minimum'].$displayedWeighting.'</li>'."\n"
    .'<li>'.$langScoreMax.' : '.$exo_scores_details['maximum'].$displayedWeighting.'</li>'."\n"
    .'<li>'.$langScoreAvg.' : '.$exo_scores_details['average'].$displayedWeighting.'</li>'."\n"
	.'<li>'.$langExeAvgTime.' : '.claro_disp_duration(floor($exo_scores_details['avgTime'])).'</li>'."\n"
	.'</ul>'."\n\n"
	.'<ul>'."\n"
	.'<li>'.$langExerciseUsersAttempts.' : '.$exo_scores_details['users'].'</li>'."\n"
	.'<li>'.$langExerciseTotalAttempts.' : '.$exo_scores_details['tusers'].'</li>'."\n"
	.'</ul>'."\n\n";

	//-- display details : USERS VIEW
	$sql = "SELECT `U`.`nom`, `U`.`prenom`, `U`.`user_id`,
	        MIN(TE.`exe_result`) AS `minimum`,
	        MAX(TE.`exe_result`) AS `maximum`,
	        AVG(TE.`exe_result`) AS `average`,
	        COUNT(TE.`exe_result`) AS `attempts`,
			AVG(TE.`exe_time`) AS `avgTime`
	FROM `".$tbl_user."` AS `U`, `".$tbl_rel_course_user."` AS `CU`, `".$tbl_quiz_test."` AS `QT`
	LEFT JOIN `".$tbl_track_e_exercices."` AS `TE`
	      ON `CU`.`user_id` = `TE`.`exe_user_id`
	      AND `QT`.`id` = `TE`.`exe_exo_id`
	WHERE `CU`.`user_id` = `U`.`user_id`
	  AND `CU`.`code_cours` = '".$_cid."'
	  AND (
	        `TE`.`exe_exo_id` = ".$exercise->selectId()."
	        OR
	        `TE`.`exe_exo_id` IS NULL
	      )
	GROUP BY `U`.`user_id`
	ORDER BY `U`.`nom` ASC, `U`.`prenom` ASC";
    
    
	$exo_users_details = claro_sql_query_fetch_all($sql);

	echo '<p><b>'.$langStatsByUser.'</b></p>'."\n";
	// display tab header
	echo '<table class="claroTable" width="100%" border="0" cellspacing="2">'."\n"
		.'<tr class="headerX" align="center" valign="top">'."\n"
	    .'<th>'.$langStudent.'</th>'."\n"
	    .'<th>'.$langScoreMin.'</th>'."\n"
	    .'<th>'.$langScoreMax.'</th>'."\n"
	    .'<th>'.$langScoreAvg.'</th>'."\n"
	    .'<th>'.$langAttempts.'</th>'."\n"
	    .'<th>'.$langExeAvgTime.'</th>'."\n"
	  	.'</tr>'."\n"
	  	.'<tbody>'."\n\n";
	  	
	// display tab content
	foreach( $exo_users_details as $exo_users_detail )
	{
		if ( $exo_users_detail['minimum'] == '' )
		{
			$exo_users_detail['minimum'] = 0;
			$exo_users_detail['maximum'] = 0;
		}
		echo 	 '<tr>'."\n"
		  		.'<td><a href="userLog.php?uInfo='.$exo_users_detail['user_id'].'&view=0100000&exoDet='.$exercise->selectId().'">'."\n"
				.$exo_users_detail['nom'].' '.$exo_users_detail['prenom'].'</a></td>'."\n"
		  		.'<td>'.$exo_users_detail['minimum'].'</td>'."\n"
		  		.'<td>'.$exo_users_detail['maximum'].'</td>'."\n"
		  		.'<td>'.(round($exo_users_detail['average']*100)/100).'</td>'."\n"
		  		.'<td>'.$exo_users_detail['attempts'].'</td>'."\n"
		  		.'<td>'.claro_disp_duration(floor($exo_users_detail['avgTime'])).'</td>'."\n"
				.'</tr>'."\n";
	}
	// foot of table
	echo '</tbody>'."\n".'</table>'."\n\n";

	// display details : QUESTIONS VIEW
	$sql = "SELECT `Q`.`id`, `Q`.`question`, `Q`.`type`, `Q`.`ponderation`,
          		MIN(TED.`result`) AS `minimum`,
				MAX(TED.`result`) AS `maximum`,
				AVG(TED.`result`) AS `average`
		FROM `".$tbl_quiz_question."` AS `Q`, `".$tbl_quiz_rel_test_question."` AS `RTQ`
		LEFT JOIN `".$tbl_track_e_exercices."` AS `TE`
		    ON `TE`.`exe_exo_id` = `RTQ`.`exercice_id`
		LEFT JOIN `".$tbl_track_e_exe_details."` AS `TED`
      		ON `TED`.`exercise_track_id` = `TE`.`exe_id`
			AND `TED`.`question_id` = `Q`.`id`
		WHERE `Q`.`id` = `RTQ`.`question_id`
			AND `RTQ`.`exercice_id` = ".$exercise->selectId()."
		GROUP BY `Q`.`id`
		ORDER BY `Q`.`q_position` ASC";

	$exo_questions_details = claro_sql_query_fetch_all($sql);

	echo '<p><b>'.$langStatsByQuestion.'</b></p>'."\n";
	// display tab header
	echo '<table class="claroTable" width="100%" border="0" cellspacing="2">'."\n"
		.'<tr class="headerX" align="center" valign="top">'."\n"
	    .'<th>'.$langQuestionTitle.'</th>'."\n"
	    .'<th>'.$langScoreMin.'</th>'."\n"
	    .'<th>'.$langScoreMax.'</th>'."\n"
	    .'<th>'.$langScoreAvg.'</th>'."\n"
	  	.'</tr>'."\n"
	  	.'<tbody>'."\n";
	// display tab content
	foreach ( $exo_questions_details as $exo_questions_detail )
	{
		if ( $exo_questions_detail['minimum'] == '' )
		{
			$exo_questions_detail['minimum'] = 0;
			$exo_questions_detail['maximum'] = 0;
		}
		echo 	 '<tr>'."\n"
		  		.'<td><a href="questions_details.php?question_id='.$exo_questions_detail['id'].'&exo_id='.$_REQUEST['exo_id'].'">'.$exo_questions_detail['question'].'</a></td>'."\n"
		  		.'<td>'.$exo_questions_detail['minimum'].'/'.$exo_questions_detail['ponderation'].'</td>'."\n"
		  		.'<td>'.$exo_questions_detail['maximum'].'/'.$exo_questions_detail['ponderation'].'</td>'."\n"
		  		.'<td>'.(round($exo_questions_detail['average']*100)/100).'/'.$exo_questions_detail['ponderation'].'</td>'."\n"
				.'</tr>'."\n";
	}
	// foot of table
	echo '</tbody>'."\n".'</table>'."\n\n";
}
// not allowed
else
{
    if(!$is_trackingEnabled)
    {
        echo $langTrackingDisabled;
    }
    else
    {
        echo $langNotAllowed;
    }
}

include($includePath."/claro_init_footer.inc.php");
?>
