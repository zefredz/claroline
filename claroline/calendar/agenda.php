<?php # $Id$
//----------------------------------------------------------------------
// CLAROLINE
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
 - For a Prof 	 -> - View agenda Content
 					- Update/delete existing entries
					- Add entries
					- generate an "announce" entries about an entries

 */

$langFile = "agenda";
//$interbredcrump[]= array ("url"=>"index.php", "name"=> $langAdmin);
#$tlabelReq = "CLCAL___";
include('../inc/claro_init_global.inc.php');
include($includePath."/conf/agenda.conf.inc.php");

$htmlHeadXtra[] = "<style type=\"text/css\">
<!--
.month {font-weight : bold;color : #FFFFFF;background-color : #4171B5;padding-left : 15px;padding-right : 15px;}
.content {position: relative; left: 25px;}
-->
</style>
<STYLE media=\"print\" type=\"text/css\">
<!--
TD {border-bottom: thin dashed Gray;}
-->
</STYLE>";

include($includePath."/lib/text.lib.php");
@include($includePath."/lib/debug.lib.inc.php");

$nameTools 			= $langAgenda;

include($includePath."/claro_init_header.inc.php");

if ( ! ($is_courseAllowed ||  $is_toolAllowed))
	claro_disp_auth_form();

//stats
include('../inc/lib/events.lib.inc.php');
event_access_tool($nameTools);

$dateNow 			= claro_format_locale_date($dateTimeFormatLong);
$TABLEAGENDA 		= $_course['dbNameGlu']."calendar_event";
$is_allowedToEdit 	= $is_courseAdmin;


claro_disp_tool_title($nameTools);
claro_disp_msg_arr($controlMsg);

echo "<small>".$dateNow."</small><br>\n";


################# FORM TO ADD AN ENTRY ######################
# 1° WORK DATA IF SUBMIT
# 2° SHOW FORM
##########################
if ($is_allowedToEdit)
{
	if (isset($submitEvent)&&$submitEvent)
	{	
		// $contenu=nl2br("$contenu");
		$date_selection = $fyear."-".$fmonth."-".$fday;
		$hour = $fhour.":".$fminute.":00";
		// Si pas d'ID, AJOUTER, sinon MODIFIER
		if(isset($tout)&&$tout)
		{
			$sql="DELETE FROM `".$TABLEAGENDA."`";
		}
		elseif(isset($id)&&$id) 	// IF TOUT
		{
			$sql = "UPDATE `".$TABLEAGENDA."`
						SET titre='".trim($titre)."',
							contenu='".trim($contenu)."',
							day='".$date_selection."',
							hour='".$hour."',
							lasting='".$lasting."'
						WHERE id='".$id."'";
			unset($id);
			unset($contenu);
			unset($titre);
		}
			else// ELSEIF ID
		{
			$sql = "INSERT INTO `".$TABLEAGENDA."` 
			        (id, titre,contenu, day, hour, lasting)
			        VALUES
			        (NULL, '".trim($titre)."','".trim($contenu)."', '".$date_selection."','".$hour."', '".$lasting."')";

			unset($id);
			unset($contenu);
			unset($titre);
		}	// ELSE  INSERT
		$result = mysql_query($sql);
		if (mysql_error()==0)
		{
			$entryId = mysql_insert_id();
			if (CONFVAL_LOG_CALENDAR_INSERT)
			{
				event_default("CALENDAR",array ("ADD_ENTRY"=>$entryId));
			}
		}
		else
		{
			//error on insert
		}
	}
	elseif (isset($delete)&&$delete) 	// IF SUBMIT
	{
		// DELETE
		$sql = "DELETE FROM `".$TABLEAGENDA."` WHERE id=$id";
		$result = mysql_query($sql);
		if (mysql_error()==0)
		{
			if (CONFVAL_LOG_CALENDAR_DELETE)
			{
				event_default("CALENDAR",array ("DELETE_ENTRY"=>$id));
			}
		}
		else
		{
			//error on delete
		}
	}       // ELSEIF DELETE
 /**************************************************************************************/
	if (isset($id)&&$id)
	{
		// MODIFIER, DONC CHOISIR UN ENREGISTREMENT
		$sql 			= "SELECT id, titre, contenu, day, hour, lasting FROM `".$TABLEAGENDA."` WHERE id=$id";
		$result			= mysql_query($sql);
		$entryToEdit 	= mysql_fetch_array($result);
		$id 			= $entryToEdit["id"];
		$titre 			= $entryToEdit["titre"];
		$contenu		= $entryToEdit["contenu"];
		$hourAncient	= $entryToEdit["hour"];
		$dayAncient		= $entryToEdit["day"];
		$lastingAncient	= $entryToEdit["lasting"];
//		$daySynthetic	= $entryToEdit["day"];
//		$hour			= $entryToEdit["hour"];
		unset($entryToEdit);
	}
?>
<form method="post" action="<?php echo $PHP_SELF ?>">
<input type="hidden" name="id" value="<?php if (isset($id)) echo $id ?>">
<table>
	<tr>
		<td colspan="7">
				<h4><?php echo $langAddEvent?></h4>
		</td>
	</tr>
	<tr>
		<td>
			&nbsp;
		</td>
		<td>
			<?php echo $langDay; ?>
		</td>
		<td>
			<?php echo $langMonth; ?>
		</td>
		<td>
			<?php echo $langYear; ?>
		</td>
		<td>
			<?php echo $langHour; ?>
		</td>
		<td>
			<?php echo $langMinute; ?>
		</td>
		<td>
			<?php echo $langLasting ?>
		</td>
	</tr>
<?php 
	$day	= date("d");
	$month	= date("m");
	$year	= date("Y");
	$hours	= date("H");
	$minutes= date("i");

	if (isset($hourAncient) && $hourAncient)
	{
		$hourAncient = split(":", $hourAncient);
		$hours=$hourAncient[0];
		$minutes=$hourAncient[1];
	}
	if (isset($dayAncient) && $dayAncient)
	{
		$dayAncient	= split("-",  $dayAncient);
		$year		= $dayAncient[0];
		$month		= $dayAncient[1];
		$day		= $dayAncient[2];
	}
?>
	<tr>
		<td>
			&nbsp;
		</td>
		<td>
			<select name="fday">
				<option value="<?php echo $day ?>">[<?php echo $day ?>]</option>
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
			<select name="fmonth">
				<option value="<?php echo $month ?>">[<?php echo $langMonthNames['long'][($month-1)] ?>]</option>
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
			<select name="fyear">
				<option value="<?php echo ($year-1) ?>">[<?php echo ($year-1) ?>]</option>
				<option value="<?php echo $year ?>"  selected>[<?php echo $year ?>]</option>
				<option value="<?php echo $year+1 ?>">[<?php echo $year+1 ?>]</option>
				<option value="<?php echo $year+2 ?>">[<?php echo $year+2 ?>]</option>
				<option value="<?php echo $year+3 ?>">[<?php echo $year+3 ?>]</option>
				<option value="<?php echo $year+4 ?>">[<?php echo $year+4 ?>]</option>
				<option value="<?php echo $year+5 ?>">[<?php echo $year+5 ?>]</option>
			</select>
		</td>
		<td>
			<select name="fhour">
				<option value="<?php echo $hours ?>">[<?php echo $hours ?>]</option>
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
			<select name="fminute">
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
			<input type="text" name="lasting" size="2" value="<?php echo $lastingAncient ?>">
		</td>
	</tr>
	<tr>
		<td valign="top">


		<!-- Testing -->

			<?php echo $langTitle ?> :
		</td>
		<td colspan="6"> 
			<input size="80" type="text" name="titre" value="<?php  echo isset($titre) ? $titre : '' ?>">		
		</td>
	</tr>
	<tr> 
		<td valign="top">
			<?php echo $langDetail ?>&nbsp;:
		</td>
		<td colspan="6"> 
			<?php claro_disp_html_area('contenu', $contenu,	
									   12, 67, $optAttrib=' wrap="virtual" '); ?>
			<br>
			<input type="Submit" name="submitEvent" value="<?php echo $langOk ?>">
		</td>
	</tr>
</table>
</form>

<?php 
	/*---------------------------------------------
	 *  End  of  adding Form
	 *---------------------------------------------*/

}

/*---------------------------------------------
 *  End  of  prof only                         
 *-------------------------------------------*/

################# LIST of ENTRIES ######################
# 1° Request Value
# 2° List value
#    - 'Month add' bar and 'now bar'
########################################################

?>
<table class="claroTable" width="100%">
	<tr>
		<td colspan="2" valign="top">
			<div align="right">
					<small>
<?php
$sens =" ASC";
if (isset($HTTP_GET_VARS["sens"]) && $HTTP_GET_VARS["sens"]=="d") 
{
	echo "
						<a href=\"".$PHP_SELF."?sens=\" >".$langOldToNew."</a>";
	$sens=" DESC ";
}
else
{
	echo "
						<a href=\"".$PHP_SELF."?sens=d\" >".$langNewToOld."</a>";
}
echo "
					</small>
			</div>
		</td>
	</tr>";
 /******** end of Order *********/

$numLine=0;
$result = claro_sql_query("SELECT id, titre, contenu, day, hour, lasting
                       FROM `".$TABLEAGENDA."`
                       ORDER BY day ".$sens.", hour ".$sens,
                       $db);

$barreMois ="";
$nowBarShowed = FALSE;

while ($myrow = mysql_fetch_array($result))
{
	$contenu = $myrow["contenu"];
	$contenu = nl2br($contenu);
	$contenu = make_clickable($contenu);
	if (!$nowBarShowed)
	{
// Following order
		if (( (strtotime($myrow["day"]." ".$myrow["hour"]) > time()) && ($sens==" ASC") )
			  ||
			( (strtotime($myrow["day"]." ".$myrow["hour"]) < time()) && ($sens==" DESC ") )
			)
//		echo "ok";
		{
			if ($barreMois!=date("m",time()))
			{
				$barreMois=date("m",time());
				echo "
	<tr>
		<th class=\"superHeader\" colspan=\"2\" valign=\"top\">
			".ucfirst(claro_format_locale_date("%B %Y",time()))."
		</th>
	</tr>";
			}
			$nowBarShowed = TRUE;
			echo "
<!-- Now -->
	<tr> 
		<td>
			<font color=\"#CC3300\">
				<b>
					 ".$dateNow." &nbsp;
				</b>
			</font>
		</td>
		<td align=\"right\" nowrap bgcolor=\"#CC3300\">
			<font color=\"#FFFFFF\">
				<b>
					&lt;&lt;&lt; ".$langNow." &nbsp;
				</b>
			</font>
		</td>
	</tr>";
		}
	}
	if ($barreMois != date("m",strtotime($myrow["day"])))
	{
		$barreMois = date("m",strtotime($myrow["day"]));
		echo "
	<tr>
		<th class=\"superHeader\" colspan=\"2\" valign=\"top\">
			".ucfirst(claro_format_locale_date("%B %Y",strtotime($myrow["day"])))."
		</th>
	</tr>";
	}


	echo "
<!-- Date -->
	";
	echo "
	<tr class=\"headerX\" valign=\"top\">
		<th>
				<a href=\"#form\" name=\"event".$myrow["id"]."\"></a>
				".$langDay."&nbsp;:
				".ucfirst(claro_format_locale_date($dateFormatLong,strtotime($myrow["day"])))."
				&nbsp;
				".$langHour."&nbsp;:
				".ucfirst(strftime($timeNoSecFormat,strtotime($myrow["hour"])))."
				&nbsp;";

	if ($myrow["lasting"] !="")
	{
		echo "
				".$langLasting."&nbsp;:
				".$myrow["lasting"]."";
	}

	echo "
		</th>
	</tr>";

	echo "
	<tr>
		<td>
			<div class=\"content\">
				<b>
					".$myrow["titre"]."
				</b>
				<br>
				".$contenu."
			</div>";
	if ($is_allowedToEdit)
	{
?>
			<a href="<?php echo $PHP_SELF; ?>?id=<?php echo $myrow["id"]; ?>"><img src="../img/edit.gif" border="O" alt="<?php echo $langModify; ?>"></a>
			<a href="<?php echo $PHP_SELF; ?>?id=<?php echo $myrow["id"];  ?>&delete=yes" onclick="javascript:if(!confirm('<?php echo addslashes(htmlspecialchars($langConfirmYourChoice." (".$langDelete." ".$myrow['titre'].") ")); ?>')) return false;" ><img src="../img/delete.gif" border="0" alt="<?php echo $langDelete; ?>"></a>
<?php
	}
	echo "
		</td>
	</tr>";
	$numLine++;
} 	// WHILE
?>
</table>
<?php
include($includePath."/claro_init_footer.inc.php");
?>