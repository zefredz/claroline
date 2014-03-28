<?php // $Id$

// vim: expandtab sw=4 ts=4 sts=4:

/**
 * Thumbnails library
 *
 * @version     1.9 $Revision$
 * @copyright   (c) 2001-2014, Universite catholique de Louvain (UCL)
 * @author      Claroline Team <info@claroline.net>
 * @license     http://www.gnu.org/copyleft/gpl.html
 *              GNU GENERAL PUBLIC LICENSE version 2 or later
 * @package     kernel.file
 * @todo        phpdoc
 */

/**
 * Get image extension from file pathname
 * @param string $imgPath
 * @return string
 */
function img_get_extension( $imgPath )
{
    $pathInfo = pathinfo( $imgPath );
    
    return strtolower( $pathInfo['extension'] );
}

/**
 * Check if the type (extension) of the image is supported by the platform
 * @param string $type
 * @return boolean
 */
function img_is_type_supported( $type )
{
    $imgSupportedType = array( 'jpg', 'jpeg', 'gif', 'png', 'bmp' );
    return in_array( strtolower($type), $imgSupportedType );
}

/**
 * Thumbnails generator
 */
class Thumbnailer
{
    protected $thumbnailDirectory;
    protected $documentRootDir;
    
    /**
     * Constructor
     * @param string $thumbnailDirectory folder in which the thumbnails will be stored
     * @param string $documentRootDir folder in which the original images are located
     */
    public function __construct( $thumbnailDirectory, $documentRootDir )
    {
        $this->thumbnailDirectory = $thumbnailDirectory;
        $this->documentRootDir = $documentRootDir;
    }
    
    /**
     * Create a thumbnail from an original image
     * @param string $srcFile path of the image relative to $documentRootDir
     * @param int $thumbHeight height of the thumbnail
     * @param int $thumbWidth width of the thumbnail
     * @return string|boolean path of the thumbnail or false
     */
    public function createThumbnail( $srcFile, $thumbHeight, $thumbWidth )
    {
        $srcPath = $this->documentRootDir . '/' . $srcFile;
        
        if ( ! function_exists( 'gd_info' ) )
        {
            return $srcPath;
        }
        
        $type = img_get_extension( $srcFile );
        
        if ( ! file_exists( $this->thumbnailDirectory ) )
        {
            claro_mkdir( $this->thumbnailDirectory, CLARO_FILE_PERMISSIONS, true );
        }
        
        if ( ! img_is_type_supported( $type ) )
        {
            return false;
        }
        
        switch ( $type )
        {
            case 'png':
            {
                $image = @imagecreatefrompng( $srcPath );
            } break;
            case 'jpg':
            case 'jpeg':
            {
                $image = @imagecreatefromjpeg( $srcPath );
            } break;
            case 'gif':
            {
                $image = @imagecreatefromgif( $srcPath );
            } break;
            case 'bmp':
            {
                $image = @imagecreatefromwbmp( $srcPath );
            } break;
            default:
            {
                return false;
            }
        }
        
        // image loading failed use srcPath instead
        if ( ! $image )
        {
            Console::warning("Failed to create GD image from {$srcPath}");
            return $srcPath;
        }

        $oldWidth = imageSX( $image );
        $oldHeight = imageSY( $image );
        
        $thumbnail = imagecreatetruecolor( $thumbWidth, $thumbHeight );

        imagecopyresampled( $thumbnail, $image
            , 0,0,0,0, $thumbWidth, $thumbHeight, $oldWidth, $oldHeight );

        $thumbName = md5($srcFile) . '_' . $thumbWidth . 'x' . $thumbHeight . '.jpg';
        $thumbPath = $this->thumbnailDirectory . '/' . $thumbName;

        imagejpeg( $thumbnail, $thumbPath );
        
        imagedestroy($image);
        imagedestroy($thumbnail);
        
        return $thumbPath;
    }
    
    /**
     * Get the path of the thumbnail for the given original image. If the 
     * thumbnail does not exists or if the original image has changed since the
     * creation of the thumbnail, the will be (re)generated
     * @param string $imgPath path of the original image relative to $documentRootDir
     * @param int $newHeight height of the thumbnail
     * @param int $newWidth width of the thumbnail
     * @return string path of the thumbnail
     */
    public function getThumbnail( $imgPath, $newHeight, $newWidth )
    {
        $thumbName = md5($imgPath) . '_' . $newWidth . 'x' . $newHeight . '.jpg';
        $thumbPath = $this->thumbnailDirectory . '/' . $thumbName;
        
        
        if ( file_exists( $thumbPath )
            && filectime($this->documentRootDir . '/' . $imgPath) < filectime($thumbPath)
            && filemtime($this->documentRootDir . '/' . $imgPath) < filemtime($thumbPath) )
        {
            return $thumbPath;
        }
        else
        {
            if ( claro_debug_mode() )
            {
                Console::debug("Regenerating thumbnail for {$imgPath}");
            }
            
            return $this->createThumbnail( $imgPath, $newHeight, $newWidth );
        }
    }
}
