<?php // $Id$
// TOOL
$toolConf['config_code']='CLGRP';
$toolConf['config_file']='group.conf.php';
// $toolConf['config_repository']=''; dislabed = includePath.'/conf'
$toolConf['section']['multigroup']['label']='multigroup';
$toolConf['section']['multigroup']['properties'] = 
array ( 'multiGroupAllowed'
      , 'tutorCanBeSimpleMemberOfOthersGroupsAsStudent'
      , 'showTutorsInGroupList'
      );
      
//PROPERTIES
$toolConfProperties['multiGroupAllowed'] =
array ( 'description' => 'if field limitNbGroupByUser is  missing in groupProperties table'
      , 'label'       => 'Autoriser les multigroupes'
      , 'default'     => 'TRUE'
      , 'type'        => 'boolean'
      , 'display'     => TRUE
      , 'readonly'    => FALSE
      , 'acceptedval' => array ( 'TRUE'=>'enabled (set  limitNbGroupByUser = ALL if not defined)'
                               , 'FALSE'=>'dislabed (set  limitNbGroupByUser = 1 if not defined)')
      );

$toolConfProperties['tutorCanBeSimpleMemberOfOthersGroupsAsStudent'] =
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

$toolConfProperties['showTutorsInGroupList'] =
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
