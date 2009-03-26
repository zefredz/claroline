<?php // $Id$
if ( count( get_included_files() ) == 1 ) die( '---' );
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
$conf_def['config_name']='Main settings';
$conf_def['config_class']='platform';

// SECTION

$conf_def['section']['ADMINISTRATIVE_SETTING']['label']='General';
$conf_def['section']['ADMINISTRATIVE_SETTING']['description']='Information about your platform and your organisation';
$conf_def['section']['ADMINISTRATIVE_SETTING']['properties'] =
array ( 'siteName'
      , 'institution_name'
      , 'institution_url'
      );

$conf_def['section']['LAYOUT']['label']='Layout';
$conf_def['section']['LAYOUT']['properties'] =
array ( 'claro_stylesheet'
      , 'siteLogo'
      , 'institutionLogo'
      );

$conf_def['section']['LANGUAGE']['label']='Language';
$conf_def['section']['LANGUAGE']['description']='';
$conf_def['section']['LANGUAGE']['properties'] =
array ( 'platformLanguage'
      , 'language_to_display'
      );

$conf_def['section']['ADMINISTRATOR_SETTING']['label']='Contact';
$conf_def['section']['ADMINISTRATOR_SETTING']['description']='These informations are displayed on the footer of the platform';
$conf_def['section']['ADMINISTRATOR_SETTING']['properties'] =
array ( 'administrator_name'
      , 'administrator_email'
      , 'administrator_phone'
      );

$conf_def['section']['FILE_SYSTEM_SETTING']['label']='File system settings';
$conf_def['section']['FILE_SYSTEM_SETTING']['properties'] =
array ( 'rootWeb'
      , 'rootSys'
      , 'urlAppend'
      , 'mysqlRepositorySys'
      , 'tmpPathSys'
      , 'cacheRepository'
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

$conf_def['section']['SMTP']['label']='SMTP';
$conf_def['section']['SMTP']['description']='Mail server configuration';
$conf_def['section']['SMTP']['properties'] =
array ( 'smtp_host'
      , 'smtp_username'
      , 'smtp_password'
      );

$conf_def['section']['RIGHT']['label']='Right';
$conf_def['section']['RIGHT']['properties'] =
array ( 'courseCreationAllowed',
       'allowSelfReg'
      , 'allowToSelfEnroll'
      );
      
$conf_def['section']['DOWNLOAD_SETTINGS']['label']='Download';
$conf_def['section']['DOWNLOAD_SETTINGS']['description']='Configure the way files are downloaded from the platform';
$conf_def['section']['DOWNLOAD_SETTINGS']['properties'] =
array ( 'useSendFile'
      , 'usePrettyUrl'
      );

$conf_def['section']['ADVANCED']['label']='Advanced settings';
$conf_def['section']['ADVANCED']['properties'] =
array ( 'userPasswordCrypted'
      , 'is_trackingEnabled'
      , 'useTinyMCECompressor'
      , 'claro_texRendererUrl'
      , 'platform_id'
      , 'CLARO_DEBUG_MODE'
      , 'triggerDebugMode'
      , 'CLARO_PROFILE_SQL'
      , 'DEVEL_MODE'
      , 'warnSessionLost'
      , 'claro_brailleViewMode'
      , 'javascriptCompression'
      // , 'secureDocumentDownload'
      );

// Platform

$conf_def_property_list['siteName'] =
array ('label'       => 'Platform name'
      ,'description' => ''
      ,'default'     => 'Claroline'
      ,'type'        => 'string'
      ,'display'     => TRUE
      ,'readonly'    => FALSE
      );

$conf_def_property_list['siteLogo'] =
array ('label'       => 'Platform logo url'
      ,'description' => 'Display the logo of the platform. (http://www.domain.tld/logo.gif)'
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
array ('label'       => 'Organisation website'
      ,'default'     => ''
      ,'type'        => 'string'
      ,'display'     => TRUE
      ,'readonly'    => FALSE
      );
$conf_def_property_list['institutionLogo'] =
array ('label'       => 'Organisation logo url'
      ,'description' => 'Display the logo of the organisation. (http://www.domain.tld/logo.gif)'
      ,'default'     => ''
      ,'type'        => 'string'
      ,'display'     => TRUE
      ,'readonly'    => FALSE
      );

// Language

$conf_def_property_list['platformLanguage'] =
array ('label'         => 'Platform language'
      ,'description'   => 'Set the default language of the platform. It doesn\'t prevent course managers to set an other language for each course they create.'
      ,'default'       => 'english'
      ,'type'          => 'enum'
      , 'acceptedValueType' => 'lang'
      ,'display'       => TRUE
      ,'readonly'      => FALSE
      );

$conf_def_property_list['language_to_display'] =
array ('label'         => 'Personal language selector'
      ,'description'   => 'For multilingual platform. Allow user to select his language from the list.'
      ,'default'       => array()
      ,'type'          => 'multi'
      ,'display'       => true
      ,'acceptedValueType' => 'lang'
      ,'readonly'      => FALSE
      );

// Database settings

$conf_def_property_list['dbHost'] =
array ('label'       => 'Host name'
      ,'default'     => 'localhost'
      ,'type'        => 'string'
      ,'display'     => TRUE
      ,'readonly'    => FALSE
      ,'technicalInfo' => 'The hostname of mysql server'
      );


$conf_def_property_list['dbLogin'] =
array ('label'       => 'Login'
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
      ,'default'     => TRUE
      ,'type'        => 'boolean'
      ,'display'     => TRUE
      ,'readonly'    => FALSE
      ,'acceptedValue' => array ('TRUE'=>'On', 'FALSE' => 'Off')
      );

$conf_def_property_list['singleDbEnabled'] =
array ('label'       => 'Database mode'
      ,'default'     => TRUE
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
      ,'type'        => 'string'
      ,'display'     => FALSE
      ,'readonly'    => FALSE
      );

// SMTP

$conf_def_property_list['smtp_host'] =
array ('label'       => 'SMTP server(s)'
      ,'description' => 'Give a SMTP server name to turn on SMTP mode. (e.g. smtp1.site.com or smtp1.site.com;smtp2.site.com)'
      ,'default'     => ''
      ,'type'        => 'string'
      ,'display'     => TRUE
      );

$conf_def_property_list['smtp_username'] =
array ('label'       => 'Username'
      ,'description' => 'Give a username and password to turn on SMTP authentication.'
      ,'default'     => ''
      ,'type'        => 'string'
      ,'display'     => TRUE
      );

$conf_def_property_list['smtp_password'] =
array ('label'       => 'Password'
      ,'description' => ''
      ,'default'     => ''
      ,'type'        => 'string'
      ,'display'     => TRUE
      );

// Path

$conf_def_property_list['rootWeb'] =
array ('label'       => 'Platform web URL'
      ,'description' => 'Example : http://www.yourdomain.tld/mycampus/'
      ,'default'     => 'http://www.yourdomain.tld/mycampus/'
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
      ,'default'       => 'tmp/'
      ,'display'       => true
      ,'type'          => 'relpath'
      );

$conf_def_property_list['cacheRepository'] =
array ('label'         => 'Repository for cache files and dirs'
      , 'description'  => 'Note : this repository should be protected with a .htaccess or
       be placed outside the web. Because there contain data of private courses. Claroline Would be able to read and write in this dir'
      ,'default'       => 'tmp/cache/'
      ,'display'       => true
      ,'type'          => 'relpath'
      );

$conf_def_property_list['garbageRepositorySys'] =
array ('label'       => 'Garbage'
      ,'description' => 'Absolute sys path to the place where are move data of a deleted course.'
      ,'default'     => 'tmp/garbage/'
      ,'type'        => 'syspath'
      ,'display'     => FALSE
      ,'readonly'    => FALSE
      );

// Layout

$conf_def_property_list['claro_stylesheet'] =
array ('label'       => 'Theme'
      ,'description' => 'Set the Cascading Style Sheet (CSS) layout.'
      ,'default'     => 'classic'
      ,'type'        => 'enum'
      ,'acceptedValueType' => 'css'
      ,'display'     => TRUE
      ,'readonly'    => FALSE
      );

$conf_def_property_list['useTinyMCECompressor'] =
array ('label'       => 'Use TinyMCE editor compressor'
      ,'description' => 'Makes TinyMCE 75% smaller and a lot faster to load.'
      ,'type'        => 'boolean'
      ,'default'     => TRUE
      ,'display'     => TRUE
      ,'readonly'    => FALSE
      , 'acceptedValue' => array('TRUE' => 'On', 'FALSE' => 'Off')
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
      ,'type'        => 'email'
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
      ,'default'       => FALSE
      ,'type'          => 'boolean'
      ,'display'       => false
      ,'readonly'      => True
      ,'acceptedValue' => array('TRUE' => 'Yes', 'FALSE' => 'No')
      );

$conf_def_property_list['allowSelfReg'] =
array ('label'           => 'User account creation allowed'
       ,'description'    => 'Display link "Create user account" on the platform homepage.'
      ,'default'         => TRUE
      ,'type'            => 'boolean'
      ,'display'         => TRUE
      ,'readonly'        => FALSE
      ,'acceptedValue' => array('TRUE' => 'Yes', 'FALSE' => 'No')
      );

$conf_def_property_list['clarolineRepositoryAppend'] =
array ('label'       => 'Relative path from root campus to claroline code'
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
array ('label'        => 'Relative path from claroline kernel to root of admin section'
      ,'type'        => 'relpath'
      ,'default'     => 'admin/'
      ,'display'     => false
      );
$conf_def_property_list['imgRepositoryAppend'] =
array ('label'        => 'Relative path from claroline web to icon set'
      ,'type'        => 'relpath'
      ,'default'     => 'img/'
      ,'display'     => FALSE
      ,'readonly'    => TRUE
      );

$conf_def_property_list['userImageRepositoryAppend'] =
array ('label'        => 'relative path from root web to user pic repository'
      ,'type'        => 'relpath'
      ,'display'     => FALSE
      ,'default'     => 'platform/img/users/'
      );

$conf_def_property_list['CLARO_DEBUG_MODE'] =
array ('label'       => 'Debug mode'
      ,'description' => 'More verbose when error occurs.'
      ,'type'        => 'boolean'
      ,'default'     => false
      ,'container'   => 'CONST'
      ,'display'     => TRUE
      ,'readonly'    => FALSE
      , 'acceptedValue' => array('TRUE' => 'On', 'FALSE' => 'Off')
      );

$conf_def_property_list['CLARO_PROFILE_SQL'] =
array ('label'       => 'Profile SQL'
      ,'description' => 'Profile SQL in DEBUG MODE.' ."\n" . 'Display for each request :duration, counter,  statement '
      ,'type'        => 'boolean'
      ,'default'     => false
      ,'container'   => 'CONST'
      ,'display'     => TRUE
      ,'readonly'    => FALSE
      , 'acceptedValue' => array('TRUE' => 'On', 'FALSE' => 'Off')
      );

$conf_def_property_list['warnSessionLost'] =
array ('label'       => 'Session lost warning'
      ,'description' => 'Warn users when they loose their session on the platform'
      ,'type'        => 'boolean'
      ,'default'     => TRUE
      ,'display'     => TRUE
      ,'readonly'    => FALSE
      , 'acceptedValue' => array('TRUE' => 'On', 'FALSE' => 'Off')
      );
      
$conf_def_property_list['javascriptCompression'] =
array ('label'       => 'Javascript compression'
      ,'description' => 'Compress javascript files. This option should be set to off only for debugging.'
      ,'type'        => 'boolean'
      ,'default'     => FALSE
      ,'display'     => TRUE
      ,'readonly'    => FALSE
      , 'acceptedValue' => array('TRUE' => 'On', 'FALSE' => 'Off')
      );

$conf_def_property_list['DEVEL_MODE'] =
array ('label'       => 'Development mode'
      ,'description' => 'Add additional tools in the SDK section of the platform administration.'
      ,'type'        => 'boolean'
      ,'default'     => false
      ,'container'   => 'CONST'
      ,'display'     => TRUE
      ,'readonly'    => FALSE
      ,'acceptedValue' => array ('TRUE'=>'On'
                               ,'FALSE'=>'Off')
      );

$conf_def_property_list['triggerDebugMode'] =
array ('label'       => 'Trigger debug mode in url'
      ,'description' => ''
      ,'type'        => 'boolean'
      ,'default'     => FALSE
      ,'display'     => TRUE
      ,'readonly'    => FALSE
      , 'acceptedValue' => array('TRUE' => 'On', 'FALSE' => 'Off')
      );

$conf_def_property_list['courseCreationAllowed'] =
array ('label'       => 'Course creation is allowed on the platform'
      ,'description' => ''
      ,'default'     => TRUE
      ,'type'        => 'boolean'
      ,'acceptedValue' => array ('TRUE' => 'Yes'
                                ,'FALSE'=> 'No'
                                )
      );

$conf_def_property_list['allowToSelfEnroll']
= array ('label'     => 'Allow enrolment/unenrolment to courses by the users'
        ,'description' => 'Display links to enrol/unenrol to course on the homepage of the user'
        ,'default'   => TRUE
        ,'type'      => 'boolean'
        ,'display'       => TRUE
        ,'readonly'      => FALSE
        ,'acceptedValue' => array ( 'TRUE'=> 'Yes', 'FALSE'=>'No' )
        );


$conf_def_property_list['module_cache_filename']
= array ('label'     => 'filename for one file module inclusion'
        ,'description' => ''
        ,'default'   => 'moduleCache.inc.php'
        ,'type'      => 'filename'
        ,'display'       => FALSE
        ,'readonly'      => TRUE
        ,'acceptedValue' => array ( 'pattern'=> '*.inc.php')
        );

$conf_def_property_list['claro_brailleViewMode'] =
array ('label'       => 'Display banner'
      ,'description' => 'This feature is use for institute with  blind users. ' . "\n" . ' I fact, System would be ehanced to activate this view user by user'
      ,'default'     => false
      ,'type'        => 'boolean'
      ,'display'     => false
      ,'readonly'    => false
      ,'acceptedValue' => array ('FALSE' => 'on top', 'TRUE'=>'on bottom')
      );

/*$conf_def_property_list['secureDocumentDownload'] =
array ( 'description' => 'Increase the security of file download. This option only works on Apache Server. To be really secure, this option have to be completed by an .htaccess file on the course folders.'
      , 'label'       => 'Secure document download'
      , 'default'     => FALSE
      , 'type'        => 'boolean'
      , 'acceptedValue' => array ('TRUE'=>'On'
                                 ,'FALSE'=>'Off'
                               )
      , 'display'     => TRUE
      , 'readonly'    => FALSE
      );*/
      
// File Download
      
$conf_def_property_list['useSendFile'] =
array ( 'description' => 'Select the way Claroline send files to a user.'
      , 'label'       => 'Download mechanism'
      , 'default'     => TRUE
      , 'type'        => 'boolean'
      , 'acceptedValue' => array ('TRUE'=>'Send file using PHP (mask real file location)'
                                 ,'FALSE'=>'Redirect to the file'
                               )
      , 'display'     => TRUE
      , 'readonly'    => FALSE
      );
      
$conf_def_property_list['usePrettyUrl'] =
array ( 'description' => 'Choose the mode for URL for file download. Warning : Pretty URL mode don\'t work with IIS.'
      , 'label'       => 'File url mode'
      , 'default'     => FALSE
      , 'type'        => 'boolean'
      , 'acceptedValue' => array ('TRUE'=>'Pretty URL using PATH_INFO (download.php/path/to/file.ext)'
                                 ,'FALSE'=>'Standard URL using QUERY_STRING (download.php?url=/path/to/file.ext)'
                               )
      , 'display'     => TRUE
      , 'readonly'    => FALSE
      );

?>
