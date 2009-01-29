<?php // $Id$

// vim: expandtab sw=4 ts=4 sts=4:

/**
 * Loader classes for CSS and Javascript
 *
 * @version     1.9 $Revision$
 * @copyright   2001-2008 Universite catholique de Louvain (UCL)
 * @author      Claroline Team <info@claroline.net>
 * @author      Frederic Minne <zefredz@claroline.net>
 * @license     http://www.gnu.org/copyleft/gpl.html
 *              GNU GENERAL PUBLIC LICENSE version 2 or later
 * @package     KERNEL
 */

if ( count( get_included_files() ) == 1 )
{
    die( 'The file ' . basename(__FILE__) . ' cannot be accessed directly, use include instead' );
}

FromKernel::uses('file.lib');

/**
 * Javascript loader singleton class
 */
class JavascriptLoader
{
    private static $instance = false;

    private $libraries, $pathList;

    private function __construct()
    {
        $this->libraries = array();
        $this->pathList = array(
            get_module_path( get_module_path(get_current_module_label()) ) . '/js' => get_module_url( get_current_module_label() ) . '/js',
            get_path( 'rootSys' ) . 'web/js' => get_path('url') . '/web/js',
            './js' => './js'
        );
    }

    public function getLibraries()
    {
        return $this->libraries;
    }

    public function loadedLibraries()
    {
        return array_keys( $this->libraries );
    }

    /**
     * Load a javascript source file
     * @param   string lib javascript lib url relative to one of the
     *  declared javascript paths
     * @return  boolean true if the library was found, false else
     */
    public function load( $lib )
    {
        $lib = secure_file_path( $lib );
        
        foreach ( $this->pathList as $tryPath => $tryUrl )
        {
            if ( claro_debug_mode() )
            {
                pushClaroMessage(__Class__."::Try to find {$lib} in {$tryPath}", 'debug');
            }

            if ( file_exists ( $tryPath . '/' . $lib . '.js' ) )
            {
                if ( array_key_exists( $tryPath . '/' . $lib . '.js', $this->libraries ) )
                {
                    return false;
                }
                
                if ( get_conf('javascriptCompression', true)
                    && file_exists( $tryPath . '/min/' . $lib . '.js' )  )
                {    
                    $this->libraries[$tryPath . '/' . $lib . '.js'] = $tryUrl . '/min/' . $lib . '.js';
                    
                    if ( claro_debug_mode() )
                    {
                        pushClaroMessage(__Class__."::Use ".$tryPath.'/min/' .$lib.'.js', 'debug');
                    }
                }
                else
                {
                    $this->libraries[$tryPath . '/' . $lib . '.js'] = $tryUrl . '/' . $lib . '.js';
                    
                    if ( claro_debug_mode() )
                    {
                        pushClaroMessage(__Class__."::Use ".$tryPath.'/' .$lib.'.js', 'debug');
                    }
                }
                
                ClaroHeader::getInstance()->addHtmlHeader(
                    '<script src="'.$this->libraries[$tryPath . '/' . $lib . '.js'].'" type="text/javascript"></script>'
                );
                
                return true;
            }
        }

        if ( claro_debug_mode() )
        {
            pushClaroMessage(__Class__."::NotFound ".$lib.'.js', 'error');
        }

        return false;
    }
    
    public function loadFromModule( $moduleLabel, $lib )
    {
        $lib = secure_file_path( $lib );
        
        if ( claro_debug_mode() )
        {
            pushClaroMessage(__Class__."::Try to find {$lib} in {$moduleLabel}", 'debug');
        }
        
        $path = get_module_path( $moduleLabel ) . '/js/' . $lib . '.js';
        $url = get_module_url( $moduleLabel ) . '/js/' . $lib . '.js';
        
        if ( file_exists( $path ) )
        {
            if ( array_key_exists( $path, $this->libraries ) )
            {
                return false;
            }
            
            $this->libraries[$path] = $url;
            
            if ( claro_debug_mode() )
            {
                pushClaroMessage(__Class__."::Use {$path}::{$url}", 'debug');
            }
            
            ClaroHeader::getInstance()->addHtmlHeader(
                '<script src="'.$url.'" type="text/javascript"></script>'
            );
            
            return true;
        }
        else
        {
            if ( claro_debug_mode() )
            {
                pushClaroMessage(__Class__."::Cannot found {$lib} in {$moduleLabel}", 'error');
            }
            
            return false;
        }
    }

    public static function getInstance()
    {
        if ( ! JavascriptLoader::$instance )
        {
            JavascriptLoader::$instance = new JavascriptLoader;
        }

        return JavascriptLoader::$instance;
    }
}

class CssLoader
{
    private static $instance = false;

    private $css, $pathList;

    private function __construct()
    {
        $this->css = array();
        $this->pathList = array(
            get_module_path( get_current_module_label() ) . '/css' => get_module_url( get_current_module_label() ) . '/css',
            get_path( 'rootSys' ) . 'web/css' => get_path('url') . '/web/css',
            get_path( 'rootSys' ) . 'claroline/css' => get_path('url') . '/claroline/css', // this stay there for legacy but should be removed.
            './css' => './css'
        );
    }

    public function getCss()
    {
        return $this->css;
    }

    public function loadedCss()
    {
        return array_keys( $this->css );
    }

    /**
     * Load a css file
     * @param   string lib css file url relative to one of the
     *  declared css paths
     * @return  boolean true if the library was found, false else
     */
    public function load( $css, $media = 'all' )
    {
        $css = secure_file_path( $css );

        foreach ( $this->pathList as $tryPath => $tryUrl )
        {
            if ( claro_debug_mode() )
            {
                pushClaroMessage(__Class__."::Try ".$tryPath.'/'.$css.'.css', 'debug');
            }

            if ( file_exists ( $tryPath . '/' . $css . '.css' ) )
            {
                if ( array_key_exists( $tryPath . '/' . $css . '.css', $this->css ) )
                {
                    return false;
                }
                
                if ( claro_debug_mode() )
                {
                    pushClaroMessage(__Class__."::Use ".$tryPath.'/'.$css.'.css', 'debug');
                }

                $this->css[$tryPath . '/' . $css . '.css'] = array(
                    'url' => $tryUrl . '/' . $css . '.css',
                    'media' => $media
                );
                
                ClaroHeader::getInstance()->addHtmlHeader(
                    '<link rel="stylesheet" type="text/css"'
                    . ' href="'. $this->css[$tryPath . '/' . $css . '.css']['url'].'"'
                    . ' media="'.$this->css[$tryPath . '/' . $css . '.css']['media'].'" />'
                );

                return true;
                // break;
            }
        }

        if ( claro_debug_mode() )
        {
            pushClaroMessage(__Class__."::NotFound ".$css.'.css', 'error');
        }

        return false;
    }
    
    public function loadFromModule( $moduleLabel, $lib, $media = 'all' )
    {
        $lib = secure_file_path( $lib );
        
        if ( claro_debug_mode() )
        {
            pushClaroMessage(__Class__."::Try to find {$lib} in {$moduleLabel}", 'debug');
        }
        
        $path = get_module_path( $moduleLabel ) . '/css/' . $lib . '.css';
        $url = get_module_url( $moduleLabel ) . '/css/' . $lib . '.css';
        
        if ( file_exists( $path ) )
        {
            if ( array_key_exists( $path, $this->css ) )
            {
                return false;
            }
            
            $this->css[$path] = array(
                'url' => $url,
                'media' => $media
            );
            
            if ( claro_debug_mode() )
            {
                pushClaroMessage(__Class__."::Use {$path}::{$url}", 'debug');
            }
            
            ClaroHeader::getInstance()->addHtmlHeader(
                '<link rel="stylesheet" type="text/css"'
                . ' href="'. $url.'"'
                . ' media="'.$media.'" />'
            );
            
            return true;
        }
        else
        {
            if ( claro_debug_mode() )
            {
                pushClaroMessage(__Class__."::Cannot found {$lib} in {$moduleLabel}", 'error');
            }
            
            return false;
        }
    }

    public static function getInstance()
    {
        if ( ! CssLoader::$instance )
        {
            CssLoader::$instance = new CssLoader;
        }

        return CssLoader::$instance;
    }
}
