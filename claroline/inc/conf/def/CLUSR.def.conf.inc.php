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
$conf_def['config_code'] = 'CLUSR';
$conf_def['config_file'] = 'CLUSR.conf.php';
$conf_def['config_name'] = 'Users tool';
$conf_def['old_config_file'][]='user.conf.inc.php';
$conf_def['config_class']='tool';


//SECTION
$conf_def['section']['main']['label']='Main settings';
//$conf_def['section']['main']['description']='Settings of the tool';
$conf_def['section']['main']['properties'] = 
array ( 'linkToUserInfo'
      , 'user_email_hidden_to_anonymous'
      , 'is_coursemanager_allowed_to_add_user'
      , 'nbUsersPerPage'
      );

//PROPERTIES

$conf_def_property_list['linkToUserInfo'] =
array ('label'         => 'Show user profile'
      ,'description'   => 'Allow user to see detail informations of other users'
      ,'default'       => 'TRUE'
      ,'type'          => 'boolean'
      ,'acceptedValue' => array ('TRUE'  => 'Yes'
                                ,'FALSE' => 'No'
                                )
      );

$conf_def_property_list['user_email_hidden_to_anonymous'] =
array ('label'         => 'Hidden email address to anonymous user'
      ,'description'   => 'Don\'t display email of user to anonymous (to avoid spam)'
      ,'default'       => 'FALSE'
      ,'type'          => 'boolean'
      ,'acceptedValue' => array ('TRUE'  => 'Yes'
                                ,'FALSE' => 'No'
                                )
      );

$conf_def_property_list['nbUsersPerPage'] = 
array ( 'label'   => 'Number of user per page'
      , 'default' => '25'
      , 'unit'    => 'users'
      ,  'type'    => 'integer'
      ,'acceptedValue' => array ('Min'=>'5')
      );

$conf_def_property_list['is_coursemanager_allowed_to_add_user'] =
array('label'         => 'Teacher can add user in his course'
     ,'default'       => 'TRUE'
     ,'type'          => 'boolean'
     ,'display'       => TRUE
     ,'readonly'      => FALSE
     ,'acceptedValue' => array ('TRUE'=>'Yes'
                              ,'FALSE'=>'No'
                              )
     );

$conf_def['section']['add_user']['label'] = 'Add user';
$conf_def['section']['add_user']['description'] = '';
$conf_def['section']['add_user']['properties'] = 
array ( 'allowSearchInAddUser' );

$conf_def_property_list['allowSearchInAddUser'] =
array ('label'         => 'Allow search in the add user option'
      ,'description'   => 'User search in the user tool is allowed'
      ,'display'       => TRUE
      ,'readonly'      => FALSE
      ,'default'       => 'TRUE'
      ,'type'          => 'boolean'
      ,'acceptedValue' => array ('TRUE'  => 'Allowed'
                                ,'FALSE' => 'Denied'
                                )
      );

?>
