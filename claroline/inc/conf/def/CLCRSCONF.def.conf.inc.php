<?php // $Id$
/**
 * This file describe the parameter for course administration.
 *
 * @author Christophe Gesché <moosh@claroline.net>
 * @version CLAROLINE 1.6
 * @copyright &copy; 2001-2005 Universite catholique de Louvain (UCL)
 * @license This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
 * as published by the FREE SOFTWARE FOUNDATION. The GPL is available 
 * through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
 * @see http://www.claroline.net/wiki/config_def/
 * @package CLCI
 */


// CONFIG HEADER
$conf_def['config_code']='CLCRSCONF';
$conf_def['config_file']='course_info.conf.php';
$conf_def['config_name']='Manage course settings';
$conf_def['description']='How can be edit a course profile, and managment action';

// CONFIG SECTIONS

$conf_def['section']['links']['label']='Action';
//$conf_def['section']['links']['description']='Links to  commands';
$conf_def['section']['links']['properties'] = 
array ( 'showLinkToDeleteThisCourse'
      , 'showLinkToExportThisCourse'
      , 'showLinkToRestoreCourse'
      );
//PROPERTIES

$conf_def_property_list['showLinkToDeleteThisCourse']
= array ('label'     => 'Show deletion of the course action'
        ,'default'   => 'TRUE'
        ,'type'      => 'boolean'
        ,'container' => 'VAR'
        ,'acceptedValue' => array ('TRUE'  => 'Yes'
                                  ,'FALSE' => 'No'
                                  )
        );
$conf_def_property_list['showLinkToExportThisCourse']
= array ('label'     => 'Show link to make an archive of the cours'
        ,'default'   => 'FALSE'
        ,'display'   => FALSE
        ,'type'      => 'boolean'
        ,'container' => 'VAR'
        ,'acceptedValue' => array ('TRUE'  => 'Yes'
                                  ,'FALSE' => 'No'
                                  )
        );
$conf_def_property_list['showLinkToRestoreCourse']
= array ('label'     => 'Show link to call the restore of a course'
        ,'display'   => FALSE
        ,'default'   => 'FALSE'
        ,'type'      => 'boolean'
        ,'container' => 'VAR'
        ,'acceptedValue' => array ('TRUE'  => 'Yes'
                                  ,'FALSE' => 'No'
                                  )
        );

// If TRUE, these fileds  keep the previous content.
//$canBeEmpty["screenCode"] 	= FALSE;
//$canBeEmpty["int"] 			= FALSE;
//$canBeEmpty["facu"] 		= FALSE;
//$canBeEmpty["description"] 	= TRUE;
//$canBeEmpty["visible"] 		= FALSE;
//$canBeEmpty["titulary"] 	= FALSE;
////$canBeEmpty["lanCourseForm"]= FALSE;
//$canBeEmpty["extLinkName"]	= TRUE;
//$canBeEmpty["extLinkUrl"] 	= TRUE;
//$canBeEmpty["email"]		= TRUE;


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
        ,'default'   => 'TRUE'
        ,'type'      => 'boolean'
        ,'container' => 'VAR'
        ,'acceptedValue' => array ('TRUE'  => 'Yes'
                                  ,'FALSE' => 'No'
                                  )
        );

$conf_def_property_list['showLastEdit']
= array ('label'     => 'Show in course setting the date of last edtion detected in course'
        ,'display'   => FALSE
        ,'default'   => 'TRUE'
        ,'type'      => 'boolean'
        ,'container' => 'VAR'
        ,'acceptedValue' => array ('TRUE'  => 'Yes'
                                  ,'FALSE' => 'No'
                                  )
        );

$conf_def_property_list['showLastVisit']
= array ('label'     => 'Show in course setting the date of last visit in course'
        ,'display'   => FALSE
        ,'default'   => 'TRUE'
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
