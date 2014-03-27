<?php // $Id$

// vim: expandtab sw=4 ts=4 sts=4:

/**
 * Time library
 *
 * @version     Claroline 1.12 $Revision$
 * @copyright   (c) 2001-2014, Universite catholique de Louvain (UCL)
 * @author      Claroline Team <info@claroline.net>
 * @author      Frederic Minne <zefredz@claroline.net>
 * @license     http://www.gnu.org/copyleft/gpl.html
 *              GNU GENERAL PUBLIC LICENSE version 2 or later
 * @package     kernel.utils
 */

/**
 * Time utility methods
 */
class Claro_Utils_Time
{
    /**
     * Check if date is compatible with iso-8601 format
     * @param string $dateStr
     * @return boolean
     */
    public static function isIso8601( $dateStr )
    {
        return preg_match( '/\d{4}-\d{2}-\d{2}T\d{2}\:\d{2}\:\d{2}\+\d{2}\:\d{2}/i', $dateStr );
    }
    
    /**
     * Convert iso-8601 string to mysql datetime format
     * @param string $iso8601Str
     * @return string|boolean
     */
    public static function iso8601ToDatetime( $iso8601Str )
    {
        if ( ! self::isIso8601( $iso8601Str ) )
        {
            return false;
        }
        
        return preg_replace( '/(\d{4}-\d{2}-\d{2})T(\d{2}\:\d{2}\:\d{2})\+\d{2}\:\d{2}/i', "$1 $2", $iso8601Str ) ;
    }
    
    /**
     * Convert unix time to iso-8601
     * @param int $time
     * @return string
     */
    public static function timeToIso8601( $time = null )
    {
        if ( is_null( $time ) ) $time = time();

        return (date('c') == 'c') ? date('Y-m-d\TH:i:sO',$time) : date('c', $time );
    }
    
    /**
     * Convert datetime to iso-8601
     * @param string $date
     * @return string
     */
    public static function dateToIso8601( $date = null )
    {
        $time = is_null( $date )
            ? time()
            : strtotime( $date )
            ;
        
        return self::timeToIso8601( $time );
    }
    
    /**
     * Convert unix time to datetime
     * @param int $time
     * @return string
     */
    public static function timeToDatetime( $time = null )
    {
        if ( $time )
        {
            return date( "Y-m-d H:i:s", $time );
        }
        else
        {
            return date( "Y-m-d H:i:s" );
        }
    }
    
    /**
     * Convert date to a datetime
     * @param string $date
     * @return string
     */
    public static function dateToDatetime( $date = null)
    {
        $time = is_null( $date )
            ? time()
            : strtotime( $date )
            ;

        return date('Y-m-d H:i:s',$time);
    }
}
