<?php // $Id$
// TOOL
$conf_def['config_code']='CLUSR';
$conf_def['config_file']='CLUSR___.conf.php';
$conf_def['config_name']='Users tool';
$conf_def['old_config_file'][]='user.conf.inc.php';

//SECTION
$conf_def['section']['main']['label']='Main settings';
$conf_def['section']['main']['description']='Settings of the tool';
$conf_def['section']['main']['properties'] = 
array ( 'linkToUserInfo'
      , 'CONF_COURSEADMIN_IS_ALLOWED_TO_ADD_USER'
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

$conf_def_property_list['nbUsersPerPage'] = 
array ( 'label'   => 'Number of user per page'
      , 'default' => '25'
      , 'unit'    => 'users'
      ,  'type'    => 'integer'
      ,'acceptedValue' => array ('Min'=>'5')
      );

$conf_def_property_list['CONF_COURSEADMIN_IS_ALLOWED_TO_ADD_USER'] =
array('label'         => 'Teacher can add user in his course'
     ,'default'       => 'TRUE'
     ,'type'          => 'boolean'
     ,'display'       => TRUE
     ,'readonly'      => FALSE
     ,'container'     => 'CONST'
     ,'acceptedValue' => array ('TRUE'=>'Yes'
                              ,'FALSE'=>'No'
                              )
     );

/*

$conf_def['section']['fakeuser']['label']='Add fake user properties';
$conf_def['section']['fakeuser']['description']='this  tool allow to  fix some option for the dev tools user account generator';
$conf_def['section']['fakeuser']['properties'] = 
array ( 'DEFAULT_SUFFIX_MAIL'
      , 'DEFAULT_NUMBER_CREATED_USERS'
      , 'DEFAULT_QTY_TEACHER'
      , 'DEFAULT_QTY_STUDENT'
      , 'ADD_FIRSTNAMES_FROM_BASE'
      , 'ADD_NAMES_FROM_BASE'
      , 'ADD_USERNAMES_FROM_BASE'
      , 'USE_FIRSTNAMES_AS_LASTNAMES'
      , 'CONFVAL_LIST_USER_ADDED'
      );

$conf_def_property_list['DEFAULT_SUFFIX_MAIL'] =
array ( 'label'         => 'Hostname for fake email generated'
      , 'description'   => 'the first part av build with name, firstname and random'
      , 'default'       => '@fake.zz'
      , 'type'          => 'regexp'
      , 'acceptedValue' => '@(([0-9]{1,3}\.){3}[0-9]{1,3}|([0-9a-z][0-9a-z-]*[0-9a-z]\.)+[a-z]{2,4})$'
      , 'container'     => 'CONST'
      );

$conf_def_property_list['DEFAULT_NUMBER_CREATED_USERS'] =
array('label'       => 'default Qty of created users'
     ,'default'     => '100'
     ,'type'        => 'integer'
     , 'unit'      => 'user(s)'
     ,'container'   => 'CONST'
     );

$conf_def_property_list['DEFAULT_QTY_STUDENT'] =
array ( 'label'     => 'default Qty of created students'
      , 'default'   => '5'
      , 'type'      => 'integer'
      , 'unit'      => 'students(s)'
      , 'container' => 'CONST'
      );

$conf_def_property_list['DEFAULT_QTY_TEACHER'] =
array ( 'label'     => 'default Qty of created course managers'
      , 'default'   => '0'
      , 'type'      => 'integer'
      , 'unit'      => 'teacher(s)'
      , 'container' => 'CONST'
      );

$conf_def_property_list['ADD_FIRSTNAMES_FROM_BASE'] =
array ( 'label'         => 'Create firstname with already existing in the database'
      , 'default'       => 'TRUE'
      , 'type'          => 'boolean'
      , 'container'     => 'CONST'
      , 'acceptedValue' => array ('TRUE'=>'Yes'
                                 ,'FALSE'=>'No'
                                 )
      );

$conf_def_property_list['ADD_NAMES_FROM_BASE'] =
array ( 'label'         => 'Create lastname with already existing in the database'
      , 'default'       => 'FALSE'
      , 'type'          => 'boolean'
      , 'container'     => 'CONST'
      , 'acceptedValue' => array ('TRUE'=>'Yes'
                                 ,'FALSE'=>'No'
                                 )
      );

$conf_def_property_list['ADD_USERNAMES_FROM_BASE'] = 
array ( 'label'         => 'Create username with already existing in the database'
      , 'default'       => 'TRUE'
      , 'type'          => 'boolean'
      , 'container'     => 'CONST'
      , 'acceptedValue' => array ('TRUE'=>'Yes'
                                 ,'FALSE'=>'No'
                                 )
      );

$conf_def_property_list['USE_FIRSTNAMES_AS_LASTNAMES'] =
array ( 'label'         => 'Create lastname with firstnames'
      , 'default'       => 'TRUE'
      , 'type'          => 'boolean'
      , 'container'     => 'CONST'
      , 'acceptedValue' => array ('TRUE'=>'Yes'
                                 ,'FALSE'=>'No'
                                 )
      );

$conf_def_property_list['CONFVAL_LIST_USER_ADDED'] = 
array ( 'label'         => 'Show list of new users'
      , 'default'       => 'TRUE'
      , 'type'          => 'boolean'
      , 'container'     => 'CONST'
      , 'acceptedValue' => array ('TRUE'=>'Yes'
                                 ,'FALSE'=>'No'
                                 )
      );

*/

?>
