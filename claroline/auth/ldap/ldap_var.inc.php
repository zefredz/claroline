<? # $Id$ 

/*
=======================================================================
	LDAP settings

	In the older code, there was a distinction between
	the teacher and student LDAP server. Later I decided not
	to make this distinction. However, it could be built in 
	in the future but then perhaps in a more general way.

	Originally, Thomas and I agreed to store all settings in one file
	(claro_main.conf.php) to make it easier for claroline admins to make changes.
	Since October 2003, this changed: the include directory has been
	changed to be called "inc", and all tools should have their own file(s).

	This file "ldap_var.inc" was already used by the 
	older french authentification functions. I have moved the new
	variables from the claro_main.conf.php to here as well.

	Roan Embrechts, October 2003
=======================================================================
*/

//parameters for LDAP module
$usesLDAP						=	TRUE;
$usesCurriculum					=	FALSE;
$ldaphost = "myldapserver.com";  // your ldap server
$ldapport = 389;                 // your ldap server's port number
$ldapDc = "dc=xx, dc=yy, dc=zz"; //domain

//older variables for French Univ. Jean Monet code

// Variable pour l'annuaire LDAP Enseignant  
$LDAPservEns = $ldaphost;  
$LDAPportEns = $ldapport;  
$LDAPbasednEns = $ldapDc;  

// Variable pour l'annuaire LDAP Enseignant  
$LDAPservEtu = $ldaphost;  
$LDAPportEtu = $ldapport;  
$LDAPbasednEtu = $ldapDc;  

$critereRechercheEtu = "employeeType";

?>