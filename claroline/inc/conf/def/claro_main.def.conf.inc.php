<?php # $Id$
# CLAROLINE version 1.5.alpha

//----------------------------------------------------------------------
// CLAROLINE
//----------------------------------------------------------------------
// Copyright (c) 2001-2003 Universite catholique de Louvain (UCL)
//----------------------------------------------------------------------
// This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
// as published by the FREE SOFTWARE FOUNDATION. The GPL is available
// through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
//----------------------------------------------------------------------
// Authors: see 'credits' file
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

$rootWeb 		= 	"http://www.domain.tld/path/until/claroline/";
$urlAppend		=	"/path/until/claroline"; // <-- this is the only line without Slash on end.
$rootSys		=	"/var/www/www.domain.tld/html/path/until/claroline/" ;


// MYSQL
$dbHost 		= "localhost";
$dbLogin 		= "007";
$dbPass			= "bondMyNameIsBond";
$dbNamePrefix	= "c_"; // prefix all created base (for courses) with this string

// DATABASES
/*
	if you don't use installer, create theses bases with sql
*/
$mainDbName			= "claroline150";
$statsDbName		= "claroline150";
$is_trackingEnabled	= TRUE;

// DO NOT MODIFY THIS AFTER FIRST USE (switch comment to use claroline in single db mode
$singleDbEnabled	= FALSE;  $dbGlu				= "`.`";
//$singleDbEnabled	= TRUE;  $dbGlu				= "_";

$courseTablePrefix	= ""; 		// IF NOT EMPTY, CAN BE REPLACED BY ANOTHER PREFIX, ELSE LEAVE EMPTY

$mysqlRepositorySys = "/var/lib/mysql/"; // read only access is enought

$garbageRepositorySys	= $rootSys."/garbage/"; // change this to a place out of web if you can

$CourseProgram="http://www.ucl.ac.be/etudes/cours";

// AVAILABLE LANGUAGES
// arabic, brazilian, croatian, dutch,  english, finnish,
// french, galician, german, greek, italian, japanese, polish,
// simpl_chinese, polish, spanish, swedish, thai, turkce
$platformLanguage 	= 	"english";



// Strings
$siteName				=	"site Name";

$emailAdministrator		=	"ImTheBossOfThisCampus@domain.tld";

// SYSADMIN
$administrator["name"]	=	"John Doe";
$administrator["phone"]	=	"(000) 001 02 03";
$administrator["email"]	=	$emailAdministrator;

// EDUCATIONAL ADMIN
$educationManager["name"]	=	"Albert Einstein";
$educationManager["phone"]	=	"";
$educationManager["email"]	=	"";
$institution["name"]		=	"inc";
$institution["url"]			=	"http://www.google.com/";


//backgrounds
$colorLight	=	"#99CCFF"; //
$colorMedium= 	"#6699FF"; // these 3 colors are used in header
$colorDark	= 	"#000066"; //

//for new login module
//uncomment these to activate ldap
//$extAuthSource['ldap']['login'] = "./claroline/auth/ldap/login.php";
//$extAuthSource['ldap']['newUser'] = "./claroline/auth/ldap/newUser.php";

















// Some value which would be moved to appropriate conf.file of targeted tools

$checkEmailByHashSent 			= 	FALSE;
$ShowEmailnotcheckedToStudent 	= 	TRUE;
$userMailCanBeEmpty 			= 	TRUE;
$userPasswordCrypted			=	FALSE;
$allowSelfReg					= TRUE;
$allowSelfRegProf				= FALSE;

// course backup
$dateBackup			=	date("Y-m-d-H-i-s");
$shortDateBackup	=	date("YzBs");

$verboseBackup		=	FALSE;

$archiveExt			=	"txt";
$archiveDirName		=	"archive";





























//Probably Nothing to change after this



// these values are keet  to  have no problem with script not upgraded to  the  new init system
$urlServer 			= 	$rootWeb ;
$serverAddress		= 	$rootWeb ;
$webDir				= 	$rootSys;
$language 			=	$platformLanguage ;

// MYSQL
$mysqlServer		=	$dbHost ;
$mysqlUser			=	$dbLogin;
$mysqlPassword		=	$dbPass;
$mysqlPrefix		=	$dbNamePrefix;
$mysqlMainDb		=	$mainDbName;

// something like /var/lib/mysql/ or  C:\Program Files\EasyPHP/mysql/data/
// PATH & URL
$phpSysInfoURL		=	"";

$color1				=	"#F5F5F5"; // light grey
$color2				=	"#E6E6E6"; // less light grey for bicolored tables

//general infos
$administratorSurname=	"";
$administratorName	=	$administrator["name"];
$telephone			=	$administrator["phone"];

$educationManager	=	$educationManager["name"];

$Institution		=	$institution["name"];
$InstitutionUrl		=	$institution["url"];




//Probably Nothing to change after this



$clarolineRepositoryAppend  = "claroline/";
$coursesRepositoryAppend	= "";
$rootAdminAppend			= "admin/";
$phpMyAdminAppend			= "mysql/";
$phpSysInfoAppend			= "sysinfo/";
$userImageRepositoryAppend	= "img/users/";
$clarolineRepositorySys		= $rootSys.$clarolineRepositoryAppend;
$clarolineRepositoryWeb 	= $rootWeb.$clarolineRepositoryAppend;
$userImageRepositorySys		= $rootSys.$userImageRepositoryAppend;
$userImageRepositoryWeb		= $rootWeb.$userImageRepositoryAppend;
$coursesRepositorySys		= $rootSys.$coursesRepositoryAppend;
$coursesRepositoryWeb		= $rootWeb.$coursesRepositoryAppend;
$rootAdminSys				= $clarolineRepositorySys.$rootAdminAppend;
$rootAdminWeb				= $clarolineRepositoryWeb.$rootAdminAppend;
$phpMyAdminWeb				= $rootAdminWeb.$phpMyAdminAppend;
$phpMyAdminSys				= $rootAdminSys.$phpMyAdminAppend;
$phpSysInfoWeb				= $rootAdminWeb.$phpSysInfoAppend;
$phpSysInfoSys				= $rootAdminSys.$phpSysInfoAppend;

$clarolineVersion	=	"1.5.alpha";
$versionDb 			= 	"1.5.alpha";



// Put below the complete url of your TEX renderer. This url doesn't have to be 
// specially on the same server than Claroline.
// 
// Claroline uses the MIMETEX renderer created by John Forkosh and available 
// under the GNU licence at http://www.forkosh.com. 
// 
// MIMETEX parses TEX/LaTEX mathematical expressions and emits gif images from 
// them. You'll find precompilated versions of MIMETEX for various platform in 
// the 'claroline/inc/lib/' directory. Move the executable file that 
// corresponding to your platform into its 'cgi-bin/' directory, where cgi 
// programs are expected (this directory are typically of the form 
// 'somewhere/www/cgi-bin/'), and change the execution permissions if necessary.
// 
// If you're not able or allowed to set MIMETEX on a server, leave the setting 
// below to 'false'. Claroline will then try to use another method for rendering 
// TEX/LaTEX mathematical expression, relying on a plug-in client side this 
// time. For this, user has to install the TECHEXPLORER plug-in, freely 
// available for both Windows, Macintosh and Linux at 
// http://www.integretechpub.com/.


$claro_texRendererUrl = false;

?>
