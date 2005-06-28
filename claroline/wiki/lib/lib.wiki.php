<?php // $Id$

    // vim: expandtab sw=4 ts=4 sts=4:
    
    if( strtolower( basename( $_SERVER['PHP_SELF'] ) )
        == strtolower( basename( __FILE__ ) ) )
    {
        die("This file cannot be accessed directly! Include it in your script instead!");
    }
    
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

    // temporary functions should be replaced by a lang var/file
    function get_demo_text()
    {
        $file = file( dirname(__FILE__) . '/wiki2xhtml/texte-demo.txt' );

        $text = str_replace( "\r\n", "", implode( '\n', array_map( 'addslashes', $file ) ));
        $text = str_replace( "\n", "", $text );
        $text = str_replace( "\r", "", $text );

        return $text;
    }
    
    // ------------ ClaroWiki Diff functions ---------
    
    // require generic diff functions from libDiff
    require_once dirname(__FILE__) . "/lib.diff.php";
    
    function format_wiki_diff_line( $nr1, $nr2, $stat, &$value )  #change to $value if problems
    {
        if ( trim( $value ) == "" )
        {
            return "";
        }

        switch ( $stat )
        {
            case "=":
                return $value;
            break;

            case "+":
                return "++" . $value . "++";
            break;

            case "-":
                return "--" . $value . "--";
            break;
        }
    }
    
    function wiki_diff( $str1, $str2 )
    {
        $str1Array = explode_wiki_content( $str1 );
        $str2Array = explode_wiki_content( $str2 );
        
        $diff = arr_diff( $f1 , $f2 , 1, 'format_wiki_diff_line' );
        
        return $diff;
    }
    
    function explode_wiki_content( $str )
    {
        $a = explode( "\n", $str );
        $b = explode( "\r\n", $str );
        
        if( count( $a ) > count( $b ) )
        {
            return $a;
        }
        else
        {
            return $b;
        }
    }
?>