<?php // $Id$

    // vim: expandtab sw=4 ts=4 sts=4:

    if ( count( get_included_files() ) == 1 )
    {
        die( 'The file ' . basename(__FILE__) . ' cannot be accessed directly, use include instead' );
    }

    $claroline->notification->addListener( 'work_added', 'eventDefault' );
    $claroline->notification->addListener( 'work_visible', 'eventDefault' );
    $claroline->notification->addListener( 'work_invisible', 'eventDelete' );
    $claroline->notification->addListener( 'work_deleted', 'eventDelete' );
    $claroline->notification->addListener( 'work_submission_posted', 'eventDefault' );
    $claroline->notification->addListener( 'work_correction_posted', 'eventDefault' );
    $claroline->notification->addListener( 'work_feedback_posted', 'eventDefault' );
?>