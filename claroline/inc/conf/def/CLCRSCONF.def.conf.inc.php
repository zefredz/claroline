<?php // $Id$
// TOOL
$conf_def['config_code']='CLCRSCONF';
$conf_def['config_file']='course_info.conf.php';
$conf_def['config_name']='general setting for course setting tool';
$conf_def['description']='Configuration du cours';
$conf_def['section']['links']['label']='links';
$conf_def['section']['links']['description']='links to  commands';
$conf_def['section']['links']['properties'] = 
array ( 'showLinkToDeleteThisCourse'
      , 'showLinkToExportThisCourse'
      , 'showLinkToRestoreCourse'
      );
$conf_def['section']['information']['label']='information';
$conf_def['section']['information']['description']='information about the course';
$conf_def['section']['information']['properties'] = 
array ( 'multiGroupAllowed'
      , 'tutorCanBeSimpleMemberOfOthersGroupsAsStudent'
      , 'showTutorsInGroupList'
      );
//PROPERTIES

$conf_def_property_list['showLinkToDeleteThisCourse']
= array ('label'     => 'Show link to call the deletion of the course'
        ,'default'   => 'TRUE'
        ,'type'      => 'boolean'
        ,'container' => 'VAR'
        );
$conf_def_property_list['showLinkToExportThisCourse']
= array ('label'     => 'Show link to make an archive of the cours'
        ,'default'   => 'FALSE'
        ,'type'      => 'boolean'
        ,'container' => 'VAR'
        );
$conf_def_property_list['showLinkToRestoreCourse']
= array ('label'     => 'Show link to call the restore of a course'
        ,'default'   => 'FALSE'
        ,'type'      => 'boolean'
        ,'container' => 'VAR'
        );

// If TRUE, these fileds  keep the previous content.
$canBeEmpty["screenCode"] 	= FALSE;
$canBeEmpty["int"] 			= FALSE;
$canBeEmpty["facu"] 		= FALSE;
$canBeEmpty["description"] 	= TRUE;
$canBeEmpty["visible"] 		= FALSE;
$canBeEmpty["titulary"] 	= FALSE;
$canBeEmpty["lanCourseForm"]= FALSE;
$canBeEmpty["extLinkName"]	= TRUE;
$canBeEmpty["extLinkUrl"] 	= TRUE;
$canBeEmpty["email"]		= TRUE;


$conf_def['section']['flags']['label']='options';
$conf_def['section']['flags']['description']='switch option for courses';
$conf_def['section']['flags']['properties'] = 
array ( 'showDiskQuota'
      , 'showDiskUse'
      , 'showLinkToChangeDiskQuota'
      , 'showExpirationDate'
      , 'showCreationDate'
      , 'showLastEdit'
      , 'showLastVisit'
      , 'canReportExpirationDate'
      , 'linkToChangeDiskQuota'
      );

$conf_def_property_list['showDiskQuota']
= array ('label'     => 'Show in course setting the quota of course'
        ,'default'   => 'FALSE'
        ,'type'      => 'boolean'
        ,'container' => 'VAR'
        );

$conf_def_property_list['showDiskUse']
= array ('label'     => 'Show in course setting the space disk used by course'
        ,'default'   => 'FALSE'
        ,'type'      => 'boolean'
        ,'container' => 'VAR'
        );

$conf_def_property_list['showLinkToChangeDiskQuota']
= array ('label'     => 'Show in course link to script to request a change of the quota'
        ,'default'   => 'FALSE'
        ,'type'      => 'boolean'
        ,'container' => 'VAR'
        );

$conf_def_property_list['showExpirationDate']
= array ('label'     => 'Show in course setting the date of expiration of the course'
        ,'default'   => 'FALSE'
        ,'type'      => 'boolean'
        ,'container' => 'VAR'
        );

$conf_def_property_list['showCreationDate']
= array ('label'     => 'Show in course setting the date creation of the course'
        ,'default'   => 'TRUE'
        ,'type'      => 'boolean'
        ,'container' => 'VAR'
        );

$conf_def_property_list['showLastEdit']
= array ('label'     => 'Show in course setting the date of last edtion detected in course'
        ,'default'   => 'TRUE'
        ,'type'      => 'boolean'
        ,'container' => 'VAR'
        );

$conf_def_property_list['showLastVisit']
= array ('label'     => 'Show in course setting the date of last visit in course'
        ,'default'   => 'TRUE'
        ,'type'      => 'boolean'
        ,'container' => 'VAR'
        );

$conf_def_property_list['canReportExpirationDate'] 
= array ( 'label'        => 'Is  course admin eable to request an time credit  for his courses'
        , 'description' => 'need to be TRUE if ScriptToReportExpirationDate  is not automaticly called'
        , 'default'      => 'FALSE'
        , 'type'         => 'boolean'
        , 'container'    => 'VAR'
        );

$conf_def_property_list['linkToChangeDiskQuota']
= array ('label'     => 'external script to change quota allowed to course.'
        ,'default'   => 'changeQuota.php'
        ,'type'      => 'string'
        ,'container' => 'VAR'
        );

$conf_def_property_list['urlScriptToReportExpirationDate']
= array ('label'     => 'external script to postpone the expiration of course.'
        ,'default'   => 'postpone.php'
        ,'type'      => 'string'
        ,'container' => 'VAR'
        );


?>
