<?php // $Id$

// vim: expandtab sw=4 ts=4 sts=4:

/**
 * Datagrid library
 *
 * @version     Claroline 1.12 $Revision$
 * @copyright   (c) 2001-2014, Universite catholique de Louvain (UCL)
 * @author      Claroline Team <info@claroline.net>
 * @author      Frederic Minne <zefredz@claroline.net>
 * @license     http://www.gnu.org/copyleft/gpl.html
 *              GNU GENERAL PUBLIC LICENSE version 2 or later
 * @package     kernel.utils
 */

require_once __DIR__ . '/html.lib.php';
require_once __DIR__ . '/htmlsanitizer.lib.php';

/**
 * HTML datagrid with internal templating
 */
class Claro_Html_Datagrid extends Claro_Html_Element
{
    protected $lineNumber = 0;
    protected $lineCount = 0;
    
    protected $columnsLabels = array();
    protected $columnsValues = array();
    protected $columnsOrder = array();
    protected $rows = array();
    
    protected $title = '';
    protected $footer = '';
    protected $emptyMessage = '';
    
    protected $_allowCallback = false;
    protected $_callBack = array();
    
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
        $this->lineCount = count( $rows );
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
    
    /**
     * Render the table head
     * @return string
     */
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
    
    /**
     * Render the table body
     * @return string
     */
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
                        . str_replace( '%_lineCount_%'
                            , $this->lineCount,
                            str_replace( '%_lineNumber_%'
                                , $this->lineNumber
                                , $this->columnsValues[$column] ) )
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
    
    /**
     * Render the table footer
     * @return type
     */
    protected function renderFooter()
    {
        return !empty($this->footer)
            ? "<tfoot>\n<tr><td colspan=\"{$this->getColumnsCount()}\">{$this->footer}</td></tr>\n</tfoot>\n"
            : ''
            ;
    }
    
    /**
     * Render the datagrid
     * @see     Claro_HTML_Element
     */
    public function render()
    {
        $this->setContent( $this->renderHeader().$this->renderBody().$this->renderFooter() );
        
        return parent::render();
    }
    
    /**
     * Allow callbacks for keys
     */
    function allowCallback()
    {
        $this->_allowCallback = true;
    }
    
    /**
     * Register a callback for a given key
     * @param string $key
     * @param callable $callback
     */
    function registerCallback( $key, $callback )
    {
        $this->_callBack[$key] = $callback;
        
        return $this;
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
        if ( $this->lineNumber !== 0 )
        {
            $output = preg_replace('/%ifisfirst\([^\)]*\)%/','', $output);
        }
        else
        {
            $output = preg_replace('/%ifisfirst\(([^\)]*)\)%/',"$1", $output);
        }

        if ( $this->lineNumber !== ( $this->lineCount - 1 ) )
        {
            $output = preg_replace('/%ifislast\([^\)]*\)%/','', $output);
        }
        else
        {
            $output = preg_replace('/%ifislast\(([^\)]*)\)%/',"$1", $output);
        }
        
        if ( $this->_allowCallback && array_key_exists( $key, $this->_callBack ) )
        {
            $matches = array();

            if ( preg_match( "/%apply\(\s*([\w_]+)\s*,\s*(".$key.")\s*\)%/", $output, $matches ) )
            {
                if ( $this->_callBack[$key] == $matches[1] )
                {
                    $replacement = call_user_func( $matches[1], $value, $matches[2] );
                    $output = preg_replace( "/%apply\(\s*([\w_]+)\s*,\s*(".$key.")\s*\)%/"
                        , $replacement, $output );
                }
            }
        }
        
        $output = str_replace( "%san($key)%", claro_html_sanitize_all( $value ), $output );
        $output = str_replace( "%$key%", $value, $output );
        $output = str_replace( "%uu($key)%", rawurlencode( $value ), $output );
        $output = str_replace( "%int($key)%", (int) $value, $output );
        
        $output = str_replace( "%html($key)%", claro_htmlspecialchars( $value ), $output );
        
        return $output;
    }
}

/**
 * Automaticaly generate columns from the data rows
 */
class Claro_Html_Autogrid extends Claro_Html_Datagrid
{
    /**
     * @see     Claro_Html_Datagrid
     */
    public function setRows( array $rows )
    {
        if ( !empty ( $rows ) )
        {
            $this->rows = $rows;
            
            $this->columnsOrder = array_merge( $this->columnsOrder, array_keys( $rows[0] ) );
            
            foreach ( array_keys( $rows[0] ) as $column )
            {
                $this->columnsLabels[$column] = claro_htmlspecialchars( $column );
                $this->columnsValues[$column] = "%html({$column})%";
            }
        }
    }
}

/**
 * Datagrid using Claro tables
 */
class Claro_Html_Clarogrid extends Claro_Html_Datagrid
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
        // not 100% due to IE box model !
        $this->attributes['style'] = 'width: 99%';
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
