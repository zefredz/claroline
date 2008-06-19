<?php // $Id$

// vim: expandtab sw=4 ts=4 sts=4:

/**
 * User input library
 * Replacement for $_GET and $_POST
 * Do not handle $_COOKIES !
 *
 * @version     1.9 $Revision$
 * @copyright   2001-2008 Universite catholique de Louvain (UCL)
 * @author      Claroline Team <info@claroline.net>
 * @author      Frederic Minne <zefredz@claroline.net>
 * @license     http://www.gnu.org/copyleft/gpl.html
 *              GNU GENERAL PUBLIC LICENSE version 2 or later
 * @package     utils
 */
 
FromKernel::uses ( 'utils/validator.lib' );

/**
 * Data Input Exception, thrown when an input value does not match
 * a filter or is missing
 */
class Claro_Input_Exception extends Exception{};

/**
 * Defines the required methods for a data input object
 */
interface Claro_Input
{
    /**
     * Get a value given its name
     * @param   string $name variable name
     * @param   mixed $default default value (if $name is missingin the input)
     * @return  mixed value of $name in input data or $default value
     * @throws  Claro_Input_Exception on failure
     */
    public function get( $name, $default = null );
    /**
     * Get a value given its name
     * @param   string $name variable name
     * @return  mixed value of $name
     * @throws  Claro_Input_Exception on failure or if $name is missing
     */
    public function getMandatory( $name );
}

/**
 * Array based data input class
 */
class Claro_Input_Array implements Claro_Input
{
    protected $input;
    
    /**
     * @param   array $input
     */
    public function __construct( $input )
    {
        $this->input = $input;
    }
    
    /**
     * @see     Claro_Input
     */
    public function get( $name, $default = null )
    {
        if ( array_key_exists( $name, $this->input ) )
        {
            return $this->input[$name];
        }
        else
        {
            return $default;
        }
    }
    
    /**
     * @see     Claro_Input
     */
    public function getMandatory( $name )
    {
        $ret = $this->get( $name );
        
        if ( empty( $ret ) )
        {
            throw new Claro_Input_Exception( "{$name} not found in ".get_class($this)." !" );
        }
        else
        {
            return $ret;
        }
    }
}

/**
 * Data input class with filters callback for validation
 */
class Claro_Input_Validator implements Claro_Input
{
    protected $filters;
    protected $input;
    
    /**
     * @param   Claro_Input $input
     */
    public function __construct( Claro_Input $input )
    {
        $this->filters = array();
        $this->input = $input;
    }
    
    /**
     * Set a validator for the given variable
     * @param   string $name variable name
     * @param   Claro_Validator $validator validator object
     * @throws  Claro_Input_Exception if the filter callback is not callable
     */
    public function setValidator( $name, Claro_Validator $validator )
    {
        if ( ! array_key_exists( $name, $this->filters ) )
        {
            $this->filters[$name] = array();
        }
        
        $filtercallback = array( $validator, 'isValid' );
        
        if ( ! is_callable( $filtercallback ) )
        {
            throw new Claro_Input_Exception ("Invalid filter callback : " 
                . $this->getFilterCallbackString($filtercallback));
        }
        
        $this->filters[$name][] = $filtercallback;
    }
    
    /**
     * @see     Claro_Input
     * @throws  Claro_Input_Exception if $value does not pass the validator
     */
    public function get( $name, $default = null )
    {
        $tainted = $this->input->get( $name, $default );
        
        if ( ( is_null( $default ) && is_null( $tainted ) )
            || $tainted == $default )
        {
            return $default;
        }
        else
        {
            return $this->filter( $name, $tainted );
        }
    }
    
    /**
     * @see     Claro_Input
     * @throws  Claro_Input_Exception if $value does not pass the validator
     */
    public function getMandatory( $name )
    {
        $tainted = $this->input->getMandatory( $name );
        
        return $this->filter( $name, $tainted );
    }
    
    /**
     * @param   string $name
     * @param   mixed $tainted value
     * @throws  Claro_Input_Exception if $value does not pass the filter for $name
     */
    public function filter( $name, $tainted )
    {
        if ( array_key_exists( $name, $this->filters ) )
        {
            foreach ( $this->filters[$name] as $filterCallback )
            {
                if ( ! call_user_func( $filterCallback, $tainted ) )
                {
                    throw new Claro_Input_Exception( "{$name} does not pass the validator "
                        . get_class( $filterCallback[0] ) . " !" );
                }
            }
        }
        
        return $tainted;
    }
}

/**
 * User input class to replace $_REQUEST
 */
class Claro_UserInput
{        
    protected static $instance = false;
    
    /**
     * Get user input object
     * @return  Claro_Input_Validator
     */
    public static function getInstance()
    {
        if ( ! self::$instance )
        {
            self::$instance = new Claro_Input_Validator( 
                new Claro_Input_Array( array_merge( $_GET, $_POST ) ) );
        }
        
        return self::$instance;
    }
}
