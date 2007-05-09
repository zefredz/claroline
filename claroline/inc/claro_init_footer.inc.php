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

if (!isset($hide_body) || $hide_body == false)
{
    echo "\n" . '</div>' . "\n"
    .    '<!-- - - - - - - - - - -   End of Claroline Body   - - - - - - - - - - -->' . "\n\n\n"
    ;
}

// depends on claro_brailleViewMode (in config)
if ( isset($claro_banner) )
{
    echo $claro_banner;
}

// don't display the footer text if requested, only display minimal html closing tags
if (!isset($hide_footer) || $hide_footer == false)
{
    echo claro_html_footer();
} // if (!isset($hide_footer) || $hide_footer == false)


if (get_conf('CLARO_DEBUG_MODE',false))
{
    echo  claro_disp_debug_banner() .  "\n" ;
}

echo '</body>' . "\n"
.    '</html>' . "\n"
;

?>
