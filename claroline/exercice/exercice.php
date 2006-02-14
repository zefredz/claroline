<?php // $Id$
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.6
      +----------------------------------------------------------------------+
      | Copyright (c) 2001-2006 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
      |   This program is free software; you can redistribute it and/or      |
      |   modify it under the terms of the GNU General Public License        |
      |   as published by the Free Software Foundation; either version 2     |
      |   of the License, or (at your option) any later version.             |
      +----------------------------------------------------------------------+
      | Authors: Olivier Brouckaert <oli.brouckaert@skynet.be>               |
      +----------------------------------------------------------------------+
*/

        /*>>>>>>>>>>>>>>>>>>>> EXERCISE LIST <<<<<<<<<<<<<<<<<<<<*/

/**
 * This script shows the list of exercises for administrators and students.
 */

include('exercise.class.php');
include('question.class.php');
include('answer.class.php');

include('exercise.lib.php');

$tlabelReq = 'CLQWZ___';

require '../inc/claro_init_global.inc.php';

include($includePath."/lib/pager.lib.php");

/*******************************/
/* Clears the exercise session */
/*******************************/

unset($_SESSION['objExercise'    ]);
unset($_SESSION['objQuestion'    ]);
unset($_SESSION['objAnswer'        ]);        
unset($_SESSION['questionList'    ]);
unset($_SESSION['exerciseResult']);
unset($_SESSION['exeStartTime'    ]);

// prevent inPathMode to be used when browsing an exercise in the exercise tool
$_SESSION['inPathMode'] = false;

if ( !$_cid || !$is_courseAllowed ) claro_disp_auth_form(true);

claro_set_display_mode_available(true);

$is_allowedToEdit = claro_is_allowed_to_edit();
$is_allowedToTrack = claro_is_allowed_to_edit() && $is_trackingEnabled;

/*
 * DB tables definition
 */

$tbl_cdb_names = claro_sql_get_course_tbl();
$tbl_lp_learnPath            = $tbl_cdb_names['lp_learnPath'           ];
$tbl_lp_rel_learnPath_module = $tbl_cdb_names['lp_rel_learnPath_module'];
$tbl_lp_user_module_progress = $tbl_cdb_names['lp_user_module_progress'];
$tbl_lp_module               = $tbl_cdb_names['lp_module'              ];
$tbl_lp_asset                = $tbl_cdb_names['lp_asset'               ];
$tbl_quiz_answer             = $tbl_cdb_names['quiz_answer'            ];
$tbl_quiz_question           = $tbl_cdb_names['quiz_question'          ];
$tbl_quiz_rel_test_question  = $tbl_cdb_names['quiz_rel_test_question' ];
$tbl_quiz_test               = $tbl_cdb_names['quiz_test'              ];
$tbl_track_e_exercises       = $tbl_cdb_names['track_e_exercices'      ];

// maximum number of exercises on a same page
$exercisesPerPage = 25;

$nameTools = get_lang('Exercises');

/* Asking for an export in IMS/QTI ?
 * We need to take care of it before any content has been sent.
 */
if( isset($_REQUEST['export']) && get_conf('enableExerciseExportQTI') )
{
    include('exercise_export.php');
    
    // Get the corresponding XML
    $xml = export_exercise($_REQUEST['export']);

    // Send it if we got something. Otherwise, just continue as if nothing happened.
    if(!empty($xml))
    {
        header("Content-type: application/xml");
        header('Content-Disposition: attachment; filename="quiz_'. http_response_splitting_workaround( $_REQUEST['export'] ) . '.xml"');
        echo $xml;
        exit();
    }
}

include($includePath.'/claro_init_header.inc.php');

event_access_tool($_tid, $_courseTool['label']);

// need functions of statsutils lib to display previous exercices scores
include($includePath.'/lib/statsUtils.lib.inc.php');

echo claro_disp_tool_title($nameTools, $is_allowedToEdit ? 'help_exercise.php' : false);

// defines answer type for previous versions of Claroline, may be removed in Claroline 1.5
$sql = "UPDATE `".$tbl_quiz_question."`
    SET `q_position` = '1', `type` = '2'
    WHERE `q_position` IS NULL
         OR `q_position` < '1'
         OR `type` = '0'";
claro_sql_query($sql) or die("Error : UPDATE at line ".__LINE__);

// only for administrator
if($is_allowedToEdit)
{
    if(!empty($_REQUEST['choice']))
    {
        // construction of Exercise
        $objExerciseTmp = new Exercise();

        if($objExerciseTmp->read($_REQUEST['exerciseId']))
        {
            switch($_REQUEST['choice'])
            {
                case 'delete':    
                                // deletes an exercise
                
                                $objExerciseTmp->delete();

                                //notify manager that the exercise is deleted
                                
                                $eventNotifier->notifyCourseEvent("exercise_deleted",$_cid, $_tid, $objExerciseTmp->selectId(), $_gid, "0");
                                
                                //if some learning path must be deleted too, just do it
                                if (isset($_REQUEST['lpmDel']) && $_REQUEST['lpmDel']=='true')
                                {
                                    //get module_id concerned (by the asset)...
                                    $sql = "SELECT `module_id`
                                            FROM `".$tbl_lp_asset."`
                                            WHERE `path` = '". addslashes($_REQUEST['exerciseId']) ."'";
                                    $aResult = claro_sql_query($sql);
                                    $aList = mysql_fetch_array($aResult);
                                    $idOfModule = $aList['module_id'];

                                    // delete the asset
                                    $sql = "DELETE
                                            FROM `".$tbl_lp_asset."`
                                            WHERE `path` = '". addslashes($_REQUEST['exerciseId']) ."'";
                                    claro_sql_query($sql);

                                    // delete the module
                                    $sql = "DELETE
                                            FROM `".$tbl_lp_module."`
                                            WHERE `module_id` = ". (int)$idOfModule ."";
                                    claro_sql_query($sql);

                                    // find the learning path module(s) concerned
                                    $sql = "SELECT *
                                            FROM `".$tbl_lp_rel_learnPath_module."`
                                            WHERE `module_id` = ". (int)$idOfModule ."";

                                    $lpmResult = claro_sql_query($sql);

                                    // delete any user progression info for this/those learning path module(s)
                                    $sql = "DELETE
                                            FROM `".$tbl_lp_user_module_progress."`
                                            WHERE
                                          ";
                                     while ($lpmList = mysql_fetch_array($lpmResult))
                                     {
                                        $sql.="`learnPath_module_id` = '". (int)$lpmList['learnPath_module_id']."' OR ";
                                     }
                                     $sql.=" 0=1 ";
                                     claro_sql_query($sql);

                                     // delete the learning path module(s)
                                    $sql = "DELETE
                                            FROM `".$tbl_lp_rel_learnPath_module."`
                                            WHERE `module_id`=" . (int)$idOfModule . "";
                                    claro_sql_query($sql);

                                } //end if at least in one learning path
                                break;
                case 'enable':  // enables an exercise
                                $objExerciseTmp->enable();
                                $objExerciseTmp->save();
                                $eventNotifier->notifyCourseEvent("exercise_visible",$_cid, $_tid, $objExerciseTmp->selectId(), $_gid, "0");

                                break;
                case 'disable': // disables an exercise
                                $objExerciseTmp->disable();
                                $objExerciseTmp->save();
                                $eventNotifier->notifyCourseEvent("exercise_invisible",$_cid, $_tid, $objExerciseTmp->selectId(), $_gid, "0");

                                break;
            }
        }

        // destruction of Exercise
        unset($objExerciseTmp);
    }

    $sql = 'SELECT `id`, `titre`, `type`, `active` 
              FROM `'.$tbl_quiz_test.'` 
              ORDER BY `id`';
}
// only for students
else
{
  if ($_uid)
  {
    $sql = 'SELECT `id`, `titre`, `type` 
              FROM `'.$tbl_quiz_test.'` 
              WHERE `active` = "1"
              ORDER BY `id`';
  }
  else // anonymous user
  {
    $sql = 'SELECT `id`, `titre`, `type` 
              FROM `'.$tbl_quiz_test.'` 
              WHERE     `active`="1"
                     AND `anonymous_attempts`="YES" 
              ORDER BY `id`';
  }
}

// pager initialisation
if( !isset($_REQUEST['offset']) )     $offset = 0;
else                                 $offset = $_REQUEST['offset'];


$myPager = new claro_sql_pager($sql, $offset, $exercisesPerPage);
$exercisesList = $myPager->get_result_list();


// commands
echo '<p>'."\n";
// if tracking is enabled && user is not anomymous
if($is_trackingEnabled && $_uid)
{
   echo '<a class="claroCmd" href="../tracking/userLog.php?uInfo='.$_uid.'&amp;view=0100000">'.get_lang('My results').'</a>';
   if( $is_allowedToEdit ) echo ' | ';
   echo "\n";
}
if($is_allowedToEdit)
{
    echo '<a class="claroCmd" href="admin.php">'.get_lang('New exercise').'</a> | '."\n"
        .'<a class="claroCmd" href="question_pool.php">'.get_lang('Question pool').'</a>'."\n";
}
echo '</p>'."\n\n";
//pager display
echo $myPager->disp_pager_tool_bar($_SERVER['PHP_SELF']);

?>


<table class="claroTable emphaseLine" border="0" align="center" cellpadding="2" cellspacing="2" width="100%">

<thead>
<tr class="headerX">
  <th><?php echo get_lang('Exercise name'); ?></th>
<?php
    if($is_allowedToEdit)
    {
?>
  <th><?php echo get_lang('Modify'); ?></th>
  <th><?php echo get_lang('Delete'); ?></th>
  <th><?php echo get_lang('Enable').' / '.get_lang('Disable'); ?></th>
<?php
        if( get_conf('enableExerciseExportQTI') )
        {
              echo '<th>'.get_lang('Export').'</th>'."\n";
        }

    }
    
      if($is_allowedToTrack)
      {
?>
  <th><?php echo get_lang('Tracking'); ?></th>
<?php
      }
?>
</tr>
</thead>
<?php

if( !is_array($exercisesList) || count($exercisesList) == 0 )
{
    if($is_allowedToEdit && get_conf('enableExerciseExportQTI') )
        $colspan = ' colspan="6"';
    elseif( $is_allowedToEdit && get_conf('enableExerciseExportQTI') )
        $colspan = ' colspan="5"';
    else
        $colspan = '';
?>
<tbody>
<tr>
  <td<?php echo $colspan; ?>><?php echo get_lang('There is no exercise for the moment'); ?></td>
</tr>
</tbody>
<?php
}



// see if exercises are used in learning path and must be protected by a confirm alert

$sql = "SELECT *,A.`path` AS thePath
          FROM `".$tbl_lp_rel_learnPath_module."` AS LPM, `".$tbl_lp_asset."` AS A, `".$tbl_lp_module."` AS M
          WHERE M.`contentType` = 'EXERCISE'
                AND A.`module_id` = M.`module_id`
                AND LPM.`module_id` = M.`module_id`";

$res = claro_sql_query($sql);

// build an array of action to add to link of deletion for each exercise included in a learning path.

$actionsForDelete[] = array();
while ($list = mysql_fetch_array($res))
{
    $exId = $list['thePath'];
    $toAdd = clean_str_for_javascript(get_block('blockUsedInSeveralPath')
    		." ".get_lang('Are you sure you want to delete this exercise ?'));
    $actionsForDelete[$exId] = "onclick=\"javascript:if(!confirm('".$toAdd."')) return false;\"";
}
$defaultConfirm = "onclick=\"javascript:if(!confirm('".clean_str_for_javascript(get_lang('Are you sure you want to delete this exercise ?'))."')) return false;\"";


$i = 1;
// while list exercises

if (isset($_uid)) $date = $claro_notifier->get_notification_date($_uid);

foreach( $exercisesList as $exercise )
{
    //modify style if the file is recently added since last login
    if (isset($_uid) && $claro_notifier->is_a_notified_ressource($_cid, $date, $_uid, $_gid, $_tid, $exercise['id']))
    {
        $classItem = ' hot';
    }
    else
    {
        $classItem = '';
    }

?>
<tbody>
<tr>

<?php
    // course admin only
    if($is_allowedToEdit)
    {
           if( !$exercise['active']) // otherwise just display its name normally
        {
            $classItem .= ' invisible';
        }
?>
  <td>
    <img src="<?php echo $imgRepositoryWeb; ?>quiz.gif" />
    <?php echo ( $i + $offset ).'.'; ?>
    <a class="item<?php echo $classItem; ?>" href="exercice_submit.php?exerciseId=<?php echo $exercise['id']; ?>"><?php echo $exercise['titre']; ?></a>
  </td>
  <td align="center">
      <a href="admin.php?exerciseId=<?php echo $exercise['id']; ?>"><img src="<?php echo $imgRepositoryWeb ?>edit.gif" border="0" alt="<?php echo htmlspecialchars(get_lang('Modify')); ?>"></a>
  </td>
  <td align="center">
      <a href="<?php echo $_SERVER['PHP_SELF']; ?>?choice=delete&amp;exerciseId=<?php echo $exercise['id']; if (isset($actionsForDelete[$exercise['id']])) { echo "&amp;lpmDel=true";}?>" <?php if (isset($actionsForDelete[$exercise['id']])) { echo $actionsForDelete[$exercise['id']];} else {echo $defaultConfirm;} ?>><img src="<?php echo $imgRepositoryWeb ?>delete.gif" border="0" alt="<?php echo htmlspecialchars(get_lang('Delete')); ?>"></a>
  </td>
<?php
        // if active
        if($exercise['active'])
        {
?>
  <td align="center">
    <a href="<?php echo $_SERVER['PHP_SELF']; ?>?choice=disable&amp;offset=<?php echo $offset; ?>&amp;exerciseId=<?php echo $exercise['id']; ?>"><img src="<?php echo $imgRepositoryWeb ?>visible.gif" border="0" alt="<?php echo htmlspecialchars(get_lang('Disable')); ?>"></a>
  </td>
<?php
        }
        // else if not active
        else
        {
?>
  <td align="center">
    <a href="<?php echo $_SERVER['PHP_SELF']; ?>?choice=enable&amp;offset=<?php echo $offset; ?>&amp;exerciseId=<?php echo $exercise['id']; ?>"><img src="<?php echo $imgRepositoryWeb ?>invisible.gif" border="0" alt="<?php echo htmlspecialchars(get_lang('Enable')); ?>"></a>
  </td>
<?php
        }
        
        if( get_conf('enableExerciseExportQTI') )
        {
?>
  <td align="center">
    <a href="<?php echo $_SERVER['PHP_SELF']; ?>?export=<?php echo $exercise['id']; ?>"><img src="<?php echo $clarolineRepositoryWeb; ?>img/export.gif" border="0" alt="<?php echo get_lang('Export'); ?>"></a>
  </td>

<?php
        }
        
        if($is_allowedToTrack)
        {
  ?>
          <td align="center"><a href="../tracking/exercises_details.php?exo_id=<?php echo $exercise['id']; ?>&amp;src=ex"><img src="<?php echo $clarolineRepositoryWeb ?>img/statistics.gif" border="0" alt="<?php echo htmlspecialchars(get_lang('Tracking')); ?>"></a></td>
     
   <?php
        }
        echo " </tr>";
    }
    // student only
    else
    {
?>
      <td>
        <img src="<?php echo $imgRepositoryWeb; ?>quiz.gif" />
          <?php echo ( $i + $offset ).'.'; ?>&nbsp;
          <a class="item<?php echo $classItem;?>" href="exercice_submit.php?exerciseId=<?php echo $exercise['id']; ?>"><?php echo $exercise['titre']; ?></a>
      </td>

<?php
    }
    $i++;
}    // end while()
?>
</tbody>
</table>

<?php

include($includePath.'/claro_init_footer.inc.php');
?>
