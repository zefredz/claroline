<?php // $Id$
$conf_def['config_file']='chat.conf.inc.php';
$conf_def['config_code']='CLCHT';
$conf_def['config_name']='general setting for chat tool';
$conf_def['description']='Note : these value would be COPY in the script to win an include.';

$conf_def['section']['buffer']['label']='buffer';
$conf_def['section']['buffer']['properties'] = 
array ( 'MAX_LINE_IN_FILE'
      , 'MAX_LINE_TO_DISPLAY'
      , 'REFRESH_DISPLAY_RATE'
      );

      
$conf_def_property_list['MAX_LINE_IN_FILE'] = 
array ( 'description' => 'Max line in the active chat file. '
                        .'For performance reason it is interesting '
                        .'to work with not too big file'
      , 'label'       => 'Max quantity of lines in buffer'
      , 'default'     => '200'
      , 'unit'         => 'lines'
      , 'type'        => 'integer'
      );

$conf_def_property_list['MAX_LINE_TO_DISPLAY'] =
array ( 'description'   => 'Maximum line diplayed to the user screen. ' 
                          .'As the active chat file is regularly shrinked '
                          .'(see max_line_in_file), '
                          .'keeping this parameter smaller than '
                          .'max_line_in_file allows smooth display '
                          .'(where no big line chunk are removed when '
                          .'the excess line from the active chat file are buffered on fly'
      , 'label'         => 'Max Quantity of line on screen'
      , 'default'       => '20'
      , 'acceptedValue' => array( 'min' => 5, 'max' => 120)
      , 'unit'          => 'lines'
      , 'type'          => 'integer'
      );
      
$conf_def_property_list['REFRESH_DISPLAY_RATE'] =
array ( 'description' => 'Time to automaticly refresh  user screen'
      , 'label'       => 'delay in second'
      , 'default'     => '10'
      , 'unit'         => 'second'
      , 'type'        => 'integer'
      );
?>
