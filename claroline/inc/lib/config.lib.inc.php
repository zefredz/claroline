<?php // $Id$
/**
 * CLAROLINE
 *
 * Config lib contain function to manage conf file
 *
 * @version 1.8 $Revision$
 *
 * @copyright 2001-2005 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @see http://www.claroline.net/wiki/config_def/
 *
 * @package CONFIG
 *
 * @author Claro Team <cvs@claroline.net>
 * @author Christophe Gesché <moosh@claroline.net>
 * @author Mathieu Laurent <laurent@cerdecam.be>
 *
 */

class Config
{
    // config code
    var $config_code;

    // path of the configuration file
    var $config_filename;

    // path of the definition file
    var $def_filename;

    // list of properties and values
    var $property_list = array();

    // definition of configuration file
    var $conf_def = array();

    // dirname of config folder
    var $conf_dirname = '';

    // dirname of def folder
    var $def_dirname = '';

    // list of property definition
    var $conf_def_property_list = array();

    // md5 of the properties
    var $md5;

    // array with error
    var $error = array();

    /**
     * constructor, build a config object
     *
     * @param string $config_code
     */

    function Config($config_code)
    {
        global $includePath;

        $this->config_code = $config_code;
        $this->conf_dirname = realpath($includePath . '/conf/') ;
        $this->def_dirname = realpath($includePath . '/conf/def/') ;
    }

    /**
     * load definition and configuration file
     */

    function load()
    {
        // search config file
        $def_filename = $this->def_dirname . '/' . $this->config_code . '.def.conf.inc.php';

        if ( file_exists($def_filename) )
        {
            // set definition filename
            $this->def_filename = $def_filename;

            // load definition file
            $this->load_def_file();

            // set configuration filename
            $this->conf_filename = $this->build_conf_filename();

            // init list of properties
            $this->init_property_list();

            // init md5
            $this->init_md5();

            return true;
        }
        else
        {
            // error definition file doesn't exist
            return false;
        }
    }

    /**
     * Initialise property list : get default values in definition file and overwrite then with values in configuration file
     */

    function init_property_list()
    {
        $this->property_list = array();

        // get default value from definition file
        foreach ( $this->conf_def_property_list as $property_name => $property_def )
        {
            if ( !empty($property_def['default']) )
            {
                if ( $property_def['type'] == 'boolean' )
                {
                    $this->property_list[$property_name] = trueFalse($property_def['default']);
                }
                else
                {
                    $this->property_list[$property_name] = $property_def['default'];
                }
            }
            else
            {
                $this->property_list[$property_name] = null;
            }
        }

        // get values from configuration file
        if ( file_exists($this->conf_filename) )
        {
            include($this->conf_filename);

            foreach ( $this->conf_def_property_list as $property_name => $property_def )
            {
                if ( isset($property_def['container']) && $property_def['container']=='CONST')
                {
                    if ( defined($property_name) )
                    {
                        $this->property_list[$property_name] = constant($property_name);
                    }
                }
                else
                {
                    if ( isset($$property_name) )
                    {
                        $this->property_list[$property_name] = $$property_name;
                    }
                }
            }
        }
        return $this->property_list;
    }

    /**
     * Read defintion file and set value of $conf_def and $conf_def_property_list
     */

    function load_def_file()
    {
        // get $conf_def and $conf_def_property_list from definition file
        include $this->def_filename;

        $this->conf_def = $conf_def;
        $this->conf_def_property_list = $conf_def_property_list;
    }

    /**
     * Build the path and filename of the config file
     */

    function build_conf_filename()
    {
        if ( !empty($this->conf_def['config_file']) )
        {
            // get the name of config file in definition file
            return $this->conf_dirname.'/'.$this->conf_def['config_file'];
        }
        else
        {
            // build the filename with the config_code
            return $this->conf_dirname.'/'.$config_code.'.conf.php';
        }
    }

    /**
     * Get the name of configuration in definition
     */

    function get_conf_name()
    {
        if ( !empty($this->conf_def['config_name']) )
        {
            // name is the config name
            $name = $this->conf_def['config_name'];
        }
        else
        {
            // name is the config_file name
            $name = $this->config_filename;
        }
        return $name;
    }

    /**
     * Get the value of a property
     */

    function get_property($name)
    {
        if ( isset($this->property_list[$name]) )
        {
            return $this->property_list[$name];
        }
        else
        {
            return null;
        }
    }

    /**
     * Set the value of a property
     */

    function set_property($name,$value)
    {
        if ( isset($this->conf_def_property_list[$name]) )
        {
            if ( validate_property($name,$value) )
            {
                $this->property_list[$name] = $value;
                return true;
            }
            else
            {
                return false;
            }
        }
        else
        {
            $this->error_message('');
            return false;
        }
    }

    /**
     * Validate value of the list of new properties
     */

    function validate($new_property_list)
    {
        $valid = true;

        $property_name_list = array_keys($this->conf_def_property_list);

        foreach ( $property_name_list as $name )
        {
            if ( isset($new_property_list[$name]) )
            {
                if ( $this->validate_property($name,$new_property_list[$name]) )
                {
                    $this->property_list[$name] = $new_property_list[$name];
                }
                else
                {
                    $valid = false;
                }
            }
        }

        return $valid ;
    }

    /**
     * Validate value of a property
     */

    function validate_property($name,$value)
    {
        global $rootSys;

        $valid = true;

        $property_def = $this->conf_def_property_list[$name];

        // init property type
        if ( isset($property_def['type']) ) $type = $property_def['type'];
        else                                $type = null;

        // init property label
        if ( isset($property_def['label']) ) $label = $property_def['label'];
        else                                $label = $name;

        // init property accepted value
        if ( ! empty($property_def['acceptedValue']) ) $acceptedValue = $property_def['acceptedValue'];
        else                                           $acceptedValue = null;

        // validate property
        switch ($type)
        {
            case 'boolean' :
                if ( ! ($value == 'TRUE' || $value == 'FALSE' ) )
                {
                    $this->error_message(sprintf(get_lang('%s would be boolean'),$label));
                    $valid = false;
                }
                break;

            case 'integer' :
                if ( eregi('[^0-9]',$value) )
                {
                    $this->error_message(sprintf(get_lang('%s would be integer'),$label));
                    $valid = false;
                }
                elseif ( isset($acceptedValue['max']) && $value > $acceptedValue['max'] )
                {
                    $this->error_message(sprintf(get_lang('%s would be integer inferior or equal to %s'), $label, $acceptedValue['max']));
                    $valid = false;
                }
                elseif ( isset($acceptedValue['min']) && $value < $acceptedValue['min'] )
                {
                    $this->error_message(sprintf(get_lang('%s would be integer superior or equal to %s'), $label, $acceptedValue['min']));
                    $valid = false;
                }
                break;

            case 'css':
            case 'lang':
            case 'editor':
            case 'enum' :

                if ( $type == 'css' )
                {
                    $acceptedValue = $this->retrieve_accepted_values_from_folder($rootSys.'claroline/css','file','.css',array('print.css','compatible.css'));
                }
                elseif ( $type == 'lang' )
                {
                    $acceptedValue = $this->retrieve_accepted_values_from_folder($rootSys.'claroline/lang','folder');
                }
                elseif ( $type == 'editor' )
                {
                    $acceptedValue = $this->retrieve_accepted_values_from_folder($rootSys.'claroline/editor','folder');
                }

                if ( isset($acceptedValue) && is_array($acceptedValue) )
                {
                    if ( !in_array($value, array_keys($acceptedValue)) )
                    {
                        $this->error_message(sprintf(get_lang('%s would be in enum list'),$label));
                        $valid = false;
                    }
                }
                break;

            case 'multi' :

                if ( is_array($value) )
                {
                    foreach ( $value as $item_value)
                    {
                        if ( !in_array($item_value,array_keys($acceptedValue)) )
                        {
                            $this->error_message(sprintf(get_lang('%s must be in the accepted value list'),$label));
                            $valid = false;
                        }
                    }
                }
                else
                {
                    if ( ! empty($value) )
                    {
                        $this->error_message(sprintf(get_lang('%s must be an array'),$label));
                        $valid = false;
                    }
                }
                break;

            case 'relpath' :
                break;

            case 'syspath' :
            case 'wwwpath' :
                if ( empty($value) )
                {
                    $this->error_message(sprintf(get_lang('%s is required'),$label));
                    $valid = false;
                }
                break;

            case 'regexp' :
                if ( isset($acceptedValue) && !eregi( $acceptedValue, $propValue ))
                {
                    $this->error_message(sprintf(get_lang('%s would be valid for %s'),$label,$acceptedValue));
                    $valid = false;
                }
                break;

            case 'string' :
            default :
                $valid = true;
        }

        return $valid;

    }

    /**
     * Save all properties in config file
     */

    function save($generatorFile=__FILE__)
    {
        global $dateTimeFormatLong;

        // split generation file

        if ( strlen($generatorFile)>50 )
        {
            $generatorFile = str_replace("\\","/",$generatorFile);
            $generatorFile = "\n\t\t".str_replace("/","\n\t\t/",$generatorFile);
        }

        $fileHeader = '<?php '."\n"
                      . '/** '
                      . ' * DONT EDIT THIS FILE - NE MODIFIEZ PAS CE FICHIER '."\n"
                      . ' * -------------------------------------------------'."\n"
                      . ' * Generated by '.$generatorFile.' '."\n"
                      . ' * Date '.claro_disp_localised_date($dateTimeFormatLong)."\n"
                      . ' * -------------------------------------------------'."\n"
                      . ' * DONT EDIT THIS FILE - NE MODIFIEZ PAS CE FICHIER '."\n"
                      . ' **/'."\n\n";

        $fileHeader .=  '// $'.$this->config_code.'GenDate is an internal mark'."\n"
                      . '   $'.$this->config_code.'GenDate = "'.time().'";'."\n\n";


        if ( ! empty($this->conf_def['technicalInfo']) )
        {
            $fileHeader .= '/*' . str_replace('*/', '* /', $this->conf_def['technicalInfo']) . '*/' ;
        }

        // open configuration file
        if ( $handle = fopen($this->conf_filename,'w') )
        {

            // write header
            fwrite($handle,$fileHeader);

            foreach ( $this->property_list as $name => $value )
            {

                // build comment about the property
                $propertyComment = '';

                // comment : add description
                if ( !empty($this->conf_def_property_list[$name]['description']) )
                {
                    $propertyComment .= '/* ' . $name . ' : ' . str_replace("\n",'',$this->conf_def_property_list[$name]['description']) . ' */' . "\n";
                }
                else
                {
                    if ( isset($this->conf_def_property_list[$name]['label']) )
                    {
                        $propertyComment .= '/* '.$name.' : '.str_replace("\n","",$this->conf_def_property_list[$name]['label']).' */'."\n";
                    }

                }

                // comment : add technical info
                if ( !empty($this->conf_def_property_list[$name]['technicalInfo']) )
                {
                    $propertyComment .= '/*'."\n"
                                      . str_replace('*/', '* /', $this->conf_def_property_list[$name]['technicalInfo'] )."\n"
                                      . '*/'."\n";
                }

                // property type define how to write the value
                switch ($this->conf_def_property_list[$name]['type'])
                {
                    case 'boolean':
                        if ( is_bool($value) )
                        {
                            $valueToWrite = trueFalse($value);
                        }
                        else
                        {
                            $valueToWrite = $value;
                        }
                        break;
                    case 'integer':
                        $valueToWrite = $value;
                        break;
                    case 'multi':
                        $valueToWrite = 'array(';
                        if ( !empty($value) && is_array($value) ) $valueToWrite .= '\''. implode('\',\'',$value) . '\'';
                        $valueToWrite .= ')';
                        break;
                    default:
                        $valueToWrite = "'". str_replace("'","\'",$value) . "'";
                        break;
                }

                // container : Constance or variable
                if ( isset($this->conf_def_property_list[$name]['container'])
                    && strtoupper($this->conf_def_property_list[$name]['container']) == 'CONST' )
                {
                    $propertyLine = 'if (!defined("'.$name.'")) define("'.$name.'",'.$valueToWrite.');'."\n";
                }
                else
                {
                    $propertyLine = '$'.$name.' = '. $valueToWrite .';'."\n";
                }
                $propertyLine .= "\n\n";

                fwrite($handle,$propertyComment);
                fwrite($handle,$propertyLine);

            }
            fwrite($handle,"\n".'?>');
            fclose($handle);

            // save the new md5 value
            $this->save_md5();

            return true;
        }
        else
        {
            $this->error_message('');
            return false;
        }
    }

    /**
     * Get the property list of the config
     */

    function get_property_list()
    {
        return $this->property_list;
    }

    /**
     * Init the value of md5
     */

    function init_md5()
    {
        $tbl_mdb_names   = claro_sql_get_main_tbl();
        $tbl_config_file = $tbl_mdb_names['config_file'];

        $sql = 'SELECT `config_hash` `config_hash`
                FROM `'.$tbl_config_file.'`
                WHERE `config_code` = "'. addslashes($this->config_code) .'"';

        $result = claro_sql_query($sql);

        if ( $row = mysql_fetch_row($result) )
        {
            // return hash value
            $this->md5 = $row[0];
        }
        else
        {
            // no hash value in db
            $this->md5 = '';
        }
        return $this->md5;
    }

    /**
     * Get the md5 value
     */

    function get_md5()
    {
        return $this->md5;
    }

    /**
     * Calculate the md5 value of the config file
     */

    function calculate_md5()
    {
        return md5_file($this->conf_filename);
    }

    /**
     * Save md5 in database and re-initialise the value of md5
     */

    function save_md5()
    {
        // get table name of config file
        $mainTbl = claro_sql_get_main_tbl();
        $tbl_config_file = $mainTbl['config_file'];

        // caculate new md5
        $new_md5 = $this->calculate_md5();

        if ( empty($this->md5) )
        {
            // insert md5 in database
            $sql = "INSERT IGNORE INTO `" . $tbl_config_file  . "`
                    SET config_hash = '" . addslashes($new_md5) . "',
                        config_code = '" . addslashes($this->config_code) . "'";
        }
        else
        {
            // update md5 in database
            $sql = "UPDATE `" . $tbl_config_file  . "`
                    SET config_hash = '" . addslashes($new_md5) . "'
                    WHERE config_code = '" . addslashes($this->config_code) . "'" ;
        }

        // execute sql query
        if ( claro_sql_query($sql) )
        {
            $this->md5 = $new_md5;
            return true;
        }
        else
        {
            $this->error_message('');
            return false;
        }
    }

    /**
     * Verify if config file is manually updated
     */

    function is_modified()
    {
        $current_md5 = '';

        if ( file_exists($this->conf_filename) )
        {
            $current_md5 = $this->calculate_md5();
        }

        if ( $current_md5 != $this->md5 )
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * Display the web form to edit config file
     */

    function display_form($property_list=null)
    {
        $form = '';

        // display description of configuration
        if ( !empty($conf_def['description']) )
        {
            $form .= '<p>' . $this->conf_def['description'] . '</p>' . "\n";
        }

        // display start form
        $form .= '<form method="POST" action="' . $_SERVER['PHP_SELF'] . '?config_code=' . $this->config_code . '" name="editConfClass" >' . "\n"
               . '<input type="hidden" name="config_code" value="' . $this->config_code . '" />' . "\n"
               . '<input type="hidden" name="cmd" value="save" />' . "\n";

        $form .= '<table class="claroTable"  border="0" cellpadding="5" width="100%">' . "\n";

        // display each section of properties
        foreach ($this->conf_def['section'] as $section)
        {
            if ( ! isset($section['display']) || $section['display'] != false )
            {
                // display fieldset with the label of the section
                $form .= '<tr>'
                       . '<th class="superHeader" colspan="3">' . $section['label'] . '</th>'
                       . '</tr>' . "\n";

                // display description of the section
                if ( !empty($section['description']) )
                {
                    $form .= '<tr><th class="headerX" colspan="3">' . $section['description'] . '</th></tr>' . "\n";
                }
                else
                {
                    $form .= '<tr><th class="headerX" colspan="3">&nbsp;</th></tr>' . "\n";
                }
            }

            if ( is_array($section['properties']) )
            {
                // display each property of the section
                foreach ( $section['properties'] as $name )
                {
                    if (key_exists($name,$this->conf_def_property_list))
                    {
                        if ( is_array($this->conf_def_property_list[$name]) )
                        {
                            if ( isset($property_list[$name]) )
                            {
                                // display elt with new content
                                $form .= $this->display_form_elt($name,$property_list[$name]);
                            }
                            else
                            {
                                // display elt with current content
                                $form .= $this->display_form_elt($name,$this->property_list[$name]);
                            }
                        }
                    }
                    else
                    {
                        die('error in $section, ' . $name . ' doesn\'t exist in property list');
                    }
                }
            }
        }

        // display end form

        $form .= '<tr>' ."\n"
               . '<td style="text-align: right">' . get_lang('Save') . '&nbsp;:</td>' . "\n"
               . '<td colspan="2"><input type="submit" value="' . get_lang('Ok') . '" /> '
               . claro_disp_button($_SERVER['HTTP_REFERER'], get_lang('Cancel')) . '</td>' . "\n"
               . '</tr>' . "\n";

        $form .= '</table>' . "\n"
               . '</form>' . "\n";

        return $form ;
    }

    /**
     * Display the form elt of a property
     */

    function display_form_elt($name,$value)
    {
        global $rootSys;

        $elt_form = '';

        // array with html-safe variable
        $html = array();

        $property_def = $this->conf_def_property_list[$name];

        // convert boolean value to true or false string
        if ( is_bool($value) )
        {
            $value = $value?'TRUE':'FALSE';
        }

        // property type
        $type = !empty($property_def['type'])?$property_def['type']:'string';

        // form name of property
        $input_name = 'property['.$name.']';

        // label of property
        $html['label'] = !empty($property_def['label'])?htmlspecialchars($property_def['label']):htmlspecialchars($name);

        // value of property
        if ( ! is_array($value) ) $html['value'] = htmlspecialchars($value);

        // description of property
        $html['description'] = !empty($property_def['description'])?nl2br(htmlspecialchars($property_def['description'])):'';

        // unit of property
        $html['unit'] = !empty($property_def['unit'])?htmlspecialchars($property_def['unit']):'';

        // type of property
        $html['type'] = !empty($property_def['type'])?' <small>('.htmlspecialchars($property_def['type']).')</small>':'';

        // evaluate the size of input box
        if(!is_array($value))
        {
            $input_size = (int) strlen($value);
            $input_size = min(150,2+(($input_size > 50)?50:(($input_size < 15)?15:$input_size)));
        }

        // build element form

        if ( isset($property_def['display']) && $property_def['display'] == false )
        {
            // no display, do nothing
        }
        else
        {
            $elt_form .= '<tr style="vertical-align: top">' . "\n" ;

            if ( isset($property_def['readonly']) && $property_def['readonly'] )
            {
                // read only display

                // display property label
                $elt_form .= '<td style="text-align: right" width="250">' . $html['label'] . '&nbsp;:</td>' . "\n";

                // display property value
                $elt_form .= '<td nowrap="nowrap">' . "\n";

                switch ( $type )
                {
                    case 'boolean' :
                    case 'enum' :
                        if ( isset($property_def['acceptedValue'][$value]) )
                        {
                            $elt_form .= $property_def['acceptedValue'][$value];
                        }
                        else
                        {
                            $elt_form .= $html['value'];
                        }
                        break;
                    case 'multi' :
                        if ( empty($value) || ! is_array($value) )
                        {
                            $elt_form .= get_lang('Empty');
                        }
                        else
                        {
                            $value_list = array();;
                            foreach ( $value as $value_item )
                            {
                                $value_list[] = htmlspecialchars($property_def['acceptedValue'][$value_item]);
                            }
                            $elt_form .= implode(', ',$value_list);
                        }
                        break;
                    case 'integer' :
                    case 'string' :
                    default :
                        // probably a string or integer
                        if ( empty($html['value']) )
                        {
                            $elt_form .= get_lang('Empty');
                        }
                        else
                        {
                            $elt_form .= $html['value'];
                        }
                }

                $elt_form .= '</td>' . "\n";

            }
            else
            {
                // display property form element

                switch( $type )
                {
                    case 'boolean' :

                    // display label
                    $elt_form .= '<td style="text-align: right" width="250">' . $html['label'] . '&nbsp;:</td>';

                    // display true/false radio button
                    $elt_form .= '<td nowrap="nowrap">'
                        . '<input id="'. $name .'_TRUE"  type="radio" name="'. $input_name.'" value="TRUE"  '
                        . ( $value=='TRUE'?' checked="checked" ':' ') . ' >'
                        . '<label for="'. $name .'_TRUE"  >'
                        . ($property_def['acceptedValue']['TRUE']?$property_def['acceptedValue']['TRUE' ]:'TRUE')
                        . '</label>'
                        . '<br />'
                        . '<input id="'. $name .'_FALSE" type="radio" name="'. $input_name.'" value="FALSE" '
                        . ($value=='FALSE'?' checked="checked" ': ' ') . ' >'
                        . '<label for="'. $name.'_FALSE" >'
                        . ($property_def['acceptedValue']['FALSE']?$property_def['acceptedValue']['FALSE']:'FALSE')
                        . '</label>'
                        . '</td>' ;
                    break;

                    case 'css':
                    case 'lang':
                    case 'editor':

                        if ( $type == 'css' )
                        {
                            $property_def['acceptedValue'] = $this->retrieve_accepted_values_from_folder($rootSys.'claroline/css','file','.css',array('print.css','compatible.css'));
                        }
                        elseif ( $type == 'lang' )
                        {
                            $property_def['acceptedValue'] = $this->retrieve_accepted_values_from_folder($rootSys.'claroline/lang','folder');
                        }
                        elseif ( $type == 'editor' )
                        {
                            $property_def['acceptedValue'] = $this->retrieve_accepted_values_from_folder($rootSys.'claroline/editor','folder');
                        }

                        // no break, go to enum case

                    case 'enum' :

                        $total_accepted_value = count($property_def['acceptedValue']);

                        if ( $total_accepted_value == 0 || $total_accepted_value == 1 )
                        {
                            $elt_form .= '<td style="text-align: right" width="250">' . $html['label'] . '&nbsp;:</td>' . "\n"
                                . '<td nowrap="nowrap">' ;
                            
                            if ( $total_accepted_value == 0 ) 
                            {
                                    $elt_form .= get_lang('Empty');
                            }
                            else
                            {
                                if ( isset($property_def['acceptedValue'][$value]) )
                                {
                                    $elt_form .= $property_def['acceptedValue'][$value];
                                }
                                else
                                {
                                    $elt_form .= $html['value'];
                                }
                            }
                            $elt_form .= '</td>' . "\n";

                        }
                        elseif ( $total_accepted_value == 2 )
                        {
                            $elt_form .= '<td style="text-align: right" width="250">' . $html['label'] . '&nbsp;:</td>'
                                . '<td nowrap="nowrap">' . "\n";

                            foreach ( $property_def['acceptedValue'] as  $keyVal => $labelVal)
                            {
                                $elt_form .= '<input id="'.$name.'_'.$keyVal.'"  type="radio" name="'.$input_name.'" value="'.$keyVal.'"  '
                                    . ($value==$keyVal?' checked="checked" ':' ').' >'
                                    . '<label for="'.$name.'_'.$keyVal.'"  >'.($labelVal?$labelVal:$keyVal ).'</label>'
                                    . '<span class="propUnit">'.$html['unit'].'</span>'
                                    . '<br />'."\n";
                            }
                            $elt_form .= '</td>';
                        }
                        elseif ( $total_accepted_value > 2 )
                        {
                            // display label
                            $elt_form .= '<td style="text-align: right" width="250"><label for="'.$name.'"  >'.$html['label'].'&nbsp;:</label></td>' ;

                            // display select box with accepted value
                            $elt_form .= '<td nowrap="nowrap">' . "\n"
                                . '<select id="' . $name . '" name="'.$input_name.'">' . "\n";

                            foreach ( $property_def['acceptedValue'] as  $keyVal => $labelVal )
                            {
                                if ( $keyVal == $value )
                                {
                                    $elt_form .= '<option value="'. htmlspecialchars($keyVal) .'" selected="selected">' . ($labelVal?$labelVal:$keyVal ). $html['unit'] .'</option>' . "\n";
                                }
                                else
                                {
                                    $elt_form .= '<option value="'. htmlspecialchars($keyVal) .'">' . ($labelVal?$labelVal:$keyVal ). $html['unit'] .'</option>' . "\n";
                                }
                            } // end foreach

                            $elt_form .= '</select></td>' . "\n";
                        }

                        break;

                    case 'multi' :

                        $elt_form .= '<td style="text-align: right" width="250">' . $html['label'] . '&nbsp;:</td>'
                            . '<td nowrap="nowrap">' . "\n";
                        
                        $elt_form .= '<input type="hidden" name="'.$input_name.'" value="">' . "\n";

                        foreach ( $property_def['acceptedValue'] as  $keyVal => $labelVal)
                        {
                            $elt_form .= '<input id="'.$name.'_'.$keyVal.'"  type="checkbox" name="'.$input_name.'[]" value="'.$keyVal.'"  '
                                . (is_array($value)&&in_array($keyVal,$value)?' checked="checked" ':' ').' >'
                                . '<label for="'.$name.'_'.$keyVal.'"  >'.($labelVal?$labelVal:$keyVal ).'</label>'
                                . '<span class="propUnit">'.$html['unit'].'</span>'
                                . '<br />'."\n";
                        }
                        $elt_form .= '</td>';

                        break;

                    case 'integer' :

                        $elt_form .= '<td style="text-align: right" width="250"><label for="'.$name.'"  >'.$html['label'].'&nbsp;:</label></td>'
                            . '<td nowrap="nowrap">' . "\n"
                            . '<input size="'.$input_size.'" align="right" id="'.$name.'" type="text" name="'.$input_name.'" value="'. $html['value'] .'"> '."\n"
                            . '<span class="propUnit">'.$html['unit'].'</span>'
                            . '<span class="propType">'.$html['type'].'</span>'
                            . '</td>';

                        break;

                    default:
                        // by default is a string
                        $elt_form .= '<td style="text-align: right" width="250"><label for="'.$name.'"  >' . $html['label'] . '&nbsp;:</label></td>'
                            . '<td nowrap="nowrap">' . "\n"
                            . '<input size="'.$input_size.'" id="'.$name.'" type="text" name="'.$input_name.'" value="'.$html['value'].'"> '
                            . '<span class="propUnit">'.$html['unit'].'</span>'
                            . '<span class="propType">'.$html['type'].'</span>'. "\n"
                            . '</td>';

                } // end switch on property type
            }

            // display description
            $elt_form .= '<td><em>' . $html['description'] . '</td>';

            $elt_form .= '</tr>' . "\n";

        }

        return $elt_form;
    }

    /**
     * Retrieve accepted value from a folder (ie : lang folder, css, folder, ...)
     */

    function retrieve_accepted_values_from_folder($path,$elt_type,$elt_extension=null,$elt_disallowed=null)
    {
        // init accepted_values list
        $accepted_values = array();

        $dirname = realpath($path) . '/' ;

        if ( is_dir($dirname) )
        {
            $handle = opendir($dirname);
            while ( $elt = readdir($handle) )
            {
                // skip '.', '..' and 'CVS'
                if ( $elt == '.' || $elt == '..' || $elt == 'CVS' ) continue;

                // skip disallowed elt
                if ( !empty($elt_disallowed) && in_array($elt,$elt_disallowed) ) continue;

                if ( $elt_type == 'folder' )
                {
                    // skip no folder
                    if ( ! is_dir($dirname.$elt) ) continue ;
                }

                if ( $elt_type == 'file' )
                {
                    // skip no file
                    if ( ! is_file($dirname.$elt) ) continue;

                    if ( isset($elt_extension) )
                    {
                        // skip file with wrong extension
                        $ext = strrchr($elt, '.');
                        if ( is_array($elt_extension) && ! in_array(strtolower($ext),$elt_extension) ) continue;
                        elseif ( strtolower($ext) != $elt_extension ) continue;
                    }
                }

                // add elt to array
                $elt_name = $elt;
                $elt_value = $elt;

                $accepted_values[$elt_name] = $elt_value;
            }
            return $accepted_values;
        }
        else
        {
            $this->error_message('');
            return false;
        }

    }

    function error_message ($message)
    {
        $this->error[] = $message ;
    }

    function get_error_message ()
    {
        return $this->error;
    }

    /**
     * Return the name (public label) of the config class
     */

    function get_conf_class()
    {
        $class = isset($this->conf_def['config_class'])
        ? strtolower($this->conf_def['config_class'])
        : 'other';

        return $class;
    }

}

/**
 * Proceed to rename conf.php.dist file in unexisting .conf.php files
 *
 * @param string $file syspath:complete path to .dist file
 *
 * @return boolean whether succes return true
 *
 * @author Mathieu Laurent <laurent@cerdecam.be>
 *
 */

function claro_undist_file ($file)
{
    if ( !file_exists($file))
    {
        if ( file_exists($file.".dist"))
        {
            /**
             * @var $perms file permission of dist file are keep to set perms of new file
             */

            $perms = fileperms($file.".dist");

            /**
             * @var $group internal var for affect same group to new file
             */

            $group = filegroup($file.".dist");

            // $perms|bindec(110000) <- preserve perms but force rw right on group
            @copy($file.".dist",$file) && chmod ($file,$perms|bindec(110000)) && @chgrp($file,$group);
            if (file_exists($file))
            {
                return TRUE;
            }
            else
            {
                return FALSE;
            }
        }
        else
        {
            return FALSE;
        }
    }
    else
    {
        return TRUE;
    }
}

/**
 * The boolean value as string
 *
 * @param $booleanState boolean
 *
 * @return string boolean value as string
 *
 */

function trueFalse($booleanState)
{
    return ($booleanState?'TRUE':'FALSE');
}

/**
 * redefine  unexisting function (compatibility for older php)
 */

if (!function_exists('md5_file'))
{
    function md5_file($file_name)
    {
        $fileContent = file($file_name);
        $fileContent = !$fileContent ? FALSE : implode('', $fileContent);
        return md5($fileContent);
    }
}

// TODO : rewrite this code :

/**
 * return array list of found definition files
 * @return array list of found definition files
 * @global string includePath use to access to def repository.
 */

function get_def_file_list()
{
    global $includePath ;

    $defConfFileList = array();
    if (is_dir($includePath . '/conf/def') && $handle = opendir($includePath . '/conf/def'))
    {
        // group of def list

        // Browse folder of definition file

        while (FALSE !== ($file = readdir($handle)))
        {

            if ($file != "." && $file != ".." && substr($file, -17)=='.def.conf.inc.php')
            {
                $config_code = str_replace('.def.conf.inc.php','',$file);
                $config = new Config($config_code);
                $config->load();
                $defConfFileList[$config_code]['name'] = $config->get_conf_name($config_code);
                $defConfFileList[$config_code]['class'] = $config->get_conf_class();
            }
        }
        closedir($handle);
        return $defConfFileList;
    }
    else
    {
        return FALSE;
    }

}


?>
