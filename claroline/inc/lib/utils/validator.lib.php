<?php // $Id$

// vim: expandtab sw=4 ts=4 sts=4:

/**
 * Data validator library
 *
 * @version     1.9 $Revision$
 * @copyright   2001-2008 Universite catholique de Louvain (UCL)
 * @author      Claroline Team <info@claroline.net>
 * @author      Frederic Minne <zefredz@claroline.net>
 * @license     http://www.gnu.org/copyleft/gpl.html
 *              GNU GENERAL PUBLIC LICENSE version 2 or later
 * @package     utils
 */

interface Claro_Validator
{
    /**
     * Check if the given value is valid
     * @param   mixed $value
     * @return  boolean
     */
    public function isValid( $value );
}

/**
 * Exception class for the Validator library
 */
class Claro_Validator_Exception extends Exception{};

/**
 * Validator that uses a given PHP callback to validate a value
 */
class Claro_Validator_Callback implements Claro_Validator
{
    protected $callback;
    
    /**
     * @param   callback $callback;
     * @throws  Claro_Validator_Exception if $callback is not callable
     */
    public function __construct( $callback )
    {
        if ( ! is_callable( $this->callback ) )
        {
            throw new Claro_Validator_Exception("Callback ".var_export($this->callback, true)."is not callable");
        }
        else
        {
            $this->callback = $callback;
        }
    }
    
    /**
     * @see Claro_Validator
     */
    public function isValid( $value )
    {
        return call_user_func( $this->callback, $value );
    }
}

/**
 * Validator that checks the data type of a value
 *
 * Allowed types are
 *  - alnum     : the value is an alpha-numeric string
 *  - alpha     : the value only containes alphabetical chars
 *  - array     : the value is an array
 *  - bool      : the value is a boolean
 *  - boolean   : the value is the string 'true' or 'false'
 *  - digit     : the value is a string containing only digits
 *  - float     : the value is a float
 *  - int       : the value is an integer
 *  - lower     : ths value is a lower case string
 *  - null      : the value is null
 *  - numeric   : the value is a number or a string representation of a number
 *  - object    : the value is an object
 *  - space     : the value is a string only containing white spaces
 *  - string    : the value is a string
 *  - upper     : the value is an upper case
 *  - xdigit    : the value is a string representation of an hexadecimal number
 */
class Claro_Validator_ValueType implements Claro_Validator
{
    protected static $supportedType = array(
        'alnum'     => 'ctype_alnum',
        'alpha'     => 'ctype_alpha',
        'array'     => 'is_array',
        'bool'      => 'is_bool',
        'boolean'   => array(Claro_Validator_ValueType, 'booleanString'),
        'digit'     => 'ctype_digit',
        'float'     => 'is_float',
        'int'       => 'is_int',
        'lower'     => 'ctype_lower',
        'null'      => 'is_null',
        'numeric'   => 'is_numeric',
        'object'    => 'is_object',
        'space'     => 'ctype_space',
        'string'    => 'is_string',
        'upper'     => 'ctype_upper',
        'xdigit'    => 'ctype_xdigit',
        );
    
    /**
     * @param   string $type;
     * @throws  Claro_Validator_Exception if $type is not supported
     */    
    public function __construct( $type )
    {
        if ( array_key_exists( $type, self::$supportedType ) )
        {
            $this->type = $type;
        }
        else
        {
            throw new Claro_Validator_Exception("Unsupported type {$type}");
        }
    }
    
    /**
     * @see     Claro_Validator
     */
    public function isValid( $value )
    {
        if ( call_user_func( self::$supportedType[$this->type], $value ) )
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    
    private static function booleanString( $value )
    {
        return strtolower( $value ) == 'true' || strtolower( $value ) == 'false';
    }
}

/**
 * Validator that checks if the value is in a given list
 */
class Claro_Validator_AllowedList implements Claro_Validator
{
    protected $allowedValues;
    
    /**
     * @param   array $allowedValues
     */
    public function __construct( $allowedValues )
    {
        $this->allowedValues = $allowedValues;
    }
    
    /**
     * @see     Claro_Validator
     */
    public function isValid( $value )
    {
        return in_array( $value, $this->allowedValues );
    }
}

/**
 * Validator that uses a PCRE regular expression to check a value
 */
class Claro_Validator_Pcre implements Claro_Validator
{
    protected $regexp;
    
    /**
     * @param   string $regexp PCRE regular expression
     */
    public function __construct( $regexp )
    {
        $this->regexp = $regexp;
    }
    
    /**
     * @see     Claro_Validator
     */
    public function isValid( $value )
    {
        return preg_match( $this->regexp, $value );
    }
}

/**
 * Validator that checks if a value has the given file extension
 */
class Claro_Validator_FileExtension implements Claro_Validator
{
    protected $extension;
    
    /**
     * @param   string $extension file extension
     */
    public function __construct( $extension )
    {
        $extension = $extension[0] == '.'
            ? substr( $extension, 1 )
            : $extension
            ;
            
        $this->extension = $extension;
    }
    
    /**
     * @see     Claro_Validator
     */
    public function isValid( $value )
    {
        return ( pathinfo( $value, PATHINFO_EXTENSION ) == $this->extension );
    }
}

/**
 * Validator that checks if a value is not empty
 */
class Claro_Validator_NotEmpty implements Claro_Validator
{
    /**
     * @see     Claro_Validator
     */
    public function isValid( $value )
    {
        return ( !empty( $value ) );
    }
}
