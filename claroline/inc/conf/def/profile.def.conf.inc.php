<?php // $Id$

// TOOL
$toolConf['label']='profil';
$toolConf['description'] = 'Assignment tool. this is a course tool';
$toolConf['config_file']='profile.conf.inc.php';
// $toolConf['config_repository']=''; dislabed = includePath.'/conf'

$toolConf['section']['checkdata']['label']='Check given data';
$toolConf['section']['checkdata']['description']='Flags  check to do, and fixe read/write access';

$toolConf['section']['checkdata']['properties'] = 
array ( 'SECURE_PASSWORD_REQUIRED'
      , 'CONFVAL_ASK_FOR_OFFICIAL_CODE'
      , 'CONFVAL_CHECK_OFFICIAL_CODE'
      );


// STATUS //

define ("COURSEMANAGER",1);
define ("STUDENT",      5);

// AUTHENTICATION //
//// PASSWORD //////
$toolConfProperties['SECURE_PASSWORD_REQUIRED'] =
array ('label'         => 'check the fiability of password'
      ,'description'   => 'check if the password is not too much easy to find'
      ,'default'       => 'TRUE'
      ,'type'          => 'boolean'
      ,'acceptedValue' => array ('TRUE'  => 'check it'
                                ,'FALSE' => 'lets do'
                                )
      , 'container'     => 'CONST'
      );

$toolConfProperties['userOfficialCodeCanBeEmpty'] =
array ( 'label'         => 'allow user to let Official Code Empty'
      , 'description'   => ''
      , 'default'       => 'TRUE'
      , 'type'          => 'boolean'
      , 'acceptedValue' => array ('TRUE'  => 'check it'
                                 ,'FALSE' => 'lets do'
                                 )
      );
$toolConfProperties['userMailCanBeEmpty'] =
array ( 'label'         => 'allow user to let Email Code Empty'
      , 'description'   => 'accept email as valid (best choice)'
      , 'default'       => 'TRUE'
      , 'type'          => 'boolean'
      , 'acceptedValue' => array ('TRUE'  => 'optionnal'
                                 ,'FALSE' => 'require it'
                                 )
      );
///// OFFICIAL CODE // BEGIN
// don't forget to change name of offical code in your institute
// $langOfficialCode in lang File  'registration'
$toolConfProperties['CONFVAL_ASK_FOR_OFFICIAL_CODE'] =
array ('label'         => 'CONFVAL_ASK_FOR_OFFICIAL_CODE'
      ,'description'   => 'Not used but name fixed'
      ,'default'       => 'TRUE'
      ,'type'          => 'boolean'
      ,'acceptedValue' => array ('TRUE'  => 'check it'
                                ,'FALSE' => 'lets do'
                                )
      , 'container'     => 'CONST'
      );

$toolConfProperties['CONFVAL_CHECK_OFFICIAL_CODE'] =
array ('label'         => 'Check the official Code'
      ,'description'   => 'if true, build here the
      function personal_check_official_code($code,$valueToReturnIfOk,$valueToReturnIfBad)
      {
	      return $stateOfficialCode 
      }'
      ,'default'       => 'FALSE'
      ,'type'          => 'boolean'
      ,'acceptedValue' => array ('TRUE'  => 'check it'
                                ,'FALSE' => 'lets do'
                                )
      , 'container'     => 'CONST'
      );

///// OFFICIAL CODE // END

      
$toolConf['section']['extracommand']['label']='Switch some extra tools';
$toolConf['section']['extracommand']['properties'] = 
array ( 'CAN_REQUEST_COURSE_CREATOR_STATUS'
      , 'CAN_REQUEST_REVOQUATION'
      );
      
$toolConfProperties['CAN_REQUEST_COURSE_CREATOR_STATUS'] =
array ( 'label'         => 'Is user allowed to request a course creator status'
      , 'description'   => 'if yes, the user have access to a request system. '."\n"
                         .'This option allow only to request it, '
                         .'and don\'t prework the answer'
      , 'default'       => 'FALSE'
      , 'type'          => 'boolean'
      , 'acceptedValue' => array ('TRUE'  => 'check it'
                                ,'FALSE' => 'lets do'
                                )
      , 'container'     => 'CONST'
      );

$toolConfProperties['CAN_REQUEST_REVOQUATION'] =
array ( 'label'         => 'Is user allowed to request to be deleted from platform'
      , 'description'   => 'if yes, the user have access to a request system. '."\n"
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
$toolConf['section']['userpicture']['label']='properties about attached image of a profile';
$toolConf['section']['userpicture']['properties'] = 
array ( 'PREFIX_IMAGE_FILENAME_WITH_UID'
      , 'RESIZE_IMAGE_TO_THIS_HEIGTH'
      , 'KEEP_THE_NAME_WHEN_CHANGE_IMAGE'
      , 'KEEP_THE_OLD_IMAGE_AFTER_CHANGE'
      ,
      );

define ('PREFIX_IMAGE_FILENAME_WITH_UID', TRUE); // if true, filename of images on server begin with uid of the user.
$toolConfProperties['PREFIX_IMAGE_FILENAME_WITH_UID'] =
array ( 'label'         => 'Prefix image file name with uid of owner'
      , 'description'   => 'This is a good option to prevent the high probability '
                         . 'of same filename for many user. '."\n"
                         . 'This is also pratical of found back the owner of a picture'
      , 'default'       => 'TRUE'
      , 'type'          => 'boolean'
      , 'acceptedValue' => array ('TRUE'  => 'prupose'
                                ,'FALSE' => 'hide'
                                )
      , 'container'     => 'CONST'
      );

$toolConfProperties['RESIZE_IMAGE_TO_THIS_HEIGTH'] =
array ( 'label'         => 'force heigth of all image to this size'
      , 'default'       => 180
      , 'type'          => 'integer'
      , 'unit'          => 'pixel'
      , 'acceptedValue' => array ('min'  => 100
                                ,'max' => 1200
                                )
      , 'container'     => 'CONST'
      );

$toolConfProperties['KEEP_THE_NAME_WHEN_CHANGE_IMAGE'] =
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
      , 'acceptedValue' => array ('TRUE'  => 'keep name'
                                ,'FALSE' => 'get new name'
                                )
      , 'container'     => 'CONST'
      );

$toolConfProperties['KEEP_THE_OLD_IMAGE_AFTER_CHANGE'] =
array ( 'label'         => 'Keep the replaced image when user update pic'
      , 'description'   => '* TRUE'
                         . ' -> if KEEP_THE_NAME_WHEN_CHANGE_IMAGE is true, the  previous image is rename before.'."\n"
                         . '* FALSE'
                         . ' -> only the last image still on server.'
      , 'default'       => 'TRUE'
      , 'type'          => 'boolean'
      , 'acceptedValue' => array ('TRUE'  => 'save'
                                ,'FALSE' => 'trash'
                                )
      , 'container'     => 'CONST'
      );

// for stats //
define ('NB_LINE_OF_EVENTS', 15);

?>