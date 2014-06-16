<?php // $Id$

// vim: expandtab sw=4 ts=4 sts=4:

/**
 * Html Form library
 *
 * @version     CLarolin 1.12 $Revision$
 * @copyright   (c) 2001-2014, Universite catholique de Louvain (UCL)
 * @author      Claroline Team <info@claroline.net>
 * @author      Frederic Minne <zefredz@claroline.net>
 * @license     http://www.gnu.org/copyleft/gpl.html
 *              GNU GENERAL PUBLIC LICENSE version 2 or later
 * @package     kernel.utils
 */

/**
 * HTML form
 */
class Claro_Html_Form extends Claro_Html_Container
{
    const METHOD_POST = 'post';
    const METHOD_GET = 'get';
    
    protected $action, $method;
    
    protected $csrf = true, $uniqid = false;
    
    protected $submitBlock;

    /**
     * Create a form
     * @param string $action callback url for the form
     * @param string $method method used to call the callback url 
     *  Claro_Html_Form::METHOD_POST or Claro_Html_Form::METHOD_GET
     * @param array $extra other attributes for the form in an associative array
     */
    public function __construct( $action = '', $method = self::METHOD_POST, $extra = array() )
    {
        // action
        
        if ( isset($extra['action']) && ! empty( $extra['action'] ) )
        {
            if ( !empty( $action ) )
            {
                Console::debug('Cannot pass action using extra attributes in html form - {$action} used');
            }
            else
            {
                $action = $extra['action'];
            }
            
            unset( $extra['action'] );
        }
        
        $this->action = empty( $action )
            ? $_SERVER['PHP_SELF']
            : $action
            ;
        
        // method
        
        if ( isset($extra['method']) && ! empty( $extra['method'] ) )
        {
            unset( $extra['method'] );
            Console::debug('Cannot pass method using extra attributes in html form - {$method} used');
        }
            
        if ( ! ( $method == self::METHOD_POST || self::METHOD_GET ) )
        {
           throw new Exception ("Invalid method {$method}");
        }
        
        $this->method = $method;

        parent::__construct('form', $extra );

        $this->setAttribute( 'action', $this->action );
        $this->setAttribute( 'method', $this->method );
        
        // default submit block
        
        $this->submitBlock = new Claro_Form_SubmitBlock();
    }
    
    /**
     * @see Claro_Renderer
     * @return string
     */
    public function render()
    {
        if ( $this->csrf )
        {
            $this->addElement(new Claro_Html_Input_CsrfToken());
        }
        
        if ( $this->uniqid )
        {
            $this->addElement(new Claro_Html_Input_Uniqid());
        }
        
        $this->addElement($this->submitBlock);
        
        return parent::render();
    }
    
    /**
     * Disable cross-site request forgery protection
     * @return \Claro_Html_Form $this
     */
    public function disableCsrfProtection()
    {
        $this->csrf = false;
        
        return $this;
    }
    
    /**
     * Enable protection against multiple submission
     * @return \Claro_Html_Form $this;
     */
    public function protectAgainstMultipleSubmision()
    {
        $this->uniqid = true;
        
        return $this;
    }
    
    /**
     * Add a submit block to the form
     * @param Claro_Form_SubmitBlock $submitBlock
     * @return \Claro_Html_Form $this
     */
    public function setSubmitBlock( Claro_Form_SubmitBlock $submitBlock )
    {
        $this->submitBlock = $submitBlock;
        
        return $this;
    }
}

// helper
/**
 * Helper to create a HTML form compliant with the Claroline UI
 */
class Claro_Form extends Claro_Html_Form
{
    protected $fieldset;
    
    /**
     * @see Claro_Html_Form::__construct()
     * @param string $title title of the main fieldset of the form
     * @param string $action
     * @param string $method
     * @param array $extra
     */
    public function __construct ( $title, $action = '', $method = self::METHOD_POST, $extra = array ( ) )
    {
        // class 
        
        if ( !isset($extra['class']) )
        {
            $extra['class'] = 'claroform';
        }
        else
        {
            $extra['class'] .= ' claroform';
        }
        
        parent::__construct ( $action, $method, $extra );
        
        $this->fieldset = new Claro_Html_Fieldset( new Claro_Html_Legend($title));
        $this->addElement($this->fieldset);
    }
    
    /**
     * Add a row to the form
     * @param string $title
     * @param Claro_Renderer $content
     * @return \Claro_Form $this
     */
    public function addRow( $title, $content )
    {
        $this->fieldset->addRow($title, $content);
        
        return $this;
    }
    
    /**
     * Add a button to the submit block
     * @param Claro_Renderer $button
     * @return \Claro_Form $this
     */
    public function addButton( $button )
    {
        $this->submitBlock->addButton($button);
        
        return $this;
    }
}


/**
 * Element of an HTML form
 */
abstract class Claro_Form_Element extends Claro_Html_Element
{  
    /**
     * @see Claro_Html_Element
     * @param string $elementName
     * @param array $attributes
     * @param boolean $autoClose
     */
    public function __construct( $elementName, $attributes = array(), $autoClose = false )
    {        
        parent::__construct( $elementName, $attributes, $autoClose );
    }
    
    /**
     * Set the name attribute (not element name !) of the form element
     * @param string $name
     * @return \Claro_Form_Element $this
     */
    public function setName( $name )
    {
        $this->setAttribute( 'name', $name );
        
        return $this;
    }
}

/**
 * Submit block containing submit button and other command buttons
 */
class Claro_Form_SubmitBlock extends Claro_Html_DefinitionList
{
    protected $definition, $submit;
    protected $buttons = array();
    protected $submitAtEnd = false;
    
    /**
     * 
     * @param array $attributes extra attributes
     */
    public function __construct ( $attributes = array ( ) )
    {
        $this->submit = new Claro_Html_Input_Submit('submit', get_lang('Submit'));
        $this->definition = new Claro_Html_Definition('');
        parent::__construct ( $attributes );
        $this->addDefinition( new Claro_Html_DefinitionTitle('&nbsp;'), $this->definition );
    }
    
    /**
     * Add a submit button
     * @param Claro_Html_Input_Submit $submit
     * @return \Claro_Form_SubmitBlock $this
     */
    public function setSubmit( Claro_Html_Input_Submit $submit )
    {
        $this->submit = $submit;
        
        return $this;
    }
    
    /**
     * Add a button
     * @param Claro_Renderer $button
     * @return \Claro_Form_SubmitBlock $this
     */
    public function addButton( Claro_Renderer $button )
    {
        $this->buttons[] = $button;
        
        return $this;
    }
    
    /**
     * Put the submit button at the end of the submit block instead of the head
     * @return \Claro_Form_SubmitBlock $this
     */
    public function putSubmitAtEnd()
    {
        $this->submitAtEnd = true;
        
        return $this;
    }
    
    /**
     * 
     * @return string
     */
    public function render()
    {
        if ( ! $this->submitAtEnd )
        {
            $content = $this->submit->render();
            $before = '&nbsp;';
            $after = '';
        }
        else
        {
            $content = '';
            $before = '';
            $after = '&nbsp;';
        }
        
        foreach ( $this->buttons as $button )
        {
            $content .= $before . $button->render() . $after;
        }
        
        if ( $this->submitAtEnd )
        {
            $content .= $this->submit->render();
        }
        
        
        $this->definition->setContent($content);
        
        return parent::render();
    }
}

/**
 * HTML fieldset
 */
class Claro_Html_Fieldset extends Claro_Html_Container
{
    protected $legend, $rows;
    protected $isCollapsible = false, $isCollapsed = false;
    
    /**
     * Create a fieldset
     * @param Claro_Html_Legend $legend legend of the field set
     * @param array $extra other attributes for the fieldset element in an associated array
     */
    public function __construct( Claro_Html_Legend $legend, $extra = array() )
    {
        $this->legend = $legend;
        $this->rows = new Claro_Html_DefinitionList();
        
        parent::__construct( 'fieldset', $extra );
        $this->addElement($legend);
        $this->addElement($this->rows);
    }
    
    /**
     * Add an element to the fieldset
     * @param Claro_Renderer $element element to add to the form
     * @param boolean $newline add a new line after the element
     * @return \Claro_Html_Fieldset $this
     */
    public function addRow( $title, $content )
    {
        if ( $title instanceof Claro_Html_Label && $content instanceof Claro_Html_Element )
        {
            $title->setFor( $content->getId () );
        }
        
        $this->rows->addDefinition( $title, $content );
        
        return $this;
    }
    
    /**
     * Make the fieldset collapsible
     * @return \Claro_Html_Fieldset $this
     */
    public function makeCollapsible()
    {
        if ( ! isset( $this->attributes['class']) )
        {
            $this->attributes['class'] = 'collapsible';
        }
        else
        {
            $this->attributes['class'] .= ' collapsible';
        }
        
        $this->isCollapsible = true;
        
        return $this;
    }
    
    /**
     * Collapse the fieldset by default (and make it collapsible if it's not the case)
     * @return \Claro_Html_Fieldset $this
     */
    public function makeCollapsed()
    {
        if ( ! $this->isCollapsible )
        {
            $this->makeCollapsible();
        }
        
        $this->attributes['class'] .= ' collapsed';
        
        return $this;     
    }
}


/**
 * Legend of a fieldset
 */
class Claro_Html_Legend extends Claro_Html_Element
{
    /**
     * Create a legend element
     * @param string $legend text of the legend
     * @param array $extra other attributes for the fieldset element in an associated array
     */
    public function __construct( $legend, $extra = array() )
    {
        parent::__construct('legend', $extra, false);
        $this->setContent( $legend );
    }
}

/**
 * Label of an element
 */
class Claro_Html_Label extends Claro_Html_Element
{
    protected $label, $for, $extra;
    
    /**
     * Create a label
     * @param string $label text of the label
     * @param string $for id of the element associated with the label
     * @param array $attributes associative array of attributes
     */
    public function __construct( $label, $for = null, $attributes = array() )
    {
        $attributes['for'] = $for;
           
        parent::__construct( 'label', $attributes, false );
        
        $this->setContent( $label );
    }
    
    public function setFor( $for )
    {
        $this->setAttribute('for', $for);
        return $this;
    }
}

class Claro_Html_Input_Generic extends Claro_Form_Element
{
    protected $label = null;
    protected $labelAfter = false;
    
    /**
     * Generic Input element
     * @param string $type type of input element
     * @param string $name name attribute of the html tag of the input element
     * @param string $value value stored in the input element
     * @param array $extra associative array of attributes
     */
    public function __construct( $type, $name, $value = '', $extra = array() )
    {
        if ( ! in_array( $type
            , array( 'text','password','submit','button','image','file','radio','checkbox','hidden') ) )
        {
            throw new Exception ( "Invalid input type {$type}" );
        }
        
        parent::__construct( 'input', $extra, true );
        
        $this->setAttribute( 'type', $type );
        $this->setName( $name );
        $this->setAttribute( 'value', $value );
    }
    
    /**
     * Set the label of the input element
     * @param string|Claro_Html_Label $label
     * @param boolean $after if set to true, the label will be placed after the input instead of before
     * @param array $extra associative array of attributes of the label
     * @return $this
     */
    public function setLabel( $label, $after = false, $extra = array() )
    {
        if ( $label instanceof Claro_Html_Label )
        {
            $this->label = $label;
        }
        else
        {
            $this->label = new Claro_Html_Label( $label, $this->getId(), $extra );
        }
        
        $this->labelAfter = $after ? true : false;
        
        return $this;
    }
    
    /**
     * Disable/ignore the input in form submit
     * @return $this
     */
    public function disable()
    {
        $this->setAttribute( 'disabled', 'disabled' );
        
        return $this;
    }
    
    /**
     * Enable/unignore the input in form submit
     * @return $this
     */
    public function enable()
    {
        $this->unsetAttribute( 'disabled' );
        
        return $this;
    }
    
    // readonly works on text fields only (text,password and textarea
    /**
     * Set the input element as readonly
     * @return $this
     */
    public function readonly()
    {
        if ( ! in_array( $this->attributes['type'], array('text','password') ) )
        {
            throw new Exception ("Only text input could be readonly not for {$this->attributes['type']}");
        }
        
        $this->setAttribute( 'readonly', 'readonly' );
        
        return $this;
    }
    
    /**
     * Set the input element as checked
     * @throws Exception
     * @return $this
     */
    public function checked()
    {
        if ( ! in_array( $this->attributes['type'], array('radio', 'checkbox') ) )
        {
            throw new Exception ("Only radio and checkbox input could be checked not {$this->attributes['type']}");
        }
        
        $this->setAttribute( 'checked', 'checked' );
        
        return $this;
    }
    
    /**
     * @see Claro_Renderer
     * @return string
     */
    public function render()
    {
        $label = is_null( $this->label )  ? ''  : $this->label->render();
        
        return ( $this->labelAfter ? '' : (empty($label) ? '' : $label .'<br />' ) )
            . parent::render()
            . ( $this->labelAfter ? $label : '' )
            ;
    }
}

/**
 * Text input
 */
class Claro_Html_Input_Text extends Claro_Html_Input_Generic
{
    /**
     * Create a text input
     * @param string $name name attribute of the html tag of the input element
     * @param string $value value stored in the input element
     * @param int $size size of the element
     * @param int $maxlength maximum length of the value stored in the element
     * @param array $extra associative array of attributes
     * @param boolean $password if true, create a password input instead of of a text input
     */
    public function __construct( $name, $value = '', $size = '', $maxlength = '', $extra = array(), $password = false )
    {
        $type = $password ? 'password' : 'text';
        
        parent::__construct( $type, $name, $value );
        
        if (!empty($size)) $this->setAttribute( 'size', $size );
        if (!empty($maxlength)) $this->setAttribute( 'maxlength', $maxlength );
        if (is_array($extra)) $this->appendAttributes( $extra );
    }
}

/**
 * Password input
 */
class Claro_Html_Input_Password extends Claro_Html_Input_Text
{
    /**
     * Create a password input
     * @param string $name name attribute of the html tag of the input element
     * @param string $value value stored in the input element
     * @param int $size size of the element
     * @param int $maxlength maximum length of the value stored in the element
     * @param array $extra associative array of attributes
     */
    public function __construct( $name, $value = '', $size = '', $maxlength = '', $extra = array() )
    {
        parent::__construct( $name, $value, $size, $maxlength, $extra, true );
    }
}

/**
 * Submit button
 */
class Claro_Html_Input_Submit extends Claro_Html_Input_Generic
{
    /**
     * Create a submit button
     * @param string $name name attribute of the html tag of the input element
     * @param string $value value stored in the input element
     * @param string $onsubmit javascript callback on form submit
     * @param array $extra associative array of attributes
     */
    public function __construct( $name, $value, $onsubmit = '', $extra = array() )
    {
        parent::__construct( 'submit', $name, $value );
        
        if (!empty($onsubmit)) $this->setAttribute( 'onsubmit', $onsubmit );
        if (is_array($extra)) $this->appendAttributes( $extra );
    }
}

/**
 * Button
 */
class Claro_Html_Input_Button extends Claro_Html_Input_Generic
{
    /**
     * Create a button
     * @param string $name name attribute of the html tag of the input element
     * @param string $value value stored in the input element
     * @param string $onclick javascript callback when the button is clicked
     * @param array $extra associative array of attributes
     */
    public function __construct( $name, $value, $onclick = '', $extra = array() )
    {
        parent::__construct( 'button', $name, $value );
        
        if (!empty($onclick)) $this->setAttribute( 'onclick', $onclick );
        if (is_array($extra)) $this->appendAttributes( $extra );
    }
}

/**
 * Cancel button
 */
class Claro_Html_Input_Cancel extends Claro_Html_Input_Button
{
    protected $location;
    
    /**
     * Create a submit button
     * @param string $name name attribute of the html tag of the input element
     * @param string $value value stored in the input element
     * @param string $location callback url
     * @param array $extra associative array of attributes
     */
    public function __construct( $name, $value, $location = '', $extra = array() )
    {
        $this->location = empty($location)? $_SERVER['PHP_SELF'] : $location;
        $onclick = "window.location='{$this->location}'";
            
        parent::__construct( $name, $value, $onclick, $extra );
    }
    
    /**
     * @see Claro_Renderer
     * @return string
     */
    public function render()
    {
        return "<a href=\"{$this->location}\">"
            . parent::render()
            . "</a>"
            ;
    }
}

/**
 * File selector
 */
class Claro_Html_Input_File extends Claro_Html_Input_Generic
{
    /**
     * Create a file selector input
     * @param string $name name attribute of the html tag of the input element
     * @param string $value value stored in the input element
     * @param array $extra associative array of attributes
     */
    public function __construct( $name, $value = '', $extra = array() )
    {
        parent::__construct( 'file', $name, $value, $extra );
    }
}

/**
 * Input element of type image
 */
class Claro_Html_Input_Image extends Claro_Html_Input_Generic
{
    /**
     * Create an input element of type image
     * @param string $name name attribute of the html tag of the input element
     * @param string $src url of the image
     * @param string $value value stored in the input element
     * @param array $extra associative array of attributes
     */
    public function __construct( $name, $src, $value = '', $extra = array() )
    {
        parent::__construct( 'image', $name, $value );
        
        $this->setAttribute( 'src', $src );
        if (is_array($extra)) $this->appendAttributes( $extra );
    }
}

/**
 * Hidden input element
 */
class Claro_Html_Input_Hidden extends Claro_Html_Input_Generic
{
    /**
     * Create a hidden input element
     * @param string $name name attribute of the html tag of the input element
     * @param string $value value stored in the input element
     * @param array $extra associative array of attributes
     */
    public function __construct( $name, $value, $extra = array() )
    {
        parent::__construct( 'hidden', $name, $value, $extra );
    }
}

/**
 * Radio button
 */
class Claro_Html_Input_Radio extends Claro_Html_Input_Generic
{
    /**
     * Create a radio button
     * @param string $name name attribute of the html tag of the input element
     * @param string $value value stored in the input element
     * @param string $label text of the button
     * @param boolean $checked if set to true the radio button is checked
     * @param array $extra associative array of attributes
     */
    public function __construct( $name, $value, $label = '', $checked = false, $extra = array() )
    {
        parent::__construct( 'radio', $name, $value, $extra );
        
        if ( $checked ) $this->checked();
        
        if ( !empty($label) ) $this->setLabel( $label, true );
    }
}

/**
 * Check box
 */
class Claro_Html_Input_Checkbox extends Claro_Html_Input_Generic
{
    public function __construct( $name, $value, $label = '', $checked = false, $extra = array() )
    {
        parent::__construct( 'checkbox', $name, $value, $extra );
        
        if ( $checked ) $this->checked();
        
        if ( !empty($label) ) $this->setLabel( $label, true );
    }
}

/**
 * Radio button list
 */
class Claro_Html_Input_RadioList extends Claro_Html_Composite
{
    protected $radioList = array();
    protected $name, $checked;
    
    /**
     * Generate a list of radio buttons
     * @param string $name name attribute of the html tag
     * @param array $list array of button text label strings
     * @param string $checked text of the checked button in the previous list
     */
    public function __construct( $name, $list, $checked = '' )
    {
        $this->elementName = $name; 
        $this->checked = $checked;
        
        foreach ( $list as $value => $label )
        {
            $this->addElement ( new Claro_Html_Input_Radio( $name, $value, $label, ($checked==$value) ) );
        }           
    }
}

/**
 * Check box list
 */
class Claro_Html_Input_CheckboxList extends Claro_Html_Composite
{
    protected $checkboxList = array();
    protected $name, $checked;
    
    /**
     * Generate a list of checkboxes
     * @param string $name name attribute of the html tag
     * @param array $list array of checkbox text label strings
     * @param string $checked text of the checked checkbox in the previous list
     */
    public function __construct( $name, $list, $checked = '' )
    {
        $this->elementName = $name; 
        $this->checked = $checked;
        
        foreach ( $list as $value => $label )
        {
            $this->addElement ( new Claro_Html_Input_Checkbox( $name, $value, $label, ($checked==$value) ) );
        }           
    }
}

/**
 * Text area
 */
class Claro_Html_Textarea extends Claro_Form_Element
{
    /**
     * Create a textarea
     * @param string $name name attribute of the html tag of the input element
     * @param string $value value stored in the textarea
     * @param int $rows number of rows
     * @param int $cols number of columns
     * @param array $extra associative array of attributes
     */
    public function __construct( $name, $value, $rows, $cols, $extra = array() )
    {
        parent::__construct( 'textarea', $extra, false );
        
        $this->setName( $name );
        $this->setAttribute( 'rows', $rows );
        $this->setAttribute( 'cols', $cols );        
        $this->setContent( $value );
    }
}

/**
 * Abstract item of a select box
 */
abstract class Claro_Html_SelectBox_Item extends Claro_Html_Element
{
    /**
     * Disable the item
     * @return $this
     */
    public function disable()
    {
        $this->setAttribute( 'disabled', 'disabled' );
        
        return $this;
    }
    
    /**
     * Enable the item
     * @return $this
     */
    public function enable()
    {
        $this->unsetAttribute( 'disabled' );
        
        return $this;
    }
}

/**
 * Option of a selct box
 */
class Claro_Html_SelectBox_Option extends Claro_Html_SelectBox_Item
{
    /**
     * Create an option
     * @param string $label label of the option
     * @param string $value value of the option
     * @param array $extra associative array of attributes
     */
    public function __construct( $label, $value, $extra = array() )
    {
        parent::__construct( 'option', $extra, false );
        
        $this->setAttribute( 'value', $value );
        
        $this->setContent( $label );
    }
    
    /**
     * Set the option as selected
     * @return $this
     */
    public function selected()
    {
        $this->attributes['selected'] = 'selected';
        
        return $this;
    }
}

/**
 * Option group
 */
class Claro_Html_SelectBox_Optgroup extends Claro_Html_SelectBox_Item
{
    protected $elements = array();
    
    /**
     * Create an option group
     * @param string $label label of the option group
     * @param array $extra associative array of attributes
     */
    public function __construct( $label, $extra = array() )
    {
        parent::__construct( 'optgroup', $extra, false );
        
        $this->setAttribute( 'label', $label );
    }
    
    /**
     * Add an option to the group
     * @param Claro_Html_SelectBox_Option $element
     * @return $this
     */
    public function addOption( Claro_Html_SelectBox_Option $element )
    {
        $this->elements[] = $element;
        
        return $this;
    }
    
    /**
     * @see Claro_Renderer
     * @return string
     */
    public function render()
    {
        $this->content = "\n";
        
        foreach ( $this->elements as $element )
        {
            $this->content .= $element->render()."\n";
        }
        
        return parent::render();
    }
}

/**
 * Select box
 */
class Claro_Html_SelectBox extends Claro_Form_Element
{
    protected $elements = array();
    
    /**
     * Create a select box
     * @param string $name name attribute
     * @param array $extra other attributes
     */
    public function __construct( $name, $extra = array() )
    {
        parent::__construct( 'select', $extra, false );
        
        $this->setName( $name );
    }
    
    /**
     * Add an option to the group
     * @param Claro_Html_SelectBox_Option $element
     * @return $this
     */
    public function addOption( Claro_Html_SelectBox_Option $element )
    {
        $this->elements[] = $element;
        
        return $this;
    }
    
    /**
     * @see Claro_Renderer
     * @return string
     */
    public function render()
    {
        $this->content = "\n";
        
        foreach ( $this->elements as $element )
        {
            $this->content .= $element->render()."\n";
        }
        
        return parent::render();
    }
    
    /**
     * Create a select box from an array of option labels and values
     * @param string $name name attribute
     * @param array $optionList array of [value => label]
     * @param string $selectedValue selected value
     * @param array $extra extra attributes associative array
     * @return \Claro_Html_SelectBox
     */
    public static function fromArray( $name, $optionList, $selectedValue, $extra = array() )
    {
        $select = new self( $name, $extra );
        
        foreach ( $optionList as $value => $label )
        {
            $option = new Claro_Html_SelectBox_Option( $label, $value );
            
            if ( $value == $selectedValue ) $option->selected();
            
            $select->addClaro_Html_SelectBox_Option( $option );
        }
        
        return $select;
    }
}

/**
 *  Cross-site request forgery protection token
 */
class Claro_Html_Input_CsrfToken extends Claro_Html_Input_Hidden
{
    public function __construct()
    {
        if ( claro_is_user_authenticated() )
        {
            $token = $_SESSION['csrf_token'];
        }
        else
        {
            $token = '';
        }
        
        parent::__construct( 'csrf_token', $token, array( 'id' => 'csrf_token' ) );
    }
}

/**
 * Protection against multiple form submissions
 */
class Claro_Html_Input_Uniqid extends Claro_Html_Input_Hidden
{
    protected $formId;
    
    public function __construct()
    {
        $this->formId = uniqid('');
        parent::__construct( 'formuniqid', $this->formId, array( 'id' => '_formuniqid' ) );
    }
    
    /**
     * Get the uniqid of the form
     * @return string
     */
    public function getUniqid()
    {
        return $this->formId;
    }
}
