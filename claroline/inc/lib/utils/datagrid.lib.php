<?php // $Id$

// vim: expandtab sw=4 ts=4 sts=4:

/**
 * Datagrid library
 *
 * @version     1.9 $Revision$
 * @copyright   2001-2008 Universite catholique de Louvain (UCL)
 * @author      Claroline Team <info@claroline.net>
 * @author      Frederic Minne <zefredz@claroline.net>
 * @license     http://www.gnu.org/copyleft/gpl.html
 *              GNU GENERAL PUBLIC LICENSE version 2 or later
 * @package     utils
 */

/**
 * Generic renderer interface
 */
interface Claro_Renderer
{
    /**
     * @return  string
     */
    public function render();
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
     * @param   string $name html element name ('input', 'p'...)
     * @param   array $attributes associative array of attributes
     * @param   bool $autoClose set to true for an autoclosed element 
     *  (&lt;input /&gt;, &lt;img /&gt;...), default false
     */
    public function __construct( $name, $attributes = null, $autoClose = false )
    {
        if ( !is_array( $attributes ) || empty( $attributes ) )
        {
            $attributes = array();
        }
        
        if ( array_key_exists( 'id', $attributes ) )
        {
            if ( in_array( $attributes['id'], self::$ids ) )
            {
                throw new Exception("A html element of id {$attributes['id']} already exists");
            }
            else
            {
                self::$ids[] = $attributes['id'];
            }
        }
        
        $this->name = $name;
        $this->attributes = $attributes;
        $this->autoClose = $autoClose;
        $this->content = '';
    }
    
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
        return "<{$this->name}"
            . ( !empty( $this->attributes )
                ? $this->formatAttributes( $this->attributes ) 
                : '' )
            . ( $this->autoClose
                ? " />"
                : ">{$this->content}</{$this->name}>" )
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
    public function getAttr( $name )
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
    public function setAttr( $name, $value )
    {
        $this->attributes[$name] = $value;
    }
}

class Claro_Utils_Datagrid extends Claro_Html_Element
{
    protected $lineNumber = 0;
    
    protected $columnsLabels = array();
    protected $columnsValues = array();
    protected $columnsOrder = array();
    protected $rows = array();
    
    protected $title = '';
    protected $footer = '';
    protected $emptyMessage = '';
    
    /**
     * @param   array $attributes attributes of the table element
     */
    public function __construct( $attributes = null )
    {
        parent::__construct( 'table', $attributes );
    }
    
    /**
     * Set the table caption
     * @param   string $title
     */
    public function setTitle( $title )
    {
        $this->title = $title;
    }
    
    /**
     * Set the table footer
     * @param   string $footer content of the footer
     */
    public function setFooter( $footer )
    {
        $this->footer = $footer;
    }
    
    /**
     * Set the empty message displayed if the data rows are empty
     * @param   string $emptyMessage
     */
    public function setEmptyMessage( $emptyMessage )
    {
        $this->emptyMessage = $emptyMessage;
    }
    
    /**
     * Set the data rows
     * @param   array $rows
     */
    public function setRows( array $rows )
    {
        $this->rows = $rows;
    }
    
    /**
     * Add a column at the start of the datagrid rows
     * @param   string $key identifier of the column
     * @param   string $label title of the column
     * @param   string $value template or value of the collumn cells
     */
    public function prependColumn( $key, $label, $value )
    {
        $this->columnsLabels[$key] = $label;
        $this->columnsValues[$key] = $value;
        array_unshift( $this->columnsOrder, $key );
    }
    
    /**
     * Add a column at the end of the datagrid rows
     * @param   string $key identifier of the column
     * @param   string $label title of the column
     * @param   string $value template or value of the collumn cells
     */
    public function addColumn( $key, $label, $value  )
    {
        $this->columnsLabels[$key] = $label;
        $this->columnsValues[$key] = $value;
        array_push( $this->columnsOrder, $key );
    }
    
    /**
     * Add a column matching a given one in the data rows
     * @param   string $key identifier of the column in the data rows
     * @param   string $label title of the column
     */
    public function addDataColumn( $key, $label  )
    {
        $this->addColumn( $key, $label, "%html($key)%" );
    }
    
    /**
     * Get the number of columns
     * @return  int
     */
    public function getColumnsCount()
    {
        return count( $this->columnsOrder );
    }
    
    /**
     * Get the number of rows
     * @return  int
     */
    public function getRowsCount()
    {
        return count( $this->rows );
    }
    
    protected function renderHeader()
    {
        $header = !empty($this->title) 
            ? "<caption>{$this->title}</caption>\n" 
            : '' 
            ;
            
        $header .= "<thead>\n<tr>"; 
        
        foreach ( $this->columnsOrder as $column )
        {
            $header .= "<th>{$this->columnsLabels[$column]}</th>";
        }
        
        $header .= "</tr>\n</thead>\n";
        
        return $header;
    }
    
    protected function renderBody()
    {
        if ( ! count( $this->rows ) )
        {
            return ( !empty($this->emptyMessage)
                ? "<tbody><tr><td colspan=\"{$this->getColumnsCount()}\">{$this->emptyMessage}</td></tr></tbody>\n"
                : "<tbody><!-- empty --></tbody>\n" )
                ;
        }
        else
        {
            $tbody = "<tbody>\n";
            
            foreach ( $this->rows as $row )
            {
                $tableRow = '';
                
                foreach ( $this->columnsOrder as $column )
                {
                    $tableRow .= "<td>"
                        . str_replace( '%_lineNumber_%'
                            , $this->lineNumber
                            , $this->columnsValues[$column] )
                        ."</td>"
                        ;
                }
                
                foreach ( $row as $key => $value )
                {
                    $tableRow = $this->replace( $key, $value, $tableRow );
                }
                
                $tbody .= "<tr>{$tableRow}</tr>\n";
                $this->lineNumber++;
            }
            
            $tbody .= "</tbody>\n";
            
            return $tbody;
        }
    }
    
    protected function renderFooter()
    {
        return !empty($this->footer)
            ? "<tfoot>\n<tr><td colspan=\"{$this->getColumnsCount()}\">{$this->footer}</td></tr>\n</tfoot>\n"
            : ''
            ;
    }
    
    /**
     * @see     Claro_HTML_Element
     */
    public function render()
    {
        $this->setContent( $this->renderHeader().$this->renderFooter().$this->renderBody() );
        
        return parent::render();
    }
    
    /**
     * Replace the templates for a given data key 
     * by the rendered string for the given value in the given string
     * For example if the key 'id' as the value 'zorg', %id% will be replaced with 'zorg'
     * @param   string $key
     * @param   string $value
     * @param   string $output
     * @return  string
     */
    protected function replace( $key, $value, $output )
    {
        $output = str_replace( "%$key%", $value, $output );
        $output = str_replace( "%html($key)%", htmlspecialchars( $value ), $output );
        $output = str_replace( "%uu($key)%", rawurlencode( $value ), $output );
        $output = str_replace( "%int($key)%", (int) $value, $output );
        
        return $output;
    }
}

/**
 * Automaticaly generate columns from the data rows
 */
class Claro_Utils_Autogrid extends Claro_Utils_Datagrid
{
    /**
     * @see     Claro_Utils_Datagrid
     */
    public function setRows( array $rows )
    {
        if ( !empty ( $rows ) )
        {
            $this->rows = $rows;
            
            $this->columnsOrder = array_merge( $this->columnsOrder, array_keys( $rows[0] ) );
            
            foreach ( array_keys( $rows[0] ) as $column )
            {
                $this->columnsLabels[$column] = htmlspecialchars( $column );
                $this->columnsValues[$column] = "%html({$column})%";
            }
        }
    }
}

/**
 * Datagrid using Claro tables
 */
class Claro_Utils_Clarogrid extends Claro_Utils_Datagrid
{
    protected $superHeader = '';
    
    public function __construct()
    {
        parent::__construct( array(
            'class' => 'claroTable'
        ) );
    }
    
    /**
     * Set a super header
     * @param   string $superHeader super header content
     */
    public function setSuperHeader( $superHeader )
    {
        $this->superHeader = $superHeader;
    }
    
    /**
     * Emphase the line the mouse is over
     */
    public function emphaseLine()
    {
        $this->attributes['class'] = 'claroTable emphaseLine';
    }
    
    /**
     * Use the available width
     */
    public function fullWidth()
    {
        $this->attributes['style'] = 'width: 100%';
    }
    
    protected function renderHeader()
    {
        $header = !empty($this->title) 
            ? "<caption>{$this->title}</caption>\n" 
            : '' 
            ;
            
        $header .= "<thead>\n";
        
        $header .= ( !empty($this->superHdr) 
                ? "<tr class=\"superHeader\">\n"
                    . "<td colspan=\"{$this->getColumnsCount()}\">{$this->superHeader}</td>\n"
                    . "</tr>\n"
                : '' )
                ;

        $header .= "<tr class=\"headerX\">"; 
        
        foreach ( $this->columnsOrder as $column )
        {
            $header .= "<th>{$this->columnsLabels[$column]}</th>";
        }
        
        $header .= "</tr>\n</thead>\n";
        
        return $header;
    }
}
