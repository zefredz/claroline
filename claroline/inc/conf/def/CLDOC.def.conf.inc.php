<?php //$Id$
// TOOL
$conf_def['config_code']='CLDOC';
$conf_def['config_file']='CLDOC___.conf.php';
$conf_def['config_name']='general setting for document tool';
// $conf_def['config_repository']=''; dislabed = includePath.'/conf'
$conf_def['description'] = 'Document tool. This is a course tool';
$conf_def['section']['quota']['label']='quota';
$conf_def['section']['quota']['properties'] = 
array ( 'maxFilledSpace_for_course'
      , 'maxFilledSpace_for_groups'
      );
      
//PROPERTIES
$conf_def_property_list['maxFilledSpace_for_course']
= array ('label'     => 'Disk space allowed to each courses for documents'
        ,'default'   => '100000000'
        ,'unit'      => 'bytes'
        ,'type'      => 'integer'
        ,'container' => 'VAR'
        );

$conf_def_property_list['maxFilledSpace_for_groups']
= array ('label'     => 'Disk space allowed to each group'
        ,'default'   => '1000000'
        ,'unit'      => 'bytes'
        ,'type'      => 'integer'
        ,'container' => 'VAR'
        );
?>
