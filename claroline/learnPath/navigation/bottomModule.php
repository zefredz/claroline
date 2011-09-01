<?php // $Id$
/**
 * CLAROLINE 
 *
 * @version 1.8 $Revision$
 *
 * @copyright   (c) 2001-2011, Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @author Piraux S�bastien <pir@cerdecam.be>
 * @author Lederer Guillaume <led@cerdecam.be>
 *
 * @package CLLNP
 * @subpackage navigation
 *
 * DESCRIPTION:
 * ************
 * This script creates the bottom frame needed when we browse a module that needs to use frame
 * This appens when the module is SCORM (@link http://www.adlnet.org )or made by the user with his own html pages.
 *
 */
require '../../inc/claro_init_global.inc.php';
// header
$hide_banner = TRUE;
$hide_body = TRUE;

// Turn off session lost
$warnSessionLost = false ;


if ( method_exists( Claroline::getDisplay()->body, 'hideCourseTitleAndTools' ) )
{
    Claroline::getDisplay()->body->hideCourseTitleAndTools();
}

Claroline::getDisplay()->banner->hide();
echo Claroline::getDisplay()->render();
