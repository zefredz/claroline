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
    protected $id;
    protected static $idCount = 1;
    
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
        
        if ( ! array_key_exists( 'id', $attributes ) )
        {
            $attributes['id'] = $elementName .'_'. self::$idCount++;
        }
        
        $this->setId($attributes['id']);
        
        unset( $attributes['id'] );
        
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
        if ( isset(self::$ids[$this->getId()]) )
        {
            unset(self::$ids[$this->getId()]);
        }
    }
    
    /**
     * Set the id of the element
     * @param string $id
     * @throws Exception when trying to set id to an id already used by another element
     * @return Claro_Html_Element $this
     */
    public function setId( $id )
    {
        if ( empty ( $id ) )
        {
            throw new Exception("You cannot assign an empty id to a html element using this API, sorry about that");
        }
        
        if ( isset( self::$ids[$id] ) && $this->id !== $id )
        {
            throw new Exception("A html element of id {$id} already exists");
        }
        elseif ( $this->id === $id )
        {
            // skip
        }
        else
        {
            $oldId = $this->id;
            $this->id = $id;
            
            unset(self::$ids[$oldId]);
            
            self::$ids[$id] = $id;
        }
        
        return $this;
    }
    
    /**
     * Set the element content
     * @param   mixed $content string or Claro_Renderer
     */
    public function setContent( $content )
    {
        $this->content = $content;
        
        return $this;
    }
    
    /**
     * @see     Claro_Renderer
     */
    public function render()
    {
        $content = $this->content instanceof Claro_Renderer 
            ? $this->content->render() 
            : $this->content
            ;
        
        return "<{$this->elementName} id={$this->getId()}"
            . ( !empty( $this->attributes )
                ? $this->formatAttributes( $this->attributes ) 
                : '' )
            . ( $this->autoClose
                ? " />"
                : ">{$content}</{$this->elementName}>" )
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
        return $this->id;
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
        if ( $name === 'id' )
        {
            return $this->setId($value);
        }
        
        $this->attributes[$name] = $value;
        
        return $this;
    }
    
    /**
     * Unset a given attribute
     * @param string $name attribute name
     */
    public function unsetAttribute( $name )
    {
        if ( $name === 'id' )
        {
            throw new Exception("You cannot assign an empty id to a html element using this API, sorry about that");
        }
        
        unset( $this->attributes[$name] );
        
        return $this;
    }
    
    /**
     * Append an associative array of attributes
     * @param array $attr
     */
    public function appendAttributes( $attr )
    {
        if ( isset( $attr['id'] ) )
        {
            $this->setId( $attr['id'] );
            unset( $attr['id'] );
        }
        
        $this->attributes = array_merge( $this->attributes, $attr );
        
        return $this;
    }
}

/**
 * HTML container
 */
class Claro_Html_Container extends Claro_Html_Element implements Countable
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
    public function addElement( Claro_Renderer $element )
    {
        $this->elems[] = $element;
        
        return $this;
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
    
    /**
     * @see Countable
     * @return int
     */
    public function count ()
    {
        return count( $this->elems );
    }
}

/**
 * Composite element
 */
class Claro_Html_Composite implements Claro_Renderer, Countable
{
    /**
     * Array of HTML elements contained in the composite element
     * @var array 
     */
    protected $elems, $separator;
    
    /**
     * Constructor
     */
    public function __construct( $separator = "\n")
    {
        $this->elems = array();
        $this->separator = $separator;
    }
    
    /**
     * Add an element
     * @param Claro_Html_Element $element
     */
    public function addElement( Claro_Renderer $element )
    {
        $this->elems[] = $element;
        
        return $this;
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
            $tmp .= $elem->render(). $this->separator;
        }
        return $tmp;
    }
    
    /**
     * @see Countable
     * @return int
     */
    public function count ()
    {
        return count( $this->elems );
    }
}

class Claro_Html_DefinitionTitle extends Claro_Html_Element
{
    public function __construct ( $title, $attributes = array ( ) )
    {
        parent::__construct ( 'dt', $attributes, false );
        $this->setContent($title);
    }
}

class Claro_Html_Definition extends Claro_Html_Element
{
    public function __construct ( $definition, $attributes = array ( ) )
    {
        parent::__construct ( 'dd', $attributes, false );
        $this->setContent($definition);
    }
}

class Claro_Html_DefinitionList extends Claro_Html_Container
{
    public function __construct ( $attributes = array ( ) )
    {
        parent::__construct ( 'dl', $attributes );
    }
    
    /**
     * Add a definition to the list
     * @param Claro_Html_DefinitionTitle|string $title
     * @param Claro_Html_Definition|string $definition
     * @return \Claro_Html_DefinitionList
     */
    public function addDefinition( $title, $definition )
    {
        if ( ! $title instanceof Claro_Html_DefinitionTitle )
        {
            $title = new Claro_Html_DefinitionTitle( $title );
        }
        
        if ( ! $definition instanceof Claro_Html_Definition )
        {
            $definition = new Claro_Html_Definition( $definition );
        }    
        
        $row = new Claro_Html_Composite();
        $row->addElement($title);
        $row->addElement($definition);
        
        $this->addElement($row);
        
        return $this;
    }
}
