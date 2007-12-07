<?php
/* >>>>>>>>>>>>>>>>>>>>>>>>> LDAP module <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<< */

include ("$includePath/../auth/ldap/ldap_var.inc.php");

/*
=======================================================================
	LDAP routines
	if the system uses LDAP, these functions are used
	for logging in, searching user info, adding this info
	to the claroline database...
=======================================================================
	- function loginWithLdap($login, $password)
	- function findUserInfoInLdap ($login)
	- function putUserInfoInClaroline ($login, $infoArray)

	known bugs
	----------
	- (fixed 18 june 2003) code has been internationalized
	- (fixed 07/05/2003) fixed some non-relative urls or includes
	- (fixed 28/04/2003) we now use global config.inc variables instead of local ones
	- (fixed 22/04/2003) the last name of a user was restricted to the first part
	- (fixed 11/04/2003) the user was never registered as a course manager

	version history
	---------------
	3.0	- updated to use ldap_var.inc.php instead of ldap_var.inc (deprecated)
		(November 2003)
	2.9	- further changes for new login procedure
		- (busy) translating french functions to english
		(October 2003)
	2.8	- adapted for new Claroline login procedure
		- ldap package now becomes a standard, in auth/ldap
	2.7 - uses more standard LDAP field names: mail, sn, givenname
			instead of mail, preferredsn, preferredgivenname
			there are still
		- code cleanup
		- fixed bug: dc = xx, dc = yy was configured for UGent
			and put literally in the code, this is now a variable
			in claro_main.conf.php ($ldapDc)

	*** LDAP development version v3.0 ***
	*** Roan Embrechts, University of Ghent ***
	*** roan.embrechts@ugent.be or roan_embrechts@yahoo.com ***

	with thanks to
	- Stefan De Wannemacker (University of Ghent)
	- Université Jean Monet (J Dubois / Michel Courbon)
*/

	/*
	----------------------------------------------------
		Necessary constants for the LDAP functions
	----------------------------------------------------
		These should be placed in the claro_main.conf.php.
		done: $usesLDAP, $ldaphost, $ldapport, $usesCurriculum --> config.inc
	*/
	$PLACEHOLDER = "PLACEHOLDER";

	//debug:
	//echo ($usesLDAP == false) ? "LDAP disabled<br>" : "LDAP enabled<br>";

	/**
	===============================================================
		function
		CHECK LOGIN & PASSWORD WITH LDAP
		returns true when login & password both OK.
		returns false otherwise
	===============================================================
		@author Roan Embrechts, based on code from Université Jean Monet
	*/
	//include_once("$includePath/../connect/authldap.php");

	function loginWithLdap($login, $password)
	{
		$res = Authentif($login, $password);

		// res=-1 -> the user does not exist in the ldap database
		// res=1 -> invalid password (user does exist)

		if ($res==1) //WRONG PASSWORD
		{
			//$errorMessage = "LDAP Username or password incorrect, please try again.<br>";
			unset($log); unset($uid);
			$loginLdapSucces = false;
		}
		if ($res==-1) //WRONG USERNAME
		{
			//$errorMessage =  "LDAP Username or password incorrect, please try again.<br>";
			$loginLdapSucces = false;
		}
		if ($res==0) //LOGIN & PASSWORD OK - SUCCES
		{
			//$errorMessage = "Successful login w/ LDAP.<br>";
			$loginLdapSucces = true;
		}

		//$result = "This is the result: $errorMessage";
		$result = $loginLdapSucces;
		return $result;
	}


	/**
	===============================================================
		function
		FIND USER INFO IN LDAP
		returns an array with positions
		"firstname", "name", "email", "employeenumber"
	===============================================================
		@author Stefan De Wannemacker
		@author Roan Embrechts
	*/
	function findUserInfoInLdap ($login)
	{
		global $ldaphost, $ldapport, $ldapDc;
		// basic sequence with LDAP is connect, bind, search,
		// interpret search result, close connection

		// using ldap bind
		$ldaprdn  = 'uname';     // ldap rdn or dn
		$ldappass = 'password';  // associated password

		//echo "<h3>LDAP query</h3>";
		//echo "Connecting ...";
		$ldapconnect = ldap_connect( $ldaphost, $ldapport);
		if ($ldapconnect) {
		    	//echo " Connect to LDAP server successful ";
		    	//echo "Binding ...";

				// this is an "anonymous" bind, typically read-only access:
		    	$ldapbind = ldap_bind($ldapconnect);

		    	if ($ldapbind)
				{
		  	  	//echo " LDAP bind successful... ";
		    	  	//echo " Searching for uid... ";
		    		// Search surname entry
		    		//OLD: $sr=ldap_search($ldapconnect,"dc=rug, dc=ac, dc=be", "uid=$login");
					//echo "<p> ldapDc = '$ldapDc' </p>";
		    		$sr=ldap_search($ldapconnect, $ldapDc, "uid=$login");

					//echo " Search result is ".$sr;
		    		//echo " Number of entries returned is ".ldap_count_entries($ldapconnect,$sr);

		    		//echo " Getting entries ...";
		    		$info = ldap_get_entries($ldapconnect, $sr);
		    		//echo "Data for ".$info["count"]." items returned:<p>";

		    	}
			else
			{
				//echo "LDAP bind failed...";
		    }
	    	//echo "Closing LDAP connection<hr>";
	    	ldap_close($ldapconnect);
		}
		else
		{
			//echo "<h3>Unable to connect to LDAP server</h3>";
		}

		//DEBUG: $result["firstname"] = "Jan"; $result["name"] = "De Test"; $result["email"] = "email@ugent.be";
		$result["firstname"] = $info[0]["givenname"][0];
		$result["name"] = $info[0]["sn"][0];
		$result["email"] = $info[0]["mail"][0];
		$result["employeenumber"] = $info[0]["employeenumber"][0];

		return $result;
	}


	/**
	===============================================================
		function
		PUT USER INFO IN CLAROLINE
		this function uses the data from findUserInfoInLdap()
		to add the userdata to Claroline

		this function does not return any result,
		it adds elements to the $_POST array

		the "rugid" field is specifically for the university of ghent.

		"firstname", "name", "email", "isEmployee"
	===============================================================
		@author Roan Embrechts
	*/
	function putUserInfoInClaroline ($login, $infoArray)
	{
		global $_POST;
		global $PLACEHOLDER;
		global $submitRegistration, $submit, $uname, $email,
				$nom, $prenom, $password, $password1, $statut;
		global $includePath, $platformLanguage;
		global $loginFailed, $uidReset, $_uid;

		/*----------------------------------------------------------
			1. set the necessary variables
		------------------------------------------------------------ */

		$uname      = $login;
		$email      = $infoArray["email"];
		$nom        = $infoArray["name"];
		$prenom     = $infoArray["firstname"];
		$password   = $PLACEHOLDER;
		$password1  = $PLACEHOLDER;

		define ("STUDENT",5);
		define ("COURSEMANAGER",1);

		if (empty($infoArray["employeenumber"]))
		{
			$statut = STUDENT;
		}
		else
		{
			$statut = COURSEMANAGER;
		}

		//$officialCode = xxx; //example: choose an attribute

		/*----------------------------------------------------------
			2. add info to claroline
		------------------------------------------------------------ */
		//header('location:'. "claroline/auth/inscription_second.php");
		//exit;

		include_once("$includePath/lib/userManage.lib.php");

		$_userId = create_new_user($prenom, $nom, $statut,
						 $email, $uname, $password, $officialCode,
						 '', '', 'ldap');

		//echo "new user added to claroline, id = $_userId";

		//user_id, username, password, authSource

		/*----------------------------------------------------------
			3. register session
		------------------------------------------------------------ */

		$uData['user_id'] = $_userId;
		$uData['username'] = $uname;
		$uData['authSource'] = "ldap";

		$loginFailed = false;
		$uidReset = true;
		$_uid = $uData['user_id'];
		session_register('_uid');
	}

/* >>>>>>>>>>>>>>>> end of UGent LDAP routines <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<< */

/* >>>>> Older but necessary code of Université Jean-Monet <<<<< */

/*
===========================================================
	The code of UGent uses these functions to authenticate.
	* function AuthVerifEnseignant ($uname, $passwd)
	* function AuthVerifEtudiant ($uname, $passwd)
	* function Authentif ($uname, $passwd)
===========================================================
	To Do
	* translate the comments and code to english
	* let these functions use the variables in config.inc instead of ldap_var.inc
*/

//*** variables en entrée
// $uname : username entré au clavier
// $passwd : password fournit par l'utilisateur

//*** en sortie : 3 valeurs possibles
// 0 -> authentif réussie
// 1 -> password incorrect
// -1 -> ne fait partie du LDAP

//---------------------------------------------------
// verification de l'existence dans le LDAP Enseignant
function AuthVerifEnseignant ($uname, $passwd)
{
	global $LDAPservEns, $LDAPservEns, $LDAPbasednEns;
	// Establish anonymous connection with LDAP server
	// Etablissement de la connexion anonyme avec le serveur LDAP
	 $ds=ldap_connect($LDAPservEns,$LDAPportEns);
	 if ($ds) {
		// Creation du filtre contenant les valeurs saisies par l'utilisateur
	    $filter="(uid=$uname)";
		// Open anonymous LDAP connection
		// Ouverture de la connection anonyme ldap
	    $result=ldap_bind($ds);
		// Execution de la recherche avec $filtre en parametre
		  $sr=ldap_search($ds,"$LDAPbasednEns", "$filter");
		// La variable $info recoit le resultat de la requete
		  $info = ldap_get_entries($ds, $sr);
		  $dn=($info[0]["dn"]);
		//affichage debug !!	echo"<br> dn = $dn<br> pass = $passwd<br>";
		// fermeture de la 1ere connexion
			ldap_close($ds);
		}

	// teste le Distinguish Name de la 1ere connection
	  if ($dn==""){
			 return (-1);		// ne fait pas partie de l'annuaire
		}
 //bug ldap.. si password vide.. retourne vrai !!
	if ($passwd=="") {
		 return(1);
		 }
	// Ouverture de la 2em connection Ldap : connexion user pour verif mot de passe
	 $ds=ldap_connect($LDAPservEns,$LDAPportEns);
	// retour en cas d'erreur de connexion password incorrecte
	 if (!(@ldap_bind( $ds, $dn , $passwd)) == true) {
		 return (1); // mot passe invalide
		 }
	// connection correcte
	else	{
		return (0);
	}

} // fin de la verif enseignant

//---------------------------------------------------
// verification de l'existence dans le LDAP Etudiant

function AuthVerifEtudiant ($uname, $passwd)
{
	global $LDAPservEtu, $LDAPservEtu, $LDAPbasednEtu;
	 // Etablissement de la connexion anonyme avec le serveur LDAP
	 $ds=ldap_connect($LDAPservEtu,$LDAPportEtu);
	 if ($ds) {
		// Creation du filtre contenant les valeurs saisies par l'utilisateur
	    $filter="(uid=$uname)";
		// Ouverture de la connection anonyme ldap
	    $result=ldap_bind($ds);
		// Execution de la recherche avec $filtre en parametre
		  $sr=ldap_search($ds,"$LDAPbasednEtu", "$filter");
		// La variable $info recoit le resultat de la requete
		  $info = ldap_get_entries($ds, $sr);
		  $dn=($info[0]["dn"]);
		// fermeture de la 1ere connexion
			 ldap_close($ds);
		}
	// teste le Distinguish Name de la 1ere connection
	  if ($dn==""){
			 return (-1);		// ne fait pas partie de l'annuaire
		}
 //bug ldap.. si password vide.. retourne vrai !!
	if ($passwd=="") {
		 return(1);
		 }
	// Ouverture de la 2em connection Ldap : connexion user pour verif mot de passe
	 $ds=ldap_connect($LDAPservEtu,$LDAPportEtu);
	// retour en cas d'erreur de connexion password incorrecte
	if (!(@ldap_bind( $ds, $dn , $passwd)) == true) {
		 return (1); // mot passe invalide
		 }
	// connection correcte
	else	{
		ldap_close($ds);
		return (0);
	}

} // fin de la verif etudiant

//-------------------------------------------------------
//  authentification

function Authentif ($uname, $passwd)
{
if ( ($res=AuthVerifEnseignant($uname,$passwd)) >= 0) {
	 return($res); // fait partie du LDAP enseignant
	 }
else {
	return(AuthVerifEtudiant($uname,$passwd));
} // fin else

} // fin Authentif

?>