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
$conf_def['config_class'] ='kernel';


//SECTION
$conf_def['section']['main']['label']='Main settings';
//$conf_def['section']['main']['description']='Settings of the tool';
$conf_def['section']['main']['properties'] = 
array ( 'rssRepositoryCacheSys'
      );

//PROPERTIES

$conf_def_property_list['rssRepositoryCacheSys'] =
array ('label'         => 'Where place rss files.'
      , 'description'  => 'Note :  this repository would be protect with an .htaccess or be out the web' 
      ,'default'       => $rootSys . 'cache/rss/'
      ,'type'          => 'syspath'
      );

?>