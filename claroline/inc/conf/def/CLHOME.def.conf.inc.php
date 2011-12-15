<?php //$Id$
if ( count( get_included_files() ) == 1 ) die( '---' );
/**
 * CLAROLINE 
 *
 * This file describe the parameter for the home page of the campus
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
 * @package CLHOME
 *
 */

// TOOL
$conf_def['config_code'] = 'CLHOME';
$conf_def['config_file'] = 'CLHOME.conf.php';
$conf_def['config_name'] = 'Home page';
$conf_def['config_class']='platform';

$conf_def['section']['courselist']['label']='Course list';
$conf_def['section']['courselist']['description']='Settings of the user course list';
$conf_def['section']['courselist']['properties'] =
array ( 'course_order_by',
        'course_categories_hidden_to_anonymous',
        'userCourseListGroupByCategories' );

//PROPERTIES

$conf_def_property_list['course_order_by']
= array ('label'     => 'Order course by'
        ,'description' => ''
        ,'default'   => 'official_code'
        ,'type'      => 'enum'
        ,'display'       => TRUE
        ,'readonly'      => FALSE
        ,'acceptedValue' => array ( 'official_code'=> 'Course code',
                                    'course_title' => 'Course title' )
        );

$conf_def_property_list['course_categories_hidden_to_anonymous']
= array ('label'     => 'Hide course categories to anonymous'
        ,'description' => ''
        ,'default'   => false
        ,'type'      => 'boolean'
        ,'display'       => TRUE
        ,'readonly'      => FALSE
        ,'acceptedValue' => array ( 'TRUE'=> 'Yes',
                                    'FALSE' => 'No' )
        );

$conf_def_property_list['userCourseListGroupByCategories']
= array ('label'     => 'Group user courses by categories'
        ,'description' => ''
        ,'default'   => false
        ,'type'      => 'boolean'
        ,'display'       => TRUE
        ,'readonly'      => FALSE
        ,'acceptedValue' => array ( 'TRUE'=> 'Yes',
                                    'FALSE' => 'No' )
        );
