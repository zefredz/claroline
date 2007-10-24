<?php // $Id$

    // vim: expandtab sw=4 ts=4 sts=4:

    if ( count( get_included_files() ) == 1 )
    {
        die( 'The file ' . basename(__FILE__) . ' cannot be accessed directly, use include instead' );
    }

    $claroline->notification->addListener( 'anouncement_visible', 'eventDefault' );
    $claroline->notification->addListener( 'anouncement_added', 'eventDefault' );
    $claroline->notification->addListener( 'anouncement_modified', 'eventDefault' );
    $claroline->notification->addListener( 'anouncement_deleted', 'eventDelete' );
    $claroline->notification->addListener( 'anouncement_invisible', 'eventDelete' );
?>