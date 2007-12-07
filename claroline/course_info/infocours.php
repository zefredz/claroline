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

/*==========================
             INIT
  ==========================*/

$langFile = "course_info";

require '../inc/claro_init_global.inc.php';
if ( ! $_cid) claro_disp_select_course();

include($includePath."/lib/course.lib.inc.php");
include($includePath."/conf/course_info.conf.php");


include($includePath."/lib/text.lib.php");
@include($includePath."/lib/debug.lib.inc.php");
$TABLECOURSE     = $mainDbName."`.`cours";
$TABLEFACULTY    = $mainDbName."`.`faculte";
$TABLECOURSDOMAIN= $mainDbName."`.`faculte";//needed for compatibility with libs
$TABLEPHPBBCONFIG = $_course['dbNameGlu']."bb_config";
$TABLECOURSEHOME = $_course['dbNameGlu']."tool_list";

$currentCourseID = $_course['sysCode'];
$currentCourseRepository = $_course["path"];

$is_allowedToEdit = $is_courseAdmin || $is_platformAdmin;

// in case of admin access (from admin tool) to the script, we must determine which course we are working with

//Possibles $_REQUEST FOR THIS SCRIPT
// cidToEdit
// 
// $int
// $faculte
// $visible
// $titulary
// $screenCode
// $lanCourseForm
// $extLinkName
// $extLinkUrl
// $email


if (isset($cidToEdit) && ($is_platformAdmin))
{
    $interbredcrump[]= array ("url"=>$rootAdminWeb, "name"=> $langAdministrationTools); // bred crump different in admin access
    unset($_cid);
    $current_cid = $cidToEdit;
    $toAddtoURL = "&cidToEdit=".$cidToEdit;
}
else
{
    $current_cid = $_course['sysCode'];
}

####################### SUBMIT #################################
if ( ! $is_courseAllowed) claro_disp_auth_form();
$nameTools = $langModifInfo;

include($includePath."/claro_init_header.inc.php");
claro_disp_tool_title($nameTools);

if($is_allowedToEdit)
{
	// check if form submitted
	if (isset($_REQUEST["changeProperties"]))
	{

		if ($int!="" || $canBeEmpty["int"])
			$fieldsToUpdate[]= "intitule='".$int."'";
		if ($faculte!="" || $canBeEmpty["facu"])
			$fieldsToUpdate[]= "faculte='".$faculte."'";
/*		if ($description!="" || $canBeEmpty["description"])
			$fieldsToUpdate[]= "description='".$description."'";*/
		if ($visible=="false" && $allowedToSubscribe=="false")
			$fieldsToUpdate[]= "visible='0'";
		elseif ($visible=="false" && $allowedToSubscribe=="true")
			$fieldsToUpdate[]= "visible='1'";
		elseif ($visible=="true" && $allowedToSubscribe=="false")
			$fieldsToUpdate[]= "visible='3'";
		elseif ($visible=="true" && $allowedToSubscribe=="true")
			$fieldsToUpdate[]= "visible='2'";
		if ( $HTTP_POST_VARS["titulary"] !="" || $canBeEmpty["titulary"])
			$fieldsToUpdate[]= "titulaires='".$titulary."'";
		if ($screenCode!="" || $canBeEmpty["screenCode"])
			$fieldsToUpdate[]= "fake_code='".$screenCode."'";
		if ($lanCourseForm !="" || $canBeEmpty["lanCourseForm"])
			$fieldsToUpdate[]= "languageCourse='".$lanCourseForm."'";
		if ($extLinkName!=""  || $canBeEmpty["extLinkName"])
			$fieldsToUpdate[]= "departmentUrlName='".$extLinkName."'";
		if ($extLinkUrl !="" || $canBeEmpty["extLinkUrl"])
			$fieldsToUpdate[]= "departmentUrl='".$extLinkUrl."'";
		if($email!="" || $canBeEmpty["email"])
			$fieldsToUpdate[]= "email='".$email."'";
				
		mysql_query("UPDATE `".$TABLECOURSE."`
					 SET ".implode(",",$fieldsToUpdate)."
					 WHERE code=\"".$current_cid."\"");
		// we also need to modify the default langage of the phpbb forums
		mysql_query("UPDATE `".$TABLEPHPBBCONFIG."`
				SET `default_lang` = '".$lanCourseForm."'
				WHERE `config_id` = 1");
		$cidReset = true;
		$cidReq = $current_cid;
		include($includePath."/claro_init_local.inc.php");
           
           
$controlMsg["success"][]= $langModifDone;

claro_disp_msg_arr($controlMsg);

///
echo "
		<br>
		<a href=\"".$_SERVER['PHP_SELF']."?".$toAddtoURL."\">".$langToCourseSettings."</a>
		|
		<a href=\"".$coursesRepositoryWeb.$currentCourseRepository."/index.php?\">".$langHome."</a>";


		if($is_platformAdmin && isset($cidToEdit))
		{
		echo " |
		<a href=\"../admin/index.php\">".$langBackToAdminPage."</a>";
		}

echo "<br>";

/*==========================
           FORM
  ==========================*/

	}
	else
	{

$sqlCourseExtention = "SELECT * FROM `".$TABLECOURSE."` WHERE code = '".$current_cid."'";

$resultCourseExtention 			= mysql_query($sqlCourseExtention);
$thecourse 	= mysql_fetch_array($resultCourseExtention);



$currentCourseDiskQuota 		= $currentCourseExtentionData["diskQuota"     ];
$currentCourseLastVisit 		= $currentCourseExtentionData["lastVisit"     ];
$currentCourseLastEdit			= $currentCourseExtentionData["lastEdit"      ];
$currentCourseCreationDate 		= $currentCourseExtentionData["creationDate"  ];
$currentCourseExpirationDate	= $currentCourseExtentionData["expirationDate"];

$int               = $thecourse['intitule'           ];
$facu              = $thecourse['faculte'   ];
$currentCourseCode = $thecourse['fake_code'   ];
$titulary          = $thecourse['titulaires'        ];
$languageCourse    = $thecourse['languageCourse'       ];
$extLinkName	   = $thecourse['departmentUrlName'];
$extLinkUrl        = $thecourse['departmentUrl' ];
$email			   = $thecourse['email'];
$directory         = $thecourse['directory'];

$thecourse['visibility'  ]         = (bool) ($thecourse['visible'] == 2 || $thecourse['visible'] == 3);
$thecourse['registrationAllowed']  = (bool) ($thecourse['visible'] == 1 || $thecourse['visible'] == 2);

$visibleChecked             [$thecourse['visibility'         ]] = "checked";
$registrationAllowedChecked [$thecourse['registrationAllowed']] = "checked";

?>
<form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">

<table  cellpadding="3" border="0">

<tr>
<td></td>
<td>
<?
if (isset($cidToEdit) && ($is_platformAdmin))
        {
           echo "<a  href=\"".$coursesRepositoryWeb.$directory."\"> ".$langViewCourse." </a>";
        }
?>
</td>
</tr>


<tr>
<td align="right"><label for="screenCode"><?php echo $langCode ?></label>&nbsp;:</td>
<td><input type="text" id="screenCode" name="screenCode" value="<?php echo htmlentities($currentCourseCode); ?>" size="20"></td>
</tr>

<tr>
<td align="right"><label for="titulary"><?php echo $langProfessor ?></label>&nbsp;:</td>
<td><input type="text"  id="titulary" name="titulary" value="<?php echo htmlentities($titulary); ?>" size="60"></td>
</tr>

<tr>
<td align="right"><label for="email"><?echo $langEmail ?></label>&nbsp;:</td>
<td><input type="text"  id="email" name="email" value="<?php echo htmlentities($email); ?>" size="30" maxlength="255"></td>
</tr>

<tr>
<td align="right"><label for="int"><?php echo $langTitle ?></label> :</td>
<td><input type="Text" name="int" id="int" value="<?php echo htmlentities($int); ?>" size="60"></td>
</tr>

<tr>
<td align="right"><label for="faculte"><?php echo $langFaculty ?></label> :</td>
<td>

<?php
BuildEditableCatTable($facu," &gt; ");      
?>

</td>
</tr>

<tr>
<td align="right"><label for="extLinkName"><?php echo $langDepartmentUrlName ?></label>&nbsp;: </td>
<td><input type="text" name="extLinkName" id="extLinkName" value="<?php echo htmlentities($extLinkName); ?>" size="20" maxlength="30"></td>
</tr>

<tr>
<td align="right" nowrap><label for="extLinkUrl" ><?php echo $langDepartmentUrl ?></label>&nbsp;:</td>
<td><input type="text" name="extLinkUrl" id="extLinkUrl" value="<?php echo htmlentities($extLinkUrl); ?>" size="60" maxlength="180"></td>
</tr>

<tr>
<td valign="top" align="right"><label for="lanCourseForm"><?php echo $langLanguage ?></label> : </td>
<td>
<select name="lanCourseForm" id="lanCourseForm">
<?php	// determine past language of the course
$dirname = "../lang/";
if($dirname[strlen($dirname)-1]!='/') $dirname.='/';
$handle=opendir($dirname);

while ($entries = readdir($handle))
{
	if ($entries=='.'||$entries=='..'||$entries=='CVS') continue;

	if (is_dir($dirname.$entries))
	{
		echo "<option value=\"$entries\"";
		if ($entries == $languageCourse) echo " selected ";
		echo '>'.$entries;
				if (!empty($langNameOfLang[$entries]) && $langNameOfLang[$entries]!="" && $langNameOfLang[$entries]!=$entries)
				echo " - $langNameOfLang[$entries]";
		echo "</option>\n";
	}
}
closedir($handle);
?>
</select>
<br><small><font color="gray"><?php echo $langTipLang ?></font></small>
</td>
</td>
</tr>

<tr>
<td></td>
<td>
<?
if (isset($cidToEdit) && ($is_platformAdmin))
{
    echo "<a  href=\"../admin/admincourseusers.php?cidToEdit=".$cidToEdit."\"> ".$langAllUsersOfThisCourse." </a>";
}
?>
</td>
</tr>

<tr>
<td valign="top" align="right" nowrap><?php echo $langCourseAccess; ?> : </td>
<td>
<input type="radio" id="visible_true" name="visible" value="true" <?php echo $visibleChecked[TRUE] ?>> <label for="visible_true"><?php echo $langPublic; ?></label><br>
<input type="radio" id="visible_false" name="visible" value="false" <?php echo $visibleChecked[FALSE]; ?>> <label for="visible_false"><?php echo $langPrivate; ?></label>
</td>
</tr>

<tr>
<td valign="top"align="right"><?php echo $langSubscription; ?> : </td>
<td>
<input type="radio" id="allowedToSubscribe_true" name="allowedToSubscribe" value="true" <?php echo $registrationAllowedChecked[TRUE] ?>> <label for="allowedToSubscribe_true"><?php echo $langAllowed; ?></label><br>
<input type="radio" id="allowedToSubscribe_false"  name="allowedToSubscribe" value="false" <?php echo $registrationAllowedChecked[FALSE] ?>> <label for="allowedToSubscribe_false"><?php echo $langDenied; ?></label>
<? if (isset($cidToEdit))
{
echo "<input type=\"hidden\" name=\"cidToEdit\" value=\"".$cidToEdit."\">";
}
?>
</td>
</tr>

<tr>
<td></td>
<td><small><font color="gray"><?php echo $langConfTip ?></font></small></td>
</tr>

<tr>
<td></td>
<td>
<input type="submit" name="changeProperties" value=" <?php echo $langOk ?> ">
</td>
</tr>

<?php
if ($showDiskQuota && $currentCourseDiskQuota!="" )
{
?>
<tr>
<td><?php echo $langDiskQuota; ?>&nbsp;:</td>
<td><?php echo $currentCourseDiskQuota; ?> <?php echo $byteUnits[0] ?></td>
</tr>

<?php
}
if ($showLastEdit && $currentCourseLastEdit!="" && $currentCourseLastEdit!="0000-00-00 00:00:00")
{
?>
<tr>
<td><?php echo $langLastEdit; ?>&nbsp;:</td>
<td><?php echo claro_format_locale_date($dateTimeFormatLong,strtotime($currentCourseLastEdit)); ?></td>
</tr>

<?php
}
if ($showLastVisit && $currentCourseLastVisit != "" && $currentCourseLastVisit!="0000-00-00 00:00:00")
{
?>
<tr>
<td><?php echo $langLastVisit; ?>&nbsp;:</td>
<td><?php echo claro_format_locale_date($dateTimeFormatLong,strtotime($currentCourseLastVisit)); ?></td>
</tr>

<?php
}
if ($showCreationDate && $currentCourseCreationDate!="" && $currentCourseCreationDate!="0000-00-00 00:00:00")
{
?>
<tr>
<td><?php echo $langCreationDate; ?>&nbsp;:</td>
<td><?php echo claro_format_locale_date($dateTimeFormatLong,strtotime($currentCourseCreationDate)); ?></td>
</tr>

<?php
}
if ($showExpirationDate && $currentCourseExpirationDate!="" && $currentCourseExpirationDate!="0000-00-00 00:00:00")
{
?>
<tr>
<td><?php echo $langExpirationDate; ?>&nbsp;:</td>
<td>
<?php
	echo claro_format_locale_date($dateTimeFormatLong,strtotime($currentCourseExpirationDate));
	echo "<BR>Soit dans : ";
	$nbJour = (strtotime($currentCourseExpirationDate) - time()) / (60*60*24);
	$nbAnnees  = round($nbJour / 365);
	$nbJour = round($nbJour - $nbAnnees*365);
	switch ($nbAnnees)
	{
		case "1" : 	echo $nbAnnees, " an "; break;
		case "0" : 	break;
		default	 : 	echo $nbAnnees, " ans ";
	};
	switch ($nbJour)
	{
		case "1" : 	echo $nbJour, " jour "; break;
		case "0" : 	break;
		default	 : 	echo $nbJour, " jours ";
	}

	if ($canReportExpirationDate)
	{
		echo " -&gt; <a href=\"".$urlScriptToReportExpirationDate."\">".$langPostPone."</a>";
	}
?>
</td>
</tr>

<?php
}
?>
<tr>
<td colspan="2">
<?php
		if($showLinkToExportThisCourse || $showLinkToDeleteThisCourse)
		{
?>

<hr noshade size="1">

<?php
		}

		if($showLinkToExportThisCourse)
		{
?>

<a href="archive_course.php"><?php echo $langBackupCourse; ?></a>

<?php
		}

		if($showLinkToExportThisCourse && $showLinkToDeleteThisCourse)
		{
			echo ' | ';
		}

		if($showLinkToDeleteThisCourse)
		{

if (isset($cidToEdit))
{
    $toAdd="?cidToEdit=".$current_cid;
    $toAdd.="&cfrom=".$cfrom;
}
?>

<a class="claroButton" href="delete_course.php<?php echo $toAdd ?>">
<img src="<?php echo $clarolineRepositoryWeb ?>img/delete.gif">
<?php echo $langDelCourse; ?>
</a>


<?php

//claro_disp_button("delete_course.php".$toAdd, $langDelCourse);

if (isset($cfrom) && ($is_platformAdmin))
      {
        if ($cfrom=="clist")  //in case we come from the course list in admintool
        {
           //claro_disp_button("../admin/admincourses".$toAdd, $langBackToList);
           ?>
           <a class="claroButton" href="../admin/admincourses.php<?php echo $toAdd ?>"><?php echo $langBackToList; ?></a>
           <?php
        }
      }

}
?>

</td>
</tr>
</table>
</form>

<?php
	}     // else
}   // if uid==prof_id
####################STUDENT VIEW ##################################
else
{
	echo $langForbidden;
}   // else

include($includePath."/claro_init_footer.inc.php");
?>
