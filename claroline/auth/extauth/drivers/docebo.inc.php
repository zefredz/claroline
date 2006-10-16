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


$authSourceName = 'docebo';
$authSourceType = 'DB';

// Define the Auth driver options

$extAuthOptionList = array(

   // PUT HERE THE CORRECT DSN FOR YOUR DB SYSTEM
    'dsn'         => 'mysql://dbuser:dbpassword@domain/docebo',

    'table'       => 'do_user', // warning ! table prefix can change from one system to another 
    'usernamecol' => 'userid',
    'passwordcol' => 'pass',
    'db_fields'   => array('surname', 'name', 'email'),
    'cryptType'   => 'md5'
);


// Link additionnal external authentication attributes to the Claroline 
// user attribute.
//
// array KEYS   are the Claroline attributes and
// array VALUES are the authentication external attributes.

$extAuthAttribNameList = array (
    'lastname'  => 'name',
    'firstname' => 'surname',
    'email'     => 'email',
);

// Array setting optionnal preliminary treatment to the data retrieved from the 
// exernal authentication source. Array KEYS are the concernend claroline 
// user table fields, and Array VALUES are either the name of a function which 
// makes the treatment or simply a default value to insert
// Note. Treatments doesn't necessary previously require data from the external 
// authentication system. They're able to be trigged from NULL value ...

$extAuthAttribTreatmentList = array ('status' => 5);

// PROCESS AUTHENTICATION

return require dirname(__FILE__).'/../extAuthProcess.inc.php';


?>