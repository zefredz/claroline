<?php // $Id$
if ( count( get_included_files() ) == 1 ) die( '---' );

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
?>