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
$conf_def['config_name'] = 'Setting for profile edition';
$conf_def['config_file'] = 'user.profile.conf.php';
$conf_def['old_config_file'][] ='profile.conf.php';
// $conf_def['description'] = 'How ca be edit an user profile';
// $conf_def['config_repository']=''; Disabled = includePath.'/conf'

// CONFIG SECTIONS

$conf_def['section']['checkdata']['label']= 'Check given data';
$conf_def['section']['checkdata']['description']='Flags check to do, and fixe read/write access';

$conf_def['section']['checkdata']['properties'] = 
array ( 'SECURE_PASSWORD_REQUIRED'
      , 'CONFVAL_ASK_FOR_OFFICIAL_CODE'
      , 'CONFVAL_CHECK_OFFICIAL_CODE'
      );

// STATUS //

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
// AUTHENTICATION //
//// PASSWORD //////
$conf_def_property_list['SECURE_PASSWORD_REQUIRED'] =
array ('label'         => 'Check the fiability of password'
      ,'description'   => 'Check if the password is not too much easy to find'
      ,'default'       => 'TRUE'
      ,'type'          => 'boolean'
      ,'acceptedValue' => array ('TRUE'  => 'check it'
                                ,'FALSE' => 'lets do'
                                )
      , 'container'     => 'CONST'
      );

$conf_def_property_list['userOfficialCodeCanBeEmpty'] =
array ( 'label'         => 'Allow user to let Official Code Empty ?'
      , 'default'       => 'TRUE'
      , 'type'          => 'boolean'
      , 'acceptedValue' => array ('TRUE'  => 'Check it'
                                 ,'FALSE' => 'Lets do'
                                 )
      );
$conf_def_property_list['userMailCanBeEmpty'] =
array ( 'label'         => 'Allow user to let Email Code Empty ?'
      , 'description'   => 'Accept email as valid (best choice)'
      , 'default'       => 'TRUE'
      , 'type'          => 'boolean'
      , 'acceptedValue' => array ('TRUE'  => 'Optionnal'
                                 ,'FALSE' => 'Require it'
                                 )
      );
///// OFFICIAL CODE // BEGIN
// don't forget to change name of offical code in your institute
// $langOfficialCode in lang File  'registration'
$conf_def_property_list['CONFVAL_ASK_FOR_OFFICIAL_CODE'] =
array ('label'         => 'CONFVAL_ASK_FOR_OFFICIAL_CODE'
      ,'description'   => 'Not used but name fixed'
      ,'default'       => 'TRUE'
      ,'type'          => 'boolean'
      ,'acceptedValue' => array ('TRUE'  => 'Check it'
                                ,'FALSE' => 'Lets do'
                                )
      , 'container'     => 'CONST'
      );

$conf_def_property_list['CONFVAL_CHECK_OFFICIAL_CODE'] =
array ('label'         => 'Check the official Code ?'
      ,'description'   => 'If true, build here the
      function personal_check_official_code($code,$valueToReturnIfOk,$valueToReturnIfBad)
      {
	      return $stateOfficialCode 
      }'
      ,'default'       => 'FALSE'
      ,'type'          => 'boolean'
      ,'acceptedValue' => array ('TRUE'  => 'Check it'
                                ,'FALSE' => 'Lets do'
                                )
      , 'container'     => 'CONST'
      );

///// OFFICIAL CODE // END

      
$conf_def['section']['extracommand']['label']='Switch some extra tools';
$conf_def['section']['extracommand']['properties'] = 
array ( 'CAN_REQUEST_COURSE_CREATOR_STATUS'
      , 'CAN_REQUEST_REVOQUATION'
      );
      
$conf_def_property_list['CAN_REQUEST_COURSE_CREATOR_STATUS'] =
array ( 'label'         => 'Is user allowed to request a course creator status ?'
      , 'description'   => 'If yes, the user have access to a request system. '."\n"
                         .'This option allow only to request it, '
                         .'and don\'t prework the answer'
      , 'default'       => 'FALSE'
      , 'type'          => 'boolean'
      , 'acceptedValue' => array ('TRUE'  => 'check it'
                                ,'FALSE' => 'lets do'
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
      , 'acceptedValue' => array ('TRUE'  => 'prupose'
                                ,'FALSE' => 'hide'
                                )
      , 'container'     => 'CONST'
      );



///// PICTURE OF USERS /////
$conf_def['section']['userpicture']['label']='Properties about attached image of a profile';
$conf_def['section']['userpicture']['properties'] = 
array ( 'PREFIX_IMAGE_FILENAME_WITH_UID'
      , 'RESIZE_IMAGE_TO_THIS_HEIGTH'
      , 'KEEP_THE_NAME_WHEN_CHANGE_IMAGE'
      , 'KEEP_THE_OLD_IMAGE_AFTER_CHANGE'
      ,
      );

define ('PREFIX_IMAGE_FILENAME_WITH_UID', TRUE); // if true, filename of images on server begin with uid of the user.
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
      );

?>