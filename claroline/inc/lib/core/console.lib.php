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
     * @copyright   2001-2007 Universite catholique de Louvain (UCL)
     * @author      Claroline Team <info@claroline.net>
     * @author      Frederic Minne <zefredz@claroline.net>
     * @license     http://www.gnu.org/copyleft/gpl.html
     *              GNU GENERAL PUBLIC LICENSE version 2 or later
     * @package     DISPLAY
     */

    uses ('core/debug.lib');

    class Console
    {
        public static function message( $message )
        {
            pushClaroMessage( $message, 'message' );
        }

        public static function debug( $message )
        {
            pushClaroMessage( $message, 'debug' );
        }
        
        public static function warning( $message )
        {
            pushClaroMessage( $message, 'warning' );
        }

        public static function info( $message )
        {
            pushClaroMessage( $message, 'info' );
        }

        public static function success( $message )
        {
            pushClaroMessage( $message, 'success' );
        }

        public static function error( $message )
        {
            claro_failure::set_failure( $message );
            pushClaroMessage( $message, 'error' );
        }
        
        public static function log( $message, $type )
        {
            pushClaroMessage( $message, $type );
        }
    }

    // class DebugBar extends Console {}
?>