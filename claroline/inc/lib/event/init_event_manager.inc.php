<?php // $Id$

if ( count( get_included_files() ) == 1 )
{
    die( 'The file ' . basename(__FILE__) . ' cannot be accessed directly, use include instead' );
}


/**
 * Declaration of needed CLASSES for the EventManager pattern
 *
 * @version 1.8 $Revision$
 *
 * @copyright (c) 2001-2006 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @see http://www.claroline.net/wiki/config_def/
 *
 * @package CLEVENT
 *
 * @author Claro Team <cvs@claroline.net>
 * @author Guillaume Lederer <guillaume@claroline.net>
 *
 */

//Main classes needed for the EventManager pattern

uses ( 'core/notify.lib' );

/**
 * Declaration of needed INSTANCES for the EventManager pattern in Claroline
 */

// Create Claroline event listener

$eventNotifier = new ClaroEventGenerator;

// Create tool listeners needed

$claro_notifier = ClaroNotifier::getInstance(); //listener used for NOTIFICATION system


// Register listener in the event manager for the NOTIFICATION system :
// EXAMPLE :
//
//  $notif_listen1 = $claro_notifier->addListener( 'document_visible', 'update' );
//
// 'update' is the name of the function called in the listener class when the event happens
// 'document_visible' is the name of the event that you want to track
// $notif_listen1 is the listener created for this survey

if (claro_is_user_authenticated())
{
   //global events (can happen outside of courses too)

   $claro_notifier->addListener( 'course_deleted', 'eventDelete' );
}

if (claro_is_user_authenticated() && claro_is_in_a_course())
{
    //global events IN COURSE only

    $claro_notifier->addListener( 'toollist_changed', 'eventDefault' );
    $claro_notifier->addListener( 'introsection_modified', 'eventDefault' );
}

if (claro_is_user_authenticated() && claro_is_in_a_course() && claro_is_in_a_tool() )
{

    //document tool events
    
    $currentModuleLabel = claro_get_current_course_tool_data('label');

    if ( $currentModuleLabel == 'CLDOC')
    {
        $claro_notifier->addListener( 'document_visible', 'update' );
        $claro_notifier->addListener( 'document_file_added', 'eventDefault');
        $claro_notifier->addListener( 'document_file_modified', 'updateResource' );
        $claro_notifier->addListener( 'document_moved', 'updateResource' );
        $claro_notifier->addListener( 'document_htmlfile_created', 'eventDefault' );
        $claro_notifier->addListener( 'document_htmlfile_edited', 'eventDefault' );
        $claro_notifier->addListener( 'document_file_deleted', 'eventDelete' );
        $claro_notifier->addListener( 'document_invisible', 'eventDelete' );
    }

    //agenda events

    if ($currentModuleLabel == 'CLCAL')
    {
        $claro_notifier->addListener( 'agenda_event_visible', 'eventDefault' );
        $claro_notifier->addListener( 'agenda_event_added', 'eventDefault' );
        $claro_notifier->addListener( 'agenda_event_modified', 'eventDefault' );
        $claro_notifier->addListener( 'agenda_event_deleted', 'eventDelete' );
        $claro_notifier->addListener( 'agenda_event_invisible', 'eventDelete' );
    }

    //announcement tool events

    if ($currentModuleLabel == 'CLANN')
    {
        $claro_notifier->addListener( 'anouncement_visible', 'eventDefault' );
        $claro_notifier->addListener( 'anouncement_added', 'eventDefault' );
        $claro_notifier->addListener( 'anouncement_modified', 'eventDefault' );
        $claro_notifier->addListener( 'anouncement_deleted', 'eventDelete' );
        $claro_notifier->addListener( 'anouncement_invisible', 'eventDelete' );
    }

    //course description tool events

    if ($currentModuleLabel == 'CLDSC')
    {
        $claro_notifier->addListener( 'course_description_added', 'eventDefault' );
        $claro_notifier->addListener( 'course_description_modified', 'eventDefault' );
        $claro_notifier->addListener( 'course_description_visible', 'eventDefault' );
        $claro_notifier->addListener( 'course_description_deleted', 'eventDelete' );
    }

    //exercise tool events

    if ($currentModuleLabel == 'CLQWZ')
    {
        $claro_notifier->addListener( 'exercise_visible', 'eventDefault' );
        $claro_notifier->addListener( 'exercise_invisible', 'eventDelete' );
        $claro_notifier->addListener( 'exercise_deleted', 'eventDelete' );
    }

    //learning path tool events

    if ($currentModuleLabel == 'CLLNP')
    {
        $claro_notifier->addListener( 'learningpath_created', 'eventDefault' );
        $claro_notifier->addListener( 'learningpath_visible', 'eventDefault' );
        $claro_notifier->addListener( 'learningpath_invisible', 'eventDelete' );
        $claro_notifier->addListener( 'learningpath_deleted', 'eventDelete' );
    }

    //assignment tool events

    if ($currentModuleLabel == 'CLWRK')
    {
        $claro_notifier->addListener( 'work_added', 'eventDefault' );
        $claro_notifier->addListener( 'work_visible', 'eventDefault' );
        $claro_notifier->addListener( 'work_invisible', 'eventDelete' );
        $claro_notifier->addListener( 'work_deleted', 'eventDelete' );
        $claro_notifier->addListener( 'work_submission_posted', 'eventDefault' );
        $claro_notifier->addListener( 'work_correction_posted', 'eventDefault' );
        $claro_notifier->addListener( 'work_feedback_posted', 'eventDefault' );
    }

    //forum tool events

    if ($currentModuleLabel == 'CLFRM')
    {
        $claro_notifier->addListener( 'forum_new_topic', 'eventDefault' );
        $claro_notifier->addListener( 'forum_answer_topic', 'eventDefault' );
    }

    //group tool events

    if ($currentModuleLabel == 'CLGRP')
    {
        $claro_notifier->addListener( 'group_deleted', 'eventDelete' );
    }

    //wiki tool events

    if ($currentModuleLabel == 'CLWIKI')
    {
        $claro_notifier->addListener( 'wiki_added', 'eventDefault' );
        $claro_notifier->addListener( 'wiki_modified', 'eventDefault' );
        $claro_notifier->addListener( 'wiki_deleted', 'eventDelete' );
        $claro_notifier->addListener( 'wiki_page_modified', 'eventDefault' );
        $claro_notifier->addListener( 'wiki_page_added', 'eventDefault' );
    }
}
?>