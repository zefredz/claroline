<?php // $Id$
$toolConf['config_file']='agenda.conf.inc.php';
$toolConf['config_code']='CLCAL';

$toolConf['section']['log']['label']='Track activity';
// $toolConf['config_repository']=''; dislabed = includePath.'/conf'

$toolConf['section']['log']['properties'] = 
array ( 'CONFVAL_LOG_CALENDAR_INSERT'
      , 'CONFVAL_LOG_CALENDAR_DELETE'
      , 'CONFVAL_LOG_CALENDAR_UPDATE'
      );

$toolConfProperties['CONFVAL_LOG_CALENDAR_INSERT'] = 
array ('label'       => 'Logguer les ajouts d\'agenda'
      ,'default'     => 'TRUE'
      ,'type'        => 'boolean'
      ,'display'     => TRUE
      ,'readonly'    => FALSE
      ,'technicalInfo' => 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aliquam dolor mi, semper vel, euismod a, sodales quis, libero. Etiam eros risus, ornare eget, placerat ac, eleifend quis, mauris. Cras blandit sapien sed magna. Duis convallis vehicula leo. Aliquam sed ipsum in orci ornare dictum. Phasellus dignissim, tortor at ornare tincidunt.'
      ,'container'   => 'CONST'
      ,'acceptedval' => array ('TRUE'=>'enabled'
                              ,'FALSE'=>'dislabed'
                              )
      );
$toolConfProperties['CONFVAL_LOG_CALENDAR_DELETE'] = 
array ('label'       => 'Logguer les suppressions dans l\'agenda'
      ,'default'     => 'TRUE'
      ,'type'        => 'boolean'
      ,'display'     => TRUE
      ,'readonly'    => FALSE
      ,'container'   => 'CONST'
      ,'acceptedval' => array ('TRUE'=>'enabled'
                              ,'FALSE'=>'dislabed'
                              )
      );
      
$toolConfProperties['CONFVAL_LOG_CALENDAR_UPDATE'] = 
array ('label'       => 'Logguer les éditions dans l\'agenda'
      ,'default'     => 'TRUE'
      ,'type'        => 'boolean'
      ,'display'     => TRUE
      ,'readonly'    => FALSE
      ,'container'   => 'CONST'
      ,'acceptedval' => array ('TRUE'=>'enabled'
                              ,'FALSE'=>'dislabed'
                              )
      );

?>