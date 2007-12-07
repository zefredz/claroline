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
 * @package CLUSR
 *
 */
// TOOL
$conf_def['config_code'] = 'CLLINKER';
$conf_def['config_file'] = 'linker.conf.php';
$conf_def['config_name'] = 'Linker tool';
$conf_def['config_class']='kernel';


//SECTION
$conf_def['section']['main']['label']='Main settings';
//$conf_def['section']['main']['description']='Settings of the tool';
$conf_def['section']['main']['properties'] =
array ( 'jpspanAllowed'
      , 'otherCoursesAllowed'
      , 'publicCoursesAllowed'
      , 'externalLinkAllowed'
      , 'groupAllowed'
      , 'toolGroupAllowed'
      );

//PROPERTIES

$conf_def_property_list['jpspanAllowed'] =
array ('label'         => 'Activate Jpspan'
      ,'description'   => 'Use Jpspan mode for the resource linking utility. Warning : Jpspan does not work on IIS web servers.'
      ,'default'       => TRUE
      ,'type'          => 'boolean'
      ,'acceptedValue' => array ('TRUE'  => 'Yes'
                                ,'FALSE' => 'No'
                                )
      );

$conf_def_property_list['otherCoursesAllowed'] =
array ('label'         => 'Allow other course resource linking'
      ,'description'   => 'Allow a course manager to browse and link resources in its other courses'
      ,'default'       => TRUE
      ,'type'          => 'boolean'
      ,'acceptedValue' => array ('TRUE'  => 'Yes'
                                ,'FALSE' => 'No'
                                )
      );

$conf_def_property_list['publicCoursesAllowed'] =
array ('label'         => 'Allow public course resource linking'
      ,'description'   => 'Allow a course manager to browse and link resources in any public course'
      ,'default'       => TRUE
      ,'type'          => 'boolean'
      ,'acceptedValue' => array ('TRUE'  => 'Yes'
                                ,'FALSE' => 'No'
                                )
      );

$conf_def_property_list['externalLinkAllowed'] =
array ('label'         => 'Allow external resource linking'
      ,'description'   => 'Allow a course manager to browse and link external resources reachable by an url'
      ,'default'       => TRUE
      ,'type'          => 'boolean'
      ,'acceptedValue' => array ('TRUE'  => 'Yes'
                                ,'FALSE' => 'No'
                                )
      );

$conf_def_property_list['groupAllowed'] =
array ('label'         => 'Show groups in resource browser'
      // ,'description'   => 'Allow a course manager to browse groups'
      ,'default'       => TRUE
      ,'type'          => 'boolean'
      ,'acceptedValue' => array ('TRUE'  => 'Yes'
                                ,'FALSE' => 'No'
                                )
      );

$conf_def_property_list['toolGroupAllowed'] =
array ('label'         => 'Allow group resource linking'
      ,'description'   => 'Allow a course manager to browse and link resources located in a group space and in group tools'
      ,'default'       => TRUE
      ,'type'          => 'boolean'
      ,'acceptedValue' => array ('TRUE'  => 'Yes'
                                ,'FALSE' => 'No'
                                )
      );

?>