<?php // $Id$

// vim: expandtab sw=4 ts=4 sts=4:

if ( count( get_included_files() ) == 1 )
{
    die( 'The file ' . basename(__FILE__) . ' cannot be accessed directly, use include instead' );
}

$GLOBALS['claroline']->notification->addListener( 'wiki_added',            'modificationDefault' );
$GLOBALS['claroline']->notification->addListener( 'wiki_modified',         'modificationDefault' );
$GLOBALS['claroline']->notification->addListener( 'wiki_deleted',          'modificationDelete' );
$GLOBALS['claroline']->notification->addListener( 'wiki_page_modified',    'modificationDefault' );
$GLOBALS['claroline']->notification->addListener( 'wiki_page_added',       'modificationDefault' );
