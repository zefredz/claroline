<?php // $Id$
/**
 * This file describe the parameter for CLDOC config file
 *
 * @author  Christophe Gesché <moosh@claroline.net>
 * @version CLAROLINE 1.6
 * @copyright &copy; 2001-2005 Universite catholique de Louvain (UCL)
 * @license This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
 * as published by the FREE SOFTWARE FOUNDATION. The GPL is available 
 * through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
 * @see http://www.claroline.net/wiki/config_def/
 * @package CLANN
 */

// CONFIG HEADER 

$conf_def['config_code']='CLANN';
$conf_def['config_file']='CLANN___.conf.php';
$conf_def['config_name']='general setting for announcements';
$conf_def['description'] = 'Use by Announcement tool. This is a course tool';
$conf_def['old_config_file']= array ('CLANN.conf.php'
                                    ,'announcement.conf.inc.php'
                                    );
// CONFIG SECTIONS
 
$conf_def['section']['log']['label']='Track activity';
$conf_def['section']['log']['properties'] = 
array ( 'CONFVAL_LOG_ANNOUNCEMENT_INSERT'
      , 'CONFVAL_LOG_ANNOUNCEMENT_DELETE'
      , 'CONFVAL_LOG_ANNOUNCEMENT_UPDATE'
      );
      
// PROPERTIES
$conf_def_property_list['CONFVAL_LOG_ANNOUNCEMENT_INSERT'] =
array( 'label'      => 'Log add'
     , 'default'    => 'TRUE'
     , 'type'       => 'boolean'
     , 'acceptedValue'=> array ('TRUE'=>'enabled'
                             ,'FALSE'=>'Disabled'
                             )
     , 'display'    => TRUE
     , 'readonly'   => FALSE
     , 'container'  => 'CONST'
     ); 

$conf_def_property_list['CONFVAL_LOG_ANNOUNCEMENT_DELETE'] =
array( 'default'     => 'TRUE'
     , 'label'       => 'Log delete'
     , 'description' => 'Record in tracking when an announcement is deleted'
     , 'type'        => 'boolean'
     , 'acceptedValue'=> array ('TRUE'=>'enabled'
                             ,'FALSE'=>'Disabled'
                             )
     , 'display'  => TRUE
     , 'readonly' => FALSE
     , 'container'=> 'CONST'
     );

$conf_def_property_list['CONFVAL_LOG_ANNOUNCEMENT_UPDATE'] =
array( 'default'  => 'FALSE'
     , 'type'     => 'boolean'
     , 'acceptedValue'=> array ('TRUE'=>'enabled'
                             ,'FALSE'=>'Disabled'
                             )
     , 'display'  => TRUE
     , 'readonly' => FALSE
     , 'container'=> 'CONST'
     , 'label'    => 'Log update'
     );

?>