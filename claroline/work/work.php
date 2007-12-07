<?php // $Id$

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


/* STUDENT WORK UPLOADER AND DOWNLOADER
 *
 * GOALS
 * *****
 * Allow student to send quickly documents immediately
 * visible on course website.
 *
 * The script makes 5 things:
 *
 * 	1. Upload documents
 * 	2. Give them a name
 * 	3. Modify data about documents
 * 	4. Delete link to documents and simultaneously remove them
 * 	5. Show documents list to students and visitors
 *
 * On the long run, the idea is to allow sending realvideo . Which means only
 * establish a correspondence between RealServer Content Path and the user's
 * documents path.
 *
 * All documents are sent to the address /$rootSys/$currentCourseID/document/
 * where $currentCourseID is the web directory for the course and $rootSys usually /var/www/html
 */

$langFile = "work";

$tlabelReq = "CLWRK___";
require '../inc/claro_init_global.inc.php';

include($includePath.'/lib/events.lib.inc.php');
include($includePath.'/conf/work.conf.inc.php');

/*
 * Connection Bloc
 */

$tbl_work                   = $_course['dbNameGlu'].'assignment_doc';
$is_allowedToEdit           = $is_courseAdmin;

$currentCourseRepositorySys = $coursesRepositorySys.$_course["path"]."/";
$currentCourseRepositoryWeb = $coursesRepositoryWeb.$_course["path"]."/";

$currentUserFirstName       = $_user['firstName'];
$currentUserLastName        = $_user['lastName'];

$fileAllowedSize = CONFVAL_MAX_FILE_SIZE_PER_WORKS ;    //file size in bytes
$updir           = $currentCourseRepositorySys.'work/'; //directory path to upload

//////////////////////////////////////////////////////////////////////////////

include($includePath."/lib/fileUpload.lib.php");
include($includePath."/lib/fileDisplay.lib.php"); // need format_url function




$nameTools = $langWorks;

if (isset($intro_cmdEdit)) $noQUERY_STRING=true; // intro_cmdEdit use GET - > noQUERY_STRING
include($includePath.'/claro_init_header.inc.php');

if ( !$_cid )	claro_disp_select_course();
if ( ! $is_courseAllowed)	claro_disp_auth_form();

event_access_tool($nameTools);

claro_disp_tool_title($nameTools);


/*========================================
           INTRODUCTION SECTION
  ========================================*/

$moduleId = $course_tool['id']; // Id of the Student Paper introduction Area
$langHelpAddIntroText=$langIntroWork;
include($includePath."/introductionSection.inc.php");


/*====================================================
  COMMANDS SECTION (reserved to course administrator)
  ===================================================*/

// handle parameters
$titre       = claro_strip_tags( trim ($_REQUEST['titre'] ) );
$auteurs     = claro_strip_tags( trim ($_REQUEST['auteurs'] ) );
$description = claro_strip_tags( trim ($_REQUEST['description'] ) );

if ($is_allowedToEdit)
{
/*-------------------------------------------
            DELETE WORK COMMAND
  -----------------------------------------*/
	if ($_REQUEST['delete'])
	{
		if ($_REQUEST['delete'] == "all")
		{
			$queryString1 = "SELECT url FROM `".$tbl_work."`";
			$queryString2 = "DELETE FROM  `".$tbl_work."`";
		}
		else
		{
			$delete = (int) $_REQUEST['delete'];
			$queryString1 = "SELECT url FROM  `".$tbl_work."`  WHERE id = '".$delete."'";
			$queryString2 = "DELETE FROM  `".$tbl_work."`  WHERE id='".$delete."'";
		}

		$result1 = claro_sql_query($queryString1);

		if ($result1)
		{
			while ($thisWork = mysql_fetch_array($result1))
			{
				// check the url realy points to a file in the work area
				// (some work links can come from groups area...)

				if (substr (dirname($thisWork['url']), -4) == "work")
				{
					@unlink($currentCourseRepositorySys.$thisWork['url']);
				}
			}
		}

		$result2 = claro_sql_query($queryString2);

	}

	/*-------------------------------------------
	           EDIT COMMAND WORK COMMAND
	  -----------------------------------------*/

	if ($_REQUEST['edit'])
	{
		$edit = (int) $_REQUEST['edit'];
		$workData = claro_get_work_data($edit);

		$workTitle       = $workData ['titre'      ];
		$workAuthor      = $workData ['auteurs'    ];
		$workDescription = $workData ['description'];
		$workUrl         = $workData ['url'        ];

	}



	/*-------------------------------------------
		MAKE INVISIBLE WORK COMMAND
	  -----------------------------------------*/
	
	if ($_REQUEST['mkInvisbl'])
	{
		if ($_REQUEST['mkInvisbl'] == "all")
		{
			$sql = "ALTER TABLE `".$tbl_work."` 
			        CHANGE `accepted` `accepted` TINYINT(1) DEFAULT '0'";

			claro_sql_query($sql);

			$sql = "UPDATE  `".$tbl_work."` 
			        SET accepted = 0";

			claro_sql_query($sql);
		}
		else
		{
			$mkInvisbl = (int) $_REQUEST['mkInvisbl'];

			$sql = "UPDATE  `".$tbl_work."`  
			        SET accepted = 0 
					WHERE id = \"".$mkInvisbl."\"";

			claro_sql_query ($sql);
		}
	}



	/*-------------------------------------------
		MAKE VISIBLE WORK COMMAND
	  -----------------------------------------*/
	if ($_REQUEST['mkVisbl'])
	{
		if ($_REQUEST['mkVisbl'] == "all")
		{
			$sql = "ALTER TABLE  `".$tbl_work."`  
			        CHANGE `accepted` `accepted` TINYINT(1) DEFAULT '1'";

			claro_sql_query($sql);

			$sql = "UPDATE  `".$tbl_work."`
			        SET accepted = 1";

			claro_sql_query($sql);

		}
		else
		{
			$mkVisbl = (int) $_REQUEST['mkVisbl'];
			$sql = "UPDATE  `".$tbl_work."`  
			        SET accepted = 1 
					WHERE id = \"".$mkVisbl."\"";

			claro_sql_query ($sql);
		}
	}

} // end if ($is_allowedToEdit)



/*=====================================
          FORM SUBMIT PROCEDURE
  =====================================*/

if ( isset($_POST['submitWork']) )
{
	if ( is_uploaded_file($_FILES['file']['tmp_name']) )
	{

        if ($_FILES['file']['size'] > $fileAllowedSize)
        {
            $message .= "$langTooBig";
			$succeed = false; 
        }
        else
        {
    		// Try to add an extension to the file if it has'nt one
	    	$new_file_name = add_ext_on_mime($_FILES['file']['name']);

    		// Replace dangerous characters
    		$new_file_name = replace_dangerous_char($new_file_name);

    		// Transform any .php file in .phps fo security
    		$new_file_name = php2phps($new_file_name);

			if( ! $titre || $titre == "" )
				$titre = $file_name;
			
			if ( ! $auteurs || $auteurs == "")
				$auteurs = $currentUserFirstName." ".$currentUserLastName;
						
			// compose a unique file name to avoid any conflict

			$new_file_name = uniqid('').$new_file_name;

			@copy($_FILES['file']['tmp_name'], $updir.$new_file_name)
				or die("<tr><td colspan=\"2\">error : ".$langNotPossible."</td></tr>\n</table>");

			$url = "work/".$new_file_name;

			$sqlAddWork = "INSERT INTO `".$tbl_work."`
			               SET url         = '".$url."',
						       titre       = '".claro_addslashes($titre)."',
			                   description = '".claro_addslashes($description)."',
			                   auteurs     = '".claro_addslashes($auteurs)."'";

			claro_sql_query($sqlAddWork);
                        
            $insertId = mysql_insert_id();
			$succeed = true;
        }
	}

	/*
	 * SPECIAL CASE ! For a work comming from another area (ie groups)
	 */

	elseif ($newWorkUrl)
	{

		$url = str_replace('../../'.$_course['path'].'/','',$newWorkUrl);


		if( ! $titre )
		{
			$titre = basename($workUrl);
		}
		
		$sql = "INSERT INTO  `".$tbl_work."`
		        SET url         = \"".$url."\", 
		            titre       = '".claro_addslashes($titre)."', 
		            description = '".claro_addslashes($description)."',
		            auteurs     = '".claro_addslashes($auteurs)."'";

		claro_sql_query($sql);

		$insertId = mysql_insert_id();
		$succeed = true;
	}

	/*
	 * SPECIAL CASE ! For a work edited
	 */

	elseif ($_REQUEST['id'] && $is_allowedToEdit)
	{
		$work_id = (int) $_REQUEST['id'];
		$sql = "UPDATE  `".$tbl_work."`  
		        SET	titre       = '".$titre."',
		            description = '".$description."',
		            auteurs     = '".$auteurs."'
		        WHERE id        = '".$work_id."'";

		claro_sql_query($sql);

        $insertId = $work_id;
		$succeed = true;
	}
    else
    {
        $message .= "$langTooBig";
        $succeed = false;
    }
}
if ($submitWork && $succeed)
{
    //stats
    event_upload($insertId);

	$message  =	$langDocAdd;
    $message .= "<p><u>". $langTitleWork ."</u> : " . $titre . "<br>
				<u>". $langAuthors ."</u> : " . $auteurs . "<br>
				<u>". $langDescription ."</u> : " . $description . "<br>
                </p>";

    claro_disp_message_box($message);

    echo "<p><a href=\"".$_SERVER['PHP_SELF']."\"> << ".$langBackAssignement."</a></p>\n";
}
else
{

	/*=======================================
		 PERMANENT FORM TO UPLOAD PAPER
	  =======================================*/

    if ($message) 
    {
        claro_disp_message_box($message);
    }

	echo	"<br>\n<form method=\"post\" action=\"",$_SERVER['PHP_SELF'],"\" enctype=\"multipart/form-data\" >\n",
			"<table>\n";
	if ($submitGroupWorkUrl) // For user comming from group space to publish his work
	{
		echo	"<tr>\n",

				"<td align=\"right\">".
				"<input type=\"hidden\" name=\"newWorkUrl\" value=\"".$submitGroupWorkUrl."\">".
				$langDocument.
				" : ".
				"</td>\n".
				"<td align=\"left\">".
				"<a href=\"".$coursesRepositoryWeb.$_course['path'].'/'.$submitGroupWorkUrl."\">".basename($submitGroupWorkUrl)."</a>".
				"</td>\n".

				"</tr>\n";
	}
	elseif ($edit && $is_allowedToEdit)
	{
 		$workUrl = $currentCourseRepositoryWeb.$workUrl;

		echo	"<tr>\n",

				"<td>",
				"<input type=\"hidden\" name=\"id\" value=\"",$edit,"\">\n",
				$langDocument," : ",
				"</td>\n",

				"<td>",
				"<a href=\"",$workUrl,"\">",$workUrl,"</a>",
				"</td>\n",

				"</tr>\n";
	}
	else // else standart upload option
	{
		echo	"<tr>\n",

				"<td  align=\"right\"><label for=\"file\">",
				$langDownloadFile,"</label> :",
				"</td>\n",

				"<td>",
				"<input type=\"file\" name=\"file\" id=\"file\" size=\"20\">",
				"</td>\n",

				"</tr>\n".
			"<tr>
				<td>&nbsp;</td>
				<td><small>".$langMaxFileSize.format_file_size( get_max_upload_size($fileAllowedSize,$updir) )."</small></td>
			</tr>";
	}
	echo
			"<tr>",
			"<td  align=\"right\"><label for=\"title\">",
			$langTitleWork,"</label> : ",
			"</td>\n",

			"<td>",
			"<input type=\"text\" name=\"titre\" id=\"title\" value=\"",$workTitle,"\" size=\"30\">",
			"</td>\n",

			"</tr>\n",

			"<tr>\n",

			"<td valign=\"top\"  align=\"right\"><label for=\"auteurs\">",
			$langAuthors."</label> : ",
			"</td>\n",

			"<td>",
			"<input type=\"text\" name=\"auteurs\" id=\"auteurs\" value=\"",$workAuthor,"\" size=\"30\">\n",
			"</td>\n",

			"</tr>\n",

			"<tr>\n",

			"<td valign=\"top\"  align=\"right\"><label for=\"description\">",
			$langDescription," : ",
			"</label></td>\n",

			"<td>",
			"<textarea name=\"description\"  id=\"description\" cols=\"30\" rows=\"3\">",
			$workDescription,
			"</textarea>",
			"<input type=\"hidden\" name=\"active\"   value=\"1\">",
			"<input type=\"hidden\" name=\"accepted\" value=\"1\">",
			"</td>\n",

			"</tr>\n",

			"<tr>\n",

			"<td></td>",

			"<td>",
			"<input type=\"Submit\" name=\"submitWork\" value=\"".$langOk."\">",
			"</td>\n",

			"</tr>\n",

			"</table>\n",

			"</form>\n",

			"<p>&nbsp;</p>";

/*> > > > > >   ALL FILES DELETE & VISIBILITY COMMANDS < < < < < < < < < */

echo	"<table cellpadding=\"5\" cellspacing=\"2\" border=\"0\">\n";

if ($is_allowedToEdit)
{

echo	"<tr>\n".
		"<td colspan=\"2\">\n".

		"<table cellspacing=\"0\" cellpadding=\"5\" border=\"1\" bordercolor\"gray\">\n".
		"<tr>\n".
		"<td>".
		"<small>".$langAllFiles." : </small>".
		"<a href=\"".$_SERVER['PHP_SELF']."?delete=all\" ".
		"onclick=\"javascript:if(!confirm('".addslashes(htmlspecialchars($langDelete." ".$langConfirmYourChoice))."')) return false;\">".
		"<img src=\"".$clarolineRepositoryWeb."img/delete.gif\" border=\"0\" alt=\"".$langDelete."\" align=\"absmiddle\">".
		"</a>".
		"&nbsp;";

	$result = claro_sql_query("SHOW COLUMNS FROM `".$tbl_work."` LIKE 'accepted'") or die("PROBLEM");

	if ($result)
	{
		$columnStatus = mysql_fetch_array($result);

		if ($columnStatus['Default'] == 1)
		{
			echo	"<a href=\"".$_SERVER['PHP_SELF']."?mkInvisbl=all\">",
					"<img src=\"".$clarolineRepositoryWeb."img/visible.gif\" border=\"0\" alt=\"".$lang_make_invisible."\" align=\"absmiddle\">",
					"</a>\n";
		}
		else
		{
			echo	"<a href=\"".$_SERVER['PHP_SELF']."?mkVisbl=all\">",
					"<img src=\"".$clarolineRepositoryWeb."img/invisible.gif\" border=\"0\" alt=\"".$lang_make_visible."\" align=\"absmiddle\">",
					"</a>\n";
		}
	}
?>
</td>
</tr>
</table>

</td>
</tr>
<?php
}

/*========================================
    DISPLAY STUDENT PAPERS LIST
  ========================================*/

	if( ! $id) 
	{
		/*  print the list if there is no editing  */

		$sqlListWorks = "SELECT id, url, titre, description, auteurs, active, accepted
		                 FROM  `".$tbl_work."`
		                 ORDER BY id";

		$works = claro_sql_query_fetch_all($sqlListWorks);

//		while ($myrow = mysql_fetch_array($result))
		foreach($works as $work)
		{
			// convert the file name in a correct url

			$url = implode("/", array_map("rawurlencode", explode("/", $work['url'])));

			if ( $work['accepted'] == 0 )
			{
				if ($is_allowedToEdit)
				{
					echo	"<tr>\n".

							"<td valign=\"top\">".
							"<a href=\"".$currentCourseRepositoryWeb.$url."\">".
							"<img  alt=\"".
							$work['titre'].
							"\" src=\"".$clarolineRepositoryWeb."img/travaux.gif\" border=0>".
							"</a>".
							"</td>\n".

							"<td valign=\"top\">".
							"<u>" . $langTitleWork . "</u> : <a href=\"".$currentCourseRepositoryWeb.$url."\">".
							"<font color=\"gray\">".
							$work['titre'].
							"</font>".
							"</a>".
							"<br>".
							"<u>" . $langAuthors . "</u> : " . $work['auteurs'].
							"<br>".
							"<u>" . $langDescription . "</u> : " . $work['description'];
				}
			}
			else // normal display
			{
				echo	"<tr>\n".

						"<td width=\"30\" valign=\"top\">".
						"<a href=\"".$currentCourseRepositoryWeb.$url."\">".
						"<img  alt=\"\" src=\"".$clarolineRepositoryWeb."img/travaux.gif\" border=\"0\">".
						"</a>".
						"</td>\n".

						"<td  width=\"570\"  valign=\"top\">\n".
						"<u>" . $langTitleWork . "</u> : <a href=\"".$currentCourseRepositoryWeb.$url."\">".
						$work['titre'].
						"</a>".
						"<br>".
						"<u>" . $langAuthors . "</u> : " . $work['auteurs'].
						"<br>".
						"<u>" . $langDescription . "</u> : " . $work['description'];
			}



			if ($is_allowedToEdit)	// course administrator only
			{
				echo	"<p>\n",

						"<a href=\"".$_SERVER['PHP_SELF']."?edit=",$work['id'],"\">",
						"<img src=\"".$clarolineRepositoryWeb."img/edit.gif\" border=\"0\" alt=\"".$langModify."\">",
						"</a>\n",

						"<a href=\"".$_SERVER['PHP_SELF']."?delete=",$work['id'],"\" ",
						"onclick=\"javascript:if(!confirm('".addslashes(htmlspecialchars($langDelete.": ".$work['titre']." ".$langConfirmYourChoice))."')) return false;\">",
						"<img src=\"".$clarolineRepositoryWeb."img/delete.gif\" border=\"0\" alt=\"".$langDelete."\">",
						"</a>\n";

				if ($work["accepted"] == 1)
				{
					echo "<a href=\"".$_SERVER['PHP_SELF']."?mkInvisbl=",$work['id'],"\">",
					"<img src=\"".$clarolineRepositoryWeb."img/visible.gif\" border=\"0\" alt=\"".$lang_make_invisible."\">",
					"</a>\n";
				}
				else
				{
					echo	"<a href=\"".$_SERVER['PHP_SELF']."?mkVisbl=",$work['id'],"\">",
							"<img src=\"".$clarolineRepositoryWeb."img/invisible.gif\" border=\"0\" alt=\"".$lang_make_visible."\">",
							"</a>\n";
				}
			} // End course administrator only

			echo	"</td>\n",
					"</tr>\n";
			$i++;

		}	// end while

		echo "</table>";
	}
}

include($includePath."/claro_init_footer.inc.php");




/**
 * function claro_get_work_data($work_id)
 *
 * @param work_id integer id of work in the current course
 * @return data of the work in work table of the current course
 * @author christophe Gesché <moosh@claroline.net>
 * @desc return data of the work in work table of the current course
 *
 */
function claro_get_work_data($work_id)
{
		GLOBAL $tbl_work;
		if (is_integer($work_id))
		{
			$sql    = "SELECT * FROM `".$tbl_work."` WHERE id='".$work_id."'";
			$resWork = claro_sql_query($sql);
			$work = mysql_fetch_array($resWork);
			return $work;
		}
		else  return claro_failure::set_failure ('<em>'.claro_strip_tags($work_id).'</em> is not an valid work id<br>');
}

?>
