<?php // $Id$
/**
 * This file describe the parameter for CLCAL config file
 *
 * @author Christophe Gesché <moosh@claroline.net>
 * @version CLAROLINE 1.6
 * @copyright &copy; 2001-2005 Universite catholique de Louvain (UCL)
 * @license This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
 * as published by the FREE SOFTWARE FOUNDATION. The GPL is available 
 * through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
 * @see http://www.claroline.net/wiki/config_def/
 * @package CLCAL
 */

$conf_def['config_code']='CLCAL';
$conf_def['config_name']='Agenda tool';
$conf_def['config_file']='CLCAL.conf.php';
$conf_def['old_config_file'][]='agenda.conf.inc.php';
// $conf_def['config_repository']=''; Disabled = includePath.'/conf'


$conf_def['section']['main']['label']='Main settings';
$conf_def['section']['main']['description']='Settings of the tool';
$conf_def['section']['main']['properties'] = 
array ( 'defaultOrder'
      );

$conf_def_property_list['defaultOrder'] = 
array ('label'       => 'Default order'
      ,'description' => 'Events can appear by newest event first or oldest event first'
      ,'default'     => 'asc'
      ,'type'        => 'enum'
      ,'display'     => TRUE
      ,'readonly'    => FALSE
      ,'container'   => 'CONST'
      ,'acceptedValue' => array ('asc'=>'Ascending'
                                ,'desc'=>'Descending'
                              )
      );

?>
