<?php // $Id$

    // vim: expandtab sw=4 ts=4 sts=4:

    if ( count( get_included_files() ) == 1 )
    {
        die( 'The file ' . basename(__FILE__) . ' cannot be accessed directly, use include instead' );
    }

    /**
     * Helper functions and classes
     *
     * @version     1.9 $Revision$
     * @copyright   2001-2007 Universite catholique de Louvain (UCL)
     * @author      Claroline Team <info@claroline.net>
     * @author      Frederic Minne <zefredz@claroline.net>
     * @license     http://www.gnu.org/copyleft/gpl.html
     *              GNU GENERAL PUBLIC LICENSE version 2.0
     * @package     CORE
     */

    uses ( 'core/url.lib' );

    /**
     * Create an html attribute list from an associative array attribute=>value
     * @param   array $attributes
     * @return  string
     */
    function make_attribute_list( $attributes )
    {
        $attribList = '';
        
        if ( is_array( $attributes ) && !empty( $attributes ) )
        {
            foreach ( $attributes as $attrib => $value )
            {
                $attribList .= ' ' . $attrib . '="'
                    . htmlspecialchars($value) . '"'
                    ;
            }
        }
        
        return $attribList;
    }
     
    /**
     * Create an html link to the given url with the given text and attributes
     * @param   string text
     * @param   string url
     * @param   array attributes (optional)
     * @return  string
     */
    function link_to ( $text, $url, $attributes = null )
    {
        $url = htmlspecialchars_decode( $url );
        
        $link = '<a href="'
            . htmlspecialchars( $url ) . '"'
            . make_attribute_list( $attributes )
            . '>' . htmlspecialchars( $text ) . '</a>'
            ;
            
        return $link;
    }
    
    /**
     * Create an html link to the given url inside claroline with the given
     * text and attributes
     * @param   string text
     * @param   string url inside claroline
     * @param   array attributes (optional)
     * @return  string
     */
    function link_to_claro ( $text, $url, $attributes = null )
    {
        $urlObj = new Url( $url );
        $urlObj->relayCurrentContext();
        
        $url = $urlObj->toUrl();
        
        return link_to ( $text, $url, $attributes );
    }
?>