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
$conf_def['config_code'] = 'CLAUTH';
$conf_def['config_file'] = 'auth.cas.conf.php';
$conf_def['config_name'] = 'Central Authentication System';
$conf_def['config_class']= 'auth';


$conf_def['section']['CAS']['label']='Cas settings';
$conf_def['section']['CAS']['description']='Centralized Authentication System';
$conf_def['section']['CAS']['properties'] =
array ( 'claro_CasEnabled'
      , 'claro_CasServerHostUrl'
      , 'claro_CasServerHostPort'
      , 'claro_CasServerRoot'
      , 'claro_CasLoginString'
      , 'claro_CasGlobalLogout'
      );

//PROPERTIES
$conf_def_property_list['claro_CasEnabled'] =
array ('label'         => 'Enable CAS system'
      ,'description'   => 'If false, other fields are optional'
      ,'default'       => FALSE
      ,'type'          => 'boolean'
      ,'acceptedValue' => array ('TRUE'  => 'Yes'
                                ,'FALSE' => 'No'
                                )
      );

$conf_def_property_list['claro_CasServerHostUrl'] =
array ('label'         => 'Host of CAS server'
      //,'description'   => ''
      ,'default'       => 'my.cas.server.domain.com'
      ,'type'          => 'string'
      );

$conf_def_property_list['claro_CasLoginString'] =
array ('label'         => 'Label of the login url to CAS'
      //,'description'   => ''
      ,'default'       => 'Magic Login'
      ,'type'          => 'string'
      );


$conf_def_property_list['claro_CasServerHostPort'] =
array ('label'         => 'Port of CAS server'
      //,'description'   => ''
      ,'default'       => '443'
      ,'type'          => 'integer'
      );

$conf_def_property_list['claro_CasServerRoot'] =
array ('label'         => 'Root of CAS server'
      ,'description'   => 'Root folder of CAS (example : \'esup-cas/\')'
      ,'default'       => ''
      ,'type'          => 'string'
      );
      
$conf_def_property_list['claro_CasGlobalLogout'] =
array ('label'         => 'Logout user from CAS server when user logout from Claroline'
      ,'description'   => ''
      ,'default'       => FALSE
      ,'type'          => 'boolean'
      ,'acceptedValue' => array ('TRUE'  => 'Yes'
                                ,'FALSE' => 'No'
                                )
      );      

?>
