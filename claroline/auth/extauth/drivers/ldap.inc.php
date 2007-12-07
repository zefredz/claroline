<?php # -$Id$

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


// Define the Auth driver options 

$authSourceName = 'ldap';
$authSourceType = 'LDAP';


$extAuthOptionList = array(
    'url'      => 'ldap://server_address',
    'port'     => '636',
    'basedn'   => 'ou=personne,o=your organisation unit,c=domain',
    'userattr' => 'uid',
    'useroc'   => 'person',
    'attributes' => array('sn', 'givenName', 'telephoneNumber','mail'),
);


// Link external authentication attributes to the Claroline user attribute.
// The keys are the claroline attributes and the value are the authentication 
// external attributes.
//
// Note. If the attribute isn't found in the external authentication results,
// the external attribute NAME would be record as claroline attribute VALUE 
// user table. It means that you can substitute a default value to the attribute 
// name.

$extAuthAttribNameList = array (
    'lastname'     => 'sn',
    'firstname'    => 'givenName',
    'email'        => 'mail',
    'phoneNumber'  => 'telephoneNumber',
    'authSource'   => 'ldap'
);

    

// Array setting optionnal preliminary treatment to the data retrieved from the 
// exernal authentication source. The array keys are the concernend claroline 
// user table fields, ans the values are the name of the function which make 
// the treatment You can use standart PHP functions or functions defined by 
// your own. If no function named like the value are found, the authentication 
// system will simply stored thisvalue into Claroline.

$extAuthAttribTreatmentList = array (
    'lastname'     => 'utf8_decode',
    'firstname'    => 'utf8_decode',
    'loginName'    => 'utf8_decode',
    'email'        => 'utf8_decode',
    'officialCode' => 'utf8_decode',
    'phoneNumber'  => 'utf8_decode',
    'status'       => 5
);

// PROCESS AUTHENTICATION

return require $clarolineRepositorySys.'/auth/extauth/extAuthProcess.inc.php';

?>