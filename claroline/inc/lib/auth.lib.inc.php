<?php // $Id$
 /*
	  +----------------------------------------------------------------------+
	  | CLAROLINE version 1.6
	  +----------------------------------------------------------------------+
	  | Copyright (c) 2001, 2004
	  +----------------------------------------------------------------------+
	  | Authors: Thomas Depraetere <depraetere@ipm.ucl.ac.be>				|
	  |		  Hugues Peeters	<peeters@ipm.ucl.ac.be>				   |
	  |		  Christophe Gesché <gesche@ipm.ucl.ac.be>					|
	  +----------------------------------------------------------------------+
 */

/**
 * Build a string without logic
 * to be used as password
 *
 * @author Christophe Gesche <gesche@ipm.ucl.ac.be>
 * @version 1.0
 * @param  integer	$nbcar 			default 5   	define here  length of password
 * @param  boolean	$lettresseules	default false	fix  if pass can content digit
 * @return string password
 * @desc return a string to be use as password
 * @see rand()
 * @package claro.auth.lib
 */

function generePass($nbcar=5,$lettresseules = false)
{ 
	$chaine = "abBDEFcdefghijkmnPQRSTUVWXYpqrst23456789"; //caractères possibles 
	if ($lettresseules) 
		$chaine = "abcdefghijklmnopqrstuvwxyzAZERTYUIOPMLKJHGFDSQWXCVBN"; //caractères possibles 
	for($i=0; $i<$nbcar; $i++) 
	{ 
		$pass .= $chaine[rand()%strlen($chaine)];//mot de passe 
	} 
	return $pass;
}


//////////////////////////////////////////////////////////////////////////////

/**
 * generates randomly a new password
 *
 * @author Damien Seguy
 * @param - void
 * @return string the new password
 */
 
function generate_passwd()
{
	if (func_num_args() == 1) $nb = func_get_arg(0);
	else                      $nb = 8;

	// on utilise certains chiffres : 1 = i, 5 = S, 6=b, 3=E, 9=G, 0=O

	$lettre = array();

	$lettre[0] = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 
	                   'j', 'k', 'l', 'm', 'o', 'n', 'p', 'q', 'r', 
	                   's', 't', 'u', 'v', 'w', 'x', 'y', 'z', 'A', 
	                   'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 
	                   'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'D', 
	                   'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', '9', 
	                   '0', '6', '5', '1', '3');

	$lettre[1] =  array('a', 'e', 'i', 'o', 'u', 'y', 'A', 'E', 
	                    'I', 'O', 'U', 'Y' , '1', '3', '0' );

	$lettre[-1] = array('b', 'c', 'd', 'f', 'g', 'h', 'j', 'k', 
	                    'l', 'm', 'n', 'p', 'q', 'r', 's', 't', 
	                    'v', 'w', 'x', 'z', 'B', 'C', 'D', 'F', 
	                    'G', 'H', 'J', 'K', 'L', 'M', 'N', 'P', 
	                    'Q', 'R', 'S', 'T', 'V', 'W', 'X', 'Z', 
	                    '5', '6', '9');

	$retour   = "";
	$prec     = 1;
	$precprec = -1;

	srand((double)microtime()*20001107);

	while(strlen($retour) < $nb)
	{
		// To generate the password string we follow these rules : (1) If two 
		// letters are consonnance (vowel), the following one have to be a vowel 
		// (consonnace) - (2) If letters are from different type, we choose a 
		// letter from the alphabet.

		$type     = ($precprec + $prec)/2;
		$r        = $lettre[$type][array_rand($lettre[$type], 1)];
		$retour  .= $r;
		$precprec = $prec;
		$prec     = in_array($r, $lettre[-1]) - in_array($r, $lettre[1]);

	}
	return $retour;
}



/**
 * Check if the password chosen by the user is not too much easy to find
 *
 * @author Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @param string requested password
 * @param array list of other values of the form we wnt to check the password
 *
 * @return boolean true if not too much easy to find
 *
 */
function is_password_secure_enough($requestedPassword, $forbiddenValueList)
{
    // Temporarly deactivated ...
    //
    // if (strlen($requestedPassword) < 8)
    // {
    //    return false;
    // }

    foreach($forbiddenValueList as $thisValue)
    {
        if( strtoupper($requestedPassword) == strtoupper($thisValue) )
        {
            return false;
        }
    }

    return true;
}


/**
 * Check an email
 * @author Christophe Gesche <gesche@ipm.ucl.ac.be>
 * @version 1.0
 * @param  integer	$email 			email to check
 * @return boolean state of validity.
 * @desc return true  if email  is  acceptable for claroline usage.
 * @package claro.auth.lib
 */

function is_well_formed_email_address($address)
{
	return eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$", $address);
}
?>
