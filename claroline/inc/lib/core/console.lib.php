<?php // $Id$

// vim: expandtab sw=4 ts=4 sts=4:

/**
 * Console and debug bar. Display a log message to the console and send it to
 * the Claroline log table.
 *
 * @version     Claroline 1.12 Revision: 12923 $
 * @copyright   (c) 2001-2014, Universite catholique de Louvain (UCL)
 * @author      Claroline Team <info@claroline.net>
 * @author      Frederic Minne <zefredz@claroline.net>
 * @license     http://www.gnu.org/copyleft/gpl.html
 *              GNU GENERAL PUBLIC LICENSE version 2 or later
 * @package     kernel.core
 */

require_once __DIR__ . '/debug.lib.php';

class Console
{
    const
        MESSAGE = 'message',
        DEBUG = 'debug',
        WARNING = 'warning',
        INFO = 'info',
        SUCCESS = 'success',
        ERROR = 'error';
    
    const
        REPORT_LEVEL_ERROR = 1,
        REPORT_LEVEL_WARNING = 2,
        REPORT_LEVEL_INFO = 3,
        REPORT_LEVEL_SUCCESS = 4,
        REPORT_LEVEL_ALL = 5;
    
    public static function message( $message )
    {
        self::_log( $message, self::MESSAGE, self::REPORT_LEVEL_ALL );
    }

    public static function debug( $message )
    {
        if ( claro_debug_mode() )
        {
            self::_log( $message, self::DEBUG, 0 );
        }
    }
    
    public static function warning( $message )
    {
        self::_log( $message, self::WARNING, self::REPORT_LEVEL_WARNING );
    }

    public static function info( $message )
    {
        self::_log( $message, self::INFO, self::REPORT_LEVEL_INFO );
    }

    public static function success( $message )
    {
        self::_log( $message, self::SUCCESS, self::REPORT_LEVEL_SUCCESS );
    }

    public static function error( $message )
    {
        self::_log( $message, self::ERROR, self::REPORT_LEVEL_ERROR );
    }
    
    public static function log( $message, $type )
    {
        self::_log( $message, $type, 0 );
    }
    
    protected static function _log( $message, $type, $logLevel = 0 )
    {
        /*$mustLogMessageInDatabase = ( get_conf( 'log_report_level', self::REPORT_LEVEL_ALL ) >= $logLevel ) ? true : false;
        
        if ( claro_debug_mode () )
        {
            $printDebugBacktrace = get_debug_print_backtrace();
            
            pushClaroMessage( $message . '<blockquote>' . nl2br(  $printDebugBacktrace ).'</blockquote>', $type );
            
            if ( $mustLogMessageInDatabase ) Claroline::getInstance()['logger']->log( $type, $message . "\n--\n" . $printDebugBacktrace );
        }
        else
        {
            if ( $mustLogMessageInDatabase ) Claroline::getInstance()['logger']->log( $type, $message );
        } */

        
        if ( claro_debug_mode () ) pushClaroMessage( $message, $type );
        
        if ( get_conf( 'log_report_level', self::REPORT_LEVEL_ALL ) >= $logLevel ) Claroline::getInstance()['logger']->log( $type, $message );
    }
}
