<?php //$Id$
// TOOL
$conf_def['config_code']='CLFRM';
$conf_def['config_file']='CLFRM.conf.inc.php';
$conf_def['config_name']='general setting for Forum';
$conf_def['description'] = 'Forum tool. This is a course tool';

$conf_def['section']['forum']['label']='General setting for forum';
$conf_def['section']['forum']['properties'] = 
array ( 'allow_html'
      , 'allow_bbcode'
      , 'allow_sig'
      , 'allow_namechange'
      , 'posts_per_page'
      , 'hot_threshold'
      , 'topics_per_page'
      , 'override_user_themes'
      , 'email_sig'
      , 'email_from'
      , 'default_lang'
      );

$conf_def['section']['pmsg']['label']='private messages';
$conf_def['section']['pmsg']['properties'] = 
array ( 'allow_pmsg_bbcode'
      , 'allow_pmsg_html'
      );


      
//PROPERTIES
// Setup forum Options.
$conf_def_property_list['allow_html']
= array ('label'     => 'html in posts'
        ,'default'   => '1'
        ,'type'      => 'enum'
        ,'container' => 'VAR'
        ,'display'       => TRUE
        ,'readonly'      => FALSE
        ,'acceptedValue' => array ( '1'=>'allow'
                                  , '0'=>'deny'
                                  )
        );

$conf_def_property_list['allow_bbcode']
= array ('label'     => 'bbcode in posts'
        ,'default'   => '1'
        ,'type'      => 'enum'
        ,'container' => 'VAR'
        ,'display'       => TRUE
        ,'readonly'      => TRUE
        ,'acceptedValue' => array ( '1'=>'allow'
                                  , '0'=>'deny'
                                  )
        );

$conf_def_property_list['allow_sig']
= array ('label'     => 'sign in posts'
        ,'default'   => '1'
        ,'type'      => 'enum'
        ,'container' => 'VAR'
        ,'display'       => TRUE
        ,'readonly'      => TRUE
        ,'acceptedValue' => array ( '1'=>'allow'
                                  , '0'=>'deny'
                                  )
        );

$conf_def_property_list['allow_namechange']
= array ('label'     => 'change author name'
        ,'default'   => '0'
        ,'type'      => 'enum'
        ,'container' => 'VAR'
        ,'display'       => TRUE
        ,'readonly'      => TRUE
        ,'acceptedValue' => array ( '1'=>'allow'
                                  , '0'=>'deny'
                                  )
        );

$conf_def_property_list['override_user_themes']
= array ('label'     => 'user can choose is own theme'
        ,'default'   => '0'
        ,'type'      => 'enum'
        ,'container' => 'VAR'
        ,'display'       => TRUE
        ,'readonly'      => TRUE
        ,'acceptedValue' => array ( '0'=>'allow'
                                  , '1'=>'deny'
                                  )
        );

$sitename             = '';

$conf_def_property_list['posts_per_page']
= array ('label'     => 'Max qty of posts per page'
        ,'default'   => '5'
        ,'unit'      => 'posts'
        ,'type'      => 'integer'
        ,'container' => 'VAR'
        ,'acceptedValue' => array ( 'min'=>2
                                  , 'max'=>25
                                  )
        );

$conf_def_property_list['hot_threshold']
= array ('label'     => 'Trigger qty of posts to mark as hot topic'
        ,'default'   => '15'
        ,'unit'      => 'posts'
        ,'type'      => 'integer'
        ,'container' => 'VAR'
        );

$conf_def_property_list['topics_per_page']
= array ('label'     => 'Max qty of topics per page'
        ,'default'   => '5'
        ,'unit'      => 'topics'
        ,'type'      => 'integer'
        ,'container' => 'VAR'
        );

$conf_def_property_list['email_sig'] = 
array ('label'       => 'signature of emails'
      ,'default'     => 'Yours sincerely, your professor'
      ,'type'        => 'string'
      );
      
$conf_def_property_list['email_from'] = 
array ('label'       => 'email from'
      ,'default'     => 'Course'
      ,'type'        => 'string'
      );
      
$conf_def_property_list['default_lang'] = 
array ( 'label'       => 'default language'
      , 'default'     => 'english'
      , 'type'        => 'lang'
      , 'display'     => FALSE
      , 'readonly'    => TRUE
      );

// PRIVATE MESSAGES
$conf_def_property_list['allow_pmsg_html']
= array ('label'     => 'html in private message'
        ,'default'   => '0'
        ,'type'      => 'enum'
        ,'container' => 'VAR'
        ,'display'       => TRUE
        ,'readonly'      => TRUE
        ,'acceptedValue' => array ( '1'=>'allow'
                                  , '0'=>'deny'
                                  )
        );

$conf_def_property_list['allow_pmsg_bbcode']
= array ('label'     => 'bbcode in private message'
        ,'default'   => '1'
        ,'type'      => 'enum'
        ,'container' => 'VAR'
        ,'display'       => TRUE
        ,'readonly'      => TRUE
        ,'acceptedValue' => array ( '1'=>'allow'
                                  , '0'=>'deny'
                                  )
        );

?>