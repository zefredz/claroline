<?php // $Id$
/**
 * CLAROLINE 
 *
 * This file describe the parameter for user tool
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
 * @package CLRSS
 *
 */
// TOOL
$conf_def['config_code']  = 'CLRSS';
$conf_def['config_file']  = 'rss.conf.php';
$conf_def['config_name']  = 'Rss (read and write) tool';
$conf_def['config_class'] = 'kernel';

//SECTION
$conf_def['section']['main']['label']='Main settings';
//$conf_def['section']['main']['description']='Settings of the tool';
$conf_def['section']['main']['properties'] = 
array ( 'rssRepositoryCache'
      , 'use_rss_cache'
      );

//PROPERTIES

$conf_def_property_list['rssRepositoryCache'] =
array ('label'         => 'Where place rss files.'
      , 'description'  => 'Note :  this repository should be protected with a .htaccess or
       be placed outside the web. Because there contain data of private courses.' 
      ,'default'       => 'cache/rss/'
      ,'type'          => 'relpath'
      );
$conf_def_property_list['use_rss_cache'] =
array ('label'         => 'Use the cache'
      , 'description'  => 'File are always created in cache, but if this value is true feed file in cache arent rebuilt on request if exiting in cache.' 
      ,'default'       => 'TRUE'
      ,'type'          => 'boolean'
      , 'readonly'      => FALSE 
      , 'acceptedValue' => array('TRUE'=>'Use it, and build  on change', 'FALSE' => 'rebuild file on each request')
      
      );

?>