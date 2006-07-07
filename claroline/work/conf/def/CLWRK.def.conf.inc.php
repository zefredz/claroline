<?php // $Id$
if ( count( get_included_files() ) == 1 ) die( '---' );
/**
 * CLAROLINE
 *
 * This file describe the parameter for assignment tool
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
 * @package CLWRK
 *
 */
// TOOL
$conf_def['config_code'] = 'CLWRK';
$conf_def['config_file'] = 'CLWRK.conf.php';
$conf_def['config_name'] = 'Assignments tool';
$conf_def['config_class']= 'tool';

$conf_def['section']['main']['label']      = ' Main';
$conf_def['section']['main']['properties'] =
array ( 'confval_def_sub_vis_change_only_new', 'open_submitted_file_in_new_window' );

$conf_def['section']['quota']['label']      = 'Quota';
$conf_def['section']['quota']['description']= 'Disk space allowed for submitted files';
$conf_def['section']['quota']['properties'] =
array ( 'max_file_size_per_works' );


//PROPERTIES

$conf_def_property_list['confval_def_sub_vis_change_only_new'] =
array ('label'     => 'Assignment property "Default works visibility" acts'
        ,'description' => 'Sets how the assignment property "default works visibility" acts.  It will change the visibility of all the new submissions or it will change the visibility of all submissions already done in the assignment and the new one. '
        ,'default'   => TRUE
        ,'type'      => 'boolean'
        ,'display'       => TRUE
        ,'readonly'      => FALSE
        ,'acceptedValue' => array ( 'TRUE'=> 'only for new works', 'FALSE'=>'for current and new works' )
        );

$conf_def_property_list['open_submitted_file_in_new_window'] =
array ( 'description' => 'When users click on a submitted file, it opens a new window'
      , 'label'       => 'New window for submitted files'
      , 'default'     => FALSE
      , 'type'        => 'boolean'
      , 'acceptedValue' => array ('TRUE'=>'Yes'
                               ,'FALSE'=>'No'
                               )
      , 'display'     => TRUE
      , 'readonly'    => FALSE
      );

$conf_def_property_list['max_file_size_per_works'] =
array ('label'         => 'Maximum size for an assignment'
      ,'description'   => 'Maximum size of a document that a user can upload'
      ,'display'       => TRUE
      ,'readonly'      => FALSE
      ,'default'       => '200000000'
      ,'type'          => 'integer'
      ,'unit'          => 'bytes'
      );

?>