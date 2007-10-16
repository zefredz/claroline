<?php // $Id$

    // vim: expandtab sw=4 ts=4 sts=4:

    /**
     * Loader classes for CSS and Javascript
     *
     * @version     1.9 $Revision$
     * @copyright   2001-2007 Universite catholique de Louvain (UCL)
     * @author      Claroline Team <info@claroline.net>
     * @author      Frederic Minne <zefredz@claroline.net>
     * @license     http://www.gnu.org/copyleft/gpl.html
     *              GNU GENERAL PUBLIC LICENSE version 2.0
     * @package     CORE
     */

    if ( count( get_included_files() ) == 1 )
    {
        die( 'The file ' . basename(__FILE__) . ' cannot be accessed directly, use include instead' );
    }

    uses('file.lib');

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
                get_module_path( get_current_module_label() ) . '/js' => get_module_url( get_current_module_label() ) . '/js',
                get_path( 'rootSys' ) . 'web/js' => get_path('url') . '/web/js'
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
            foreach ( $this->pathList as $tryPath => $tryUrl )
            {
                if ( claro_debug_mode() )
                {
                    pushClaroMessage(__Class__."::Try ".$tryPath.'/' .$lib.'.js', 'debug');
                }

                if ( file_exists ( $tryPath . '/' . $lib . '.js' ) )
                {
                    if ( claro_debug_mode() )
                    {
                        pushClaroMessage(__Class__."::Use ".$tryPath.'/' .$lib.'.js', 'debug');
                    }

                    $this->libraries[$lib] = $tryUrl . '/' . $lib . '.js';
                    return true;
                    // break;
                }
            }

            if ( claro_debug_mode() )
            {
                pushClaroMessage(__Class__."::NotFound ".$lib.'.js', 'error');
            }

            return false;
        }

        /**
         * Get HTML code for included libraries
         * @return  string html code
         */
        public function toHtml()
        {
            $ret = array();
            $list = $this->getLibraries();

            foreach ( $list as $url )
            {
                $ret[] = '<script src="'.$url.'" type="text/javascript"></script>';
            }

            $str = implode ( "\n", $ret );

            return $str;
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
                get_path( 'rootSys' ) . 'claroline/css' => get_path('url') . '/claroline/css',
                get_path( 'rootSys' ) . 'web/css' => get_path('url') . '/web/css'
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
                    if ( claro_debug_mode() )
                    {
                        pushClaroMessage(__Class__."::Use ".$tryPath.'/'.$css.'.css', 'debug');
                    }

                    $this->css[$css] = array(
                        'url' => $tryUrl . '/' . $css . '.css',
                        'media' => $media
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

        /**
         * Get HTML code for included libraries
         * @return  string html code
         */
        public function toHtml()
        {
            $ret = array();
            $list = $this->getCss();

            foreach ( $list as $css )
            {
                $ret[] = '<link rel="stylesheet" type="text/css"'
                        . ' href="'. $css['url'].'"'
                        . ' media="'.$css['media'].'" />'
                        ;
            }

            $str = implode ( "\n", $ret );

            return $str;
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
?>