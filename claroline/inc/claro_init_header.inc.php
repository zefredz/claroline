<?php // $Id$

/**
 * CLAROLINE
 *
 * @version     1.9 $Revision$
 * @copyright   2001-2008 Universite catholique de Louvain (UCL)
 * @license     http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @package     CLKERNEL
 * @author      Claro Team <cvs@claroline.net>
 */
 

if ( count( get_included_files() ) == 1 )
{
    die( 'The file ' . basename(__FILE__) . ' cannot be accessed directly, use include instead' );
}

// this file can be called from within a function so we need to add the
// folowwing line !!!
$claroline = Claroline::getInstance();

echo $claroline->display->header->render();

echo '<body dir="' . $text_dir . '" '
.    ( isset( $claroBodyOnload ) ? ' onload="' . implode('', $claroBodyOnload ) . '" ':'')
.    '>'

;

//  Banner

if ( isset($hide_banner) && $hide_banner )
{
    $claroline->display->banner->hide();
}

if ( ! get_conf('claro_brailleViewMode',false))
{
    echo $claroline->display->banner->render();
}

if (!isset($hide_body) || $hide_body == false)
{
    // need body div
    echo "\n\n\n"
    .    '<!-- - - - - - - - - - - Claroline Body - - - - - - - - - -->' . "\n"
    .    '<div id="claroBody">' . "\n\n"
    ;
}
