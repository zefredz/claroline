<?php // $Id$
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.4.* $Revision$                            |
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2003 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
      |   This program is free software; you can redistribute it and/or      |
      |   modify it under the terms of the GNU General Public License        |
      |   as published by the Free Software Foundation; either version 2     |
      |   of the License, or (at your option) any later version.             |
      |                                                                      |
      |   This program is distributed in the hope that it will be useful,    |
      |   but WITHOUT ANY WARRANTY; without even the implied warranty of     |
      |   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the      |
      |   GNU General Public License for more details.                       |
      |                                                                      |
      |   You should have received a copy of the GNU General Public License  |
      |   along with this program; if not, write to the Free Software        |
      |   Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA          |
      |   02111-1307, USA. The GNU GPL license is also available through     |
      |   the world-wide-web at http://www.gnu.org/copyleft/gpl.html         |
      +----------------------------------------------------------------------+
      | Authors: Thomas Depraetere <depraetere@ipm.ucl.ac.be>                |
      |          Hugues Peeters    <peeters@ipm.ucl.ac.be>                   |
      |          Christophe Gesché <gesche@ipm.ucl.ac.be>                    |
      +----------------------------------------------------------------------+
 */

/**
 * try to create database of course  without remove exitent content
 */
if (!function_exists(mysql_info)) {function mysql_info() {return "";}} // mysql_info is used in 
 
$mtime = microtime();$mtime = explode(" ",$mtime);$mtime = $mtime[1] + $mtime[0];$starttime = $mtime;$steptime =$starttime;

$langFile = "admin";
@include('../../inc/claro_init_global.inc.php');

if ($HTTP_GET_VARS["forceUpgrade"])
	$versionDb = md5 (uniqid (rand())); // for debug

$nameTools = $langCheckDatabase;
$TotalnbError = 0;

//$TABLEAGENDA 		= $_course["dbName"]."`.`agenda";
$is_allowedToEdit 	= $is_platformAdmin || $PHP_AUTH_USER;
if ($is_allowedToEdit)
{

//	@include($rootAdminSys."/checkIfHtAccessIsPresent.php");
	include("createBaseOfACourse.sql.php");
	include("upgradeContentBaseOfACourse.sql.php");
	$langUpgradeDataBase = "Upgrading Database ".$currentCourseID;

	$db = mysql_connect("$mysqlServer", "$mysqlUser", "$mysqlPassword");
	$nameTools = $langUpgradeDataBase;

	$up_link ="checkCourseDatabase.php";

	//$interbredcrump[]= array ("url"=>"index.php", "name"=> $langAdmin);
	//@include($includePath."/claro_init_header.inc.php");
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">

<html>
<head>
	<TITLE>
      -- Claroline upgrade -- version <?php echo $clarolineVersion ?>
	</TITLE>
<style type="text/css">
<!--
body,h1,h2,h3,h4,h5,h6,p,blokquote,td,ol,ul {font-family: Arial, Helvetica, sans-serif; }
-->
</style>
<Style  media="print" >
<!--
.notethis {	border: thin double Black;	margin-left: 15px;	margin-right: 15px;}
-->
</style>

</head>
<body bgcolor="white" dir="<?php echo $text_dir ?>" onload="javascript:refreshIfBlock.style.visibility='hidden';">
<div align="center">
<table cellpadding="6" cellspacing="0" border="0" width="650" bgcolor="#E6E6E6" id="cadre">
	<tr bgcolor="navy">
		<td valign="top">
			<font color="white" face="arial, helvetica">
				-- Claroline upgrade -- version <?php echo $clarolineVersion ?>
			</font>
		</td>
	</tr>
	<tr bgcolor="#E6E6E6">
		<td>
			<div align="center" name="dv1">
				<div id="refreshIfBlock">
						Updating courses databases (It may take some time).
						<blockquote>
							<small>
								<strong>Notice.</strong>
								Due to possible PHP restrictions on your server, the script may interrupt before ending, so that install does'nt work properly.
								Don't Panic. You can fix it simply by refreshing your browser page as many time as required.
							</small>
						</blockquote>
						<div align="center">
<form action="<?php echo $PHP_SELF?>">
							<input type="submit" name="refresh" value="Refresh">
</form>
						</div>
				</div>
			</div>
<?php 
	$sqlListCourses = "SELECT *, cours.dbName dbName, cours.code sysCode, cours.fake_code officialCode, directory coursePath FROM ".$mainDbName.".cours ";
	if (is_array($coursesToUpgrade))
	{
		$sqlListCourses .= "where code in ('".implode( "','", $coursesToUpgrade )."') and versionDb != '".$versionDb."' order by dbName";
	}
	else
	{
		$sqlListCourses .= "where versionDb != '".$versionDb."' order by dbName";
	}
	//echo $sqlListCourses;
	$res_listCourses = mysql_query($sqlListCourses);
	$nbCourseUpgraded=0;
	while ($cours = mysql_fetch_array($res_listCourses))
	{
		$currentCourseID		= $cours["dbName"];
		$currentCourseDbName	= $cours["dbName"];
		$currentcoursePathSys		= $coursesRepositorySys.$cours["coursePath"]."/";
		$currentcoursePathWeb		= $coursesRepositoryWeb.$cours["coursePath"]."/";
		$currentCourseIDsys		= $cours["sysCode"];
		$currentCourseCode		= $cours["officialCode"];
		//mysql_select_db("$currentCourseDbName",$db);
		$currentCourseDbNameGlu         = $courseTablePrefix . $currentCourseDbName . $dbGlu; // use in all queries

		
		unset($sqlForUpdate);
		include("createBaseOfACourse.sql.php");
		@include("repairTables.sql.php");
 		include("upgradeContentBaseOfACourse.sql.php");
		
		include("moveVideo.php");		
		include("moveStat.php");	
		
		if (isset ($cours["cahier_charges"]))
		{
			$currentCourseProgram	= $cours["cahier_charges"];
			include("moveCourseProgram.php");
		}

		@mysql_query ( "SET @currentCourseCode := '".$currentCourseIDsys."'");

		echo "
			<br>
			".++$nbCourseUpgraded."
			Upgrading database of course <strong>".$currentCourseCode."</strong>
			<small><small>DB Name : <em>".$currentCourseDbName."</em>
			internal Code : <em>".$currentCourseIDsys."</em></small></small>
			<OL>";
		$nbError =0;
		reset($sqlForUpdate);
		while (list($key,$sqlTodo) = each($sqlForUpdate))
		{
			if ($sqlTodo[0] == "#")
			{
				if ($verbose)
				{
					echo "
		<BR>
		<BR>
		<font color=blue>
			-------------  $sqlTodo -------------
		</font>
		<br>";	}
			}
			else
			{
				$res = @mysql_query($sqlTodo);
				if ($verbose)
				{
					echo "
				<LI class=\"verbose\">
					<u>$currentCourseDbName</u> : <Small><TT>$sqlTodo</TT></Small>
					<BR>
					".mysql_affected_rows()." affected rows
					<br>
					".mysql_info ();
					if (mysql_errno() > 0 )
					{
						echo "
					<br>
					<font color=red >
						n° <u>".mysql_errno()."</u> : ".mysql_error()."
					</font>";
					}
					echo "
				</LI>";
				}
				if (mysql_errno() > 0 && mysql_errno() != 1060  && mysql_errno() != 1062 && mysql_errno() != 1065 && mysql_errno() != 1146  )
				{
					++$nbError;
					echo "
					<BR>
						<font color=red>
							<strong>".($nbError)."</strong> n° <u>".mysql_errno()."</u> : ".mysql_error()."
						</font><br>
						$currentCourseDbName : $sqlTodo<br>";
				}
			}
		}
	echo "
		</OL>";

		if ($nbError>0)
		{
			echo "
				<font color=\"red\">
					<strong>".$nbError." errors found</strong>
				</font>
				";
			$TotalnbError += $nbError;
			$nbError = 0;
		}
		else
		{
			echo "
				<font color=green>
					Ok
				</font>
				<hr align=\"left\" width=\"30%\" color=\"#0000ff\" size=\"1\" noshade>
				";
			$sqlFlagUpgrade = "
	update ".$mainDbName.".cours
		set versionDb='".$versionDb."'
		where code = '".$currentCourseIDsys."';";
			$res = @mysql_query($sqlFlagUpgrade);
			if (mysql_errno() > 0)
			{
				echo "
				<BR>
					<font color=red>
						n° <u>".mysql_errno()."</u> : ".mysql_error()."
					</font>
					<br>".$sqlFlagUpgrade;
			}
		}
		$mtime = microtime();
		$mtime = explode(" ",$mtime);
		$mtime = $mtime[1] + $mtime[0];
		$endtime = $mtime;
		$totaltime = ($endtime - $starttime);
		$stepDuration = ($endtime - $steptime);
		$steptime = $endtime;
		echo "
				<font color=gray size=2>";
		printf("execution time for this courses[%01.3f ms] - total [%01.2f s].",$stepDuration*1000,$totaltime);
		echo "
				</font>";
	}
	mysql_close();

	$mtime = microtime();	$mtime = explode(" ",$mtime);	$mtime = $mtime[1] + $mtime[0];	$endtime = $mtime;	$totaltime = ($endtime - $starttime);

	echo "<hr>";
		if ($TotalnbError>0)
		{
			echo "
		<br>
				<font color=red>
					<strong>",$TotalnbError," ",$langErrorsFound,"</strong>
				</font>
				<strong><a href=\"".$PHP_SELF."?verbose=true\">Retry</A></strong>
				";
			$TotalnbError += $nbError;
			$nbError = 0;
		}
		else
		{
	?>
	<br>
		<font color=green>
			<strong>Ok</strong>
		</font>
	<br>

	<script language="JavaScript" type="text/javascript" event="onload">
		refreshIfBlock.style.visibility='hidden';
	</script>

	<br>
	<DIV>
	<FORM action="../managing/adminCoursesTree.php"  target="rebuildTree" method="post" >
	On new feature of clarine is that courses categories are now in a tree. <br>
	You need to  "build tree" by clicking on this button.
		<input type="submit" name="rebuiltTreePos" value="rebuilt Tree Pos">
	</FORM>

	After click the work on the next page find and click button like this <input type="submit" name="refreshAllNbChildInBase" value="rebuilt nb Childs in db">
	</DIV>
	<br>
	<?
	}
}


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
		&$numero_erreur, &$texte_erreur)
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

?>
