<?php // $Id$
if ( count( get_included_files() ) == 1 ) die( '---' );

/* --------------------------------------
 * HEADERS SECTION
 * --------------------------------------*/

/*
 * HTTP HEADER
 */

claro_send_http_headers();

/*
* HTML HEADER
*/
echo claro_html_doctype() . "\n"
.    '<html>' . "\n"
.    claro_html_headers() . "\n"
;

if ( isset( $claroBodyOnload ) )
{
    echo '<body dir="' . $text_dir . '" onload="' . implode('', $claroBodyOnload ) . '">';
}
else
{
    echo '<body dir="' . $text_dir . '">';
}

//  Banner

if (!isset($hide_banner) || false == $hide_banner)
{
    $clarolineBannerOutput .= claro_html_banner();

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