<?php # $Id$

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

	/*
		An external authentification module
		needs to set
		- $loginFailed
		- $uidReset
		- $_uid
		- register the $_uid in the session
		As the LDAP code shows, this is not as difficult as you might think.
	*/
	/*
	===============================================
		LDAP authentification module
		this calls the loginWithLdap function
		from the LDAP library, and sets a few 
		variables based on the result.
	===============================================
	*/
	include_once("./claroline/auth/ldap/authldap.php");

	$loginLdapSucces = loginWithLdap($login, $password);	

	if ($loginLdapSucces)
	{
		$loginFailed = false;
		$uidReset = true;
		$_uid = $uData['user_id'];
		session_register('_uid');
	}
	else
	{
		$loginFailed = true;
		unset($_uid);
    	$uidReset = false;
	}
?>