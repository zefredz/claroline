<?php // $Id$

/**
 * Fix the issues with the change of default encoding in claro_htmlspecialchars 
 * and htmlentities functions
 * 
 * The drawback is that this will probably degrade the performance of the 
 * platform a bit by adding some indirection for PHP native functions
 *
 * @version     Claroline 1.12 $Revision$
 * @copyright   (c) 2001-2014, Universite catholique de Louvain (UCL)
 * @author      Claroline Team <info@claroline.net>
 * @license     http://www.gnu.org/copyleft/gpl.html
 *              GNU GENERAL PUBLIC LICENSE version 2 or later
 * @package     kernel.utils
 */

// define constants added to PHP in PHP 5.2.7 if missing
if (!defined('PHP_VERSION_ID')) 
{
    $version = explode('.',PHP_VERSION);

    define('PHP_VERSION_ID', ($version[0] * 10000 + $version[1] * 100 + $version[2]));
    define('PHP_MAJOR_VERSION',   $version[0]);
    define('PHP_MINOR_VERSION',   $version[1]);
    define('PHP_RELEASE_VERSION', $version[2]);
}

// PHP 5.4 fix
if ( PHP_MAJOR_VERSION > 5 ||  ( PHP_MAJOR_VERSION == 5 && PHP_MINOR_VERSION >= 4 ) )
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
    
    function claro_html_entity_decode (
        $string, 
        $flags = DEFAULT_COMPAT, 
        $encoding = DEFAULT_ENCODING )
    {
        return html_entity_decode( $string, $flags, $encoding );
    }
}
// PHP 5.3 allows passing func_get_args() as argument
elseif ( PHP_MAJOR_VERSION == 5 && PHP_MINOR_VERSION == 3 )
{
    function claro_htmlspecialchars()
    {
        return call_user_func_array('htmlspecialchars', func_get_args());
    }
     
    function claro_htmlentities()
    {
        return call_user_func_array('htmlentities', func_get_args());
    }
    
    function claro_html_entity_decode()
    {
        return call_user_func_array('html_entity_decode', func_get_args());
    }
}
// Other versions
else
{
    function claro_htmlspecialchars()
    {
        $args = func_get_args();
        return call_user_func_array('htmlspecialchars', $args);
    }
     
    function claro_htmlentities()
    {
        $args = func_get_args();
        return call_user_func_array('htmlentities', $args);
    }
    
    function claro_html_entity_decode()
    {
        $args = func_get_args();
        return call_user_func_array('html_entity_decode', $args);
    }
}
