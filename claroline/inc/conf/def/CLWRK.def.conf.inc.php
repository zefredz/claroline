<?php //$Id$
// TOOL
$conf_def['config_code'] = 'CLWRK';
$conf_def['config_file'] = 'CLWRK___.conf.php';
$conf_def['config_name'] = 'Quota';
$conf_def['description'] = 'Seeting for the assignment tool.';
$conf_def['section']['storage']['label']      = 'Quota';
$conf_def['section']['storage']['properties'] = 
array ( 'CONFVAL_MAX_FILE_SIZE_PER_WORKS'
      );
//PROPERTIES
$conf_def_property_list['CONFVAL_MAX_FILE_SIZE_PER_WORKS']['label']     = 'Max size for an assignment';
$conf_def_property_list['CONFVAL_MAX_FILE_SIZE_PER_WORKS']['default']   = '200000000';
$conf_def_property_list['CONFVAL_MAX_FILE_SIZE_PER_WORKS']['unit' ]     = 'bytes';
$conf_def_property_list['CONFVAL_MAX_FILE_SIZE_PER_WORKS']['type' ]     = 'integer';
$conf_def_property_list['CONFVAL_MAX_FILE_SIZE_PER_WORKS']['container'] = 'CONST';
?>
