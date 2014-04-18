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
    protected $content = '';
    
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
    
    public function setName( $attributeName )
    {
        $this->setAttribute( 'name', $attributeName );
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
    public function __construct ( $elementName, $attributes = array() )
    {
        parent::__construct ( $elementName, $attributes, false );
    }
}

class Claro_Html_Form extends Claro_Html_Element_OpenClose
{
    const METHOD_POST = 'post';
    const METHOD_GET = 'get';
    
    protected $action, $method;
    
    protected $elements = array();
    
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
    
    public function render()
    {
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
    
    public function addElement( $element, $newline = false )
    {
        $this->elements[] = array(
            'element' => $element,
            'newline' => $newline
        );
    }
}

class Claro_Html_Fieldset extends Claro_Html_Element_OpenClose
{
    protected $legend;
    protected $elements = array();
    
    public function __construct( $legend, $extra = '' )
    {
        parent::__construct( 'fieldset', $extra );
        $this->legend = new Legend( $legend );
    }
    
    public function addElement( $element, $newline = false )
    {
        $this->elements[] = array(
            'element' => $element,
            'newline' => $newline
        );
    }
    
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

class Claro_Html_Legend extends Claro_Html_Element_OpenClose
{
    public function __construct( $legend, $extra = '' )
    {
        parent::__construct('legend', $extra);
        $this->setContent( $legend );
    }
}

class Claro_Html_Label extends Claro_Html_Element_OpenClose
{
    protected $label, $for, $extra;
    
    public function __construct( $label, $for, $extra = null )
    {
        $attributes = is_array( $extra ) ? $extra : array();
        $attributes['for'] = $for;
           
        parent::__construct( 'label', $attributes );
        
        $this->setContent( $label );
    }
}

class Claro_Html_Input_Generic extends Claro_Form_Element_AutoClose
{
    protected $label = null;
    protected $labelAfter = false;
    
    public function __construct( $type, $name, $value = '', $extra = '' )
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
    
    public function setLabel( $label, $after = false, $extra = '' )
    {
        $this->label = new Claro_Html_Label( $label, $this->getId(), $extra );
        $this->labelAfter = $after ? true : false;
    }
    
    public function disable()
    {
        $this->setAttribute( 'disabled', 'disabled' );
    }
    
    public function enable()
    {
        $this->unsetAttribute( 'disabled' );
    }
    
    // readonly works on text fields only (text,password and textarea
    public function readonly()
    {
        if ( ! in_array( $this->attributes['type'], array('text','password') ) )
        {
            throw new Exception ("Only text input could be readonly not for {$this->attributes['type']}");
        }
        
        $this->setAttribute( 'readonly', 'readonly' );
    }
    
    public function checked()
    {
        if ( ! in_array( $this->attributes['type'], array('radio', 'checkbox') ) )
        {
            throw new Exception ("Only radio and checkbox input could be checked not {$this->attributes['type']}");
        }
        
        $this->setAttribute( 'checked', 'checked' );
    }
    
    public function render()
    {
        $label = is_null( $this->label )  ? ''  : $this->label->render();
        
        return ( $this->labelAfter ? '' : (empty($label) ? '' : $label .'<br />' ) )
            . parent::render()
            . ( $this->labelAfter ? $label : '' )
            ;
    }
}

class Claro_Html_Input_Text extends Claro_Html_Input_Generic
{
    public function __construct( $name, $value = '', $size = '', $maxlength = '', $extra = '', $password = false )
    {
        $type = $password ? 'password' : 'text';
        
        parent::__construct( $type, $name, $value );
        
        if (!empty($size)) $this->setAttribute( 'size', $size );
        if (!empty($maxlength)) $this->setAttribute( 'maxlength', $maxlength );
        if (is_array($extra)) $this->appendAttributes( $extra );
    }
}

class Claro_Html_Input_Password extends Claro_Html_Input_Text
{
    public function __construct( $name, $value = '', $size = '', $maxlength = '', $extra = '' )
    {
        parent::__construct( $name, $value, $size, $maxlength, $extra, true );
    }
}

class Claro_Html_Input_Submit extends Claro_Html_Input_Generic
{
    public function __construct( $name, $value, $onsubmit = '', $extra = '' )
    {
        parent::__construct( 'submit', $name, $value );
        
        if (!empty($onsubmit)) $this->setAttribute( 'onsubmit', $onsubmit );
        if (is_array($extra)) $this->appendAttributes( $extra );
    }
}

class Claro_Html_Input_Button extends Claro_Html_Input_Generic
{
    public function __construct( $name, $value, $onclick = '', $extra = '' )
    {
        parent::__construct( 'button', $name, $value );
        
        if (!empty($onclick)) $this->setAttribute( 'onclick', $onclick );
        if (is_array($extra)) $this->appendAttributes( $extra );
    }
}

class Claro_Html_Input_Cancel extends Claro_Html_Input_Button
{
    protected $location;
    
    public function __construct( $name, $value, $location = '', $extra = '' )
    {
        $this->location = empty($location)? $_SERVER['PHP_SELF'] : $location;
        $onclick = "window.location='{$this->location}'";
            
        parent::__construct( $name, $value, $onclick, $extra );
    }
    
    public function render()
    {
        return "<a href=\"{$this->location}\">"
            . parent::render()
            . "</a>"
            ;
    }
}

class Claro_Html_Input_File extends Claro_Html_Input_Generic
{
    public function __construct( $name, $value = '', $extra = '' )
    {
        parent::__construct( 'file', $name, $value, $extra );
    }
}

class Claro_Html_Input_Image extends Claro_Html_Input_Generic
{
    public function __construct( $name, $src, $value = '', $extra = '' )
    {
        parent::__construct( 'image', $name, $value );
        
        $this->setAttribute( 'src', $src );
        if (is_array($extra)) $this->appendAttributes( $extra );
    }
}

class Claro_Html_Input_Hidden extends Claro_Html_Input_Generic
{
    public function __construct( $name, $value, $extra = '' )
    {
        parent::__construct( 'hidden', $name, $value, $extra );
    }
}

class Claro_Html_Input_Radio extends Claro_Html_Input_Generic
{
    public function __construct( $name, $value, $label = '', $checked = false, $extra = '' )
    {
        parent::__construct( 'radio', $name, $value, $extra );
        
        if ( $checked ) $this->checked();
        
        if ( !empty($label) ) $this->setLabel( $label, true );
    }
}

class Claro_Html_Input_Checkbox extends Claro_Html_Input_Generic
{
    public function __construct( $name, $value, $label = '', $checked = false, $extra = '' )
    {
        parent::__construct( 'checkbox', $name, $value, $extra );
        
        if ( $checked ) $this->checked();
        
        if ( !empty($label) ) $this->setLabel( $label, true );
    }
}

class Claro_Html_Input_RadioList
{
    protected $radioList = array();
    protected $name, $checked;
    
    public function __construct( $name, $list, $checked = '' )
    {
        $this->elementName = $name; 
        $this->checked = $checked;
        
        foreach ( $list as $value => $label )
        {
            $this->radioList[] = new Claro_Html_Input_Radio( $name, $value, $label, ($checked==$value) );
        }           
    }
    
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

class Claro_Html_Input_CheckboxList
{
    protected $checkboxList = array();
    protected $name, $checked;
    
    public function __construct( $name, $list, $checked = '' )
    {
        $this->elementName = $name; 
        $this->checked = $checked;
        
        foreach ( $list as $value => $label )
        {
            $this->checkboxList[] = new Claro_Html_Input_Checkbox( $name, $value, $label, ($checked==$value) );
        }           
    }
    
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

class Claro_Html_Textarea extends Claro_Form_Element_OpenClose
{
    public function __construct( $name, $value, $rows, $cols, $extra = '' )
    {
        parent::__construct( 'textarea' );
        
        $this->setName( $name );
        $this->setAttribute( 'rows', $rows );
        $this->setAttribute( 'cols', $cols );
        
        if ( is_array($extra) ) $this->appendAttributes( $extra );
        
        $this->setContent( $value );
    }
}

abstract class Claro_Html_Select_Item extends Claro_Html_Element_OpenClose
{
    public function disable()
    {
        $this->setAttribute( 'disabled', 'disabled' );
    }
    
    public function enable()
    {
        $this->unsetAttribute( 'disabled' );
    }
}

class Claro_Html_Select_Option extends Claro_Html_Select_Item
{
    public function __construct( $label, $value, $extra = '' )
    {
        parent::__construct( 'option' );
        
        $this->setAttribute( 'value', $value );
        
        if ( is_array($extra) ) $this->appendAttributes( $extra );
        
        $this->setContent( $label );
    }
    
    public function selected()
    {
        $this->attributes['selected'] = 'selected';
    }
}

class Claro_Html_Select_Optgroup extends Claro_Html_Select_Item
{
    protected $elements = array();
    
    public function __construct( $label, $extra = '' )
    {
        parent::__construct( 'optgroup' );
        
        $this->setAttribute( 'label', $label );
        
        if ( is_array($extra) ) $this->appendAttributes( $extra );
    }
    
    public function addClaro_Html_Select_Option( $element )
    {
        $this->elements[] = $element;
    }
    
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

class Claro_Html_Select extends Claro_Form_Element_OpenClose
{
    protected $elements = array();
    
    public function __construct( $name, $extra = '' )
    {
        parent::__construct( 'select' );
        
        $this->setName( $name );
        
        if ( is_array($extra) ) $this->appendAttributes( $extra );
    }
    
    public function addClaro_Html_Select_Option( $element )
    {
        $this->elements[] = $element;
    }
    
    public function render()
    {
        $this->content = "\n";
        
        foreach ( $this->elements as $element )
        {
            $this->content .= $element->render()."\n";
        }
        
        return parent::render();
    }
    
    public static function fromArray( $name, $optionList, $selectedValue, $extra = '' )
    {
        $select = new Select( $name, $extra );
        
        foreach ( $optionList as $value => $label )
        {
            $option = new Claro_Html_Select_Option( $label, $value );
            if ( $value == $selectedValue ) $option->selected();
            $select->addClaro_Html_Select_Option( $option );
        }
        
        return $select;
    }
}

class Claro_Html_Input_CsrfToken extends Claro_Html_Input_Hidden
{
    protected $token;
    protected $time;
    
    public function __construct()
    {
        $this->token = md5(uniqid(rand(),true));
        $this->time = time();
        
        parent::__construct( 'token', $this->token, array( 'id' => '_token' ) );
    }
    
    public function getToken()
    {
        return $this->token;
    }
    
    public function getTime()
    {
        return $this->time;
    }
    
    public static function checkToken( $tokenToCheck, $csrfToken, $csrfTime )
    {
        if ( $csrfToken != $tokenToCheck
            || ( time() - $csrfTime ) > 60 )
        {
            return false;
        }
        else
        {
            return true;
        }
    }
}

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
