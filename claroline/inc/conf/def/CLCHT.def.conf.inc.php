<?php // $Id$
/**
 * This file describe the parameter for CLCHAT config file
 *
 * @author Christophe Gesché <moosh@claroline.net>
 * @author Hugues Peeters    <peeters@ipm.ucl.ac.be>
 * @version CLAROLINE 1.6
 * @copyright 2001-2005 Universite catholique de Louvain (UCL)
 * @license This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
 * as published by the FREE SOFTWARE FOUNDATION. The GPL is available 
 * through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
 * @see http://www.claroline.net/wiki/config_def/
 * @package CLCHAT
 */

$conf_def['config_file']='CLCHT.conf.php';
$conf_def['config_code']='CLCHT';
$conf_def['config_name']='Chat tool';

$conf_def['section']['main']['label']='Main Settings';
$conf_def['section']['main']['properties'] = 
array ( 'REFRESH_DISPLAY_RATE'
      , 'MAX_LINE_TO_DISPLAY'
      ,'MAX_LINE_IN_FILE'
      );

$conf_def_property_list['REFRESH_DISPLAY_RATE'] =
array ( 'label'       => 'Refresh time'
      , 'description' => 'Time to automatically refresh the user screen'
      , 'default'     => '10'
      , 'unit'        => 'seconds'
      , 'type'        => 'integer'
      );
      
$conf_def_property_list['MAX_LINE_TO_DISPLAY'] =
array ( 'label'         => 'Maximum conversation lines'
      , 'description'   => 'Maximum conversation lines displayed to the user. ' 
      , 'default'       => '20'
      , 'acceptedValue' => array( 'min' => 5, 'max' => 120)
      , 'unit'          => 'lines'
      , 'type'          => 'integer'
      );

$conf_def_property_list['MAX_LINE_IN_FILE'] = 
array ( 'label'       => 'Maximum conversation lines in buffer'
      , 'description' => 'Maximum lines in the active chat file. '
                        .'For performance, it\'s interresting '
                        .'to not work with too big file'      
      , 'default'     => '200'
      , 'unit'        => 'lines'
      , 'type'        => 'integer'
      );
      
?>