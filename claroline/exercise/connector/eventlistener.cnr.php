<?php // $Id$

// vim: expandtab sw=4 ts=4 sts=4:

if ( count( get_included_files() ) == 1 ) die( '---' );

$GLOBALS['claroline']->notification->addListener( 'exercise_visible',      'modificationDefault' );
$GLOBALS['claroline']->notification->addListener( 'exercise_invisible',    'modificationDelete' );
$GLOBALS['claroline']->notification->addListener( 'exercise_deleted',      'modificationDelete' );

$GLOBALS['claroline']->notification->addListener( 'exercise_added',        'calendarAddEvent' );
$GLOBALS['claroline']->notification->addListener( 'exercise_deleted',      'calendarDeleteEvent' );
$GLOBALS['claroline']->notification->addListener( 'exercise_updated',      'calendarUpdateEvent' );