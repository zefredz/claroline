<?php // $Id$
/**
 * CLAROLINE
 *
 * @version 1.9 $Revision$
 *
 * @copyright 2001-2007 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @package CLKERNEL
 *
 * @author Claro Team <cvs@claroline.net>
 *
 */

if ( count( get_included_files() ) == 1 ) die( '---' );
// Start the output, no outpout would be done before call of this file
claro_send_http_headers();
/*
* HTML HEADER
*/
echo claro_html_doctype() . "\n"
.    '<html>' . "\n"
.    claro_html_headers() . "\n"

.    '<body dir="' . $text_dir . '" '
.    ( isset( $claroBodyOnload ) ? ' onload="' . implode('', $claroBodyOnload ) . '" ':'')
.    '>'

;

//  Banner

if (!isset($hide_banner) || false == $hide_banner)
{
    $clarolineBannerOutput = claro_html_banner();

    if ( get_conf('claro_brailleViewMode',false))
    {
        $claro_banner = $clarolineBannerOutput;
    }
    else
    {
        echo $clarolineBannerOutput;
        $claro_banner = false;
    }

}

if (!isset($hide_body) || $hide_body == false)
{
    // need body div
    echo "\n\n\n"
    .    '<!-- - - - - - - - - - - Claroline Body - - - - - - - - - -->' . "\n"
    .    '<div id="claroBody">' . "\n\n"
    ;
}
?>