<?php # $Id$
/**
 * This file describe the parameter for Claroline main config file
 *
 * @author Christophe Gesché <moosh@claroline.net>
 * @version CLAROLINE 1.6
 * @copyright &copy; 2001-2005 Universite catholique de Louvain (UCL)
 * @license This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
 * as published by the FREE SOFTWARE FOUNDATION. The GPL is available
 * through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
 * @see http://www.claroline.net/wiki/config_def/
 * @package KERNEL
 */

// CONFIG HEADER

$conf_def['config_code']='CLMAIN';
$conf_def['config_file']='claro_main.conf.php';
$conf_def['config_name']='Main settings';

// SECTION

$conf_def['section']['PLATFORM_SETTING']['label']='Platform';
$conf_def['section']['PLATFORM_SETTING']['description']='Global settings';
$conf_def['section']['PLATFORM_SETTING']['properties'] =
array ( 'siteName'
      , 'platformLanguage'
      , 'claro_stylesheet'
      );

$conf_def['section']['ADMINISTRATOR_SETTING']['label']='Administrator';
$conf_def['section']['ADMINISTRATOR_SETTING']['description']='Information about the technical administrator';
$conf_def['section']['ADMINISTRATOR_SETTING']['properties'] =
array ( 'administrator_name'
      , 'administrator_email'
      , 'administrator_phone'
      );

$conf_def['section']['ADMINISTRATIVE_SETTING']['label']='Institution';
$conf_def['section']['ADMINISTRATIVE_SETTING']['description']='Information about your institution (optional)';
$conf_def['section']['ADMINISTRATIVE_SETTING']['properties'] =
array ( 'institution_name'
      , 'institution_url'
      );

$conf_def['section']['DISP_FILE_SYSTEM_SETTING']['label']='File system settings';
$conf_def['section']['DISP_FILE_SYSTEM_SETTING']['properties'] =
array ('rootWeb'
      , 'urlAppend'
      , 'rootSys'
      , 'garbageRepositorySys'
      , 'clarolineRepositoryAppend'
      , 'coursesRepositoryAppend'
      , 'rootAdminAppend'
      , 'phpMyAdminAppend'
      , 'phpSysInfoAppend'
      , 'userImageRepositoryAppend'
      , 'clarolineRepositorySys'
      , 'clarolineRepositoryWeb'
      , 'userImageRepositorySys'
      , 'userImageRepositoryWeb'
      , 'coursesRepositorySys'
      , 'coursesRepositoryWeb'
      , 'rootAdminSys'
      , 'rootAdminWeb'
      , 'phpMyAdminWeb'
      , 'phpMyAdminSys'
      , 'phpSysInfoWeb'
      , 'phpSysInfoSys'
      , 'PEAR_LIB_PATH'
      );

$conf_def['section']['DB_CONNECT_SETTING']['label']= 'MySQL database settings';
$conf_def['section']['DB_CONNECT_SETTING']['properties'] =
array ( 'dbHost'
      , 'dbLogin'
      , 'dbPass'
      );
$conf_def['section']['DB_NAMES_SETTING']['label']= 'MySQL Names';
$conf_def['section']['DB_NAMES_SETTING']['properties'] =
array ( 'mainDbName'
      , 'statsDbName'
      , 'mainTblPrefix'
      , 'statsTblPrefix'
      );
$conf_def['section']['DB_OTHER_SETTING']['label']= 'Db extended Settings';
$conf_def['section']['DB_OTHER_SETTING']['properties'] =
array ( 'dbNamePrefix'
      , 'is_trackingEnabled'
      , 'singleDbEnabled'
      , 'dbGlu'
      , 'courseTablePrefix'
      );

$conf_def['section']['advanced']['label']='Advanced settings';
$conf_def['section']['advanced']['properties'] =
array ( 'claro_texRendererUrl'
      , 'mysqlRepositorySys'
      , 'CLAROLANG'
      , 'userPasswordCrypted'
      , 'allowSelfReg'
      , 'userImageRepositorySys'
      , 'userImageRepositoryWeb'
      , 'clarolineVersion'
      , 'versionDb'
      , 'platform_id'
      , 'CLARO_DEBUG_MODE'
      );


$conf_def_property_list['dbHost'] =
array ('label'       => 'Hostname'
      ,'default'     => 'localhost'
      ,'type'        => 'string'
      ,'display'     => TRUE
      ,'readonly'    => FALSE
      ,'technicalInfo' => 'The hostname of mysql server'
      );


$conf_def_property_list['dbLogin'] =
array ('label'       => 'Username'
      ,'default'     => 'claroline'
      ,'type'        => 'string'
      ,'display'     => TRUE
      ,'readonly'    => FALSE
      ,'technicalInfo' => 'The login given by your administrator to connect on the mysql server'
      ,'description' => 'The login given by your administrator to connect on the mysql server'
      );


$conf_def_property_list['dbPass'] =
array ('label'       => 'Password'
      ,'default'     => ''
      ,'type'        => 'string'
      ,'display'     => TRUE
      ,'readonly'    => FALSE
      ,'technicalInfo' => 'The clear password'
      );

$conf_def_property_list['dbNamePrefix'] =
array ('label'       => 'Prefix for name of courses Database '
      ,'default'     => 'c_'
      ,'type'        => 'string'
      ,'display'     => TRUE
      ,'readonly'    => FALSE
      ,'description' => 'This prefix let database group by sense if sort by name. '
      ,'technicalInfo' => 'Prefix all created base (for courses) with this string'
      );

$conf_def_property_list['mainDbName'] =
array ('label'       => 'Main database name'
      ,'default'     => ''
      ,'type'        => 'string'
      ,'display'     => TRUE
      ,'readonly'    => FALSE
      ,'description' => 'You probably don\'t must edit this value'
      );

$conf_def_property_list['mainTblPrefix'] =
array ('label'       => 'Prefix for central tables'
      //,'description' => ''
      ,'default'     => 'cl_'
      ,'type'        => 'string'
      ,'display'     => TRUE
      ,'readonly'    => TRUE
      );


$conf_def_property_list['statsDbName'] =
array ( 'label'       => 'Database name where stored the tracking and stat tables'
      , 'description' => 'can be the same name as main database'
      , 'type'        => 'string'
      , 'display'     => TRUE
      , 'readonly'    => FALSE
      );

$conf_def_property_list['statsTblPrefix'] =
array ( 'label'       => 'Prefix for name of tracking and stat tables'
      , 'description' => 'can be the same prefix as main database'
      , 'default'     => 'cl_'
      , 'type'        => 'string'
      , 'display'     => TRUE
      , 'readonly'    => TRUE
      );

$conf_def_property_list['is_trackingEnabled'] =
array ('label'       => 'Tracking'
      ,'description' => 'Enable the log of activities (user and course access, course tool usage, ...) on the whole platform'
      ,'default'     => 'TRUE'
      ,'type'        => 'boolean'
      ,'display'     => TRUE
      ,'readonly'    => FALSE
      ,'acceptedValue' => array ('TRUE'=>'Enabled', 'FALSE' => 'Disabled')
      );

$conf_def_property_list['singleDbEnabled'] =
array ('label'       => 'Database mode'
      ,'default'     => 'TRUE'
      ,'type'        => 'boolean'
      ,'display'     => TRUE
      ,'readonly'    => TRUE
      ,'acceptedValue' => array ('TRUE'=>'single', 'FALSE' => 'Multiple')
      );

$conf_def_property_list['dbGlu'] =
array ('label'       => 'db glu'
      ,'description' => 'To find a table name, the choose database name is prepend to the table name.'."\n"
                       .'db glu is use between these two name.'."\n"."\n"
                       .'In multi db mode, IT MUST be a dot.'."\n"
                       .'In single db mode, IT CAN\'T be a dot.'."\n"
      ,'default'     => ''
      ,'type'        => 'string'
      ,'display'     => FALSE
      ,'readonly'    => TRUE
      );

$conf_def_property_list['courseTablePrefix'] =
array ('label'       => 'Course name table prefix'
      ,'description' => 'This  prefix is add to table names. It\'s usefull in single database to group courses tables.'
      ,'default'     => ''
      ,'type'        => 'string'
      ,'display'     => TRUE
      ,'readonly'    => FALSE
      );

$conf_def_property_list['mysqlRepositorySys'] =
array ('label'       => 'Mysql Base Path'
      ,'description' => 'This is the physical path to databases storage. This path is  optional, use by the quota and size'
      ,'default'     => ''
      ,'type'        => 'syspath'
      ,'display'     => FALSE
      ,'readonly'    => FALSE
      );

//paths

$conf_def_property_list['rootWeb'] =
array ('label'       => 'web base'
      ,'description' => 'Absolute url of the entrance of claroline'
      ,'default'     => 'http://www.yourdomaine.tld/mycampus/'
      ,'type'        => 'urlpath'
      ,'display'     => TRUE
      ,'readonly'    => FALSE
      );

$conf_def_property_list['urlAppend'] =
array ('label'       => 'URL append'
      ,'description' => 'relative path from the root of the website until the value of web base'
      ,'default'     => 'mycampus'
      ,'type'        => 'string'
      ,'display'     => TRUE
      ,'readonly'    => FALSE
      ,'technicalInfo' => 'no trailing / in this value'
      );

$conf_def_property_list['rootSys'] =
array ('label'       => 'System Path to web base value'
      ,'default'     => ''
      ,'type'        => 'syspath'
      ,'display'     => TRUE
      ,'readonly'    => FALSE
      ,'technicalInfo' => 'The hostname of mysql server'
      );

$conf_def_property_list['garbageRepositorySys'] =
array ('label'       => 'Garbage'
      ,'description' => 'absolute sys path to the place where are move data of a deleted course'
      ,'default'     => ''
      ,'type'        => 'syspath'
      ,'display'     => FALSE
      ,'readonly'    => FALSE
      );

// Platform

$conf_def_property_list['siteName'] =
array ('label'       => 'Campus name'
      ,'description' => 'Name of your campus'
      ,'default'     => 'MyCampus'
      ,'type'        => 'string'
      ,'display'     => TRUE
      ,'readonly'    => FALSE
      );

$conf_def_property_list['platformLanguage'] =
array ('label'         => 'Default Language'
      ,'description'   => 'Select the default language of the platform'
      ,'default'       => 'english'
      ,'type'          => 'lang'
      ,'display'       => TRUE
      ,'readonly'      => FALSE
      );

$conf_def_property_list['claro_stylesheet'] =
array ('label'       => 'Layout'
      ,'description' => 'Set the stylesheet layout'
      ,'default'     => 'default.css'
      ,'type'        => 'css'
      ,'display'     => TRUE
      ,'readonly'    => FALSE
      );

$conf_def_property_list['CLAROLANG'] =
array('label'         => 'Language Mode'
     ,'description'   => 'Translation: use a single language file, Production: each script use its own language file'
     ,'default'       => 'PRODUCTION'
     ,'type'          => 'enum'
     ,'display'       => TRUE
     ,'readonly'      => FALSE
     ,'container'     => 'CONST'
     ,'acceptedValue' => array ('TRANSLATION'=>'Translation'
                              ,'PRODUCTION'=>'Production'
                              )
     );

// Administrator

$conf_def_property_list['administrator_name'] =
array ('label'       => 'Name'
      ,'description' => 'Complete name'
      ,'default'     => 'John Doe'
      ,'type'        => 'string'
      ,'display'     => TRUE
      ,'readonly'    => FALSE
      );

$conf_def_property_list['administrator_email'] =
array ('label'       => 'Email'
      ,'description' => 'This email is the main contact address on the platform'
      ,'type'        => 'string'
      ,'display'     => TRUE
      ,'readonly'    => FALSE
      );
$conf_def_property_list['administrator_phone'] =
array ('label'       => 'Phone'
      ,'type'        => 'string'
      ,'display'     => TRUE
      ,'readonly'    => FALSE
      );

// Institution
$conf_def_property_list['institution_name'] =
array ('label'       => 'Name'
      ,'default'     => 'My institute'
      ,'description' => 'Name displayed in the top banner'
      ,'type'        => 'string'
      ,'display'     => TRUE
      ,'readonly'    => FALSE
      );
$conf_def_property_list['institution_url'] =
array ('label'       => 'URL'
      ,'default'     => 'http://www.google.com/'
      ,'type'        => 'string'
      ,'display'     => TRUE
      ,'readonly'    => FALSE
      );

// Latex

$conf_def_property_list['claro_texRendererUrl'] =
array ('label'       => 'The complete url of your TEX renderer'
       ,'technicalInfo' =>
'Put above the complete url of your TEX renderer. This url doesn\'t have to be
 specially on the same server than Claroline.

 Claroline uses the MIMETEX renderer created by John Forkosh and available
 under the GNU licences at http://www.forkosh.com.

 MIMETEX parses TEX/LaTEX mathematical expressions and emits gif images from
 them. You\'ll find precompilated versions of MIMETEX for various platform in
 the "claroline/inc/lib/" directory. Move the executable file that
 corresponding to your platform into its "cgi-bin/" directory, where cgi
 programs are expected (this directory are typically of the form
 "somewhere/www/cgi-bin/"), and change the execution permissions if necessary.

 If you\'re not able or allowed to set MIMETEX on a server, leave the setting
 below to "false". Claroline will then try to use another method for rendering
 TEX/LaTEX mathematical expression, relying on a plug-in client side this
 time. For this, user has to install the TECHEXPLORER plug-in, freely
 available for both Windows, Macintosh and Linux at
 http://www.integretechpub.com/.'
      ,'default'     => ''
      ,'type'        => 'string'
      ,'display'     => TRUE
      ,'readonly'    => FALSE
      );

$conf_def_property_list['userPasswordCrypted'] =
array ('label'       => 'By default Crypt passwords'
      ,'technical'   => 'By default use claroCrypt as authType'
      ,'default'     => 'FALSE'
      ,'type'        => 'boolean'
      ,'display'     => TRUE
      ,'readonly'    => FALSE
      );

$conf_def_property_list['allowSelfReg'] =
array ('label'       => 'User can subcribe it self to the platform'
      ,'default'     => 'TRUE'
      ,'type'        => 'boolean'
      ,'display'     => TRUE
      ,'readonly'    => FALSE
      );

////for new login module
////uncomment these to activate ldap
////$extAuthSource['ldap']['login'] = "./claroline/auth/ldap/login.php";
////$extAuthSource['ldap']['newUser'] = "./claroline/auth/ldap/newUser.php";
//
////Probably Nothing to change after this
//
//// these values are keet  to  have no problem with script not upgraded to  the  new init system
//$serverAddress        =   $rootWeb ;
//$webDir               =   $rootSys;
//$language             =   $platformLanguage ;
//
//// MYSQL
//$mysqlServer      =   $dbHost ;
//$mysqlUser            =   $dbLogin;
//$mysqlPassword        =   $dbPass;
//$mysqlPrefix      =   $dbNamePrefix;
//$mysqlMainDb      =   $mainDbName;
//
$conf_def_property_list['clarolineRepositoryAppend'] =
array ('type'        => 'relpath'
      ,'default'     => 'claroline/'
      ,'display'     => FALSE
      ,'readonly'    => FALSE
      );
$conf_def_property_list['coursesRepositoryAppend'] =
array ('type'        => 'relpath'
      ,'default'     => 'courses/'
      ,'display'     => FALSE
      ,'readonly'    => FALSE
      );

$conf_def_property_list['rootAdminAppend'] =
array ('type'        => 'relpath'
      ,'default'     => 'admin/'
      ,'display'     => FALSE
      ,'readonly'    => FALSE
      );

$conf_def_property_list['phpMyAdminAppend'] =
array ('type'        => 'relpath'
      ,'default'     => 'mysql/'
      ,'display'     => FALSE
      ,'readonly'    => FALSE
      );
$conf_def_property_list['phpSysInfoAppend'] =
array ('type'        => 'relpath'
      ,'default'     => 'sysinfo/'
      ,'display'     => FALSE
      ,'readonly'    => FALSE
      );
$conf_def_property_list['userImageRepositoryAppend'] =
array ('type'        => 'relpath'
      ,'default'     => 'img/users/'
      ,'display'     => FALSE
      ,'readonly'    => FALSE
      );

$conf_def_property_list['clarolineRepositorySys'] =
array ('type'        => 'syspath'
      ,'default'     => str_replace('\\','/',$rootSys.$clarolineRepositoryAppend)
      ,'display'     => FALSE
      ,'readonly'    => FALSE
      );
$conf_def_property_list['clarolineRepositoryWeb'] =
array ('type'        => 'syspath'
      ,'default'     => str_replace('\\','/',$rootWeb.$clarolineRepositoryAppend)
      ,'display'     => FALSE
      ,'readonly'    => FALSE
      );
$conf_def_property_list['userImageRepositorySys'] =
array ('type'        => 'syspath'
      ,'default'     => str_replace('\\','/',$rootSys.$userImageRepositoryAppend)
      ,'display'     => FALSE
      ,'readonly'    => FALSE
      );
$conf_def_property_list['userImageRepositoryWeb'] =
array ('type'        => 'syspath'
      ,'default'     => str_replace('\\','/',$rootWeb.$userImageRepositoryAppend)
      ,'display'     => FALSE
      ,'readonly'    => FALSE
      );
$conf_def_property_list['coursesRepositorySys'] =
array ('type'        => 'syspath'
      ,'default'     => str_replace('\\','/',$rootSys.$coursesRepositoryAppend)
      ,'display'     => FALSE
      ,'readonly'    => FALSE
      );
$conf_def_property_list['coursesRepositoryWeb'] =
array ('type'        => 'syspath'
      ,'default'     => str_replace('\\','/',$rootWeb.$coursesRepositoryAppend)
      ,'display'     => FALSE
      ,'readonly'    => FALSE
      );
$conf_def_property_list['rootAdminSys'] =
array ('type'        => 'syspath'
      ,'default'     => str_replace('\\','/',$clarolineRepositorySys.$rootAdminAppend)
      ,'display'     => FALSE
      ,'readonly'    => FALSE
      );
$conf_def_property_list['rootAdminWeb'] =
array ('type'        => 'syspath'
      ,'default'     => str_replace('\\','/',$clarolineRepositoryWeb.$rootAdminAppend)
      ,'display'     => FALSE
      ,'readonly'    => FALSE
      );
$conf_def_property_list['phpMyAdminWeb'] =
array ('type'        => 'syspath'
      ,'default'     => str_replace('\\','/',$rootAdminWeb.$phpMyAdminAppend)
      ,'display'     => FALSE
      ,'readonly'    => FALSE
      );
$conf_def_property_list['phpMyAdminSys'] =
array ('type'        => 'syspath'
      ,'default'     => $rootAdminSys.$phpMyAdminAppend
      ,'display'     => FALSE
      ,'readonly'    => FALSE
      );
$conf_def_property_list['phpSysInfoWeb'] =
array ('type'        => 'syspath'
      ,'default'     => $rootAdminWeb.$phpSysInfoAppend
      ,'display'     => FALSE
      ,'readonly'    => FALSE
      );
$conf_def_property_list['phpSysInfoSys'] =
array ('type'        => 'syspath'
      ,'default'     => str_replace('\\','/',$rootAdminSys.$phpSysInfoAppend)
      ,'display'     => FALSE
      ,'readonly'    => FALSE
      );
$conf_def_property_list['PEAR_LIB_PATH'] =
array ('type'        => 'syspath'
      ,'label'       => 'Pear lib'
      ,'default'     => str_replace('\\','/',$includePath.'/lib/pear')
      ,'container'   => 'CONST'
      ,'display'     => FALSE
      ,'readonly'    => FALSE
      );



//
$conf_def_property_list['CLARO_DEBUG_MODE'] =
array ('type'        => 'boolean'
      ,'default'     => 'FALSE'
      ,'container'   => 'CONST'
      ,'display'     => FALSE
      ,'readonly'    => FALSE
      );
$conf_def_property_list['clarolineVersion'] =
array ('type'        => 'string'
      ,'default'     => '1.6.beta2'
      ,'display'     => FALSE
      ,'readonly'    => TRUE
      );
$conf_def_property_list['versionDb'] =
array ('type'        => 'string'
      ,'default'     => '1.6.beta2'
      ,'display'     => FALSE
      ,'readonly'    => TRUE
      );
$conf_def_property_list['platform_id'] =
array ('type'        => 'string'
      ,'technicalDesc' => 'id for this campus. Would  be unique'
      ,'default'     => md5(realpath(__FILE__))
      ,'display'     => FALSE
      ,'readonly'    => TRUE
      );

      //missing
      /*

      */
?>
