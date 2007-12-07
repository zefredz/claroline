<?php // $Id$
/**
 * CLAROLINE
 *
 * This file describe the parameter for profil editor
 *
 * @version 1.8 $Revision$
 *
 * @copyright 2001-2006 Universite catholique de Louvain (UCL)
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

$conf_def['section']['agreement']['label'] = 'Registration agreement';
$conf_def['section']['agreement']['description'] = '';
$conf_def['section']['agreement']['properties'] =
array ( 'show_agreement_panel'
      );

$conf_def_property_list['show_agreement_panel'] =
array ( 'label'         => 'Show the agreement panel before creating a new account'
      ,'description'   => 'The content of this panel is editable in administration '
      , 'default'       => 'FALSE'
      , 'type'          => 'boolean'
      , 'acceptedValue' => array ('TRUE'  => 'Show'
                                 ,'FALSE' => 'Hide'
                                 )
      );

$conf_def['section']['required']['label'] = 'Data checkin';
$conf_def['section']['required']['description'] = '';
$conf_def['section']['required']['properties'] =
array ( 'profile_editable'
      , 'userOfficialCodeCanBeEmpty'
      , 'ask_for_official_code'
      , 'userMailCanBeEmpty'
      , 'SECURE_PASSWORD_REQUIRED'
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
      , 'acceptedValue' => array ('FALSE' => 'Required'
                                 ,'TRUE'  => 'Optional'
                                 )
      );

$conf_def_property_list['ask_for_official_code'] =
array ( 'label'         => 'Ask the official code'
      , 'description'   => 'Display the field official code in form'
      , 'default'       => 'TRUE'
      , 'type'          => 'boolean'
      , 'acceptedValue' => array ('TRUE' => 'Yes'
                                 ,'FALSE'  => 'No'
                                 )
      );

$conf_def_property_list['profile_editable'] =
array ( 'label'         => 'Profile form'
      , 'description'   => 'Which parts of the profile can be changed?'
      , 'default'       => array('name','login','password','email','language')
      , 'type'          => 'multi'
      , 'acceptedValue' => array ('name' => 'Name'
                                 ,'official_code' => 'Official Code'
                                 ,'login' => 'Login'
                                 ,'password' => 'Password'
                                 ,'email' => 'Email'
                                 ,'language' => 'Language'
                                 )
      );

// Section read only fields

$conf_def['section']['readonly']['label'] = 'Allow to modify field';
//$conf_def['section']['readonly']['description'] = '';
$conf_def['section']['readonly']['display'] = FALSE;
$conf_def['section']['readonly']['properties'] =
array (
      );

$conf_def_property_list['SECURE_PASSWORD_REQUIRED'] =
array ('label'         => 'Check password strength'
      ,'description'   => 'Check if the password is not too easy to find'
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
      , 'can_request_course_creator_status'
      , 'can_request_revoquation'
      );

$conf_def_property_list['can_request_course_creator_status'] =
array ( 'label'         => '"Request a Course Creator status" command ?'
      , 'description'   => 'This option insert a command in the user profile form to request a status of course creator. This request is sent by e-mail to platform administrator.'
      , 'display'       => true
      , 'default'       => 'FALSE'
      , 'type'          => 'boolean'
      , 'acceptedValue' => array ('TRUE'  => 'Displayed'
                                ,'FALSE' => 'Hidden'
                                )
      );

$conf_def_property_list['can_request_revoquation'] =
array ( 'label'         => 'Is user allowed to request to be deleted from platform ?'
      , 'description'   => 'If yes, the user have access to a request system. '."\n"
                         .'This option allow only to request it, '."\n"
                         .'and don\'t prework the answer'."\n"
      , 'display'       => false
      , 'default'       => 'FALSE'
      , 'type'          => 'boolean'
      , 'acceptedValue' => array ('TRUE'  => 'Yes'
                                 ,'FALSE' => 'No'
                                )
      );


$conf_def_property_list['allowSelfRegProf'] =
array ('label'       => 'Creation of Course Creator account'
       ,'description' => 'Are users allowed to create themselves a Course Creator account ?'
      ,'default'     => 'TRUE'
      ,'type'        => 'boolean'
      ,'acceptedValue' => array ('TRUE'  => 'Allowed'
                                ,'FALSE' => 'Denied'
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
