<?php //$Id$

// TOOL
$conf_def['config_code']='CLSAVE';
$conf_def['config_file']='export.conf.inc.php';
$conf_def['config_name']='general setting for exporting data';
// $conf_def['config_repository']=''; dislabed = includePath.'/conf'
$conf_def['description'] = 'Export tool. This is a course tool';
$conf_def['section']['protocols']['label']='protocols';
$conf_def['section']['protocols']['properties'] = 
array ( 'localStoreArchiveAivailable'
      , 'downloadArchiveAivailable'
      , 'putArchiveOnFtpAivailable'
      );
      
//PROPERTIES

$conf_def_property_list['localStoreArchiveAivailable']
= array ( 'label'     => 'User can store  his archive on server'
        , 'description' => 'It\'s a good soltuion because user can\'t edit  archive content'
        , 'default'   => 'TRUE'
        , 'type'      => 'boolean'
        , 'container' => 'VAR'
        );

$conf_def_property_list['downloadArchiveAivailable']
= array ('label'       => 'User can get archive by download'
        ,'description' => 'Mean that user (course manager) can have all data.'
        ,'default'     => 'TRUE'
        ,'type'        => 'boolean'
        ,'container'   => 'VAR'
        );

$conf_def_property_list['putArchiveOnFtpAivailable']
= array ('label'       => 'User can send archive on a FTP'
        ,'description' => 'Not yet aivailable'
        ,'default'     => 'FALSE'
        ,'type'        => 'boolean'
        ,'container'   => 'VAR'
        ,'readonly'    => TRUE
        );
//$sendByFTPAivailable 		= FALSE; // not  yet aivailable  in 1.4

$conf_def['section']['permissions']['label']='About the output format to store in archive';
$conf_def['section']['permissions']['description']='Data cans be export in several format.';
$conf_def['section']['permissions']['properties'] = 
array ( 'createNewCourseWithArchiveAivailable'
      , 'allowedToSelectBackupSection'
      );

        
$conf_def_property_list['createNewCourseWithArchiveAivailable']
= array ('label'       => 'User can create a new course with direct filling from an archive'
        ,'description' => 'Not yet aivailable'
        ,'default'     => 'FALSE'
        ,'type'        => 'boolean'
        ,'container'   => 'VAR'
        ,'readonly'    => TRUE
        );

$conf_def_property_list['allowedToSelectBackupSection']
= array ('label'       => 'User can select what would be add in package'
        ,'description' => 'This feature offer the possibility to course manager to select parts of cours wich would be exported.'
        ,'default'     => 'FALSE'
        ,'type'        => 'boolean'
        ,'container'   => 'VAR'
        ,'readonly'    => TRUE
        );

$conf_def['section']['exportFormat']['label']='About the output format to store in archive';
$conf_def['section']['exportFormat']['description']='Data cans be export in several format.';
$conf_def['section']['exportFormat']['properties'] = 
array ( 'allowedToSelectFormatTargetBackup'
      , 'buildHtmlExportAivailable'
      , 'buildSqlExportAivailable'
      , 'buildXmlExportAivailable'
      , 'buildCsvExportAivailable'
      );

$conf_def_property_list['allowedToSelectFormatTargetBackup']
= array ('label'       => 'User can select in output format wich would be used. It\'s not about archive format but each sub part (txt,csv,html,sqlInsert,xls,xml,...)'
        ,'description' => 'Not yet aivailable'
        ,'default'     => 'FALSE'
        ,'type'        => 'boolean'
        ,'container'   => 'VAR'
        ,'readonly'    => TRUE
        );

$conf_def_property_list['buildHtmlExportAivailable']
= array ('label'       => 'User can select HTML output format'
        ,'description' => 'Output content of DB in html format and place it in Documents'
        ,'default'     => 'TRUE'
        ,'type'        => 'boolean'
        ,'container'   => 'VAR'
        ,'readonly'    => TRUE
        );

$conf_def_property_list['buildSqlExportAivailable']
= array ('label'       => 'User can select SQL output format'
        ,'description' => 'Not yet aivailable'
        ,'default'     => 'TRUE'
        ,'type'        => 'boolean'
        ,'container'   => 'VAR'
        ,'readonly'    => TRUE
        );

$conf_def_property_list['buildCsvExportAivailable']
= array ('label'       => 'User can select CSV output format'
        ,'description' => 'Not yet aivailable'
        ,'default'     => 'TRUE'
        ,'type'        => 'boolean'
        ,'container'   => 'VAR'
        ,'readonly'    => TRUE
        );

$conf_def_property_list['buildXmlExportAivailable']
= array ('label'       => 'User can select XML output format'
        ,'description' => 'Not yet aivailable'
        ,'default'     => 'FALSE'
        ,'type'        => 'boolean'
        ,'container'   => 'VAR'
        ,'readonly'    => TRUE
        );


$conf_def['section']['internalPaths']['label']='Internal path of archive';
$conf_def['section']['internalPaths']['description']='When a course is archive, some data come from central database, others from course database.';
$conf_def['section']['internalPaths']['properties'] = 
array ( 'appendMainDb'
      , 'appendCourse'
      );
//PROPERTIES
// You can change this but so  perhaps  restore isn't possible
$conf_def_property_list['appendMainDb']
= array ('label'       => 'Internal path for platform data'
        ,'description' => 'contain user propreties and course properties with  attached category'
        ,'default'     => 'Central/'
        ,'type'        => 'relpath'
        ,'container'   => 'VAR'
        );

$conf_def_property_list['appendCourse']
= array ('label'       => 'Internal path for courses data'
        ,'description' => 'When a course archive is build, some part '
        ,'default'     => 'Course/'
        ,'type'        => 'relpath'
        ,'container'   => 'VAR'
        );

?>