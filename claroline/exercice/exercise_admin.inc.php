<?php // $Id$
/*
+----------------------------------------------------------------------+
| CLAROLINE 1.6
+----------------------------------------------------------------------+
| Copyright (c) 2001, 2004 Universite catholique de Louvain (UCL)      |
+----------------------------------------------------------------------+
      |   This program is free software; you can redistribute it and/or      |
      |   modify it under the terms of the GNU General Public License        |
      |   as published by the Free Software Foundation; either version 2     |
      |   of the License, or (at your option) any later version.             |
      +----------------------------------------------------------------------+
      | Authors: Olivier Brouckaert <oli.brouckaert@skynet.be>               |
      +----------------------------------------------------------------------+
*/

		/*>>>>>>>>>>>>>>>>>>>> EXERCISE ADMINISTRATION <<<<<<<<<<<<<<<<<<<<*/

/**
 * This script allows to manage an exercise
 *
 * It is included from the script admin.php
 */

// ALLOWED_TO_INCLUDE is defined in admin.php
if(!defined('ALLOWED_TO_INCLUDE'))
{
	exit();
}

// the exercise form has been submitted
if($_REQUEST['submitExercise'])
{
	$exerciseTitle=trim($_REQUEST['exerciseTitle']);
	$exerciseDescription=trim($_REQUEST['exerciseDescription']);
	$randomQuestions=$_REQUEST['randomQuestions']?$_REQUEST['questionDrawn']:0;

	// no title given
	if(empty($_REQUEST['exerciseTitle']))
	{
		$msgErr=$langGiveExerciseName;
	}
	else
	{
		$objExercise->updateTitle($_REQUEST['exerciseTitle']);
		$objExercise->updateDescription($_REQUEST['exerciseDescription']);
		$objExercise->updateType($_REQUEST['exerciseType']);
    // build start date
    $composedStartDate = $_REQUEST['startYear']."-"
                        .$_REQUEST['startMonth']."-"
                        .$_REQUEST['startDay']." "
                        .$_REQUEST['startHour'].":"
                        .$_REQUEST['startMinute'].":00";
    $objExercise->set_start_date($composedStartDate);
    
    //  build end date
    if($_REQUEST['useEndDate'])
    {
        $composedEndDate = $_REQUEST['endYear']."-"
                            .$_REQUEST['endMonth']."-"
                            .$_REQUEST['endDay']." "
                            .$_REQUEST['endHour'].":"
                            .$_REQUEST['endMinute'].":00";
    
    }
    else
    {
        $composedEndDate = "9999-12-31 23:59:59";
    }
    $objExercise->set_end_date($composedEndDate);
    
    if( $_REQUEST['exerciseMaxTime'] )
    {
      if( is_numeric($_REQUEST['exerciseMaxTimeMin']) && is_numeric($_REQUEST['exerciseMaxTimeSec']) )
      {
        $maxTime = $_REQUEST['exerciseMaxTimeMin']*60 + $_REQUEST['exerciseMaxTimeSec'];
        $objExercise->set_max_time($maxTime);
      }
      // don't set maxTime in the object if data are not numeric
    }
    else
    {
      $objExercise->set_max_time( 0 );
    }
		$objExercise->set_max_attempt($_REQUEST['exerciseMaxAttempt']);
    
    if ( $_REQUEST['anonymousAttempts'] == 'YES')
    {
        $objExercise->set_anonymous_attempts(true);
    }
    else
    {
        $objExercise->set_anonymous_attempts(false);
    }

		$objExercise->set_show_answer($_REQUEST['exerciseShowAnswer']);
		$objExercise->setRandom($randomQuestions);
		$objExercise->save();

		// reads the exercise ID (only usefull for a new exercise)
		$exerciseId=$objExercise->selectId();

		unset($_REQUEST['modifyExercise']);
    unset($modifyExercise);
	}
}
// get all properties of the exercise before display of form or of resume
	$exerciseTitle		= $objExercise->selectTitle();
	$exerciseDescription= $objExercise->selectDescription();
	$exerciseType		= $objExercise->selectType();
	$randomQuestions	= $objExercise->isRandom();
	$maxTime			= $objExercise->get_max_time();
  $maxTimeSec = $maxTime%60 ;
  $maxTimeMin = ($maxTime-$maxTimeSec)/ 60;
  
	$maxAttempt			= $objExercise->get_max_attempt();
	$showAnswer			= $objExercise->get_show_answer();
  $anonymousAttempts   = $objExercise->anonymous_attempts();
    
  // start date splitting
  list($startDate, $startTime) = split(' ', $objExercise->get_start_date());
    
  // end date splitting
  if($objExercise->get_end_date() == "9999-12-31 23:59:59")
  {
      $useEndDate = false;
      $endDate = date("Y-m-d", mktime( 0,0,0,date("m"), date("d"), date("Y")+1 ) );
      $endTime = date("H:i:00", mktime( date("H"),date("i"),0) );
  }
  else
  {
      $useEndDate = true;
      list($endDate, $endTime) = split(' ', $objExercise->get_end_date());
  }


// shows the form to modify the exercise
if($_REQUEST['modifyExercise'] || $modifyExercise )
{
    include($includePath."/lib/form.lib.php");
?>

<form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>?modifyExercise=<?php echo $modifyExercise; ?>">
<table border="0" cellpadding="5">

<?php
	if(!empty($msgErr))
	{
?>

<tr>
  <td colspan="2">
<?php
	claro_disp_message_box($msgErr);
?>
  </td>
</tr>

<?php
	}
?>

<tr>
  <td>
  <label for="exerciseTitle"><?php echo $langExerciseName; ?>&nbsp;:<br /><small>(<?php echo $langRequired; ?>)</small></label>
  </td>
  <td><input type="text" name="exerciseTitle" id="exerciseTitle" size="50" maxlength="200" value="<?php echo htmlentities($exerciseTitle); ?>"></td>
</tr>
<tr>
  <td valign="top">
  <label for="exerciseDescription"><?php echo $langExerciseDescription; ?>&nbsp;:</label>
  </td>
  <td>
  <!--<textarea wrap="virtual" name="exerciseDescription" cols="50" rows="4"><?php //echo htmlentities($exerciseDescription); ?></textarea></td>-->
  <?php claro_disp_html_area('exerciseDescription', $exerciseDescription) ?>
</tr>
<tr>
  <td valign="top"><?php echo $langExerciseType; ?>&nbsp;:</td>
  <td><input type="radio" name="exerciseType" id="exerciseType1" value="1" <?php if($exerciseType <= 1) echo 'checked="checked"'; ?>> <label for="exerciseType1"><?php echo $langSimpleExercise; ?></label><br>
      <input type="radio" name="exerciseType" id="exerciseType2" value="2" <?php if($exerciseType >= 2) echo 'checked="checked"'; ?>> <label for="exerciseType2"><?php echo $langSequentialExercise; ?></td></label>
</tr>
<!-- start date form -->
<tr>

<td><?php echo $langExerciseOpening; ?>&nbsp;:</td>

<td>
<?php
   echo claro_disp_date_form("startDay", "startMonth", "startYear", $startDate)." ".claro_disp_time_form("startHour", "startMinute", $startTime);
?>
  </td>
</tr>

<!-- end date form -->
<tr>

<td><?php echo $langExerciseClosing; ?>&nbsp;:</td>

<td>

<input type="checkbox" name="useEndDate" id="useEndDate" value="1" <?php if( $useEndDate ) echo 'checked="checked"';?>>
<label for="useEndDate"><?php echo $langYes; ?>, </label>
<?php
   echo claro_disp_date_form("endDay", "endMonth", "endYear", $endDate)." ".claro_disp_time_form("endHour", "endMinute", $endTime);
?>

  
  </td>
</tr>
<tr>
  <td><label for="exerciseMaxTime"><?php echo $langAllowedTime; ?>&nbsp;:</label></td>
  <td>
  <input type="checkbox" name="exerciseMaxTime" id="exerciseMaxTime" value="1" <?php if($maxTime != 0) echo 'checked="checked"';?>>
  <label for="exerciseMaxTime"><?php echo $langYes; ?>, </label>
  <input type="text" name="exerciseMaxTimeMin" id="exerciseMaxTimeMin" size="3" maxlength="3" value="<?php echo $maxTimeMin; ?>">  <?php echo $langMinuteShort; ?>
	<input type="text" name="exerciseMaxTimeSec" id="exerciseMaxTimeSec" size="2" maxlength="2" value="<?php echo $maxTimeSec; ?>"> <?php echo $langSecondShort; ?>
  </td>
</tr>

<tr>
  <td><label for="exerciseMaxAttempt"><?php echo $langAllowedAttempts; ?>&nbsp;:</label></td>
  <td>
	<select name="exerciseMaxAttempt" id="exerciseMaxAttempt">
        <option value="0" <?php echo ($maxAttempt == 0)? 'selected="selected"' : ''?>><?php echo $langUnlimitedAttempts; ?></option>
        <option value="1" <?php echo ($maxAttempt == 1)? 'selected="selected"' : ''?>>1 <?php echo $langAttemptAllowed; ?></option>
        <option value="2" <?php echo ($maxAttempt == 2)? 'selected="selected"' : ''?>>2 <?php echo $langAttemptsAllowed; ?></option>
        <option value="3" <?php echo ($maxAttempt == 3)? 'selected="selected"' : ''?>>3 <?php echo $langAttemptsAllowed; ?></option>
        <option value="4" <?php echo ($maxAttempt == 4)? 'selected="selected"' : ''?>>4 <?php echo $langAttemptsAllowed; ?></option>       
        <option value="5" <?php echo ($maxAttempt == 5)? 'selected="selected"' : ''?>>5 <?php echo $langAttemptsAllowed; ?></option>       
    </select>
  </td>
</tr>

<tr>
  <td valign="top"><?php echo $langAllowAnonymousAttempts; ?>&nbsp;: </td>
  <td>
    <input type="radio" name="anonymousAttempts" id="anonymousAttemptsYes" value="YES" <?php if( $anonymousAttempts ) echo 'checked="checked"'; ?>>
    <label for="anonymousAttemptsYes"><?php echo $langAnonymousAttemptsAllowed; ?></label><br />
    <input type="radio" name="anonymousAttempts" id="anonymousAttemptsNo" value="NO" <?php if( !$anonymousAttempts ) echo 'checked="checked"';?>>
    <label for="anonymousAttemptsNo"><?php echo $langAnonymousAttemptsNotAllowed; ?></label>
  </td>
</tr>

<tr>
  <td valign="top"><?php echo $langShowAnswers; ?>&nbsp;: </td>
  <td>
    <input type="radio" name="exerciseShowAnswer" id="alwaysShowAnswer" value="ALWAYS" <?php if($showAnswer == 'ALWAYS') echo 'checked="checked"';?>>
    <label for="alwaysShowAnswer"><?php echo $langYes; ?></label><br />
    <input type="radio" name="exerciseShowAnswer" id="showAnswerAfterLastTry" value="LASTTRY" <?php if($showAnswer == 'LASTTRY') echo 'checked="checked"';?>>
    <label for="showAnswerAfterLastTry"><?php echo $langShowAnswersAfterLastTry; ?></label><br />
    <input type="radio" name="exerciseShowAnswer" id="neverShowAnswer" value="NEVER" <?php if($showAnswer == 'NEVER') echo 'checked="checked"';?>>
    <label for="neverShowAnswer"><?php echo $langNo; ?></label><br />
  </td>
</tr>
<?php
	if($exerciseId && $nbrQuestions)
	{
?>

<tr>
  <td valign="top"><label for="randomQuestions"><?php echo $langRandomQuestions; ?>&nbsp;:</label></td>
  <td><input type="checkbox" name="randomQuestions" id="randomQuestions" value="1" <?php if($randomQuestions) echo 'checked="checked"'; ?>> <label for="randomQuestions"><?php echo $langYes; ?></label>, <label for="questionDrawn"><?php echo $langTake; ?></label>
    <select name="questionDrawn" id="questionDrawn">

<?php
		for($i=1;$i <= $nbrQuestions;$i++)
		{
?>

	<option value="<?php echo $i; ?>" <?php if(($formSent && $questionDrawn == $i) || (!$formSent && ($randomQuestions == $i || ($randomQuestions <= 0 && $i == $nbrQuestions)))) echo 'selected="selected"'; ?>><?php echo $i; ?></option>

<?php
		}
?>

	</select> <label for="questionDrawn"><?php echo strtolower($langQuestions).' '.$langAmong.' '.$nbrQuestions; ?></label>
  </td>
</tr>

<?php
	}
?>

<tr>
  <td colspan="2" align="center">
	<input type="submit" name="submitExercise" value="<?php echo $langOk; ?>">
	&nbsp;&nbsp;<input type="submit" name="cancelExercise" value="<?php echo $langCancel; ?>">
  </td>
</tr>
</table>
</form>

<?php
}
else
{
  // display exercise settings
?>

<h3>
  <?php echo $exerciseTitle; ?>
</h3>
<blockquote>
  <?php echo claro_parse_user_text($exerciseDescription); ?>
</blockquote>
<small>
<ul>
  <li><?php echo $langExerciseType." : "; echo ($exerciseType >= 2)?$langSequentialExercise:$langSimpleExercise; ?></li>
  <li><?php echo $langExerciseOpening. " : ";  echo claro_disp_localised_date($dateTimeFormatLong,$objExercise->get_start_date('timestamp')); ?></li>
  <li><?php echo $langExerciseClosing." : "; 
                    if($useEndDate) 
                    {
                      echo claro_disp_localised_date($dateTimeFormatLong,$objExercise->get_end_date('timestamp')); 
                    }
                    else
                    {                    
                      echo $langNoEndDate;
                    }
  ?></li>
  <li>
<?php 
  if ( $maxTime == 0 )
  {
    echo $langNoTimeLimit;
  }
  else
  {
    echo $langAllowedTime." : ".disp_minutes_seconds($maxTime);
  }
?>
  <li>
<?php 
  if($maxAttempt == 0)
  {
    echo $langUnlimitedAttempts;
  }
  elseif($maxAttempt == 1)
  {
    echo $maxAttempt." ".$langAttemptAllowed;
  }
  else
  {
    echo $maxAttempt." ".$langAttemptsAllowed;
  }
?>
  </li>
  <li><?php echo $langAllowAnonymousAttempts." : "; echo($anonymousAttempts)?$langAnonymousAttemptsAllowed:$langAnonymousAttemptsNotAllowed; ?></li>
  <li>
<?php 
    echo $langShowAnswers." : "; 
    switch($showAnswer)
    {
      case 'ALWAYS' : echo $langAlways; 
                                break;
      case 'NEVER'  : echo $langNever;
                              break;
    }
?>  
  </li>
   <li><?php echo $langRandomQuestions." : "; echo ($randomQuestions)?$langYes:$langNo; ?></li>
</ul>
</small>
<a href="<?php echo $_SERVER['PHP_SELF']; ?>?modifyExercise=yes"><img src="<?php echo $clarolineRepositoryWeb ?>img/edit.gif" border="0" align="absmiddle" alt=""><small><?php echo $langEditExercise; ?></small></a>

<?php
}
?>
