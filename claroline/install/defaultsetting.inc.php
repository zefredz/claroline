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

// 	<input type=\"hidden\" name=\"dbHostForm\" value=\"$dbHostForm\">
	$dbHostForm		= "localhost";
// 	<input type=\"hidden\" name=\"dbUsernameForm\" value=\"$dbUsernameForm\">
	$dbUsernameForm	= "root";

// 	<input type=\"hidden\" name=\"dbMyAdmin\" value=\"$dbMyAdmin\">
// 	<input type=\"hidden\" name=\"dbPassForm\" value=\"$dbPassForm\">

// 	<input type=\"hidden\" name=\"dbPrefixForm\" value=\"$dbPrefixForm\">
	$dbPrefixForm	= "";
// 	<input type=\"hidden\" name=\"dbNameForm\" value=\"$dbNameForm\">
	$dbNameForm		= $dbPrefixForm."claroline";
//  <input type=\"hidden\" name=\"dbStatsForm\" value=\"$dbStatsForm\">
	$dbStatsForm    = $dbPrefixForm."claroline";
	$dbPrefixForm	= $dbPrefixForm."c_";

//	<input type=\"hidden\" name=\"singleDbForm\" value=\"".$singleDbForm."\">
 	$singleDbForm	= true;
//  <input type=\"hidden\" name=\"enableTrackingForm\" value=\"$enableTrackingForm\">
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
    $urlAppendPath 	= preg_replace ($ereg, '', $urlAppendPath);
// 	<input type=\"hidden\" name=\"urlForm\" value=\"$urlForm\">
  	$urlForm 		= "http://".$_SERVER['SERVER_NAME'].$urlAppendPath."/";
	$pathForm		= realpath("../..")."/";

// 			<input type=\"hidden\" name=\"urlAppendPath\" value=\"$urlAppendPath\">
// 			<input type=\"hidden\" name=\"urlEndForm\" value=\"$urlEndForm\">
// 			<input type=\"hidden\" name=\"pathForm\" value=\"".str_replace("\\","/",realpath($pathForm)."/")."\" >

	$adminEmailForm		= '';//$_SERVER['SERVER_ADMIN'];

// 	<input type=\"hidden\" name=\"adminNameForm\" value=\"$adminNameForm\">
	$adminNameForm		= 'Doe';
// 	<input type=\"hidden\" name=\"adminSurnameForm\" value=\"$adminSurnameForm\">
	$adminSurnameForm	= 'John';
// 	<input type=\"hidden\" name=\"adminPhoneForm\" value=\"$adminPhoneForm\">
	$adminPhoneForm    = '(00) 1-23 456 789';
// 	<input type=\"hidden\" name=\"adminEmailForm\" value=\"$adminEmailForm\">
	$adminEmailForm    = ''; // 

//  <input type=\"hidden\" name=\"loginForm\" value=\"$loginForm\">
	$loginForm		    = 'admin';
// 	<input type=\"hidden\" name=\"passForm\" value=\"$passForm\">
	$passForm  		    = '';

// 	<input type=\"hidden\" name=\"campusForm\" value=\"$campusForm\">
	$campusForm		     = 'My campus';
// 	<input type=\"hidden\" name=\"contactNameForm\" value=\"$contactNameForm\">
	$contactNameForm     = '*notSet*'; // This magic value is use to detect if the content is edit or not.
// 	<input type=\"hidden\" name=\"contactPhoneForm\" value=\"$contactPhoneForm\">
	$contactPhoneForm    = '*notSet*'; // if *notSet* is found, the data form admin are copied
// 	<input type=\"hidden\" name=\"contactEmailForm\" value=\"$contactEmailForm\">
	$contactEmailForm    = '*notSet*'; // This tips  permit to  empty these fields
// 	<input type=\"hidden\" name=\"institutionForm\" value=\"$institutionForm\">
	$institutionForm     = '';
//	<input type=\"hidden\" name=\"institutionUrlForm\" value=\"$institutionUrlForm\">
	$institutionUrlForm  = '';
	$urlEndForm		     = 'mydir/';

// 	<input type=\"hidden\" name=\"languageForm\" value=\"$languageForm\">
	$languageForm = 'english';

// 	<input type=\"hidden\" name=\"checkEmailByHashSent\" value=\"$checkEmailByHashSent\">
	$checkEmailByHashSent 			= false;
// 	<input type=\"hidden\" name=\"ShowEmailnotcheckedToStudent\" value=\"$ShowEmailnotcheckedToStudent\">
	$ShowEmailnotcheckedToStudent 	= true;
// 	<input type=\"hidden\" name=\"userMailCanBeEmpty\" value=\"$userMailCanBeEmpty\">
	$userMailCanBeEmpty 			= true;
// 	<input type=\"hidden\" name=\"userPasswordCrypted\" value=\"$userPasswordCrypted\">
	$userPasswordCrypted 			= false;
// 	<input type=\"hidden\" name=\"encryptPassForm\" value=\"$encryptPassForm\">

//  <input type=\"hidden\" name=\"allowSelfReg\" value=\"$allowSelfReg\">
	$allowSelfReg = true;
// 	<input type=\"hidden\" name=\"allowSelfRegProf\" value=\"$allowSelfRegProf\">
	$allowSelfRegProf = true;
 
// 	<input type=\"hidden\" name=\"confirmUseExistingMainDb\" value=\"$confirmUseExistingMainDb\">
// 	<input type=\"hidden\" name=\"confirmUseExistingStatsDb\" value=\"$confirmUseExistingStatsDb\">


?>