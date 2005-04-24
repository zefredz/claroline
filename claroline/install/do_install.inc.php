<?php // $Id$
/**
 * CLAROLINE 
 *
 * This part of script is include on run_intall step of  setup tool.

 * in this  part. Script try to run Install
 * if  all is right $display still DISP_RUN_INSTALL_COMPLETE set on start
 * if  any problem happend, $display is switch to DISP_RUN_INSTALL_NOT_COMPLETE
 * and a  flag to mark what's happend is set.
 * in DISP_RUN_INSTALL_NOT_COMPLETE the screen show an explanation about problem and
 * prupose to back  to correct or to accept and continue.

 * First block is about database
 * Second block is  writing config
 * third block is building paths
 * Forth block check some right
 *
 * @version 1.6 $Revision$
 *
 * @copyright 2001-2005 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @see http://www.claroline.net/wiki/index.php/Install
 *
 * @author Claro Team <cvs@claroline.net>
 * @author Christophe Gesché <moosh@claroline.net>
 *
 * @package INSTALL
 *
 */

$display=DISP_RUN_INSTALL_COMPLETE; //  if  all is righ $display don't change

 // PATCH TO ACCEPT Prefixed DBs
$mainDbName     = $dbNameForm;
$statsDbName    = $dbStatsForm;
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

$userPasswordCrypted = $encryptPassForm;
$mainDbName     = $dbNameForm;
$statsDbName    = $dbStatsForm;
$mainTblPrefix  = $mainTblPrefixForm;
$statsTblPrefix = $statsTblPrefixForm;
$tbl_mdb_names = claro_sql_get_main_tbl();
$tbl_admin = $tbl_mdb_names['admin'];


if ($runfillMainDb && $runfillStatsDb)
{
    mysql_select_db ($mainDbName);
    include ('./createMainBase.inc.php');
    include ('./fillMainBase.inc.php');

    mysql_select_db ($statsDbName);
    include ('./createStatBase.inc.php');
    include ('./fillStatBase.inc.php');
}

// FILE SYSTEM OPERATION
//
// Build path

$rootSys                    =   str_replace("\\","/",realpath($pathForm)."/") ;
$coursesRepositoryAppend    = "";
$coursesRepositorySys = $rootSys.$courseRepositoryForm;
@mkdir($coursesRepositorySys,0777);
$clarolineRepositoryAppend  = "claroline/";
$clarolineRepositorySys     = $rootSys.$clarolineRepositoryAppend;
$garbageRepositorySys   = str_replace("\\","/",realpath($clarolineRepositorySys)."/claroline_garbage");
@mkdir($garbageRepositorySys,0777);

########################## WRITE claro_main.conf.php ##################################
// extract the path to append to the url
// if Claroline is not installed on the web root directory

//$urlAppendPath = ereg_replace ("claroline/install/index.php", "", $_SERVER['PHP_SELF']);

// here I want find  something to get garbage out of documentRoot
include_once('../inc/conf/def/CLMAIN.def.conf.inc.php');

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

// This file was generate by script /install/index.php
// on '.date("r").'
// REMOTE_ADDR :        '.$_REMOTE_ADDR.' = '.gethostbyaddr($REMOTE_ADDR).'
// REMOTE_HOST :        '.$REMOTE_HOST.'
// REMOTE_PORT :        '.$REMOTE_PORT.'
// REMOTE_USER :        '.$REMOTE_USER.'
// REMOTE_IDENT :       '.$REMOTE_IDENT.'
// HTTP_USER_AGENT :    '.$HTTP_USER_AGENT.'
// SERVER_NAME :        '.$SERVER_NAME.'
// HTTP_COOKIE :        '.$HTTP_COOKIE.'


$platform_id        = "'.md5(realpath(__FILE__)).'";

$rootWeb            = "'.$urlForm.'";
$urlAppend          = "'.$urlAppendPath.'";
$rootSys            = "'.$rootSys.'" ;

/* CLAROLANG : Translation: use a single language file, Production: each script use its own language file */
define("CLAROLANG","'.$conf_def_property_list['CLAROLANG']['default'].'");

// MYSQL
$dbHost             = "'.$dbHostForm.'";
$dbLogin            = "'.$dbUsernameForm.'";
$dbPass             = "'.$dbPassForm.'";

$mainDbName         = "'.$mainDbName.'";
$mainTblPrefix      = "'.$mainTblPrefixForm.'";
$statsDbName        = "'.$statsDbName.'";
$statsTblPrefix     = "'.$statsTblPrefixForm.'";

$dbNamePrefix       = "'.$dbPrefixForm.'"; // prefix all created base (for courses) with this string

$is_trackingEnabled = '.trueFalse($enableTrackingForm).';
$singleDbEnabled    = '.trueFalse($singleDbForm).'; // DO NOT MODIFY THIS
$courseTablePrefix  = "'.($singleDbForm && empty($dbPrefixForm)?'crs_':'').'"; // IF NOT EMPTY, CAN BE REPLACED BY ANOTHER PREFIX, ELSE LEAVE EMPTY
$dbGlu              = "'.($singleDbForm?'_':'`.`').'"; // DO NOT MODIFY THIS
$mysqlRepositorySys = "'.str_replace("\\","/",realpath($mysqlRepositorySys)."/").'";

$clarolineRepositoryAppend  = "claroline/";
$coursesRepositoryAppend    = "'.$courseRepositoryForm.'";
$rootAdminAppend            = "admin/";
$clarolineRepositorySys     = $rootSys.$clarolineRepositoryAppend;
$clarolineRepositoryWeb     = $rootWeb.$clarolineRepositoryAppend;
$coursesRepositorySys       = $rootSys.$coursesRepositoryAppend;
$coursesRepositoryWeb       = $rootWeb.$coursesRepositoryAppend;
$rootAdminSys               = $clarolineRepositorySys.$rootAdminAppend;
$rootAdminWeb               = $clarolineRepositoryWeb.$rootAdminAppend;
$garbageRepositorySys       = "'.$garbageRepositorySys.'";

// Strings
$siteName               =   "'.cleanwritevalue($campusForm).'";
$administrator_name =   "'.cleanwritevalue($contactNameForm).'";
$administrator_phone    =   "'.cleanwritevalue($contactPhoneForm).'";
$administrator_email    =   "'.cleanwritevalue((empty($contactEmailForm)?$adminEmailForm:$contactEmailForm)).'";

$institution_name       =   "'.cleanwritevalue($institutionForm).'";
$institution_url            =   "'.$institutionUrlForm.'";

// param for new and future features
$userPasswordCrypted            =   '.trueFalse($encryptPassForm).';
$allowSelfReg                   = '.trueFalse($allowSelfReg).';

$platformLanguage   =   "'.$languageForm.'";
$claro_stylesheet   =   "default.css";
$clarolineVersion   =   "'.$clarolineVersion.'";
$versionDb          =   "'.$versionDb.'";

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


define("CLAROLANG","'.$conf_def_property_list['CLAROLANG']['default'].'");

/* CLARO_DEBUG_MODE : More verbose when error occurs. */
define("CLARO_DEBUG_MODE", '.trueFalse($conf_def_property_list['CLARO_DEBUG_MODE']['default']).');


/* DEVEL_MODE : Add addtionnal tools in the SDK section of the platform administration. */
define("DEVEL_MODE", '.trueFalse($conf_def_property_list['DEVEL_MODE']['default']).');




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
    foreach ( array_keys($def_file_list) as  $config_code )
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
            unset($propertyList);
            reset($conf_def_property_list);
            foreach($conf_def_property_list as $propName => $propDef )
            {
                $propertyList[] = array('propName'  => $propName
                                       ,'propValue' => $propDef['default']);
            }

            $conf_file = get_conf_file($config_code);

            if ( !file_exists($conf_file) ) touch($conf_file);

            if ( is_array($propertyList) && count($propertyList)>0 )
            {

                if ( write_conf_file($conf_def,$conf_def_property_list,$propertyList,$conf_file,realpath(__FILE__)) )
                {
                    // calculate hash of the config file
                    $conf_hash = md5_file($conf_file); // md5_file not in PHP 4.1
                    //$conf_hash = filemtime($conf_file);
                    save_config_hash_in_db($config_code,$conf_hash);
                }
            }
        }
    }
    
}

// Check File System

$coursesRepositorySysWriteProtected = FALSE;
$coursesRepositorySysMissing        = FALSE;
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

include_once($newIncludePath."lib/admin.lib.inc.php");
$idAdmin = add_user( cleanwritevalue($adminNameForm)
        , cleanwritevalue($adminSurnameForm)
        , cleanwritevalue($adminEmailForm)
        , cleanwritevalue($adminPhoneForm)
        , ''
        , cleanwritevalue($loginForm)
        , cleanwritevalue($passForm)
        , TRUE);

set_user_admin($idAdmin);
?>