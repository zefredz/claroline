<?php // $Id$
if ( count( get_included_files() ) == 1 ) die( '---' );
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
array ( 'enableICalInCourse'
      , 'defaultEventDuration'
      , 'iCalGenStandard'
      , 'iCalGenXml'
      , 'iCalGenRdf'
      );

$conf_def['section']['cache']['label']='Cache settings';
$conf_def['section']['cache']['properties'] =
array ( 'iCalRepositoryCache'
      , 'iCalUseCache'
      , 'iCalCacheLifeTime'
      , 'iCalRepositoryCache'
      );

//PROPERTIES

$conf_def_property_list['enableICalInCourse'] =
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

$conf_def_property_list['iCalUseCache'] =
array ('label'         => 'Use the cache'
      , 'description'  => 'File are always created in cache, but if this value is true feed file in cache arent rebuilt on request if exiting in cache.'
      ,'default'       => 'TRUE'
      ,'type'          => 'boolean'
      , 'readonly'      => FALSE
      , 'acceptedValue' => array('TRUE'=>'Use it, and build  on change', 'FALSE' => 'rebuild file on each request')
      );

$conf_def_property_list['iCalGenStandard'] =
array ('label'         => 'Generate ics file'
      , 'description'  => 'When iCal File is regenerated, make the ics version.'
      ,'default'       => 'TRUE'
      ,'type'          => 'boolean'
      , 'display'      => true
      , 'readonly'      => FALSE
      , 'acceptedValue' => array('TRUE'=>'Yes, create ics version', 'FALSE' => 'No')
      );

      $conf_def_property_list['iCalGenXml'] =
array ('label'         => 'Generate Xml file'
      , 'description'  => 'When iCal File is regenerated, make the xml version.'
      ,'default'       => 'TRUE'
      ,'type'          => 'boolean'
      , 'display'      => true
      , 'readonly'      => FALSE
      , 'acceptedValue' => array('TRUE'=>'Yes, create XML version', 'FALSE' => 'No')
      );

$conf_def_property_list['iCalGenRdf'] =
array ('label'         => 'Generate RDF file'
      , 'description'  => 'When iCal File is regenerated, make the RDF version.'
      , 'default'       => 'FALSE'
      , 'type'          => 'boolean'
      , 'display'      => true
      , 'readonly'      => FALSE
      , 'acceptedValue' => array('TRUE'=>'Yes, create RDF version', 'FALSE' => 'No')
      );


$conf_def_property_list['defaultEventDuration'] =
array (
        'label'         => 'Event duration'
      , 'description'   => 'In iCal, an event have a duration, but not in claroline. 3600 = 1 Hour.'
      , 'default'       => '3600'
      , 'type'           => 'integer'
      , 'unit'           => 'seconds'
      , 'display'      => true
      , 'readonly'      => FALSE
      , 'acceptedValue' => array('min'=> '1', 'max' => '86400')
      );


$conf_def_property_list['iCalCacheLifeTime'] =
array (
        'label'         => 'Life time of cache'
      , 'description'   => 'time before really compute data. 86400 = 1 day.'
      , 'default'       => '86400'
      , 'type'           => 'integer'
      , 'unit'           => 'seconds'
      , 'display'      => true
      , 'readonly'      => FALSE
      , 'acceptedValue' => array('min'=> '360', 'max' => '8640000')
      );

?>