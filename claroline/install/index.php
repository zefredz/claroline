<?php // $Id$
/**
 * CLAROLINE
 *
 * GOAL : install claroline 1.8 on server
 *
 * @version 1.9 $Revision$
 *
 * @copyright 2001-2007 Universite catholique de Louvain (UCL)
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

$imgStatus['X'] = 'delete.gif';
$imgStatus['V'] = 'checkbox_on.gif';
$imgStatus['?'] = 'checkbox_off.gif';
$imgStatus['!'] = 'caution.gif';

$cssStepStatus['X'] = 'error';
$cssStepStatus['V'] = 'done';
$cssStepStatus['?'] = 'todo';
$cssStepStatus['!'] = 'caution';

/* LET DEFINE ON SEPARATE LINES !!!*/

// TODO remove this code
error_reporting(E_ERROR | E_WARNING | E_PARSE);

// Place of Config file
$configFileName = 'claro_main.conf.php';

session_start();
$_SESSION = array();
session_destroy();

$newIncludePath ='../inc/';
include $newIncludePath . 'installedVersion.inc.php';

include '../lang/english/complete.lang.php';
include '../lang/english/locale_settings.php';

include_once $newIncludePath . 'lib/user.lib.php'; // needed fo generate_passwd()
include_once './install.lib.inc.php';
require_once $newIncludePath . 'lib/config.lib.inc.php';
include_once $newIncludePath . 'lib/form.lib.php';
include_once $newIncludePath . 'lib/course.lib.inc.php';
include_once $newIncludePath . 'lib/claro_main.lib.php';
include_once $newIncludePath . 'lib/language.lib.php';
include_once $newIncludePath . 'lib/module.manage.lib.php';
include_once $newIncludePath . 'lib/right/right_profile.lib.php';

/**
 * Unquote GET, POST AND COOKIES if magic quote gpc is enabled in php.ini
 */

claro_unquote_gpc();


// TODO remove this code
if (count($_GET) > 0)      {extract($_GET, EXTR_OVERWRITE);}
if (count($_POST) > 0)     {extract($_POST, EXTR_OVERWRITE);}
if (count($_SERVER) > 0)   {extract($_SERVER, EXTR_OVERWRITE);}


// LIST OF  VIEW IN ORDER TO SHOW
$panelSequence  = array(
DISP_LICENSE,
DISP_WELCOME,
//DISP_FILE_SYSTEM_SETTING,
DISP_DB_CONNECT_SETTING,
DISP_DB_NAMES_SETTING,
DISP_ADMINISTRATOR_SETTING,
DISP_PLATFORM_SETTING,
DISP_ADMINISTRATIVE_SETTING,
DISP_LAST_CHECK_BEFORE_INSTALL);
//DISP_RUN_INSTALL_NOT_COMPLETE is not a panel of sequence


// VIEW TITLE
$panelTitle[DISP_LICENSE]                   = get_lang('Licence');
$panelTitle[DISP_WELCOME]                   = get_lang('Requirements');
//$panelTitle[DISP_FILE_SYSTEM_SETTING]      = get_lang('FileSystemSetting');
$panelTitle[DISP_DB_CONNECT_SETTING]        = get_lang('MySQL Database Settings');
$panelTitle[DISP_DB_NAMES_SETTING]          = get_lang('MySQL Database and Table Names');
$panelTitle[DISP_ADMINISTRATOR_SETTING]     = get_lang('Administrator Account');
$panelTitle[DISP_PLATFORM_SETTING]          = get_lang('Platform Settings');
$panelTitle[DISP_ADMINISTRATIVE_SETTING]    = get_lang('Additional Informations');
$panelTitle[DISP_LAST_CHECK_BEFORE_INSTALL] = get_lang('Last check before install');
$panelTitle[DISP_RUN_INSTALL_COMPLETE]      = get_lang('Claroline Installation succeeds');

//$rootSys="'.realpath($pathForm).'";

$cmdName[DISP_WELCOME]                   = 'cmdWelcomePanel';
$cmdName[DISP_LICENSE]                   = 'cmdLicence';
//$cmdName[DISP_FILE_SYSTEM_SETTING]     = 'cmdFILE_SYSTEM_SETTING';
$cmdName[DISP_DB_CONNECT_SETTING]        = 'cmdDB_CONNECT_SETTING';
$cmdName[DISP_DB_NAMES_SETTING]          = 'cmdDbNameSetting';
$cmdName[DISP_ADMINISTRATOR_SETTING]     = 'cmdAdministratorSetting';
$cmdName[DISP_PLATFORM_SETTING]          = 'cmdPlatformSetting';
$cmdName[DISP_ADMINISTRATIVE_SETTING]    = 'cmdAdministrativeSetting';
$cmdName[DISP_LAST_CHECK_BEFORE_INSTALL] = 'install6';
$cmdName[DISP_RUN_INSTALL_COMPLETE]      = 'cmdDoInstall';


// CONTROLER
// GET cmd,
if($_REQUEST['cmdLicence'])
{
    $cmd=DISP_LICENSE;
}
elseif($_REQUEST['cmdWelcomePanel'])
{
    $cmd=DISP_WELCOME;
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
    $cmd = DISP_RUN_INSTALL_COMPLETE;
}








##### INITIALISE FORM VARIABLES ##################

###  IF FIRST VISIT ###
if(!$_REQUEST['alreadyVisited'] || $_REQUEST['resetConfig']) // on first step prupose values
{
     include './defaultsetting.inc.php';
     foreach (array_keys($panelTitle) as $step ) $stepStatus[$step] = '?';
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
if ($_REQUEST['fromPanel'] == DISP_WELCOME || $_REQUEST['cmdDoInstall'])
{
    $stepStatus[DISP_WELCOME] = 'V';
}

if ($_REQUEST['fromPanel'] == DISP_LICENSE || $_REQUEST['cmdDoInstall'])
{
    $stepStatus[DISP_LICENSE] = 'V';
}

if ($_REQUEST['fromPanel'] == DISP_LAST_CHECK_BEFORE_INSTALL || $_REQUEST['cmdDoInstall'])
{
    $stepStatus[DISP_LAST_CHECK_BEFORE_INSTALL] = 'V';
}

if($_REQUEST['fromPanel'] == DISP_ADMINISTRATOR_SETTING || $_REQUEST['cmdDoInstall'])
{
    $stepStatus[DISP_ADMINISTRATOR_SETTING] = 'V';
    if (empty($adminSurnameForm)||empty($passForm)||empty($loginForm)||empty($adminNameForm)||empty($adminEmailForm)||!is_well_formed_email_address($adminEmailForm))
    {
        $stepStatus[DISP_ADMINISTRATOR_SETTING] = 'X';
        $adminDataMissing = TRUE;
        if (empty($loginForm)) $missing_admin_data[] = 'login';
        if (empty($passForm))  $missing_admin_data[] = 'password';
        if (empty($adminSurnameForm)) $missing_admin_data[] = 'firstname';
        if (empty($adminNameForm)) $missing_admin_data[] = 'lastname';
        if (empty($adminEmailForm)) $missing_admin_data[] = 'email';
        if (!empty($adminEmailForm) && !is_well_formed_email_address($adminEmailForm)) $error_in_admin_data[] = 'email';

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

if( DISP_ADMINISTRATIVE_SETTING == $_REQUEST['fromPanel'] )
{
    $check_administrative_data = array();
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
            $check_administrative_data[] = get_lang('Institution Url');
        }
    }

    if (empty($contactEmailForm) || empty($contactNameForm)
        || !is_well_formed_email_address($contactEmailForm)
    )
    {
        $administrativeDataMissing = TRUE;
        if (empty($contactNameForm))
        {
            $check_administrative_data[] = get_lang('Contact name');
            $contactNameForm = $adminNameForm;
        }


        if (empty($contactEmailForm)||!is_well_formed_email_address($contactEmailForm))
        {
            $check_administrative_data[] = get_lang('Contact email');
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
        $msg_missing_administrative_data = '<div class="dialogError">'
        .    '<p>' . "\n"
        .    '<strong>'.get_lang('Error').'</strong> : '
        .    get_lang('Please complete following informations')
        .    '</p>' . "\n"
        .    '<ul>';
        foreach ( $check_administrative_data as $missing_administrative_data )
        {
            $msg_missing_administrative_data .= '<li>'.$missing_administrative_data.'</li>';
        }        
        
        $msg_missing_administrative_data .= '</ul>'
        .    '</div>';
        
        $display =  ( $cmd > DISP_ADMINISTRATIVE_SETTING ) ? DISP_ADMINISTRATIVE_SETTING : $cmd;
        $canRunCmd = FALSE;
        $stepStatus[DISP_ADMINISTRATIVE_SETTING] = 'X';
    }
    else
    {
        $stepStatus[DISP_ADMINISTRATIVE_SETTING] = 'V';
        // here add some check  on email, password crackability, ... of admin.
    }
}

if ($_REQUEST['fromPanel'] == DISP_DB_CONNECT_SETTING || $_REQUEST['cmdDoInstall'])
{
    // Check Connection //
    $databaseParam_ok = TRUE;
    $db = @mysql_connect("$dbHostForm", "$dbUsernameForm", "$dbPassForm");
    $stepStatus[DISP_DB_CONNECT_SETTING] = 'V';
    if ( mysql_errno() > 0 ) // problem with server
    {
        $no  = mysql_errno();
        $msg = mysql_error();
        $msg_no_connection = 
                '<div class="dialogError">'
                .    '<p>'
                .	 '<strong>'.get_lang('Error').'</strong> : '
                .    '</p>';
                
        if ( '2005' == $no )
        {
            $msg_no_connection .= get_lang('Wrong Database Host');
        }
        elseif ( '1045' == $no )
        {
            $msg_no_connection .= get_lang('Wrong database Login or Password');
        }
        else
        {
            $msg_no_connection .= 'Server unavailable. '
            					. '<p>'
                               .  'Is your MySQL server started ?'
                               .  '<br />'
                               .  'Fix this problem before going further'
                               .  '</p>'
                               ;
        }

        $msg_no_connection .= '<p>' . "\n" 
        .    '<small>(Mysql error ' . $no . ' : ' . $msg . ')</small>'
        .    '</p>'
        .	 '</div>';
        
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
        $stepStatus[DISP_DB_CONNECT_SETTING] = 'X';

    }
}


// CHECK DATA OF DB NAMES Form
if ($_REQUEST['fromPanel'] == DISP_DB_NAMES_SETTING || $_REQUEST['cmdDoInstall'])
{
    $stepStatus[DISP_DB_NAMES_SETTING] = 'V';
    $regexpPatternForDbName = '^[a-z0-9][a-z0-9_-]*$';
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
        if ($valMain || $valStat )
        if ($confirmUseExistingStatsDb ) $stepStatus[DISP_DB_NAMES_SETTING] = 'V';
        else
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
            $stepStatus[DISP_DB_NAMES_SETTING] = 'X';

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
    $stepStatus[DISP_PLATFORM_SETTING] = 'V';

    

    if(empty($urlForm) || empty($campusForm) )
    {
        $msg_missing_platform_data = '<div class="dialogError">'
        .    '<p>' . "\n"
        .     '<strong>'.get_lang('Error').'</strong> : '
        .     get_lang('Please complete following informations')
        .    '</p>' . "\n"
        .     '<ul>';
        
        if (empty($campusForm))
        {
            $msg_missing_platform_data .= '<li>'.get_lang('Name').'</li>';
        }

        if (empty($urlForm))
        {
            $msg_missing_platform_data .= '<li>'.get_lang('Complete URL').' (Something like : http://'.$_SERVER['SERVER_NAME'].$urlAppendPath.'/)</li>';
        }
    
        
        $msg_missing_platform_data .= '</ul>' . "\n"
        .     '</div>';
        
        $canRunCmd = FALSE;
        
        if ($cmd > DISP_PLATFORM_SETTING)
        {
            $display = DISP_PLATFORM_SETTING;
        }
        else
        {
            $display= $cmd;
        }
        $stepStatus[DISP_PLATFORM_SETTING] = 'X';
    }
}

// ALL Check are done.
// $canRunCmd has set during checks

if ($canRunCmd)
{
    // OK TEST WAS GOOD, What's the next step ?

    // SET default display
    $display = $panelSequence[0];
    if($_REQUEST['cmdWelcomePanel'])
    {
        $display = DISP_WELCOME;
    }
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
        $display = DISP_PLATFORM_SETTING;
    }
    elseif($_REQUEST['cmdAdministrativeSetting'])
    {
        $display = DISP_ADMINISTRATIVE_SETTING;
    }
    elseif($_REQUEST['cmdDoInstall'])
    {
        /*
        $includePath = $newIncludePath;
        $rootSys = realpath($newIncludePath . '/../../');
        include('./do_install.inc.php');
        */
    }
 }

//PREPARE DISPLAY


if( DISP_DB_NAMES_SETTING == $display )
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
elseif( DISP_ADMINISTRATIVE_SETTING == $display )
{
    if ($contactNameForm == '*not set*')
    {
        $contactNameForm = $adminSurnameForm . ' ' . $adminNameForm;
    }

    if ($contactEmailForm == '*not set*')
    {
        $contactEmailForm = $adminEmailForm;
    }

}
elseif( DISP_PLATFORM_SETTING == $display )
{
    $includePath = $newIncludePath;
    $language_list = claro_get_lang_flat_list();
}

// BEGIN OUTPUT

// COMMON OUTPUT Including top of form  and list of hidden values

echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"'
.    "\t". '"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">' ."\n"
.    '<html>' . "\n"
.    '<head>' . "\n"
.    '<title>' . "\n"
.    'Claroline installation'
.    ' - version ' . $new_version
.    ' - Step  ' . (array_search($display, $panelSequence) + 1)  . "\n"
.    '</title>' . "\n\n"

.    '<link rel="stylesheet" href="../css/install.css" type="text/css" />' . "\n"
.    '<style media="print" type="text/css" >' . "\n"
.    '    .progressPanel{ visibility: hidden;width:0px; }' . "\n"
.    '</style>' . "\n"
.    '</head>' . "\n"
.    '<body dir="' . $text_dir . '">' . "\n\n"
.    '<h1>Claroline ' . $new_version  . ' - Installation</h1>' . "\n"
.    '<div id="installBody">' . "\n\n"
.    '<form action="' . $_SERVER['PHP_SELF'] . '" method="post">' . "\n\n"
;


// don't display stepping on last panel
if (DISP_RUN_INSTALL_COMPLETE != $display )
{
    echo '<br/>' . "\n"
    .    '<div class="progressPanel">' . "\n"
    ;

    foreach ($panelSequence as $stepCount => $thisStep  )
    {
        $stepStyle = ($thisStep == $display) ? 'active' : $cssStepStatus[$stepStatus[$thisStep]];

        echo '<div class="progress ' . $stepStyle . '"  >'
        .    '<b>' . ($stepCount +1) . '</b> '
        .    strip_tags($panelTitle[$thisStep])
        .    '</div>' . "\n"
        ;
    }
    $stepPos = array_search($display, $panelSequence);
    echo '</div>' . "\n";
}

echo '<div id="panel">' . "\n\n";

if (DISP_RUN_INSTALL_COMPLETE != $display )
{
    $htmlNextPrevButton = '<div id="navigation">'  . "\n"
    .    '<div id="navToNext">'  . "\n"
    .    ($stepPos !== false && ($stepPos+1 < count($panelSequence)) ? '<input type="submit" name="' . $cmdName[$panelSequence[$stepPos+1]] . '" value="Next &gt; " />' :'')
    .    '</div>' . "\n"
    .    '<div id="navToPrev">'  . "\n"
    .    ($stepPos!==false && ( $stepPos > 0 ) ? '<input type="submit" name="' . $cmdName[$panelSequence[$stepPos-1]] . '" value="&lt; Back" />' :'')
    .    '</div>' . "\n"
    .    '</div>' . "\n"
    ;
}
else $htmlNextPrevButton = '';


foreach (array_keys($panelTitle) as $step )
{
    echo '<input type="hidden" name="stepStatus['.$step.']" value="' . $stepStatus[$step] . '" />'                ."\n";
}

echo '<input type="hidden" name="alreadyVisited" value="1" />'                                                 ."\n"
.    '<input type="hidden" name="urlAppendPath"                value="'.$urlAppendPath.'" />'                  ."\n"
.    '<input type="hidden" name="urlEndForm"                   value="'.$urlEndForm.'" />'                     ."\n"
.    '<input type="hidden" name="courseRepositoryForm"         value="'.$courseRepositoryForm.'" />'           ."\n"
.    '<input type="hidden" name="pathForm" value="'.str_replace("\\","/",realpath($pathForm)."/").'"  />'      ."\n"
.    '<input type="hidden" name="imgRepositoryAppendForm" value="'.str_replace("\\","/",$imgRepositoryAppendForm).'"  />'      ."\n"
.    '<input type="hidden" name="userImageRepositoryAppendForm" value="'.str_replace("\\","/",$userImageRepositoryAppendForm).'"  />'      ."\n"
.    '<input type="hidden" name="dbHostForm"                   value="'.$dbHostForm.'" />'                     ."\n"
.    '<input type="hidden" name="dbUsernameForm"               value="'.$dbUsernameForm.'" />'                 ."\n\n"
.    '<input type="hidden" name="singleDbForm"                 value="'.$singleDbForm.'" />'                   ."\n\n"
.    '<input type="hidden" name="dbPrefixForm"                 value="'.$dbPrefixForm.'" />'                   ."\n"
.    '<input type="hidden" name="dbNameForm"                   value="'.$dbNameForm.'" />'                     ."\n"
.    '<input type="hidden" name="dbStatsForm"                  value="'.$dbStatsForm.'" />'                    ."\n"
.    '<input type="hidden" name="mainTblPrefixForm"            value="'.$mainTblPrefixForm.'" />'              ."\n"
.    '<input type="hidden" name="statsTblPrefixForm"           value="'.$statsTblPrefixForm.'" />'              ."\n"
.    '<input type="hidden" name="dbMyAdmin"                    value="'.$dbMyAdmin.'" />'                      ."\n"
.    '<input type="hidden" name="dbPassForm"                   value="'.$dbPassForm.'" />'                     ."\n\n"
.    '<input type="hidden" name="urlForm"                      value="'.$urlForm.'" />'                        ."\n"
.    '<input type="hidden" name="adminEmailForm"               value="'.htmlspecialchars($adminEmailForm).'" />'   ."\n"
.    '<input type="hidden" name="adminNameForm"                value="'.htmlspecialchars($adminNameForm).'" />'    ."\n"
.    '<input type="hidden" name="adminSurnameForm"             value="'.htmlspecialchars($adminSurnameForm).'" />' ."\n\n"
.    '<input type="hidden" name="loginForm"                    value="'.htmlspecialchars($loginForm).'" />'        ."\n"
.    '<input type="hidden" name="passForm"                     value="'.htmlspecialchars($passForm).'" />'         ."\n\n"
.    '<input type="hidden" name="languageForm"                 value="'.$languageForm.'" />'                   ."\n\n"
.    '<input type="hidden" name="campusForm"                   value="'.htmlspecialchars($campusForm).'" />'       ."\n"
.    '<input type="hidden" name="contactNameForm"              value="'.htmlspecialchars($contactNameForm).'" />'  ."\n"
.    '<input type="hidden" name="contactEmailForm"             value="'.htmlspecialchars($contactEmailForm).'" />' ."\n"
.    '<input type="hidden" name="contactPhoneForm"             value="'.htmlspecialchars($contactPhoneForm).'" />' ."\n"
.    '<input type="hidden" name="institutionForm"              value="'.htmlspecialchars($institutionForm).'" />'  ."\n"
.    '<input type="hidden" name="institutionUrlForm"           value="'.$institutionUrlForm.'" />'             ."\n\n"
.    '<!-- BOOLEAN -->'                                                                                      ."\n"
.    '<input type="hidden" name="enableTrackingForm"           value="'.$enableTrackingForm.'" />'             ."\n"
.    '<input type="hidden" name="allowSelfReg"                 value="'.$allowSelfReg.'" />'                   ."\n"
.    '<input type="hidden" name="userPasswordCrypted"          value="'.$userPasswordCrypted.'" />'            ."\n"
.    '<input type="hidden" name="encryptPassForm"              value="'.$encryptPassForm.'" />'                ."\n"
.    '<input type="hidden" name="confirmUseExistingMainDb"     value="'.$confirmUseExistingMainDb.'" />'       ."\n"
.    '<input type="hidden" name="confirmUseExistingStatsDb"    value="'.$confirmUseExistingStatsDb.'" />';


 ##### PANNELS  ######
 #
 # INSTALL IS a big form
 # Too big to show  in one time.
 # PANEL show some  field to edit, all other are in HIDDEN FIELDS
###################################################################
############### STEP 1 LICENSE  ###################################
###################################################################
if(DISP_LICENSE == $display)
{
    echo '<input type="hidden" name="fromPanel" value="'.$display.'" />'  . "\n"
    .    '<h2>'  . "\n"
    .    get_lang('Step %step of %nb_step : %step_name', array( '%step' => array_search(DISP_LICENSE, $panelSequence)+1 ,
                                                                '%nb_step' => count($panelSequence) ,
                                                                '%step_name' => $panelTitle[DISP_LICENSE] ) )
    .    '</h2>'  . "\n"
    .    '<p>'  . "\n"
    .    'Claroline is free software, distributed under GNU General Public licence (GPL).<br />'  . "\n"
    .    'Please read the licence and click &quot;Next &gt;&quot; to accept it.'  . "\n"
    .    '<a href="../../LICENCE.txt">Printer-friendly version</a>'  . "\n"
    .    '</p>'  . "\n"
    .    '<textarea id="license" cols="65" rows="15">'
    ;

    readfile ('../license/gpl.txt');
    echo '</textarea>'
    ;

}
###################################################################
###### STEP 2 REQUIREMENTS ########################################
###################################################################
elseif ($display == DISP_WELCOME)
{
    echo '<input type="hidden" name="fromPanel" value="' . $display . '" />'
    .    '<h2>'
    .    get_lang('Step %step of %nb_step : %step_name', array( '%step' => array_search(DISP_WELCOME, $panelSequence)+1 ,
                                                                '%nb_step' => count($panelSequence) ,
                                                                '%step_name' => $panelTitle[DISP_WELCOME] ) )
    .    '</h2>'
    ;
    // check if an claroline configuration file doesn't already exists.
    if ( file_exists('../../platform/conf/claro_main.conf.php')
    ||   file_exists('../inc/conf/claro_main.conf.inc.php')
    ||   file_exists('../inc/conf/claro_main.conf.php')
    ||   file_exists('../inc/conf/config.inc.php')
    ||   file_exists('../include/config.inc.php')
    ||   file_exists('../include/config.php'))
    {
        echo '<div class="dialogWarning">'
        .    '<p>' . "\n"
        .    '<strong>'.get_lang('Warning !').'</strong>'
        .    ' : ' . get_lang('The installer has detected an existing claroline platform on your system.') . "\n"
        .    '</p>' . "\n"
        .    '<ul>' . "\n"
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
        .    'Be aware that a complete reinstallation will crush the data stored in your previous installed Claroline.'
        .    '</li>'
        .    '</ul>'
        .    '</div>'
        ;
    }

    if(!$stable)
    {
        echo '<div class="dialogWarning">'
        .    '<p>' . "\n"
        .    '<strong>'.get_lang('Warning !').'</strong>'
        .    ' : ' . get_lang('This version is not considered as stable and is not aimed for production.')
        .    '</p>' . "\n"
         .	 '<p>'
        .    'If something goes wrong, '
        .    'come talk on our support forum at '
        .    '<a href="http://forum.claroline.net/">'
        .    'http://forum.claroline.net'
        .    '</a>.'
        .    '</p>'."\n"
        .    '</div>'."\n\n"
        ;
    }

    echo '<p>Please, read thoroughly the '
    .    '<a href="../../INSTALL.txt">INSTALL.txt</a> document '
    .    'before proceeding to installation.'
    .    '</p>'
    .    '<fieldset>' . "\n"
    .	 '<legend>'.get_lang('Server requirements').'</legend>' . "\n"
    
    .	 '<table class="requirements">'
    .    '<tbody>' . "\n"
    .    '<tr>'
    .    '<td>Php version >= 5.2</td>' 
    .    '<td>' . ( version_compare(phpversion(), $requiredPhpVersion, ">=" ) ? '<span class="ok">Ok</span>':'<span class="ko">Ko</span>') . ' (' . phpversion() . ')</td>' 
    .    '</tr>'
    .    '<tr>'
    .    '<td>MySQL version >= 4.3</td>' 
    .    '<td>' . ( version_compare(mysql_get_client_info(), $requiredMySqlVersion, ">=" ) ? '<span class="ok">Ok</span>':'<span class="ko">Ko</span>') . ' (' . mysql_get_client_info(). ')</td>' 
    .    '</tr>' 
 
    .    '<tr>'
    .    '<th colspan="2">Required extensions</th>' 
    .    '</tr>'
    .    '<tr>'
    .    '<td>MySql</td>' 
    .    '<td>' . ( extension_loaded('mysql') ? '<span class="ok">Ok</span>':'<span class="ko">Ko</span>') . '</td>'
    .    '</tr>'
    .    '<tr>'
    .    '<td>Zlib compression</td>' 
    .    '<td>' . ( extension_loaded('zlib') ? '<span class="ok">Ok</span>':'<span class="ko">Ko</span>') . '</td>'
    .    '</tr>'
    .    '<tr>'
    .    '<td>Regular expressions</td>' 
    .    '<td>' . ( extension_loaded('pcre') ? '<span class="ok">Ok</span>':'<span class="ko">Ko</span>') . '</td>'
    .    '</tr>'
    .    '<tr>'
    .    '<td>XML</td>' 
    .    '<td>' . ( extension_loaded('xml') ? '<span class="ok">Ok</span>':'<span class="ko">Ko</span>') . '</td>'
    .    '</tr>'
    .    '<tr>'
    .    '<td>mbstring or iconv</td>' 
    .    '<td>'
    ;
    if( extension_loaded('mbstring') || extension_loaded('iconv') )
    {
        echo '<span class="ok">Ok</span> (';
        if( extension_loaded('mbstring') ) echo ' mbstring ';
        if( extension_loaded('iconv') ) echo ' iconv ';        
        echo ')';
    }
    else
    {
        echo '<span class="ko">Ko</span>';        
    }
    
    echo '</td>'
    .    '</tr>'
     
    .    '<tr>'
    .    '<th colspan="2">Optional extensions</th>' 
    .    '</tr>'    
    .    '<tr>'
    .    '<td>GD</td>' 
    .    '<td>' . ( extension_loaded('gd') ? '<span class="ok">Ok</span>':'<span class="ko">Ko</span>') . '</td>'
    .    '</tr>'
    .    '<tr>'
    .    '<td>LDAP</td>' 
    .    '<td>' . ( extension_loaded('ldap') ? '<span class="ok">Ok</span>':'<span class="ko">Ko</span>') . '</td>'
    .    '</tr>'
    .    '<tr>'
    .    '<td>OpenSSL</td>' 
    .    '<td>' . ( extension_loaded('openssl') ? '<span class="ok">Ok</span>':'<span class="ko">Ko</span>') . '</td>'
    .    '</tr>'
    .    '</tbody>' . "\n"
    .    '</table>'
	.	 '</fieldset>' . "\n\n"
    
    .    '<fieldset>' . "\n"
    .    '<legend>'.get_lang('Recommanded settings').'</legend>' . "\n"
    .    '<table  class="requirements">' . "\n"
    .    '<tr>' . "\n"
    .    '<th>'.get_lang('Setting').'</th>' . "\n" 
    .    '<th>'.get_lang('Recommended value').'</th>' . "\n"
    .    '<th>'.get_lang('Current value').'</th>' . "\n"
    .    '</tr>' . "\n"
    .    '<tbody>' . "\n"    
    .    '<tr>' . "\n"
    .    '<td>Safe mode</td>' . "\n"
    .    '<td>Off</td>' . "\n"
    .    '<td>' . check_php_setting('safe_mode', 'OFF') . '</td>' . "\n"
    .    '</tr>' . "\n"
    .    '<tr>' . "\n"
    .    '<td>Display errors</td>' . "\n"
    .    '<td>Off</td>' . "\n"
    .	 '<td>' . check_php_setting('display_errors', 'OFF') . '</td>' . "\n"
    .    '</tr>' . "\n"
    .    '<tr>' . "\n"
    .    '<td>Register globals</td>' . "\n" 
    .    '<td>Off</td>' . "\n"  
    .	 '<td>' . check_php_setting('register_globals', 'OFF') . '</td>' . "\n"
    .    '</tr>' . "\n"
    .    '<tr>' . "\n"
    .    '<td>Magic quotes GPC</td>' . "\n" 
    .    '<td>Off</td>' . "\n"  
    .	 '<td>' . check_php_setting('magic_quotes_gpc', 'OFF') . '</td>' . "\n"
    .    '</tr>' . "\n"
    .    '<tr>' . "\n"
    .    '<td>File uploads</td>' . "\n" 
    .    '<td>On</td>' . "\n"  
    .	 '<td>' . check_php_setting('file_uploads', 'ON') . '</td>' . "\n"
    .    '</tr>' . "\n"
    .    '<tr>' . "\n"
    .    '<td>Upload max filesize</td>' . "\n" 
    .    '<td>8-100M</td>' . "\n"  
    .	 '<td>' . ini_get('upload_max_filesize') . '</td>' . "\n"
    .    '</tr>' . "\n"
    .    '<tr>' . "\n"
    .    '<td>Post max size</td>' . "\n" 
    .    '<td>8-100M</td>' . "\n"  
    .	 '<td>' . ini_get('post_max_size') . '</td>' . "\n"
    .    '</tr>' . "\n"
    .    '</tbody>' . "\n"
    .    '</table>' . "\n\n"
    ;

    echo '</fieldset>' . "\n\n"
    
    .    '<fieldset>' . "\n"
    .    '<legend>'.get_lang('Directory and file permissions').'</legend>' . "\n"
    .    '<table class="requirements">' . "\n"
    .    '<tbody>' . "\n"
    .    '<tr>' . "\n"
    .    '<td>Is root folder ('.realpath('../..').') readable ?</td>'  . "\n"
    .    '<td>' . ( is_readable('../..') ? '<span class="ok">Yes</span>':'<span class="ko">No</span>') . '</td>' . "\n"
    .    '</tr>'     . "\n"
    .    '<tr>' . "\n"
    .    '<td>Is root folder ('.realpath('../..').') writable ?</td>'  . "\n"
    .    '<td>' . ( is_writable('../..') ? '<span class="ok">Yes</span>':'<span class="ko">No</span>') . '</td>' . "\n"
    .    '</tr>' . "\n"
    .    '</tbody>' . "\n"
    .    '</table>' . "\n"
	.	 '</fieldset>' . "\n\n"
    ;

}



##########################################################################
###### STEP 3 MYSQL DATABASE SETTINGS ####################################
##########################################################################

elseif(DISP_DB_CONNECT_SETTING == $display)
{


    echo '<input type="hidden" name="fromPanel" value="'.$display.'" />'
    .    '<h2>'
    .    get_lang('Step %step of %nb_step : %step_name', array( '%step' => array_search(DISP_DB_CONNECT_SETTING, $panelSequence)+1 ,
                                                                '%nb_step' => count($panelSequence) ,
                                                                '%step_name' => $panelTitle[DISP_DB_CONNECT_SETTING] ) )
    .    '</h2>'
    .    $msg_no_connection
    .    '<fieldset>' . "\n"
    .    '<legend>'.get_lang('Mysql connection parameters').'</legend>' . "\n"
    .    '<p>'
    .    get_lang('Enter here the parameters given by your database server administrator.')
    .    '</p>'
    
    
    .    '<div class="row">' . "\n"
    .    '<div class="rowTitle">' . "\n"
    .    '<label for="dbHostForm"><span class="required">*</span> '.get_lang('Database host').'</label>' . "\n"
    .    '</div>' . "\n"
    .    '<div class="rowField">' . "\n"
    .    '<input type="text" size="25" id="dbHostForm" name="dbHostForm" value="'.htmlspecialchars($dbHostForm).'" />' . "\n"
    .    '<span class="example">' . get_lang('e.g.') . ' localhost' . '</span>' . "\n"
    .    '</div>' . "\n"
    .    '</div>' . "\n\n"
        
    .    '<div class="row">' . "\n"
    .    '<div class="rowTitle">' . "\n"
    .    '<label for="dbUsernameForm"><span class="required">*</span> '.get_lang('Database username').'</label>' . "\n"
    .    '</div>' . "\n"
    .    '<div class="rowField">' . "\n"
    .    '<input type="text"  size="25" id="dbUsernameForm" name="dbUsernameForm" value="'.htmlspecialchars($dbUsernameForm).'" />' . "\n"
    .    '<span class="example">' . get_lang('e.g.') . ' root' . '</span>' . "\n"
    .    '</div>' . "\n"
    .    '</div>' . "\n\n"
    
    .    '<div class="row">' . "\n"
    .    '<div class="rowTitle">' . "\n"
    .    '<label for="dbPassForm"><span class="required">*</span> '.get_lang('Database password').'</label>' . "\n"
    .    '</div>' . "\n"
    .    '<div class="rowField">' . "\n"
    .    '<input type="text"  size="25" id="dbPassForm" name="dbPassForm" value="'.htmlspecialchars($dbPassForm).'" />' . "\n"
    .    '<span class="example">' . get_lang('e.g.') . ' ' . generate_passwd(8) . '</span>' . "\n"
    .    '</div>' . "\n"
    .    '</div>' . "\n\n"
    
    .    '</fieldset>' . "\n\n"
    
    .    '<fieldset>' . "\n"
    .    '<legend>'.get_lang('Database usage').'</legend>' . "\n"  

    .    '<div class="row">' . "\n"
    .    '<div class="rowTitle">' . "\n"
    .    '<span class="required">*</span> ' . get_lang('Database mode') . "\n"
    .    '</div>' . "\n"
    .    '<div class="rowField">' . "\n"
    .    '<input type="radio" id="singleDbForm_single" name="singleDbForm" value="1" '.($singleDbForm?'checked':'').' />'
    .    '<label for="singleDbForm_single">' . get_lang('Single') . '</label>' . "\n"
    .    '<br />'
    .    '<input type="radio" id="singleDbForm_multi" name="singleDbForm" value="0" '.($singleDbForm?'':'checked').' />'
    .    '<label for="singleDbForm_multi">' . get_lang('Multi') 
    .    '<small>'
    .    '(a database is created at each course creation)'
    .    '</small>' 
    .    '</label>' . "\n"
    .    '</div>' . "\n"
    .    '</div>' . "\n\n"  
    .    '</fieldset>' . "\n\n"
	.    '<small><span class="required">*</span> denotes required field</small>' . "\n"
    ;
}     // cmdDB_CONNECT_SETTING


##########################################################################
###### STEP 4 MYSQL DATABASE SETTINGS ####################################
##########################################################################
elseif(DISP_DB_NAMES_SETTING == $display )
{
    echo '<input type="hidden" name="fromPanel" value="' . $display . '" />'  . "\n"
    .    '<h2>'  . "\n"
    .    get_lang('Step %step of %nb_step : %step_name', array( '%step' => array_search(DISP_DB_NAMES_SETTING, $panelSequence)+1 ,
                                                                '%nb_step' => count($panelSequence) ,
                                                                '%step_name' => $panelTitle[DISP_DB_NAMES_SETTING] ) )
    .    '</h2>'  . "\n"
    .    $msg_no_connection . ''  . "\n"
    ;
    
    if( isset($databaseNameValid) && !$databaseNameValid )
    {

        echo '<div class="dialogError">'  . "\n"
        .    '<p>' . "\n"
        .    '<strong>Error</strong> '  . "\n"
        .    ' : Database <em>'.$dbNameForm.'</em> is not valid. '  . "\n"
        .    '</p>' . "\n"
        .    '<ul>'
        .    ($msgErrorDbMain_dbName?'<li>Main db<ul>':'')
        .    ($msgErrorDbMain_dbNameToolLong?'<li>dbName Too Long':'')
        .    ($msgErrorDbMain_dbNameInvalid?'<li>dbName Invalid Check the character (only letter ciffer and _)':'')
        .    ($msgErrorDbMain_dbNameBadStart?'<li>dbName Must begin by a letter':'')
        .    ($msgErrorDbStat_dbName?'</ul><li>Stat db<ul>':'')
        .    ($msgErrorDbStat_dbNameToolLong?'<li>dbName Too Long':'')
        .    ($msgErrorDbStat_dbNameInvalid?'<li>dbName Invalid. Check the character (only letter ciffer and _)':'')
        .    ($msgErrorDbStat_dbNameBadStart?'<li>dbName Must begin by a letter':'')
        .    '</ul>'  . "\n"
        .    '</ul>'  . "\n"
        .    '</div>'  . "\n"
        ;

    }
    
    if ($mainDbNameExist)
    {
        echo '<div class="dialogWarning">'  . "\n"
        .    '<p>' . "\n"
        .    '<strong>Warning</strong>'  . "\n"
        .    ' : Database <em>'.$dbNameForm.'</em> already exists.'  . "\n"
        .    '</p>' . "\n"
        .    '<p>'  . "\n"
        .    'Claroline may overwrite data previously stored'  . "\n"
        .    'in tables of this database.'  . "\n"
        .    '</p>'  . "\n"
        .    '<p>'  . "\n"
        .    '<input type="checkbox" name="confirmUseExistingMainDb"  id="confirmUseExistingMainDb" value="true" '.($confirmUseExistingMainDb?'checked':'').' />'  . "\n"
        .    '<label for="confirmUseExistingMainDb" >'  . "\n"
        .    '<strong>I know, I want to use this database.</strong>'  . "\n"
        .    '</label>'  . "\n"
        .    '</p>'  . "\n"
        .    '</div>'  . "\n"
        ;
    }
    
    echo '<fieldset>' . "\n"
    .    '<legend>'.get_lang('Database names').'</legend>' . "\n"
    .	 '<div class="row">' . "\n"
    .    '<div class="rowTitle">' . "\n"
    .    '<label for="dbNameForm"><span class="required">*</span> '.($singleDbForm ? get_lang('Database name'):get_lang('Main database')).'</label>' . "\n"
    .    '</div>' . "\n"
    .    '<div class="rowField">' . "\n"
    .    '<input type="text"  size="25" id="dbNameForm" name="dbNameForm" value="'.htmlspecialchars($dbNameForm).'" />' . "\n"
    .    '<span class="example">' . get_lang('e.g.') . ' ' . $dbNameForm . '</span>' . "\n"
    .    '</div>' . "\n"
    .    '</div>' . "\n\n"
    
    /*
    Moosh would like to put this in a popup.
    .    (is_array($existingDbs) ? (5 > count($existingDbs) ? '<br/><abbr title="&quot;' . implode('&quot;, &quot;', $existingDbs) . '&quot;" >INFO : Existing databases</abbr>' . "\n"
                                                            : '<br/>INFO : ' . count($existingDbs) . ' databases found<br/><select size="8" ><option>' . implode('</option><option>', $existingDbs) . '</option></select>')
                                 : '')
 	*/
	.	 '<div class="row">' . "\n"
    .    '<div class="rowTitle">' . "\n"
    .    '<label for="mainTblPrefixForm">'.get_lang('Prefix for names of main tables').'</label>' . "\n"
    .    '</div>' . "\n"
    .    '<div class="rowField">' . "\n"
    .    '<input type="text"  size="5" id="mainTblPrefixForm" name="mainTblPrefixForm" value="'.htmlspecialchars($mainTblPrefixForm).'" />' . "\n"
    .    '<span class="example">' . get_lang('e.g.') . ' ' . $mainTblPrefixForm . '</span>' . "\n"
    .    '</div>' . "\n"
    .    '</div>' . "\n\n"
    ;
    
    if (!$singleDbForm)
    {
        if ($statsDbNameExist && $dbStatsForm != $dbNameForm)
        {
            echo '<div class="dialogWarning">'  . "\n"
            .    '<p>' . "\n"
            .    '<strong>Warning</strong>'  . "\n"
            .    ' : Database <em>'.$dbStatsForm.'</em> already exists'  . "\n"
            .    '</p>' . "\n"
            .    '<p>'  . "\n"
            .    'Claroline may overwrite data previously stored'  . "\n"
            .    'in tables of this database.'  . "\n"
            .    '</p>'  . "\n"
            .    '<p>'  . "\n"
            .    '<input type="checkbox" name="confirmUseExistingStatsDb"  id="confirmUseExistingStatsDb" value="true" ' . ($confirmUseExistingStatsDb?'checked':'') . ' />'  . "\n"
            .    '<label for="confirmUseExistingStatsDb" >'  . "\n"
            .    '<strong>I know, I want to use this database.</strong>'  . "\n"
            .    '</label>'  . "\n"
            .    '</p>'  . "\n"
            .    '</div>'  . "\n"
            ;
        }
        
        echo '<div class="row">' . "\n"
        .    '<div class="rowTitle">' . "\n"
        .    '<label for="dbStatsForm"><span class="required">*</span> '.get_lang('Tracking database').'</label>' . "\n"
        .    '</div>' . "\n"
        .    '<div class="rowField">' . "\n"
        .    '<input type="text"  size="25" id="dbStatsForm" name="dbStatsForm" value="'.htmlspecialchars($dbStatsForm).'" />' . "\n"
        .    '<span class="example">' . get_lang('e.g.') . ' ' . $dbStatsForm . '</span>' . "\n"
        .    '</div>' . "\n"
        .    '</div>' . "\n\n"
        
        .    '<div class="row">' . "\n"
        .    '<div class="rowTitle">' . "\n"
        .    '<label for="statsTblPrefixForm">'.get_lang('Prefix for names of tracking tables').'</label>' . "\n"
        .    '</div>' . "\n"
        .    '<div class="rowField">' . "\n"
        .    '<input type="text"  size="5" id="statsTblPrefixForm" name="statsTblPrefixForm" value="'.htmlspecialchars($statsTblPrefixForm).'" />' . "\n"
        .    '<span class="example">' . get_lang('e.g.') . ' ' . $statsTblPrefixForm . '</span>' . "\n"
        .    '</div>' . "\n"
        .    '</div>' . "\n\n"

        .    '<blockquote><small>'  . "\n"
        .    'Normally, Claroline creates the tracking tables into the main Claroline database. <br />'
        .    'But, if you want, you have the possibility to store tracking data into a separate database <br />'
        .    'or to specify a special prefix for tracking tables.'  . "\n"
        .    '</small></blockquote>'  . "\n"
        ;
    }
    
    echo '<div class="row">' . "\n"
    .    '<div class="rowTitle">' . "\n"
    .    '<label for="dbPrefixForm">'.($singleDbForm?'Prefix for names of course tables':get_lang('Prefix for names of course databases')).'</label>' . "\n"
    .    '</div>' . "\n"
    .    '<div class="rowField">' . "\n"
    .    '<input type="text"  size="25" id="dbPrefixForm" name="dbPrefixForm" value="'.htmlspecialchars($dbPrefixForm).'" />' . "\n"
    .    '<span class="example">' . get_lang('e.g.') . ' ' . $dbPrefixForm . '</span>' . "\n"
    .    '</div>' . "\n"
    .    '</div>' . "\n\n"
    ;
    
    if (!$singleDbForm)
    {
        echo '<blockquote>'  . "\n"
        .    '<small>'  . "\n"
        .    '<b>'  . "\n"
        .    'Afterwards, Claroline will create a new database for each newly '  . "\n"
        .    'created course. '  . "\n"
        .    '</b>'  . "\n"
        .    '<br />'  . "\n"
        .    'You can specify a prefix for these database names.'  . "\n"
        .    '</small>'  . "\n"
        .    '</blockquote>'  . "\n"
        ;

    }
    
    echo '</fieldset>' . "\n\n"
    .    '<small><span class="required">*</span> denotes required field</small>' . "\n";
    
}     // cmdDB_CONNECT_SETTING

##########################################################################
###### STEP ADMIN SETTINGS ##############################################
##########################################################################
elseif(DISP_ADMINISTRATOR_SETTING == $display )

{
    echo '<input type="hidden" name="fromPanel" value="'.$display.'" />'  . "\n"
    .    '<h2>'  . "\n"
    .    get_lang('Step %step of %nb_step : %step_name', array( '%step' => array_search(DISP_ADMINISTRATOR_SETTING, $panelSequence)+1 ,
                                                                '%nb_step' => count($panelSequence) ,
                                                                '%step_name' => $panelTitle[DISP_ADMINISTRATOR_SETTING] ) )
    .    '</h2>'  . "\n"
	;
	
    if( is_array($missing_admin_data) || is_array($error_in_admin_data) )
    {
        echo '<div class="dialogError">'  . "\n"
        .    '<p>' . "\n"
        .     '<strong>'.get_lang('Error').'</strong> : '
        .     get_lang('Please complete following informations')
        .    '</p>' . "\n"
        .    '<p>'  . "\n"
        .    ( is_array($missing_admin_data) ? 'Fill in '.implode(', ',$missing_admin_data) .'<br />' : '' )
        .    ( is_array($error_in_admin_data) ? 'Check '.implode(', ',$error_in_admin_data) : '' )
        .    '</p>'  . "\n"
        .    '</div>'  . "\n"
        ; 
    }
	
    echo '<fieldset>' . "\n"
    .    '<legend>'.get_lang('Administrator identity').'</legend>' . "\n"

    .	 '<div class="row">' . "\n"
    .    '<div class="rowTitle">' . "\n"
    .    '<label for="loginForm"><span class="required">*</span> '.get_lang('Login').'</label>' . "\n"
    .    '</div>' . "\n"
    .    '<div class="rowField">' . "\n"
    .    '<input type="text" size="40" id="loginForm" name="loginForm" value="'.htmlspecialchars($loginForm).'" />' . "\n"
    .    '<span class="example">' . get_lang('e.g.') . ' jdoe</span>' . "\n"
    .    '</div>' . "\n"
    .    '</div>' . "\n\n"
    
    .	 '<div class="row">' . "\n"
    .    '<div class="rowTitle">' . "\n"
    .    '<label for="passForm"><span class="required">*</span> '.get_lang('Password').'</label>' . "\n"
    .    '</div>' . "\n"
    .    '<div class="rowField">' . "\n"
    .    '<input type="text" size="40" id="passForm" name="passForm" value="'.htmlspecialchars($passForm).'" />' . "\n"
    .    '<span class="example">' . get_lang('e.g.') . generate_passwd(8) . '</span>' . "\n"
    .    '</div>' . "\n"
    .    '</div>' . "\n\n"
    
    .	 '<div class="row">' . "\n"
    .    '<div class="rowTitle">' . "\n"
    .    '<label for="adminEmailForm"><span class="required">*</span> '.get_lang('Email').'</label>' . "\n"
    .    '</div>' . "\n"
    .    '<div class="rowField">' . "\n"
    .    '<input type="text" size="40" id="adminEmailForm" name="adminEmailForm" value="'.htmlspecialchars($adminEmailForm).'" />' . "\n"
    .    '<span class="example">' . get_lang('e.g.') . ' jdoe@mydomain.net</span>' . "\n"
    .    '</div>' . "\n"
    .    '</div>' . "\n\n"
        
    .	 '<div class="row">' . "\n"
    .    '<div class="rowTitle">' . "\n"
    .    '<label for="adminNameForm"><span class="required">*</span> '.get_lang('Last name').'</label>' . "\n"
    .    '</div>' . "\n"
    .    '<div class="rowField">' . "\n"
    .    '<input type="text" size="40" id="adminNameForm" name="adminNameForm" value="'.htmlspecialchars($adminNameForm).'" />' . "\n"
    .    '<span class="example">' . get_lang('e.g.') . ' Doe</span>' . "\n"
    .    '</div>' . "\n"
    .    '</div>' . "\n\n"
    
    .	 '<div class="row">' . "\n"
    .    '<div class="rowTitle">' . "\n"
    .    '<label for="adminSurnameForm"><span class="required">*</span> '.get_lang('First name').'</label>' . "\n"
    .    '</div>' . "\n"
    .    '<div class="rowField">' . "\n"
    .    '<input type="text" size="40" id="adminSurnameForm" name="adminSurnameForm" value="'.htmlspecialchars($adminSurnameForm).'" />' . "\n"
    .    '<span class="example">' . get_lang('e.g.') . ' John</span>' . "\n"
    .    '</div>' . "\n"
    .    '</div>' . "\n\n"
    
    .    '</fieldset>'  . "\n"
    .    '<small><span class="required">*</span> denotes required field</small>' . "\n"
    ;
}

###################################################################
###### STEP CONFIG SETTINGS #######################################
###################################################################

elseif(DISP_PLATFORM_SETTING == $display)
{
    echo '<input type="hidden" name="fromPanel" value="'.$display.'" />' . "\n"
    .    '<h2>' . "\n"
    .    get_lang('Step %step of %nb_step : %step_name', array( '%step' => array_search(DISP_PLATFORM_SETTING, $panelSequence)+1 ,
                                                                '%nb_step' => count($panelSequence) ,
                                                                '%step_name' => $panelTitle[DISP_PLATFORM_SETTING] ) )
    .    '</h2>'  . "\n"
    .    $msg_missing_platform_data . "\n"
    .    '<fieldset>' . "\n"
    .    '<legend>'.get_lang('Campus').'</legend>' . "\n"

    .	 '<div class="row">' . "\n"
    .    '<div class="rowTitle">' . "\n"
    .    '<label for="campusForm"><span class="required">*</span> '.get_lang('Name').'</label>' . "\n"
    .    '</div>' . "\n"
    .    '<div class="rowField">' . "\n"
    .    '<input type="text" size="40" id="campusForm" name="campusForm" value="'.htmlspecialchars($campusForm).'" />' . "\n"
    .    '</div>' . "\n"
    .    '</div>' . "\n\n"

    .	 '<div class="row">' . "\n"
    .    '<div class="rowTitle">' . "\n"
    .    '<label for="urlForm"><span class="required">*</span> '.get_lang('Complete URL').'</label>' . "\n"
    .    '</div>' . "\n"
    .    '<div class="rowField">' . "\n"
    .    '<input type="text" size="60" id="urlForm" name="urlForm" value="'.htmlspecialchars($urlForm).'" />' . "\n"
    .    '</div>' . "\n"
    .    '</div>' . "\n\n"    

    .	 '<div class="row">' . "\n"
    .    '<div class="rowTitle">' . "\n"
    .    '<label for="courseRepositoryForm">'.get_lang('Courses repository path (relative to the URL above)').'</label>' . "\n"
    .    '</div>' . "\n"
    .    '<div class="rowField">' . "\n"
    .    '<input type="text"  size="60" id="courseRepositoryForm" name="courseRepositoryForm" value="'.htmlspecialchars($courseRepositoryForm).'" />' . "\n"
    .    '</div>' . "\n"
    .    '</div>' . "\n\n"  

    .	 '<div class="row">' . "\n"
    .    '<div class="rowTitle">' . "\n"
    .    '<label for="languageForm"><span class="required">*</span> '.get_lang('Main language').'</label>' . "\n"
    .    '</div>' . "\n"
    .    '<div class="rowField">' . "\n"
    .    claro_html_form_select( 'languageForm'
                               , $language_list
                               , $languageForm
                               , array('id'=>'languageForm')) . "\n"
    .    '</div>' . "\n"
    .    '</div>' . "\n\n"  
    
    .    '</fieldset>' . "\n\n"
    
    .    '<fieldset>' . "\n"
    .    '<legend>'.get_lang('User').'</legend>' . "\n"

    .	 '<div class="row">' . "\n"
    .    '<div class="rowTitle">' . "\n"
    .    '<span class="required">*</span> ' . "\n" 
    .    get_lang('Self-registration') . "\n"
    .    '</div>' . "\n"
    .    '<div class="rowField">' . "\n"
    .    '<input type="radio" id="allowSelfReg_1" name="allowSelfReg" value="1" ' . ($allowSelfReg?'checked':'') . ' />' . "\n"
    .    '<label for="allowSelfReg_1">'.get_lang('Enabled').'</label>' . "\n"
    .    '<br />' . "\n"
    .    '<input type="radio" id="allowSelfReg_0" name="allowSelfReg" value="0" '.($allowSelfReg?'':'checked').' />' . "\n"
    .    '<label for="allowSelfReg_0">'.get_lang('Disabled').'</label>' . "\n"
    .    '</div>' . "\n"
    .    '</div>' . "\n\n"  
    
    .	 '<div class="row">' . "\n"
    .    '<div class="rowTitle">' . "\n"
    .    '<span class="required">*</span> ' . "\n"
    .    get_lang('Password storage') . "\n"
    .    '</div>' . "\n"
    .    '<div class="rowField">' . "\n"
    .    '<input type="radio" name="encryptPassForm" id="encryptPassForm_0" value="0"  '.($encryptPassForm?'':'checked') . ' />' . "\n"
    .    '<label for="encryptPassForm_0">'.get_lang('Clear text').'</label>' . "\n"
    .    '<br />' . "\n"
    .    '<input type="radio" name="encryptPassForm" id="encryptPassForm_1" value="1" ' . ($encryptPassForm?'checked':'') . ' />' . "\n"
    .    '<label for="encryptPassForm_1">'.get_lang('Crypted').'</label>' . "\n"
    .    '</div>' . "\n"
    .    '</div>' . "\n\n" 
   
    .    '</fieldset>' . "\n"
    .    '<small><span class="required">*</span> denotes required field</small>' . "\n"
    ;
}
###################################################################
###### STEP CONFIG SETTINGS #######################################
###################################################################
elseif(DISP_ADMINISTRATIVE_SETTING == $display)
{
    echo '<input type="hidden" name="fromPanel" value="' . $display . '" /><h2>'
    .    get_lang('Step %step of %nb_step : %step_name', array( '%step' => array_search(DISP_ADMINISTRATIVE_SETTING, $panelSequence)+1 ,
                                                                '%nb_step' => count($panelSequence) ,
                                                                '%step_name' => $panelTitle[DISP_ADMINISTRATIVE_SETTING] ) )
    .    '</h2>'  . "\n"
    .    $msg_missing_administrative_data
    
    .	 '<fieldset>' . "\n"
    .    '<legend>'.get_lang('Related organisation').'</legend>' . "\n"
    
    .	 '<div class="row">' . "\n"
    .    '<div class="rowTitle">' . "\n"
    .    '<label for="institutionForm">'.get_lang('Institution name').'</label>' . "\n"
    .    '</div>' . "\n"
    .    '<div class="rowField">' . "\n"
    .    '<input type="text" size="40" id="institutionForm" name="institutionForm" value="'.htmlspecialchars($institutionForm) . '" />' . "\n"
    .    '</div>' . "\n"
    .    '</div>' . "\n\n"   
    
    .	 '<div class="row">' . "\n"
    .    '<div class="rowTitle">' . "\n"
    .    '<label for="institutionUrlForm">'.get_lang('Institution url').'</label>' . "\n"
    .    '</div>' . "\n"
    .    '<div class="rowField">' . "\n"
    .    '<input type="text" size="40" id="institutionUrlForm" name="institutionUrlForm" value="'.htmlspecialchars($institutionUrlForm) . '" />' . "\n"
    .    '</div>' . "\n"
    .    '</div>' . "\n\n"       

    .    '</fieldset>' . "\n\n"
    
    .    '<fieldset>' . "\n"
    .    '<legend>'.get_lang('Campus contact').'</legend>' . "\n"

    .	 '<div class="row">' . "\n"
    .    '<div class="rowTitle">' . "\n"
    .    '<label for="contactNameForm"><span class="required">*</span> '.get_lang('Contact name').'</label>' . "\n"
    .    '</div>' . "\n"
    .    '<div class="rowField">' . "\n"
    .    '<input type="text" size="40" id="contactNameForm" name="contactNameForm" value="'.htmlspecialchars($contactNameForm) . '"/>' . "\n"
    .    '</div>' . "\n"
    .    '</div>' . "\n\n"  
    
    .	 '<div class="row">' . "\n"
    .    '<div class="rowTitle">' . "\n"
    .    '<label for="contactEmailForm"><span class="required">*</span> '.get_lang('Contact email').'</label>' . "\n"
    .    '</div>' . "\n"
    .    '<div class="rowField">' . "\n"
    .    '<input type="text" size="40" id="contactEmailForm" name="contactEmailForm" value="'.htmlspecialchars($contactEmailForm) . '"/>' . "\n"
    .    '</div>' . "\n"
    .    '</div>' . "\n\n"  
    
    .	 '<div class="row">' . "\n"
    .    '<div class="rowTitle">' . "\n"
    .    '<label for="contactPhoneForm">'.get_lang('Contact phone').'</label>' . "\n"
    .    '</div>' . "\n"
    .    '<div class="rowField">' . "\n"
    .    '<input type="text" size="40" id="contactPhoneForm" name="contactPhoneForm" value="'.htmlspecialchars($contactPhoneForm) . '" />' . "\n"
    .    '</div>' . "\n"
    .    '</div>' . "\n\n"  
    
    .    '</fieldset>' . "\n"
    .    '<small><span class="required">*</span> denotes required field</small>' . "\n"
    ;
}

###################################################################
###### STEP LAST CHECK BEFORE INSTALL #############################
###################################################################
elseif(DISP_LAST_CHECK_BEFORE_INSTALL == $display )
{
    $pathForm = str_replace("\\\\", "/", $pathForm);
    //echo "pathForm $pathForm";
    echo '<input type="hidden" name="fromPanel" value="'.$display . '" />' . "\n"
    .    '<h2>'
    .    get_lang('Step %step of %nb_step : %step_name', array( '%step' => array_search(DISP_LAST_CHECK_BEFORE_INSTALL, $panelSequence)+1 ,
                                                                '%nb_step' => count($panelSequence) ,
                                                                '%step_name' => $panelTitle[DISP_LAST_CHECK_BEFORE_INSTALL] ) )
    .    '</h2>' . "\n"
    .    '<p>' . "\n"
    .    'Here are the values you entered <br />' . "\n"
    .    'Print this page to remember your admin password and other settings' . "\n"
    .    '</p>' . "\n"
    .    '<fieldset>' . "\n"
    .    '<legend>'.$panelTitle[DISP_DB_CONNECT_SETTING] .'</legend>' . "\n"

    .    '<p class="checkSubTitle">'.get_lang('Mysql connection parameters').'</p>' . "\n"
    .    '<ul class="checkList">' . "\n\n"
    
    .    '<li class="check">' . "\n"
    .    '<span class="checkTitle">' . "\n"
    .    get_lang('Database host') . ' : ' . "\n"
    .    '</span>' . "\n"
    .    '<div class="checkValue">' . "\n"
    .    htmlspecialchars($dbHostForm)
    .    '</div>' . "\n"
    .    '</li>' . "\n\n"
        
    .    '<li class="check">' . "\n"
    .    '<span class="checkTitle">' . "\n"
    .    get_lang('Database username') . ' : ' . "\n"
    .    '</span>' . "\n"
    .    '<div class="checkValue">' . "\n"
    .    htmlspecialchars($dbUsernameForm)
    .    '</div>' . "\n"
    .    '</li>' . "\n\n"
        
    .    '<li class="check">' . "\n"
    .    '<span class="checkTitle">' . "\n"
    .    get_lang('Database password') . ' : ' . "\n"
    .    '</span>' . "\n"
    .    '<div class="checkValue">' . "\n"
    .    htmlspecialchars((empty($dbPassForm) ? '--empty--' : $dbPassForm))
    .    '</div>' . "\n"
    .    '</li>' . "\n\n"

    .    '</ul>' . "\n\n"
    
    .    '<p class="checkSubTitle">'.get_lang('Database usage').'</p>' . "\n"
    .    '<ul class="checkList">' . "\n\n"
        
    .    '<li class="check">' . "\n"
    .    '<span class="checkTitle">' . "\n"
    .    get_lang('Database mode') . ' : ' . "\n"
    .    '</span>' . "\n"
    .    '<div class="checkValue">' . "\n"
    .    ($singleDbForm ? get_lang('Single') : get_lang('Multi'))
    .    '</div>' . "\n"
    .    '</li>' . "\n\n"
   
    .    '</ul>' . "\n\n"
    .    '</fieldset>' . "\n"
    
    .    '<fieldset>' . "\n"
    .    '<legend>'.$panelTitle[DISP_DB_NAMES_SETTING] .'</legend>' . "\n"
    
    .    '<ul class="checkList">' . "\n\n"
    
    .    '<li class="check">' . "\n"
    .    '<span class="checkTitle">' . "\n"
    .    get_lang('Main database') . ' : ' . "\n"
    .    '</span>' . "\n"
    .    '<div class="checkValue">' . "\n"
    .    htmlspecialchars($dbNameForm)
    .    '</div>' . "\n"
    .    '</li>' . "\n\n"
    
    .    '<li class="check">' . "\n"
    .    '<span class="checkTitle">' . "\n"
    .    get_lang('Tracking database') . ' : ' . "\n"
    .    '</span>' . "\n"
    .    '<div class="checkValue">' . "\n"
    .    htmlspecialchars($dbStatsForm)
    .    '</div>' . "\n"
    .    '</li>' . "\n\n"
    
    .    '</ul>' . "\n\n"
    .    '<p class="checkSubTitle">'.get_lang('Table prefixes').'</p>' . "\n"
    .    '<ul class="checkList">' . "\n\n"
  	;
  	
    if ( '' != $mainTblPrefixForm )
    {
        echo '<li class="check">' . "\n"
        .    '<span class="checkTitle">' . "\n"
        .    get_lang('Main tables prefix') . ' : ' . "\n"
        .    '</span>' . "\n"
        .    '<div class="checkValue">' . "\n"
        .    htmlspecialchars($mainTblPrefixForm)
        .    '</div>' . "\n"
        .    '</li>' . "\n\n"
        ;
    } 

    if ( '' != $statsTblPrefixForm )
    {
        echo '<li class="check">' . "\n"
        .    '<span class="checkTitle">' . "\n"
        .    get_lang('Tracking tables prefix') . ' : ' . "\n"
        .    '</span>' . "\n"
        .    '<div class="checkValue">' . "\n"
        .    htmlspecialchars($statsTblPrefixForm)
        .    '</div>' . "\n"
        .    '</li>' . "\n\n"
        ;
    } 
    
    if ( '' != $dbPrefixForm )
    {
        echo '<li class="check">' . "\n"
        .    '<span class="checkTitle">' . "\n"
        .    get_lang('Courses database prefix') . ' : ' . "\n"
        .    '</span>' . "\n"
        .    '<div class="checkValue">' . "\n"
        .    htmlspecialchars($dbPrefixForm)
        .    '</div>' . "\n"
        .    '</li>' . "\n\n"
        ;
    }     
        
    echo '</ul>' . "\n\n"
    .	 '</fieldset>' . "\n"
    
    .    '<ul class="checkList">' . "\n"
    
    .    '<fieldset>' . "\n"
    .    '<legend>'.$panelTitle[DISP_ADMINISTRATOR_SETTING].'</legend>' . "\n"
    
    .    '<li class="check">' . "\n"
    .    '<span class="checkTitle notehis">' . "\n"
    .    get_lang('Login') . ' : ' . "\n"
    .    '</span>' . "\n"
    .    '<div class="checkValue">' . "\n"
    .    htmlspecialchars($loginForm)
    .    '</div>' . "\n"
    .    '</li>' . "\n\n"

    .    '<li class="check">' . "\n"
    .    '<span class="checkTitle notethis">' . "\n"
    .    get_lang('Password') . ' : ' . "\n"
    .    '</span>' . "\n"
    .    '<div class="checkValue">' . "\n"
    .    htmlspecialchars((empty($passForm)?"--empty-- <B>&lt;-- Error !</B>":$passForm))
    .    '</div>' . "\n"
    .    '</li>' . "\n\n"

    .    '<li class="check">' . "\n"
    .    '<span class="checkTitle">' . "\n"
    .    get_lang('Email') . ' : ' . "\n"
    .    '</span>' . "\n"
    .    '<div class="checkValue">' . "\n"
    .    htmlspecialchars($adminEmailForm)
    .    '</div>' . "\n"
    .    '</li>' . "\n\n"

    .    '<li class="check">' . "\n"
    .    '<span class="checkTitle">' . "\n"
    .    get_lang('Last name') . ' : ' . "\n"
    .    '</span>' . "\n"
    .    '<div class="checkValue">' . "\n"
    .    htmlspecialchars($adminNameForm)
    .    '</div>' . "\n"
    .    '</li>' . "\n\n"

    .    '<li class="check">' . "\n"
    .    '<span class="checkTitle">' . "\n"
    .    get_lang('First name') . ' : ' . "\n"
    .    '</span>' . "\n"
    .    '<div class="checkValue">' . "\n"
    .    htmlspecialchars($adminSurnameForm)
    .    '</div>' . "\n"
    .    '</li>' . "\n\n"
   
    .    '</ul>' . "\n\n"
    
    .    '</fieldset>' . "\n"

    .    '<fieldset>' . "\n"
    .    '<legend>'.$panelTitle[DISP_PLATFORM_SETTING].'</legend>' . "\n"
    
    .    '<p class="checkSubTitle">'.get_lang('Campus').'</p>' . "\n"
    .    '<ul class="checkList">' . "\n\n"
    
    .    '<li class="check">' . "\n"
    .    '<span class="checkTitle">' . "\n"
    .    get_lang('Campus name') . ' : ' . "\n"
    .    '</span>' . "\n"
    .    '<div class="checkValue">' . "\n"
    .    htmlspecialchars($campusForm)
    .    '</div>' . "\n"
    .    '</li>' . "\n\n"

    .    '<li class="check">' . "\n"
    .    '<span class="checkTitle">' . "\n"
    .    get_lang('Campus url') . ' : ' . "\n"
    .    '</span>' . "\n"
    .    '<div class="checkValue">' . "\n"
    .    (empty($urlForm)?"--empty--":$urlForm)
    .    '</div>' . "\n"
    .    '</li>' . "\n\n"

    .    '<li class="check">' . "\n"
    .    '<span class="checkTitle">' . "\n"
    .    get_lang('Main language') . ' : ' . "\n"
    .    '</span>' . "\n"
    .    '<div class="checkValue">' . "\n"
    .    ucwords($languageForm)
    .    '</div>' . "\n"
    .    '</li>' . "\n\n"
    
    .    '</ul>' . "\n\n"
    
    .    '<p class="checkSubTitle">'.get_lang('Users').'</p>' . "\n"
    .    '<ul class="checkList">' . "\n\n"

    .    '<li class="check">' . "\n"
    .    '<span class="checkTitle">' . "\n"
    .    get_lang('Self-registration') . ' : ' . "\n"
    .    '</span>' . "\n"
    .    '<div class="checkValue">' . "\n"
    .    ($allowSelfReg?'enabled':'disabled ')
    .    '</div>' . "\n"
    .    '</li>' . "\n\n"

    .    '<li class="check">' . "\n"
    .    '<span class="checkTitle">' . "\n"
    .    get_lang('Password storage') . ' : ' . "\n"
    .    '</span>' . "\n"
    .    '<div class="checkValue">' . "\n"
    .    ($encryptPassForm ?'crypted ':'clear text')
    .    '</div>' . "\n"
    .    '</li>' . "\n\n"
    
    .    '</ul>' . "\n\n"

    .    '</fieldset>' . "\n"
    
    
    .    '<fieldset>' . "\n"
    .    '<legend>'. get_lang('Additional Informations') . '</legend>' . "\n"
    .    '<p class="checkSubTitle">'.get_lang('Related organisation').'</p>' . "\n"
    
    .    '<ul class="checkList">' . "\n\n"

    .    '<li class="check">' . "\n"
    .    '<span class="checkTitle">' . "\n"
    .    get_lang('Institution name') . ' : ' . "\n"
    .    '</span>' . "\n"
    .    '<div class="checkValue">' . "\n"
    .    htmlspecialchars((empty($institutionForm)?"--empty--":$institutionForm))
    .    '</div>' . "\n"
    .    '</li>' . "\n\n"

    .    '<li class="check">' . "\n"
    .    '<span class="checkTitle">' . "\n"
    .    get_lang('Password storage') . ' : ' . "\n"
    .    '</span>' . "\n"
    .    '<div class="checkValue">' . "\n"
    .    (empty($institutionUrlForm)?"--empty--":$institutionUrlForm)
    .    '</div>' . "\n"
    .    '</li>' . "\n\n"
    
    .    '</ul>' . "\n\n"
        

    .    '<p class="checkSubTitle">'.get_lang('Campus contact').'</p>' . "\n"
    
    .    '<ul class="checkList">' . "\n\n"

    .    '<li class="check">' . "\n"
    .    '<span class="checkTitle">' . "\n"
    .    get_lang('Contact name') . ' : ' . "\n"
    .    '</span>' . "\n"
    .    '<div class="checkValue">' . "\n"
    .    htmlspecialchars((empty($contactNameForm)?"--empty--":$contactNameForm))
    .    '</div>' . "\n"
    .    '</li>' . "\n\n"

    .    '<li class="check">' . "\n"
    .    '<span class="checkTitle">' . "\n"
    .    get_lang('Contact email') . ' : ' . "\n"
    .    '</span>' . "\n"
    .    '<div class="checkValue">' . "\n"
    .    htmlspecialchars((empty($contactEmailForm)?$adminEmailForm:$contactEmailForm))
    .    '</div>' . "\n"
    .    '</li>' . "\n\n"

    .    '<li class="check">' . "\n"
    .    '<span class="checkTitle">' . "\n"
    .    get_lang('Contact phone') . ' : ' . "\n"
    .    '</span>' . "\n"
    .    '<div class="checkValue">' . "\n"
    .    htmlspecialchars($contactPhoneForm)
    .    '</div>' . "\n"
    .    '</li>' . "\n\n"
    
    .    '</ul>' . "\n\n"
    .    '</fieldset>' . "\n"
    .    '<center><input type="submit" name="cmdDoInstall" value="Install Claroline" /></center>' . "\n"
    ;

}

###################################################################
###### DB NAME ERROR !#########################################
###################################################################

elseif($display==DISP_DB_NAMES_SETTING_ERROR)
{
    echo '<input type="hidden" name="fromPanel" value="' . $display . '" />' . "\n"
    .    '<h2>' . "\n"
    .    'Install Problem' . "\n"
    .    '</h2>'
    ;
    if (
        $mainDbNameExist
    ||    $statsDbNameExist
    )
    {
        echo "<hr />";
        if ($mainDbNameExist)
            echo '<P>' . "\n"
            .    '<B>'.get_lang('Main database').'</B> db (<em>'.$dbNameForm.'</em>) already exist <br />' . "\n"
            .    '<input type="checkbox" name="confirmUseExistingMainDb"  id="confirmUseExistingMainDb" value="true" '.($confirmUseExistingMainDb?'checked':'').' />' . "\n"
            .    '<label for="confirmUseExistingMainDb" >I know, I want use it.</label>' . "\n"
            .    '<br />' . "\n"
            .    '<font color="red">Warning !</font>' . "\n"
            .    ' : this script write in tables use by Claroline.' . "\n"
            .    '</P>'
            ;
        if ($statsDbNameExist && $dbStatsForm!=$dbNameForm)
            echo '<P>' . "\n"
            .    '<B>'.get_lang('Tracking database').'</B> db ('.$dbStatsForm.') already exist' . "\n"
            .    '<br />' . "\n"
            .    '<input type="checkbox" name="confirmUseExistingStatsDb"  id="confirmUseExistingStatsDb" value="true" '.($confirmUseExistingStatsDb?'checked':'') . ' />' . "\n"
            .    '<label for="confirmUseExistingStatsDb" >I know, I want use it.</label><br />' . "\n"
            .    '<font color="red">Warning !</font>' . "\n"
            .    ': this script write in tables use by Claroline.' . "\n"
            .    '</P>'
            ;
        echo '<P>' . "\n"
        .    'OR <input type="submit" name="cmdDbNameSetting" value="set DB Names" />' . "\n"
        .    '</P>' . "\n"
        .    '<hr />'
        ;
    }

    if( $mainDbNameCreationError )
        echo '<br />' . $mainDbNameCreationError;

    echo '<p align="right">' . "\n"
    .    '<input type="submit" name="alreadyVisited" value="|&lt; Restart from beginning" />' . "\n"
    .    '<input type="submit" name="' . $cmdName[$panelSequence[array_search($display, $panelSequence)-1]] . '" value="&lt; Back" />' . "\n"
    .    '<input type="submit" name="cmdDoInstall" value="Retry" />' . "\n"
    .    '</p>'
    ;
}

###################################################################
###### INSTALL INCOMPLETE!#########################################
###################################################################

elseif(DISP_RUN_INSTALL_NOT_COMPLETE == $display)
{
    echo '
          <input type="hidden" name="fromPanel" value="'.$display.'" />
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
    echo '<b>' . "\n"
    .    '<font color="red">' . "\n"
    .    'Probably, your script doesn\'t have write access to the config directory' . "\n"
    .    '</font>' . "\n"
    .    '<br />' . "\n"
    .    '<SMALL>' . "\n"
    .    '<EM>('.realpath("../inc/conf/").')</EM>' . "\n"
    .    '</SMALL>' . "\n"
    .    '</b>' . "\n"
    .    '<br /><br />' . "\n"
    .    'You probably do not have write access on Claroline root directory,' . "\n"
    .    'i.e. you should <EM>CHMOD 777</EM> or <EM>755</EM> or <EM>775</EM><br /><br />' . "\n"
    .    'Your problems can be related on two possible causes :<br />' . "\n"
    .    '<UL>' . "\n"
    .    '<LI>' . "\n"
    .    'Permission problems.' . "\n"
    .    '<br />Try initially with' . "\n"
    .    '<EM>chmod 777 -R</EM> and increase restrictions gradually.' . "\n"
    .    '</LI>' . "\n"
    .    '<LI>' . "\n"
    .    'PHP is running in' . "\n"
    .    '<a href="http://www.php.net/manual/en/features.safe-mode.php" target="_phpman">' . "\n"
    .    'SAFE MODE</a>.' . "\n"
    .    'If possible, try to switch it off.' . "\n"
    .    '</LI>' . "\n"
    .    '</UL>' . "\n"
    .    '<a href="http://www.claroline.net/forum/viewtopic.php?t=753">Read about this problem in Support Forum</a>' . "\n"
    ;

    if ($configError)
    {
        if(is_array($messageConfigErrorList) )
        if(count($messageConfigErrorList) )
        {
            echo '<br />Error on config files creation : <ul>';

            foreach($messageConfigErrorList as $messageConfigError)
            {
                echo '<li><b><font color="red">'. $messageConfigError . '</font></b></li>';
            }
            echo '</ul>';
        }
        else
        {
            echo '<br />' . "\n"
            .    'Unidentified Error on config files creation'
            ;
        }

    }

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
        </b>' . "\n"
        .    'Claroline need to have write right to trash courses.' . "\n"
        .    '<br />' . "\n"
        .    'change right on this directory and retry.'
        ;
    }

    if ($platformConfigRepositorySysMissing)
    {
        echo '<br /> <em>$garbageRepositorySys = ' . claro_get_conf_repository() . '</em> : <br />dir is missing';
    }

    if ($platformConfigRepositorySysWriteProtected)
    {
        echo '<br />' . "\n"
        .    '<b>' . "\n"
        .    '<em>' . claro_get_conf_repository() . '</em>
            is Write Protected.' . "\n"
        .    '</b>' . "\n"
        .    'Claroline need to have write right to trash courses.' . "\n"
        .    '<br />' . "\n"
        .    'change right on this directory and retry.';
    }


    echo '<p align="right">'
    .    '<input type="submit" name="alreadyVisited" value="Restart from beginning" />' . "\n"
    .    '<input type="submit" name="' . $cmdName[$panelSequence[count($panelSequence)-1]] . '" value="&lt; Back" />' . "\n"
    .    '<input type="submit" name="cmdDoInstall" value="Retry" />' . "\n"
    .    '</p>'
    ;

}

###################################################################
###### STEP RUN_INSTALL_COMPLETE !#################################
###################################################################
elseif(DISP_RUN_INSTALL_COMPLETE == $display)
{

    echo '<h2>'
    .    $panelTitle[DISP_RUN_INSTALL_COMPLETE]
    .    '</h2>' . "\n"
    .    '</form>' . "\n" // close main form to open the redirection one
    .    '<div class="dialogWarning">'
    .    '<p>'
    .	 '<strong>'.get_lang('Warning').'</strong> : '
    .    get_lang('We highly recommend that you <strong>protect or remove the <em>/claroline/install/</em> directory</strong>.') . "\n"
    .    '</p>'
    .    '</div>' . "\n"
    .    '<fieldset>' . "\n"
    .    '<legend>'.get_lang('Do not forget to ').'</legend>'
    .    '<ul>'
    .    '<li>'
    .    get_lang('Tune your install in config in %administration | %configuration', array('%administration' => get_lang('Administration'),'%configuration' => get_lang('Configuration'))) . "\n"
    .    '</li>'
    .    '<li>'
    .    get_lang('Build your course category tree in %administration | %manage course categories', array('%administration' => get_lang('Administration'),'%manage course categories' => get_lang('Manage course categories'))) . "\n"
    .    '</li>'
    .    '<li>'
    .    get_lang('Edit or clear text Zones in %administration | %edit text zones', array('%administration' => get_lang('Administration'),'%edit text zones' => get_lang('Edit text zones'))) . "\n"
    .    '</li>'
    .    '</ul>' . "\n"
    .    '</fieldset>' . "\n"
    .    '<form action="../../" method="post">' . "\n"
    .    '<input type="hidden" name="logout" value="TRUE" />' . "\n"
    .    '<input type="hidden" name="uidReset" value="TRUE" />' . "\n"
    .    '<input type="submit" value="Go to your newly created campus" />' . "\n"
    .    '</form>' . "\n"
    ;
}    // STEP RUN_INSTALL_COMPLETE

else
{
    echo 'Error in script. <br />' . "\n"
    .    '<br />' . "\n"
    .    'Please report and explain that failure on <a href="http://forum.claroline.net">Claroline\'s support forums</a> )'
    ;
}

echo $htmlNextPrevButton;
?>
</div><!-- end of div panel -->
</form>
</div><!--  end div install -->
<div id="footer">
    <hr />
    <div id="footerLeft">
		<a href="http://www.claroline.net">http://www.claroline.net</a>		    
    </div>


    <div id="footerRight">
		<a href="mailto:&#105;&#x6E;&#x66;&#x6F;&#64;&#99;&#x6C;&#x61;&#114;&#x6F;&#108;&#105;&#110;&#x65;&#x2E;&#110;&#101;&#116;">&#105;&#x6E;&#x66;&#x6F;&#64;&#99;&#x6C;&#x61;&#114;&#x6F;&#108;&#105;&#110;&#x65;&#x2E;&#110;&#101;&#116;</a>
	</div>


    <div id="footerCenter">
	<?php echo get_lang('Powered by'); ?> <a href="http://www.claroline.net" target="_blank">Claroline</a>
	</div>

    </div>
</div> 
</body>
</html>
