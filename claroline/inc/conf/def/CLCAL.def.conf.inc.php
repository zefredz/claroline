<?php // $Id$
$conf_def['config_file']='CLCAL___.conf.php';
$conf_def['config_code']='CLCAL';
$conf_def['config_name']='General setting for calendar';

$conf_def['section']['log']['label']='Track activity';
// $conf_def['config_repository']=''; dislabed = includePath.'/conf'

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
      ,'acceptedValue' => array ('TRUE'=>'enabled'
                              ,'FALSE'=>'dislabed'
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
                              ,'FALSE'=>'Dislabed'
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
                              ,'FALSE'=>'Dislabed'
                              )
      );

?>