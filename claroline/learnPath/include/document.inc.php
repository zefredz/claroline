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
 *
 */
   // select more infos about the module
   /*
   $sql = "SELECT *
             FROM `".$TABLEASSET."`
            WHERE `module_id` = ".$_SESSION['module_id'];
  $query = claro_sql_query($sql);
  $asset = mysql_fetch_array($query);
     */
  //
  // document browser vars
  $TABLEDOCUMENT     = $_course['dbNameGlu']."document";
  
  $courseDir   = $_course['path']."/document";
  $baseWorkDir = $coursesRepositorySys.$courseDir;



   //####################################################################################\\
   //######################## DISPLAY DETAILS ABOUT THE DOCUMENT ########################\\
   //####################################################################################\\
   echo "<hr noshade=\"noshade\" size=\"1\" />";
   // Update infos about asset
   $sql = "SELECT *
             FROM `".$TABLEMODULE."`
            WHERE `module_id` = ".$_SESSION['module_id'];
   $query = claro_sql_query($sql);
   $module = mysql_fetch_array($query);
   $sql = "SELECT *
             FROM `".$TABLEASSET."`
            WHERE `module_id` = ".$_SESSION['module_id'];
   $query = claro_sql_query($sql);
   $asset = mysql_fetch_array($query);

   $file = $baseWorkDir.$asset['path'];
   $fileSize = format_file_size(filesize($file));
   $fileDate = format_date(filectime($file));

   echo "<h4>".$langDocumentInModule."</h4>";
   echo "<table class=\"claroTable\" width=\"100%\" border=\"0\" cellspacing=\"2\">";
   echo " <thead>\n";
   echo                "<tr class=\"headerX\">";
   echo                "<th>$langFileName</th>\n",
                       "<th>$langSize</th>\n",
                       "<th>$langDate</th>\n";
   echo                "</tr></thead>\n<tbody>\n";

   echo "<tr align=\"center\">";
   echo " <td align=\"left\">".basename($file)."</td>",
        " <td>".$fileSize."</td>",
        " <td>".$fileDate."</td>";
   echo "</tr>";

   // same as header
   echo "</tbody></table>";
?>
