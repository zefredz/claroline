<?php // $Id$
// TOOL
$conf_def['config_code']='CLUSR';
$conf_def['config_file']='user.conf.inc.php';
// $conf_def['config_repository']=''; dislabed = includePath.'/conf'
$conf_def['section']['list']['label']='Listing properties';
$conf_def['section']['list']['description']='common properties for listing of users';
$conf_def['section']['list']['properties'] = 
array ( 'linkToUserInfo'
      , 'nbUsersPerPage'
      , 'CONF_COURSEADMIN_IS_ALLOWED_TO_ADD_USER'
      );

//PROPERTIES

$conf_def_property_list['linkToUserInfo'] =
array ('label'         => 'Afficher le lien vers les infos supplémentaires de l\'utilisateur'
      ,'default'       => 'TRUE'
      ,'type'          => 'boolean'
      ,'acceptedValue' => array ('TRUE'  => 'enabled'
                                ,'FALSE' => 'dislabed'
                                )
      );

$conf_def_property_list['nbUsersPerPage'] = 
array ( 'label'   => 'Nombre d\'utilisateurs par page',
        'default' => '25',
        'type'    => 'integer');

$conf_def_property_list['CONF_COURSEADMIN_IS_ALLOWED_TO_ADD_USER'] =
array('label'         => 'Le professeur peut-il ajouter des utilisateurs à son cours'
     ,'default'       => 'TRUE'
     ,'type'          => 'boolean'
     ,'display'       => TRUE
     ,'readonly'      => FALSE
     ,'container'     => 'CONST'
     ,'acceptedValue' => array ('TRUE'=>'oui'
                              ,'FALSE'=>'non'
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
     ,'container'   => 'CONST'
     );

$conf_def_property_list['DEFAULT_QTY_STUDENT'] =
array ( 'label'     => 'le nombre de comptes user générés'
      , 'default'   => '5'
      , 'type'      => 'integer'
      , 'container' => 'CONST'
      );

$conf_def_property_list['DEFAULT_QTY_TEACHER'] =
array ( 'label'     => 'le nombre de comptes créateur de cours générés'
      , 'default'   => '0'
      , 'type'      => 'integer'
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