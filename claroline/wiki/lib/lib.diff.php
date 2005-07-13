<?php // $Id$

    // vim: expandtab sw=4 ts=4 sts=4:

    if( (bool) stristr( $_SERVER['PHP_SELF'], basename(__FILE__) ) )
    {
        die("This file cannot be accessed directly! Include it in your script instead!");
    }
    
    define( "DIFF_EQUAL", "=" );
    define( "DIFF_ADDED", "+" );
    define( "DIFF_DELETED", "-" );
    
    /**
     * @version CLAROLINE 1.7
     *
     * @copyright 2001-2005 Universite catholique de Louvain (UCL)
     *
     * @license GENERAL PUBLIC LICENSE (GPL)
     * This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
     * as published by the FREE SOFTWARE FOUNDATION. The GPL is available
     * through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
     *
     * @author Frederic Minne <zefredz@gmail.com>
     *
     * @package Wiki
     */
    
    function diff( $old, $new, $show_equals = false )
    {
        $oldArr = str_split( $old );
        $newArr = str_split( $new );
        
        $oldCount = count ( $oldArr );
        $newCount = count ( $newArr );
        
        $max = max( $oldCount, $newCount );
        
        //get added and deleted lines
        
        $deleted = array_diff_assoc( $oldArr, $newArr );
        $added = array_diff_assoc( $newArr, $oldArr );
        
        $output = '';
        
        for ( $i = 0; $i < $max; $i++ )
        {
            // line changed
            if ( isset ( $deleted[$i] ) && isset( $added[$i] ) )
            {
                $output .= format_line( $i, DIFF_DELETED, $deleted[$i] );
                $output .= format_line( $i, DIFF_ADDED, $added[$i] );
            }
            // line deleted
            elseif ( isset ( $deleted[$i] ) && ! isset ( $added[$i] ) )
            {
                $output .= format_line( $i, DIFF_DELETED, $deleted[$i] );
            }
            // line added
            elseif ( isset ( $added[$i] ) && ! isset ( $deleted[$i] ) )
            {
                $output .= format_line( $i, DIFF_ADDED, $added[$i] );
            }
            // line unchanged
            elseif ( $show_equals == true )
            {
                $output .= format_line( $i, DIFF_EQUAL, $newArr[$i] );
            }
            else
            {
                // skip
            }
        }
        
        return $output;
    }
    
    function str_split( $str )
    {
        $content = array();

        if ( strpos( $str, "\r\n" ) != false )
        {
            $content = explode( "\r\n", $str );
        }
        elseif ( strpos( $str, "\n" ) != false )
        {
            $content = explode( "\n", $str );
        }
        elseif ( strpos( $str, "\r" ) != false )
        {
            $content = explode( "\r", $str );
        }
        else
        {
            $content[] = $str;
        }

        return $content;
    }
    
    function format_line( $line, $type, $value, $skip_empty = false )  #change to $value if problems
    {
        if ( trim( $value ) == "" && $skip_empty )
        {
            return "";
        }
        elseif ( trim( $value ) == "" )
        {
            $value = '&nbsp;';
        }

        switch ( $type )
        {
            case DIFF_EQUAL:
            {
                return $line. ' : '
                    . ' = <span class="diffEqual" >'
                    . $value
                    . '</span><br />' . "\n"
                    ;

                break;
            }
            case DIFF_ADDED:
            {
                return $line . ' : '
                    . ' + <span class="diffAdded" >'
                    . $value
                    . '</span><br />' . "\n"
                    ;

                break;
            }
            case DIFF_DELETED:
            {
                return $line . ' : '
                    . ' - <span class="diffDeleted" >'
                    . $value
                    . '</span><br />' . "\n"
                    ;

                break;
            }
        }
    }
    
    /**
 * Replace array_diff_assoc()
 *
 * @category    PHP
 * @package     PHP_Compat
 * @link        http://php.net/function.array_diff_assoc
 * @author      Aidan Lister <aidan@php.net>
 * @version     $Revision$
 * @since       PHP 4.3.0
 * @require     PHP 4.0.0 (user_error)
 */
if ( ! function_exists('array_diff_assoc') )
{
    function array_diff_assoc()
    {
        // Check we have enough arguments
        $args = func_get_args();
        $count = count( $args );
        if ( count( $args ) < 2 )
        {
            trigger_error( 'Wrong parameter count for array_diff_assoc()', E_USER_WARNING );
            return;
        }

        // Check arrays
        for ( $i = 0; $i < $count; $i++ )
        {
            if ( ! is_array( $args[$i] ) )
            {
                trigger_error( 'array_diff_assoc() Argument #' .
                    ($i + 1) . ' is not an array', E_USER_WARNING );
                return;
            }
        }

        // Get the comparison array
        $array_comp = array_shift( $args );
        --$count;

        // Traverse values of the first array
        foreach ( $array_comp as $key => $value )
        {
            // Loop through the other arrays
            for ( $i = 0; $i < $count; $i++ )
            {
                // Loop through this arrays key/value pairs and compare
                foreach ( $args[$i] as $comp_key => $comp_value )
                {
                    if ( (string) $key === (string)$comp_key &&
                        (string) $value === (string) $comp_value )
                    {

                        unset( $array_comp[$key] );
                    }
                }
            }
        }

        return $array_comp;
    }
}
?>