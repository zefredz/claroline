<?php // $Id$
/**
 * CLAROLINE
 *
 * GOAL : install claroline 1.8 on server
 *
 * @version 1.8 $Revision$
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

define ('DISP_WELCOME',__LINE__);
define ('DISP_LICENSE',__LINE__);
define ('DISP_DB_CONNECT_SETTING',__LINE__);
define ('DISP_DB_NAMES_SETTING',__LINE__);
define ('DISP_ADMINISTRATOR_SETTING',__LINE__);
define ('DISP_PLATFORM_SETTING',__LINE__);
define ('DISP_ADMINISTRATIVE_SETTING',__LINE__);
define ('DISP_LAST_CHECK_BEFORE_INSTALL',__LINE__);
define ('DISP_RUN_INSTALL_NOT_COMPLETE',__LINE__);
define ('DISP_RUN_INSTALL_COMPLETE',__LINE__);
/* LET DEFINE ON SEPARATE LINES !!!*/

error_reporting(E_ERROR | E_WARNING | E_PARSE);

// Place of Config file
$configFileName = 'claro_main.conf.php';
$configFilePath = '../inc/conf/' . $configFileName;

session_start();
$_SESSION = array();
session_destroy();

$newIncludePath ='../inc/';
include $newIncludePath . 'installedVersion.inc.php';

include '../lang/english/complete.lang.php';
include '../lang/english/locale_settings.php';

include $newIncludePath . 'lib/auth.lib.inc.php'; // to generate pass and to cryto it if needed
include './install.lib.inc.php';
include $newIncludePath . 'lib/config.lib.inc.php';
include $newIncludePath . 'lib/form.lib.php';
include $newIncludePath . 'lib/course.lib.inc.php';
include $newIncludePath . 'lib/claro_main.lib.php';
include $newIncludePath . 'lib/language.lib.php';

/**
 * Unquote GET, POST AND COOKIES if magic quote gpc is enabled in php.ini
 */

claro_unquote_gpc();

if (count($_GET) > 0)      {extract($_GET, EXTR_OVERWRITE);}
if (count($_POST) > 0)     {extract($_POST, EXTR_OVERWRITE);}
if (count($_SERVER) > 0)   {extract($_SERVER, EXTR_OVERWRITE);}


// LIST OF  VIEW IN ORDER TO SHOW
$panelSequence  = array(
DISP_WELCOME,
DISP_LICENSE,
//DISP_FILE_SYSTEM_SETTING,
DISP_DB_CONNECT_SETTING,
DISP_DB_NAMES_SETTING,
DISP_ADMINISTRATOR_SETTING,
DISP_PLATFORM_SETTING,
DISP_ADMINISTRATIVE_SETTING,
DISP_LAST_CHECK_BEFORE_INSTALL,
DISP_RUN_INSTALL_COMPLETE);
//DISP_RUN_INSTALL_NOT_COMPLETE is not a panel of sequence


// VIEW TITLE
$panelTitle[DISP_WELCOME]                   = get_lang('Requirements');
$panelTitle[DISP_LICENSE]                   = get_lang('Licence');
//$panelTitle[DISP_FILE_SYSTEM_SETTING]      = get_lang('FileSystemSetting');
$panelTitle[DISP_DB_CONNECT_SETTING]        = 'MySql Database Settings';
$panelTitle[DISP_DB_NAMES_SETTING]          = get_lang('MysqlNames');
$panelTitle[DISP_ADMINISTRATOR_SETTING]     = 'Administrator Account';
$panelTitle[DISP_PLATFORM_SETTING]          = 'Platform Settings';
$panelTitle[DISP_ADMINISTRATIVE_SETTING]    = 'Additional Informations<small> (optional)</small>';
$panelTitle[DISP_LAST_CHECK_BEFORE_INSTALL] = get_lang('LastCheck');
$panelTitle[DISP_RUN_INSTALL_COMPLETE]      = 'Claroline Installation succeeds';

//$rootSys="'.realpath($pathForm).'";




// CONTROLER
// GET cmd,

if($_REQUEST['cmdLicence'])
{
    $cmd=DISP_LICENSE;
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








##### INITIALISE FORM VARIABLES ##################

###  IF FIRST VISIT ###
if(!$_REQUEST['alreadyVisited'] || $_REQUEST['resetConfig']) // on first step prupose values
{
     include './defaultsetting.inc.php';
}
else ###  IF NOT ###
{
    extract($_REQUEST);
    $campusForm  = $_REQUEST['campusForm'];
}






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


/**
 *
 * Check New Data  (following $_REQUEST['fromPanel'] value)
 * or if $_REQUEST['cmdDoInstall']
 *
 * Each check set the view to display following check Result
 * when check failed, some flag are set to trigger some explict messages
 */



$canRunCmd = TRUE;
if($_REQUEST['fromPanel'] == DISP_ADMINISTRATOR_SETTING || $_REQUEST['cmdDoInstall'])
{
    if (empty($adminSurnameForm)||empty($passForm)||empty($loginForm)||empty($adminNameForm)||empty($adminEmailForm)||!is_well_formed_email_address($adminEmailForm))
    {
        $adminDataMissing = TRUE;
        if (empty($loginForm)) $missing_admin_data[] = 'login';
        if (empty($passForm))  $missing_admin_data[] = 'password';
        if (empty($adminSurnameForm)) $missing_admin_data[] = 'firstname';
        if (empty($adminNameForm)) $missing_admin_data[] = 'lastname';
        if (empty($adminEmailForm)) $missing_admin_data[] = 'email';
        if (!empty($adminEmailForm) && !is_well_formed_email_address($adminEmailForm)) $error_in_admin_data[] = 'email';
        if (is_array ($missing_admin_data))  $msg_missing_admin_data = '<font color="red" >Please, fill in '.implode(', ',$missing_admin_data).'</font><br />';
        if (is_array ($error_in_admin_data)) $msg_missing_admin_data .= '<font color="red" >Please, check '.implode(', ',$error_in_admin_data).'</font><br />';
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

    $institutionUrlForm = trim($institutionUrlForm);
    $contactNameForm    = trim($contactNameForm);
    $adminNameForm      = trim($adminNameForm);
    $contactEmailForm   = trim($contactEmailForm);
    $regexp = "^(http|https|ftp)\://[a-zA-Z0-9\-\.]+\.[a-zA-Z0-9]{1,3}(:[a-zA-Z0-9]*)?/?([a-zA-Z0-9\-\._\?\,\'/\\\+&%\$#\=~])*$";
    if ( (!empty($institutionUrlForm)) && !eregi( $regexp, $institutionUrlForm) )
    {
        // problem with url. try to repair
        // if  it  only the protocol missing add http
        if (eregi('^[a-zA-Z0-9\-\.]+\.[a-zA-Z0-9]{2,3}(:[a-zA-Z0-9]*)?/?([a-zA-Z0-9\-\._\?\,\'/\\\+&%\$#\=~])*$', $institutionUrlForm )
        && (eregi($regexp, 'http://' . $institutionUrlForm )))
        {
            $institutionUrlForm = 'http://' . $institutionUrlForm;
        }
        else
        {
            $administrativeDataMissing = TRUE;
            $check_administrative_data[] = 'Institution Url';
        }
    }

    if (empty($contactEmailForm)||empty($contactNameForm)
        ||!is_well_formed_email_address($contactEmailForm)
    )
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
                $contactEmailForm ='';
            }
        }

    }

    if($administrativeDataMissing)
    {
        $msg_missing_administrative_data = '<font color="red" >Please check '.implode(', ',$check_administrative_data).'</font><br />';
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
                    <br />';
        if ($no=='2005')
        $msg_no_connection .= '
                    Wrong '.get_lang('DBHost').' : <I>'.$dbHostForm.'</I>';
        elseif ($no=='1045')
        $msg_no_connection .= 'Wrong database Login : '
                           .  '(<I>' . $dbUsernameForm . '</I>) '
                           .  'or Password '
                           .  '(<I>'.$dbPassForm.'</I>)'
                           ;
        else
        $msg_no_connection .= 'Server unavailable. '
                           .  'Is your MySQL server started ?';
        $msg_no_connection .= '<br />'
                           .  '<font color="blue">'
                           .  'Fix this problem before going further'
                           .  '</font>'
                           .  '<br />'
                           .  '</P>'
                           ;
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
    $regexpPatternForDbName = '^[a-z0-9][a-z0-9_]*$';
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
        $msgErrorDbMain_dbNameBadStart = !eregi('^[a-z0-9]',$dbNameForm);

        if (!$singleDbForm)
        {
            $msgErrorDbMain_dbName = $msgErrorDbMain_dbNameToolLong ||
                                     $msgErrorDbMain_dbNameInvalid ||
                                     $msgErrorDbMain_dbNameBadStart ;

            $msgErrorDbStat_dbNameInvalid = !eregi($regexpPatternForDbName,$dbStatsForm);
            $msgErrorDbStat_dbNameToolLong = (strlen($dbStatsForm)>64);
            $msgErrorDbStat_dbNameBadStart = !eregi('^[a-z0-9]',$dbStatsForm);
        }

    }
    else
    {
        $db = mysql_connect("$dbHostForm", "$dbUsernameForm", "$dbPassForm");

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
            $databaseAlreadyExist              = TRUE;
            if ($valMain)    $mainDbNameExist  = TRUE;
            if ($valStat)    $statsDbNameExist = TRUE;
            $canRunCmd                         = FALSE;
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
        $missing_platform_data[]= 'the <B>name</b> of your online campus';
    }

    if($platformDataMissing)
    {
        $canRunCmd = FALSE;
        $msg_missing_platform_data = '<font color="red" >Please fill ' . implode(', ',$missing_platform_data) . '</font><br />';
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
    $display = DISP_WELCOME;
    if($_REQUEST['cmdLicence'])
    {
        $display = DISP_LICENSE;
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
        $display = DISP_LAST_CHECK_BEFORE_INSTALL;
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
        $includePath = $newIncludePath;
        $language_list = claro_get_lang_flat_list();
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
        $existingDbs[] = $__dbName[0];
    }
    unset($__dbName);
}

if ($display==DISP_ADMINISTRATIVE_SETTING)
{
    if ($contactNameForm == '*not set*')
    {
        $contactNameForm = $adminSurnameForm . ' ' . $adminNameForm;
    }

    if ($contactEmailForm == '*not set*')
    {
        $contactEmailForm = $adminEmailForm;
    }

    if ($contactPhoneForm == '*not set*')
    {
        $contactPhoneForm = $adminPhoneForm;
    }
}

// BEGIN OUTPUT

// COMMON OUTPUT Including top of form  and list of hidden values
?>
<html>
<head>

<title>
-- Claroline installation -- version <?php echo $new_version ?>
</title>

<link rel="stylesheet" href="../css/default.css" type="text/css" >
<style media="print" type="text/css"  >
    .notethis { font-weight : bold;  }
</style>
<style  type="text/css"  >
    .notethis { font-weight : bold; }
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
                    Claroline <?php echo $new_version ?> - Installation
                </font>
            </th>
        </TR>
    <tr>
        <td>
<?php
echo '<input type="hidden" name="alreadyVisited" value="1">'                                                 ."\n"
.    '<input type="hidden" name="urlAppendPath"                value="'.$urlAppendPath.'">'                  ."\n"
.    '<input type="hidden" name="urlEndForm"                   value="'.$urlEndForm.'">'                     ."\n"
.    '<input type="hidden" name="courseRepositoryForm"         value="'.$courseRepositoryForm.'">'           ."\n"
.    '<input type="hidden" name="pathForm" value="'.str_replace("\\","/",realpath($pathForm)."/").'" >'      ."\n"
.    '<input type="hidden" name="imgRepositoryAppendForm" value="'.str_replace("\\","/",$imgRepositoryAppendForm).'" >'      ."\n"
.    '<input type="hidden" name="userImageRepositoryAppendForm" value="'.str_replace("\\","/",$userImageRepositoryAppendForm).'" >'      ."\n"
.    '<input type="hidden" name="dbHostForm"                   value="'.$dbHostForm.'">'                     ."\n"
.    '<input type="hidden" name="dbUsernameForm"               value="'.$dbUsernameForm.'">'                 ."\n\n"
.    '<input type="hidden" name="singleDbForm"                 value="'.$singleDbForm.'">'                   ."\n\n"
.    '<input type="hidden" name="dbPrefixForm"                 value="'.$dbPrefixForm.'">'                   ."\n"
.    '<input type="hidden" name="dbNameForm"                   value="'.$dbNameForm.'">'                     ."\n"
.    '<input type="hidden" name="dbStatsForm"                  value="'.$dbStatsForm.'">'                    ."\n"
.    '<input type="hidden" name="mainTblPrefixForm"            value="'.$mainTblPrefixForm.'">'              ."\n"
.    '<input type="hidden" name="statsTblPrefixForm"           value="'.$statsTblPrefixForm.'">'              ."\n"
.    '<input type="hidden" name="dbMyAdmin"                    value="'.$dbMyAdmin.'">'                      ."\n"
.    '<input type="hidden" name="dbPassForm"                   value="'.$dbPassForm.'">'                     ."\n\n"
.    '<input type="hidden" name="urlForm"                      value="'.$urlForm.'">'                        ."\n"
.    '<input type="hidden" name="adminEmailForm"               value="'.htmlspecialchars($adminEmailForm).'">'   ."\n"
.    '<input type="hidden" name="adminPhoneForm"               value="'.htmlspecialchars($adminPhoneForm).'">'   ."\n"
.    '<input type="hidden" name="adminNameForm"                value="'.htmlspecialchars($adminNameForm).'">'    ."\n"
.    '<input type="hidden" name="adminSurnameForm"             value="'.htmlspecialchars($adminSurnameForm).'">' ."\n\n"
.    '<input type="hidden" name="loginForm"                    value="'.htmlspecialchars($loginForm).'">'        ."\n"
.    '<input type="hidden" name="passForm"                     value="'.htmlspecialchars($passForm).'">'         ."\n\n"
.    '<input type="hidden" name="languageForm"                 value="'.$languageForm.'">'                   ."\n\n"
.    '<input type="hidden" name="campusForm"                   value="'.htmlspecialchars($campusForm).'">'       ."\n"
.    '<input type="hidden" name="adminPhoneForm"               value="'.htmlspecialchars($adminPhoneForm).'">'   ."\n"
.    '<input type="hidden" name="contactNameForm"              value="'.htmlspecialchars($contactNameForm).'">'  ."\n"
.    '<input type="hidden" name="contactEmailForm"             value="'.htmlspecialchars($contactEmailForm).'">' ."\n"
.    '<input type="hidden" name="contactPhoneForm"             value="'.htmlspecialchars($contactPhoneForm).'">' ."\n"
.    '<input type="hidden" name="institutionForm"              value="'.htmlspecialchars($institutionForm).'">'  ."\n"
.    '<input type="hidden" name="institutionUrlForm"           value="'.$institutionUrlForm.'">'             ."\n\n"
.    '<!-- BOOLEAN -->'                                                                                      ."\n"
.    '<input type="hidden" name="enableTrackingForm"           value="'.$enableTrackingForm.'">'             ."\n"
.    '<input type="hidden" name="allowSelfReg"                 value="'.$allowSelfReg.'">'                   ."\n"
.    '<input type="hidden" name="userPasswordCrypted"          value="'.$userPasswordCrypted.'">'            ."\n"
.    '<input type="hidden" name="encryptPassForm"              value="'.$encryptPassForm.'">'                ."\n"
.    '<input type="hidden" name="confirmUseExistingMainDb"     value="'.$confirmUseExistingMainDb.'">'       ."\n"
.    '<input type="hidden" name="confirmUseExistingStatsDb"    value="'.$confirmUseExistingStatsDb.'">';


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
    echo '<input type="hidden" name="fromPanel" value="'.$display.'">'
    .    '<h2>'
    .    sprintf(get_lang('StepNOfN'),(array_search(DISP_WELCOME, $panelSequence)+1),count($panelSequence)).' : '.$panelTitle[DISP_WELCOME]
    .    '</h2>'
    ;
    // check if an claroline configuration file doesn't already exists.
    if ( file_exists('../inc/conf/claro_main.conf.inc.php')
    ||   file_exists('../inc/conf/claro_main.conf.php')
    ||   file_exists('../inc/conf/config.inc.php')
    ||   file_exists('../include/config.inc.php')
    ||   file_exists('../include/config.php'))
    {
        echo '<div style="background-color:#FFFFFF;margin:20px;padding:5px">'
        .    '<b>'
        .    '<font color="red">Warning !</font> '
        .    'The installer has detected an existing claroline platform on your system. '
        .    '<br />'
        .    '</b>'
        .    '<ul>'
        ;
        if ($is_upgrade_available)
        {
            echo '<li>'
            .    'For a Claroline upgrade click '
            .    '<a href="../admin/upgrade/upgrade.php">here</a>.'
            .    '</li>'
            ;
        }
        else
        {
            echo '<li>'
            .    'For a Claroline upgrade, please wait the release of a stable version. '
            .    '</li>'
            ;
        }
        echo '<li>'
        .    'For a Claroline complete reinstallation click on the "Next" button below.<br />'
        .    '<font color="red">'
        .    'Be aware that a complete reinstallation will crush the data stored in your previous installed Claroline.'
        .    '</font>'
        .    '</li>'
        .    '</ul>'
        .    '</div>'
        ;
    }

    if(!$stable)
    {
        echo '<B>'
        .    'Notice. This version is not considered as stable '
        .    'and is not aimed for production.'
        .    '</B><br />'
        .    'If  something goes wrong, '
        .    'come talk on our support forum at '
        .    '<a href="http://www.claroline.net/forum/viewforum.php?f=62" '
        .    'target="_clarodev">http://www.claroline.net'
        .    '</a>.'
        ;
    }

    if($SERVER_SOFTWARE=="") $SERVER_SOFTWARE = $_SERVER["SERVER_SOFTWARE"];
    $WEBSERVER_SOFTWARE = explode(" ",$SERVER_SOFTWARE,2);
    echo '<p>Please, read thoroughly the '
    .    '<a href="../../INSTALL.txt">INSTALL.txt</a> document '
    .    'before proceeding to installation.'
    .    '</p>'
    .    '<h4>Checking requirements</h4>'
    .    '<ul>'
    .    '<li>'
    .    'Checking PHP extentions.'
    .    '<UL>'
    ;

    warnIfExtNotLoaded('standard');
    warnIfExtNotLoaded('session');
    warnIfExtNotLoaded('mysql');
    warnIfExtNotLoaded('zlib');
    warnIfExtNotLoaded('pcre');
    warnIfExtNotLoaded('tokenizer');
    //    warnIfExtNotLoaded('exif'); // exif  would be needed later for pic view properties.
    //    warnIfExtNotLoaded('nameOfExtention'); // list here http://www.php.net/manual/fr/resources.php

    echo '
        </UL>
    </LI>
    <LI>
        Checking PHP settings.
        <UL>
            ';
    if (!version_compare(phpversion(), $requiredPhpVersion,'>='))
    {
        echo '<li>'
        .    '<p class="setup_error">' . "\n"
        .    '<font color="red">Warning !</font>' . "\n"
        .    'php version is <strong>' . phpversion() . '</strong>.' . "\n"
        .    '<br />' . "\n"
        .    'Upgrade your php to <strong>' . $requiredPhpVersion . '</strong><br />' . "\n"
        .    '</p>' . "\n"
        .    '</li>' . "\n"
        ;


    }

    if (ini_get('safe_mode') )
    {
        echo '<li>'
        .    '<p class="setup_error">' . "\n"
        .    '<font color="red">Warning !</font>' . "\n"
        .    'safe_mode is set to <strong>on</strong>.' . "\n"
        .    '<br />' . "\n"
        .    'Change the following parameter in your <i>php.ini</i> file to this value :<br />' . "\n"
        .    '<font color="blue">' . "\n"
        .    '<code>safe_mode = off </code>' . "\n"
        .    '</font>' . "\n"
        .    '</p>' . "\n"
        .    '</li>' . "\n"
        ;
    }


    echo '</UL>'
    .    '</li>'
    .    '<li>'
    .    'Checking file access to web directory.'
    .    '<ul>'
    .    (is_writable('../..')
    ? ''
    : '</li>'
    . '<font color="red">Warning !</font> Claroline is not able to write on : <br />'
    . '<nobr><code>' . realpath('../..') . '</code><nobr>'
    . '<br />'
    . 'Change this file permission the server file system.'
    . '</li>')
    .    ' '
    .    (is_readable('../..')
    ? ''
    : '<li>'
    . '<font color="red">Warning !</font> '
    . 'Claroline is not able to read on : <br />'
    . '<nobr><code>' . realpath('../..') . '</code><nobr>'
    . '<br />'
    . 'Change this file permission the server file system.'
    . '</li>')
    .    '</ul>'
    .    '</li>'
    .    '</ul>'
    .    '<p>'
    .    'If the checks above has passed without any problem, '
    .    'click on the <i>Next</i> button to continue.'
    .    '<p align="right">'
    .    '<input type="submit" name="cmdLicence" value="Next &gt;">'
    .    '</p>'
    ;

}

###################################################################
############### STEP 2 LICENSE  ###################################
###################################################################
elseif($display==DISP_LICENSE)
{
    echo '<input type="hidden" name="fromPanel" value="'.$display.'">'  . "\n"
    .    '<h2>'  . "\n"
    .    sprintf(get_lang('StepNOfN'),(array_search(DISP_LICENSE, $panelSequence)+1),count($panelSequence)).' : '.$panelTitle[DISP_LICENSE]
    .    '</h2>'  . "\n"
    .    '<P>'  . "\n"
    .    'Claroline is free software, distributed under GNU General Public licence (GPL).'  . "\n"
    .    'Please read the licence and click &quot;I accept&quot;.'  . "\n"
    .    '<a href="../../LICENCE.txt">' . get_lang('PrintVers') . '</a>'  . "\n"
    .    '</P>'  . "\n"
    .    '<textarea wrap="virtual" cols="65" rows="15">'
    ;

    readfile ('../license/gpl.txt');
    echo '</textarea>'
    .    '</td>'
    .    '</tr>'
    .    '<tr>'
    .    '<td>'
    .    '<table width="100%">'
    .    '<tr>'
    .    '<td>'
    .    '</td>'
    .    '<td align="right">'
    .    '<input type="submit" name="cmdWelcomePanel" value="&lt; Back">'
    .    '<!-- input type="submit" name="cmdFILE_SYSTEM_SETTING" value="I accept &gt;" -->'
    .    '<input type="submit" name="cmdDB_CONNECT_SETTING" value="I accept &gt;">'
    .    '</td>'
    .    '</tr>'
    .    '</table>'
    ;

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
'.sprintf(get_lang('StepNOfN'),(array_search(DISP_FILE_SYSTEM_SETTING, $panelSequence)+1),count($panelSequence)).' : '.$panelTitle[DISP_FILE_SYSTEM_SETTING].'
</h2>
</td>
</tr>
<tr>
<td>
<!--            <h4>Absolute path</h4>
<label for="urlForm">Campus Path (absolute path to your campus)</label><br />
<input type="text" size="85" id="urlForm" name="urlForm" value="'.$urlForm.'"><br />
<h4>Relative path</h4>
<label for="urlAppend">Campus Path (relative path  from document root to your campus)</label><br />
<input type="text" size="85" id="urlAppend" name="urlAppendPath" value="'.$urlAppendPath.'"><br />
<br />
-->
<label for="courseRepositoryForm"> Course Repository path (relative to index of your campus) </label><br />
<input type="text"  size="85" id="courseRepositoryForm" name="courseRepositoryForm" value="'.$courseRepositoryForm.'">
<br />
<br />
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



    echo '<input type="hidden" name="fromPanel" value="'.$display.'">'
    .    '<h2>'
    .    sprintf(get_lang('StepNOfN'),(array_search(DISP_DB_CONNECT_SETTING, $panelSequence)+1),count($panelSequence)).' : '.$panelTitle[DISP_DB_CONNECT_SETTING]
    .    '</h2>'
    .    '</td>'
    .    '</tr>'
    .    '<tr>'
    .    '<td>'
    .    '<h4>'.get_lang('DBConnectionParameters').'</h4>'
    .    '<p>'
    .    'Enter here the parameters given by your database server administrator.'
    .    '</p>'
    .    $msg_no_connection
    .    '<table width="100%">'
    .    '<tr>'
    .    '<td>'
    .    '<label for="dbHostForm">Database host</label>'
    .    '</td>'
    .    '<td>'
    .    '<input type="text" size="25" id="dbHostForm" name="dbHostForm" value="'.htmlspecialchars($dbHostForm).'">'
    .    '</td>'
    .    '<td>'
    .    get_lang('EG') . ' localhost'
    .    '</td>'
    .    '</tr>'
    .    '<tr>'
    .    '<td>'
    .    '<label for="dbUsernameForm">Database username</label>'
    .    '</td>'
    .    '<td>'
    .    '<input type="text"  size="25" id="dbUsernameForm" name="dbUsernameForm" value="'.htmlspecialchars($dbUsernameForm).'">'
    .    '</td>'
    .    '<td>'
    .    get_lang('EG').' root'
    .    '</td>'
    .    '</tr>'
    .    '<tr>'
    .    '<td>'
    .    '<label for="dbPassForm">Database password</label>'
    .    '</td>'
    .    '<td>'
    .    '<input type="text"  size="25" id="dbPassForm" name="dbPassForm" value="'.htmlspecialchars($dbPassForm).'">'
    .    '</td>'
    .    '<td>'
    .    get_lang('EG').' '.generate_passwd(8)
    .    '</td>'
    .    '</tr>'
    .    '</table>'
    .    '<h4>'.get_lang('DBUse').'</h4>'
    .    '<table width="100%">'
    .    '<tr>'
    .    '<td>'
    .    'Tracking</label>'
    .    '</td>'
    .    '<td>'
    .    '<input type="radio" id="enableTrackingForm_enabled" name="enableTrackingForm" value="1" '.($enableTrackingForm?'checked':'').'>'
    .    '<label for="enableTrackingForm_enabled">'
    .    'enabled'
    .    '</label>'
    .    '</td>'
    .    '<td>'
    .    '<input type="radio" id="enableTrackingForm_disabled" name="enableTrackingForm" value="0" '.($enableTrackingForm?'':'checked').'>'
    .    '<label for="enableTrackingForm_disabled">'
    .    'disabled'
    .    '</label>'
    .    '</td>'
    .    '</tr>'
    .    '<tr>'
    .    '<td>'
    .    'Database mode'
    .    '</td>'
    .    '<td>'
    .    '<input type="radio" id="singleDbForm_single" name="singleDbForm" value="1" '.($singleDbForm?'checked':'').' >'
    .    '<label for="singleDbForm_single">'
    .    'single'
    .    '</label>'
    .    '</td>'
    .    '<td>'
    .    '<input type="radio" id="singleDbForm_multi" name="singleDbForm" value="0" '.($singleDbForm?'':'checked').' >'
    .    '<label for="singleDbForm_multi">'
    .    'multi '
    .    '<small>'
    .    '(a database is created at each course creation)'
    .    '</small>'
    .    '</label>'
    .    '</td>'
    .    '</tr>'
    .    '<tr>'
    .    '<td>'
    .    '</td>'
    .    '<td >'
    .    '&nbsp;'
    .    '</td>'
    .    '<td align="right">'
    .    '</td>'
    .    '</tr>'
    .    '</table>'
    .    '<table width="100%">'  . "\n"
    .    '<tr>'  . "\n"
    .    '<td>'  . "\n"
    .    '</td>'  . "\n"
    .    '<td align="right" rowspan="2" valign="bottom">'  . "\n"
    .    '<input type="submit" name="cmdDbNameSetting" value="Next &gt;">'
    .    '</td>' . "\n"
    .    '</tr>' . "\n"
    .    '<tr>' . "\n"
    .    '<td align="left">'  . "\n"
    .    '<input type="submit" name="cmdLicence" value="&lt; Back">'
    .    '<!-- input type="submit" name="cmdFILE_SYSTEM_SETTING" value="&lt; Back" -->'
    .    '</td>' . "\n"
    .    '</tr>' . "\n"
    .    '</table>'
    ;
}     // cmdDB_CONNECT_SETTING


##########################################################################
###### STEP 4 MYSQL DATABASE SETTINGS ####################################
##########################################################################
elseif($display == DISP_DB_NAMES_SETTING )
{
    echo '<input type="hidden" name="fromPanel" value="'.$display.'">'  . "\n"
    .    '<h2>'  . "\n"
    .    sprintf(get_lang('StepNOfN'),(array_search(DISP_DB_NAMES_SETTING, $panelSequence)+1),count($panelSequence)).' : '.$panelTitle[DISP_DB_NAMES_SETTING]  . "\n"
    .    '</h2>'  . "\n"
    .    ($singleDbForm?'':get_lang('DBSettingNamesIntro'))  . "\n"
    .    '</td>'  . "\n"
    .    '</tr>'  . "\n"
    .    '<tr>'  . "\n"
    .    '<td>'  . "\n"
    .    $msg_no_connection.''  . "\n"
    .    '<table width="100%">'
    ;
    if (isset($databaseNameValid) && !$databaseNameValid)
    {

        echo '<tr>'  . "\n"
        .    '<td colspan="2">'  . "\n"
        .    '<P class="setup_error">'  . "\n"
        .    '<font color="red">Warning !</font> '  . "\n"
        .    ' : Database <em>'.$dbNameForm.'</em> is not valid. '  . "\n"
        .    '<ul>'
        .    ($msgErrorDbMain_dbName?'<LI>Main db<UL>':'')
        .    ($msgErrorDbMain_dbNameToolLong?'<LI>dbName Too Long':'')
        .    ($msgErrorDbMain_dbNameInvalid?'<LI>dbName Invalid Check the character (only letter ciffer and _)':'')
        .    ($msgErrorDbMain_dbNameBadStart?'<LI>dbName Must begin by a letter':'')
        .    ($msgErrorDbStat_dbName?'</UL><LI>Stat db<UL>':'')
        .    ($msgErrorDbStat_dbNameToolLong?'<LI>dbName Too Long':'')
        .    ($msgErrorDbStat_dbNameInvalid?'<LI>dbName Invalid. Check the character (only letter ciffer and _)':'')
        .    ($msgErrorDbStat_dbNameBadStart?'<LI>dbName Must begin by a letter':'')
        .    '</UL>'  . "\n"
        .    '</UL>'  . "\n"
        .    '</P>'  . "\n"
        .    '</td>'  . "\n"
        .    '</tr>'
        ;

    }
    if ($mainDbNameExist)
    {
        echo '<tr>'  . "\n"
        .    '<td colspan="2">'  . "\n"
        .    '<p class="setup_error">'  . "\n"
        .    '<font color="red">Warning !</font>'  . "\n"
        .    'Database <em>'.$dbNameForm.'</em> already exists'  . "\n"
        .    '<br />'  . "\n"
        .    'Claroline may overwrite data previously stored'  . "\n"
        .    'in tables of this database.'  . "\n"
        .    '<br />'  . "\n"
        .    '<input type="checkbox" name="confirmUseExistingMainDb"  id="confirmUseExistingMainDb" value="true" '.($confirmUseExistingMainDb?'checked':'').'>'  . "\n"
        .    '<label for="confirmUseExistingMainDb" >'  . "\n"
        .    '<B>I know, I want to use this database.</B>'  . "\n"
        .    '</label>'  . "\n"
        .    '</p>'  . "\n"
        .    '</td>'  . "\n"
        .    '</tr>'
        ;
    }
    echo '<tr>'  . "\n"
    .    '<td>'  . "\n"
    .    '<label for="dbNameForm">'  . "\n"
    .    ''.($singleDbForm?get_lang('DbName'):get_lang('MainDB')).''  . "\n"
    .    '</label>'  . "\n"
    .    '</td>'  . "\n"
    .    '<td>'  . "\n"
    .    '<input type="text"  size="25" id="dbNameForm" name="dbNameForm" value="'.htmlspecialchars($dbNameForm).'">'  . "\n"
    .    '</td>'  . "\n"
    .    '<td>'  . "\n"
    .    'e.g. \''.$dbNameForm.'\''  . "\n"        .    '</td>'  . "\n"
    .    '</td>'  . "\n"
    .    '</tr>'  . "\n"
    .    '<tr>'  . "\n"
    .    '<td>'  . "\n"
    .    '<label for="mainTblPrefixForm">'  . "\n"
    .    'Prefix for names of main tables'  . "\n"
    .    '</label>'  . "\n"
    .    '</td>'  . "\n"
    .    '<td>'  . "\n"
    .    '<input type="text"  size="5" id="mainTblPrefixForm" name="mainTblPrefixForm" value="'.htmlspecialchars($mainTblPrefixForm).'">'  . "\n"
    .    '</td>'  . "\n"
    .    '<td>'  . "\n"
    .    'e.g. \''.$mainTblPrefixForm.'\''  . "\n"        .    '</td>'  . "\n"
    .    '</td>'  . "\n"
    .    '</tr>'  . "\n"
    .    '<tr>'  . "\n"
    .    '<td colspan="3">'  . "\n"
    .    '</td>'  . "\n"
    .    '</tr>'  . "\n"
    ;
    if (!$singleDbForm)
    {
        if ($statsDbNameExist && $dbStatsForm!=$dbNameForm)
        {
            echo '<tr>'  . "\n"
            .    '<td colspan="2">'  . "\n"
            .    '<P class="setup_error">'  . "\n"
            .    '<font color="red">Warning !</font>'  . "\n"
            .    'Database <em>'.$dbStatsForm.'</em> already exists'  . "\n"
            .    '<br />'  . "\n"
            .    'Claroline may overwrite data previously stored'  . "\n"
            .    'in tables of this database.'  . "\n"
            .    '<br />'  . "\n"
            .    '<input type="checkbox" name="confirmUseExistingStatsDb"  id="confirmUseExistingStatsDb" value="true" '.($confirmUseExistingStatsDb?'checked':'').'>'  . "\n"
            .    '<label for="confirmUseExistingStatsDb" >'  . "\n"
            .    '<B>I know, I want to use this database.</B>'  . "\n"
            .    '</label>'  . "\n"
            .    '</P>'  . "\n"
            .    '</td>'  . "\n"
            .    '</tr>'
            ;
        }
        echo '<tr>'  . "\n"
        .    '<td>'  . "\n"
        .    '<label for="dbStatsForm">'.get_lang('StatDB').'</label>'  . "\n"
        .    '</td>'  . "\n"
        .    '<td>'  . "\n"
        .    '<input type="text"  size="25" id="dbStatsForm" name="dbStatsForm" value="'.htmlspecialchars($dbStatsForm).'">'  . "\n"
        .    '</td>'  . "\n"
        .    '<td>'  . "\n"
        .    'e.g. \''.$dbStatsForm.'\''  . "\n"        .    '</td>'  . "\n"
        .    '</td>'  . "\n"
        .    '</tr>'  . "\n"
        .    '<tr>'  . "\n"
        .    '<td>'  . "\n"
        .    '<label for="statsTblPrefixForm">'  . "\n"
        .    'Prefix for names of tracking tables'  . "\n"
        .    '</label>'  . "\n"
        .    '</td>'  . "\n"
        .    '<td>'  . "\n"
        .    '<input type="text"  size="5" id="statsTblPrefixForm" name="statsTblPrefixForm" value="'.htmlspecialchars($statsTblPrefixForm).'">'  . "\n"
        .    '</td>'  . "\n"
        .    '<td>'  . "\n"
        .    'e.g. \''.$statsTblPrefixForm.'\''  . "\n"
        .    '</td>'  . "\n"
        .    '</tr>'  . "\n"
        .    '<tr>'  . "\n"
        .    '<td colspan="3">'  . "\n"
        .    '<blockquote><small>'  . "\n"
        .    'Normally, Claroline creates the tracking tables into the main Claroline database. <br />'
        .    'But, if you want, you have the possibility to store tracking data into a separate database <br />'
        .    'or to specify a special prefix for tracking tables.'  . "\n"
        .    '</small></blockquote>'  . "\n"
        .    '</td>'  . "\n"
        .    '</tr>'  . "\n"
        ;
    }
    echo '<tr>'  . "\n"
    .    '<td>'  . "\n"
    .    '<label for="dbPrefixForm">'  . "\n"
    .    ($singleDbForm?'Prefix for names of course tables':get_lang('DbPrefixForm')).''  . "\n"
    .    '</label>'  . "\n"
    .    '</td>'  . "\n"
    .    '<td>'  . "\n"
    .    '<input type="text"  size="25" id="dbPrefixForm" name="dbPrefixForm" value="'.htmlspecialchars($dbPrefixForm).'">'  . "\n"
    .    '</td>'  . "\n"
    .    '<td>'  . "\n"
    .    'e.g. \''.$dbPrefixForm.'\''  . "\n"
    .    '</td>'  . "\n"
    .    '</tr>'
    ;
    if (!$singleDbForm)
    {
        echo '<tr>'  . "\n"
        .    '<td colspan="3">'  . "\n"
        .    '<blockquote>'  . "\n"
        .    '<small>'  . "\n"
        .    '<b>'  . "\n"
        .    'Afterwards, Claroline will create a new database for each newly '  . "\n"
        .    'created course. '  . "\n"
        .    '</b>'  . "\n"
        .    '<br />'  . "\n"
        .    'You can specify a prefix for these database names.'  . "\n"
        .    '</small>'  . "\n"
        .    '</blockquote>'  . "\n"
        .    '</td>'  . "\n"
        .    '</tr>'
        ;

    }
    echo '<table width="100%">'  . "\n"
    .    '<tr>'  . "\n"
    .    '<td>'  . "\n"
    .    '</td>'  . "\n"
    .    '<td align="right" rowspan="2" valign="bottom">'  . "\n"
    .    '<input type="submit" name="cmdAdministratorSetting" value="Next &gt;">'  . "\n"
    .    '</td>' . "\n"
    .    '</tr>' . "\n"
    .    '<tr>' . "\n"
    .    '<td align="left">'  . "\n"
    .    '<input type="submit" name="cmdDB_CONNECT_SETTING" value="&lt; Back">'  . "\n"
    .    '</td>' . "\n"
    .    '</tr>' . "\n"
    .    '</table>'
    ;
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
###### STEP ADMIN SETTINGS ##############################################
##########################################################################
elseif($display==DISP_ADMINISTRATOR_SETTING)

{
    echo '<input type="hidden" name="fromPanel" value="'.$display.'">'  . "\n"
    .    '<h2>'  . "\n"
    .    sprintf(get_lang('StepNOfN'),(array_search(DISP_ADMINISTRATOR_SETTING, $panelSequence)+1),count($panelSequence)).' : '.$panelTitle[DISP_ADMINISTRATOR_SETTING] . "\n"
    .    '</h2>' . "\n"
    .    '</td>' . "\n"
    .    '</tr>' . "\n"
    .    '<tr>'  . "\n"
    .    '<td>'  . "\n"
    .    $msg_missing_admin_data. "\n"
    .    $msg_admin_exist.''  . "\n"
    .    '<table width="100%">'  . "\n"
    .    '<tr>'  . "\n"
    .    '<tr>'  . "\n"
    .    '<td>'  . "\n"
    .    '<b><label for="loginForm">'.get_lang('AdminLogin').'</label></b>'  . "\n"
    .    '</td>' . "\n"
    .    '<td>'  . "\n"
    .    '<input type="text" size="40" id="loginForm" name="loginForm" value="'.htmlspecialchars($loginForm).'">'  . "\n"
    .    '</td>' . "\n"
    .    '<td>'  . "\n"
    .    'e.g. jdoe'  . "\n"
    .    '</td>' . "\n"
    .    '</tr>' . "\n"
    .    '<tr>'  . "\n"
    .    '<td>'  . "\n"
    .    '<b><label for="passForm">'.get_lang('Password').'</label></b>'  . "\n"
    .    '</td>' . "\n"
    .    '<td>'  . "\n"
    .    '<input type="text" size="40" id="passForm" name="passForm" value="'.htmlspecialchars($passForm).'">'  . "\n"
    .    '</td>' . "\n"
    .    '<td>'  . "\n"
    .    'e.g. ' . generate_passwd(8) . "\n"
    .    '</td>' . "\n"
    .    '</tr>' . "\n"
    .    '<td>'  . "\n"
    .    '<label for="adminEmailForm">'.get_lang('Email').'</label>'  . "\n"
    .    '</td>' . "\n"
    .    '<td>'  . "\n"
    .    '<input type="text" size="40" id="adminEmailForm" name="adminEmailForm" value="'.htmlspecialchars($adminEmailForm).'">'  . "\n"
    .    '</td>' . "\n"
    .    '<td>'  . "\n"
    .    'e.g. jdoe@mydomain.net'  . "\n"
    .    '</td>' . "\n"
    .    '</tr>' . "\n"
    .    '<td>'  . "\n"
    .    '<label for="adminPhoneForm">Phone</label>'  . "\n"
    .    '</td>' . "\n"
    .    '<td>'  . "\n"
    .    '<input type="text" size="40" id="adminPhoneForm" name="adminPhoneForm" value="'.htmlspecialchars($adminPhoneForm).'">'  . "\n"
    .    '</td>' . "\n"
    .    '<td>'  . "\n"
    .    'e.g. 877-426-6006'  . "\n"
    .    '</td>' . "\n"
    .    '</tr>' . "\n"
    .    '<tr>'  . "\n"
    .    '<td>'  . "\n"
    .    '<label for="adminNameForm">'.get_lang('AdminName').'</label>'  . "\n"
    .    '</td>' . "\n"
    .    '<td>'  . "\n"
    .    '<input type="text" size="40" id="adminNameForm" name="adminNameForm" value="'.htmlspecialchars($adminNameForm).'">'  . "\n"
    .    '</td>' . "\n"
    .    '<td>'  . "\n"
    .    'e.g. Doe'  . "\n"
    .    '</td>' . "\n"
    .    '</tr>' . "\n"
    .    '<tr>'  . "\n"
    .    '<td>'  . "\n"
    .    '<label for="adminSurnameForm">'.get_lang('AdminSurname').'</label>'  . "\n"
    .    '</td>' . "\n"
    .    '<td>'  . "\n"
    .    '<input type="text" size="40" id="adminSurnameForm" name="adminSurnameForm" value="'.htmlspecialchars($adminSurnameForm).'">'  . "\n"
    .    '</td>' . "\n"
    .    '<td>'  . "\n"
    .    'e.g. John'  . "\n"
    .    '</td>' . "\n"
    .    '</tr>' . "\n"
    .    '</table>'  . "\n"
    .    '<table width="100%">'  . "\n"
    .    '<tr>'  . "\n"
    .    '<td>'  . "\n"
    .    '</td>'  . "\n"
    .    '<td align="right" rowspan="2" valign="bottom">'  . "\n"
    .    '<input type="submit" name="cmdPlatformSetting" value="Next &gt;">'  . "\n"
    .    '</td>' . "\n"
    .    '</tr>' . "\n"
    .    '<tr>' . "\n"
    .    '<td align="left">'  . "\n"
    .    '<input type="submit" name="cmdDbNameSetting" value="&lt; Back">'  . "\n"
    .    '</td>' . "\n"
    .    '</tr>' . "\n"
    .    '</table>'
    ;
}

###################################################################
###### STEP CONFIG SETTINGS #######################################
###################################################################
elseif($display==DISP_PLATFORM_SETTING)

{
    echo '<input type="hidden" name="fromPanel" value="'.$display.'">' . "\n"
    .    '<h2>' . "\n"
    .    sprintf(get_lang('StepNOfN'),(array_search(DISP_PLATFORM_SETTING, $panelSequence)+1),count($panelSequence)).' : '.$panelTitle[DISP_PLATFORM_SETTING]
    .    '</h2>' . "\n"
    .    '</td>' . "\n"
    .    '</tr>' . "\n"
    .    '<tr>' . "\n"
    .    '<td>' . "\n"
    .    '<h4>Campus</h4>' . "\n"
    .    ''.$msg_missing_platform_data.'' . "\n"
    .    '<table >' . "\n"
    .    '<tr>' . "\n"
    .    '<td>' . "\n"
    .    '<label for="campusForm">Name</label>' . "\n"
    .    '</td>' . "\n"
    .    '<td colspan="2">' . "\n"
    .    '<input type="text" size="40" id="campusForm" name="campusForm" value="'.htmlspecialchars($campusForm).'">' . "\n"
    .    '</td>' . "\n"
    .    '</tr>' . "\n"
    .    '<tr>' . "\n"
    .    '<td>' . "\n"
    .    '<label for="urlForm">Complete URL</label>' . "\n"
    .    '</td>' . "\n"
    .    '<td colspan="2">' . "\n"
    .    '<input type="text" size="60" id="urlForm" name="urlForm" value="'.htmlspecialchars($urlForm).'">' . "\n"
    .    '</td>' . "\n"
    .    '</tr>' . "\n"
    .    '<tr>' . "\n"
    .    '<td colspan="3">' . "\n"
    .    '<label for="courseRepositoryForm">Courses repository path (relative to the URL above) </label><br />' . "\n"
    .    '</td>' . "\n"
    .    '</tr>' . "\n"
    .    '<tr>' . "\n"
    .    '<td>' . "\n"
    .    '</td>' . "\n"
    .    '<td colspan="2">' . "\n"
    .    '<input type="text"  size="60" id="courseRepositoryForm" name="courseRepositoryForm" value="'.htmlspecialchars($courseRepositoryForm).'">' . "\n"
    .    '</td>' . "\n"
    .    '</tr>' . "\n"
    .    '<tr>' . "\n"
    .    '<td>' . "\n"
    .    '<label for="languageForm">Main language</label>' . "\n"
    .    '</td>' . "\n"
    .    '<td colspan="2">'
    .    claro_html_form_select( 'languageForm'
                               , $language_list
                               , $languageForm
                               , array('id'=>'languageForm'))
    .    '</font>' . "\n"
    .    '</td>' . "\n"
    .    '</tr>' . "\n"
    .    '<tr>' . "\n"
    .    '<td colspan=3><br />' . "\n"
    .    '<h4>User </h4>' . "\n"
    .    '</td>' . "\n"
    .    '</tr>' . "\n"
    .    '<tr>' . "\n"
    .    '<td>' . "\n"
    .    'Self-registration' . "\n"
    .    '</td>' . "\n"
    .    '<td>' . "\n"
    .    '<input type="radio" id="allowSelfReg_1" name="allowSelfReg" value="1" '.($allowSelfReg?'checked':'').'>' . "\n"
    .    '<label for="allowSelfReg_1">enabled</label>' . "\n"
    .    '</td>' . "\n"
    .    '<td>' . "\n"
    .    '<input type="radio" id="allowSelfReg_0" name="allowSelfReg" value="0" '.($allowSelfReg?'':'checked').'>' . "\n"
    .    '<label for="allowSelfReg_0">disabled</label>' . "\n"
    .    '</td>' . "\n"
    .    '</tr>' . "\n"
    .    '<tr>' . "\n"
    .    '<td>' . "\n"
    .    'Password storage' . "\n"
    .    '</td>' . "\n"
    .    '<td>' . "\n"
    .    '<input type="radio" name="encryptPassForm" id="encryptPassForm_0" value="0"  '.($encryptPassForm?'':'checked').'>' . "\n"
    .    '<label for="encryptPassForm_0">clear text</label>' . "\n"
    .    '</td>' . "\n"
    .    '<td>' . "\n"
    .    '<input type="radio" name="encryptPassForm" id="encryptPassForm_1" value="1" '.($encryptPassForm?'checked':'').'>' . "\n"
    .    '<label for="encryptPassForm_1">crypted</label>' . "\n"
    .    '</td>' . "\n"
    .    '</tr>' . "\n"
    .    '</table>' . "\n"
    .    '<table width="100%">' . "\n"
    .    '<tr>' . "\n"
    .    '<td>' . "\n"
    .    '</td>' . "\n"
    .    '<td align="right" rowspan="2" valign="bottom">' . "\n"
    .    '<input type="submit" name="cmdAdministrativeSetting" value="Next &gt;">' . "\n"
    .    '</td>' . "\n"
    .    '</tr>' . "\n"
    .    '<tr>' . "\n"
    .    '<td align="left">' . "\n"
    .    '<input type="submit" name="cmdAdministratorSetting" value="&lt; Back">' . "\n"
    .    '</td>' . "\n"
    .    '</tr>' . "\n"
    .    '</table>' . "\n"
    ;
}
###################################################################
###### STEP CONFIG SETTINGS #######################################
###################################################################
elseif($display==DISP_ADMINISTRATIVE_SETTING)
{
    echo '
                 <input type="hidden" name="fromPanel" value="'.$display.'">
                <h2>
                    '.sprintf(get_lang('StepNOfN'),(array_search(DISP_ADMINISTRATIVE_SETTING, $panelSequence)+1),count($panelSequence)).' : '.$panelTitle[DISP_ADMINISTRATIVE_SETTING].'
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
                                <input type="text" size="40" id="institutionForm" name="institutionForm" value="'.htmlspecialchars($institutionForm).'">
                                </td>
                        </tr>
                    <tr>
                        <td>
                            <label for="institutionUrlForm">URL</label>
                        </td>
                        <td colspan="2">
                            <input type="text" size="40" id="institutionUrlForm" name="institutionUrlForm" value="'.htmlspecialchars($institutionUrlForm).'">
                            <br />
                        </td>
                    </tr>
                <tr>
                        <td colspan="3"><br />
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
                            <input type="text" size="40" id="contactNameForm" name="contactNameForm" value="'.htmlspecialchars($contactNameForm).'">
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label for="contactEmailForm">Email</label>
                        </td>
                        <td colspan="2">
                            <input type="text" size="40" id="contactEmailForm" name="contactEmailForm" value="'.htmlspecialchars($contactEmailForm).'">
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3"><br />
                        </td>
                    </tr>
                </table>
                <table width="100%">
                 <tr>
                  <td>
                  </td>
                  <td align="right" rowspan="2" valign="bottom">
                   <input type="submit" name="install6" value="Next &gt;">
                  </td>
                 </tr>
                 <tr>
                  <td align="left">
                   <input type="submit" name="cmdPlatformSetting" value="&lt; Back">
                  </td>
                 </tr>
                </table>
                ';
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
                    '.sprintf(get_lang('StepNOfN'),(array_search(DISP_LAST_CHECK_BEFORE_INSTALL, $panelSequence)+1),count($panelSequence)).' : '.$panelTitle[DISP_LAST_CHECK_BEFORE_INSTALL].'
                </h2>
        Here are the values you entered <br />
        <Font color="red">
            Print this page to remember your admin password and other settings
        </font>
        <blockquote>

        <FIELDSET>
        <LEGEND>'.$panelTitle[DISP_DB_CONNECT_SETTING].'</LEGEND>
        <EM>Account</EM>
        <br />
        &nbsp;Database host : '.htmlspecialchars($dbHostForm).'<br />
        &nbsp;Database username : '.htmlspecialchars($dbUsernameForm).'<br />
        &nbsp;Database password : '.htmlspecialchars((empty($dbPassForm)?"--empty--":$dbPassForm)).'<br />

        &nbsp;Enable single database : '.($singleDbForm?'yes':'no').'<br />
        &nbsp;Enable tracking : '.($enableTrackingForm?'yes':'no').'<br />
        <EM>Database Names</EM><br />
        &nbsp;Main database : '.htmlspecialchars($dbNameForm).'<br />
        &nbsp;Tracking database : '.htmlspecialchars($dbStatsForm).'<br />';
    if ($mainTblPrefixForm!="" || $statsTblPrefixForm!="" || $dbPrefixForm!="")
        echo '<em>Prefixes</em><br />';
    if ($mainTblPrefixForm!="")
        echo '&nbsp;Main tables prefix : '.htmlspecialchars($mainTblPrefixForm).'<br />';
    if ($statsTblPrefixForm!="")
        echo '&nbsp;Tracking tables prefix : '.htmlspecialchars($statsTblPrefixForm).'<br />';
    if ($dbPrefixForm!="")
        echo '&nbsp;Courses database prefix : '.htmlspecialchars($dbPrefixForm).'<br />';
    echo '
        </FIELDSET>

        <FIELDSET>
        <LEGEND>'.$panelTitle[DISP_ADMINISTRATOR_SETTING].'</LEGEND>
        <div class="notethis">
                    Login : '.htmlspecialchars($loginForm).'<br />
                    Password : '.htmlspecialchars((empty($passForm)?"--empty-- <B>&lt;-- Error !</B>":$passForm)) .'<br />
        </div>
        Email : '.htmlspecialchars($adminEmailForm).'<br />
        Phone : '.htmlspecialchars($adminPhoneForm).'<br />
        Lastname : '.htmlspecialchars($adminNameForm).'<br />
        Firstname : '.htmlspecialchars($adminSurnameForm).'<br />

        </FIELDSET>
        <FIELDSET>
        <LEGEND>'.$panelTitle[DISP_PLATFORM_SETTING].'</LEGEND>
        Name : '.htmlspecialchars($campusForm).'<br />
        Complete URL : ' . (empty($urlForm)?"--empty--":$urlForm) . '<br />
        Main language : ' . ucwords($languageForm) . '<br />

        Self-registration : '.($allowSelfReg?'enabled':'disabled ').'<br />
        Password storage : ' .($encryptPassForm ?'crypted ':'clear text').'
        </FIELDSET>
        <FIELDSET>
        <LEGEND>Additional Informations</LEGEND>
        <em>Related organisation</em><br />

        &nbsp;Name : '.htmlspecialchars((empty($institutionForm)?"--empty--":$institutionForm)).'<br />
        &nbsp;URL  : '.(empty($institutionUrlForm)?"--empty--":$institutionUrlForm).'<br />

        <em>Campus contact</em><br />
        &nbsp;Name : '.htmlspecialchars((empty($contactNameForm)?"--empty--":$contactNameForm)).'<br />
        &nbsp;Email : '.htmlspecialchars((empty($contactEmailForm)?$adminEmailForm:$contactEmailForm)).'<br />


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
        </table>';

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
        echo "<hr />";
        if ($mainDbNameExist)
            echo '<P><B>'.get_lang('MainDB').'</B> db (<em>'.$dbNameForm.'</em>) already exist <br />
            <input type="checkbox" name="confirmUseExistingMainDb"  id="confirmUseExistingMainDb" value="true" '.($confirmUseExistingMainDb?'checked':'').'>
            <label for="confirmUseExistingMainDb" >I know, I want use it.</label><br />
            <font color="red">Warning !</font> : this script write in tables use by Claroline.
            </P>';
        if ($statsDbNameExist && $dbStatsForm!=$dbNameForm)
            echo '
        <P>
            <B>'.get_lang('StatDB').'</B> db ('.$dbStatsForm.') already exist
            <br />
            <input type="checkbox" name="confirmUseExistingStatsDb"  id="confirmUseExistingStatsDb" value="true" '.($confirmUseExistingStatsDb?'checked':'').'>
            <label for="confirmUseExistingStatsDb" >I know, I want use it.</label><br />
            <font color="red">Warning !</font>
            : this script write in tables use by Claroline.
        </P>';
        echo '
        <P>
            OR <input type="submit" name="cmdDbNameSetting" value="set DB Names">
        </P>
        <hr />';
    }
    if($mainDbNameCreationError)
        echo '<br />'.$mainDbNameCreationError;
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
        echo "<br />".$mainDbNameCreationError;
    if($statsDbNameCreationError)
        echo "<br />".$statsDbNameCreationError;
    if($fileAccessInLangRepositoryCreationError)
        echo "<br />Error on creation : file <EM>".$htAccessName."</EM> in <U>".realpath($htAccessLangPath)."</U><br />";
    if($fileAccessInSqlRepositoryCreationError)
        echo "<br />Error on creation : file <EM>".$htAccessName."</EM> in <U>".realpath($htAccessSqlPath)."</U><br />";
    if ($fileConfigCreationError)
    echo '
    <b>
        <font color="red">
            Probably, your script doesn\'t have write access to the config directory
        </font>
        <br />
        <SMALL>
            <EM>('.realpath("../inc/conf/").')</EM>
        </SMALL>
    </b>
    <br /><br />
    You probably do not have write access on Claroline root directory,
    i.e. you should <EM>CHMOD 777</EM> or <EM>755</EM> or <EM>775</EM><br /><br />

Your problems can be related on two possible causes :<br />
<UL>
    <LI>
        Permission problems.
        <br />Try initially with
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
        echo '<br /> <em>$coursesRepositorySys = '.$coursesRepositorySys.'</em> : <br />dir is missing';
    }

    if ($coursesRepositorySysWriteProtected)
    {
        echo '<br /><b><em>'.$coursesRepositorySys.'</em> is Write Protected.</b>
        Claroline need to have write right to create course.<br />
        change right on this directory and retry.';
    }

    if ($garbageRepositorySysMissing)
    {
        echo '<br /> <em>$garbageRepositorySys = '.$garbageRepositorySys.'</em> : <br />dir is missing';
    }

    if ($garbageRepositorySysWriteProtected)
    {
        echo '
        <br />
        <b>
            <em>'.$garbageRepositorySys.'</em>
            is Write Protected.
        </b>
        Claroline need to have write right to trash courses.<br />
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
                    echo sprintf(get_lang('StepNOfN'),(array_search(DISP_RUN_INSTALL_COMPLETE, $panelSequence)+1),count($panelSequence)).' : '.$panelTitle[DISP_RUN_INSTALL_COMPLETE];

 ?>

            </h2>
            <br />
            <br />


</form>
<form action="../../" method="POST">
        <input type="hidden" name="logout" value="TRUE">
        <input type="hidden" name="uidReset" value="TRUE">
<center>
        <input type="submit" value="Go to your newly created campus">

</form>
            <br />
            <br />
                Last tip : we highly recommend that you <strong>protect or remove the <em>/claroline/install/</em> directory</strong>.

            <br />
            <br />
        </center>
<?php
}    // STEP RUN_INSTALL_COMPLETE

else
{
    echo '
            <pre>'.$display.'</pre not set.
            <br />
            Error in script. <br />
            <br />
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
