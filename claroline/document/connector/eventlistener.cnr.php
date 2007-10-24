<?php // $Id$

    // vim: expandtab sw=4 ts=4 sts=4:

    if ( count( get_included_files() ) == 1 )
    {
        die( 'The file ' . basename(__FILE__) . ' cannot be accessed directly, use include instead' );
    }

    $claroline->notification->addListener( 'document_visible', 'update' );
    $claroline->notification->addListener( 'document_file_added', 'eventDefault');
    $claroline->notification->addListener( 'document_file_modified', 'updateResource' );
    $claroline->notification->addListener( 'document_moved', 'updateResource' );
    $claroline->notification->addListener( 'document_htmlfile_created', 'eventDefault' );
    $claroline->notification->addListener( 'document_htmlfile_edited', 'eventDefault' );
    $claroline->notification->addListener( 'document_file_deleted', 'eventDelete' );
    $claroline->notification->addListener( 'document_invisible', 'eventDelete' );
?>