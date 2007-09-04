<?php // $Id$

    // vim: expandtab sw=4 ts=4 sts=4:

    if ( count( get_included_files() ) == 1 )
    {
        die( 'The file ' . basename(__FILE__) . ' cannot be accessed directly, use include instead' );
    }
    
    function is_value_allowed( $value, $allowedValueList )
    {
        return in_array( $value, $allowedValueList );
    }
    
    function check_value_type( $value, $type )
    {
        $supportedType = array();

        $supportedType['ctype'] = array( 'alnum'
            , 'alpha', 'digit', 'lower'
            , 'upper', 'space', 'xdigit' );
        $supportedType['phptype'] = array( 'float'
            , 'int', 'string', 'array', 'bool' );

        if ( in_array( $type, $supportedType['ctype'] ) )
        {
            if ( call_user_func( 'ctype_' . $type, $value ) )
            {
                return true;
            }
            else
            {
                return false;
            }
        }
        elseif ( in_array( $type, $supportedType['phptype'] ) )
        {
            switch( $type )
            {
                case 'bool':
                    return is_bool( $value );
                case 'int':
                    return is_integer( $value );
                case 'float':
                    return is_float( $value );
                case 'array':
                    return is_array( $value );
                case 'string':
                    return is_string( $value );
            }
        }
        else
        {
            return false;
        }
    }
    
    function protect_against_file_inclusion( $path )
    {
        // protect against remote file inclusion
        while ( false !== strpos( $path, '://' ) )
        {
            $path = str_replace( '://', '', $path );
        }
        
        // protect against arbitrary file inclusion
        while ( false !== strpos( $path, '..' ) )
        {
            $path = str_replace( '..', '.', $path );
        }
            
        return $path;
    }

    function uses()
    {
        $args = func_get_args();
        $notFound = array();
        
        defined('INCLUDES') || define ( 'INCLUDES', dirname(__FILE__) . '/..');
        
        foreach ( $args as $lib )
        {
            $lib = protect_against_file_inclusion( $lib );
            
            $kernelPath = INCLUDES . '/' . $lib . '.php';
            $localPath = get_module_path(get_current_module_label()) . '/lib/' . $lib . '.php';
            
            if ( file_exists( $localPath ) )
            {
                require_once $localPath;
            }
            elseif ( file_exists( $kernelPath ) )
            {
                require_once $kernelPath;
            }
            else
            {
                // error not found
                if ( claro_debug_mode() ) 
                {
                    trigger_error( "Lib not found $lib", E_USER_ERROR );
                }
                
                $notFound[] = $lib;
            }
        }
        
        return $notFound;
    }
?>
