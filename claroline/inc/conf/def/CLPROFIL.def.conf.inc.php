<?php // $Id$
/**
 * This file describe the parameter for user profile config file
 *
 * @author Christophe Gesché <moosh@claroline.net>
 * @version CLAROLINE 1.6
 * @copyright &copy; 2001-2005 Universite catholique de Louvain (UCL)
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
$conf_def['section']['readonly']['description'] = '';
$conf_def['section']['readonly']['display'] = FALSE;
$conf_def['section']['readonly']['properties'] = 
array (
      );

// Section check data

$conf_def['section']['checkdata']['label'] = 'Validate field';
$conf_def['section']['checkdata']['description'] = '';
$conf_def['section']['checkdata']['properties'] = 
array ( 'SECURE_PASSWORD_REQUIRED'
      , 'checkEmailByHashSent'
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

$conf_def_property_list['checkEmailByHashSent'] = 
array ('label'       => 'If email is fill (or change), send an email to check it'
      ,'default'     => 'FALSE'
      ,'type'        => 'boolean'
      ,'display'     => TRUE
      ,'readonly'    => FALSE
      );


// Section view

$conf_def['section']['view']['label'] = 'Display data';
$conf_def['section']['view']['display'] = FALSE;
$conf_def['section']['view']['description'] = '';
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
      ,'display'     => TRUE
      ,'readonly'    => FALSE
      );


// DEFINE COURSE_MANAGER AND STUDENT CONSTANTS VALUE

$conf_def['section']['const']['label'] = 'Const';
$conf_def['section']['const']['display'] = FALSE;
$conf_def['section']['const']['description'] = '';
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

/* Inactive feature. 

///// PICTURE OF USERS /////

$conf_def['section']['userpicture']['label']='Properties about attached image of a profile';
$conf_def['section']['userpicture']['properties'] = 
array ( 'PREFIX_IMAGE_FILENAME_WITH_UID'
      , 'RESIZE_IMAGE_TO_THIS_HEIGTH'
      , 'KEEP_THE_NAME_WHEN_CHANGE_IMAGE'
      , 'KEEP_THE_OLD_IMAGE_AFTER_CHANGE'
      ,
      );

$conf_def_property_list['PREFIX_IMAGE_FILENAME_WITH_UID'] =
array ( 'label'         => 'Prefix image file name with uid of owner'
      , 'description'   => 'This is a good option to prevent the high probability '
                         . 'of same filename for many user. '."\n"
                         . 'This is also pratical of found back the owner of a picture'
      , 'default'       => 'TRUE'
      , 'type'          => 'boolean'
      , 'acceptedValue' => array ('TRUE'  => 'Prupose'
                                ,'FALSE' => 'Hide'
                                )
      , 'container'     => 'CONST'
      ,'display'       => FALSE
      );

$conf_def_property_list['RESIZE_IMAGE_TO_THIS_HEIGTH'] =
array ( 'label'         => 'Force heigth of all image to this size '
      , 'default'       => 180
      , 'type'          => 'integer'
      , 'unit'          => 'pixel'
      , 'acceptedValue' => array ('min'  => 50
                                ,'max' => 1200
                                )
      , 'container'     => 'CONST'
      ,'display'       => FALSE
      );

$conf_def_property_list['KEEP_THE_NAME_WHEN_CHANGE_IMAGE'] =
array ( 'label'         => 'Keep the name of file when the image is changed'
      , 'description'   => 'TRUE -> the new image have the name of previous.'."\n"
                         . 'FALSE -> a new name is build for each upladed image.'."\n"
                         . 'The difference is about www.'."\n"
                         . '* If your view that ressource is "picture of this profile" answer "keep"'."\n"
                         . '* If your view that ressource is _this_ "picture" and _this_ "picture" is no longer "picture of this profile" answer "rename"'."\n"
                         . 'Because, if you rename the file, the uri point to the new pic (cool uri don\'t change)'
                         . ''
      , 'default'       => 'TRUE'
      , 'type'          => 'boolean'
      , 'acceptedValue' => array ('TRUE'  => 'Keep name'
                                ,'FALSE' => 'Get new name'
                                )
      , 'container'     => 'CONST'
      ,'display'       => FALSE
      );

$conf_def_property_list['KEEP_THE_OLD_IMAGE_AFTER_CHANGE'] =
array ( 'label'         => 'Keep the replaced image when user update pic'
      , 'description'   => '* TRUE'
                         . ' -> if KEEP_THE_NAME_WHEN_CHANGE_IMAGE is true, the  previous image is rename before.'."\n"
                         . '* FALSE'
                         . ' -> only the last image still on server.'
      , 'default'       => 'TRUE'
      , 'type'          => 'boolean'
      , 'acceptedValue' => array ('TRUE'  => 'Save'
                                ,'FALSE' => 'Trash'
                                )
      , 'container'     => 'CONST'
      ,'display'       => FALSE
      );
*/

?>
