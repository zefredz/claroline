<?php // $Id$
    
    // vim: expandtab sw=4 ts=4 sts=4:
    
    class HTML_Sanitizer
    {
        function filterHTTPResponseSplitting( $url )
        {
            $dangerousCharactersPattern = '~(\r\n|\r|\n|%0a|%0d|%0D|%0A)~';
            return preg_replace( $dangerousCharactersPattern, '', $url );
        }

        function sanitizeURL( $url )
        {
            $url = HTML_Sanitizer::removeJavascriptURL( $url );
            $url = HTML_Sanitizer::filterHTTPResponseSplitting( $url );

            return $url;
        }

        function sanitizeHref( $str )
        {
            $HTML_Sanitizer_stripJavascriptURL = 'href="([^"]+)"';

            return preg_replace("/$HTML_Sanitizer_stripJavascriptURL/ie"
                , "'href=\"'.HTML_Sanitizer::sanitizeURL('\\1').'\"'"
                , $str );
        }

        function removeJavascriptURL( $str )
        {
            $HTML_Sanitizer_stripJavascriptURL = 'javascript:[^"]+';

            $str = preg_replace("/$HTML_Sanitizer_stripJavascriptURL/i"
                , ''
                , $str );

            return $str;
        }

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

        function sanitize( $html )
        {
            return HTML_Sanitizer::removeEvilTags( $html );
        }
    }
?>
