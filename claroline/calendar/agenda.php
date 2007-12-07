<?php // $Id$

//----------------------------------------------------------------------
// CLAROLINE 1.5.*
//----------------------------------------------------------------------
// Copyright (c) 2001-2004 Universite catholique de Louvain (UCL)
//----------------------------------------------------------------------
// This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
// as published by the FREE SOFTWARE FOUNDATION. The GPL is available
// through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
//----------------------------------------------------------------------
// Authors: see 'credits' file
//----------------------------------------------------------------------


/*

 - For a Student -> View angeda Content
 - For a Prof    -> - View agenda Content
          - Update/delete existing entries
          - Add entries
          - generate an "announce" entries about an entries

 */

$langFile = 'agenda';

$tlabelReq = "CLCAL___";

require '../inc/claro_init_global.inc.php';
if ( ! $_cid) claro_disp_select_course();
if ( ! $is_courseAllowed) claro_disp_auth_form();

include($includePath."/conf/agenda.conf.inc.php");

$htmlHeadXtra[] = 
"<style type=\"text/css\">
<!--
.content {padding-left: 25px;}
-->
</style>
<style media=\"print\" type=\"text/css\">
<!--
th {border-bottom: thin dashed Gray;}
-->
</style>";

include($includePath."/lib/text.lib.php");

$nameTools = $langAgenda;

include($includePath."/claro_init_header.inc.php");


//stats
include('../inc/lib/events.lib.inc.php');
event_access_tool($nameTools);

$tbl_calendar_event = $_course['dbNameGlu'].'calendar_event';
$is_allowedToEdit   = $is_courseAdmin;

$cmd = $_REQUEST['cmd'];
unset($msg);

if     ($cmd == 'rqAdd' ) $subTitle = $langAddEvent;
elseif ($cmd == 'rqEdit') $subTitle = $langEditEvent;
else                 $subTitle = '';

claro_disp_tool_title(array('mainTitle' => $nameTools, 'subTitle' => $subTitle));

if ($is_allowedToEdit)
{
    if ($cmd == 'exAdd')
    {
        $date_selection = $_REQUEST['fyear']."-".$_REQUEST['fmonth'].'-'.$_REQUEST['fday'];
        $hour           = $_REQUEST['fhour'].':'.$_REQUEST['fminute'].':00';

        $sql = "INSERT INTO `".$tbl_calendar_event."` 
                SET   titre   = '".trim(claro_addslashes($_REQUEST['titre']))."',
                      contenu = '".trim(claro_addslashes($_REQUEST['contenu']))."',
                      day     = '".$date_selection."',
                      hour    = '".$hour."',
                      lasting = '".$_REQUEST['lasting']."'";

        if ( claro_sql_query($sql) != false)
        {
            $msg .= '<p>'.$langEventAdded.'</p>';

            if (CONFVAL_LOG_CALENDAR_INSERT)
            {
                event_default('CALENDAR',array ('ADD_ENTRY' => $entryId));
            }
        }
        else
        {
            $msg .= '<p>'.$langUnableToAdd.'</p>';
        }
    }

    if ($cmd == 'exEdit')
    {
        $date_selection = $fyear."-".$fmonth.'-'.$fday;
        $hour           = $fhour.':'.$fminute.':00';

        if ( $_REQUEST['id'] )
        {
            $sql = "UPDATE `".$tbl_calendar_event."`
                    SET   titre   = '".trim(claro_addslashes($_REQUEST['titre']))."',
                          contenu = '".trim(claro_addslashes($_REQUEST['contenu']))."',
                          day     = '".$date_selection."',
                          hour    = '".$hour."',
                          lasting = '".$_REQUEST['lasting']."'
                    WHERE id      ='".$_REQUEST['id']."'";

            if ( claro_sql_query($sql) !== false)
            {
                $msg .= '<p>'.$langEventUpdated.'</p>';
            }
            else
            {
                $msg .= '<p>'.$langUnableToUpdate.'</p>';
            }
        }
    }

    if ($cmd == 'exDelete')
    {
        if ($_REQUEST['id'] == 'ALL')
        {
            $sql = "DELETE 
                    FROM `".$tbl_calendar_event."`";
        }
        elseif ( (int) $_REQUEST['id'] != 0 )
        {
            $sql = "DELETE 
                    FROM `".$tbl_calendar_event."`
                    WHERE id ='".$id."'";
        }

        if ( claro_sql_query($sql) !== false)
        {
            $msg .= '<p>'.$langEventDeleted.'</p>';

            if (CONFVAL_LOG_CALENDAR_DELETE)
            {
                event_default('CALENDAR',array ('DELETE_ENTRY' => $id));
            }
        }
        else
        {
            $msg = '<p>'.$langUnableToDelete.'</p>';
        }
    }

    if ($cmd == 'rqEdit' || $cmd == 'rqAdd')
    {
        if ($cmd == 'rqEdit' && $_REQUEST['id'])
        {
            $sql = "SELECT id, titre, contenu, 
                           day     dayAncient,
                           hour    hourAncient, 
                           lasting lastingAncient
                    FROM `".$tbl_calendar_event."` 
                    WHERE id='".$id."'";

            list($editedEvent) = claro_sql_query_fetch_all($sql);

            $nextCommand = 'exEdit';
    	}
        else
        {
            $editedEvent['id'            ] = '';
            $editedEvent['titre'         ] = '';
            $editedEvent['contenu'       ] = '';
            $editedEvent['dayAncient'    ] = false;
            $editedEvent['hourAncient'   ] = false;
            $editedEvent['lastingAncient'] = false;

            $nextCommand = 'exAdd';

        }

?>
<form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">

<input type="hidden" name="cmd" value="<?php echo $nextCommand       ?>"> 
<input type="hidden" name="id"  value="<?php echo $editedEvent['id'] ?>">

<table>

<tr>
<td>&nbsp;</td>
<td><label for="fday"><?php echo $langDay;    ?></label></td>
<td><label for="fmonth"><?php echo $langMonth;  ?></label></td>
<td><label for="fyear"><?php echo $langYear;   ?></label></td>
<td><label for="fhour"><?php echo $langHour;   ?></label></td>
<td><label for="fminute"><?php echo $langMinute; ?></label></td>
<td><label for="lasting"><?php echo $langLasting ?></label></td>
</tr>

<?php 

      $day     = date('d');
      $month   = date('m');
      $year    = date('Y');
      $hours   = date('H');
      $minutes = date('i');

      if ($editedEvent['hourAncient'])
      {
        list($hours, $minutes) = split(':', $editedEvent['hourAncient']);
      }

      if ($editedEvent['dayAncient'])
      {
        list($year, $month, $day) = split('-',  $editedEvent['dayAncient']);
      }

      $titre   = $editedEvent['titre'];
      $contenu = $editedEvent['contenu'];
?>
<tr>

<td>&nbsp;</td>

<td>
<select name="fday" id="fday">
<option value="<?php echo $day ?>" selected>[<?php echo $day ?>]</option>
<option value="01">1</option>
<option value="02">2</option>
<option value="03">3</option>
<option value="04">4</option>
<option value="05">5</option>
<option value="06">6</option>
<option value="07">7</option>
<option value="08">8</option>
<option value="09">9</option>
<option value="10">10</option>
<option value="11">11</option>
<option value="12">12</option>
<option value="13">13</option>
<option value="14">14</option>
<option value="15">15</option>
<option value="16">16</option>
<option value="17">17</option>
<option value="18">18</option>
<option value="19">19</option>
<option value="20">20</option>
<option value="21">21</option>
<option value="22">22</option>
<option value="23">23</option>
<option value="24">24</option>
<option value="25">25</option>
<option value="26">26</option>
<option value="27">27</option>
<option value="28">28</option>
<option value="29">29</option>
<option value="30">30</option>
<option value="31">31</option>
</select>
</td>

<td>

<select name="fmonth" id="fmonth">
<option value="<?php echo $month ?>" selected>
[<?php echo $langMonthNames['long'][ $month - 1 ] ?>]
</option>
<option value="01"><?php echo $langMonthNames['long'][0] ?></option>
<option value="02"><?php echo $langMonthNames['long'][1] ?></option>
<option value="03"><?php echo $langMonthNames['long'][2] ?></option>
<option value="04"><?php echo $langMonthNames['long'][3] ?></option>
<option value="05"><?php echo $langMonthNames['long'][4] ?></option>
<option value="06"><?php echo $langMonthNames['long'][5] ?></option>
<option value="07"><?php echo $langMonthNames['long'][6] ?></option>
<option value="08"><?php echo $langMonthNames['long'][7] ?></option>
<option value="09"><?php echo $langMonthNames['long'][8] ?></option>
<option value="10"><?php echo $langMonthNames['long'][9] ?></option>
<option value="11"><?php echo $langMonthNames['long'][10] ?></option>
<option value="12"><?php echo $langMonthNames['long'][11] ?></option>
</select>
</td>

<td>
<select name="fyear" id="fyear">
<option value="<?php echo $year -1 ?>"><?php echo $year -1 ?></option>
<option value="<?php echo $year ?>"  selected>[<?php echo $year ?>]</option>
<option value="<?php echo $year +1 ?>"><?php echo $year +1 ?></option>
<option value="<?php echo $year +2 ?>"><?php echo $year +2 ?></option>
<option value="<?php echo $year +3 ?>"><?php echo $year +3 ?></option>
<option value="<?php echo $year +4 ?>"><?php echo $year +4 ?></option>
<option value="<?php echo $year +5 ?>"><?php echo $year +5 ?></option>
</select>
</td>

<td>

<select name="fhour" id="fhour">
<option value="<?php echo $hours ?>">
[<?php echo $hours ?>]
</option>
<option value="--">--</option>
<option value="00">00</option>
<option value="01">01</option>
<option value="02">02</option>
<option value="03">03</option>
<option value="04">04</option>
<option value="05">05</option>
<option value="06">06</option>
<option value="07">07</option>
<option value="08">08</option>
<option value="09">09</option>
<option value="10">10</option>
<option value="11">11</option>
<option value="12">12</option>
<option value="13">13</option>
<option value="14">14</option>
<option value="15">15</option>
<option value="16">16</option>
<option value="17">17</option>
<option value="18">18</option>
<option value="19">19</option>
<option value="20">20</option>
<option value="21">21</option>
<option value="22">22</option>
<option value="23">23</option>
</select>

</td>
<td>

<select name="fminute" id="fminute">
<option value="<?php echo $minutes ?>">[<?php echo $minutes ?>]</option>
<option value="--">--</option>
<option value="00">00</option>
<option value="05">05</option>
<option value="10">10</option>
<option value="15">15</option>
<option value="20">20</option>
<option value="25">25</option>
<option value="30">30</option>
<option value="35">35</option>
<option value="40">40</option>
<option value="45">45</option>
<option value="50">50</option>
<option value="55">55</option>
</select>

</td>

<td>
	<input type="text" name="lasting" id="lasting" size="20" maxlength="20" value="<?php echo $lastingAncient ?>">
</td>

</tr>


<tr>
<td valign="top"><label for="titre"><?php echo $langTitle ?> : </label></td>

<td colspan="6"> 
<input size="80" type="text" name="titre" id="titre" value="<?php  echo isset($titre) ? $titre : '' ?>">   
</td>
</tr>

<tr> 

<td valign="top">
<label for="contenu"><?php echo $langDetail ?> : </label>
</td>

<td colspan="6"> 
<?php claro_disp_html_area('contenu', $contenu, 12, 67, $optAttrib = ' wrap="virtual" '); ?>
<br>
<input class="claroButton" type="Submit" name="submitEvent" value="<?php echo $langOk ?>">
<?php claro_disp_button($_SERVER['PHP_SELF'], 'Cancel'); ?>
</td>

</tr>

</table>

</form>
<?php

    } // end if cmd == 'rqEdit' && cmd == 'rqAdd'

    if (! empty($msg)) claro_disp_message_box($msg);


    if ($cmd != 'rqEdit' && $cmd != 'rqAdd') // display main commands only if we're not in the event form
    {
        echo '<p>';

        /*
         * Add event button
         */

        claro_disp_button($_SERVER['PHP_SELF'].'?cmd=rqAdd', 
                       '<img src="'.$clarolineRepositoryWeb.'img/agenda.gif" width="20" alt="">'
                      .$langAddEvent);

        /*
         * remove all event button
         */

        claro_disp_button($_SERVER['PHP_SELF'].'?cmd=exDelete&id=ALL', 
                          '<img src="'.$clarolineRepositoryWeb.'img/delete.gif" width="20" alt="">'
                          .$langClearList, $langClearList.' ?');

        echo '</p>';
    } // end if diplayMainCommands
    
} // end id is_allowed to edit



echo "<table class=\"claroTable\" width=\"100%\">\n";

if (isset($_REQUEST['order']) && $_REQUEST['order']=='desc')
{
    $orderDirection = 'DESC';
}
else
{
    $orderDirection = 'ASC';
}


$sql = "SELECT id, titre, contenu, day, hour, lasting
        FROM `".$tbl_calendar_event."`
        ORDER BY day ".$orderDirection." , hour ".$orderDirection;

$eventList = claro_sql_query_fetch_all($sql);

$monthBar     = '';

if (count($eventList) < 1)
{
	echo '<br><blockquote>'.$langNoEventInTheAgenda.'</blockquote>';
}
else
{
    echo "<tr>\n"
        ."<td align=\"right\" valign=\"top\">\n"
        ."<small>\n";

    if ($orderDirection == 'DESC')
    {
        echo "<a href=\"".$_SERVER['PHP_SELF']."?order=asc\" >".$langOldToNew."</a>\n";
    }
    else
    {
        echo "<a href=\"".$_SERVER['PHP_SELF']."?order=desc\" >".$langNewToOld."</a>\n";
    }
    
    echo "</small>\n"
        ."</td>\n"
        ."</tr>\n";
}

$nowBarAlreadyShowed = false;

foreach($eventList as $thisEvent)
{

    // TREAT "NOW" BAR CASE

    if( ! $nowBarAlreadyShowed)
    if (( ( strtotime($thisEvent['day'].' '.$thisEvent['hour'] ) > time() ) && $orderDirection == 'ASC'  )
        ||
        ( ( strtotime($thisEvent['day'].' '.$thisEvent['hour'] ) < time() ) && $orderDirection == 'DESC' )
      )
    {
        if ($monthBar != date('m',time()))
        {
            $monthBar = date('m',time());

            echo "<tr>\n"
                ."<th class=\"superHeader\" colspan=\"2\" valign=\"top\">\n"
                .ucfirst(claro_format_locale_date('%B %Y',time()))
                ."</th>\n"
                ."</tr>\n";
        }


        // 'NOW' Bar

        echo "<tr>\n"
            ."<td style=\"border-top: #CC3300 1px solid; border-bottom: #CC3300	1px	solid\">\n"
            ."<img src=\"".$clarolineRepositoryWeb."img/pixel.gif\" width=\"20\" alt=\" \">"
            ."<font color=\"#CC3300\">"
            ."<i>"
            .ucfirst(claro_format_locale_date( $dateFormatLong))." "
            .ucfirst( strftime( $timeNoSecFormat))
            ." -- ".$langNow
            ."</i>"
            ."</font>\n"
            ."</td>\n"
            ."</tr>\n";

         $nowBarAlreadyShowed = true;
    }


  /*
   * Display the month bar when the current month 
   * is different from the current month bar
   */

  if ( $monthBar != date( 'm', strtotime($thisEvent['day']) ) )
  {
    $monthBar = date('m', strtotime($thisEvent['day']));

    echo "<tr>\n"
        ."<th class=\"superHeader\" valign=\"top\">\n"
        .ucfirst(claro_format_locale_date('%B %Y', strtotime( $thisEvent['day']) ))
        ."</th>\n"
        ."</tr>\n";
  }

  /*
   * Display the event date
   */

  echo "<tr class=\"headerX\" valign=\"top\">\n"
      ."<th>\n"
      ."<a href=\"#form\" name=\"event".$thisEvent['id']."\"></a>\n"
      ."<img src=\"".$clarolineRepositoryWeb."img/agenda.gif\" alt=\" \">"
      .ucfirst(claro_format_locale_date( $dateFormatLong, strtotime($thisEvent['day'])))." "
      .ucfirst( strftime( $timeNoSecFormat, strtotime($thisEvent['hour'])))." "
      .( empty($thisEvent['lasting']) ? '' : $langLasting.' : '.$thisEvent['lasting'] );

  /*
   * Display the event content
   */

  echo "</th>\n"
      ."</tr>\n"
      ."<tr>\n"
      ."<td>\n"
      ."<div class=\"content\">\n"
      .( empty($thisEvent['titre']  ) ? '' : "<p><strong>".$thisEvent['titre']."</strong></p>\n" )
      .( empty($thisEvent['contenu']) ? '' :  claro_parse_user_text($thisEvent['contenu']) )
      ."</div>\n";

  if ($is_allowedToEdit)
  {
    echo "<a href=\"".$_SERVER['PHP_SELF']."?cmd=rqEdit&id=".$thisEvent['id']."\">"
        ."<img src=\"".$clarolineRepositoryWeb."img/edit.gif\" border=\"O\" alt=\"".$langModify."\">"
        ."</a> "
         
        ."<a href=\"".$_SERVER['PHP_SELF']."?cmd=exDelete&id=".$thisEvent['id']."\" "
        ."onclick=\"javascript:if( ! confirm('"
        .addslashes (htmlspecialchars($langDelete.' '.$thisEvent['titre']." ?"))
        ."')) return false;\" >"
        ."<img src=\"".$clarolineRepositoryWeb."img/delete.gif\" border=\"0\" alt=\"".$langDelete."\">"
        ."</a>";
  }
  echo "</td>\n"
      ."</tr>\n";
}   // end while

echo "</table>";

include($includePath."/claro_init_footer.inc.php");

?>
