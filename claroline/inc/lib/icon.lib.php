<?php  // $Id$
    
    // vim: expandtab sw=4 ts=4 sts=4:
    
    if ( count( get_included_files() ) == 1 )
    {
        die( 'The file ' . basename(__FILE__) . ' cannot be accessed directly, use include instead' );
    }
    
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
     * Returns the (system) path to the current iconset
     */
    function get_current_iconset_path()
    {
        return get_path('imgRepositorySys');
    }
    
    /**
     * Returns the (web) url to the current iconset
     */
    function get_current_iconset_url()
    {
         return get_path('imgRepositoryWeb');
    }
    

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
            // claroline theme iconset
            get_current_iconset_path() => get_current_iconset_url(),
            // module img directory
            get_module_path(get_current_module_label()).'/img/' => get_module_url(get_current_module_label()).'/img/',
            // module root directory
            get_module_path(get_current_module_label()).'/' => get_module_url(get_current_module_label()).'/',
            // img directory in working directory
            './img/' => './img/',
            // working directory
            './' => './',
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
        
        if ( claro_debug_mode() ) pushClaroMessage("Icon $fileName not found",'error');
        
        return null;
    }
    
    /**
     * Includes an icon in html code
     * @param string fileName file name with or without extension
     * @param string toolTip tooltip for the image (optional, default none)
     * @param string alternate alt text for the image (optional, default fileName)
     * @return string html code for the image
     */
    function claro_html_icon( $fileName, $toolTip = null, $alternate = null )
    {
        $alt = $alternate
            ? ' alt="' . $alternate . '"'
            : ' alt="' . htmlspecialchars( $fileName ) . '"'
            ;
            
        $title = $toolTip
            ? ' title="' . htmlspecialchars( $toolTip ) .'"'
            : ''
            ;
            
        if ( false !== ( $iconUrl = get_icon( $fileName ) ) )
        {
            return '<img src="' . $iconUrl .'"'
                . $alt . $title
                . ' />'
                ;
        }
        else
        {
            return false;
        }
    }
?>