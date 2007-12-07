<?php // $Id$
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.4.1  $Revision$                           |
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2003 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
      | Authors: Thomas Depraetere <depraetere@ipm.ucl.ac.be>                |
      |          Hugues Peeters    <peeters@ipm.ucl.ac.be>                   |
      |          Christophe Gesché <gesche@ipm.ucl.ac.be>                    |
      |          Olivier Brouckaert <oli.brouckaert@skynet.be>               |
      +----------------------------------------------------------------------+
 */

/**
 * Back up a course.
 *
 *	- check if course exists (to be used by admin)
 * 	- build backup config file contain max info to restore the course.
 *	- Copy all of this in a target directory.
 * 		- records form main database, about the course
 * 		- course database
 * 		- diretory of the course
 * 	- compress the directory and content in an archive file.
 */

$langFile='course_info';

require '../inc/claro_init_global.inc.php';

if(extension_loaded('zlib'))
{
	include($includePath.'/lib/pclzip/pclzip.lib.php');
}

@include($includePath."/lib/debug.lib.inc.php");


$isAllowedToBackUp=$is_courseAdmin;

$currentCourseId		= $_course["sysCode"];
$currentCourseDbName 	= $_course["dbName"];
$currentCourseDbNameGlu = $_course["dbNameGlu"];
$currentCoursePath 		= $_course["path"];
$currentCourseCode 		= $_course["officialCode"];
$currentCourseName 		= $_course["name"];

$archivePath=$rootSys.$archiveDirName.'/'.$currentCoursePath.'/';
$archiveFile=$archivePath. date("Y-m-d-H-i-s") .'.'.$archiveExt;

$nameTools=$langArchiveCourse;

$interbredcrump[]=array("url" => "infocours.php","name" => $langModifInfo);

@include($includePath."/claro_init_header.inc.php");
?>

<h3>
  <?php echo $nameTools.' &quot;'.$currentCourseName.'&quot;'; ?>
</h3>

<?php
if($isAllowedToBackUp && $confirmBackup) //  check if allowed to back up and if confirmed
{
	// does the course exist ?

	$result=mysql_query("SELECT code_cours, user_id, statut
						 FROM cours_user
		                 WHERE code_cours='$currentCourseId'
		                 AND user_id='$_uid'
		                 AND statut='1'") or die('Error in file '.__FILE__.' at line '.__LINE__);

	if(!mysql_num_rows($result))
	{
		@include($includePath."/claro_init_footer.inc.php");

		exit();
	}
?>

<table border="0" align="center" cellpadding="0" cellspacing="0" width="100%">
<tr>
  <td>
	<hr noshade="noshade" size="1">

	<u><?php echo $langArchiveName; ?></u> : archive.<?php echo $currentCourseId.'.'. date("YzBs"); ?>.zip

	<br>

	<u><?php echo $langArchiveLocation; ?></u> : <?php echo str_replace($currentCoursePath.'/','',$archivePath); ?>

	<br>

<?php
	
// Thomas debugging for 1.4.1
/* $courseDirSize=dirSize($rootSys.$currentCoursePath.'/');


$courseDirSize=($courseDirSize >= 1048576) ? round($courseDirSize/(1024*1024),2).' Mb'
											   : round($courseDirSize/1024,2).' Kb';
?>

	<u><?php echo $langSizeOf.' '.$coursesRepositorySys.$currentCoursePath.'/'; ?></u> : <?php echo $courseDirSize; ?>

<?php
	if(function_exists('diskfreespace'))
	{
		$diskFreeSpace=diskfreespace('/');

		$diskFreeSpace=($diskFreeSpace >= 1073741824) ? round($diskFreeSpace/(1024*1024*1024),2).' Gb'
													  : round($diskFreeSpace/(1024*1024),2).' Mb';
?>

	<br>

	<u><?php echo $langDisk_free_space; ?></u> : <?php echo $diskFreeSpace; ?>

<?php
	}
*/
?>

	<hr noshade="noshade" size="1">

<?php
// ********************************************************************
// build config file
// ********************************************************************

	// str_replace() removes \r that cause squares to appear at the end of each line
	$stringConfig=str_replace("\r","","<?php
/*
      ----------------------------------------------------------------------
        CLAROLINE version $clarolineVersion
      ----------------------------------------------------------------------
        This file was generate by script
        $PHP_SELF
        ".date('r')."
      ----------------------------------------------------------------------
        This program is free software; you can redistribute it and/or
        modify it under the terms of the GNU General Public License
        as published by the Free Software Foundation; either version 2
      ----------------------------------------------------------------------
        Source are in $archivePath
      ----------------------------------------------------------------------
*/
");

	// begin list of works

	echo '<ol>';

	// if dir is missing, first we create it. mkpath() is a recursive function

    $dirCourBase=$archivePath.'courseBase';

	if(!is_dir($dirCourBase))
	{
		echo '<li>'.$langCreateMissingDirectories.' : '.$dirCourBase;

		if($verboseBackup)
		{
			echo '<hr size="1" noshade="noshade">';
		}

		mkpath($dirCourBase,$verboseBackup);

		echo '</li>';
	}

    $dirMainBase=$archivePath.'mainBase';

	if(!is_dir($dirMainBase))
	{
		echo '<li>'.$langCreateMissingDirectories.' : '.$dirMainBase;

		if($verboseBackup)
		{
			echo '<hr size="1" noshade="noshade">';
		}

		mkpath($dirMainBase,$verboseBackup);

		echo '</li>';
	}

	$dirhtml=$archivePath.'html';

	if(!is_dir($dirhtml))
	{
		echo '<li>'.$langCreateMissingDirectories.' : '.$dirhtml;

		if($verboseBackup)
		{
			echo '<hr size="1" noshade="noshade">';
		}

		mkpath($dirhtml,$verboseBackup);

		echo '</li>';
	}

// ********************************************************************
//  copy datas of "cours" table from the main database
// ********************************************************************

	echo '<li>'.$langBUCourseDataOfMainBase.'  '.$currentCourseCode;

	if($verboseBackup)
	{
		echo '<hr size="1" noshade="noshade">';
	}

	$sqlInsertCourse='INSERT INTO `cours` SET ';

	$sqlSelectInfoCourse="SELECT * FROM `cours` WHERE code='$currentCourseId'";
	$resInfoCourse=mysql_query_dbg($sqlSelectInfoCourse);

	$infoCourse=mysql_fetch_array($resInfoCourse);

	for($noField=0;$noField < mysql_num_fields($resInfoCourse);$noField++)
	{
		if($noField)
		{
			$sqlInsertCourse.=', ';
		}

		$nameField=mysql_field_name($resInfoCourse,$noField);

		$sqlInsertCourse.="$nameField='".addslashes($infoCourse[$nameField])."'";
	}

	$sqlInsertCourse.=';';

	$stringConfig.="\n# Insert Course\n#------------------------------------------\n$sqlInsertCourse\n#------------------------------------------";

	if($verboseBackup)
	{
		echo $sqlInsertCourse.'<br><br></li>';
	}

	$fcoursql=fopen($archivePath."mainBase/cours.sql","w");
	fwrite($fcoursql, $sqlInsertCourse);
	fclose($fcoursql);

// ********************************************************************
//  copy info about users
// ********************************************************************

	echo '<li>'.$langBUUsersInMainBase.' '.$currentCourseCode;

	if($verboseBackup)
	{
		echo '<hr size="1" noshade="noshade">';
	}

	// recup users

	$sqlUserOfTheCourse="SELECT	u.* FROM `user` u, `cours_user` cu WHERE u.user_id=cu.user_id AND cu.code_cours='$currentCourseId'";

	$resUsers=mysql_query_dbg($sqlUserOfTheCourse,$db);
	$nbFields=mysql_num_fields($resUsers);

	$csvInsertUsers='';

	// creation of header

	for($noField=0;$noField < $nbFields;$noField++)
	{
		$nameField=mysql_field_name($resUsers,$noField);

		$csvInsertUsers.="'".addslashes($nameField)."';";
	}

	// creation of body

	while($users=mysql_fetch_array($resUsers))
	{
		$csvInsertUsers.="\n";

		for($noField=0;$noField < $nbFields;$noField++)
		{
			$nameField=mysql_field_name($resUsers,$noField);

			$csvInsertUsers.="'".addslashes($users[$nameField])."';";
		}
	}

	if($verboseBackup)
	{
		echo claro_parse_user_text($csvInsertUsers).'<br><br></li>';
	}

	$stringConfig.="\n\n# CSV of Users\n#------------------------------------------\n$csvInsertUsers\n#------------------------------------------";

	$fuserscsv=fopen($archivePath."mainBase/users.csv","w");
	fwrite($fuserscsv, $csvInsertUsers);
	fclose($fuserscsv);

// ********************************************************************
//  copy course's files
// ********************************************************************

	echo '<li>'.$langCopyDirectoryCourse;

	if($verboseBackup)
	{
		echo '<hr size="1" noshade="noshade">';
	}

	$nbFiles=copydir($coursesRepositorySys.$currentCoursePath.'/', $dirhtml, $verboseBackup);

	if($verboseBackup)
	{
		echo '<strong>'.$nbFiles.'</strong> '.$langFileCopied.'<br><br></li>';
	}

	$stringConfig.="\n\n".$nbFiles." files were in ".$coursesRepositorySys.$currentCoursePath."/";

// ********************************************************************
//  copy course database
// ********************************************************************

	echo '<li>'.$langBackupOfDataBase.' '.$currentCourseDbName.'  (SQL)';

	if($verboseBackup)
	{
		echo '<hr size="1" noshade="noshade">';
	}

	backupDatabase($db, $currentCourseDbName, true, true, 'SQL', $archivePath.'courseBase/', true, $verboseBackup);

// ********************************************************************
//  compress the archive
// ********************************************************************

	$fdesc=fopen($archiveFile, "w");
	fwrite($fdesc,$stringConfig);
	fclose($fdesc);

	echo '</li><li>'.$langBuildTheCompressedFile.'<hr size="1" noshade="noshade">';

	echo '<font color="#FF0000">'.$langBackupSuccesfull.'</font>';

	if(extension_loaded('zlib'))
	{
		$zipCourse=new PclZip($archivePath.'../archive.'.$currentCourseId.'.'. date("YzBs") .'.zip');
		$zipCourse->create('../../'.$archiveDirName.'/'.$currentCoursePath,PCLZIP_OPT_REMOVE_PATH,'../../'.$archiveDirName.'/'.$currentCoursePath.'/');

		echo ' - <a href="../../'.$archiveDirName.'/archive.'.$currentCourseId.'.'. date("YzBs") .'.zip">'.$langDownload.'</a>';

		removeDir($archivePath);
	}

	echo '</ol>';
}	// if allowed to backup
 /**************************************************************************************/
elseif(!$isAllowedToBackUp) //  not allowed to backup
{
	echo $langNotAllowed;
}
else
{
	echo	"<p>",
			"$langConfirmBackup &quot;$currentCourseName&quot; ($currentCourseCode) ?",
			"</p>",
			"<p>",
			"<a href=\"$PHP_SELF?confirmBackup=yes\">$langY</a>",
			"&nbsp;|&nbsp;",
			"<a href=\"infocours.php\">$langN</a>",
			"</p>";
}
?>

  </td>
</tr>
</table>

<?php
@include($includePath."/claro_init_footer.inc.php");

/*******************************/
/* FUNCTIONS					/
/*******************************/

/**
 * Returns the size of a directory
 *
 * @return		integer		size of the directory
 *
 * @param		string		$path			directory path
 * @param		boolean		$recursive		set to true if must go to sub-directories
 */
function dirSize($path, $recursive=true)
{
	$result=0;

	if(!is_dir($path) || !is_readable($path))
	{
		return 0;
	}

	$fd=dir($path);

	while($file = $fd->read())
	{
	   	if($file != '.' && $file != '..')
		{
			if(is_dir($path.$file.'/'))
			{
	 			$result+=$recursive?dirSize($path.$file.'/'):0;
			}
			else
			{
				$result+=filesize($path.$file);
			}
		}
	}

	$fd->close();

	return $result;
}

/**
 * Backup a db to a file
 *
 * @param ressource	$link			lien vers la base de donnees
 * @param string	$db_name		nom de la base de donnees
 * @param boolean	$structure		true => sauvegarde de la structure des tables
 * @param boolean	$donnees		true => sauvegarde des donnes des tables
 * @param boolean	$format			format des donnees
 									'INSERT' => des clauses SQL INSERT
									'CSV' => donnees separees par des virgules
 * @param boolean	$insertComplet	true => clause INSERT avec nom des champs
 * @param boolean	$verbose 		true => comment are printed
 */
function backupDatabase($link, $db_name, $structure, $donnees, $format="SQL", $whereSave=".", $insertComplet="", $verbose=false)
{
	global $singleDbEnabled, $currentCourseDbNameGlu;

	if(!$singleDbEnabled)
	{
		if(!@mysql_select_db($db_name))
		{
			return false;
		}
	}

	$filename=$whereSave.'/courseDbContent.'.strtolower($format);

	if(!$fp=@fopen($filename, "w"))
	{
		return false;
	}

	// selects all tables from the DB
	$result=mysql_query("SHOW TABLES");

	$courseTables=array();

	while($row=mysql_fetch_row($result))
	{
		if(!$singleDbEnabled || eregi("^$currentCourseDbNameGlu",$row[0]))
		{
			$courseTables[]=$row[0];
		}
	}

	foreach($courseTables as $tableName)
	{
		if($format == 'PHP')
		{
			fwrite($fp,'mysql_query("');
		}

		if($verbose)
		{
			echo '['.$tableName.'] ';
		}

		if($structure)
		{
			fwrite($fp,"DROP TABLE IF EXISTS `$tableName`;\n");

			if($format == 'PHP')
			{
				fwrite($fp,'");'."\n\n");
				fwrite($fp,'mysql_query("');
			}

			$query="SHOW CREATE TABLE `$tableName`";
			$resCreate=mysql_query($query);

			$row=mysql_fetch_array($resCreate);

			$schema=$row[1].';';

			fwrite($fp,"$schema\n\n");

			if($format == 'PHP')
			{
				fwrite($fp,'");'."\n\n");
			}
		}

		if($donnees == true)
		{
			$query="SELECT * FROM `$tableName`";
			$resData=mysql_query($query);

			if(mysql_num_rows($resData))
			{
				$sFieldnames='';

				if($insertComplet)
				{
					$num_fields=mysql_num_fields($resData);

					for($j=0;$j < $num_fields;$j++)
					{
						$sFieldnames.='`'.mysql_field_name($resData, $j).'`, ';
					}

					$sFieldnames='('.substr($sFieldnames, 0, -2).')';
				}

				$sInsert="INSERT INTO `$tableName` $sFieldnames values ";

				while($rowdata=mysql_fetch_assoc($resData))
				{
					$lesDonnees='<guillemet>'.implode('<guillemet>,<guillemet>', $rowdata).'<guillemet>';
					$lesDonnees=str_replace('<guillemet>', "'",addslashes($lesDonnees));

					if($format == 'SQL')
					{
						$lesDonnees=$sInsert.' ( '.$lesDonnees.' );';
					}

					if($format == 'PHP')
					{
						fwrite($fp,'mysql_query("');
					}

					fwrite($fp, "$lesDonnees\n");

					if($format == 'PHP')
					{
						fwrite($fp,'");');
					}
				}
			}
		}
	}

	fclose($fp);
}

/**
 * copy a directory from a source location to a target location
 *
 * @return		integer		total of file copied
 *
 * @param string	$origine		source location
 * @param string	$destination	target location
 * @param boolean	$verbose 		true => comment are printed
 */
function copydir($origine, $destination, $verbose=false)
{
	if(!is_dir($destination))
	{
		mkdir($destination, 0770);
	}

	if($verbose)
	{
		echo '<strong>['.basename($destination).']</strong><ol>';

		$total=0;
	}

	$dossier=opendir($origine);

	while($fichier = readdir($dossier))
	{
		$l=array('.', '..');

		if(!in_array($fichier,$l))
		{
			if(is_dir($origine.'/'.$fichier))
			{
				if($verbose)
				{
					echo '<li>';
				}

				$total+=copydir("$origine/$fichier", "$destination/$fichier", $verbose);
			}
			else
			{
				copy("$origine/$fichier", "$destination/$fichier");

				if($verbose)
				{
					echo '<li>'.$fichier;
				}

				$total++;
			}

			if($verbose)
			{
				echo '</li>';
			}
		}
	}

	if($verbose)
	{
		echo '</ol>';
	}

	return $total;
}

/**
 * to create missing directory in a gived path
 *
 * @returns a resource identifier or FALSE if the query was not executed correctly.
 * @author KilerCris@Mail.com original function from  php manual
 * @author Christophe Gesché gesche@ipm.ucl.ac.be Claroline Team
 * @since  28-Aug-2001 09:12
 * @param sting		$path 		wanted path
 * @param boolean	$verbose	fix if comments must be printed
 * @param string	$mode		fix if chmod is same of parent or default
 */
function mkpath($path, $verbose = false, $mode = "herit")
{
	global $langCreatedIn;

	$path=str_replace("/","\\",$path);
	$dirs=explode("\\",$path);

	$path=$dirs[0];

	if($verbose)
	{
		echo "<UL>";
	}

	for($i=1;$i < sizeof($dirs);$i++)
	{
		$path.='/'.$dirs[$i];

		if(!is_dir($path))
		{
			$ret=mkdir($path,0770);

			if($ret)
			{
				if($verbose)
				{
					echo '<li><strong>'.basename($path).'</strong><br>'.$langCreatedIn.'<br><strong>'.realpath($path.'/..').'</strong></li>';
				}
			}
			else
			{
				if($verbose)
				{
					echo '</UL>error : '.$path.' not created';
				}

				$ret=false;

				break;
			}
		}
	}

	if($verbose)
	{
		echo '</UL>';
	}

	return $ret;
}

/**
 * removes a directory recursively
 *
 * @returns true if OK, otherwise false
 *
 * @author Amary <MasterNES@aol.com> (from Nexen.net)
 * @author Olivier Brouckaert <oli.brouckaert@skynet.be>
 *
 * @param string	$dir		directory to remove
 */
function removeDir($dir)
{
	if(!@$opendir = opendir($dir))
	{
		return false;
	}

	while($readdir = readdir($opendir))
	{
		if($readdir != '..' && $readdir != '.')
		{
			if(is_file($dir.'/'.$readdir))
			{
				if(!@unlink($dir.'/'.$readdir))
				{
					return false;
				}
			}
			elseif(is_dir($dir.'/'.$readdir))
			{
				if(!removeDir($dir.'/'.$readdir))
				{
					return false;
				}
			}
		}
	}

	closedir($opendir);

	if(!@rmdir($dir))
	{
		return false;
	}

	return true;
}
?>
