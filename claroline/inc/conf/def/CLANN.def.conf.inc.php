<?php // $Id$
$toolConf['config_file']='announcement.conf.inc.php';
$toolConf['config_code']='CLANN';

$toolConf['description'] = 'Use by Announcement tool. This is a course tool';
$toolConf['section']['log']['label']='Track activity';
$toolConf['section']['log']['properties'] = 
array ( 'CONFVAL_LOG_ANNOUNCEMENT_INSERT'
      , 'CONFVAL_LOG_ANNOUNCEMENT_DELETE'
      , 'CONFVAL_LOG_ANNOUNCEMENT_UPDATE'
      );
      
//PROPERTIES
$toolConfProperties['CONFVAL_LOG_ANNOUNCEMENT_INSERT'] =
array( 'label'      => 'Logguer les ajouts dans les annonces'
     , 'default'    => 'TRUE'
     , 'type'       => 'boolean'
     , 'acceptedval'=> array ('TRUE'=>'enabled'
                             ,'FALSE'=>'dislabed'
                             )
     , 'display'    => TRUE
     , 'readonly'   => FALSE
     , 'container'  => 'CONST'
     ); 

$toolConfProperties['CONFVAL_LOG_ANNOUNCEMENT_DELETE'] =
array( 'default'  => 'TRUE'
     , 'label'    => 'Logguer les suppressions d\'annonce'
     , 'type'     => 'boolean'
     , 'acceptedval'=> array ('TRUE'=>'enabled'
                             ,'FALSE'=>'dislabed'
                             )
     , 'display'  => TRUE
     , 'readonly' => FALSE
     , 'container'=> 'CONST'
     );

$toolConfProperties['CONFVAL_LOG_ANNOUNCEMENT_UPDATE'] =
array( 'default'  => 'FALSE'
     , 'type'     => 'boolean'
     , 'acceptedval'=> array ('TRUE'=>'enabled'
                             ,'FALSE'=>'dislabed'
                             )
     , 'display'  => TRUE
     , 'readonly' => FALSE
     , 'container'=> 'CONST'
     , 'label'    => 'Logguer les éditions d\'annonce'
     );

?>