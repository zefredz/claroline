<?php // $Id$
/**
 * This file describe the parameter for CLCAL config file
 *
 * @author Christophe Gesché <moosh@claroline.net>
 * @version CLAROLINE 1.6
 * @copyright &copy; 2001-2005 Universite catholique de Louvain (UCL)
 * @license This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
 * as published by the FREE SOFTWARE FOUNDATION. The GPL is available 
 * through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
 * @see http://www.claroline.net/wiki/config_def/
 * @package CLCAL
 */

$conf_def['config_file']='CLCAL___.conf.php';
$conf_def['config_code']='CLCAL';
$conf_def['config_name']='General setting for calendar';

$conf_def['section']['log']['label']='Track activity';
// $conf_def['config_repository']=''; Disabled = includePath.'/conf'

$conf_def['section']['log']['properties'] = 
array ( 'CONFVAL_LOG_CALENDAR_INSERT'
      , 'CONFVAL_LOG_CALENDAR_DELETE'
      , 'CONFVAL_LOG_CALENDAR_UPDATE'
      );

$conf_def_property_list['CONFVAL_LOG_CALENDAR_INSERT'] = 
array ('label'       => 'Log add'
      ,'default'     => 'TRUE'
      ,'type'        => 'boolean'
      ,'display'     => TRUE
      ,'readonly'    => FALSE
      ,'container'   => 'CONST'
      ,'acceptedValue' => array ('TRUE'=>'Enabled'
                              ,'FALSE'=>'Disabled'
                              )
      );
$conf_def_property_list['CONFVAL_LOG_CALENDAR_DELETE'] = 
array ('label'       => 'Log deletion'
      ,'default'     => 'TRUE'
      ,'type'        => 'boolean'
      ,'display'     => TRUE
      ,'readonly'    => FALSE
      ,'container'   => 'CONST'
      ,'acceptedValue' => array ('TRUE'=>'Enabled'
                              ,'FALSE'=>'Disabled'
                              )
      );
      
$conf_def_property_list['CONFVAL_LOG_CALENDAR_UPDATE'] = 
array ('label'       => 'Log edition'
      ,'default'     => 'TRUE'
      ,'type'        => 'boolean'
      ,'display'     => TRUE
      ,'readonly'    => FALSE
      ,'container'   => 'CONST'
      ,'acceptedValue' => array ('TRUE'=>'Enabled'
                              ,'FALSE'=>'Disabled'
                              )
      );

?>