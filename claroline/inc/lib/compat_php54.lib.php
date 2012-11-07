<?php // $Id$

/**
 * Fix the issues with the change of default encoding in claro_htmlspecialchars 
 * and htmlentities functions.
 * 
 * The drawback is this will degrade a bit the performances of the platform.
 */

// PHP 5.2.7 constants
if (!defined('PHP_VERSION_ID')) 
{
    $version = explode('.',PHP_VERSION);

    define('PHP_VERSION_ID', ($version[0] * 10000 + $version[1] * 100 + $version[2]));
    define('PHP_MAJOR_VERSION',   $version[0]);
    define('PHP_MINOR_VERSION',   $version[1]);
    define('PHP_RELEASE_VERSION', $version[2]);
}

// PHP 5.4 fix
if ( PHP_MAJOR_VERSION >= 5 && PHP_MINOR_VERSION >= 4 )
{
    define ( 'DEFAULT_COMPAT', ENT_COMPAT | ENT_HTML401 );
    define ( 'DEFAULT_ENCODING', 'iso-8859-1' );
    
    function claro_htmlspecialchars( 
        $string, 
        $flags = DEFAULT_COMPAT, 
        $encoding = DEFAULT_ENCODING )
    {
        return htmlspecialchars( $string, $flags, $encoding );
    }
    
    function claro_htmlentities( 
        $string, 
        $flags = DEFAULT_COMPAT, 
        $encoding = DEFAULT_ENCODING )
    {
        return htmlentities( $string, $flags, $encoding );
    }
}
else
{
    function claro_htmlspecialchars()
    {
        return call_user_func_array('htmlspecialchars', func_get_args());
    }
    
    function claro_htmlentities()
    {
        return call_user_func_array('htmlentities', func_get_args());
    }
}
