<?php //$Id$
/**
 * This file describe the parameter for CLDOC config file
 *
 * @author Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @version CLAROLINE 1.6
 * @copyright &copy; 2001-2005 Universite catholique de Louvain (UCL)
 * @license This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
 * as published by the FREE SOFTWARE FOUNDATION. The GPL is available 
 * through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
 * @see http://www.claroline.net/wiki/config_def/
 * @package CLDOC
 */

// CONFIG HEADER

$conf_def['config_code'] = 'CLDOC';
$conf_def['config_file'] = 'CLDOC.conf.php';
$conf_def['config_name'] = 'Documents and Links tool';

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
?>
