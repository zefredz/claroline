<?php // $Id$
if ( ! defined('CLARO_INCLUDE_ALLOWED') ) die('---');
/**
 * CLAROLINE
 *
 * @version 1.7 $Revision$
 *
 * @copyright (c) 2001-2005 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @package CLAUTH
 *
 * @author Claro Team <cvs@claroline.net>
 */

/**
 * generates randomly a new password
 *
 * @author Damien Seguy
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

    srand((double)microtime() * 20001107);

    while(strlen($retour) < $nb)
    {
        // To generate the password string we follow these rules : (1) If two
        // letters are consonnance (vowel), the following one have to be a vowel
        // (consonnace) - (2) If letters are from different type, we choose a
        // letter from the alphabet.

        $type     = ($precprec + $prec) / 2;
        $r        = $lettre[$type][array_rand($lettre[$type], 1)];
        $retour  .= $r;
        $precprec = $prec;
        $prec     = in_array($r, $lettre[-1]) - in_array($r, $lettre[1]);

    }
    return $retour;
}

/**
 * Check an email
 * @version 1.0
 * @param  integer    $email email to check
 * @return boolean state of validity.
 * @author Christophe Gesche <moosh@claroline.net>
 */

function is_well_formed_email_address($address)
{
    $regexp = '^[0-9a-z_\.-]+@(([0-9]{1,3}\.){3}[0-9]{1,3}|([0-9a-z][0-9a-z-]*[0-9a-z]\.)+[a-z]{2,4})$';

    //  $regexp = '^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$';
    return eregi($regexp, $address);
}
?>
