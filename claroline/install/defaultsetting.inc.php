<?php # $Id$

/**
 * @version CLAROLINE 1.6
 *
 * @Copyright (c) 2001-2004 Universite catholique de Louvain (UCL)
 *
 * @license GENERAL PUBLIC LICENSE (GPL)
 * This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
 * as published by the FREE SOFTWARE FOUNDATION. The GPL is available
 * through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
 *
 * @author: Christophe Gesché <moosh@claroline.net>
 *
 * @package Install
 *
 * This script set default content at init of install
 *
 */
    include_once('../inc/conf/def/CLMAIN.def.conf.inc.php');
    
    $dbHostForm     = $conf_def_property_list['dbHost']['default'];
    $dbUsernameForm = $conf_def_property_list['dbLogin']['default'];

    $dbPrefixForm   = $conf_def_property_list['dbNamePrefix']['default'];// $dbPrefixForm."c_";
    
    $mainTblPrefixForm  = $conf_def_property_list['mainTblPrefix']['default'];
    $dbNameForm         = $conf_def_property_list['mainDbName']['default'];// $dbPrefixForm."claroline";
    $statsTblPrefixForm = $conf_def_property_list['statsTblPrefix']['default'];
    $dbStatsForm        = $conf_def_property_list['statsDbName']['default'];
    
    $singleDbForm   = $conf_def_property_list['singleDbEnabled']['default'];
    $enableTrackingForm =  $conf_def_property_list['is_trackingEnabled']['default'];
    /*
     * extract the path to append to the url if Claroline is not installed on the web root directory
     */

     // remove possible double slashes
    $urlAppendPath = str_replace( array('///', '//'), '/', $PHP_SELF);
    // detect if url case sensitivity does matter
    $caseSensitive = (PHP_OS == 'WIN32' || PHP_OS == 'WINNT') ? 'i' : '';
    // build the regular expression pattern
    $ereg = "#/claroline/install/".basename($_SERVER['SCRIPT_NAME'])."$#$caseSensitive";
    $urlAppendPath  = preg_replace ($ereg, '', $urlAppendPath);
    $urlForm        = "http://".$_SERVER['SERVER_NAME'].$urlAppendPath."/";
    $pathForm       = realpath("../..")."/";

    $courseRepositoryForm = $conf_def_property_list['coursesRepositoryAppend']['default'];



    $campusForm          = $conf_def_property_list['siteName']['default'];
    $institutionForm     = $conf_def_property_list['institution_name'] ['default'];
    $institutionUrlForm  = $conf_def_property_list['institution_url'] ['default'];

    $languageForm = $conf_def_property_list['platformLanguage']['default'];

    $userPasswordCrypted =$conf_def_property_list['userPasswordCrypted']['default'];

    $allowSelfReg =$conf_def_property_list['allowSelfReg']['default'] ;

    
    
    /**
     * admin & contact
     */
    $loginForm          = 'admin';
    $passForm           = '';
    $adminNameForm      = 'Doe';
    $adminSurnameForm   = 'John';
    $adminPhoneForm    = $conf_def_property_list['administrator_phone']['default'];
    $adminEmailForm    = $conf_def_property_list['administrator_email']['default'];


    $contactNameForm     = '*not set*'; // This magic value is use to detect if the content is edit or not.
    $contactPhoneForm    = '*not set*'; // if <not set> is found, the data form admin are copied
    $contactEmailForm    = '*not set*'; // This tips  permit to  empty these fields
    
    
?>