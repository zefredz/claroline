<?php // $Id$

// vim: expandtab sw=4 ts=4 sts=4:

if ( count( get_included_files() ) == 1 )
{
    die( 'The file ' . basename(__FILE__) . ' cannot be accessed directly, use include instead' );
}

$GLOBALS['claroline']->notification->addListener( 'work_added',                'modificationDefault' );
$GLOBALS['claroline']->notification->addListener( 'work_visible',              'modificationDefault' );
$GLOBALS['claroline']->notification->addListener( 'work_invisible',            'modificationDelete' );
$GLOBALS['claroline']->notification->addListener( 'work_deleted',              'modificationDelete' );
$GLOBALS['claroline']->notification->addListener( 'work_submission_posted',    'modificationDefault' );
$GLOBALS['claroline']->notification->addListener( 'work_correction_posted',    'modificationDefault' );
$GLOBALS['claroline']->notification->addListener( 'work_feedback_posted',      'modificationDefault' );

$GLOBALS['claroline']->notification->addListener( 'work_added',                'calendarAddEvent' );
$GLOBALS['claroline']->notification->addListener( 'work_deleted',              'calendarDeleteEvent' );
$GLOBALS['claroline']->notification->addListener( 'work_updated',              'calendarUpdateEvent' );
