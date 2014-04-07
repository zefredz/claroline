<?php // $Id$

// vim: expandtab sw=4 ts=4 sts=4:

if ( count( get_included_files() ) == 1 ) die( '---' );

$GLOBALS['claroline']->notification->addListener( 'document_visible',          'modificationDefault' );
$GLOBALS['claroline']->notification->addListener( 'document_file_added',       'modificationDefault');
$GLOBALS['claroline']->notification->addListener( 'document_file_modified',    'modificationUpdate' );
$GLOBALS['claroline']->notification->addListener( 'document_moved',            'modificationUpdate' );
$GLOBALS['claroline']->notification->addListener( 'document_htmlfile_created', 'modificationDefault' );
$GLOBALS['claroline']->notification->addListener( 'document_htmlfile_edited',  'modificationDefault' );
$GLOBALS['claroline']->notification->addListener( 'document_file_deleted',     'modificationDelete' );
$GLOBALS['claroline']->notification->addListener( 'document_invisible',        'modificationDelete' );
