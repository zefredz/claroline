<?php // $Id$

// vim: expandtab sw=4 ts=4 sts=4:

/**
 * Singleton class to represent the Claroline platform. This is a utility
 * class providing classes and methods to deal with the kernel and the page
 * display
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

FromKernel::uses( 'core/debug.lib', 'core/console.lib', 'core/event.lib'
    , 'core/notify.lib', 'display/display.lib', 'database/database.lib'
    , 'core/log.lib', 'core/url.lib' );

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
    
    // Kernel objects
    // Event manager
    public $eventManager;
    // Notification manager
    public $notification;
    // Notifier object
    public $notifier;
    // Display object
    public $display;
    // logger
    public $logger;
    
    protected $moduleLabelStack;
    
    // this class is a singleton, use static method getInstance()
    private function __construct()
    {
        try
        {
            // initialize the event manager and notification classes
            $this->eventManager = EventManager::getInstance();
            $this->notification = ClaroNotification::getInstance();
            $this->notifier = ClaroNotifier::getInstance();
            
            // initialize logger
            $this->logger = new Logger();
            
            $this->moduleLabelStack = array();
            
            if ( isset($GLOBALS['tlabelReq']) )
            {
                $this->pushModuleLabel($GLOBALS['tlabelReq']);
                
                pushClaroMessage("Set current module to {$GLOBALS['tlabelReq']}", 'debug');
            }
        }
        catch ( Exception $e )
        {
            die( $e );
        }
    }
    
    public function pushModuleLabel( $label )
    {
        array_push( $this->moduleLabelStack, $label );
    }
    
    public function popModuleLabel()
    {
        array_pop( $this->moduleLabelStack );
    }
    
    public function currentModuleLabel()
    {
        if ( empty( $this->moduleLabelStack ) )
        {
            return false;
        }
        else
        {
           return $this->moduleLabelStack[count($this->moduleLabelStack)-1];
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
        
        JavascriptLoader::getInstance()->load('jquery');
        JavascriptLoader::getInstance()->load('claroline');
    }
    
    // Singleton instance
    private static $instance = false; // this class is a singleton
    
    /**
     * Returns the singleton instance of the Claroline object
     * @return  Claroline singleton instance
     */
    public static function getInstance()
    {
        if ( ! self::$instance )
        {
            self::$instance = new self;
        }

        return self::$instance;
    }
    
    public static function initDisplay( $displayType = self::PAGE )
    {
        self::getInstance()->setDisplayType( $displayType );
    }
    
    public static function log( $type, $data )
    {
        self::getInstance()->logger->log($type, $data);
    }
    
    protected static $db = false;
    // Database link
    protected static $database = false;
    
    public static function getDisplay()
    {
        return self::getInstance()->display;
    }
    
    public static function getDatabase()
    {
        if ( ! self::$database )
        {
            // self::initMainDatabase();
            self::$database = new Claroline_Database_Connection(self::$db);
            self::$database->connect();
        }
        
        return self::$database;
    }
    
    public static function initMainDatabase()
    {
        if ( self::$db )
        {
            return;
        }
        
        if ( ! defined('CLIENT_FOUND_ROWS') ) define('CLIENT_FOUND_ROWS', 2);
        // NOTE. For some reasons, this flag is not always defined in PHP.
        
        self::$db = @mysql_connect(
            get_conf('dbHost'),
            get_conf('dbLogin'),
            get_conf('dbPass'),
            false,
            CLIENT_FOUND_ROWS );
        
        if ( ! self::$db )
        {
            throw new Exception ( 'FATAL ERROR ! SYSTEM UNABLE TO CONNECT TO THE DATABASE SERVER.' );
        }
        
        // NOTE. CLIENT_FOUND_ROWS is required to make claro_sql_query_affected_rows()
        // work properly. When using UPDATE, MySQL will not update columns where the new
        // value is the same as the old value. This creates the possiblity that
        // mysql_affected_rows() may not actually equal the number of rows matched,
        // only the number of rows that were literally affected by the query.
        // But this behavior can be changed by setting the CLIENT_FOUND_ROWS flag in
        // mysql_connect(). mysql_affected_rows() will return then the number of rows
        // matched, even if none are updated.
        
        
        
        $selectResult = mysql_select_db( get_conf('mainDbName'), self::$db);
        
        if ( ! $selectResult )
        {
            throw new Exception ( 'FATAL ERROR ! SYSTEM UNABLE TO SELECT THE MAIN CLAROLINE DATABASE.' );
        }
        
        if ($GLOBALS['statsDbName'] == '')
        {
            $GLOBALS['statsDbName'] = get_conf('mainDbName');
        }
    }
}
