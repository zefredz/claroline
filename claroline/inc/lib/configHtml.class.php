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

require_once dirname(__FILE__) . '/config.class.php';

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

class ConfigHtml extends Config
{

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

}

?>
