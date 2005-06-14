<?php //$Id$
/**
 * Declaration of needed CLASSES for the EventManager pattern
 *
 * @version 1.7 $Revision$
 * 
 * @copyright (c) 2001-2005 Universite catholique de Louvain (UCL)
 * 
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE 
 *
 * @see http://www.claroline.net/wiki/config_def/
 *
 * @package CLEVENT
 *
 * @author Claro Team <cvs@claroline.net> 
 * @author Guillaume Lederer <guim@claroline.net> 
 *
 */
 
//Main classes needed for the EventManager pattern

require_once($includePath . '/lib/event/class.event.php');

require_once($includePath . '/lib/event/notifier.php');
  
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

//4.Register listener in the event manager for the NOTIFICATION system :
// EXAMPLE : 
//
//  $notif_listen1 = $claro_notifier->addListener( 'update', "document_visible");
//
// 'update' is the name of the function called in the listener class when the event happens
// "document_visible" is the name of the event that you want to track
// $notif_listen1 is the listener created for this survey

$notif_listen1  = $claro_notifier->addListener( 'update', "document_visible");
$notif_listen2  = $claro_notifier->addListener( 'update', "document_file_added");
$notif_listen3  = $claro_notifier->addListener( 'update', "document_file_modified");
$notif_listen4  = $claro_notifier->addListener( 'update', "document_htmlfile_created");
$notif_listen5  = $claro_notifier->addListener( 'update', "document_htmlfile_edited");
$notif_listen6  = $claro_notifier->addListener( 'delete_notif', "document_file_deleted");
$notif_listen7  = $claro_notifier->addListener( 'delete_notif', "document_invisible");

$notif_listen7  = $claro_notifier->addListener( 'update', "agenda_event_visible");
$notif_listen8  = $claro_notifier->addListener( 'update', "agenda_event_added");
$notif_listen9  = $claro_notifier->addListener( 'update', "agenda_event_modified");
$notif_listen10  = $claro_notifier->addListener( 'delete_notif', "agenda_event_deleted");
$notif_listen11  = $claro_notifier->addListener( 'delete_notif', "agenda_event_invisible");

$notif_listen12  = $claro_notifier->addListener( 'update', "anouncement_visible");
$notif_listen13  = $claro_notifier->addListener( 'update', "anouncement_added");
$notif_listen14  = $claro_notifier->addListener( 'update', "anouncement_modified");
$notif_listen15  = $claro_notifier->addListener( 'delete_notif', "anouncement_deleted");
$notif_listen16  = $claro_notifier->addListener( 'delete_notif', "anouncement_invisible");


$notif_listen17 = $claro_notifier->addListener( 'update', "course_description_added");
$notif_listen18 = $claro_notifier->addListener( 'update', "course_description_modified");
$notif_listen19 = $claro_notifier->addListener( 'update', "course_description_visible");

$notif_listen20 = $claro_notifier->addListener( 'update', "exercise_visible");
$notif_listen21 = $claro_notifier->addListener( 'delete_notif', "exercise_invisible");
$notif_listen22 = $claro_notifier->addListener( 'delete_notif', "exercise_deleted");

$notif_listen23 = $claro_notifier->addListener( 'update', "learningpath_created");
$notif_listen24 = $claro_notifier->addListener( 'update', "learningpath_visible");
$notif_listen25 = $claro_notifier->addListener( 'delete_notif', "learningpath_invisible");
$notif_listen26 = $claro_notifier->addListener( 'delete_notif', "learningpath_deleted");

$notif_listen27 = $claro_notifier->addListener( 'update', "work_added");
$notif_listen28 = $claro_notifier->addListener( 'update', "work_visible");
$notif_listen29 = $claro_notifier->addListener( 'delete_notif', "work_invisible");
$notif_listen30 = $claro_notifier->addListener( 'delete_notif', "work_deleted");
$notif_listen31 = $claro_notifier->addListener( 'update', "work_submission_posted");
$notif_listen32 = $claro_notifier->addListener( 'update', "work_correction_posted");

$notif_listen33 = $claro_notifier->addListener( 'update', "forum_new_topic");
$notif_listen34 = $claro_notifier->addListener( 'update', "introsection_modified");
$notif_listen35 = $claro_notifier->addListener( 'update', "toollist_changed");
?>