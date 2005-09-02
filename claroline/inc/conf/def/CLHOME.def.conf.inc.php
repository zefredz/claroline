<?php //$Id$
/**
 * CLAROLINE 
 *
 * This file describe the parameter for the home page of the campus
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
 * @package CLHOME
 *
 */
// TOOL
$conf_def['config_code'] = 'CLHOME';
$conf_def['config_file'] = 'CLHOME.conf.php';
$conf_def['config_name'] = 'Home page';
$conf_def['config_class']='platform';

$conf_def['section']['rightmenu']['label']='Right menu settings';
$conf_def['section']['rightmenu']['description']='Settings of the right menu elements';
$conf_def['section']['rightmenu']['properties'] =
array ( 'max_char_from_content', 'allow_to_self_enroll'
      );

//PROPERTIES
$conf_def_property_list['max_char_from_content']
= array ('label'     => 'Last event length'
        ,'description' => 'Max length of the \'last events\' displayed content'
        ,'default'   => '80'
        ,'unit'     => 'characters'
        ,'type'      => 'integer'
        ,'display'       => TRUE
        ,'readonly'      => FALSE
        ,'acceptedValue' => array ( 'min'=> '0' )
        );

$conf_def_property_list['allow_to_self_enroll']
= array ('label'     => 'Personnal course list modification'
        ,'description' => 'Set if the users are allowed to modify their personnal courses list or not'
        ,'default'   => 'TRUE'
        ,'type'      => 'boolean'
        ,'display'       => TRUE
        ,'readonly'      => FALSE
        ,'acceptedValue' => array ( 'TRUE'=> 'allowed', 'FALSE'=>'not allowed' )
        );
                
?>
