<?php
// $Id$
/*
  +----------------------------------------------------------------------+
  | CLAROLINE version 1.3.2 $Revision$                            |
  +----------------------------------------------------------------------+
  | Copyright (c) 2001, 2004 Universite catholique de Louvain (UCL)      |
  +----------------------------------------------------------------------+
  | This source file is subject to the GENERAL PUBLIC LICENSE,           |
  | available through the world-wide-web at                              |
  | http://www.gnu.org/copyleft/gpl.html                                 |
  +----------------------------------------------------------------------+
  | Authors:                                                             |
  +----------------------------------------------------------------------+

  DESCRIPTION:
  ****

*/
   // select more infos about the module
   /*
   $sql = "SELECT *
             FROM `".$TABLEASSET."`
            WHERE `module_id` = ".$_SESSION['module_id'];
  $query = mysql_query($sql);
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
   echo                "<tr class=\"headerX\">";
   echo                "<th>$langFileName</th>\n",
                       "<th>$langSize</th>\n",
                       "<th>$langDate</th>\n";
   echo                "</tr><tbody>\n";

   echo "<tr align=\"center\">";
   echo " <td align=\"left\">".basename($file)."</td>",
        " <td>".$fileSize."</td>",
        " <td>".$fileDate."</td>";
   echo "</tr>";

   // same as header
   echo "</tbody></table>";
?>

