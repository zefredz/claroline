<?php // $Id$
/**
 * This file describe the parameter for user profile config file
 * ----------------------------------------------------------------------------
 * @author Christophe Gesch� <moosh@claroline.net>
 * ----------------------------------------------------------------------------
 * @version CLAROLINE 1.6
 * ----------------------------------------------------------------------------
 * @copyright 2001-2005 Universite catholique de Louvain (UCL)
 * @license This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
 * as published by the FREE SOFTWARE FOUNDATION. The GPL is available 
 * through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
 * @see http://www.claroline.net/wiki/config_def/
 * @package user
 */

// CONFIG HEADER

$conf_def['config_code'] = 'CLPROFIL';
$conf_def['config_name'] = 'User profile options';
$conf_def['description'] = ''; 
$conf_def['config_file'] = 'user_profile.conf.php';
$conf_def['old_config_file'][] ='profile.conf.php';

// Section required fields

$conf_def['section']['required']['label'] = 'Required fields';
$conf_def['section']['required']['description'] = '';
$conf_def['section']['required']['properties'] = 
array ( 'userOfficialCodeCanBeEmpty'
      , 'userMailCanBeEmpty'
      );

$conf_def_property_list['userOfficialCodeCanBeEmpty'] =
array ( 'label'         => 'Official Code is'
      , 'default'       => 'TRUE'
      , 'type'          => 'boolean'
      , 'acceptedValue' => array ('TRUE'  => 'Optional'
                                 ,'FALSE' => 'Required'
                                 )
      );
$conf_def_property_list['userMailCanBeEmpty'] =
array ( 'label'         => 'Email is'
      , 'description'   => 'Accept email as valid (best choice)'
      , 'default'       => 'TRUE'
      , 'type'          => 'boolean'
      , 'acceptedValue' => array ('TRUE'  => 'Optional'
                                 ,'FALSE' => 'Required'
                                 )
      );

// Section read only fields

$conf_def['section']['readonly']['label'] = 'Allow to modify field';
//$conf_def['section']['readonly']['description'] = '';
$conf_def['section']['readonly']['display'] = FALSE;
$conf_def['section']['readonly']['properties'] = 
array (
      );

// Section check data

$conf_def['section']['checkdata']['label'] = 'Validate field';
//$conf_def['section']['checkdata']['description'] = '';
$conf_def['section']['checkdata']['properties'] = 
array ( 'SECURE_PASSWORD_REQUIRED'
      );
      

$conf_def_property_list['SECURE_PASSWORD_REQUIRED'] =
array ('label'         => 'Check the fiability of password'
      ,'description'   => 'Check if the password is not too much easy to find'
      ,'default'       => 'TRUE'
      ,'type'          => 'boolean'
      ,'acceptedValue' => array ('TRUE'  => 'Yes'
                                ,'FALSE' => 'No'
                                )
      , 'container'     => 'CONST'
      );

// Section view

$conf_def['section']['view']['label'] = 'Display data';
$conf_def['section']['view']['display'] = FALSE;
//$conf_def['section']['view']['description'] = '';
$conf_def['section']['view']['properties'] = 
array (
      );

// Section 

$conf_def['section']['request']['label'] = 'User request';
$conf_def['section']['request']['description'] = '';
$conf_def['section']['request']['properties'] = 
array ( 'allowSelfRegProf'
      , 'CAN_REQUEST_COURSE_CREATOR_STATUS'
      , 'CAN_REQUEST_REVOQUATION' 
      );

$conf_def_property_list['CAN_REQUEST_COURSE_CREATOR_STATUS'] =
array ( 'label'         => 'Is user allowed to request a course creator status ?'
      , 'description'   => 'If yes, the user have access to a request system. '."\n"
                         .'This option allow only to request it, '
                         .'and don\'t prework the answer'
      , 'default'       => 'FALSE'
      , 'type'          => 'boolean'
      , 'acceptedValue' => array ('TRUE'  => 'Yes'
                                ,'FALSE' => 'No'
                                )
      , 'container'     => 'CONST'
      );

$conf_def_property_list['CAN_REQUEST_REVOQUATION'] =
array ( 'label'         => 'Is user allowed to request to be deleted from platform ?'
      , 'description'   => 'If yes, the user have access to a request system. '."\n"
                         .'This option allow only to request it, '."\n"
                         .'and don\'t prework the answer'."\n"
      , 'default'       => 'FALSE'
      , 'type'          => 'boolean'
      , 'acceptedValue' => array ('TRUE'  => 'Yes'
                                ,'FALSE' => 'No'
                                )
      , 'container'     => 'CONST'
      );


$conf_def_property_list['allowSelfRegProf'] = 
array ('label'       => 'Are teacher allowed to subscribe as teacher ?'
      ,'default'     => 'TRUE'
      ,'type'        => 'boolean'
      ,'acceptedValue' => array ('TRUE'  => 'Yes'
                                ,'FALSE' => 'No'
                                )
      ,'display'     => TRUE
      ,'readonly'    => FALSE
      );


// DEFINE COURSE_MANAGER AND STUDENT CONSTANTS VALUE

$conf_def['section']['const']['label'] = 'Const';
$conf_def['section']['const']['display'] = FALSE;
//$conf_def['section']['const']['description'] = '';
$conf_def['section']['const']['properties'] = 
array ( 'COURSEMANAGER'
       ,'STUDENT' 
      );

$conf_def_property_list['COURSEMANAGER'] =
array ('label'         => 'Database value for course manager status'
      ,'description'   => 'Do not change this'
      ,'display'       => FALSE
      ,'readonly'      => TRUE
      ,'default'       => '1'
      ,'type'          => 'integer'
      ,'acceptedValue' => array ('1'  => 'Course manager'
                                )
      , 'container'     => 'CONST'
      );
$conf_def_property_list['STUDENT'] =
array ('label'         => 'Database value for student status'
      ,'description'   => 'Do not change this'
      ,'display'       => FALSE
      ,'readonly'      => TRUE
      ,'default'       => '5'
      ,'type'          => 'integer'
      ,'acceptedValue' => array ('5'  => 'Student'
                                )
      , 'container'     => 'CONST'
      );


?>
