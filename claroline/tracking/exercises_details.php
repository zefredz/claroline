<?php 
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.3.0 $Revision$                            |
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
      | Authors:                                                           |
      |          Hugues Peeters    <peeters@ipm.ucl.ac.be>                   |
      |          Christophe Gesché <gesche@ipm.ucl.ac.be>                    |
      |          Sebastien Piraux  <piraux_seb@hotmail.com>   |
      |                                                                        |
      |                    http://www.claroline.net/                |
      +----------------------------------------------------------------------+
      
      
      DESCRIPTION
      -------------------
      This page display global information about 
 */
 
$langFile = "tracking";
require '../inc/claro_init_global.inc.php';

$interbredcrump[]= array ("url"=>"courseLog.php", "name"=> $langToolName);

$nameTools = $langStatsOfExercise;

$htmlHeadXtra[] = "<style type=\"text/css\">
<!--
.secLine {background-color : #E6E6E6;}
.content {padding-left : 15px;padding-right : 15px; }
.specialLink{color : #0000FF;}
-->
</style>
<STYLE media=\"print\" type=\"text/css\">
<!--
TD {border-bottom: thin dashed Gray;}
-->
</STYLE>";


// regroup table names for maintenance purpose
$TABLETRACK_EXERCISES = $_course['dbNameGlu']."track_e_exercices";
$TABLE_QUIZ_TEST = $_course['dbNameGlu']."quiz_test";

$TABLECOURSUSER	        = $mainDbName."`.`cours_user";
$TABLEUSER = $mainDbName."`.`user";


@include($includePath."/claro_init_header.inc.php");
@include($includePath."/lib/statsUtils.lib.inc.php");

$is_allowedToTrack = $is_courseAdmin;

// get infos about the exercise
$sql = "SELECT * 
        FROM `".$TABLE_QUIZ_TEST."`
       WHERE `id` = ".$_GET['exo_id'];

$result = claro_sql_query($sql);
$exo_details = @mysql_fetch_array($result);

// display title
$titleTab['mainTitle'] = $nameTools;
$titleTab['subTitle'] = $langStatsOfExercise." : ".$exo_details['titre'];
claro_disp_tool_title($titleTab);

if($is_allowedToTrack && $is_trackingEnabled) 
{

  // get global infos about scores in the exercise
  $sql = "SELECT  MIN(TEX.`exe_result`) AS `minimum`, 
                MAX(TEX.`exe_result`) AS `maximum`, 
                AVG(TEX.`exe_result`) AS `average`,
                MAX(TEX.`exe_weighting`) AS `weighting` ,
                COUNT(DISTINCT TEX.`exe_user_id`) AS `users`,
                COUNT(TEX.`exe_user_id`) AS `tusers`,
				AVG(`TEX`.`exe_time`) AS `avgTime`
        FROM `".$TABLETRACK_EXERCISES."` AS TEX
        WHERE TEX.`exe_exo_id` = ".$exo_details['id']."
                AND TEX.`exe_user_id` IS NOT NULL";
  
  $result = claro_sql_query($sql);
  $exo_scores_details = mysql_fetch_array($result);
?>

<ul>
  <? 
        if (isset($exo_score_details['weighting']) || $exo_scores_details['weighting'] != '')
            echo "<li>".$langWeighting." : ".$exo_scores_details['weighting']."</li>";

        if ( ! isset($exo_scores_details['minimum']) )
        {
          $exo_scores_details['minimum'] = 0;
          $exo_scores_details['maximum'] = 0;
          $exo_scores_details['average'] = 0;
        }
        else
        {
            // round average number for a beautifuler display :p
            $exo_scores_details['average'] = (round($exo_scores_details['average']*100)/100);
        }
  ?>
  <li><?php echo $langScoreMin; ?> : <?php echo $exo_scores_details['minimum']; ?></li>
  <li><?php echo $langScoreMax; ?> : <?php echo $exo_scores_details['maximum']; ?></li>
  <li><?php echo $langScoreAvg; ?> : <?php echo $exo_scores_details['average']; ?></li>
  <li><?php echo $langExeAvgTime; ?> : <?php echo round($exo_scores_details['avgTime']*100)/100; ?></li>
</ul>
<ul>
  <li><?php echo $langExerciseUsersAttempts; ?> : <?php echo $exo_scores_details['users']; ?></li>
  <li><?php echo $langExerciseTotalAttempts; ?> : <?php echo $exo_scores_details['tusers']; ?></li>
</ul>  


<?
  // display details
   $sql = "SELECT `U`.`nom`, `U`.`prenom`, `U`.`user_id`,
            MIN(TEX.`exe_result`) AS `minimum`,
            MAX(TEX.`exe_result`) AS `maximum`,
            AVG(TEX.`exe_result`) AS `average`,
            COUNT(TEX.`exe_result`) AS `attempts`,
			AVG(TEX.`exe_time`) AS `avgTime`
    FROM `".$TABLEUSER."` AS `U`, `".$TABLECOURSUSER."` AS `CU`, `".$TABLE_QUIZ_TEST."` AS `QT`
    LEFT JOIN `".$TABLETRACK_EXERCISES."` AS `TEX` 
          ON `CU`.`user_id` = `TEX`.`exe_user_id` 
          AND `QT`.`id` = `TEX`.`exe_exo_id`
    WHERE `CU`.`user_id` = `U`.`user_id`
      AND `CU`.`code_cours` = '".$_cid."'
      AND (
            `TEX`.`exe_exo_id` = ".$exo_details['id']." 
            OR 
            `TEX`.`exe_exo_id` IS NULL 
          )
    GROUP BY `U`.`user_id`
    ORDER BY `U`.`nom` ASC, `U`.`prenom` ASC";
    
    
  $result = claro_sql_query($sql);
  // display tab header
  echo "<table class=\"claroTable\" width=\"100%\" border=\"0\" cellspacing=\"2\">\n
      <tr class=\"headerX\" align=\"center\" valign=\"top\">\n
        <th>$langStudent</th>\n
        <th>$langScoreMin</th>\n
        <th>$langScoreMax</th>\n
        <th>$langScoreAvg</th>\n
        <th>$langAttempts</th>\n
        <th>$langExeAvgTime</th>\n
      </tr>\n
      <tbody>";
  // display tab content
  while ( $exo_users_details = mysql_fetch_array($result) )
  {
    if ( $exo_users_details['minimum'] == '' )
    {
      $exo_users_details['minimum'] = 0;
      $exo_users_details['maximum'] = 0;
    }
    echo 	 "<tr>\n"
      		."<td><a href=\"userLog.php?uInfo=".$exo_users_details['user_id']."&view=0100000&exoDet=".$exo_details['id']."\">"
			.$exo_users_details['nom']." ".$exo_users_details['prenom']."</a></td>\n"
      		."<td>".$exo_users_details['minimum']."</td>\n"
      		."<td>".$exo_users_details['maximum']."</td>\n"
      		."<td>".(round($exo_users_details['average']*100)/100)."</td>\n"
      		."<td>".$exo_users_details['attempts']."</td>\n"
      		."<td>".(round($exo_users_details['avgTime']*100)/100)."</td>\n"
    		."</tr>";
  }
  // foot of table
  echo "</tbody>\n</table>";

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


?>
</table>

<?
@include($includePath."/claro_init_footer.inc.php");
?>
