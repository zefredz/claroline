<?php // $Id$
$conf_def['config_code']='CLADDCRS';
$conf_def['config_file']='core.add_course.conf.php';
$conf_def['config_name']='general setting for course creation';
// $conf_def['config_repository']=''; Disabled = includePath.'/conf'

$conf_def['section']['create']['label']='Creation properties';
$conf_def['section']['create']['properties'] = 
array ( 'defaultVisibilityForANewCourse'
      , 'HUMAN_CODE_NEEDED'
      , 'HUMAN_LABEL_NEEDED'
      , 'COURSE_EMAIL_NEEDED'
      , 'prefixAntiNumber'
      , 'prefixAntiEmpty');

$conf_def_property_list['defaultVisibilityForANewCourse'] = 
array ('label'       => 'Default visibility for new course'
      ,'description' => 'hide = the course can be acces without subscription to this course.
      open = an authenticated user on the platform can subscribe the course.
      '
      ,'default'     => '2'
      ,'type'        => 'enum'
      ,'display'     => TRUE
      ,'readonly'    => FALSE
      ,'acceptedValue' => array ('0'=>'hide and closed'
                                ,'1'=>'visible and closed'
                                ,'2'=>'visible and open'
                                ,'3'=>'hide and open'
                                )
      );

$conf_def_property_list['is_allowedToRestore'] = 
array ('label'       => 'Autoriser le créateur de cours de restaurer une archive de cours'
      ,'default'     => 'FALSE'
      ,'type'        => 'boolean'
      ,'display'     => TRUE
      ,'readonly'    => FALSE
      ,'acceptedValue' => array ('TRUE'  =>'enabled'
                                ,'FALSE' =>'Disabled'
                                )
      );

$conf_def_property_list['HUMAN_CODE_NEEDED'] = 
array ('label'       => 'whether user can leave course code (officialCode) field empty'
      ,'default'     => 'TRUE'
      ,'type'        => 'boolean'
      ,'container'   => 'CONST'
      ,'acceptedValue' => array ('TRUE' => 'enabled'
                                ,'FALSE'=> 'Disabled'
                                )
      );

$conf_def_property_list['HUMAN_LABEL_NEEDED'] = 
array ('label'       => 'whether user can leave course label (name) field empty'
      ,'default'     => 'TRUE'
      ,'type'        => 'boolean'
      ,'container'   => 'CONST'
      ,'acceptedValue' => array ('TRUE'=>'enabled'
                              ,'FALSE'=>'Disabled'
                              )
      );

$conf_def_property_list['COURSE_EMAIL_NEEDED'] = 
array ('label'       => 'whether user can leave email field empty'
      ,'default'     => 'FALSE'
      ,'type'        => 'boolean'
      ,'display'     => TRUE
      ,'readonly'    => FALSE
      ,'container'   => 'CONST'
      ,'acceptedValue' => array ('TRUE'=>'enabled'
                              ,'FALSE'=>'Disabled'
                              )
      );

$conf_def_property_list['prefixAntiNumber'] = 
array ('label'       => 'Ce préfixe est utilisé si le code commence par un chiffre'
      ,'default'     => 'No'
      ,'type'        => 'string'
      );

$conf_def_property_list['prefixAntiEmpty'] = 
array ('label'       => 'Ce préfixe sera utilisé si le code cours est vide'
      ,'default'     => 'Course'
      ,'type'        => 'string'
      );


// Course properties rules
$conf_def['section']['restore']['label']='Restore // Create a course from an archive';
$conf_def['section']['restore']['properties'] = 
array ( 'is_allowedToRestore'
      , 'sendByUploadAivailable'
      , 'sendByLocaleAivailable'
      , 'sendByHTTPAivailable'
      , 'sendByFTPAivailable'
      , 'localArchivesRepository'
      );

$conf_def_property_list['is_allowedToRestore'] = 
array ('label'       => 'Course creator can create a course from an archive'
      ,'default'     => 'FALSE'
      ,'type'        => 'boolean'
      ,'container'   => 'CONST'
      ,'display'     => FALSE
      ,'acceptedValue' => array ('TRUE'=>'enabled'
                              ,'FALSE'=>'Disabled'
                              )
      );

$conf_def_property_list['sendByUploadAivailable'] = 
array ('label'       => 'Course creator can upload an archive to restore as new course'
      ,'description' => 'is_allowedToRestore must be enabled' 
      ,'default'     => 'FALSE'
      ,'type'        => 'boolean'
      ,'display'     => FALSE
      ,'container'   => 'CONST'
      ,'acceptedValue' => array ('TRUE'=>'enabled'
                                ,'FALSE'=>'Disabled'
                              )
      );

$conf_def_property_list['sendByLocaleAivailable'] = 
array ('label'       => 'restaurer une archive de cours stockées sur le serveur'
      ,'description' => 'is_allowedToRestore must be enabled' 
      ,'default'     => 'FALSE'
      ,'display'     => FALSE
      ,'type'        => 'boolean'
      ,'container'   => 'CONST'
      ,'acceptedValue' => array ('TRUE'=>'enabled'
                              ,'FALSE'=>'Disabled'
                              )
      );

$conf_def_property_list['sendByHTTPAivailable'] = 
array ('label'       => 'restaurer une archive de cours présente sur un autre serveur web'
      ,'description' => 'is_allowedToRestore must be enabled' 
      ,'default'     => 'FALSE'
      ,'type'        => 'boolean'
      ,'display'     => FALSE
      ,'readonly'    => TRUE
      ,'container'   => 'CONST'
      ,'acceptedValue' => array ('TRUE'=>'enabled'
                              ,'FALSE'=>'Disabled'
                              )
      );

$conf_def_property_list['sendByFTPAivailable'] = 
array ('label'       => 'Restaurer une archive de cours présente sur un serveur FTP'
      ,'description' => 'is_allowedToRestore must be enabled' 
      ,'default'     => 'FALSE'
      ,'type'        => 'boolean'
      ,'display'     => FALSE
      ,'readonly'    => TRUE
      ,'container'   => 'CONST'
      ,'acceptedValue' => array ('TRUE'=>'enabled'
                              ,'FALSE'=>'Disabled'
                              )
      );

$conf_def_property_list['localArchivesRepository'] = 
array ('label'       => 'Repository where stored archives on server'
      ,'default'     => realpath($rootSys."archive/")
      ,'type'        => 'filepath'
      );


// Course properties rules
$conf_def['section']['expiration']['label']='Fix a delay for consider a course as expired';
$conf_def['section']['expiration']['properties'] = 
array ( 'firstExpirationDelay'
      );

$conf_def_property_list['firstExpirationDelay'] = 
array ('label'       => 'Time to expire the created course (in second)'
      ,'default'     => '31536000' // <- 86400*365    // 60*60*24 = 1 jour = 86400
      ,'unit'        => 'second'
      ,'type'        => 'integer'
      );

?>