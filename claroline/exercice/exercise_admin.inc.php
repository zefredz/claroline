<?php // $Id$
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.4.0 $Revision$                            |
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2003 Universite catholique de Louvain (UCL)      |
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
    $composedEndDate = $_REQUEST['endYear']."-"
                        .$_REQUEST['endMonth']."-"
                        .$_REQUEST['endDay']." "
                        .$_REQUEST['endHour'].":"
                        .$_REQUEST['endMinute'].":00";
    $objExercise->set_end_date($composedEndDate);
    
		$objExercise->set_max_time($_REQUEST['exerciseMaxTime']);
		$objExercise->set_max_attempt($_REQUEST['exerciseMaxAttempt']);
		if($_REQUEST['exerciseShowAnon'] == "show") 
		{
			$objExercise->set_show_anon();	
		}
		else
		{
			$objExercise->set_hide_anon();
		}
    
    if ( $_REQUEST['recordUidInScore'] )
    {
        $objExercise->set_record_uid_in_score(false);
    }
    else
    {
        $objExercise->set_record_uid_in_score(true);
    }

		$objExercise->set_show_answer($_REQUEST['exerciseShowAnswer']);
		$objExercise->setRandom($randomQuestions);
		$objExercise->save();

		// reads the exercise ID (only usefull for a new exercise)
		$exerciseId=$objExercise->selectId();

		unset($_REQUEST['modifyExercise']);
	}
}
else
{
	$exerciseTitle		= $objExercise->selectTitle();
	$exerciseDescription= $objExercise->selectDescription();
	$exerciseType		= $objExercise->selectType();
	$randomQuestions	= $objExercise->isRandom();
	$maxTime			= $objExercise->get_max_time();
	$maxAttempt			= $objExercise->get_max_attempt();
	$showAnon			= $objExercise->get_show_anon();
	$showAnswer			= $objExercise->get_show_answer();
  $recordUidInScore   = $objExercise->record_uid_in_score();
    
  // start date splitting
  list($startDate, $startTime) = split(' ', $objExercise->get_start_date());
    
  // end date splitting
  list($endDate, $endTime) = split(' ', $objExercise->get_end_date());
}

// shows the form to modify the exercise
if($_REQUEST['modifyExercise'])
{
?>

<form method="post" action="<?php echo $PHP_SELF; ?>?modifyExercise=<?php echo $modifyExercise; ?>">
<table border="0" cellpadding="5">

<?php
	if(!empty($msgErr))
	{
?>

<tr>
  <td colspan="2">
	<table border="0" cellpadding="3" align="center" width="400" bgcolor="#FFCC00">
	<tr>
	  <td><?php echo $msgErr; ?></td>
	</tr>
	</table>
  </td>
</tr>

<?php
	}
?>

<tr>
  <td>
  <label for="exerciseTitle"><?php echo $langExerciseName; ?> :</label>
  </td>
  <td><input type="text" name="exerciseTitle" id="exerciseTitle" size="50" maxlength="200" value="<?php echo htmlentities($exerciseTitle); ?>" style="width:400px;"></td>
</tr>
<tr>
  <td valign="top">
  <label for="exerciseDescription"><?php echo $langExerciseDescription; ?> :</label>
  </td>
  <td>
  <!--<textarea wrap="virtual" name="exerciseDescription" cols="50" rows="4" style="width:400px;"><?php //echo htmlentities($exerciseDescription); ?></textarea></td>-->
  <?php claro_disp_html_area('exerciseDescription', $exerciseDescription) ?>
</tr>
<tr>
  <td valign="top"><?php echo $langExerciseType; ?> :</td>
  <td><input type="radio" name="exerciseType" id="exerciseType1" value="1" <?php if($exerciseType <= 1) echo 'checked="checked"'; ?>> <label for="exerciseType1"><?php echo $langSimpleExercise; ?></label><br>
      <input type="radio" name="exerciseType" id="exerciseType2" value="2" <?php if($exerciseType >= 2) echo 'checked="checked"'; ?>> <label for="exerciseType2"><?php echo $langSequentialExercise; ?></td></label>
</tr>
<!-- start date form -->
<tr>

<td>Exercise opening :</td>

<td>
<?php
   echo claro_disp_date_form("startDay", "startMonth", "startYear", $startDate)." ".claro_disp_time_form("startHour", "startMinute", $startTime);
?>
  </td>
</tr>

<!-- end date form -->
<tr>

<td>Exercise closing :</td>

<td>
<?php
   echo claro_disp_date_form("endDay", "endMonth", "endYear", $endDate)." ".claro_disp_time_form("endHour", "endMinute", $endTime);
?>

  
  </td>
</tr>
<tr>
  <td><label for="exerciseMaxTime"><?php echo $langAllowedTime; ?> :</label></td>
  <td>
	<input type="text" name="exerciseMaxTime" id="exerciseMaxTime" size="4" maxlength="4" value="<?php echo $maxTime; ?>">
  </td>
</tr>

<tr>
  <td><label for="exerciseMaxAttempt"><?php echo $langAllowedAttempts; ?> :</label></td>
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
  <td valign="top"><?php echo $langAnonymousVisibility; ?> : </td>
  <td>	<input type="radio" name="exerciseShowAnon" id="showAnon" value="show" <?php if($showAnon) echo 'checked="checked"'; ?>>
		<label for="showAnon"><?php echo $langShow; ?></label>
		<br />
  		<input type="radio" name="exerciseShowAnon" id="hideAnon" value="hide" <?php if(!$showAnon) echo 'checked="checked"'; ?>>
		<label for="hideAnon"><?php echo $langHide; ?></label>
  </td>
</tr>
<tr>
  <td valign="top"><?php echo $langAllowAnonymousAttempts; ?> : </td>
  <td>
    <input type="checkbox" name="recordUidInScore" id="recordUidInScore" value="1" <?php if( !$recordUidInScore ) echo 'checked="checked"'; ?>>
    <label for="recordUidInScore"><?php echo $langDontRecordUid; ?></label>
  </td>
</tr>

<tr>
  <td valign="top"><?php echo $langShowAnswers; ?> : </td>
  <td>
    <input type="radio" name="exerciseShowAnswer" id="alwaysShowAnswer" value="ALWAYS" <?php if($showAnswer == 'ALWAYS') echo 'checked="checked"';?>>
    <label for="alwaysShowAnswer"><?php echo $langAlways; ?></label><br />
    
    <input type="radio" name="exerciseShowAnswer" id="neverShowAnswer" value="NEVER" <?php if($showAnswer == 'NEVER') echo 'checked="checked"';?>>
    <label for="neverShowAnswer"><?php echo $langNever; ?></label><br />
    
    <input type="radio" name="exerciseShowAnswer" id="endDateShowAnswer" value="ENDDATE" <?php if($showAnswer == 'ENDDATE') echo 'checked="checked"';?>>
    <label for="endDateShowAnswer"><?php echo $langAfterEndDate; ?></label><br />
  </td>
</tr>
<?php
	if($exerciseId && $nbrQuestions)
	{
?>

<tr>
  <td valign="top"><label for="randomQuestions"><?php echo $langRandomQuestions; ?> :</label></td>
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
?>

<h3>
  <?php echo $exerciseTitle; ?>
</h3>

<blockquote>
  <?php echo claro_parse_user_text($exerciseDescription); ?>
</blockquote>

<a href="<?php echo $PHP_SELF; ?>?modifyExercise=yes"><img src="<?php echo $clarolineRepositoryWeb ?>img/edit.gif" border="0" align="absmiddle" alt=""><small><?php echo $langEditExercise; ?></small></a>

<?php
}

function claro_disp_date_form($dayFieldName, $monthFieldName, $yearFieldName, $selectedDate = 0 )
{
    global $langMonthNames;
    
    if(!$selectedDate)
    {
        $selectedDate = date("Y-m-d");
    }
    // split selectedDate
    list($selYear, $selMonth, $selDay) = split("-", $selectedDate);
    
    // day field
    $dayField = "<select name=\"".$dayFieldName."\" id=\"".$dayFieldName."\">\n";
    for ($i=1;$i <=31; $i++)
    {
        $dayField .= "<option value=\"".$i."\"";
        if($i == $selDay)
        {
            $dayField .= " selected=\"true\"";
        }
        $dayField .= ">".$i."</option>\n";
    }
    $dayField .="</select>\n";
    
    // month field
    $monthField = "<select name=\"".$monthFieldName."\" id=\"".$monthFieldName."\">\n";
    for ($i=1;$i <=12; $i++)
    {
        $monthField .= "<option value=\"".$i."\"";
        if($i == $selMonth)
        {
            $monthField .= " selected=\"true\"";
        }
        $monthField .= ">".$langMonthNames['long'][$i-1]."</option>\n";
    }
    $monthField .="</select>\n";
    
    // year field
    $yearField = "<select name=\"".$yearFieldName."\" id=\"".$yearFieldName."\">\n";
    for ($i= $selYear-5;$i <=$selYear+5; $i++)
    {
        $yearField .= "<option value=\"".$i."\"";
        if($i == $selYear)
        {
            $yearField .= " selected=\"true\"";
        }
        $yearField .= ">".$i."</option>\n";
    }
    $yearField .='</select>';
    
    return $dayField.'&nbsp;'.$monthField.'&nbsp;'.$yearField;
}


function claro_disp_time_form($hourFieldName, $minuteFieldName, $selectedTime = 0)
{
    if(!$selectedTime)
    {
        $selectedTime = date("H:i");
    }
    
    //split selectedTime 
    list($selHour, $selMinute) = split(":",$selectedTime);
    
    $hourField = "<select name=\"".$hourFieldName."\" id=\"".$hourFieldName."\">\n";
    for($i=0;$i < 24; $i++)
    {
        $hourField .= "<option value=\"".$i."\"";
        if($i == $selHour)
        {
            $hourField .= " selected=\"true\"";
        }
        $hourField .= ">".$i."</option>\n";
    }
    $hourField .= "</select>";
    
    $minuteField = "<select name=\"".$minuteFieldName."\" id=\"".$minuteFieldName."\">\n";
    $i = 0;
    while($i < 60)
    {
        $minuteField .= "<option value=\"".$i."\"";
        if($i == $selMinute)
        {
            $minuteField .= " selected=\"true\"";
        }
        $minuteField .= ">".$i."</option>\n";
        $i += 5;
    }
    $minuteField .= "</select>";
    
    return $hourField."&nbsp;".$minuteField;
}
?>
