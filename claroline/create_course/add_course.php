<?php # $Id$
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.5
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2004 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
 */
/**
 * COURSE SITE CREATION TOOL
 * GOALS
 * *******
 * Allow professors and administrative staff to create course sites.
 * This big script makes, basically, 6 things:
 *     1. Create a database whose name=course code (sort of course id)
 *     2. Create tables in this base and fill some of them
 *     3. Create a www directory with the same name as the db name
 *     4. Add the course to the main icampus/course table
 *     5. Check whether the course code is not already taken.
 *     6. Associate the current user id with the course in order to let 
 *        him administer it.
 * 
 * One of the functions of this script is to merge the different 
 * Open Source Tools used in the courses (statistics by EzBoo,
 * forum by phpBB...) under one unique user session and one unique
 * course id.
 * ******************************************************************
 */
/*

List of Events
	- can't create course
		show displayNotForU and exit
	-

List  of  views
	- displayNotForU
		the  user  is not allowed to  use this script
	- displayWhatAdd
		here  user select  what take in the archive
	- displayCourseRestore
		User  can select source file to add course (that's must be a file  build with export)
	- displayCoursePropertiesForm
		User  can enter/edit  parameter  for the  new  course. If  they use an archive,
		value are proposed but can be edited
	- displayCourseAddResult
		New course is added.  Show  success message.
*/
$langFile = "create_course";
require '../inc/claro_init_global.inc.php';

//// Config tool
include($includePath."/conf/add_course.conf.php");
//// LIBS
include($includePath."/lib/text.lib.php");
include($includePath."/lib/add_course.lib.inc.php");
include($includePath."/lib/course.lib.inc.php");
include($includePath."/lib/debug.lib.inc.php");
include($includePath."/lib/fileManage.lib.php");
include($includePath."/conf/course_info.conf.php");


$nameTools = $langCreateSite;

$TABLECOURSE 		= $mainDbName.'`.`cours';
$TABLECOURSE 		= $mainDbName.'`.`cours';
$TABLECOURSDOMAIN	= $mainDbName.'`.`faculte';
$TABLEUSER			= $mainDbName.'`.`user';
$TABLECOURSUSER 	= $mainDbName.'`.`cours_user';
$TABLEANNOUNCEMENTS	= 'announcement';
$can_create_courses = (bool) ($is_allowedCreateCourse);
$coursesRepositories = $coursesRepositorySys;

if (empty($valueEmail)) $valueEmail = $_user['mail'];

//// Starting script
$displayNotForU = FALSE;
if (!$can_create_courses)
{
	$displayNotForU = TRUE;
} // (!$can_create_courses)
else
{
	if (	$sendByUploadAivailable
			|| $sendByLocaleAivailable
			|| $sendByHTTPAivailable
			|| $sendByFTPAivailable
		)
	{
		$displayWhatAdd = TRUE;
	}
	else
	{
		$displayCoursePropertiesForm 	= TRUE;
		$valueTitular					= $_user['firstName']." ".$_user['lastName'];
		$valueLanguage 					= $platformLanguage;
	}

	if (isset($HTTP_POST_VARS["fromWhatAdd"]))
	{
		$displayWhatAdd = FALSE;

		if ($HTTP_POST_VARS["whatAdd"] == "newCourse")
		{
			$displayCoursePropertiesForm 	= TRUE;
			$valueTitular					= $_user['firstName']." ".$_user['lastName'];
			$valueLanguage 					= $platformLanguage;
		}
		elseif ($HTTP_POST_VARS["whatAdd"] == "archive")
		{
			$displayCourseRestore 			= TRUE;
		}
		else
		{
			$displayWhatAdd 				= TRUE;
		}
	} // if (isset($HTTP_POST_VARS["fromWhatAdd"]))
	elseif (isset($HTTP_POST_VARS["selectArchive"]))
	{
		$displayWhatAdd = FALSE;

// 1°   Keep the  zipFile and move it in $pathToStorgeArchiveBeforeUnzip
//		printVar($postFile, "PostFile");
//		printVar($HTTP_POST_FILES, "HTTP_POST_FILES");

		$pathToStorgeArchiveBeforeUnzip = $rootSys."claroline/tmp/".md5(uniqid(mt_rand().$_uid, true));
		mkpath($pathToStorgeArchiveBeforeUnzip);
		//debugIO($pathToStorgeArchiveBeforeUnzip);
		switch($HTTP_POST_VARS["typeStorage"])
		{
			case "upload" :
				$displayCoursePropertiesForm = TRUE;
				if (	$sendByUploadAivailable
						&& is_uploaded_file($postFile)
//						&& copy($HTTP_POST_FILES["postFile"]["tmp_name"], $pathToStorgeArchiveBeforeUnzip)
					)
				{
					$pathToStorgeArchiveBeforeUnzip = dirname($HTTP_POST_FILES["postFile"]["tmp_name"]);
					$nameOfZipFile = basename($HTTP_POST_FILES["postFile"]["tmp_name"]);
					$okToUnzip = TRUE;
				}
				else
				{
					// error during send, back to 1st Panel
					$displayWhatAdd = TRUE;
					$displayCoursePropertiesForm = FALSE;
					$okToUnzip = FALSE;
					break;
				}
				$displayCoursePropertiesForm = TRUE;
				break;
			case "local":
				// copy local file to $pathToStorgeArchiveBeforeUnzip
				$displayCoursePropertiesForm = TRUE;
				$okToUnzip = TRUE;
				if (	!$sendByLocaleAivailable
						&& file_exists($localArchivesRepository.trim($HTTP_POST_VARS["localFile"]))
						&& !copy($localArchivesRepository.trim($HTTP_POST_VARS["localFile"]), $pathToStorgeArchiveBeforeUnzip)
					)
				{
					$nameOfZipFile = basename(trim($HTTP_POST_VARS["localFile"]));
					// error during send, back to 1st Panel
					$displayWhatAdd = TRUE;
					$displayCoursePropertiesForm = FALSE;
					$okToUnzip = FALSE;
					break;
				}
				break;
			case "http":
				// copy downloaded file to $pathToStorgeArchiveBeforeUnzip
				$displayCoursePropertiesForm = TRUE;
				$okToUnzip = TRUE;
				if (!$sendByHTTPAivailable)
				{
					$displayWhatAdd = TRUE;
					$displayCoursePropertiesForm = FALSE;
					$okToUnzip = FALSE;
					break;
				}
				break;
			case "ftp":
				// copy downloaded file to $pathToStorgeArchiveBeforeUnzip
				$displayCoursePropertiesForm = TRUE;
				$okToUnzip = TRUE;
				if (!$sendByFTPAivailable)
				{
					$displayWhatAdd = TRUE;
					$displayCoursePropertiesForm = FALSE;
					$okToUnzip = FALSE;
					break;
				}

				break;
			default :
				$displayWhatAdd = TRUE;
				$okToUnzip = FALSE;
				// gloups
		} // elseif (isset($HTTP_POST_VARS["selectArchive"]))

		//2° unzip archive in $pathToStorgeArchiveBeforeUnzip
		if ($okToUnzip)
		{
			checkArchive($pathToStorgeArchiveBeforeUnzip."/".$nameOfZipFile);

			$displayWhatAdd = FALSE;
			$displayCoursePropertiesForm = TRUE;
			$courseProperties = readPropertiesInArchive($pathToStorgeArchiveBeforeUnzip."/".$nameOfZipFile);
//			printVar($courseProperties," propriétés du cours");
			$showPropertiesFromArchive = TRUE;

			$valueSysId 		= $courseProperties["sysId"];

			$valueCode			= $courseProperties["officialCode"];
			$valueTitular		= $courseProperties["titular"];
			$valueIntitule		= $courseProperties["name"];
			$valueFacultyName	= $courseProperties["categoryName"];
			$valueFacultyCode	= $courseProperties["categoryCode"];
			$valueLanguage 		= $courseProperties["language"];

			$valueDescription 	= $courseProperties["description"];
			$valueDepartment	= $courseProperties["extLinkName"];
			$valueDepartmentUrl	= $courseProperties["extLinkUrl"];

			$valueScoreShow		= $courseProperties["scoreShow"];
			$valueVisibility	= $courseProperties["visibility"];

			$valueAdminCode		= $courseProperties["adminCode"];
			$valueDbName		= $courseProperties["dbName"];
			$valuePath			= $courseProperties["path"];
			$valueRegAllowed 	= $courseProperties["registrationAllowed"];

			$valueVersionDb		= $courseProperties["versionDb"];
			$valueVersionClaro	= $courseProperties["versionClaro"];
			$valueLastVisit		= $courseProperties["lastVisit"];
			$valueLastEdit 		= $courseProperties["lastEdit"];
			$valueExpire 		= $courseProperties["expirationDate"];
		} //if ($okToUnzip)
	}
	elseif ($submitFromCoursProperties)
	{

		if(!isset($_uid)) continue;
		$wantedCode 		= strip_tags($_REQUEST['wantedCode'    ]);
		$newcourse_category	= strip_tags($_REQUEST['faculte'       ]);
		$newcourse_label	= strip_tags($_REQUEST['intitule'      ]);
		$newcourse_language = strip_tags($_REQUEST['languageCourse']);
		$newcourse_titulars	= strip_tags($_REQUEST['titulaires'    ]);
		$newcourse_email 	= strip_tags($_REQUEST['email'         ]);
		
		$okToCreate = true;
		
		
		/////CHECK DATA
		
		
		// LABEL (Previously called intitule
		if (HUMAN_LABEL_NEEDED && empty($newcourse_label)) 
		{
			$okToCreate = FALSE;
			$controlMsg["error"][] = $langLabelCanBeEmpty;
		}
		
		if (HUMAN_CODE_NEEDED && empty($wantedCode)) 
		{
			$okToCreate = FALSE;
			$controlMsg["error"][] = $langCodeCanBeEmpty;
		}
		
		if (COURSE_EMAIL_NEEDED && empty($newcourse_email)) 
		{
			$okToCreate = FALSE;
			$controlMsg["error"][] = $langEmailCanBeEmpty;
		}
		
		// if an email is given It would be correct
		$regexp = "^[0-9a-z_\.-]+@(([0-9]{1,3}\.){3}[0-9]{1,3}|([0-9a-z][0-9a-z-]*[0-9a-z]\.)+[a-z]{2,4})$";
		if (!empty($newcourse_email)&&!eregi( $regexp, $newcourse_email)) 
		{
			$okToCreate = FALSE;
			$controlMsg["error"][] = $langEmailWrong;
		}
		
		
	//  function define_course_keys ($wantedCode, $prefix4all="", $prefix4baseName="", 	$prefix4path="", $addUniquePrefix =false,	$useCodeInDepedentKeys = TRUE	)
		$keys = define_course_keys ($wantedCode,"",$dbNamePrefix);
		$currentCourseCode		 = $keys["currentCourseCode"];
		$currentCourseId		 = $keys["currentCourseId"];
		$currentCourseDbName	 = $keys["currentCourseDbName"];
		$currentCourseRepository = $keys["currentCourseRepository"];
		$expirationDate 		= 	time() + $firstExpirationDelay;
	
		if ($okToCreate)
		{
			if ($DEBUG) echo "[Code:",	$currentCourseCode,"][Id:",$currentCourseId,"][Db:",$currentCourseDbName	 ,"][Path:",$currentCourseRepository ,"]";

			//function prepare_course_repository($courseRepository, $courseId)
	
			prepare_course_repository($currentCourseRepository,$currentCourseId);
			update_Db_course($currentCourseDbName);
			fill_course_repository($currentCourseRepository);
	
			// function 	fill_Db_course($courseDbName,$courseRepository)
			fill_Db_course(	$currentCourseDbName, 
							$currentCourseRepository, 
							$newcourse_language);
							
			register_course($currentCourseId, 
							$currentCourseCode, 
							$currentCourseRepository, 
							$currentCourseDbName, 
							$newcourse_titulars,
							$newcourse_email,
							$newcourse_category,
							$newcourse_label,
							$newcourse_language , 
							$_uid, 
							$expirationDate);
							
			$displayCourseAddResult = TRUE;
			$displayCoursePropertiesForm = FALSE;
			$displayWhatAdd = FALSE;
	
		    // warn platform administrator of the course creation
			$strCreationMailNotificationSubject = 		    '['.$siteName.'] '.$langCreationMailNotificationSubject.' : '.$newcourse_label;
			$strCreationMailNotificationBody = 
		    claro_format_locale_date($dateTimeFormatLong)."\n"
		    .$langCreationMailNotificationBody.' '.$siteName.' '
		    .$langByUser.$_user['firstName'].' '.$_user['lastName']." (".$_user['mail'].") \n"
		    .' '.$langCode			.' : '.$currentCourseCode."\n"
		    .' '.$langTitle			.' : '.$newcourse_label."\n"
		    .' '.$langProfessors	.' : '.$newcourse_titulars."\n"
		    .' '.$langEmail			.' : '.$newcourse_email."\n\n"
		    .' '.$langFac.' : '.$newcourse_category."\n"
		    .' '.$langLn.' : '.$newcourse_language."\n"
		    ."\n ".$coursesRepositoryWeb.$currentCourseRepository."/\n\n";
		    if (    
					!@mail(	$administrator["email"], 
							$strCreationMailNotificationSubject ,
							$strCreationMailNotificationBody ))
			{
				//find here another notification system
			}
		} // if ($okToCreate)
	} // elseif ($submitFromCoursProperties)
} // else (!$can_create_courses)
if ($fromAdmin=="yes")
{
    $interbredcrump[] = array ("url"=>$rootAdminWeb, "name"=> $langAdministrationTools);
}
include($includePath."/claro_init_header.inc.php");
claro_disp_tool_title($nameTools);
claro_disp_msg_arr($controlMsg);

// db connect
// path for breadcrumb contextual menu in this page
$chemin="<a href=../../index.php>$siteName</a>&nbsp;&gt;&nbsp;<b>$langCreateSite</b>";
###################### FORM  #########################################

if($displayNotForU)
{
	echo $langNotAllowed;
} 
elseif($displayWhatAdd)
{
?>
<form lang="<?php echo $iso639_2_code ?>" class="forms" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" accept-charset="<?php echo $charset ?>">
<table  width="100%">
	<tr valign="top">
		<td colspan="2" valign="top">
			<H5>
			<?php echo $langAddNewCourse ?>
			</H5>
			<br>
		</td>
	</tr>
	<tr valign="top">
		<td width="40"></td>
		<td >
			<input type="radio" name="whatAdd" value="newCourse" checked id="whatAdd_newCourse">
			<label for="whatAdd_newCourse"><?php echo $langNewCourse ?></label>
		</td>
	</tr>
	<tr valign="top">
		<td width="40"></td>
		<td >
			<input type="radio" name="whatAdd" value="archive"  id="whatAdd_archive">
			<label for="whatAdd_archive"><?php echo $langRestoreACourse ?></label>
		</td>
	</tr>
	<tr valign="top">
		<td width="40"></td>
		<td valign="top">
			<br><br>
			<input type="submit" name="fromWhatAdd" value="Next">
		</td>
	</tr>
</table>
</form>
<?php
}
elseif($displayCourseRestore)
{
?>
<br>
<form  class="forms" action="<?php echo $PHP_SELF; ?>" method="post" enctype="multipart/form-data">
<table width="100%">
	<tr valign="top">
		<td colspan="2" valign="top">
			<H5>
				<?php echo $langChoseFile ?>
			</H5>
			<br>
		</td>
	</tr>
<?php
	if ($sendByUploadAivailable)
	{
?>
	<tr valign="top">
		<TD >
			<input type="radio" name="typeStorage" value="upload" checked  id="typeStorage_upload">&nbsp;
			<label for="typeStorage_upload">Upload</label>
		</TD>
		<td >
			<INPUT TYPE="hidden" name="MAX_FILE_SIZE" value="<?php echo MAX_FILE_SIZE_UPLOAD ?>">
			<input type="file" name="postFile" accept="application/x-zip-compressed">
			<DIV class="formsTips">
				<?php echo $langPostFileTips; ?>
			</DIV>
		</td>
	</tr>
<?php
	}
	if ($sendByHTTPAivailable)
	{
?>
	<tr valign="top">
		<TD >
			<input type="radio" name="typeStorage" value="http" id="typeStorage_http" >&nbsp;
			<label for="typeStorage_http">Http</label>
		</TD>
		<td >
			<input type="text" name="httpFile" >
			<DIV class="formsTips">
				<?php echo $langHttpFileTips; ?>
			</DIV>
		</td>
	</tr>
<?php
	}
	if ($sendByFTPAivailable )
	{
?>
	<tr valign="top">
		<TD >
			<input type="radio" name="typeStorage" value="ftp" id="typeStorage_ftp" >&nbsp;
			<label for="typeStorage_ftp">Ftp</label>
		</TD>
		<td >
			<input type="text" name="ftpFile" >
			<DIV class="formsTips">
				<?php echo $langFtpFileTips; ?>
			</DIV>
		</td>
	</tr>
<?php
	}
	if ($sendByLocaleAivailable)
	{
?>
	<tr valign="top">
		<TD>
			<input type="radio" name="typeStorage" value="local" id="typeStorage_local">&nbsp;
			<label for="typeStorage_local">On server</label>
		</TD>
		<td >
			<input type="text" name="localFile" >
			<DIV class="formsTips">
				<?php echo $langLocalFileTips; ?>
			</DIV>
		</td>
	</tr>
<?php
	}
?>
	<tr valign="top">
		<TD >
		</TD>
		<td valign="top">
			<br><br>
			<input type="submit" name="selectArchive" value="Next">
		</td>
	</tr>
</table>
</form>
<?php
}
elseif($displayCoursePropertiesForm)
{
?>
<b><?php echo $langFieldsRequ ?></b>
<form lang="<?php echo $iso639_2_code ?>" action="<?php echo $PHP_SELF; ?>" method="post" accept-charset="<?php echo $charset ?>">
<table>
<tr valign="top">
<td colspan="2">

</td>
</tr>

<tr valign="top">
<td align="right">
<label for="intitule"><?php echo $langTitle ?></label> :
</td>
<td valign="top">
<input type="Text" name="intitule" id="intitule" size="60" value="<?php echo $valueIntitule ?>">
<br><small><?php echo $langEx ?></small>
<input type="hidden" name="fromAdmin" size="60" value="<?php echo $fromAdmin ?>">
</td>
</tr>

<tr valign="top">
<td align="right">
<label for="faculte"><?php echo $langFac ?></label> : 
</td>
<td>
<?php
BuildEditableCatTable(""," &gt; ");      
?>
<br><small><?php echo $langTargetFac ?></small>
</td>
</tr>

<tr valign="top">
<td align="right">
	<label for="wantedCode"><?php echo $langCode ?></label> : 
</td>
<td >
	<input type="Text" id="wantedCode" name="wantedCode" maxlength="12" value="<?php echo $valuePublicCode ?>">
	<br>
	<small><?php echo $langMaxSizeCourseCode ?></small>
</td>
</tr>

<tr valign="top">
<td align="right">
<label for="titulaires"><?php echo $langProfessors ?></label> :
</td>
<td>
<input type="Text" name="titulaires" id="titulaires" size="60" value="<?php echo $valueTitular ?>">
</td>
</tr>

<tr>
<td align="right">
<label for="email"><?echo $langEmail ?></label>&nbsp;:
</td>
<td>
<input type="text" name="email" id="email" value="<?php echo $valueEmail; ?>" size="30" maxlength="255">
</td>
</tr>

<tr valign="top">
<td align="right">
<label for="languageCourse"><?php echo $langLn ?></label>:
</td>
<td>
<select name="languageCourse" id="languageCourse">";
<?php
	$dirname = "../lang/";
	if($dirname[strlen($dirname)-1]!='/')
		$dirname.='/';
	$handle=opendir($dirname);
	while ($entries = readdir($handle))
	{
		if ($entries=='.'||$entries=='..'||$entries=='CVS')
			continue;
		if (is_dir($dirname.$entries))
		{
			echo "<option value=\"".$entries."\"";
			if ($entries == $valueLanguage) echo " selected ";
			echo ">"; 
					if (!empty($langNameOfLang[$entries]) && $langNameOfLang[$entries]!="" && $langNameOfLang[$entries]!=$entries)
					echo $langNameOfLang[$entries]." - ";
			echo $entries,"</option>\n";
		}
	}	
	closedir($handle);
?>
</select>
</td>
</tr>
<tr valign="top">
<td>
</td>
<td>
<input type="Submit" name="submitFromCoursProperties" value="<?php echo $langOk?>">
</td>
</tr>
</table>
</form>
<p><?php echo $langExplanation ?>.</p>

<?php
		if($showLinkToRestoreCourse)
		{
			if($is_platformAdmin)
			{
?>

<hr noshade size="1">
<a href="../course_info/restore_course.php"><?php echo $langRestoreCourse; ?></a>

<?php

			}
		}
/*
	$valueCode			= $courseProperties["officialCode"];
	$valueIntitule		= $courseProperties["name"];
	$valueFacultyName	= $courseProperties["categoryName"];
	$valueFacultyCode	= $courseProperties["categoryCode"];
	$valueLanguage 		= $courseProperties["language"];
	$valueAdminCode		= $courseProperties["adminCode"];
	$valueDbName		= $courseProperties["dbName"];
	$valuePath			= $courseProperties["path"];
	$valueRegAllowed 	= $courseProperties["registrationAllowed"];
*/
	if ($showPropertiesFromArchive)
	{
?>
<table class="forms" width="100%">
	<tr valign="top">
		<td colspan="2" valign="top">
				<b>
					<?php echo $langOtherProperties ?>
				</b>
		</td>
	</tr>
	<tr>
		<td>
			<?php echo $langSysId ?>
		</td>
		<td>
			<?php echo $valueSysId ?><br>
			<?php echo $valueAdminCode?><br>
			<?php echo $valueDbName?><br>
			<?php echo $valuePath?>
		</td>
	</tr>

	<tr>
		<td>
			<?php echo $langFaculty ?>
		</td>
		<td>
			[<?php echo $valueFacultyCode ?>]<?php echo $valueFacultyName ?>
		</td>
	</tr>
	<tr>
		<td>
			<?php echo $langDescription ?>
		</td>
		<td>
			<?php echo $valueDescription ?>
		</td>
	</tr>
	<tr>
		<td>
			<?php echo $langDepartment	 ?>
		</td>
		<td>
			<?php echo $valueDepartment ?>
		</td>
	</tr>
	<tr>
		<td>
			<?php echo $langDepartmentUrl	 ?>
		</td>
		<td>
			<?php echo $valueDepartmentUrl ?>
		</td>
	</tr>
	<tr>
		<td>
			<?php echo $langScoreShow ?>
		</td>
		<td>
			<?php echo $valueScoreShow ?>
		</td>
	</tr>
	<tr>
		<td>
			<?php echo $langVisibility ?>
		</td>
		<td>
			<?php echo $valueVisibility ?>
		</td>
	</tr>
	<tr>
		<td>
			<?php echo $langregistration ?>
		</td>
		<td>
			<?php echo $valueRegAllowed ?>
		</td>
	</tr>
	<tr>
		<td>
			<?php echo $langVersionDb ?>
		</td>
		<td>
			<?php echo $valueVersionDb ?>
		</td>
	</tr>
	<tr>
		<td>
			<?php echo $langVersionClaro ?>
		</td>
		<td>
			<?php echo $valueVersionClaro ?>
		</td>
	</tr>
	<tr>
		<td>
			<?php echo $langLastVisit ?>
		</td>
		<td>
			<?php echo ucfirst(claro_format_locale_date($dateTimeFormatLong,strtotime($valueLastVisit))) ?>
		</td>
	</tr>
	<tr>
		<td>
			<?php echo $langLastEdit ?>
		</td>
		<td>
			<?php echo ucfirst(claro_format_locale_date($dateTimeFormatLong,strtotime($valueLastEdit))) ?>
		</td>
	</tr>
	<tr>
		<td>
			<?php echo $langExpire ?>
		</td>
		<td>
			<?php echo $valueExpire ?>
		</td>
	</tr>
<?
	}
?>
</table>
<?
}   // IF ! SUBMIT

#################SORT THE FORM ####################
# 1. CHECK IF DIRECTORY/COURSE_CODE ALREADY TAKEN #
#### CREATE THE COURSE AND THE DATABASE OF IT #####
elseif($displayCourseAddResult)
{
// Replace HTML special chars by equivalent - cannot use html_specialchars
// Special for french
?>
	<tr bgcolor="<?php echo $colorMedium ?>">
		<td colspan="3">
<?php
                 echo $langJustCreated." <strong>".$currentCourseCode."</strong><br>"; 
                 if ($_POST['fromAdmin']!="yes")
                 {
                    claro_disp_button("../../index.php",$langEnter);
                 }
                 else
                 {
                    claro_disp_button("add_course.php?fromAdmin=yes",$langAnotherCreateSite);
                    claro_disp_button("../admin/index.php",$langBackToAdmin);
                 }?>
		</td>
	</tr>
<?php
} // if all fields fulfilled


include($includePath."/claro_init_footer.inc.php");
?>
