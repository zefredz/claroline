<?
// $Id$
/*
  +----------------------------------------------------------------------+
  | CLAROLINE version 1.5.*                                              |
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
  * This script is the main page loaded when user start viewing a module in the browser.
  * We define here the frameset containing the launcher module (SCO if it is a SCORM conformant one)
  * and a top and bottom frame to display the claroline banners.
  * If the module is an exercise of claroline, no frame is created,
  * we redirect to exercise_submit.php page in a path mode
  *
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

  if ( ! $_cid) claro_disp_select_course();
  if ( ! $is_courseAllowed) claro_disp_auth_form();
  
  // Tables names

  $TABLELEARNPATH         = $_course['dbNameGlu']."lp_learnPath";
  $TABLEMODULE            = $_course['dbNameGlu']."lp_module";
  $TABLELEARNPATHMODULE   = $_course['dbNameGlu']."lp_rel_learnPath_module";
  $TABLEASSET             = $_course['dbNameGlu']."lp_asset";
  $TABLEUSERMODULEPROGRESS= $_course['dbNameGlu']."lp_user_module_progress";


  // lib of this tool


  @include($includePath."/lib/learnPath.lib.inc.php");

  if(isset ($_GET['viewModule_id']) && $_GET['viewModule_id'] != '')
        $_SESSION['module_id'] = $_GET['viewModule_id'];
  // SET USER_MODULE_PROGRESS IF NOT SET

  if($_uid) // if not anonymous
  {
      $sql = "SELECT *
                FROM `".$TABLEUSERMODULEPROGRESS."` AS UMP, `".$TABLELEARNPATHMODULE."` AS LPM
               WHERE UMP.`user_id` = '$_uid'
                 AND UMP.`learnPath_module_id` = LPM.`learnPath_module_id`
                 AND LPM.`learnPath_id` = ".$_SESSION['path_id']."
                 AND LPM.`module_id` = ".$_SESSION['module_id'];
      $query1 = claro_sql_query($sql);
      $num = mysql_num_rows($query1);

      $sql = "SELECT *
                FROM `".$TABLELEARNPATHMODULE."`
               WHERE `learnPath_id` = ".$_SESSION['path_id']."
                 AND `module_id` = ".$_SESSION['module_id'];
      $query = claro_sql_query($sql);
      $LPM = mysql_fetch_array($query);


      // if never intialised : create an empty user_module_progress line
      if ($num == 0)
      {

            $sql = "INSERT
                      INTO `".$TABLEUSERMODULEPROGRESS."`
                           ( `user_id` , `learnPath_id` , `learnPath_module_id` )
                    VALUES ( '$_uid' , ".$_SESSION['path_id']." , ".$LPM['learnPath_module_id'].")";
            claro_sql_query($sql);
      }
  }  // else anonymous : record nothing !


  // Get info about launched module

  $query = "SELECT `contentType`,`startAsset_id`
              FROM `".$TABLEMODULE."`
             WHERE `module_id` = ".$_SESSION['module_id'];

  $result      = claro_sql_query($query);
  $module        = mysql_fetch_array($result);


  $assetQuery = "SELECT `path`
                   FROM `".$TABLEASSET."`
                  WHERE `asset_id` = ".$module['startAsset_id'];

  $assetResult = claro_sql_query($assetQuery);
  $asset   = mysql_fetch_array($assetResult);


  // Get path of file of the starting asset to launch

  $withFrames = false;
  switch ($module['contentType'])
  {
          case CTDOCUMENT_ :
                if($_uid)
                {
                    // if credit was already set this query changes nothing else it update the query made at the beginning of this script
                    $sql = "UPDATE `".$TABLEUSERMODULEPROGRESS."`
                               SET `credit` = 1,
                                   `raw` = 100,
                                   `lesson_status` = 'completed',
                                   `scoreMin` = 0,
                                   `scoreMax` = 100
                             WHERE `user_id` = $_uid
                               AND `learnPath_module_id` = ".$LPM['learnPath_module_id'];

                    claro_sql_query($sql);
                } // else anonymous : record nothing

                $startAssetPage = $asset['path'];
                // str_replace("%2F","/",urlencode($startAssetPage)) is useed to avoid problems with accents in filename.
                // without tracking of document
                //$moduleStartAssetPage = $coursesRepositoryWeb.$_course['path']."/document".str_replace("%2F","/",urlencode($startAssetPage));
                // with tracking of document
                $moduleStartAssetPage = $clarolineRepositoryWeb."/document/goto/?doc_url=".str_replace("%2F","/",urlencode($startAssetPage));

                $withFrames = true;
                break;
          case CTEXERCISE_ :
               // clean session vars of exercise
                unset($_SESSION['objExercise']);
                unset($_SESSION['objQuestion']);
                unset($_SESSION['objAnswer']);
                unset($_SESSION['questionList']);
                unset($_SESSION['exerciseResult']);
                session_unregister('objExercise');
                session_unregister('objQuestion');
                session_unregister('objAnswer');
                session_unregister('questionList');
                session_unregister('exerciseResult');
                $_SESSION['inPathMode'] = true;
                $startAssetpage = $clarolineRepositoryWeb."exercice/exercice_submit.php";
                $exerciseId     = $asset['path'];
                $moduleStartAssetPage = $startAssetpage."?exerciseId=".$exerciseId;
                break;
          case CTSCORM_ :
                // real scorm content method
                $startAssetPage = $asset['path'];
                $modulePath     = "path_".$_SESSION['path_id'];
                $moduleStartAssetPage = $coursesRepositoryWeb.$_course['path']."/scormPackages/".$modulePath.$startAssetPage;
                break;
          case CTCLARODOC_ :
               break;
  } // end switch

?>


<html>

  <head>
  
<?php
   // add the update frame if this is a SCORM module   
   if ( $module['contentType'] == CTSCORM_ )
   {
      
      include("scormAPI.inc.php");
      echo "<frameset border='0' cols='0,20%,80%' frameborder='no' onUnload=\"LMSCommit('')\">
            <frame src='updateProgress.php' name='upFrame'>";
      
   }
   else
   {
      echo "<frameset border='0' cols='20%,80%' frameborder='yes'>";
   }
?>    
    <frame src="tableOfContent.php" name="tocFrame" />
    <frame src="<?= $moduleStartAssetPage; ?>" name="scoFrame">

    </frameset>
  <noframes>
<body>
<?php
  echo $langBrowserCannotSeeFrames;
?>
   </body>
</noframes>
</html>
