<?php // $Id$
/**
 * CLAROLINE 
 *
 * This file describe the parameter for profil editor
 *
 * @version 1.6 $Revision$
 *
 * @copyright 2001-2005 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @see http://www.claroline.net/wiki/index.php/Config
 *
 * @author Claro Team <cvs@claroline.net>
 *
 * @package CLPROFIL
 *
 */

// CONFIG HEADER

$conf_def['config_code'] = 'CLPROFIL';
$conf_def['config_name'] = 'User profile options';
//$conf_def['description'] = ''; 
$conf_def['config_file'] = 'user_profile.conf.php';
$conf_def['old_config_file'][] ='profile.conf.php';
$conf_def['config_class']='user';

// Section required fields

$conf_def['section']['required']['label'] = 'Required fields';
$conf_def['section']['required']['description'] = '';
$conf_def['section']['required']['properties'] = 
array ( 'userOfficialCodeCanBeEmpty'
      , 'userMailCanBeEmpty'
      );

$conf_def_property_list['userOfficialCodeCanBeEmpty'] =
array ( 'label'         => 'Official Code is'
      , 'default'       => TRUE
      , 'type'          => 'boolean'
      , 'acceptedValue' => array ('TRUE'  => 'Optional'
                                 ,'FALSE' => 'Required'
                                 )
      );
$conf_def_property_list['userMailCanBeEmpty'] =
array ( 'label'         => 'Email is'
      , 'description'   => 'Accept email as valid (best choice)'
      , 'default'       => TRUE
      , 'type'          => 'boolean'
      , 'acceptedValue' => array ('FALSE' => 'Required'
                                 ,'TRUE'  => 'Optional'
                                 
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
array ('label'         => 'Check password strength'
      ,'description'   => 'Check if the password is not too much easy to find'
      ,'default'       => TRUE
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
      , 'can_request_course_creator_status'
      , 'can_request_revoquation' 
      );

$conf_def_property_list['can_request_course_creator_status'] =
array ( 'label'         => 'Is user allowed to request a course creator status ?'
      , 'description'   => 'If yes, the user have access to a request system. '."\n"
                         .'This option allow only to request it, '
                         .'and don\'t prework the answer'
      , 'default'       => FALSE
      , 'type'          => 'boolean'
      , 'acceptedValue' => array ('TRUE'  => 'Yes'
                                ,'FALSE' => 'No'
                                )
      );

$conf_def_property_list['can_request_revoquation'] =
array ( 'label'         => 'Is user allowed to request to be deleted from platform ?'
      , 'description'   => 'If yes, the user have access to a request system. '."\n"
                         .'This option allow only to request it, '."\n"
                         .'and don\'t prework the answer'."\n"
      , 'default'       => FALSE
      , 'type'          => 'boolean'
      , 'acceptedValue' => array ('TRUE'  => 'Yes'
                                 ,'FALSE' => 'No'
                                )
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
