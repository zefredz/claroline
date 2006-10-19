<?php  // $Id$
    
    // vim: expandtab sw=4 ts=4 sts=4:
    
    /**
     * Get file MIME type from file name based on extension
     * @param string $fileName name of the file
     * @return string file MIME type
     * 
     */
    function get_mime_on_ext($fileName)
    {
        $mimeType = null;
    
        /*
         * Check if the file has an extension AND if the browser has send a MIME Type
         */
         
        $fileExtension = strtolower( pathinfo( $fileName, PATHINFO_EXTENSION ) );
        
        $defaultMimeType = 'document/unknown';
    
        if( $fileExtension )
        {
            /*
             * Build a "MIME-types / extensions" connection table
             */
    
            $mimeTypeList = array(
                'aif'   => 'audio/x-aiff',
                'avi'   => 'video/x-msvideo',
                'bmp'   => 'image/bmp',
                'doc'   => 'application/msword',
                'fla'   => 'application/octet-stream',
                'gif'   => 'image/gif',
                'gz'    => 'application/x-gzip',
                'htm'   => 'text/html',
                'html'  => 'text/html',
                'hqx'   => 'application/mac-binhex40',
                'jpg'   => 'image/jpeg',
                'jpeg'  => 'image/jpeg',
                'm3u'   => 'audio/x-mpegurl',
                'mid'   => 'audio/midi',
                'mov'   => 'video/quicktime',
                'mp3'   => 'audio/mpeg',
                'mp4'   => 'video/mp4',
                'mpg'   => 'video/mpeg',
                'mpeg'  => 'video/mpeg',
                'ogg'   => 'application/x-ogg',
                
                # Open Document Formats
                'odt'   => 'application/vnd.oasis.opendocument.text',
                'ott'   => 'application/vnd.oasis.opendocument.text-template',
                'oth'   => 'application/vnd.oasis.opendocument.text-web',
                'odm'   => 'application/vnd.oasis.opendocument.text-master',
                'odg'   => 'application/vnd.oasis.opendocument.graphics',
                'otg'   => 'application/vnd.oasis.opendocument.graphics-template',
                'odp'   => 'application/vnd.oasis.opendocument.presentation',
                'otp'   => 'application/vnd.oasis.opendocument.presentation-template',
                'ods'   => 'application/vnd.oasis.opendocument.spreadsheet',
                'ots'   => 'application/vnd.oasis.opendocument.spreadsheet-template',
                'odc'   => 'application/vnd.oasis.opendocument.chart',
                'odf'   => 'application/vnd.oasis.opendocument.formula',
                'odb'   => 'application/vnd.oasis.opendocument.database',
                'odi'   => 'application/vnd.oasis.opendocument.image',
                
                'pdf'   => 'application/pdf',
                'png'   => 'image/png',
                'ppt'   => 'application/vnd.ms-powerpoint',
                'pps'   => 'application/vnd.ms-powerpoint',
                'ps'    => 'application/postscript',
                'ra'    => 'audio/x-realaudio',
                'ram'   => 'audio/x-pn-realaudio',
                'rm'    => 'audio/x-pn-realaudio',
                'rpm'   => 'audio/x-pn-realaudio-plugin',
                'rtf'   => 'application/rtf',
                'sit'   => 'application/x-stuffit',
                'svg'   => 'image/svg+xml',
                'swf'   => 'application/x-shockwave-flash',
                
                # Open Office 1 Documents
                'sxw'   => 'application/vnd.sun.xml.writer',
                'stw'   => 'application/vnd.sun.xml.writer.template',
                'sxc'   => 'application/vnd.sun.xml.calc',
                'stc'   => 'application/vnd.sun.xml.calc.template',
                'sxd'   => 'application/vnd.sun.xml.draw',
                'std'   => 'application/vnd.sun.xml.draw.template',
                'sxi'   => 'application/vnd.sun.xml.impress',
                'sti'   => 'application/vnd.sun.xml.impress.template',
                'sxg'   => 'application/vnd.sun.xml.writer.global',
                'sxm'   => 'application/vnd.sun.xml.math',
                
                'tar'   => 'application/x-tar',
                'tex'   => 'application/x-tex',
                'tgz'   => 'application/x-gzip',
                'tif'   => 'image/tiff',
                'tiff'  => 'image/tiff',
                'txt'   => 'text/plain',
                'url'   => 'text/html',
                'wav'   => 'audio/x-wav',
                'wmv'   => 'video/x-ms-wmv',
                'xml'   =>'application/xml',
                'xls'   => 'application/vnd.ms-excel',
                'zip'   => 'application/zip',
            );
    
            $mimeType = array_key_exists( $fileExtension, $mimeTypeList )
                ? $mimeTypeList[$fileExtension]
                : $defaultMimeType
                ;
        }
        else
        {
            $mimeType = $defaultMimeType;
        }
    
        return $mimeType;
    }
    
    /**
     * Send a file over HTTP
     * @param   string $path file path
     * @param   string $name file name to force (optional)
     * @return  true on success,
     *          false if file not found or file empty, 
     *          set a claro_failure if file not found 
     */
    function claro_send_file( $path, $name = '' )
    {
        if ( file_exists( $path ) )
        {
            if ( empty( $name ) ) $name = basename( $path );
            
            $mimeType = get_mime_on_ext( $path );
        
            if ( ! is_null( $mimeType ) )
            {
                header( 'Content-Type: ' . $mimeType );
            }
            else
            {
                header( 'Content-Type: document/unknown' );
            }
                
            // IE no-cache bug
            
            // TODO move $lifetime to config
            $lifetime = 60;
            
            header( 'Cache-Control: max-age=' . $lifetime );
            header( 'Expires: ' . gmdate( 'D, d M Y H:i:s', time() + $lifetime ) .' GMT' );
            header( 'Pragma: ' );
            
            // force file name
            header('Content-Disposition: inline; filename="' . $name . '"');
            header('Content-Length: '. filesize( $path ) );
            
            return ( readfile( $path ) > 0 );
        }
        else
        {
            return claro_failure::set_failure( 'FILE_NOT_FOUND' );
        }
    }
    
    /**
     * Remove /.. ../ from file path
     * @param   string $path file path
     * @return  string, clean file path
     */
    function secure_file_path( $path )
    {
        return preg_replace( '~^(\.\.)$|(/\.\.)|(\.\./)~', '', $path );
    }
    
if ( ! function_exists( 'replace_dangerous_char' ) )
{
    /**
     * replaces some dangerous character in a file name
     *
     * @param   string $string
     * @param   string $strict (optional) removes also scores and simple quotes
     * @return  string : the string cleaned of dangerous character
     * @todo    TODO use boolean instead as string for the second parameter 
     *
     */
    function replace_dangerous_char($string, $strict = 'loose')
    {
        $search[] = ' ';  $replace[] = '_';
        $search[] = '/';  $replace[] = '-';
        $search[] = '\\'; $replace[] = '-';
        $search[] = '"';  $replace[] = '-';
        $search[] = '\'';  $replace[] = '_';
        $search[] = '?';  $replace[] = '-';
        $search[] = '*';  $replace[] = '-';
        $search[] = '>';  $replace[] = '';
        $search[] = '<';  $replace[] = '-';
        $search[] = '|';  $replace[] = '-';
        $search[] = ':';  $replace[] = '-';
        $search[] = '$';  $replace[] = '-';
        $search[] = '(';  $replace[] = '-';
        $search[] = ')';  $replace[] = '-';
        $search[] = '^';  $replace[] = '-';
        $search[] = '[';  $replace[] = '-';
        $search[] = ']';  $replace[] = '-';
        $search[] = '..';  $replace[] = '';
    
    
        foreach($search as $key=>$char )
        {
            $string = str_replace($char, $replace[$key], $string);
        }
        
        // TODO FIXME is this valid in all charsets ???
        if ($strict == 'strict')
        {
            $string = str_replace('-', '_', $string);
            $string = str_replace("'", '', $string);
            $string = strtr($string,
                            'ÀÁÂÃÄÅàáâãäåÒÓÔÕÖØòóôõöøÈÉÊËèéêëÇçÌÍÎÏìíîïÙÚÛÜùúûüÿÑñ',
                            'AAAAAAaaaaaaOOOOOOooooooEEEEeeeeCcIIIIiiiiUUUUuuuuyNn');
        }
    
        return $string;
    }
}
?>