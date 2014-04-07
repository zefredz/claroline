<?php // $Id$
// vim: expandtab sw=4 ts=4 sts=4:

if ( count( get_included_files() ) == 1 ) die( '---' );

/**
 * CLAROLINE
 *
 * @version     $Revision$
 * @copyright   (c) 2001-2014, Universite catholique de Louvain (UCL)
 * @license     http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @author      Claro Team <cvs@claroline.net>
 */

$GLOBALS['claroline']->notification->addListener( 'course_description_added',      'modificationDefault' );
$GLOBALS['claroline']->notification->addListener( 'course_description_modified',   'modificationDefault' );
$GLOBALS['claroline']->notification->addListener( 'course_description_visible',    'modificationDefault' );
$GLOBALS['claroline']->notification->addListener( 'course_description_deleted',    'modificationDelete' );
