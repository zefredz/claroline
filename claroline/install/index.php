<?php // $Id$
/**
 * CLAROLINE
 *
 * GOAL : install claroline 1.6 on server
 *
 * @version 1.6 $Revision$
 *
 * @copyright 2001-2005 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE 
 *
 * @see http://www.claroline.net/wiki/install/
 *
 * @author Claro Team <cvs@claroline.net>
 *
 * @package INSTALL
 * 
 */


/* LET DEFINE ON SEPARATE LINES !!!*/
// __LINE__ use to have arbitrary number but order of panels

define ("DISP_WELCOME",__LINE__);
define ("DISP_LICENCE",__LINE__);
//define ("DISP_FILE_SYSTEM_SETTING",__LINE__);
define ("DISP_DB_CONNECT_SETTING",__LINE__);
define ("DISP_DB_NAMES_SETTING",__LINE__);
define ("DISP_ADMINISTRATOR_SETTING",__LINE__);
define ("DISP_PLATFORM_SETTING",__LINE__);
define ("DISP_ADMINISTRATIVE_SETTING",__LINE__);
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

if (!empty($_GET))      {extract($_GET, EXTR_OVERWRITE);}
if (!empty($_POST))     {extract($_POST, EXTR_OVERWRITE);}
if (!empty($_SERVER))     {extract($_SERVER, EXTR_OVERWRITE);}

$newIncludePath ="../inc/";
include ($newIncludePath."installedVersion.inc.php");

include ("../lang/english/complete.lang.php");
include ("../lang/english/locale_settings.php");

include ($newIncludePath."lib/auth.lib.inc.php"); // to generate pass and to cryto it if needed
include ("./install.lib.inc.php");
include ($newIncludePath."lib/config.lib.inc.php");
include ($newIncludePath."lib/claro_main.lib.php");

$panelSequence  = array(
DISP_WELCOME,
DISP_LICENCE,
//DISP_FILE_SYSTEM_SETTING,
DISP_DB_CONNECT_SETTING,
DISP_DB_NAMES_SETTING,
DISP_ADMINISTRATOR_SETTING,
DISP_PLATFORM_SETTING,
DISP_ADMINISTRATIVE_SETTING,
DISP_LAST_CHECK_BEFORE_INSTALL,
DISP_RUN_INSTALL_COMPLETE);
//DISP_RUN_INSTALL_NOT_COMPLETE is not a panel of sequence
$panelTitle[DISP_WELCOME]                   = $langRequirements;
$panelTitle[DISP_LICENCE]                   = $langLicence;
//$panelTitle[DISP_FILE_SYSTEM_SETTING]      = $langFileSystemSetting;
$panelTitle[DISP_DB_CONNECT_SETTING]        = $langDBSetting;
$panelTitle[DISP_DB_NAMES_SETTING]          = $langMysqlNames;
$panelTitle[DISP_ADMINISTRATOR_SETTING]     = $langAdminSetting;
$panelTitle[DISP_PLATFORM_SETTING]          = $langCfgSetting;
$panelTitle[DISP_ADMINISTRATIVE_SETTING]    = 'Additional Informations<small> (optional)</small>';
$panelTitle[DISP_LAST_CHECK_BEFORE_INSTALL] = $langLastCheck;
$panelTitle[DISP_RUN_INSTALL_COMPLETE]      = 'Claroline Installation succeeds';

//$rootSys="'.realpath($pathForm).'";


if($_REQUEST['cmdLicence'])
{
    $cmd=DISP_LICENCE;
}
//elseif($_REQUEST['cmdFILE_SYSTEM_SETTING'])
//{
//    $cmd=DISP_FILE_SYSTEM_SETTING;
//}
elseif($_REQUEST['cmdDB_CONNECT_SETTING'])
{
    $cmd=DISP_DB_CONNECT_SETTING;
}
elseif($_REQUEST['cmdDbNameSetting'])
{
    $cmd=DISP_DB_NAMES_SETTING;
}
elseif($_REQUEST['cmdAdministratorSetting'])
{
    $cmd=DISP_ADMINISTRATOR_SETTING;
}
elseif($_REQUEST['cmdPlatformSetting'])
{
    $cmd=DISP_PLATFORM_SETTING;
}
elseif($_REQUEST['install6'])
{
    $cmd=DISP_LAST_CHECK_BEFORE_INSTALL;
}
elseif($_REQUEST['cmdAdministrativeSetting'])
{
    $cmd=DISP_ADMINISTRATIVE_SETTING;
}
elseif($_REQUEST['cmdDoInstall'])
{
    $cmd=DISP_RUN_INSTALL_COMPLETE;
}

##### STEP 0 INITIALISE FORM VARIABLES IF FIRST VISIT ##################

if(!$_REQUEST['alreadyVisited'] || $_REQUEST['resetConfig']) // on first step prupose values
{
     include ('./defaultsetting.inc.php');
}
else
{
    extract($_REQUEST);
    $campusForm  = $_REQUEST['campusForm'];
}

if ($PHP_SELF == "") $PHP_SELF = $_SERVER['PHP_SELF'];


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


$canRunCmd = TRUE;
if($_REQUEST['fromPanel'] == DISP_ADMINISTRATOR_SETTING || $_REQUEST['cmdDoInstall'])
{
    if (empty($adminSurnameForm)||empty($passForm)||empty($loginForm)||empty($adminNameForm)||empty($adminEmailForm)||!is_well_formed_email_address($adminEmailForm))
    {
        $adminDataMissing = TRUE;
        if (empty($loginForm))             $missing_admin_data[] = 'login';
        if (empty($passForm))             $missing_admin_data[] = 'password';
        if (empty($adminSurnameForm))     $missing_admin_data[] = 'firstname';
        if (empty($adminNameForm))         $missing_admin_data[] = 'lastname';
        if (empty($adminEmailForm))     $missing_admin_data[] = 'email';
        if (!empty($adminEmailForm) && !is_well_formed_email_address($adminEmailForm))     $error_in_admin_data[] = 'email';
        if (is_array ($missing_admin_data))  $msg_missing_admin_data = '<font color="red" >Please fill '.implode(', ',$missing_admin_data).'</font><br>';
        if (is_array ($error_in_admin_data)) $msg_missing_admin_data .= '<font color="red" >Please check '.implode(', ',$error_in_admin_data).'</font><br>';
        if ($cmd>DISP_ADMINISTRATOR_SETTING)
        {
            $display=DISP_ADMINISTRATOR_SETTING;
        }
        else
        {
            $display=$cmd;
        }
        $canRunCmd = FALSE;
    }
    else
    {
        // here add some check  on email, password crackability, ... of admin.
    }
}

if($_REQUEST['fromPanel'] == DISP_ADMINISTRATIVE_SETTING )
{
    if (empty($contactEmailForm)||empty($contactNameForm)||!is_well_formed_email_address($contactEmailForm))
    {
        $administrativeDataMissing = TRUE;
        if (empty($contactNameForm))
        {
            $check_administrative_data[] = 'name of contact ';
            $contactNameForm = $adminNameForm;
        }
        if (empty($contactEmailForm)||!is_well_formed_email_address($contactEmailForm))
        {
            $check_administrative_data[] = 'email ';
            if (empty($contactEmailForm))
            {
                $contactEmailForm = $adminEmailForm;
            }
            else     // if not empty but wrong, I can suppose the good value, so I let it blank
            {
                $contactEmailForm ="";
            }
        }
        $msg_missing_administrative_data = '<font color="red" >Please check '.implode(', ',$check_administrative_data).'</font><br>';
        if ( $cmd > DISP_ADMINISTRATIVE_SETTING )
        {
            $display = DISP_ADMINISTRATIVE_SETTING;
        }
        else
        {
            $display = $cmd;
        }
        $canRunCmd = FALSE;
    }
    else
    {
        // here add some check  on email, password crackability, ... of admin.
    }
}

if ($_REQUEST['fromPanel'] == DISP_DB_CONNECT_SETTING || $_REQUEST['cmdDoInstall'])
{
    // Check Connection //
    $databaseParam_ok = TRUE;
    $db = @mysql_connect("$dbHostForm", "$dbUsernameForm", "$dbPassForm");
    if ( mysql_errno() > 0 ) // problem with server
    {
        $no  = mysql_errno();
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
                    Server unavailable. Is your MySQL server started ?';
        $msg_no_connection .= '
                    <BR>
                    <font color="blue">
                        Fix this problem before going further
                    </font>
                    <BR>
                </P>';
        $databaseParam_ok = FALSE;
        $canRunCmd = FALSE;
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


// CHECK DATA OF DB NAMES Form
if ($_REQUEST['fromPanel'] == DISP_DB_NAMES_SETTING || $_REQUEST['cmdDoInstall'])
{
    $regexpPatternForDbName = "^[a-z0-9][a-z0-9_]*$";
    // Now mysql connect param are ok, try  to use given DBNames
    // 1° check given string
    // 2° check if db exists

    $databaseParam_ok = TRUE;
    if ($singleDbForm) $dbStatsForm = $dbNameForm;
    if ($singleDbForm) $statsTblPrefixForm = $mainTblPrefixForm;
    $dbNameForm = trim($dbNameForm);
    $dbStatsForm = trim($dbStatsForm);
    $databaseNameValid = TRUE;
    $databaseAlreadyExist = FALSE;
    if (!eregi($regexpPatternForDbName,$dbNameForm)|| strlen($dbNameForm)>64 
        ||
        !eregi($regexpPatternForDbName,$dbStatsForm)|| strlen($dbStatsForm)>64 ) 
    
    //  64 is  the  max  for the name of a mysql database
    {
        $databaseNameValid = FALSE;
        $msgErrorDbMain_dbNameToolLong = (strlen($dbNameForm)>64);
        $msgErrorDbMain_dbNameInvalid = !eregi($regexpPatternForDbName,$dbNameForm);
        $msgErrorDbMain_dbNameBadStart = !eregi("^[a-z0-9]",$dbNameForm);
        
        if (!$singleDbForm)
        {
            $msgErrorDbMain_dbName = $msgErrorDbMain_dbNameToolLong ||
                                        $msgErrorDbMain_dbNameInvalid ||
                                        $msgErrorDbMain_dbNameBadStart ;
        
            $msgErrorDbStat_dbNameInvalid = !eregi($regexpPatternForDbName,$dbStatsForm);
            $msgErrorDbStat_dbNameToolLong = (strlen($dbStatsForm)>64);
            $msgErrorDbStat_dbNameBadStart = !eregi("^[a-z0-9]",$dbStatsForm);
        }
        
    }
    else
    {
        $db = @mysql_connect($dbHostForm, $dbUsernameForm, $dbPassForm);
        $valMain = check_if_db_exist($dbNameForm  ,$db);
        if ($dbStatsForm == $dbNameForm) $confirmUseExistingStatsDb = $confirmUseExistingMainDb ;
        if (!$singleDbForm) $valStat = check_if_db_exist($dbStatsForm ,$db);
        if (($valMain && !$confirmUseExistingMainDb)
             ||
             ($valStat && !$confirmUseExistingStatsDb ))
        {   
            $databaseAlreadyExist              = TRUE;
            if ($valMain)    $mainDbNameExist  = TRUE;
            if ($valStat)    $statsDbNameExist = TRUE;
        }
    }
    if (   $databaseAlreadyExist 
       || !$databaseNameValid    )
    {
        $canRunCmd = FALSE;
        if ($cmd > DISP_DB_NAMES_SETTING)
        {
            $databaseAlreadyExist             = TRUE;
            if ($valMain)    $mainDbNameExist  = TRUE;
            if ($valStat)    $statsDbNameExist = TRUE;
            $canRunCmd                        = FALSE;
        }
        else
        {
            $databaseAlreadyExist = false;
        }
        if (!$canRunCmd)
        {
            if ($cmd > DISP_DB_NAMES_SETTING)
            {
                $display = DISP_DB_NAMES_SETTING;
            }
            else
            {
                $display= $cmd;
            }
        }
    }
    else
    {
        $databaseAlreadyExist = false;
    }
    // Check to add
    // If database already exist but confirm , ok but not if one of table exist in the db.

}

if($_REQUEST['fromPanel'] == DISP_PLATFORM_SETTING || $_REQUEST['cmdDoInstall'])
{
    $platformDataMissing = FALSE;
    if (empty($urlForm))
    {
        $platformDataMissing = TRUE;
        $missing_platform_data[] = 'the <B>complete url</b> to your campus (something like <em>http://'.$_SERVER['SERVER_NAME'].$urlAppendPath.'/</em>)';

    }

    if (empty($campusForm))
    {
        $platformDataMissing = TRUE;
        $missing_platform_data[]='the <B>name</b> of your online campus';
    }

    if($platformDataMissing)
    {
        $canRunCmd = FALSE;
        $msg_missing_platform_data = '<font color="red" >Please fill '.implode(', ',$missing_platform_data).'</font><br>';
        if ($cmd > DISP_PLATFORM_SETTING)
        {
            $display = DISP_PLATFORM_SETTING;
        }
        else
        {
            $display= $cmd;
        }

    }
}



// ALL Check are done.
// $canRunCmd has set during checks

if ($canRunCmd)
{
    // OK TEST WAS GOOD, What's the next step ?

    // SET default display
    $display=DISP_WELCOME;
    if($_REQUEST['cmdLicence'])
    {
        $display = DISP_LICENCE;
    }
//    elseif($_REQUEST['cmdFILE_SYSTEM_SETTING'])
//    {
//        $display = DISP_FILE_SYSTEM_SETTING;
//    }
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
    elseif($_REQUEST['cmdAdministratorSetting'])
    {
        $display = DISP_ADMINISTRATOR_SETTING;
    }
    elseif($_REQUEST['cmdPlatformSetting'])
    {
        $display = DISP_PLATFORM_SETTING;
    }
    elseif($_REQUEST['cmdAdministrativeSetting'])
    {
        $display = DISP_ADMINISTRATIVE_SETTING;
    }
    elseif($_REQUEST['cmdDoInstall'])
    {
        include('./do_install.inc.php');
    }
 }





//PREPARE DISPLAY


if ($display==DISP_DB_NAMES_SETTING)
{
    // GET DB Names  //
    // this is  to prevent duplicate before submit
    $db = @mysql_connect("$dbHostForm", "$dbUsernameForm", "$dbPassForm");
    $sql = "show databases";
    $res = claro_sql_query($sql,$db);
    while ($__dbName = mysql_fetch_array($res, MYSQL_NUM))
    {
        $existingDbs[]=$__dbName[0];
    }
    unset($__dbName);
}

if ($display==DISP_ADMINISTRATIVE_SETTING)
{
    if ($contactNameForm == '*not set*')
    {
        $contactNameForm     = $adminSurnameForm.' '.$adminNameForm;
    }

    if ($contactEmailForm == '*not set*')
    {
        $contactEmailForm     = $adminEmailForm;
    }

    if ($contactPhoneForm == '*not set*')
    {
        $contactPhoneForm     = $adminPhoneForm;
    }
}





















// BEGIN OUTPUT

// COMMON OUTPUT Including top of form  and list of hidden values
?>
<html>
<head>

<title>
-- Claroline installation -- version <?php echo $version_file_cvs ?>
</title>

<link rel="stylesheet" href="../css/default.css" type="text/css" >
<style media="print" type="text/css"  >
    .notethis { font-weight : bold;  }
</style>
<style  type="text/css"  >
    .notethis { color : red; }
    .setup_error { background:white; margin-left: 15px;    margin-right: 15px; }
</style>

</head>
<body dir="<?php echo $text_dir ?>">
<center>
<form action="<?php echo $_SERVER['PHP_SELF']?>" method="post">
<table  bgcolor="#DDDDDD"  cellpadding="10" cellspacing="0" border="0" width="650" class="claroTable">
        <tr  bgcolor="#000066" >
            <th valign="top">
               <FONT color="White">
                    Claroline 1.6 (<?php echo $version_file_cvs ?>) - installation
                </font>
            </th>
        </TR>
    <tr>
        <td>
<?php
echo '<input type="hidden" name="alreadyVisited" value="1">'                                                 ."\n"
    .'<input type="hidden" name="urlAppendPath"                value="'.$urlAppendPath.'">'                  ."\n"
    .'<input type="hidden" name="urlEndForm"                   value="'.$urlEndForm.'">'                     ."\n"
    .'<input type="hidden" name="courseRepositoryForm"         value="'.$courseRepositoryForm.'">'           ."\n"
    .'<input type="hidden" name="pathForm" value="'.str_replace("\\","/",realpath($pathForm)."/").'" >'      ."\n"
    .'<input type="hidden" name="dbHostForm"                   value="'.$dbHostForm.'">'                     ."\n"
    .'<input type="hidden" name="dbUsernameForm"               value="'.$dbUsernameForm.'">'                 ."\n\n"
    .'<input type="hidden" name="singleDbForm"                 value="'.$singleDbForm.'">'                   ."\n\n"
    .'<input type="hidden" name="dbPrefixForm"                 value="'.$dbPrefixForm.'">'                   ."\n"
    .'<input type="hidden" name="dbNameForm"                   value="'.$dbNameForm.'">'                     ."\n"
    .'<input type="hidden" name="dbStatsForm"                  value="'.$dbStatsForm.'">'                    ."\n"
    .'<input type="hidden" name="mainTblPrefixForm"            value="'.$mainTblPrefixForm.'">'              ."\n"
    .'<input type="hidden" name="statsTblPrefixForm"           value="'.$statsTblPrefixForm.'">'              ."\n"
    .'<input type="hidden" name="dbMyAdmin"                    value="'.$dbMyAdmin.'">'                      ."\n"
    .'<input type="hidden" name="dbPassForm"                   value="'.$dbPassForm.'">'                     ."\n\n"
    .'<input type="hidden" name="urlForm"                      value="'.$urlForm.'">'                        ."\n"
    .'<input type="hidden" name="adminEmailForm"               value="'.cleanoutputvalue($adminEmailForm).'">'   ."\n"
    .'<input type="hidden" name="adminPhoneForm"               value="'.cleanoutputvalue($adminPhoneForm).'">'   ."\n"
    .'<input type="hidden" name="adminNameForm"                value="'.cleanoutputvalue($adminNameForm).'">'    ."\n"
    .'<input type="hidden" name="adminSurnameForm"             value="'.cleanoutputvalue($adminSurnameForm).'">' ."\n\n"
    .'<input type="hidden" name="loginForm"                    value="'.cleanoutputvalue($loginForm).'">'        ."\n"
    .'<input type="hidden" name="passForm"                     value="'.cleanoutputvalue($passForm).'">'         ."\n\n"
    .'<input type="hidden" name="languageForm"                 value="'.$languageForm.'">'                   ."\n\n"
    .'<input type="hidden" name="campusForm"                   value="'.cleanoutputvalue($campusForm).'">'       ."\n"
    .'<input type="hidden" name="adminPhoneForm"               value="'.cleanoutputvalue($adminPhoneForm).'">'   ."\n"
    .'<input type="hidden" name="contactNameForm"              value="'.cleanoutputvalue($contactNameForm).'">'  ."\n"
    .'<input type="hidden" name="contactEmailForm"             value="'.cleanoutputvalue($contactEmailForm).'">' ."\n"
    .'<input type="hidden" name="contactPhoneForm"             value="'.cleanoutputvalue($contactPhoneForm).'">' ."\n"
    .'<input type="hidden" name="institutionForm"              value="'.cleanoutputvalue($institutionForm).'">'  ."\n"
    .'<input type="hidden" name="institutionUrlForm"           value="'.$institutionUrlForm.'">'             ."\n\n"
    .'<!-- BOOLEAN -->'                                                                                      ."\n"
    .'<input type="hidden" name="enableTrackingForm"           value="'.$enableTrackingForm.'">'             ."\n"
    .'<input type="hidden" name="allowSelfReg"                 value="'.$allowSelfReg.'">'                   ."\n"
    .'<input type="hidden" name="userPasswordCrypted"          value="'.$userPasswordCrypted.'">'            ."\n"
    .'<input type="hidden" name="encryptPassForm"              value="'.$encryptPassForm.'">'                ."\n"
    .'<input type="hidden" name="confirmUseExistingMainDb"     value="'.$confirmUseExistingMainDb.'">'       ."\n"
    .'<input type="hidden" name="confirmUseExistingStatsDb"    value="'.$confirmUseExistingStatsDb.'">';

















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
                '.sprintf($langStepNOfN,(array_search(DISP_WELCOME, $panelSequence)+1),count($panelSequence)).' : '.$panelTitle[DISP_WELCOME].'
                </h2>';
    // check if an claroline configuration file doesn't already exists.
    if ( file_exists('../inc/conf/claro_main.conf.inc.php')
    ||   file_exists('../inc/conf/claro_main.conf.php')
    ||   file_exists('../inc/conf/config.inc.php')
    ||   file_exists('../include/config.inc.php')
    ||   file_exists('../include/config.php'))
    {
        echo '
 <div style="background-color:#FFFFFF;margin:20px;padding:5px">
    <b>
        <font color="red">Warning !</font> 
        The installer has detected an existing
        claroline platform on your system.
        <br>
    </b>
    <ul>';
        if ($is_upgrade_available)
        {
            echo '
        <li>
            For Claroline upgrade click
            <a href="../admin/upgrade/upgrade.php">here</a>.
        </li>';
        }
        else
        {
            echo '
        <li>
            For claroline upgrade please wait a stable release.
        </li>';
        }
        echo     '
        <li>
            For claroline overwrite click on "next" button
        </li>
    </ul>
</div>';
    }


    if(!$stable)
    {
        echo '
        
        <B>
        Notice .
        This version is not considered as stable
        and is not aimed for production.
        </B><br>

        If  something goes wrong,
        come talk on our support forum at
        <a href="http://www.claroline.net/forum/viewforum.php?f=62" target="_clarodev">http://www.claroline.net</a>.';
    }

    if($SERVER_SOFTWARE=="") $SERVER_SOFTWARE = $_SERVER["SERVER_SOFTWARE"];
    $WEBSERVER_SOFTWARE = explode(" ",$SERVER_SOFTWARE,2);
    echo '
    <p>Read thoroughly <a href="../../INSTALL.txt">INSTALL.txt</a>
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
    warnIfExtNotLoaded("tokenizer");
//    warnIfExtNotLoaded("exif"); // exif  would be needed later for pic view properties.
//    warnIfExtNotLoaded("nameOfExtention"); // list here http://www.php.net/manual/fr/resources.php

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
        echo '
            <LI>
                <font color="red">Warning !</font> magic_quotes_gpc is set to <strong>off</strong>.
                <br>
                Change the following parameter in your <i>php.ini</i> file to this value :<br>
                <font color="blue">
                <code>magic_quotes_gpc = on</code>
                </font>
            </LI>';
    }

    if (    ini_get('display_errors')
        && (ini_get('error_reporting') & E_NOTICE )
        )
    {
        echo '
            <LI>
                <font color="red">
                    Warning !
                </font>
                error_reporting include <strong>E_NOTICE</strong>.
                <br>
                Change the following parameter in your <i>php.ini</i> file to this value :<br>
                <font color="blue">
                    <code>error_reporting  =  E_ALL & ~E_NOTICE</code>
                </font><BR>
                or<BR>

                <font color="blue">
                    <code>display_errors = off</code>
                </font>
                <br>
            </LI>';
    }

    echo '
        </UL>
    </li>

    <li>
        Checking file access to web directory.
        <ul>
        '.(is_writable('../..')?'':'</li>
            <font color="red">Warning !</font> Claroline is not able to write on : <br>
            <nobr><code>'.realpath('../..').'</code><nobr>
                <br>
                Change this file permission the server file system.</li>
        ').'
        '.(is_readable('../..')?'':'
            <li><font color="red">Warning !</font> claroline is not able to read on : <br>
            <nobr><code>'.realpath('../..').'</code><nobr>
            <br>
            Change this file permission the server file system.
        </li>
        ').'
        </ul>
    </li>
</ul>
<p>
If the checks above has passed without any problem, click on the <i>Next</i> button to continue.
<p align="right"><input type="submit" name="cmdLicence" value="Next &gt;"></p>';

}



###################################################################
############### STEP 2 LICENSE  ###################################
###################################################################
elseif($display==DISP_LICENCE)
{
    echo '
                <input type="hidden" name="fromPanel" value="'.$display.'">
                <h2>
                '.sprintf($langStepNOfN,(array_search(DISP_LICENCE, $panelSequence)+1),count($panelSequence)).' : '.$panelTitle[DISP_LICENCE].'
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
                    <!-- input type="submit" name="cmdFILE_SYSTEM_SETTING" value="I accept &gt;" -->
                    <input type="submit" name="cmdDB_CONNECT_SETTING" value="I accept &gt;">
                    </td>
                </tr>
            </table>';

}





#########################################################################
###### STEP DISP_FILE_SYSTEM_SETTING ####################################
#########################################################################
/*
elseif($display==DISP_FILE_SYSTEM_SETTING)
{

    echo '
                <input type="hidden" name="fromPanel" value="'.$display.'">
                <h2>
                    '.sprintf($langStepNOfN,(array_search(DISP_FILE_SYSTEM_SETTING, $panelSequence)+1),count($panelSequence)).' : '.$panelTitle[DISP_FILE_SYSTEM_SETTING].'
                </h2>
            </td>
        </tr>
        <tr>
            <td>
<!--            <h4>Absolute path</h4>
                <label for="urlForm">Campus Path (absolute path to your campus)</label><br>
                <input type="text" size="85" id="urlForm" name="urlForm" value="'.$urlForm.'"><br>
                <h4>Relative path</h4>
                <label for="urlAppend">Campus Path (relative path  from document root to your campus)</label><br>
                <input type="text" size="85" id="urlAppend" name="urlAppendPath" value="'.$urlAppendPath.'"><br>
                <br>
-->
                 <label for="courseRepositoryForm"> Course Repository path (relative to index of your campus) </label><br>
                <input type="text"  size="85" id="courseRepositoryForm" name="courseRepositoryForm" value="'.$courseRepositoryForm.'">
                <br>
                <br>
                <table width="100%">
                    <tr>
                        <td>
                            <input type="submit" name="cmdLicence" value="&lt; Back">
                        </td>
                        <td >
                            &nbsp;
                        </td>
                        <td align="right">
                            <input type="submit" name="cmdDB_CONNECT_SETTING" value="Next &gt;">
                        </td>
                    </tr>
                </table>';
}     // cmdDB_CONNECT_SETTING


*/





##########################################################################
###### STEP 3 MYSQL DATABASE SETTINGS ####################################
##########################################################################

elseif($display==DISP_DB_CONNECT_SETTING)
{



    echo '
                <input type="hidden" name="fromPanel" value="'.$display.'">
                <h2>
                    '.sprintf($langStepNOfN,(array_search(DISP_DB_CONNECT_SETTING, $panelSequence)+1),count($panelSequence)).' : '.$panelTitle[DISP_DB_CONNECT_SETTING].'
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
                            <input type="text" size="25" id="dbHostForm" name="dbHostForm" value="'.cleanoutputvalue($dbHostForm).'">
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
                            <input type="text"  size="25" id="dbUsernameForm" name="dbUsernameForm" value="'.cleanoutputvalue($dbUsernameForm).'">
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
                            <input type="text"  size="25" id="dbPassForm" name="dbPassForm" value="'.cleanoutputvalue($dbPassForm).'">
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
                                    <input type="radio" id="enableTrackingForm_enabled" name="enableTrackingForm" value="1" '.($enableTrackingForm?'checked':'').'>
                                    <label for="enableTrackingForm_enabled">
                                        Enabled
                                    </label>
                            </td>
                            <td>
                                    <input type="radio" id="enableTrackingForm_disabled" name="enableTrackingForm" value="0" '.($enableTrackingForm?'':'checked').'>
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
                                    (a database is created at each course creation)
                                </small>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <td>

                            <input type="submit" name="cmdLicence" value="&lt; Back">
                            <!-- input type="submit" name="cmdFILE_SYSTEM_SETTING" value="&lt; Back" -->
                        </td>
                        <td >
                            &nbsp;
                        </td>
                        <td align="right">
                            <input type="submit" name="cmdDbNameSetting" value="Next &gt;">
                        </td>
                    </tr>
                </table>';
}     // cmdDB_CONNECT_SETTING


##########################################################################
###### STEP 4 MYSQL DATABASE SETTINGS ####################################
##########################################################################
elseif($display == DISP_DB_NAMES_SETTING )
{
    echo '
            <input type="hidden" name="fromPanel" value="'.$display.'">
                <h2>
                    '.sprintf($langStepNOfN,(array_search(DISP_DB_NAMES_SETTING, $panelSequence)+1),count($panelSequence)).' : '.$panelTitle[DISP_DB_NAMES_SETTING].'
                </h2>
                '.($singleDbForm?'':$langDBSettingNamesIntro).'
            </td>
        </tr>
        <tr>
            <td>
                '.$msg_no_connection.'
                <table width="100%">';
                if (isset($databaseNameValid) && !$databaseNameValid)
                {
                    
                    echo '
                    <tr>
                        <td colspan="2">
                            <P class="setup_error">
                                <font color="red">Warning !</font> 
                                : Database <em>'.$dbNameForm.'</em> is not valid. 
                                <ul>'
                    .($msgErrorDbMain_dbName?'<LI>Main db<UL>':'')
                    .($msgErrorDbMain_dbNameToolLong?'<LI>dbName Too Long':'')
                    .($msgErrorDbMain_dbNameInvalid?'<LI>dbName Invalid Check the character (only letter ciffer and _)':'')
                    .($msgErrorDbMain_dbNameBadStart?'<LI>dbName Must begin by a letter':'')
                    .($msgErrorDbStat_dbName?'</UL><LI>Stat db<UL>':'')
                    .($msgErrorDbStat_dbNameToolLong?'<LI>dbName Too Long':'')
                    .($msgErrorDbStat_dbNameInvalid?'<LI>dbName Invalid. Check the character (only letter ciffer and _)':'')
                    .($msgErrorDbStat_dbNameBadStart?'<LI>dbName Must begin by a letter':'')
                    .'
                                </UL>
                                </UL>
                                
                            </P>
                        </td>
                    </tr>';
                }
                if ($mainDbNameExist)
                    echo '
                    <tr>
                        <td colspan="2">
                            <P class="setup_error">
                                <font color="red">Warning !</font>
                                : Database <em>'.$dbNameForm.'</em> already exists
                                <BR>
                                Claroline could overwrite data previsously recorded
                                in these database tables.
                                <BR>
                                <input type="checkbox" name="confirmUseExistingMainDb"  id="confirmUseExistingMainDb" value="true" '.($confirmUseExistingMainDb?'checked':'').'>
                                <label for="confirmUseExistingMainDb" >
                                    <B>I know, I want to use this database.</B>
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
                            <input type="text"  size="25" id="dbNameForm" name="dbNameForm" value="'.cleanoutputvalue($dbNameForm).'">
                        </td>
                        <td>
                            &nbsp;
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label for="mainTblPrefixForm">
                                Prefix for names of main tables
                            </label>
                        </td>
                        <td>
                            <input type="text"  size="5" id="mainTblPrefixForm" name="mainTblPrefixForm" value="'.cleanoutputvalue($mainTblPrefixForm).'">
                        </td>
                        <td>
                            &nbsp;
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3">
                        </td>
                    </tr>

                    ';
    if (!$singleDbForm)
    {
        if ($statsDbNameExist && $dbStatsForm!=$dbNameForm)
        {
            echo '
                    <tr>
                        <td colspan="2">
                            <P class="setup_error">
                                <font color="red">Warning !</font>
                                : '.$dbStatsForm.' already exist
                                <BR>
                                Claroline could overwrite data previsously recorded
                                in these database tables.
                                <BR>
                                <input type="checkbox" name="confirmUseExistingStatsDb"  id="confirmUseExistingStatsDb" value="true" '.($confirmUseExistingStatsDb?'checked':'').'>
                                <label for="confirmUseExistingStatsDb" >
                                    <B>I know, I want to use this database.</B>
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
                            <input type="text"  size="25" id="dbStatsForm" name="dbStatsForm" value="'.cleanoutputvalue($dbStatsForm).'">
                        </td>
                        <td>
                            &nbsp;
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label for="statsTblPrefixForm">
                                Prefix for names of tracking tables
                            </label>
                        </td>
                        <td>
                            <input type="text"  size="5" id="statsTblPrefixForm" name="statsTblPrefixForm" value="'.cleanoutputvalue($statsTblPrefixForm).'">
                        </td>
                        <td>
                            &nbsp;
                        </td>
                    </tr>
                    
                    <tr>
                        <td colspan="3">
                        <blockquote><small>
        Normally, Claroline creates a separate database for the tracking tables. 
        But, you can share the same database for the main tables and the tracking ones
        (you can specify a prefix for each of these tables).
    </small></blockquote>
                        </td>
                    </tr>
        ';
    }
    echo '
                    <tr>
                        <td>
                            <label for="dbPrefixForm">
                                '.($singleDbForm?'Prefix for names of course tables':$langDbPrefixForm).'
                            </label>
                        </td>
                        <td>
                            <input type="text"  size="25" id="dbPrefixForm" name="dbPrefixForm" value="'.cleanoutputvalue($dbPrefixForm).'">
                        </td>
                        <td>
                            e.g. \''.$dbPrefixForm.'\'
                        </td>
                    </tr>';
    if (!$singleDbForm)
    {
        echo '
                    <tr>
                        <td colspan="3">
                        <blockquote>
                        <small>
                            <b>
                            Afterwards, Claroline will create a new database for each newly 
                            created course. 
                            </b>
                            <BR>
                            You can specify a prefix for these database names.
                        </small>
                        </blockquote>
                        </td>
                    </tr>';

    }              
                    echo '
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
                            <input type="submit" name="cmdAdministratorSetting" value="Next &gt;">
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
}     // cmdDB_CONNECT_SETTING






##########################################################################
###### STEP CONFIG SETTINGS ##############################################
##########################################################################
elseif($display==DISP_ADMINISTRATOR_SETTING)

{
    echo '
                <input type="hidden" name="fromPanel" value="'.$display.'">
                <h2>
                    '.sprintf($langStepNOfN,(array_search(DISP_ADMINISTRATOR_SETTING, $panelSequence)+1),count($panelSequence)).' : '.$panelTitle[DISP_ADMINISTRATOR_SETTING].'
                </h2>
            </td>
        </tr>
        <tr>
            <td>
              

                  '.$msg_missing_admin_data.'
                  '.$msg_admin_exist.'

                <table width="100%">
                    <tr>
                        <tr>
                            <td>
                                <b><label for="loginForm">'.$langAdminLogin.'</label></b>
                            </td>
                            <td>
                                <input type="text" size="40" id="loginForm" name="loginForm" value="'.cleanoutputvalue($loginForm).'">
                            </td>
                            <td>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <b><label for="passForm">'.$langAdminPass.'</label></b>
                            </td>
                            <td>
                                <input type="text" size="40" id="passForm" name="passForm" value="'.cleanoutputvalue($passForm).'">
                            </td>
                            <td>
                                e.g. '.generePass(8).'
                            </td>
                        </tr>
                        <td>
                                <label for="adminEmailForm">'.$langAdminEmail.'</label>
                            </td>
                            <td>
                            <input type="text" size="40" id="adminEmailForm" name="adminEmailForm" value="'.cleanoutputvalue($adminEmailForm).'">
                            </td>
                            <td>
                            </td>
                        </tr>
                        <td>
                                <label for="adminPhoneForm">Phone</label>
                            </td>
                            <td>
                            <input type="text" size="40" id="adminPhoneForm" name="adminPhoneForm" value="'.cleanoutputvalue($adminPhoneForm).'">
                            </td>
                            <td>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="adminNameForm">'.$langAdminName.'</label>
                            </td>
                            <td>
                                <input type="text" size="40" id="adminNameForm" name="adminNameForm" value="'.cleanoutputvalue($adminNameForm).'">
                            </td>
                            <td>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="adminSurnameForm">'.$langAdminSurname.'</label>
                            </td>
                            <td>
                                <input type="text" size="40" id="adminSurnameForm" name="adminSurnameForm" value="'.cleanoutputvalue($adminSurnameForm).'">
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
                    '.sprintf($langStepNOfN,(array_search(DISP_PLATFORM_SETTING, $panelSequence)+1),count($panelSequence)).' : '.$panelTitle[DISP_PLATFORM_SETTING].'
                </h2>';
    echo '
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
                                <input type="text" size="40" id="campusForm" name="campusForm" value="'.cleanoutputvalue($campusForm).'">
                            </td>
                        </tr>
                    <tr>
                        <td>
                            <label for="urlForm">Complete URL</label>
                        </td>
                        <td colspan="2">
                            <input type="text" size="60" id="urlForm" name="urlForm" value="'.cleanoutputvalue($urlForm).'">
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3">
                             <label for="courseRepositoryForm">Courses repository path (relative to the url above) </label><br>
                        </td>
                    </tr>
                    <tr>
                        <td>
                        </td>
                        <td colspan="2">
                            <input type="text"  size="60" id="courseRepositoryForm" name="courseRepositoryForm" value="'.cleanoutputvalue($courseRepositoryForm).'">
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label for="languageForm">Main language</label>
                        </td>
                        <td colspan="2">
                            <select id="languageForm" name="languageForm">    ';
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
            <tr>
                <td colspan=3><br>

                    <h4>User </h4>
                </td>
            </tr>
            <tr>
                <td>
                    Self-registration
                </td>
                <td>
                    <input type="radio" id="allowSelfReg_1" name="allowSelfReg" value="1" '.($allowSelfReg?'checked':'').'>
                       <label for="allowSelfReg_1">Enabled</label>
                </td>
                <td>
                    <input type="radio" id="allowSelfReg_0" name="allowSelfReg" value="0" '.($allowSelfReg?'':'checked').'>
                       <label for="allowSelfReg_0">Disabled</label>
                </td>
            </tr>
                    <tr>
                        <td>
                            Password in db
                        </td>
                        <td>
                            <input type="radio" name="encryptPassForm" id="encryptPassForm_0" value="0"  '.($encryptPassForm?'':'checked').'>
                            <label for="encryptPassForm_0">Clear text</label>
                        </td>
                        <td>
                            <input type="radio" name="encryptPassForm" id="encryptPassForm_1" value="1" '.($encryptPassForm?'checked':'').'>
                            <label for="encryptPassForm_1">Crypted</label>
                        </td>
                    </tr>
                </table>
                <table width="100%">
                        <tr>
                            <td>
                                <input type="submit" name="cmdAdministratorSetting" value="&lt; Back">
                            </td>
                            <td align="right">
                                <input type="submit" name="cmdAdministrativeSetting" value="Next &gt;">
                            </td>
                        </tr>
                    </table>';
}
###################################################################
###### STEP CONFIG SETTINGS #######################################
###################################################################
elseif($display==DISP_ADMINISTRATIVE_SETTING)
{
    echo '
                 <input type="hidden" name="fromPanel" value="'.$display.'">
                <h2>
                    '.sprintf($langStepNOfN,(array_search(DISP_ADMINISTRATIVE_SETTING, $panelSequence)+1),count($panelSequence)).' : '.$panelTitle[DISP_ADMINISTRATIVE_SETTING].'
                </h2>'
                .$msg_missing_administrative_data ;
    echo '
            </td>
        </tr>
        <tr>
            <td>
                '.$msg_missing_platform_data.'
                <table >
                    <tr>
                        <td colspan="3">
                        <H4>Related organisation</H4>
                    </tr>
                    <tr>
                            <td>
                                <label for="institutionForm">Name</label>
                            </td>
                            <td colspan="2">
                                <input type="text" size="40" id="institutionForm" name="institutionForm" value="'.cleanoutputvalue($institutionForm).'">
                                </td>
                        </tr>
                    <tr>
                        <td>
                            <label for="institutionUrlForm">URL</label>
                        </td>
                        <td colspan="2">
                            <input type="text" size="40" id="institutionUrlForm" name="institutionUrlForm" value="'.cleanoutputvalue($institutionUrlForm).'">
                            <br>
                        </td>
                    </tr>
                <tr>
                        <td colspan="3"><br>
                    </tr>
                    <tr>
                        <td colspan="3">
                        <H4>Campus contact</H4>
                    </tr>
                    <tr>
                        <td>
                            <label for="contactNameForm">Name</label>
                        </td>
                        <td colspan="2">
                            <input type="text" size="40" id="contactNameForm" name="contactNameForm" value="'.cleanoutputvalue($contactNameForm).'">
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label for="contactEmailForm">Email</label>
                        </td>
                        <td colspan="2">
                            <input type="text" size="40" id="contactEmailForm" name="contactEmailForm" value="'.cleanoutputvalue($contactEmailForm).'">
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3"><br>
                        </td>
                    </tr>
                </table>
                <table width="100%">
                        <tr>
                            <td>
                                <input type="submit" name="cmdPlatformSetting" value="&lt; Back">
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
                    '.sprintf($langStepNOfN,(array_search(DISP_LAST_CHECK_BEFORE_INSTALL, $panelSequence)+1),count($panelSequence)).' : '.$panelTitle[DISP_LAST_CHECK_BEFORE_INSTALL].'
                </h2>
        Here are the values you entered <br>
        <Font color="red">
            Print this page to remember your admin password and other settings
        </font>
        <blockquote>

        <FIELDSET>
        <LEGEND>Database</LEGEND>
        <EM>Account</EM>
        <br>
        Database host : '.cleanoutputvalue($dbHostForm).'<br>
        Database username : '.cleanoutputvalue($dbUsernameForm).'<br>
        Database password : '.cleanoutputvalue((empty($dbPassForm)?"--empty--":$dbPassForm)).'<br>
        <EM>Names</EM>
        <br>
        Main DB name : '.cleanoutputvalue($dbNameForm).'<br>
        Statistics and tracking DB Name : '.cleanoutputvalue($dbStatsForm).'<br>
        Enable single DB : '.($singleDbForm?$langYes:$langNo).'<br>
        ';
    if ($mainTblPrefixForm!="" || $statsTblPrefixForm!="" || $dbPrefixForm!="")
        echo '<em>Prefixes</em><br>';
    if ($mainTblPrefixForm!="")
        echo 'Main tables prefix : '.cleanoutputvalue($mainTblPrefixForm).'<br>';
    if ($statsTblPrefixForm!="")
        echo 'Tracking tables prefix : '.cleanoutputvalue($statsTblPrefixForm).'<br>';
    if ($dbPrefixForm!="")
        echo 'Courses DB prefix : '.cleanoutputvalue($dbPrefixForm).'<br>';
    echo '
        </FIELDSET>

        <FIELDSET>
        <LEGEND>Admin</LEGEND>
        Administrator email : '.cleanoutputvalue($adminEmailForm).'<br>
        Administrator phone : '.cleanoutputvalue($adminPhoneForm).'<br>
        Administrator name : '.cleanoutputvalue($adminNameForm).'<br>
        Administrator surname : '.cleanoutputvalue($adminSurnameForm).'<br>
        <div class="notethis">
                    Administrator login : '.cleanoutputvalue($loginForm).'<br>
                    Administrator password : '.cleanoutputvalue((empty($passForm)?"--empty-- <B>&lt;-- Error !</B>":$passForm)).'<br>
        </div>
        </FIELDSET>
        <FIELDSET>
        <LEGEND>Contact</LEGEND>

        Name : '.cleanoutputvalue((empty($contactNameForm)?"--empty--":$contactNameForm)).'<br>
        Email : '.cleanoutputvalue((empty($contactEmailForm)?$adminEmailForm:$contactEmailForm)).'<br>
        Phone : '.cleanoutputvalue((empty($contactPhoneForm)?"--empty--":$contactPhoneForm)).'
        </FIELDSET>
        <FIELDSET>
        <LEGEND>Campus</LEGEND>
        Your campus name : '.cleanoutputvalue($campusForm).'<br>
        Your organisation : '.cleanoutputvalue($institutionForm).'<br>
        URL of this organisation : '.$institutionUrlForm.'<br>
        Language : '.$languageForm.'<br>
        URL of claroline : '.$urlForm.'<br>
        </FIELDSET>
        <FIELDSET>
        <LEGEND>Config</LEGEND>
        Enable tracking : '.($enableTrackingForm?$langYes:$langNo).'<br>
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
                    <input type="submit" name="cmdAdministrativeSetting" value="&lt; Back">
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
    ||    $statsDbNameExist
    )
    {
        echo "<HR>";
        if ($mainDbNameExist)
            echo '<P><B>'.$langMainDB.'</B> db (<em>'.$dbNameForm.'</em>) already exist <BR>
            <input type="checkbox" name="confirmUseExistingMainDb"  id="confirmUseExistingMainDb" value="true" '.($confirmUseExistingMainDb?'checked':'').'>
            <label for="confirmUseExistingMainDb" >I know, I want use it.</label><BR>
            <font color="red">Warning !</font> : this script write in tables use by claroline.
            </P>';
        if ($statsDbNameExist && $dbStatsForm!=$dbNameForm)
            echo '
        <P>
            <B>'.$langStatDB.'</B> db ('.$dbStatsForm.') already exist
            <BR>
            <input type="checkbox" name="confirmUseExistingStatsDb"  id="confirmUseExistingStatsDb" value="true" '.($confirmUseExistingStatsDb?'checked':'').'>
            <label for="confirmUseExistingStatsDb" >I know, I want use it.</label><BR>
            <font color="red">Warning !</font>
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
    if($statsDbNameCreationError)
        echo "<BR>".$statsDbNameCreationError;
    if($fileAccessInLangRepositoryCreationError)
        echo "<BR>Error on creation : file <EM>".$htAccessName."</EM> in <U>".realpath($htAccessLangPath)."</U><br>";
    if($fileAccessInSqlRepositoryCreationError)
        echo "<BR>Error on creation : file <EM>".$htAccessName."</EM> in <U>".realpath($htAccessSqlPath)."</U><br>";
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
    }

    if ($coursesRepositorySysWriteProtected)
    {
        echo '<BR><b><em>'.$coursesRepositorySys.'</em> is Write Protected.</b>
        Claroline need to have write right to create course.<br>
        change right on this directory and retry.';
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
        change right on this directory and retry.';
    }

    echo '
                <p align="right">
                    <input type="submit" name="alreadyVisited" value="Restart from beginning">
                    <input type="submit" name="cmdPlatformSetting"     value="Previous">
                    <input type="submit" name="cmdDoInstall"         value="Retry">
                </p>';

}











###################################################################
###### STEP RUN_INSTALL_COMPLETE !#################################
###################################################################
elseif($display==DISP_RUN_INSTALL_COMPLETE)
{
?>
            <h2>
<?php
                    echo sprintf($langStepNOfN,(array_search(DISP_RUN_INSTALL_COMPLETE, $panelSequence)+1),count($panelSequence)).' : '.$panelTitle[DISP_RUN_INSTALL_COMPLETE];

 ?>

            </h2>
            <br>
            <br>
            <b>
                Last tip
            </b>
            : we highly recommend that you <strong>protect</strong> or <strong>remove</strong> installer directory.
            <br>
            <br>

            <br>
            <br>


</form>
<form action="../../" method="POST">
        <input type="hidden" name="logout" value="TRUE">
        <input type="hidden" name="uidReset" value="TRUE">

        <input type="submit" value="Go to your newly created campus">
</form>
<?php
}    // STEP RUN_INSTALL_COMPLETE

else
{
    echo '
            <pre>'.$display.'</pre not set.
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
