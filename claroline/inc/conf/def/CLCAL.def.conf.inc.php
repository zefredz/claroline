<?php // $Id$
$toolConf['label']='CLCAL';
$toolConf['section']['log']['label']='Track activity';

$toolConf['config_file']='agenda.conf.inc.php';
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
      ,'technicalInfo' => 'lnqr qerg i qerio ijrùgihqùgo  qriuhgh  qerighjqùehù qreùi ùirùiohrqùg ùoihqr gùihj ù ùiqrgùihqùe ihjgù ùiohjùiherù gùih ùjqerùgihqmrhbgùporkjhgbuqh ùijhg ùiqjreùih ùqerùpiogjùijherùoi ùihjnqùerhgùoihùi */ ùijhù ùqirgùihùihrg ùijhq ùihgùqiherùgihqùih qerùigjhù iùjhqùioeg'
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