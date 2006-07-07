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
$conf_def['config_class']='auth';


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

//PROPERTIES
$conf_def_property_list['claro_CasEnabled'] =
array ('label'         => 'Enable Cas system'
      ,'description'   => 'if false, other field are optional'
      ,'default'       => FALSE
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




?>