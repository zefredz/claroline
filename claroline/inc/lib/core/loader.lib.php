<?php // $Id$

    // vim: expandtab sw=4 ts=4 sts=4:
    
    if ( count( get_included_files() ) == 1 )
    {
        die( 'The file ' . basename(__FILE__) . ' cannot be accessed directly, use include instead' );
    }
    
    uses('file.lib');

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
        
        public function load( $lib )
        {
           $found = false;
            
            foreach ( $this->pathList as $tryPath => $tryUrl )
            {
                if ( file_exists ( $tryPath . '/' . $lib . '.js' ) )
                {
                    $this->libraries[$lib] = $tryUrl . '/' . $lib . '.js';
                    $found = true;
                    break;
                }
            }
            
            return $found;
        }
        
        public function toHtml()
        {
            $ret = array();
            $list = $this->getLibraries();
            
            foreach ( $list as $lib => $url )
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

        public function load( $css, $media )
        {
            $css = secure_file_path( $css );
            $found = false;
            
            foreach ( $this->pathList as $tryPath => $tryUrl )
            {
                if ( file_exists ( $tryPath . '/' . $css . '.css' ) )
                {
                    $this->css[$css] = '<link rel="stylesheet" type="text/css"'
                        . ' href="'.$tryUrl . '/' . $css . '.css'.'"'
                        . ' media="'.$media.'" />'
                        ;
                        
                    $found = true;
                    break;
                }
            }


            return $found;
        }

        public function toHtml()
        {
            $str = implode ( "\n", $this->getCss() );

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