<?php // $Id$
/**
 * @version  CLAROLINE version 1.6
 *
 * @copyright (c) 2001, 2005 Universite catholique de Louvain (UCL)
 *
 * @license GENERAL PUBLIC LICENSE
 *
 * @author Piraux Sébastien <pir@cerdecam.be>
 * @author Lederer Guillaume <led@cerdecam.be>
 *
 * @package CLLNP
 */
  if ((bool) stristr($_SERVER['PHP_SELF'], basename(__FILE__))) die('---');
  if(isset($cmd) && $cmd = "raw")
  {
              // change raw if value is a number between 0 and 100
              if (isset($_POST['newRaw']) && is_num($_POST['newRaw']) && $_POST['newRaw'] <= 100 && $_POST['newRaw'] >= 0 )
              {
                        $sql = "UPDATE `".$TABLELEARNPATHMODULE."`
                                   SET `raw_to_pass` = ".$_POST['newRaw']."
                                 WHERE `module_id` = ".$_SESSION['module_id']."
                                   AND `learnPath_id` = ".$_SESSION['path_id'];
                        claro_sql_query($sql);

                        $dialogBox .= $langRawHasBeenChanged;
              }
  }

   //####################################################################################\\
   //############################### DIALOG BOX SECTION #################################\\
   //####################################################################################\\
   if ($dialogBox)
   {
      claro_disp_message_box($dialogBox);
   }

  // form to change raw needed to pass the exercise
  $sql = "SELECT *
            FROM `".$TABLELEARNPATHMODULE."` AS LPM
           WHERE LPM.`module_id` = ".$_SESSION['module_id']."
             AND LPM.`learnPath_id` = ".$_SESSION['path_id'];

  $query = claro_sql_query($sql);
  $learningPath_module = mysql_fetch_array($query);

  if ($learningPath_module['lock'] == 'CLOSE') // this module blocks the user if he doesn't complete
  {
       echo "<hr noshade=\"noshade\" size=\"1\" />";
       //echo "<p>".$langModuleHelpExercise."</p>";
       echo "<form method=\"POST\" action=\"".$_SERVER['PHP_SELF']."\"><label for=\"newRaw\">";
       echo $langChangeRaw;
       echo "</label><input type=\"text\" value=\"".$learningPath_module['raw_to_pass']."\" name=\"newRaw\" id=\"newRaw\" size=\"3\" maxlength=\"3\" /> % ";
       echo "<input type=\"hidden\" name=\"cmd\" value=\"raw\" />";
       echo "<input type=\"submit\" value=\"$langOk\" />";
       echo "</form>";

  }

?>
