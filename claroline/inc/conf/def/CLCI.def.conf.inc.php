<?php // $Id$

// TOOL
$toolConf['label']='CLCRSCONF';
$toolConf['file']='course_info.conf.php';
$toolConf['description']='Configuration du cours';
$toolConf['section']['links']['label']='links';
$toolConf['section']['links']['description']='links to  commands';
$toolConf['section']['links']['properties'] = 
array ( 'showLinkToDeleteThisCourse'
      , 'showLinkToExportThisCourse'
      , 'showLinkToRestoreCourse'
      );
$toolConf['section']['information']['label']='information';
$toolConf['section']['information']['description']='information about the course';
$toolConf['section']['information']['properties'] = 
array ( 'multiGroupAllowed'
      , 'tutorCanBeSimpleMemberOfOthersGroupsAsStudent'
      , 'showTutorsInGroupList'
      );
//PROPERTIES

$toolConfProperties['showLinkToDeleteThisCourse']
= array ('label'     => 'Show link to call the deletion of the course'
        ,'default'   => 'TRUE'
        ,'type'      => 'boolean'
        ,'container' => 'VAR'
        );
$toolConfProperties['showLinkToExportThisCourse']
= array ('label'     => 'Show link to make an archive of the cours'
        ,'default'   => 'FALSE'
        ,'type'      => 'boolean'
        ,'container' => 'VAR'
        );
$toolConfProperties['showLinkToRestoreCourse']
= array ('label'     => 'Show link to call the restore of a course'
        ,'default'   => 'FALSE'
        ,'type'      => 'boolean'
        ,'container' => 'VAR'
        );

// If true, these fileds  keep the previous content.
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


$toolConf['section']['flags']['label']='options';
$toolConf['section']['flags']['description']='switch option for courses';
$toolConf['section']['flags']['properties'] = 
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

$toolConfProperties['showDiskQuota']
= array ('label'     => 'Show in course setting the quota of course'
        ,'default'   => 'FALSE'
        ,'type'      => 'boolean'
        ,'container' => 'VAR'
        );

$toolConfProperties['showDiskUse']
= array ('label'     => 'Show in course setting the space disk used by course'
        ,'default'   => 'FALSE'
        ,'type'      => 'boolean'
        ,'container' => 'VAR'
        );

$toolConfProperties['showLinkToChangeDiskQuota']
= array ('label'     => 'Show in course link to script to request a change of the quota'
        ,'default'   => 'FALSE'
        ,'type'      => 'boolean'
        ,'container' => 'VAR'
        );

$toolConfProperties['showExpirationDate']
= array ('label'     => 'Show in course setting the date of expiration of the course'
        ,'default'   => 'FALSE'
        ,'type'      => 'boolean'
        ,'container' => 'VAR'
        );

$toolConfProperties['showCreationDate']
= array ('label'     => 'Show in course setting the date creation of the course'
        ,'default'   => 'TRUE'
        ,'type'      => 'boolean'
        ,'container' => 'VAR'
        );

$toolConfProperties['showLastEdit']
= array ('label'     => 'Show in course setting the date of last edtion detected in course'
        ,'default'   => 'TRUE'
        ,'type'      => 'boolean'
        ,'container' => 'VAR'
        );

$toolConfProperties['showLastVisit']
= array ('label'     => 'Show in course setting the date of last visit in course'
        ,'default'   => 'TRUE'
        ,'type'      => 'boolean'
        ,'container' => 'VAR'
        );

$toolConfProperties['canReportExpirationDate'] 
= array ( 'label'        => 'Is  course admin eable to request an time credit  for his courses'
        , 'description' => 'need to be true if ScriptToReportExpirationDate  is not automaticly called'
        , 'default'      => 'FALSE'
        , 'type'         => 'boolean'
        , 'container'    => 'VAR'
        );

$toolConfProperties['linkToChangeDiskQuota']
= array ('label'     => 'external script to change quota allowed to course.'
        ,'default'   => 'changeQuota.php'
        ,'type'      => 'string'
        ,'container' => 'VAR'
        );

$toolConfProperties['urlScriptToReportExpirationDate']
= array ('label'     => 'external script to postpone the expiration of course.'
        ,'default'   => 'postpone.php'
        ,'type'      => 'string'
        ,'container' => 'VAR'
        );


?>
