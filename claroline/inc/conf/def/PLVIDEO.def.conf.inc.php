<?php // $Id$
$toolConf['label']='PLVIDEO';
$toolConf['config_file']='PLVIDEO.conf.inc.php';
// $toolConf['config_repository']=''; dislabed = includePath.'/conf'
$toolConf['section']['image']['label']='Images settings';
$toolConf['section']['image']['properties'] = 
array ( 'image_fond'
      , 'image_swap'
      , 'video_x'
      , 'video_y'
      , 'ext_video'
      );

$toolConfProperties['image_fond'] = 
array ('label'       => 'Background picture'
      ,'default'     => 'images/video_show.jpg'
      ,'type'        => 'string'
      ,'display'     => TRUE
      ,'readonly'    => FALSE
      ,'technicalInfo' => 'enter here an image to the picture video show'
      );
$toolConfProperties['image_swap'] = 
array ('label'       => 'Swap picture'
      ,'default'     => 'images/rido.gif'
      ,'type'        => 'string'
      ,'display'     => TRUE
      ,'readonly'    => FALSE
      ,'technicalInfo' => 'enter here an image to the picture video show'
      );
$toolConfProperties['video_x'] = 
array ('label'       => 'horizontal size of the video output'
      ,'default'     => '360'
      ,'type'        => 'integer'
      ,'display'     => TRUE
      ,'readonly'    => FALSE
      );
$toolConfProperties['video_y'] = 
array ('label'       => 'vertical size of the video output'
      ,'default'     => '270'
      ,'type'        => 'integer'
      ,'display'     => TRUE
      ,'readonly'    => FALSE
      );
$toolConfProperties['ext_video'] = 
array ('label'       => 'undefined properties'
      ,'default'     => ''
      ,'type'        => 'string'
      ,'display'     => TRUE
      ,'readonly'    => FALSE
      );
?>
