<?php // $Id$

/**
 * CLAROLINE 
 *
 * @version 1.11 $Revision$
 *
 * @copyright   (c) 2001-2011, Universite catholique de Louvain (UCL)
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @author Piraux Sebastien <pir@cerdecam.be>
 * @author Lederer Guillaume <led@cerdecam.be
 * @package CLLNP
 * @subpackage navigation
 *
 * This script creates the top frame needed when we browse a module that needs to use frame
 * This appens when the module is SCORM (@link http://www.adlnet.org )
 * or made by the user with his own html pages.
 */

/*======================================
       CLAROLINE MAIN
  ======================================*/

require '../../inc/claro_init_global.inc.php';

$interbredcrump[]= array ("url"=>"../learningPathList.php", "name"=> get_lang('Learning path list'));
if ( claro_is_allowed_to_edit() && (!isset($_SESSION['asStudent']) || $_SESSION['asStudent'] == 0 ) )
{
       $interbredcrump[]= array ("url"=>"../learningPathAdmin.php", "name"=> get_lang('Learning path admin'));
}
else
{
       $interbredcrump[]= array ("url"=>"../learningPath.php", "name"=> get_lang('Learning path'));
}
$interbredcrump[]= array ("url"=>"../module.php", "name"=> get_lang('Module'));
//$htmlHeadXtra[] = "<script src=\"APIAdapter.js\" type=\"text/javascript\" language=\"JavaScript\">";
//header

// Turn off session lost
$warnSessionLost = false ;

Claroline::getDisplay()->body->hideCourseTitleAndTools();

// footer
Claroline::getDisplay()->footer->hide();

echo Claroline::getDisplay()->render();
