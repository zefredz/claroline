<?php // $Id$

/**
 * try to create database of course  without remove exitent content
 */

DEFINE ("DISPLAY_WELCOME_PANEL",1);
DEFINE ("DISPLAY_RESULT_PANEL",2);

if ($_REQUEST['cmd'] == 'run')
{
	$display = DISPLAY_RESULT_PANEL;
}
else 
{
	$display = DISPLAY_WELCOME_PANEL;
}

if (!function_exists(mysql_info)) {function mysql_info() {return "";}} // mysql_info is used in 

// get start time

$mtime = microtime();$mtime = explode(" ",$mtime);$mtime = $mtime[1] + $mtime[0];$starttime = $mtime;$steptime =$starttime;

// init language
$langFile = "admin";
include('../../inc/claro_init_global.inc.php');
$nameTools = $langCheckDatabase;

// force upgrade for debug
if ($HTTP_GET_VARS["forceUpgrade"])
	$versionDb = md5 (uniqid (rand())); // for debug

// Variables
$totalNbError = 0;
$nbCourseUpgraded = 0;

$is_allowedToEdit 	= $is_platformAdmin || $PHP_AUTH_USER;
if (!$is_allowedToEdit)
{
 die ('Not Allowed');
}

include("createBaseOfACourse.sql.php");
include("upgradeContentBaseOfACourse.sql.php");
$langUpgradeDataBase = "Upgrading Database ".$currentCourseID;

$db = mysql_connect($dbHost, $dbLogin, $dbPass);
$nameTools = $langUpgradeDataBase;


?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
 "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
 
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">

<head>
  <title>-- Claroline upgrade -- version <?php echo $clarolineVersion ?></title>  
  <meta http-equiv="Content-Type" content="text/HTML; charset=iso-8859-1"  />
  <link rel="stylesheet" type="text/css" href="upgrade.css" media="screen" />
  <style media="print" >
    .notethis {	border: thin double Black;	margin-left: 15px;	margin-right: 15px;}
  </style>
</head>
<body bgcolor="white" dir="<?php echo $text_dir ?>">

<div id="header">
<?php
 echo "<h1>Claroline upgrade -- version " . $clarolineVersion . "</h1>";
?>
</div>
<div id="menu">
<p><a href="upgrade.php">Upgrade</a> - Courses</p>
</div>

<div id="content">

<h2>Upgrading courses database</h2>

<?php 

switch ($display)
{
	case DISPLAY_WELCOME_PANEL :
		echo "<p><a href=\"". $PHP_SELF . "?cmd=run\">Launch Claroline courses upgrade</a></p>\n";
		echo "<p class=\"help\">Notice: Updating courses databases (It may take some time).</p>\n";
		break;
	case DISPLAY_RESULT_PANEL : 

		echo "<div class=\"help\" id=\"refreshIfBlock\">";
		echo "<form action=\"" . $PHP_SELF . "\">\n";
		echo "<input type=\"hidden\" name=\"cmd\" value=\"run\" />";
		echo "<h3>Notice: Updating courses databases (It may take some time).</h3>\n";
		echo "<p>Due to possible PHP restrictions on your server, the script may interrupt before ending, so that install does'nt work properly.<br />\nDon't Panic. You can fix it simply by refreshing your browser page as many time as required.<br />\n";
		echo "<input type=\"submit\" name=\"refresh\" value=\"Refresh\"></p>";
		echo "</form>";
		echo "</div>";

		$sqlListCourses = " SELECT *, " .
				  " cours.dbName dbName, cours.code sysCode, cours.fake_code officialCode, directory coursePath ".
		                  " FROM ".$mainDbName.".cours ";
				  
		if (is_array($coursesToUpgrade))
		{
			$sqlListCourses .= "where code in ('".implode( "','", $coursesToUpgrade )."') and versionDb != '".$versionDb."' order by dbName";
		}
		else
		{
			$sqlListCourses .= "where versionDb != '".$versionDb."' order by dbName";
		}
		$res_listCourses = mysql_query($sqlListCourses);
		
		while ($cours = mysql_fetch_array($res_listCourses))
		{
			$currentCourseID	= $cours["dbName"];
			$currentCourseDbName	= $cours["dbName"];
			$currentcoursePathSys	= $coursesRepositorySys.$cours["coursePath"]."/";
			$currentcoursePathWeb	= $coursesRepositoryWeb.$cours["coursePath"]."/";
			$currentCourseIDsys	= $cours["sysCode"];
			$currentCourseCode	= $cours["officialCode"];
			$currentCourseDbNameGlu = $courseTablePrefix . $currentCourseDbName . $dbGlu; // use in all queries
			
			// initialise $sqlForUpdate
			unset($sqlForUpdate);
			include("createBaseOfACourse.sql.php");
			@include("repairTables.sql.php");
		 	include("upgradeContentBaseOfACourse.sql.php");
			
			// move files
			include("moveVideo.php");		
			include("moveStat.php");	
			
			@mysql_query ( "SET @currentCourseCode := '".$currentCourseIDsys."'");
		
			echo "<p>".++$nbCourseUpgraded .
			        "Upgrading database of course <strong>".$currentCourseCode."</strong><br />\n
				DataBase Name : <em>".$currentCourseDbName."</em><br />\n
				internal Code : <em>".$currentCourseIDsys."</em></p>\n";
				
			echo "<ol>\n";
			
			$nbError = 0;
			reset($sqlForUpdate);
			while (list($key,$sqlTodo) = each($sqlForUpdate))
			{
				// Comment in $sqlForUpdate
				if ($sqlTodo[0] == "#")
				{
					if ($verbose)
					{
						echo "<p class=\"comment\">Comment: $sqlTodo </p>\n";
					}
				}
				else
				{
					$res = @mysql_query($sqlTodo);
					if ($verbose)
					{
						echo "<li>\n";
						echo "<p class=\"tt\"><strong>". $currentCourseDbName. ":</strong>" . $sqlTodo .  "</p>\n";
						echo "<p>".mysql_affected_rows()." affected rows <br />\n" .mysql_info() . "</p>\n";
						if (mysql_errno() > 0 )
						{
							echo "<p class=\"error\">n° <strong>".mysql_errno().":</strong> ".mysql_error()."</p>\n";
						}
						echo "</li>\n";
					}
					if (mysql_errno() > 0 && mysql_errno() != 1060  && mysql_errno() != 1050  && mysql_errno() != 1017  && mysql_errno() != 1062 && mysql_errno() != 1065 && mysql_errno() != 1146  )
					{
						++$nbError;
						echo "<p class=\"error\">";
						echo "<strong>".($nbError)."</strong> ";
						echo "<strong>n°: ".mysql_errno()."</strong> : " . mysql_error()."";
						echo $currentCourseDbName . ":" . $sqlTodo;
						echo "</p>";
					}
				}
			}
			echo "</ol>";
		
			// upgrade tool list
			upgrade_tool_list($currentCourseDbNameGlu);
			
			if ($nbError>0)
			{
				echo "<p class=\"error\"><strong>".$nbError." errors found</strong></p>";
				$totalNbError += $nbError;
				$nbError = 0;
			}
			else
			{
				echo "<p class=\"success\">Ok</p>";
				echo "<hr noshade=\"noshade\" />";
				
				// Success: update versionDB of course
				$sqlFlagUpgrade = " update ".$mainDbName.".cours
							set versionDb='".$versionDb."'
							where code = '".$currentCourseIDsys."';";				
				$res = @mysql_query($sqlFlagUpgrade);
				if (mysql_errno() > 0)
				{
					echo "<p class=\"error\">n° <strong>".mysql_errno()."</strong>: ".mysql_error()."</p>";
					echo "<p>" . $sqlFlagUpgrade . "</p>";
					}
				}
				$mtime = microtime();
				$mtime = explode(" ",$mtime);
				$mtime = $mtime[1] + $mtime[0];
				$endtime = $mtime;
				$totaltime = ($endtime - $starttime);
				$stepDuration = ($endtime - $steptime);
				$steptime = $endtime;
				echo "<p class=\"microtime\">";
				printf("execution time for this courses[%01.3f ms] - total [%01.2f s].",$stepDuration*1000,$totaltime);
				echo "</p>";
			}
		
			$mtime = microtime();	$mtime = explode(" ",$mtime);	$mtime = $mtime[1] + $mtime[0];	$endtime = $mtime;	$totaltime = ($endtime - $starttime);
		
			echo "<hr noshade=\"noshade\" />";
			if ($totalNbError>0)
			{
				echo "<p class=\"error\">" . $totalNbError . " " . $langErrorsFound . "</p>";
				echo "<p><a href=\"".$PHP_SELF."?verbose=true\">Retry</a></p>";
				$totalNbError += $nbError;
				$nbError = 0;
			}
			else
			{
				echo "<p class=\"success\">Ok</p>\n";
				echo "<p><a href=\"upgrade.php\">Next</a></p>\n";
			}
			
			mysql_close();
		break;
	default : 
		echo "<p>nothing to do</p>\n";
}

?>

<script type="text/javascript">
 document.getElementById('refreshIfBlock').style.visibility = "hidden";
</script>

</div>

</body>
</html>

<?php

//***********************************************************************

function dir_empty($pathToCheck)
{
	if (is_dir($pathToCheck))
	{
		$handle = opendir($pathToCheck);
		while ($file = readdir($handle)) 
		{
	    	if ($file != "." && $file != "..") 
			{
				closedir($handle);
	        	return false;
		    }
		}
		closedir($handle);
	}
	return true;
}

/**
 * return 
 * array with  
 * - string : HTTP code (200, 404, 403, ...)
 * - string : header content
 */
 
 
function checkurl($url)
{
	if (!eregi("^http://", $url)) 
	{
		return FALSE;
	}
	$details = parse_url($url);
	if (!isset($details['port'])) 
	{
		$details['port'] = "80";
	}
	if (!isset($details['path']))
	{
		$details['path'] = "/";
	}
	if (!ereg("[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+", $details['host']))
	{
		$details['host'] = gethostbyname($details['host']);
	}
	if (	
		$sock = fsockopen( $details['host'], $details['port'],
		$numero_erreur, $texte_erreur)
		)
	{
		$requete = "GET ".$details['path']." HTTP/1.1\r\n";
		$requete .= "Host: ".$details['host']."\r\n\r\n";
		
		fputs($sock, $requete);
		$str = fgets($sock, 1024);
		while(!ereg('^HTTP/1.1 ', $str))
		{
			$str = fgets($sock, 1024);
		}
		fclose($sock);
		list($http, $str, $texte) = explode(" ", $str, 3);
		return array($str, $reponse[$str]);
	}
	return FALSE;
}

function upgrade_tool_list ($dbNameGlu)
{

 global $mainDbName;

 $TABLECOURSETOOL = $mainDbName.'`.`course_tool';
 $nb_tool = 0;
 
 // Fill tool_list with default value from course_tool of main db
 $sql = "SELECT id, def_access, def_rank, claro_label FROM   `". $TABLECOURSETOOL . "` where add_in_course = 'AUTOMATIC'";
 $result = claro_sql_query($sql);
		
 if (mysql_num_rows($result) > 0)
 {
	while ( $courseTool = mysql_fetch_array($result, MYSQL_ASSOC))
	{
		$sql_insert = " INSERT IGNORE INTO `".$dbNameGlu."tool_list` " 
				. " (id,tool_id, rank, access) " 
				. " VALUES ('". $courseTool['id'] ."','" . $courseTool['id'] . "','" . $courseTool['def_rank'] . "','" . $courseTool['def_access'] . "')";
		claro_sql_query($sql_insert);
		$nb_tool++;
	}
 }
 
 // Set access of internal tool_list
 $sql =  " SELECT C.id, A.rubrique, A.lien, A.visible "
       . " FROM `".$dbNameGlu."accueil` A, `". $TABLECOURSETOOL . "` C  " 
       . " WHERE A.addedTool = 'NO' and A.lien = C.script_url";
       
 $result = claro_sql_query($sql);
 if (mysql_num_rows($result) > 0)
 {
	while ( $tool = mysql_fetch_array($result, MYSQL_ASSOC))
	{
		// set access
		// visible4all = 1, visible4AdminCourse = 0, visible4AdminClaroline = 2;
		
		$visible = $tool['visible'];
		
		if ($visible == 1) $access='ALL';
		elseif ($visible == 0) $access='COURSE_ADMIN';
		else $access='PLATFORM_ADMIN';
		
		$sql_update = " UPDATE `".$dbNameGlu."tool_list` " 
				. " set access = '" . $access ."' "
				. " where tool_id = '" . $tool[id]  . "' ";
		claro_sql_query($sql_update);
	
	}
 }
 
 // Add external tool
 $sql =  " SELECT rubrique, lien, visible FROM `".$dbNameGlu."accueil` " 
       . " WHERE addedTool = 'YES' ";
       
 $result = claro_sql_query($sql);
 if (mysql_num_rows($result) > 0)
 {

	while ( $externalTool = mysql_fetch_array($result, MYSQL_ASSOC))
	{
		// set access
		// visible4all = 1, visible4AdminCourse = 0, visible4AdminClaroline = 2;
		
		$visible = $externalTool['visible'];
		
		if ($visible == 1) $access='ALL';
		elseif ($visible == 0) $access='COURSE_ADMIN';
		else $access='PLATFORM_ADMIN';

		$nb_tool++;	
		$sql_insert = " INSERT INTO `".$dbNameGlu."tool_list` " 
		                . " (rank,access,script_url,script_name, addedTool) "
				. " VALUES ('" . $nb_tool . "','" . $access . "','" . $externalTool['lien'] . "','" . $externalTool['rubrique'] . "','YES')";
		claro_sql_query($sql_insert);		
	}
 
 }

}

?>
