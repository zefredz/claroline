<?php // $Id$

    // vim: expandtab sw=4 ts=4 sts=4:
    
    /**
     * Singleton class to represent the Claroline platform. This is a utility
     * class providing classes and methods to deal with the kernel and the page
     * display
     *
     * @version     1.9 $Revision$
     * @copyright   2001-2007 Universite catholique de Louvain (UCL)
     * @author      Frederic Minne <zefredz@claroline.net>
     * @license     http://www.gnu.org/copyleft/gpl.html
     *              GNU GENERAL PUBLIC LICENSE
     * @package     CORE
     */
    
    if ( count( get_included_files() ) == 1 )
    {
        die( 'The file ' . basename(__FILE__) . ' cannot be accessed directly, use include instead' );
    }
    
    uses( 'core/debug.lib', 'display/debugbar.lib', 'display/display.lib' );
    
    define ( 'CL_PAGE',     'CL_PAGE' );
    define ( 'CL_FRAMESET', 'CL_FRAMESET' );

    class Claroline
    {
        /**
         * The display object for the current script
         */
        public $display;
        
        private static $instance = false;
        
        private function __construct()
        {
            $this->setDisplayType();
        }
        
        /**
         * Set the type of display to use
         * @param   string type, display type could be
         *  CL_PAGE         a standard page with header, banner, body and footer
         *  CL_FRAMESET     a frameset with header and frames
         */
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
        
        /**
         * Returns the instance of the Claroline object
         * @return  Claroline singleton
         */
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