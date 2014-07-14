<?php // $Id$

// vim: expandtab sw=4 ts=4 sts=4:

/**
 * CLAROLINE
 *
 * Singleton class to represent the Claroline platform. This is a utility
 * class providing classes and methods to deal with the kernel and the page
 * display.
 *
 * @version     Claroline 1.12 $Revision$
 * @copyright   (c) 2001-2014, Universite catholique de Louvain (UCL)
 * @author      Claroline Team <info@claroline.net>
 * @author      Frederic Minne <zefredz@claroline.net>
 * @license     http://www.gnu.org/copyleft/gpl.html
 *              GNU GENERAL PUBLIC LICENSE version 2 or later
 * @package     kernel.core
 */

require_once __DIR__ . '/../thirdparty/Pimple/lib/Pimple.php';
require_once __DIR__ . '/../core/debug.lib.php';
require_once __DIR__ . '/../core/console.lib.php';
require_once __DIR__ . '/../core/event.lib.php';
require_once __DIR__ . '/../core/log.lib.php';
require_once __DIR__ . '/../core/url.lib.php';
require_once __DIR__ . '/../core/notify.lib.php';
require_once __DIR__ . '/../display/display.lib.php';
require_once __DIR__ . '/../database/database.lib.php';
require_once __DIR__ . '/../utils/ajax.lib.php';
require_once __DIR__ . '/../utils/input.lib.php';
require_once __DIR__ . '/accessmanager.lib.php';

/**
 * Define somme constants shared accross classes
 * @since Claroline 1.12
 */
interface Claroline_Constants
{
    // Display type constants
    const PAGE      = 'CL_PAGE';
    const FRAMESET  = 'CL_FRAMESET';
    const POPUP     = 'CL_POPUP';
    const FRAME     = 'CL_FRAME';
}

/**
 * Main Claroline class containing references to Claroline core services.
 * This class is a dependency injection container since Claroline 1.12.
 * @since Claroline 1.12
 * @see Pimple
 */
class Claroline_Container extends Pimple implements Claroline_Constants
{
    
    
    /**
     * Some magic for backward compatibility mode
     * @param type $name
     */
    public function __get( $name )
    {
        /*
         * This is only for upgrading code to the Claroline 1.12 API.
         * This option should remain set to true in production
         */
        if ( ! get_conf( 'backwardCompatibilityMode', true ) )
        {
            throw new Exception("Access to Claroline object properties has to be made using the Pimple dependency injection container instead of the old object property access.");
        }
        
        Console::debug("Try to get container property {$name} as an object property instead of using the dependency injection container");
        
        if ( isset($this[$name]) )
        {
            return $this[$name];
        }
        else
        {
            return null;
        }
    }
    
    /**
     * Set the type of display to use
     * @param   string type, display type could be
     *  Claroline::PAGE         a standard page with header, banner, body and footer
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
                $this['display'] = new ClaroPage;
                break;
            case self::POPUP:
                $this['display'] = new ClaroPage;
                $this['display']->popupMode();
                break;
            case self::FRAME:
                $this['display'] = new ClaroPage;
                $this['display']->frameMode();
                break;
            case self::FRAMESET:
                $this['display'] = new ClaroFramesetPage;
                break;
            default:
                throw new Exception( 'Invalid display type' );
        }
    }
}

/**
 * Factory and helper returning the Claroline dependency injection container instance.
 * 
 * This class is a factory (that creates a dependency injection container) 
 * instead of a singleton since Claroline 1.12. This class also provide some 
 * helper methods to access database, display and ajax broker core services for
 * backward compatibility.
 * @see Claroline_Container for the definition of the dependency injection container class
 */
class Claroline implements Claroline_Constants
{
    
    // Singleton instance
    private static $instance = false; // this class is a singleton
    
    /**
     * Initializes and returns the instance of the Claroline object
     * @return  Claroline singleton instance
     */
    public static function getInstance()
    {
        if ( ! self::$instance )
        {
            throw new Exception("Claroline container not initialized !");
        }

        return self::$instance;
    }
    
    /**
     * Initialize the core service providers of the platform :
     *  
     * ['eventManager'] : event handling 
     * ['notification'] : notifications, 
     * ['notifier'] : notifier
     * ['logger'] : log, 
     * ['moduleLabelStack'] : module statck, 
     * ['accessManager'] : access manager, 
     * ['userInput'] : user input, 
     * ['ajaxServiceBroker'] : ajax service broker
     * @return void
     * @throws Exception if the database connection is not initialized
     */
    public static function initCoreServices()
    {
        /*
         * core database service needed for service initialization
         * since this method should only be called in the kernel init process, 
         * we decided to thorw an exception if the database service is not started
         */
        if ( ! self::$instance || empty( self::$instance['database'] ) )
        {
            throw new Exception('FATAL ERROR: the database provider is not initialize. You have to call Claroline::initDatabaseProvider before calling Claroline::initCoreServices');
        }
        
        try
        {
            /*
             * initialize core services
             * WARNING initialization order matters !
             */
            self::$instance['eventManager'] = self::$instance->share( function() { return EventManager::getInstance(); } );
            self::$instance['notification'] = self::$instance->share( function() { return ClaroNotification::getInstance(); } );
            self::$instance['notifier'] = self::$instance->share( function() { return ClaroNotifier::getInstance(); } );
            self::$instance['logger'] = self::$instance->share( function() { return new Logger(); } );
            self::$instance['moduleLabelStack'] = self::$instance->share( function() { return new Claro_ModuleLabelStack(); } );
            self::$instance['accessManager'] = self::$instance->share( function() { return new Claro_AccessManager(); } );
            self::$instance['userInput'] = self::$instance->share( function() { return Claro_UserInput::getInstance(); } );
            self::$instance['ajaxServiceBroker'] = self::$instance->share( function() { return new Ajax_Remote_Service_Broker(); } );
        }
        catch ( Exception $e )
        {
            die( $e );
        }
    }

    /**
     * Get the current display object
     * @return Display which can be a ClaroPage or ClaroFramesetPage according
     *  to the display type
     */
    public static function getDisplay()
    {
        /*
         * PHP 5.4 compatibility  workaround
         * in PHP 5.5 one would have writen it more simply as
         *      return self::getInstance()['display'];
         */
        self::getInstance();
        return self::$instance['display'];
    }

    /**
     * Helper to initialize the display
     * @param string $displayType
     */
    public static function initDisplay( $displayType = self::PAGE )
    {
        self::getInstance()->setDisplayType( $displayType );
    }

    /**
     * Get the current database connection object
     * @return Claroline_Database_Connection
     */
    public static function getDatabase()
    {
        /*
         * core database service needed for service initialization
         * since this method should only be called in the kernel init process, 
         * we decided to thorw an exception if the database service is not started
         */
        if ( ! self::$instance || empty( self::$instance['database'] ) )
        {
            throw new Exception('FATAL ERROR: the database provider is not initialize. You have to call Claroline::initDatabaseProvider before calling Claroline::getDatabase');
        }
        
        return self::$instance['database'];
    }

    /**
     * Initialize the database
     * @return void
     * @throws Exception when the database connection cannot be created
     * @deprecated since 1.12
     */
    public static function initDatabaseProvider()
    {
        if ( ! self::$instance )
        {
            self::$instance = new Claroline_Container;
        }
        
        if ( empty( self::$instance['database'] ) )
        {
            /* 
             * we need to instanciate the database from the start because many 
             * other core services are depending on it to their own 
             * initialization 
             */
            $database = new Claroline_Database_Connection();
            $database->connect();
            
            $charset = get_conf( 'mysqlSetNames' );

            if ( !empty( $charset ) )
            {
                $database->setCharset($charset);
            }
            
            // claro_sql_* and mysqli_* backward compatibility hack
            $GLOBALS["___mysqli_ston"] = $database->getDbLink();
            
            /*
             * Now we can share the database as a core service provided by the
             * dependency injection container
             */
            self::$instance['database'] = self::$instance->share( function() use ($database) {
                return $database;
            } );
        }
    }

    /**
     * Get kernel Ajax Service Broker instance
     * @return Ajax_Remote_Service_Broker
     * @since Claroline 1.9.5
     */
    public static function ajaxServiceBroker()
    {
        return self::$instance['ajaxServiceBroker'];
    }
}
