<?php // $Id$
/**
 * CLAROLINE 
 *
 * This file describe the parameter for edit setting  of groups in a course
 *
 * @version 1.6 $Revision$
 *
 * @copyright 2001-2005 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @see http://www.claroline.net/wiki/index.php/Config
 * @see http://www.claroline.net/wiki/index.php/CLGRP
 *
 * @author Claro Team <cvs@claroline.net>
 *
 * @package CLGRP
 *
 */
// TOOL

$conf_def['config_code']='CLGRP';
$conf_def['config_file']='CLGRP.conf.php';
$conf_def['config_name']='Groups tool';
$conf_def['config_class']='tool';


$conf_def['section']['users']['label']='Users';
$conf_def['section']['users']['description']='Settings for users of group';
$conf_def['section']['users']['properties'] = 
array ( 'multiGroupAllowed'
      );


$conf_def['section']['tutors']['label']='Tutors';
$conf_def['section']['tutors']['description']='Settings for tutors of group';
$conf_def['section']['tutors']['properties'] = 
array ( 'tutorCanBeSimpleMemberOfOthersGroupsAsStudent'
      , 'showTutorsInGroupList'
      );
      
//PROPERTIES
$conf_def_property_list['multiGroupAllowed'] =
array ( 'description' => 'Allow teacher to subscribe users in several groups'
      , 'label'       => 'Multi group allowed'
      , 'default'     => TRUE
      , 'type'        => 'boolean'
      , 'display'     => TRUE
      , 'readonly'    => FALSE
      , 'acceptedValue' => array ( 'TRUE'=>'Yes'
                               , 'FALSE'=>'No')
      );

$conf_def_property_list['tutorCanBeSimpleMemberOfOthersGroupsAsStudent'] =
array ( 'description' => 'A tutor attached to a group can subscribe himself to another group as a simple user.'
      , 'label'       => 'Tutors can subscribe to a group as a simple member'
      , 'default'     => FALSE
      , 'type'        => 'boolean'
      , 'acceptedValue' => array ('TRUE'=>'Yes'
                               ,'FALSE'=>'No'
                               )
      , 'display'     => TRUE
      , 'readonly'    => FALSE
      );

$conf_def_property_list['showTutorsInGroupList'] =
array ( 'description' => 'Not implemented, name reserved  for future version of Claroline'
      , 'label'       => 'Whether include tutors in the displayed member list'
      , 'default'     => FALSE
      , 'type'        => 'boolean'
      , 'acceptedValue' => array ('TRUE'=>'Yes'
                               ,'FALSE'=>'No'
                               )
      , 'display'     => FALSE
      , 'readonly'    => TRUE
      );
?>