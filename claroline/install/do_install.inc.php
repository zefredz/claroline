<?php # $Id$

//----------------------------------------------------------------------
// CLAROLINE 1.6.*
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
$resBdbHome = @claro_sql_query("SHOW VARIABLES LIKE 'datadir'");
$mysqlRepositorySys = mysql_fetch_array($resBdbHome,MYSQL_ASSOC);
$mysqlRepositorySys = $mysqlRepositorySys ["Value"];

/////////////////////////////////////////
// MAIN DB                             //
// DB with central info  of  Claroline //

mysql_query("CREATE DATABASE `".$mainDbName."`");
if (mysql_errno() >0)
{
	if (mysql_errno() == 1007)
	{
		if ($confirmUseExistingMainDb)
		{
			$runfillMainDb = TRUE;
			$mainDbSuccesfullCreated = TRUE;
		}
		else
		{
			$mainDbNameExist = TRUE;
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
		claro_sql_query("CREATE DATABASE `$statsDbName`");
		if (mysql_errno() >0)
		{
			if (mysql_errno() == 1007)
			{
				if ($confirmUseExistingStatsDb)
				{
					$runfillStatsDb = TRUE;
					$statsDbSuccesfullCreated = TRUE;
				}
				else
				{
					$statsDbNameExist = TRUE;
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
			$runfillStatsDb = TRUE;
		}
	}
	else
	{
		// single DB mode so $statsDbName MUST BE the SAME than $mainDbName
		// because it's actually singleDB and not singleCourseDB
		$statsDbName = $mainDbName;
		$runfillStatsDb = TRUE;
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
$coursesRepositorySys = $rootSys.$courseRepositoryForm;
@mkdir($coursesRepositorySys,0777);
$clarolineRepositoryAppend  = "claroline/";
$clarolineRepositorySys		= $rootSys.$clarolineRepositoryAppend;
$garbageRepositorySys	= str_replace("\\","/",realpath($clarolineRepositorySys)."/claroline_garbage");
@mkdir($garbageRepositorySys,0777);

########################## WRITE claro_main.conf.php ##################################
// extract the path to append to the url
// if Claroline is not installed on the web root directory

//$urlAppendPath = ereg_replace ("claroline/install/index.php", "", $_SERVER['PHP_SELF']);

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


$platform_id        = "'.md5(realpath(__FILE__)).'";

$rootWeb 			= "'.$urlForm.'";
$urlAppend			= "'.$urlAppendPath.'";
$rootSys			= "'.$rootSys.'" ;

/* CLAROLANG : Translation: use a single language file, Production: each script use its own language file */
define("CLAROLANG","TRANSLATION");

// MYSQL
$dbHost 			= "'.$dbHostForm.'";
$dbLogin 			= "'.$dbUsernameForm.'";
$dbPass				= "'.$dbPassForm.'";

$mainDbName			= "'.$mainDbName.'";
$statsDbName		= "'.$statsDbName.'";
$dbNamePrefix		= "'.$dbPrefixForm.'"; // prefix all created base (for courses) with this string

$is_trackingEnabled	= '.trueFalse($enableTrackingForm).';
$singleDbEnabled	= '.trueFalse($singleDbForm).'; // DO NOT MODIFY THIS
$courseTablePrefix	= "'.($singleDbForm && empty($dbPrefixForm)?'crs_':'').'"; // IF NOT EMPTY, CAN BE REPLACED BY ANOTHER PREFIX, ELSE LEAVE EMPTY
$dbGlu				= "'.($singleDbForm?'_':'`.`').'"; // DO NOT MODIFY THIS
$mysqlRepositorySys = "'.str_replace("\\","/",realpath($mysqlRepositorySys)."/").'";

$clarolineRepositoryAppend  = "claroline/";
$coursesRepositoryAppend	= "'.$courseRepositoryForm.'";
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
$imgRepositorySys		    = $rootSys.$clarolineRepositoryAppend.\'img/\';
$imgRepositoryWeb 	        = $rootWeb.$clarolineRepositoryAppend.\'img/\';

// Path to the PEAR library. PEAR stands for "PHP Extension and Application 
// Repository". It is a framework and distribution system for reusable PHP 
// components. More on http://pear.php.net.
// Claroline is provided with the basic PEAR components needed by the 
// application in the "claroline/inc/lib/pear" directory. But, server 
// administator can redirect to their own PEAR library directory by setting 
// its path to the PEAR_LIB_PATH constant.

define(\'PEAR_LIB_PATH\', $includePath.\'/lib/pear\');


// Strings
$siteName				=	"'.cleanwritevalue($campusForm).'";
$administrator_name	=	"'.cleanwritevalue($contactNameForm).'";
$administrator_phone	=	"'.cleanwritevalue($contactPhoneForm).'";
$administrator_email	=	"'.cleanwritevalue((empty($contactEmailForm)?$adminEmailForm:$contactEmailForm)).'";

$institution_name		=	"'.cleanwritevalue($institutionForm).'";
$institution_url			=	"'.$institutionUrlForm.'";

// param for new and future features
$checkEmailByHashSent 			= 	'.trueFalse($checkEmailByHashSent).';
$ShowEmailnotcheckedToStudent 	= 	'.trueFalse($ShowEmailnotcheckedToStudent).';
$userMailCanBeEmpty 			= 	'.trueFalse($userMailCanBeEmpty).';
$userPasswordCrypted			=	'.trueFalse($encryptPassForm).';
$allowSelfReg					= '.trueFalse($allowSelfReg).';
$allowSelfRegProf				= '.trueFalse($allowSelfRegProf).';

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

$claro_texRendererUrl = \'\';

?>');

######### DEALING WITH FILES #########################################

	fwrite($fd, $stringConfig);


	
/**
* Config file to undist
*/



$arr_file_to_undist = 
array (
$newIncludePath.'../../textzone_top.inc.html',
$newIncludePath.'../../textzone_right.inc.html',
$newIncludePath.'conf/auth.conf.php'
);

foreach ($arr_file_to_undist As $undist_this)
	claro_undist_file($undist_this);

/***
 * Generate conf from definition files.
 */
$includePath = $newIncludePath;
$def_file_list = get_def_file_list();
if(is_array($def_file_list))
foreach ( $def_file_list as $def_file_bloc)
{
    if (is_array($def_file_bloc['conf']))
    foreach ( $def_file_bloc['conf'] as $config_code => $def_file)
    {

        // tmp: skip the main conf
        if ( $config_code == 'CLMAIN' ) continue;

        $okToSave = TRUE;
        
        unset($conf_def, $conf_def_property_list);
        
        $def_file  = get_def_file($config_code);
        
        if ( file_exists($def_file) )
            require($def_file);
            
        if ( is_array($conf_def_property_list) )
        {
            foreach($conf_def_property_list as $propName => $propDef )
            {
                $propValue = $propDef['default']; // USe default as effective value
                if ( !validate_property($propValue, $propDef) )
                {
                    $okToSave = FALSE;
                }
            }
        }
        else
        {
            $okToSave = FALSE;
        }
    
        if ($okToSave) 
        {
            reset($conf_def_property_list);
            foreach($conf_def_property_list as $propName => $propDef )
            {
                $propValue     = $propDef['default']; // USe default as effective value
                save_property_in_db($propName,$propValue, $config_code);
            }
            
            $conf_file = get_conf_file($config_code);
            
            if ( !file_exists($conf_file) ) touch($conf_file);
            
            $storedPropertyList = read_properties_in_db($config_code);
    
            if ( is_array($storedPropertyList) && count($storedPropertyList)>0 )
            {
                
                if ( write_conf_file($conf_def,$conf_def_property_list,$storedPropertyList,$conf_file,realpath(__FILE__)) )
                {
                    // calculate hash of the config file 
                    $conf_hash = md5_file($conf_file);
                    save_config_hash_in_db($conf_file,$config_code,$conf_hash);
                }               
            }                            
        }
    }
}	
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
	@rename ($htPasswordPath.$htPasswordName,$htPasswordPath.$htPasswordName."_old");

	$filePasswd=@fopen($htPasswordPath.$htPasswordName, "w");
	if (!$filePasswd)
	{
		$filePasswordCreationError = TRUE;
		$display=DISP_RUN_INSTALL_NOT_COMPLETE;
	}
	else
	{
		$stringPasswd=cleanwritevalue($loginForm.':'.$passFormToStore);
		@fwrite($filePasswd, $stringPasswd);
	}

	// htaccess files

	$htAccessAdminPath = "../admin/";
	$htAccessName = ".htaccess";
	@rename ($htAccessAdminPath.$htAccessName, 			$htAccessAdminPath.$htAccessName."_old");
	$fileAccess=@fopen($htAccessAdminPath.$htAccessName, "w");
	if (!$fileAccess)
	{
		$fileAccessInAdminSectionCreationError = TRUE;
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

	$htAccessLangPath = "../lang/";
	$fileAccess=@fopen($htAccessLangPath.$htAccessName, "w");
	if (!$fileAccess)
	{
		$fileAccessInLangRepositoryCreationError = TRUE;
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

	$htAccessSqlPath = "../sql/";
	$fileAccess=@fopen($htAccessSqlPath.$htAccessName, "w");
	if (!$fileAccess)
	{
		$fileAccessInSqlRepositoryCreationError = TRUE;
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
