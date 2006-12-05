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
$conf_def['config_name'] = 'Authentication';
$conf_def['config_class']='auth';


//SECTION
$conf_def['section']['main']['label']='Main settings';
//$conf_def['section']['main']['description']='Settings of the tool';
$conf_def['section']['main']['properties'] =
array ( 'claro_authUsernameCaseSensitive'
      , 'claro_displayLocalAuthForm'
      );

//PROPERTIES

$conf_def_property_list['claro_authUsernameCaseSensitive'] =
array ( 'label'         => 'The username is case sensitive'
      , 'description'   => 'Choose "No" if you use microsoft active directory (by default this authentication system is case-insensitive)'
      , 'default'       => TRUE
      , 'type'          => 'boolean'
      , 'acceptedValue' => array ('TRUE'  => 'Yes'
                                ,'FALSE' => 'No'
                                )
      );

$conf_def_property_list['claro_displayLocalAuthForm'] =
array ('label'         => 'Display authentication login form'
      ,'description'   => ''
      ,'default'       => TRUE
      ,'type'          => 'boolean'
      ,'acceptedValue' => array ('TRUE'  => 'Yes'
                                ,'FALSE' => 'No'
                                )
      );

?>
