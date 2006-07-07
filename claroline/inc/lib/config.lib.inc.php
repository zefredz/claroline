<?php // $Id$
if ( count( get_included_files() ) == 1 ) die( '---' );
/**
 * CLAROLINE
 *
 * Config lib contain function to manage conf file
 *
 * @version 1.8 $Revision$
 *
 * @copyright 2001-2006 Universite catholique de Louvain (UCL)
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


/**
 * To use this class.
 *
 * Example :
 * $fooConfig = new Config('CLFOO');
 *
 * $fooConfig->load(); Load property with actual values in configs files.
 * $fooConfig->save(); write a new config file if (following def file),
 *                     a property would be in the config file,
 *                     and this property is in memory,
 *                     the value would be write in the new config file)
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

    var $def_loaded;

    /**
     * constructor, build a config object
     *
     * @param string $config_code
     */

    function Config($config_code)
    {
        $this->config_code = $config_code;
        $this->conf_dirname = claro_get_conf_repository();
        $this->def_dirname = claro_get_conf_def_file($config_code) ;
        $this->def_loaded = false;
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

            // set def_loaded var
            $this->def_loaded = true;

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
            if ( isset($property_def['default']) )
            {
                if ( 'boolean' == $property_def['type'] )
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
            $conf_filename = $this->conf_filename;

            include($conf_filename);

            foreach ( $this->conf_def_property_list as $property_name => $property_def )
            {
                if ( isset($property_def['container']) && 'CONST' == $property_def['container'])
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
        $def_filename = $this->def_filename;

        include $def_filename ;

        $this->conf_def = $conf_def;
        $this->conf_def_property_list = $conf_def_property_list;
    }

    /**
     * Build the path and filename of the config file
     *
     * @return string : complete path and name of config file
     */

    function build_conf_filename()
    {
        if ( !empty($this->conf_def['config_file']) )
        {
            // get the name of config file in definition file
            return $this->conf_dirname . '/' . $this->conf_def['config_file'];
        }
        else
        {
            // build the filename with the config_code
            return $this->conf_dirname . $config_code . '.conf.php';
        }
    }

    /**
     * Get the name of configuration in definition
     *
     * @return string name of the current config
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
     *
     * @param string $name value name
     * @return value of the givent property | null if not found
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
     * Set the value of a property with validation.
     *
     * @param string $name value name
     * @param mixed $value content for the property to set
     *
     * @return boolean true on success | false if unvalid or unknow value
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
            $this->error_message('property ' . $name . 'unknow');
            return false;
        }
    }

    /**
     * Validate value of the list of new properties
     *
     * Given property are checked only if defined in def file.
     *
     * Other are ignored and don't fail the validation
     *
     * @param array $newPropertyList array of property => new values
     * @return boolean : true if ALL know value are valid.
     *
     */

    function validate($newPropertyList)
    {
        $valid = true;

        $property_name_list = array_keys($this->conf_def_property_list);

        foreach ( $property_name_list as $name )
        {
            if ( isset($newPropertyList[$name]) )
            {

                if ( $this->validate_property($name,$newPropertyList[$name]) )
                {
                    $this->property_list[$name] = $newPropertyList[$name];
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
     *
     * @param string $name
     * @param string $value
     *
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

        if ( isset($property_def['acceptedValueType']) )
        {
            switch ( $property_def['acceptedValueType'] )
            {
                case 'css':
                    $acceptedValue = $this->retrieve_accepted_values_from_folder($rootSys.'claroline/css','file','.css',array('print.css','rss.css','compatible.css'));
                    break;
                case 'lang':
                    $acceptedValue = $this->retrieve_accepted_values_from_folder($rootSys.'claroline/lang','folder');
                    break;
                case 'auth':
                    $acceptedValue = $this->retrieve_accepted_values_from_folder($rootSys.'claroline/auth/extauth/drivers','file','.php');
                    break;
                case 'editor':
                    $acceptedValue = $this->retrieve_accepted_values_from_folder($rootSys.'claroline/editor','folder');
                    break;
            }
        }

        // validate property
        switch ($type)
        {
            case 'boolean' :
                if ( ! is_bool ($value ) && ! in_array(strtoupper($value), array ('TRUE', 'FALSE','1','0' )))
                {
                    $this->error_message(get_lang('%name should be boolean',array('%name'=>$label)));
                    $valid = false;
                }
                break;

            case 'integer' :
                if ( eregi('[^0-9]',$value) )
                {
                    $this->error_message( get_lang('%name would be integer',array('%name'=>$label)));
                    $valid = false;
                }
                elseif ( isset($acceptedValue['max']) && $value > $acceptedValue['max'] )
                {
                    $this->error_message( get_lang('%name would be integer inferior or equal to %value', array('%name'=>$label,'%value'=>$acceptedValue['max'])) );
                    $valid = false;
                }
                elseif ( isset($acceptedValue['min']) && $value < $acceptedValue['min'] )
                {
                    $this->error_message( get_lang('%name would be integer superior or equal to %value', array('%name'=>$label,'%value'=>$acceptedValue['min'])));
                    $valid = false;
                }
                break;

            case 'enum' :

                if ( isset($acceptedValue) && is_array($acceptedValue) )
                {
                    if ( !in_array($value, array_keys($acceptedValue)) )
                    {
                        $this->error_message( get_lang('%value would be in enum list of %name', array('%value'=>$value,'%name'=>$label)) );
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
                            $this->error_message(get_lang('%value must be in the accepted value list of %name',array('%value' => $item_value, '%name' => $label)) );
                            $valid = false;
                        }
                    }
                }
                else
                {
                    if ( ! empty($value) )
                    {
                        $this->error_message(get_lang('%name must be an array',array('%name' => $label) ));
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
                    $this->error_message( get_lang('%name is required', array('%name' => $label)) );
                    $valid = false;
                }
                break;

            case 'regexp' :
                if ( isset($acceptedValue) && !eregi( $acceptedValue, $value ))
                {
                    $this->error_message( get_lang('%name would be match %regular_expression', array('%name' => $label,'%regular_expression'=> $acceptedValue) ));
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
        if ( false !== ($handle = fopen($this->conf_filename,'w') ) )
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
                        if (is_bool($value)) $valueToWrite = trueFalse($value);
                        else
                        switch (strtoupper($value))
                        {
                            case 'TRUE' :
                            case '1' :
                                $valueToWrite = 'TRUE';
                                break;
                            case 'FALSE' :
                            case '0' :
                                $valueToWrite = 'FALSE';
                                break;
                            default:
                                trigger_error('$value is not a boolean ',E_USER_NOTICE);
                                return false;
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
                    $propertyLine .= '$_conf[\''.$name.'\'] = '. $valueToWrite .';'."\n";
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
        $tbl = claro_sql_get_main_tbl();

        $sql = "SELECT config_hash
                FROM `" . $tbl['config_file'] . "`
                WHERE config_code = '". addslashes($this->config_code) . "'";

        $result = claro_sql_query($sql);

        if ( false !== ($row = mysql_fetch_row($result) ) )
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
     * Function to create the different elements of the configuration form to display
     *
     * @param array $property_list
     * @param string $section_selected
     * @param string $url_params appeded to POST query
     * @return the HTML code to display web form to edit config file
     */
    function display_form($property_list=null,$section_selected=null,$url_params = null)
    {
        $form = '';

        // get section list
        $section_list = $this->get_def_section_list();

        if ( !empty($section_list) )
        {
            if ( empty($section_selected) || ! in_array($section_selected,$section_list) )
            {
                $section_selected = current($section_list);
            }

            // display start form
            $form .= '<form method="POST" action="' . $_SERVER['PHP_SELF'] . '?config_code=' . $this->config_code .htmlspecialchars($url_params). '" name="editConfClass" >' . "\n"
            . '<input type="hidden" name="config_code" value="' . htmlspecialchars($this->config_code) . '" />' . "\n"
            . '<input type="hidden" name="section" value="' . htmlspecialchars($section_selected) . '" />' . "\n"
            . '<input type="hidden" name="cmd" value="save" />' . "\n";

            $form .= '<table class="claroTable"  border="0" cellpadding="5" width="100%">' . "\n";
            if ($section_selected!='viewall') $section_list = $section_list= array($section_selected);

            foreach ($section_list as $thisSection)
            {
                if ($thisSection=='viewall') continue;
                // section array
                $section = $this->conf_def['section'][$thisSection];

                if ($section_selected=='viewall')
                {
                    $form .= '<tr><td colspan="3">' . "\n";
                    $form .= '<ul class="tabTitle">' . "\n";
                    $form .= '<li><a href="#">' . htmlspecialchars($this->conf_def['section'][$thisSection]['label']) . '</a></li>' . "\n";
                    $form .= '</td></tr>' . "\n";

                }
                // display description of the section
                if ( !empty($section['description']) ) $form .= '<tr><td colspan="3"><p class="configSectionDesc" ><em>' . $section['description'] . '</em></p></td></tr>';


                // display each property of the section
                if ( is_array($section['properties']) )
                {

                    foreach ( $section['properties'] as $name )
                    {
                        if ( key_exists($name,$this->conf_def_property_list) )
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
                            $form .= 'Error in $section, ' . $name . ' doesn\'t exist in property list';
                        }
                    } // foreach $section['properties'] as $name
                } // is_array($section['properties'])

            }

            // display submit button
            $form .= '<tr>' ."\n"
            . '<td style="text-align: right">' . get_lang('Save') . '&nbsp;:</td>' . "\n"
            . '<td colspan="2"><input type="submit" value="' . get_lang('Ok') . '" /> '
            . claro_html_button($_SERVER['HTTP_REFERER'], get_lang('Cancel')) . '</td>' . "\n"
            . '</tr>' . "\n";

            // display end form
            $form .= '</table>' . "\n"
            . '</form>' . "\n";

        }

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
                        {
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
                }

                $elt_form .= '</td>' . "\n";

            }
            else
            {

                if ( isset($property_def['acceptedValueType']) )
                {
                    switch ( $property_def['acceptedValueType'] )
                    {
                        case 'css' :
                            $property_def['acceptedValue'] = $this->retrieve_accepted_values_from_folder($rootSys . 'claroline/css','file','.css',array('print.css','rss.css','compatible.css'));
                            break;
                        case 'lang' :
                            $property_def['acceptedValue'] = $this->retrieve_accepted_values_from_folder($rootSys . 'claroline/lang','folder');
                            break;
                        case 'auth':
                            $property_def['acceptedValue'] = $this->retrieve_accepted_values_from_folder($rootSys . 'claroline/auth/extauth/drivers','file','.php');
                            break;
                        case 'editor' :
                            $property_def['acceptedValue'] = $this->retrieve_accepted_values_from_folder($rootSys . 'claroline/editor','folder');
                            break;
                    }
                }

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
            $elt_form .= '<td><em><small>' . $html['description'] . '</small></em></td>';

            $elt_form .= '</tr>' . "\n";

        }

        return $elt_form;
    }

    /**
     * Return list of displayed section
     */

    function get_def_section_list()
    {
        $section_list = array();

        if(!array_key_exists('section',$this->conf_def) || ($this->conf_def['section']))
        {
            $this->conf_def['section']['viewall']['label'] = get_lang('View all');
            $this->conf_def['section']['viewall']['properties'] = array_keys($this->conf_def_property_list);
        }

        foreach ( $this->conf_def['section'] as $id => $section )
        {
            if ( ! isset($section['display']) || $section['display'] != false )
            {
                $section_list[] = $id ;
            }
        }


        return $section_list ;
    }

    /**
     * Display section menu
     */

    function display_section_menu($section_selected,$url_params = null)
    {
        $menu = '';

        $section_list = $this->get_def_section_list();

        if ( !empty($section_list) && count($section_list)>2)
        {
            if ( empty($section_selected) || ! in_array($section_selected,$section_list) )
            {
                $section_selected = current($section_list);
            }

            $menu  = '<div >' . "\n";
            $menu .= '<ul id="navlist">' . "\n";

            foreach ( $section_list as $section )
            if($section != 'viewall')
            {
                $menu .=  '<li>'
                . '<a ' . ( $section == $section_selected ? 'class="current"' : '' )
                . ' href="' . $_SERVER['PHP_SELF'] . '?config_code=' . htmlspecialchars($this->config_code)
                . '&section=' . htmlspecialchars($section) . htmlspecialchars($url_params). '">'
                . htmlspecialchars($this->conf_def['section'][$section]['label']) . '</a></li>' . "\n";
            }
            $menu .= '<li><a class="viewall" href="' . $_SERVER['PHP_SELF'] . '?config_code=' . htmlspecialchars($this->config_code) . '&amp;section=viewall'.htmlspecialchars($url_params).'">' . get_lang('View all') . '</a></li>' . "\n";
            $menu .= '</ul>' . "\n";
            $menu .= '</div>' . "\n" ;
        }
        return $menu;
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
            while ( false !== ($elt = readdir($handle) ) )
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

                        if ( is_array($elt_extension) )
                        {
                            if ( ! in_array(strtolower($ext),$elt_extension) )
                            continue;
                        }
                        elseif ( strtolower($ext) != $elt_extension ) continue;



                    }
                }

                // add elt to array
                $elt_name = $elt;
                $elt_value = $elt;

                $accepted_values[$elt_name] = $elt_value;
            }
            ksort($accepted_values);
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

/**
 * return the path of a def file following the configCode
 *
 * @param string $configCode
 * @return path
 *
 * @todo $centralizedDef won't be hardcoded.
 */

function claro_get_conf_def_file($configCode)
{
    $centralizedDef = array('CLCRS','CLAUTH', 'CLSSO',  'CLCAS', 'CLHOME', 'CLKCACHE','CLLINKER','CLMAIN','CLPROFIL' ,'CLRSS','CLICAL');
    if(in_array($configCode,$centralizedDef)) return realpath($GLOBALS['includePath'] . '/conf/def/') ;
    else                                      return get_module_path($configCode) . '/conf/def/';
}

/**
 * Generate the conf for a given config
 *
 * @param  object $config instance of config to manage.
 * @param  array $properties array of properties to changes
 *
 * @return array list of messages and error tag
 */

function generate_conf(&$config,$properties = null)
{
    // load configuration if not loaded before
    if ( !$config->def_loaded )
    {
        if ( !$config->load() )
        {
            // error loading the configuration
            $message[] = $config->get_error_message();
            return array($message , false);
        }
    }

    $config_code = $config->conf_def['config_code'];
    $config_name = $config->conf_def['config_name'];

    // validate config
    if ( $config->validate($properties) )
    {
        // save config file
        $config->save();
        $message[] = get_lang('Properties for %config_name, (%config_code) are now effective on server.'
        , array('%config_name' => $config_name, '%config_code' => $config_code));
    }
    else
    {
        // no valid
        $error = true ;
        $message = $config->get_error_message();
    }

    if (!empty($error))
    return array ($message, true);
    else
    return array ($message, false);
}


/**
 * Return array list of found definition files
 * @return array list of found definition files
 * @global string includePath use to access to def repository.
 */

function get_def_file_list($type = 'default')
{
    require_once(dirname(__FILE__) . '/module.manage.lib.php');

    //path where we can search defFile : kernel and modules
    // defs of kernel
    if ($type == 'kernel' || $type == 'default')
    $defConfPathList[] = $GLOBALS['includePath'] . '/conf/def';

    // defs of modules
    if ($type == 'module' || $type == 'default')
    {
        $moduleList = get_installed_module_list();
        foreach ($moduleList as $module)
        {
            $possiblePath = get_module_path($module) . '/conf/def';
            if (file_exists($possiblePath)) $defConfPathList[] = $possiblePath;
        }
    }

    $defConfFileList = array();

    foreach ($defConfPathList as $defConfPath)
    {
        if (is_dir($defConfPath) && $handle = opendir($defConfPath))
        {
            // group of def list
            // Browse folder of definition file

            while (FALSE !== ($file = readdir($handle)))
            {

                if ($file != "." && $file != ".." && substr($file, -17)=='.def.conf.inc.php')
                {
                    $config_code = str_replace('.def.conf.inc.php','',$file);
                    $config = new Config($config_code);
                    if($config->load())
                    {
                        $defConfFileList[$config_code]['name'] = $config->get_conf_name($config_code);
                        $defConfFileList[$config_code]['class'] = $config->get_conf_class();
                    }
                }
            }
            closedir($handle);
        }
    }
    return $defConfFileList;
}

?>