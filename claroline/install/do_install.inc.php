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
 * @version 1.8 $Revision$
 *
 * @copyright 2001-2006 Universite catholique de Louvain (UCL)
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
 * @todo check if dbexist would be improve for check if table exists, not if db exist.
 *
 */

! defined( "CLARO_FILE_PERMISSIONS" ) && define( "CLARO_FILE_PERMISSIONS", 0777 );
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

mysql_query("CREATE DATABASE `" . $mainDbName . "`");
if (mysql_errno() >0)
{
    if (mysql_errno() == 1007)
    {   // DB already exist
        if ($confirmUseExistingMainDb)
        {
            $runfillMainDb = TRUE;
            $mainDbSuccesfullCreated = TRUE;
        }
        else
        {
            $mainDbNameExist = TRUE;
            $display = DISP_DB_NAMES_SETTING;
        }
    }
    else
    {   // other error would  break install
        $mainDbNameCreationError = '
                <P class="setup_error">
                    <font color="red">Warning !</font>
                    <small>[' . mysql_errno() . '] - ' . mysql_error() . '</small>
                    <br />
                    Error on creation ' . get_lang('MainDB') . ' : <I>' . $dbHostForm . '</I>
                    <br />
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
        claro_sql_query("CREATE DATABASE `" . $statsDbName . "`");
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
                    <small>[' . mysql_errno() . '] - ' . mysql_error() . '</small>
                    <br />
                    Error on creation ' . get_lang('StatDB') . ' : <I>' . $dbStatsForm . '</I>
                    <br />
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

    include './dropMainTables.inc.php';
    include './createMainBase.inc.php';
    include './fillMainBase.inc.php';

    mysql_select_db ($statsDbName);
    include './dropStatTables.inc.php';
    include './createStatBase.inc.php';
    include './fillStatBase.inc.php';
}

// FILE SYSTEM OPERATION
//
// Build path

$rootSys                    = str_replace("\\","/",realpath($pathForm)."/") ;
$coursesRepositoryAppend    = '';
$coursesRepositorySys = $rootSys . $courseRepositoryForm;
@mkdir($coursesRepositorySys, CLARO_FILE_PERMISSIONS);
$clarolineRepositoryAppend  = 'claroline/';
$clarolineRepositorySys     = $rootSys . $clarolineRepositoryAppend;
$garbageRepositorySys   = str_replace("\\","/",realpath($clarolineRepositorySys) . '/claroline_garbage');
@mkdir($garbageRepositorySys, CLARO_FILE_PERMISSIONS);

########################## WRITE claro_main.conf.php ##################################
// extract the path to append to the url
// if Claroline is not installed on the web root directory

//$urlAppendPath = ereg_replace ("claroline/install/index.php", "", $_SERVER['PHP_SELF']);

// here I want find  something to get garbage out of documentRoot

$fd = @fopen($configFilePath, 'w');
if (!$fd)
{
    $fileConfigCreationError = true;
    $display=DISP_RUN_INSTALL_NOT_COMPLETE;
}
else
{
    // get value form installer form
    $form_value_list['platform_id'] = md5(realpath(__FILE__));
    $form_value_list['rootWeb'] = $urlForm;
    $form_value_list['urlAppend'] = $urlAppendPath;
    $form_value_list['rootSys'] = $rootSys;
    $form_value_list['dbHost'] =  $dbHostForm;
    $form_value_list['dbLogin'] = $dbUsernameForm;
    $form_value_list['dbPass'] = $dbPassForm;
    $form_value_list['mainDbName'] = $mainDbName;
    $form_value_list['mainTblPrefix'] = $mainTblPrefixForm;
    $form_value_list['statsDbName'] = $statsDbName;
    $form_value_list['statsTblPrefix'] = $statsTblPrefixForm ;
    $form_value_list['dbNamePrefix'] = $dbPrefixForm;
    $form_value_list['is_trackingEnabled'] = trueFalse($enableTrackingForm);
    $form_value_list['singleDbEnabled'] = trueFalse($singleDbForm);
    $form_value_list['courseTablePrefix'] = $singleDbForm && empty($dbPrefixForm)?'crs_':'';
    $form_value_list['dbGlu'] = $singleDbForm?'_':'`.`';
    $form_value_list['mysqlRepositorySys']= str_replace("\\","/",realpath($mysqlRepositorySys)."/");
    $form_value_list['clarolineRepositoryAppend'] = 'claroline/';
    $form_value_list['coursesRepositoryAppend'] = $courseRepositoryForm;
    $form_value_list['rootAdminAppend'] = 'admin/';
    $form_value_list['imgRepositoryAppend'] = $imgRepositoryAppendForm;
    $form_value_list['userImageRepositoryAppend'] = $userImageRepositoryAppendForm ;
    $form_value_list['clarolineRepositorySys'] = $rootSys.$clarolineRepositoryAppend;
    $form_value_list['clarolineRepositoryWeb'] = $rootWeb.$clarolineRepositoryAppend;
    $form_value_list['coursesRepositorySys'] = $rootSys.$coursesRepositoryAppend;
    $form_value_list['coursesRepositoryWeb'] = $rootWeb.$coursesRepositoryAppend;
    $form_value_list['rootAdminSys'] = $clarolineRepositorySys.$rootAdminAppend;
    $form_value_list['rootAdminWeb'] = $clarolineRepositoryWeb.$rootAdminAppend;
    $form_value_list['garbageRepositorySys'] = $garbageRepositorySys;
    $form_value_list['siteName'] = $campusForm;
    $form_value_list['administrator_name'] = $contactNameForm;
    $form_value_list['administrator_phone'] = $contactPhoneForm;
    $form_value_list['administrator_email'] = (empty($contactEmailForm)?$adminEmailForm:$contactEmailForm);
    $form_value_list['institution_name'] = $institutionForm;
    $form_value_list['institution_url'] = $institutionUrlForm;
    $form_value_list['userPasswordCrypted'] = trueFalse($encryptPassForm);
    $form_value_list['allowSelfReg'] = trueFalse($allowSelfReg);
    $form_value_list['platformLanguage'] = $languageForm ;
    $form_value_list['claro_stylesheet'] = 'default.css';

    ######### DEALING WITH FILES #########################################

    /**
     * Config file to undist
     */

    $arr_file_to_undist =
    array (
    $newIncludePath . '../../textzone_top.inc.html',
    $newIncludePath . '../../textzone_right.inc.html',
    $newIncludePath . 'conf/auth.conf.php'
    );

    foreach ($arr_file_to_undist As $undist_this)
        claro_undist_file($undist_this);

    /***
     * Generate conf from definition files.
     */

    $includePath = $newIncludePath;
    $def_file_list = get_def_file_list();

    if ( is_array($def_file_list) )
    {
        foreach ( $def_file_list as  $config_code => $def )
        {
            // new config object
            $config = new Config($config_code);    

            // load configuration
            if ( $config->load() ) 
            {
                $config_name = $config->config_code;
                
                // validate config
                if ( $config->validate($form_value_list) )
                {
                    // save config file
                    $config->save();
                }
                else
                {
                    // no valid
                    $error = true ;
                    $message = $config->get_error_message();
                }
            }
            else
            {
                // error loading the configuration
                $error = true ;
                $message = $config->get_error_message();
            }
        }
    }
}

// write currentVersion.inc.php

$fp_currentVersion = fopen($includePath .'/currentVersion.inc.php','w');
$currentVersionStr = '<?php
$clarolineVersion = "'.$new_version.'";
$versionDb = "'.$new_version.'";
?>';
fwrite($fp_currentVersion, $currentVersionStr);
fclose($fp_currentVersion);

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

/**
 * Add administrator in user and admin table
 */

if ( $runfillMainDb )
{
    include_once($newIncludePath . 'lib/user.lib.php');

    $user_data['lastname'] = $adminNameForm;
    $user_data['firstname'] = $adminSurnameForm;
    $user_data['username'] = $loginForm;
    $user_data['password'] = $passForm;
    $user_data['email'] = $adminEmailForm;
    $user_data['status'] = 1; // COURSEMANAGER constant
    $user_data['officialCode'] = '';
    $user_data['phone'] = $adminPhoneForm;

    $id_admin = user_add($user_data);

    user_add_admin($id_admin);
}

?>
