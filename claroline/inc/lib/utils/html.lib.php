<?php // $Id$

// vim: expandtab sw=4 ts=4 sts=4:

/**
 * Html library
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
 * Generic renderer interface
 * This is now an alias of display
 */
interface Claro_Renderer extends Display
{
    /**
     * @return  string
     */
    // public function render();
}

/**
 * Generic HTML Element class
 */
class Claro_Html_Element implements Claro_Renderer
{
    protected static $ids = array();
    
    protected $autoClose;
    protected $name;
    protected $attributes;
    protected $content;
    
    /**
     * @param   string $elementName html element name ('input', 'p'...)
     * @param   array $attributes associative array of attributes
     * @param   bool $autoClose set to true for an autoclosed element 
     *  (&lt;input /&gt;, &lt;img /&gt;...), default false
     */
    public function __construct( $elementName, $attributes = array(), $autoClose = false )
    {
        if ( !is_array( $attributes ) || empty( $attributes ) )
        {
            $attributes = array();
        }
        
        if ( array_key_exists( 'id', $attributes ) )
        {
            $this->setId($attributes['id']);
        }
        
        $this->elementName = $elementName;
        $this->attributes = $attributes;
        $this->autoClose = $autoClose;
        $this->content = '';
    }
    
    /**
     * Free ressources on object destruction
     */
    public function __destruct()
    {
        if ( array_key_exists( 'id', $this->attributes ) )
        {
            if ( in_array( $this->attributes['id'], self::$ids ) )
            {
                foreach ( self::$ids as $key => $value )
                {
                    if ( $value == $this->attributes['id'] )
                    {
                        unset ( self::$ids[$key] );
                        break;
                    }
                }
            }
        }
    }
    
    /**
     * Set the id of the element
     * @param string $id
     * @throws Exception
     */
    protected function setId( $id )
    {
        if ( in_array( $id, self::$ids ) )
        {
            throw new Exception("A html element of id {$id} already exists");
        }
        else
        {
            self::$ids[] = $id;
        }
    }
    
    /**
     * Set the element content
     * @param   string $content
     */
    public function setContent( $content )
    {
        $this->content = $content;
    }
    
    /**
     * @see     Claro_Renderer
     */
    public function render()
    {
        return "<{$this->elementName}"
            . ( !empty( $this->attributes )
                ? $this->formatAttributes( $this->attributes ) 
                : '' )
            . ( $this->autoClose
                ? " />"
                : ">{$this->content}</{$this->elementName}>" )
            ;
    }
    
    /**
     * Format attributes
     * @param   array $attributes associative array of attributes
     * @return  string formated attributes
     */
    protected function formatAttributes( $attributes )
    {
        if ( empty( $attributes ) )
        {
            return '';
        }
        else
        {
            $attribs = '';
            
            foreach ( $attributes as $key => $value )
            {
                if ( $value )
                {
                    $attribs .= " {$key}=\"{$value}\"";
                }
            }
            
            return $attribs;
        }
    }
    
    /**
     * Get element id
     * @return  string id
     */
    public function getId()
    {
        if ( array_key_exists( 'id', $this->attributes ) )
        {
            return $this->attributes['id'];
        }
        else
        {
            return null;
        }
    }
    
    /**
     * Return the value of the given attribute
     * @param   string $name attribute name
     * @return  string attribute value or null if not defined
     */
    public function getAttribute( $name )
    {
        if ( array_key_exists( $name, $this->attributes ) )
        {
            return $this->attributes[$name];
        }
        else
        {
            return null;
        }
    }
    
    /**
     * Set or modify the value of the given attribute
     * @param   string $name attribute name
     * @param   string $value attribute value
     */
    public function setAttribute( $name, $value )
    {
        $this->attributes[$name] = $value;
    }
    
    /**
     * Unset a given attribute
     * @param string $name attribute name
     */
    public function unsetAttribute( $name )
    {
        unset( $this->attributes[$name] );
    }
    
    /**
     * Append an associative array of attributes
     * @param array $attr
     */
    public function appendAttributes( $attr )
    {
        $this->attributes = array_merge( $this->attributes, $attr );
    }
    
    /**
     * Alias of format attributes
     * @return string
     */
    protected function renderAttributes()
    {
        return $this->formatAttributes($this->attributes);
    }
}

/**
 * HTML container
 */
class Claro_Html_Container extends Claro_Html_Element
{
    protected $elems;
    
    /**
     * Constructor
     * @param string $elementName
     * @param array $attributes
     */
    public function __construct( $elementName, $attributes = array() )
    {
        parent::__construct( $elementName, $attributes );
        
        $this->elems = array();
    }
    
    /**
     * Add an element to the container
     * @param Claro_Html_Element $element
     */
    public function addElement( $element )
    {
        $this->elems[] = $element;
    }
    
    /**
     * Render the container in HTML
     * @return string
     */
    public function render()
    {
        $this->setContent( $this->renderElems() );
        return parent::render();
    }
    
    /**
     * Render the contained elements
     * @return string
     */
    protected function renderElems()
    {
        $tmp = '';
        foreach ( $this->elems as $elem )
        {
            $tmp .= $elem->render() . "\n";
        }
        return $tmp;
    }
}

/**
 * Composite element
 */
class Claro_Html_Composite implements Claro_Renderer
{
    /**
     * Array of HTML elements contained in the composite element
     * @var array 
     */
    protected $elems;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->elems = array();
    }
    
    /**
     * Add an element
     * @param Claro_Html_Element $element
     */
    public function addElement( $element )
    {
        $this->elems[] = $element;
    }
    
    /**
     * Render the composite element
     * @return string
     */
    public function render()
    {
        $tmp = '';
        foreach ( $this->elems as $elem )
        {
            $tmp .= $elem->render() . "\n";
        }
        return $tmp;
    }
}

/**
 * HTML auto-closing element (i.e. no closing tag) <element />
 */
abstract class Claro_Html_Element_AutoClose extends Claro_Html_Element
{
    /**
     * @see Claro_Html_Element
     * @param string $elementName
     * @param array $attributes
     */
    public function __construct ( $elementName, $attributes = array() )
    {
        parent::__construct ( $elementName, $attributes, true );
    }
}

/**
 * HTML element with opening/closing tag <element></element>
 */
abstract class Claro_Html_Element_OpenClose extends Claro_Html_Element
{
    /**
     * @see Claro_Html_Element
     * @param string $elementName
     * @param array $attributes
     */
    public function __construct ( $elementName, $attributes = array() )
    {
        parent::__construct ( $elementName, $attributes, false );
    }
}

/**
 * Element of an HTML form
 */
abstract class Claro_Form_Element extends Claro_Html_Element
{  
    /** 
     * Accumulator to compute the form element id
     * @var int $id
     */
    protected static $id = 1;
    
    /**
     * @see Claro_Html_Element
     * @param string $elementName
     * @param array $attributes
     * @param boolean $autoClose
     */
    public function __construct( $elementName, $attributes = array(), $autoClose = false )
    {        
        if ( ! array_key_exists( 'id', $attributes ) )
        {
            $attributes['id'] = $elementName .'_'. self::$id++;
        }
        
        parent::__construct( $elementName, $attributes, $autoClose );
    }
    
    /**
     * Set the name attribute (not element name !) of the form element
     * @param string $name
     */
    public function setName( $name )
    {
        $this->setAttribute( 'name', $name );
    }
    
    /**
     * @see Claro_Html_Element
     * @throws Exception if no name given in attributes
     */
    public function renderAttributes ()
    {
        if ( ! array_key_exists( 'name', $this->attributes ) )
        {
            throw new Exception ('Form elements must have a name');
        }
        
        parent::renderAttributes ();
    }
}

/**
 * HTML auto close <element /> for a form
 */
abstract class Claro_Form_Element_AutoClose extends Claro_Form_Element
{
    /**
     * @see Claro_Html_Element
     * @param string $elementName
     * @param array $attributes
     */
    public function __construct ( $elementName, $attributes = array() )
    {
        parent::__construct ( $elementName, $attributes, true );
    }
}

/**
 * 
 */
abstract class Claro_Form_Element_OpenClose extends Claro_Form_Element
{
    /**
     * @see Claro_Html_Element
     * @param string $elementName
     * @param array $attributes
     */
    public function __construct ( $elementName, $attributes = array() )
    {
        parent::__construct ( $elementName, $attributes, false );
    }
}

/**
 * HTML form
 */
class Claro_Html_Form extends Claro_Html_Element_OpenClose
{
    const METHOD_POST = 'post';
    const METHOD_GET = 'get';
    
    protected $action, $method;
    
    protected $elements = array();
    
    protected $csrf = true, $uniqid = false;

    /**
     * Create a form
     * @param string $action callback url for the form
     * @param string $method method used to call the callback url 
     *  Claro_Html_Form::METHOD_POST or Claro_Html_Form::METHOD_GET
     * @param array $extra other attributes for the form in an associative array
     */
    public function __construct( $action = '', $method = self::METHOD_POST, $extra = array() )
    {
        $this->action = empty( $action )
            ? $_SERVER['PHP_SELF']
            : $action
            ;
            
         if ( ! ( $method == self::METHOD_POST || self::METHOD_GET ) )
         {
            throw new Exception ("Invalid method {$method}");
         }
         
         $this->method = $method;
         
         parent::__construct('form', $extra);
         
         $this->setAttribute( 'action', $this->action );
         $this->setAttribute( 'method', $this->method );
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
        
        $this->content = "\n";
        
        foreach ( $this->elements as $element )
        {
            $this->content .= $element['element']->render()
                . ($element['newline'] ? '<br />' : '')
                . "\n"
                ;
        }
        
        return parent::render();
    }
    
    /**
     * Add an element to the form
     * @param Claro_Renderer $element element to add to the form
     * @param boolean $newline add a new line after the element
     */
    public function addElement( Claro_Renderer $element, $newline = false )
    {
        $this->elements[] = array(
            'element' => $element,
            'newline' => $newline
        );
    }
    
    /**
     * Disable cross-site request forgery protection
     */
    public function disableCsrfProtection()
    {
        $this->csrf = false;
    }
    
    /**
     * Enable protection against multiple submission
     */
    public function protectAgainstMultipleSubmision()
    {
        $this->uniqid = true;
    }
}

/**
 * HTML fieldset
 */
class Claro_Html_Fieldset extends Claro_Html_Element_OpenClose
{
    protected $legend;
    protected $elements = array();
    
    /**
     * Create a fieldset
     * @param Claro_Html_Legend $legend legend of the field set
     * @param array $extra other attributes for the fieldset element in an associated array
     */
    public function __construct( Claro_Html_Legend $legend, $extra = array() )
    {
        $this->legend = $legend;
        parent::__construct( 'fieldset', $extra );
    }
    
    /**
     * Add an element to the fieldset
     * @param Claro_Renderer $element element to add to the form
     * @param boolean $newline add a new line after the element
     */
    public function addElement( Claro_Renderer $element, $newline = false )
    {
        $this->elements[] = array(
            'element' => $element,
            'newline' => $newline
        );
    }
    
    /**
     * @see Claro_Renderer
     * @return string
     */
    public function render()
    {
        $this->content = "\n" . $this->legend->render() . "\n";
        
        foreach ( $this->elements as $element )
        {
            $this->content .= $element['element']->render()
                . ($element['newline'] ? '<br />' : '')
                . "\n"
                ;
        }
        
        return parent::render();
    }
}

/**
 * Legend of a fieldset
 */
class Claro_Html_Legend extends Claro_Html_Element_OpenClose
{
    /**
     * Create a legend element
     * @param string $legend text of the legend
     * @param array $extra other attributes for the fieldset element in an associated array
     */
    public function __construct( $legend, $extra = array() )
    {
        parent::__construct('legend', $extra);
        $this->setContent( $legend );
    }
}

/**
 * Label of an element
 */
class Claro_Html_Label extends Claro_Html_Element_OpenClose
{
    protected $label, $for, $extra;
    
    /**
     * Create a label
     * @param string $label text of the label
     * @param string $for id of the element associated with the label
     * @param array $attributes associative array of attributes
     */
    public function __construct( $label, $for, $attributes = array() )
    {
        $attributes['for'] = $for;
           
        parent::__construct( 'label', $attributes );
        
        $this->setContent( $label );
    }
}

class Claro_Html_Input_Generic extends Claro_Form_Element_AutoClose
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
        
        parent::__construct( 'input' );
        
        $this->setAttribute( 'type', $type );
        $this->setName( $name );
        $this->setAttribute( 'value', $value );
        if (is_array($extra)) $this->appendAttributes( $extra );
    }
    
    /**
     * Set the label of the input element
     * @param string|Claro_Html_Label $label
     * @param boolean $after if set to true, the label will be placed after the input instead of before
     * @param array $extra associative array of attributes of the label
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
    }
    
    /**
     * Disable/ignore the input in form submit
     */
    public function disable()
    {
        $this->setAttribute( 'disabled', 'disabled' );
    }
    
    /**
     * Enable/unignore the input in form submit
     */
    public function enable()
    {
        $this->unsetAttribute( 'disabled' );
    }
    
    // readonly works on text fields only (text,password and textarea
    /**
     * Set the input element as readonly
     */
    public function readonly()
    {
        if ( ! in_array( $this->attributes['type'], array('text','password') ) )
        {
            throw new Exception ("Only text input could be readonly not for {$this->attributes['type']}");
        }
        
        $this->setAttribute( 'readonly', 'readonly' );
    }
    
    /**
     * Set the input element as checked
     * @throws Exception
     */
    public function checked()
    {
        if ( ! in_array( $this->attributes['type'], array('radio', 'checkbox') ) )
        {
            throw new Exception ("Only radio and checkbox input could be checked not {$this->attributes['type']}");
        }
        
        $this->setAttribute( 'checked', 'checked' );
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
class Claro_Html_Input_RadioList
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
            $this->radioList[] = new Claro_Html_Input_Radio( $name, $value, $label, ($checked==$value) );
        }           
    }
    
    /**
     * @see Claro_Renderer
     * @return string
     */
    public function render()
    {
        $ret = '';
        
        foreach ( $this->radioList as $radio )
        {
            $ret .= $radio->render() . "<br />\n";
        }
        
        return $ret;
    }
}

/**
 * Check box list
 */
class Claro_Html_Input_CheckboxList
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
            $this->checkboxList[] = new Claro_Html_Input_Checkbox( $name, $value, $label, ($checked==$value) );
        }           
    }
    
    /**
     * @see Claro_Renderer
     * @return string
     */
    public function render()
    {
        $ret = '';
        
        foreach ( $this->checkboxList as $checkbox )
        {
            $ret .= $checkbox->render() . "<br />\n";
        }
        
        return $ret;
    }
}

/**
 * Text area
 */
class Claro_Html_Textarea extends Claro_Form_Element_OpenClose
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
        parent::__construct( 'textarea' );
        
        $this->setName( $name );
        $this->setAttribute( 'rows', $rows );
        $this->setAttribute( 'cols', $cols );
        
        if ( is_array($extra) ) $this->appendAttributes( $extra );
        
        $this->setContent( $value );
    }
}

/**
 * Abstract item of a select box
 */
abstract class Claro_Html_SelectBox_Item extends Claro_Html_Element_OpenClose
{
    /**
     * Disable the item
     */
    public function disable()
    {
        $this->setAttribute( 'disabled', 'disabled' );
    }
    
    /**
     * Enable the item
     */
    public function enable()
    {
        $this->unsetAttribute( 'disabled' );
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
        parent::__construct( 'option' );
        
        $this->setAttribute( 'value', $value );
        
        if ( is_array($extra) ) $this->appendAttributes( $extra );
        
        $this->setContent( $label );
    }
    
    /**
     * Set the option as selected
     */
    public function selected()
    {
        $this->attributes['selected'] = 'selected';
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
        parent::__construct( 'optgroup' );
        
        $this->setAttribute( 'label', $label );
        
        if ( is_array($extra) ) $this->appendAttributes( $extra );
    }
    
    /**
     * Add an option to the group
     * @param Claro_Html_SelectBox_Option $element
     */
    public function addOption( Claro_Html_SelectBox_Option $element )
    {
        $this->elements[] = $element;
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
class Claro_Html_SelectBox extends Claro_Form_Element_OpenClose
{
    protected $elements = array();
    
    /**
     * Create a select box
     * @param string $name name attribute
     * @param array $extra other attributes
     */
    public function __construct( $name, $extra = array() )
    {
        parent::__construct( 'select' );
        
        $this->setName( $name );
        
        if ( is_array($extra) ) $this->appendAttributes( $extra );
    }
    
    /**
     * Add an option to the group
     * @param Claro_Html_SelectBox_Option $element
     */
    public function addOption( Claro_Html_SelectBox_Option $element )
    {
        $this->elements[] = $element;
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
    
    public function getUniqid()
    {
        return $this->formId;
    }
}
