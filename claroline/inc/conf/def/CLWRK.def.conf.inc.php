<?php //$Id$
// TOOL
$conf_def['config_code'] = 'CLWRK';
$conf_def['config_file'] = 'CLWRK___.conf.php';
$conf_def['config_name'] = 'Assignments tool';
$conf_def['section']['storage']['label']      = 'Quota';
$conf_def['section']['storage']['properties'] = 
array ( 'CONFVAL_MAX_FILE_SIZE_PER_WORKS'
      );
//PROPERTIES

$conf_def_property_list['CONFVAL_MAX_FILE_SIZE_PER_WORKS'] =
array ('label'         => 'Maximum size for an assignment'
      ,'description'   => ''
      ,'display'       => TRUE
      ,'readonly'      => FALSE
      ,'default'       => '200000000'
      ,'type'          => 'integer'
      ,'unit'          => 'bytes'
      , 'container'     => 'CONST'
      );

?>
