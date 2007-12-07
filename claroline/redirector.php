<?php // $Id$

//----------------------------------------------------------------------
// CLAROLINE 1.6
//----------------------------------------------------------------------
// Copyright (c) 2001-2004 Universite catholique de Louvain (UCL)
//----------------------------------------------------------------------
// This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
// as published by the FREE SOFTWARE FOUNDATION. The GPL is available 
// through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
//----------------------------------------------------------------------
// Authors: see 'credits' file
//----------------------------------------------------------------------

// duplicated from claro_main.lib.php to avoid loading unwanted functions.
function http_response_splitting_workaround( $str )
{
    $pattern = '~(\r\n|\r|\n|%0a|%0d|%0D|%0A)~';
    return preg_replace( $pattern, '', $str );
}

$url = isset( $_REQUEST['url'] )
    ? http_response_splitting_workaround( $_REQUEST['url'] )
    : '../'
    ;

header( 'Location: ' . $url );

?>
