<?php // $Id$

    // vim: expandtab sw=4 ts=4 sts=4:
    
    function img_get_extension( $imgPath )
    {
        $pathInfo = pathinfo( $imgPath );
        
        return strtolower( $pathInfo['extension'] );
    }
    
    function img_is_type_supported( $type )
    {
        $imgSupportedType = array( 'jpg', 'jpeg', 'gif', 'png', 'bmp' );
        return in_array( strtolower($type), $imgSupportedType );
    }
    
    class Thumbnailer
    {
        var $thumbnailDirectory;
        var $documentRootDir;
        
        function Thumbnailer( $thumbnailDirectory, $documentRootDir )
        {
            $this->thumbnailDirectory = $thumbnailDirectory;
            $this->documentRootDir = $documentRootDir;
        }
        
        function createThumbnail( $srcFile, $thumbHeight, $thumbWidth )
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
                    $image = imagecreatefrompng( $srcPath );
                } break;
                case 'jpg':
                case 'jpeg':
                {
                    $image = imagecreatefromjpeg( $srcPath );
                } break;
                case 'gif':
                {
                    $image = imagecreatefromgif( $srcPath );
                } break;
                case 'bmp':
                {
                    $image = imagecreatefromwbmp( $srcPath );
                } break;
                default:
                {
                    return false;
                }
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
        
        function getThumbnail( $imgPath, $newHeight, $newWidth )
        {
            $thumbName = md5($imgPath) . '_' . $newWidth . 'x' . $newHeight . '.jpg';
            $thumbPath = $this->thumbnailDirectory . '/' . $thumbName;
            
            
            if ( file_exists( $thumbPath ) )
            {
                return $thumbPath;
            }
            else
            {
                return $this->createThumbnail( $imgPath, $newHeight, $newWidth );
            }
        }
    }
?>