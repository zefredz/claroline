<?php // $Id$

// vim: expandtab sw=4 ts=4 sts=4:

if ( count( get_included_files() ) == 1 ) die( '---' );

$claroline->notification->addListener( 'exercise_visible',      'modificationDefault' );
$claroline->notification->addListener( 'exercise_invisible',    'modificationDelete' );
$claroline->notification->addListener( 'exercise_deleted',      'modificationDelete' );

$claroline->notification->addListener( 'exercise_added',        'calendarAddEvent' );
$claroline->notification->addListener( 'exercise_deleted',      'calendarDeleteEvent' );
$claroline->notification->addListener( 'exercise_updated',      'calendarUpdateEvent' );