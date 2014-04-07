<?php // $Id$

// vim: expandtab sw=4 ts=4 sts=4:

if ( count( get_included_files() ) == 1 )
{
    die( 'The file ' . basename(__FILE__) . ' cannot be accessed directly, use include instead' );
}

$GLOBALS['claroline']->notification->addListener( 'forum_new_topic',       'modificationDefault' );
$GLOBALS['claroline']->notification->addListener( 'forum_answer_topic',    'modificationDefault' );
$GLOBALS['claroline']->notification->addListener( 'forum_new_post',    'modificationDefault' );
$GLOBALS['claroline']->notification->addListener( 'forum_read_topic',    'modificationDefault' );
