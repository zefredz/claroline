<?php // $Id$

    if ( count( get_included_files() ) == 1 )
    {
        die( 'The file ' . basename(__FILE__) . ' cannot be accessed directly, use include instead' );
    }

    /**
     * Event Notification
     *
     * @version     1.9 $Revision$
     * @copyright   2001-2007 Universite catholique de Louvain (UCL)
     * @author      Claroline Team <info@claroline.net>
     * @author      Guillaume Lederer <guillaume@claroline.net>
     * @author      Frederic Minne <zefredz@claroline.net>
     * @license     http://www.gnu.org/copyleft/gpl.html
     *              GNU GENERAL PUBLIC LICENSE version 2.0
     * @package     KERNEL
     */

    // for backward compatibility
    $eventNotifier = $claroline->notifier;
    $claro_notifier = $claroline->notification; //listener used for NOTIFICATION system


    // Register listener in the event manager for the NOTIFICATION system :
    // EXAMPLE :
    //
    //  $notif_listen1 = $claroline->notification->addListener( 'document_visible', 'update' );
    //
    // 'update' is the name of the function called in the listener class when the event happens
    // 'document_visible' is the name of the event that you want to track
    // $notif_listen1 is the listener created for this survey

    if ( claro_is_user_authenticated() )
    {
       //global events (can happen outside of courses too)

       $claroline->notification->addListener( 'course_deleted', 'eventDelete' );
    }

    if ( claro_is_user_authenticated() && claro_is_in_a_course() )
    {
        //global events IN COURSE only

        $claroline->notification->addListener( 'toollist_changed', 'eventDefault' );
        $claroline->notification->addListener( 'introsection_modified', 'eventDefault' );
    }

    if ( claro_is_in_a_group() )
    {
        $claroline->notification->addListener( 'group_deleted', 'eventDelete' );
    }

    if ( claro_is_in_a_tool() )
    {
        load_current_module_listeners();
    }
?>