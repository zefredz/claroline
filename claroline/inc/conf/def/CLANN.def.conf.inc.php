<?php // $Id$
$conf_def['config_file']='announcement.conf.inc.php';
$conf_def['config_code']='CLANN';
$conf_def['config_name']='general setting for announcements';

$conf_def['description'] = 'Use by Announcement tool. This is a course tool';
$conf_def['section']['log']['label']='Track activity';
$conf_def['section']['log']['properties'] = 
array ( 'CONFVAL_LOG_ANNOUNCEMENT_INSERT'
      , 'CONFVAL_LOG_ANNOUNCEMENT_DELETE'
      , 'CONFVAL_LOG_ANNOUNCEMENT_UPDATE'
      );
      
//PROPERTIES
$conf_def_property_list['CONFVAL_LOG_ANNOUNCEMENT_INSERT'] =
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

$conf_def_property_list['CONFVAL_LOG_ANNOUNCEMENT_DELETE'] =
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

$conf_def_property_list['CONFVAL_LOG_ANNOUNCEMENT_UPDATE'] =
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