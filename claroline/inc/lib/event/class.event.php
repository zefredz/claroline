<?php // $Id$

// +----------------------------------------------------------------------+
// | EventDriven Programming                                              |
// | PHP version 4                                                        |
// | version 0.1  - 2005-01-14                                            |
// | author Frederic Minne                                                |
// +----------------------------------------------------------------------+
// | Copyright (c) 2004 Frederic Minne                                    |
// +----------------------------------------------------------------------+
// | This program is free software; you can redistribute it and/or modify |
// | it under the terms of the GNU General Public License as published by |
// | the Free Software Foundation; either version 2 of the License, or    |
// | (at your option) any later version.                                  |
// |                                                                      |
// | This program is distributed in the hope that it will be useful,      |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of       |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the        |
// | GNU General Public License for more details.                         |
// |                                                                      |
// | You should have received a copy of the GNU General Public License    |
// | along with this program; if not, write to the Free Software          |
// | Foundation, Inc., 59 Temple Place, Suite 330, Boston,                |
// | MA  02111-1307  USA                                                  |
// +----------------------------------------------------------------------+
// | Authors: Frederic Minne <zefredz@gmail.com>                          |
// |          Contributor <contributor@example.com>                       |
// +----------------------------------------------------------------------+

// TODO : modify the code to use Event class instead of string to represent events
// ie:
//      - pass additonnal arguments to event listeners
//      - modify event manager to use $event->type instead of $event in eventOccurs

// ---------------------- Functions ----------------------

/**
 * Compute size of arrays containing object reference with possible
 * recursion
 * @param $arry (array)
 * @return (int) size of the array, -1 if $arry is not an array
 * @access global
 */
function array_size( $arry )
{
    if ( !is_array( $arry ) )
    {
        return -1;
    }
    $size = 0;
    
    foreach ( $arry as $value )
    {
        $size++;
    }
    
    return $size;
}


// ------------------- Classes ------------------------

/**
* class to manage events and dispatch them to event listeners
* @access public
*/
class EventManager
{
    // protected fields
    var $_registry;

    /**
        * Constructor
        * @access public
        */
    function EventManager()
    {
        $this->_registry = array();
    }

    /**
     * register new event listener for a given event
     * @access public
     * @param $event (string) event identifier
     * @param $listener (object) reference to the event listener
     * @return (string) event listener ID
     */
    function register( $event, &$listener )
    {
        if( ! isset( $this->_registry[$event] ) )
        {
            $this->_registry[$event] = array();
        }
        $id = md5( serialize($listener) );
        $this->_registry[$event][$id] = & $listener;
        return $id;
    }

    /**
     * unregister event listener
     * @access public
     * @param $event (string) event watching by the listener
     * @param $id (string) listener ID
     */
    function unregister( $event, $id )
    {
        unset($this->_registry[$event][$id]);
        if( array_size( $this->_registry[$event] ) == 0 )
        {
            unset( $this->_registry[$event] );
        }
    }

    /**
     * notify occurence of an event to the event manager
     * @access package private
     * @param $event (string) the occured event
     */
    function eventOccurs( $event )
    {
        if ( isset( $this->_registry[$event->getEventType()] )
            && is_array( $this->_registry[$event->getEventType()] )
            && array_size( $this->_registry[$event->getEventType()] ) != 0 )
        {
            foreach( $this->_registry[$event->getEventType()] as $listener )
            {
                if( !is_null( $listener ) )
                {
                    $listener->handle($event);
                }
            }
        }
        else
        {
            if ( defined( "DEBUG_MODE" ) && DEBUG_MODE )
            {
                $errmsg = __CLASS__ 
                    . "{no listener found for EVENT["
                    . $event->getEventType()
                    . "]}"
                    ;
                trigger_error( $errmsg, E_USER_ERROR );
            }
        }
    }

    // debugging methods

    /**
     * list all registered events and the number of listeners for each
     * @access public
     */
    function listRegiteredEvents()
    {
        if ( is_array( $this->_registry )
        && array_size( $this->_registry ) != 0 )
        {
            foreach( $this->_registry as $event => $listeners )
            {
                echo "$event(" . array_size( $listeners ) . ")\n";
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
    function listRegisteredListeners()
    {
        if ( is_array( $this->_registry )
        && array_size( $this->_registry ) != 0 )
        {
            foreach( $this->_registry as $event => $listeners )
            {
                echo "$event(" . array_size( $listeners ) . ")\n";
                foreach( $listeners as $id => $listener )
                {
                    echo "\t$id\n";
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
    var $_event;
    var $_obj;
    var $_callback;

    /**
        * constructor
        * @access public
        * @param $obj (object) reference to the creator
        * @param $callback (string) name of the callback method of the creator
        *       object to call when event occurs
        */
    function EventListener( &$obj, $callback )
    {
        $this->_obj = & $obj;
        $this->_callback = $callback;
    }

    /**
        * notification of event occurence
        * @access package private
        */
    function handle($event)
    {
        call_user_func( array( &$this->_obj, $this->_callback ),$event );
    }
}


/**
 * generic event generator for test purpose
 * @access public
 */
class EventGenerator
{
    // protected fields
    var $_registry;

    /**
        * constructor
        * @access public
        * @param $registry (object) reference to an event manager
        */
    function EventGenerator( &$registry )
    {
        $this->_registry =& $registry;
    }

    /**
     * notify the event manager for an event occurence
     * @access public
     * @param $event the event that occurs; an instance of the event class
     */
    function sendEvent( $event )
    {
        $this->_registry->eventOccurs($event);
    }
    
    /**
     * public function to notify manager that an event occured,  
     * using this fucntion instead of sendEvent allow to let the class create the Event instance for you
     *
     * @param $eventType (string) the type of the event 
     * @param $args an array contening any parameters needed to describe the event occurence
     */

    function notifyEvent($eventType, $args )
    {
        $myEvent = new Event( $eventType, $args);
        $this->sendEvent($myEvent);
    }

    /**
     * Public function to notify manager that an event occured IN A COURSE TOOL
     * using this function allow to notify an event in any tool of any course into Claroline, 
     * it allows to use only one call to this function in the Claroline code
     *
     * @param eventType (string)
     * @param cid identifier of the COURSE concerned by the event    (should always be set)
     * @param tid identifier of the TOOL concerned by the event      (0 if not used)
     * @param rid identifier of the RESSOURCE concerned by the event (0 if not used)
     * @param gid identifier of the GROUP concerned by the event     (0 if not used)
     * @param uid identifier of the USER concerned by the event      (0 if not used)
	 */

    function notifyCourseEvent($eventType, $cid, $tid, $rid, $gid, $uid)
    {
        $eventArgs = array();
        $eventArgs['cid'] = $cid;
        $eventArgs['tid'] = $tid;
        $eventArgs['rid'] = $rid;
        $eventArgs['gid'] = $gid;
        $eventArgs['uid'] = $uid;
        $eventArgs['date'] = date("Y-m-d H:i:00");

        $this->notifyEvent($eventType, $eventArgs);
    }

}

/**
 * generic event driven application
 * @access public
 * @abstract
 */
class EventDriven
{
    // protected fields
    var $_registry;
    var $_listeners;

    /**
     * constructor
     * @access public
     * @param $registry (object) reference to the event manager
     */
    function EventDriven( &$registry )
    {
        $this->_registry =& $registry;
        $this->_listeners = array();
    }

    /**
     * add an event listener to the event driven application
     * @access public
     * @param $callback (string) callback method
     * @param $event (string) event
     * @return $id (string) eventlistener ID
     */
    function addListener( $callback, $eventType )
    {
        $listener = new EventListener( $this, $callback );
        $id = $this->_registry->register($eventType, $listener );
        $this->_listeners[$eventType][$id] =& $listener;
        return $id;
    }

    /**
     * remove an event listener from the application
     * @access public
     * @param $event (string) event
     * @param $id (string) eventlistener ID
     */
    function removeListener( $event, $id )
    {
        unset( $this->_listeners[$event][$id] );
        $this->_registry->unregister($event, $id);
        if( is_array( $this->_listeners[$event] )
            && array_size( $this->_listeners[$event] ) == 0 )
        {
            unset( $this->_listeners[$event] );
        }
    }

    // ----------- Debugging methods ----------

    /**
     * list all registered listeners
     * @access public
     */
    function listListeners()
    {
        if ( is_array( $this->_listeners )
        && array_size( $this->_listeners ) != 0 )
        {
            foreach( $this->_listeners as $event => $listeners )
            {
                echo "$event(" . array_size( $listeners ) . ")\n";
                foreach( $listeners as $id => $listener )
                {
                    echo "\t$id\n";
                }
            }
        }
        else
        {
            echo "none\n";
        }
    }
}

class Event
{
    // event type
    var $_type;
    // additionnal arguments needed by event listeners
    var $_args;

    function Event( $type, $args = null )
    {
        $this->_type = $type;
        $this->_args = $args;
    }

    function getEventType()
    {
        return $this->_type;
    }

    function getArgs()
    {
        return $this->_args;
    }
}


// ---------------------- Test Class --------------------

    class MyEventDrivenObject extends EventDriven
    {
        function MyEventDrivenObject( &$registry )
        {
            parent::EventDriven( $registry );
        }

        function onHelloEventCallMe( )
        {
            echo "Hello World\n";
        }

        function onHelloEventCallMe2( )
        {
            echo "Hello World 2\n";
        }

        function onAnotherEventCallMe( )
        {
            echo "another event occurs\n";
        }

        function main( )
        {
            echo "> creating objects...\n";
            $registry = new EventManager( );
            $edo = new MyEventDrivenObject( $registry );
            $em = new EventGenerator( $registry );
            echo "> done\n\n";

            echo "> registrering listeners\n";
            $id1 = $edo->addListener( 'onHelloEventCallMe', "hello" );
            $id2 = $edo->addListener( 'onAnotherEventCallMe', "another" );
            echo "> done\n\n";

            echo "> sending events\n";
            $em->sendEvent( new Event("hello") );
            $em->sendEvent( new Event("another") );
            echo "> done\n\n";

            echo "> lists\n";
            echo "* registry:\n";
            $registry->listRegisteredListeners( );
            echo "* listeners\n";
            $edo->listListeners( );
            echo "> done\n\n";

            echo "> registrering another hello listener\n";
            $id3 = $edo->addListener( 'onHelloEventCallMe2', "hello" );
            echo "> done\n\n";

            echo "> lists\n";
            echo "* registry:\n";
            $registry->listRegisteredListeners( );
            echo "* listeners\n";
            $edo->listListeners( );
            echo "> done\n\n";

            echo "> sending events\n";
            $em->sendEvent( new Event("hello") );
            $em->sendEvent( new Event("another") );
            echo "> done\n\n";

            echo "> unregistrering another and second hello listener\n";
            $edo->removeListener( "another", $id2 );
            $edo->removeListener( "hello", $id3 );
            echo "> done\n\n";

            echo "> lists\n";
            echo "* registry:\n";
            $registry->listRegisteredListeners( );
            echo "* listeners\n";
            $edo->listListeners( );
            echo "> done\n\n";

            echo "> sending events\n";
            $em->sendEvent( new Event("hello") );
            $em->sendEvent( new Event("another") );
            echo "> done\n";
        }
    }

// -------------------- Run Test ---------------------

//MyEventDrivenObject::main();

?>
