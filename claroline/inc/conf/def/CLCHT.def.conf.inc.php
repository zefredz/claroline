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

$conf_def['config_file']='CLCHT___.conf.php';
$conf_def['config_code']='CLCHT';
$conf_def['config_name']='General setting for chat tool';
$conf_def['description']='Note : these value would be COPY in the script to win an include.';

$conf_def['section']['buffer']['label']='buffer';
$conf_def['section']['buffer']['properties'] = 
array ( 'MAX_LINE_IN_FILE'
      , 'MAX_LINE_TO_DISPLAY'
      , 'REFRESH_DISPLAY_RATE'
      );

      
$conf_def_property_list['MAX_LINE_IN_FILE'] = 
array ( 'description' => 'Max line in the active chat file. '
                        .'For performance reason it is interesting '
                        .'to work with not too big file'
      , 'label'       => 'Max quantity of lines in buffer'
      , 'default'     => '200'
      , 'unit'         => 'lines'
      , 'type'        => 'integer'
      );

$conf_def_property_list['MAX_LINE_TO_DISPLAY'] =
array ( 'description'   => 'Maximum line diplayed to the user screen. ' 
                          .'As the active chat file is regularly shrinked '
                          .'(see max_line_in_file), '
                          .'keeping this parameter smaller than '
                          .'max_line_in_file allows smooth display '
                          .'(where no big line chunk are removed when '
                          .'the excess line from the active chat file are buffered on fly'
      , 'label'         => 'Max Quantity of line on screen'
      , 'default'       => '20'
      , 'acceptedValue' => array( 'min' => 5, 'max' => 120)
      , 'unit'          => 'lines'
      , 'type'          => 'integer'
      );
      
$conf_def_property_list['REFRESH_DISPLAY_RATE'] =
array ( 'description' => 'Time to automaticly refresh  user screen'
      , 'label'       => 'Delay in second'
      , 'default'     => '10'
      , 'unit'        => 'second'
      , 'type'        => 'integer'
      );
?>