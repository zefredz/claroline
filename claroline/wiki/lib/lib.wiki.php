<?php // $Id$

    // vim: expandtab sw=4 ts=4 sts=4:
    
    if( (bool) stristr( $_SERVER['PHP_SELF'], basename(__FILE__) ) )
    {
        die("This file cannot be accessed directly! Include it in your script instead!");
    }
    
    /**
     * CLAROLINE
     *
     * @version 1.7 $Revision$
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
     
    /**
     * Get wiki2xhtml demo text
     * @return string wiki2xhtml demo text
     * @todo move to a lang file
     */
    function get_demo_text()
    {
        $file = file( dirname(__FILE__) . '/wiki2xhtml/texte-demo.txt' );

        $text = str_replace( "\r\n", "", implode( '\n', array_map( 'addslashes', $file ) ));
        $text = str_replace( "\n", "", $text );
        $text = str_replace( "\r", "", $text );

        return $text;
    }
?>