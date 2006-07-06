<?php // $Id$
if ( count( get_included_files() ) == 1 ) die( '---' );
/**
 * CLAROLINE
 *
 * This file describe the parameter for user tool
 *
 * @version 1.8 $Revision$
 *
 * @copyright 2001-2006 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @see http://www.claroline.net/wiki/index.php/Config
 *
 * @author Claro Team <cvs@claroline.net>
 *
 * @package CLUSR
 *
 */
// TOOL
$conf_def['config_code'] = 'CLAUTH';
$conf_def['config_file'] = 'auth.extra.conf.php';
$conf_def['config_name'] = 'auth tool';
$conf_def['config_class']='kernel';


//SECTION
$conf_def['section']['main']['label']='Main settings';
//$conf_def['section']['main']['description']='Settings of the tool';
$conf_def['section']['main']['properties'] =
array ( 'claro_authUsernameCaseSensitive'
      , 'claro_displayLocalAuthForm'
      );

$conf_def['section']['CAS']['label']='Cas settings';
$conf_def['section']['CAS']['description']='Centralized Authentication System';
$conf_def['section']['CAS']['properties'] =
array ( 'claro_CasEnabled'
      , 'claro_CasServerHostUrl'
      , 'claro_CasServerHostPort'
      , 'claro_CasLibPath'
      , 'claro_CasProcessPath'
      , 'claro_CasLoginString'
      );

$conf_def['section']['SSO']['label']='SSO settings';
$conf_def['section']['SSO']['description']='Once a user logs to the Claroline platform a cookie is sent to the user browser if the authentication process succeeds. The cookie value is also stored in a internal table of the Claroline platform for a certain time. If requested, the Claroline SSO server provides a way a way to retrieve the user parameters from another server on the internet on the base of this cookie value.';
$conf_def['section']['SSO']['properties'] =
array ( 'ssoEnabled'
      , 'ssoCookieName'
      , 'ssoCookiePeriodValidity'
      , 'ssoCookieDomain'
      , 'ssoCookiePath'
      , 'ssoAuthenticationKeyList'
      );

//PROPERTIES

$conf_def_property_list['claro_authUsernameCaseSensitive'] =
array ( 'label'         => 'Auth username is Case Sensitive'
      //'description'   => ''
      , 'default'       => 'TRUE'
      ,'type'          => 'boolean'
      ,'acceptedValue' => array ('TRUE'  => 'Yes'
                                ,'FALSE' => 'No'
                                )
      );

$conf_def_property_list['claro_displayLocalAuthForm'] =
array ('label'         => 'Display Local Auth Form'
      ,'description'   => ''
      ,'default'       => 'TRUE'
      ,'type'          => 'boolean'
      ,'acceptedValue' => array ('TRUE'  => 'Yes'
                                ,'FALSE' => 'No'
                                )
      );

$conf_def_property_list['claro_CasEnabled'] =
array ('label'         => 'Enable Cas system'
      ,'description'   => 'if false, other field are optional'
      ,'default'       => 'FALSE'
      ,'type'          => 'boolean'
      ,'acceptedValue' => array ('TRUE'  => 'Enabled'
                                ,'FALSE' => 'Disabled'
                                )
      );

$conf_def_property_list['claro_CasServerHostUrl'] =
array ('label'         => 'Url host of CAS server'
      //,'description'   => ''
      ,'default'       => 'my.cas.server.domain.com'
      ,'type'          => 'string'
      );

$conf_def_property_list['claro_CasLoginString'] =
array ('label'         => 'Label of link to cas login'
      //,'description'   => ''
      ,'default'       => 'Magic Login'
      ,'type'          => 'string'
      );


$conf_def_property_list['claro_CasServerHostPort'] =
array ('label'         => 'port of CAS server'
      //,'description'   => ''
      ,'default'       => '443'
      ,'type'          => 'integer'
      );

$conf_def_property_list['claro_CasLibPath'] =
array ('label'         => 'CAS lib path'
      ,'display'       => false
      ,'default'       => $GLOBALS['includePath'].'/lib/cas/CAS.php'
      ,'type'          => 'sysPath'
      );


$conf_def_property_list['claro_CasProcessPath'] =
array ('label'         => 'casProcess path'
      ,'display'       => false
      ,'default'       => $GLOBALS['clarolineRepositorySys'] . '/auth/extauth/casProcess.inc.php'
      ,'type'          => 'sysPath'
      );





// ---------------------------------------
// CLAROLINE SINGLE SIGN ON (SSO) SECTION
// ---------------------------------------

/**
 * SINGLE SIGN ON (SSO)
 *
 * Once a user logs to the Claroline platform a cookie is sent to the
 * user browser if the authentication process succeeds. The cookie value
 * is also stored in a internal table of the Claroline platform for a certain
 * time.
 *
 * If requested, the Claroline SSO server provides a way a way to retrieve
 * the user parameters from another server on the internet on the base of this
 * cookie value.
 */


// SSO ENABLED. Enable the Claroline SSO system.
// Set this parameter to TRUE if you want to enable SSO.


$conf_def_property_list['ssoEnabled'] =
array ('label'         => 'Enable SSO system'
      ,'description'   => 'if false, other field are optional'
      ,'default'       => 'FALSE'
      ,'type'          => 'boolean'
      ,'acceptedValue' => array ('TRUE'  => 'Enabled'
                                ,'FALSE' => 'Disabled'
                                )
      );


$conf_def_property_list['ssoCookieName'] =
array ('label'         => 'sso cookie name'
      ,'description'   => 'The name of the cookie the Claroline platform has set into the user browser. By default this name is "clarolineSsoCookie". But it can be changed by the Claroline platform administrator.'
      ,'default'       => 'clarolineSsoCookie'
      ,'type'          => 'string'
      );


$conf_def_property_list['ssoCookiePeriodValidity'] =
array ('label'         => 'sso cookie period validity'
      ,'description'   => 'Number of seconds before before the cookie to expire'
      ,'default'       => '3600'
      ,'type'          => 'integer'
      );


$conf_def_property_list['ssoCookieDomain'] =
array ('label'         => 'sso cookie domain'
      ,'description'   => 'The domain that the cookie is available.  To make
the cookie available on all subdomains of example.com then you\'d set it to ".example.com". The . is not required but makes it compatible with more browsers. Setting it to www.example.com  will make the cookie only available in the www  subdomain.'
      ,'default'       => 'www.my.domain.com'
      ,'type'          => 'string'
      );


$conf_def_property_list['ssoCookiePath'] =
array ( 'label'         => 'sso cookie path'
      , 'description'   => 'The path on the server in which the cookie will be available on.  If set to "/", the cookie will be available within the entire domain. If set to "/foo/", the cookie will only be available within the /foo/ directory and all sub-directories such as /foo/bar/ of domain. The default value is the current directory that the cookie is being set in.'
      , 'default'       => '/'
      , 'type'          => 'relPath'
      );

$conf_def_property_list['ssoAuthenticationKeyList'] =
array ( 'label'         => 'sso authentication key list'
      , 'description'   => 'A list of keys allowing requests to the Claroline SSO server. The SSO client have to provide one of the keys contained into this list to receive any answer from the Claroline SSO server.'
      , 'default'       => 'array()'
      , 'type'          => 'string'
      );

?>