<?php // $Id$
/**
 * This file describe the parameter for Course creation tool config file
 * @author Christophe Gesché <moosh@claroline.net>
 * @version CLAROLINE 1.6
 * @copyright &copy; 2001-2005 Universite catholique de Louvain (UCL)
 * @license This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
 * as published by the FREE SOFTWARE FOUNDATION. The GPL is available 
 * through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
 * @see http://www.claroline.net/wiki/index.php/Config
 * @package COURSES
 */

$conf_def['config_code']='CLCRS';
$conf_def['config_name']='Course options';
$conf_def['config_file']='course_main.conf.php';
$conf_def['old_config_file'][]='add_course.conf.php';

$conf_def['section']['create']['label']='Course parameters';
$conf_def['section']['create']['description']='These settings will be use whenever a user creates a new course';
$conf_def['section']['create']['properties'] = 
array ( 'defaultVisibilityForANewCourse'
      , 'HUMAN_CODE_NEEDED'
      , 'HUMAN_LABEL_NEEDED'
      , 'COURSE_EMAIL_NEEDED'
      , 'extLinkNameNeeded'
      , 'extLinkUrlNeeded'
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

$conf_def_property_list['HUMAN_CODE_NEEDED'] = 
array ('label'       => 'Course code is'
      ,'description' => 'User can leave course code (officialCode) field empty or not'
      ,'default'     => 'TRUE'
      ,'type'        => 'boolean'
      ,'container'   => 'CONST'
      ,'acceptedValue' => array ('TRUE' => 'Required'
                                ,'FALSE'=> 'Optional'
                                )
      );

$conf_def_property_list['HUMAN_LABEL_NEEDED'] = 
array ('label'       => 'Course label (name) is'
      ,'description' => 'User can leave course label (name) field empty or not'
      ,'default'     => 'TRUE'
      ,'type'        => 'boolean'
      ,'container'   => 'CONST'
      ,'acceptedValue' => array ('TRUE'=>'Required'
                              ,'FALSE'=>'Optional'
                              )
      );

$conf_def_property_list['COURSE_EMAIL_NEEDED'] = 
array ('label'       => 'Course email email is'
      ,'description' => 'User can leave email field empty or not'
      ,'default'     => 'FALSE'
      ,'type'        => 'boolean'
      ,'display'     => TRUE
      ,'readonly'    => FALSE
      ,'container'   => 'CONST'
      ,'acceptedValue' => array ('TRUE'=>'Required'
                              ,'FALSE'=>'Optional'
                              )
      );

$conf_def_property_list['extLinkNameNeeded'] = 
array ('label'       => 'Label of external Link is'
      ,'description' => 'This name is show in top right of course banner'
      ,'default'     => 'FALSE'
      ,'type'        => 'boolean'
      ,'display'     => TRUE
      ,'readonly'    => FALSE
      ,'acceptedValue' => array ('TRUE'=>'Required (choose this is Url of external Link is set)'
                              ,'FALSE'=>'Optional'
                              )
      );
$conf_def_property_list['extLinkUrlNeeded'] = 
array ('label'       => 'Url of external Link is Label of external Link'
      ,'description' => 'This url is under the '
      ,'default'     => 'FALSE'
      ,'type'        => 'boolean'
      ,'display'     => TRUE
      ,'readonly'    => FALSE
      ,'acceptedValue' => array ('TRUE'=>'Required'
                              ,'FALSE'=>'Optional'
                              )
      );

$conf_def_property_list['prefixAntiNumber'] = 
array ('label'       => 'Prefix course code beginning with number'
      ,'description' => 'This string is prepend to course database name if it begins with a number'
      ,'default'     => 'No'
      ,'display'     => FALSE
      ,'readonly'    => TRUE
      ,'type'        => 'string'
      );

$conf_def_property_list['prefixAntiEmpty'] = 
array ('label'       => 'Prefix for empty code course'
      ,'default'     => 'Course'
      ,'display'     => FALSE
      ,'readonly'    => TRUE
      ,'type'        => 'string'
      );

// Course Setting Section

$conf_def['section']['links']['label']='Course settings';
$conf_def['section']['links']['description']='These settings will be use whenever a user modify course settings';
$conf_def['section']['links']['properties'] =
array ( 'showLinkToDeleteThisCourse'
      );

$conf_def_property_list['showLinkToDeleteThisCourse']
= array ('label'     => 'Allow course manager to delete course'
        ,'default'   => 'TRUE'
        ,'type'      => 'boolean'
        ,'container' => 'VAR'
        ,'acceptedValue' => array ('TRUE'  => 'Yes'
                                  ,'FALSE' => 'No'
                                  )
        );

?>