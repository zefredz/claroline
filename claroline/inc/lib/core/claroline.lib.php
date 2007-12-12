<?php // $Id$

    // vim: expandtab sw=4 ts=4 sts=4:
    
    /**
     * Singleton class to represent the Claroline platform. This is a utility
     * class providing classes and methods to deal with the kernel and the page
     * display
     *
     * @version     1.9 $Revision$
     * @copyright   2001-2007 Universite catholique de Louvain (UCL)
     * @author      Claroline Team <info@claroline.net>
     * @author      Frederic Minne <zefredz@claroline.net>
     * @license     http://www.gnu.org/copyleft/gpl.html
     *              GNU GENERAL PUBLIC LICENSE version 2 or later
     * @package     CORE
     */
    
    if ( count( get_included_files() ) == 1 )
    {
        die( 'The file ' . basename(__FILE__) . ' cannot be accessed directly, use include instead' );
    }
    
    uses( 'core/debug.lib', 'core/console.lib', 'core/event.lib'
        , 'core/notify.lib', 'display/display.lib', 'database/database.lib' );
    
    define ( 'CL_PAGE',     'CL_PAGE' );
    define ( 'CL_FRAMESET', 'CL_FRAMESET' );
    define ( 'CL_POPUP',    'CL_POPUP' );
    define ( 'CL_FRAME',    'CL_FRAME' );

    /**
     * Main Claroline class containing references to Claroline kernel objects
     * This class is a Singleton
     */
    class Claroline
    {
        // Display type constants
    	const PAGE = 'CL_PAGE';
    	const FRAMESET = 'CL_FRAMESET';
    	const POPUP = 'CL_POPUP';
    	const FRAME = 'CL_FRAME';
    	
        // Singleton instance
        private static $instance = false; // this class is a singleton
        
        // Kernel objects
        // Database link
        public $database;
        // Event manager
        public $eventManager;
        // Notification manager
        public $notification;
        // Notifier object
        public $notifier;
        // Display object
        public $display;
        
        // this class is a singleton, use static method getInstance()
        private function __construct()
        {
            try
            {
                // Load database driver
                if ( extension_loaded( 'pdo' ) && extension_loaded('pdo_mysql') )
                {
                    Console::debug('use pdo mysql database driver');
                    Database::loadDriver( 'pdomysql' );
                }
                elseif ( extension_loaded( 'mysqli' ) )
                {
                    Console::debug('use mysqli database driver');
                    Database::loadDriver( 'mysqli' );
                }
                else
                {
                    Console::debug('use mysql database driver');
                    Database::loadDriver( 'mysql' );
                }
                
                // Create main database connection
            	$this->database = Database::getMainConnection();
            
                // initialize the event manager and notification classes
                $this->eventManager = EventManager::getInstance();
                $this->notification = ClaroNotification::getInstance();
                $this->notifier = ClaroNotifier::getInstance();
                
                // initialize set the default display mode
                $this->setDisplayType();
            }
            catch ( Exception $e )
            {
                die( $e );
            }
        }
        
        /**
         * Set the type of display to use
         * @param   string type, display type could be
         *  Claroline::PAGE 		a standard page with header, banner, body and footer
         *  Claroline::FRAMESET     a frameset with header and frames
         *  Claroline::POPUP        a popup-embedded page
         *  Claroline::FRAME        a frame-embedded page
         *  default value : Claroline::PAGE
         */
        public function setDisplayType( $type = self::PAGE )
        {
            switch ( $type )
            {
                case self::PAGE:
                    $this->display = new ClaroPage;
                    break;
                case self::POPUP:
                    $this->display = new ClaroPage;
                    $this->display->popupMode();
                    break;
                case self::FRAME:
                    $this->display = new ClaroPage;
                    $this->display->frameMode();
                    break;
                case self::FRAMESET:
                    $this->display = new ClaroFramesetPage;
                    break;
                default:
                    throw new Exception( 'Invalid display type' );
            }
        }
        
        /**
         * Returns the singleton instance of the Claroline object
         * @return  Claroline singleton instance
         */
        public static function getInstance()
        {
            if ( ! self::$instance )
            {
                self::$instance = new Claroline;
            }

            return self::$instance;
        }
    }
?>