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
define ("DISP_CONFIG_SETTING",__LINE__);
define ("DISP_LAST_CHECK_BEFORE_INSTALL",__LINE__);
define ("DISP_RUN_INSTALL_NOT_COMPLETE",__LINE__);
define ("DISP_RUN_INSTALL_COMPLETE",__LINE__);
/* LET DEFINE ON SEPARATE LINES !!!*/

error_reporting(E_ERROR | E_WARNING | E_PARSE);

// Place of Config file
$configFileName = "claro_main.conf.php";
$configFilePath = "../inc/conf/".$configFileName;




if (!empty($_GET))  	{extract($_GET, EXTR_OVERWRITE);}
if (!empty($_POST)) 	{extract($_POST, EXTR_OVERWRITE);}
if (!empty($_SERVER)) 	{extract($_SERVER, EXTR_OVERWRITE);}

$confirmUseExistingPMADb = $_REQUEST['confirmUseExistingPMADb'];

$newIncludePath ="../inc/";
include ($newIncludePath."installedVersion.inc.php");

include ("../lang/english/trad4all.inc.php");
include ("../lang/english/install.inc.php");

include ($newIncludePath."lib/auth.lib.inc.php"); // to generate pass and to cryto it if needed
include ($newIncludePath."lib/config.lib.inc.php");


/* DISPLAY ORDER */

$display_order = array
(	DISP_WELCOME, 
	DISP_LICENCE,
	DISP_DB_CONNECT_SETTING,
	DISP_DB_NAMES_SETTING,
	DISP_ADMIN_SETTING,
	DISP_CONFIG_SETTING,
	DISP_LAST_CHECK_BEFORE_INSTALL,
	DISP_RUN_INSTALL_NOT_COMPLETE,
	DISP_RUN_INSTALL_COMPLETE);



##### STEP 0 INITIALISE FORM VARIABLES IF FIRST VISIT ##################
//$rootSys="'.realpath($pathForm).'";

$topRigthPath = topRigthPath(); // to known right (read and write)

if(!$_REQUEST['alreadyVisited'] || $_REQUEST['resetConfig']) // on first step prupose values
{

	$dbHostForm		= "localhost";
	$dbUsernameForm	= "root";

	$dbPrefixForm	= "CL150RC1";
	$dbNameForm		= $dbPrefixForm."Main";
	$dbStatsForm    = $dbPrefixForm."Main";
	$dbPMAForm		= $dbPrefixForm."Main";
 	$dbPrefixForm	= $dbPrefixForm."_";
 	$singleDbForm	= $singleDbForm = true;


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
	$educationForm	= "Albert Einstein";
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
//  * Create and fill PMA Database
//  * Create and fill STAT Database
//  * Create  some  directories
//  * Write the config file
//  * Protect some  directory with an .htaccess (work only  for apache)


// SET default display
$display=DISP_WELCOME;
if($_REQUEST['cmdShowLicence'])
{
	$display = DISP_LICENCE;
}
elseif($_REQUEST['setDbAccountProperties'])
{
	$display = DISP_DB_CONNECT_SETTING;
}
elseif($_REQUEST['install6'] || $_REQUEST['back6'] )
{
	$display=DISP_LAST_CHECK_BEFORE_INSTALL;
}
elseif($_REQUEST['ADMIN_SETTING'])
{
	$display = DISP_ADMIN_SETTING;
}
elseif($_REQUEST['CONFIG_SETTING'])
{
	$display = DISP_CONFIG_SETTING;
}
elseif($_REQUEST['doInstall'])
{
	// in this  part. Script try to run Install
	// if  all is right $display still DISP_RUN_INSTALL_COMPLETE set on start
	// if  any problem happend, $display is switch to DISP_RUN_INSTALL_NOT_COMPLETE
	// and a  flag to mark what's happend is set.
	// in DISP_RUN_INSTALL_NOT_COMPLETE the screen show an explanation about problem and
	// prupose to back  to correct or to accept and continue.

	// First block is about database
	// Second block is  writing config
	// third block is building paths
	// Forth block check some right

	$display=DISP_RUN_INSTALL_COMPLETE; //  if  all is righ $display don't change
    
	if (empty($adminSurnameForm)||empty($passForm)||empty($loginForm)||empty($adminNameForm))
	{
		$adminDataMissing = true;
		$display=DISP_RUN_INSTALL_NOT_COMPLETE;
	}

	$db = @mysql_connect("$dbHostForm", "$dbUsernameForm", "$dbPassForm");
	if (mysql_errno()>0) // problem with server
	{
		$no = mysql_errno();     $msg = mysql_error();
		$noMysqlConnection =true;
		$display=DISP_RUN_INSTALL_NOT_COMPLETE;
	}
	else
	{
		// PATCH TO ACCEPT Prefixed DBs
		$mainDbName 	= "$dbNameForm";
		$statsDbName 	= "$dbStatsForm";
		$pmaDbName 	= "$dbPMAForm";
		$resBdbHome = @mysql_query("SHOW VARIABLES LIKE 'datadir'");
		$mysqlRepositorySys = mysql_fetch_array($resBdbHome,MYSQL_ASSOC);
		$mysqlRepositorySys = $mysqlRepositorySys ["Value"];

		/////////////////////////////////////////
		// MAIN DB                             //
		// DB with central info  of  Claroline //

		mysql_query("CREATE DATABASE `$mainDbName`");
		if (mysql_errno() >0)
		{
			if (mysql_errno() == 1007)
			{
				if ($confirmUseExistingMainDb)
				{
					$runfillMainDb = true;
				}
				else
				{
					$mainDbNameExist = true;
					$display=DISP_RUN_INSTALL_NOT_COMPLETE;
				}
			}
			else
			{
				$mainDbNameCreationError = "mysql Main Db".$mainDbName."<br><b><small>".mysql_errno()." ".mysql_error()."</small></b>";
				$display=DISP_RUN_INSTALL_NOT_COMPLETE;
			}
		}
		else
		{
			$runfillMainDb = TRUE;
			$confirmUseExistingMainDb = TRUE;
		}


		/////////////////////////////////////////
		// STATS DB                            //
		// DB with tracking info of  Claroline //
		if($statsDbName != $mainDbName)
		{
			if(!$singleDbForm)
			{
				// multi DB mode AND tracking has its own DB so create it
				mysql_query("CREATE DATABASE `$statsDbName`");
				if (mysql_errno() >0)
				{
					if (mysql_errno() == 1007)
					{
						if ($confirmUseExistingStatsDb)
						{
							$runfillStatsDb = true;
						}
						else
						{
							$statsDbNameExist = true;
							$display=DISP_RUN_INSTALL_NOT_COMPLETE;
						}
					}
					else
					{
						$statsDbNameCreationError ="mysql Stats Db ".$statsDbName."<br><b><small>".mysql_errno()." ".mysql_error()."</small></b>";
						$display=DISP_RUN_INSTALL_NOT_COMPLETE;
					}
				}
				else
				{
					$runfillStatsDb = true;
				}
			}
			else
			{
				// single DB mode so $statsDbName MUST BE the SAME than $mainDbName
				// because it's actually singleDB and not singleCourseDB
				$statsDbName = $mainDbName;
				$runfillStatsDb = true;
			}
		}
		else
		{
			$runfillStatsDb = true;
			$confirmUseExistingStatsDb = TRUE;
		}

		/////////////////////////////////////////
		// PMA DB                              //
		// DB with info for extention of PMA   //

		if($pmaDbName != $mainDbName)
		{
			if(!$singleDbForm)
			{
				mysql_query("CREATE DATABASE `$pmaDbName`");
				if (mysql_errno() > 0)
				{
					if (mysql_errno() == 1007)
					{
						if ($confirmUseExistingPMADb)
						{
							$runfillPMADb = true;
						}
						else
						{
							$pmaDbNameExist = true;
							$display=DISP_RUN_INSTALL_NOT_COMPLETE;
						}
					}
					else
					{
						$pmaDbNameCreationError ="mysql PhpMyAdmin Db (".$pmaDbName.")<br><b><small>(".mysql_errno().") ".mysql_error()."</small></b>";
						$display=DISP_RUN_INSTALL_NOT_COMPLETE;
					}
				}
				else
				{
					$runfillPMADb = true;
				}
			}
			else
			{
				// single DB mode so $pmaDbName MUST BE the SAME than $mainDbName
				$pmaDbName = $mainDbName;
				$runfillPMADb = true;
			}
		}
		else
		{
			$runfillPMADb = true;
			$confirmUseExistingPMADb = TRUE;
		}

		if ($runfillMainDb)
		{
			mysql_select_db ($mainDbName);
			include ("./createMainBase.inc.php");
			include ("./fillMainBase.inc.php");
		}
		if ($runfillStatsDb)
		{
			mysql_select_db ($statsDbName);
			include ("./createStatBase.inc.php");
			include ("./fillStatBase.inc.php");
		}

		if ($runfillPMADb)
		{
			mysql_select_db ($pmaDbName);
			include ("./createPMABase.inc.php");
			include ("./fillPMABase.inc.php");
		}
	}

	// FILE SYSTEM OPERATION
	//
	// Build path

	$rootSys					=	str_replace("\\","/",realpath($pathForm)."/") ;
	$coursesRepositoryAppend	= "";
	$coursesRepositorySys = $rootSys.$coursesRepositoryAppend;
	@mkdir($coursesRepositorySys,0777);
	$clarolineRepositoryAppend  = "claroline/";
	$clarolineRepositorySys		= $rootSys.$clarolineRepositoryAppend;
	$garbageRepositorySys	= str_replace("\\","/",realpath($clarolineRepositorySys)."/claroline_garbage");
	@mkdir($garbageRepositorySys,0777);

	########################## WRITE claro_main.conf.php ##################################
	// extract the path to append to the url
	// if Claroline is not installed on the web root directory

	//$urlAppendPath = ereg_replace ("claroline/install/index.php", "", $PHP_SELF);

	// here I want find  something to get garbage out of documentRoot


	$fd=@fopen($configFilePath, "w");
	if (!$fd)
	{
		$fileConfigCreationError = true;
		$display=DISP_RUN_INSTALL_NOT_COMPLETE;
	}
	else
	{
		// str_replace() removes \r that cause squares to appear at the end of each line
		$stringConfig=str_replace("\r","",'<?php

# CLAROLINE version '.$clarolineVersion.'
# File generated by /install/index.php script - '.date("r").'

//----------------------------------------------------------------------
// CLAROLINE
//----------------------------------------------------------------------
// Copyright (c) 2001-2003 Universite catholique de Louvain (UCL)
//----------------------------------------------------------------------
// This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
// as published by the FREE SOFTWARE FOUNDATION. The GPL is available
// through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
//----------------------------------------------------------------------
// Authors: see \'credits\' file
//----------------------------------------------------------------------

/***************************************************************
*           CONFIG OF VIRTUAL CAMPUS
****************************************************************
GOAL
****
List of variables to be modified by the campus site administrator.
File has been CHMODDED 0444 by install.php.
CHMOD 0666 (Win: remove read-only file property) to edit manually
*****************************************************************/

/*

******************
** WARNING !!!  **
******************

This  file  would  parsed.
A variable must be in one line.
and they doesn\'t actually have an ; in value of a variable

**********************************************************************/

// This file was generate by script /install/index.php
// on '.date("r").'
// REMOTE_ADDR : 		'.$_REMOTE_ADDR.' = '.gethostbyaddr($REMOTE_ADDR).'
// REMOTE_HOST :		'.$REMOTE_HOST.'
// REMOTE_PORT : 		'.$REMOTE_PORT.'
// REMOTE_USER : 		'.$REMOTE_USER.'
// REMOTE_IDENT :	 	'.$REMOTE_IDENT.'
// HTTP_USER_AGENT : 	'.$HTTP_USER_AGENT.'
// SERVER_NAME :		'.$SERVER_NAME.'
// HTTP_COOKIE :		'.$HTTP_COOKIE.'

$rootWeb 					= 	"'.$urlForm.'";
$urlAppend					=	"'.$urlAppendPath.'";
$rootSys					=	"'.$rootSys.'" ;

// MYSQL
$dbHost 			= "'.$dbHostForm.'";
$dbLogin 			= "'.$dbUsernameForm.'";
$dbPass				= "'.$dbPassForm.'";

$mainDbName			= "'.$mainDbName.'";
$statsDbName		= "'.$statsDbName.'";
$pmaDbName			= "'.$pmaDbName.'";
$dbNamePrefix		= "'.$dbPrefixForm.'"; // prefix all created base (for courses) with this string

$is_trackingEnabled	= '.trueFalse($enableTrackingForm).';
$singleDbEnabled	= '.trueFalse($singleDbForm).'; // DO NOT MODIFY THIS
$courseTablePrefix	= "'.($singleDbForm?'crs_':'').'"; // IF NOT EMPTY, CAN BE REPLACED BY ANOTHER PREFIX, ELSE LEAVE EMPTY
$dbGlu				= "'.($singleDbForm?'_':'`.`').'"; // DO NOT MODIFY THIS
$mysqlRepositorySys = "'.str_replace("\\","/",realpath($mysqlRepositorySys)."/").'";

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
$garbageRepositorySys		= "'.$garbageRepositorySys.'";
//for new login module
//uncomment these to activate ldap
//$extAuthSource[\'ldap\'][\'login\'] = "./claroline/auth/ldap/login.php";
//$extAuthSource[\'ldap\'][\'newUser\'] = "./claroline/auth/ldap/newUser.php";

$CourseProgram="http://www.ucl.ac.be/etudes/cours";

// Strings
$siteName				=	"'.$campusForm.'";

$administrator["name"]	=	"'.$adminSurnameForm.' '.$adminNameForm.'";
$administrator["phone"]	=	"'.$adminPhoneForm.'";
$administrator["email"]	=	"'.$adminEmailForm.'";

$educationManager["name"]	=	"'.$educationForm.'";
$educationManager["phone"]	=	"'.$educationPhoneForm.'";
$educationManager["email"]	=	"'.$educationEmailForm.'";
$institution["name"]		=	"'.$institutionForm.'";
$institution["url"]			=	"'.$institutionUrlForm.'";

// param for new and future features
$checkEmailByHashSent 			= 	'.trueFalse($checkEmailByHashSent).';
$ShowEmailnotcheckedToStudent 	= 	'.trueFalse($ShowEmailnotcheckedToStudent).';
$userMailCanBeEmpty 			= 	'.trueFalse($userMailCanBeEmpty).';
$userPasswordCrypted			=	'.trueFalse($encryptPassForm).';
$allowSelfReg					= '.trueFalse($allowSelfReg).';
$allowSelfRegProf				= '.trueFalse($allowSelfRegProf).';

//backgrounds
$colorLight	=	"#99CCFF"; //
$colorMedium= 	"#6699FF"; // these 3 colors are used in header
$colorDark	= 	"#000066"; //

$platformLanguage 	= 	"'.$languageForm.'";

$clarolineVersion	=	"'.$clarolineVersion.'";
$versionDb 			= 	"'.$versionDb.'";
?>');

######### DEALING WITH FILES #########################################

		fwrite($fd, $stringConfig);


/**
 * Config file to undist
 */

$arr_file_to_undist = 
array (
$newIncludePath."conf/add_course.conf.php",
$newIncludePath."conf/admin.usermanagement.conf.php",
$newIncludePath."conf/agenda.conf.inc.php",
$newIncludePath."conf/announcement.conf.inc.php",
$newIncludePath."conf/course_info.conf.php",
$newIncludePath."conf/export.conf.php",
$newIncludePath."conf/group.conf.php",
$newIncludePath."conf/group.document.conf.php",
$newIncludePath."conf/index.conf.inc.php",
$newIncludePath."conf/profile.conf.inc.php",
$newIncludePath."conf/user.conf.php",
$newIncludePath."conf/work.conf.inc.php"
);

foreach ($arr_file_to_undist As $undist_this)
	claro_undist_file($undist_this);

//$output_undist_job ="<h3>Others conf files</h3><ul>";
//foreach ($arr_file_to_undist As $undist_this)
//{
//	$output_undist_job .="<li>Conf file : ".basename ($undist_this);
//	if (claro_undist_file($undist_this))
//	{
//		$output_undist_job .=" added";
//	}
//	else
//	{
//		$output_undist_job .=" not change.";
//	};
//	$output_undist_job .="</li>";
//}
//$output_undist_job .="</ul>";						
		
#### CREATE AND WRITE .HTACCESS AND .HTPASSWD4ADMIN HIDDEN FILES #####

		if (PHP_OS!="WIN32"&&PHP_OS!="WINNT")
		{
			$passFormToStore=crypt($passForm);
		}
		else
		{
			$passFormToStore=$passForm;
		}

		// ADD htPassword
		
		$htPasswordPath = "../admin/";
		$htPasswordName = ".htpasswd4admin";
		@rename ($htPasswordPath.$htPasswordName,		 	$htPasswordPath.$htPasswordName."_old");

		$filePasswd=@fopen($htPasswordPath.$htPasswordName, "w");
		if (!$filePasswd)
		{
			$filePasswordCreationError = true;
			$display=DISP_RUN_INSTALL_NOT_COMPLETE;
		}
		else
		{
			$stringPasswd="$loginForm:$passFormToStore";
			@fwrite($filePasswd, $stringPasswd);
		}

		
		// htaccess files

		$htAccessPath = "../admin/";
		$htAccessName = ".htaccess";
		@rename ($htAccessAdminPath.$htAccessName, 			$htAccessAdminPath.$htAccessName."_old");
		$fileAccess=@fopen($htAccessPath.$htAccessName, "w");
		if (!$fileAccess)
		{
			$fileAccessCreationError = true;
			$display=DISP_RUN_INSTALL_NOT_COMPLETE;
		}
		else
		{
			$stringAccess='AuthName "Administration Claroline"
			AuthType Basic
			Require valid-user
			AuthUserFile "'.realpath($htPasswordPath).'/'.$htPasswordName.'"';

			fwrite($fileAccess, $stringAccess);
		}

		$htAccessPath = "../lang/";
		$fileAccess=@fopen($htAccessPath.$htAccessName, "w");
		if (!$fileAccess)
		{
			$fileAccessCreationError = true;
			$display=DISP_RUN_INSTALL_NOT_COMPLETE;
		}
		else
		{
			$stringAccess='AuthName "Administration Claroline"
			AuthType Basic
			Require valid-user
			AuthUserFile "'.realpath($htPasswordPath).'/'.$htPasswordName.'"';

			fwrite($fileAccess, $stringAccess);
		}

		$htAccessPath = "../sql/";
		$fileAccess=@fopen($htAccessPath.$htAccessName, "w");
		if (!$fileAccess)
		{
			$fileAccessCreationError = true;
			$display=DISP_RUN_INSTALL_NOT_COMPLETE;
		}
		else
		{
			$stringAccess='AuthName "Administration Claroline"
			AuthType Basic
			Require valid-user
			AuthUserFile "'.realpath($htPasswordPath).'/'.$htPasswordName.'"';

			fwrite($fileAccess, $stringAccess);
		}


		$htAccessPath = "../sources/";
		$fileAccess=@fopen($htAccessPath.$htAccessName, "w");
		if (!$fileAccess)
		{
			$fileAccessCreationError = true;
			$display=DISP_RUN_INSTALL_NOT_COMPLETE;
		}
		else
		{
			$stringAccess='AuthName "Administration Claroline"
			AuthType Basic
			Require valid-user
			AuthUserFile "'.realpath($htPasswordPath).'/'.$htPasswordName.'"';

			fwrite($fileAccess, $stringAccess);
		}

############ PROTECTING FILES AGAINST WEB WRITING ###################
	}

// Check File System
	
	$coursesRepositorySysWriteProtected = FALSE;
	$coursesRepositorySysMissing 	    = FALSE;
	$garbageRepositorySysWriteProtected = FALSE;
	$garbageRepositorySysMissing        = FALSE;

	if (file_exists($coursesRepositorySys))
	{
		if (!is_writable($coursesRepositorySys))
		{
			$coursesRepositorySysWriteProtected = TRUE;
			$display=DISP_RUN_INSTALL_NOT_COMPLETE;
		}
	}
	else
	{
		$coursesRepositorySysMissing = TRUE;
		$display=DISP_RUN_INSTALL_NOT_COMPLETE;
	}


	if (file_exists($garbageRepositorySys))
	{
		if (!is_writable($garbageRepositorySys))
		{
			$garbageRepositorySysWriteProtected = TRUE;
			$display=DISP_RUN_INSTALL_NOT_COMPLETE;
		}
	}
	else
	{
		$garbageRepositorySysMissing = TRUE;
		$display=DISP_RUN_INSTALL_NOT_COMPLETE;
	}

}

if ($doCheckDatabaseAccountSetting)
{
	// Check Connection //
	$databaseParam_ok = true;
	$db = @mysql_connect("$dbHostForm", "$dbUsernameForm", "$dbPassForm");
	if (mysql_errno()>0) // problem with server
	{


		$no = mysql_errno();
		$msg = mysql_error();

		$msg_no_connection = "<font color=\"red\"><HR>[".$no."] - ".$msg."<HR>";
		$msg_no_connection .= "Error with Server Mysql.<br>";
		if ($no=="2005")
		$msg_no_connection .= "<I>".$dbHostForm."</I> is probably false<br>";
		elseif ($no=="1045")
		$msg_no_connection .= "login : (<I>".$dbUsernameForm."</I>) or password (<I>".$dbPassForm."</I>) probably false<br>";
		else
		$msg_no_connection .= "check server status and  connection parameters<br>";

		$msg_no_connection .= "<HR></font>";
		$databaseParam_ok = false;
		$display = DISP_DB_CONNECT_SETTING;
	}
	else
	{
		// Check DB Names  //

		$sql = "show databases";
		$res = mysql_query($sql,$db);
		while ($__dbName = mysql_fetch_array($res, MYSQL_NUM))
		{
			$existingDbs[]=$__dbName[0];
		}
		unset($__dbName);
		$display = DISP_DB_NAMES_SETTING;
	}
}

if ($doCheckDatabaseName)
{

	// re Check Connection //
	$databaseParam_ok = true;
	$db = @mysql_connect("$dbHostForm", "$dbUsernameForm", "$dbPassForm");
	if (mysql_errno()>0) // problem with server
	{

		$no = mysql_errno();
		$msg = mysql_error();

		$msg_no_connection = "<font color=\"red\"><HR>[".$no."] - ".$msg."<HR>";
		$msg_no_connection .= "Error with Server Mysql.<br>";
		if ($no=="2005")
		$msg_no_connection .= "<I>".$dbHostForm."</I> is probably false<br>";
		elseif ($no=="1045")
		$msg_no_connection .= "login : (<I>".$dbUsernameForm."</I>) or password (<I>".$dbPassForm."</I>) probably false<br>";
		else
		$msg_no_connection .= "check server status and  connection parameters<br>";

		$msg_no_connection .= "<HR></font>";
		$databaseParam_ok = false;
		$display = DISP_DB_CONNECT_SETTING;
	}
	else
	{


		// Check DB Names  //
		$sql = "show databases LIKE '".$dbNameForm."'";
		$res = mysql_query($sql,$db);
		$valMain = mysql_fetch_array($res, MYSQL_NUM);

		$sql = "show databases LIKE '".$dbStatsForm."'";
		$res = mysql_query($sql,$db);
		$valStat = mysql_fetch_array($res, MYSQL_NUM);
		$sql = "show databases LIKE '".$dbPMAForm."'";

		$res = mysql_query($sql,$db);
		$valPMA = mysql_fetch_array($res, MYSQL_NUM);

		if ($valMain||$valPMA||$valStat)
		{
			$databaseAlreadyExist = true;
		}
		else
		{
			$databaseAlreadyExist = false;
		}
		$display=DISP_CONFIG_SETTING;

	//	$display = DISP_DB_NAMES_SETTING;
 	}
}




























// BEGIN OUTPUT





	
// COMMON OUTPUT Including top of form  and list of hidden values
?>
<html>
<head>

<title>
-- Claroline installation -- version <?php echo $clarolineVersion ?>
</title>

<link rel="stylesheet" href="../css/default.css" type="text/css">

<Style media="print" >
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
			<input type=\"hidden\" name=\"urlEndFormvalue=\"$urlEndForm\">
			<input type=\"hidden\" name=\"pathForm\" value=\"".str_replace("\\","/",realpath($pathForm)."/")."\" >

			<input type=\"hidden\" name=\"dbHostForm\" value=\"$dbHostForm\">
			<input type=\"hidden\" name=\"dbUsernameForm\" value=\"$dbUsernameForm\">

			<input type=\"hidden\" name=\"singleDbForm\" value=\"".$singleDbForm."\">

			<input type=\"hidden\" name=\"dbPrefixForm\" value=\"$dbPrefixForm\">
			<input type=\"hidden\" name=\"dbNameForm\" value=\"$dbNameForm\">
            <input type=\"hidden\" name=\"dbStatsForm\" value=\"$dbStatsForm\">
            <input type=\"hidden\" name=\"dbPMAForm\" value=\"$dbPMAForm\">
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
			<input type=\"hidden\" name=\"educationForm\" value=\"$educationForm\">
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
			<input type=\"hidden\" name=\"confirmUseExistingPMADb\" value=\"$confirmUseExistingPMADb\">


";

 ##### PANNELS  ######
 #
 # INSTALL IS a big form
 # Too big to show  in one time.
 # PANEL show some  field to edit, all other are in HIDDEN FIELDS

###### STEP 1 REQUIREMENTS ##############################################
if ($display==DISP_WELCOME)
{
	echo "
<h2>
	".$langRequirements."
</h2>";
	// check if an claroline configuration file doesn't already exists.
	if (
		file_exists("../inc/conf/claro_main.conf.inc.php")
	||	file_exists("../inc/conf/claro_main.conf.php")
	|| 	file_exists("../inc/conf/config.inc.php")
	|| file_exists("../include/config.inc.php")
	|| file_exists("../include/config.php"))
	{
		echo "
 <div style=\"background-color:#FFFFFF;margin:20px;padding:5px\">
	<p>
		<b>
			<font color=\"red\">
				Warning ! The installer has detected an existing
				claroline platform on your system.
				<br>
				<ul>";
		if ($is_upgrade_available)
		{
			echo "
				<li>For claroline upgrade click
				<a href=\"../admin/maintenance/upgrade.php\">here.</a>
				<br>";
		}
		else
		{
			echo "
				<li>For claroline upgrade please wait a stable release.
				<br>";
		}
		echo 	"
				<li>For claroline overwrite click on  \"next\" button
				</ul>
			</font>
		</b>
	</p>
</div>";
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
	echo "
	<p>Read Thouroughly <a href=\"../../INSTALL.txt\">INSTALL.txt</a> 
	before proceeding to install.</p>
	<h4>Checking requirement</h4>
<ul>

	<li>
		Checking PHP extentions.
		<UL>";

	warnIfExtNotLoaded("standard");
	warnIfExtNotLoaded("session");
	warnIfExtNotLoaded("mysql");
	warnIfExtNotLoaded("zlib");
	warnIfExtNotLoaded("pcre");
//	warnIfExtNotLoaded("exif"); // exif  would be needed later for pic view properties.
//	warnIfExtNotLoaded("nameOfExtention"); // list here http://www.php.net/manual/fr/resources.php

	echo "
		</UL>
	</LI>
	<LI>
		Checking PHP settings
		<UL>
			".(ini_get('register_globals')?
			"
			<!--LI>register_globals ON</LI-->":
			"
			<LI>
				<font color=\"red\">Warning !</font> register_globals is set to <strong>off</strong>.
				<br>
				Change the following parameter in your <i>php.ini</i> file to this value :<br>
				<font color=\"blue\">
				<code>register_globals = on</code>
				</font>
			</LI>"
			).(ini_get('magic_quotes_gpc')?
			"
			<!--LI>magic_quotes_gpc ON</LI-->":
			"
			<LI>
				<font color=\"red\">Warning !</font> magic_quotes_gpc is set to <strong>off</strong>.
				<br>
				Change the following parameter in your <i>php.ini</i> file to this value :<br>
				<font color=\"blue\">
				<code>magic_quotes_gpc = on</code>
				</font>
			</LI>"
			).(ini_get('display_errors')?
			"
			<LI>
				<font color=\"red\">Warning !</font> display_errors is set to <strong>on</strong>.
				<br>
				For production, change the following parameter in your <i>php.ini</i> file to this value :<br>
				<font color=\"blue\">
				<code>display_errors = off</code> 
				</font>
				<br>
				".((ini_get('error_reporting') & E_NOTICE )?"
				<font color=\"red\">Warning !</font> error_reporting include <strong>E_NOTICE</strong>.
				<br>
				Change the following parameter in your <i>php.ini</i> file to this value :<br>
				<font color=\"blue\">
				<code>error_reporting  =  E_ALL & ~E_NOTICE</code>
				</font>":"") ."
			</LI>":
			"
			<!--LI>
				display_errors OFF
			</LI-->"
			)."
</UL>
	</li>
	<li>Checking file access to web directory.
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
<p align=\"right\"><input type=\"submit\" name=\"cmdShowLicence\" value=\"Next >\"></p>";

}

############### STEP 2 LICENSE  ###################################

elseif($display==DISP_LICENCE)
{
	echo "
				<h2>
					 ".$langLicence."
				</h2>
				<P>
				Claroline is free software, distributed under GNU General Public licence (GPL).
				Please read the licence and click 'I accept'.
				<a href=\"../license/gpl_print.txt\">".$langPrintVers."</a>
				</P>
				<textarea wrap=\"virtual\" cols=\"65\" rows=\"15\">";
	include ('../license/gpl.txt');
	echo "</textarea>

		</td>
	</tr>
	<tr>
		<td>
			<table width=\"100%\">
				<tr>
					<td>
					</td>
					<td align=\"right\">
					<input type=\"submit\" name=\"back\" value=\"< Back\">
					<input type=\"submit\" name=\"setDbAccountProperties\" value=\"I  accept >\">
					</td>
				</tr>
			</table>";

}



elseif($display==DISP_DB_CONNECT_SETTING)
{

###### STEP 3 MYSQL DATABASE SETTINGS ##############################################

	echo "
				<h2>
					".$langDBSetting." 
				</h2>
				
			</td>
		</tr>
		<tr>
			<td>
				<h4>".$langDBConnectionParameters."</h4>
				<p>
				Enter here the parameters given by your database server administrator.
				</p>
				".$msg_no_connection."
				<table width=\"100%\">
					<tr>
						<td>
							<Label for=\"dbHostForm\">".$langDBHost."</label>
						</td>
						<td>
							<input type=\"text\" size=\"25\" id=\"dbHostForm\" name=\"dbHostForm\" value=\"".$dbHostForm."\">
						</td>
						<td>
							".$langEG." localhost
						</td>
					</tr>
					<tr>
						<td>
							<Label for=\"dbUsernameForm\">".$langDBLogin."</label>
						</td>
						<td>
							<input type=\"text\"  size=\"25\" id=\"dbUsernameForm\" name=\"dbUsernameForm\" value=\"".$dbUsernameForm."\">
						</td>
						<td>
							".$langEG." root
						</td>
					</tr>
					<tr>
						<td>
							<Label for=\"dbPassForm\">".$langDBPassword."</label>
						</td>
						<td>
							<input type=\"text\"  size=\"25\" id=\"dbPassForm\" name=\"dbPassForm\" value=\"$dbPassForm\">
						</td>
						<td>
							".$langEG." ".generePass(8)."
						</td>
					</tr>
				</table>
				<h4>".$langDBUse."</h4>
				<table width=\"100%\">
					<tr>
							<td>
									<Label for=\"enableTrackingForm\">Tracking</label>
							</td>
							<td>
									<input type=\"radio\" id=\"enableTrackingForm\" name=\"enableTrackingForm\" value=\"1\" checked> Enabled
							</td>
							<td>
									<input type=\"radio\" id=\"enableTrackingForm\" name=\"enableTrackingForm\" value=\"0\"> Disabled
							</td>
					</tr>
					<tr>
						<td>
							<Label for=\"singleDbForm\">Database mode</label>
						</td>
						<td>
							<input type=\"radio\" id=\"singleDbForm\" name=\"singleDbForm\" value=\"1\" ".($singleDbForm?"checked":"")." > Single
							</td>
							<td>
							<input type=\"radio\" id=\"singleDbForm\" name=\"singleDbForm\" value=\"0\" ".($singleDbForm?"":"checked")." > Multi
							<small>
								(one new database created at each course creation)
							</small>
						</td>
					</tr>
					<tr>
						<td>
							<input type=\"submit\" name=\"cmdShowLicence\" value=\"< Back\">
						</td>
						<td >
							&nbsp;
						</td>
						<td align=\"right\">
							<input type=\"submit\" name=\"".($databaseParam_ok?"configCampus":"doCheckDatabaseAccountSetting")."\" value=\"Next >\">
						</td>
					</tr>
				</table>";
}	 // setDbAccountProperties
elseif($display == DISP_DB_NAMES_SETTING )
{

###### STEP 3 MYSQL DATABASE SETTINGS ##############################################

	echo "
				<h2>
					MySQL Names
				</h2>
				".($singleDbForm?"":$langDBSettingNamesIntro)."
			</td>
		</tr>
		<tr>
			<td>
				".$msg_no_connection."
				<h4>".$langDBNamesRules."</h4>
				<table width=\"100%\">
					<tr>
						<td>
							<Label for=\"dbNameForm\">"
							.($singleDbForm?$langDbName:$langMainDB)."</label>
						</td>
						<td>
							<input type=\"text\"  size=\"25\" id=\"dbNameForm\" name=\"dbNameForm\" value=\"$dbNameForm\">
						</td>
						<td>
							&nbsp;
						</td>
					</tr>";
	if (!$singleDbForm)
	{
		echo "
					<tr>
						<td>
							<Label for=\"dbStatsForm\">".$langStatDB."</label>
						</td>
						<td>
							<input type=\"text\"  size=\"25\" id=\"dbStatsForm\" name=\"dbStatsForm\" value=\"$dbStatsForm\">
						</td>
						<td>
							&nbsp;
						</td>
					</tr>
					<tr>
						<td>
							<Label for=\"dbPMAForm\">".$langPMADB."</label>
						</td>
						<td>
							<input type=\"text\"  size=\"25\" id=\"dbPMAForm\" name=\"dbPMAForm\" value=\"$dbPMAForm\">
						</td>
						<td>
							&nbsp;
						</td>
					</tr>
";
	}
	echo "
					<tr>
						<td>
							<Label for=\"dbPrefixForm\">".($singleDbForm?"Prefix Name for Course Tables":$langDbPrefixForm)."</label>
						</td>
						<td>
							<input type=\"text\"  size=\"25\" id=\"dbPrefixForm\" name=\"dbPrefixForm\" value=\"$dbPrefixForm\">
						</td>
						<td>
							".$langDbPrefixCom."
						</td>
					</tr>
				</table>
				<table width=\"100%\">
					<tr>
						<td>
							<input type=\"submit\" name=\"setDbAccountProperties\" value=\"< Back\">
						</td>
						<td>
							&nbsp;
						</td>
						<td align=\"right\">
							<input type=\"submit\" name=\"".($databaseParam_ok?"ADMIN_SETTING":"doCheckDatabaseName")."\" value=\"Next >\">
						</td>
					</tr>
				</table>";
/*
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
}	 // setDbAccountProperties



###### STEP CONFIG SETTINGS ##############################################
elseif($display==DISP_ADMIN_SETTING)

{
	echo "
				<h2>
					".$langAdminSetting."
				</h2>
				The following values will be written in '<em>".$configFilePath."</em>'
			</td>
		</tr>
		<tr>
			<td>
				<h4>Administrator</h4>
				<table width=\"100%\">
					<tr>
						<tr>
							<td>
								<b><Label for=\"loginForm\">".$langAdminLogin."</label></b>
							</td>
							<td>
								<input type=\"text\" size=\"40\" id=\"loginForm\" name=\"loginForm\" value=\"$loginForm\">
							</td>
							<td></td>
						</tr>
						<tr>
							<td>
								<b><Label for=\"passForm\">".$langAdminPass."</label></b>
							</td>
							<td>
								<input type=\"text\" size=\"40\" id=\"passForm\" name=\"passForm\" value=\"$passForm\">
							</td>
							<td>
								e.g. ".generePass(8)."
							</td>
						</tr>
						<td>
							<Label for=\"adminEmailForm\">".$langAdminEmail."</label>
						</td>
						<td>
							<input type=\"text\" size=\"40\" id=\"adminEmailForm\" name=\"adminEmailForm\" value=\"$adminEmailForm\">
						</td>
							<td></td>
						</tr>
						<tr>
							<td>
								<Label for=\"adminNameForm\">".$langAdminName."</label>
							</td>
							<td>
								<input type=\"text\" size=\"40\" id=\"adminNameForm\" name=\"adminNameForm\" value=\"$adminNameForm\">
							</td>
							<td></td>
						</tr>
						<tr>
							<td>
								<Label for=\"adminSurnameForm\">".$langAdminSurname."</label>
							</td>
							<td>
								<input type=\"text\" size=\"40\" id=\"adminSurnameForm\" name=\"adminSurnameForm\" value=\"$adminSurnameForm\">
							</td>
							<td></td>
						</tr>
				</table>
				<table width=\"100%\">
						<tr>
							<td>
								<input type=\"submit\" name=\"setDbAccountProperties\" value=\"< Back\">
							</td>
							<td align=\"right\">
								<input type=\"submit\" name=\"CONFIG_SETTING\" value='Next >'>
							</td>
						</tr>
					</table>";
}
###### STEP CONFIG SETTINGS ##############################################
elseif($display==DISP_CONFIG_SETTING)

{
	echo "
				<h2>
					".$langCfgSetting."
				</h2>
				The following values will be written in '<em>".$configFilePath."</em>'
			</td>
		</tr>
		<tr>
			<td>
				<h4>Campus</h4>
				<table >
					<tr>
							<td>
								<Label for=\"campusForm\">Name</label>
							</td>
							<td colspan=2>
								<input type=\"text\" size=\"40\" id=\"campusForm\" name=\"campusForm\" value=\"$campusForm\">
							</td>
						</tr>
					<tr>
						<td>
							<Label for=\"urlForm\">Complete URL</label>
						</td>
						<td colspan=2>
							<input type=\"text\" size=\"40\" id=\"urlForm\" name=\"urlForm\" value=\"$urlForm\">
						</td>
					</tr>
					<tr>
						<td>
							<Label for=\"languageForm\">Main language</label>
						</td>
						<td colspan=2>
							<select id=\"languageForm\" name=\"languageForm\">	";
	$dirname = "../lang/";
	if($dirname[strlen($dirname)-1]!='/')
		$dirname.='/';
	$handle=opendir($dirname);
	while ($entries = readdir($handle))
	{
		if ($entries=='.'||$entries=='..'||$entries=='CVS')
			continue;
		if (is_dir($dirname.$entries))
		{
			echo "
							<option value=\"$entries\"";
			if ($entries == $languageForm)
				echo " selected ";
			echo ">
						$entries
									</option>";
		}
	}
	closedir($handle);
echo "
								</select>
							</font>
						</td>
					</tr>
<!--					<tr>
							<td>
								<Label for=\"institutionForm\">".$langInstituteShortName."</label>
							</td>
							<td colspan=2>
								<input type=text size=40 id=\"institutionForm\" name=\"institutionForm\" value=\"$institutionForm\">
							</td>
						</tr>
						<tr>
							<td>
								<Label for=\"institutionUrlForm\">".$langInstituteName."</label>
							</td>
							<td colspan=2>
								<input type=\"text\" size=\"40\" id=\"institutionUrlForm\" name=\"institutionUrlForm\" value=\"$institutionUrlForm\">
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
               &nbsp;&nbsp;&nbsp;&nbsp;<label for=\"allowSelfReg\">Simple user</label>
				</td>
				<td>
					<input type=\"radio\" id=\"allowSelfReg\" name=\"allowSelfReg\" value=\"1\" checked> Enabled
				</td>
				<td>
					<input type=\"radio\" id=\"allowSelfReg\" name=\"allowSelfReg\" value=\"0\"> Disabled
				</td>
			</tr>

					<tr>
						<td>
		               &nbsp;&nbsp;&nbsp;&nbsp;<label for=\"allowSelfRegProf\">Course creator</label>
						</td>
						<td>
							<input type=\"radio\" id=\"allowSelfRegProf\" name=\"allowSelfRegProf\" value=\"1\" checked> Enabled
						</td>
						<td>
							<input type=\"radio\" id=\"allowSelfRegProf\" name=\"allowSelfRegProf\" value=\"0\">  Disabled
						</td>
					</tr>
		
					<tr>
						<td colspan=3>&nbsp;
						</td>
					
					</tr>
		
					<tr>
						<td>

							<Label for=\"encryptPassForm\">User password</label>
						</td>
						<td>
							<input type=\"radio\" name=\"encryptPassForm\" id=\"encryptPassForm\" value=0 checked> Clear text
						</td>
						<td>
							<input type=\"radio\" name=\"encryptPassForm\" id=\"encryptPassForm\" value=1> Crypted
						</td>
					</tr>

					<tr>
						<td>
						</td>
						<td>
						</td>
						</tr>
				</table>
				<table width=\"100%\">
						<tr>
							<td>
								<input type=\"submit\" name=\"ADMIN_SETTING\" value=\"< Back\">
							</td>
							<td align=\"right\">
								<input type=\"submit\" name=\"install6\" value='Next >'>
							</td>
						</tr>
					</table>";
}
###### STEP LAST CHECK BEFORE INSTALL ##############################################
elseif($display==DISP_LAST_CHECK_BEFORE_INSTALL)
{
	$pathForm = str_replace("\\\\", "/", $pathForm);
	//echo "pathForm $pathForm";
	echo "
				<h2>
					".$langStep6." : ".$langLastCheck."
				</h2>
		Here are the values you entered <br>
		<Font color=\"red\">
			Print this page to remember your admin password and other settings
		</font>
		<blockquote>

		<FIELDSET>
		<LEGEND>Database</LEGEND>
		<EM>Account</EM><br>
		Database Host : $dbHostForm<br>
		Database Username : $dbUsernameForm<br>
		Database Password : ".(empty($dbPassForm)?"--empty--":$dbPassForm)."<br>
		<em>Names</em>
		";

	if ($dbPrefixForm=="")
		echo "";
	else
		echo "DB Prefix : $dbPrefixForm<br>";
	echo "
		Main DB Name : $dbNameForm<br>
		Statistics and Tracking DB Name : $dbStatsForm<br>
		PhpMyAdmin Extention DB Name : $dbPMAForm<br>
		Enable Single DB : ".($singleDbForm?$langYes:$langNo)."<br>
		</FIELDSET>
		<FIELDSET>
		<LEGEND>Admin</LEGEND>
		Administrator email : $adminEmailForm<br>
		Administrator Name : $adminNameForm<br>

		Administrator Surname : $adminSurnameForm<br>
		<table border=0 class=\"notethis\">
			<tr>
				<td>
					<font size=\"2\" color=\"red\" face=\"arial, helvetica\">
					Administrator Login : $loginForm<br>
					Administrator Password : ".(empty($passForm)?"--empty-- <B>&lt;-- Error !</B>":$dbPassForm)."<br>
					</font>
				</td>
			<tr>
		</table>
		</FIELDSET>
		<FIELDSET>
		<LEGEND>Campus</LEGEND>
		Language : $languageForm<br>
		URL of claroline : $urlForm<br>
		Your campus Name : $campusForm<br>
		Your organisation : $institutionForm<br>
		URL of this organisation : $institutionUrlForm<br>
		</FIELDSET>
		<FIELDSET>
		<LEGEND>Config</LEGEND>
		Enable Tracking : ".($enableTrackingForm?$langYes:$langNo)."<br>
		Self-registration allowed : ".($allowSelfReg?$langYes:$langNo)."<br>
		Encrypt user passwords in database : ";

		if ($encryptPassForm)
			echo "Yes";
		else
			echo "No";
?>
		</FIELDSET>
		</blockquote>
		<table width="100%">
			<tr>
				<td>
					<input type="submit" name="CONFIG_SETTING" value="< Back">
				</td>
				<td align="right">
					<input type="submit" name="doInstall" value="Install Claroline >">
				</td>
			</tr>
		</table>
<?php

}
###### INSTALL INCOMPLETE!##############################################
elseif($display==DISP_RUN_INSTALL_NOT_COMPLETE)
{
	echo "
				<h2>
					Install Problem
				</h2>";
	if ($adminDataMissing)
	{
		echo "<strong>Admin Data missing</strong><br>
			<input type=\"submit\" name=\"ADMIN_SETTING\" value=\"Admin Data\">
		";	
	}
	if ($noMysqlConnection)
	{

		echo "<HR>[".$no."] - ".$msg."<HR>
	    The Server Mysql  doesn't work or login pass is false.<br>
	    please  check these values<br>
	    host : ".$dbHostForm."<br>
		user : ".$dbUsernameForm."<br>
		password  : ".$dbPassForm."<br>
		<input type=\"submit\" name=\"setDbAccountProperties\" value=\"set DB Account\">";
	}

	
	if (
		$mainDbNameExist
	||	$statsDbNameExist
	||	$pmaDbNameExist
	)
	{
		echo "<HR>";
		if ($mainDbNameExist)
			echo '<P><B>Main</B> db (<em>'.$mainDbName.'</em>) already exist <BR>
			<input type="checkbox" name="confirmUseExistingMainDb"  id="confirmUseExistingMainDb" value="true" '.($confirmUseExistingMainDb?'checked':'').'>
			<label for="confirmUseExistingMainDb" >I know, I want use it.</label><BR>
			<font color="red">Warning : this script write in tables use by claroline.</font>
			</P>';
		if ($statsDbNameExist)
			echo '<P><B>Stat</B> db ('.$statsDbName.') already exist<BR>
			<input type="checkbox" name="confirmUseExistingStatsDb"  id="confirmUseExistingStatsDb" value="true" '.($confirmUseExistingStatsDb?'checked':'').'>
			<label for="confirmUseExistingStatsDb" >I know, I want use it.</label><BR>
			<font color="red">Warning : this script write in tables use by claroline.</font>
			</P>';
		if ($pmaDbNameExist)
			echo '<P><B>PhpMyAdmin</B> db (<em>'.$pmaDbName.'</em>) already exist<BR>
			<input type="checkbox" name="confirmUseExistingPMADb"  id="confirmUseExistingPMADb" value="true" '.($confirmUseExistingPMADb?'checked':'').'>
			<label for="confirmUseExistingPMADb" >I know, I want use it.</label><BR>
			<font color="red">Warning : this script write in tables use by claroline.</font>
			</P>';
		echo "<P>
			OR <input type=\"submit\" name=\"doCheckDatabaseAccountSetting\" value=\"set DB Names\"></P><HR>";
	}
	if($mainDbNameCreationError)
		echo "<BR>".$mainDbNameCreationError;
	if($fileAccessCreationError)
		echo "<BR>".$statsDbNameCreationError;
	if($pmaDbNameCreationError)
		echo "<BR>".$pmaDbNameCreationError;
	if($fileAccessCreationError)
		echo "<BR>Error on creation : file <EM>".$htAccessName."</EM> in <U>".realpath($htAccessPath)."</U><br>";
	if($filePasswordCreationError)
		echo "<BR>Error on creation : file <EM>".$htPasswordName."</EM> in <U>".realpath($htPasswordPath)."</U><br>";
	if ($fileConfigCreationError)
	echo "
	<b>
		<font color=\"red\">
			Probably, your script doesn't have write access to the config directory
		</font>
		<br>
		<SMALL>
			<EM>(".realpath("../inc/conf/").")</EM>
		</SMALL>
	</b>
	<br><br>
	You probably do not have write access on claroline root directory,
	i.e. you should <EM>CHMOD 777</EM> or <EM>755</EM> or <EM>775</EM><br><br>

Your problems can be related on two possible causes :<br>
<UL>
	<LI>Permission problems. <br>Try initially with <EM>chmod 777 -R</EM> and increase restrictions gradually.</LI>
    <LI>PHP is running in
	<a href=\"http://www.php.net/manual/en/features.safe-mode.php\" target=\"_phpman\">
	SAFE MODE</a>. If possible, try to switch it off.</LI>
</UL>
	<a href=\"http://www.claroline.net/forum/viewtopic.php?t=753\">Read about this problem in Support Forum</a>";

	if ($coursesRepositorySysMissing)
	{
		echo "<BR> <em>\$coursesRepositorySys = ".$coursesRepositorySys."</em> : <br>dir is missing";
		echo "<BR>".$coursesRepositorySys." is missing";
	}

	if ($coursesRepositorySysWriteProtected)
	{
		echo "<BR><b><em>".$coursesRepositorySys."</em> is Write Protected.</b>
		Claroline need to have write right to create course.<br>
		change rigth on this directory and retry.";
	}

	if ($garbageRepositorySysMissing)
	{
		echo "<BR> <em>\$garbageRepositorySys = ".$garbageRepositorySys."</em> : <br>dir is missing";
	}
	
	if ($garbageRepositorySysWriteProtected)
	{
		echo "<BR><b><em>".$garbageRepositorySys."</em> is Write Protected.</b> Claroline need to have write right to trash courses.<br>
		change rigth on this directory and retry.";
	}

	echo "
				<p align=\"right\">
					<input type=\"submit\" name=\"alreadyVisited\" value=\"Restart from beginning\">
					<input type=\"submit\" name=\"CONFIG_SETTING\" value=\"Previous\">
					<input type=\"submit\" name=\"doInstall\" value=\"Retry\">
				</p>";

}
###### STEP RUN_INSTALL_COMPLETE !##############################################
elseif($display==DISP_RUN_INSTALL_COMPLETE)
{
?>
			<h2>
				<?php echo $langCfgSetting ?>
			</h2>
			<br>
			<br>
			When you enter your campus for the first time, the best way to understand it
			is to register with the option 'Create course websites' and then follow the way.
			<br>
			<br><b>
			Security advice: To protect your site, make '<?php echo $configFileName ?>'
			and 'claroline/install/index.php' read-only (CHMOD 444).</b>
</form>
<form action="../../">
		<input type="submit" value="Go to your newly created campus">
</form>
<?php
}	// STEP RUN_INSTALL_COMPLETE

else
{
	echo "\$display notSet. error in script.";
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

?>
