<?php // $Id$
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
$conf_def['config_code']  = 'CLSSO';
$conf_def['config_file']  = 'auth.sso.conf.php';
$conf_def['config_name']  = 'Single Sign On';
$conf_def['config_class'] = 'auth';

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
      ,'default'       => FALSE
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