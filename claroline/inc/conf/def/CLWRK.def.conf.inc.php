<?php //$Id$
/**
 * CLAROLINE 
 *
 * This file describe the parameter for assignment tool
 *
 * @version 1.7 $Revision$
 *
 * @copyright 2001-2005 Universite catholique de Louvain (UCL)
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

$conf_def['section']['main']['label']      = ' Main settings';
$conf_def['section']['main']['properties'] = 
array ( 'confval_def_sub_vis_change_only_new', 'show_only_author', 'mail_notification' );

$conf_def['section']['quota']['label']      = 'Quota';
$conf_def['section']['quota']['properties'] = 
array ( 'max_file_size_per_works' );


//PROPERTIES

$conf_def_property_list['confval_def_sub_vis_change_only_new'] =
array ('label'     => 'Assignment property "Default works visibility" acts'
        ,'description' => 'Sets how the assignment property "default works visibility" acts.  It will change the visibility of all the new submissions or it will change the visibility of all submissions already done in the assignment and the new one. '
        ,'default'   => 'TRUE'
        ,'type'      => 'boolean'
        ,'display'       => TRUE
        ,'readonly'      => FALSE
        ,'acceptedValue' => array ( 'TRUE'=> 'only for new works', 'FALSE'=>'for current and new works' )
        );

$conf_def_property_list['show_only_author'] =
array ('label'     => 'Show only author submissions'
        ,'description' => 'Sets if user can see only his own submissions (or those from his groups) or if he can see every visible submission.'
        ,'default'   => 'FALSE'
        ,'type'      => 'boolean'
        ,'display'       => TRUE
        ,'readonly'      => FALSE
        ,'acceptedValue' => array ( 'TRUE'=> 'Only his own submissions', 'FALSE'=>'All visible submissions' )
        );
        
$conf_def_property_list['mail_notification'] =
array ('label'     => 'Mail notification'
        ,'description' => 'If activated course administrator will receive an email every time a submission is made.  Students will receive an email adivising them that their work has been marked.'
        ,'default'   => 'FALSE'
        ,'type'      => 'boolean'
        ,'display'       => TRUE
        ,'readonly'      => FALSE
        ,'acceptedValue' => array ( 'TRUE'=> 'Yes', 'FALSE'=>'No' )
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