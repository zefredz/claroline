<?php // $Id$
/*
+----------------------------------------------------------------------+
| CLAROLINE 1.6
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
if( isset($_REQUEST['submitExercise']) )
{
    $exerciseTitle = trim($_REQUEST['exerciseTitle']);
    $exerciseDescription = trim($_REQUEST['exerciseDescription']);
    $randomQuestions = isset($_REQUEST['randomQuestions'])?$_REQUEST['questionDrawn']:0;

    // no title given
    if( empty($exerciseTitle) )
    {
        $msgErr = get_lang('GiveExerciseName');

        // get values that were in form when before it was sent
        $exerciseType        = $_REQUEST['exerciseType'];
        $maxTime        = ( isset($_REQUEST['exerciseMaxTime']) )? true : false;
        $maxTimeSec        = $_REQUEST['exerciseMaxTimeSec'] ;
        $maxTimeMin         = $_REQUEST['exerciseMaxTimeMin'];

        $maxAttempt        = $_REQUEST['exerciseMaxAttempt'];
        $showAnswer        = $_REQUEST['exerciseShowAnswer'];
        $anonymousAttempts = ($_REQUEST['anonymousAttempts'] == "YES") ? true : false ;

        $useEndDate        = ( isset($_REQUEST['useEndDate']) )? true : false;

        $startDate = date("Y-m-d", mktime( 0,0,0,$_REQUEST['startMonth'], $_REQUEST['startDay'], $_REQUEST['startYear'] ) );
        $startTime = date("H:i:00", mktime( $_REQUEST['startHour'],$_REQUEST['startMinute'],0) );

                $endDate = date("Y-m-d", mktime( 0,0,0,$_REQUEST['endMonth'], $_REQUEST['endDay'], $_REQUEST['endYear'] ) );
        $endTime = date("H:i:00", mktime( $_REQUEST['endHour'],$_REQUEST['endMinute'],0) );
    }
    else
    {
        $_SESSION['objExercise']->updateTitle($_REQUEST['exerciseTitle']);
        $_SESSION['objExercise']->updateDescription($_REQUEST['exerciseDescription']);
        $_SESSION['objExercise']->updateType($_REQUEST['exerciseType']);
        // build start date
        $composedStartDate = $_REQUEST['startYear']."-"
                            .$_REQUEST['startMonth']."-"
                            .$_REQUEST['startDay']." "
                            .$_REQUEST['startHour'].":"
                            .$_REQUEST['startMinute'].":00";
        $_SESSION['objExercise']->set_start_date($composedStartDate);

        //  build end date
        if(isset($_REQUEST['useEndDate']) && $_REQUEST['useEndDate'])
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
        $_SESSION['objExercise']->set_end_date($composedEndDate);

        if( isset($_REQUEST['exerciseMaxTime']) && $_REQUEST['exerciseMaxTime'] )
        {
            if( is_numeric($_REQUEST['exerciseMaxTimeMin']) && is_numeric($_REQUEST['exerciseMaxTimeSec']) )
            {
                $maxTime = $_REQUEST['exerciseMaxTimeMin']*60 + $_REQUEST['exerciseMaxTimeSec'];
                $_SESSION['objExercise']->set_max_time($maxTime);
            }
            // don't set maxTime in the object if data are not numeric
        }
        else
        {
            $_SESSION['objExercise']->set_max_time( 0 );
        }

        $_SESSION['objExercise']->set_max_attempt($_REQUEST['exerciseMaxAttempt']);

        if ( isset($_REQUEST['anonymousAttempts']) && $_REQUEST['anonymousAttempts'] == 'YES')
        {
            $_SESSION['objExercise']->set_anonymous_attempts(true);
        }
        else
        {
            $_SESSION['objExercise']->set_anonymous_attempts(false);
        }

        $_SESSION['objExercise']->set_show_answer($_REQUEST['exerciseShowAnswer']);
        $_SESSION['objExercise']->setRandom($randomQuestions);
        $_SESSION['objExercise']->save();

        // reads the exercise ID (only usefull for a new exercise)
        $exerciseId = $_SESSION['objExercise']->selectId();

         unset($_REQUEST['modifyExercise']);
        unset($modifyExercise);
    }
}

// if the form has not been submitted it means that we display the form or we display the details of the exercise
if( ! isset($_REQUEST['submitExercise']) || ( isset($_REQUEST['exerciseTitle']) && !empty($_REQUEST['exerciseTitle']) ) )
{
    // get all properties of the exercise before display of form or of resume
    $exerciseTitle        = $_SESSION['objExercise']->selectTitle();
    $exerciseDescription    = $_SESSION['objExercise']->selectDescription();
    $exerciseType        = $_SESSION['objExercise']->selectType();
    $randomQuestions    = $_SESSION['objExercise']->isRandom();
    $maxTime        = $_SESSION['objExercise']->get_max_time();
    $maxTimeSec        = $maxTime%60 ;
    $maxTimeMin         = ($maxTime-$maxTimeSec)/ 60;

    $maxAttempt        = $_SESSION['objExercise']->get_max_attempt();
    $showAnswer        = $_SESSION['objExercise']->get_show_answer();
    $anonymousAttempts      = $_SESSION['objExercise']->anonymous_attempts();

    // start date splitting
    list($startDate, $startTime) = split(' ', $_SESSION['objExercise']->get_start_date());

    // end date splitting
    if($_SESSION['objExercise']->get_end_date() == "9999-12-31 23:59:59")
    {
        // if we don't have a date get the date of now + 1 year
        $useEndDate = false;
        $endDate = date("Y-m-d", mktime( 0,0,0,date("m"), date("d"), date("Y")+1 ) );
        $endTime = date("H:i:00", mktime( date("H"),date("i"),0) );
    }
    else
    {
        // if we have a date separe date and time for the form functions
        $useEndDate = true;
        list($endDate, $endTime) = split(' ', $_SESSION['objExercise']->get_end_date());
    }
}

// shows the form to modify the exercise
if( isset($modifyExercise) )
{
    require_once $includePath . '/lib/form.lib.php';
?>

<form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>?modifyExercise=<?php echo $modifyExercise; ?>">
<table border="0" cellpadding="5">

<?php
    if(!empty($msgErr))
    {
        echo '<tr>' . "\n"
        .    '<td colspan="2">' . "\n"
        .    claro_html::message_box($msgErr) . "\n"
        .    '</td>' . "\n"
        .    '</tr>' . "\n"
        ;
    }
?>

<tr>
  <td>
  <label for="exerciseTitle"><?php echo get_lang('ExerciseName'); ?>&nbsp;:<br /><small>(<?php echo get_lang('Required'); ?>)</small></label>
  </td>
  <td><input type="text" name="exerciseTitle" id="exerciseTitle" size="50" maxlength="200" value="<?php echo htmlspecialchars($exerciseTitle); ?>"></td>
</tr>
<tr>
  <td valign="top">
  <label for="exerciseDescription"><?php echo get_lang('ExerciseDescription'); ?>&nbsp;:</label>
  </td>
  <td>
  <?php echo claro_disp_html_area('exerciseDescription', htmlspecialchars($exerciseDescription),15) ?>
</tr>
<tr>
  <td valign="top"><?php echo get_lang('ExerciseType'); ?>&nbsp;:</td>
  <td><input type="radio" name="exerciseType" id="exerciseType1" value="1" <?php if($exerciseType <= 1) echo 'checked="checked"'; ?>> <label for="exerciseType1"><?php echo get_lang('SimpleExercise'); ?></label><br>
      <input type="radio" name="exerciseType" id="exerciseType2" value="2" <?php if($exerciseType >= 2) echo 'checked="checked"'; ?>> <label for="exerciseType2"><?php echo get_lang('SequentialExercise'); ?></label>
  </td>
</tr>
<?php
    if($exerciseId && $nbrQuestions)
    {
?>

<tr>
  <td valign="top"><label for="randomQuestions"><?php echo get_lang('RandomQuestions'); ?>&nbsp;:</label></td>
  <td><input type="checkbox" name="randomQuestions" id="randomQuestions" value="1" <?php if($randomQuestions) echo 'checked="checked"'; ?>> <label for="randomQuestions"><?php echo get_lang('Yes'); ?></label>, <label for="questionDrawn"><?php echo get_lang('Take'); ?></label>
    <select name="questionDrawn" id="questionDrawn">

<?php
        for($i=1;$i <= $nbrQuestions;$i++)
        {
?>

    <option value="<?php echo $i; ?>" <?php if( $randomQuestions == $i || ($randomQuestions <= 0 && $i == $nbrQuestions) ) echo 'selected="selected"'; ?>><?php echo $i; ?></option>

<?php
        }
?>

    </select> <label for="questionDrawn"><?php echo strtolower(get_lang('Questions')).' '.get_lang('Among').' '.$nbrQuestions; ?></label>
  </td>
</tr>

<?php
    }

?>
<tr>
  <td colspan="2">
  <hr />
  <b><?php echo get_lang('Advanced'); ?></b> (<small><?php echo get_lang('Optional'); ?></small>)
  </td>
</tr>

<!-- start date form -->
<tr>

<td><?php echo get_lang('Start date'); ?>&nbsp;:</td>

<td>
<?php

   echo claro_disp_date_form("startDay", "startMonth", "startYear", $startDate, 'long')." ".claro_disp_time_form("startHour", "startMinute", $startTime);
?>
  </td>
</tr>

<!-- end date form -->
<tr>

<td><?php echo get_lang('EndDate'); ?>&nbsp;:</td>

<td>
<input type="checkbox" name="useEndDate" id="useEndDate" value="1" <?php if( $useEndDate ) echo 'checked="checked"';?>>
<label for="useEndDate"><?php echo get_lang('Yes'); ?>, </label>
<?php
   echo claro_disp_date_form("endDay", "endMonth", "endYear", $endDate, 'long')." ".claro_disp_time_form("endHour", "endMinute", $endTime);
?>
  </td>
</tr>

<tr>
  <td><label for="exerciseMaxTime"><?php echo get_lang('AllowedTime'); ?>&nbsp;:</label></td>
  <td>
  <input type="checkbox" name="exerciseMaxTime" id="exerciseMaxTime" value="1" <?php if($maxTime != 0) echo 'checked="checked"';?>>
  <label for="exerciseMaxTime"><?php echo get_lang('Yes'); ?>, </label>
  <input type="text" name="exerciseMaxTimeMin" id="exerciseMaxTimeMin" size="3" maxlength="3" value="<?php echo $maxTimeMin; ?>">  <?php echo get_lang('MinuteShort'); ?>
  <input type="text" name="exerciseMaxTimeSec" id="exerciseMaxTimeSec" size="2" maxlength="2" value="<?php echo $maxTimeSec; ?>"> <?php echo get_lang('SecondShort'); ?>
  </td>
</tr>

<tr>
  <td><label for="exerciseMaxAttempt"><?php echo get_lang('AttemptsAllowed'); ?>&nbsp;:</label></td>
  <td>
    <select name="exerciseMaxAttempt" id="exerciseMaxAttempt">
        <option value="0" <?php echo ($maxAttempt == 0)? 'selected="selected"' : ''?>><?php echo get_lang('UnlimitedAttempts'); ?></option>
        <option value="1" <?php echo ($maxAttempt == 1)? 'selected="selected"' : ''?>>1 <?php echo get_lang('AttemptAllowed'); ?></option>
        <option value="2" <?php echo ($maxAttempt == 2)? 'selected="selected"' : ''?>>2 <?php echo get_lang('AttemptsAllowed'); ?></option>
        <option value="3" <?php echo ($maxAttempt == 3)? 'selected="selected"' : ''?>>3 <?php echo get_lang('AttemptsAllowed'); ?></option>
        <option value="4" <?php echo ($maxAttempt == 4)? 'selected="selected"' : ''?>>4 <?php echo get_lang('AttemptsAllowed'); ?></option>
        <option value="5" <?php echo ($maxAttempt == 5)? 'selected="selected"' : ''?>>5 <?php echo get_lang('AttemptsAllowed'); ?></option>
    </select>
  </td>
</tr>

<tr>
  <td valign="top"><?php echo get_lang('AllowAnonymousAttempts'); ?>&nbsp;: </td>
  <td>
    <input type="radio" name="anonymousAttempts" id="anonymousAttemptsYes" value="YES" <?php if( $anonymousAttempts ) echo 'checked="checked"'; ?>>
    <label for="anonymousAttemptsYes"><?php echo get_lang('AnonymousAttemptsAllowed'); ?></label><br />
    <input type="radio" name="anonymousAttempts" id="anonymousAttemptsNo" value="NO" <?php if( !$anonymousAttempts ) echo 'checked="checked"';?>>
    <label for="anonymousAttemptsNo"><?php echo get_lang('AnonymousAttemptsNotAllowed'); ?></label>
  </td>
</tr>

<tr>
  <td valign="top"><?php echo get_lang('ShowAnswers'); ?>&nbsp;: </td>
  <td>
    <input type="radio" name="exerciseShowAnswer" id="alwaysShowAnswer" value="ALWAYS" <?php if($showAnswer == 'ALWAYS') echo 'checked="checked"';?>>
    <label for="alwaysShowAnswer"><?php echo get_lang('Yes'); ?></label><br />
    <input type="radio" name="exerciseShowAnswer" id="showAnswerAfterLastTry" value="LASTTRY" <?php if($showAnswer == 'LASTTRY') echo 'checked="checked"';?>>
    <label for="showAnswerAfterLastTry"><?php echo get_lang('ShowAnswersAfterLastTry'); ?></label><br />
    <input type="radio" name="exerciseShowAnswer" id="neverShowAnswer" value="NEVER" <?php if($showAnswer == 'NEVER') echo 'checked="checked"';?>>
    <label for="neverShowAnswer"><?php echo get_lang('No'); ?></label><br />
  </td>
</tr>

<tr>
  <td>&nbsp;</td>
  <td>
    <input type="submit" name="submitExercise" value="<?php echo get_lang('Ok'); ?>">
    &nbsp;&nbsp;<input type="submit" name="cancelExercise" value="<?php echo get_lang('Cancel'); ?>">
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

<blockquote>
  <?php echo claro_parse_user_text($exerciseDescription); ?>
</blockquote>
<small>
<ul>
  <li><?php echo get_lang('ExerciseType')." : "; echo ($exerciseType >= 2)?get_lang('SequentialExercise'):get_lang('SimpleExercise'); ?></li>
  <li><?php echo get_lang('RandomQuestions')." : "; echo ($randomQuestions)?get_lang('Yes'):get_lang('No'); ?></li>
  <li><?php echo get_lang('Start date'). " : ";  echo claro_disp_localised_date($dateTimeFormatLong,$_SESSION['objExercise']->get_start_date('timestamp')); ?></li>
  <li><?php echo get_lang('EndDate')." : ";
                    if($useEndDate)
                    {
                      echo claro_disp_localised_date($dateTimeFormatLong,$_SESSION['objExercise']->get_end_date('timestamp'));
                    }
                    else
                    {
                      echo get_lang('NoEndDate');
                    }
  ?></li>
  <li>
<?php
  if ( $maxTime == 0 )
  {
    echo get_lang('NoTimeLimit');
  }
  else
  {
    echo get_lang('AllowedTime')." : ".claro_disp_duration($maxTime);
  }
?>
  <li>
<?php
  if($maxAttempt == 0)
  {
    echo get_lang('UnlimitedAttempts');
  }
  elseif($maxAttempt == 1)
  {
    echo $maxAttempt." ".get_lang('AttemptAllowed');
  }
  else
  {
    echo $maxAttempt." ".get_lang('AttemptsAllowed');
  }
?>
  </li>
  <li><?php echo get_lang('AllowAnonymousAttempts')." : "; echo($anonymousAttempts)?get_lang('AnonymousAttemptsAllowed'):get_lang('AnonymousAttemptsNotAllowed'); ?></li>
  <li>
<?php
    echo get_lang('ShowAnswers')." : ";

    switch($showAnswer)
    {
      case 'ALWAYS' : echo get_lang('Always'); break;
      case 'LASTTRY' : echo get_lang('ShowAnswersAfterLastTry'); break;
      case 'NEVER'  : echo get_lang('Never'); break;
    }
?>
  </li>
</ul>
</small>
<a class="claroCmd" href="<?php echo $_SERVER['PHP_SELF']; ?>?modifyExercise=yes">
<img src="<?php echo $imgRepositoryWeb ?>edit.gif" border="0" align="absmiddle" alt="">
<?php echo get_lang('EditExercise'); ?>
</a>
<?php
}
?>
