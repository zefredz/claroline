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
            'mid'   => 'audio/midi',
            'mov'   => 'video/quicktime',
            'mp3'   => 'audio/mpeg',
            'mpg'   => 'video/mpeg',
            'mpeg'  => 'video/mpeg',
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
            'rm'    => 'audio/x-pn-realaudio',
            'rpm'   => 'audio/x-pn-realaudio-plugin',
            'rtf'   => 'application/rtf',
            'sit'   => 'application/x-stuffit',
            'svg'   => 'image/svg+xml',
            'swf'   => 'application/x-shockwave-flash',
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
?>
