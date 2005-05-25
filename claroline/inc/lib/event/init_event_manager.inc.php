<?php

/**
 * Declaration of needed CLASSES for the observer pattern
 */
 
//Main classes needed for the EventManager pattern

require_once($includePath."/lib/event/class.event.php");

require_once($includePath."/lib/event/notifier.php");
  
/**
 * Declaration of needed INSTANCES for the event manager pattern
 */

//1.Create event manager

$claro_event_manager = new EventManager();

//2.Create event listener

$eventNotifier = new EventGenerator( $claro_event_manager );

//3.Create listeners

$claro_notifier = new Notifier( $claro_event_manager);
//$claro_indexer  = new Indexer(& $claro_event_manager);

//4.Register listener in the event manager

$listen1 = $claro_notifier->addListener( 'update', "document_visible");
$listen2 = $claro_notifier->addListener( 'update', "document_file_added");

$listen3 = $claro_notifier->addListener( 'update', "agenda_event_added");
$listen4 = $claro_notifier->addListener( 'update', "anouncement_added");

?>