<?php // $Id$
/**
 * CLAROLINE
 * This file describe the parameter for Claroline main config file
 *
 * @version 1.8 $Revision$
 *
 * @copyright 2001-2006 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @see http://www.claroline.net/wiki/config_def/
 *
 * @author Claro Team <cvs@claroline.net>
 *
 * @package Kernel
 */

// CONFIG HEADER

$conf_def['config_code']='CLMAIN';
$conf_def['config_file']='claro_main.conf.php';
$conf_def['config_name']=' Main settings';
$conf_def['config_class']='platform';

// SECTION

$conf_def['section']['PLATFORM_SETTING']['label']='Platform';
$conf_def['section']['PLATFORM_SETTING']['description']='Global settings';
$conf_def['section']['PLATFORM_SETTING']['properties'] =
array ( 'siteName'
      , 'claro_stylesheet'
      );

$conf_def['section']['LANGUAGE']['label']='Language';
$conf_def['section']['LANGUAGE']['description']='';
$conf_def['section']['LANGUAGE']['properties'] =
array ( 'platformLanguage'
      , 'language_to_display'
      , 'CLAROLANG'
      );

$conf_def['section']['ADMINISTRATOR_SETTING']['label']='Technical contact';
$conf_def['section']['ADMINISTRATOR_SETTING']['description']='These informations are displayed in each platform screen footer';
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
array ( 'rootWeb'
      , 'rootSys'
      , 'urlAppend'
      , 'garbageRepositorySys'
      , 'clarolineRepositoryAppend'
      , 'coursesRepositoryAppend'
      , 'rootAdminAppend'
      , 'imgRepositoryAppend'
      , 'userImageRepositoryAppend'
     );

$conf_def['section']['DB_CONNECT_SETTING']['label']= 'MySQL database settings';
$conf_def['section']['DB_CONNECT_SETTING']['properties'] =
array ( 'dbHost'
      , 'dbLogin'
      , 'dbPass'
      , 'mainDbName'
      , 'statsDbName'
      , 'singleDbEnabled'
      , 'mainTblPrefix'
      , 'statsTblPrefix'
      , 'dbNamePrefix'
      , 'dbGlu'
      , 'courseTablePrefix'
      );

$conf_def['section']['advanced']['label']='Advanced settings';
$conf_def['section']['advanced']['properties'] =
array ( 'mysqlRepositorySys'
      , 'tmpPathSys'
      , 'userPasswordCrypted'
      , 'allowSelfReg'
      , 'allowToSelfEnroll'
      , 'is_trackingEnabled'
      , 'claro_editor'
      , 'claro_texRendererUrl'
      , 'platform_id'
      , 'CLARO_DEBUG_MODE'
      , 'DEVEL_MODE'
      );


$conf_def_property_list['dbHost'] =
array ('label'       => 'Host name'
      ,'default'     => 'localhost'
      ,'type'        => 'string'
      ,'display'     => TRUE
      ,'readonly'    => FALSE
      ,'technicalInfo' => 'The hostname of mysql server'
      );


$conf_def_property_list['dbLogin'] =
array ('label'       => 'User account'
      ,'default'     => 'root'
      ,'type'        => 'string'
      ,'display'     => TRUE
      ,'readonly'    => FALSE
      ,'technicalInfo' => 'The login given by your administrator to connect on the mysql server'
      ,'description' => ''
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
array ('label'       => 'Prefix for course table  / db names'
      ,'default'     => 'c_'
      ,'type'        => 'string'
      ,'display'     => TRUE
      ,'readonly'    => FALSE
      ,'description' => 'This prefix could allow to order more easily the tables / DB in the user interface of your server technical back office  '
      ,'technicalInfo' => 'Prefix all created base (for courses) with this string'
      );

$conf_def_property_list['mainDbName'] =
array ('label'       => 'Main database name'
      ,'default'     => 'claroline'
      ,'type'        => 'string'
      ,'display'     => TRUE
      ,'readonly'    => FALSE
      ,'description' => 'Change this setting only if it\'s absolutely required.'
      );

$conf_def_property_list['mainTblPrefix'] =
array ('label'       => 'Prefix for main table names'
      //,'description' => ''
      ,'default'     => ''
      ,'type'        => 'string'
      ,'display'     => TRUE
      ,'readonly'    => TRUE
      );


$conf_def_property_list['statsDbName'] =
array ( 'label'       => 'Tracking database name'
      , 'description' => 'This is where tracking and statistics data are stored. This database can be the same as the main database.'
      ,'default'     => 'claroline'
      , 'type'        => 'string'
      , 'display'     => TRUE
      , 'readonly'    => FALSE
      );

$conf_def_property_list['statsTblPrefix'] =
array ( 'label'       => 'Prefix for tracking table names'
      , 'description' => ''
      , 'default'     => ''
      , 'type'        => 'string'
      , 'display'     => TRUE
      , 'readonly'    => TRUE
      );

$conf_def_property_list['platform_id'] =
array ('label'       => 'unique id of the platform'
      ,'type'        => 'string'
      ,'technicalDesc' => 'id for this campus. Would  be unique'
      ,'default'     => md5(realpath(__FILE__))
      ,'display'     => FALSE
      ,'readonly'    => TRUE
      );

$conf_def_property_list['is_trackingEnabled'] =
array ('label'       => 'Tracking'
      ,'description' => 'Log of user activities  on the whole platform (course access, tool use, ...).'
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
      ,'acceptedValue' => array ('TRUE'=>'Single', 'FALSE' => 'Multiple')
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
      ,'description' => 'This  prefix is added to each course table name. It\'s usefull in single database mode as it groups all course tables together.'
      ,'default'     => ''
      ,'type'        => 'string'
      ,'display'     => FALSE
      ,'readonly'    => FALSE
      );

$conf_def_property_list['mysqlRepositorySys'] =
array ('label'       => 'Mysql Base Path'
      ,'description' => 'This is the physical path to databases storage. This path is  optional, use by the quota and size.'
      ,'default'     => ''
      ,'type'        => 'syspath'
      ,'display'     => FALSE
      ,'readonly'    => FALSE
      );

//paths

$conf_def_property_list['rootWeb'] =
array ('label'       => 'Platform web URL'
      ,'description' => 'Exemple : http://www.yourdomaine.tld/mycampus/'
      ,'default'     => 'http://www.yourdomaine.tld/mycampus/'
      ,'type'        => 'urlpath'
      ,'display'     => TRUE
      ,'readonly'    => FALSE
      );

$conf_def_property_list['urlAppend'] =
array ('label'       => 'URL trail'
      ,'description' => 'Common part of both parameters above.'
      ,'default'     => 'mycampus'
      ,'type'        => 'string'
      ,'display'     => TRUE
      ,'readonly'    => FALSE
      ,'technicalInfo' => 'no trailing / in this value'
      );

$conf_def_property_list['rootSys'] =
array ('label'       => 'Platform local path '
      ,'description' => 'Relative to the complete platform url'
      ,'default'     => ''
      ,'type'        => 'syspath'
      ,'display'     => TRUE
      ,'readonly'    => FALSE
      ,'technicalInfo' => 'The hostname of mysql server'
      );


$conf_def_property_list['tmpPathSys'] =
array ('label'         => 'Repository for temporary files and dirs'
      , 'description'  => 'Note : this repository should be protected with a .htaccess or
       be placed outside the web. Because there contain data of private courses. Claroline Would be able to read and write in this dir'
      ,'default'       => get_conf('rootWeb') . 'claroline/tmp /'
      ,'display'     => FALSE
      ,'type'          => 'syspath'
      );

$conf_def_property_list['garbageRepositorySys'] =
array ('label'       => 'Garbage'
      ,'description' => 'Absolute sys path to the place where are move data of a deleted course.'
      ,'default'     => ''
      ,'type'        => 'syspath'
      ,'display'     => FALSE
      ,'readonly'    => FALSE
      );

// Platform

$conf_def_property_list['siteName'] =
array ('label'       => 'Campus name'
      ,'description' => ''
      ,'default'     => 'Claroline'
      ,'type'        => 'string'
      ,'display'     => TRUE
      ,'readonly'    => FALSE
      );

$conf_def_property_list['platformLanguage'] =
array ('label'         => 'Platform language'
      ,'description'   => 'Set the default language of the platform.'."\n".'It doesn\'t prevent course managers to set an other language for each course they create.'
      ,'default'       => 'english'
      ,'type'          => 'enum'
      , 'acceptedValueType' => 'lang'
      ,'display'       => TRUE
      ,'readonly'      => FALSE
      );

$conf_def_property_list['language_to_display'] =
array ('label'         => 'Language to display'
      ,'description'   => ''
      ,'default'       => 'english'
      ,'type'          => 'multi'
      ,'display'       => true
      ,'acceptedValueType' => 'lang'
      ,'readonly'      => FALSE
      );

$conf_def_property_list['claro_stylesheet'] =
array ('label'       => 'Layout'
      ,'description' => 'Set the Cascading Style Sheet (CSS) layout.'
      ,'default'     => 'default.css'
      ,'type'        => 'enum'
      ,'acceptedValueType' => 'css'
      ,'display'     => TRUE
      ,'readonly'    => FALSE
      );

$conf_def_property_list['claro_editor'] =
array ('label'       => 'Editor'
      ,'description' => 'Set the editor that will replace standard html textarea.'
      ,'default'     => 'tiny_mce'
      ,'type'        => 'enum'
      ,'acceptedValueType' => 'editor'
      ,'display'     => TRUE
      ,'readonly'    => FALSE
      );

$conf_def_property_list['CLAROLANG'] =
array('label'         => 'Language mode'
     ,'description'   => 'Translation: use a single language file'."\n".'Production: each script use its own language file.'
     ,'default'       => 'TRANSLATION'
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
      ,'description' => ''
      ,'default'     => ''
      ,'type'        => 'string'
      ,'display'     => TRUE
      ,'readonly'    => FALSE
      );

$conf_def_property_list['administrator_email'] =
array ('label'       => 'E-mail'
      ,'description' => ''
      ,'type'        => 'string'
      ,'display'     => TRUE
      ,'readonly'    => FALSE
      );
$conf_def_property_list['administrator_phone'] =
array ('label'       => 'Phone'
      ,'default'     => ''
      ,'type'        => 'string'
      ,'display'     => TRUE
      ,'readonly'    => FALSE
      );

// Institution
$conf_def_property_list['institution_name'] =
array ('label'       => 'Organisation Name'
      ,'default'     => ''
      ,'description' => 'Name displayed in the top banner.'
      ,'type'        => 'string'
      ,'display'     => TRUE
      ,'readonly'    => FALSE
      );
$conf_def_property_list['institution_url'] =
array ('label'       => 'Organisation - URL'
      ,'default'     => ''
      ,'type'        => 'string'
      ,'display'     => TRUE
      ,'readonly'    => FALSE
      );

// Latex

$conf_def_property_list['claro_texRendererUrl'] =
array ('label'       => 'Mathematical renderer URL'
      ,'description' => 'This renderer is used for TEX/LaTEX expressions. It is available into the \'claroline/inc/lib/\' directory and has to be copied on a server location where CGI programs are expected.'
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
array ('label'         => 'Crypt passwords'
      ,'technical'     => 'By default use claroCrypt as authType'
      ,'default'       => 'FALSE'
      ,'type'          => 'boolean'
      ,'display'       => false
      ,'readonly'      => True
      ,'acceptedValue' => array('TRUE' => 'Yes', 'FALSE' => 'No')
      );

$conf_def_property_list['allowSelfReg'] =
array ('label'           => 'User account creation allowed'
       ,'description'    => 'Can users create new accounts themselves ?'
      ,'default'         => 'TRUE'
      ,'type'            => 'boolean'
      ,'display'         => TRUE
      ,'readonly'        => FALSE
      ,'acceptedValue' => array('TRUE' => 'Yes', 'FALSE' => 'No')
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
array ('label'       => 'relative path from root campus to claroline code'
      ,'type'        => 'relpath'
      ,'default'     => 'claroline/'
      ,'display'     => false
      );
$conf_def_property_list['coursesRepositoryAppend'] =
array ( 'label'      => 'relative path from root campus to courses'
      , 'type'       => 'relpath'
      , 'default'    => 'courses/'
      ,'display'     => false
      );

$conf_def_property_list['rootAdminAppend'] =
array ('label'        => 'relative path from claroline kernel to root of admin section'
      ,'type'        => 'relpath'
      ,'default'     => 'admin/'
      ,'display'     => false
      );
$conf_def_property_list['imgRepositoryAppend'] =
array ('label'        => 'relative path from claroline web to iconset'
      ,'type'        => 'relpath'
      ,'default'     => 'img/'
      ,'display'     => FALSE
      ,'readonly'    => TRUE
      );

$conf_def_property_list['userImageRepositoryAppend'] =
array ('label'        => 'relative path from root web to user pic repository'
      ,'type'        => 'relpath'
      ,'display'     => FALSE
      ,'default'     => 'claroline/img/users/'
      );

$conf_def_property_list['CLARO_DEBUG_MODE'] =
array ('label'       => 'Debug mode'
      ,'description' => 'More verbose when error occurs.'
      ,'type'        => 'boolean'
      ,'default'     => 'TRUE'
      ,'container'   => 'CONST'
      ,'display'     => TRUE
      ,'readonly'    => FALSE
      , 'acceptedValue' => array('TRUE' => 'On', 'FALSE' => 'Off')
      );

$conf_def_property_list['DEVEL_MODE'] =
array ('label'       => 'Development mode'
      ,'description' => 'Add addtionnal tools in the SDK section of the platform administration.'
      ,'type'        => 'boolean'
      ,'default'     => 'TRUE'
      ,'container'   => 'CONST'
      ,'display'     => TRUE
      ,'readonly'    => FALSE
      ,'acceptedValue' => array ('TRUE'=>'On'
                               ,'FALSE'=>'Off')
      );

$conf_def_property_list['allowToSelfEnroll']
= array ('label'     => 'Personnal course list modification'
        ,'description' => 'Set if the users are allowed to modify their personnal courses list or not'
        ,'default'   => 'TRUE'
        ,'type'      => 'boolean'
        ,'display'       => TRUE
        ,'readonly'      => FALSE
        ,'acceptedValue' => array ( 'TRUE'=> 'Allowed', 'FALSE'=>'Not allowed' )
        );

?>