<?php // $Id$
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.6
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2004 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
*/

		/*>>>>>>>>>>>>>>>>>>>> EXERCISE LIST <<<<<<<<<<<<<<<<<<<<*/

/**
 * This script shows the list of exercises for administrators and students.
 */

include('exercise.class.php');
include('question.class.php');
include('answer.class.php');
$tlabelReq='CLQWZ___';
$langFile='exercice';

require '../inc/claro_init_global.inc.php';
if ( ! $_cid) claro_disp_select_course();
if ( ! $is_courseAllowed) claro_disp_auth_form();

/*******************************/
/* Clears the exercise session */
/*******************************/

if(session_is_registered('objExercise'))	{ session_unregister('objExercise');	}
if(session_is_registered('objQuestion'))	{ session_unregister('objQuestion');	}
if(session_is_registered('objAnswer'))		{ session_unregister('objAnswer');		}
if(session_is_registered('questionList'))	{ session_unregister('questionList');	}
if(session_is_registered('exerciseResult'))	{ session_unregister('exerciseResult');	}

// prevent inPathMode to be used when browsing an exercise in the exercise tool
$_SESSION['inPathMode'] = FALSE;

$is_allowedToEdit  = $is_courseAdmin;
$is_allowedToTrack = $is_courseAdmin && $is_trackingEnabled;

$TBL_EXERCICE_QUESTION = $_course['dbNameGlu'].'quiz_rel_test_question';
$TBL_EXERCICES         = $_course['dbNameGlu'].'quiz_test';
$TBL_QUESTIONS         = $_course['dbNameGlu'].'quiz_question';
$TBL_TRACK_EXERCICES   = $_course['dbNameGlu']."track_e_exercices";

// learning path mode table
$TABLELEARNPATH         = $_course['dbNameGlu']."lp_learnPath";
$TABLEMODULE            = $_course['dbNameGlu']."lp_module";
$TABLELEARNPATHMODULE   = $_course['dbNameGlu']."lp_rel_learnPath_module";
$TABLEASSET             = $_course['dbNameGlu']."lp_asset";
$TABLEUSERMODULEPROGRESS= $_course['dbNameGlu']."lp_user_module_progress";

$TABLEEXERCISES               = $_course['dbNameGlu']."exercices";

// maximum number of exercises on a same page
$limitExPage=50;

$htmlHeadXtra[]='<style type="text/css">
<!--
a.invisible
{
	color: #999999;
}
-->
</style>';

$nameTools=$langExercices;
include($includePath.'/claro_init_header.inc.php');

// used for stats
@include($includePath.'/lib/events.lib.inc.php');
event_access_tool($nameTools);

// need functions of statsutils lib to display previous exercices scores
@include($includePath.'/lib/statsUtils.lib.inc.php');

claro_disp_tool_title($nameTools, $is_allowedToEdit ? 'help_exercise.php' : false);

// defines answer type for previous versions of Claroline, may be removed in Claroline 1.5
$sql="UPDATE `$TBL_QUESTIONS` SET q_position='1',type='2' WHERE q_position IS NULL OR q_position<'1' OR type='0'";
mysql_query($sql) or die("Error : UPDATE at line ".__LINE__);

// selects $limitExPage exercises at the same time
$from=$page*$limitExPage;

// only for administrator
if($is_allowedToEdit)
{
	if(!empty($choice))
	{
		// construction of Exercise
		$objExerciseTmp=new Exercise();

		if($objExerciseTmp->read($exerciseId))
		{
			switch($choice)
			{
				case 'delete':	// deletes an exercise
								$objExerciseTmp->delete();

                                //if some learning path must be deleted too, just do it
                                if ($lpmDel=='true')
                                {
                                    //get module_id concerned (by the asset)...
                                    $sql="SELECT `module_id`
                                          FROM `".$TABLEASSET."`
                                          WHERE `path`='".$exerciseId."'
                                          ";
                                    $aResult = mysql_query($sql);
                                    $aList = mysql_fetch_array($aResult);
                                    $idOfModule = $aList['module_id'];

                                    // delete the asset
                                    $sql="DELETE
                                          FROM `".$TABLEASSET."`
                                          WHERE `path`='".$exerciseId."'

                                          ";
                                    mysql_query($sql);
                                    mysql_query($sql);

                                    // delete the module
                                    $sql="DELETE
                                          FROM `".$TABLEMODULE."`
                                          WHERE `module_id`=".$idOfModule."
                                          ";
                                    mysql_query($sql);

                                    // find the learning path module(s) concerned
                                    $sql="SELECT *
                                          FROM `".$TABLELEARNPATHMODULE."`
                                          WHERE `module_id`=".$idOfModule."
                                          ";

                                    $lpmResult = mysql_query($sql);

                                    // delete any user progression info for this/those learning path module(s)
                                    $sql="DELETE
                                          FROM `".$TABLEUSERMODULEPROGRESS."`
                                          WHERE
                                          ";
                                     while ($lpmList = mysql_fetch_array($lpmResult))
                                     {
                                        $sql.="`learningpath_module_id`= '".$lpmList['learningPath_module_id']."' OR ";
                                     }
                                     $sql.=" 0=1 ";
                                     mysql_query($sql);

                                     // delete the learning path module(s)
                                    $sql="DELETE
                                          FROM `".$TABLELEARNPATHMODULE."`
                                          WHERE `module_id`=".$idOfModule."
                                          ";
                                    mysql_query($sql);

                                } //end if at least in one learning path
								break;
				case 'enable':  // enables an exercise
								$objExerciseTmp->enable();
								$objExerciseTmp->save();

								break;
				case 'disable': // disables an exercise
								$objExerciseTmp->disable();
								$objExerciseTmp->save();

								break;
			}
		}

		// destruction of Exercise
		unset($objExerciseTmp);
	}

	$sql="SELECT id,titre,type,active FROM `$TBL_EXERCICES` ORDER BY id LIMIT $from,".($limitExPage+1);
	$result=mysql_query($sql) or die("Error : SELECT at line ".__LINE__);
}
// only for students
else
{
	$sql="SELECT id,titre,type FROM `$TBL_EXERCICES` WHERE active='1' ORDER BY id LIMIT $from,".($limitExPage+1);
	$result=mysql_query($sql) or die("Error : SELECT at line ".__LINE__);
}

$nbrExercises=mysql_num_rows($result);
?>

<table border="0" align="center" cellpadding="2" cellspacing="2" width="100%">
<tr>

<?php
if($is_allowedToEdit)
{
?>

  <td width="50%">
	<a href="admin.php"><?php echo $langNewEx; ?></a> |
	<a href="question_pool.php"><?php echo $langQuestionPool; ?></a>
  </td>
  <td width="50%" align="right">

<?php
}
else
{
?>

	<td align="right">

<?php
}

if($page)
{
?>

	<small><a href="<?php echo $PHP_SELF; ?>?page=<?php echo ($page-1); ?>">&lt;&lt; <?php echo $langPreviousPage; ?></a></small> |

<?php
}
elseif($nbrExercises > $limitExPage)
{
?>

	<small>&lt;&lt; <?php echo $langPreviousPage; ?> |</small>

<?php
}

if($nbrExercises > $limitExPage)
{
?>

	<small><a href="<?php echo $PHP_SELF; ?>?page=<?php echo ($page+1); ?>"><?php echo $langNextPage; ?> &gt;&gt;</a></small>

<?php
}
elseif($page)
{
?>

	<small><?php echo $langNextPage; ?> &gt;&gt;</small>

<?php
}
?>

  </td>
</tr>
</table>

<table class="claroTable" border="0" align="center" cellpadding="2" cellspacing="2" width="100%">

<?php
// shows the title bar only for the administrator
if($is_allowedToEdit)
{
?>

<tr class="headerX">
  <th>
	<?php echo $langExerciseName; ?>
  </th>
  <th>
	<?php echo $langModify; ?>
  </th>
  <th>
	<?php echo $langDelete; ?>
  </th>
  <th>
	<?php echo $langActivate.' / '.$langDeactivate; ?>
  </th>
<?php
  if($is_allowedToTrack)
  {
?>
  <th>
	<?php echo $langTracking; ?>
  </th>
<?php
  }
?>
</tr>

<?php
}

if(!$nbrExercises)
{
?>

<tr>
  <td <?php if($is_allowedToEdit) echo 'colspan="4"'; ?>><?php echo $langNoEx; ?></td>
</tr>

<?php
}

$i=1;

// see if exercises are used in learning path and must be protected by a confirm alert

$sql = "SELECT *,A.`path` AS thePath
          FROM `".$TABLELEARNPATHMODULE."` AS LPM, `".$TABLEASSET."` AS A, `".$TABLEMODULE."` AS M
          WHERE M.`contentType` = 'EXERCISE'
                AND A.`module_id` = M.`module_id`
                AND LPM.`module_id` = M.`module_id`
          ";

$res=mysql_query($sql);

// build an array of action to add to link of deletion for each exercise included in a learning path.

$actionsForDelete[] = array();
while ($list=mysql_fetch_array($res))
{
    $exId = $list['thePath'];
    $toAdd = $langUsedInSeveralPath." ".$langConfirmDeleteExercise;
    $actionsForDelete[$exId] = "onclick=\"javascript:if(!confirm('".addslashes(htmlentities($toAdd))."')) return false;\"";
}
$defaultConfirm = "onclick=\"javascript:if(!confirm('".addslashes(htmlentities($langConfirmDeleteExercise))."')) return false;\"";



// while list exercises
while($row=mysql_fetch_array($result))
{
?>
<tbody>
<tr>

<?php
	// prof only
	if($is_allowedToEdit)
	{
?>

  <td>
    <?php echo ($i+($page*$limitExPage)).'.'; ?>
    &nbsp;
    <a href="exercice_submit.php?exerciseId=<?php echo $row['id']; ?>" <?php if(!$row['active']) echo 'class="invisible"'; ?>><?php echo $row['titre']; ?></a>
  </td>
  <td align="center"><a href="admin.php?exerciseId=<?php echo $row['id']; ?>"><img src="<?php echo $clarolineRepositoryWeb ?>img/edit.gif" border="0" alt="<?php echo htmlentities($langModify); ?>"></a></td>
  <td align="center"><a href="<?php echo $PHP_SELF; ?>?choice=delete&exerciseId=<?php echo $row['id']; if (isset($actionsForDelete[$row['id']])) { echo "&lpmDel=true";}?>" <?php if (isset($actionsForDelete[$row['id']])) { echo $actionsForDelete[$row[id]];} else {echo $defaultConfirm;} ?>><img src="<?php echo $clarolineRepositoryWeb ?>img/delete.gif" border="0" alt="<?php echo htmlentities($langDelete); ?>"></a></td>
<?php
		// if active
		if($row['active'])
		{
?>

  <td align="center"><a href="<?php echo $PHP_SELF; ?>?choice=disable&page=<?php echo $page; ?>&exerciseId=<?php echo $row['id']; ?>"><img src="<?php echo $clarolineRepositoryWeb ?>img/visible.gif" border="0" alt="<?php echo htmlentities($langDeactivate); ?>"></a></td>

<?php
		}
		// else if not active
		else
		{
?>

  <td align="center"><a href="<?php echo $PHP_SELF; ?>?choice=enable&page=<?php echo $page; ?>&exerciseId=<?php echo $row['id']; ?>"><img src="<?php echo $clarolineRepositoryWeb ?>img/invisible.gif" border="0" alt="<?php echo htmlentities($langActivate); ?>"></a></td>

<?php
		}
    if($is_allowedToTrack)
    {
  ?>
          <td align="center"><a href="../tracking/exercises_details.php?exo_id=<?php echo $row['id']; ?>"><img src="<?php echo $clarolineRepositoryWeb ?>img/statistiques.gif" border="0" alt="<?php echo htmlentities($langTracking); ?>"></a></td>
     
   <?php
    }
    echo " </tr>";
	}
	// student only
	else
	{
?>

  <td width="100%">
	<table border="0" cellpadding="0" cellspacing="0" width="100%">
	<tr>
	  <td width="20" align="right"><?php echo ($i+($page*$limitExPage)).'.'; ?></td>
	  <td width="1">&nbsp;</td>
	  <td><a href="exercice_submit.php?exerciseId=<?php echo $row['id']; ?>"><?php echo $row['titre']; ?></a></td>
	</tr>
	</table>
  </td>
</tr>

<?php
	}

	// skips the last exercise, that is only used to know if we have or not to create a link "Next page"
	if($i == $limitExPage)
	{
		break;
	}

	$i++;
}	// end while()
?>
</tbody>
</table>

<?php
/*****************************************/
/* Exercise Results (uses tracking tool) */
/*****************************************/

// if tracking is enabled && user is not anomymous
if($is_trackingEnabled && $_uid):
?>

<br><br>

<table class="claroTable" cellpadding="2" cellspacing="2" border="0" width="80%">
<tr class="headerX">
  <th width="50%"><?php echo $langExercice; ?></th>
  <th width="30%"><?php echo $langDate; ?></th>
  <th width="20%"><?php echo $langResult; ?></th>
</tr>

<?php
$sql="SELECT `ce`.`titre`, `te`.`exe_result` , `te`.`exe_weighting`, UNIX_TIMESTAMP(`te`.`exe_date`)
      FROM `$TBL_EXERCICES` AS ce , `$TBL_TRACK_EXERCICES` AS te
      WHERE `te`.`exe_user_id` = '$_uid'
      AND `te`.`exe_exo_id` = `ce`.`id`
      ORDER BY `ce`.`titre` ASC, `te`.`exe_date`ASC";

$results=getManyResultsXCol($sql,4);

if(is_array($results))
{
	for($i = 0; $i < sizeof($results); $i++)
	{

?>
<tr>
  <td><?php echo $results[$i][0]; ?></td>
  <td><small><?php echo strftime($dateTimeFormatLong,$results[$i][3]); ?></small></td>
  <td><?php echo $results[$i][1]; ?> / <?php echo $results[$i][2]; ?></td>
</tr>

<?php
	}
}
else
{
?>

<tr>
  <td colspan="3"><?php echo $langNoResult; ?></td>
</tr>

<?php
}
?>

</table>

<?php
endif; // end if tracking is enabled

@include($includePath.'/claro_init_footer.inc.php');
?>
