<?php // $Id$
/*
+----------------------------------------------------------------------+
| CLAROLINE 1.6
+----------------------------------------------------------------------+
| Copyright (c) 2001, 2004 Universite catholique de Louvain (UCL)      |
+----------------------------------------------------------------------+
*/

// Lang file

// Include global and libs
require '../inc/claro_init_global.inc.php';

if(extension_loaded('zlib'))
{
	include($includePath.'/lib/pclzip/pclzip.lib.php');
}
@include($includePath."/lib/debug.lib.inc.php");
include($includePath."/lib/fileManage.lib.php");

// Courses variables

// Tables variables

$tbl_mdb_names   = claro_sql_get_main_tbl();
$TBL_COURS       = $tbl_mdb_names['course'           ];
$TBL_COURS_USER  = $tbl_mdb_names['rel_course_user'  ];

// Path and files variables

$archivePath=$rootSys.$archiveDirName.'/';


$isAllowedToRestore=$is_allowedCreateCourse;
if(!$isAllowedToRestore) claro_disp_auth_form();

// execute 

if($submitForm && $isAllowedToRestore) // if the form has been sent and if the user is allowed to restore a course
{
	// prevent hacking
	$archiveFile=str_replace(array('/','\\','..'),'',$archiveFile);

	if(empty($archiveFile) || !strstr($archiveFile,'.zip'))
	{
		$msgErr=$langNoArchive;
	}
	elseif(!file_exists($archivePath.$archiveFile))
	{
		$msgErr=$langArchiveNotFound;
	}
	else
	{
		list(,$courseId)=explode('.',$archiveFile);

		// if an archive for this course has already been extracted, remove the directory and its subdirectories
		if(is_dir($archivePath.$courseId))
		{
			removeDir($archivePath.$courseId);
		}

		// create the directory for extracting the archive
		@mkdir($archivePath.$courseId,0770);

		$zipCourse=new PclZip($archivePath.$archiveFile);

		// go to the created directory
		chdir($archivePath.$courseId);

		// extract the archive
		$zipCourse->extract(PCLZIP_OPT_REMOVE_PATH,$archivePath.$courseId);

		// read query to insert course data into the main database
		list($courseSQL)=file($archivePath.$courseId.'/mainBase/cours.sql');

		claro_sql_query($courseSQL);

		$sql="SELECT directory,dbname FROM `$TBL_COURS` WHERE code='$courseId'";
		$result=claro_sql_query($sql);

		list($coursePath,$courseDbName)=mysql_fetch_row($result);

		$sql="INSERT INTO `$TBL_COURS_USER`(code_cours,user_id,statut,role,team,tutor)
		      VALUES('$courseId','$_uid','1','$langProfessor','0','1')";
		claro_sql_query($sql);

		// create the course DB only in multiple DB
		if(!$singleDbEnabled)
		{
			$sql="DROP DATABASE IF EXISTS `$courseDbName`";
			claro_sql_query($sql);

			$sql="CREATE DATABASE `$courseDbName`";
			claro_sql_query($sql);

			mysql_select_db($courseDbName);
		}

		$courseSQL=file($archivePath.$courseId.'/courseBase/courseDbContent.sql');

		$courseSQL=implode('',$courseSQL);

		$queries=array();

		PMA_splitSqlFile($queries,$courseSQL);

		foreach($queries as $sql)
		{
			$sql=trim($sql);

			if(!empty($sql))
			{
				claro_sql_query($sql);
			}
		}

		$hiddenPath=str_replace('.zip','',$archiveFile);

		if($singleDbEnabled)
		{
			$TBL_DOCUMENT=$mainDbName.'`.`'.$courseTablePrefix.$coursePath.$dbGlu.'document';
		}
		else
		{
			$TBL_DOCUMENT=$mainDbName.$courseTablePrefix.$dbGlu.'document';
		}

		$sql="INSERT INTO `$TBL_DOCUMENT`(path,visibility,comment)
		      VALUES('/$hiddenPath','i','$langArchive ".date('Y-m-d',filemtime($archivePath.$archiveFile))."'),
				    ('/$hiddenPath/users.csv','i','')";
		claro_sql_query($sql);

		if(is_dir($rootSys.$coursePath))
		{
			mkPath($garbageRepositorySys);
			@rename($rootSys.$coursePath,$garbageRepositorySys.$coursePath.'_'.time());
		}

		// copy the course directory
		@rename($archivePath.$courseId.'/html',$rootSys.$coursePath);

		// creates empty directories
		if(!is_dir($rootSys.$coursePath.'/document'))	{	@mkdir($rootSys.$coursePath.'/document',0770);	}
		if(!is_dir($rootSys.$coursePath.'/group'))		{	@mkdir($rootSys.$coursePath.'/group',0770);		}
		if(!is_dir($rootSys.$coursePath.'/image'))		{	@mkdir($rootSys.$coursePath.'/image',0770);		}
		if(!is_dir($rootSys.$coursePath.'/page'))		{	@mkdir($rootSys.$coursePath.'/page',0770);		}
		if(!is_dir($rootSys.$coursePath.'/work'))		{	@mkdir($rootSys.$coursePath.'/work',0770);		}

		// create the directory for copying the users.csv file
		@mkdir($rootSys.$coursePath.'/document/'.$hiddenPath,0770);

		// copy the users.csv file into the Documents tool
		@copy($archivePath.$courseId.'/mainBase/users.csv',$rootSys.$coursePath.'/document/'.$hiddenPath.'/users.csv');

		// remove the extracted archives
		removeDir($archivePath.$courseId);

		$msgErr=$langArchiveUncompressed.'<br><br>'.$langCsvPutIntoDocTool;
	}
}

$nameTools=$langRestoreCourse;

$interbredcrump[]=array("url" => "../create_course/add_course.php","name" => $langCreateSite);

@include($includePath.'/claro_init_header.inc.php');
?>

<h3>
  <?php echo $nameTools; ?>
</h3>

<table border="0" align="center" cellpadding="0" cellspacing="0" width="100%">
<tr>
  <td>

<?php echo $langRestoreDescription; ?><br><br>

	<b><?php echo $langNotice; ?> :</b> <?php echo $langRestoreNotice; ?><br><br>

<?php
	// if there is a message
	if(!empty($msgErr))
	{
?>

	<table border="0" cellpadding="3" width="400" bgcolor="#FFCC00">
	<tr>
	  <td><?php echo $msgErr; ?></td>
	</tr>
	</table>

<?php
	}
?>

	<form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
	<input type="hidden" name="submitForm" value="1">

	<label for="archiveFile"><?php echo $langAvailableArchives; ?></label> :

	<select name="archiveFile" id="archiveFile">
	<option value="">---</option>

<?php
	if($dir=@opendir($archivePath))
	{
		while($file=readdir($dir))
		{
			if(strstr($file,'.zip'))
			{
				list(,$courseId)=explode('.',$file);

				$archiveInfos=$langCourse.' &quot;'.$courseId.'&quot; ('.date('Y-m-d H:i:s',filemtime($archivePath.$file)).')';

				echo '<option value="'.$file.'">'.$archiveInfos.'</option>';
			}
		}

		closedir($dir);
	}
?>

	</select>

	<br><br>

	<input type="submit" value="<?php echo $langRestore; ?>">

	</form>

  </td>
</tr>
</table>

<?php
include($includePath."/claro_init_footer.inc.php");

/**
 * Removes comment lines and splits up large sql files into individual queries
 *
 * Last revision: September 23, 2001 - gandon
 *
 * @param   array    the splitted sql commands
 * @param   string   the sql commands
 * @param   integer  the MySQL release number (because certains php3 versions
 *                   can't get the value of a constant from within a function)
 *
 * @return  boolean  always true
 *
 * @access  public
 */
function PMA_splitSqlFile(&$ret, $sql)
{
    // do not trim, see bug #1030644
    //$sql          = trim($sql);
    $sql          = rtrim($sql, "\n\r");
    $sql_len      = strlen($sql);
    $char         = '';
    $string_start = '';
    $in_string    = FALSE;     $nothing      = TRUE;
    $time0        = time();

    for ($i = 0; $i < $sql_len; ++$i) {
        $char = $sql[$i];

        // We are in a string, check for not escaped end of strings except for
        // backquotes that can't be escaped
        if ($in_string) {
            for (;;) {
                $i         = strpos($sql, $string_start, $i);
                // No end of string found -> add the current substring to the
                // returned array
                if (!$i) {
                    $ret[] = $sql;
                    return TRUE;
                }
                // Backquotes or no backslashes before quotes: it's indeed the
                // end of the string -> exit the loop
                else if ($string_start == '`' || $sql[$i-1] != '\\') {
                    $string_start      = '';
                    $in_string         = FALSE;
                    break;
                }
                // one or more Backslashes before the presumed end of string...
                else {
                    // ... first checks for escaped backslashes
                    $j                     = 2;
                    $escaped_backslash     = FALSE;
                    while ($i-$j > 0 && $sql[$i-$j] == '\\') {
                        $escaped_backslash = !$escaped_backslash;
                        $j++;
                    }
                    // ... if escaped backslashes: it's really the end of the
                    // string -> exit the loop
                    if ($escaped_backslash) {
                        $string_start  = '';
                        $in_string     = FALSE;
                        break;
                    }
                    // ... else loop
                    else {
                        $i++;
                    }
                } // end if...elseif...else
            } // end for
        } // end if (in string)

        // lets skip comments (/*, -- and #)
        else if (($char == '-' && $sql_len > $i + 2 && $sql[$i + 1] == '-' && $sql[$i + 2] <= ' ') || $char == '#' || ($char == '/' && $sql_len > $i + 1 && $sql[$i + 1] == '*')) {
            $i = strpos($sql, $char == '/' ? '*/' : "\n", $i);
            // didn't we hit end of string?
            if ($i === FALSE) {
                break;
            }
            if ($char == '/') $i++;
        }

        // We are not in a string, first check for delimiter...
        else if ($char == ';') {
            // if delimiter found, add the parsed part to the returned array
            $ret[]      = array('query' => substr($sql, 0, $i), 'empty' => $nothing);
            $nothing    = TRUE;
            $sql        = ltrim(substr($sql, min($i + 1, $sql_len)));
            $sql_len    = strlen($sql);
            if ($sql_len) {
                $i      = -1;
            } else {
                // The submited statement(s) end(s) here
                return TRUE;
            }
        } // end else if (is delimiter)

        // ... then check for start of a string,...
        else if (($char == '"') || ($char == '\'') || ($char == '`')) {
            $in_string    = TRUE;
            $nothing      = FALSE;
            $string_start = $char;
        } // end else if (is start of string)

        elseif ($nothing) {
            $nothing = FALSE;
        }

        // loic1: send a fake header each 30 sec. to bypass browser timeout
        $time1     = time();
        if ($time1 >= $time0 + 30) {
            $time0 = $time1;
            header('X-pmaPing: Pong');
        } // end if
    } // end for

    // add any rest to the returned array
    if (!empty($sql) && preg_match('@[^[:space:]]+@', $sql)) {
        $ret[] = array('query' => $sql, 'empty' => $nothing);
    }

    return TRUE;
} // end of the 'PMA_splitSqlFile()' function

?>
