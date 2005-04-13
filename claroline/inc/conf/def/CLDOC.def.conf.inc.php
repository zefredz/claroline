<?php //$Id$
/**
 * CLAROLINE 
 *
 * This file describe the parameter for CLDOC config file
 *
 * @version 1.6 $Revision$
 *
 * @copyright 2001-2005 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @see http://www.claroline.net/wiki/index.php/Config
 *
 * @package CLDOC
 *
 * @author Claro Team <cvs@claroline.net>
 * @author Hugues Peeters <peeters@ipm.ucl.ac.be> (core)
 * @author Frederic Minne <minne@ipm.ucl.ac.be>   (gallery)
 *
 */

// CONFIG HEADER

$conf_def['config_code'] = 'CLDOC';
$conf_def['config_file'] = 'CLDOC.conf.php';
$conf_def['config_name'] = 'Documents and Links tool';
$conf_def['config_class']= 'tool';

// CONFIG SECTIONS
$conf_def['section']['quota']['label']='Quota';
$conf_def['section']['quota']['description']='Disk space allowed for documents';
$conf_def['section']['quota']['properties'] = 
array ( 'maxFilledSpace_for_course'
      , 'maxFilledSpace_for_groups'
      );
      
// CONFIG PROPERTIES
$conf_def_property_list['maxFilledSpace_for_course']
= array ('label'     => 'Quota for courses'
	,'description' => 'Disk space allowed to each course'
        ,'default'   => '100000000'
        ,'unit'      => 'bytes'
        ,'type'      => 'integer'
        ,'container' => 'VAR'
        ,'acceptedValue' => array('min' => '1024')
        );

$conf_def_property_list['maxFilledSpace_for_groups']
= array ('label'     => 'Quota for groups'
	,'description' => 'Disk space allowed to each group'
        ,'default'   => '1000000'
        ,'unit'      => 'bytes'
        ,'type'      => 'integer'
        ,'container' => 'VAR'
        ,'acceptedValue' => array('min' => '1024')
        );
        
// IMAGE VIEWER        

$conf_def['section']['img_viewer']['label']='Image Viewer';
$conf_def['section']['img_viewer']['description']='Display options for Image Viewer';
$conf_def['section']['img_viewer']['properties'] = 
array ( 'thumbnailWidth'
      , 'numberOfRows'
      , 'numberOfCols'
      );
      
// CONFIG PROPERTIES
$conf_def_property_list['thumbnailWidth']
= array ('label'     => 'Thumbnail width'
	// ,'description' => ''
        ,'default'   => '75'
        ,'unit'      => 'pixels'
        ,'type'      => 'integer'
        ,'container' => 'VAR'
        ,'acceptedValue' => array('min' => '5')
        );

$conf_def_property_list['numberOfRows']
= array ('label'     => 'Number of rows'
	    ,'description' => 'Number of rows displayed per pages'
        ,'default'   => '3'
        ,'unit'      => 'rows'
        ,'type'      => 'integer'
        ,'container' => 'VAR'
        ,'acceptedValue' => array('min' => '1')
        );

$conf_def_property_list['numberOfCols']
= array ('label'     => 'Number of columns'
     	,'description' => 'Number of columns displayed per pages'
        ,'default'   => '4'
        ,'unit'      => 'columns'
        ,'type'      => 'integer'
        ,'container' => 'VAR'
        ,'acceptedValue' => array('min' => '1')
        );
        
?>