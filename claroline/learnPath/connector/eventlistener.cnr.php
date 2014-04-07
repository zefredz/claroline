<?php // $Id$

// vim: expandtab sw=4 ts=4 sts=4:

if ( count( get_included_files() ) == 1 )
{
    die( 'The file ' . basename(__FILE__) . ' cannot be accessed directly, use include instead' );
}

$GLOBALS['claroline']->notification->addListener( 'learningpath_created',      'modificationDefault' );
$GLOBALS['claroline']->notification->addListener( 'learningpath_visible',      'modificationDefault' );
$GLOBALS['claroline']->notification->addListener( 'learningpath_invisible',    'modificationDelete' );
$GLOBALS['claroline']->notification->addListener( 'learningpath_deleted',      'modificationDelete' );
