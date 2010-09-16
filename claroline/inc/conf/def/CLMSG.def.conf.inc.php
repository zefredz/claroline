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
$conf_def['config_code'] = 'CLMSG';
$conf_def['config_file'] = 'CLMSG.conf.php';
$conf_def['config_name'] = 'Internal messaging system';
$conf_def['config_class']= 'kernel';


//SECTION
$conf_def['section']['main']['label']='Main settings';
//$conf_def['section']['main']['description']='';
$conf_def['section']['main']['properties'] =
array ( 
    'messagePerPage',
    'mailNotification'
);

//PROPERTIES

$conf_def_property_list['messagePerPage'] =
array ( 'label'   => 'Number of message per page'
      , 'default' => '15'
      , 'unit'    => 'messages'
      , 'type'    => 'integer'
      , 'acceptedValue' => array ('min'=>'5')
      , 'display'     => TRUE
      , 'readonly'    => FALSE
      );


$conf_def_property_list['mailNotification'] =
array ( 'label'   => 'Enable Email notification'
      , 'default' => TRUE
      , 'type'    => 'boolean'
      , 'acceptedValue' => array('TRUE'=>'Yes', 'FALSE' => 'No')
      , 'display'     => TRUE
      , 'readonly'    => FALSE
      );
?>