<?php // $Id$
$conf_def['config_code']='PLVIDEO';
$conf_def['config_file']='PLVIDEO.conf.inc.php';
$conf_def['config_name']='Video main conf';
// $conf_def['config_repository']=''; Disabled = includePath.'/conf'
$conf_def['section']['image']['label']='Images settings';
$conf_def['section']['image']['properties'] = 
array ( 'image_fond'
      , 'image_swap'
      , 'video_x'
      , 'video_y'
      , 'ext_video'
      );

$conf_def_property_list['image_fond'] = 
array ('label'       => 'Background picture'
      ,'default'     => 'images/video_show.jpg'
      ,'type'        => 'string'
      ,'display'     => TRUE
      ,'readonly'    => FALSE
      ,'technicalInfo' => 'enter here an image to the picture video show'
      );
$conf_def_property_list['image_swap'] = 
array ('label'       => 'Swap picture'
      ,'default'     => 'images/rido.gif'
      ,'type'        => 'string'
      ,'display'     => TRUE
      ,'readonly'    => FALSE
      ,'technicalInfo' => 'enter here an image to the picture video show'
      );
$conf_def_property_list['video_x'] = 
array ('label'       => 'Horizontal size of the video output'
      ,'default'     => '360'
      ,'type'        => 'integer'
      ,'display'     => TRUE
      ,'readonly'    => FALSE
      );
$conf_def_property_list['video_y'] = 
array ('label'       => 'vertical size of the video output'
      ,'default'     => '270'
      ,'type'        => 'integer'
      ,'display'     => TRUE
      ,'readonly'    => FALSE
      );
$conf_def_property_list['ext_video'] = 
array ('label'       => 'undefined properties'
      ,'default'     => ''
      ,'type'        => 'string'
      ,'display'     => TRUE
      ,'readonly'    => FALSE
      );
?>