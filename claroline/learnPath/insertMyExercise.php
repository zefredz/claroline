<?php
    // $Id$
/*
  +----------------------------------------------------------------------+
  | CLAROLINE version 1.5 $Revision$                               |
  +----------------------------------------------------------------------+
  | Copyright (c) 2001, 2004 Universite catholique de Louvain (UCL)      |
  +----------------------------------------------------------------------+
  | This source file is subject to the GENERAL PUBLIC LICENSE,           |
  | available through the world-wide-web at                              |
  | http://www.gnu.org/copyleft/gpl.html                                 |
  +----------------------------------------------------------------------+
  | Authors:  Piraux Sébastien <pir@cerdecam.be>                         |
  |          Lederer Guillaume <led@cerdecam.be>                         |
  +----------------------------------------------------------------------+

  DESCRIPTION:
  ****

*/

/*======================================
       CLAROLINE MAIN
  ======================================*/

  $langFile = "learnPath";

  $tlabelReq = 'CLLNP___';
  require '../inc/claro_init_global.inc.php';
  
  // if there is an auth information missing redirect to the first page of lp tool 
  // this page will do the necessary to auth the user, 
  // when leaving a course all the LP sessions infos are cleared so we use this trick to avoid other errors
  if ( ! $_cid) header("Location:./learningPathList.php");
  if ( ! $is_courseAllowed) header("Location:./learningPathList.php");
  
  $htmlHeadXtra[] =
            "<script>
            function confirmation (name)
            {
                if (confirm(\" $langAreYouSureDeleteModule \"+ name + \" ?\"))
                    {return true;}
                else
                    {return false;}
            }
            </script>";

  $interbredcrump[]= array ("url"=>"../learnPath/learningPathList.php", "name"=> $langLearningPathList);
  $interbredcrump[]= array ("url"=>"../learnPath/learningPathAdmin.php", "name"=> $langLearningPathAdmin);
  $nameTools = $langInsertMyExerciseToolName;

  //header
  @include($includePath."/claro_init_header.inc.php");

  //lib of document tool
  @include($includePath."/lib/fileDisplay.lib.php");

  // tables names
  $TABLELEARNPATH         = $_course['dbNameGlu']."lp_learnPath";
  $TABLEMODULE            = $_course['dbNameGlu']."lp_module";
  $TABLELEARNPATHMODULE   = $_course['dbNameGlu']."lp_rel_learnPath_module";
  $TABLEASSET             = $_course['dbNameGlu']."lp_asset";
  $TABLEUSERMODULEPROGRESS= $_course['dbNameGlu']."lp_user_module_progress";

  // exercises table name
  $TABLEEXERCISES                = $_course['dbNameGlu']."quiz_test";


  //lib of this tool
  include($includePath."/lib/learnPath.lib.inc.php");

  // $_SESSION
  if ( !isset($_SESSION['path_id']) )
  {
        die ("<center> Not allowed ! (path_id not set :@ )</center>");
  }


/*======================================
       CLAROLINE MAIN
  ======================================*/


      // main page

   $is_AllowedToEdit = $is_courseAdmin;
   if (! $is_AllowedToEdit ) header("Location:./learningPathList.php");



   // display title

  claro_disp_tool_title($nameTools);

    // see checked exercises to add

    $sql = "SELECT *
             FROM `".$TABLEEXERCISES;
    $resultex = claro_sql_query($sql);

    // for each exercise checked, try to add it to the learning path.

    while ($listex = mysql_fetch_array($resultex) )
    {


       if ( isset($insertExercise) && isset($_GET['check_'.$listex['id']]) )  //add
       {
           $insertedExercise = $listex['id'];

           // check if a module of this course already used the same exercise
           $sql = "SELECT *
                     FROM `".$TABLEMODULE."` AS M, `".$TABLEASSET."` AS A
                    WHERE A.`module_id` = M.`module_id`
                      AND A.`path` LIKE \"".$insertedExercise."\"
                      AND M.`contentType` = \"".CTEXERCISE_."\"";
           $query = claro_sql_query($sql);

           $num = mysql_numrows($query);
           if($num == 0)
           {
                // select infos about added exercise
                $sql = "SELECT *
                          FROM `".$TABLEEXERCISES."`
                         WHERE `id` = ".$insertedExercise;
                //echo $sql;
                $result = claro_sql_query($sql);
                $exercise = mysql_fetch_array($result);

                // create new module
                $sql = "INSERT
                          INTO `".$TABLEMODULE."`
                               (`name` , `comment`, `contentType`)
                        VALUES ('".addslashes($exercise['titre'])."' , '".addslashes($langDefaultModuleComment)."', '".CTEXERCISE_."')";
                //echo "<br /><1> ".$sql;
                $query = claro_sql_query($sql);

                $insertedExercice_id = mysql_insert_id();

                // create new asset
                $sql = "INSERT
                          INTO `".$TABLEASSET."`
                               (`path` , `module_id` , `comment`)
                        VALUES ('".$insertedExercise."', ".claro_addslashes($insertedExercice_id)." , '')";
                //echo "<br /><2> ".$sql;
                $query = claro_sql_query($sql);

                $insertedAsset_id = mysql_insert_id();

                $sql = "UPDATE `".$TABLEMODULE."`
                           SET `startAsset_id` = ".$insertedAsset_id."
                         WHERE `module_id` = ".$insertedExercice_id;
                //echo "<br /><3> ".$sql;
                $query = claro_sql_query($sql);

                // determine the default order of this Learning path
                $result = claro_sql_query("SELECT MAX(`rank`)
                                         FROM `".$TABLELEARNPATHMODULE."`");

                list($orderMax) = mysql_fetch_row($result);
                $order = $orderMax + 1;
                // finally : insert in learning path
                $sql = "INSERT
                          INTO `".$TABLELEARNPATHMODULE."`
                               (`learnPath_id`, `module_id`, `specificComment`, `rank`, `lock`)
                        VALUES ('".$_SESSION['path_id']."', '".$insertedExercice_id."','".addslashes($langDefaultModuleAddedComment)."', ".$order.",'OPEN')";
                //echo "<br /><4> ".$sql;
                $query = claro_sql_query($sql);

                $dialogBox .= $exercise['titre'] ." :  ".$langExInsertedAsModule."<br>";
           }
           else    // exercise is already used as a module in another learning path , so reuse its reference
           {
                // check if this is this LP that used this exercise as a module
                $sql = "SELECT *
                          FROM `".$TABLELEARNPATHMODULE."` AS LPM,
                               `".$TABLEMODULE."` AS M,
                               `".$TABLEASSET."` AS A
                         WHERE M.`module_id` =  LPM.`module_id`
                           AND M.`startAsset_id` = A.`asset_id`
                           AND A.`path` = ".$insertedExercise."
                           AND LPM.`learnPath_id` = ".$_SESSION['path_id'];

                $query2 = claro_sql_query($sql);
                $num = mysql_numrows($query2);
                if ($num == 0)     // used in another LP but not in this one, so reuse the module id reference instead of creating a new one
                {
                    $thisExerciseModule = mysql_fetch_array($query);
                    // determine the default order of this Learning path
                    $result = claro_sql_query("SELECT MAX(`rank`)
                                             FROM `".$TABLELEARNPATHMODULE."`");

                    list($orderMax) = mysql_fetch_row($result);
                    $order = $orderMax + 1;
                    // finally : insert in learning path
                    $sql = "INSERT
                              INTO `".$TABLELEARNPATHMODULE."`
                                   (`learnPath_id`, `module_id`, `specificComment`, `rank`, `lock`)
                            VALUES (".$_SESSION['path_id'].", ".$thisExerciseModule['module_id'].",'".addslashes($langDefaultModuleAddedComment)."', ".$order.", 'OPEN')";
                    //echo "<br /><4> ".$sql;
                    $query = claro_sql_query($sql);

                    // select infos about added exercise
                    $sql = "SELECT *
                              FROM `".$TABLEEXERCISES."`
                             WHERE `id` = ".$insertedExercise;
                    //echo $sql;
                    $result = claro_sql_query($sql);
                    $exercise = mysql_fetch_array($result);
                    $dialogBox .= $exercise['titre']." : ".$langExInsertedAsModule."<br>";
                }
                else
                {
                    $dialogBox .= $listex['titre']." : ".$langExAlreadyUsed."<br>";
                }
           }
       }
    }
   //end ADD command

   //STEP ONE : display form to add an exercise

   display_my_exercises($dialogBox);

   //STEP TWO : display learning path content

  claro_disp_tool_title($langPathContentTitle);
  echo '<a href="learningPathAdmin.php">&lt;&lt;&nbsp;'.$langBackToLPAdmin.'</a>';
  // display list of modules used by this learning path
  display_path_content($param_array, $table);

   // footer

   @include($includePath."/claro_init_footer.inc.php");
?>
