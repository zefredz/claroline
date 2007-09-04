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

    uses('core/claroline.lib');

    $claroline = Claroline::getInstance();

    echo $claroline->display->header->render();

    echo '<body dir="' . $text_dir . '" '
    .    ( isset( $claroBodyOnload ) ? ' onload="' . implode('', $claroBodyOnload ) . '" ':'')
    .    '>'

    ;

    //  Banner

    if (!isset($hide_banner) || false == $hide_banner)
    {
        if ( ! get_conf('claro_brailleViewMode',false))
        {
            echo $claroline->display->banner->render();
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