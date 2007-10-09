<?php // $Id$

    // vim: expandtab sw=4 ts=4 sts=4:
    
    if ( count( get_included_files() ) == 1 )
    {
        die( 'The file ' . basename(__FILE__) . ' cannot be accessed directly, use include instead' );
    }
    
    /**
     * Event Manager library
     *
     * @version     1.9 $Revision$
     * @copyright   2001-2007 Universite catholique de Louvain (UCL)
     * @author      Frederic Minne <zefredz@claroline.net>
     * @license     http://www.gnu.org/copyleft/gpl.html
     *              GNU GENERAL PUBLIC LICENSE
     * @package     Core
     */
    
    /**
    * Compute size of arrays containing object reference with possible
    * recursion
    *
    * @param array arry
    * @return int size of the array, -1 if $arry is not an array
    * @access global
    */
    function array_size( $arry )
    {
        if ( !is_array( $arry ) )
        {
            return -1;
        }
        else
        {
            $size = 0;

            foreach( $arry as $value )
            {
                $size++;
            }

            return $size;
        }
    }

    /**
     * Event used within event manager architecture
     * @access public
     */
    class Event
    {
        // event type
        private $_type;
        // additionnal arguments needed by event listeners
        private $_args;

        /**
         * constructor
         * @access public
         * @param $type string event type
         * @param $args array extra parameters
         */
        public function __construct( $type, $args = null )
        {
            $this->_type = $type;
            $this->_args = $args;
        }

        /**
         * get event type
         * @access public
         * @return string event type
         */
        public function getEventType( )
        {
            return $this->_type;
        }

        /**
         * get extra parameters
         * @access public
         * @return array event extra parameters
         */
        public function getArgs( )
        {
            return $this->_args;
        }
    }
    
    /**
    * Class to manage events and dispatch them to event listeners
    * @access public
    */
    class EventManager
    {
        // private fields
        private $_registry = array();
        
        private static $instance = false;

        /**
         * Constructor
         * @access public
         */
        private function __construct()
        {
        }

        /**
         * register new event listener for a given event
         * @access public
         * @param string eventType event type
         * @param EventListener listener reference to the event listener
         * @return string event listener ID
         */
        public function register( $eventType, &$listener )
        {
            if ( ! isset( $this->_registry[$eventType] ) )
            {
                $this->_registry[$eventType] = array( );
            }

            $id = md5( serialize( $listener ) );
            $this->_registry[$eventType][$id] =& $listener;

            return $id;
        }

        /**
         * unregister event listener
         * @access public
         * @param string eventype type of event watching by the listener
         * @param string id listener ID
         * @return bool
         */
        public function unregister( $eventType, $id )
        {
            if ( array_key_exists( $eventType, $this->_registry )
                && array_key_exists( $id, $this->_registry[$eventType] ) )
            {
                unset( $this->_registry[$eventType][$id] );

                if ( array_size( $this->_registry[$eventType] ) == 0 )
                {
                    unset( $this->_registry[$eventType] );
                }

                return true;
            }
            else
            {
                return false;
            }
        }

        /**
         * notify occurence of an event to the event manager
         * @access package private
         * @param string event type of occured event
         * @return int number of listeners notified or boolean false
         */
        public function eventOccurs( $event )
        {
            if ( isset( $this->_registry[$event->getEventType()] )
                && is_array( $this->_registry[$event->getEventType( )] )
                && array_size( $this->_registry[$event->getEventType( )] ) != 0 )
            {
                $cnt = 0;

                foreach( $this->_registry[$event->getEventType( )] as $listener )
                {
                    if ( !is_null( $listener ) )
                    {
                        $listener->handle( $event );
                        $cnt++;
                    }
                }

                return $cnt;
            }
            else
            {
                if ( defined( "DEBUG_MODE" ) && DEBUG_MODE )
                {
                    $errmsg = __CLASS__ . " : No listener found for EVENT["
                        . $event->getEventType( ) . "]"
                        ;
                    trigger_error( $errmsg, E_USER_NOTICE );
                }

                return false;
            }
        }

        // static

        /**
         * get event manager singleton instance
         * @access public
         * @return EventManager instance
         * @static
         */
        public static function getInstance()
        {
            if  ( ! EventManager::$instance )
            {
                EventManager::$instance = new EventManager;
            }
            
            return EventManager::$instance;
        }

        /**
         * notify occurence of an event to the event manager
         * @access public
         * @param string event type of occured event
         * @static
         */
        public static function notify( $event )
        {
            if ( claro_debug_mode() )
            {
                pushClaroMessage(__Class__."::notify ".$event->getEventType(), 'debug');
            }
            
            $mngr = EventManager::getInstance();
            return $mngr->eventOccurs($event);
        }

        /**
         * register new event listener for a given event
         * @access public
         * @static
         * @param string eventType event type
         * @param EventListener listener reference to the event listener
         * @return string event listener ID
         */
        public static function addListener( $eventType, &$listener )
        {
            $mngr = EventManager::getInstance();
            return $mngr->register($eventType, &$listener);
        }

        /**
         * unregister event listener
         * @access public
         * @static
         * @param string eventype type of event watching by the listener
         * @param string id listener ID
         * @return boolean
         */
        public static function removeListener( $eventType, $id )
        {
            $mngr = EventManager::getInstance();
            return $mngr->unregister($eventType, $id);
        }

        // debugging methods

        /**
         * list all registered events and the number of listeners for each
         * @access public
         */
        function listRegiteredEvents( )
        {
            if ( is_array( $this->_registry )
                && array_size( $this->_registry ) != 0 )
            {
                foreach ( $this->_registry as $eventType => $listeners )
                {
                    echo "$eventType( " . array_size( $listeners ) . " )\n";
                }
            }
            else
            {
                echo "none\n";
            }
        }

        /**
         * list all registered listeners and their ID
         * @access public
         */
        function listRegisteredListeners( )
        {
            if ( is_array( $this->_registry )
                && array_size( $this->_registry ) != 0 )
            {
                foreach ( $this->_registry as $eventType => $listeners )
                {
                    echo "$eventType( " . array_size( $listeners ) . " )\n";

                    foreach ( $listeners as $id => $listener )
                    {
                        echo "\tID: $id\n";
                    }
                }
            }
            else
            {
                echo "none\n";
            }
        }
    }
    
    /**
    * listen to a particular event
    * @access public
    */
    class EventListener
    {
        // protected fields
        private $_callback;

        /**
        * constructor
        * @access public
        * @param callback to call when the observed event occurs
        */
        public function __construct( $callback )
        {
            $this->_callback = $callback;
        }

        /**
        * notification of event occurence
        * @access package private
        * @param Event event the event to handle
        */
        public function handle( $event )
        {
            call_user_func( $this->_callback, $event );
        }
    }
    
    /**
    * generic event driven application
    * @access public
    * @abstract
    */
    class EventDriven
    {
        /**
         * add an event listener to the event driven application
         * @access public
         * @param string methodName callback method, must be a method of the
         *   current event-driven instance
         * @param string eventType event type
         * @return string eventlistener ID
         */
        public function addListener( $eventType, $methodName )
        {
            $listener = new EventListener( array( &$this, $methodName ) );
            return EventManager::addListener( $eventType, $listener );
        }

        /**
         * remove an event listener from the application
         * @access public
         * @param string eventType event type
         * @param string eventlistener ID
         * @return boolean
         */
        public function removeListener( $eventType, $id )
        {
            return EventManager::removeListener( $eventType, $id );
        }
    }
    
    /**
    * Generic event generator for test purpose
    * @access public
    */
    class EventGenerator
    {
        /**
        * notify the event manager for an event occurence
        * @access public
        * @param Event event the event that occurs; an instance of the event class
        */
        public function sendEvent( $event )
        {
            EventManager::notify( $event );
        }

        /**
        * public function to notify manager that an event occured,
        * using this fucntion instead of sendEvent allow to let the class create
        * the Event instance for you
        *
        * @param string eventType the type of the event
        * @param array args an array containing any parameters needed
        *   to describe the event occurence
        */

        public function notifyEvent( $eventType, $args )
        {
            $event = new Event( $eventType, $args );
            $this->sendEvent( $event );
        }
    }
?>