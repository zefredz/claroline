<?php // $Id$
/**
 * This file describe the parameter for Course creation tool config file
 * @author Christophe Gesché <moosh@claroline.net>
 * @version CLAROLINE 1.6
 * @copyright &copy; 2001-2005 Universite catholique de Louvain (UCL)
 * @license This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
 * as published by the FREE SOFTWARE FOUNDATION. The GPL is available 
 * through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
 * @see http://www.claroline.net/wiki/config_def/
 * @package COURSES
 */

$conf_def['config_code']='CLADDCRS';
$conf_def['config_name']='General setting for course creation';
$conf_def['config_file']='core.add_course.conf.php';
$conf_def['old_config_file'][]='add_course.conf.php';
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
array ('label'       => 'All courses manager can create a course from an archive'
      ,'default'     => 'FALSE'
      ,'type'        => 'boolean'
      ,'display'     => TRUE
      ,'readonly'    => FALSE
      ,'acceptedValue' => array ('TRUE'  =>'Enabled'
                                ,'FALSE' =>'Disabled'
                                )
      );

$conf_def_property_list['HUMAN_CODE_NEEDED'] = 
array ('label'       => 'Whether user can leave course code (officialCode) field empty'
      ,'default'     => 'TRUE'
      ,'type'        => 'boolean'
      ,'container'   => 'CONST'
      ,'acceptedValue' => array ('TRUE' => 'Enabled'
                                ,'FALSE'=> 'Disabled'
                                )
      );

$conf_def_property_list['HUMAN_LABEL_NEEDED'] = 
array ('label'       => 'Whether user can leave course label (name) field empty'
      ,'default'     => 'TRUE'
      ,'type'        => 'boolean'
      ,'container'   => 'CONST'
      ,'acceptedValue' => array ('TRUE'=>'Enabled'
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
      ,'acceptedValue' => array ('TRUE'=>'Enabled'
                              ,'FALSE'=>'Disabled'
                              )
      );

$conf_def_property_list['prefixAntiNumber'] = 
array ('label'       => 'This string is prepend to code if begin with a number'
      ,'default'     => 'No'
      ,'type'        => 'string'
      );

$conf_def_property_list['prefixAntiEmpty'] = 
array ('label'       => 'Prefix for empty code course'
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
      ,'acceptedValue' => array ('TRUE'=>'Enabled'
                              ,'FALSE'=>'Disabled'
                              )
      );

$conf_def_property_list['sendByUploadAivailable'] = 
array ('label'       => 'Course creator can upload an archive to restore as new course'
      ,'description' => 'is_allowedToRestore must be Enabled' 
      ,'default'     => 'FALSE'
      ,'type'        => 'boolean'
      ,'display'     => FALSE
      ,'container'   => 'CONST'
      ,'acceptedValue' => array ('TRUE'=>'Enabled'
                                ,'FALSE'=>'Disabled'
                              )
      );

$conf_def_property_list['sendByLocaleAivailable'] = 
array ('label'       => 'Course manager can restore a local archive'
      ,'description' => 'is_allowedToRestore must be Enabled' 
      ,'default'     => 'FALSE'
      ,'display'     => FALSE
      ,'type'        => 'boolean'
      ,'container'   => 'CONST'
      ,'acceptedValue' => array ('TRUE'=>'Enabled'
                              ,'FALSE'=>'Disabled'
                              )
      );

$conf_def_property_list['sendByHTTPAivailable'] = 
array ('label'       => 'Course manager can restore an archive from the web'
      ,'description' => 'is_allowedToRestore must be Enabled' 
      ,'default'     => 'FALSE'
      ,'type'        => 'boolean'
      ,'display'     => FALSE
      ,'readonly'    => TRUE
      ,'container'   => 'CONST'
      ,'acceptedValue' => array ('TRUE'=>'Enabled'
                              ,'FALSE'=>'Disabled'
                              )
      );

$conf_def_property_list['sendByFTPAivailable'] = 
array ('label'       => 'Restaurer une archive de cours présente sur un serveur FTP'
      ,'description' => 'is_allowedToRestore must be Enabled' 
      ,'default'     => 'FALSE'
      ,'type'        => 'boolean'
      ,'display'     => FALSE
      ,'readonly'    => TRUE
      ,'container'   => 'CONST'
      ,'acceptedValue' => array ('TRUE'=>'Enabled'
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