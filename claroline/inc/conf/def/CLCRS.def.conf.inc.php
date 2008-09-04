<?php // $Id$
if ( count( get_included_files() ) == 1 ) die( '---' );
/**
 * CLAROLINE
 *
 * This file describe the parameter for Course creation tool config file
 *
 * @version 1.9 $Revision$
 *
 * @copyright 2001-2007 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @see http://www.claroline.net/wiki/index.php/Config
 *
 * @author Claro Team <cvs@claroline.net>
 * @author Christophe Gesché <moosh@claroline.net>
 *
 * @package COURSE
 */

$conf_def['config_code']='CLCRS';
$conf_def['config_name']='Course options';
$conf_def['config_file']='course_main.conf.php';
$conf_def['old_config_file'][]='add_course.conf.php';
$conf_def['config_class']='course';

$conf_def['section']['create']['label']='Course main settings';
$conf_def['section']['create']['description']='';
$conf_def['section']['create']['properties'] =
array ( 'fill_course_example'
      , 'prefixAntiNumber'
      , 'prefixAntiEmpty'
      , 'showLinkToDeleteThisCourse'
      , 'nbCharFinalSuffix'
      , 'forceCodeCase'
      );

$conf_def['section']['create']['label']='Course needed settings';
$conf_def['section']['create']['description'] = 'Witch value are needed ?';
$conf_def['section']['create']['properties']  =
array ( 'human_code_needed'
      , 'human_label_needed'
      , 'course_email_needed'
      , 'extLinkNameNeeded'
      , 'extLinkUrlNeeded'
      );

$conf_def['section']['create']['label']='Course default settings';
$conf_def['section']['create']['description']='';
$conf_def['section']['create']['properties'] =
array (
      //, 'defaultVisibilityForANewCourse'
        'defaultAccessOnCourseCreation'
      , 'defaultRegistrationOnCourseCreation'
      , 'defaultVisibilityOnCourseCreation'
      );

$conf_def_property_list['fill_course_example'] =
array ('label'       => 'Fill courses tools with material example'
      ,'description' => ''
      ,'default'     => TRUE
      ,'type'        => 'boolean'
      ,'acceptedValue' => array ('TRUE' => 'Yes'
                                ,'FALSE'=> 'No'
                                )
      );

$conf_def_property_list['forceCodeCase'] =
array ('label'       => 'Course code case'
      ,'description' => 'You can force the case  of course code'
      ,'default'     => 'upper'
      ,'type'        => 'enum'
      ,'display'     => TRUE
      ,'readonly'    => FALSE
      ,'acceptedValue' => array ('upper'=>'Force to uppercase the course code'
                                ,'lower'=>'Force to lowercase the course code'
                                ,'nochange'=>'dont change case'
                                )
      );
      /*
$conf_def_property_list['defaultVisibilityForANewCourse'] =
array ('label'       => 'Default course access'
      ,'description' => ''
      ,'default'     => '2'
      ,'type'        => 'enum'
      ,'display'     => TRUE
      ,'readonly'    => FALSE
      ,'acceptedValue' => array ('0'=>'Private&nbsp;+ New registration denied'
                                ,'1'=>'Private&nbsp+ New Registration allowed'
                                ,'2'=>'Public&nbsp;&nbsp;+ New Registration allowed'
                                ,'3'=>'Public&nbsp;&nbsp;+ New Registration denied'
                                )
      );
*/
$conf_def_property_list['defaultVisibilityOnCourseCreation'] =
array ('label'       => 'Default course visibility'
      ,'description' => 'This is probably a bad idea to set as hidden'
      ,'default'     => TRUE
      ,'type'        => 'boolean'
      ,'display'     => TRUE
      ,'readonly'    => FALSE
      ,'acceptedValue' => array ('TRUE' => 'Show'
                                ,'FALSE'=> 'Hidden'
                                )
      );

$conf_def_property_list['defaultAccessOnCourseCreation'] =
array ('label'       => 'Default course access'
      ,'description' => ''
      ,'default'     => 'public'
      ,'type'        => 'enum'
      ,'display'     => TRUE
      ,'readonly'    => FALSE
      ,'acceptedValue' => array ('public' => 'Public'
                                ,'private'=> 'Reserved to course members'
                                ,'platform'=> 'Reserved to platform members'
                                )
      );


$conf_def_property_list['defaultRegistrationOnCourseCreation'] =
array ('label'       => 'Default course enrolment'
      ,'description' => ''
      ,'default'     => TRUE
      ,'type'        => 'boolean'
      ,'display'     => TRUE
      ,'readonly'    => FALSE
      ,'acceptedValue' => array ('TRUE' => 'New Registration allowed'
                                ,'FALSE'=> 'New registration denied'
                                )
      );


$conf_def_property_list['human_code_needed'] =
array ('label'       => 'Course code is'
      ,'description' => 'User can leave course code (officialCode) field empty or not'
      ,'default'     => TRUE
      ,'type'        => 'boolean'
      ,'display'     => false
      ,'readonly'    => true
      ,'acceptedValue' => array ('TRUE' => 'Required'
//                                ,'FALSE'=> 'Optional'
                                )
      );

$conf_def_property_list['human_label_needed'] =
array ('label'       => 'Course Title is'
      ,'description' => 'User can leave course title field empty or not'
      ,'default'     => TRUE
      ,'type'        => 'boolean'
      ,'acceptedValue' => array ('TRUE'=>'Required'
                              ,'FALSE'=>'Optional'
                              )
      );

$conf_def_property_list['course_email_needed'] =
array ('label'       => 'Course email is'
      ,'description' => 'User can leave email field empty or not'
      ,'default'     => FALSE
      ,'type'        => 'boolean'
      ,'display'     => true
      ,'readonly'    => FALSE
      ,'acceptedValue' => array ('TRUE'=>'Required'
                              ,'FALSE'=>'Optional'
                              )
      );

$conf_def_property_list['extLinkNameNeeded'] =
array ('label'       => 'Department name'
      ,'description' => ''
      ,'default'     => FALSE
      ,'type'        => 'boolean'
      ,'display'     => TRUE
      ,'readonly'    => FALSE
      ,'acceptedValue' => array ('TRUE'=>'Required'
                              ,'FALSE'=>'Optional'
                              )
      );
$conf_def_property_list['extLinkUrlNeeded'] =
array ('label'       => 'Department website'
      ,'description' => ''
      ,'default'     => FALSE
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

$conf_def_property_list['nbCharFinalSuffix'] =
array ('label'       => 'Length of course code suffix'
      ,'technicalInfo'=> 'Length of suffix added when key is already exist'
      ,'default'     => 3
      ,'display'     => FALSE
      ,'readonly'    => TRUE
      ,'type'        => 'integer'
      ,'acceptedValue' => array ('min'=> 1
                                ,'max'=> 10)
      );

// Course Setting Section

$conf_def_property_list['showLinkToDeleteThisCourse']
= array ('label'     => 'Delete course allowed'
        ,'description' => 'Allow course manager to delete their own courses'
        ,'default'   => TRUE
        ,'type'      => 'boolean'
        ,'container' => 'VAR'
        ,'acceptedValue' => array ('TRUE'  => 'Yes'
                                  ,'FALSE' => 'No'
                                  )
        );

?>
