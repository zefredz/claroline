<?php // $Id$
/**
 * CLAROLINE
 *
 * This file describe the parameter for user tool
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
 * @package CLICAL
 *
 */
// TOOL
$conf_def['config_code']  = 'CLICAL';
$conf_def['config_file']  = 'iCal.conf.php';
$conf_def['config_name']  = 'iCal generator';
$conf_def['config_class'] = 'kernel';

//SECTION
$conf_def['section']['main']['label']='Main settings';
$conf_def['section']['main']['properties'] =
array ( 'enable_iCal_in_course'
      , 'iCalRepositoryCache'
      , 'use_iCal_cache'
      );

//PROPERTIES

$conf_def_property_list['enable_iCal_in_course'] =
array ('label'         => 'Enable iCal in course'
      , 'description'  => ''
      ,'default'       => 'TRUE'
      ,'type'          => 'boolean'
      , 'readonly'      => FALSE
      , 'acceptedValue' => array('TRUE'=>'Yes', 'FALSE' => 'No')

      );

$conf_def_property_list['iCalRepositoryCache'] =
array ('label'         => 'Where place iCal files.'
      , 'description'  => 'Note :  this repository should be protected with a .htaccess or
       be placed outside the web. Because there contain data of private courses.'
      ,'default'       => 'tmp/cache/iCal/'
      ,'type'          => 'relpath'
      );

$conf_def_property_list['use_iCal_cache'] =
array ('label'         => 'Use the cache'
      , 'description'  => 'File are always created in cache, but if this value is true feed file in cache arent rebuilt on request if exiting in cache.'
      ,'default'       => 'TRUE'
      ,'type'          => 'boolean'
      , 'readonly'      => FALSE
      , 'acceptedValue' => array('TRUE'=>'Use it, and build  on change', 'FALSE' => 'rebuild file on each request')

      );

?>