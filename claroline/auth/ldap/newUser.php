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
==================================================
	when a user does not exist yet in claroline, 
	but he or she does exist in the LDAP,
	we add him to the claroline database
==================================================
*/

include_once("./claroline/auth/ldap/authldap.php");

$loginLdapSucces = loginWithLdap($login, $password);	

if ($loginLdapSucces)
{
	/*
		In here, we know that
		- the user does not exist in Claroline
		- the users login and password are correct
	*/
	$infoArray = findUserInfoInLdap($login);
	putUserInfoInClaroline ($login, $infoArray);
}
else
{
	$loginFailed = true;
	unset($_uid);
	$uidReset = false;
}
?>