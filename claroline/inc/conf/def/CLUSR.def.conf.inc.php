<?php // $Id$
// TOOL
$conf_def['config_code']='CLUSR';
$conf_def['config_file']='CLUSR___.conf.php';
$conf_def['config_name']='General setting for users listing (include User info tool)';
// $conf_def['config_repository']=''; Disabled = includePath.'/conf'
$conf_def['section']['list']['label']='Listing properties';
$conf_def['section']['list']['description']='Common properties for listing of users';
$conf_def['section']['list']['properties'] = 
array ( 'linkToUserInfo'
      , 'nbUsersPerPage'
      , 'CONF_COURSEADMIN_IS_ALLOWED_TO_ADD_USER'
      );

//PROPERTIES

$conf_def_property_list['linkToUserInfo'] =
array ('label'         => 'Show the link to user info'
      ,'default'       => 'TRUE'
      ,'type'          => 'boolean'
      ,'acceptedValue' => array ('TRUE'  => 'Yes'
                                ,'FALSE' => 'No'
                                )
      );

$conf_def_property_list['nbUsersPerPage'] = 
array ( 'label'   => 'Number of user per page'
      , 'default' => '25'
      , 'unit'    => 'users per lines'
      ,  'type'    => 'integer'
      ,'acceptedValue' => array ('Min'=>'5')
      );

$conf_def_property_list['CONF_COURSEADMIN_IS_ALLOWED_TO_ADD_USER'] =
array('label'         => 'Teacher can add himself user in his course'
     ,'default'       => 'TRUE'
     ,'type'          => 'boolean'
     ,'display'       => TRUE
     ,'readonly'      => FALSE
     ,'container'     => 'CONST'
     ,'acceptedValue' => array ('TRUE'=>'Yes'
                              ,'FALSE'=>'No'
                              )
     );

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
array ( 'label'         => 'extention des emails générés'
      , 'description'   => 'la partie avant sera générée aléatoirement'
      , 'default'       => '@fake.zz'
      , 'type'          => 'regexp'
      , 'acceptedValue' => '@(([0-9]{1,3}\.){3}[0-9]{1,3}|([0-9a-z][0-9a-z-]*[0-9a-z]\.)+[a-z]{2,4})$'
      , 'container'     => 'CONST'
      );

$conf_def_property_list['DEFAULT_NUMBER_CREATED_USERS'] =
array('label'       => 'le nombre de comptes générés'
     ,'default'     => '100'
     ,'type'        => 'integer'
     , 'unit'      => 'user(s)'
     ,'container'   => 'CONST'
     );

$conf_def_property_list['DEFAULT_QTY_STUDENT'] =
array ( 'label'     => 'le nombre de comptes user générés'
      , 'default'   => '5'
      , 'type'      => 'integer'
      , 'unit'      => 'students(s)'
      , 'container' => 'CONST'
      );

$conf_def_property_list['DEFAULT_QTY_TEACHER'] =
array ( 'label'     => 'le nombre de comptes créateur de cours générés'
      , 'default'   => '0'
      , 'type'      => 'integer'
      , 'unit'      => 'teacher(s)'
      , 'container' => 'CONST'
      );

$conf_def_property_list['ADD_FIRSTNAMES_FROM_BASE'] =
array ( 'label'         => 'créer les prénoms à partir de ceux qui existent dans la base'
      , 'default'       => 'TRUE'
      , 'type'          => 'boolean'
      , 'container'     => 'CONST'
      , 'acceptedValue' => array ('TRUE'=>'oui'
                                 ,'FALSE'=>'non'
                                 )
      );

$conf_def_property_list['ADD_NAMES_FROM_BASE'] =
array ( 'label'         => 'créer les noms à partir de ceux qui existent dans la base'
      , 'default'       => 'FALSE'
      , 'type'          => 'boolean'
      , 'container'     => 'CONST'
      , 'acceptedValue' => array ('TRUE'=>'oui'
                                 ,'FALSE'=>'non'
                                 )
      );

$conf_def_property_list['ADD_USERNAMES_FROM_BASE'] = 
array ( 'label'         => 'créer les noms de compte à partir de ceux '
                          .'qui existent dans la base'
      , 'default'       => 'TRUE'
      , 'type'          => 'boolean'
      , 'container'     => 'CONST'
      , 'acceptedValue' => array ('TRUE'=>'oui'
                                 ,'FALSE'=>'non'
                                 )
      );

$conf_def_property_list['USE_FIRSTNAMES_AS_LASTNAMES'] =
array ( 'label'         => 'Créer des noms à partir les prénoms'
      , 'default'       => 'TRUE'
      , 'type'          => 'boolean'
      , 'container'     => 'CONST'
      , 'acceptedValue' => array ('TRUE'=>'oui'
                                 ,'FALSE'=>'non'
                                 )
      );

$conf_def_property_list['CONFVAL_LIST_USER_ADDED'] = 
array ( 'label'         => 'Show list of new users'
      , 'default'       => 'TRUE'
      , 'type'          => 'boolean'
      , 'container'     => 'CONST'
      , 'acceptedValue' => array ('TRUE'=>'yes'
                                 ,'FALSE'=>'no'
                                 )
      );
?>