<?php # $Id$

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

/*
GOAL : install claroline 1.5.* on server
*/
$langAdminSetting ="Admin Setting";
/* LET DEFINE ON SEPARATE LINES !!!*/
// __LINE__ use to have arbitrary number

define ("DISP_WELCOME",__LINE__);
define ("DISP_LICENCE",__LINE__);
define ("DISP_DB_CONNECT_SETTING",__LINE__);
define ("DISP_DB_NAMES_SETTING",__LINE__);
define ("DISP_ADMIN_SETTING",__LINE__);
define ("DISP_PLATFORM_SETTING",__LINE__);
define ("DISP_LAST_CHECK_BEFORE_INSTALL",__LINE__);
define ("DISP_RUN_INSTALL_NOT_COMPLETE",__LINE__);
define ("DISP_RUN_INSTALL_COMPLETE",__LINE__);
/* LET DEFINE ON SEPARATE LINES !!!*/

error_reporting(E_ERROR | E_WARNING | E_PARSE);

// Place of Config file
$configFileName = "claro_main.conf.php";
$configFilePath = "../inc/conf/".$configFileName;


session_start();
$_SESSION = array();
session_destroy();

if (!empty($_GET))  	{extract($_GET, EXTR_OVERWRITE);}
if (!empty($_POST)) 	{extract($_POST, EXTR_OVERWRITE);}
if (!empty($_SERVER)) 	{extract($_SERVER, EXTR_OVERWRITE);}

$newIncludePath ="../inc/";
include ($newIncludePath."installedVersion.inc.php");

include ("../lang/english/trad4all.inc.php");
include ("../lang/english/install.inc.php");

include ($newIncludePath."lib/auth.lib.inc.php"); // to generate pass and to cryto it if needed
include ($newIncludePath."lib/config.lib.inc.php");

##### STEP 0 INITIALISE FORM VARIABLES IF FIRST VISIT ##################
//$rootSys="'.realpath($pathForm).'";

$topRigthPath = topRigthPath(); // to known right (read and write)

if($_REQUEST['cmdLicence'])
{
	$cmd=DISP_LICENCE;
}
elseif($_REQUEST['cmdDB_CONNECT_SETTING'])
{
	$cmd=DISP_DB_CONNECT_SETTING;
}
elseif($_REQUEST['cmdDbNameSetting'])
{
	$cmd=DISP_DB_NAMES_SETTING;
}
elseif($_REQUEST['cmdAdminSetting'])
{
	$cmd=DISP_ADMIN_SETTING;
}
elseif($_REQUEST['cmdPlatformSetting'])
{
	$cmd=DISP_PLATFORM_SETTING;
}
elseif($_REQUEST['cmdDoInstall'])
{
	$cmd=DISP_RUN_INSTALL_COMPLETE;
}
 
if(!$_REQUEST['alreadyVisited'] || $_REQUEST['resetConfig']) // on first step prupose values
{

	$dbHostForm		= "localhost";
	$dbUsernameForm	= "root";

	$dbPrefixForm	= "claroline";
	$dbNameForm		= $dbPrefixForm."Main";
	$dbStatsForm    = $dbPrefixForm."Main";
	$dbPrefixForm	= $dbPrefixForm."_";
 	$singleDbForm	= true;


	// extract the path to append to the url if Claroline is not installed on the web root directory

	$urlAppendPath 	= ereg_replace ("/claroline/install/".basename($_SERVER['SCRIPT_NAME']), "", $PHP_SELF);
  	$urlForm 		= "http://".$_SERVER['SERVER_NAME'].$urlAppendPath."/";
	$pathForm		= realpath("../..")."/";


	$adminEmailForm		= $_SERVER['SERVER_ADMIN'];

	$adminNameForm		= "Doe";
	$adminSurnameForm	= "John";
	$loginForm		= "admin";
	$passForm  		= "";

	$campusForm		= "My campus";
	$adminPhoneForm	= "(000) 001 02 03";
	$institutionForm= "My Univ";
	$institutionUrlForm="http://www.google.com/";
	$urlEndForm		= "mydir/";

	$languageForm = "english";

	$checkEmailByHashSent 			= false;
	$ShowEmailnotcheckedToStudent 	= true;
	$userMailCanBeEmpty 			= true;
	$userPasswordCrypted 			= false;
}

if ($PHP_SELF == "") $PHP_SELF = $_SERVER["PHP_SELF"];




// This script is a big form.
// all value are in HIDDEN FIELD,
// and different display show step by step some fields in editable input
// The last panel have another job. It's  run install and show result.
// Run install dom many task
//  * Create and fill main Database
//  * Create and fill STAT Database
//  * Create  some  directories
//  * Write the config file
//  * Protect some  directory with an .htaccess (work only  for apache)


$canRunCmd = true;
if($_REQUEST['fromPanel'] == DISP_ADMIN_SETTING || $_REQUEST['cmdDoInstall'])
{
	if (empty($adminSurnameForm)||empty($passForm)||empty($loginForm)||empty($adminNameForm)||empty($adminEmailForm))
	{
		$adminDataMissing = true;
		if (empty($loginForm)) 			$missing_admin_data[] = 'login';
		if (empty($passForm)) 			$missing_admin_data[] = 'password';
		if (empty($adminSurnameForm)) 	$missing_admin_data[] = 'firstname';
		if (empty($adminNameForm)) 		$missing_admin_data[] = 'lastname';
		if (empty($adminEmailForm)) 	$missing_admin_data[] = 'email';
		
		$msg_missing_admin_data = '<font color="red" >Please fill '.implode(', ',$missing_admin_data).'</font>';
		if ($cmd>DISP_ADMIN_SETTING)
		{
			$display=DISP_ADMIN_SETTING;
		}
		else 
		{
			$display=$cmd;
		}
		$canRunCmd = false;
	}
	else 
	{
		// here add some check  on email, password crackability, ... of admin.
	}
}


if ($_REQUEST['fromPanel'] == DISP_DB_CONNECT_SETTING || $_REQUEST['cmdDoInstall'])
{
	// Check Connection //
	$databaseParam_ok = true;
	$db = @mysql_connect("$dbHostForm", "$dbUsernameForm", "$dbPassForm");
	if (mysql_errno()>0) // problem with server
	{
		$no = mysql_errno();
		$msg = mysql_error();
		$msg_no_connection = '
				<P class="setup_error">
					<font color="red">Warning !</font> 
					<small>['.$no.'] - '.$msg.'</small>
					<br>';
		if ($no=="2005")
		$msg_no_connection .= '		
					Wrong '.$langDBHost.' : <I>'.$dbHostForm.'</I>';
		elseif ($no=="1045")
		$msg_no_connection .= '
					Wrong database Login : (<I>'.$dbUsernameForm.'</I>) 
					or Password (<I>'.$dbPassForm.'</I>)';
		else
		$msg_no_connection .= '
					Server unavailable (mysql started ?)';
		$msg_no_connection .= '
					<BR>
					<font color="blue">
						Fix this problem before going further
					</font>
					<BR>
				</P>';
		$databaseParam_ok = false;
		$canRunCmd = false;
		if ($cmd>DISP_DB_CONNECT_SETTING)
		{
			$display=DISP_DB_CONNECT_SETTING;
		}
		else 
		{
			$display=$cmd;
		}
	}
}

if ($_REQUEST['fromPanel'] == DISP_DB_NAMES_SETTING || $_REQUEST['cmdDoInstall'])
{
	// re Check Connection //
	$databaseParam_ok = true;
	if ($singleDbForm) $dbStatsForm = $dbNameForm;
	$db = @mysql_connect("$dbHostForm", "$dbUsernameForm", "$dbPassForm");
	$valMain = check_if_db_exist($dbNameForm  ,$db);
	if (!$singleDbForm) $valStat = check_if_db_exist($dbStatsForm ,$db);
	if (
			($valMain && !$confirmUseExistingMainDb)
			||
			($valStat && !$confirmUseExistingStatsDb)
		)
	{
		$databaseAlreadyExist = true;
		if ($valMain)	$mainDbNameExist  = true;
		if ($valStat)	$statsDbNameExist = true;
		$canRunCmd = false;
	    if ($cmd > DISP_DB_NAMES_SETTING)
	    {
	    	$display = DISP_DB_NAMES_SETTING;
	    }
	    else
	    {
	    	$display= $cmd;
	    }
	}
	else
	{
		$databaseAlreadyExist = false;
	}
}

if ($canRunCmd)
{
	// SET default display
	$display=DISP_WELCOME;
	if($_REQUEST['cmdLicence'])
	{
		$display = DISP_LICENCE;
	}
	elseif($_REQUEST['cmdDB_CONNECT_SETTING'])
	{
		$display = DISP_DB_CONNECT_SETTING;
	}
	elseif($_REQUEST['install6'] || $_REQUEST['back6'] )
	{
		$display=DISP_LAST_CHECK_BEFORE_INSTALL;
	}
	elseif($_REQUEST['cmdDbNameSetting'])
	{
		$display = DISP_DB_NAMES_SETTING;
	}
	elseif($_REQUEST['cmdAdminSetting'])
	{
		$display = DISP_ADMIN_SETTING;
	}
	elseif($_REQUEST['cmdPlatformSetting'])
	{
		$display = DISP_PLATFORM_SETTING;
	}
	elseif($_REQUEST['cmdDoInstall'])
	{
		include("./do_install.inc.php");
	}
 }
 
if ($display==DISP_DB_NAMES_SETTING)
{
	// GET DB Names  //
	// this is  to prevent duplicate before submit
	$db = @mysql_connect("$dbHostForm", "$dbUsernameForm", "$dbPassForm");
	$sql = "show databases";
	$res = mysql_query($sql,$db);
	while ($__dbName = mysql_fetch_array($res, MYSQL_NUM))
	{
		$existingDbs[]=$__dbName[0];
	}
	unset($__dbName);
}























// BEGIN OUTPUT
	
// COMMON OUTPUT Including top of form  and list of hidden values
?>
<html>
<head>

<title>
-- Claroline installation -- version <?php echo $clarolineVersion ?>
</title>

<link rel="stylesheet" href="../css/default.css" type="text/css" >
<style media="print" type="text/css"  >
	.notethis {	border: thin double Black;	margin-left: 15px;	margin-right: 15px;}
</style>
</head>
<body bgcolor="white" dir="<?php echo $text_dir ?>">
<center>
<form action="<?php echo $PHP_SELF?>?alreadyVisited=1" method="post">
<table cellpadding="10" cellspacing="0" border="0" width="650" bgcolor="#E6E6E6">
	<tr bgcolor="navy">
		<td valign="top">
			<font color="white">
				Claroline 1.5 Release Candidate (<?php echo $clarolineVersion ?>) - installation
			</font>
		</td>
	</tr>
	<tr bgcolor="#E6E6E6">
		<td>
<?php
echo "
			<input type=\"hidden\" name=\"languageCourse\" value=\"$languageCourse\">
			<input type=\"hidden\" name=\"urlAppendPath\" value=\"$urlAppendPath\">
			<input type=\"hidden\" name=\"urlEndForm\" value=\"$urlEndForm\">
			<input type=\"hidden\" name=\"pathForm\" value=\"".str_replace("\\","/",realpath($pathForm)."/")."\" >

			<input type=\"hidden\" name=\"dbHostForm\" value=\"$dbHostForm\">
			<input type=\"hidden\" name=\"dbUsernameForm\" value=\"$dbUsernameForm\">

			<input type=\"hidden\" name=\"singleDbForm\" value=\"".$singleDbForm."\">

			<input type=\"hidden\" name=\"dbPrefixForm\" value=\"$dbPrefixForm\">
			<input type=\"hidden\" name=\"dbNameForm\" value=\"$dbNameForm\">
            <input type=\"hidden\" name=\"dbStatsForm\" value=\"$dbStatsForm\">
            <input type=\"hidden\" name=\"enableTrackingForm\" value=\"$enableTrackingForm\">
			<input type=\"hidden\" name=\"allowSelfReg\" value=\"$allowSelfReg\">
			<input type=\"hidden\" name=\"allowSelfRegProf\" value=\"$allowSelfRegProf\">


			<input type=\"hidden\" name=\"dbMyAdmin\" value=\"$dbMyAdmin\">
			<input type=\"hidden\" name=\"dbPassForm\" value=\"$dbPassForm\">

			<input type=\"hidden\" name=\"urlForm\" value=\"$urlForm\">
			<input type=\"hidden\" name=\"adminEmailForm\" value=\"$adminEmailForm\">
			<input type=\"hidden\" name=\"adminNameForm\" value=\"$adminNameForm\">
			<input type=\"hidden\" name=\"adminSurnameForm\" value=\"$adminSurnameForm\">

			<input type=\"hidden\" name=\"loginForm\" value=\"$loginForm\">
			<input type=\"hidden\" name=\"passForm\" value=\"$passForm\">

			<input type=\"hidden\" name=\"languageForm\" value=\"$languageForm\">

			<input type=\"hidden\" name=\"phpSysInfoURL\" value=\"$phpSysInfoURL\">

			<input type=\"hidden\" name=\"campusForm\" value=\"$campusForm\">
			<input type=\"hidden\" name=\"adminPhoneForm\" value=\"$adminPhoneForm\">
			<input type=\"hidden\" name=\"institutionForm\" value=\"$institutionForm\">
			<input type=\"hidden\" name=\"institutionUrlForm\" value=\"$institutionUrlForm\">

			<input type=\"hidden\" name=\"versionDb\" value=\"$versionDb\">
			<input type=\"hidden\" name=\"checkEmailByHashSent\" value=\"$checkEmailByHashSent\">
			<input type=\"hidden\" name=\"ShowEmailnotcheckedToStudent\" value=\"$ShowEmailnotcheckedToStudent\">
			<input type=\"hidden\" name=\"userMailCanBeEmpty\" value=\"$userMailCanBeEmpty\">
			<input type=\"hidden\" name=\"userPasswordCrypted\" value=\"$userPasswordCrypted\">
			<input type=\"hidden\" name=\"encryptPassForm\" value=\"$encryptPassForm\">
			<input type=\"hidden\" name=\"confirmUseExistingMainDb\" value=\"$confirmUseExistingMainDb\">
			<input type=\"hidden\" name=\"confirmUseExistingStatsDb\" value=\"$confirmUseExistingStatsDb\">
		

";



























 ##### PANNELS  ######
 #
 # INSTALL IS a big form
 # Too big to show  in one time.
 # PANEL show some  field to edit, all other are in HIDDEN FIELDS
###################################################################
###### STEP 1 REQUIREMENTS ########################################
###################################################################
if ($display==DISP_WELCOME)
{
	echo '
				<input type="hidden" name="fromPanel" value="'.$display.'">
				<h2>
					'.$langStep1.' : '.$langRequirements.'
				</h2>';
	// check if an claroline configuration file doesn't already exists.
	if (
		file_exists("../inc/conf/claro_main.conf.inc.php")
	||	file_exists("../inc/conf/claro_main.conf.php")
	|| 	file_exists("../inc/conf/config.inc.php")
	|| file_exists("../include/config.inc.php")
	|| file_exists("../include/config.php"))
	{
		echo '
 <div style="background-color:#FFFFFF;margin:20px;padding:5px">
	<b>
		<font color="red">
			Warning ! The installer has detected an existing
			claroline platform on your system.
		</font>
		<br>
	</b>
	<ul>';
		if ($is_upgrade_available)
		{
			echo '
		<li>
			For claroline upgrade click
			<a href="../admin/maintenance/upgrade.php">here.</a>
		</li>';
		}
		else
		{
			echo '
		<li>
			For claroline upgrade please wait a stable release.
		</li>';
		}
		echo 	'
		<li>
			For claroline overwrite click on "next" button
		</li>
	</ul>
</div>';
	}


	if(!$stable)
	{
		echo "
		<strong>Warning !</strong> 
		This version is not considered as stable
		and is not aimed for production.<br>
		
		If  something goes wrong,
		come talk on our support forum at 
		<a href=\"http://www.claroline.net/forum/index.php?c=8\" target=\"_clarodev\">http://www.claroline.net</a>.";
	}

	if($SERVER_SOFTWARE=="") $SERVER_SOFTWARE = $_SERVER["SERVER_SOFTWARE"];
	$WEBSERVER_SOFTWARE = explode(" ",$SERVER_SOFTWARE,2);
	echo '
	<p>Read Thouroughly <a href="../../INSTALL.txt">INSTALL.txt</a> 
	before proceeding to install.</p>
	<h4>Checking requirement</h4>
<ul>

	<li>
		Checking PHP extentions.
		<UL>';

	warnIfExtNotLoaded("standard");
	warnIfExtNotLoaded("session");
	warnIfExtNotLoaded("mysql");
	warnIfExtNotLoaded("zlib");
	warnIfExtNotLoaded("pcre");
//	warnIfExtNotLoaded("exif"); // exif  would be needed later for pic view properties.
//	warnIfExtNotLoaded("nameOfExtention"); // list here http://www.php.net/manual/fr/resources.php

	echo '
		</UL>
	</LI>
	<LI>
		Checking PHP settings
		<UL>
			';
	if (!ini_get('register_globals'))
	{
		echo '
			<li>
				<p class="setup_error">
					<font color="red">Warning !</font> 
					register_globals is set to <strong>off</strong>.
					<br>
					Change the following parameter in your <i>php.ini</i> file to this value :<br>
					<font color="blue">
					<code>register_globals = on </code>
					</font>
				</p>
			</li>';
	}

	if (!ini_get('magic_quotes_gpc'))
	{
		echo "
			<LI>
				<font color=\"red\">Warning !</font> magic_quotes_gpc is set to <strong>off</strong>.
				<br>
				Change the following parameter in your <i>php.ini</i> file to this value :<br>
				<font color=\"blue\">
				<code>magic_quotes_gpc = on</code>
				</font>
			</LI>";
	}

	if (	ini_get('display_errors') 
		&& (ini_get('error_reporting') & E_NOTICE )
		)
	{
		echo "
			<LI>
				<font color=\"red\">
					Warning !
				</font> 
				error_reporting include <strong>E_NOTICE</strong>.
				<br>
				Change the following parameter in your <i>php.ini</i> file to this value :<br>
				<font color=\"blue\">
					<code>error_reporting  =  E_ALL & ~E_NOTICE</code>
				</font><BR>
				or<BR>

				<font color=\"blue\">
					<code>display_errors = off</code> 
				</font>
				<br>
			</LI>";
	}

	echo "
		</UL>
	</li>

	<li>
		Checking file access to web directory.
		<ul>
		".(is_writable("../..")?"":"</li>
			<font color=\"red\">Warning !</font> Claroline is not able to write on : <br>
			<nobr><code>".realpath("../..")."</code><nobr>
				<br>
				Change this file permission the server file system.</li>
		")."
		".(is_readable("../..")?"":"
			<li><font color=\"red\">Warning !</font> claroline is not able to read on : <br>
			<nobr><code>".realpath("../..")."</code><nobr>
			<br>
			Change this file permission the server file system.
		</li>
		")."
		</ul>
	</li>
</ul>
<p>
If the checks above has passed without any problem, click on the <i>Next</i> button to continue.
<p align=\"right\"><input type=\"submit\" name=\"cmdLicence\" value=\"Next &gt;\"></p>";

}



###################################################################
############### STEP 2 LICENSE  ###################################
###################################################################
elseif($display==DISP_LICENCE)
{
	echo '
				<input type="hidden" name="fromPanel" value="'.$display.'">
				<h2>
					'.$langStep2.' : '.$langLicence.'
				</h2>
				<P>
				Claroline is free software, distributed under GNU General Public licence (GPL).
				Please read the licence and click &quot;I accept&quot;.
				<a href="../../LICENCE.txt">'.$langPrintVers.'</a>
				</P>
				<textarea wrap="virtual" cols="65" rows="15">';
	include ('../license/gpl.txt');
	echo '</textarea>

		</td>
	</tr>
	<tr>
		<td>
			<table width="100%">
				<tr>
					<td>
					</td>
					<td align="right">
					<input type="submit" name="cmdWelcomePanel" value="&lt; Back">
					<input type="submit" name="cmdDB_CONNECT_SETTING" value="I accept &gt;">
					</td>
				</tr>
			</table>';

}





##########################################################################
###### STEP 3 MYSQL DATABASE SETTINGS ####################################
##########################################################################

elseif($display==DISP_DB_CONNECT_SETTING)
{



	echo '
				<input type="hidden" name="fromPanel" value="'.$display.'">
				<h2>
					'.$langStep3.' : '.$langDBSetting.'
				</h2>
			</td>
		</tr>
		<tr>
			<td>
				<h4>'.$langDBConnectionParameters.'</h4>
				<p>
				Enter here the parameters given by your database server administrator.
				</p>
				'.$msg_no_connection.'
				<table width="100%">
					<tr>
						<td>
							<label for="dbHostForm">'.$langDBHost.'</label>
						</td>
						<td>
							<input type="text" size="25" id="dbHostForm" name="dbHostForm" value="'.$dbHostForm.'">
						</td>
						<td>
							'.$langEG.' localhost
						</td>
					</tr>
					<tr>
						<td>
							<label for="dbUsernameForm">'.$langDBLogin.'</label>
						</td>
						<td>
							<input type="text"  size="25" id="dbUsernameForm" name="dbUsernameForm" value="'.$dbUsernameForm.'">
						</td>
						<td>
							'.$langEG.' root
						</td>
					</tr>
					<tr>
						<td>
							<label for="dbPassForm">'.$langDBPassword.'</label>
						</td>
						<td>
							<input type="text"  size="25" id="dbPassForm" name="dbPassForm" value="'.$dbPassForm.'">
						</td>
						<td>
							'.$langEG.' '.generePass(8).'
						</td>
					</tr>
				</table>
				<h4>'.$langDBUse.'</h4>
				<table width="100%">
					<tr>
							<td>
									Tracking</label>
							</td>
							<td>
									<input type="radio" id="enableTrackingForm_enabled" name="enableTrackingForm" value="1" checked> 
									<label for="enableTrackingForm_enabled">
										Enabled
									</label>
							</td>
							<td>
									<input type="radio" id="enableTrackingForm_disabled" name="enableTrackingForm" value="0"> 
									<label for="enableTrackingForm_disabled">
										Disabled
									</label>
							</td>
					</tr>
					<tr>
						<td>
							Database mode
						</td>
						<td>
							<input type="radio" id="singleDbForm_single" name="singleDbForm" value="1" '.($singleDbForm?'checked':'').' > 
							<label for="singleDbForm_single">
								Single
							</label>
						</td>
						<td>
							<input type="radio" id="singleDbForm_multi" name="singleDbForm" value="0" '.($singleDbForm?'':'checked').' > 
							<label for="singleDbForm_multi">
								Multi
								<small>
									(one new database created at each course creation)
								</small>
							</label>
						</td>
					</tr>
					<tr>
						<td>
							<input type="submit" name="cmdLicence" value="&lt; Back">
						</td>
						<td >
							&nbsp;
						</td>
						<td align="right">
							<input type="submit" name="cmdDbNameSetting" value="Next &gt;">
						</td>
					</tr>
				</table>';
}	 // cmdDB_CONNECT_SETTING 












##########################################################################
###### STEP 3 MYSQL DATABASE SETTINGS ####################################
##########################################################################
elseif($display == DISP_DB_NAMES_SETTING )
{
	echo '
			<input type="hidden" name="fromPanel" value="'.$display.'">
				<h2>
					'.$langStep4.' : MySQL Names
				</h2>
				'.($singleDbForm?'':$langDBSettingNamesIntro).'
			</td>
		</tr>
		<tr>
			<td>
				'.$msg_no_connection.'
				<h4>'.$langDBNamesRules.'</h4>
	
				<table width="100%">';
				if ($mainDbNameExist)
			echo '
					<tr>
						<td colspan="2">
							<P class="setup_error">
								<font color="red">Warning</font> 
								: <em>'.$dbNameForm.'</em> already exist
								<BR>
								<input type="checkbox" name="confirmUseExistingMainDb"  id="confirmUseExistingMainDb" value="true" '.($confirmUseExistingMainDb?'checked':'').'>
								<label for="confirmUseExistingMainDb" >
									I know, I want use it. (this script write in tables use by claroline.)
								</label>
							</P>
						</td>
					</tr>';
			echo '
					<tr>
						<td>
							<label for="dbNameForm">
								'.($singleDbForm?$langDbName:$langMainDB).'
							</label>
						</td>
						<td>
							<input type="text"  size="25" id="dbNameForm" name="dbNameForm" value="'.$dbNameForm.'">
						</td>
						<td>
							&nbsp;
						</td>
					</tr>';
	if (!$singleDbForm)
	{
		if ($statsDbNameExist && $dbStatsForm!=$dbNameForm)
		{
			echo '
					<tr>
						<td colspan="2">
							<P class="setup_error">
								<font color="red">Warning</font> 
								: '.$dbStatsForm.' already exist
								<BR>
								<input type="checkbox" name="confirmUseExistingStatsDb"  id="confirmUseExistingStatsDb" value="true" '.($confirmUseExistingStatsDb?'checked':'').'>
								<label for="confirmUseExistingStatsDb" >
									I know, I want use it. (This script write in tables use by claroline.)
								</label>
							</P>
						</td>
					</tr>';
		}
		echo '
					<tr>
						<td>
							<label for="dbStatsForm">'.$langStatDB.'</label>
						</td>
						<td>
							<input type="text"  size="25" id="dbStatsForm" name="dbStatsForm" value="'.$dbStatsForm.'">
						</td>
						<td>
							&nbsp;
						</td>
					</tr>
		';
	}
	echo '
					<tr>
						<td>
							<label for="dbPrefixForm">
								'.($singleDbForm?'Prefix Name for Course Tables':$langDbPrefixForm).'
							</label>
						</td>
						<td>
							<input type="text"  size="25" id="dbPrefixForm" name="dbPrefixForm" value="'.$dbPrefixForm.'">
						</td>
						<td>
							'.$langDbPrefixCom.'
						</td>
					</tr>
				</table>
				<table width="100%">
					<tr>
						<td>
							<input type="submit" name="cmdDB_CONNECT_SETTING" value="&lt; Back">
						</td>
						<td>
							&nbsp;
						</td>
						<td align="right">
							<input type="submit" name="cmdAdminSetting" value="Next &gt;">
						</td>
					</tr>
				</table>';
/*
				I want  put this in a popup.
				if (is_array($existingDbs))
				{
					echo "
				INFO : Bases existantes
				<SELECT>";

					foreach($existingDbs as $__dbName)
					{
					echo "
					<OPTION>".$__dbName."</OPTION>";
					}
					echo "
					</SELECT>";
					unset($__dbName);
				}
*/
}	 // cmdDB_CONNECT_SETTING 






##########################################################################
###### STEP CONFIG SETTINGS ##############################################
##########################################################################
elseif($display==DISP_ADMIN_SETTING)

{
	echo '
	            <input type="hidden" name="fromPanel" value="'.$display.'">
				<h2>
					'.$langStep5.' : '.$langAdminSetting.'
				</h2>
				The following values will be written in `<em>'.$configFilePath.'</em>´
			</td>
		</tr>
		<tr>
			<td>
				<h4>Administrator</h4>
	  			'.$msg_missing_admin_data.'
	  			'.$msg_admin_exist.'
	
				<table width="100%">
					<tr>
						<tr>
							<td>
								<b><label for="loginForm">'.$langAdminLogin.'</label></b>
							</td>
							<td>
								<input type="text" size="40" id="loginForm" name="loginForm" value="'.$loginForm.'">
							</td>
							<td>
							</td>
						</tr>
						<tr>
							<td>
								<b><label for="passForm">'.$langAdminPass.'</label></b>
							</td>
							<td>
								<input type="text" size="40" id="passForm" name="passForm" value="'.$passForm.'">
							</td>
							<td>
								e.g. '.generePass(8).'
							</td>
						</tr>
						<td>
								<label for="adminEmailForm">'.$langAdminEmail.'</label>
							</td>
							<td>
							<input type="text" size="40" id="adminEmailForm" name="adminEmailForm" value="'.$adminEmailForm.'">
							</td>
							<td>
							</td>
						</tr>
						<tr>
							<td>
								<label for="adminNameForm">'.$langAdminName.'</label>
							</td>
							<td>
								<input type="text" size="40" id="adminNameForm" name="adminNameForm" value="'.$adminNameForm.'">
							</td>
							<td>
							</td>
						</tr>
						<tr>
							<td>
								<label for="adminSurnameForm">'.$langAdminSurname.'</label>
							</td>
							<td>
								<input type="text" size="40" id="adminSurnameForm" name="adminSurnameForm" value="'.$adminSurnameForm.'">
							</td>
							<td>
							</td>
						</tr>
				</table>
				<table width="100%">
						<tr>
							<td>
								<input type="submit" name="cmdDbNameSetting" value="&lt; Back">
							</td>
							<td align="right">
								<input type="submit" name="cmdPlatformSetting" value="Next &gt;">
							</td>
						</tr>
					</table>';
}













###################################################################
###### STEP CONFIG SETTINGS #######################################
###################################################################
elseif($display==DISP_PLATFORM_SETTING)

{
	echo '
	             <input type="hidden" name="fromPanel" value="'.$display.'">
				<h2>
					'.$langStep6.' : '.$langCfgSetting.'
				</h2>';
	echo '
				The following values will be written in `<em>'.$configFilePath.'</em>´
			</td>
		</tr>
		<tr>
			<td>
				<h4>Campus</h4>
				'.$msg_missing_platform_data.'
				<table >
					<tr>
							<td>
								<label for="campusForm">Name</label>
							</td>
							<td colspan="2">
								<input type="text" size="40" id="campusForm" name="campusForm" value="'.$campusForm.'">
							</td>
						</tr>
					<tr>
						<td>
							<label for="urlForm">Complete URL</label>
						</td>
						<td colspan="2">
							<input type="text" size="40" id="urlForm" name="urlForm" value="'.$urlForm.'">
						</td>
					</tr>
					<tr>
						<td>
							<label for="languageForm">Main language</label>
						</td>
						<td colspan="2">
							<select id="languageForm" name="languageForm">	';
	$dirname = '../lang/';
	if($dirname[strlen($dirname)-1]!='/')
		$dirname.='/';
	$handle=opendir($dirname);
	while ($entries = readdir($handle))
	{
		if ($entries=='.'||$entries=='..'||$entries=='CVS')
			continue;
		if (is_dir($dirname.$entries))
		{
			echo '
							<option value="'.$entries.'"';
			if ($entries == $languageForm)
				echo ' selected ';
			echo '>
						'.$entries.'
									</option>';
		}
	}
	closedir($handle);
echo '
								</select>
							</font>
						</td>
					</tr>
<!--					<tr>
							<td>
								<label for="institutionForm">'.$langInstituteShortName.'</label>
							</td>
							<td colspan=2>
								<input type=text size=40 id="institutionForm" name="institutionForm" value="$institutionForm">
							</td>
						</tr>
						<tr>
							<td>
								<label for="institutionUrlForm">'.$langInstituteName.'</label>
							</td>
							<td colspan=2>
								<input type="text" size="40" id="institutionUrlForm" name="institutionUrlForm" value="$institutionUrlForm">
							</td>
						</tr>
-->
			<tr>
				<td colspan=3><br><br>
				
				
					User self-registration
				</td>
			</tr>
			<tr>
				<td>
					Simple user
				</td>
				<td>
					<input type="radio" id="allowSelfReg_1" name="allowSelfReg" value="1" checked> 
               		<label for="allowSelfReg_1">Enabled</label>
				</td>
				<td>
					<input type="radio" id="allowSelfReg_0" name="allowSelfReg" value="0"> 
               		<label for="allowSelfReg_0">Disabled</label>
				</td>
			</tr>

					<tr>
						<td>

							Course creator
						</td>
						<td>
							<input type="radio" id="allowSelfRegProf_1" name="allowSelfRegProf" value="1" checked> 
							<label for="allowSelfRegProf_1">Enabled</label>
						</td>
						<td>
							<input type="radio" id="allowSelfRegProf_0" name="allowSelfRegProf" value="0">  
							<label for="allowSelfRegProf_0">Disabled</label>
						</td>
					</tr>
		
					<tr>
						<td colspan="3">
							&nbsp;
						</td>
					
					</tr>
		
					<tr>
						<td>
							User password
						</td>
						<td>
							<input type="radio" name="encryptPassForm" id="encryptPassForm_0" value="0" checked> 
							<label for="encryptPassForm_0">Clear text</label>
						</td>
						<td>
							<input type="radio" name="encryptPassForm" id="encryptPassForm_1" value="1">
							<label for="encryptPassForm_1">Crypted</label>
						</td>
					</tr>

					<tr>
						<td>
						</td>
						<td>
						</td>
						</tr>
				</table>
				<table width="100%">
						<tr>
							<td>
								<input type="submit" name="cmdAdminSetting" value="&lt; Back">
							</td>
							<td align="right">
								<input type="submit" name="install6" value="Next &gt;">
							</td>
						</tr>
					</table>';
}














###################################################################
###### STEP LAST CHECK BEFORE INSTALL #############################
###################################################################
elseif($display==DISP_LAST_CHECK_BEFORE_INSTALL)
{
	$pathForm = str_replace("\\\\", "/", $pathForm);
	//echo "pathForm $pathForm";
	echo '
           <input type="hidden" name="fromPanel" value="'.$display.'">';

	echo '
				<h2>
					'.$langStep7.' : '.$langLastCheck.'
				</h2>
		Here are the values you entered <br>
		<Font color="red">
			Print this page to remember your admin password and other settings
		</font>
		<blockquote>

		<FIELDSET>
		<LEGEND>Database</LEGEND>
		<EM>Account</EM><br>
		Database Host : '.$dbHostForm.'<br>
		Database Username : '.$dbUsernameForm.'<br>
		Database Password : '.(empty($dbPassForm)?"--empty--":$dbPassForm).'<br>
		<em>Names</em>
		';

	if ($dbPrefixForm=="")
		echo "";
	else
		echo 'DB Prefix : '.$dbPrefixForm.'<br>';
	echo '
		Main DB Name : '.$dbNameForm.'<br>
		Statistics and Tracking DB Name : '.$dbStatsForm.'<br>
		Enable Single DB : '.($singleDbForm?$langYes:$langNo).'<br>
		</FIELDSET>

		<FIELDSET>
		<LEGEND>Admin</LEGEND>
		Administrator email : '.$adminEmailForm.'<br>
		Administrator Name : '.$adminNameForm.'<br>

		Administrator Surname : '.$adminSurnameForm.'<br>
		<table border=0 class="notethis">
			<tr>
				<td>
					<font size="2" color="red" face="arial, helvetica">
					Administrator Login : '.$loginForm.'<br>
					Administrator Password : '.(empty($passForm)?"--empty-- <B>&lt;-- Error !</B>":$passForm).'<br>
					</font>
				</td>
			<tr>
		</table>
		</FIELDSET>
		
		<FIELDSET>
		<LEGEND>Campus</LEGEND>
		Language : '.$languageForm.'<br>
		URL of claroline : '.$urlForm.'<br>
		Your campus Name : '.$campusForm.'<br>
		Your organisation : '.$institutionForm.'<br>
		URL of this organisation : '.$institutionUrlForm.'<br>
		</FIELDSET>
		<FIELDSET>
		<LEGEND>Config</LEGEND>
		Enable Tracking : '.($enableTrackingForm?$langYes:$langNo).'<br>
		Self-registration allowed : '.($allowSelfReg?$langYes:$langNo).'<br>
		Encrypt user passwords in database : ';

		if ($encryptPassForm)
			echo 'Yes';
		else
			echo 'No';
?>
		</FIELDSET>
		</blockquote>
		<table width="100%">
			<tr>
				<td>
					<input type="submit" name="cmdPlatformSetting" value="&lt; Back">
				</td>
				<td align="right">
					<input type="submit" name="cmdDoInstall" value="Install Claroline &gt;">
				</td>
			</tr>
		</table>
<?php

}



###################################################################
###### DB NAME ERROR !#########################################
###################################################################

elseif($display==DISP_DB_NAMES_SETTING_ERROR)
{
	echo '
	      		<input type="hidden" name="fromPanel" value="'.$display.'">
				<h2>
					Install Problem
				</h2>';
	if (
		$mainDbNameExist
	||	$statsDbNameExist
	)
	{
		echo "<HR>";
		if ($mainDbNameExist)
			echo '<P><B>'.$langMainDB.'</B> db (<em>'.$dbNameForm.'</em>) already exist <BR>
			<input type="checkbox" name="confirmUseExistingMainDb"  id="confirmUseExistingMainDb" value="true" '.($confirmUseExistingMainDb?'checked':'').'>
			<label for="confirmUseExistingMainDb" >I know, I want use it.</label><BR>
			<font color="red">Warning</font> : this script write in tables use by claroline.
			</P>';
		if ($statsDbNameExist && $dbStatsForm!=$dbNameForm)
			echo '
		<P>
			<B>'.$langStatDB.'</B> db ('.$dbStatsForm.') already exist
			<BR>
			<input type="checkbox" name="confirmUseExistingStatsDb"  id="confirmUseExistingStatsDb" value="true" '.($confirmUseExistingStatsDb?'checked':'').'>
			<label for="confirmUseExistingStatsDb" >I know, I want use it.</label><BR>
			<font color="red">Warning</font> 
			: this script write in tables use by claroline.
		</P>';
		echo '
		<P> 
			OR <input type="submit" name="cmdDbNameSetting" value="set DB Names">
		</P>
		<HR>';
	}
	if($mainDbNameCreationError)
		echo '<BR>'.$mainDbNameCreationError;
		echo '
				<p align="right">
					<input type="submit" name="alreadyVisited" value="|&lt; Restart from beginning">
					<input type="submit" name="cmdDbNameSetting" value="&lt; Back">
					<input type="submit" name="cmdDoInstall" value="Retry">
				</p>';

}









###################################################################
###### INSTALL INCOMPLETE!#########################################
###################################################################

elseif($display==DISP_RUN_INSTALL_NOT_COMPLETE)
{
	echo '
	      <input type="hidden" name="fromPanel" value="'.$display.'">
				<h2>
					Install Problem
				</h2>';
	if($mainDbNameCreationError)
		echo "<BR>".$mainDbNameCreationError;
	if($fileAccessCreationError)
		echo "<BR>".$statsDbNameCreationError;
	if($fileAccessCreationError)
		echo "<BR>Error on creation : file <EM>".$htAccessName."</EM> in <U>".realpath($htAccessPath)."</U><br>";
	if($filePasswordCreationError)
		echo "<BR>Error on creation : file <EM>".$htPasswordName."</EM> in <U>".realpath($htPasswordPath)."</U><br>";
	if ($fileConfigCreationError)
	echo '
	<b>
		<font color="red">
			Probably, your script doesn\'t have write access to the config directory
		</font>
		<br>
		<SMALL>
			<EM>('.realpath("../inc/conf/").')</EM>
		</SMALL>
	</b>
	<br><br>
	You probably do not have write access on claroline root directory,
	i.e. you should <EM>CHMOD 777</EM> or <EM>755</EM> or <EM>775</EM><br><br>

Your problems can be related on two possible causes :<br>
<UL>
	<LI>
		Permission problems. 
		<br>Try initially with 
		<EM>chmod 777 -R</EM> and increase restrictions gradually.
	</LI>
    <LI>
		PHP is running in
		<a href="http://www.php.net/manual/en/features.safe-mode.php" target="_phpman">
		SAFE MODE</a>. 
		If possible, try to switch it off.
	</LI>
</UL>
<a href="http://www.claroline.net/forum/viewtopic.php?t=753">Read about this problem in Support Forum</a>';

	if ($coursesRepositorySysMissing)
	{
		echo '<BR> <em>$coursesRepositorySys = '.$coursesRepositorySys.'</em> : <br>dir is missing';
		echo '<BR>'.$coursesRepositorySys.' is missing';
	}

	if ($coursesRepositorySysWriteProtected)
	{
		echo '<BR><b><em>'.$coursesRepositorySys.'</em> is Write Protected.</b>
		Claroline need to have write right to create course.<br>
		change rigth on this directory and retry.';
	}

	if ($garbageRepositorySysMissing)
	{
		echo '<BR> <em>$garbageRepositorySys = '.$garbageRepositorySys.'</em> : <br>dir is missing';
	}
	
	if ($garbageRepositorySysWriteProtected)
	{
		echo '
		<BR>
		<b>
			<em>'.$garbageRepositorySys.'</em>
			is Write Protected.
		</b>
		Claroline need to have write right to trash courses.<br>
		change rigth on this directory and retry.';
	}

	echo '
				<p align="right">
					<input type="submit" name="alreadyVisited" 		value="Restart from beginning">
					<input type="submit" name="cmdPlatformSetting" 	value="Previous">
					<input type="submit" name="cmdDoInstall" 		value="Retry">
				</p>';

}











###################################################################
###### STEP RUN_INSTALL_COMPLETE !#################################
###################################################################
elseif($display==DISP_RUN_INSTALL_COMPLETE)
{
?>
			<h2>
				Claroline Installation succeeds
			</h2>
			<br>
			<br>
			<b>
				Last tip
			</b> 
			: we highly recommend that you protect or remove installer directory.
			
			
</form>
<form action="../../" method="POST">
		<input type="submit" value="Go to your newly created campus">
</form>
<?php
}	// STEP RUN_INSTALL_COMPLETE

else
{
	echo '
			<pre>$display</pre not set. 
			<BR>
			Error in script. <BR>
			<BR>
			Please inform  <a href=mailto:moosh@claroline.net">claroline team</a> )';
}

?>
		</td>
	</tr>
</table>
</form>

</center>
</body>
</html>
<?php
















/**
 * check extention and  write  if exist  in a  <LI></LI>
 *
 * @params string	$extentionName 		name  of  php extention to be checked
 * @params boolean	$echoWhenOk			true => show ok when  extention exist
 * @author Christophe Gesché
 * @desc check extention and  write  if exist  in a  <LI></LI>
 *
 */

function warnIfExtNotLoaded($extentionName,$echoWhenOk=false)
{
	if (extension_loaded ($extentionName))
	{
		if ($echoWhenOk)
			echo "
				<LI> $extentionName - ok </LI> ";
	}
	else
	{
		echo '
				<LI>
					<font color="red">Warning !</font> 
					'.$extentionName.' is missing.</font>
				<br>
				Configure php to use this extention
				(see <a href="http://www.php.net/'.$extentionName.'">'.$extentionName.' manual</a>).
				</LI>';
	}
}

/**
 * function topRigthPath()
 * @desc search read and write access from the given directory to root
 * @param path string path where begin the scan
 * @return array with 2 fields "topWritablePath" and "topReadablePath"
 * @author Christophe Gesché
 *
 * $serchtop log is only use for debug
 */

function topRigthPath($path=".")
{
	$whereIam = getcwd();
	chdir($path);
	$pathToCheck = realpath(".");
	$previousPath=$pathToCheck."*****";

	$search_top_log = "top Rigth Path<dl>";
	while(!empty($pathToCheck))
	{
		$pathToCheck = realpath(".");
		if (is_writable($pathToCheck))
			$topWritablePath = $pathToCheck;
		if (is_readable($pathToCheck))
			$topReadablePath = $pathToCheck;
		$search_top_log .= "<dt>".$pathToCheck."</dt><dd>write:".(is_writable($pathToCheck)?"open":"close")." read:".(is_readable($pathToCheck)?"open":"close")."</dd>";
		if ($pathToCheck!="/" && $pathToCheck!=$previousPath &&(is_writable($pathToCheck)||is_readable($pathToCheck)))
		{
			chdir("..") ;
			$previousPath=$pathToCheck;
		}
		else
		{
			$pathToCheck ="";
		}

	}
	$search_top_log .= "</dl>
 	topWritablePath = ".$topWritablePath."<br>
	topReadablePath = ".$topReadablePath;

	//echo $search_top_log;
	chdir($whereIam);
	return array("topWritablePath" => $topWritablePath, "topReadablePath" => $topReadablePath);
};

function check_if_db_exist($db_name,$db=null)
{
	
	// I HATE THIS SOLUTION . 
	// It's would be better to have a SHOW DATABASE case insensitive
	if (PHP_OS!="WIN32"&&PHP_OS!="WINNT")
	{
		$sql = "SHOW DATABASES LIKE '".$db_name."'";
	}
	else 
	{
		$sql = "SHOW DATABASES LIKE '".strtolower($db_name)."'";
	}
	
	if ($db)
	{
		$res = mysql_query($sql,$db);
	}
	else 
	{
		$res = mysql_query($sql);
	}
	$foundDbName = mysql_fetch_array($res, MYSQL_NUM);
	return $foundDbName;
}
?>