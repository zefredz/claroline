<?php // $Id$

if ( count( get_included_files() ) == 1 ) die( '---' );

/**
 * CLAROLINE
 *
 * This file describe the parameter for user tool.
 *
 * @version     1.8 $Revision$
 * @copyright   (c) 2001-2011, Universite catholique de Louvain (UCL)
 * @license     http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @see         http://www.claroline.net/wiki/index.php/Config
 * @author      Claro Team <cvs@claroline.net>
 * @package     CLUSR
 */

// TOOL
$conf_def['config_code'] = 'CLAUTH';
$conf_def['config_file'] = 'auth.extra.conf.php';
$conf_def['config_name'] = 'Authentication';
$conf_def['config_class']='auth';


//SECTION
$conf_def['section']['main']['label']='Main settings';
//$conf_def['section']['main']['description']='Settings of the tool';
$conf_def['section']['main']['properties'] =
array ( 'claro_authUsernameCaseSensitive'
      , 'claro_displayLocalAuthForm'
      , 'claro_secureLogin'
      , 'claro_displayLostPasswordLink'
      , 'claro_loadDeprecatedPearAuthDriver'
      );

//PROPERTIES

$conf_def_property_list['claro_authUsernameCaseSensitive'] =
array ( 'label'         => 'The username is case sensitive'
      , 'description'   => 'Choose "No" if you use microsoft active directory (by default this authentication system is case-insensitive)'
      , 'default'       => true
      , 'type'          => 'boolean'
      , 'acceptedValue' => array ('TRUE'  => 'Yes'
                                ,'FALSE' => 'No'
                                )
      );


$conf_def_property_list['claro_displayLocalAuthForm'] =
array ('label'         => 'Display authentication login form'
      ,'description'   => 'If you are not using the local Claroline password to identify a user, disable this option so the access authentication form will not be available'
      ,'default'       => true
      ,'type'          => 'boolean'
      ,'acceptedValue' => array ('TRUE'  => 'Yes'
                                ,'FALSE' => 'No'
                                )
      );

$conf_def_property_list['claro_secureLogin'] =
array ('label'         => 'Use SSL secure connection for login'
      ,'description'   => 'You also need to configure your web server to allow SSL connections to the auth/login.php script !'
      ,'default'       => false
      ,'type'          => 'boolean'
      ,'acceptedValue' => array ('TRUE'  => 'Yes'
                                ,'FALSE' => 'No'
                                )
      );

$conf_def_property_list['claro_displayLostPasswordLink'] =
array ('label'         => 'Display a link to the lost password form'
      ,'description'   => 'Disable this option if you are not using the local Claroline password to identify a user'
      ,'default'       => true
      ,'type'          => 'boolean'
      ,'acceptedValue' => array ('TRUE'  => 'Yes'
                                ,'FALSE' => 'No'
                                )
      );

$conf_def_property_list['claro_loadDeprecatedPearAuthDriver'] =
array ( 'label'         => 'Use the old deprecated PEAR:Auth drivers'
      , 'description'   => 'Choose "No" if you don\'t use any deprecated external auth driver. (If you are using the old PEAR-based LDAP authentication, you should replace it with the new ldap.conf.php driver found in inc/conf/extauth and set this option to "No" afterwards)'
      , 'default'       => true
      , 'type'          => 'boolean'
      , 'acceptedValue' => array ('TRUE'  => 'Yes'
                                ,'FALSE' => 'No'
                                )
      );
