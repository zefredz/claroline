<?php // $Id$
/*
+----------------------------------------------------------------------+
| CLAROLINE 1.6
+----------------------------------------------------------------------+
| Copyright (c) 2001, 2004 Universite catholique de Louvain (UCL)      |
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

// Debug 

$verboseBackup = 0;
$message = "";

// Lang

$langFile='course_info';

// Include global and libs

require '../inc/claro_init_global.inc.php';

if(extension_loaded('zlib'))
{
	include($includePath.'/lib/pclzip/pclzip.lib.php');
}

@include($includePath."/lib/debug.lib.inc.php");
include($includePath."/lib/fileManage.lib.php");

// Courses variables

$currentCourseId		= $_course["sysCode"];
$currentCourseDbName 	= $_course["dbName"];
$currentCourseDbNameGlu = $_course["dbNameGlu"];
$currentCoursePath 		= $_course["path"];
$currentCourseCode 		= $_course["officialCode"];
$currentCourseName 		= $_course["name"];

// Tables variables

$tbl_mdb_names   = claro_sql_get_main_tbl();
$TABLECOURSUSER  = $tbl_mdb_names['rel_course_user'];
$TABLECOURS      = $tbl_mdb_names['course'];
$TABLEUSERS      = $tbl_mdb_names['user'];

// Path and files variables

$archiveDirName    = "archive"; // claro_main_conf 
$archiveExt         = "claro";

$archivePath       = $rootSys.$archiveDirName ;
$archiveCoursePath = $archivePath .'/'.$currentCoursePath;
$archiveFile       = $archiveCoursePath . "/" . date("Ymd-Hi") . "." . $archiveExt;
$dirCourseBase     = $archiveCoursePath . "/" . "courseBase";
$dirMainBase       = $archiveCoursePath . "/" . "mainBase";
$dirHtml           = $archiveCoursePath . "/" . "html";
$courseFile        = $dirMainBase . "/" . "course.sql";
$userCSVFile       = $dirMainBase . "/" . "users.csv";
$coursePath        = $coursesRepositorySys.$currentCoursePath.'/';
    
$archiveName       = "archive." . $currentCourseId.'.'. date("Ymd-Hi") . ".zip";
$archiveLocation   = str_replace($currentCoursePath.'/','',$archivePath);

// Display header

$interbredcrump[]=array("url" => "infocours.php","name" => $langCourseSettings);
include($includePath."/claro_init_header.inc.php");

// Display tool title

claro_disp_tool_title(array('mainTitle'=>$langArchiveCourse,'subTitle'=>'&quot;'.$currentCourseName.'&quot;'));

// User is allowed ?

$isAllowedToBackUp = $is_courseAdmin;
if (!$isAllowedToBackUp) claro_disp_auth_form();

if($isAllowedToBackUp && $confirmBackup) //  check if allowed to back up and if confirmed
{
	// does the course exist ?

	$sql = "SELECT code_cours, user_id, statut
		     FROM `" . $TABLECOURSUSER . "`
		     WHERE code_cours='" . $currentCourseId . "'
		     AND user_id='" . $_uid . "'
		     AND statut='1'";
    $result = claro_sql_query($sql);

	if(!mysql_num_rows($result))
	{
		include($includePath."/claro_init_footer.inc.php");
		exit();
	}

    // Display file name, location, course size
    
    $courseDirSize=DirSize($coursePath);
    $courseDirSize=($courseDirSize >= 1048576) ? round($courseDirSize/(1024*1024),2)." Mb" : round($courseDirSize/1024,2)." Kb";

    echo "<hr noshade=\"noshade\" size=\"1\">\n" .
         "<p>" .
         "<u>" . $langArchiveName . "</u>: " . $archiveName .  "<br >\n" .
         "<u>" . $langArchiveLocation . "</u>: " .  $archiveLocation . "<br >\n" . 
         "<u>" . $langSizeOf . " " . $currentCourseName . "</u>: " . $courseDirSize . "<br >\n" .
         "</p>\n" .
         "<hr noshade=\"noshade\" size=\"1\">\n";

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
        ".$_SERVER['PHP_SELF']."
        ".date('r')."
      ----------------------------------------------------------------------
        This program is free software; you can redistribute it and/or
        modify it under the terms of the GNU General Public License
        as published by the Free Software Foundation; either version 2
      ----------------------------------------------------------------------
        Source are in $archiveCoursePath
      ----------------------------------------------------------------------
    */
    ");

	// Begin list of works

	echo "<h3>" . $langCreateDirectory . "</h3>";
	echo "<ol>";

    // Create folder for Course Database in archive folder
 
	if(!is_dir($dirCourseBase))
	{
		echo "<li>" . $langCreateMissingDirectories . ": " . $dirCourseBase;
		mkpath($dirCourseBase,$verboseBackup);
		echo "</li>\n";
	}

    // Create folder for Main Database in archive folder

	if(!is_dir($dirMainBase))
	{
		echo "<li>" . $langCreateMissingDirectories . ": " . $dirMainBase;
		mkpath($dirMainBase,$verboseBackup);
		echo "</li>\n";
	}

    // Create folder for Html 

	if(!is_dir($dirHtml))
	{
		echo "<li>" . $langCreateMissingDirectories . ": " . $dirHtml;
		mkpath($dirHtml,$verboseBackup);
		echo "</li>\n";
	}

    echo "</ol>\n";

// ********************************************************************
//  copy datas of "cours" table from the main database
// ********************************************************************

	echo "<h3>" . $langBackupCourseInformation . "</h3>\n";

	echo "<ol>\n";

	echo "<li>" . $langBUCourseDataOfMainBase . "  " . $currentCourseCode . "</li>\n";

	$sqlInsertCourse= "INSERT INTO `" . $TABLECOURS . "` SET ";

    // Select course information
	$sql = "SELECT * 
            FROM `" . $TABLECOURS . "` 
            WHERE code='" . $currentCourseId . "'";

	$resInfoCourse= claro_sql_query($sql);

	$infoCourse=mysql_fetch_array($resInfoCourse);

    // create sql insert query

	for($noField=0;$noField < mysql_num_fields($resInfoCourse);$noField++)
	{
		if($noField)
		{
			$sqlInsertCourse.=", ";
		}
		$nameField=mysql_field_name($resInfoCourse,$noField);
		$sqlInsertCourse.="$nameField='".addslashes($infoCourse[$nameField])."'";
	}
	$sqlInsertCourse.=';';

    // add to config

	$stringConfig .= "\n" .
                    "# Insert Course\n" .
                    "#------------------------------------------\n" .
                    $sqlInsertCourse . "\n" .
                    "#------------------------------------------\n" ;

    if ($verboseBackup)
    {
	    echo "<p style='color: green'>" . $sqlInsertCourse . "</p>\n";
    }

    // save in courseFile

	$fcourse=fopen($courseFile,"w");
	fwrite($fcourse, $sqlInsertCourse);
	fclose($fcourse);

// ********************************************************************
//  copy info about users
// ********************************************************************

	echo "<li>" . $langBUUsersInMainBase . " " . $currentCourseCode . "</li>\n";

	// Select user of the course

	$sql = "SELECT u.* 
            FROM `" . $TABLEUSERS . "` as `u`, `" . $TABLECOURSUSER  . "` as `cu` 
            WHERE u.user_id=cu.user_id 
              AND cu.code_cours='". $currentCourseId . "'";

	$resUsers = claro_sql_query($sql);
	$nbFields = mysql_num_fields($resUsers);

	$csvInsertUsers = "";

	// creation of csv column title

	for($noField=0;$noField < $nbFields;$noField++)
	{
		$nameField = mysql_field_name($resUsers,$noField);
        if ($nameField != "password") {
    		$csvInsertUsers .= "'" . addslashes($nameField) . "';";
        }
	}

	// creation of csv data

	while($users=mysql_fetch_array($resUsers))
	{
		$csvInsertUsers .= "\n";

		for($noField=0;$noField < $nbFields;$noField++)
		{
			$nameField=mysql_field_name($resUsers,$noField);
            if ($nameField != "password") {
    			$csvInsertUsers .= "'" . addslashes($users[$nameField]) . "';";
            }
		}
	}

    if ($verboseBackup) 
    {
    	echo "<p style='color: green'>" . claro_parse_user_text($csvInsertUsers). "<p>\n";
    }

    // add to config

	$stringConfig .= "\n" . 
                     "\n" .
                     "# CSV of Users\n" . 
                     "#------------------------------------------\n" . 
                     $csvInsertUsers . "\n" .
                     "#------------------------------------------\n";
    
    // write to file

	$fuserscsv=fopen($userCSVFile,"w");
	fwrite($fuserscsv, $csvInsertUsers);
	fclose($fuserscsv);

// ********************************************************************
//  copy course's files
// ********************************************************************

	echo "<li>" . $langCopyDirectoryCourse ;

    // copy course's files

    copyDirTo($coursePath, $dirHtml, false);

	if($verboseBackup)
	{
		echo "<p style='color: green'>copyDirTo($coursePath, $dirHtml) </p> " ;
	}
    else 
    {
        echo "</li>\n";
    }

	$stringConfig .= "\n".
                     "\n".
                     $nbFiles." files were in ".$coursePath."/ \n" ;

// ********************************************************************
//  copy course database
// ********************************************************************

	echo "<li>" . $langBackupOfDataBase . " " . $currentCourseDbName . " (SQL)";

    backupDatabase($currentCourseDbName, true, true, 'SQL', $dirCourseBase, true, $verboseBackup);

// ********************************************************************
//  compress the archive
// ********************************************************************

	$fdesc=fopen($archiveFile, "w");
	fwrite($fdesc,$stringConfig);
	fclose($fdesc);

	echo "</li>\n" . 
         "<li>" . $langBuildTheCompressedFile . "</li>\n" . 
         "</ol>\n";


	if(extension_loaded('zlib'))
	{
		$zipCourse=new PclZip($archivePath . '/' . $archiveName);
		$zipCourse->create($archiveCoursePath,PCLZIP_OPT_REMOVE_PATH,$archivePath);
		removeDir($archiveCoursePath);
        $message .= $langBackupSuccesfull . " - <a href=\"$rootWeb".$archiveDirName."/$archiveName\">" . $langDownload . "</a>";
	}

}	// if allowed to backup

// **************************************************************************************

elseif(!$isAllowedToBackUp) //  not allowed to backup
{
	echo $langNotAllowed;
}
else
{
    $message .= "<p>" . $langConfirmBackup . " &quot;" . $currentCourseName . "&quot; (" . $currentCourseCode . ") ?" . "</p>\n" ;
    $message .= "<p>" . "<a href=\"".$_SERVER['PHP_SELF']."?confirmBackup=yes\">$langYes</a>" . "&nbsp;|&nbsp;" . "<a href=\"infocours.php\">$langNo</a>" . "</p>";


}
    
if ($message)
{
   claro_disp_message_box($message);
}

@include($includePath."/claro_init_footer.inc.php");

// *******************************
// * FUNCTIONS					
// *******************************

// **
// * Backup a db to a file
// *
// * @param string	$db_name		nom de la base de data
// * @param boolean	$structure		true => sauvegarde de la structure des tables
// * @param boolean	$data		true => sauvegarde des donnes des tables
// * @param boolean	$format			format des data
// 									'INSERT' => des clauses SQL INSERT
//									'CSV' => data separees par des virgules
// * @param boolean	$insertComplet	true => clause INSERT avec nom des champs
// * @param boolean	$verbose 		true => comment are printed
// *

function backupDatabase($db_name, $structure, $data, $format="SQL", $whereSave=".", $insertComplet="", $verbose=false)
{
	global $singleDbEnabled, $currentCourseDbNameGlu;

	if(!$singleDbEnabled)
	{
		if(!@mysql_select_db($db_name))
		{
			return false;
		}
	}

	$filename = $whereSave.'/courseDbContent.'.strtolower($format);

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

		if($data == true)
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

?>
