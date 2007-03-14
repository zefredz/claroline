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
        
        $imgPath = array(
            get_path('imgRepositorySys') => get_path('imgRepositoryWeb'),
            get_module_path(get_current_module_label()).'/' => get_module_url(get_current_module_label()).'/',
            './' => './',
            './img/' => './img/'
        );
        
        if ( !empty( $fileInfo['extension'] ) )
        {
            $img = array( $fileName );
        }
        else
        {
            $img = array(
                $fileName . '.gif',
                $fileName . '.png'
            );
        }
        
        foreach ( $imgPath as $tryPath => $tryUrl )
        {
            foreach ( $img as $tryImg )
            {
                if ( file_exists( $tryPath.$tryImg ) ) return $tryUrl.$tryImg;
            }
        }
        
        pushClaroMessage("Icon $fileName not found",'error');
        
        return null;
    }
?>