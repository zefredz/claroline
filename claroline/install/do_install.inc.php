<?php # $Id$

//----------------------------------------------------------------------
// CLAROLINE 1.5.*
//----------------------------------------------------------------------
// Copyright (c) 2001-2004 Universite catholique de Louvain (UCL)
//----------------------------------------------------------------------
// This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
// as published by the FREE SOFTWARE FOUNDATION. The GPL is available
// through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
//----------------------------------------------------------------------
// Authors: see 'credits' file
//----------------------------------------------------------------------

// This part of script is include on run_intall step of  setup tool.

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

// PATCH TO ACCEPT Prefixed DBs
$mainDbName 	= $dbNameForm;
$statsDbName 	= $dbStatsForm;
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
			$mainDbSuccesfullCreated = true;
		}
		else
		{
			$mainDbNameExist = true;
			$display=DISP_DB_NAMES_SETTING;
		}
	}
	else
	{
		$mainDbNameCreationError = '
				<P class="setup_error">
					<font color="red">Warning !</font> 
					<small>['.mysql_errno().'] - '.mysql_error().'</small>
					<br>
					Error on creation '.$langMainDB.' : <I>'.$dbHostForm.'</I>
					<BR>
					<font color="blue">
						Fix this problem before going further
					</font>
				</P>';
		$display=DISP_DB_NAMES_SETTING;
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
					$statsDbSuccesfullCreated = true;
				}
				else
				{
					$statsDbNameExist = true;
					$display=DISP_DB_NAMES_SETTING;
				}
			}
			else
			{
				$statsDbNameCreationError = '
				<P class="setup_error">
					<font color="red">Warning !</font> 
					<small>['.mysql_errno().'] - '.mysql_error().'</small>
					<br>
					Error on creation '.$langStatDB.' : <I>'.$dbStatsForm.'</I>
					<BR>
					<font color="blue">
						Fix this problem before going further
					</font>
				</P>';
				$display=DISP_DB_NAMES_SETTING;
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

if ($runfillMainDb && $runfillStatsDb)
{
	mysql_select_db ($mainDbName);
	include ("./createMainBase.inc.php");
	include ("./fillMainBase.inc.php");

	mysql_select_db ($statsDbName);
	include ("./createStatBase.inc.php");
	include ("./fillStatBase.inc.php");
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
// Copyright (c) 2001-2004 Universite catholique de Louvain (UCL)
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
$claro_stylesheet   =   "default.css";
$clarolineVersion	=	"'.$clarolineVersion.'";
$versionDb 			= 	"'.$versionDb.'";

// Put below the complete url of your TEX renderer. This url doesn\'t have to be 
// specially on the same server than Claroline.
// 
// Claroline uses the MIMETEX renderer created by John Forkosh and available 
// under the GNU licences at http://www.forkosh.com. 
// 
// MIMETEX parses TEX/LaTEX mathematical expressions and emits gif images from 
// them. You\'ll find precompilated versions of MIMETEX for various platform in 
// the \'claroline/inc/lib/\' directory. Move the executable file that 
// corresponding to your platform into its \'cgi-bin/\' directory, where cgi 
// programs are expected (this directory are typically of the form 
// \'somewhere/www/cgi-bin/\'), and change the execution permissions if necessary.
// 
// If you\'re not able or allowed to set MIMETEX on a server, leave the setting 
// below to \'false\'. Claroline will then try to use another method for rendering 
// TEX/LaTEX mathematical expression, relying on a plug-in client side this 
// time. For this, user has to install the TECHEXPLORER plug-in, freely 
// available for both Windows, Macintosh and Linux at 
// http://www.integretechpub.com/.

$claro_texRendererUrl = false;

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
$newIncludePath."conf/work.conf.inc.php",
$newIncludePath."../../textzone_top.inc.html",
$newIncludePath."../../textzone_right.inc.html"
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

	if (PHP_OS!="WIN32" && PHP_OS!="WINNT")
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
?>