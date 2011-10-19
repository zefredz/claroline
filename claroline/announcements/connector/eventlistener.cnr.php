<?php // $Id$
if ( count( get_included_files() ) == 1 ) die( '---' );

/**
 * CLAROLINE
 *
 * @version     $Revision$
 * @copyright   (c) 2001-2011, Universite catholique de Louvain (UCL)
 * @license     http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @author      Claro Team <cvs@claroline.net>
 */

// vim: expandtab sw=4 ts=4 sts=4:

$claroline->notification->addListener( 'anouncement_visible',   'modificationDefault' );
$claroline->notification->addListener( 'anouncement_added',     'modificationDefault' );
$claroline->notification->addListener( 'anouncement_modified',  'modificationDefault' );
$claroline->notification->addListener( 'anouncement_deleted',   'modificationDelete' );
$claroline->notification->addListener( 'anouncement_invisible', 'modificationDelete' );
