<?php 
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.5.*                                              |
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2002 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
      |   $Id$         |
      +----------------------------------------------------------------------+
      |   This program is free software; you can redistribute it and/or      |
      |   modify it under the terms of the GNU General Public License        |
      |   as published by the Free Software Foundation; either version 2     |
      |   of the License, or (at your option) any later version.             |
      |                                                                      |
      |   This program is distributed in the hope that it will be useful,    |
      |   but WITHOUT ANY WARRANTY; without even the implied warranty of     |
      |   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the      |
      |   GNU General Public License for more details.                       |
      |                                                                      |
      |   You should have received a copy of the GNU General Public License  |
      |   along with this program; if not, write to the Free Software        |
      |   Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA          |
      |   02111-1307, USA. The GNU GPL license is also available through     |
      |   the world-wide-web at http://www.gnu.org/copyleft/gpl.html         |
      +----------------------------------------------------------------------+
      | Authors:  see credits.txt                                            |
      +----------------------------------------------------------------------+

 */
 $langFile = "tracking";
require '../inc/claro_init_global.inc.php';

$interbredcrump[]= array ("url"=>"../learnPath/learningPathList.php", "name"=> $langLearningPathList);
$interbredcrump[]= array ("url"=>"learnPath_details.php?path_id=".$_GET['path_id'], "name"=> $langStatsOfLearnPath);

$nameTools = $langModules;


// table names
$TABLELEARNPATH         = $_course['dbNameGlu']."lp_learnPath";
$TABLEMODULE            = $_course['dbNameGlu']."lp_module";
$TABLELEARNPATHMODULE   = $_course['dbNameGlu']."lp_rel_learnPath_module";
$TABLEASSET             = $_course['dbNameGlu']."lp_asset";
$TABLEUSERMODULEPROGRESS= $_course['dbNameGlu']."lp_user_module_progress";

$TABLECOURSUSER	        = $mainDbName."`.`cours_user";
$TABLEUSER = $mainDbName."`.`user";


include($includePath."/claro_init_header.inc.php");
include($includePath."/lib/statsUtils.lib.inc.php");

// lib of learning path tool
include($includePath."/lib/learnPath.lib.inc.php");
//lib of document tool
include($includePath."/lib/fileDisplay.lib.php");

// only the course administrator or the student himself can view the tracking
$is_allowedToTrack = $is_courseAdmin;
if (isset($uInfo) && isset($_uid)) $is_allowedToTrack = $is_allowedToTrack || ($uInfo == $_uid);

// get infos about the user
$sql = "SELECT `nom`, `prenom`, `email` 
        FROM `".$TABLEUSER."`
       WHERE `user_id` = ".$_GET['uInfo'];
$uDetails = claro_sql_query_fetch_all($sql);

// get infos about the learningPath
$sql = "SELECT `name` 
        FROM `".$TABLELEARNPATH."`
       WHERE `learnPath_id` = ".$_GET['path_id'];
$lpDetails = claro_sql_query_fetch_all($sql);

// display title
$titleTab['mainTitle'] = $nameTools;
$titleTab['subTitle'] = $lpDetails[0]['name'];
claro_disp_tool_title($titleTab);


if($is_allowedToTrack && $is_trackingEnabled) 
{
  //### PREPARE LIST OF ELEMENTS TO DISPLAY #################################

   $sql = "SELECT LPM.* , 
                M.*, 
                UMP.`lesson_status`, UMP.`raw`, 
                UMP.`scoreMax`, UMP.`credit`,
                UMP.`session_time`, UMP.`total_time`,
                A.`path`
             FROM `".$TABLELEARNPATHMODULE."` AS LPM,
                  `".$TABLEMODULE."` AS M
       LEFT JOIN `".$TABLEUSERMODULEPROGRESS."` AS UMP
               ON UMP.`learnPath_module_id` = LPM.`learnPath_module_id`
               AND UMP.`user_id` = ".$_GET['uInfo']."
       LEFT JOIN `".$TABLEASSET."` AS A
              ON M.`startAsset_id` = A.`asset_id`
            WHERE LPM.`module_id` = M.`module_id`
              AND LPM.`learnPath_id` = ".$_GET['path_id']."
              AND LPM.`visibility` = 'SHOW'
              AND LPM.`module_id` = M.`module_id`
         GROUP BY LPM.`module_id`
         ORDER BY LPM.`rank`";

  $result = claro_sql_query($sql);
  
  $extendedList = array();
  while ($list = mysql_fetch_array($result, MYSQL_ASSOC))
  {
    $extendedList[] = $list;
  }
  
  // build the array of modules     
  // build_element_list return a multi-level array, where children is an array with all nested modules
  // build_display_element_list return an 1-level array where children is the deep of the module
  $flatElementList = build_display_element_list(build_element_list($extendedList));
   
  $moduleNb = 0;
  $global_time = "0000:00:00";
   
  // look for maxDeep
  $maxDeep = 1; // used to compute colspan of <td> cells
  for ($i=0 ; $i < sizeof($flatElementList) ; $i++)
  {
    if ($flatElementList[$i]['children'] > $maxDeep) $maxDeep = $flatElementList[$i]['children'] ;
  }
  
  //### SOME USER DETAILS ###########################################
  echo $informationsAbout." : <br>";
  echo "<ul>\n"
          ."<li>".$langFirstName." : ".$uDetails[0]['nom']."</li>\n"
          ."<li>".$langLastName." : ".$uDetails[0]['prenom']."</li>\n"
          ."<li>".$langEmail." : ".$uDetails[0]['email']."</li>\n"
          ."</ul>";
  //### TABLE HEADER ################################################
?>
     <br>
     <table class="claroTable" width="100%" border="0" cellspacing="2">
            <tr class="headerX" align="center" valign="top">
              <th colspan="<?= $maxDeep+1; ?>"><?= $langModule; ?></th>
              <th><?= $langLastSessionTimeSpent; ?></th>
              <th><?= $langTotalTimeSpent; ?></th>
              <th><?= $langLessonStatus; ?></th>
              <th colspan="2"><?= $langProgress; ?></th>
              
             </tr>
             <tbody>
<?
  
  //### DISPLAY LIST OF ELEMENTS #####################################
  foreach ($flatElementList as $module)
  {
          if( $module['scoreMax'] > 0 )
          {
               $progress = @round($module['raw']/$module['scoreMax']*100);
          }
          else
          {
                $progress = 0;
          }
          
          if ( $module['contentType'] == CTSCORM_ && $module['scoreMax'] <= 0 )
          {
             if ( $module['lesson_status'] == 'COMPLETED' || $module['lesson_status'] == 'PASSED')
             {
                 $progress = 100;
             }
             else
             {
                 $progress = 0;
             }
          }
          
          
          // display the current module name
          
          $spacingString = "";
          for($i = 0; $i < $module['children']; $i++)
            $spacingString .= "<td width=\"5\">&nbsp;</td>";
          $colspan = $maxDeep - $module['children']+1;
          
          echo "<tr align=\"center\"".$style.">\n".$spacingString."<td colspan=\"".$colspan."\" align=\"left\">";
          //-- if chapter head
          if ( $module['contentType'] == CTLABEL_ )
          {
              echo "<b>".$module['name']."</b>";
          }
          //-- if user can access module
          else
          {
              if($module['contentType'] == CTEXERCISE_ ) 
                $moduleImg = "quiz.gif";
              else
                $moduleImg = choose_image(basename($module['path']));
                
              $contentType_alt = selectAlt($module['contentType']);
              echo "<img src=\"".$clarolineRepositoryWeb."img/".$moduleImg."\" alt=\"".$contentType_alt."\" border=\"0\">".$module['name'];

          }
          
          echo "</td>";
          
          if ($module['contentType'] == CTSCORM_)
          {          
              $session_time = preg_replace("/\.[0-9]{0,2}/", "", $module['session_time']);
              $total_time = preg_replace("/\.[0-9]{0,2}/", "", $module['total_time']);
              $global_time = addScormTime($global_time,$total_time);
          }
          elseif($module['contentType'] == CTLABEL_)
          {
              $session_time = $module['session_time'];
              $total_time = $module['total_time'];
          }
          else
          {
              // if no progression has been recorded for this module
              // leave 
              if($module['lesson_status'] == "") 
              {
                $session_time = "&nbsp;";
                $total_time = "&nbsp;";
              }
              else // columns are n/a
              {
                $session_time = "-";
                $total_time = "-";
              }
          }
          //-- session_time
          echo "<td>".$session_time."</td>";
          //-- total_time
          echo "<td>".$total_time."</td>";
          //-- status
          echo "<td>";
          if($module['contentType'] == CTEXERCISE_ && $module['lesson_status'] != "" ) 
            echo " <a href=\"userLog.php?uInfo=".$_GET['uInfo']."&view=0100000&exoDet=".$module['path']."\">".strtolower($module['lesson_status'])."</a>";
          else
            echo strtolower($module['lesson_status']);
          echo "</td>";
          //-- progression
          if($module['contentType'] != CTLABEL_ )
          {
                // display the progress value for current module
                
                echo "<td align=\"right\">".claro_disp_progress_bar($progress, 1)."</td>";
                echo "<td align=\"left\">
                       <small>&nbsp;".$progress."%</small>
                      </td>";
          }
          else // label
          {
            echo "<td colspan=\"2\">&nbsp;</td>";
          }
          
          if ($progress > 0)
          {
            $globalProg =  $globalProg+$progress;
          }
          
          if($module['contentType'] != CTLABEL_) 
              $moduleNb++; // increment number of modules used to compute global progression except if the module is a title
           
          echo "\n</tr>\n";
  }
  echo "</tbody>\n<tfoot>\n";
  
  if ($moduleNb == 0)
  {
          echo "<tr><td align=\"center\" colspan=\"3\">".$langNoModule."</td></tr>";
  }
  elseif($moduleNb > 0)
  {
            // add a blank line between module progression and global progression
            echo "<tr><td colspan=\"".($maxDeep+6)."\">&nbsp;</td></tr>";
            // display global stats
            echo "<tr><small>".
                "<td colspan=\"".($maxDeep+1)."\">&nbsp;</td>".
                "<td align=\"right\">".(($global_time != "0000:00:00")? $langTimeInLearnPath : "&nbsp;")."</td>".
                "<td align=\"center\">".(($global_time != "0000:00:00")? preg_replace("/\.[0-9]{0,2}/", "", $global_time) : "&nbsp;")."</td>".
                "<td align=\"right\">".$langGlobalProgress."</td>".
                "<td align=\"right\">".
                claro_disp_progress_bar(round($globalProg / ($moduleNb) ), 1).
            	"</td>".
                "<td align=\"left\">
                    <small>&nbsp;".round($globalProg / ($moduleNb) ) ."%</small></td>
                  </td>
                  </tr>";
  }
  echo "</tfoot>\n</table>";
}
// not allowed
else
{
    if(!$is_trackingEnabled)
    {
        echo $langTrackingDisabled;
    }
    else
    {
        echo $langNotAllowed;
    }
}

include($includePath."/claro_init_footer.inc.php");
?>
