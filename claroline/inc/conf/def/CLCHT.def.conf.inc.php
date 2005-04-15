<?php // $Id$
/**
 * CLAROLINE 
 *
 * This file describe the parameter for CLCHAT config file
 *
 * @version 1.6 $Revision$
 *
 * @copyright 2001-2005 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @see http://www.claroline.net/wiki/index.php/Config
 * @see http://www.claroline.net/wiki/index.php/CLCHT
 *
 * @author Claro Team <cvs@claroline.net>
 *
 * @package CLCHAT
 */

$conf_def['config_file']='CLCHT.conf.php';
$conf_def['config_code']='CLCHT';
$conf_def['config_name']='Chat tool';
$conf_def['config_class']='tool';


$conf_def['section']['main']['label']='Main Settings';
$conf_def['section']['main']['properties'] = 
array ( 'refresh_display_rate'
      ,'max_line_in_file'
      );

$conf_def['section']['display']['label']='Display Settings';
$conf_def['section']['display']['properties'] = 
array ( 'max_nick_lenght'
      , 'max_line_to_display'
      );

$conf_def_property_list['refresh_display_rate'] =
array ( 'label'       => 'Refresh time'
      , 'description' => 'Time to automatically refresh the user screen'."\n"
                       . 'Each refresh is a request to your server.'."\n"
                       . 'Too low value can be hard for your server.'."\n"
                       . 'Too high value can be hard for user.'."\n"
      , 'default'     => '10'
      , 'unit'        => 'seconds'
      , 'acceptedValue' => array( 'min' => 4, 'max' => 90)
      , 'type'        => 'integer'
      );
      
$conf_def_property_list['max_line_to_display'] =
array ( 'label'         => 'Maximum conversation lines'
      , 'description'   => 'Maximum conversation lines displayed to the user. ' 
      , 'technicalInfo'   => 'Maximum line diplayed to the user screen. As the active chat file is 
      regularly shrinked (see max_line_in_file), keeping this parameter smaller 
      than  $max_line_in_file allows smooth display (where no big line chunk are 
      removed when the excess line from the active chat file are buffered on fly'

      , 'default'       => '20'
      , 'acceptedValue' => array( 'min' => 5, 'max' => 120)
      , 'unit'          => 'lines'
      , 'type'          => 'integer'
      );

$conf_def_property_list['max_line_in_file'] = 
array ( 'label'       => 'Maximum conversation lines in buffer'
      , 'description' => 'Maximum lines in the active chat file. '
                        .'For performance, it\'s interresting '
                        .'to not work with too big file.'."\n"
                        .' Note that this value don\'t reduce the saved file'
      , 'default'     => '200'
      , 'unit'        => 'lines'
      , 'type'        => 'integer'
      );
      
$conf_def_property_list['max_nick_lenght'] = 
array ( 'label'       => 'Maximum lengh for a nick'
      , 'description' => 'If  name and firstname is longer '
                       . 'than this value, the script reduce it.'."\n"
                       . 'For revelance, it\'s interresting '
                       . 'to not work with to littel value'      
      , 'default'     => '20'
      , 'unit'        => 'charachers'
      , 'acceptedValue' => array( 'min' => 5, 'max' => 60)
      , 'type'        => 'integer'
      );
      
?>