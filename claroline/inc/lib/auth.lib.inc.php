<?php // $Id$
 /*
	  +----------------------------------------------------------------------+
	  | CLAROLINE version 1.3.0 Lib for auth Upload $Revision$		 |
	  +----------------------------------------------------------------------+
	  | Copyright (c) 2001, 2002 Universite catholique de Louvain (UCL)	  |
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
