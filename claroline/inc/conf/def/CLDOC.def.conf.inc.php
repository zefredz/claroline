<?php //$Id$
// TOOL
$toolConf['config_code']='CLDOC';
$toolConf['config_file']='CLDOC.conf.inc.php';
// $toolConf['config_repository']=''; dislabed = includePath.'/conf'
$toolConf['description'] = 'Document tool. This is a course tool';
$toolConf['section']['quota']['label']='quota';
$toolConf['section']['quota']['properties'] = 
array ( 'maxFilledSpace_for_course'
      , 'maxFilledSpace_for_groups'
      );
      
//PROPERTIES
$toolConfProperties['maxFilledSpace_for_course']
= array ('label'     => 'size (in bytes) allowed to each courses for documents'
        ,'default'   => '100000000'
        ,'unit'      => 'bytes'
        ,'type'      => 'integer'
        ,'container' => 'VAR'
        );

$toolConfProperties['maxFilledSpace_for_groups']
= array ('label'     => 'size (in bytes) allowed to each group'
        ,'default'   => '1000000'
        ,'unit'      => 'bytes'
        ,'type'      => 'integer'
        ,'container' => 'VAR'
        );
?>
