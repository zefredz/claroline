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

if (isset($_uid))
{
   //global events (can happen outside of courses too)
   
   $claro_notifier->addListener( 'delete_notif', "course_deleted"); 
}

if (isset($_uid) && isset($_cid))
{
    //global events IN COURSE only
     
    $claro_notifier->addListener( 'update', "toollist_changed");
    $claro_notifier->addListener( 'update', "introsection_modified");
}

if (isset($_uid) && isset($_cid) && isset($_courseTool)) 
{  
        
    //document tool events
    
    if (isset($_courseTool['label']) && $_courseTool['label'] == "CLDOC___") 
    {
    $claro_notifier->addListener( 'update',       "document_visible");
    $claro_notifier->addListener( 'update',       "document_file_added");
    $claro_notifier->addListener( 'update',       "document_file_modified");
    $claro_notifier->addListener( 'update',       "document_htmlfile_created");
    $claro_notifier->addListener( 'update',       "document_htmlfile_edited");
    $claro_notifier->addListener( 'delete_notif', "document_file_deleted");
    $claro_notifier->addListener( 'delete_notif', "document_invisible");
    }
    
    //agenda events

    if (isset($_courseTool['label']) && $_courseTool['label'] == "CLCAL___") 
    {
    $claro_notifier->addListener( 'update',       "agenda_event_visible");
    $claro_notifier->addListener( 'update',       "agenda_event_added");
    $claro_notifier->addListener( 'update',       "agenda_event_modified");
    $claro_notifier->addListener( 'delete_notif', "agenda_event_deleted");
    $claro_notifier->addListener( 'delete_notif', "agenda_event_invisible");
    }
    
    //announcement tool events
    
    if (isset($_courseTool['label']) && $_courseTool['label'] == "CLANN___") 
    {
    $claro_notifier->addListener( 'update',       "anouncement_visible");
    $claro_notifier->addListener( 'update',       "anouncement_added");
    $claro_notifier->addListener( 'update',       "anouncement_modified");
    $claro_notifier->addListener( 'delete_notif', "anouncement_deleted");
    $claro_notifier->addListener( 'delete_notif', "anouncement_invisible");
    }
    
    //course description tool events

    if (isset($_courseTool['label']) && $_courseTool['label'] == "CLDSC___") 
    {
    $claro_notifier->addListener( 'update',       "course_description_added");
    $claro_notifier->addListener( 'update',       "course_description_modified");
    $claro_notifier->addListener( 'update',       "course_description_visible");
    $claro_notifier->addListener( 'delete_notif', "course_description_deleted");
    }
    
    //exercise tool events
    
    if (isset($_courseTool['label']) && $_courseTool['label'] == "CLQWZ___") 
    {
    $claro_notifier->addListener( 'update',       "exercise_visible");
    $claro_notifier->addListener( 'delete_notif', "exercise_invisible");
    $claro_notifier->addListener( 'delete_notif', "exercise_deleted");
    }
    
    //learning path tool events
    
    if (isset($_courseTool['label']) && $_courseTool['label'] == "CLLNP___") 
    {
    $claro_notifier->addListener( 'update',       "learningpath_created");
    $claro_notifier->addListener( 'update',       "learningpath_visible");
    $claro_notifier->addListener( 'delete_notif', "learningpath_invisible");
    $claro_notifier->addListener( 'delete_notif', "learningpath_deleted");
    }
    
    //assignment tool events
    
    if (isset($_courseTool['label']) && $_courseTool['label'] == "CLWRK___") 
    {
    $claro_notifier->addListener( 'update',       "work_added");
    $claro_notifier->addListener( 'update',       "work_visible");
    $claro_notifier->addListener( 'delete_notif', "work_invisible");
    $claro_notifier->addListener( 'delete_notif', "work_deleted");
    $claro_notifier->addListener( 'update',       "work_submission_posted");
    $claro_notifier->addListener( 'update',       "work_correction_posted");
    $claro_notifier->addListener( 'update',       "work_feedback_posted");   
    }
    
    //forum tool events
    
    if (isset($_courseTool['label']) && $_courseTool['label'] == "CLFRM___") 
    {
    $claro_notifier->addListener( 'update', "forum_new_topic");
    $claro_notifier->addListener( 'update', "forum_answer_topic");
    }
    
    //group tool events
    
    if (isset($_courseTool['label']) && $_courseTool['label'] == "CLGRP___") 
    {
    $claro_notifier->addListener( 'delete_notif', "group_deleted");
    }
    
}
    
// wiki tool events
// anonymous user can contribute to wiki tool 

if (isset($_cid) && isset($_courseTool['label']) && $_courseTool['label'] == "CLWIKI__") 
{
    $claro_notifier->addListener( 'update',       "wiki_added");
    $claro_notifier->addListener( 'update',       "wiki_modified");
    $claro_notifier->addListener( 'delete_notif', "wiki_deleted");
    $claro_notifier->addListener( 'update',       "wiki_page_modified");
    $claro_notifier->addListener( 'update',       "wiki_page_added");
}

?>
