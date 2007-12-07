<?php // $Id$
/**
 * CLAROLINE 
 *
 * COURSE SITE CREATION TOOL
 *
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
 * List of Events
 * 	- can't create course
 * 		show displayNotForU and exit
 * 
 * List  of  views
 * 	- displayNotForU
 * 		the  user  is not allowed to  use this script
 * 	- displayCoursePropertiesForm
 * 		User  can enter/edit  parameter  for the  new  course. If  they use an archive,
 * 		value are proposed but can be edited
 * 	- displayCourseAddResult
 * 		New course is added.  Show  success message.
 *
 * @version 1.6 $Revision$
 * 
 * @copyright (c) 2001-2005 Universite catholique de Louvain (UCL)
 * 
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE 
 *
 * @see http://www.claroline.net/wiki/config_def/
 *
 * @package COURSE
 *
 * @author Claro Team <cvs@claroline.net>
 * @author Christophe Gesché <moosh@claroline.net>
 *
 */
require '../inc/claro_init_global.inc.php';

//// Config tool
include($includePath.'/conf/course_main.conf.php');
//// LIBS

include($includePath.'/lib/add_course.lib.inc.php');
include($includePath.'/lib/course.lib.inc.php');
include($includePath.'/lib/fileManage.lib.php');

$nameTools = $langCreateSite;

/**
 * DB tables definition
 */

$tbl_cdb_names = claro_sql_get_course_tbl();
$tbl_mdb_names = claro_sql_get_main_tbl();

$tbl_course          = $tbl_mdb_names['course'           ];
$tbl_rel_course_user = $tbl_mdb_names['rel_course_user'  ];
$tbl_category        = $tbl_mdb_names['category'         ];
$tbl_user            = $tbl_mdb_names['user'             ];
$tbl_announcement    = $tbl_cdb_names['announcement'     ];

$TABLECOURSE        = $tbl_course;
$TABLECOURSUSER     = $tbl_rel_course_user;
$TABLECOURSDOMAIN   = $tbl_category;
$TABLEUSER          = $tbl_user;
$TABLEANNOUNCEMENTS = $tbl_announcement;

$can_create_courses = (bool) ($is_allowedCreateCourse);
$coursesRepositories = $coursesRepositorySys;

//Prefield values for the form to create a course :

//1- first we take default value (or blank values)
$wantedCode         = $_REQUEST['wantedCode'];
$valueIntitule      = $_REQUEST['intitule'];
$valueLanguage      = $_REQUEST['languageCourse'];
$valueTitular       = $_user['firstName']." ".$_user['lastName'];
$valueEmail         = $_user['mail'];
$valueLanguage      = $platformLanguage;

//2- then we overwrite with entered value if the form was already posted before
if (isset($_REQUEST['titulaires'])) 
{
    $valueTitular = $_REQUEST['titulaires'] ; 
} 
if (isset($_REQUEST['email']))      
{
    $valueEmail = $_REQUEST['email'];
}
if (isset($_REQUEST['languageCourse']))
{
    $valueLanguage = $_REQUEST['languageCourse'];
}
if (isset($_REQUEST['faculte']))
{
    $facu = $_REQUEST['faculte'];
}

//// Starting script
$displayNotForU = FALSE;
if (!$can_create_courses) $displayNotForU = TRUE; // (!$can_create_courses)
else
{
    $displayCoursePropertiesForm 	= TRUE;
    if ($submitFromCoursProperties)
	{
		$wantedCode 		= strip_tags($_REQUEST['wantedCode'    ]);
		$newcourse_category	= strip_tags($_REQUEST['faculte'       ]);
		$newcourse_label	= strip_tags($_REQUEST['intitule'      ]);
		$newcourse_language = strip_tags($_REQUEST['languageCourse']);
		$newcourse_titulars	= strip_tags($_REQUEST['titulaires'    ]);
		$newcourse_email 	= strip_tags($_REQUEST['email'         ]);
		
		//$wantedCode = $wantedCode.' '.date('Y');
		
		$okToCreate = TRUE;
		/////CHECK DATA
		
		// LABEL (Previously called intitule
		if ($human_label_needed && empty($newcourse_label)) 
		{
			$okToCreate = FALSE;
			$controlMsg['error'][] = $langLabelCanBeEmpty;
		}
		
		if ($human_code_needed && empty($wantedCode)) 
		{
			$okToCreate = FALSE;
			$controlMsg['error'][] = $langCodeCanBeEmpty;
		}
		
		if ($course_email_needed && empty($newcourse_email)) 
		{
			$okToCreate = FALSE;
			$controlMsg['error'][] = $langEmailCanBeEmpty;
		}

		// if an email is given It would be correct
		$regexp = "^[0-9a-z_\.-]+@(([0-9]{1,3}\.){3}[0-9]{1,3}|([0-9a-z][0-9a-z-]*[0-9a-z]\.)+[a-z]{2,4})$";
		if (!empty($newcourse_email)&&!eregi( $regexp, $newcourse_email)) 
		{
			$okToCreate = FALSE;
			$controlMsg['error'][] = $langEmailWrong;
		}

	//  function define_course_keys ($wantedCode, $prefix4all="", $prefix4baseName="", 	$prefix4path="", $addUniquePrefix =false,	$useCodeInDepedentKeys = TRUE	)
		$keys = define_course_keys ($wantedCode,"",$dbNamePrefix);
		$currentCourseCode		 = $keys['currentCourseCode'      ];
		$currentCourseId		 = $keys['currentCourseId'        ];
		$currentCourseDbName	 = $keys['currentCourseDbName'    ];
		$currentCourseRepository = $keys['currentCourseRepository'];
		$expirationDate 		= 	time() + $firstExpirationDelay;
	
		if ($okToCreate)
		{
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

			$displayCourseAddResult       = TRUE;
			$displayCoursePropertiesForm  = FALSE;
			$displayWhatAdd               = FALSE;

		    // warn platform administrator of the course creation
			$strCreationMailNotificationSubject = 		    '['.$siteName.'] '.$langCreationMailNotificationSubject.' : '.$newcourse_label;
			$strCreationMailNotificationBody = 
		    claro_disp_localised_date($dateTimeFormatLong)."\n"
		    .$langCreationMailNotificationBody.' '.$siteName.' '
		    .$langByUser.$_user['firstName'].' '.$_user['lastName']." (".$_user['mail'].") \n"
		    .' '.$langCode			.' : '.$currentCourseCode."\n"
		    .' '.$langCourseTitle	.' : '.$newcourse_label."\n"
		    .' '.$langProfessors	.' : '.$newcourse_titulars."\n"
		    .' '.$langEmail			.' : '.$newcourse_email."\n\n"
		    .' '.$langCategory.' : '.$newcourse_category."\n"
		    .' '.$langLanguage.' : '.$newcourse_language."\n"
		    ."\n ".$coursesRepositoryWeb.$currentCourseRepository."/\n\n";
		    if (    
					!@mail(	$administrator_email, 
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
    $interbredcrump[] = array ("url"=>$rootAdminWeb, "name"=> $langAdministration);
}
include $includePath.'/claro_init_header.inc.php';

claro_disp_tool_title($nameTools);

claro_disp_msg_arr($controlMsg);

// db connect
// path for breadcrumb contextual menu in this page
$chemin='<a href="../../index.php>'.$siteName.'</a>&nbsp;&gt;&nbsp;<b>'.$langCreateSite.'</b>';

if($displayNotForU)
{
	echo $langNotAllowed;
} 
elseif($displayCoursePropertiesForm)
{
?>
<b><?php echo $langFieldsRequ ?></b>
<form lang="<?php echo $iso639_2_code ?>" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" accept-charset="<?php echo $charset ?>">
<table>
<tr valign="top">
<td colspan="2">

</td>
</tr>

<tr valign="top">
<td align="right">
<label for="intitule"><?php echo $langCourseTitle ?></label> :
</td>
<td valign="top">
<input type="Text" name="intitule" id="intitule" size="60" value="<?php echo htmlspecialchars($valueIntitule) ?>">
<br><small><?php echo $langEx ?></small>
<input type="hidden" name="fromAdmin" size="60" value="<?php echo $fromAdmin ?>">
</td>
</tr>

<tr valign="top">
<td align="right">
	<label for="wantedCode"><?php echo $langCode ?></label> : 
</td>
<td >
	<input type="Text" id="wantedCode" name="wantedCode" maxlength="12" value="<?php echo $wantedCode ?>">
	<br>
	<small><?php echo $langMaxSizeCourseCode ?></small>
</td>
</tr>

<tr valign="top">
<td align="right">
<label for="titulaires"><?php echo $langProfessors ?></label> :
</td>
<td>
<input type="Text" name="titulaires" id="titulaires" size="60" value="<?php echo htmlspecialchars($valueTitular) ?>">
</td>
</tr>

<tr>
<td align="right">
<label for="email"><?php echo $langEmail ?></label>&nbsp;:
</td>
<td>
<input type="text" name="email" id="email" value="<?php echo $valueEmail; ?>" size="30" maxlength="255">
</td>
</tr>

<tr valign="top">
<td align="right">
<label for="faculte"><?php echo $langCategory ?></label> : 
</td>
<td>
<?php
BuildEditableCatTable($facu," &gt; ");      
?>
<br><small><?php echo $langTargetFac ?></small>
</td>
</tr>

<tr valign="top">
<td align="right">
<label for="languageCourse"><?php echo $langLanguage ?></label> :
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
			echo '<option value="'.$entries.'"';
			if ($entries == $valueLanguage) echo ' selected ';
			echo ">"; 
					if (!empty($langNameOfLang[$entries]) && $langNameOfLang[$entries]!="" && $langNameOfLang[$entries]!=$entries)
					echo $langNameOfLang[$entries].' - ';
			echo $entries
               . '</option>'."\n"
               ;
		}
	}	
	closedir($handle);
?>
</select>
</td>
</tr>
<tr valign="top">
<td align="right">
<label for="submitFromCoursProperties"><?php echo $langCreate ?> : </label>
</td>
<td>
<input type="Submit" name="submitFromCoursProperties" id ="submitFromCoursProperties" value="<?php echo $langOk?>"> 
<?php claro_disp_button($_SERVER['HTTP_REFERER'], $langCancel); ?>
</td>
</tr>
</table>
</form>
<p><?php echo $langExplanation ?>.</p>
</table>
<?php
}   // IF ! SUBMIT

#################SORT THE FORM ####################
# 1. CHECK IF DIRECTORY/COURSE_CODE ALREADY TAKEN #
#### CREATE THE COURSE AND THE DATABASE OF IT #####
elseif($displayCourseAddResult)
{
// Replace HTML special chars by equivalent - cannot use html_specialchars
// Special for french

  $dialogBox = "\n"
       . $langJustCreated
       . ' : ' 
       . '<strong>'
       . $currentCourseCode
       . '</strong>';
       
  if($dialogBox)
  {
      claro_disp_message_box($dialogBox);
      echo "<br />";
  }
       
	if ($_REQUEST['fromAdmin']!="yes")
	{
		echo '<a class="claroCmd" href="../../index.php">'.$langBackToMyCourseList.'</a>'; 
	}
	else
	{
		echo '<a class="claroCmd" href="add_course.php?fromAdmin=yes">'.$langAnotherCreateSite.'</a> | '
		   . '<a class="claroCmd" href="../admin/index.php">'.$langBackToAdmin.'</a>';
	}


} // if all fields fulfilled

include($includePath."/claro_init_footer.inc.php");
?>
