<?php // $Id$
/**
 * This file describe the parameter for Course creation tool config file
 * @author Christophe Gesché <moosh@claroline.net>
 * @version CLAROLINE 1.6
 * @copyright &copy; 2001-2005 Universite catholique de Louvain (UCL)
 * @license This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
 * as published by the FREE SOFTWARE FOUNDATION. The GPL is available 
 * through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
 * @see http://www.claroline.net/wiki/index.php/Config
 * @package COURSES
 */

$conf_def['config_code']='CLCRS';
$conf_def['config_name']='Course options';
$conf_def['config_file']='course_main.conf.php';
$conf_def['old_config_file'][]='add_course.conf.php';

$conf_def['section']['create']['label']='Course parameters';
$conf_def['section']['create']['description']='These settings will be use whenever a user creates a new course';
$conf_def['section']['create']['properties'] = 
array ( 'defaultVisibilityForANewCourse'
      , 'HUMAN_CODE_NEEDED'
      , 'HUMAN_LABEL_NEEDED'
      , 'COURSE_EMAIL_NEEDED'
      , 'extLinkNameNeeded'
      , 'extLinkUrlNeeded'
      , 'prefixAntiNumber'
      , 'prefixAntiEmpty');

$conf_def_property_list['defaultVisibilityForANewCourse'] = 
array ('label'       => 'Default visibility for new course'
      ,'description' => 'hide = the course can be acces without subscription to this course.
      open = an authenticated user on the platform can subscribe the course.
      '
      ,'default'     => '2'
      ,'type'        => 'enum'
      ,'display'     => TRUE
      ,'readonly'    => FALSE
      ,'acceptedValue' => array ('0'=>'hide and closed'
                                ,'1'=>'visible and closed'
                                ,'2'=>'visible and open'
                                ,'3'=>'hide and open'
                                )
      );

$conf_def_property_list['HUMAN_CODE_NEEDED'] = 
array ('label'       => 'Course code is'
      ,'description' => 'User can leave course code (officialCode) field empty or not'
      ,'default'     => 'TRUE'
      ,'type'        => 'boolean'
      ,'container'   => 'CONST'
      ,'acceptedValue' => array ('TRUE' => 'Required'
                                ,'FALSE'=> 'Optional'
                                )
      );

$conf_def_property_list['HUMAN_LABEL_NEEDED'] = 
array ('label'       => 'Course label (name) is'
      ,'description' => 'User can leave course label (name) field empty or not'
      ,'default'     => 'TRUE'
      ,'type'        => 'boolean'
      ,'container'   => 'CONST'
      ,'acceptedValue' => array ('TRUE'=>'Required'
                              ,'FALSE'=>'Optional'
                              )
      );

$conf_def_property_list['COURSE_EMAIL_NEEDED'] = 
array ('label'       => 'Course email email is'
      ,'description' => 'User can leave email field empty or not'
      ,'default'     => 'FALSE'
      ,'type'        => 'boolean'
      ,'display'     => TRUE
      ,'readonly'    => FALSE
      ,'container'   => 'CONST'
      ,'acceptedValue' => array ('TRUE'=>'Required'
                              ,'FALSE'=>'Optional'
                              )
      );

$conf_def_property_list['extLinkNameNeeded'] = 
array ('label'       => 'Label of external Link is'
      ,'description' => 'This name is show in top right of course banner'
      ,'default'     => 'FALSE'
      ,'type'        => 'boolean'
      ,'display'     => TRUE
      ,'readonly'    => FALSE
      ,'acceptedValue' => array ('TRUE'=>'Required (choose this is Url of external Link is set)'
                              ,'FALSE'=>'Optional'
                              )
      );
$conf_def_property_list['extLinkUrlNeeded'] = 
array ('label'       => 'Url of external Link is Label of external Link'
      ,'description' => 'This url is under the '
      ,'default'     => 'FALSE'
      ,'type'        => 'boolean'
      ,'display'     => TRUE
      ,'readonly'    => FALSE
      ,'acceptedValue' => array ('TRUE'=>'Required'
                              ,'FALSE'=>'Optional'
                              )
      );

$conf_def_property_list['prefixAntiNumber'] = 
array ('label'       => 'Prefix course code beginning with number'
      ,'description' => 'This string is prepend to course database name if it begins with a number'
      ,'default'     => 'No'
      ,'display'     => FALSE
      ,'readonly'    => TRUE
      ,'type'        => 'string'
      );

$conf_def_property_list['prefixAntiEmpty'] = 
array ('label'       => 'Prefix for empty code course'
      ,'default'     => 'Course'
      ,'display'     => FALSE
      ,'readonly'    => TRUE
      ,'type'        => 'string'
      );

// Course Setting Section

$conf_def['section']['links']['label']='Course settings';
$conf_def['section']['links']['description']='These settings will be use whenever a user modify course settings';
$conf_def['section']['links']['properties'] =
array ( 'showLinkToDeleteThisCourse'
      , 'showLinkToExportThisCourse'
      , 'showLinkToRestoreCourse'
      );

$conf_def_property_list['showLinkToDeleteThisCourse']
= array ('label'     => 'Allow course manager to delete course'
        ,'default'   => 'TRUE'
        ,'type'      => 'boolean'
        ,'container' => 'VAR'
        ,'acceptedValue' => array ('TRUE'  => 'Yes'
                                  ,'FALSE' => 'No'
                                  )
        );
$conf_def_property_list['showLinkToExportThisCourse']
= array ('label'         => 'Show link to make an archive of the cours'
        ,'description'   => 'this tool is broken in claroline 1.6 Activate it only if you want work on it'
        ,'default'       => 'FALSE'
        ,'display'       => FALSE
        ,'type'          => 'boolean'
        ,'container'     => 'VAR'
        ,'acceptedValue' => array ('TRUE'  => 'Yes'
                                  ,'FALSE' => 'No'
                                  )
        );

$conf_def_property_list['showLinkToRestoreCourse']
= array ('label'     => 'Show link to call the restore of a course'
        ,'description'   => 'this tool is broken in claroline 1.6 Activate it only if you want work on it'
        ,'display'   => FALSE
        ,'default'   => 'FALSE'
        ,'type'      => 'boolean'
        ,'container' => 'VAR'
        ,'acceptedValue' => array ('TRUE'  => 'Yes'
                                  ,'FALSE' => 'No'
                                  )
        );

// Course properties rules
// Not displayed

$conf_def['section']['restore']['label']='Restore // Create a course from an archive';
$conf_def['section']['restore']['description']='this tool is broken in claroline 1.6 Activate it only if you want work on it';
$conf_def['section']['restore']['display']=FALSE;
$conf_def['section']['restore']['properties'] = 
array ( 'is_allowedToRestore'
      , 'sendByUploadAivailable'
      , 'sendByLocaleAivailable'
      , 'sendByHTTPAivailable'
      , 'sendByFTPAivailable'
      , 'localArchivesRepository'
      );

$conf_def_property_list['is_allowedToRestore'] = 
array ('label'       => 'Course creator can create a course from an archive'
      ,'default'     => 'FALSE'
      ,'type'        => 'boolean'
      ,'container'   => 'CONST'
      ,'display'     => FALSE
      ,'acceptedValue' => array ('TRUE'=>'Enabled'
                              ,'FALSE'=>'Disabled'
                              )
      );

$conf_def_property_list['sendByUploadAivailable'] = 
array ('label'       => 'Course creator can upload an archive to restore as new course'
      ,'description' => 'is_allowedToRestore must be Enabled' 
      ,'default'     => 'FALSE'
      ,'type'        => 'boolean'
      ,'display'     => FALSE
      ,'container'   => 'CONST'
      ,'acceptedValue' => array ('TRUE'=>'Enabled'
                                ,'FALSE'=>'Disabled'
                              )
      );

$conf_def_property_list['sendByLocaleAivailable'] = 
array ('label'       => 'Course manager can restore a local archive'
      ,'description' => 'is_allowedToRestore must be Enabled' 
      ,'default'     => 'FALSE'
      ,'display'     => FALSE
      ,'type'        => 'boolean'
      ,'container'   => 'CONST'
      ,'acceptedValue' => array ('TRUE'=>'Enabled'
                              ,'FALSE'=>'Disabled'
                              )
      );

$conf_def_property_list['sendByHTTPAivailable'] = 
array ('label'       => 'Course manager can restore an archive from the web'
      ,'description' => 'is_allowedToRestore must be Enabled' 
      ,'default'     => 'FALSE'
      ,'type'        => 'boolean'
      ,'display'     => FALSE
      ,'readonly'    => TRUE
      ,'container'   => 'CONST'
      ,'acceptedValue' => array ('TRUE'=>'Enabled'
                              ,'FALSE'=>'Disabled'
                              )
      );

$conf_def_property_list['sendByFTPAivailable'] = 
array ('label'       => 'Restaurer une archive de cours présente sur un serveur FTP'
      ,'description' => 'is_allowedToRestore must be Enabled' 
      ,'default'     => 'FALSE'
      ,'type'        => 'boolean'
      ,'display'     => FALSE
      ,'readonly'    => TRUE
      ,'container'   => 'CONST'
      ,'acceptedValue' => array ('TRUE'=>'Enabled'
                              ,'FALSE'=>'Disabled'
                              )
      );

$conf_def_property_list['localArchivesRepository'] = 
array ('label'       => 'Repository where stored archives on server'
      ,'default'     => str_replace('\\','/',realpath($rootSys."archive/"))
      ,'type'        => 'filepath'
      );


// Course properties rules
$conf_def['section']['expiration']['label']='Fix a delay for consider a course as expired';
$conf_def['section']['expiration']['display']=FALSE;
$conf_def['section']['expiration']['properties'] = 
array ( 'firstExpirationDelay'
      );

$conf_def_property_list['firstExpirationDelay'] = 
array ('label'       => 'Time to expire the created course (in second)'
      ,'default'     => '31536000' // <- 86400*365    // 60*60*24 = 1 jour = 86400
      ,'unit'        => 'second'
      ,'type'        => 'integer'
      );

$conf_def_property_list['is_allowedToRestore'] = 
array ('label'       => 'All courses manager can create a course from an archive'
      ,'default'     => 'FALSE'
      ,'type'        => 'boolean'
      ,'display'     => TRUE
      ,'readonly'    => FALSE
      ,'acceptedValue' => array ('TRUE'  =>'Enabled'
                                ,'FALSE' =>'Disabled'
                                )
      );

// Course optionnal config

$conf_def['section']['flags']['label']       = 'options';
$conf_def['section']['flags']['display']     = FALSE;
$conf_def['section']['flags']['description'] = 'switch option for courses';
$conf_def['section']['flags']['properties']  =
array ( 'showDiskQuota'
      , 'showDiskUse'
      , 'showLinkToChangeDiskQuota'
      , 'showExpirationDate'
      , 'showCreationDate'
      , 'showLastEdit'
      , 'showLastVisit'
      , 'canReportExpirationDate'
      , 'linkToChangeDiskQuota'
      , 'urlScriptToReportExpirationDate'
      );

$conf_def_property_list['showDiskQuota']
= array ('label'     => 'Show in course setting the quota of course'
        ,'default'   => 'FALSE'
        ,'display'   => FALSE
        ,'type'      => 'boolean'
        ,'container' => 'VAR'
        ,'acceptedValue' => array ('TRUE'  => 'Yes'
                                  ,'FALSE' => 'No'
                                  )
        );

$conf_def_property_list['showDiskUse']
= array ('label'     => 'Show in course setting the space disk used by course'
        ,'display'   => FALSE
        ,'default'   => 'FALSE'
        ,'type'      => 'boolean'
        ,'container' => 'VAR'
        ,'acceptedValue' => array ('TRUE'  => 'Yes'
                                  ,'FALSE' => 'No'
                                  )
        );

$conf_def_property_list['showLinkToChangeDiskQuota']
= array ('label'     => 'Show in course link to script to request a change of the quota'
        ,'display'   => FALSE
        ,'default'   => 'FALSE'
        ,'type'      => 'boolean'
        ,'container' => 'VAR'
        ,'acceptedValue' => array ('TRUE'  => 'Yes'
                                  ,'FALSE' => 'No'
                                  )
        );

$conf_def_property_list['showExpirationDate']
= array ('label'     => 'Show in course setting the date of expiration of the course'
        ,'display'   => FALSE
        ,'default'   => 'FALSE'
        ,'type'      => 'boolean'
        ,'container' => 'VAR'
        ,'acceptedValue' => array ('TRUE'  => 'Yes'
                                  ,'FALSE' => 'No'
                                  )
        );

$conf_def_property_list['showCreationDate']
= array ('label'     => 'Show in course setting the date creation of the course'
        ,'display'   => FALSE
        ,'default'   => 'FALSE'
        ,'type'      => 'boolean'
        ,'container' => 'VAR'
        ,'acceptedValue' => array ('TRUE'  => 'Yes'
                                  ,'FALSE' => 'No'
                                  )
        );

$conf_def_property_list['showLastEdit']
= array ('label'     => 'Show in course setting the date of last edtion detected in course'
        ,'display'   => FALSE
        ,'default'   => 'FALSE'
        ,'type'      => 'boolean'
        ,'container' => 'VAR'
        ,'acceptedValue' => array ('TRUE'  => 'Yes'
                                  ,'FALSE' => 'No'
                                  )
        );

$conf_def_property_list['showLastVisit']
= array ('label'     => 'Show in course setting the date of last visit in course'
        ,'display'   => FALSE
        ,'default'   => 'FALSE'
        ,'type'      => 'boolean'
        ,'container' => 'VAR'
        ,'acceptedValue' => array ('TRUE'  => 'Yes'
                                  ,'FALSE' => 'No'
                                  )
        );

$conf_def_property_list['canReportExpirationDate']
= array ( 'label'        => 'Is course admin eable to request an time credit for his courses'
        , 'display'   => FALSE
        , 'description'  => 'Need to be TRUE if ScriptToReportExpirationDate is not automaticly called'
        , 'default'      => 'FALSE'
        , 'type'         => 'boolean'
        , 'container'    => 'VAR'
        ,'acceptedValue' => array ('TRUE'  => 'Yes'
                                  ,'FALSE' => 'No'
                                  )
        );

$conf_def_property_list['linkToChangeDiskQuota']
= array ('label'     => 'External script to change quota allowed to course.'
        ,'display'   => FALSE
        ,'default'   => 'changeQuota.php'
        ,'type'      => 'string'
        ,'container' => 'VAR'
        );

$conf_def_property_list['urlScriptToReportExpirationDate']
= array ('label'     => 'External script to postpone the expiration of course.'
        ,'display'   => FALSE
        ,'default'   => 'postpone.php'
        ,'type'      => 'string'
        ,'container' => 'VAR'
        );

?>