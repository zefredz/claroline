<?php //$Id$
// TOOL
$conf_def['config_code']='CLFRM';
$conf_def['config_file']='CLFRM.conf.inc.php';
$conf_def['config_name'] = 'Forums tool';

$conf_def['section']['forum']['label']='General settings';
$conf_def['section']['forum']['description']='Settings of the tool';
$conf_def['section']['forum']['properties'] = 
array ( 'allow_html'
      , 'posts_per_page'
      , 'topics_per_page'
      );
      
//PROPERTIES
// Setup forum Options.
$conf_def_property_list['allow_html']
= array ('label'     => 'HTML in posts'
	,'description' => 'Allow user to use html tag in message'
        ,'default'   => '1'
        ,'type'      => 'enum'
        ,'container' => 'VAR'
        ,'display'       => TRUE
        ,'readonly'      => FALSE
        ,'acceptedValue' => array ( '1'=>'Allow'
                                  , '0'=>'Deny'
                                  )
        );

$conf_def_property_list['posts_per_page']
= array ('label'     => 'Maximum of posts per page'
        ,'default'   => '5'
        ,'unit'      => 'posts'
        ,'type'      => 'integer'
        ,'container' => 'VAR'
        ,'acceptedValue' => array ( 'min'=>2
                                  , 'max'=>25
                                  )
        );

$conf_def_property_list['topics_per_page']
= array ('label'     => 'Maximum of topics per page'
        ,'default'   => '5'
        ,'unit'      => 'topics'
        ,'type'      => 'integer'
        ,'container' => 'VAR'
        );

?>
