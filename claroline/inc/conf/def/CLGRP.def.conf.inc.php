<?php // $Id$
// TOOL
$conf_def['config_code']='CLGRP';
$conf_def['config_file']='CLGRP___.conf.php';
$conf_def['config_name']='General setting for groups';
// $conf_def['config_repository']=''; Disabled = includePath.'/conf'
$conf_def['section']['multigroup']['label']='multigroup';
$conf_def['section']['multigroup']['properties'] = 
array ( 'multiGroupAllowed'
      , 'tutorCanBeSimpleMemberOfOthersGroupsAsStudent'
      , 'showTutorsInGroupList'
      );
      
//PROPERTIES
$conf_def_property_list['multiGroupAllowed'] =
array ( 'description' => 'Whether teacher can fix than a user can subscribe in many team'
      , 'label'       => 'Multi group allowed'
      , 'default'     => 'TRUE'
      , 'type'        => 'boolean'
      , 'display'     => TRUE
      , 'readonly'    => FALSE
      , 'acceptedValue' => array ( 'TRUE'=>'enabled (set  limitNbGroupByUser = ALL if not defined)'
                               , 'FALSE'=>'Disabled (set  limitNbGroupByUser = 1 if not defined)')
      );

$conf_def_property_list['tutorCanBeSimpleMemberOfOthersGroupsAsStudent'] =
array ( 'description' => 'Fix if a us user mark as potential tutor attached to a group, can subscribe himself to another group (as simple student).'
      , 'label'       => 'Tutors can subscribe a team as simple member'
      , 'default'     => 'FALSE'
      , 'type'        => 'boolean'
      , 'acceptedValue' => array ('TRUE'=>'yes, he can'
                               ,'FALSE'=>'No, he can\'t'
                               )
      , 'display'     => TRUE
      , 'readonly'    => FALSE
      );

$conf_def_property_list['showTutorsInGroupList'] =
array ( 'description' => 'Not implemented, name reserved  for future version of Claroline'
      , 'label'       => 'Whether include tutors in the displayed member list'
      , 'default'     => 'FALSE'
      , 'type'        => 'boolean'
      , 'acceptedValue' => array ('TRUE'=>'enabled'
                               ,'FALSE'=>'Disabled'
                               )
      , 'display'     => FALSE
      , 'readonly'    => TRUE
      );
?>
