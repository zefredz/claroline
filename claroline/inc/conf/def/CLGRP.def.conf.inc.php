<?php // $Id$
// TOOL
$conf_def['config_code']='CLGRP';
$conf_def['config_file']='group.conf.php';
// $conf_def['config_repository']=''; dislabed = includePath.'/conf'
$conf_def['section']['multigroup']['label']='multigroup';
$conf_def['section']['multigroup']['properties'] = 
array ( 'multiGroupAllowed'
      , 'tutorCanBeSimpleMemberOfOthersGroupsAsStudent'
      , 'showTutorsInGroupList'
      );
      
//PROPERTIES
$conf_def_property_list['multiGroupAllowed'] =
array ( 'description' => 'if field limitNbGroupByUser is  missing in groupProperties table'
      , 'label'       => 'Autoriser les multigroupes'
      , 'default'     => 'TRUE'
      , 'type'        => 'boolean'
      , 'display'     => TRUE
      , 'readonly'    => FALSE
      , 'acceptedval' => array ( 'TRUE'=>'enabled (set  limitNbGroupByUser = ALL if not defined)'
                               , 'FALSE'=>'dislabed (set  limitNbGroupByUser = 1 if not defined)')
      );

$conf_def_property_list['tutorCanBeSimpleMemberOfOthersGroupsAsStudent'] =
array ( 'description' => 'if field limitNbGroupByUser is  missing in groupProperties table'
      , 'label'       => 'Tutors can subscribe a team as simple member'
      , 'default'     => 'FALSE'
      , 'type'        => 'boolean'
      , 'acceptedval' => array ('TRUE'=>'yes, he can'
                               ,'FALSE'=>'No, he can\'t'
                               )
      , 'display'     => TRUE
      , 'readonly'    => FALSE
      );

$conf_def_property_list['showTutorsInGroupList'] =
array ( 'description' => 'Not implemented, name reserved  for future version of Claroline'
      , 'label'       => 'whether include tutors in the displayed member list'
      , 'default'     => 'FALSE'
      , 'type'        => 'boolean'
      , 'acceptedval' => array ('TRUE'=>'enabled'
                               ,'FALSE'=>'dislabed'
                               )
      , 'display'     => TRUE
      , 'readonly'    => TRUE
      );


?>
