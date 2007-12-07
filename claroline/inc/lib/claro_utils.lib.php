<?php // $Id$
//----------------------------------------------------------------------
// CLAROLINE
//----------------------------------------------------------------------
// Copyright (c) 2001-2005 Universite catholique de Louvain (UCL)
//----------------------------------------------------------------------
// This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
// as published by the FREE SOFTWARE FOUNDATION. The GPL is available
// through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
//----------------------------------------------------------------------
// Authors: see 'credits' file
//----------------------------------------------------------------------

	/**
    * cut string allowing word integrity preservation
    *
    * TODO : move to a more accurate library
    *
    * @see inc/lib/immage.lib.php#cutstring
    * @author Frederic Minne <minne@ipm.ucl.ac.be>
    * @param  string (string) string
    * @param  length (int) length of the resulting string
    * @param  allow_cut_word (boolean) allow word cutting default : TRUE
    * @param  extra_length (int) allow extra length to the string to
    *		preserve word integrity
    * @param  ending (string) append the given string at the end of the
	*		cutted one
    * @return (string) the cutted string
    */
    function cutstring( $str, $length, $allow_cut_word = TRUE, 
		$extra_length = 0, $ending = "" )
    {
        if( $allow_cut_word )
        {
            return substr( $str, 0, $length );
        }
        else
        {
            $words = preg_split( "~\s~", $str );
            
            $ret = "";
            
            foreach( $words as $word )
            {
                if( strlen( $ret . $word ) + 1 <= $length + $extra_length )
                {
                    $ret.= $word. " ";
                }
                else
                {
                    $ret = trim( $ret ) . $ending;
                    break;
                }
            }
            
            return $ret;
        }
    }
    
    /**
    * list the property of a course
    *
    * @return $array a array  
    */
    function stripstresses( $str )
    {
    	$str = strtolower( $str );
        $ret = "";
        for( $i = 0; $i < strlen( $str ); $i++ )
        {
        	$chr = substr( $str, $i, 1 );
            $val = ord( $chr );
            if ( $val >= 224 )
            {
                if ( $val >= 224 && $val <= 229 )
                {
                	$chr = 'a';
                }
                if ( $val == 231 )
                {
                    $chr = 'c';
                }
                if ( $val >= 232 && $val <= 235 )
                {
                    $chr = 'e';
                }
                if ( $val >= 236 && $val <= 239 )
                {
                    $chr = 'i';
                }
                if ( $val == 240 || ( $val >= 242 && $val <= 246 ) )
                {
                    $chr = 'o';
                }
                if ( $val == 241 )
                {
                    $chr = 'n';
                }
                if ( $val >= 249 && $val <= 252 )
                {
                    $chr = 'u';
                }
                if ( $val == 253 || $val == 255 )
                {
                    $chr = 'y';
                }
            };
              
            $ret .= $chr;
        }
        return( $ret );
	}
?>
