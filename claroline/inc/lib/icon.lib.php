<?php  // $Id$
    
    // vim: expandtab sw=4 ts=4 sts=4:
    
    /**
     * @author  Frederic Minne <zefredz@claroline.net>
     * @copyright Copyright &copy; 2006-2007, Frederic Minne
     * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
     * @version 1.0
     * @package PlugIt
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