<?php // $Id$
if ( count( get_included_files() ) == 1 ) die( '---' );
/**
 * CLAROLINE
 *
 * @version 1.8 $Revision$
 *
 * @copyright (c) 2001-2006 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @package CLAUTH
 *
 * @author Claro Team <cvs@claroline.net>
 */

$authSourceName = 'spip';
$authSourceType = 'DB';

// Define the Auth driver options

$extAuthOptionList = array(

    // PUT HERE THE CORRECT DSN FOR YOUR DB SYSTEM
    'dsn'         => 'mysql://root:@localhost/spip', 

    'table'       => 'spip_auteurs', // warning ! table prefix can change from one system to another 
    'usernamecol' => 'login',
    'passwordcol' => 'pass',
    'db_fields'   => array('nom', 'email', 'statut'),
    'cryptType'   => 'md5'
);


// Link additionnal external authentication attributes to the Claroline 
// user attribute.
//
// array KEYS   are the Claroline attributes and
// array VALUES are the authentication external attributes.

$extAuthAttribNameList = array (
    'lastname'     => 'nom',
    'email'        => 'email',
    'status'       => 'statut',
);

// Array setting optionnal preliminary treatment to the data retrieved from the 
// exernal authentication source. Array KEYS are the concernend claroline 
// user table fields, and Array VALUES are either the name of a function which 
// makes the treatment or simply a default value to insert
// Note. Treatments doesn't necessary previously require data from the external 
// authentication system. They're able to be trigged from NULL value ...

$extAuthAttribTreatmentList = array ('status' => 'manage_user_status_from_spip_to_claroline');


function manage_user_status_from_spip_to_claroline($spipStatus)
{
    $spipStatus = (int) $spipStatus;

    switch ($spipStatus)
    {
        case 0:         // spip administrator
            return 1;   // claroline course manager
            break;
        case 1:         // spip writer
            return 5;   // claroline student
            break;
        case 5:         // spip trashed user
            die('<center>user not allowed</center>');
            break;
        case 6:         // spip forum user
            return 5;   // claroline student
            break;
        default:
            return 5;   // claroline student
    }
}


// The trick below is necessary to make SPIP working with the Pear Auth library
// The password system of SPIP is a bit special. There are two fields in the 
// 'spip_auteur' table : 'pass' and 'alea_atuel'. The password validation is 
// based on the following sentence.
// 
//      $db_pass == md5($db_alea_actuel . $post_password);
//
// That's wy we need to connect first to the SPIP DB and retrieve the 
// 'alea_actuel' beforehand.

require_once 'DB.php';
$dbh = DB::connect($extAuthOptionList['dsn']);
$sql = "SELECT alea_actuel 
        FROM   spip_auteurs 
        WHERE  login ='".$GLOBALS['login']."'";

$spipAleaField = $dbh->getOne($sql);

$GLOBALS['password']  = $spipAleaField.$GLOBALS['password'];

// PROCESS AUTHENTICATION

return require dirname(__FILE__).'/../extAuthProcess.inc.php';


?>