<?php // $Id$
/**
 * CLAROLINE version 1.6
 *
 * Copyright (c) 2001, 2004 Universite catholique de Louvain (UCL)
 *
 * @Author Thomas Depraetere <depraetere@ipm.ucl.ac.be>
 * @Author Hugues Peeters    <peeters@ipm.ucl.ac.be>
 * @Author Christophe Gesch� <gesche@ipm.ucl.ac.be>

 * Backuping  of  a course.
 *
 * this  script  must be  adminCourse only.
 *
 *	- check if course exist ( to be used  by admin.)
 * 	- build backup config file contain max info to restore the  course.
 *	- Copy all of  this  in a  target directory.
 * 		- records  form main database, about the course
 * 		- course database
 * 		- diretory of the  course
 *
 * 	- compress the directory and content  in a archive file.
 *
 * @var boolean	$verboseBackup		fix if the comment about backuping must be echo
 * @var string	$archiveDir			path  from claroRoot
 * @var string	$ext				ext of global description file  of backup.
 * @var string	$dateBackuping		litteral date  to marks all file generated during the backup
 * @var	string	$shortDateBackuping litteral date  to marks file generated during the backup
 * @var string	$systemFileNameOfArchive	global description file  of backup.
*/

//$interbredcrump[]= array ("url"=>"index.php", "name"=> $langAdmin);
require '../inc/claro_init_global.inc.php';
if ( ! $_cid) claro_disp_select_course();
$nameTools = $langBackup;
include($includePath."/conf/course_export.conf.php");
include($includePath."/lib/export.lib.inc.php");
@include($includePath."/lib/debug.lib.inc.php");
include($includePath."/lib/fileManage.lib.php");
include("temp.lang.file.inc.php");
if (extension_loaded("zlib"))
{
	include($includePath."/lib/pclzip/pclzip.lib.php");
}

$htmlHeadXtra[] = "
<SCRIPT>
function checkChild(me,child)
{
	child.checked = me.checked;
};
function checkAsMe (me,target)
{
		target.checked = me.checked;
};

function checkIfImChecked (me,target)
{
	if (me.checked)
	{
		target.checked = me.checked;
	}
};

function uncheckIfImUnchecked (me,target)
{
	if (me.checked)
	{
	}
	else
	{
		target.checked = me.checked;
	}
};

function checkFather(me,father)
{
	if (me.checked)
	{
		father.checked = me.checked;

	}
};
</SCRIPT>";

$default_language = $platformLanguage; 
/*
 * DB tables definition
 */

$tbl_mdb_names       = claro_sql_get_main_tbl();
$tbl_course          = $tbl_mdb_names['course'           ];
$tbl_rel_course_user = $tbl_mdb_names['rel_course_user'  ];
$tbl_user            = $tbl_mdb_names['user'             ];

$TABLEUSER 		= $tbl_user;
$TABLECOURS 	= $tbl_course;
$TABLECOURSUSER = $tbl_rel_course_user;

$currentCourseID = $_course["sysCode"];

$displayFormWhatSaveMain = TRUE;

if (isset($_REQUEST["goToStep2"]))
{
	// go to the Form "What Do Now" From the Form "What Save" Main 
	// save info from Form "What Save" Main 
	$saveProperties 					= $_REQUEST["saveProperties"];
	$saveContent 						= $_REQUEST["saveContent"];
	$saveSubscription					= $_REQUEST["saveSubscription"];
	$backupUser 						= $_REQUEST["backupUser"];
	$makeListingUserSubscriptionHTML	= $_REQUEST["makeListingUserSubscriptionHTML"];
	$makeListingUserSubscriptionCSV 	= $_REQUEST["makeListingUserSubscriptionCSV"];
	$backupUserSubscription 			= $_REQUEST["backupUserSubscription"];
	$backupUserInfo 					= $_REQUEST["backupUserInfo"];
	$saveGroupsSubscriptions 			= $_REQUEST["saveGroupsSubscriptions"];
	$makeListingGroupComposition		= $_REQUEST["makeListingGroupComposition"];
	$backupGroupComposition 			= $_REQUEST["backupGroupComposition"];
	$saveContentDoc 					= $_REQUEST["saveContentDoc"];
	$saveContentLink 					= $_REQUEST["saveContentLink"];
	$saveContentCalandar 				= $_REQUEST["saveContentCalandar"];
	$saveContentAnnouncement 			= $_REQUEST["saveContentAnnouncement"];
	$saveContentCourseHomePage 			= $_REQUEST["saveContentCourseHomePage"];
	$saveContentCourseDescription 		= $_REQUEST["saveContentCourseDescription"];
	$saveContentWorks 					= $_REQUEST["saveContentWorks"];
	$saveContentWorksStructure 			= $_REQUEST["saveContentWorksStructure"];
	$saveContentWorksContent 			= $_REQUEST["saveContentWorksContent"];
	$saveContentForum 					= $_REQUEST["saveContentForum"];
	$saveContentForumStructure 			= $_REQUEST["saveContentForumStructure"];
	$saveContentForumContent 			= $_REQUEST["saveContentForumContent"];
	$saveContentWiki 					= $_REQUEST["saveContentWiki"];
	$saveContentGroup 					= $_REQUEST["saveContentGroup"];
	$saveContentGroupStructure			= $_REQUEST["saveContentGroupStructure"];
	$saveContentGroupForum 				= $_REQUEST["saveContentGroupForum"];
	$saveContentGroupForumStructure 	= $_REQUEST["saveContentGroupForumStructure"];
	$saveContentGroupForumContent 		= $_REQUEST["saveContentGroupForumContent"];
	$saveContentGroupWorks 				= $_REQUEST["saveContentGroupWorks"];
	$saveContentGroupWorksStructure 	= $_REQUEST["saveContentGroupWorksStructure"];
	$saveContentGroupWorksContent 		= $_REQUEST["saveContentGroupWorksContent"];
	session_register (
		"saveContentDoc",
		"saveContentLink",
		"saveContentCalandar",
		"saveContentAnnouncement",
		"saveContentCourseHomePage",
		"saveContentCourseDescription",
		"saveContentWorks",
		"saveContentWorksStructure",
		"saveContentWorksContent",
		"saveContentForum",
		"saveContentForumStructure",
		"saveContentForumContent",
		"saveContentWiki",
		"saveContentGroup",
		"saveContentGroupStructure",
		"saveContentGroupForum",
		"saveContentGroupForumStructure",
		"saveContentGroupForumContent",
		"saveContentGroupWorks",
		"saveContentGroupWorksStructure",
		"saveContentGroupWorksContent",
		"backupUser",
		"makeListingUserSubscriptionHTML",
		"makeListingUserSubscriptionCSV",
		"backupUserSubscription",
		"backupUserInfo",
		"saveGroupsSubscriptions",
		"makeListingGroupComposition",
		"backupGroupComposition",
		"saveProperties",
		"saveContent",
		"saveSubscription");
	$displayFormWhatDoNow = TRUE;
	$displayFormWhatSaveMain = FALSE;
}
elseif (isset($_REQUEST["BackToStep1"]))
{
	// go to the Form "What Save" Main From the Form "What Do Now"
	// save info from Form "What Do Now"
	$saveOnUser 		= $_REQUEST["saveOnUser"];
	$saveOnServer 		= $_REQUEST["saveOnServer"];
	$saveOnFtp 			= $_REQUEST["saveOnFtp"];
	$verboseBackup 		= $_REQUEST["verboseBackup"];
	$newCourse 			= $_REQUEST["newCourse"];
	session_register ("saveOnUser","saveOnServer","saveOnFtp","newCourse","verboseBackup");
}
elseif ( isset($_REQUEST["doTheWork"]) and ($saveOnUser=="checked"))
{
	$displayFormWhatSaveMain 	= FALSE;
	$displayShowWork 			= TRUE;
	$nameTools 					= $langArchiveCourse;

	$verboseBackup 				= $_REQUEST["verboseBackup"];
	$showLinkToZip 				= $_REQUEST["saveOnUser"];
	$backupDataFromMainDB 		= $_REQUEST["saveProperties"];
	$backupDataFromCourseDB		= $_REQUEST["saveContent"];
	$backupUser					= $_REQUEST["saveSubscription"];
}
else // default values
{
	setValueIfNotInSession("saveProperties","checked");
	setValueIfNotInSession("saveContent","checked");
	setValueIfNotInSession("saveSubscription","checked");
	setValueIfNotInSession("makeListingUserSubscriptionHTML","checked");
	setValueIfNotInSession("makeListingUserSubscriptionCSV","checked");
	setValueIfNotInSession("backupUserSubscription","checked");
	setValueIfNotInSession("backupUserInfo","checked");
	setValueIfNotInSession("backupUser","checked");
	setValueIfNotInSession("saveGroupsSubscriptions","checked");
	setValueIfNotInSession("makeListingGroupComposition","checked");
	setValueIfNotInSession("backupGroupComposition","checked");
	setValueIfNotInSession("saveContentDoc","checked");
	setValueIfNotInSession("saveContentLink","checked");
	setValueIfNotInSession("saveContentCalandar","checked");
	setValueIfNotInSession("saveContentAnnouncement","checked");
	setValueIfNotInSession("saveContentCourseHomePage","checked");
	setValueIfNotInSession("saveContentCourseDescription","checked");
	setValueIfNotInSession("saveContentWorks","checked");
	setValueIfNotInSession("saveContentWorksStructure","checked");
	setValueIfNotInSession("saveContentWorksContent","checked");
	setValueIfNotInSession("saveContentForum","checked");
	setValueIfNotInSession("saveContentForumStructure","checked");
	setValueIfNotInSession("saveContentForumContent","checked");
	setValueIfNotInSession("saveContentWiki","checked");
	setValueIfNotInSession("saveContentGroup","checked");
	setValueIfNotInSession("saveContentGroupStructure","checked");
	setValueIfNotInSession("saveContentGroupForum","checked");
	setValueIfNotInSession("saveContentGroupForumStructure","checked");
	setValueIfNotInSession("saveContentGroupForumContent","checked");
	setValueIfNotInSession("saveContentGroupWorks","checked");
	setValueIfNotInSession("saveContentGroupWorksStructure","checked");
	setValueIfNotInSession("saveContentGroupWorksContent","checked");
	setValueIfNotInSession("saveOnUser","checked");
	setValueIfNotInSession("saveOnServer","checked");
	setValueIfNotInSession("saveOnFtp","checked");
	setValueIfNotInSession("newCourse","checked");
	setValueIfNotInSession("verboseBackup",($verboseBackupDefault?"checked":""));
}

include($includePath."/claro_init_header.inc.php");
claro_disp_tool_title(array("mainTitle"=>$nameTools, "subTitle"=>$langBackupThisCourse));

if ($displayFormWhatSaveMain)
{
?>
<form action="<?php echo $_server['PHP_SELF'] ?>" method="post" name="WhatSaveMain">
<table class="forms" width="100%" >
	<tr>
		<TD colspan="2">
			<input type="checkbox" name="saveProperties" value="checked" <?php echo $saveProperties ?>> 
			<?php echo $langCourseSettings; ?>
		</td>
	</tr>
	<tr>
		<TD >
			<input type="checkbox" onClick="checkAsMe(this,this.form.saveContent); checkAsMe(this,this.form.saveContentDoc); checkAsMe(this,this.form.saveContentLink); checkAsMe(this,this.form.saveContentCalandar); checkAsMe(this,this.form.saveContentAnnouncement); checkAsMe(this,this.form.saveContentCourseHomePage); checkAsMe(this,this.form.saveContentCourseDescription); checkAsMe(this,this.form.saveContentWorks); checkAsMe(this,this.form.saveContentWorksStructure); checkAsMe(this,this.form.saveContentWorksContent); checkAsMe(this,this.form.saveContentForum); checkAsMe(this,this.form.saveContentForumStructure); checkAsMe(this,this.form.saveContentForumContent); checkAsMe(this,this.form.saveContentWiki); checkAsMe(this,this.form.saveContentGroup); checkAsMe(this,this.form.saveContentGroupStructure); checkAsMe(this,this.form.saveContentGroupForum); checkAsMe(this,this.form.saveContentGroupForumStructure); checkAsMe(this,this.form.saveContentGroupForumContent); checkAsMe(this,this.form.saveContentGroupWorks); checkAsMe(this,this.form.saveContentGroupWorksStructure); checkAsMe(this,this.form.saveContentGroupWorksContent); "
			name="saveContent" value="checked" <?php echo $saveContent ?>> 
		</td>
		<td >
			<?php echo $langCourseContent; ?>
		</td>
	</tr>
	<tr>
		<TD>
		</TD>
		<td >
			<table name="tbl_courseContent" width="100%" >
				<tr>
					<TD >
						<input type="checkbox" onClick="checkIfImChecked(this,this.form.saveContent);"
						name="saveContentDoc" value="checked" "<?php echo $saveContentDoc ?>" >  
					</td>
					<td colspan="3">
						<?php echo $langSaveContentDoc; ?>
					</td>
				</tr>
				<tr>
					<TD >
						<input type="checkbox" onClick="checkIfImChecked(this,this.form.saveContent); "
						name="saveContentLink" value="checked" "<?php echo $saveContentLink ?>" >  
					</td>
					<td colspan="3">
						<?php echo $langLinks; ?>
					</td>
				</tr>
				<tr>
					<TD >
						<input type="checkbox" onClick="checkIfImChecked(this,this.form.saveContent);"
						name="saveContentCalandar" value="checked" "<?php echo $saveContentCalandar ?>" >  
				</td>
					<td colspan="3">
						<?php echo $langAgenda; ?>
					</td>
				</tr>
				<tr>
					<TD >
						<input type="checkbox" onClick="checkIfImChecked(this,this.form.saveContent);"
						name="saveContentAnnouncement" value="checked" "<?php echo $saveContentAnnouncement ?>" >  
					</td>
					<td colspan="3">
						<?php echo $langAnnouncement; ?>
					</td>
				</tr>
				<tr>
					<TD >
						<input type="checkbox" onClick="checkIfImChecked(this,this.form.saveContent); "
						name="saveContentCourseHomePage" value="checked" "<?php echo $saveContentCourseHomePage ?>" >  
					</td>
					<td colspan="3">
						<?php echo $langCourseHomePage; ?>
					</td>
				</tr>
				<tr>
					<TD >
						<input type="checkbox" onClick="checkIfImChecked(this,this.form.saveContent); "
						name="saveContentCourseDescription" value="checked" "<?php echo $saveContentCourseDescription ?>" >  
					</td>
					<td colspan="3">
						<?php echo $langCourseDescription; ?>
					</td>
				</tr>
				<tr>
					<TD >
						<input type="checkbox" onClick="checkIfImChecked(this,this.form.saveContent);checkAsMe(this,this.form.saveContentWorksStructure);checkAsMe(this,this.form.saveContentWorksContent);" 
						name="saveContentWorks" value="checked" "<?php echo $saveContentWorks ?>" >  
					</td>
					<td colspan="3">
						<?php echo $langWork; ?>
					</td>
				</tr>
				<tr>
					<TD>
					</TD>
					<TD >
						<input type="checkbox" onClick="checkIfImChecked(this,this.form.saveContent);checkIfImChecked(this,this.form.saveContentWorks);uncheckIfImUnchecked(this,this.form.saveContentWorksContent)"
						name="saveContentWorksStructure" value="checked" "<?php echo $saveContentWorksStructure ?>" >  
					</td>
					<td colspan="2">
						<?php echo $langWorkStructure ; ?>
					</td>
				</tr>
				<tr>
					<TD>
					</TD>
					<TD >
						<input type="checkbox" onClick="checkIfImChecked(this,this.form.saveContent);checkIfImChecked(this,this.form.saveContentWorks);checkIfImChecked(this,this.form.saveContentWorksStructure);" 
						name="saveContentWorksContent" value="checked" "<?php echo $saveContentWorksContent ?>" >  
					</td>
					<td colspan="2">
						<?php echo $langWorkContent  ; ?>
					</td>
				</tr>
				<tr>
					<TD >
						<input type="checkbox" onClick="checkIfImChecked(this,this.form.saveContent);checkAsMe(this,this.form.saveContentForumStructure);checkAsMe(this,this.form.saveContentForumContent);"
						name="saveContentForum" value="checked" "<?php echo $saveContentForum ?>" >  
					</td>
					<td colspan="3">
						<?php echo $langForums; ?>
					</td>
				</tr>
				<tr>
					<td>
					</td>
					<TD >
						<input type="checkbox" onClick="checkIfImChecked(this,this.form.saveContent);checkAsMe(this,this.form.saveContentForum);uncheckIfImUnchecked(this,this.form.saveContentForumContent);"
						name="saveContentForumStructure" value="checked" "<?php echo $saveContentForumStructure ?>" >  
					</td>
					<td colspan="2">
						<?php echo $langForumStructure; ?>
					</td>
				</tr>
				<tr>
					<TD>
					</TD>
					<TD>
						<input type="checkbox" onClick="checkIfImChecked(this,this.form.saveContent);checkIfImChecked(this,this.form.saveContentForum);checkIfImChecked(this,this.form.saveContentForumStructure);" 
						name="saveContentForumContent" value="checked" "<?php echo $saveContentForumContent ?>" >  
					</td>
					<td colspan="2">
						<?php echo $langForumContent ; ?>
					</td>
				</tr>
				<tr>
					<TD >
						<input type="checkbox" onClick="checkIfImChecked(this,this.form.saveContent);"
						name="saveContentWiki" value="checked" "<?php echo $saveContentWiki ?>" >  
					</td>
					<td colspan="3">
						<?php echo $langWikiContent ; ?>
					</td>
				</tr>
				<tr>
					<TD >
						<input type="checkbox" onClick="checkIfImChecked(this,this.form.saveContent);checkAsMe(this,this.form.saveContentGroupStructure);checkAsMe(this,this.form.saveContentGroupForum);checkAsMe(this,this.form.saveContentGroupForumStructure);checkAsMe(this,this.form.saveContentGroupForumContent);checkAsMe(this,this.form.saveContentGroupWorks);checkAsMe(this,this.form.saveContentGroupWorksStructure);checkAsMe(this,this.form.saveContentGroupWorksContent);" 
						name="saveContentGroup" value="checked" "<?php echo $saveContentGroup ?>" >  
					</td>
					<td colspan="3">
						<?php echo $langGroup ; ?>
					</td>
				</tr>
				<tr>
					<TD>
					</TD>
					<TD >
						<input type="checkbox" onClick="checkIfImChecked(this,this.form.saveContent);checkIfImChecked(this,this.form.saveContentGroup);checkAsMe(this,this.form.saveContentGroupForum);checkAsMe(this,this.form.saveContentGroupForumStructure);uncheckIfImUnchecked(this,this.form.saveContentGroupForumContent);checkAsMe(this,this.form.saveContentGroupWorks);checkAsMe(this,this.form.saveContentGroupWorksStructure);uncheckIfImUnchecked(this,this.form.saveContentGroupWorksContent);" 
						name="saveContentGroupStructure" value="checked" "<?php echo $saveContentGroupStructure ?>" >  
					</td>
					<td colspan="2">
						<?php echo $langGroupStructure ; ?>
					</td>
				</tr>
				
				<!-- Groups Forum-->
				
				<tr>
					<TD>
					</TD>
					<TD>
						<input type="checkbox"  onClick="checkIfImChecked(this,this.form.saveContent);checkIfImChecked(this,this.form.saveContentGroup);checkIfImChecked(this,this.form.saveContentGroupStructure);checkAsMe(this,this.form.saveContentGroupForumStructure);checkAsMe(this,this.form.saveContentGroupForumContent);" 
						name="saveContentGroupForum" value="checked" "<?php echo $saveContentGroupForum ?>" >  
					</td>
					<td colspan="2">
						<?php echo $langGroupForum; ?>
					</td>
				</tr>
				<tr>
					<TD>
					</TD>
					<TD>
					</TD>
					<TD>
						<input type="checkbox"  onClick="checkIfImChecked(this,this.form.saveContent);checkIfImChecked(this,this.form.saveContentGroup);checkIfImChecked(this,this.form.saveContentGroupStructure);checkAsMe(this,this.form.saveContentGroupForum);uncheckIfImUnchecked(this,this.form.saveContentGroupForumContent);" 
						name="saveContentGroupForumStructure" value="checked" "<?php echo $saveContentGroupForumStructure ?>" >  
					</td>
					<td colspan="1">
						<?php echo $langGroupForumStructure; ?>
					</td>
				</tr>
				<tr>
					<TD>
					</TD>
					<TD>
					</TD>
					<TD>
						<input type="checkbox" onClick="checkIfImChecked(this,this.form.saveContent);checkIfImChecked(this,this.form.saveContentGroup);checkIfImChecked(this,this.form.saveContentGroupStructure);checkIfImChecked(this,this.form.saveContentGroupForum);checkIfImChecked(this,this.form.saveContentGroupForumStructure);" 
						name="saveContentGroupForumContent" value="checked" "<?php echo $saveContentGroupForumContent ?>" >  
					</td>
					<td >
						<?php echo $langGroupForumContent ; ?>
					</td>
				</tr>
				
				<!-- Groups Works-->
				
				<tr>
					<TD>
					</TD>
					<TD>
						<input type="checkbox" onClick="checkIfImChecked(this,this.form.saveContent);checkIfImChecked(this,this.form.saveContentGroup);checkIfImChecked(this,this.form.saveContentGroupStructure);checkAsMe(this,this.form.saveContentGroupWorksStructure);checkAsMe(this,this.form.saveContentGroupWorksContent);" 
						name="saveContentGroupWorks" value="checked" "<?php echo $saveContentGroupWorks ?>" >  
					</td>
					<td colspan="2">
						<?php echo $langGroupWork; ?>
					</td>
				</tr>
				<tr>
					<TD>
					</TD>
					<TD>
					</TD>
					<TD>
						<input type="checkbox" onClick="checkIfImChecked(this,this.form.saveContent);checkIfImChecked(this,this.form.saveContentGroup);checkIfImChecked(this,this.form.saveContentGroupStructure);checkAsMe(this,this.form.saveContentGroupWorks);uncheckIfImUnchecked(this,this.form.saveContentGroupWorksContent);"
						name="saveContentGroupWorksStructure" value="checked" "<?php echo $saveContentGroupWorksStructure ?>" >  
					</td>
					<td colspan="1">
						<?php echo $langGroupWorkStructure; ?>
					</td>
				</tr>
				<tr>
					<TD>
					</TD>
					<TD>
					</TD>
					<TD>
						<input type="checkbox" onClick="checkIfImChecked(this,this.form.saveContent);checkIfImChecked(this,this.form.saveContentGroup);checkIfImChecked(this,this.form.saveContentGroupStructure);checkIfImChecked(this,this.form.saveContentGroupWorks);checkIfImChecked(this,this.form.saveContentGroupWorksStructure);"  
						name="saveContentGroupWorksContent" value="checked" "<?php echo $saveContentGroupWorksContent ?>" >  
					</td>
					<td >
						<?php echo $langGroupWorkContent; ?>
					</td>
				</tr>
			</table>
		</td> 
	</tr>
	<tr>
		<TD >
			<input type="checkbox" onClick="checkAsMe(this,this.form.backupUserInfo);checkAsMe(this,this.form.saveGroupsSubscriptions);" 
			name="backupUser" value="checked" <?php echo $backupUser ?> >  
		</td>
		<TD >
			
			<?php echo $langSaveSubscription; ?>
		</td>
	</tr>
	<tr>
		</td>
		<TD >
		<TD >
			<table width="100%" >
				<tr>
					<TD>
						<input type="checkbox" onClick="checkIfImChecked(this,this.form.backupUser)" 
						name="backupUserInfo" value="checked" "<?php echo $backupUserInfo ?>" >  
					</td>
					<td >
						<?php echo $langIncludeUserPersonalInfo ; ?>
					</td>
				</tr>
				<tr>
					<td>
						<input type="checkbox" onClick="checkIfImChecked(this,this.form.backupUser)" 
						name="saveGroupsSubscriptions" value="checked" "<?php echo $saveGroupsSubscriptions ?>" >  
					</td>
					<td colspan="1">
						<?php echo $langGroups; ?>
					</td>
				</tr>
			</table>
		</td> 
	</tr>
	<tr>
		<TD colspan="2">
<input type="submit" name="goToStep2">
</td>
	</tr>

</table>
</form>
<?
}
elseif ($displayFormWhatDoNow)
{
?>
<H3>
	<?php echo $langWhatDoAfterBackup; ?>
</H3>
<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post" name="STEP3" id="STEP2">
<table  class="forms" width="100%" >
<?
if ($downloadArchiveAivailable)
{
?>
	<tr>
		<td>
			<input type="checkbox" name="saveOnUser" id="saveOnUser" value="checked" "<?php echo $saveOnUser ?>" >   
			<label for="saveOnUser"><?php echo $langRestoreNow; ?></label>
		</td>
	</tr>
<?
}
if ($localStoreArchiveAivailable)
{
?>
	<tr>
		<td>
			<input type="checkbox" name="saveOnServer" id="saveOnServer" value="checked" "<?php echo $saveOnServer ?>" >  
			<label for="saveOnServer"><?php echo $langCopyOnServerBackupRepository; ?></label>
		</td>
	</tr>
<?
}
if ($putArchiveOnFtpAivailable)
{
?>
	<tr>
		<td>
			<input type="checkbox" name="saveOnFtp" id="saveOnFtp" value="checked" "<?php echo $saveOnFtp ?>" >  
			<label for="saveOnFtp"><?php echo $langPutOnFtpServer; ?></label>
		</td>
	</tr>
<?
}
if ($createNewCourseWithArchiveAivailable)
{
?>
	<tr>
		<td>
			<input type="checkbox" name="newCourse" id="newCourse" value="checked" "<?php echo $newCourse ?>" >  
			<label for="newCourse"><?php echo $langCreateANewCourseNow; ?></label>
		</td>
	</tr>
<?
}
?>
	<tr>
		<td>
			<input type="submit" value="Back" name="BackToStep1">
		</td>
	</tr>
	<tr>
		<td>
			<input type="submit" name="doTheWork" validationmsg="<?php echo $langBackup; ?>" value="<?php echo $langBackup; ?>">
			<input type="checkbox" name="verboseBackup" id="verboseBackup" value="checked" <?php echo $verboseBackup ?>">
			<label for="verboseBackup"><?php echo $langShowAllProcessDuringTheWork; ?></label>
		</td>
	</tr>
</table>
</form>
<?
}
elseif ($displayShowWork )
{
	// $showLinkToZip = true; //force output to debug
	if (makeTheBackup($currentCourseID, $showLinkToZip))
	{
		if ($showLinkToZip)
		{
			echo "
	<font color=\"#FF0000\">
		".$langBackupSuccesfull."
	</font><br><br>
	
	<a href=\"".$rootWeb.$pathToArchive."\">".$archiveName."</a>";
		}
	}
	else
	{
		echo "<br>
		<br>
		<div class=\"msgErreur\">erreur pendant le backup :
		<UL>";
		echo $errorReport;
		reset($error_no);
		foreach($error_no as $errType => $theseLineErrNo)
		{
			echo "<LI><hR>",$errType,"<HR><UL>";
			foreach($theseLineErrNo as $errLine => $thisLineErrNo)
			{
				echo "<LI>(",$errLine,"):",$thisLineErrNo,"-",$error_msg[$errType][$errLine],"<BR></LI>";
			}
			echo "</UL></LI>";
		}
		echo "</UL>
		</div>";
	};
}
?>
		<br>
		<br>
		</td>
	</tr>
</table>
<?php
 if (is_array($error_no))
 {
		echo "<br>
		<br>
		<div class=\"msgErreur\">erreur pendant le backup :
		<UL>";
		echo $errorReport;
		reset($error_no);
		foreach($error_no as $errType => $theseLineErrNo)
		{
			echo "<LI><hR>",$errType,"<HR><UL>";
			foreach($theseLineErrNo as $errLine => $thisLineErrNo)
			{
				echo "<LI>(",$errLine,"):",$thisLineErrNo,"-",$error_msg[$errType][$errLine],"<BR></LI>";
			}
			echo "</UL></LI>";
		}
		echo "</UL>
		</div>";
		}
include($includePath."/claro_init_footer.inc.php");
?>
