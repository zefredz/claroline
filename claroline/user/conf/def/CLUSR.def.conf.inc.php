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
$conf_def['config_code'] = 'CLUSR';
$conf_def['config_file'] = 'CLUSR.conf.php';
$conf_def['config_name'] = 'Users list';
$conf_def['old_config_file'][]='user.conf.inc.php';
$conf_def['config_class']='user';



//SECTION
$conf_def['section']['main']['label']='Main settings';
//$conf_def['section']['main']['description']='Settings of the tool';
$conf_def['section']['main']['properties'] =
array ( 'linkToUserInfo'
      , 'user_email_hidden_to_anonymous'
      , 'nbUsersPerPage'
      );

//PROPERTIES

$conf_def_property_list['linkToUserInfo'] =
array ('label'         => 'Show user profile'
      ,'description'   => 'Allow user to see detail informations of other users'
      ,'default'       => TRUE
      ,'type'          => 'boolean'
      ,'acceptedValue' => array ('TRUE'  => 'Yes'
                                ,'FALSE' => 'No'
                                )
      );

$conf_def_property_list['user_email_hidden_to_anonymous'] =
array ('label'         => 'Hidden email address to anonymous user'
      ,'description'   => 'Don\'t display email of user to anonymous (to avoid spam)'
      ,'default'       => FALSE
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
array('label'         => 'Teacher can add some users in his course'
     ,'default'       => TRUE
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
array ( 'is_coursemanager_allowed_to_add_user'
      , 'is_coursemanager_allowed_to_add_single_user'
      , 'allowSearchInAddUser'
      , 'is_coursemanager_allowed_to_import_user_list'
      , 'is_coursemanager_allowed_to_import_user_class'

);

$conf_def_property_list['allowSearchInAddUser'] =
array ('label'         => 'Allow search in the add user option'
      ,'description'   => 'User search in the user tool is allowed'
      ,'display'       => TRUE
      ,'readonly'      => FALSE
      ,'default'       => TRUE
      ,'type'          => 'boolean'
      ,'acceptedValue' => array ('TRUE'  => 'Allowed'
                                ,'FALSE' => 'Denied'
                                )
      );

$conf_def_property_list['is_coursemanager_allowed_to_add_single_user'] =
array('label'         => 'Teacher can add a user in his course'
     ,'default'       => TRUE
     ,'type'          => 'boolean'
     ,'display'       => TRUE
     ,'readonly'      => FALSE
     ,'acceptedValue' => array ('TRUE'=>'Yes'
                              ,'FALSE'=>'No'
                              )
     );


$conf_def_property_list['is_coursemanager_allowed_to_import_user_list'] =
array('label'         => 'Teacher can import user list in his course'
     ,'default'       => TRUE
     ,'type'          => 'boolean'
     ,'display'       => TRUE
     ,'readonly'      => FALSE
     ,'acceptedValue' => array ('TRUE'=>'Yes'
                              ,'FALSE'=>'No'
                              )
     );


$conf_def_property_list['is_coursemanager_allowed_to_import_user_class'] =
array('label'         => 'Teacher can import an existing class course'
     ,'default'       => TRUE
     ,'type'          => 'boolean'
     ,'display'       => TRUE
     ,'readonly'      => FALSE
     ,'acceptedValue' => array ('TRUE'=>'Yes'
                              ,'FALSE'=>'No'
                              )
     );

?>