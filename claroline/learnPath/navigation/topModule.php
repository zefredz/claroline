<?
// $Id$
/*
  +----------------------------------------------------------------------+
  | CLAROLINE 1.5.*                                                      |
  +----------------------------------------------------------------------+
  | Copyright (c) 2001, 2004 Universite catholique de Louvain (UCL)      |
  +----------------------------------------------------------------------+
  | This source file is subject to the GENERAL PUBLIC LICENSE,           |
  | available through the world-wide-web at                              |
  | http://www.gnu.org/copyleft/gpl.html                                 |
  +----------------------------------------------------------------------+
  | Authors: Piraux Sébastien <pir@cerdecam.be>                          |
  |          Lederer Guillaume <led@cerdecam.be>                         |
  +----------------------------------------------------------------------+
 */
 /**
  * This script creates the top frame needed when we browse a module that needs to use frame
  * This appens when the module is SCORM (@link http://www.adlnet.org )or made by the user with his own html pages.
  * @package learningpath
  * @subpackage navigation
  * @author Piraux Sébastien <pir@cerdecam.be>
  * @author Lederer Guillaume <led@cerdecam.be>
  * @filesource
  */

/*======================================
       CLAROLINE MAIN
  ======================================*/

  $langFile = "learnPath";

  require '../../inc/claro_init_global.inc.php';

  $interbredcrump[]= array ("url"=>"../learningPathList.php", "name"=> $langLearningPathList);
  if ( $is_courseAdmin && (!isset($_SESSION['asStudent']) || $_SESSION['asStudent'] == 0 ) )
  {
       $interbredcrump[]= array ("url"=>"../learningPathAdmin.php", "name"=> $langLearningPathAdmin);
  }
  else
  {
       $interbredcrump[]= array ("url"=>"../learningPath.php", "name"=> $langLearningPath);
  }
  $interbredcrump[]= array ("url"=>"../module.php", "name"=> $langModule);
  //$htmlHeadXtra[] = "<script src=\"APIAdapter.js\" type=\"text/javascript\" language=\"JavaScript\">";
  //header
  @include($includePath."/claro_init_header.inc.php");


?>
