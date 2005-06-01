<?php // $Id$
/**
 * CLAROLINE 
 *
 * This file describe the parameter for user tool
 *
 * @version 1.6 $Revision$
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
$conf_def['config_file']  = 'rss.conf.inc.php';
$conf_def['config_name']  = 'Rss (read and write) tool';
$conf_def['config_class'] ='kernel';


//SECTION
$conf_def['section']['main']['label']='Main settings';
//$conf_def['section']['main']['description']='Settings of the tool';
$conf_def['section']['main']['properties'] = 
array ( 'rssRepository'
      );

//PROPERTIES

$conf_def_property_list['rssRepository'] =
array ('label'         => 'Where place rss files'
       ,'default'       => 'rss/'
      ,'type'          => 'relsys'
      );

?>