<?php // $Id$
/**---------------------------------------------------------------------+
 * CLAROLINE version 1.4.0 $Revision$                            |
 *----------------------------------------------------------------------+
 * Copyright (c) 2000, 2003 Universite catholique de Louvain (UCL)      |
 *----------------------------------------------------------------------+
 * This source file is subject to the GENERAL PUBLIC LICENSE,           |
 * available through the world-wide-web at                              |
 * http://www.gnu.org/copyleft/gpl.html                                 |
 *----------------------------------------------------------------------+
 * Authors: Thomas Depraetere <depraetere@ipm.ucl.ac.be>                |
 *          Hugues Peeters    <peeters@ipm.ucl.ac.be>                   |
 *          Christophe Gesché <gesche@ipm.ucl.ac.be>                    |
 *----------------------------------------------------------------------+
 */
/*
  DESCRIPTION:
  ****
  This PHP script allow user to manage files and directories on a remote http server.
  The user can : - navigate trough files and directories.
                 - upload a file
				 - rename, delete, copy a file or a directory

  The script is organised in four sections.

  * 1st section execute the command called by the user
                Note: somme commands of this section is organised in two step.
			    The script lines always begin by the second step,
			    so it allows to return more easily to the first step.

  * 2nd section define the directory to display

  * 3rd section read files and directories from the directory defined in part 3

  * 4th section display all of that on a HTML page
*/

/*========================================
       CLAROLINE MAIN SETTINGS  PART 1
  =======================================*/
$langFile = "document";
include('../inc/claro_init_global.inc.php');


if (!$_gid || !$is_groupAllowed) header("Location:group.php");


$nameGroup = $_group['name'];
$nameTools = $langDoc;

$TABLEFORUM	= $_course['dbNameGlu']."forums";

@include($includePath."/conf/group.document.conf.php");
@include($includePath."/lib/fileDisplay.lib.php");
@include($includePath."/lib/fileManage.lib.php");
@include($includePath."/lib/fileUpload.lib.php");

$interbredcrump[]= array ("url"=>"group.php", "name"=> $langGroupManagement);

$htmlHeadXtra[] ="<style type=text/css><!--
.comment { margin-left: 30px}
.invisible {color: #999999}
.invisible a {color: #999999}
--></style>";

$htmlHeadXtra[] =
"<script>
function confirmation (name)
{
	if (confirm(\" $langAreYouSureToDelete \"+ name + \" ?\"))
		{return true;}
	else
		{return false;}
}
</script>";


@include($includePath."/claro_init_header.inc.php");

$maxFilledSpace = $groupDocument_maxFilledSpace;

$baseServDir = $rootSys; // ie $rootSys = /var/www/html/toto/Claroline010
$baseServUrl = $urlAppend."/";
$courseDir   = $_course["path"]."/group/".$_group['directory'];
$baseWorkDir = $baseServDir.$courseDir;

if(!$is_groupAllowed) die ("<br><center>not allowed...</center>");


/* clean information submited by the user from antislash */

stripSubmitValue($HTTP_POST_VARS);
stripSubmitValue($HTTP_GET_VARS);

//////////////////////////////////////////////////////////////////////////////

/* > > > > > > > > > > > > MAIN SECTION  < < < < < < < < < < < < */

/*=====================================
              UPLOAD FILE
=======================================*/

	// check the request method in place of a variable from POST because if the file size exceed the maximum file upload
	// size set in php.ini, all variables from POST are cleared !
	if ($REQUEST_METHOD == 'POST')
	{
		/*
		 * Check if the file is valide (not to big and exists)
		 */

		if(!is_uploaded_file($userFile))
		{
			$dialogBox .= $langFileError.'<br>'.$langNotice.' : '.$langMaxFileSize.' '.get_cfg_var('upload_max_filesize');
		}

		/*
		 * Check the file size doesn't exceed
		 * the maximum file size authorized in the directory
		 */

		elseif ( ! enough_size($HTTP_POST_FILES['userFile']['size'], $baseWorkDir, $maxFilledSpace))
		{
			$dialogBox .= $langNoSpace;
		}
		else
		{
			$fileName = trim ($HTTP_POST_FILES['userFile']['name']);

			/* Check for no desired characters */
			$fileName = replace_dangerous_char($fileName);

			/* Try to add an extension to files witout extension */
			$fileName = add_ext_on_mime($fileName);
				
			/* Handle PHP files */
			$fileName = php2phps($fileName);
				
			/*Copy the file to the desired destination */
			copy ($userFile, $baseWorkDir.$uploadPath."/".$fileName);

			$dialogBox .= $langDownloadEnd;

		} // end else
	}


	/*==========================
	   MOVE FILE OR DIRECTORY
	==========================*/
			

	/*
	 * The code begin with STEP 2
	 * so it allows to return to STEP 1 if STEP 2 unsucceeds
	 */

	/*-------------------------------------
		MOVE FILE OR DIRECTORY : STEP 2
	--------------------------------------*/

	if (isset($moveTo))
	{
		if ( move($baseWorkDir.$source,$baseWorkDir.$moveTo) )
		{
			$dialogBox = $langDirMv;
		}
		else
		{
			$dialogBox = $langImpossible;

			/*** return to step 1 ***/
			$move = $source;
			unset ($moveTo);
		}
		
	}


	/*-------------------------------------
		MOVE FILE OR DIRECTORY : STEP 1
	--------------------------------------*/

	if (isset($move))
	{
		$dialogBox .= form_dir_list("source", $move, "moveTo", $baseWorkDir);
	}

	/*==========================
	   DELETE FILE OR DIRECTORY
	  ==========================*/


	if ( isset($delete) )
	{
		if ( my_delete($baseWorkDir.$delete))
		{
			$dialogBox = $langDocDeleted;
		}
	}




	/*==========================
				RENAME
	  ==========================*/
	/*
	 * The code begin with STEP 2
	 * so it allows to return to STEP 1
	 * if STEP 2 unsucceds
	 */

	/*-------------------------------------
			  RENAME : STEP 2
	--------------------------------------*/

	if (isset($renameTo))
	{
		if ( my_rename($baseWorkDir.$sourceFile, $renameTo) )
		{
			$dialogBox = $langElRen;
		}
		else
		{
			$dialogBox = $langFileExists;

			/*** return to step 1 ***/
			$rename = $sourceFile; 
			unset($sourceFile);
		}
	}

	/*-------------------------------------
				RENAME : STEP 1
	--------------------------------------*/

	if (isset($rename))
	{
		$fileName = basename($rename);
		$dialogBox .= "<!-- rename -->\n";
		$dialogBox .= "<form>\n";
		$dialogBox .= "<input type=\"hidden\" name=\"sourceFile\" value=\"$rename\">\n";
		$dialogBox .= $langRename." ".htmlentities($fileName)." :\n";
		$dialogBox .= "<input type=\"text\" name=\"renameTo\" value=\"$fileName\">\n";
		$dialogBox .= "<input type=\"submit\" value=\"OK\">\n";
		$dialogBox .= "</form>\n";
	}

/*==========================
        CREATE DIRECTORY
  ==========================*/

	/*
	 * The code begin with STEP 2
	 * so it allows to return to STEP 1
	 * if STEP 2 unsucceds
	 */

	/*-------------------------------------
	               STEP 2
	--------------------------------------*/
	if (isset($newDirPath) && isset($newDirName))
	{
		$newDirName = replace_dangerous_char($newDirName);

		if ( check_name_exist($baseWorkDir.$newDirPath."/".$newDirName) )
		{
			$dialogBox .= $langFileExists;
			$createDir = $newDirPath; unset($newDirPath);// return to step 1
		}
		else
		{
			mkdir($baseWorkDir.$newDirPath."/".$newDirName, 0700);
		}

		$dialogBox = $langDirCr;
	}

	/*-------------------------------------
	                STEP 1
	--------------------------------------*/

	if (isset($createDir))
	{
		$dialogBox .= "<!-- create dir -->\n";
		$dialogBox .= "<form>\n";
		$dialogBox .= "<input type=\"hidden\" name=\"newDirPath\" value=\"$createDir\">\n";
		$dialogBox .= "nom du nouveau r&eacute;pertoire :\n";
		$dialogBox .= "<input type=\"text\" name=\"newDirName\">\n";
		$dialogBox .= "<input type=\"submit\" value=\"Ok\">\n";
		$dialogBox .= "</form>\n";
	}


//////////////////////////////////////////////////////////////////////////////


	/*==========================
	   DEFINE CURRENT DIRECTORY
	  ==========================*/
	   
if (isset($openDir)  || isset($moveTo) || isset($createDir) || isset($newDirPath) || isset($uploadPath) ) // $newDirPath is from createDir command (step 2) and $uploadPath from upload command
{
	$curDirPath = $openDir . $createDir . $moveTo . $newDirPath . $uploadPath;
	/*
	 * NOTE: Actually, only one of these variables is set.
	 * By concatenating them, we eschew a long list of "if" statements
	 */
}
elseif ( isset($delete) || isset($move) || isset($rename) || isset($sourceFile) || isset($comment) || isset($commentPath) || isset($mkVisibl) || isset($mkInvisibl)) //$sourceFile is from rename command (step 2)
{
	$curDirPath = dirname($delete . $move . $rename . $sourceFile . $comment . $commentPath . $mkVisibl . $mkInvisibl);
	/*
	 * NOTE: Actually, only one of these variables is set.
	 * By concatenating them, we eschew a long list of "if" statements
	 */
}
else
{
	$curDirPath="";
}

if ($curDirPath == "/" || $curDirPath == "\\" || strstr($curDirPath, ".."))
{
	$curDirPath =""; // manage the root directory problem
}

$curDirName = basename($curDirPath);
$parentDir = dirname($curDirPath);

if ($parentDir == "/" || $parentDir == "\\")
{
	$parentDir = ""; // manage the root directory problem
}

$query="SELECT md5 FROM `$TABLEFORUM` WHERE forum_id='$forumId'";
$result=mysql_query($query) or die('Erreur in query');

list($md5) = mysql_fetch_row($result);


/*==============================
  READ CURRENT DIRECTORY CONTENT
  ==============================*/
	
/*----------------------------------------
  LOAD FILES AND DIRECTORIES INTO ARRAYS
  --------------------------------------*/

@chdir ($baseWorkDir.$curDirPath) or die ("<center><b>Wrong Directory !</b><br> Please contact your Platform Administrator</center>");

$handle = opendir(".");


define('A_DIRECTORY', 1);
define('A_FILE',      2);


while ($file = readdir($handle))
{
	if ($file == "." || $file == "..")
	{
		continue;						// Skip current and parent directories
	}

	$fileList['name'][] = $file;
	
	if(is_dir($file))
	{
		$fileList['type'][] = A_DIRECTORY;
		$fileList['size'][] = false;
		$fileList['date'][] = false;
	}
	elseif(is_file($file))
	{
		$fileList['type'][] = A_FILE;
		$fileList['size'][] = filesize($file);
		$fileList['date'][] = filectime($file);
	}
}				// end while ($file = readdir($handle))

/*
 * Sort alphabetically the File list
 */

if ($fileList)
{
	array_multisort($fileList['type'], $fileList['name'],
	                $fileList['size'], $fileList['date']);
}

closedir($handle);


/*==========================
             DISPLAY
  ==========================*/



$dspCurDirName = htmlentities($curDirName);
$cmdCurDirPath = rawurlencode($curDirPath);
$cmdParentDir  = rawurlencode($parentDir);

?>

<div class="fileman" align="center">
<table width="100%" border="0" cellspacing="2" cellpadding="4">
<tr>
<td><h4><?= $langDoc; ?></h4></td>

<td align="right">

<a href="#" onClick="MyWindow=window.open('../help/help_document.php','MyWindow','toolbar=no,location=no,directories=no,status=yes,menubar=no,scrollbars=yes,resizable=yes,width=350,height=450,left=300,top=10'); return false;">
<?php echo $langHelp ?>
</a>
</td>
</tr>

<tr>

<?php

echo	"<td colspan=2>",
		$nameGroup," : ",
		"<a href=\"../group/group_space.php\">$langGroupSpaceLink</a>&nbsp;&nbsp;\n",
		"<a href=\"../phpbb/viewforum.php?forum=$forumId&md5=$md5\">$langGroupForumLink</a>",
		"</td>\n",
		"</tr>\n",

		"<tr>\n";


/*----------------------------------------
	DIALOG BOX SECTION
--------------------------------------*/

if ($dialogBox) echo "<td bgcolor=\"#FFCC00\">",$dialogBox,"</td>";
else            echo "<td>\n<!-- dialog box -->\n&nbsp;\n</td>\n";


/*----------------------------------------
               UPLOAD SECTION
  --------------------------------------*/

echo	"<!-- upload  -->",
		"<td align=\"right\">",
		"<form action=\"$PHP_SELF\" method=\"post\" enctype=\"multipart/form-data\">",
		"<input type=\"hidden\" name=\"uploadPath\" value=\"$curDirPath\">",
		$langDownloadFile,"&nbsp;:",
		"<input type=\"file\" name=\"userFile\">",
		"<input type=\"submit\" value=\"$langDownload\">",
		"</form>",
		"</td>\n";

?>

</tr>
</table>

<table width="100%" border="0" cellspacing="2">

<?php


/*----------------------------------------
	CURRENT DIRECTORY LINE
  --------------------------------------*/

echo	"<tr>\n",
		"<td colspan=8>\n";

/* GO TO PARENT DIRECTORY */

if ($curDirName) // if the $curDirName is empty, we're in the root point and we can't go to a parent dir
{
	echo	"<!-- parent dir -->\n",
			"<a href=\"$PHP_SELF?openDir=".$cmdParentDir."\">",
			"<img src=\"../img/parent.gif\" border=\"0\" alt=\"",$langUp,"\"  align=\"absbottom\" hspace=\"5\">",
			"<small>$langUp</small>",
			"</a>\n";
}


/* CREATE DIRECTORY */

echo	"<!-- create dir -->\n",
		"<a href=\"$PHP_SELF?createDir=".$cmdCurDirPath."\">",
		"<img  alt=\"",$langCreateDir,"\"  src=\"../img/dossier.gif\" border=\"0\" align=\"absbottom\" hspace=\"5\">",
		"<small>$langCreateDir</small>",
		"</a>",

		"</tr>\n";
		"</td>\n";


if ($curDirName) // if the $curDirName is empty, we're in the root point and there is'nt a dir name to display
{
	/* CURRENT DIRECTORY */

	echo	"<!-- current dir name -->\n",
			"<tr>\n",
			"<td colspan=\"7\" align=\"left\" bgcolor=\"#4171B5\">\n",
			"<img  alt=\"\" src=\"../img/opendir.gif\" align=\"absbottom\" vspace=\"2\" hspace=\"5\">\n",
			"<font color=\"#CCCCCC\"><b>".$dspCurDirName."</b></font>\n",
			"</td>\n",
			"</tr>\n";
}




/* COMMAND LIST */

echo	"<tr bgcolor=\"$color2\"  align=\"center\" valign=\"top\">\n",

		"<td>$langName</td>\n",
		"<td>$langSize</td>\n",
		"<td>$langDate</td>\n",
		"<td>$langDelete</td>\n",
		"<td>$langMove</td>\n",
		"<td>$langRename</td>\n",
		"<td>$langPublish</td>\n",

		"</tr>\n";


/*----------------------------------------
	DISPLAY FILES LIST
--------------------------------------*/

if ($fileList)
{
	while (list($fileKey, $fileName) = each ($fileList['name']))
	{
		$dspFileName = htmlentities($fileName);
		$cmdFileName = rawurlencode($curDirPath."/".$fileName);

		if ($fileList['type'][$fileKey] == A_FILE)
		{
			$image       = choose_image($fileName);
			$size        = format_file_size($fileList['size'][$fileKey]);
			$date        = format_date($fileList['date'][$fileKey]);
			$urlFileName = "../../".format_url($courseDir.$curDirPath."/".$fileName);
		}
		elseif ($fileList['type'][$fileKey] == A_DIRECTORY)
		{
			$image       = 'dossier.gif';
			$size        = '';
			$date        = '';
			$urlFileName = $PHP_SELF.'?openDir='.$cmdFileName;
		}

		echo	"<tr align=\"center\"",$style,">\n",
				"<td align=\"left\">",
				"<a href=\"",$urlFileName,"\"",$style,">",
				"<img src=\"./../img/",$image,"\" border=\"0\" hspace=\"5\" alt=\"",$dspFileName,"\">",$dspFileName,"</a>",
				"</td>\n",

				"<td><small>",$size,"</small></td>\n",
				"<td><small>",$date,"</small></td>\n";


		/* DELETE COMMAND */
		echo 	"<td>",
				"<a href=\"",$PHP_SELF,"?delete=",$cmdFileName,"\" ",
				"onClick=\"return confirmation('",addslashes($dspFileName),"');\">",
				"<img src=\"../img/supprimer.gif\" border=\"0\" alt=\"",$langDelete,"\">",
				"</a>",
				"</td>\n";

		/* COPY COMMAND */
		echo	"<td>",
				"<a href=\"",$PHP_SELF,"?move=",$cmdFileName,"\">",
				"<img src=\"../img/deplacer.gif\" border=\"0\" alt=\"",$langMove,"\">",
				"</a>",
				"</td>\n";

		/* RENAME COMMAND */
		echo	"<td>",
				"<a href=\"",$PHP_SELF,"?rename=",$cmdFileName,"\">",
				"<img src=\"../img/edit.gif\" border=\"0\" alt=\"",$langRename,"\">",
				"</a>",
				"</td>\n";

		/* PUBLISH COMMAND */
		if ($fileList['type'][$fileKey] == A_FILE)
		{
			echo	"<td>",
					"<a href=\"../work/work.php?",
					"submitGroupWorkUrl=",rawurlencode("../../$courseDir"),$cmdFileName,"\">",
					"<small>",$langPublish,"</small>",
					"</a>",
					"</td>\n";
		}
		else
		{
			echo "<td></td>";
		}
		
		echo "</tr>\n";
	}
}
?>
</table>
</div>
<?php
@include($includePath."/claro_init_footer.inc.php");
?>
