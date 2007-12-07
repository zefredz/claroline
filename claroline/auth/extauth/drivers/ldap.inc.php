<?php // $Id$
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
    'attrformat' => 'AUTH_LDAP_ATTR_AUTH_STYLE',
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
    'lastname'     => 'ldap_to_claroline',
    'firstname'    => 'ldap_to_claroline',
    'loginName'    => 'ldap_to_claroline',
    'email'        => 'ldap_to_claroline',
    'officialCode' => 'ldap_to_claroline',
    'phoneNumber'  => 'ldap_to_claroline',
    'status'       => 5
);


function ldap_to_claroline($attribute)
{
    if ( is_array( $attribute ) ) $attribute = implode(', ', $attribute);
    return utf8_decode($attribute);
}


// PROCESS AUTHENTICATION

return require $clarolineRepositorySys.'/auth/extauth/extAuthProcess.inc.php';

?>