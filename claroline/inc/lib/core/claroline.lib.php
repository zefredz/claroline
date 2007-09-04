<?php // $Id$

    // vim: expandtab sw=4 ts=4 sts=4:
    
    if ( count( get_included_files() ) == 1 )
    {
        die( 'The file ' . basename(__FILE__) . ' cannot be accessed directly, use include instead' );
    }
    
    uses( 'core/debug.lib', 'display/debugbar.lib', 'display/display.lib' );
    
    define ( 'CL_PAGE',     'CL_PAGE' );
    define ( 'CL_FRAMESET', 'CL_FRAMESET' );

    class Claroline
    {
        public $display;
        
        private static $instance = false;
        
        private function __construct()
        {
            $this->setDisplayType();
        }
        
        public function setDisplayType( $type = CL_PAGE )
        {
            switch ( $type )
            {
                case CL_PAGE:
                    $this->display = new ClaroPage;
                    break;
                case CL_FRAMESET:
                    $this->display = new ClaroFrameSet;
                    break;
                default:
                    trigger_error( 'Invalid display type', E_USER_ERROR );
            }
        }
        
        public static function getInstance()
        {
            if ( ! Claroline::$instance )
            {
                Claroline::$instance = new Claroline;
            }

            return Claroline::$instance;
        }
    }
?>