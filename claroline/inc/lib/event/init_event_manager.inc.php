<?php

/**
 * Declaration of needed CLASSES for the EventManager pattern
 */
 
//Main classes needed for the EventManager pattern

require_once($includePath."/lib/event/class.event.php");

require_once($includePath."/lib/event/notifier.php");
  
/**
 * Declaration of needed INSTANCES for the EventManager pattern in Claroline
 */

//1.Create Claroline event manager

$claro_event_manager = new EventManager();

//2.Create Claroline event listener

$eventNotifier = new EventGenerator( $claro_event_manager );

//3.Create tool listeners needed

$claro_notifier = new Notifier( $claro_event_manager); //listener used for NOTIFICATION system

//$claro_indexer  = new Indexer(& $claro_event_manager);

//4.Register listener in the event manager for the NOTIFICATION system

$notif_listen1  = $claro_notifier->addListener( 'update', "document_visible");
$notif_listen2  = $claro_notifier->addListener( 'update', "document_file_added");
$notif_listen3  = $claro_notifier->addListener( 'update', "document_file_modified");
$notif_listen4  = $claro_notifier->addListener( 'update', "document_htmlfile_created");
$notif_listen5  = $claro_notifier->addListener( 'update', "document_htmlfile_edited");
$notif_listen6  = $claro_notifier->addListener( 'update', "agenda_event_added");
$notif_listen7  = $claro_notifier->addListener( 'update', "agenda_event_modified");
$notif_listen8  = $claro_notifier->addListener( 'update', "anouncement_added");
$notif_listen9  = $claro_notifier->addListener( 'update', "anouncement_modified");
$notif_listen10 = $claro_notifier->addListener( 'update', "course_description_added");
$notif_listen11 = $claro_notifier->addListener( 'update', "course_description_modified");

?>