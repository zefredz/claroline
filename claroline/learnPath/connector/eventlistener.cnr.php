<?php // $Id$

    // vim: expandtab sw=4 ts=4 sts=4:

    if ( count( get_included_files() ) == 1 )
    {
        die( 'The file ' . basename(__FILE__) . ' cannot be accessed directly, use include instead' );
    }

    $claroline->notification->addListener( 'learningpath_created', 'eventDefault' );
    $claroline->notification->addListener( 'learningpath_visible', 'eventDefault' );
    $claroline->notification->addListener( 'learningpath_invisible', 'eventDelete' );
    $claroline->notification->addListener( 'learningpath_deleted', 'eventDelete' );
?>