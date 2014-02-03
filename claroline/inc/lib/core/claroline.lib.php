<?php // $Id$

// vim: expandtab sw=4 ts=4 sts=4:

/**
 * CLAROLINE
 *
 * Singleton class to represent the Claroline platform. This is a utility
 * class providing classes and methods to deal with the kernel and the page
 * display.
 *
 * @version     $Revision$
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

interface Claroline_Constants
{
    // Display type constants
    const PAGE      = 'CL_PAGE';
    const FRAMESET  = 'CL_FRAMESET';
    const POPUP     = 'CL_POPUP';
    const FRAME     = 'CL_FRAME';
}

/**
 * Main Claroline class containing references to Claroline kernel objects
 * This class is a Singleton
 */
class Claroline_Container extends Pimple implements Claroline_Constants
{
    
    
    /**
     * Some magic for backward compatibility mode
     * @param type $name
     */
    public function __get( $name )
    {
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
 * Main Claroline class containing references to Claroline kernel objects
 * This class is a Singleton
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
    
    public static function initCoreServices()
    {
        if ( ! self::$instance )
        {
            self::$instance = new Claroline_Container;
        }
        
        try
        {
            // initialize the event manager and notification classes
            self::$instance['eventManager'] = self::$instance->share( function() { return EventManager::getInstance(); } );
            self::$instance['notification'] = self::$instance->share( function() { return ClaroNotification::getInstance(); } );
            self::$instance['notifier'] = self::$instance->share( function() { return ClaroNotifier::getInstance(); } );
            // initialize logger
            self::$instance['logger'] = self::$instance->share( function() { return new Logger(); } );
            // initialize the module stack
            self::$instance['moduleLabelStack'] = self::$instance->share( function() { return new Claro_ModuleLabelStack(); } );
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
        return self::getInstance()['display'];
    }

    /**
     * Helper to initialize the display
     * @param string $displayType
     */
    public static function initDisplay( $displayType = self::PAGE )
    {
        self::getInstance()->setDisplayType( $displayType );
    }
    
    protected static $db = false;
    // Database link
    protected static $database = false;

    /**
     * Get the current database connection object
     * @return Claroline_Database_Connection
     */
    public static function getDatabase()
    {
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
            $database = new Claroline_Database_Connection();
            $database->connect();
            
            $charset = get_conf( 'mysqlSetNames' );

            if ( !empty( $charset ) )
            {
                $database->setCharset($charset);
            }

            $GLOBALS["___mysqli_ston"] = $database->getDbLink();
        
            self::$instance['database'] = self::$instance->share( function() use ($database) {
                return $database;
            } );
        }
    }

    protected static $_ajaxServiceBroker = false;

    /**
     * Get kernel Ajax Service Broker instance
     * @return Ajax_Remote_Service_Broker
     * @since Claroline 1.9.5
     */
    public static function ajaxServiceBroker()
    {
        if ( ! self::$_ajaxServiceBroker )
        {
            self::$_ajaxServiceBroker = new Ajax_Remote_Service_Broker();
        }

        return self::$_ajaxServiceBroker;
    }
}
