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
 //     ,'description'   => '...'
      ,'default'       => 'TRUE'
      ,'type'          => 'boolean'
      ,'acceptedValue' => array ('TRUE'  => 'Yes'
                                ,'FALSE' => 'No'
                                )
      );

$conf_def_property_list['otherCoursesAllowed'] =
array ('label'         => 'Show the link of the other course '
    //  ,'description'   => '...'
      ,'default'       => 'TRUE'
      ,'type'          => 'boolean'
      ,'acceptedValue' => array ('TRUE'  => 'Yes'
                                ,'FALSE' => 'No'
                                )
      );
     
$conf_def_property_list['publicCoursesAllowed'] =
array ('label'         => 'Show the link of the public course '
  //    ,'description'   => '...'
      ,'default'       => 'TRUE'
      ,'type'          => 'boolean'
      ,'acceptedValue' => array ('TRUE'  => 'Yes'
                                ,'FALSE' => 'No'
                                )
      );

$conf_def_property_list['externalLinkAllowed'] =
array ('label'         => 'Show the link of the external link '
 //     ,'description'   => '...'
      ,'default'       => 'TRUE'
      ,'type'          => 'boolean'
      ,'acceptedValue' => array ('TRUE'  => 'Yes'
                                ,'FALSE' => 'No'
                                )
      );
      
$conf_def_property_list['groupAllowed'] =
array ('label'         => 'Explore the group'
 //     ,'description'   => '...'
      ,'default'       => 'TRUE'
      ,'type'          => 'boolean'
      ,'acceptedValue' => array ('TRUE'  => 'Yes'
                                ,'FALSE' => 'No'
                                )
      );
     
$conf_def_property_list['toolGroupAllowed'] =
array ('label'         => 'Explore the tool of a group'
  //    ,'description'   => '...'
      ,'default'       => 'TRUE'
      ,'type'          => 'boolean'
      ,'acceptedValue' => array ('TRUE'  => 'Yes'
                                ,'FALSE' => 'No'
                                )
      );                 

?>