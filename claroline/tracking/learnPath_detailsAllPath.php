<?php 
/**
 * CLAROLINE 
 *
 * This script displays the stats of all users of a course 
 * for his progression into the sum of all learning paths of the course
 *
 * @version 1.6
 *
 * @copyright 2001-2005 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE 
 *
 * @package TRACKING
 *
 * @author Claro Team <cvs@claroline.net>
 * @author Sebastien Piraux  <piraux_seb@hotmail.com>
 * @author Gioacchino Poletto <info@polettogioacchino.com>
 *
 */
 
require '../inc/claro_init_global.inc.php';

$interbredcrump[]= array ("url"=>"../learnPath/learningPathList.php", "name"=> $langLearningPathList);

$nameTools = $langTrackAllPath;

$tbl_cdb_names               = claro_sql_get_course_tbl();
$tbl_mdb_names               = claro_sql_get_main_tbl();

$tbl_rel_course_user         = $tbl_mdb_names['rel_course_user'  ];
$tbl_user                    = $tbl_mdb_names['user'             ];
$tbl_lp_learnPath            = $tbl_cdb_names['lp_learnPath'           ];
$tbl_lp_rel_learnPath_module = $tbl_cdb_names['lp_rel_learnPath_module'];
$tbl_lp_user_module_progress = $tbl_cdb_names['lp_user_module_progress'];
$tbl_lp_module               = $tbl_cdb_names['lp_module'              ];
$tbl_lp_asset                = $tbl_cdb_names['lp_asset'               ];

// keep old name for inside the library that use the vars in global
$TABLELEARNPATH         = $tbl_lp_learnPath;
$TABLEMODULE            = $tbl_lp_module;
$TABLELEARNPATHMODULE   = $tbl_lp_rel_learnPath_module;
$TABLEASSET             = $tbl_lp_asset;
$TABLEUSERMODULEPROGRESS= $tbl_lp_user_module_progress;

$TABLECOURSUSER	        = $tbl_rel_course_user;
$TABLEUSER              = $tbl_user;

include($includePath."/claro_init_header.inc.php");
include($includePath."/lib/statsUtils.lib.inc.php");


include($includePath."/lib/learnPath.lib.inc.php");

$is_allowedToTrack = $is_courseAdmin;

// display title
$titleTab['mainTitle'] = $nameTools;
$titleTab['subTitle'] = $langTrackAllPathExplanation;

claro_disp_tool_title($titleTab);

if($is_allowedToTrack && $is_trackingEnabled) 
{
    // display a list of user and their respective progress
    
    $sql = "SELECT U.`nom`, U.`prenom`, U.`user_id`
          FROM `".$tbl_user."` AS U, `".$tbl_rel_course_user."`	 AS CU
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
		$visibility = " AND LP.`visibility` = 'SHOW' ";

		// check if user is anonymous
		$lpUid = $user['user_id'];
		if($lpUid)
		{
			$uidCheckString = "AND UMP.`user_id` = ".$lpUid;
		}
		else // anonymous
		{
			$uidCheckString = "AND UMP.`user_id` IS NULL ";
		}

		// list available learning paths
		$sql = "SELECT LP.* , MIN(UMP.`raw`) AS minRaw, LP.`lock`
		         FROM `".$tbl_lp_learnPath."` AS LP
		   LEFT JOIN `".$tbl_lp_rel_learnPath_module."` AS LPM
		          ON LPM.`learnPath_id` = LP.`learnPath_id`
		   LEFT JOIN `".$tbl_lp_user_module_progress."` AS UMP
		          ON UMP.`learnPath_module_id` = LPM.`learnPath_module_id`
		          ".$uidCheckString."
		       WHERE 1=1
		           ".$visibility."
		    GROUP BY LP.`learnPath_id`
		    ORDER BY LP.`rank`";

		$result = claro_sql_query($sql);


		$iterator = 1;
		$globalprog = 0;

		while ( $list = mysql_fetch_array($result) ) // while ... learning path list
		{
			// % progress
			$prog = get_learnPath_progress($list['learnPath_id'], $user['user_id']);

			if ($prog >= 0)
			{
			    $globalprog += $prog;
			}
			$iterator++;
		}


		if( $iterator == 1 )
		{
			echo "<tr><td align=\"center\" colspan=\"8\">".$langNoLearningPath."</td></tr>";
		}
		else
		{
			$total = round($globalprog/($iterator-1));
			echo "<tr>
			  <td><a href=\"".$clarolineRepositoryWeb."/tracking/userLog.php?uInfo=".$user['user_id']."&view=0010000\">".$user['nom']." ".$user['prenom']."</a></td>\n
			  <td align=\"right\">".
			claro_disp_progress_bar($total, 1).
			" </td>
			   <td align=\"left\"><small>".$total."%</small></td>
			</tr>";
		}

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
