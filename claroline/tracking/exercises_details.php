<?php // $Id$
/**
 * CLAROLINE
 *
 * @version 1.8 $Revision$ 
 * @copyright 2001-2006 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @package CLTRACK
 *
 * @author Claro Team <cvs@claroline.net>
 *
 */
require '../inc/claro_init_global.inc.php';

if ( ! $_cid || ! $is_courseAllowed ) claro_disp_auth_form(true);
if ( ! $is_courseAdmin ) claro_die(get_lang('Not allowed'));

// exo_id is required
if( empty($_REQUEST['exo_id']) )
{
    header("Location: ../exercice/exercice.php");
    exit();
}

include('../exercice/exercise.class.php');
/**
 * DB tables definition
 */
$tbl_mdb_names = claro_sql_get_main_tbl();
$tbl_rel_course_user = $tbl_mdb_names['rel_course_user'  ];
$tbl_user            = $tbl_mdb_names['user'             ];

$tbl_cdb_names = claro_sql_get_course_tbl();
$tbl_quiz_test          = $tbl_cdb_names['quiz_test'              ];
$tbl_quiz_question      = $tbl_cdb_names['quiz_question'          ];
$tbl_quiz_answer        = $tbl_cdb_names['quiz_answer'              ];
$tbl_quiz_rel_test_question  = $tbl_cdb_names['quiz_rel_test_question' ];
$tbl_track_e_exercices     = $tbl_cdb_names['track_e_exercices'];
$tbl_track_e_exe_details = $tbl_cdb_names['track_e_exe_details'];
$tbl_track_e_exe_answers = $tbl_cdb_names['track_e_exe_answers'];

// get exercise details
$exercise = new Exercise();
$exercise->read($_REQUEST['exo_id']);

if( isset($_REQUEST['src']) && $_REQUEST['src'] == 'ex' )
{
    $interbredcrump[]= array ("url"=>"../exercice/exercice.php", "name"=> get_lang('Exercises'));
    $src = '&src=ex';
}
else
{
    $interbredcrump[]= array ("url"=>"courseLog.php", "name"=> get_lang('Statistics'));
    $src = '';
}
    
$nameTools = get_lang('Statistics of exercise');

// get the tracking of a question as a csv file
if( $is_trackingEnabled && isset($_REQUEST['exportCsv']) )
{
    include($includePath.'/lib/export_exe_tracking.class.php');

    // contruction of XML flow
    $csv = export_exercise_tracking($_REQUEST['exo_id']);

    if( isset($csv) )
    {
        header("Content-type: application/csv");
        header('Content-Disposition: attachment; filename="exercise_'. $_REQUEST['exo_id'] . '.csv"');
        echo $csv;
        exit;
    }
}

include($includePath."/claro_init_header.inc.php");

// display title
$titleTab['mainTitle'] = $nameTools;
$titleTab['subTitle'] = $exercise->selectTitle();

echo claro_disp_tool_title($titleTab);

if ( $is_trackingEnabled ) 
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
        WHERE TEX.`exe_exo_id` = ". (int)$exercise->selectId()."
                AND TEX.`exe_user_id` IS NOT NULL";

    $exo_scores_details = claro_sql_query_get_single_row($sql);

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
    .'<li>'.get_lang('Worst score').' : '.$exo_scores_details['minimum'].$displayedWeighting.'</li>'."\n"
    .'<li>'.get_lang('Best score').' : '.$exo_scores_details['maximum'].$displayedWeighting.'</li>'."\n"
    .'<li>'.get_lang('Average score').' : '.$exo_scores_details['average'].$displayedWeighting.'</li>'."\n"
    .'<li>'.get_lang('Average Time').' : '.claro_disp_duration(floor($exo_scores_details['avgTime'])).'</li>'."\n"
    .'</ul>'."\n\n"
    .'<ul>'."\n"
    .'<li>'.get_lang('User attempts').' : '.$exo_scores_details['users'].'</li>'."\n"
    .'<li>'.get_lang('Total attempts').' : '.$exo_scores_details['tusers'].'</li>'."\n"
    .'</ul>'."\n\n";
    
    echo '<ul>'."\n"
    .'<li><a href="'.$_SERVER['PHP_SELF'].'?exportCsv=1&exo_id='.$_REQUEST['exo_id'].'">'.get_lang('Get tracking data in a CSV file').'</a></li>'."\n"
    .'</ul>'."\n\n";

    //-- display details : USERS VIEW
    $sql = "SELECT `U`.`nom`, `U`.`prenom`, `U`.`user_id`,
            MIN(TE.`exe_result`) AS `minimum`,
            MAX(TE.`exe_result`) AS `maximum`,
            AVG(TE.`exe_result`) AS `average`,
            COUNT(TE.`exe_result`) AS `attempts`,
            AVG(TE.`exe_time`) AS `avgTime`
    FROM (`".$tbl_user."` AS `U`, `".$tbl_rel_course_user."` AS `CU`, `".$tbl_quiz_test."` AS `QT`)
    LEFT JOIN `".$tbl_track_e_exercices."` AS `TE`
          ON `CU`.`user_id` = `TE`.`exe_user_id`
          AND `QT`.`id` = `TE`.`exe_exo_id`
    WHERE `CU`.`user_id` = `U`.`user_id`
      AND `CU`.`code_cours` = '".$_cid."'
      AND (
            `TE`.`exe_exo_id` = ". (int)$exercise->selectId()."
            OR
            `TE`.`exe_exo_id` IS NULL
          )
    GROUP BY `U`.`user_id`
    ORDER BY `U`.`nom` ASC, `U`.`prenom` ASC";
    
    
    $exo_users_details = claro_sql_query_fetch_all($sql);

    echo '<p><b>'.get_lang('Statistics by user').'</b></p>'."\n";
    // display tab header
    echo '<table class="claroTable" width="100%" border="0" cellspacing="2">'."\n\n"
        .'<tr class="headerX" align="center" valign="top">'."\n"
        .'<th>'.get_lang('Student').'</th>'."\n"
        .'<th>'.get_lang('Worst score').'</th>'."\n"
        .'<th>'.get_lang('Best score').'</th>'."\n"
        .'<th>'.get_lang('Average score').'</th>'."\n"
        .'<th>'.get_lang('Attempts').'</th>'."\n"
        .'<th>'.get_lang('Average Time').'</th>'."\n"
          .'</tr>'."\n\n"
          .'<tbody>'."\n\n";
          
    // display tab content
    foreach( $exo_users_details as $exo_users_detail )
    {
        if ( $exo_users_detail['attempts'] == 0 )
        {
            $exo_users_detail['minimum'] = '-';
            $exo_users_detail['maximum'] = '-';
            $displayedAverage = '-';
            $displayedAvgTime = '-';
        }
        else
        {
        	$displayedAverage = round($exo_users_detail['average']*100)/100;
        	$displayedAvgTime = claro_disp_duration(floor($exo_users_detail['avgTime']));	
        }
        echo      '<tr>'."\n"
                  .'<td><a href="userLog.php?uInfo='.$exo_users_detail['user_id'].'&view=0100000&exoDet='.$exercise->selectId().'">'."\n"
                .$exo_users_detail['nom'].' '.$exo_users_detail['prenom'].'</a></td>'."\n"
                  .'<td>'.$exo_users_detail['minimum'].'</td>'."\n"
                  .'<td>'.$exo_users_detail['maximum'].'</td>'."\n"
                  .'<td>'.$displayedAverage.'</td>'."\n"
                  .'<td>'.$exo_users_detail['attempts'].'</td>'."\n"
                  .'<td>'.$displayedAvgTime.'</td>'."\n"
                .'</tr>'."\n\n";
    }
    // foot of table
    echo '</tbody>'."\n".'</table>'."\n\n";

    // display details : QUESTIONS VIEW
    $sql = "SELECT `Q`.`id`, `Q`.`question`, `Q`.`type`, `Q`.`ponderation`,
                  MIN(TED.`result`) AS `minimum`,
                MAX(TED.`result`) AS `maximum`,
                AVG(TED.`result`) AS `average`
        FROM (`".$tbl_quiz_question."` AS `Q`, `".$tbl_quiz_rel_test_question."` AS `RTQ`)
        LEFT JOIN `".$tbl_track_e_exercices."` AS `TE`
            ON `TE`.`exe_exo_id` = `RTQ`.`exercice_id`
        LEFT JOIN `".$tbl_track_e_exe_details."` AS `TED`
              ON `TED`.`exercise_track_id` = `TE`.`exe_id`
            AND `TED`.`question_id` = `Q`.`id`
        WHERE `Q`.`id` = `RTQ`.`question_id`
            AND `RTQ`.`exercice_id` = ". (int)$exercise->selectId()."
        GROUP BY `Q`.`id`
        ORDER BY `Q`.`q_position` ASC";

    $exo_questions_details = claro_sql_query_fetch_all($sql);

    echo '<p><b>'.get_lang('Statistics by question').'</b></p>'."\n";
    // display tab header
    echo '<table class="claroTable" width="100%" border="0" cellspacing="2">'."\n"
        .'<tr class="headerX" align="center" valign="top">'."\n"
        .'<th>'.get_lang('Question title').'</th>'."\n"
        .'<th>'.get_lang('Worst score').'</th>'."\n"
        .'<th>'.get_lang('Best score').'</th>'."\n"
        .'<th>'.get_lang('Average score').'</th>'."\n"
          .'</tr>'."\n\n"
          .'<tbody>'."\n\n";
    // display tab content
    foreach ( $exo_questions_details as $exo_questions_detail )
    {
        if ( $exo_questions_detail['minimum'] == '' )
        {
            $exo_questions_detail['minimum'] = 0;
            $exo_questions_detail['maximum'] = 0;
        }
        echo      '<tr>'."\n"
                  .'<td><a href="questions_details.php?question_id='.$exo_questions_detail['id'].'&exo_id='.$_REQUEST['exo_id'].$src.'">'.$exo_questions_detail['question'].'</a></td>'."\n"
                  .'<td>'.$exo_questions_detail['minimum'].'/'.$exo_questions_detail['ponderation'].'</td>'."\n"
                  .'<td>'.$exo_questions_detail['maximum'].'/'.$exo_questions_detail['ponderation'].'</td>'."\n"
                  .'<td>'.(round($exo_questions_detail['average']*100)/100).'/'.$exo_questions_detail['ponderation'].'</td>'."\n"
                .'</tr>'."\n\n";
    }
    // foot of table
    echo '</tbody>'."\n\n".'</table>'."\n\n";
}
else
{
    echo get_lang('Tracking has been disabled by system administrator.');
}

include($includePath."/claro_init_footer.inc.php");
?>
