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

    $dbHostForm     = "localhost";
    $dbUsernameForm = "root";

    $dbPrefixForm   = "";
    $dbNameForm     = $dbPrefixForm."claroline";
    $dbStatsForm    = $dbPrefixForm."claroline";
    $dbPrefixForm   = $dbPrefixForm."c_";
    $mainTblPrefixForm  = "cl_";
    $statsTblPrefixForm = "cl_";

    $singleDbForm   = true;
    $enableTrackingForm = true;
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

    $courseRepositoryForm = "courses/";

    $adminEmailForm     = '';//$_SERVER['SERVER_ADMIN'];

    $adminNameForm      = 'Doe';
    $adminSurnameForm   = 'John';
    $adminPhoneForm    = '(00) 1-23 456 789';
    $adminEmailForm    = ''; //

    $loginForm          = 'admin';
    $passForm           = '';

    $campusForm          = 'My campus';
    $contactNameForm     = '*not set*'; // This magic value is use to detect if the content is edit or not.
    $contactPhoneForm    = '*not set*'; // if <not set> is found, the data form admin are copied
    $contactEmailForm    = '*not set*'; // This tips  permit to  empty these fields
    $institutionForm     = '';
    $institutionUrlForm  = '';

    $languageForm = 'english';

    $checkEmailByHashSent           = FALSE;
    $ShowEmailnotcheckedToStudent   = TRUE;
    $userMailCanBeEmpty             = TRUE;
    $userPasswordCrypted            = FALSE;

    $allowSelfReg = TRUE;
    $allowSelfRegProf = TRUE;


?>
