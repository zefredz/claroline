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

 */
 
$langFile = "tracking";
require '../inc/claro_init_global.inc.php';

$interbredcrump[]= array ("url"=>"../learnPath/learningPathList.php", "name"=> $langLearningPathList);

$nameTools = $langStatsOfLearnPath;

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
$TABLELEARNPATH         = $_course['dbNameGlu']."lp_learnPath";
$TABLEMODULE            = $_course['dbNameGlu']."lp_module";
$TABLELEARNPATHMODULE   = $_course['dbNameGlu']."lp_rel_learnPath_module";
$TABLEASSET             = $_course['dbNameGlu']."lp_asset";
$TABLEUSERMODULEPROGRESS= $_course['dbNameGlu']."lp_user_module_progress";

$TABLECOURSUSER	        = $mainDbName."`.`cours_user";
$TABLEUSER = $mainDbName."`.`user";


include($includePath."/claro_init_header.inc.php");
include($includePath."/lib/statsUtils.lib.inc.php");


include($includePath."/lib/learnPath.lib.inc.php");

$is_allowedToTrack = $is_courseAdmin;

// get infos about the learningPath
$sql = "SELECT `name` 
        FROM `".$TABLELEARNPATH."`
       WHERE `learnPath_id` = ".$_GET['path_id'];

$result = claro_sql_query($sql);
$pDetails = @mysql_fetch_array($result);

// display title
$titleTab['mainTitle'] = $nameTools;
$titleTab['subTitle'] = $pDetails['name'];
claro_disp_tool_title($titleTab);

if($is_allowedToTrack && $is_trackingEnabled) 
{
    // display a list of user and their respective progress
    
    $sql = "SELECT U.`nom`, U.`prenom`, U.`user_id`
          FROM `".$TABLEUSER."` AS U, `".$TABLECOURSUSER."`	 AS CU
          WHERE U.`user_id`= CU.`user_id`
           AND CU.`code_cours` = '$_cid'";
    $usersList = claro_sql_query_fetch_all($sql);
    // display tab header
    echo "<table class=\"claroTable\" width=\"100%\" border=\"0\" cellspacing=\"2\">\n
        <tr class=\"headerX\" align=\"center\" valign=\"top\">\n
          <th>$langStudent</th>\n
          <th colspan=\"2\">$langProgress</th>\n
        </tr>\n
        <tbody>";
    // display tab content
    foreach ( $usersList as $user )
    {
      $lpProgress = get_learnPath_progress($_GET['path_id'],$user['user_id']);
      echo "<tr>
          <td><a href=\"lp_modules_details.php?uInfo=".$user['user_id']."&path_id=".$_GET['path_id']."\">".$user['nom']." ".$user['prenom']."</a></td>\n
          <td align=\"right\">".
      claro_disp_progress_bar($lpProgress, 1).
      	" </td>
           <td align=\"left\"><small>".$lpProgress."%</small></td>
        </tr>";
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



include($includePath."/claro_init_footer.inc.php");
?>
