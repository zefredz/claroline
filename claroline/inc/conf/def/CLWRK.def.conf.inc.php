<?php //$Id$
// TOOL
$toolConf['config_code']='CLWRK';
$toolConf['config_file'] = 'work.conf.inc.php';
$toolConf['description'] = 'Assignment tool. this is a course tool';
$toolConf['section']['storage']['label']='Quota';
$toolConf['section']['storage']['properties'] = 
array ( 'CONFVAL_MAX_FILE_SIZE_PER_WORKS'
      );
      
//PROPERTIES
$toolConfProperties['CONFVAL_MAX_FILE_SIZE_PER_WORKS']['label']     = 'max size for an assignment';
$toolConfProperties['CONFVAL_MAX_FILE_SIZE_PER_WORKS']['default']   = '200000000';
$toolConfProperties['CONFVAL_MAX_FILE_SIZE_PER_WORKS']['unit' ]     = 'bytes';
$toolConfProperties['CONFVAL_MAX_FILE_SIZE_PER_WORKS']['type' ]     = 'integer';
$toolConfProperties['CONFVAL_MAX_FILE_SIZE_PER_WORKS']['container'] = 'CONST';
?>
