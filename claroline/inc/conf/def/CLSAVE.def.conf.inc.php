<?php //$Id$

// TOOL
$toolConf['config_code']='CLSAVE';
$toolConf['config_file']='export.conf.inc.php';
// $toolConf['config_repository']=''; dislabed = includePath.'/conf'
$toolConf['description'] = 'Export tool. This is a course tool';
$toolConf['section']['protocols']['label']='protocols';
$toolConf['section']['protocols']['properties'] = 
array ( 'localStoreArchiveAivailable'
      , 'downloadArchiveAivailable'
      , 'putArchiveOnFtpAivailable'
      );
      
//PROPERTIES

$toolConfProperties['localStoreArchiveAivailable']
= array ( 'label'     => 'User can store  his archive on server'
        , 'description' => 'It\'s a good soltuion because user can\'t edit  archive content'
        , 'default'   => 'TRUE'
        , 'type'      => 'boolean'
        , 'container' => 'VAR'
        );

$toolConfProperties['downloadArchiveAivailable']
= array ('label'       => 'User can get archive by download'
        ,'description' => 'Mean that user (course manager) can have all data.'
        ,'default'     => 'TRUE'
        ,'type'        => 'boolean'
        ,'container'   => 'VAR'
        );

$toolConfProperties['putArchiveOnFtpAivailable']
= array ('label'       => 'User can send archive on a FTP'
        ,'description' => 'Not yet aivailable'
        ,'default'     => 'FALSE'
        ,'type'        => 'boolean'
        ,'container'   => 'VAR'
        ,'readonly'    => TRUE
        );
//$sendByFTPAivailable 		= FALSE; // not  yet aivailable  in 1.4

$toolConf['section']['permissions']['label']='About the output format to store in archive';
$toolConf['section']['permissions']['description']='Data cans be export in several format.';
$toolConf['section']['permissions']['properties'] = 
array ( 'createNewCourseWithArchiveAivailable'
      , 'allowedToSelectBackupSection'
      );

        
$toolConfProperties['createNewCourseWithArchiveAivailable']
= array ('label'       => 'User can create a new course with direct filling from an archive'
        ,'description' => 'Not yet aivailable'
        ,'default'     => 'FALSE'
        ,'type'        => 'boolean'
        ,'container'   => 'VAR'
        ,'readonly'    => TRUE
        );

$toolConfProperties['allowedToSelectBackupSection']
= array ('label'       => 'User can select what would be add in package'
        ,'description' => 'This feature offer the possibility to course manager to select parts of cours wich would be exported.'
        ,'default'     => 'FALSE'
        ,'type'        => 'boolean'
        ,'container'   => 'VAR'
        ,'readonly'    => TRUE
        );

$toolConf['section']['exportFormat']['label']='About the output format to store in archive';
$toolConf['section']['exportFormat']['description']='Data cans be export in several format.';
$toolConf['section']['exportFormat']['properties'] = 
array ( 'allowedToSelectFormatTargetBackup'
      , 'buildHtmlExportAivailable'
      , 'buildSqlExportAivailable'
      , 'buildXmlExportAivailable'
      , 'buildCsvExportAivailable'
      );

$toolConfProperties['allowedToSelectFormatTargetBackup']
= array ('label'       => 'User can select in output format wich would be used. It\'s not about archive format but each sub part (txt,csv,html,sqlInsert,xls,xml,...)'
        ,'description' => 'Not yet aivailable'
        ,'default'     => 'FALSE'
        ,'type'        => 'boolean'
        ,'container'   => 'VAR'
        ,'readonly'    => TRUE
        );

$toolConfProperties['buildHtmlExportAivailable']
= array ('label'       => 'User can select HTML output format'
        ,'description' => 'Output content of DB in html format and place it in Documents'
        ,'default'     => 'TRUE'
        ,'type'        => 'boolean'
        ,'container'   => 'VAR'
        ,'readonly'    => TRUE
        );

$toolConfProperties['buildSqlExportAivailable']
= array ('label'       => 'User can select SQL output format'
        ,'description' => 'Not yet aivailable'
        ,'default'     => 'TRUE'
        ,'type'        => 'boolean'
        ,'container'   => 'VAR'
        ,'readonly'    => TRUE
        );

$toolConfProperties['buildCsvExportAivailable']
= array ('label'       => 'User can select CSV output format'
        ,'description' => 'Not yet aivailable'
        ,'default'     => 'TRUE'
        ,'type'        => 'boolean'
        ,'container'   => 'VAR'
        ,'readonly'    => TRUE
        );

$toolConfProperties['buildXmlExportAivailable']
= array ('label'       => 'User can select XML output format'
        ,'description' => 'Not yet aivailable'
        ,'default'     => 'FALSE'
        ,'type'        => 'boolean'
        ,'container'   => 'VAR'
        ,'readonly'    => TRUE
        );


$toolConf['section']['internalPaths']['label']='Internal path of archive';
$toolConf['section']['internalPaths']['description']='When a course is archive, some data come from central database, others from course database.';
$toolConf['section']['internalPaths']['properties'] = 
array ( 'appendMainDb'
      , 'appendCourse'
      );
//PROPERTIES
// You can change this but so  perhaps  restore isn't possible
$toolConfProperties['appendMainDb']
= array ('label'       => 'Internal path for platform data'
        ,'description' => 'contain user propreties and course properties with  attached category'
        ,'default'     => 'Central/'
        ,'type'        => 'relpath'
        ,'container'   => 'VAR'
        );

$toolConfProperties['appendCourse']
= array ('label'       => 'Internal path for courses data'
        ,'description' => 'When a course archive is build, some part '
        ,'default'     => 'Course/'
        ,'type'        => 'relpath'
        ,'container'   => 'VAR'
        );

?>