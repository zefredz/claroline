<?php //$Id$
// TOOL
$conf_def['config_code'] = 'CLHOME';
$conf_def['config_file'] = 'CLHOME.conf.php';
$conf_def['config_name'] = 'Home page';

$conf_def['section']['rightmenu']['label']='Right menu settings';
$conf_def['section']['rightmenu']['description']='Settings of the right menu elements';
$conf_def['section']['rightmenu']['properties'] =
array ( 'max_char_from_content'
      );

//PROPERTIES
$conf_def_property_list['max_char_from_content']
= array ('label'     => 'Last event length'
        ,'description' => 'Max length of the \'last events\' displayed content'
        ,'default'   => '80'
        ,'unit'     => 'characters'
        ,'type'      => 'integer'
        ,'container' => 'CONST'
        ,'display'       => TRUE
        ,'readonly'      => FALSE
        ,'acceptedValue' => array ( 'min'=> '0' )
        );

?>
