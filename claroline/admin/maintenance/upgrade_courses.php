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
$langFile = "upgrade";
require '../../inc/claro_init_global.inc.php';

// force upgrade for debug
if ($HTTP_GET_VARS["forceUpgrade"])
	$versionDb = md5 (uniqid (rand())); // for debug

// Variables
$totalNbError = 0;

$is_allowedToEdit 	= $is_platformAdmin || $PHP_AUTH_USER;
if (!$is_allowedToEdit)
{
 die ('Not Allowed');
}

$db = mysql_connect($dbHost, $dbLogin, $dbPass);

// count courses upgraded

$sqlNbCourses = "SELECT count(*) as nb FROM ".$mainDbName.".cours 
		where versionDb = '".$versionDb."' ";
$res_NbCourses = mysql_query($sqlNbCourses);
$nbCoursesUpgraded = mysql_fetch_array($res_NbCourses);

$sqlNbCourses = "SELECT count(*) as nb FROM ".$mainDbName.".cours";
$res_NbCourses = mysql_query($sqlNbCourses);
$nbCourses = mysql_fetch_array($res_NbCourses);

$sqlNbCourses = "SELECT count(*) as nb FROM ".$mainDbName.".cours
		where versionDb = 'error';";
$res_NbCourses = mysql_query($sqlNbCourses);
$nbCoursesError = mysql_fetch_array($res_NbCourses);

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
 "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
 
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">

<head>
  <title>-- Claroline upgrade -- version <?php echo $clarolineVersion ?></title>  

<?php

if ( $display==DISPLAY_RESULT_PANEL && ($nbCoursesUpgraded['0'] + $nbCoursesError['0'] )< $nbCourses['0'])
{
	$refresh_time = 20;
	echo "<meta http-equiv=\"refresh\" content=\"". $refresh_time  ."\" />\n";
}

?>

  <meta http-equiv="Content-Type" content="text/HTML; charset=iso-8859-1"  />
  <link rel="stylesheet" type="text/css" href="upgrade.css" media="screen" />
  <style media="print" >
    .notethis {	border: thin double Black;	margin-left: 15px;	margin-right: 15px;}
  </style>
</head>
<body bgcolor="white" dir="<?php echo $text_dir ?>">

<center>

<table cellpadding="10" cellspacing="0" border="0" width="650" bgcolor="#E6E6E6">
<tbody>
<tr bgcolor="navy">
<td valign="top" align="left">
<div id="header">
<?php
 echo sprintf("<h1>Claroline (%s) - upgrade</h1>",$clarolineVersion);
?>
</div>
</td>
</tr>
<!--
<div id="menu">
<?php
 echo sprintf("<p><a href=\"upgrade.php\">%s</a> - %s</p>", "upgrade", $langStep3);
?>
</div>
-->
<tr valign="top" align="left">
<td>
<div id="content">
<?php 

switch ($display)
{
	case DISPLAY_WELCOME_PANEL :
        
                echo sprintf ("<h2>%s</h2>",$langStep3);
                echo $langIntroStep3;
                echo sprintf($langNbCoursesUpgraded, $nbCoursesUpgraded['nb'],$nbCourses['nb']);
		echo "<center>" . sprintf ($langLaunchStep3, $PHP_SELF."?cmd=run") . "</center>";
		break;
                
	case DISPLAY_RESULT_PANEL : 

                echo sprintf ("<h2>%s</h2>",$langStep3);
                
                echo $langIntroStep3Run;

		// course upgraded

                echo sprintf($langNbCoursesUpgraded, $nbCoursesUpgraded['nb'],$nbCourses['nb']);

		// list of course upgraded failed

		$sqlNbCourses = "SELECT code FROM ".$mainDbName.".cours 
		         where versionDb = 'error' ";
		$res_listCoursesError = mysql_query($sqlNbCourses);
		if (mysql_num_rows($res_listCoursesError))
		{
			echo "<p  class=\"error\">Upgrade failed for course(s) ";
			while ($cours = mysql_fetch_array($res_listCoursesError))
			{
				echo $cours['code'] . " ";	
			}
			echo "- You can <a href=\"" . $PHP_SELF . "?cmd=run&upgradeCoursesError=1\">retry to upgrade</a> these courses</p>\n";
			
		}
                flush();
                
		// display refresh

		echo "<div class=\"help\" id=\"refreshIfBlock\">";
		echo "<p>Few seconds after the load of the page<sup>*</sup>, the <em>Claroline Upgrade tool</em> will automatically continue its job. If it doesn't, click yourself on the button below.</p>";
		echo "<p style=\"text-align: center\">" ;
		echo sprintf ("<button onclick=\"document.location='%s';\">Continue courses data upgrade</button>", $PHP_SELF."?cmd=run");
		echo "</p>";
		echo "<p><small>(*) see in the status bar of your browser.</small></p>";
		echo "</div>"; 
                flush();

		// select course to upgrade

		$sqlListCourses = " SELECT *, " .
				  " cours.dbName dbName, cours.code sysCode, cours.fake_code officialCode, directory coursePath ".
		                  " FROM ".$mainDbName.".cours ";
		
		/*		  
		if (is_array($coursesToUpgrade))
		{
			$sqlListCourses .= "where code in ('".implode( "','", $coursesToUpgrade )."') and versionDb != '".$versionDb."' order by dbName";
		}
		*/

		if ($upgradeCoursesError == 1)
		{
			$sqlListCourses .= "where versionDb != '".$versionDb."' order by dbName";
		}
		else
		{
			$sqlListCourses .= "where versionDb != '".$versionDb."' and versionDb !='error' order by dbName";
		}
		$res_listCourses = mysql_query($sqlListCourses);
		
                $nbCourseUpgraded = $nbCoursesUpgraded['nb'];
                
		while ($cours = mysql_fetch_array($res_listCourses))
		{
			$currentCourseDbName	= $cours["dbName"];
			$currentcoursePathSys	= $coursesRepositorySys.$cours["coursePath"]."/";
			$currentcoursePathWeb	= $coursesRepositoryWeb.$cours["coursePath"]."/";
			$currentCourseIDsys	= $cours["sysCode"];
			$currentCourseCode	= $cours["officialCode"];
			$currentCourseDbNameGlu = $courseTablePrefix . $currentCourseDbName . $dbGlu; // use in all queries
			
			// initialise $sqlForUpdate
			unset($sqlForUpdate);
			@include("repairTables.sql.php");
			include("createBaseOfACourse.sql.php");
			
			@mysql_query ( "SET @currentCourseCode := '".$currentCourseIDsys."'");
		
			echo "<p><strong>".++$nbCourseUpgraded . ".</strong> " .
			        "Upgrading database of course <strong>".$currentCourseCode."</strong> - 
				DB Name : <em>".$currentCourseDbName."</em> - 
				course ID : <em>".$currentCourseIDsys."</em></p>\n";
				
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
			if (upgrade_tool_list($currentCourseDbNameGlu))
                        {
                            $nbError++;
                        }
                        
                        //upgrade index.php et course repository
                        if (!upgrade_course_repository($currentCourseIDsys,$currentcoursePathSys)) 
                        {
                            $nbError++;
                        }
                        
			// move link
			include("moveLink.php");	
                        
                        upgrade_question_picture($currentcoursePathSys, $currentCourseDbNameGlu);
                        
			
			if ($nbError>0)
			{
				echo "<p class=\"error\"><strong>".$nbError." errors found</strong></p>";
				$totalNbError += $nbError;
				// Error: update versionDB of course
				$sqlFlagUpgrade = " update ".$mainDbName.".cours
							set versionDb='error'
							where code = '".$currentCourseIDsys."';";				
				$res = @mysql_query($sqlFlagUpgrade);
				if (mysql_errno() > 0)
				{
					echo "<p class=\"error\">n° <strong>".mysql_errno()."</strong>: ".mysql_error()."</p>";
					echo "<p>" . $sqlFlagUpgrade . "</p>";
                                }
			}
			else
			{
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
                                
			$str_execution_time = sprintf("execution time for this course [%01.2f s] - total [%01.2f s].",$stepDuration,$totaltime);
			                        
                        if ($nbError==0)
                        {
                            echo "<p class=\"success\">Upgrade Ok - $str_execution_time</p>";
                        }
                        else 
                        {
                            echo "<p class=\"error\">Upgrade Failed - $str_execution_time</p>";
			    $nbError = 0;
                        }
			echo "<hr noshade=\"noshade\" />";           
                        flush();
		}
                
                $mtime = microtime();	$mtime = explode(" ",$mtime);	$mtime = $mtime[1] + $mtime[0];	$endtime = $mtime;	$totaltime = ($endtime - $starttime);

		$sqlNbCourses = "SELECT count(*) as nb FROM ".$mainDbName.".cours
			where versionDb = 'error';";
		$res_NbCourses = mysql_query($sqlNbCourses);
		$nbCoursesError = mysql_fetch_array($res_NbCourses);
		
		if ($totalNbError>0 || $nbCoursesError['0'])
		{
			if ($totalNbError>0 ) echo "<p class=\"error\">" . $totalNbError . " errors</p>";
			if ($nbCoursesError['0']>0 ) echo "<p class=\"error\">" . $nbCoursesError['0'] . " course(s) not upgraded.";
			echo "<p><a href=\"".$PHP_SELF."?verbose=true\">Retry</a></p>";
			$totalNbError += $nbError;
			$nbError = 0;
		}
		else
		{
			echo "<p class=\"success\">The claroline upgrade tool has successfullly upgrade all your platform courses</p>\n";
                        echo "<div align=\"right\">" . sprintf($langNextStep,"upgrade.php") . "</div>";
		}
			
		mysql_close();
                       
                echo "<script type=\"text/javascript\">\n";
                echo "document.getElementById('refreshIfBlock').style.visibility = \"hidden\"";
                echo "</script>";
                
		break;
	default : 
		echo "<p>nothing to do</p>\n";
}

?>

</div>

</td>
</tr>
</tbody>
</table>

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
				. " VALUES ('". $courseTool['id'] ."','" . $courseTool['id'] . "','" . $courseTool['def_rank'] . "','COURSE_ADMIN')";
// . " VALUES ('". $courseTool['id'] ."','" . $courseTool['id'] . "','" . $courseTool['def_rank'] . "','" . $courseTool['def_access'] . "')";
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
				. " where tool_id = '" . $tool['id']  . "' ";
		claro_sql_query($sql_update);
	
	}
 }
 
 // Add external tool
 $sql =  " SELECT rubrique, lien, visible FROM `".$dbNameGlu."accueil` " 
       . " WHERE addedTool = 'YES' and lien not like '%courseLog.ph%' ";
       
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
				. " VALUES ('" . $nb_tool . "','" . $access . "',\"" . addslashes($externalTool['lien']) . "\",\"" . addslashes($externalTool['rubrique']) . "\",'YES')";
		claro_sql_query($sql_insert);		
	}
 
 }

}

function upgrade_course_repository($courseID,$courseRepository)
{

    global $clarolineRepositorySys, $includePath;

    if (is_writable($courseRepository))
    {
        umask(0);

        /*
            create directory for new tools of claroline 1.5 
        */
    
        if ( !is_dir($courseRepository) ) mkdir($courseRepository, 0777);
        if ( !is_dir($courseRepository."/chat") ) mkdir($courseRepository."/chat", 0777);
        if ( !is_dir($courseRepository."/modules") ) mkdir($courseRepository."/modules", 0777);
        if ( !is_dir($courseRepository."/scormPackages") ) mkdir($courseRepository."/scormPackages", 0777);

        /*
            add $cidReq in index.php (Missing var in claroline 1.3)
        */

        // build index.php of course
        $fd=fopen($courseRepository."/index.php", "w");

        // str_replace() removes \r that cause squares to appear at the end of each line
        $string=str_replace("\r","","<?"."php
              \$cidReq = \"$courseID\";
              \$claroGlobalPath = \"$includePath\";
              include(\"".$clarolineRepositorySys."course_home/course_home.php\");
	?>");
        
        fwrite($fd, "$string");
        fclose($fd);
        $fd=fopen($courseRepository."/group/index.php", "w");
        $string="<"."?"."php"." session_start"."()"."; ?>";
        fwrite($fd, "$string");
	fclose($fd);        
        return 1;
    
    } else {
        printf ("repository %s not writable",$courseRepository);
        return 0;
    }

}

// Fill picture_name with id, previously implicit

function upgrade_question_picture($courseRepository, $dbNameGlu) {

    $quizRepository = $courseRepository . "/image/";

    $handle = opendir($quizRepository);
    
    while ($file = readdir($handle))
    {
	if ($file == '.' || $file == '..')
	{
            // Skip current and parent directories
            continue;
	}
	if(preg_match("/^quiz\-/",$file))
	{
            $fileList[] = $file;
	}
    }

    if (is_array($fileList)) 
    {
        // Add external tool
         $sql =  " SELECT id FROM `".$dbNameGlu."quiz_question` ";
         $result = claro_sql_query($sql);
         if (mysql_num_rows($result) > 0)
         {
            while ( $questions = mysql_fetch_array($result, MYSQL_ASSOC))
            {
                if ( in_array("quiz-".$questions['id'],$fileList) )
                {
                    $sql_update = "UPDATE `".$dbNameGlu."quiz_question` SET  `picture_name` = concat('quiz-',`id`) WHERE id = '" . $questions['id'] . "'";
                    claro_sql_query($sql_update);		
                }
            }
         }
    }
    
}



?>
