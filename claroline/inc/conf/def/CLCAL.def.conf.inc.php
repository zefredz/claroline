<?php // $Id$
$conf_def['config_file']='agenda.conf.inc.php';
$conf_def['config_code']='CLCAL';
$conf_def['config_name']='general setting for calendar';

$conf_def['section']['log']['label']='Track activity';
// $conf_def['config_repository']=''; dislabed = includePath.'/conf'

$conf_def['section']['log']['properties'] = 
array ( 'CONFVAL_LOG_CALENDAR_INSERT'
      , 'CONFVAL_LOG_CALENDAR_DELETE'
      , 'CONFVAL_LOG_CALENDAR_UPDATE'
      );

$conf_def_property_list['CONFVAL_LOG_CALENDAR_INSERT'] = 
array ('label'       => 'Logguer les ajouts d\'agenda'
      ,'default'     => 'TRUE'
      ,'type'        => 'boolean'
      ,'display'     => TRUE
      ,'readonly'    => FALSE
      ,'technicalInfo' => 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aliquam dolor mi, semper vel, euismod a, sodales quis, libero. Etiam eros risus, ornare eget, placerat ac, eleifend quis, mauris. Cras blandit sapien sed magna. Duis convallis vehicula leo. Aliquam sed ipsum in orci ornare dictum. Phasellus dignissim, tortor at ornare tincidunt.'
      ,'container'   => 'CONST'
      ,'acceptedValue' => array ('TRUE'=>'enabled'
                              ,'FALSE'=>'dislabed'
                              )
      );
$conf_def_property_list['CONFVAL_LOG_CALENDAR_DELETE'] = 
array ('label'       => 'Logguer les suppressions dans l\'agenda'
      ,'default'     => 'TRUE'
      ,'type'        => 'boolean'
      ,'display'     => TRUE
      ,'readonly'    => FALSE
      ,'container'   => 'CONST'
      ,'acceptedValue' => array ('TRUE'=>'enabled'
                              ,'FALSE'=>'dislabed'
                              )
      );
      
$conf_def_property_list['CONFVAL_LOG_CALENDAR_UPDATE'] = 
array ('label'       => 'Logguer les éditions dans l\'agenda'
      ,'default'     => 'TRUE'
      ,'type'        => 'boolean'
      ,'display'     => TRUE
      ,'readonly'    => FALSE
      ,'container'   => 'CONST'
      ,'acceptedValue' => array ('TRUE'=>'enabled'
                              ,'FALSE'=>'dislabed'
                              )
      );

?>