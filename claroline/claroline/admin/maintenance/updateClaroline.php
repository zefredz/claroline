<?php // $Id$
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.4.2 INSTALL SCRIPT $Revision$             |
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2003 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
      | Authors: Thomas Depraetere <depraetere@ipm.ucl.ac.be>                |
      |          Hugues Peeters    <peeters@ipm.ucl.ac.be>                   |
      |          Christophe Gesché <gesche@ipm.ucl.ac.be>                    |
      +----------------------------------------------------------------------+
      GOAL : install claroline 1.4.2 on server
*/

die ("deprecated");


@include ("../../include/installedVersion.inc.php");
$thisClarolineVersion 	= $clarolineVersion;
$thisVersionDb 			= $versionDb;

$langErrorToBuildNewConfig = "le nouveau config n'a pas été bien réalisé";
$langConfirmBackupIsDone	= "Confirm the backup procedure has been done before clicking on \"Upgrade\".";
$langMakeAbackupBefore		= "<b>Warning.</b> In case of trouble, we strongly recommend you
to backup your previous courses data before commiting the Claroline upgrade.";
$langConfirm 				= "confirm";

$langFile = "install";

@include('../../include/config.php');
$platformLanguage = $language;

@include('../../lang/english/install.inc');
@include('../../lang/english/install.inc.php');
// include langFiles

$pruposeToCryptAllPass = TRUE;

if ($mainDbName!="")
@include('../../inc/claro_init_local.inc.php');
if ($includePath=="")
{
	$includePath = "../../include";
}
$nameTools = $langAdministrationTools;
//@include ($includePath."/lib/auth.lib.inc.php");
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>
-- Claroline upgrade
-- version
<?php echo $clarolineVersion ?>
->
<?php echo $thisClarolineVersion ?>
</title>
<link rel="stylesheet" href="../../css/default.css" type="text/css">
<STYLE type="text/css" >
<!--
.warn {	border: thin double Silver;	margin-left: 15px;	margin-right: 15px;  font-family: serif;  color: Red;  padding-left: 25px; background-color:#FFFFFF; }
-->
</STYLE>
<Style  media="print" >
<!--
.notethis {	border: thin double Black;	margin-left: 15px;	margin-right: 15px;}
-->
</style>

</head>
<body bgcolor="white" dir="<?php echo $text_dir ?>">

<div align="center">
<table cellpadding="6" cellspacing="0" border="0" width="650" bgcolor="#E6E6E6">
	<tr bgcolor="#4171B5">
		<td valign="top">
			<font color="white">
				-- Claroline upgrade -- version
				<?php echo $clarolineVersion ?>
				->
				<?php echo $thisClarolineVersion ?>
			</font>
		</td>
	</tr>
	<tr bgcolor="#E6E6E6">
		<td>
			<H2>
				<?php echo $langWarningResponsible ?>
			</H2>
<?php
if ($iHaveMAkeBackupBefore && $iHaveStopServer && (!$askForEncryption||!$pruposeToCryptAllPass))
{
////////////// UPGRADE CONFIG ////////////////
// 1° Build var  to work
// $fileSource
// $fileTarget
// $fileBackup
// $fileTemp

	if ($fileSource=="")
	{
		$fileSource 		= $includePath."/"."config.inc.php";
	}

	if (!file_exists($fileSource))
	{
		$fileSource 		= "../../include/config.php";

	}
	if (!file_exists($fileSource))
	{
		$fileSource 		= $includePath."/"."config.php";
	}

	if (!file_exists($fileSource))
	{
		$fileSource 		= $includePath."/"."config.inc.php.dist";
	}

	if ($fileTarget=="")
	{
		$fileTarget 		= $includePath."/"."config.inc.php";
	}

	$fileTemp 	= tempnam ( $includePath, "config_work");
	$fileBackup 	= $includePath."/config.inc.".date("Y-z-B").".bak.php";


/////// SAVE THE TARGET
	echo "
			<br>
			Previous claroline configuration backed up in
			<SMALL>

				",$fileBackup,
			"</SMALL>" ;

	if (!@copy($fileTarget, $fileBackup) && $DEBUG )
	{
		echo "<div class=\"warn\"><code>",$fileTarget,"</code> copy failed !</div><br>, \n";
	}

	{
		@chmod( $fileBackup, 600 );
		@chmod( $fileBackup, 0600 );

//// Built Values for Content
/// 4 Step
//// 1° Set default
//// 2° Read actual (find  unknow values
//// 3° Set Forced values
//// 4° Write the  new file

	##### STEP 0 INITIALISE FORM VARIABLES IF FIRST VISIT ##################

	$urlServer				=	"";
	$urlAppend				=	"";
	$webDir					=	"" ;
	$mysqlServer			=	"";
	$mysqlUser				=	"";
	$mysqlPassword			=	"";
	$mysqlMainDb			=	"claroline";
	$serverAddress			=	"";
	$emailAdministrator		=	"";
	$administratorName		=	"";
	$administratorSurname	=	"";
	$educationManager		=	"";
	$siteName				=	"";
	$CourseProgram			=	"";
	$telephone				=	"(515) 648 208";
	$Institution			=	"";
	$InstitutionUrl			=	"";

	//backgrounds
	$color1="#F5F5F5"; // light grey
	$color2="#E6E6E6"; // less light grey for bicolored tables
	$colorLight		= "#99CCFF"; //
	$colorMedium	= "#6699FF"; // these 3 colors are used in header
	$colorDark	 	= "#000066"; //

	$language 						= "english";
	$userMailCanBeEmpty 			= true;
	$userPasswordCrypted 			= false;
	$allowSelfReg					= true;

// Following line is being commented: the value of $userPasswordCrypted	 should
// be read from .config.php.
	$userPasswordCrypted			= 	false;

	$checkEmailByHashSent 			=	false;
	$ShowEmailnotcheckedToStudent	=	true;
	$versionDb =  $thisVersionDb;

		////////// READ ACTUAL CONFIG.//////////////
		echo "
<p align=\"right\">
	<font color=green>
			<strong>Ok</strong>&nbsp;&nbsp;&nbsp;
	</font>
</p>
		Read previous Claroline configuration <br><SMALL>IN ".$fileSource."</SMALL>";
		@include ($fileSource); // read Values in sources
$rootWeb			= $urlServer;
$rootSys 			= $webDir;
$language 			= $platformLanguage ;
$dbHost				= $mysqlServer;
$dbLogin 			= $mysqlUser;
$dbPass				= $mysqlPassword;
$dbNamePrefix		= $mysqlPrefix;
$mainDbName			= $mysqlMainDb;
if ($statsDbName=="") $statsDbName = $mainDbName_stats;

$administrator["name"]		= $administratorSurname." ".$administratorName;
$administrator["phone"]		= $telephone;
$educationManager["name"]	= $educationManager;
$institution["name"]		= $Institution;
$institution["url"]			= $InstitutionUrl;
		@include ($fileSource);
// read again Values in sources
// if source contain old var names, they are  rewrited in  new names.
// if source contain new var names, the second read overwrite the assignment.

	# force some  values
		$clarolineVersion 	= $thisClarolineVersion;
		$versionDb 			= $thisVersionDb;
		if ($haveToEncrypt)
			$userPasswordCrypted	= TRUE;

		echo "<p align=\"right\">
	<font color=green>
			<strong>Ok</strong>&nbsp;&nbsp;&nbsp;
	</font>
</p>
				New claroline configuration created.";
		$stringConfig=str_replace("\r","",'<?php
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version '.$clarolineVersion.' $Revision$
      +----------------------------------------------------------------------+
      |   This file was generate by script /install/index.php                |
      |   '.date("r").'                                    |
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
/***************************************************************
*           CONFIG OF VIRTUAL CAMPUS
****************************************************************
GOAL
****
List of variables to be modified by the campus site administrator.
File has been CHMODDED 0444 by install.php.
CHMOD 0666 (Win: remove read-only file property) to edit manually
*****************************************************************/





// DO NOT EDIT VALUE expect bettween marks
// YOU CAN EDIT SINCE HERE  and // YOU CAN EDIT UNTIL HERE





/*****************************************************************
// This file was generate by script /install/index.php
// on '.date("r").'
// REMOTE_ADDR : 		'.$REMOTE_ADDR.' = '.@gethostbyaddr($REMOTE_ADDR).'
// REMOTE_HOST :		'.$REMOTE_HOST.'
// REMOTE_PORT : 		'.$REMOTE_PORT.'
// REMOTE_USER : 		'.$REMOTE_USER.'
// REMOTE_IDENT :	 	'.$REMOTE_IDENT.'
// HTTP_USER_AGENT : 	'.$HTTP_USER_AGENT.'
// SERVER_NAME :		'.$SERVER_NAME.'
// HTTP_COOKIE :		'.$HTTP_COOKIE.'
/*****************************************************************/



// YOU CAN EDIT SINCE HERE

$rootWeb 			= 	"'.$rootWeb.'";
$urlAppend			=	"'.$urlAppend.'";
$rootSys			=	"'.$rootSys.'" ;

// MYSQL
$dbHost 			= "'.$dbHost.'";
$dbLogin 			= "'.$dbLogin.'";
$dbPass				= "'.$dbPass.'";
$dbNamePrefix		= "'.$dbNamePrefix.'";

$mainDbName			= "'.$mainDbName.'";
$statsDbName		= "'.$statsDbName.'";
$is_trackingEnabled	= '.trueFalse($is_trackingEnabled).';
$singleDbEnabled	= '.trueFalse($singleDbEnabled).'; // DO NOT MODIFY THIS
$courseTablePrefix	= "'.($singleDb?'crs_':'').'"; // IF NOT EMPTY, CAN BE REPLACED BY ANOTHER PREFIX, ELSE LEAVE EMPTY
$dbGlu				= "'.($singleDb?'_':'`.`').'"; // DO NOT MODIFY THIS

$CourseProgram="http://www.ucl.ac.be/etudes/cours";

// Strings
$siteName				=	"'.$siteName.'";

$emailAdministrator		=	"'.$emailAdministrator.'";
$administrator["name"]	=	"'.$administrator["name"].'";
$administrator["phone"]	=	"'.$administrator["phone"].'";
$administrator["email"]	=	"'.$administrator["email"].'";

$educationManager["name"]	=	"'.$educationManager["name"].'";
$educationManager["phone"]	=	"'.$educationManager["phone"].'";
$educationManager["email"]	=	"'.$educationManager["email"].'";
$institution["name"]		=	"'.$institution["name"].'";
$institution["url"]			=	"'.$institution["url"].'";

// param for new and future features
$checkEmailByHashSent 			= '.trueFalse($checkEmailByHashSent).';
$ShowEmailnotcheckedToStudent 	= '.trueFalse($ShowEmailnotcheckedToStudent).';
$userMailCanBeEmpty 			= '.trueFalse($userMailCanBeEmpty).';
$userPasswordCrypted			= '.trueFalse($encryptPassForm).';
$allowSelfReg					= '.trueFalse($allowSelfReg).';


//backgrounds
$colorLight	=	"#99CCFF"; //
$colorMedium= 	"#6699FF"; // these 3 colors are used in header
$colorDark	= 	"#000066"; //

// available: english, french, german, italian, japanese, spanish , simplified_chinese
// (finnish and swedish forthcoming see http://www.claroline.net)
$platformLanguage 	= 	"'.$platformLanguage.'";
// YOU CAN EDIT UNTIL HERE


// DO NOT CHANGE [BEGIN]

$clarolineVersion	=	"'.$clarolineVersion.'";
$versionDb 			= 	"'.$versionDb.'";

$clarolineRepositoryAppend  = "claroline/";
$coursesRepositoryAppend	= "";
$rootAdminAppend			= "admin/";
$phpMyAdminAppend			= "mysql/";
$phpSysInfoAppend			= "sysinfo/";
$clarolineRepositorySys		= $rootSys.$clarolineRepositoryAppend;
$clarolineRepositoryWeb 	= $rootWeb.$clarolineRepositoryAppend;
$coursesRepositorySys		= $rootSys.$coursesRepositoryAppend;
$coursesRepositoryWeb		= $rootWeb.$coursesRepositoryAppend;
$rootAdminSys				= $clarolineRepositorySys.$rootAdminAppend;
$rootAdminWeb				= $clarolineRepositoryWeb.$rootAdminAppend;
$phpMyAdminWeb				= $rootAdminWeb.$phpMyAdminAppend;
$phpMyAdminSys				= $rootAdminSys.$phpMyAdminAppend;
$phpSysInfoWeb				= $rootAdminWeb.$phpSysInfoAppend;
$phpSysInfoSys				= $rootAdminSys.$phpSysInfoAppend;
// DO NOT CHANGE [E N D]
// DO NOT CHANGE FOLLOWINGS LINES
// these values are keep to have no problem with script not upgraded to  the  new init system
$urlServer 			= 	$rootWeb ;
$serverAddress		= 	$rootWeb ;
$webDir				= 	$rootSys;
$language 			=	$platformLanguage ;
$mainInterfaceWidth =	"600";

// MYSQL
$mysqlServer		=	$dbHost ;
$mysqlUser			=	$dbLogin;
$mysqlPassword		=	$dbPass;
$mysqlPrefix		=	$dbNamePrefix;
$mysqlMainDb		=	$mainDbName;

// PATH & URL
$phpSysInfoURL		=	"'.$phpSysInfoWeb.'";

$color1				=	"#F5F5F5"; // light grey
$color2				=	"#E6E6E6"; // less light grey for bicolored tables

//general infos
$administratorSurname=	"";
$administratorName	=	$administrator["name"];
$telephone			=	$administrator["phone"];

$educationManager	=	$educationManager["name"];

$Institution		=	$institution["name"];
$InstitutionUrl		=	$institution["url"];

// course backup
$dateBackup			=	date("Y-m-d-H-i-s");
$shortDateBackup	=	date("YzBs");

$verboseBackup		=	FALSE;

$archiveExt			=	"txt";
$archiveDirName		=	"archive";
?>');
######### DEALING WITH FILES #########################################
		echo  "<br><SMALL>Temp :".$fileTemp."</SMALL>" ;
		$fd=fopen($fileTemp, "w");
		fwrite($fd, $stringConfig);
		fclose($fd);
		@unlink($fileTarget);
		echo  "<br><SMALL>Saved as : ".$fileTarget ."</SMALL><BR>";
		if ( !rename($fileTemp, $fileTarget) )
		{
			if ($DEBUG) echo $langErrorToBuildNewConfig;
		}
		else
		{
			@chmod( $fileTarget, 766 );
			@chmod( $fileTarget, 0766 );
?>
<p align="right">
	<font color=green>
			<strong>Ok</strong>&nbsp;&nbsp;&nbsp;
	</font>
</p>
<Form action="./updateMainDataBase.php" >
<strong>Step 4</strong>	: Upgrade main Claroline database

<?php
	if ($haveToEncrypt)
		echo "<input type=\"hidden\" name=\"encrypt\" value=\"1\">";
	else
		echo "<input type=\"hidden\" name=\"encrypt\" value=\"0\">";
?>
	<div>
	<input type="checkbox" name="verbose" value="true">verbose (<small>output massives information, use only if needed</small>)<br>
	<input type="submit" name="upgrade" value="Upgrade">
	</div>
</FORM>
<?php
		}
	}
}
elseif ($iHaveMAkeBackupBefore && $iHaveStopServer && $askForEncryption)
{
	echo '
<Form action="".$PHP_SELF."">
	You can choose to enable password encryption into your database. If you choose so, your database will be converted.<BR><br>

	Do you want to encrypt user passwords ?
		<input type="radio" name="haveToEncrypt" value=1>Yes
		<input type="radio" name="haveToEncrypt" value=0 checked>No
		<input type="hidden" name="iHaveMAkeBackupBefore" value=1>
		<input type="hidden" name="iHaveStopServer" value=1>
		<BR>
		<input type="hidden" name="askForEncryption" value=0>
	<br>
	<input type="submit" name="continue" value="Continue">
</FORM>
';
}
else
{
	@include ($includePath."/config.inc.php");
	$db = @mysql_connect("$mysqlServer", "$mysqlUser", "$mysqlPassword");
	if ($db)
	{
		$resBdbHome			= mysql_query("SHOW VARIABLES LIKE 'datadir'");
		$mysqlRepositorySys	= mysql_fetch_array($resBdbHome,MYSQL_ASSOC);
		$mysqlPath = $mysqlRepositorySys ["Value"];
	}

	echo "<Form action=\"".$PHP_SELF."\" >";
	if ($thisClarolineVersion==$clarolineVersion)
	{

		echo "
		<br>
		<div class=\"warn\">
			<font size=\"+1\">The config is already upgraded to claroline $clarolineVersion</font>
		</div>";
		if ($thisVersionDb==$versionDb)
		{
			echo "
		<div class=\"warn\">
			<font size=\"+1\">And the main database is also already upgraded  to $versionDb db structure</font>
		</div><br>

		<UL>
			<LI>
				go directly to <strong><a href=\"batchUpdateDb.php\">upgrade courses DBs</a></strong>
			</LI>
			<LI>
				go to <a href=\"updateMainDataBase.php\">upgrade central DB <strong>again</strong></a>
			</LI>
			";
		}
		else
		{
		echo "
		<br>
		<UL>
			<LI>
				go to <a href=\"updateMainDataBase.php\">upgrade central DB</a>
			</LI>
			";
		}
		echo "
			<LI>
				Or do you want to continue and rebuild everything ?
			</LI>
		</UL>
					";
	}


	?>
	<ol>
		<li>
			<strong>Services</strong> :
			Stop campus external access before running upgrade.
			<br><br>
			<div style="background-color:#FFFFFF">
				<div align="center">
					Confirm campus external access is disabled before clicking on "Upgrade".
				</div>
				<p align="center">
					<strong>Confirm</strong>
					<input type="checkbox" value="true"  name="iHaveStopServer">
				</p>
			</div>
		</li>
		<li>
			<strong>Backup</strong> :
			In case of trouble, we strongly recommend you to backup your
			previous courses data before commiting the Claroline upgrade.
			<blockquote>These data are stored :
				<ul>
					<li>
						in your campus web directory
						<br>
						<?php
			if (isset($rootSys))
			{
				echo "
						<small>
							at ".$rootSys."
						</small>";
			}
			else
			{
		 	?>
						ie <code>/var/www/html/claroline131</code>
						<small>(unix)</small>
						<br>or <code>c:\www\claroline131</code> <small>(windows)</small>
			<?php
			}
			?>
					</li>
					<li>
						in the mySQL databases created by the claroline application
						<br>
			<?php
			if (isset($mysqlPath))
			{
				echo "
						<small>
							at ".$mysqlPath."
						</small>";
			}
			else
			{
		 	?>
						ie <code>/var/lib/mysql</code> <small>(unix)</small> <br>or
						<code>c:\program files\easyphp\mysql\data\</code>
						<small>(windows)</small>
						<br>
			<?php
			}
			?>
					</li>
				</ul>
			</blockquote>
			<div  style="background-color:#FFFFFF">
				<div align="center">
					Confirm the backup procedure has been done
					before clicking on "Upgrade".
				</div>
				<p align="center">
					<strong>Confirm</strong>
					<input type="checkbox" value="true"  name="iHaveMAkeBackupBefore">
				</p>
			</div>
		</ol>
		<br>
<?php
	if ($userPasswordCrypted)
		$askForEncryption = FALSE;
	else
		$askForEncryption = TRUE;


?>
				<input type="hidden" name="askForEncryption" value=<?php if ($askForEncryption) echo 1; else echo 0;?>>

				<div align="center">
					<input type="submit" name="upgrade" value="Upgrade">
				</div>
</FORM>
<?
}
?>
		</td>
	</tr>
</table>
</div>
</body>
</html>
<?
/**
 * function trueFalse($var)
 * @desc Output boolean value in string.
 */
function trueFalse($var)
{
	if ($var)
		$var = "TRUE";
	else
		$var = "FALSE";
	return $var;
}
?>
