<?php  // $Id$
    
    // vim: expandtab sw=4 ts=4 sts=4:
    
    /**
     * Icon library
     *
     * @version     1.9 $Revision$
     * @copyright   2001-2007 Universite catholique de Louvain (UCL)
     * @author      Frederic Minne <zefredz@claroline.net>
     * @license     http://www.gnu.org/copyleft/gpl.html
     *              GNU GENERAL PUBLIC LICENSE
     * @package     KERNEL
     */

	/**
     * Returns the url of the given icon
     *
     * @param string fileName file name with or without extension
     * @return string icon url
     *         mixed null if icon not found
     */
	function get_icon( $fileName )
    {
        $fileInfo = pathinfo( $fileName );
        
        if ( !empty( $fileInfo['extension'] ) )
        {
            $imgPath = array(
                get_path('imgRepositorySys').$fileName => get_path('imgRepositoryWeb').$fileName,
                $GLOBALS['moduleImageRepositorySys'].'/'.$fileName => $GLOBALS['moduleImageRepositoryWeb'].'/'.$fileName,
            );
        }
        else
        {
            $imgPath = array(
                get_path('imgRepositorySys').$fileName.'.gif' => get_path('imgRepositoryWeb').$fileName.'.gif',
                get_path('imgRepositorySys').$fileName.'.png' => get_path('imgRepositoryWeb').$fileName.'.png',
                $GLOBALS['moduleImageRepositorySys'].'/'.$fileName.'.gif' => $GLOBALS['moduleImageRepositoryWeb'].'/'.$fileName.'.gif',
                $GLOBALS['moduleImageRepositorySys'].'/'.$fileName.'.png' => $GLOBALS['moduleImageRepositoryWeb'].'/'.$fileName.'.png',
            );
        }
        
        foreach ( $imgPath as $tryPath => $tryUrl )
        {
            if ( file_exists( $tryPath ) ) return $tryUrl;
        }
        
        pushClaroMessage("Icon $fileName not found",'error');
        
        return null;
    }
?>