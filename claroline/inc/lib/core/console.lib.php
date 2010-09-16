<?php // $Id$

    // vim: expandtab sw=4 ts=4 sts=4:
    
    if ( count( get_included_files() ) == 1 )
    {
        die( 'The file ' . basename(__FILE__) . ' cannot be accessed directly, use include instead' );
    }
    
    /**
     * Debug bar
     *
     * @version     1.9 $Revision$
     * @copyright   2001-2008 Universite catholique de Louvain (UCL)
     * @author      Claroline Team <info@claroline.net>
     * @author      Frederic Minne <zefredz@claroline.net>
     * @license     http://www.gnu.org/copyleft/gpl.html
     *              GNU GENERAL PUBLIC LICENSE version 2 or later
     * @package     KERNEL
     */

FromKernel::uses('core/debug.lib');

class Console
{
    public static function message( $message )
    {
        pushClaroMessage( $message, 'message' );
        Claroline::log( 'message', $message );
    }

    public static function debug( $message )
    {
        if ( claro_debug_mode() )
        {
            pushClaroMessage( $message, 'debug' );
            Claroline::log( 'debug', $message );
        }
    }
    
    public static function warning( $message )
    {
        pushClaroMessage( $message, 'warning' );
        Claroline::log( 'warning', $message );
    }

    public static function info( $message )
    {
        pushClaroMessage( $message, 'info' );
        Claroline::log( 'info', $message );
    }

    public static function success( $message )
    {
        pushClaroMessage( $message, 'success' );
        Claroline::log( 'success', $message );
    }

    public static function error( $message )
    {
        // claro_failure::set_failure( $message );
        pushClaroMessage( $message, 'error' );
        Claroline::log( 'error', $message );
    }
    
    public static function log( $message, $type )
    {
        pushClaroMessage( $message, $type );
        Claroline::log( $type, $message );
    }
}
