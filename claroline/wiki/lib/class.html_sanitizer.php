<?php // $Id$
    
    // vim: expandtab sw=4 ts=4 sts=4:
    
    /**
     * CLAROLINE
     *
     * @version 1.8 $Revision$
     *
     * @copyright 2001-2006 Universite catholique de Louvain (UCL)
     *
     * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
     * This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
     * as published by the FREE SOFTWARE FOUNDATION. The GPL is available
     * through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
     *
     * @author Frederic Minne <zefredz@gmail.com>
     */
    
    /**
     * Sanitize HTML content
     */
    class HTML_Sanitizer
    {
        /**
         * Filter URLs to avoid HTTP response splitting attacks
         * @access  public
         * @param   string url
         * @return  string filtered url
         */
        function filterHTTPResponseSplitting( $url )
        {
            $dangerousCharactersPattern = '~(\r\n|\r|\n|%0a|%0d|%0D|%0A)~';
            return preg_replace( $dangerousCharactersPattern, '', $url );
        }
        
        /**
         * Remove potential javascript in urls
         * @access  public
         * @param   string url
         * @return  string filtered url
         */
        function removeJavascriptURL( $str )
        {
            $HTML_Sanitizer_stripJavascriptURL = 'javascript:[^"]+';

            $str = preg_replace("/$HTML_Sanitizer_stripJavascriptURL/i"
                , ''
                , $str );

            return $str;
        }
        
        /**
         * Remove potential flaws in urls
         * @access  private
         * @param   string url
         * @return  string filtered url
         */
        function sanitizeURL( $url )
        {
            $url = HTML_Sanitizer::removeJavascriptURL( $url );
            $url = HTML_Sanitizer::filterHTTPResponseSplitting( $url );

            return $url;
        }
        
        /**
         * Remove potential flaws in href attributes
         * @access  private
         * @param   string html tag
         * @return  string filtered html tag
         */
        function sanitizeHref( $str )
        {
            $HTML_Sanitizer_stripJavascriptURL = 'href="([^"]+)"';

            return preg_replace("/$HTML_Sanitizer_stripJavascriptURL/ie"
                , "'href=\"'.HTML_Sanitizer::sanitizeURL('\\1').'\"'"
                , $str );
        }
        
        /**
         * Remove dangerous attributes from html tags
         * @access  private
         * @param   string html tag
         * @return  string filtered html tag
         */
        function removeEvilAttributes( $str )
        {
            $str = preg_replace ( '/\s*=\s*/', '=', $str );

            $HTML_Sanitizer_stripAttrib = '(onclick|ondblclick|onmousedown|'
                . 'onmouseup|onmouseover|onmousemove|onmouseout|onkeypress|onkeydown|'
                . 'onkeyup)'
                ;

            $str = stripslashes( preg_replace("/$HTML_Sanitizer_stripAttrib/i"
                , 'forbidden'
                , $str ) );

            $str = HTML_Sanitizer::sanitizeHref( $str );

            return $str;
        }
        
        /**
         * Remove dangerous HTML tags
         * @access  private
         * @param   string html code
         * @return  string filtered url
         */
        function removeEvilTags( $str )
        {
            $HTML_Sanitizer_allowedTags = '<a><br><b><h1><h2><h3><h4><i>'
                . '<img><li><ol><p><strong><table><tr><td><th><u><ul><thead>'
                . '<tbody><em><dd><dt>'
                ;

            $str = strip_tags($str, $HTML_Sanitizer_allowedTags);

            return preg_replace('/<(.*?)>/ie'
                , "'<'.HTML_Sanitizer::removeEvilAttributes('\\1').'>'"
                , $str );
        }
        
        /**
         * Sanitize HTML
         *  remove dangerous tags and attributes
         *  clean urls
         * @access  public
         * @param   string html code
         * @return  string sanitized html code
         */
        function sanitize( $html )
        {
            return HTML_Sanitizer::removeEvilTags( $html );
        }
    }
?>
